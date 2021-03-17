package vocie

import (
	"io/ioutil"
	"os"
	"qzpnp/app/model"
	"qzpnp/app/service/oss"
	"qzpnp/library/oss/qiniu"
	"qzpnp/library/util"
	"time"

	"github.com/gogf/gf/util/gconv"

	"github.com/gogf/gf/frame/g"

	"github.com/gogf/gf/os/glog"

	"github.com/gogf/gf/database/gdb"
)

// Process 处理过程封装
func Process(t uint32) {
	m := model.NewPnpTelCallbackModel()
	// 待处理的录音列表
	vocieList, err := m.GetRecordURLByCreatedAT(t)
	if err != nil {
		glog.Error(err)
	}
	if vocieList.IsEmpty() {
		return
	}

	// 上传并填充字段
	fillD := FillRecordUrlSave(vocieList)
	if len(fillD) <= 0 {
		return
	}
	glog.Info(fillD)

	// 保存数据
	_, err = m.UpdateRecordURLSave(fillD)
	if err != nil {
		glog.Error(err)
	}
}

// FillRecordUrlSave 获取待处理的录音列表
func FillRecordUrlSave(d gdb.Result) g.List {
	var fd g.List
	var timeout int64
	timeout = 60 * 3
	for _, v := range d {
		vm := v.GMap()
		recordUrl := vm.Get("record_url").(string)
		if recordUrl != "" {
			mp3Byte, err := util.GetNetFileByte(recordUrl, timeout)
			if err != nil {
				glog.Error(err)
				return fd
			}
			if len(mp3Byte) <= 0 {
				glog.Error("mp3Byte字节数为：" + gconv.String(len(mp3Byte)))
				continue
			}
			putRet, err := FileByteUpload(mp3Byte)
			if err != nil {
				glog.Error(err)
				return fd
			}
			fd = append(fd, g.Map{"id": v.GMap().Get("id").(int),
				"record_url_save": putRet.UrlFull,
				"updated_at":      time.Now().Unix(),
			})
		}
	}
	return fd
}

// FileByteUpload 字节文件上传到对象存储
func FileByteUpload(b []byte) (qiniu.ReturnBody, error) {
	ossPerfix := "voice"
	f := CreateTempFileByByte(b)
	//fmt.Println(ioutil.ReadFile(f))
	returnBody, err := oss.OssQiniuUpFile(f, ossPerfix)

	if err == nil {
		// 移除临时文件
		err := os.Remove(f)
		if err != nil {
			glog.Error("FileByteUpload移除临时文件夹错误：", err)
		}
	}
	return returnBody, err
}

func CreateTempFileByByte(b []byte) string {
	//在dir目录下创建一个新的、使用prefix为前缀的临时文件，以读写模式打开该文件并返回os.File指针。
	//如果dir是空字符串，TempFile使用默认用于临时文件的目录（参见os.TempDir函数）。
	file, err := ioutil.TempFile("", "tmpvoice")
	if err != nil {
		glog.Error("CreateTempFileByByte创建临时文件错误：", err)
	}
	defer func() {
		file.Close()
	}()

	if _, err := file.Write(b); err != nil {
		glog.Error("CreateTempFileByByte写入临时文件夹错误", err)
	}

	return file.Name()
}
