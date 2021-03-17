package logic

import (
    "github.com/gogf/gf/database/gdb"
    "github.com/gogf/gf/frame/g"
    "github.com/gogf/gf/util/gconv"
    "math/rand"
    "qzorder/app/enum"
    "qzorder/app/model"
    "time"
)

type CompanyAccountLogic struct {
}

func NewCompanyAccountLogic() *CompanyAccountLogic {
    return &CompanyAccountLogic{}
}

// 轮单费返还
func (that *CompanyAccountLogic) setAccountRoundOrderBack(userId int, tradeAmount float64, orderId string, operator int) error {
    companyAccountModel := model.NewUserCompanyAccountModel()
    accountInfo := companyAccountModel.GetAccountInfo(userId)

    //获取最新轮单扣费信息
    companyAccountLogModel := model.NewUserCompanyAccountLogModel()
    logInfo, _ := companyAccountLogModel.GetLogRecordAmount(userId, orderId)

    // 交易后轮单余额
    accountAmount := accountInfo.AccountAmount + tradeAmount

    data := gdb.Map{
        "account_amount": accountAmount,
        "updated_at": time.Now().Unix(),
    }
    err := companyAccountModel.Update(userId, data)
    if err == nil {
       accountAmountTotal := accountAmount + accountInfo.GiftAmount
       logData := g.Map{
           "user_id": userId,
           "order_id": orderId,
           "trade_amount": tradeAmount,
           "operator": operator,
           "trade_type": enum.ACCOUNT_CODE_TRADE_ROUND_BACK,
           "account_type": enum.ACCOUNT_CODE_TYPE_DEFAULT,
           "operation_type": enum.ACCOUNT_CODE_OPERATION_INC,
           "trade_remark": "轮单费返还",
           "account_amount": accountAmountTotal,
       }
       logId := that.addLog(logData)
        //添加退费轮单单价日志
        if gconv.Int(logInfo["log_id"]) != 0 {
            logRoundData := g.Map{
                "log_id":              logId,
                "round_amount_type":   logInfo["round_amount_type"],
                "mianji":              logInfo["mianji"],
                "round_amount_mianji": logInfo["round_amount_mianji"],
                "price":               logInfo["price"],
                "created_at":          time.Now().Unix(),
            }
            _ = that.addRoundRelationLog(logRoundData)
        }
    }

    return err
}

// 补轮单次数返还
func (that *CompanyAccountLogic) setAccountRoundOrderNumberBack(userId int, orderId string, operator int){
    companyAccountModel := model.NewUserCompanyAccountModel()
    accountInfo := companyAccountModel.GetAccountInfo(userId)

    // 交易后补轮数量
    roundOrderNumber := accountInfo.RoundOrderNumber + 1

    data := gdb.Map{
        "round_order_number": roundOrderNumber,
        "updated_at": time.Now().Unix(),
    }
    err := companyAccountModel.Update(userId, data)
    if err == nil {
        logData := g.Map{
            "user_id": userId,
            "order_id": orderId,
            "operator": operator,
            "account_amount": accountInfo.AccountAmount,
            "trade_type": enum.ACCOUNT_CODE_TRADE_LDS_BACK,
            "account_type": enum.ACCOUNT_CODE_TYPE_LDS,
            "operation_type": enum.ACCOUNT_CODE_OPERATION_INC,
            "trade_remark": "补轮数量返还",
        }
        that.addLog(logData)
    }
}

// 添加交易日志
func (that*CompanyAccountLogic) addLog(data g.Map) int64 {
    data["trade_no"] = that.makeLogTradeNo()
    data["created_at"] = time.Now().Unix()
    data["updated_at"] = time.Now().Unix()

    companyAccountLogModel := model.NewUserCompanyAccountLogModel()
    logId := companyAccountLogModel.AddLogRecord(data)
    return logId
}

// 生成一个交易流水号
func (that*CompanyAccountLogic) makeLogTradeNo() string {
    var date string = time.Unix(time.Now().Unix(), 0).Format("2006010215")
    randnum := rand.Intn(9999)
    if randnum < 1000 {
        randnum += 1000
    }

    tradeNo := date + gconv.String(randnum)
    companyAccountLogModel := model.NewUserCompanyAccountLogModel()
    ret := companyAccountLogModel.CheckTradeNo(tradeNo)
    if ret == false {
        tradeNo = that.makeLogTradeNo()
    }

    return tradeNo
}

// 添加多轮单扣费关联日志
func (that*CompanyAccountLogic) addRoundRelationLog(data g.Map) int64 {
    companyAccountLogModel := model.NewUserCompanyAccountLogRoundRelationModel()
    logId := companyAccountLogModel.AddLogRoundRecord(data)
    return logId
}
