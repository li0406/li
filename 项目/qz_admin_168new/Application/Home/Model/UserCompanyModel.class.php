<?php

namespace Home\Model;
Use Think\Model;
/**
 * 用户表
 */
class UserCompanyModel extends Model
{
    /**
     * 编辑会员扩展信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editCompanyExtendInfo($id,$data)
    {
        $map = array(
            "userid" => array("EQ",$id)
        );
        return M("user_company")->where($map)->save($data);
    }

    /**
     * 查询会员的总合同时间
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCompanyContract($id)
    {
        $map = array(
            "userid" => array("EQ",$id)
        );
        return M("user_company")->where($map)->field("contract_start,contract_end")->find();
    }

    public function getCompanyDeliverArea($qx){
        return M('user_company_deliver_area')->alias("a")
                ->join("join qz_user_company b on b.userid = a.company_id")
                ->where(['a.area_id'=>$qx,"b.cooperate_mode" => ["EQ",1]])->select();
    }

    public function getDeliverAreaByCompany($com_id){
        if(count($com_id) == 0){
            return [];
        }
        return M('user_company_deliver_area')->where(['company_id'=>['in',$com_id]])->select();
    }
}