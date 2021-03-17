<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/9/7
 * Time: 14:01
 */
namespace Common\Enums;

class OrderSource
{
    const QIZUANG = 2;//齐装来源
    const OTHER = 3;//其他(营销宝来源)

    protected static $allSource = [
        2 => '齐装',
        3 => '其他',
    ];

    public static function getAllSource()
    {
        return self::$allSource;
    }

    public static function getSourceName($key){
        $status = self::$allSource;
        if(isset($status[$key])){
            return $status[$key];
        }
        return '';
    }
    public static function getOrderSource($key){
        return D('Home/Db/YxbOrderSource')->getOrderSource($key);
    }
}