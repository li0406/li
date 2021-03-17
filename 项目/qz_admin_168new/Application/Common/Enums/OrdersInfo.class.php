<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Common\Enums;


class OrdersInfo
{
    /**
     * 此顺序不可能顺便改动
     * @var array
     */
    protected static $sourceIn = array(
        "0" => "-请选择-",
        "4" => "业主发布",
        "1" => "在线客服",
        "2" => "400电话",
        "3" => "QQ咨询",
        "10" => "微信咨询",
        "11" => "推广部",
        "5" => "赠送单生成",
        "100" => "非业主发布"
    );

    protected static $lx = array(
        "1" => "家装",
        "2" => "公装",
    );

    protected static $lxs = array(
        "1" => "新房装修",
        "2" => "旧房装修",
        "3" => "旧房改造"
    );

    protected static $keys = array(
        "1" => "有钥匙",
        "0" => "无钥匙",
        "3" => "其他"
    );

    protected static $type_fw = array(
        "1" => "分单",
        "2" => "赠单",
    );

    /**
     * 部门 师
     * @var array
     */
    protected static $shi = array(
        0 => "-",
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10
    );

    /**
     * 量房时间
     * @var array
     */
    protected static $lf_time = array(
        "随时" => "随时",
        "今天" => "今天",
        "1周内" => "1周内",
        "2周内" => "2周内",
        "1个月内" => "1个月内",
        "2个月内" => "2个月内",
        "3个月内" => "3个月内",
        "3个月以上" => "3个月以上",
        "周末" => "周末",
        "拿房后" => "拿房后",
        "电话预约后" => "电话预约后",
        "看户型图后" => "看户型图后",
        "去实体店后" => "去实体店后"
    );

    /**
     * 开工时间
     * @var array
     */
    protected static $start_time = array(
        "1个月内开工" => "1个月内开工",
        "2个月内开工" => "2个月内开工",
        "3个月内开工" => "3个月内开工",
        "4个月内开工" => "4个月内开工",
        "5个月内开工" => "5个月内开工",
        "6个月内开工" => "6个月内开工",
        "6个月以上开工" => "6个月以上开工",
        "方案满意开工" => "方案满意开工",
        "满意拿房后开工" => "满意拿房后开工",
        "面议" => "面议"
    );

    protected static $qiandan_status = array(
        0 => '申请签单确认',
        1 => '确认签单',
        2 => '继续跟踪'
    );

    /**
     * @return array
     */
    public static function getSourceIn()
    {
        return self::$sourceIn;
    }

    public static function getLx()
    {
        return self::$lx;
    }

    public static function getLxs()
    {
        return self::$lxs;
    }

    public static function getKeys()
    {
        return self::$keys;
    }

    public static function getShi()
    {
        return self::$shi;
    }

    public static function getLfTime()
    {
        return self::$lf_time;
    }

    public static function getStartTime()
    {
        return self::$start_time;
    }

    public static function getQiandanStatus($key)
    {
        return self::$qiandan_status[$key];
    }


    public static function getItem($field, $key, $default = ""){
        if (isset(static::$$field)) {
            $variable = static::$$field;
            if (array_key_exists($key, $variable)) {
                $default = $variable[$key];
            }
        }

        return $default;
    }
}