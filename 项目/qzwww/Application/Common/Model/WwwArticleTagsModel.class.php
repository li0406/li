<?php

//详情页面底部标签组

namespace Common\Model;

use Think\Model;

class WwwArticleTagsModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取详情页标签
     *
     * @param array $where 查询条件
     * @param string $order 排序条件
     * @return mixed
     */
    public function getData($where,$order = 'addtime desc,id desc')
    {
        return M('www_article_tags')->where($where)->field('id,name,url,p_id')->order($order)->select();
    }

    public function getData2($where,$order = 'addtime asc,id desc')
    {
        return M('www_article_tags')->where($where)->field('id,name,url,p_id,addtime')->order($order)->select();
    }

}

