<?php

namespace Home\Model;

use Think\Model;

class WeixinArticleModel extends Model{

    /**
     * 新增微信文章
     * @param array $save 内容
     */
    public function addWeixinArticle($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('weixin_article')->add($save);
    }

    /**
     * 编辑微信文章
     * @param  [type] $id   文章ID
     * @param  array  $save 内容
     * @return bool
     */
    public function editWeixinArticle($id, $save = array())
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('weixin_article')->where($map)->save($save);
    }

    /**
     * 根据ID获取微信文章
     * @param  integer $id 文章ID
     * @return array
     */
    public function getWeixinArticleById($id = 0){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('weixin_article')->where($map)->find();
    }

    /**
     * 获取微信专享文章数量
     * @param  integer $category  分类
     * @param  integer $recommend 是否推荐
     * @param  integer $limit     获取数量
     * @return array
     */
    public function getWeixinArticleCount($category = 0, $recommend = 1)
    {
        $map['status'] = 1;
        if (!empty($category)) {
            $map['category'] = intval($category);
        }
        if (!empty($recommend)) {
            $map['recommend'] = intval($recommend);
        }
        return M('weixin_article')->where($map)->count();
    }

    /**
     * 获取微信专享文章
     * @param  integer $category  分类
     * @param  integer $recommend 是否推荐
     * @param  integer $start     开始位置
     * @param  integer $each      获取数量
     * @return array
     */
    public function getWeixinArticleList($category = 0, $recommend = 1, $start = 0, $each = 10, $order = 'recommend ASC, id DESC')
    {
        $map['status'] = 1;
        if (!empty($category)) {
            $map['category'] = intval($category);
        }
        if (!empty($recommend)) {
            $map['recommend'] = intval($recommend);
        }

        return M('weixin_article')->where($map)->limit($start, $each)->order($order)->select();
    }
}