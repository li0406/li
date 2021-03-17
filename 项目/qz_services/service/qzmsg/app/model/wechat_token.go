package model

import (
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "qzmsg/library/util"
    "time"
)

type WechatTokenModel struct {

}

func NewWechatTokenModel() *WechatTokenModel {
    return &WechatTokenModel{}
}

// 表名
func (that *WechatTokenModel) table() string {
    return "qz_wechat_token"
}

// 配置数据表（带别名）
func (that *WechatTokenModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 获取access_token
func (that *WechatTokenModel) GetToken(appid string) string {
    ret,_ := that.connect().
        Where("t.appid=?", appid).
        Where("t.expires_in > ?", time.Now().Unix()).
        Fields("t.appid,t.token,t.expires_in").
        OrderBy("t.id desc").
        One()

    var token string
    if ret["token"] != nil {
        token = gconv.String(ret["token"])
    }

    return token
}

func (that *WechatTokenModel) SetToken(appid string, token string, expires_in int64) {
    ret,_ := that.connect().Where("t.appid=?", appid).Fields("t.id,t.appid").One()

    nowtime := time.Now().Unix()
    datetime := util.TimeToDate(nowtime, 19)

    data := gdb.Map{
        "appid": appid,
        "token": token,
        "expires_in": nowtime + expires_in,
        "updated_at": datetime,
    }

    if ret["id"] == nil {
        data["info"] = "首次插入"
        data["created_at"] = datetime
    } else {
        data["id"] = ret["id"]
        data["info"] = "正常更新"
    }

    _,_ = db.Table(that.table()).Data(data).Save()
}
