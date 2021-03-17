package model

import (
    "github.com/gogf/gf/database/gdb"
)

type UserCompanyAccountLogModel struct {

}

func NewUserCompanyAccountLogModel() *UserCompanyAccountLogModel {
    return &UserCompanyAccountLogModel{}
}

// 表名
func (that *UserCompanyAccountLogModel) table() string {
    return "qz_user_company_account_log"
}

// 检测日志流水号
func (that *UserCompanyAccountLogModel) CheckTradeNo(tradeNo string) bool {
    num,_ := db.Table(that.table()).Where("trade_no=?", tradeNo).Count()
    return !(num > 0)
}

func (that *UserCompanyAccountLogModel) AddLogRecord(data gdb.Map) int64 {
    res,_ := db.Table(that.table()).Data(data).Save()
    lastId, _ := res.LastInsertId()
    return lastId
}

func (that *UserCompanyAccountLogModel) GetLogRecordAmount(userId int, orderId string) (logInfo gdb.Record, err error) {
    dbQuery := db.Table(that.table() + " as l")
    dbQuery.Where("l.`order_id`=?", orderId)
    dbQuery.Where("l.`user_id`=?", userId)
    dbQuery.Where("l.`trade_type`=?", 3)
    dbQuery.LeftJoin("qz_user_company_account_log_round_relation as r", "r.log_id = l.id")
    dbQuery.Fields("r.*")
    dbQuery.Order("l.id desc")
    logInfo, err = dbQuery.FindOne()
    return logInfo, err
}

