<?php

namespace Home\Model\Db;
use Think\Model;

class ContentCategoryParticipleModel extends Model
{
    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }

    public function delInfo($category_id,$module)
    {
        $map = [
            "category_id" => ["EQ",$category_id],
            "module" => ["EQ",$module]
        ];
        return $this->where($map)->delete();
    }
}