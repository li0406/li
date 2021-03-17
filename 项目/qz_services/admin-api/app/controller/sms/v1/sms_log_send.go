package v1

import (
	"admin-api/app/enum"
	"admin-api/app/logic"
	"admin-api/library/util"
	"github.com/gogf/gf/g/frame/gmvc"
	"strconv"
)

type SmsLogSendController struct {
	gmvc.Controller
}

func (c SmsLogSendController)Getloglist() {
	var l = logic.SmsLogSendLogic{}

	sms := new(logic.SmslogSendParams)
	r,_:= strconv.Atoi(c.Request.Get("module"));
	sms.Module = r
	sms.Sign = c.Request.Get("sign")
	r,_ = strconv.Atoi(c.Request.Get("gateway"))
	sms.Gateway = r
	r,_ = strconv.Atoi(c.Request.Get("status"))
	sms.Status = r
	sms.Date = c.Request.Get("date")
	sms.Tel = c.Request.Get("tel")
	sms.Content = c.Request.Get("content")
	sms.PageIndex,_ = strconv.Atoi(c.Request.Get("page"))
	if sms.PageIndex == 0 {
		sms.PageIndex = 1
	}

	sms.PageCount = 20
	res := l.Getloglist(sms)
	json,_ := util.EncodeResponseJson(0,enum.CODE_0,res)
	c.Response.WriteJson(json)
}