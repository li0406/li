<?php
// +----------------------------------------------------------------------
// | RoundOrderCodeEnum 轮单相关枚举值定义
// +----------------------------------------------------------------------

namespace app\index\enum;

use app\common\enum\BaseStatusCodeEnum;

class RoundOrderCodeEnum extends BaseStatusCodeEnum {

    // 审核状态
    const EXAM_STATUS_APPLY = 1; // 确认中
    const EXAM_STATUS_PASS = 2; // 已通过
    const EXAM_STATUS_FAIL = 3; // 未通过
    const EXAM_STATUS_CHECK = 11; // 待定单

    // 审核状态
    protected static $exam_status = array(
        self::EXAM_STATUS_APPLY => "预处理",
        self::EXAM_STATUS_CHECK => "待定单",
        self::EXAM_STATUS_PASS => "已通过",
        self::EXAM_STATUS_FAIL => "未通过"
    );

    // 会员状态
    protected static $apply_reason = [
        1 => "业主75天内无法量房",
        2 => "业主表示不装修了",
        3 => "业主已动工",
        4 => "同行、中介等无装修意向",
        99 => "其它",
    ];

}