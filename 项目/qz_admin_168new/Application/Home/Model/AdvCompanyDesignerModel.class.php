<?php

namespace Home\Model;
use Think\Model;

/**
* 广告辅助设计表
*/
class AdvCompanyDesignerModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取辅助设计列表数量
     * @return [type] [description]
     */
    public function getAdvListCount() {
        return M("adv_company_designer")->count();
    }

    /**
     * 获取辅助设计列表数量
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    public function getAdvList($pageIndex,$pageCount,$cs,$query){
        if (!empty($query)) {
            $map["_complex"] = array(
                        "a.company_name" => array("LIKE","%$cs%"),
                        "a.company_id" =>array("LIKE","%$cs%"),
                        "_logic"=>"OR"
            );
        }

        if (!empty($cs)) {
            $map["a.city_id"] = array("EQ",$cs);
        }

        return M("adv_company_designer")->where($map)->alias("a")
                                        ->order("time desc")
                                        ->join("INNER JOIN qz_quyu as q on q.cid = a.city_id")
                                        ->field("a.*,q.cname")
                                        ->limit($pageIndex.",".$pageCount)
                                        ->select();
    }

    /**
     * 获取广告信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getAdvInfo($id) {
        $map = array(
                "id" => array("EQ",$id)
                     );
        return M("adv_company_designer")->where($map)->find();
    }

    /**
     * 编辑广告信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editAdvInfo($id,$data) {
        $map = array(
                "id" => array("EQ",$id)
                     );
        return M("adv_company_designer")->where($map)->save($data);
    }
}