<?php
return array(
    //路由配置
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES' => array(
       "main"             =>  array("Home/Main/index"),//主框架路由
       "login"            =>  array("Home/Index/login"),//登录路由
       "loginout"         =>  array("Home/Index/loginout"),//登出路由
       "basic"            =>  array("Home/Main/basic"),//个人信息修改
       "verify"           =>  array("Home/Index/Verify"),//验证码路由
       "portrait"         =>  array("Home/Main/portrait"),//设置个人头像
       "uplogo"           =>  array("Home/Upload/uplogo"),//上传图片的路由
       "password"         =>  array("Home/Main/password"),//修改密码的路由
       "sendsms"          =>  array("Home/Index/sms"),//发送短信路由
       "account"          =>  array("Home/Main/account"),//账号安全路由
       "telephone"        =>  array("Home/Main/telephone"),//绑定安全电话
       "untelephone"      =>  array("Home/Main/untelephone"),//绑定安全电话
       "sendaccountsms"   =>  array("Home/Main/sendaccountsms"),//绑定安全电话短信
       "sendunaccountsms" =>  array("Home/Main/sendunaccountsms"),//解除绑定安全电话短信
       "loginset"         =>  array("Home/Main/loginset"),//登录设置
       "globalset"        =>  array("Home/Main/globalset"),//全局设置
       "forgetpwd"        =>  array("Home/Password/index"),//忘记密码路由
       "step"             =>  array("Home/Password/step"),//忘记密码第一步
       "successpwd"       =>  array("Home/Password/successpwd"),//重置密码成功
       "getpassaccount"   =>  array("Home/Password/getpassaccount"),//重置密码获取短信
       "rbac"             =>  array("Home/Rbac/index"),//权限控制
       "rbac/setting"     =>  array("Home/Rbac/setting"),//权限设置路由
       "saverole"         =>  array("Home/Rbac/saverole"),//保存权限路由
       "systemrole"       =>  array("Home/Rbac/systemrole"),//登录系统的权限
       "redirect"         =>  array("Home/Main/redirect"),//跳转页面

       //我的电话
       "mytelset"                              =>  array("Mytel/mytelset"),//设置我的电话
       "mytel/autocompletdirectnumber"         =>  array("Mytel/autocompletDirectNumber"),//跳转页面

		'auth/approval'         =>  array('Home/Main/approvalToken','post'),//下发去装修说后台的token

	),
);