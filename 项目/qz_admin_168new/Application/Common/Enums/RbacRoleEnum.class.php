<?php

namespace Common\Enums;

class RbacRoleEnum {

    const ROLE_ADMIN = 1; // 超级管理员

    // 客服角色
    const ROLE_KF = 2; // 客服
    const ROLE_KFZG = 30; // 客服主管
    const ROLE_KFZZ = 31; // 客服组长
    const ROLE_KFZJ = 33; // 客服总监
    const ROLE_KFJL = 63; // 客服经理
    const ROLE_ZXKF = 81; // 在线客服
    const ROLE_LZKF = 84; // 离职客服
    const ROLE_DJKF = 97; // 对接客服
    const ROLE_DJKFTZ = 104; // 对接客服团长

    // 装中客服角色
    const ROLE_ZZKF_KF = 154; // 装中客服
    const ROLE_ZZKF_ZZ = 155; // 装中组长
    const ROLE_ZZKF_JL = 156; // 装中经理
    const ROLE_ZZKF_ZG = 158; // 装中主管

    // 质检角色
    const ROLE_ZJZG = 66; // 质检主管
    const ROLE_ZJZZ = 99; // 质检组长

    // 产品技术
    const ROLE_CPJL = 51; // 产品经理

    // 渠道部
    const ROLE_QDZY = 88; // 渠道专员

    // 新开站提醒角色ID（质检主管、在线客服、对接客服、质检组长、对接客服团长、产品经理）
    public static function getNewOpenCityNoticeRoles(){
        return [
            static::ROLE_ZJZG,
            static::ROLE_ZXKF,
            static::ROLE_DJKF,
            static::ROLE_ZJZZ,
            static::ROLE_DJKFTZ,
            static::ROLE_CPJL,
        ];
    }

    // 新开站提醒客服角色ID（客服总监、客服经理、客服主管、客服团长[客服组长]）
    public static function getNewOpenCityNoticeKfRoles(){
        return [
            static::ROLE_KFZJ,
            static::ROLE_KFJL,
            static::ROLE_KFZG,
            static::ROLE_KFZZ,
        ];
    }

    // 获取客服角色ID
    public static function getKfRoles($lz = false){
        $list = [
            self::ROLE_KF
        ];

        if ($lz == true) {
            $list[] = self::ROLE_LZKF;
        }

        return $list;
    }

    // 获取装中客服所有角色
    public static function getZhkfRoles(){
        return [
            static::ROLE_ZZKF_KF,
            static::ROLE_ZZKF_ZZ,
            static::ROLE_ZZKF_JL,
            static::ROLE_ZZKF_ZG,
        ];
    }

    // 允许获取新单的角色
    public static function getNewOrderRoles(){
        return [
            static::ROLE_KF,
            static::ROLE_KFZZ,
            static::ROLE_ZZKF_KF,
            static::ROLE_ZZKF_ZZ,
            static::ROLE_ZZKF_JL,
            static::ROLE_ZZKF_ZG,
        ];
    }

    // 老回访归属人角色
    public static function getReviewRoles(){
        return [
            static::ROLE_KF,
            static::ROLE_ZZKF_KF,
            static::ROLE_ZZKF_ZZ,
            static::ROLE_ZZKF_JL,
            static::ROLE_ZZKF_ZG,
        ];
    }

}