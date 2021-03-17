package model

import (
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/database/gdb"
)

//三方网关设置
type Gateway struct {
    ID           int    `json:"id"`            //id
    Slot         string `json:"slot"`          //唯一标识
    Type         string `json:"type"`          //'通道类型,1.验证码通知(行业),2.营销,3.国际验证码,4.语音验证码',
    TypeName     string `json:"type_name"`     //'通道类型,1.验证码通知(行业),2.营销,3.国际验证码,4.语音验证码',
    Name         string `json:"name"`          //通道名称
    Config       string `json:"config"`        //配置文件(json)，数据库存储为文本
    Level        int    `json:"level"`         //优先级
    Note         string `json:"note"`          //备注
    IsEnable     uint8  `json:"is_enable"`     //是否启用,0:不启用,1:启用
    Creator      string `json:"creator"`       //  创建人ID
    CreatorName  string `json:"creator_name"`  //  创建人
    Operator     string `json:"operator"`      //操作人ID
    OperatorName string `json:"operator_name"` //操作人
    CreatedAt    string `json:"created_at"`    //创建时间
    UpdatedAt    string `json:"updated_at"`    //更新时间
}

//  实例化方法
func NewGatewayModel() *Gateway {
    return &Gateway{}
}

//  获取表名
func (gate *Gateway) TableName() (table string) {
    table = "qz_sms_gateway"
    return
}

//  获取数据库链接
func (gate *Gateway) link() (conn *gdb.Model) {
    conn = g.DB().Table(gate.TableName() + " g").Safe()
    return
}

//获取列表
func (gate *Gateway) List() ([]Gateway, error) {
    var gateway = []Gateway{}
    m := gate.link()
    m = m.InnerJoin("qz_adminuser uc", "uc.id=g.creator")
    m = m.InnerJoin("qz_adminuser uo", "uo.id=g.operator")
    m = m.InnerJoin("qz_sms_gateway_type_map tm", "tm.gateway_id=g.id")
    m = m.Fields("g.*,group_concat(tm.type) type,uc.name creator_name,uo.name operator_name,from_unixtime(g.created_at,'%Y-%m-%d %H:%i:%s') created_at,from_unixtime(g.updated_at,'%Y-%m-%d %H:%i:%s') updated_at")
    m = m.GroupBy("g.id")
    err := m.OrderBy("id desc").Structs(&gateway)

    return gateway, err
}

// 获取总数
func (gate *Gateway) Count() (int, error) {
    m := gate.link()
    m = m.InnerJoin("qz_adminuser uc", "uc.id=g.creator")
    m = m.InnerJoin("qz_adminuser uo", "uo.id=g.operator")
    m = m.InnerJoin("qz_sms_gateway_type_map tm", "tm.gateway_id=g.id")
    ret, err := m.Count()
    return ret, err
}

// 是否有数据
func (gate *Gateway) Has(condition g.Map) (bool, error) {
    m := gate.link()
    m = m.Where(condition)
    ret, err := m.Count()
    return !!(ret > 0), err
}

// 获取详情
func (gate *Gateway) Detail(id int) (Gateway, error) {
    var gateway = Gateway{}
    m := gate.link()
    m = m.Where("g.id=?", id)
    m = m.InnerJoin("qz_sms_gateway_type_map tm", "tm.gateway_id=g.id")
    m = m.Fields("g.*,group_concat(tm.type) type")
    err := m.Struct(&gateway)
    return gateway, err
}

//保存数据
func (gate *Gateway) Save(data g.Map) (int, error) {
    saveRet, err := g.DB().Table(gate.TableName()).Safe().Data(data).Insert()
    if err != nil {
        return 0, err
    }
    ret, err := saveRet.LastInsertId()
    insertId := int(ret)
    return insertId, err
}

//  更新数据
func (gate *Gateway) Update(condition g.Map, data g.Map) (bool, error) {
    saveRet, err := gate.link().Data(data).Where(condition).Update()
    if err != nil {
        return false, err
    }
    ret, err := saveRet.RowsAffected()
    return !!(ret > 0), err
}
