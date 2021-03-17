package v1

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "admin-api/app/model"
    "admin-api/library/util"
    "encoding/json"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/frame/gmvc"
    "github.com/gogf/gf/g/util/gvalid"
)

type MsgInAppController struct {
    gmvc.Controller
}

/**
  APP在线推送消息——列表
*/
func (that *MsgInAppController) MsgInAppList() {
    search := g.MapStrStr{
        "title":    that.Request.GetString("title"), //消息标题
        "pushType": that.Request.GetString("type"),  //1:群发；2:单发
    }

    page := util.PageFormat(that.Request.GetInt("page"), 1)
    limit := util.PageFormat(that.Request.GetInt("limit"), 20)

    // 获取列表
    appinlogic := new(logic.MsgInAppLogic)
    list, count := appinlogic.GetMsgInAppList(search, page, limit)

    //设置返回值
    respData := make(map[string]interface{})
    respData["list"] = list
    respData["page"] = util.GetPage(page, limit, count)
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)

}

func (that *MsgInAppController) MsgInAppSave() {
    input := g.MapStrStr{
        "id":        that.Request.GetString("id"),        // 消息id
        "title":     that.Request.GetString("title"),     //消息标题
        "img":       that.Request.GetString("img"),       //消息图片
        "body":      that.Request.GetString("body"),      //消息内容
        "type_id":   that.Request.GetString("type_id"),   //消息类型。对应qz_msg_type表id
        "push_type": that.Request.GetString("push_type"), //推送方式，默认1，1:群发；2:单发
        "push_time": that.Request.GetString("push_time"), //推送时间
        "app_type":  that.Request.GetString("app_type"),  //应用类型（1:前台系统；2：后台系统）
        "send_type": that.Request.GetString("send_type"), //是否定时  1.定时发送 2.立即发送
        "enabled":   that.Request.GetString("enabled"),   //是否启用 1.启用 2.不启用
    }

    //验证数据
    if err := gvalid.CheckMap(input, logic.MsgInAppSendValidateRules, logic.MsgInAppSendValidateMsgs); err != nil {
        ret, _ := util.EncodeResponseJson(4000002, err.FirstString(), nil)
        _ = that.Request.Response.WriteJson(ret)
        that.Exit()
    }

    msgInAppLogic := new(logic.MsgInAppLogic)

    // 这里加了个限制条件。 已推送的消息无法编辑
    if input["id"] != "" {
        basicInfo := msgInAppLogic.GetOnceMsgInAppBasicInfo(input["id"])
        if basicInfo["push_status"] == 2 {
            ret, _ := util.EncodeResponseJson(4000006, enum.CODE_4000006, nil)
            _ = that.Request.Response.WriteJson(ret)
            that.Exit()
        }
    }

    // link_json参数解析
    var linkMapList []g.Map
    linkJson := that.Request.GetPostString("link_json")
    if linkJson != "" {
        err := json.Unmarshal([]byte(linkJson), &linkMapList)
        if err != nil {
            ret, _ := util.EncodeResponseJson(4000002, "link_json参数格式错误", nil)
            _ = that.Request.Response.WriteJson(ret)
            that.Exit()
        }
    }

    // linkMapList

    //添加/编辑消息类型
    status := msgInAppLogic.SaveMsgAppSend(input, linkMapList)
    if status == true {
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, nil)
        _ = that.Request.Response.WriteJson(ret)
    } else {
        ret, _ := util.EncodeResponseJson(5000002, enum.CODE_5000002, nil)
        _ = that.Request.Response.WriteJson(ret)
    }
    that.Exit()

}

// 获取消息详情
func (that *MsgInAppController) GetMsgDetail() {
    id := that.Request.GetString("id")

    var msgInfo gdb.Map
    if id != "" {
        msgInAppLogic := new(logic.MsgInAppLogic)
        msgInfo = msgInAppLogic.GetMsgDetail(id)
        if len(msgInfo) == 0 {
            ret, _ := util.EncodeResponseJson(4000001, "消息内容不存在", nil)
            _ = that.Response.WriteJson(ret)
            that.Exit()
        }
    }

    // 获取消息类型
    msgTypeModel := model.NewMsgTypeModel()
    typeList,_ := msgTypeModel.GetAll()

    respData := g.Map{
        "detail": msgInfo,
        "type_list": typeList,
    }

    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Response.WriteJson(ret)
    that.Exit()

}
