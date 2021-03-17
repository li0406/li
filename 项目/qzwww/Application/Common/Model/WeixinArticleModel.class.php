<?php

namespace Common\Model;

use Think\Model;

class WeixinArticleModel extends Model{

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
            'id' => $id,
            'status' => 1
        );
        return M('weixin_article')->where($map)->find();
    }

    /**
     * 获取微信专享文章
     * @param  integer $category  分类
     * @param  integer $recommend 是否推荐
     * @param  integer $limit     获取数量
     * @return array
     */
    public function getWeixinArticleList($category = 0, $recommend = 1, $limit = 2)
    {
        $map['status'] = 1;
        if (!empty($category)) {
            $map['category'] = intval($category);
        }
        if (!empty($recommend)) {
            $map['recommend'] = intval($recommend);
        }

        return M('weixin_article')->where($map)->limit($limit)->order('id DESC')->select();
    }
}