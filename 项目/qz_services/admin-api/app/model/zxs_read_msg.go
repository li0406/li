package model

import (
    "github.com/gogf/gf/g/database/gdb"
)

type ZxsReadMsgModel struct {
}

func NewZxsReadMsgModel() *ZxsReadMsgModel {
    return &ZxsReadMsgModel{}
}

func (that *ZxsReadMsgModel) table() string {
    return "qz_zxs_read_msg"
}

// 配置数据表（带别名）
func (that *ZxsReadMsgModel) connect() *gdb.Model {
    return db.Table(that.table() + " as a")
}

// 统计满足条件的数量
func (that *ZxsReadMsgModel) MsgInAppReadCount(msgId string) (int, error) {
    dbQuery := that.connect()
    dbQuery.Where("a.msg_id=?", msgId)
    //dbQuery.OrderBy("a.created_at desc")
    return dbQuery.Count()
}