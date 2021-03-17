<?php

namespace Common\Model;
use Think\Model;

class BaiduTokenModel extends Model
{
    public function addToken($data)
    {
        return M("baidu_token")->add($data);
    }


    public function getToken()
    {
        return M("baidu_token")->order("id desc")->find();
    }
}