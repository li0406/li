<?php
return array(
	//'配置项'=>'配置值'
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
        "index" => array("https://".$_SERVER['HTTP_HOST']),//主站带有index方法的路由
        "verify" => array("Muser/Index/Verify"),//验证码路由
        "loginout" => array("Muser/Index/loginout"),//登出路由
        /**
         * S 登陆页面
         */
        "dologin" => array("Muser/Index/dologin"),//登陆
        /**
         * 主页路由
         */
        "home" => array("Muser/Home/index"),
        "orderlist" => array("Muser/Home/orderlist"),//获取订单列表路由
        /**
         * 订单详情
         */
        "orderdetails" => array("Muser/Order/details"),//订单详情页面
        "robdetail" => array("Muser/Order/robdetail"),//抢单详情页面
        "getrob" => array("Muser/Order/getRobOrder"),//抢单操作
        "add_norob" => array("Muser/Order/setNoRobOrder"),//不抢操作
        "muser/orderinfo" => array("Muser/Index/orderinfo",301),//老板路由的301
    ),
);