package logic

import (
    "github.com/gogf/gf/frame/g"
    "qzorder/app/enum"
    "qzorder/app/model"
)

type OrderLogic struct {
}

func NewOrderLogic() *OrderLogic {
    return &OrderLogic{}
}

// 获取订单信息
func (that *OrderLogic) GetOrderDetail(orderId string) (model.OrderDetailStruct, error) {
    ordersModel := model.NewOrdersModel()
    detail,err := ordersModel.GetOrderDetail(orderId)

    return detail,err
}

// 获取订单分配记录
func (that *OrderLogic) GetOrderInfoList(orderId string) ([]model.OrderAllotStruct, error) {
    orderInfoModel := model.NewOrderInfoModel()
    orderAllotList,err := orderInfoModel.GetOrderAllotList(orderId)
    if err == nil {
        for key,item := range orderAllotList {
            if item.PackageOrderId != 0 {  // 老签返
                orderAllotList[key].CompanyType = enum.COMPANY_CODE_OLD_SIGNBACK
            } else if item.RoundOrderId != 0 { // 新签返
                orderAllotList[key].CompanyType = enum.COMPANY_CODE_SIGNBACK
            } else { // 常规会员
                orderAllotList[key].CompanyType = enum.COMPANY_CODE_DEFAULT
            }
        }
    }

    return orderAllotList,err
}

// 设置订单状态为无效
func (that *OrderLogic) SetOrderInvalid(orderId string) error {
    data := g.Map{
        "on": enum.ORDER_CODE_INVALID,
        "fen_customer": 0,
    }

    ordersModel := model.NewOrdersModel()
    err := ordersModel.Update(orderId, data)
    return err
}