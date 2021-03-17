package v1

import (
	"encoding/json"
	"fmt"
	"github.com/gogf/gf/g/frame/gmvc"
	"qzsms/app/enum"
	"qzsms/app/service/yuntongxun"
	"qzsms/library/util"
)

type YuntongxunController struct {
	gmvc.Controller
}

func (c *YuntongxunController)SendSms()  {
	var response []byte
	req := c.Request
	tel := req.Get("tel")
	templateId := req.Get("templateId")
	result := req.Get("data")

	var data []string
	json.Unmarshal([]byte(result),&data)
	res,str := yuntongxun.SendSms(tel,templateId,data)
	fmt.Println(res)
	m := make(map[string] string)
	if res == "000000" {
		m["msg_id"] = str
		response,_ = util.EncodeResponseJson(0,enum.CODE_0,m)
	} else {
		m["error_msg"] = str
		m["error_code"] = res
		response,_ = util.EncodeResponseJson(5000003,enum.CODE_5000003,m)
	}

	c.Request.Response.WriteJson(response)
}