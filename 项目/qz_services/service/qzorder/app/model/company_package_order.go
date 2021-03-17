package model

import (
    "github.com/gogf/gf/database/gdb"
    "time"
)

type CompanyPackageOrderModel struct {

}

func NewCompanyPackageOrderModel() *CompanyPackageOrderModel {
    return &CompanyPackageOrderModel{}
}

// 表名
func (that *CompanyPackageOrderModel) table() string {
    return "qz_company_package_order"
}

// 配置数据表（带别名）
func (that *CompanyPackageOrderModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 删除装企订单包订单记录（逻辑删除）
func (that *CompanyPackageOrderModel) DeleteInfo(orderId string, companyId int) error {
    data := gdb.Map{
        "deleted" : 2,
        "updated_at": time.Now().Unix(),
    }
    _,err := db.Table(that.table()).
        Where("order_id=?", orderId).
        Where("company_id=?", companyId).
        Data(data).Update()
    return err
}

