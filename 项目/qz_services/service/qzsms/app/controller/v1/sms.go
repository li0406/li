package v1

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/database/gdb"
	"github.com/gogf/gf/g/encoding/gjson"
	"github.com/gogf/gf/g/frame/gmvc"
	"github.com/gogf/gf/g/util/gconv"
	"github.com/gogf/gf/g/util/gvalid"
	"qzsms/app/enum"
	"qzsms/app/model"
	"qzsms/app/service/chuanglan"
	"qzsms/app/service/chuanglanyx"
	ddrobot "qzsms/app/service/robot/dingding"
	yrt "qzsms/app/service/yunorngt"
	yunorngtyx "qzsms/app/service/yunrongtyx"
	"qzsms/app/service/yuntongxun"
	"qzsms/library/prometheus/factory"
	"qzsms/library/util"
	"strconv"
	"strings"
	"time"
)

type SmsController struct {
	gmvc.Controller
}

type SmsSendParam struct {
	Sms_Id string `gvalid:"sms_id@required#短信模版ID不能为空"`
	Params string
	Tel string  `gvalid:"tel@required#电话不能为空"`
	From_app string `gvalid:"from_app@required#项目来源不能为空"`
	Referer string
	ParamsArr []string
}

type SendSmsStruct struct {
	SmsSendParam *SmsSendParam
	App gdb.Map
	Template *model.SmsTemplate
	App_env string
}

