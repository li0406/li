<?php


namespace app\index\controller;


use think\Controller;

class Collect extends Base
{

    //收藏
    public function index()
    {
        return $this->fetch();
    }

}