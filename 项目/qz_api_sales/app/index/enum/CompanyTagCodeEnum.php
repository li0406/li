<?php

namespace app\index\enum;

class CompanyTagCodeEnum
{
    protected static $company_fixation_tag = [
        'jz' => [
            '别墅装修', '旧房改造', '小户型装修', '新房装修', '二手房装修', '婚房装修'
        ],
        'gz' => [
            '办公室装修', '写字楼装修', 'ktv装修', '饭店装修', '店面装修', '酒店装修', '服装店装修', '会所装修', '茶楼装修',
            '酒楼装修', '展厅装修', '美发店装修', '美容院装修', '咖啡厅装修', '火锅店装修', '宾馆装修', '商场装修', '超市装修',
            '厂房装修', '健身房装修', '公寓装修', '餐厅装修'
        ],
        'sg' => [
            '半包装修', '全包装修', '水电装修', '局部装修'
        ],
    ];

    public static function getCompanyFixationTag()
    {
        return self::$company_fixation_tag;
    }
}
