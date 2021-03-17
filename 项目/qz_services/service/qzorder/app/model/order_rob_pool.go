package model

import "github.com/gogf/gf/database/gdb"

type OrderRobPoolModel struct {

}

func NewOrderRobPoolModel() *OrderRobPoolModel {
    return &OrderRobPoolModel{}
}

// 表名
func (that *OrderRobPoolModel) table() string {
    return "qz_order_rob_pool"
}

// 配置数据表（带别名）
func (that *OrderRobPoolModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 删除订单池
func (that *OrderRobPoolModel) DeleteByOrderId(orderId string) error {
    _,err := db.Table(that.table()).Where("order_id=?", orderId).Delete()
    return err
}