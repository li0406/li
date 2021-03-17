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

}