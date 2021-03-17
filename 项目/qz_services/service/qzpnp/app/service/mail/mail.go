package mail

import (
	"errors"
	"fmt"
	"io/ioutil"
	"os"
	"path/filepath"
	"qzpnp/app/model"
	"qzpnp/library/util/mail"
	"strings"
	"time"

	"github.com/gogf/gf/os/glog"

	"github.com/gogf/gf/database/gdb"

	"github.com/360EntSecGroup-Skylar/excelize/v2"

	"github.com/gogf/gf/frame/g"
	"github.com/gogf/gf/util/gconv"
)

// RecordCallListReport 电话拨打记录
func RecordCallListReport() error {
	nowDayStr := time.Now().Format("2006-01-02")
	nowDay, _ := time.ParseInLocation("2006-01-02", nowDayStr, time.Local)
	lastDay := nowDay.Add(-time.Second * time.Duration((24 * (60 * 60))))
	lastDayStartStr := lastDay.Format("20060102")

	tmpDir, _ := ioutil.TempDir("", "RecordList")
	NameXlsx := "RecordList" + lastDayStartStr + ".xlsx"
	RecordListFileNameXlsx := tmpDir + string(filepath.Separator) + NameXlsx

	// 配置文件
	cfg := &mail.Cfg{}
	cfg.MailSMTPServer = g.Config().GetString("mail.mail_smtp_server")
	cfg.MailSMTPUser = g.Config().GetString("mail.mail_smtp_user")
	cfg.MailSMTPPassword = g.Config().GetString("mail.mail_smtp_password")
	mailTo := g.Config().GetString("mail.mail_to")
	cfg.MailTo = strings.Split(mailTo, ",")

	// 生成报表

	m := model.NewPnpTelCallbackModel()
	t := uint32(lastDay.Unix())
	RecordList, err := m.GetRecordListByCreatedAT(t)
	if err != nil {
		glog.Error(err)
		return err
	}
	err = RecordCallListToXlsxData(RecordList, RecordListFileNameXlsx)
	if err != nil {
		glog.Error(err)
		return err
	}

	// 发送邮件

	ms := mail.NewServer(cfg)

	mail := &mail.Mail{}
	// 发件人
	mail.From = cfg.MailSMTPUser
	// 收件人
	mail.To = cfg.MailTo
	// 标题
	mail.Subject = fmt.Sprintf("虚拟号电话拨打记录列表【 %s 】在 %s 生成", NameXlsx, GetymdhmsStr())
	// 正文
	mail.HTML = []byte(mail.Subject + "\n ，下载附件")
	// 附件文件
	//fileName := string(filepath.Separator) + RecordListFileNameXlsx
	mail.Attachments = append(mail.Attachments, RecordListFileNameXlsx)

	err = ms.SendMail(mail)
	if err != nil {
		glog.Error(err)
		return err
	}
	defer func() {
		os.Remove(RecordListFileNameXlsx) // 删除临时文件
	}()
	return nil
}

// VoiceToXlsxData 数据集到excel文件
func RecordCallListToXlsxData(resultData gdb.Result, f string) error {
	var xlsxData [][]string
	for _, vcs := range resultData {
		vm := vcs.GMap()
		var rowOne []string
		rowOne = append(rowOne, vm.Get("remark").(string))
		rowOne = append(rowOne, vm.Get("record_url_save").(string))
		rowOne = append(rowOne, vm.Get("tel_b").(string))
		rowOne = append(rowOne, vm.Get("calltime").(string))
		rowOne = append(rowOne, gconv.String(vm.Get("duration")))
		rowOne = append(rowOne, vm.Get("callid").(string))

		xlsxData = append(xlsxData, rowOne)
	}

	// 字段 6 num
	head := []string{"订单号", "录音", "主叫", "拨打时间", "通话时长(秒)", "通话callid"}
	sheetName := "Sheet1"

	err := ExportFileExcel(xlsxData, f, head, sheetName)
	if err != nil {
		return err
	}
	return nil
}

// GetymdhmsStr 年月日时分秒 文本
func GetymdhmsStr() string {
	year := time.Now().Format("2006")
	month := time.Now().Format("01")
	day := time.Now().Format("02")
	hour := time.Now().Format("15")
	min := time.Now().Format("04")
	second := time.Now().Format("05")
	return year + month + day + hour + min + second
}

// ExportFileExcel 导出为excel
// index 1 data row 数据
// index 2 filepath 文件.xlsx
// index 3 head 表头第一行
// index 4 sheetname
func ExportFileExcel(p ...interface{}) error {
	// 参数定义
	var data [][]string  // 1
	var fileName string  // 2
	var head []string    // 3
	var sheetName string // 4

	var rowID = 1
	var fileExt = ".xlsx"
	var defaultSheetName = "Sheet1"

	// 可变参数检查
	pLen := len(p)
	if pLen <= 0 {
		return errors.New("缺少必要的传入参数")
	}
	if pLen < 2 {
		return errors.New("数据和文件名时必须的")
	}
	data = p[0].([][]string)
	fileName = p[1].(string)

	if !strings.HasSuffix(fileName, fileExt) {
		fileName = fileName + fileExt // 文件保存路径
	}

	if pLen >= 3 {
		head = p[2].([]string)
	}
	if pLen >= 4 {
		sheetName = p[3].(string)
	}

	// 处理为空的情况
	if sheetName == "" {
		sheetName = defaultSheetName
	}

	file := excelize.NewFile()
	if sheetName != defaultSheetName {
		file.NewSheet(sheetName)
		file.DeleteSheet(defaultSheetName)
	}
	streamWriter, err := file.NewStreamWriter(sheetName)
	if err != nil {
		println(err.Error())
		return err
	}

	// 处理表头
	if len(head) > 0 {
		var phead []interface{}
		for _, v := range head {
			phead = append(phead, excelize.Cell{Value: v})
		}
		err := streamWriter.SetRow("A1", phead)
		if err != nil {
			//println(err.Error())
			return err
		} else {
			// 表行数指向2，在增加了表头成功之后
			rowID = 2
		}
	}
	// 处理数据row
	if len(data) > 0 {
		for _, row := range data {
			rowOne := make([]interface{}, len(row))
			for k, v := range row {
				rowOne[k] = v
			}

			cell, _ := excelize.CoordinatesToCellName(1, rowID)
			if err := streamWriter.SetRow(cell, rowOne); err != nil {
				//println(err.Error())
				return err
			}

			rowID++
		}

		if err := streamWriter.Flush(); err != nil {
			//println(err.Error())
			return err
		}

	}

	if err := file.SaveAs(fileName); err != nil {
		return err
	}
	return nil
}
