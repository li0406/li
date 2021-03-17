package logic

import (
    "admin-api/app/model"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "github.com/gogf/gf/g/util/gvalid"
)

type TemplateLogic struct {

}

// 验证规则
// 切片是有序的，map是无序的，故用切片定义
var TempValidateRules = []string {
    "content@required",
    "use_scene@required",
    "type@min:1",
    "sign_id@min:1",
}

// 自定义错误提示（单独定义方便维护）
var TempValidateMsgs = map[string]interface{} {
    "content" : "请先填写模板内容",
    "use_scene" : "请先填写使用场景",
    "type" : map[string]string {
        "min": "请先选择模板类型",
    },
    "sign_id" : map[string]string {
        "min": "请先选择签名",
    },
}

// 验证
func (that *TemplateLogic) ValidateMap (data gdb.Map) *gvalid.Error {
    return gvalid.CheckMap(data, TempValidateRules, TempValidateMsgs)
}

func (that *TemplateLogic)GetSmsTemplateList(smsType string,content string) map[string] interface{} {
    m :=  new(model.SmsTemplateModel)
    res := m.GetSmsTemplateList(smsType,content)

    var tmpMap = make(map[string] interface{})

    type Gateway struct {
       Slot string
       Name  string
    }
    for _,val := range  res{
       if _,ok := tmpMap[val["id"].(string)];ok == false {
           tmp := model.SmsTemplasteSendStruct{}
           tmp.Id = val["id"].(string)
           tmp.Type = val["type"].(int)
           tmp.Content = val["content"].(string)
           tmp.Gateway = make(map[string] interface{})
           tmpMap[val["id"].(string)] = tmp
       }

       if val["slot"] != nil {
           var gateway = Gateway{
               Slot: gconv.String(val["slot"]),
               Name: gconv.String(val["gateway_name"]),
           }
           tmpMap[val["id"].(string)].(model.SmsTemplasteSendStruct).Gateway[gconv.String(val["slot"])] = gateway
       }
    }
    return tmpMap

}
