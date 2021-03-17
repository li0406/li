package model

import "github.com/gogf/gf/g/database/gdb"

type WechatOpenidModel struct {

}

func NewWechatOpenidModel() *WechatOpenidModel {
    return &WechatOpenidModel{}
}

// 表名
func (that *WechatOpenidModel) table() string {
    return "qz_wechat_openid"
}

// 配置数据表（带别名）
func (that *WechatOpenidModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

func (that *WechatOpenidModel) GetUserOpenIds(userIds []string, appletAppid string, officialAppid string) (gdb.List, error){
    ret,err := that.connect().
        InnerJoin("qz_adminuser_openid as a", "t.wx_unionid = a.wx_unionid").
        Where("t.wx_appid=?", officialAppid).
        Where("a.wx_appid=?", appletAppid).
        Where("a.user_id IN(?)", userIds).
        Where("t.enabled=?", 1).
        Where("a.enabled=?", 1).
        Where("t.wx_unionid <> ''").
        Fields("t.wx_openid").
        GroupBy("t.wx_openid").
        Select()

    if err == nil {
        return ret.ToList(),nil
    }

    return nil,err
}