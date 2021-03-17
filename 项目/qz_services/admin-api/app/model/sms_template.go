package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
    "github.com/gogf/gf/g/util/gconv"
    "time"
)

type SmsTemplateModel struct {

}

// 适用于短信模板列表
type SmsTemplateListStruct struct {
    Id string `json:"id"`                                  // 模板ID
    Content string `json:"content"`                     // 模板内容
    UseScene string `json:"use_scene"`                  // 使用场景
    Encrypt int `json:"encrypt"`                        // 是否脱敏
    CreatedAt string `json:"created_at"`                // 创建时间
    Enabled int `json:"enabled"`                        // 是否启用
    SignId int `json:"sign_id"`                         // 签名ID
    SignName string `json:"sign_name"`                  // 签名
    Type int `json:"type"`                              // 模板类型
    TypeName string `json:"type_name"`                  // 模板类型名称
    GatewayList []SmsTempGatewayStruct `json:"gateway_list"` // 关联的三方通道
}

type SmsTemplasteSendStruct struct {
    Id string `json:"id"`
    Content string `json:"content"`
    Type int `json:"type"`
    Gateway map[string] interface{}  `json:"gateway"`
}

func NewSmsTemplateModel() *SmsTemplateModel {
    return &SmsTemplateModel{}
}

// 表名
func (that *SmsTemplateModel) table() string {
    return "qz_sms_template"
}

// 配置数据表（带别名）
func (that *SmsTemplateModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 生成一个ID
func (that *SmsTemplateModel) MakeId() string {
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
func (that *SmsTemplateModel) GetById(id string) map[string]interface{} {
    r,_ := that.connect().Where("id=?", id).One()
    return r.ToMap()
}

// 获取短信模板列表
func (that *SmsTemplateModel) GetList(where map[string]string, offset int, limit int) ([]SmsTemplateListStruct, error){
    dbQuery := that.connect()
    that.setListWhere(dbQuery, where)

    dbQuery.InnerJoin("qz_sms_sign as s", "s.id = t.sign_id")
    dbQuery.LeftJoin("qz_sms_template_gateway as g", "g.temp_id = t.id")
    dbQuery.Fields("t.id,t.`content`,t.use_scene,t.encrypt,t.enabled,t.sign_id,t.type,FROM_UNIXTIME(t.created_at) created_at,s.`name` as sign_name")
    dbQuery.OrderBy("t.id desc")
    dbQuery.GroupBy("t.id")
    dbQuery.Limit(offset, limit)

    var list []SmsTemplateListStruct
    err := dbQuery.Structs(&list)

    return list,err
}

// 获取短信模板列表总数
func (that *SmsTemplateModel) GetListCount(where map[string]string) (int, error) {
    dbQuery := that.connect()
    that.setListWhere(dbQuery, where)

    dbQuery.InnerJoin("qz_sms_sign as s", "s.id = t.sign_id")
    dbQuery.LeftJoin("qz_sms_template_gateway as g", "g.temp_id = t.id")
    dbQuery.Fields("t.id")
    dbQuery.GroupBy("t.id")
   return dbQuery.Count()
}

// 设置签名启用状态
func (that *SmsTemplateModel) SetEnabled(id string, enabled int) error{
    // enabled 值只允许0和1，不为1的全部当0处理
    if enabled != 1 {
        enabled = 0
    }

    data := gdb.Map{"enabled" : enabled, "updated_at": time.Now().Unix()}
    _,err := db.Table(that.table()).Data(data).Where("id=?", id).Update()

    return err
}

// 保存数据
func (that *SmsTemplateModel) Save(data gdb.Map) error{
    if data["enabled"] != 1 {
        data["enabled"] = 0
    }

    _,err := db.Table(that.table()).Data(data).Save()
    return err
}


// 查询条件
func (that *SmsTemplateModel) setListWhere(dbQuery *gdb.Model, where map[string]string) {
    // 模板类型
    if where["type_id"] != "" {
        dbQuery.Where("t.type=?", where["type_id"])
    }

    // 签名
    if where["sign_id"] != "" {
        dbQuery.Where("t.sign_id=?", where["sign_id"])
    }

    // 是否启用
    if where["enabled"] != "" {
        dbQuery.Where("t.enabled=?", where["enabled"])
    }

    // 模板内容
    if where["content"] != "" {
        dbQuery.Where("t.`content` like ?", "%" + where["content"] + "%")
    }

    // 三方通道网关
    if where["gateway_id"] != "" {
        dbQuery.Where("g.gateway_id=?", where["gateway_id"])
    }
}

func (that *SmsTemplateModel) GetSmsTemplateList(smsType string,content string) gdb.List  {
    conditions := g.Map{
        "type"         : smsType,
    }

    if content != "" {
        conditions["content like ?"] = "%"+content+"%"
    }

    res,_ := that.connect().Where(conditions).
        LeftJoin("qz_sms_template_gateway b","t.id = b.temp_id").
        LeftJoin("qz_sms_gateway c","c.id = b.gateway_id").
        Fields("t.id,t.content,t.type,c.slot,c.name as gateway_name").OrderBy("id").Select()
    return res.ToList()
}
