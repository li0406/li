package v1

import (
	"admin-api/app/enum"
	"admin-api/app/logic"
	"admin-api/app/model"
	"admin-api/library/util"
	"encoding/json"
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/container/gqueue"
	"github.com/gogf/gf/g/encoding/gjson"
	"github.com/gogf/gf/g/frame/gmvc"
	"strconv"
	"strings"
)

type SendSmsController struct {
	gmvc.Controller
}

type QueueSms struct {
	channel string
	url string
	body  map[string] string
	headers map[string] string
	params map[string] string
}

func (c *SendSmsController)SendSms()  {
	config := g.Config()

	//签名
	signature := c.Request.Get("signature")

	if signature == "" {
		j,_ := util.EncodeResponseJson(4000002,enum.CODE_4000002,"签名为空")
		c.Response.WriteJson(j)
		c.Exit()
	}

	signature_id,_ := strconv.Atoi(signature)
	//获取签名
	var singModel = new(model.SmsSignModel)
	singInfo := singModel.GetById(signature_id)

	//网关
	gateway :=  c.Request.Get("gateway")
	if gateway == "" {
		j,_ := util.EncodeResponseJson(4000002,enum.CODE_4000002,"通道网关为空")
		c.Response.WriteJson(j)
		c.Exit()
	}
	gatewayArr := strings.Split(gateway,",")

	//短信模版参数
	params := c.Request.Get("params")
	paramsArr := strings.Split(params,",")

	//请求头部
	var headers = make(map[string] string)
	headers["Accept"] = "application/json"
	headers["Content-Type"] = "application/json;charset=utf-8"

	//请求参数
	var body = make(map[string] string)
	body["tel"] = c.Request.Get("tel")

	//替换模版变量
	content := c.Request.Get("content")
	if len(paramsArr) > 0 {
		for _,val := range paramsArr {
			content =  strings.Replace(content,"{变量}",val,1)
		}
	}

	var queueList []QueueSms
	for _,val := range gatewayArr{
		url := config.GetString("sms.url")
		switch val {
			case "yuntongxun":
				//云通讯
				body["templateId"] =  c.Request.Get("template_id")
				if len(paramsArr) > 0 {
					s,_ := gjson.Encode(paramsArr)
					body["data"] = string(s)
				}
				url = url + "/v1/ytx/sendsms"
				break
			case "yunrongt":
				//云容正通
				content = "【" + singInfo["name"].(string) + "】" + content
				body["content"] = content
				url = url + "/v1/yrt/sendsms"
				break
			case "yunrongtyx":
				//云容正通
				content = "【" + singInfo["name"].(string) + "】" + content
				body["content"] = content
				url = url + "/v1/yrt/sendsmsyx"
				break
		case "chuanglan":
			content = "【" + singInfo["name"].(string) + "】" + content
			body["content"] = content
			url = url + "/v1/chuanglan/sendsms"
			break
		case "chuanglanyx":
			content = "【" + singInfo["name"].(string) + "】" + content
			body["content"] = content
			url = url + "/v1/chuanglan/sendsmsyx"
			break
		}
		queueSms := QueueSms{
			channel:val,
			url:    url,
			body:   body,
			headers: headers,
		}
		queueList = append(queueList, queueSms )
	}

	q := gqueue.New()

	for _,val := range  queueList{
		q.Push(val)
	}

	len :=  q.Size()
	var resContent = ""
	for i := 0; i < len ; i ++  {
		v := q.Pop()
		if v != nil {
			sub := v.(QueueSms)
			result,_ := util.CurlPost(sub.url,sub.body,sub.params,sub.headers)

			m := make(map[string] interface{})
			json.NewDecoder(result.Body).Decode(&m)
			code,_ :=  m["error_code"].(float64)
			var r_data  map[string] interface{}
			if  m["data"] != nil {
				r_data = m["data"].(map[string] interface{})
			}
			if code == 0 {
				resContent += "【"+sub.channel +"】: OK"
			} else {
				resContent += "【"+sub.channel +"】 ERROR:("+ r_data["error_msg"].(string)+");"
			}
			defer result.Body.Close()
		}
	}

	q.Close()
	var jsonRes []byte
	jsonRes,_ = util.EncodeResponseJson(0,enum.CODE_0,resContent)
	c.Response.WriteJson(jsonRes)
}

func (c *SendSmsController)GetSmsTemplateList() {
	 smsType := c.Request.Get("type")
	 content := c.Request.Get("content")

	 //获取短信模版列表
	tempLogic := new(logic.TemplateLogic)
	 res := tempLogic.GetSmsTemplateList(smsType,content)
	 ret,_ := util.EncodeResponseJson(0,enum.CODE_0,res)

	 c.Request.Response.WriteJson(ret)
}

