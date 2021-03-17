package ctl_default

import (
	"github.com/gogf/gf/g/frame/gmvc"
	"qzsms/library/util"
)

type DefaultController struct {
	gmvc.Controller
}

//  空方法
func (c *DefaultController) Empty()  {
	//lib_response.Json(r, -1, "err 404")
	json,_ := util.EncodeResponseJson(404,"无法访问该地址",nil)
	c.Response.WriteJson(json)
}