package v1

import (
	"github.com/gogf/gf/frame/gmvc"
	"qzorder/app/enum"
	"qzorder/app/logic"
	"qzorder/library/util"
)

type RecallController struct {
	gmvc.Controller
}

// 订单撤销操作
func (that *RecallController) SetOrderRecall () {
	orderId := that.Request.GetString("order_id")
	operator := that.Request.GetInt("operator")
	if orderId == "" {
		ret,_ := util.EncodeResponseJson(4000001, enum.CODE_4000001, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	orderLogic := logic.NewOrderLogic()
	orderDetail,err := orderLogic.GetOrderDetail(orderId)
	if err != nil { // 订单不存在
		ret,_ := util.EncodeResponseJson(6000001, enum.CODE_6000001, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	} else if orderDetail.On != enum.ORDER_CODE_VALID { // 订单不是有效单
		ret,_ := util.EncodeResponseJson(6000002, enum.CODE_6000002, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	} else if orderDetail.QiandanCompanyId != 0 { // 已签单不能撤销
		errmsg := enum.CODE_6000003
		if orderDetail.QiandanStatus == 1 {
			errmsg = enum.CODE_6000004
		}

		ret,_ := util.EncodeResponseJson(6000002, errmsg, nil)
		_ = that.Request.Response.WriteJson(ret)
		that.Exit()
	}

	// 实例化订单撤销逻辑层
	recallLogic := logic.NewRecallLogic()

	// 查询订单分配信息
	orderInfoList,_ := orderLogic.GetOrderInfoList(orderId)
	if length := len(orderInfoList); length > 0 {
		// 订单分配信息验证
		errcode,errmsg := recallLogic.CheckOrderInfoRecall(orderInfoList)
		if errcode != 0 {
			ret,_ := util.EncodeResponseJson(errcode, errmsg, nil)
			_ = that.Request.Response.WriteJson(ret)
			that.Exit()
		}

		for _,item := range orderInfoList {
			switch item.CompanyType {
				case enum.COMPANY_CODE_DEFAULT:
					// 常规会员撤销处理逻辑
					recallLogic.RecallDefaultOrder(orderId, item.CompanyId)
				case enum.COMPANY_CODE_SIGNBACK:
					// 新签返会员撤销处理逻辑
					recallLogic.RecallSignbackOrder(orderId, item.CompanyId, operator)
				case enum.COMPANY_CODE_OLD_SIGNBACK:
					// 老签返会员撤销处理逻辑
					recallLogic.RecallOldSignbackOrder(orderId, item.CompanyId, item.TypeFw)
			}
		}
	}

	// 删除订单分配信息和装修公司订单跟踪信息
	_ = recallLogic.DeleteOrderInfo(orderId)
	_ = recallLogic.DeleteOrderCompanyReview(orderId)

	// 订单状态改为无效
	_ = orderLogic.SetOrderInvalid(orderId)

	ret,_ := util.EncodeResponseJson(0, enum.CODE_0, nil)
	_ = that.Request.Response.WriteJson(ret)
	that.Exit()
}
