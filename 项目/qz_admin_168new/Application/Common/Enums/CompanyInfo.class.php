<?php

namespace Common\Enums;


class CompanyInfo
{
    /**
     * 此顺序不可能顺便改动
     * @var array
     */
    protected static $company_tags = [
        1 => '规模大',
        2 => '全国连锁',
        3 => '代办贷款服务',
        4 => '延长质保期',
        5 => '专车服务',
    ];

    /**
     * 获取装修公司标签
     * @param $key
     * @return array|mixed
     */
    public static function getCompanyTags($key)
    {
        if ($key) {
            if (is_array($key)) {
                $returnData = [];
                foreach ($key as $k => $v) {
                    $returnData[$v] = self::$company_tags[$v];
                }
                return $returnData;
            }
            return self::$company_tags[$key];
        } else {
            return self::$company_tags;
        }
    }
}