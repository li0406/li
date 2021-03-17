<?php
// +----------------------------------------------------------------------
// | WwwArticleTagsModel  底部标签逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;


class WwwArticleTagsModel
{
    /**
     * 获取带分页的标签列表信息
     *
     * @param [array]$where 查询条件
     * @param [array]$order 排序条件
     * @return array
     */
    public function getArticleTagsList($where, $order, $pageSize = 10)
    {
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        $count = $tagModel->tagCount($where);
        $result = ['page' => '', 'list' => []];
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageSize);
            $p->setConfig('prev', '上一页');
            $p->setConfig('next', '下一页');
            $p->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $result['page'] = $p->show();
            $result['list'] = $tagModel->tagList($where, 'a.*,b.name as pname', $order, $p->nowPage, $pageSize);
        }
        return $result;
    }

    /**
     * 查询列表
     *
     * @param [array]$where 查询条件
     * @return array
     */
    public function getList($where)
    {
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        return $tagModel->tagList($where, 'a.*,b.name as pname', '', 1, 1000);
    }

    /**
     * 删除标签数据
     *
     * @param array $where 查询条件
     * @param array $data 更新数据
     * @return bool
     */
    public function delTags($ids)
    {
        $where = ['id' => ['in', $ids]];
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        return $tagModel->delTags($where);
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
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        return $tagModel->getInfoBtId($where, $field);
    }

    /**
     * 新增记录
     *
     * @param array $data 数据
     * @return mixed
     */
    public function addData($data)
    {
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        return $tagModel->addData($data);
    }
    /**
     * 修改记录
     *
     * @param array $where 查询条件
     * @param array $data 数据
     * @return mixed
     */
    public function upData($where = [], $data)
    {
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        return $tagModel->update($where, $data);
    }

    /**
     * 获取标签组
     *
     * @param array $where 查询条件
     * @return mixed
     */
    public function getgroup($where = [])
    {
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        $where['p_id'] = 0;
        return $tagModel->getPData($where);
    }


    /**
     * 获取带分页的标签列表信息
     *
     * @param [array]$where 查询条件
     * @param [array]$order 排序条件
     * @return array
     */
    public function getTagsGroupList($where, $order, $pageSize = 10)
    {
        $tagModel = new \Home\Model\Db\WwwArticleTagsModel();
        $where['p_id'] = 0;
        $count = $tagModel->tagGroupCount($where);
        $result = ['page' => '', 'list' => []];
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageSize);
            $p->setConfig('prev', '上一页');
            $p->setConfig('next', '下一页');
            $p->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $result['page'] = $p->show();
            $result['list'] = $tagModel->tagGroupList($where, '*', $order, $p->nowPage, $pageSize);
        }
        return $result;
    }
}