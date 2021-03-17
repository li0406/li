<?php
/**
 * 装修公司
 */

namespace Common\Enums;

class CompanyCodeEnum
{
    const COMPANY_LX_JIA = 1;//家装
    const COMPANY_LX_GONG = 2;//公装
    const COMPANY_LX_ALL = 3;//家装公装

    const CONTRACT_ALL = 3;//不限
    const CONTRACT_BAN = 1;//半包
    const CONTRACT_QUAN = 2;//全包

    // 默认头像
    const COMPANY_DEFAULT_LOGO = "Public/default/images/default_logo.png";
    const EMPLOYEE_DEFAULT_LOGO = "public/default/images/default_avator.jpg";

    //装修类型
    private static $company_lx = array(
        1 => "家装",
        2 => "公装",
        3 => "家装公装",
    );

    //施工方式
    private static $contract_type = array(
        3 => "不限",
        1 => "半包",
        2 => "全包",
    );

    //承接价位
    private static $jiawei = array(
        0 => ['name' => "不限", 'min' => 0, 'max' => 0],
        1 => ['name' => "3万以下", 'min' => 0, 'max' => 3],
        2 => ['name' => "3~5万", 'min' => 3, 'max' => 5],
        3 => ['name' => "5~8万", 'min' => 5, 'max' => 8],
        4 => ['name' => "8~10万", 'min' => 8, 'max' => 10],
        5 => ['name' => "10~15万", 'min' => 10, 'max' => 15],
        6 => ['name' => "15~20万", 'min' => 15, 'max' => 20],
        7 => ['name' => "30万以上", 'min' => 30, 'max' => 0],
    );

    // 获取装修类型
    public static function getCompanyLx($lx_id = "")
    {
        if (!empty($lx_id)) {
            return self::$company_lx[$lx_id];
        }
        return self::$company_lx;
    }

    // 获施工方式
    public static function getCompanyContract($type = "")
    {
        if (!empty($type)) {
            return self::$contract_type[$type];
        }
        return self::$contract_type;
    }
    //获取承接价位
    public static function getJiawei($jiawei_id = "")
    {
        if (!empty($jiawei_id) && $jiawei_id != 0) {
            return self::$jiawei[$jiawei_id];
        }
        return self::$jiawei;
    }
}