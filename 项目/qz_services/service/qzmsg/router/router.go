package router

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/net/ghttp"
    v1 "qzmsg/app/controller/v1"
    "qzmsg/library/util"
)

// 统一路由注册.
func init() {
    s := g.Server()
    s.BindHandler("/", func(r *ghttp.Request){
        json,err := util.EncodeResponseJson(0,"请求成功",nil)
        if err == nil {
            r.Response.Writeln(json)
        }
    })

    //v1版本
    msgController := new(v1.MsgController)
    s.Group("/v1").Bind([]ghttp.GroupItem{
        {"ALL", "*", HookHandler, ghttp.HOOK_BEFORE_SERVE},
        // 消息发送
        {"POST",  "/send/system_msg", msgController, "SendSystemMsg"}, //消息发送路由
        {"POST",  "/send/company_msg", msgController, "SendCompanyMsg"}, //消息发送路由
        {"POST",  "/send/user_msg", msgController, "SendUserMsg"}, //消息发送路由
    })

    // 404
    s.BindHandler("/*any", func(r *ghttp.Request) {
        ret, err := util.EncodeResponseJson(404, "not found", nil)
        if err == nil {
            _ = r.Response.WriteJson(ret)
            r.ExitAll()
        }
    })
}

func HookHandler(r *ghttp.Request) {
    // 设置统一请求参数
    util.Request(r)
}