func (c *SmsController) SendSms()  {
	var json []byte
	sms := &SmsSendParam{
		Sms_Id:c.Request.Get("sms_id"),
		Params:c.Request.Get("params"),
		Tel:c.Request.Get("tel"),
		From_app:c.Request.Get("from_app"),
		Referer:c.Request.Get("referer"),
	}

	err := gvalid.CheckStruct(sms,nil)
	if err  !=  nil {
		json,_ := util.EncodeResponseJson(4000002,enum.CODE_4000002,err.Map()["required"])
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//获取发送项目信息
	app := model.NewSmsAppModel().GetAppInfo(sms.From_app)
	if len(app) == 0 || gconv.Int(app["is_enable"]) == 0 {
		json,_ := util.EncodeResponseJson(4000004,enum.CODE_4000004,nil)
		c.Request.Response.WriteJson(json)
		c.Request.ExitAll()
	}

	//根据ID获取模版信息
	m := model.NewSmsTemplateModel()
	res,errret := m.GetSmsTemplateInfo(sms.Sms_Id)

	if errret == nil{
		if len(res) > 0 {
			for _,val := range res {
				if m.Id == "" {
					m.Id = val["id"].(string)
					m.Content = val["content"].(string)
					m.SmsSign.Id = val["sign_id"].(int)
					m.SmsSign.Name = val["sign_name"].(string)
					m.Encrypt = val["encrypt"].(int)
					m.Type = val["type"].(int)
				}
				gateway := model.SmsGateway{
					Id:val["gateway_id"].(int),
					Name:val["gateway_name"].(string),
					Level:val["level"].(int),
					Slot:val["slot"].(string),
					Third_temp_id:val["third_temp_id"].(string),
				}
				m.SmsGateway = append(m.SmsGateway, gateway)
			}
			
			//替换模版中的变量
			params := c.Request.Get("params")
			var paramsArr []string
			gjson.DecodeTo(params,&paramsArr)
			//如果是字符串，英文逗号切割
			if len(paramsArr) == 0 {
				paramsArr = strings.Split(params,",")
			}

			if len(paramsArr) > 0 {
				for _,val := range paramsArr {
					m.Content =  strings.Replace(m.Content,"{变量}",val,1)
				}
			}
			sms.ParamsArr = paramsArr
			config := g.Config()
			sendSmsStruct := SendSmsStruct{
				SmsSendParam: sms,
				App:          app,
				Template:    m,
				App_env: config.GetString("setting.app_env"),
			}

			//定义通道
			c := make(chan SendSmsStruct)
			f := make(chan int)
			go producer(c,sendSmsStruct)
			go customer_send(c,f)
			<-f

			json,_ = util.EncodeResponseJson(0,enum.CODE_0,nil)
		} else {
			json,_ = util.EncodeResponseJson(4000001,enum.CODE_4000001,nil)
		}
	} else {
		json,_ = util.EncodeResponseJson(5000003,enum.CODE_5000003,nil)
	}
	c.Request.Response.WriteJson(json)
}

func producer (c chan SendSmsStruct,s SendSmsStruct) {
	defer close(c)//关闭通道
	c <- s //将s值赋值给通道C
}

func customer_send (c chan SendSmsStruct,f chan int){
	for{
		if v,ok := <-c;ok {
			sms := v.SmsSendParam
			app :=  v.App
			m := v.Template
			//根据查询数据发送短信信息
			i := 0
			var logList []model.SmsLogSend
			for {
				//最多发送三次，如果超过了网关数量
				if i > 3 || (i+1) > len(m.SmsGateway) {
					break
				}
				var errorList = make(map[string] string)
				var status = 1
				//do something
				gateway := m.SmsGateway[i]
				var content = ""
				switch gateway.Slot {
				case "yunrongt":
					//云融正通 行业短信
					//添加签名
					content = "【" + m.SmsSign.Name + "】" + m.Content
					result,msg := yrt.SendSms(sms.Tel,content)
					res,_ := strconv.ParseInt(result,10,64)
					if res <= 0 {
						status = 2
						errorList["error_code"] = result
						errorList["error_msg"] = msg
					}
					break
				case "yunrongtyx":
					//云融正通 营销短信
					//添加签名
					content = "【" + m.SmsSign.Name + "】" + m.Content
					result,msg := yunorngtyx.SendSms(sms.Tel,content)
					res,_ := strconv.Atoi(result)
					if res <= 0 {
						status = 2
						errorList["error_code"] = result
						errorList["error_msg"] = msg
					}
					break
				case "yuntongxun":
					//容联云通讯
					result,msg := yuntongxun.SendSms(sms.Tel,gateway.Third_temp_id,sms.ParamsArr)
					if result != "000000" {
						status = 2
						errorList["error_code"] = result
						errorList["error_msg"] = msg
					}
					break
				case "chuanglan":
					content = "【" + m.SmsSign.Name + "】" + m.Content
					result,msg := chuanglan.SendSms(sms.Tel,content)
					if result != "0"  {
						status = 2
						errorList["error_code"] = result
						errorList["error_msg"] = msg
					}
					break
				case "chuanglanyx":
					content = "【" + m.SmsSign.Name + "】" + m.Content
					result,msg := chuanglanyx.SendSms(sms.Tel,content)
					if result != "0"  {
						status = 2
						errorList["error_code"] = result
						errorList["error_msg"] = msg
					}
					break
				}
				var log = model.SmsLogSend{}
				tel := sms.Tel
				if m.Encrypt == 1 {
					//脱敏操作
					tel = sms.Tel[0:3] + "*****" + sms.Tel[len(sms.Tel)-3:len(sms.Tel)]
				}
				log.Tel = tel
				log.Tel_encrypt = util.MD5(sms.Tel)
				log.Content = content
				log.Type = m.Type
				log.Module = app["id"].(int)
				body,_ := gjson.Encode(errorList)
				log.Body = string(body)
				log.Url = sms.Referer
				log.Create_time = int(time.Now().Unix())
				log.Status = status
				log.Gateway = gateway.Id
				log.Sign = m.SmsSign.Id
				log.Template_id = sms.Sms_Id
				//日志对象
				logList = append(logList, log)
				//添加发送日志
				model.NewSmsLogSendModel().Save(logList)
				//开发状态下发送短信机器人通知
				if v.App_env == "dev" || v.App_env == "test" {
					robot := ddrobot.NewRobotService();
					robot.SendMarkdownMessage("短信通知","### 短信通知 \n\n >[发送电话]："+ sms.Tel + "\n\n >[短信内容]：" + content + "\n\n")
				}
				factory.Create().CreateCollector("Sms").ReqAdd("customer_send", strconv.Itoa(app["id"].(int)), strconv.Itoa(m.Type), strconv.Itoa(gateway.Id),sms.Sms_Id)
				i ++
				//如果发送有错误，继续发送
				if errorList["error_code"] != "" {
					factory.Create().CreateCollector("Error").ReqAdd("customer_send",strconv.Itoa(app["id"].(int)), strconv.Itoa(m.Type), strconv.Itoa(gateway.Id),sms.Sms_Id)
					continue
				} else {
					break
				}
			}
		} else {
			break
		}
	}
	f <- 1
}
