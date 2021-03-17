package service

import (
    "flag"
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/encoding/gjson"
    "github.com/gogf/gf/g/util/gconv"
    "github.com/gogf/gf/third/github.com/gorilla/websocket"
    "net/http"
    "net/url"
)

type SendService struct {

}

// 消息发送格式
type SendMsg struct {
    MsgType string
    RemindType int
    ToApps string
    Users string
    Data g.Map
}

func NewSendService() *SendService {
    return &SendService{}
}

var wsHost = g.Config().GetString("setting.ws_host")
var wsMsgToken = g.Config().GetString("setting.ws_msg_token")

var addr = flag.String("addr", wsHost, "")

// 通知用户
func (that *SendService) NoticeSystemUser (data g.Map) {
    // 建立websocket连接
    u := url.URL{Scheme: "ws", Host: *addr, Path: "/msg/system"}
    rHeader := http.Header {
        "Sec-WebSocket-Protocol": []string{wsMsgToken},
    }

    c, _, err := websocket.DefaultDialer.Dial(u.String(), rHeader)
    if err != nil {
        fmt.Println("websocket连接失败", err)
        return
    }

    // 构建消息发送结构体
    sendMsg := &SendMsg{}
    sendMsg.MsgType = gconv.String(data["type_slot"])
    sendMsg.RemindType = gconv.Int(data["remind_type"])
    sendMsg.ToApps = gconv.String(data["type_apps"])
    sendMsg.Users = gconv.String(data["userIds"])
    sendMsg.Data = g.Map{
        "title": data["title"],
        "notice": data["notice"],
        "template_id": data["template_id"],
        "send_group": data["send_group"],
    }

    // 发送消息
    ret,_ := gjson.Encode(sendMsg)
    err = c.WriteMessage(websocket.TextMessage, gconv.Bytes(ret))
    if err != nil {
        fmt.Println("发送失败")
    }

    // 关闭websocket连接
    _ = c.Close()
}

func (that *SendService) NoticeCompanyUser (data g.Map) {
    // 建立websocket连接
    u := url.URL{Scheme: "ws", Host: *addr, Path: "/msg/company"}
    rHeader := http.Header {
        "Sec-WebSocket-Protocol": []string{wsMsgToken},
    }
    c, _, err := websocket.DefaultDialer.Dial(u.String(), rHeader)
    if err != nil {
        fmt.Println("websocket连接失败:", err)
        return
    }

    // 构建消息发送结构体
    sendMsg := &SendMsg{}
    sendMsg.MsgType = gconv.String(data["type_slot"])
    sendMsg.RemindType = gconv.Int(data["remind_type"])
    sendMsg.ToApps = gconv.String(data["type_apps"])
    sendMsg.Users = gconv.String(data["userIds"])
    sendMsg.Data = g.Map{
        "title": data["title"],
        "notice": data["notice"],
        "template_id": data["template_id"],
        "send_group": data["send_group"],
    }

    // 发送消息
    ret,_ := gjson.Encode(sendMsg)
    err = c.WriteMessage(websocket.TextMessage, gconv.Bytes(ret))
    if err != nil {
        fmt.Println("发送失败")
    }

    // 关闭websocket连接
    _ = c.Close()
}
