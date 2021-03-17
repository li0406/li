package logic

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "qzmsg/app/model"
)

type SendLogLogic struct {

}

func NewSendLogLogic() *SendLogLogic {
    return &SendLogLogic{}
}

func (that SendLogLogic) AddLog(platform string, data g.Map){
    // 生成发送记录
    logData := gdb.Map{
        "employees": "",
        "users": data["userIds"],
        "type_id": data["type_id"],
        "from_app": data["from_app"],
        "content": data["notice"],
        "operator": data["operator"],
        "send_type": data["send_type"],
        "template_id": data["template_id"],
        "send_position": data["send_position"],
        "msg_platform": platform,
    }

    if _,ok := data["employeeIds"]; ok {
        logData["employees"] = data["employeeIds"]
    }

    msgSendLogModel := model.NewMsgSendLogModel()
    _ = msgSendLogModel.AddLog(logData)
}