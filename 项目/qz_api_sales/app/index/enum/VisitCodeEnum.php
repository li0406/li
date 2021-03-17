<?php
namespace app\index\enum;

class VisitCodeEnum
{
    protected static $visitStyle = [
        1 => '上门',
        2 => '电话',
        3 => 'QQ',
        4 => '微信',
    ];

    protected static $visitStatus = [
        1 => '未签约',
        2 => '已签约',
        3 => '本周内',
        4 => '半月内',
        5 => '1个月内',
        6 => '3个月内',
        7 => '暂无意向',
    ];

	static public function getVisitStyle($key){
        $status = self::$visitStyle;
        if (isset($status[$key])) {
            return $status[$key];
        }
        return '';
    }

    static public function getVisitStatus($key){
        $status = self::$visitStatus;
        if (isset($status[$key])) {
            return $status[$key];
        }
        return '';
    }
}
