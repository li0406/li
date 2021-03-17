<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Common\Enums;


class LiangFangInfo
{
    /**
     * 此顺序不可能顺便改动
     * @var array
     */
    protected static $falseReason = [
        1 => '业主无法联系',
        2 => '业主无装修需求',
        3 => '业主已经签约',
        4 => '业主无法量房',
        5 => '业主仅咨询了解',
        6 => '业主有户型图'
    ];

    protected static $backStatus = [
        1 => '拒接',
        2 => '无人接听',
        3 => '审核一半挂机',
        4 => '通话中',
        5 => '关机',
        6 => '停机',
        7 => '过段时间联系',
        8 => '等会联系',
        9 => '其他'
    ];

    /**
     * 回访状态
     * @var array
     */
    protected static $back_status = array(
        "1" => "已量房",
        "2" => "已签约",
        "3" => "回访成功",
        "4" => "回访失败",
        "5" => "无效",
    );

    /**
     * 回访备注(失败)
     * @var array
     */
    protected static $back_remark_bust = array(
        "1" => "拒接",
        "2" => "无人接听",
        "3" => "核实一半挂机",
        "4" => "通话中",
        "5" => "关机",
        "6" => "停机",
        "7" => "过段时间联系",
        "8" => "等会联系",
        "9" => "其他",
    );

    /**
     * 回访备注(无效)
     * @var array
     */
    protected static $back_remark_false = array(
        "10" => "已定好其他装修公司",
        "11" => "业主不同意再次量房",
        "12" => "近期不装修",
        "9" => "其他",
    );

    protected static $company_lf_stats = array(
        1 => "已量房",
        2 => "已见面/已到店",
        3 => "未量房",
        4 => "已签约",
        0 => "",
    );

    /**
     * 获取全部量房失败原因
     * author: mcj
     * @return array
     */
    public static function getAllFalseReason(){
        return self::$falseReason;
    }

    /**
     * 获取量房失败原因
     * author: mcj
     * @param $key
     * @return mixed
     */
    public static function getFalseReason($key){
        return self::$falseReason[$key];
    }

    /**
     * 获取回访备注
     * @return array
     */
    public static function getBackRemark(){
        return self::$falseReason;
    }

    public static function getBackStatus()
    {
        return self::$back_status;
    }
    public static function getBackRemarkBust()
    {
        return self::$back_remark_bust;
    }
    public static function getBackRemarkFalse()
    {
        return self::$back_remark_false;
    }

    /**
     * 装修公司量房状态
     * @param $key
     * @return string
     */
    public static function getCompanyLfStats($key){
        return self::$company_lf_stats[$key];
    }

}