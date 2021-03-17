<?php

namespace Home\Model;
Use Think\Model;


/**
*   广告报备表
*/
class AdvertisingReportModel extends Model {

    /**
     * 添加报备
     */
    public function addReport($data)
    {
        return M("advertising_report")->add($data);
    }

    public function editReport($id, $data)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        return M("advertising_report")->where($map)->save($data);
    }

    /**
     * 删除广告报备
     * @param  [type] $company_id [description]
     * @param  [type] $start_time [description]
     * @return [type]             [description]
     */
    public function delReport($company_id,$start_time)
    {
        $map = array(
            "comid" => array("EQ",$company_id),
            "start" => array("EQ",$start_time)
        );
        return M("advertising_report")->where($map)->delete();
    }

    /**
     * 获取当前合同的广告报备
     * @param  [type] $company_id [description]
     * @param  [type] $start_time [description]
     * @return [type]             [description]
     */
    public function getNowAdvReport($company_id,$start_time)
    {
        $map = array(
            "comid" => array("EQ",$company_id),
            "start" => array("EQ",$start_time)
        );

        return M("advertising_report")->where($map)->select();
    }

    /**
     * 获取广告报备信息
     * @param  [type] $company_id [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    public function getAdvReportByType($company_id,$type)
    {
        $map = array(
            "comid" => array("IN",$company_id),
            "type" => array("EQ",$type)
        );

        $buildSql =  M("advertising_report")->where($map)->order("id desc")->buildSql();
        return M("advertising_report")->table($buildSql)->alias("t")->group("type,location,comid")->select();
    }
}