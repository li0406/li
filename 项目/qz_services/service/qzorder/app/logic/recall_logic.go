package logic

import (
    "github.com/gogf/gf/frame/g"
    "qzorder/app/enum"
    "qzorder/app/model"
    "time"
)

type RecallLogic struct {
}

func NewRecallLogic() *RecallLogic {
    return &RecallLogic{}
}

// 取消分配验证
func (that *RecallLogic) CheckOrderInfoRecall(orderInfoList []model.OrderAllotStruct) (errcode int, errmsg string) {
    for _,item := range orderInfoList {
        // 抢单不能撤销
        //if item.AllotSource == enum.ORDER_CODE_ALLOT_SOURCE_ROB {
        //    return 1000001, "已有装企抢单，无法撤消"
        //}

        // 新签返已申请补轮不能撤销
        if item.CompanyType == enum.COMPANY_CODE_SIGNBACK && item.RoundApplyId != 0 && item.ExamStatus == 2{
            return 1000001, "已通过补轮，无法撤销"
        }
    }

    return errcode, errmsg
}

// 常规会员撤销逻辑
func (that *RecallLogic) RecallDefaultOrder(orderId string, companyId int){
    // 删除抢单池信息
    orderRobPoolModel := model.NewOrderRobPoolModel()
    _ = orderRobPoolModel.DeleteByOrderId(orderId)
}

// 签返订单撤销逻辑
func (that *RecallLogic) RecallSignbackOrder(orderId string, companyId int, operator int){
    orderInfoModel := model.NewOrderInfoModel()
    roundInfo,err := orderInfoModel.GetOrderRoundInfo(orderId, companyId)
    if err == nil && roundInfo.TypeFw == enum.ORDER_CODE_TYPE_FW_FEN {
        companyAccountLogic := NewCompanyAccountLogic()
        if roundInfo.AllotType == enum.ORDER_CODE_ALLOT_TYPE_BULUN {
            // 补轮次数返还
            companyAccountLogic.setAccountRoundOrderNumberBack(roundInfo.CompanyId, roundInfo.OrderId, operator)
        } else {
            // 轮单费返还
            _ = companyAccountLogic.setAccountRoundOrderBack(roundInfo.CompanyId, roundInfo.RoundMoney, roundInfo.OrderId, operator)
        }
    }

    // 删除装企轮单记录
    companyRoundOrderModel := model.NewUserCompanyRoundOrderModel()
    _ = companyRoundOrderModel.DeleteRoundInfo(orderId, companyId)

    //删除装修公司申请补轮表
    companyRoundOrderApplyModel := model.NewUserCompanyRoundOrderApplyModel()
    _ = companyRoundOrderApplyModel.DeleteRoundApply(orderId, companyId)
}

// 老签返订单撤销逻辑
func (that *RecallLogic) RecallOldSignbackOrder(orderId string, companyId int, typeFw int) {
    companyPackageModel := model.NewCompanyPackageModel()
    packageInfo, err := companyPackageModel.GetCompanyPackage(companyId, typeFw)
    if err == nil && packageInfo.Id != 0 {
        // 订单包总数量+1
        // 如果订单包不是使用中状态改为使用中状态
        infoMapData := g.Map{
            "total_number": packageInfo.TotalNumber + 1,
            "use_status": enum.COMPANY_CODE_PACKAGE_USE_SYZ,
            "updated_at": time.Now().Unix(),
        }
        companyPackageInfoModel := model.NewCompanyPackageInfoModel()
        _ = companyPackageInfoModel.Update(packageInfo.InfoId, infoMapData)

        // 如果总包不是使用中状态改为使用中状态
        if packageInfo.UseStatus != enum.COMPANY_CODE_PACKAGE_USE_SYZ {
            packMapData := g.Map{
                "use_status": enum.COMPANY_CODE_PACKAGE_USE_SYZ,
                "updated_at": time.Now().Unix(),
            }
            _ = companyPackageModel.Update(packageInfo.Id, packMapData)
        }
    }

    // 删除装企订单包订单记录
    companyPackageOrderModel := model.NewCompanyPackageOrderModel()
    _ = companyPackageOrderModel.DeleteInfo(orderId, companyId)
}

// 删除订单分配信息
func (that *RecallLogic) DeleteOrderInfo(orderId string) error {
    orderInfoModel := model.NewOrderInfoModel()
    err := orderInfoModel.DeleteOrderInfo(orderId)
    return err
}

// 删除订单分配信息
func (that *RecallLogic) DeleteOrderCompanyReview(orderId string) error {
    orderCompanyReviewModel := model.NewOrderCompanyReviewModel()
    err := orderCompanyReviewModel.DeleteOrderCompanyReview(orderId)
    return err
}

