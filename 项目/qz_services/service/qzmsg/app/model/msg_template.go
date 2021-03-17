package model

import "github.com/gogf/gf/g/database/gdb"

type MsgTemplateModel struct {

}

func NewMsgTemplateModel() *MsgTemplateModel {
    return &MsgTemplateModel{}
}

// 表名
func (that *MsgTemplateModel) table() string {
    return "qz_msg_template"
}

// 配置数据表（带别名）
func (that *MsgTemplateModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 根据ID获取模板信息
func (that *MsgTemplateModel) GetById(id string) gdb.Map {
    ret,_ := that.connect().
        Where("t.id=?", id).
        Where("t.enabled = 1").
        InnerJoin("qz_msg_type as a", "t.type_id = a.id").
        LeftJoin("qz_msg_type_app as app", "t.type_id = app.type_id").
        Fields("t.*,a.slot as type_slot,a.`name` as type_name,a.remind_type,group_concat(app.app_slot) as type_apps").
        GroupBy("t.id").
        One()

    return ret.ToMap()
}
