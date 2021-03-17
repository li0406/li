package ws

import (
    "github.com/gogf/gf/g/frame/gmvc"
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/os/glog"
    "github.com/gogf/gf/g/util/gconv"
    "qzws/app/enum"
    "qzws/app/logic"
    "qzws/app/model"
    "qzws/library/util"
)

type WsController struct {
    gmvc.Controller
}

// 连接池
var wsSystemLinkPool = make(logic.WsPool)
var wsCompanyLinkPool = make(logic.WsPool)

var wsLogic = new(logic.WsLogic)

// 后台用户websocket
func (that *WsController) System () {
    ws,err := wsLogic.CreateService(that.Request, that.Response)
    if err != nil {
        glog.Error(err)
        that.Exit()
    }

    if model.AdminUser.Exists == 1 {
        go wsLogic.SetLinkPool(wsSystemLinkPool, ws, model.AdminUser.UserId, model.AdminUser.FromApp) // 加入连接池
    } else if model.AdminUser.Exists == 0 {
        // token验证失败
        json,_ := util.EncodeResponseJson(3000002, enum.CODE_3000002,nil)
        _  = ws.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
        _ = ws.Close()
    }

    for {
       _, msg, err := ws.ReadMessage()
       if err != nil { // 客户端关闭
           go wsLogic.CloseHandler(ws, wsSystemLinkPool)
           return
       }

       // 向用户推送消息
       go wsLogic.SendMessage(msg, wsSystemLinkPool)
    }
}

// 前台用户websocket
func (that *WsController) Company () {
    ws,err := wsLogic.CreateService(that.Request, that.Response)
    if err != nil {
        glog.Error(err)
        that.Exit()
    }

    if model.UserCompany.Exists == 1 {
        go wsLogic.SetLinkPool(wsCompanyLinkPool, ws, model.UserCompany.UserId, model.UserCompany.FromApp) // 加入连接池
    } else if model.UserCompany.Exists == 0 {
        // token验证失败
        json,_ := util.EncodeResponseJson(3000002, enum.CODE_3000002,nil)
        _  = ws.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
        _ = ws.Close()
    }

    for {
        _, msg, err := ws.ReadMessage()
        if err != nil { // 客户端关闭
            go wsLogic.CloseHandler(ws, wsCompanyLinkPool)
            _ = ws.Close()
            return
        }

        // 向用户推送消息
        go wsLogic.SendMessage(msg, wsCompanyLinkPool)
    }
}


