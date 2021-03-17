<?php

namespace Home\Model;
Use Think\Model;

/**
*  装修公司广告表
*/
class AdvCompanyModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 编辑广告
     * @return [type] [description]
     */
    public function editAdv($id,$data) {
        $map = array(
            "work_order" => array("EQ",$id)
                    );
        return M("adv_company")->where($map)->save($data);
    }

    /**
     * 根据ID编辑信息
     * @return [type] [description]
     */
    public function editInfo($id, $data) {
        $map = array(
            "id" => array("EQ",$id)
                    );
        return M("adv_company")->where($map)->save($data);
    }

    /**
     * 根据工单号查询广告信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findAdvInfo($id) {
        $map = array(
            "work_order" => array("EQ",$id)
                    );
        return M("adv_company")->where($map)->find();
    }
}