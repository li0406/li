<?php

namespace app\index\enum;

class CacheKeyCodeEnum {

    // 管理员相关缓存名
    const ADMINUSER_AUTH_USERS = "RAPI:ADMINUSER:USERS:%s"; // 销售下级用户ID
    const ADMINUSER_AUTH_CITYS = "RAPI:ADMINUSER:CITYIDS:%s"; // 销售管辖城市
 
    const ADMINUSER_ROLE_DEPTMENT = "RAPI:ADMINUSER:ROLE_DEPTMENT:%s"; // 角色所属部门

    const AUTH_TEAM_USERS = "RAPI:AHTU:TEAM:USERS:%s"; // 销售下级用户ID
    const AUTH_TEAM_CITYS = "RAPI:AHTU:TEAM:CITYS:%s"; // 销售管辖城市ID

    // 部门员工
    const DEPT_USERS = "RAPI:DEPT:USERS:%s"; // 部门员工ID

    // 团队结构
    const TEAM_ALL = "RAPI:TEAM:ALL";
    const TEAM_MEMBERS = "RAPI:TEAM:MEMBERS:%s";

    // 渠道账户
    const SOURCE_ACCOUNT_INFO = "RAPI:SACCOUNT:%s";
    const SOURCE_ACCOUNT_EXPEND = "RAPI:SACCOUNT:EXPEND:%s:%s";

}