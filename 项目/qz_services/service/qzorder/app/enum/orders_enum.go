package enum

const ORDER_CODE_VALID = 4 // 订单有效状态
const ORDER_CODE_INVALID = 99 // 订单无效状态
const ORDER_CODE_TYPE_FW_FEN = 1 // 订单分单状态
const ORDER_CODE_TYPE_FW_ZEN = 2 // 订单赠单状态

// 订单分配方式
const ORDER_CODE_ALLOT_SOURCE_ROB = 3 // 抢单

// 签单轮单方式
const ORDER_CODE_ALLOT_TYPE_DEFAULT = 1 // 常规轮单（扣费）
const ORDER_CODE_ALLOT_TYPE_BULUN = 2 // 补轮单（扣次数）

