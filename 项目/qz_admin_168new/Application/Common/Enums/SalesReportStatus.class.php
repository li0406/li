<?php
/**
 * 会员报备状态
 */

namespace Common\Enums;

class SalesReportStatus
{
    const REPORT_STATUS_BC = 1;//待提交
    const REPORT_STATUS_TJ = 2;//待审核
    const REPORT_STATUS_SHTG = 3;//审核通过
    const REPORT_STATUS_WTG = 4;//未通过
    const REPORT_STATUS_KFSHTG = 5;//客服审核通过
    const REPORT_STATUS_KFWTG = 6;//客服未通过，普通信息更改
    const REPORT_STATUS_KFWTG_CHECK = 7;//客服未通过，需要重新审核
    const REPORT_STATUS_KFWC = 8;//客服完成上传
    const REPORT_STATUS_KFSTOP = 9;//客服暂停
    const REPORT_STATUS_KFWAIT = 10;//待客服补充
    const REPORT_STATUS_KFWCUPLOAD = 11;//客服补充完成

    //报备状态
    private static $report_status_code = [
        self::REPORT_STATUS_BC => "待提交",
        self::REPORT_STATUS_TJ => "待审核",
        self::REPORT_STATUS_SHTG => "审核通过/待客服上传",
        self::REPORT_STATUS_WTG => "未通过",
        self::REPORT_STATUS_KFSHTG => "客服审核通过",
        self::REPORT_STATUS_KFWTG => "客服未通过，普通信息更改",
        self::REPORT_STATUS_KFWTG_CHECK => "客服未通过，需要重新审核",
        self::REPORT_STATUS_KFWC => "客服完成上传",
        self::REPORT_STATUS_KFSTOP => "客服暂停",
        self::REPORT_STATUS_KFWAIT => "待客服补充",
        self::REPORT_STATUS_KFWCUPLOAD => "客服补充完成",
    ];

    public static function getReportStatus($key)
    {
        return isset(self::$report_status_code[$key]) ? self::$report_status_code[$key] : '——';
    }
}