<?php
// +----------------------------------------------------------------------
// | WwwArticleTagsModel  详情页面标签组
// +----------------------------------------------------------------------

namespace Home\Model\Db;

Use Think\Model;

class WwwArticleTagsModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取标签总数
     *
     * @param array $where 查询条件
     * @return mixed
     */
    public function tagCount($where)
    {
        return M('www_article_tags')->alias('a')
            ->join('left join qz_www_article_tags b on a.p_id = b.id')
            ->where($where)
            ->count();
    }

    /**
     * 获取标签列表
     *
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param string $order 排序条件
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return mixed
     */
    public function tagList($where, $field = 'a.*', $order = '', $page, $pageSize)
    {
        return M('www_article_tags')->alias('a')
            ->field($field)
            ->join('left join qz_www_article_tags b on a.p_id = b.id')
            ->where($where)
            ->page($page,$pageSize)
            ->order($order)
            ->select();
    }

    /**
     * 获取单个标签信息
     *
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return mixed
     */
    public function getInfoBtId($where, $field = '*')
    {
        return M('www_article_tags')->alias('a')
            ->field($field)
            ->join('left join qz_www_article_tags b on a.p_id = b.id')
            ->where($where)
            ->find();
    }

    /**
     * 添加数据
     *
     * @param $where
     * @param array $data 数据集
     * @return mixed
     */
    public function addData($data)
    {
        return M('www_article_tags')->add($data);
    }

    /**
     * 更新标签数据
     *
     * @param array $where 查询条件
     * @param array $data 更新数据
     * @return bool
     */
    public function update($where, $data)
    {
        return M('www_article_tags')->where($where)->save($data);
    }

    /**
     * 删除标签数据
     *
     * @param array $where 查询条件
     * @param array $data 更新数据
     * @return bool
     */
    public function delTags($where)
    {
        return M('www_article_tags')->where($where)->delete();
    }


    /**
     * 获取所有标签组数据
     *
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return mixed
     */
    public function getPData($where, $field = '*')
    {
        return M('www_article_tags')
            ->field($field)
            ->where($where)
            ->order('`order` asc,addtime desc')
            ->select();
    }


    /**
     * 获取标签组总数
     *
     * @param array $where 查询条件
     * @return mixed
     */
    public function tagGroupCount($where)
    {
        return M('www_article_tags')->where($where)->count();
    }

    /**
     * 获取标签组列表
     *
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param string $order 排序条件
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return mixed
     */
    public function tagGroupList($where, $field = '*', $order = '', $page, $pageSize)
    {
        return M('www_article_tags')
            ->field($field)
            ->where($where)
            ->page($page,$pageSize)
            ->order($order)
            ->select();
    }



}

