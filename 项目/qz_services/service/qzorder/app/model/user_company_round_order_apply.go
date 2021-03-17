package model

import "github.com/gogf/gf/database/gdb"

type UserCompanyRoundOrderApplyModel struct {

}

func NewUserCompanyRoundOrderApplyModel() *UserCompanyRoundOrderApplyModel {
    return &UserCompanyRoundOrderApplyModel{}
}

// 表名
func (that *UserCompanyRoundOrderApplyModel) table() string {
    return "qz_user_company_round_order_apply"
}

// 配置数据表（带别名）
func (that *UserCompanyRoundOrderApplyModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 删除装企申请补轮表
func (that *UserCompanyRoundOrderApplyModel) DeleteRoundApply(orderId string, userId int) error {
    _,err := db.Table(that.table()).Where("order_id=?", orderId).Where("company_id=?", userId).Delete()
    return err
}
