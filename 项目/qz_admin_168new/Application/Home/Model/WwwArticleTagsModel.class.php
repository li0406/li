<?php

//详情页面底部标签组

namespace Home\Model;

Use Think\Model;

class WwwArticleTagsModel extends Model
{

    protected $autoCheckFields = false;

    public function getPData($where, $field = '*')
    {
        return M('www_article_tags')
            ->field($field)
            ->where($where)
            ->order('`order` asc,addtime desc')
            ->select();
    }

    public function getDataCount($where)
    {
        return M('www_article_tags')->alias('a')
            ->join('left join qz_www_article_tags b on a.p_id = b.id')
            ->where($where)
            ->count();
    }

    public function getData($where, $field = 'a.*', $pageIndex, $pageCount, $order = '')
    {
        return M('www_article_tags')->alias('a')
            ->field($field)
            ->join('left join qz_www_article_tags b on a.p_id = b.id')
            ->where($where)
            ->limit($pageIndex . "," . $pageCount)
            ->order($order)
            ->select();
    }

    public function getInfoBtId($where, $field = '*')
    {
        return M('www_article_tags')->alias('a')
            ->field($field)
            ->join('left join qz_www_article_tags b on a.p_id = b.id')
            ->where($where)
            ->find();
    }

    public function update($where, $save)
    {
        return M('www_article_tags')->where($where)->save($save);
    }

    public function delTags($ids)
    {
        return M('www_article_tags')->where(array('id' => array('in', $ids)))->delete();
    }
}

