package model

type SmsSign struct {
	Id int `json:"id"` // '主键'
	Name string  `json:"name"`//'签名'
}

func NewSmsSignModel() *SmsLogSend{
	return &SmsLogSend{}
}