<?php
namespace app\index\enum;

class CompanyCodeEnum
{

    // 用户类型
    const USER_CLASSID_ZX = 3; // 常规
    const USER_CLASSID_SIGNBACK = 6; // 老签返

    // 合作模式
    const USER_COOPERATE_MODE_ZX = 1; // 常规
    const USER_COOPERATE_MODE_SIGNBACK = 2; // 新签返
    const USER_COOPERATE_MODE_ZX_BIAOGAN = 3; //常规标杆
    const USER_COOPERATE_MODE_SIGNBACK_BIAOGAN = 4; //新签返标杆

    // 公司合作类型
    const COMPANY_MODE_ZX = 1; // 常规会员
    const COMPANY_MODE_SBACK = 2; // 新签返会员
    const COMPANY_MODE_OBACK = 3; // 老签返会员

    // 会员状态
    const USER_ON_VIP = 2; // 有效会员
    const USER_ON_STOP = -4; // 暂停会员
    const USER_ON_EXPIRE = -1; // 过期会员

    // 公司合作类型
    private static $code_company_mode = [
        self::COMPANY_MODE_ZX => "常规会员",
        self::COMPANY_MODE_SBACK => "签返会员",
        self::COMPANY_MODE_OBACK => "签返会员",
    ];

    private static $code_cooperate_mode = [
        self::USER_COOPERATE_MODE_ZX => "常规会员",
        self::USER_COOPERATE_MODE_SIGNBACK => "新签返会员",
        self::USER_COOPERATE_MODE_ZX_BIAOGAN => "常规标杆会员",
        self::USER_COOPERATE_MODE_SIGNBACK_BIAOGAN => "新签返标杆会员"
    ];

    // 公司合作类型
    private static $code_user_on = [
        self::USER_ON_VIP => "有效会员",
        self::USER_ON_STOP => "暂停会员",
        self::USER_ON_EXPIRE => "过期会员",
    ];

    protected static $companyIntention = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
    ];

    protected static $companySource = [
        1 => '已签会员',
        2 => '客户咨询转接',
        3 => '会员转介绍',
        4 => '后台注册公司',
        5 => '扫楼',
        6 => '同行网站',
        7 => '订单轰炸',
        8 => '空间营销',
        9 => '其他',
        10 => '合作页面',
    ];

    protected static $code_lx = [
        1 => '家装',
        2 => '公装',
        3 => '家装/公装'
    ];

    protected static $code_lxs = [
        1 => '新房',
        2 => '旧房',
        3 => '新房/旧房'
    ];

    protected static $code_contract = [
        1 => '半包',
        2 => '全包',
        3 => '半包/全包'
    ];

	static public function getCompanyIntention($key){
        $status = self::$companyIntention;
        if (isset($status[$key])) {
            return $status[$key];
        }
        return '';
    }

    static public function getCompanySource($key){
        $status = self::$companySource;
        if (isset($status[$key])) {
            return $status[$key];
        }
        return '';
    }

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

    // 获取公司类型
    public static function getCompanyMode($classid, $cooperate_mode){
        if ($classid == static::USER_CLASSID_SIGNBACK) {
            $company_mode = static::COMPANY_MODE_OBACK; // 老签返
        } else if ($cooperate_mode == static::USER_COOPERATE_MODE_SIGNBACK) {
            $company_mode = static::COMPANY_MODE_SBACK; // 新签返
        } else {
            $company_mode = static::COMPANY_MODE_ZX; // 常规会员
        }

        return $company_mode;
    }

}
