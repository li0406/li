package logic

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/encoding/gjson"
    "github.com/gogf/gf/g/util/gconv"
    uuid "github.com/satori/go.uuid"
    "qzmsg/app/model"
    "qzmsg/library/util"
    "strings"
    "time"
)

type MsgLogic struct {

}

// 消息发送参数
type MsgSendParam struct {
    TemplateId string `gvalid:"template_id@required#消息模板ID不能为空"`
    Users string `gvalid:"users@required#接收人不能为空"`
    FromApp string `gvalid:"from_app@required#项目来源不能为空"`
    Employees string
    ActionId string
    LinkParam string
    Operator int
    SendPosition string
    ReplaceParams string
}

func NewMsgLogic() *MsgLogic {
    return &MsgLogic{}
}

func (that *MsgLogic) replaceMsgContent(replace_params string, msgContent string, msgNotice string) (string, string) {
    var paramsArr []string
    _ = gjson.DecodeTo(replace_params, &paramsArr)

    //如果是字符串，英文逗号切割
    if len(paramsArr) == 0 {
        paramsArr = strings.Split(replace_params,",")
    }

    if len(paramsArr) > 0 {
        for _,val := range paramsArr {
            msgContent =  strings.Replace(msgContent,"{变量}",val,1)
            msgNotice =  strings.Replace(msgNotice,"{变量}",val,1)
        }
    }

    return msgContent, msgNotice
}

func (that *MsgLogic) CreatedSystemMsg(input *MsgSendParam) g.Map {
    // 获取消息模板信息
    msgTemplateModel := model.NewMsgTemplateModel()
    tempInfo := msgTemplateModel.GetById(input.TemplateId)

    // 模板消息内容替换
    msgNotice := gconv.String(tempInfo["notice"])
    msgContent := gconv.String(tempInfo["content"])
    msgContent, msgNotice = that.replaceMsgContent(input.ReplaceParams, msgContent, msgNotice)

    // 接收人
    userIds := strings.Split(input.Users, ",")

    // guid
    guid := uuid.NewV4()
    sendGroup := gconv.String(guid)

    data := gdb.Map{
        "user_id": "0",
        "type_id": tempInfo["type_id"],
        "title": tempInfo["title"],
        "notice": msgNotice,
        "content": msgContent,
        "template_id": tempInfo["id"],
        "action_id": input.ActionId,
        "link_param": input.LinkParam,
        "operator": input.Operator,
        "send_group": sendGroup,
    }

    msgSystemModel := model.NewMsgSystemModel()

    var successUserIds g.SliceStr
    if tempInfo["send_type"] == 1 {
        // 单发（每一个接收人都有一条对应消息）
        for _,user_id := range userIds {
            data["user_id"] = user_id
            msg_id,err := msgSystemModel.AddRecord(data)
            if err == nil {
                _,_ = msgSystemModel.AddRelated(msg_id, gconv.Int(user_id))
                successUserIds = append(successUserIds, user_id)
            }
        }
    } else {
        // 多发（多人共用一个消息）
        msg_id,err := msgSystemModel.AddRecord(data)
        if err == nil {
            for _,user_id := range userIds {
                _,err = msgSystemModel.AddRelated(msg_id, gconv.Int(user_id))
                if err == nil {
                    successUserIds = append(successUserIds, user_id)
                }
            }
        }
    }

    // 构建返回结构
    response := g.Map{
        "userIds": strings.Join(successUserIds, ","),
        "title": tempInfo["title"],
        "type_id": tempInfo["type_id"],
        "type_slot": tempInfo["type_slot"],
        "type_apps": tempInfo["type_apps"],
        "remind_type": tempInfo["remind_type"],
        "notice": msgNotice,
        "template_id": tempInfo["id"],
        "send_type": tempInfo["send_type"],
        "send_position": input.SendPosition,
        "operator": input.Operator,
        "from_app": input.FromApp,
        "send_group": sendGroup,
    }

    return response
}

