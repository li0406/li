package model

import "github.com/gogf/gf/database/gdb"

type OrderInfoModel struct {

}

type OrderAllotStruct struct {
    OrderId string `json:"order_id"`
    CompanyId int `json:"company_id"`
    TypeFw int `json:"type_fw"`
    AllotSource int `json:"allot_source"`
    RoundOrderId int `json:"round_order_id"`
    RoundApplyId int `json:"round_apply_id"`
    PackageOrderId int `json:"package_order_id"`
    CompanyType int `json:"company_type"`
    ExamStatus int `json:"exam_status"`
}

type OrderRoundInfoStruct struct {
    OrderId string `json:"order_id"`
    CompanyId int `json:"company_id"`
    TypeFw int `json:"type_fw"`
    AllotType int `json:"allot_type"`
    RoundMoney float64 `json:"round_money"`
}

func NewOrderInfoModel() *OrderInfoModel {
    return &OrderInfoModel{}
}

// 表名
func (that *OrderInfoModel) table() string {
    return "qz_order_info"
}

// 配置数据表（带别名）
func (that *OrderInfoModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 查询订单分配记录
func (that *OrderInfoModel) GetOrderAllotList(orderId string) (list []OrderAllotStruct, err error) {
    dbQuery := that.connect()
    dbQuery.Where("t.`order`=?", orderId)
    dbQuery.InnerJoin("qz_user as u", "u.id = t.`com`")
    dbQuery.LeftJoin("qz_user_company_round_order as r", "r.order_id = t.`order` and r.user_id = t.`com`")
    dbQuery.LeftJoin("qz_company_package_order as p", "p.order_id = t.`order` and p.company_id = t.`com` and p.deleted = 1")
    dbQuery.LeftJoin("qz_user_company_round_order_apply as a", "a.id = r.apply_id")
    dbQuery.Fields("t.`order` as order_id,u.id as company_id,t.type_fw,t.allot_source,p.id as package_order_id,r.id as round_order_id,r.apply_id as round_apply_id,a.exam_status")
    err = dbQuery.Structs(&list)
    return list, err
}

// 查询新签返订单分配详情
func (that *OrderInfoModel) GetOrderRoundInfo (orderId string, companyId int) (detail OrderRoundInfoStruct, err error) {
    dbQuery := that.connect()
    dbQuery.Where("t.`order`=?", orderId)
    dbQuery.Where("t.`com`=?", companyId)
    dbQuery.LeftJoin("qz_user_company_round_order as r", "r.order_id = t.`order` and r.user_id = t.`com`")
    dbQuery.Fields("t.`order` as order_id,t.com as company_id,t.type_fw,r.allot_type,r.round_money")
    err = dbQuery.Struct(&detail)
    return detail,err
}

// 删除订单分配信息
func (that *OrderInfoModel) DeleteOrderInfo(orderId string) error {
    _,err := db.Table(that.table()).Where("`order`=?", orderId).Delete()
    return err
}
