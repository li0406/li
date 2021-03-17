package model

import (
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgSendLogModel struct {

}

func NewMsgSendLogModel() *MsgSendLogModel {
    return &MsgSendLogModel{}
}

// 表名
func (that *MsgSendLogModel) table() string {
    return "qz_msg_send_log"
}

// 配置数据表（带别名）
func (that *MsgSendLogModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 添加记录
func (that *MsgSendLogModel) AddLog (data gdb.Map) error {
    data["created_at"] = time.Now().Unix()
    data["updated_at"] = time.Now().Unix()

    _,err := db.Table(that.table()).Data(data).Insert()
    return err
}