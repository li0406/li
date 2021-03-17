package dingding

import (
    "encoding/json"
    "github.com/gogf/gf/g"
    "qzsms/library/util"
)

type RobotService struct {
}

type Text struct {
    Msgtype string `json:"msgtype"`
    Markdown struct{
        Title string `json:"title"`
        Text string `json:"text"`
    } `json:"markdown"`
    At struct{
        AtMobiles []string `json:"at_mobiles"`
        IsAtAll bool `json:"is_at_all"`
    } `json:"at"`
}


func NewRobotService() *RobotService{
    return &RobotService{}
}


func (s RobotService)SendMarkdownMessage(title string,msg string)  {
    config := g.Config()
    webhook := config.GetString("dingding.sms.webhook")

    t :=  &Text{}
    t.Msgtype = "markdown"
    t.Markdown.Title = title
    t.Markdown.Text = msg
    t.At.IsAtAll = true
    bodyJson, err := json.Marshal(t)

    if err == nil {
         util.CurlPostByBodyJson(webhook,bodyJson,nil,nil)
    }
}
