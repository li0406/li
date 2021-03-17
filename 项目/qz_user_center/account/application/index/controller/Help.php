<?php


namespace app\index\controller;

class Help extends Base
{
    //帮助中心
    public function index()
    {
        $this->assign('active','');
        return $this->fetch();
    }

}