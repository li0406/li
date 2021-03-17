package chuanglan

import (
    "encoding/json"
    "github.com/gogf/gf/g/encoding/gjson"
    neturl "net/url"
    "qzsms/app/model"
    "qzsms/library/util"
)

var (
    config Chuanglan
)


type Chuanglan struct {
    Url string `json:"url"`
    Account string `json:"account"`
    Password string `json:"password"`
    Phone string `json:"phone"`
    Msg string `json:"msg"`
}

type Response struct {
   Code string `json:"code"`
   MsgId  string `json:"msg_id"`
   Time string `json:"time"`
   ErrorMsg string `json:"error_msg"`
}

func init()  {
    m := model.NewSmsGatewayModel()
    res,err := m.GetGatewayInfo("chuanglan")

    if  err != nil || len(res) == 0{
        return
    }

    gjson.DecodeTo(res["config"],&config)
}

func SendSms(tel string,content string)(code string,msg string)   {
    if tel == "" || content == ""{
        return "-1","缺失参数"
    }

    if config.Account == "" {
        return "-1","缺失帐号参数"
    }

    //请求头部
    var headers = make(map[string] string)
    headers["Accept"] = "application/json"
    headers["Content-Type"] = "application/json;charset=utf-8"

    //读取配置
    //请求参数
    var body = make(map[string] string)
    url := config.Url
    body["account"] = config.Account
    body["password"] = config.Password
    body["phone"] = tel
    body["msg"] = neturl.QueryEscape(content)
    body["report"] = "true" //是否需要状态报告

    result,err := util.CurlPost(url,body,nil,headers)
    if err == nil {
        response := Response{}
        json.NewDecoder(result.Body).Decode(&response)
        if response.Code != "0" {
            return response.Code,response.ErrorMsg
        }
        return response.Code,response.MsgId
    }
    return "",""
}
