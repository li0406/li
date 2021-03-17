package model

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/database/gdb"
)

type SmsGateway struct {
	Id int `json:"id"` 		   //网关id
	Name string `json:"name"` //通道名称
	Level int `json:"level"` //优先级
	Slot string `json:"slot"` //唯一标识
	Third_temp_id string `json:"third_temp_id"` //'三方模版ID'
}

func NewSmsGatewayModel() *SmsGateway{
	return &SmsGateway{}
}

func  (that SmsGateway)getTable()  *gdb.Model {
	return db.Table("qz_sms_gateway a").Safe()
}

func (that SmsGateway)GetGatewayInfo(slot string) (gdb.Map,error) {
	 condition := g.Map{
	 	"slot" : slot,
	 }
	 res,err := that.getTable().Where(condition).One()
	 return res.ToMap(),err
}


