package logic

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
    "qzmsg/app/model"
    "qzmsg/app/service/weixin/official"
    "strings"
)

type WechatOfficialLogic struct {
    wechatOfficialDriver *official.WeixinApi
    officialAppid string
    appletAppid string
}

func NewWechatOfficlalLogic() *WechatOfficialLogic{
    config := g.Config()
    var wxconf map[string]interface{}
    if config.GetString("setting.APP_ENV") == "dev" {
        wxconf = config.GetMap("weixin.official.develop")
    } else {
        wxconf = config.GetMap("weixin.official.default")
    }

    appid := gconv.String(wxconf["appid"])
    secret := gconv.String(wxconf["secret"])

    appletConf := config.GetMap("weixin.applet.sales")

    return &WechatOfficialLogic{
        wechatOfficialDriver: wechatOfficialDriver(appid, secret),
        officialAppid: appid,
        appletAppid: gconv.String(appletConf["appid"]),
    }
}

func wechatOfficialDriver(appid string, secret string) *official.WeixinApi {
    tokenModel := model.NewWechatTokenModel()
    return official.NewWeixinApi(appid, secret, tokenModel.GetToken, tokenModel.SetToken)
}

func (that *WechatOfficialLogic) SendTemplateMsg(data g.Map, templateId string) {
    userIds := strings.Split(gconv.String(data["userIds"]), ",")
    wechatOpenidModel := model.NewWechatOpenidModel()
    openIdList,err := wechatOpenidModel.GetUserOpenIds(userIds, that.appletAppid, that.officialAppid)
    if err == nil && len(openIdList) > 0 {
        wechatTempLogic := NewWechatTemplateLogic()
        params := wechatTempLogic.GetTemplateParam(data, templateId)

        for _,item := range openIdList {
            params["touser"] = item["wx_openid"]
            go that.wechatOfficialDriver.SendTemplateMessage(params)
        }
    }
}
