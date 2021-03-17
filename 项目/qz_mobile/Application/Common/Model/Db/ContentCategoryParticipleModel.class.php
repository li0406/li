<?php
namespace Common\Model\Db;

use Think\Model;

class ContentCategoryParticipleModel extends Model
{
    public function getArticleClassParticiple($category_id,$module)
    {
        $map = [
            "module" => ["EQ",$module],
            "category_id" => $category_id
        ];
        return $this->where($map)->field("word")->select();
    }
}