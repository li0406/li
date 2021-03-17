<?php
// +----------------------------------------------------------------------
// | CompanyCodeEnum 装修公司相关枚举值定义
// +----------------------------------------------------------------------

namespace app\index\enum;

use app\common\enum\BaseStatusCodeEnum;

class CompanyCodeEnum extends BaseStatusCodeEnum {

    // 检索最大数量
    const SEARCH_LIMIT = 20;

    // 会员状态
    const USER_ON_EXPIRE = -1; // 过期会员
    const USER_ON_VALID = 2; // 有效会员
    const USER_ON_STOP = -4; // 暂停会员

    // 合作模式
    const COOPERATE_MODE_ZX = 1; // 装修会员
    const COOPERATE_MODE_SIGNBACK = 2; // 签返会员

    // 会员状态
    protected static $user_on = [
        self::USER_ON_EXPIRE => "过期会员",
        self::USER_ON_VALID => "有效会员",
        self::USER_ON_STOP => "暂停会员",
    ];

    // 会员状态
    protected static $cooperate_mode = [
        self::COOPERATE_MODE_ZX => "装修会员",
        self::COOPERATE_MODE_SIGNBACK => "签返会员",
    ];
}