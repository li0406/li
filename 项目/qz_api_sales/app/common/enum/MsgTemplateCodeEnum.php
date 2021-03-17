<?php

namespace app\common\enum;

class MsgTemplateCodeEnum {

    // 订单相关
    const ORDER_NEW = "202009161039"; // 新订单提醒

    // 签单审核
    const QIANDAN_CHECK_PASS = "202009161038"; // 签单审核通过
    const QIANDAN_CHECK_FAIL = "202009161037"; // 签单审核不通过
    
    // 订单回访
    const ORDER_REVIEW_PUSH = "202009141030"; // 订单回访提醒
    
}