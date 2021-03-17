<?php

namespace app\common\enum;

class CacheKeyCodeEnum {

    // 管理员相关缓存名
    const ADMINUSER_PRIV_USERS = "SALESAPI:ADMINUSER:USERS:%s"; // 销售下级用户ID
    const ADMINUSER_PRIV_CITYS = "SALESAPI:ADMINUSER:CITYIDS:%s"; // 销售管辖城市

    // 角色相关缓存名
    const RBACROLE_DEPARTMENTS = "SALESAPI:ROLE:DEPTS:%s"; // 角色所在部门
    const RBACROLE_MENU = "SALESAPI:ROLE:MENU:%s"; // 角色菜单

    // 城市区域相关
    const CITY_AREA_ALL = "SALESAPI:CITY:AREA"; // 所有城市区域
    const CITY_PART_CIDS = "SALESAPI:CITY:CIDS:%s"; // 部分城市信息
    const CITY_USER_COUNT = "SALESAPI:CITY:USERCOUNT"; // 城市会员数量
    const CITY_AUDIT_CITYS = "SALESAPI:CITY:AUDITCITYS"; // 质检操作城市

    // options配置相关
    const OPTIONS_NAME_VALUE = "SALESAPI:OPTIONS:%s"; // 配置项值
    const OPTIONS_QINIU = "SALESAPI:OPTIONS:QINIU"; // 七牛相关配置

    // 团队相关
    const TEAM_MEMBER_ALL = "SALESAPI:TEAM:ALLMEMBER"; // 团队结构缓存

    // 其它缓存
    const WORKSITE_QRCODE = "SALESAPI:WORKSITE:QRCODE:%s"; // 工地直播二维码

}