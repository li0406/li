package model

import "github.com/gogf/gf/database/gdb"

type UserCompanyRoundOrderModel struct {

}

func NewUserCompanyRoundOrderModel() *UserCompanyRoundOrderModel {
    return &UserCompanyRoundOrderModel{}
}

// 表名
func (that *UserCompanyRoundOrderModel) table() string {
    return "qz_user_company_round_order"
}

// 配置数据表（带别名）
func (that *UserCompanyRoundOrderModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 删除装企订单轮单信息
func (that *UserCompanyRoundOrderModel) DeleteRoundInfo(orderId string, userId int) error {
    _,err := db.Table(that.table()).Where("order_id=?", orderId).Where("user_id=?", userId).Delete()
    return err
}
