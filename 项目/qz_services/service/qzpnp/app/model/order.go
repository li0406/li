package model

import (
    "github.com/gogf/gf/database/gdb"
    "github.com/gogf/gf/frame/g"
)

type OrderModel struct {
    id int `json:"order_id"`
    tel8 string `json:"tel_8"`
}

func NewOrderModel() *OrderModel {
    return &OrderModel{}
}

func  (app OrderModel)getTable()  *gdb.Model {
    return g.DB().Table("qz_orders o").Safe()
}

func (o *OrderModel) GetOrderInfo(order_id string) (result gdb.Map,err error){
    r,e := o.getTable().Where("o.id = ?",order_id).
          InnerJoin("safe_order_tel8 t","t.orderid = o.id").
          Fields("o.id,t.tel8 as tel,o.cs").One()

    return r.Map(),e
}



