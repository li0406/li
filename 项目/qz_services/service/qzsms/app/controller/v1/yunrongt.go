package v1

import (
	"github.com/gogf/gf/g/frame/gmvc"
	"qzsms/app/enum"
	yrt "qzsms/app/service/yunorngt"
	yrtyx "qzsms/app/service/yunrongtyx"
	"qzsms/library/util"
	"strconv"
)

type YunrongtController struct {
	gmvc.Controller
}

func (c *YunrongtController)SendSms()  {
	var json []byte
	gjson := c.Request
	tel := gjson.Get("tel")
	content :=  gjson.Get("content")
	res,str := yrt.SendSms(tel,content)
	m := make(map[string] string)
	code,_ := strconv.Atoi(res)
	if code > 0 {
		m["msg_id"] = string(res)
		json,_ = util.EncodeResponseJson(0,enum.CODE_0,m)
	} else {
		m["error_msg"] = str
		m["error_code"] = string(res)
		json,_ = util.EncodeResponseJson(5000003,enum.CODE_5000003,m)
	}
	c.Request.Response.WriteJson(json)
}

func (c *YunrongtController)SendSmsYx()  {
	var json []byte
	gjson := c.Request
	tel := gjson.Get("tel")
	content :=  gjson.Get("content")
	res,str := yrtyx.SendSms(tel,content)
	m := make(map[string] string)
	code,_ := strconv.Atoi(res)
	if code > 0 {
		m["msg_id"] = string(res)
		json,_ = util.EncodeResponseJson(0,enum.CODE_0,m)
	} else {
		m["error_msg"] = str
		m["error_code"] = string(res)
		json,_ = util.EncodeResponseJson(5000003,enum.CODE_5000003,m)
	}
	c.Request.Response.WriteJson(json)
}

