package model

import (
	"github.com/gogf/gf/g/database/gdb"
)

type SmsTemplate struct {
	Id string  `json:"id"` //主键（程序生成）,
	Content string `json:"content"`// COMMENT '模板内容,
	SmsSign SmsSign `json:"sms_sign"`//签名对象
	Encrypt int `json:"encrypt"`//是否脱敏（0：否：1：是）（选择脱敏后  发送号码中间打*）
	SmsGateway []SmsGateway //模版关联网关
	Type int //通道类型(多个以,分隔),1.验证码通知(行业),2.营销,3.国际验证码,4.语音验证码
}

func NewSmsTemplateModel() *SmsTemplate{
	return &SmsTemplate{}
}

func  (st SmsTemplate)getTable()  *gdb.Model {
	return db.Table("qz_sms_template a").Safe()
}

func (s SmsTemplate)GetSmsTemplateInfo(sms_id string) (gdb.List,error) {
	res,err:= s.getTable().Where("a.id = ?",sms_id).And("a.enabled = 1").
		InnerJoin("qz_sms_sign b","b.id = a.sign_id and b.enabled = 1").
		InnerJoin("qz_sms_template_gateway c","c.temp_id = a.id and c.prepared = 1 and c.status = 1").
		InnerJoin("qz_sms_gateway d","d.id = c.gateway_id and d.is_enable = 1").
		Fields("a.id,a.content,a.encrypt,a.type,d.id as gateway_id,d.name as gateway_name,d.slot, d.level,b.id as sign_id,b.name as sign_name,c.third_temp_id").
		OrderBy("d.level desc,a.id asc").
		Select()
	return res.ToList(),err
}
