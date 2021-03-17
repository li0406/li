<?php

namespace Common\Model\Db;

use Think\Model;

class WwwArticleModel extends Model
{
    public function getArticle($where, $field = 'a.*')
    {
        return $this->alias('a')
            ->field($field)
            ->where($where)
            ->select();
    }
}