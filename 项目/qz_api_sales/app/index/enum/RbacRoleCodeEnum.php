<?php

namespace app\index\enum;

class RbacRoleCodeEnum {

    const RBAC_ROLE_ADMIN = 1; // 超级管理员
    const RBAC_ROLE_XSZL = 45; // 销售助理
    const RBAC_ROLE_CW = 4; // 财务

    // 获取有权限看所有数据的角色
    public static function getAuthRoles(){
        return [
            static::RBAC_ROLE_ADMIN,
            static::RBAC_ROLE_XSZL,
            static::RBAC_ROLE_CW,
        ];
    }
}