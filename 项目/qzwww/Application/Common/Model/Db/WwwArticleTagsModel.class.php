<?php
// +----------------------------------------------------------------------
// | WwwArticleTagsModel  底部标签模型
// +----------------------------------------------------------------------

namespace Common\Model\Db;

use Think\Model;

class WwwArticleTagsModel extends Model
{
    protected $tableName = 'www_article_tags';
    protected $autoCheckFields = false;

    /**
     * 获取标签列表数据
     *
     * @param array $where 查询条件
     * @param string $order 排序条件
     * @return mixed
     */
    public function getList($where, $order = 'addtime desc,id desc')
    {
        return $this->where($where)->order($order)->select();
    }
}