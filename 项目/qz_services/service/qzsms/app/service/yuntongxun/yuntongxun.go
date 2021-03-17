package yuntongxun

import (
	"encoding/base64"
	"encoding/json"
	"github.com/gogf/gf/g/encoding/gjson"
	"qzsms/app/model"
	"qzsms/library/util"
	"strings"
	"time"
)

var (
	config Config
)

type Config struct {
	Yuntongxun Yuntongxun
}

type Yuntongxun struct {
	Url  string
	Port string
	AccountSid string
	AppId string
	AccountToken string
	SoftVersion string
}

type Response struct {
	StatusCode string `json:"statusCode"`
	TemplateSMS TemplateSms `templateSMS`
	StatusMsg string `statusMsg`
}

type TemplateSms struct {
	DateCreated string `json:"dateCreated"`
	SmsMessageSid string `json:"smsMessageSid"`
}

func init()  {
	m := model.NewSmsGatewayModel()
	res,err := m.GetGatewayInfo("yuntongxun")

	if  err != nil || len(res) == 0{
		return
	}

	gjson.DecodeTo(res["config"],&config)
}

func SendSms(tel string,templateId string,maps []string)  (code string,msg string) {

	if config.Yuntongxun.AppId == "" {
		return "-1","缺失帐号参数"
	}

	url := config.Yuntongxun.Url
	url = url + ":" + config.Yuntongxun.Port + "/" + config.Yuntongxun.SoftVersion + "/Accounts/" + config.Yuntongxun.AccountSid + "/SMS/TemplateSMS?sig="
	unixtimestamp := time.Now().Format("20060102150405")

	signature :=  strings.ToUpper(util.MD5(config.Yuntongxun.AccountSid + config.Yuntongxun.AccountToken + unixtimestamp))
	url = url + signature
	input := []byte(config.Yuntongxun.AccountSid + ":" + unixtimestamp)
	authorization := base64.StdEncoding.EncodeToString(input)

	//请求头部
	var headers = make(map[string] string)
	headers["Accept"] = "application/json"
	headers["Content-Type"] = "application/json;charset=utf-8"
	headers["Authorization"] = authorization

	//请求参数
	var body = make(map[string] string)
	body["to"] = tel
	body["appId"] = config.Yuntongxun.AppId
	body["templateId"] = templateId

	if len(maps) > 0 {
		r,_ := json.Marshal(maps)
		body["datas"] = string(r)
	}

	result,err := util.CurlPost(url,body,nil,headers)
	if err == nil {
		response := Response{}
		json.NewDecoder(result.Body).Decode(&response)
		if response.StatusCode != "000000" {
			return response.StatusCode,response.StatusMsg
		}
		return response.StatusCode,response.TemplateSMS.SmsMessageSid
	}
	result.Body.Close()
	return "",""
}