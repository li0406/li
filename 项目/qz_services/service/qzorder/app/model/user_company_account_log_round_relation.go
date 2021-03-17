package model

import "github.com/gogf/gf/database/gdb"

type UserCompanyAccountLogRoundRelationModel struct {

}

func NewUserCompanyAccountLogRoundRelationModel() *UserCompanyAccountLogRoundRelationModel {
    return &UserCompanyAccountLogRoundRelationModel{}
}

// 表名
func (that *UserCompanyAccountLogRoundRelationModel) table() string {
    return "qz_user_company_account_log_round_relation"
}

// 配置数据表（带别名）
func (that *UserCompanyAccountLogRoundRelationModel) connect() *gdb.Model {
    return db.Table(that.table() + " as t")
}

func (that *UserCompanyAccountLogRoundRelationModel) AddLogRoundRecord(data gdb.Map) int64 {
    res,_ := db.Table(that.table()).Data(data).Save()
    lastId, _ := res.LastInsertId()
    return lastId
}
