package ctl_default

import (
    "fmt"
    "github.com/gogf/gf/g/frame/gmvc"
    "qzmsg/app/logic"
    "qzmsg/library/util"
)

type DefaultController struct {
    gmvc.Controller
}

//  空方法
func (c *DefaultController) Empty()  {
    //lib_response.Json(r, -1, "err 404")
    json,_ := util.EncodeResponseJson(404,"无法访问该地址",nil)
    _ = c.Response.WriteJson(json)
}

func (c *DefaultController) Example()  {
    driver := logic.WechatAppletDriver()
    access_token,_ := driver.GetAccessToken(false)
    fmt.Println(access_token)
}
