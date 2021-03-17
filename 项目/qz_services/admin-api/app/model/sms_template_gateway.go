package model

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/database/gdb"
    "time"
)

type SmsTemplateGatewayModel struct {
}

// 适用于短信模板列表中的通道关联
type SmsTempGatewayStruct struct {
    TempId string `json:"temp_id"` // 模板ID
    GatewayId int `json:"id"` // 三方通道ID
    Slot string `json:"slot"` // 三方通道标识
    Name string `json:"name"` // 三方通道名称
    TypeList []int `json:"type_list"` // 模板类型
    ThirdTempId string `json:"third_temp_id"` // 三方模板ID
    Prepared int `json:"prepared"` // 是否报备
}

// 适用于短信模板编辑时三方通道列表
type SmsTempGatewayEditStruct struct {
    Id int `json:"id"`  // 三方通道ID
    Slot string `json:"slot"` // 三方通道标识
    Name string `json:"name"` // 三方通道名称
    TypeList []int `json:"type_list"` // 模板类型
    ThirdTempId string `json:"third_temp_id"` // 三方模板ID
    Prepared int `json:"prepared"` // 是否报备
    Checked int `json:"checked"` // 是否选中
}

// 表名
func (that *SmsTemplateGatewayModel) table() string {
	return "qz_sms_template_gateway"
}

// 配置数据表（带别名）
func (that *SmsTemplateGatewayModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 根据短信模板ID获取三方通道列表
func (that *SmsTemplateGatewayModel) GetGatewayListByTempIds(tempIds g.Slice) ([]SmsTempGatewayStruct, error) {
    dbQuery := that.connect()
    dbQuery.Where("t.status = ?", 1)
    dbQuery.Where("t.temp_id IN(?)", tempIds)
    dbQuery.InnerJoin("qz_sms_gateway as g", "t.gateway_id = g.id")
    dbQuery.Fields("t.temp_id,t.gateway_id,t.third_temp_id,t.prepared,g.slot,g.name")
    dbQuery.OrderBy("g.id desc")

    var list []SmsTempGatewayStruct
    err := dbQuery.Structs(&list)
    return list,err
}

// 获取三方通道列表
func (that *SmsTemplateGatewayModel) GetGatewayList() ([]SmsTempGatewayEditStruct, error){
    dbQuery := db.Table("qz_sms_gateway")
    dbQuery.Where("is_enable = ?", 1)
    dbQuery.Fields("id,slot,name")
    dbQuery.OrderBy("id desc")

    var list []SmsTempGatewayEditStruct
    err := dbQuery.Structs(&list)
    return list,err
}

// 删除模板下的所有三方关联（物理删除）
func (that *SmsTemplateGatewayModel) DeleteByTempId(tempId string) error {
    _,err := db.Table(that.table()).Where("temp_id=?", tempId).Delete()

    return err
}

// 新增
func (that *SmsTemplateGatewayModel) AddRecord(tempId string, gatewayId int, thirdTempId string, prepared int) error {
    data := gdb.Map{
        "temp_id": tempId,
        "gateway_id": gatewayId,
        "third_temp_id": thirdTempId,
        "prepared": prepared,
        "created_at": time.Now().Unix(),
        "updated_at": time.Now().Unix(),
        "status": 1,
    }

    _,err := db.Table(that.table()).Data(data).Insert()
    return err
}

// 编辑
func (that *SmsTemplateGatewayModel) EditRecord(tempId string, gatewayId int, thirdTempId string, prepared int) error {
    where := gdb.Map{
        "temp_id": tempId,
        "gateway_id": gatewayId,
    }

    data := gdb.Map{
        "third_temp_id": thirdTempId,
        "prepared": prepared,
        "updated_at": time.Now().Unix(),
        "status": 1,
    }

    _,err := db.Table(that.table()).Where(where).Data(data).Update()
    return err
}


//  根据网关ID查看是否存在有效的模板
func (that *SmsTemplateGatewayModel) HasTemplateByGatewayID(id int) (bool, error) {
	dbQuery := that.connect()
	dbQuery.Where("g.id = ?", id)
	dbQuery.Where("t.status = ?", 1)
	dbQuery.Where("st.enabled = ?", 1)
	dbQuery.InnerJoin("qz_sms_gateway as g", "t.gateway_id = g.id")
	dbQuery.InnerJoin("qz_sms_template as st", "t.temp_id = st.id")
	count, err := dbQuery.Count()
	return !!(count > 0), err
}

// 根据模板ID验证是否有启用的通道网关
func (that *SmsTemplateGatewayModel) HasGatewayByTemplateId(id string) (int, error) {
    dbQuery := that.connect()
    dbQuery.Where("t.temp_id = ?", id)
    dbQuery.Where("t.status = ?", 1)
    dbQuery.Where("g.is_enable = ?", 1)
    dbQuery.InnerJoin("qz_sms_gateway as g", "g.id = t.gateway_id")
    count, err := dbQuery.Count()
    return count, err
}