package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type SmsLogSend struct {
    Id int `json:"id"`
    Type int `json:"type"`//'行业类型 1.行业 2.营销',
    Tel string `json:"tel"`//'电话号码',
    Content string `json:"content"`// '发送内容',
    Module string `json:"module"`// '项目应用',
    Url string `json:"url"`// '发送提交的url',
    Status int `json:"status"`// '提交状态 1成功 2失败',
    Body string `json:"body"`// '返回信息',
    Create_time string `json:"create_time"` //提交时间
    Gateway string `json:"gateway"` //网关
    Sign string `json:"sign"` //签名
    Template_id string `json:"template_id"` //模版ID
}

func NewSmsLogSendModel () *SmsLogSend {
    return &SmsLogSend{}
}

func (s SmsLogSend)getTable() *gdb.Model{
    return db.Table("qz_sms_log_send a").Safe()
}

func  (s SmsLogSend)GetLogListCount(param g.Map) int {
    res,_ := s.getTable().Where(param).Count()
    return res
}

func (s SmsLogSend)GetLogList(param g.Map,pageIndex int,pageCount int)  gdb.List{
    res,_ := s.getTable().Where(param).
        LeftJoin("qz_sms_app b","b.id = a.module").
        LeftJoin("qz_sms_gateway c","c.id = a.gateway").
        LeftJoin("qz_sms_sign d","d.id = a.sign").
        Fields("a.*,b.name as app_name,c.name as gateway_name,d.name as sign_name").
        OrderBy("a.id desc").
        Limit(pageIndex,pageCount).Select()
    return res.ToList()
}