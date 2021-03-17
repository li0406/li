<?php

namespace Home\Model;
Use Think\Model;

/**
*   广告审核表
*/

class AdvCompanyAuditModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取审核的广告数量
     * @param  [type] $cs [管辖城市]
     * @return [type]     [description]
     */
    public function getAuditListCount($cs) {
        $map = array(
            "b.city_id" => array("IN",$cs)
        );

        return M("adv_company_audit")->where($map)->alias("a")
                               ->join("INNER JOIN qz_adv_company as b on a.adv_id = b.id and  b.assistance = 0")
                               ->count();
    }

    /**
     *  获取审核列表
     * @param  [type] $cs        [description]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    public function getAuditList($cs, $pageIndex, $pageCount) {
        $map = array(
            "b.city_id" => array("IN",$cs)
        );

        $buildSql = M("adv_company_audit")->where($map)->alias("a")
                                    ->join("INNER JOIN qz_adv_company as b on a.adv_id = b.id and b.assistance = 0")
                                    ->order("a.status,a.id desc")
                                    ->limit($pageIndex.",".$pageCount)
                                    ->field("a.id as audit_id,a.status as audit_status,a.time as audit_time,a.uptime,a.op_uname,b.*")
                                    ->buildSql();
        return  M("adv_company_audit")->table($buildSql)->alias("t")
                                ->join("INNER JOIN qz_quyu as q on q.cid = t.city_id")
                                ->field("t.*,q.cname")
                                ->select();
    }

    /**
     * 获取审核的详细信息
     * @param  [type] $id [编号]
     * @param  [type] $cs [管辖城市]
     * @return [type]     [description]
     */
    public function getAuditInfo($id,$cs) {
        $map = array(
            "b.city_id" => array("IN",$cs),
            "a.id" => array("EQ",$id)
        );

        return  M("adv_company_audit")->where($map)->alias("a")
                                    ->join("INNER JOIN qz_adv_company as b on a.adv_id = b.id and b.audit = 1 and b.assistance = 0")
                                    ->field("a.id as audit_id,a.status as audit_status,b.*")
                                    ->find();
    }

    /**
     * 编辑审核信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editAudit($id,$data) {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("adv_company_audit")->where($map)->save($data);
    }

   /**
    *  获取审核通知的数量
    * @param  [type] $city_id [管辖城市]
    * @return [type]          [description]
    */
    public function getAuditCount($city_id) {
        $map = array(
            "a.status" => array("EQ",0),
            "b.city_id" => array("IN",$city_id)
        );
        return M("adv_company_audit")->where($map)->alias("a")
                              ->join("inner join qz_adv_company as b on a.adv_id = b.id")
                              ->count();
    }
}