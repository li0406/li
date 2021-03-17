package logic

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
    "qzmsg/app/model"
    "qzmsg/app/service/weixin/applet"
)

func WechatAppletDriver() *applet.WeixinApi {
    conf := g.Config().GetMap("weixin.applet.sales")
    appid := gconv.String(conf["appid"])
    secret := gconv.String(conf["secret"])

    tokenModel := model.NewWechatTokenModel()
    return applet.NewWeixinApi(appid, secret, tokenModel.GetToken, tokenModel.SetToken)
}



