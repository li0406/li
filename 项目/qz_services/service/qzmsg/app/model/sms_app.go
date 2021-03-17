package model

import "github.com/gogf/gf/g/database/gdb"

type SmsAppModel struct {

}

func NewSmsAppModel() *SmsAppModel {
    return &SmsAppModel{}
}

// 表名
func (that *SmsAppModel) table() string {
    return "qz_sms_app"
}

// 配置数据表（带别名）
func (that *SmsAppModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

func (that *SmsAppModel) GetAppInfo (slot string) gdb.Map {
    res,_ := that.connect().
        InnerJoin("qz_sms_app_access as a", "a.app_slot = t.slot and a.access_type = 2").
        Where("t.slot = ?", slot).
        Fields("t.id,t.name,t.is_enable").
        One()

    return res.ToMap()
}