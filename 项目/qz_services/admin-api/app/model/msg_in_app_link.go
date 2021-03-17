package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgInAppLinkModel struct {
}

func NewMsgInAppLinkModel() *MsgInAppLinkModel {
    return &MsgInAppLinkModel{}
}

func (that *MsgInAppLinkModel) table() string {
    return "qz_msg_in_app_link"
}

// 配置数据表（带别名）
func (that *MsgInAppLinkModel) connect() *gdb.Model {
    return db.Table(that.table() + " as al")
}

// 根据模板ID获取模板链接
func (that *MsgInAppLinkModel) GetLinkByMsgId(msg_id string) (g.List, error) {
    dbQuery := that.connect().
        Where("al.msg_id = ? ", msg_id).
        InnerJoin("qz_sms_app as sa", "sa.slot = al.app_slot").
        Fields("al.id,al.link,al.msg_id,al.app_slot,sa.`name`as app_name")

    ret, err := dbQuery.Select()
    return ret.ToList(), err
}



// 删除模板下的所有链接
func (that *MsgInAppLinkModel) DeleteByMsgId(msgId string) error {
    _,err := db.Table(that.table()).Where("msg_id=?", msgId).Delete()

    return err
}

// 添加模板链接
func (that *MsgInAppLinkModel) AddRecord(msg_id string, app_slot string, link string) error {
    data := gdb.Map{
        "msg_id": msg_id,
        "app_slot": app_slot,
        "link": link,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }

    _,err := db.Table(that.table()).Data(data).Insert()
    return err
}
