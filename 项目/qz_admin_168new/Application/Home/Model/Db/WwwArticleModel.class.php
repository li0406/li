<?php
// +----------------------------------------------------------------------
// | WwwArticleTagsModel  详情页面标签组
// +----------------------------------------------------------------------

namespace Home\Model\Db;

Use Think\Model;

class WwwArticleModel extends Model
{

    public function getArticle($fields, $where)
    {
        return M('www_article')->field($fields)->where($where)->select();
    }

    public function findArticle($map, $field = '*')
    {
        return M('www_article')->field($field)->where($map)->find();
    }
}

