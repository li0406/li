<?php

namespace app\index\enum;

class MonthEnmu
{
    private static  $month = [
        "01" => "Jan",
        "02" => "Feb",
        "03" => "Mar",
        "04" => "Apr",
        "05" => "May",
        "06" => "Jun",
        "07" => "Jul",
        "08" => "Aug",
        "09" => "Sep",
        "10" => "Oct",
        "11" => "Nov",
        "12" => "Dec"
    ];

    public static function ConvertToShort($value='')
    {
       if (!array_key_exists($value,self::$month)) {
           return "";
       }
       return self::$month[$value];
    }

}