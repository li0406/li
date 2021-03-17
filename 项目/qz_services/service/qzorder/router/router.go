package router

import (
    "github.com/gogf/gf/frame/g"
    "github.com/gogf/gf/net/ghttp"
    v1 "qzorder/app/controller/v1"
    "qzorder/library/util"
)

// 统一路由注册.
func init() {
    s := g.Server()
    s.BindHandler("/", func(r *ghttp.Request){
        json,err := util.EncodeResponseJson(0,"请求成功",nil)
        if err == nil {
            r.Response.WriteJson(json)
        }
    })

    //v1版本
    recallController := new(v1.RecallController)
    s.Group("/v1").Bind([]ghttp.GroupItem{
        {"ALL", "*", HookHandler, ghttp.HOOK_BEFORE_SERVE},
        
        {"POST",  "order/recall", recallController, "SetOrderRecall"}, // 订单撤回
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
