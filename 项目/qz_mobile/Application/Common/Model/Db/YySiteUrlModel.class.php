<?php

namespace Common\Model\Db;
use Think\Model;

class YySiteUrlModel extends Model
{
    public function getUrlInfo($url)
    {
        $map = ["url" => ["EQ",$url]];
        return $this->where($map)->field("id")->find();
    }
}
