package model

import (
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgSystemModel struct {

}

func NewMsgSystemModel() *MsgSystemModel {
    return &MsgSystemModel{}
}

// 表名
func (that *MsgSystemModel) table() string {
    return "qz_msg_system"
}

// 配置数据表（带别名）
func (that *MsgSystemModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 添加站内信
func (that *MsgSystemModel) AddRecord(data gdb.Map) (int64, error) {
    data["created_at"] = time.Now().Unix()
    data["updated_at"] = time.Now().Unix()

    ret,_ := db.Table(that.table()).Data(data).Insert()
    return ret.LastInsertId()
}

// 添加关联关系
func (that *MsgSystemModel) AddRelated(msg_id int64, user_id int) (int64, error) {
    data := gdb.Map{
        "msg_id": msg_id,
        "user_id": user_id,
        "is_read": 2,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }
    ret,_ := db.Table("qz_msg_system_related").Data(data).Insert()
    return ret.LastInsertId()
}

