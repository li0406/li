package model

import (
    "github.com/gogf/gf/g/database/gdb"
    "time"
)

type SmsSignModel struct {
}


// 适用于短信签名列表
type SmsSignListStruct struct {
    Id int `json:"id"`                          // 签名ID
    Name string `json:"name"`                   // 签名名称
    Enabled int `json:"enabled"`                // 是否启用
    Creator int `json:"creator"`                // 创建人ID
    CreatorName string `json:"creator_name"`    // 创建人用户名
    CreatedAt string `json:"created_at"`        // 创建时间
}

func NewSmsSignModel () *SmsSignModel {
    return &SmsSignModel{}
}

// 表名
func (that *SmsSignModel) table() string {
    return "qz_sms_sign"
}

// 配置数据表（带别名）
func (that *SmsSignModel) connect() *gdb.Model {
    return db.Table(that.table() + " as s")
}

// 根据ID获取签名信息
func (that *SmsSignModel) GetById(id int) map[string]interface{} {
    r,_ := that.connect().Where("id=?", id).One()
    return r.ToMap()
}

// 根据name获取签名信息
func (that *SmsSignModel) GetByName(name string) map[string]interface{} {
    r,_ := that.connect().Where("name=?", name).One()
    return r.ToMap()
}

// 获取签名列表
func (that *SmsSignModel) GetList() ([]SmsSignListStruct, error) {
    var signList []SmsSignListStruct

    dbQuery := that.connect()
    dbQuery.InnerJoin("qz_adminuser as u", "u.id = s.creator")
    dbQuery.Fields("s.id,s.`name`,s.creator,s.enabled,FROM_UNIXTIME(s.created_at) created_at,u.name as creator_name")
    err := dbQuery.OrderBy("s.id desc").Structs(&signList)

    return signList,err
}

// 获取可用签名列表
func (that *SmsSignModel) GetEnabledList() ([]SmsSignListStruct, error) {
    var signList []SmsSignListStruct

    dbQuery := that.connect().Where("s.enabled = ?", 1)
    dbQuery.InnerJoin("qz_adminuser as u", "u.id = s.creator")
    dbQuery.Fields("s.id,s.`name`,s.creator,s.enabled,FROM_UNIXTIME(s.created_at) created_at,u.name as creator_name")
    err := dbQuery.OrderBy("s.id desc").Structs(&signList)

    return signList,err
}

// 设置签名启用状态
func (that *SmsSignModel) SetEnabled(id int, enabled int) error {
    // enabled 值只允许0和1，不为1的全部当0处理
    if enabled != 1 {
        enabled = 0
    }

    data := gdb.Map{"enabled" : enabled, "updated_at": time.Now().Unix()}
    _,err := db.Table(that.table()).Data(data).Where("id=?", id).Update()

    return err
}

// 新增签名
func (that *SmsSignModel) AddRecord(name string, enabled int, creator int64) (int64, error){
    // enabled 值只允许0和1，不为1的全部当0处理
    if enabled != 1 {
        enabled = 0
    }
    data := gdb.Map{
        "name": name,
        "enabled": enabled,
        "creator": creator,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
    }

    r,_ := db.Table(that.table()).Data(data).Insert()
    return r.LastInsertId()
}

// 编辑签名
func (that *SmsSignModel) EditRecord(id int, name string, enabled int) (int64, error) {
    // enabled 值只允许0和1，不为1的全部当0处理
    if enabled != 1 {
        enabled = 0
    }

    data := gdb.Map{
        "name": name,
        "enabled": enabled,
        "updated_at": time.Now().Unix(),
    }
    r,_ := db.Table(that.table()).Data(data).Where("id=?", id).Update()
    return r.RowsAffected()
}

func (that *SmsSignModel)GetSignList() (gdb.List,error) {
    data := gdb.Map{
        "enabled": 1,
    }
    res,err := that.connect().Where(data).Fields("id,name").Select()
    return res.ToList(),err
}