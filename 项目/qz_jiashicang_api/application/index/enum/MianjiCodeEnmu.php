<?php
namespace app\index\enum;
/**
 *
 */
class MianjiCodeEnmu
{
    const MIANJI_1 = "40㎡以下";
    const MIANJI_2 = "40㎡-60㎡";
    const MIANJI_3 = "60㎡-80㎡";
    const MIANJI_4 = "80㎡-100㎡";
    const MIANJI_5 = "100㎡-120㎡";
    const MIANJI_6 = "120㎡-150㎡";
    const MIANJI_7 = "150㎡-200㎡";
    const MIANJI_8 = "200㎡以上";
    const MIANJI_99 = "其他";


    public static function getMianji($key)
    {
        $objClass = new \ReflectionClass(__CLASS__);
        // 此处获取类中定义的全部常量 返回的是 [key=>value,...] 的数组
        // key是常量名 value是常量值
        $arrConst = $objClass->getConstants();
        foreach ($arrConst as $k => $val)
        {
            if ("MIANJI_".$key == $k) {
                return $val;
            }
        }
        return "";
    }
}