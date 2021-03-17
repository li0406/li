package logic

import (
    "admin-api/app/model"
    "admin-api/library/util"
    "fmt"
    "github.com/gogf/gf/g"
    "time"
)

type SmsLogSendLogic struct {

}

type SmslogSendParams struct {
    Module int
    Sign string
    Gateway int
    Status int
    Date string
    Tel string
    Content string
    PageIndex int
    PageCount int
}


func (s SmsLogSendLogic)Getloglist(param *SmslogSendParams) map[string] interface{}{
    m := model.NewSmsLogSendModel()
    condition := g.Map{}

    if param.Content != "" {
        condition["content like ?"] =  "%"+ param.Content + "%"
    }

    if param.Gateway != 0 {
        condition["gateway"] = param.Gateway
    }

    if param.Module != 0 {
        condition["module"] = param.Module
    }

    if param.Status != 0 {
        condition["status"] = param.Status
    }

    if param.Tel != "" {
        condition["tel_encrypt"] = util.MD5(param.Tel)
    }
    t1 := "2006-01-02 15:04:05"
    begin,_ := time.ParseInLocation( t1,(time.Now().Format("2006-01-02") + " 00:00:00"), time.Local)
    end,_ := time.ParseInLocation( t1,(time.Now().Format("2006-01-02") + " 23:59:59"), time.Local)

    if param.Date != "" {
        begin,_ = time.ParseInLocation( t1,(param.Date + " 00:00:00"), time.Local)
        end,_ = time.ParseInLocation( t1,(param.Date + " 23:59:59"), time.Local)
    }
    condition["create_time between ? and ?"] = g.Slice{begin.Unix(),end.Unix()}

    count := m.GetLogListCount(condition)
    fmt.Println(count)
    var list  []*model.SmsLogSend
    var mapList  = make(map[string] interface{})

    if count > 0 {
       p := util.GetPage(param.PageIndex,param.PageCount,count)
       res := m.GetLogList(condition,(p.PageCurrent-1)*p.PageSize,p.PageSize)

       for _,val := range res{
           sub := new(model.SmsLogSend)
           sub.Id = val["id"].(int)
           sub.Tel = val["tel"].(string)
           sub.Status = val["status"].(int)
           if val["app_name"] != nil {
               sub.Module = val["app_name"].(string)
           }
           sub.Content = val["content"].(string)
           sub.Url = val["url"].(string)
           sub.Body = val["body"].(string)
           sub.Type = val["type"].(int)
           sub.Create_time = util.TimeToDate(int64(val["create_time"].(int)),19)
           if val["gateway_name"] != nil {
               sub.Gateway = val["gateway_name"].(string)
           }

           if val["sign_name"] != nil {
               sub.Sign = val["sign_name"].(string)
           }

           sub.Template_id = val["template_id"].(string)
           list =  append(list,sub)
       }
       mapList["list"] = list
       mapList["page"] = p
    }
    return mapList
}

