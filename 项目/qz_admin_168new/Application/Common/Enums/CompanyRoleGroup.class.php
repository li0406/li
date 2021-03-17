<?php
namespace Common\Enums;
/**
 *
 */
class CompanyRoleGroup
{
    const CODE_0 = "超管";
    const CODE_1 = "客服类";
    const CODE_2 = "派单类";
    const CODE_3 = "设计类";
    const CODE_4 = "网店类";


    public static   function  getRoleGroup ($key) {
        $objClass = new \ReflectionClass(__CLASS__);
        $arrConst = $objClass->getConstants();
        if (isset($arrConst["CODE_".$key])) {
            return $arrConst["CODE_".$key];
        }
        return "";
    }
}