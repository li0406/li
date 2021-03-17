package model

import (
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgCompanyModel struct {

}

func NewMsgCompanyModel() *MsgCompanyModel {
    return &MsgCompanyModel{}
}

// 表名
func (that *MsgCompanyModel) table() string {
    return "qz_msg_company"
}

// 配置数据表（带别名）
func (that *MsgCompanyModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 添加站内信
func (that *MsgCompanyModel) AddRecord(data gdb.Map) (int64, error) {
    data["created_at"] = time.Now().Unix()
    data["updated_at"] = time.Now().Unix()

    ret,_ := db.Table(that.table()).Data(data).Insert()
    return ret.LastInsertId()
}

// 添加关联关系
func (that *MsgCompanyModel) AddRelated(msgId int64, userId int, employeeId int) (int64, error) {
    data := gdb.Map{
        "msg_id": msgId,
        "user_id": userId,
        "employee_id": employeeId,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }
    ret,_ := db.Table("qz_msg_company_related").Data(data).Insert()
    return ret.LastInsertId()
}

