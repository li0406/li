<?php

/**
 * 报备相关状态码
 */

namespace app\index\enum;

class SalesReportCodeEnum
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

    const REPORT_IMG_BB = 1;//销售报备图
    const REPORT_IMG_PZ = 2;//客服上传凭证图

    // 合作类型
    const REPORT_COOPERATION_ZX = 1; // 装修会员
    const REPORT_COOPERATION_DJ = 2; // 独家会员
    const REPORT_COOPERATION_LD = 3; // 垄断会员
    const REPORT_COOPERATION_SWJ = 4; // 三维家会员
    const REPORT_COOPERATION_ERP = 5; // ERP会员
    const REPORT_COOPERATION_SBACK = 6; // 签单返点会员
    const REPORT_COOPERATION_QWU = 7; // 全屋定制会员
    const REPORT_COOPERATION_DELAY = 8; // 会员延期
    const REPORT_COOPERATION_NORMAL_STANDARD = 14;//1.0标杆会员
    const REPORT_COOPERATION_NEW_STANDARD = 15;//2.0标杆会员


    //合作类型
    private static $cooperation_type_code = [
        self::REPORT_COOPERATION_ZX => "装修会员",
        self::REPORT_COOPERATION_DJ => "独家会员",
        self::REPORT_COOPERATION_LD => "垄断会员",
        self::REPORT_COOPERATION_SWJ => "三维家会员",
        self::REPORT_COOPERATION_ERP => "ERP会员",
        self::REPORT_COOPERATION_SBACK => "签单返点会员",
        self::REPORT_COOPERATION_QWU => "全屋定制会员",
        self::REPORT_COOPERATION_DELAY => "会员延期",
        self::REPORT_COOPERATION_NORMAL_STANDARD => "常规标杆会员",
        self::REPORT_COOPERATION_NEW_STANDARD => "新签返标杆会员",
    ];

    // 合作类型
    private static $code_statistic_cooperation_type = [
        self::REPORT_COOPERATION_ZX => "装修",
        self::REPORT_COOPERATION_DJ => "独家",
        self::REPORT_COOPERATION_LD => "垄断",
        self::REPORT_COOPERATION_SWJ => "三维家",
        self::REPORT_COOPERATION_ERP => "ERP",
        self::REPORT_COOPERATION_SBACK => "签约返点",
        self::REPORT_COOPERATION_QWU => "全屋定制",
        self::REPORT_COOPERATION_DELAY => "会员延期",
        self::REPORT_COOPERATION_NORMAL_STANDARD => "1.0标杆会员",//1.0标杆会员
        self::REPORT_COOPERATION_NEW_STANDARD => "2.0标杆会员",//2.0标杆会员
    ];


    //会员状态
    private static $user_type_code = [
        1 => "新会员",
        2 => "老会员",
    ];

    private static $passage_position_code = [
        1 => "A通栏",
        2 => "B通栏",
        3 => "A+B通栏",
    ];
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

    private static $back_ratio = [
        5 => "5%",
        3 => "3%",
        2 => "2%",
        1 => "1%",
        0 => "0%"
    ];

    private static $year_month = [
        "2020/1/5至2020/2/3" => ["start" => "2020-01-05", "end" => "2020-02-03", "show" => 1],
        "2021/1/21至2021/2/20" => ["start" => "2021-01-21", "end" => "2021-02-20", "show" => 1],
        "2021/1/23至2021/2/21" => ["start" => "2021-01-23", "end" => "2021-02-21", "show" => 2],
        "2022/1/12至2022/2/10" => ["start" => "2022-01-12", "end" => "2022-02-10", "show" => 1],
        "2023/1/2至2023/1/31" => ["start" => "2023-01-02", "end" => "2023-01-31", "show" => 1],
        "2024/1/21至2024/2/19" => ["start" => "2024-01-21", "end" => "2024-02-19", "show" => 1],
    ];

    public static function getBackRatioList($type = 1){
        $list = array_values(static::$back_ratio);
        if ($type == 2) {
            array_pop($list);
        }

        return $list;
    }

    public static function getCooperationType($key)
    {
        return isset(self::$cooperation_type_code[$key]) ? self::$cooperation_type_code[$key] : '——';
    }

    public static function getCooperationTypeList() {
        $list = [];
        array_walk(static::$cooperation_type_code, function($item, $index) use (&$list) {
            $list[] = [
                "id" => $index,
                "name" => $item
            ];
        });
        
        return $list;
    }

    public static function getUserType($key)
    {
        return isset(self::$user_type_code[$key]) ? self::$user_type_code[$key] : '——';
    }

    public static function getReportStatus($key)
    {
        return isset(self::$report_status_code[$key]) ? self::$report_status_code[$key] : '——';
    }
    public static function getPassagePosition($key)
    {
        return isset(self::$passage_position_code[$key]) ? self::$passage_position_code[$key] : '——';
    }

    //主要审核能看到的报备状态
    public static function getCheckReportStatus()
    {
        return [
            self::REPORT_STATUS_TJ,
            self::REPORT_STATUS_SHTG,
            self::REPORT_STATUS_WTG,
            self::REPORT_STATUS_KFSHTG,
            self::REPORT_STATUS_KFWTG,
            self::REPORT_STATUS_KFWTG_CHECK,
            self::REPORT_STATUS_KFWC,
            self::REPORT_STATUS_KFSTOP
        ];
    }

    //客服能看到的报备状态
    public static function getKfReportStatus()
    {
        return [
            self::REPORT_STATUS_SHTG,
            self::REPORT_STATUS_KFSHTG,
            self::REPORT_STATUS_KFWTG,
            self::REPORT_STATUS_KFWTG_CHECK,
            self::REPORT_STATUS_KFWC,
            self::REPORT_STATUS_KFSTOP
        ];
    }

    // 可驳回的报备状态
    public static function getRecallStatus(){
        return [
            self::REPORT_STATUS_SHTG,
            self::REPORT_STATUS_KFSHTG,
            self::REPORT_STATUS_KFWC,
        ];
    }

    //获取全部状态
    public static function getAllStatus()
    {
        return [
            self::REPORT_STATUS_BC,
            self::REPORT_STATUS_TJ,
            self::REPORT_STATUS_SHTG,
            self::REPORT_STATUS_WTG,
            self::REPORT_STATUS_KFSHTG,
            self::REPORT_STATUS_KFWTG,
            self::REPORT_STATUS_KFWTG_CHECK,
            self::REPORT_STATUS_KFWC,
            self::REPORT_STATUS_KFSTOP,
            self::REPORT_STATUS_KFWAIT,
            self::REPORT_STATUS_KFWCUPLOAD,
        ];
    }

    // 可被小报备关联的状态
    public static function getPyamentRelatedStatus(){
        return [
            self::REPORT_STATUS_BC,
            self::REPORT_STATUS_WTG,
            self::REPORT_STATUS_KFWTG_CHECK,
            self::REPORT_STATUS_KFWAIT,
            self::REPORT_STATUS_KFWCUPLOAD
        ];
    }

    // 小报备不能重新关联的状态
    public static function getLockRelatedStatus(){
        return [
            self::REPORT_STATUS_SHTG,
            self::REPORT_STATUS_KFSHTG,
            self::REPORT_STATUS_KFWC
        ];
    }

    // 所有合作类型
	public static function getCooperationAll(){
        return array_keys(static::$cooperation_type_code);
    }

    // 装修合作类型
    public static function getCooperationZx($signback = true){
        $list = [
            static::REPORT_COOPERATION_ZX,
            static::REPORT_COOPERATION_DJ,
            static::REPORT_COOPERATION_LD,
            static::REPORT_COOPERATION_QWU,
            static::REPORT_COOPERATION_NORMAL_STANDARD,
            static::REPORT_COOPERATION_NEW_STANDARD,

        ];

        if ($signback == true) {
            $list[] = static::REPORT_COOPERATION_SBACK;
        }

        return $list;
    }

    // 计算异常的报备
    public static function getCooperationAbnormal(){
        return [
            static::REPORT_COOPERATION_ZX,
            static::REPORT_COOPERATION_DJ,
            static::REPORT_COOPERATION_LD,
        ];
    }

    // 计算1.0会员日消耗的报备类型
    public static function getCompanyExpendType() {
        return [
            static::REPORT_COOPERATION_ZX,
            static::REPORT_COOPERATION_DJ,
            static::REPORT_COOPERATION_LD,
            static::REPORT_COOPERATION_DELAY,
        ];
    }

    public static function getYearMonth($key) {
        if(empty($key)){
            return '';
        }
        
        return self::$year_month[$key] ?? "";
    }

    // 过年月列表
    public static function getYearMonthList(){
        $list = [];
        foreach (static::$year_month as $key => $item) {
            if ($item["show"] == 1) {
                $list[] = [
                    "start" => $item["start"],
                    "end" => $item["end"],
                    "value" => $key
                ];
            }
        }

        return $list;
    }

    // 获取会员倍数和返点比例显示类型
    public static function getViptypeBackRatioShow($cooperation_type, $company_mode = 0){
        $show = 1; // 默认显示会员倍数
        if ($cooperation_type == static::REPORT_COOPERATION_SBACK || $cooperation_type == static::REPORT_COOPERATION_NEW_STANDARD) {
            $show = 2; // 显示返点比例
        } else if ($cooperation_type == static::REPORT_COOPERATION_ERP) {
            $show = 0; // 都不显示
        } else if ($cooperation_type == static::REPORT_COOPERATION_DELAY) {
            $modeList = [
                CompanyCodeEnum::COMPANY_MODE_SBACK,
                CompanyCodeEnum::COMPANY_MODE_OBACK
            ];

            if (in_array($company_mode, $modeList)) {
                $show = 2;
            }
        }

        return $show;
    }

    /**
     * 获取状态值
     * @Author   liuyupeng
     * @DateTime 2019-05-20T15:28:28+0800
     */
    public static function getItem($module, $code, $default = ''){
        $key = "code_" . strtolower($module);
        $code = strtolower($code);

        if (isset(static::$$key)) {
            $modules = static::$$key;
            if (array_key_exists($code, $modules)) {
                return $modules[$code];
            }
        }

        return $default;
    }
}