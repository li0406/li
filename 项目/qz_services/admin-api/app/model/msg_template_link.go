package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type MsgTemplateLinkModel struct {
}

type MsgTempLinkStruct struct {
    Id int `json:"id"`
    AppSlot string `json:"app_slot"`
    AppName string `json:"app_name"`
    TemplateId string `json:"template_id"`
    Link string `json:"link"`
}

func NewMsgTemplateLinkModel() *MsgTemplateLinkModel{
    return &MsgTemplateLinkModel{}
}

// 表名
func (that *MsgTemplateLinkModel) table() string {
    return "qz_msg_template_link"
}

// 配置数据表（带别名）
func (that *MsgTemplateLinkModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 根据模板ID获取模板链接
func (that *MsgTemplateLinkModel) GetLinkByTemplateIds(templateIds g.Slice) (list []MsgTempLinkStruct, err error) {
    dbQuery := that.connect().
        Where("t.template_id IN(?)", templateIds).
        InnerJoin("qz_sms_app as a", "a.slot = t.app_slot").
        Fields("t.id,t.link,t.template_id,t.app_slot,a.`name`as app_name")

    err = dbQuery.Structs(&list)
    return list,err
}

// 删除模板下的所有链接
func (that *MsgTemplateLinkModel) DeleteByTemplateId(tempId string) error {
    _,err := db.Table(that.table()).Where("template_id=?", tempId).Delete()

    return err
}

// 添加模板链接
func (that *MsgTemplateLinkModel) AddRecord(template_id string, app_slot string, link string) error {
    data := gdb.Map{
        "template_id": template_id,
        "app_slot": app_slot,
        "link": link,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }

    _,err := db.Table(that.table()).Data(data).Insert()
    return err
}
