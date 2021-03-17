package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type MsgTemplateModel struct {

}

// 适用于短信模板列表
type MsgTemplateListStruct struct {
    Id string `json:"id"`                               // 模板ID
    Title string `json:"title"`                         // 模板标题
    Notice string `json:"notice"`                       // 模板通知内容
    Content string `json:"content"`                     // 模板内容
    Image string `json:"image"`                         // 图片地址
    ImageFull string `json:"image_full"`                // 图片完整地址
    SendType int `json:"send_type"`                     // 推送方式
    CreatedDate string `json:"created_date"`            // 创建时间
    Enabled int `json:"enabled"`                        // 是否启用
    TypeId int `json:"type_id"`                         // 类型ID
    TypeName string `json:"type_name"`                  // 类型名称
    Creator string `json:"creator"`                     // 添加人
    CreatorName string `json:"creator_name"`            // 添加人
    LinkList []MsgTempLinkStruct `json:"link_list"`     // 模板链接
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

// 生成一个ID
func (that *MsgTemplateModel) MakeId() string {
    var prefix string = "1001"
    var date string = time.Unix(time.Now().Unix(), 0).Format("20060102")

    r,_ := that.connect().Fields("id").OrderBy("id desc").One()
    if maxInfo := r.ToMap(); len(maxInfo) > 0 {
        maxId := maxInfo["id"].(string)
        prefix = maxId[8:]
        prefix = gconv.String(gconv.Int(prefix) + 1)
    }

    var newId string = date + prefix // 新ID
    r,_ = that.connect().Fields("id").Where("id = ?", newId).One()
    if newInfo := r.ToMap(); len(newInfo) > 0 {
        // 新ID已存在重新生成
        newId = that.MakeId()
    }

    return newId
}

// 根据ID获取模板信息
func (that *MsgTemplateModel) GetById(id string) gdb.Map {
    r,_ := that.connect().
        Where("t.id=?", id).
        LeftJoin("qz_msg_type as t1", "t1.id = t.type_id").
        Fields("t.*,t1.`name` as type_name,t1.enabled as type_enabled").
        One()

    return r.ToMap()
}

// 根据type获取模板信息
func (that *MsgTemplateModel) GetByType(typeId int) gdb.Map {
    r,_ := that.connect().Where("type_id=?", typeId).One()
    return r.ToMap()
}

// 获取消息模板列表
func (that *MsgTemplateModel) GetList(input g.MapStrStr, offset int, limit int) (list []MsgTemplateListStruct, err error){
    dbQuery := that.connect()
    that.setListWhere(dbQuery, input)

    field := "t.id,t.title,t.notice,t.`content`,t.image,t.send_type,t.enabled,t.type_id,t.creator," +
        "FROM_UNIXTIME(t.created_at) created_date,t1.`name` as type_name,u.`name` as creator_name"

    dbQuery.InnerJoin("qz_msg_type as t1", "t1.id = t.type_id")
    dbQuery.LeftJoin("qz_adminuser as u", "u.id = t.creator")
    dbQuery.Fields(field)
    dbQuery.OrderBy("t.id desc")
    dbQuery.Limit(offset, limit)

    err = dbQuery.Structs(&list)
    return list,err
}

// 获取短信模板列表总数
func (that *MsgTemplateModel) GetListCount(input g.MapStrStr) (int, error) {
    dbQuery := that.connect()
    that.setListWhere(dbQuery, input)

    dbQuery.InnerJoin("qz_msg_type as t1", "t1.id = t.type_id")
    dbQuery.Fields("t.id")
    return dbQuery.Count()
}

// 设置签名启用状态
func (that *MsgTemplateModel) SetEnabled(id string, enabled int) error{
    // enabled 值只允许1和2，不为1的全部当2处理
    if enabled != 1 {
        enabled = 2
    }

    data := gdb.Map{"enabled" : enabled, "updated_at": time.Now().Unix()}
    _,err := db.Table(that.table()).Data(data).Where("id=?", id).Update()

    return err
}

// 保存数据
func (that *MsgTemplateModel) Save(data gdb.Map) error{
    if gconv.Int(data["enabled"]) != 1 {
        data["enabled"] = 2
    }

    _,err := db.Table(that.table()).Data(data).Save()
    return err
}


// 查询条件
func (that *MsgTemplateModel) setListWhere(dbQuery *gdb.Model, where g.MapStrStr) {
    // 消息类型
    if where["type_id"] != "" && where["type_id"] != "0" {
        dbQuery.Where("t.type_id=?", where["type_id"])
    }

    // 推送方式
    if where["send_type"] != "" && where["send_type"] != "0" {
        dbQuery.Where("t.send_type=?", where["send_type"])
    }

    // 是否启用
    if where["enabled"] != "" && where["enabled"] != "0" {
        dbQuery.Where("t.enabled=?", where["enabled"])
    }

    // 消息内容
    if where["keyword"] != "" {
        keyword := gconv.String(where["keyword"])
        dbQuery.Where("t.`content` like ?", "%" + keyword + "%")
    }
}

