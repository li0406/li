package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

type GatewayTypeMap struct {
    ID           int    `json:"id"`            //id
    Type         int    `json:"type"`          //'通道类型,1.验证码通知(行业),2.营销,3.国际验证码,4.语音验证码',
    GatewayID    int    `json:"gateway_id"`    //'通道ID
    Creator      string `json:"creator"`       //  创建人ID
    CreatorName  string `json:"creator_name"`  //  创建人
    Operator     string `json:"operator"`      //操作人ID
    OperatorName string `json:"operator_name"` //操作人
    CreatedAt    string `json:"created_at"`    //创建时间
    UpdatedAt    string `json:"updated_at"`    //更新时间
}

//  实例化方法
func NewGatewayTypeMapModel() *GatewayTypeMap {
    return &GatewayTypeMap{}
}

//  获取表名
func (gtm *GatewayTypeMap) TableName() (table string) {
    table = "qz_sms_gateway_type_map"
    return
}

//  获取数据库链接
func (gtm *GatewayTypeMap) link() (conn *gdb.Model) {
    conn = g.DB().Table(gtm.TableName() + " tm").Safe()
    return
}

//保存数据
func (gtm *GatewayTypeMap) Save(data g.Map) (bool, error) {
    saveRet, err := g.DB().Table(gtm.TableName()).Safe().Data(data).Insert()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.LastInsertId()
    return !!(ret > 0), err
}

//保存多条数据
func (gtm *GatewayTypeMap) SaveMulti(data g.List) (bool, error) {
    saveRet, err := g.DB().Table(gtm.TableName()).Safe().Data(data).Save()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.LastInsertId()
    return !!(ret > 0), err
}

//  更新数据
func (gtm *GatewayTypeMap) Update(condition g.Map, data g.Map) (bool, error) {
    saveRet, err := gtm.link().Data(data).Where(condition).Update()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.RowsAffected()
    return !!(ret > 0), err
}

//  删除数据
func (gtm *GatewayTypeMap) Delete(condition g.Map) (bool, error) {
    saveRet, err := g.DB().Table(gtm.TableName()).Safe().Where(condition).Delete()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.RowsAffected()
    return !!(ret > 0), err
}

// 根据三方通道ID查询所有关联的分类
func (gtm *GatewayTypeMap) GetListByGatewayIds (gatewayIds g.Slice) gdb.List {
    r,_ := gtm.link().Where("tm.gateway_id IN(?)", gatewayIds).Fields("tm.id,tm.type,tm.gateway_id").Select()
    return r.ToList()
}
