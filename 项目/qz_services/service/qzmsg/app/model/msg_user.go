package model

import (
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgUserModel struct {

}

func NewMsgUserModel() *MsgUserModel {
    return &MsgUserModel{}
}

// 表名
func (that *MsgUserModel) table() string {
    return "qz_msg_user"
}

// 配置数据表（带别名）
func (that *MsgUserModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 添加站内信
func (that *MsgUserModel) AddRecord(data gdb.Map) (int64, error) {
    data["created_at"] = time.Now().Unix()
    data["updated_at"] = time.Now().Unix()

    ret,_ := db.Table(that.table()).Data(data).Insert()
    return ret.LastInsertId()
}

// 添加关联关系
func (that *MsgUserModel) AddRelated(msg_id int64, user_id int) (int64, error) {
    data := gdb.Map{
        "msg_id": msg_id,
        "user_id": user_id,
        "is_read": 2,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }
    ret,_ := db.Table("qz_msg_user_related").Data(data).Insert()
    return ret.LastInsertId()
}

