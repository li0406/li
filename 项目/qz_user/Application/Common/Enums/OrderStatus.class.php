<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 9:11
 */
namespace Common\Enums;

class OrderStatus
{
    const ORDER_DEFAULT = 0;//默认状态
    const HAS_LIANG_FANG = 1;//已量房
    const HAS_MEET = 2;//已经见面
    const NOT_LIANG_FANG = 3;//未量房
    const HAS_SIGN = 4;//已签约

    protected static $allStatus = [
        1 => '已量房',
        2 => '已经见面',
        3 => '未量房',
        4 => '已签约',
    ];

    public static function getAllStatus(){
        $status = self::$allStatus;
        $status[0] = '默认状态';
        return $status;
    }

    public static function getChangeAbledStatus(){
        return self::$allStatus;
    }
}