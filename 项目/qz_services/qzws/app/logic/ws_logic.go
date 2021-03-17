package logic

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/encoding/gjson"
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/util/gconv"
    "github.com/gogf/gf/third/github.com/gorilla/websocket"
    uuid "github.com/satori/go.uuid"
    "net/http"
    "qzws/app/enum"
    "qzws/library/util"
    "strings"
    "sync"
)

type WsLogic struct {
    mutex sync.Mutex
}

// 消息接收结构体
type ReceiveMsg struct {
    MsgType string
    RemindType int
    ToApps string
    Users string
    Data g.Map
}

// 消息发送结构体
type SendMsg struct {
    MsgType string `json:"msg_type"`
    RemindType int `json:"remind_type"`
    Info g.Map `json:"info"`
}

// 客户端结构体
type Client struct {
    Id string
    UserId int64
    Socket *websocket.Conn
    FromApp string
}

// 链接池类型
type WsPool map[string]*Client


// 创建websocket
func (that *WsLogic) CreateService(request *ghttp.Request, response *ghttp.Response) (*websocket.Conn, error) {
    protocol := request.Header.Get("Sec-WebSocket-Protocol")
    protocols := strings.Split(protocol, ",")

    upgrade := &websocket.Upgrader{
        // 检测请求来源
        CheckOrigin: func(r *http.Request) bool {
            return true
        },
        Subprotocols: protocols,
    }

    responseWriter := response.ResponseWriter.ResponseWriter
    ws, err := upgrade.Upgrade(responseWriter, request.Request, nil)
    return ws, err
}

// 加入连接池
func (that *WsLogic) SetLinkPool(wsLinkPool WsPool, ws *websocket.Conn, userId int64, fromApp string){
    that.mutex.Lock()

    guid := uuid.NewV4()
    poolId := gconv.String(guid)

    client := &Client{
        Id: poolId,
        UserId: userId,
        Socket: ws,
        FromApp: fromApp,
    }

    wsLinkPool[poolId] = client

    // 加入连接池后发送一条成功消息
    sendMsg := &SendMsg{}
    sendMsg.MsgType = "test"
    sendMsg.RemindType = 99
    sendMsg.Info = g.Map{
        "title": "",
        "notice": "websocket连接成功",
    }

    json,_ := util.EncodeResponseJson(0, enum.CODE_0, sendMsg)
    _  = ws.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))

    that.mutex.Unlock()
}

// 消息推送
func (that *WsLogic) SendMessage(msg []byte, wsLinkPool WsPool){
    that.mutex.Lock()

    // 参数接收
    receiveMsg := &ReceiveMsg{}
    if err := gjson.DecodeTo(msg, receiveMsg); err == nil {
        users := strings.Split(receiveMsg.Users, ",")
        toApps := strings.Split(receiveMsg.ToApps, ",")

        for _,item := range wsLinkPool {
            appExist := util.StrInSlice(gconv.String(item.FromApp), toApps)
            userExist := util.StrInSlice(gconv.String(item.UserId), users)

            if appExist && userExist {
                sendMsg := &SendMsg{}
                sendMsg.MsgType = receiveMsg.MsgType
                sendMsg.RemindType = receiveMsg.RemindType
                sendMsg.Info = receiveMsg.Data

                json,_ := util.EncodeResponseJson(0, enum.CODE_0, sendMsg)
                err := item.Socket.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
                if err != nil { // 客户端关闭
                    that.CloseHandler(item.Socket, wsLinkPool)
                    _ = item.Socket.Close()
                }
            }
        }
    }

    that.mutex.Unlock()
}

// 客户端关闭后操作
func (that *WsLogic) CloseHandler(ws *websocket.Conn, wsLinkPool WsPool) {
    for _,item := range wsLinkPool {
        if item.Socket == ws {
            delete(wsLinkPool, item.Id)
        }
    }
}