package model

import (
	"github.com/gogf/gf/g/database/gdb"
)

type SmsLogSend struct {
	Type int `json:"type"`
	Tel string `json:"tel"`
	Tel_encrypt string `json:"tel_encrypt"`
 	Content string `json:"content"`
	Module int `json:"module"`
	Url string `json:"url"`
	Status int `json:"status"`
	Body string `json:"body"`
	Create_time int `json:"create_time"`
	Gateway int `json:"gateway"`
	Sign int `json:"sign"`
	Template_id string `json:"template_id"`
}

func NewSmsLogSendModel() *SmsLogSend{
	return &SmsLogSend{}
}

func  (log SmsLogSend)getTable()  *gdb.Model {
	return db.Table("qz_sms_log_send").Safe()
}

func  (log SmsLogSend)getAliasTable() *gdb.Model   {
	return db.Table("qz_sms_log_send a").Safe()
}

func (log SmsLogSend) Add(s interface{}) int64 {
	res,_ := log.getTable().Data(s).Save()
	lastId, _ := res.LastInsertId()
	return lastId
}

func (log SmsLogSend) Edit(s interface{}) int64{
	res,_ := log.getTable().Data(s).Update()
	r,_ := res.RowsAffected()
	return r
}

func (log SmsLogSend) Delete(s interface{})  int64 {
	res,_ := log.getTable().Data(s).Delete()
	r,_ := res.RowsAffected()
	return r
}

func (log SmsLogSend) Save(data []SmsLogSend) {
	log.getTable().Data(data).Save()
}





