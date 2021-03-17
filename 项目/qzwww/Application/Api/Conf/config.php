<?php
return array(
	//'配置项'=>'配置值'
    'SESSION_AUTO_START'    =>  false,
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
        "fb_order" => array("Zbfb/fb_order"),//订单发布路由
        "loginin"=>array("Login/loginin"),//用户登录操作
        "login"=>array("Login/login"),//请求登录窗口
        "refresh"=>array("Zbfb/refresh"),//刷新安全码路由
        "collect"=>array("Collect/setCollect"),//添加搜藏路由
        "cancelcollect"=>array("Collect/cancelcollect"),//用户取消收藏路由
        "account"=>array("Accountsecurity/account"),//绑定安全手机/邮箱
        "bindaccount"=>array("Accountsecurity/bindaccount"),//绑定安全手机/邮箱
        "loginout"=>array("Login/loginout"),//注销帐号路由
        "loginfromsina"=>array("OAuth/sina_login"),//新浪认证路由
        "validateaccount"=>array("OAuth/validateaccount"),//绑定验证帐号
        "jump"=>array("OAuth/jump"),//跳过此步路由
        "register"=>array("OAuth/register"),//授权注册路由
        "run"=>array("Login/run"),//轮询查询用户是否登录路由
        "loginfromqq"=>array("OAuth/qq_login"),//qq授权登录路由
        "loginfromwechat"=>array("OAuth/weixin_login"),//微信授权登录路由
        "loginfrombaidu"=> array("OAuth/baidu_login"),//百度登录接口
        "fb_order_safe"=>array("Zbfb/fb_order_safe"),//带验证码的发布订单
        "zbprice"=>array("Zbfb/getZbPrice"),//获取订单报价
        "feedback"=>array("Zbfb/feedback"),//订单报价结果反馈
        "snslogin/wxproxy"=>array("Snslogin/wxproxy"),//微信登录代理
        "shorturl" => array("OAuth/short_url"),

        //百度寻客API
        "xunke" => array("Xunke/xunkedata"), //百度寻客数据接收API接口
    ),
);