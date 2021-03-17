<?php


namespace app\index\controller;

class Account extends Base
{
    //账号中心首页
    public function index()
    {
        $this->assign('active','');
        return $this->fetch();
    }

    //基本信息设置
    public function setting()
    {
        $this->assign('active','setting');
        return $this->fetch();
    }

    //密保认证页面
    public function security()
    {
        $this->assign('active','security');
        return $this->fetch();
    }

    //修改密码页面
    public function setpassword()
    {
        $this->assign('active','setpassword');
        return $this->fetch();
    }

}