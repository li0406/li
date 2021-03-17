package model

type SmsTemplateGateway struct {
	Temp_id string //'模板ID',
	Gateway_id int //'网关通道ID',
	Third_temp_id string //'三方模板ID'
}

func NewSmsTemplateGatewayModel() *SmsTemplate{
	return &SmsTemplate{}
}