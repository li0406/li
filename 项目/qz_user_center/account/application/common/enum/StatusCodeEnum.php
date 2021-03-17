<?php

namespace app\common\enum;

class StatusCodeEnum extends BaseStatusCodeEnum
{
    /************** 0 请求成功 *****************/


    /************* 3 用户权限 ******************/
    const CODE_3000001 = "用户被禁用";
    const CODE_3000002 = "未获取到token";
    const CODE_3000003 = "token完全过期，不可用";
    const CODE_3000004 = "token类型异常";
    const CODE_3000005 = "未获取到platform";
    const CODE_3000006 = "platform类型异常";
    const CODE_3000007 = "用户已退出，请重新登陆";


    /************* 4 数据校验 ******************/
    const CODE_4000003 = "数据格式不正确";
    const CODE_4000004 = "该手机号已注册，可直接登录";
    const CODE_4000005 = "验证码已过期，请重新获取";           //原来是：验证码过期
    const CODE_4000006 = "验证码有误，请核对";  //原来是 ： 验证码错误
    const CODE_4000007 = "用户不存在";
    const CODE_4000008 = "密码有误，请重新输入";  //原来是：账号密码错误
    const CODE_4000009 = '数据纪录已被删除';
    const CODE_4000010= '用户被禁用';
    const CODE_4000011= '手机号已经绑定当前三方账号';
    const CODE_4000012= '回复不存在';
    const CODE_4000013= '评论不存在';
    const CODE_4000014 = "旧密码错误";
    const CODE_4000015 = "已绑定其他帐号";
    const CODE_4000016 = '不可自己回复自己';
    const CODE_4000017 = '图形验证码不能为空';
    const CODE_4000018 = '图形验证码错误';
    const CODE_4000019 = '新旧密码不能相同';
    const CODE_4000101 = '用户已经点赞';
    const CODE_4000102 = '用户还未点赞';
    const CODE_4000201 = '用户已经收藏';
    const CODE_4000202 = '用户还未收藏';
    const CODE_4000301 = '视频不存在';
    const CODE_4000502 = '数据不存在';
    const CODE_4000503 = '未获取到定位信息';
    const CODE_4000504 = '操作频繁';
    const CODE_4000505 = '数据错误';
    const CODE_4000506 = '重复操作';
    const CODE_4000507 = '邮箱已绑定账号';
    const CODE_4000508 = '邮件发送失败';
    const CODE_4000509 = '邮件地址不能为空';
    const CODE_4000510 = '操作失败';

    const CODE_4000601 = '您已分享';
    const CODE_4000602 = '分享操作出错';
    const CODE_4000603 = '抽奖次数已达上限';

    const CODE_4000701 = '抽奖次数已用尽';

    /************* 5 服务器问题 ****************/
    const CODE_5000002 = "服务器连接异常";

    /************* 6 环境配置 ******************/
}