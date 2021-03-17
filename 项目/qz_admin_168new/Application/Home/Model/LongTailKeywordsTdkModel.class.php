<?php
namespace Home\Model;
Use Think\Model;

/**
*   长尾词TDK
*/
class LongTailKeywordsTdkModel extends Model
{
    /**
     * 添加TDK
     * @param [type] $data [description]
     */
    public function addTdk($data)
    {
        return M("long_tail_keywords_tdk")->add($data);
    }

    /**
     * 编辑TDK
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editTdk($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("long_tail_keywords_tdk")->where($map)->save($data);
    }

    /**
     * 删除TDK
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deltdk($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("long_tail_keywords_tdk")->where($map)->delete();
    }

    /**
     * 获取TDK列表
     * @return [type] [description]
     */
    public function getTdkList()
    {
        return M("long_tail_keywords_tdk")->order("id desc")->select();
    }

    /**
     * 查询TDK信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getTdkInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("long_tail_keywords_tdk")->where($map)->find();
    }

    /**
     * 添加长尾词和TDK关联
     * @param [type] $data [description]
     */
    public function addRelete($data)
    {
        return M("long_tail_tdk_relete")->addAll($data);
    }

    /**
     * 删除长尾词关联
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function delReleteByLongTailId($ids)
    {
        $map = array(
            "long_tail_id" => array("IN",$ids)
        );
        return M("long_tail_tdk_relete")->where($map)->delete();
    }

    /**
     * 根据TDK删除长尾词关联
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delReleteByTdkId($id)
    {
        $map = array(
            "tdk_id" => array("EQ",$id)
        );
        return M("long_tail_tdk_relete")->where($map)->delete();
    }
}