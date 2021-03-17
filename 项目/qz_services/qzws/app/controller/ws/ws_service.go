package ws

import (
	"github.com/gogf/gf/g/encoding/gjson"
	"github.com/gogf/gf/g/frame/gmvc"
	"github.com/gogf/gf/g/net/ghttp"
	"github.com/gogf/gf/g/os/glog"
	"github.com/gogf/gf/g/util/gconv"
	"github.com/gogf/gf/third/github.com/gorilla/websocket"
	uuid "github.com/satori/go.uuid"
	"net/http"
	"qzws/app/enum"
	"qzws/app/model"
	"qzws/library/util"
	"strings"
)

type WsServiceController struct {
	gmvc.Controller
	upgrade *websocket.Upgrader
}

// 消息接收结构体
type ReceiveMsg struct {
	Type  string
	Users string
	Data  interface{}
}

type SendMsg struct {
	Type string      `json:"type"`
	Info interface{} `json:"info"`
}

// 客户端结构体
type Client struct {
	Id     string
	UserId int64
	Socket *websocket.Conn
	Send   chan []byte
}

// 连接池
var wsLinkPool = make(map[string]*Client)

func (that *WsServiceController) System() {
	receiveMsg := &ReceiveMsg{}

	that.upgrade = &websocket.Upgrader{
		// 检测请求来源
		CheckOrigin: func(r *http.Request) bool {
			return true
		},
		Subprotocols: []string{that.Request.Header.Get("Sec-WebSocket-Protocol")},
	}

	responseWriter := that.Response.ResponseWriter.ResponseWriter
	ws, err := that.upgrade.Upgrade(responseWriter, that.Request.Request, nil)
	if err != nil {
		glog.Error(err)
		that.Exit()
	} else {
		// 加入连接池
		that.setLinkPool(ws)
	}

	defer ws.Close()

	for {
		_, msg, err := ws.ReadMessage()
		if err != nil { // 取消息有错误发生
			// 清理连接池
			that.closeHandler(ws)
			glog.Errorf("取消息有错误发生,错误为:%s", err.Error())
			break
		}

		// 参数接收
		if err := gjson.DecodeTo(msg, receiveMsg); err != nil {
			glog.Errorf("收到了消息但是格式错误,消息为:%s", msg)
			continue
		}

		users := strings.Split(receiveMsg.Users, ",")

		for _, uid := range users {
			for _, item := range wsLinkPool {
				if item.UserId == gconv.Int64(uid) {
					sendMsg := &SendMsg{}
					sendMsg.Type = receiveMsg.Type
					sendMsg.Info = receiveMsg.Data

					json, _ := util.EncodeResponseJson(0, enum.CODE_0, sendMsg)
					err := item.Socket.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
					if err != nil { // 客户端关闭
						that.closeHandler(item.Socket)
						glog.Errorf("发送消息有错误发生,错误为:%s", err.Error())
						break
					}
				}
			}
		}
	}
}

// 设置连接池
func (that *WsServiceController) setLinkPool(ws *websocket.Conn) {
	if model.AdminUser.Exists > 0 {
		if model.AdminUser.Exists == 1 {
			guid := uuid.NewV4()
			poolId := gconv.String(guid)

			client := &Client{
				Id:     poolId,
				UserId: model.AdminUser.UserId,
				Socket: ws,
				Send:   make(chan []byte),
			}

			wsLinkPool[poolId] = client

			// 加入连接池后发送一条成功消息
			json, _ := util.EncodeResponseJson(0, "连接成功", nil)
			_ = ws.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
		} else {
			json, _ := util.EncodeResponseJson(3000002, enum.CODE_3000002, nil)
			_ = ws.WriteMessage(ghttp.WS_MSG_TEXT, gconv.Bytes(json))
			_ = ws.Close()
		}
	}
}

// 客户端关闭后操作
func (that *WsServiceController) closeHandler(ws *websocket.Conn) {
	for _, item := range wsLinkPool {
		if item.Socket == ws {
			delete(wsLinkPool, item.Id)
		}
	}
}
