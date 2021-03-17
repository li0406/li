<?php

namespace Common\Enums;

class MsgTemplateCodeEnum {

    // 订单相关
    const ORDER_NEW = "202009161039"; // 新订单提醒

    // 补轮审核
    const ROUND_APPLY_CHECK_PASS = "202009141026"; // 补轮审核通过
    const ROUND_APPLY_CHECK_FAIL = "202009141027"; // 补轮审核不通过
    
    // 公司账户信息
    const COMPANY_ACCOUNT_RECHARGE = "202009141028"; // 轮单费到帐提醒
    const COMPANY_DEPOSIT_RECHARGE = "202009141029"; // 保证金到账提醒
    const COMPANY_ACCOUNT_DEDUCTION = "202009141031"; // 轮单费扣费提醒

    // 订单回访
    const ORDER_REVIEW_PUSH = "202009141030"; // 订单回访提醒

    // 审核相关
    const EXAM_CASE_FAIL = "202009141032"; // 案例审核不通过提醒
    const EXAM_XIAOGUOTU_3D_FAIL = "202009141033"; // 3D效果图审核提示
    const EXAM_COMPANY_BANNER_FAIL = "202009141034"; // 店铺轮播图审核提示

    // 短信通知相关
    const MSG_BEFORE_CALL = "202012091051"; // 打电话前通知
    const MSG_ORDER_HAS_CHECK = '202012091052'; //订单分配通知
}