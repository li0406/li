export const orderStatus = value => {
  switch (value) {
    case '1':
      return '待付款'
    case '2':
      return '待发货'
    case '3':
      return '待收货'
    case '4':
      return '已收货'
    case '5':
      return '已完成'
    case '6':
      return '退款中'
    case '7':
      return '退款完成'
    case '8':
      return '已取消'
    case '9':
      return '待退货'
    case '10':
      return '已退货'
    case '11':
      return '退款失败'
    default:
      return '----'
  }
}

export const settleStatus = value => {
  switch (value) {
    case '1':
      return '未结算'
    case '2':
      return '已结算'
    case '0':
      return '默认'
    default:
      return '----'
  }
}
export const businessType = value => {
  switch (value) {
    case '1':
      return '百度货款'
    case '2':
      return '装企购买'
    case '3':
      return '装企提现'
    case '4':
      return '用户购买'
    case '5':
      return '百度退回'
    case '6':
      return '装企退回'
    default:
      return '----'
  }
}
