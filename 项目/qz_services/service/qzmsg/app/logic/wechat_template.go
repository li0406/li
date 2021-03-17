package logic

import (
    "github.com/gogf/gf/g"
)

type WechatTemplateLogic struct {
}

func NewWechatTemplateLogic() *WechatTemplateLogic{
    return &WechatTemplateLogic{}
}

func (that *WechatTemplateLogic) GetTemplateParam(data g.Map, temlateId string) (params g.Map) {
    switch temlateId {
        case "qzmsg":
            params = that.getTempQxmsg(data)
            break
    }

    return params
}

func (that *WechatTemplateLogic) getTempQxmsg(data g.Map) (params g.Map) {
    var templateId string
    if g.Config().GetString("setting.APP_ENV") == "dev" {
        templateId = "qoTFhIdqzT4qHtr7mrGKPcE-60fhzhuVW5ljpY-b6vE"
    } else {
        templateId = "mVrCrn-etrFBM7cb6yf0FP48k69YqZiDDsc5QatT0f4"
    }

    params = g.Map{
        "touser": "",
        "template_id": templateId,
        "url": "",
        "data": map[string]interface{}{
            "first": map[string]interface{}{
                "value": "你好，你有一条新消息。",
                "color": "#173177",
            },
            "keyword1": map[string]interface{}{
                "value": data["title"],
                "color": "#173177",
            },
            "keyword2": map[string]interface{}{
                "value": data["notice"],
                "color": "#173177",
            },
            "keyword3": map[string]interface{}{
                "value": data["datetime"],
                "color": "#173177",
            },
            "remark": map[string]interface{}{
                "value": "感谢您的支持！",
                "color": "#173177",
            },
        },
    }

    return params
}