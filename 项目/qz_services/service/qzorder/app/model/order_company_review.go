package model

import "github.com/gogf/gf/database/gdb"

type OrderCompanyReviewModel struct {

}

func NewOrderCompanyReviewModel() *OrderCompanyReviewModel {
    return &OrderCompanyReviewModel{}
}

// 表名
func (that *OrderCompanyReviewModel) table() string {
    return "qz_order_company_review"
}

// 配置数据表（带别名）
func (that *OrderCompanyReviewModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 删除装修公司订单跟踪信息
func (that *OrderCompanyReviewModel) DeleteOrderCompanyReview(orderId string) error {
    _,err := db.Table(that.table()).Where("orderid=?", orderId).Delete()
    return err
}