func (that *MsgLogic) CreatedCompanyMsg (input *MsgSendParam) (response g.Map) {
    // 获取消息模板信息
    msgTemplateModel := model.NewMsgTemplateModel()
    tempInfo := msgTemplateModel.GetById(input.TemplateId)

    // 模板消息内容替换
    msgNotice := gconv.String(tempInfo["notice"])
    msgContent := gconv.String(tempInfo["content"])
    msgContent, msgNotice = that.replaceMsgContent(input.ReplaceParams, msgContent, msgNotice)

    // 接收人
    userIds := strings.Split(input.Users, ",")

    // guid
    guid := uuid.NewV4()
    sendGroup := gconv.String(guid)

    data := gdb.Map{
        "type_id": tempInfo["type_id"],
        "title": tempInfo["title"],
        "notice": msgNotice,
        "content": msgContent,
        "template_id": tempInfo["id"],
        "action_id": input.ActionId,
        "link_param": input.LinkParam,
        "operator": input.Operator,
        "send_group": sendGroup,
    }

    msgCompanyModel := model.NewMsgCompanyModel()

    var successUserIds g.SliceStr
    if tempInfo["send_type"] == 1 {
        // 单发（每一个接收人都有一条对应消息）
        for _,userId := range userIds {
            if userId != "" && userId != "0" {
                msgId, err := msgCompanyModel.AddRecord(data)
                if err == nil {
                    _, _ = msgCompanyModel.AddRelated(msgId, gconv.Int(userId), 0)
                    successUserIds = append(successUserIds, userId)
                }
            }
        }
    } else {
        // 多发（多人共用一个消息）
        msgId,err := msgCompanyModel.AddRecord(data)
        if err == nil {
            for _,userId := range userIds {
                if userId != "" && userId != "0" {
                    _, err = msgCompanyModel.AddRelated(msgId, gconv.Int(userId), 0)
                    if err == nil {
                        successUserIds = append(successUserIds, userId)
                    }
                }
            }
        }
    }

    // 构建返回结构
    response = g.Map{
        "userIds": strings.Join(successUserIds, ","),
        "type_id": tempInfo["type_id"],
        "type_slot": tempInfo["type_slot"],
        "type_apps": tempInfo["type_apps"],
        "remind_type": tempInfo["remind_type"],
        "title": tempInfo["title"],
        "notice": msgNotice,
        "template_id": tempInfo["id"],
        "send_type": tempInfo["send_type"],
        "send_position": input.SendPosition,
        "datetime": util.TimeToDate(time.Now().Unix(), 19),
        "operator": input.Operator,
        "from_app": input.FromApp,
        "send_group": sendGroup,
    }

    return response
}

