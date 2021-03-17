<?php
namespace app\index\controller;

use think\Controller;

class Index extends Base
{
    //个人中心首页
    public function index()
    {
        return $this->fetch();
    }
}
