package model

import (
    "github.com/gogf/gf/g/database/gdb"
)

type MsgToAppLogModel struct {
}

func NewMsgToAppLogModel() *MsgToAppLogModel {
    return &MsgToAppLogModel{}
}

func (that *MsgToAppLogModel) table() string {
    return "qz_msg_to_app_log"
}

// 配置数据表（带别名）
func (that *MsgToAppLogModel) connect() *gdb.Model {
    return db.Table(that.table() + " as a")
}

// 统计满足条件的数量
func (that *MsgToAppLogModel) MsgToAppSendCount(msgId string) (int, error) {
    dbQuery := that.connect()
    dbQuery.Where("a.msg_id=?", msgId)
    return dbQuery.Count()
}