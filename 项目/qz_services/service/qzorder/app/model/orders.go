package model

import "github.com/gogf/gf/database/gdb"

type OrdersModel struct {

}

type OrderDetailStruct struct {
    Id string `json:"id"`
    On int `json:"on"`
    TypeFw int `json:"type_fw"`
    TimeReal int64 `json:"time_real"`
    QiandanCompanyId int `json:"qiandan_companyid"`
    QiandanStatus int `json:"qiandan_status"`
}

func NewOrdersModel() *OrdersModel {
    return &OrdersModel{}
}

// 表名
func (that *OrdersModel) table() string {
    return "qz_orders"
}

// 配置数据表（带别名）
func (that *OrdersModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 查询订单详情
func (that *OrdersModel) GetOrderDetail(orderId string) (detail OrderDetailStruct, err error) {
    dbQuery := that.connect()
    dbQuery.Where("t.id=?", orderId)
    dbQuery.Fields("t.id,t.`on`,t.type_fw,t.time_real,t.qiandan_companyid,t.qiandan_status")
    err = dbQuery.Struct(&detail)
    return detail,err
}

// 保存数据
func (that *OrdersModel) Update(orderId string, data gdb.Map) error{
    _,err := db.Table(that.table()).Where("id=?", orderId).Data(data).Update()
    return err
}