package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type SmsAppAccessModel struct {

}

func NewSmsAppAccessModel() *SmsAppAccessModel {
    return &SmsAppAccessModel{}
}

// 表名
func (that *SmsAppAccessModel) table() string {
    return "qz_sms_app_access"
}

// 配置数据表（带别名）
func (that *SmsAppAccessModel) connect() *gdb.Model {
    return db.Table(that.table() + " as a")
}

// 获取APP关联数据
func (that *SmsAppAccessModel) GetListByAppSlot(app_slot g.Slice) (list gdb.List, err error){
    ret,err := that.connect().
        Where("app_slot IN(?)", app_slot).
        Fields("app_slot,access_type").
        Select()

    if err == nil {
        return ret.ToList(),err
    }

    return list,err
}

func (that *SmsAppAccessModel) DeleteByAppSlot(app_slot string) (err error) {
    _,err = db.Table(that.table()).Where("app_slot = ?", app_slot).Delete()
    return err
}

func (that *SmsAppAccessModel) AddRecord (app_slot string, access_type int64) (err error) {
    data := gdb.Map{
        "app_slot": app_slot,
        "access_type": access_type,
        "created_at": time.Now().Unix(),
    }

    _,err = db.Table(that.table()).Data(data).Insert()
    return err
}