// 发送员工消息
func (that *MsgLogic) CreatedCompanyEmployeeMsg (input *MsgSendParam) (response g.Map) {
    // 获取消息模板信息
    msgTemplateModel := model.NewMsgTemplateModel()
    tempInfo := msgTemplateModel.GetById(input.TemplateId)

    // 模板消息内容替换
    msgNotice := gconv.String(tempInfo["notice"])
    msgContent := gconv.String(tempInfo["content"])
    msgContent, msgNotice = that.replaceMsgContent(input.ReplaceParams, msgContent, msgNotice)

    // 接收员工
    employeeIds := strings.Split(input.Employees, ",")
    userId := gconv.Int(input.Users)

    // guid
    guid := uuid.NewV4()
    sendGroup := gconv.String(guid)

    data := gdb.Map{
        "type_id": tempInfo["type_id"],
        "title": tempInfo["title"],
        "notice": msgNotice,
        "content": msgContent,
        "template_id": tempInfo["id"],
        "action_id": input.ActionId,
        "link_param": input.LinkParam,
        "operator": input.Operator,
        "send_group": sendGroup,
    }

    msgCompanyModel := model.NewMsgCompanyModel()

    var successEmployeeIds g.SliceStr
    if tempInfo["send_type"] == 1 {
        // 单发（每一个接收人都有一条对应消息）
        for _,employeeId := range employeeIds {
            if employeeId != "" && employeeId != "0" {
                msgId,err := msgCompanyModel.AddRecord(data)
                if err == nil {
                    _,_ = msgCompanyModel.AddRelated(msgId, userId, gconv.Int(employeeId))
                    successEmployeeIds = append(successEmployeeIds, employeeId)
                }
            }
        }
    } else {
        // 多发（多人共用一个消息）
        msgId,err := msgCompanyModel.AddRecord(data)
        if err == nil {
            for _,employeeId := range employeeIds {
                if employeeId != "" && employeeId != "0" {
                    _, err = msgCompanyModel.AddRelated(msgId, userId, gconv.Int(employeeId))
                    if err == nil {
                        successEmployeeIds = append(successEmployeeIds, employeeId)
                    }
                }
            }
        }
    }

    // 构建返回结构
    response = g.Map{
        "userIds": userId,
        "employeeIds": strings.Join(successEmployeeIds, ","),
        "type_id": tempInfo["type_id"],
        "type_slot": tempInfo["type_slot"],
        "type_apps": tempInfo["type_apps"],
        "remind_type": tempInfo["remind_type"],
        "title": tempInfo["title"],
        "notice": msgNotice,
        "template_id": tempInfo["id"],
        "send_type": tempInfo["send_type"],
        "send_position": input.SendPosition,
        "datetime": util.TimeToDate(time.Now().Unix(), 19),
        "operator": input.Operator,
        "from_app": input.FromApp,
        "send_group": sendGroup,
    }

    return response
}


func (that *MsgLogic) CreatedUserMsg (input *MsgSendParam) (response g.Map) {
    // 获取消息模板信息
    msgTemplateModel := model.NewMsgTemplateModel()
    tempInfo := msgTemplateModel.GetById(input.TemplateId)

    // 接收人
    userIds := strings.Split(input.Users, ",")

    // guid
    guid := uuid.NewV4()
    sendGroup := gconv.String(guid)

    data := gdb.Map{
        "user_id": "0",
        "type_id": tempInfo["type_id"],
        "title": tempInfo["title"],
        "notice": tempInfo["notice"],
        "content": tempInfo["content"],
        "template_id": tempInfo["id"],
        "action_id": input.ActionId,
        "link_param": input.LinkParam,
        "operator": input.Operator,
        "send_group": sendGroup,
    }

    msgUserModel := model.NewMsgUserModel()

    var successUserIds g.SliceStr
    if tempInfo["send_type"] == 1 {
        // 单发（每一个接收人都有一条对应消息）
        for _,user_id := range userIds {
            data["user_id"] = user_id
            msg_id,err := msgUserModel.AddRecord(data)
            if err == nil {
                _,_ = msgUserModel.AddRelated(msg_id, gconv.Int(user_id))
                successUserIds = append(successUserIds, user_id)
            }
        }
    } else {
        // 多发（多人共用一个消息）
        msg_id,err := msgUserModel.AddRecord(data)
        if err == nil {
            for _,user_id := range userIds {
                _,err = msgUserModel.AddRelated(msg_id, gconv.Int(user_id))
                if err == nil {
                    successUserIds = append(successUserIds, user_id)
                }
            }
        }
    }

    // 构建返回结构
    response = g.Map{
        "userIds": strings.Join(successUserIds, ","),
        "type_id": tempInfo["type_id"],
        "type_slot": tempInfo["type_slot"],
        "type_apps": tempInfo["type_apps"],
        "remind_type": tempInfo["remind_type"],
        "title": tempInfo["title"],
        "notice": tempInfo["notice"],
        "template_id": tempInfo["id"],
        "send_type": tempInfo["send_type"],
        "send_position": input.SendPosition,
        "datetime": util.TimeToDate(time.Now().Unix(), 19),
        "operator": input.Operator,
        "from_app": input.FromApp,
        "send_group": sendGroup,
    }

    return response
}
