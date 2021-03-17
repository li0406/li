package model

import (
    "github.com/gogf/gf/database/gdb"
)

type UserCompanyAccountModel struct {

}

type AccountInfoStruct struct {
    UserId int `json:"user_id"`
    AccountAmount float64 `json:"account_amount"`
    RoundOrderNumber int `json:"round_order_number"`
    GiftAmount float64 `json:"gift_amount"`
}

func NewUserCompanyAccountModel() *UserCompanyAccountModel {
    return &UserCompanyAccountModel{}
}

// 表名
func (that *UserCompanyAccountModel) table() string {
    return "qz_user_company_account"
}

// 配置数据表（带别名）
func (that *UserCompanyAccountModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

// 获取账户信息
func (that *UserCompanyAccountModel) GetAccountInfo(userId int) (accountInfo AccountInfoStruct) {
    dbQuery := that.connect()
    dbQuery.Where("t.user_id=?", userId)
    dbQuery.Fields("t.user_id,t.account_amount,t.round_order_number,t.gift_amount")
    _ = dbQuery.Struct(&accountInfo)
    return accountInfo
}

// 保存数据
func (that *UserCompanyAccountModel) Update(userId int, data gdb.Map) error{
    _,err := db.Table(that.table()).Where("user_id=?", userId).Data(data).Update()
    return err
}
