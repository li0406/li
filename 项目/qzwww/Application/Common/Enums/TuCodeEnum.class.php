<?php
/**
 * 美图
 */

namespace Common\Enums;

class TuCodeEnum
{
    const TU_LX_GONG = 1;//公装
    const TU_LX_JIA = 2;//家装
    const TU_LX_GONG_TWO_RM = 3;//热门
    const TU_LX_GONG_TWO_GY = 4;//公用
    const TU_LX_GONG_TWO_SH = 5;//生活
    const TU_LX_GONG_TWO_SY = 6;//商用
    const TU_LX_JIA_KJ = 7;//空间
    const TU_LX_JIA_JB = 8;//局部
    const TU_LX_JIA_FG = 9;//风格
    const TU_LX_JIA_HX = 10;//户型

    //家装/公装二级分类
    private static $category = array(
        self::TU_LX_GONG => [
            self::TU_LX_GONG_TWO_RM,
            self::TU_LX_GONG_TWO_GY,
            self::TU_LX_GONG_TWO_SH,
            self::TU_LX_GONG_TWO_SY,
        ],
        self::TU_LX_JIA => [
            self::TU_LX_JIA_KJ,
            self::TU_LX_JIA_JB,
            self::TU_LX_JIA_FG,
            self::TU_LX_JIA_HX,
        ],
    );


    //获取承接价位
    public static function getCategory($top= "")
    {
        if (!empty($top)) {
            return self::$category[$top];
        }
        return self::$category;
    }
}