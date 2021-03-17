package router

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/util/gconv"
    "qzws/app/controller/ws"
    "qzws/app/model"
    "qzws/library/jwt"
    "qzws/library/util"
    "strings"
    "time"
)

// 统一路由注册.
func init() {
    s := g.Server()
    s.EnablePprof() // 开启服务性能分析
    s.BindHandler("/", func(r *ghttp.Request){
        wsLink, err := r.WebSocket()
        if err == nil {
            json,_ := util.EncodeResponseJson(0, "连接成功", nil)
            _= wsLink.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
        }
    })

    wsController := new(ws.WsController)
    s.Group("/msg").Bind([]ghttp.GroupItem{
        {"ALL", "/*", HookHandler, ghttp.HOOK_BEFORE_SERVE},

        // 管理平台用户Websocket
        {"ALL", "/system", HookSystemHandler, ghttp.HOOK_BEFORE_SERVE},
        {"ALL", "/system", wsController, "System"},

        // 会员公司Websocket
        {"ALL", "/company", HookCompanyHandler, ghttp.HOOK_BEFORE_SERVE},
        {"ALL", "/company", wsController, "Company"},
    })
}

func HookHandler(r *ghttp.Request){
    util.Request(r)
}

func HookSystemHandler(r *ghttp.Request){
    //获取请求的token
    protocol := r.Request.Header.Get("Sec-WebSocket-Protocol")
    model.AdminUser.Exists = 0
    if protocol != "" {
        protocols := strings.Split(protocol, ",")
        token := protocols[0]

        if token == g.Config().GetString("setting.ws_msg_token") {
            // 来自qzmsg内部推送
            model.AdminUser.Exists = 2
        } else if len(protocols) >= 2 {
            res, err := jwt.ParseToken(token)
            if err == true {
                exp := gconv.Int64(res["exp"])
                unixTime := time.Now().Unix()
                if exp >= unixTime {
                    data := gconv.Map(res["data"])
                    model.AdminUser.Logo = gconv.String(data["logo"])
                    model.AdminUser.NickName = gconv.String(data["nick_name"])
                    model.AdminUser.RoleId = gconv.Int64(data["role_id"])
                    model.AdminUser.RoleName = gconv.String(data["role_name"])
                    model.AdminUser.UserId = gconv.Int64(data["user_id"])
                    model.AdminUser.UserName = gconv.String(data["user_name"])
                    model.AdminUser.FromApp = strings.Trim(protocols[1], " ")
                    model.AdminUser.Exists = 1
                }
            }
        }
    }
}

func HookCompanyHandler(r *ghttp.Request){
    //获取请求的token
    protocol := r.Request.Header.Get("Sec-WebSocket-Protocol")
    model.UserCompany.Exists = 0
    if protocol != "" {
        protocols := strings.Split(protocol, ",")
        token := protocols[0]

        if token == g.Config().GetString("setting.ws_msg_token") {
            // 来自qzmsg内部推送
            model.UserCompany.Exists = 2
        } else if len(protocols) >= 2 {
            res, err := jwt.ParseToken(token)
            if err == true {
                exp := gconv.Int64(res["exp"])
                unixTime := time.Now().Unix()
                if exp >= unixTime {
                    data := gconv.Map(res["data"])
                    model.UserCompany.UserId = gconv.Int64(data["user_id"])
                    model.UserCompany.UserType = gconv.Int64(data["user_type"])
                    model.UserCompany.Logo = gconv.String(data["logo"])
                    model.UserCompany.Name = gconv.String(data["name"])
                    model.UserCompany.FullName = gconv.String(data["full_name"])
                    model.UserCompany.ShortName = gconv.String(data["short_name"])
                    model.UserCompany.Telephone = gconv.String(data["telephone"])
                    model.UserCompany.FromApp = strings.Trim(protocols[1], " ")
                    model.UserCompany.Exists = 1
                }
            }
        }
    }
}