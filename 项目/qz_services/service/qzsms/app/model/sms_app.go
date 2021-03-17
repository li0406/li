package model

import "github.com/gogf/gf/g/database/gdb"

type SmsApp struct {
	Id int 			// '项目id',
	Slot string 	// '应用标识唯一',
	Name string 	// '应用名称',
	Is_enable int   // '是否启用,0:不启用,1:启用',
}

func NewSmsAppModel() *SmsApp{
	return &SmsApp{}
}

func  (app SmsApp)getTable()  *gdb.Model {
	return db.Table("qz_sms_app a").Safe()
}

func (app SmsApp) GetAppInfo (app_id string) gdb.Map {
	res,_ :=app.getTable().
		InnerJoin("qz_sms_app_access as b", "b.app_slot = a.slot and b.access_type = 1").
		Where("a.slot = ?",app_id).
		Fields("a.id,a.name,a.is_enable").
		One()

	return res.ToMap()
}
