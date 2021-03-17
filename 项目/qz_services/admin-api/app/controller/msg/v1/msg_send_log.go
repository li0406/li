package v1

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
)

type MsgSendLogController struct {
    gmvc.Controller
}

func (that *MsgSendLogController) MsgSendLogList() {
    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 20)

    input := g.MapStrStr{
        "keyword": that.Request.GetString("keyword"),
        "from_app": that.Request.GetString("from_app"),
        "send_type": that.Request.GetString("send_type"),
        "push_time": that.Request.GetString("push_time"),
    }

    // 请求数据
    msgLogLogic := logic.NewMsgSendLogLogic()
    list,count := msgLogLogic.GetMsgSendLogList(input, page, limit)

    // 处理返回结果
    respData := g.Map{
        "list": list,
        "page": util.GetPage(page, limit, count),
    }

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}