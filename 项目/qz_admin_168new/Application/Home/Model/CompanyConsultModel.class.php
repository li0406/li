<?php
/**
 * 公司入驻咨询信息表
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 16:07
 */

namespace Home\Model;

Use Think\Model;

class CompanyConsultModel extends Model
{
    public function getSourceListCount($begin, $end,$city,$src,$group)
    {
        $map = array(
            "a.add_time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.src" => array("NEQ","")
        );

        if (!empty($city)) {
            $map["a.cs"] = array("EQ",$city);
        }

        if (!empty($src)) {
            $map["a.src"] = array("EQ",$src);
        }

        if (!empty($group)) {
            $map["s.groupid"] = array("EQ",$group);
        }

        return M("company_consult")->where($map)->alias("a")
                                   ->join("left join qz_order_source s on s.src = a.src")
                                   ->count();
    }

    public function getSourceList($pageIndex,$pageCount,$begin, $end,$city,$src,$group)
    {
        $map = array(
            "a.add_time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.src" => array("NEQ","")
        );

        if (!empty($city)) {
            $map["a.cs"] = array("EQ",$city);
        }

        if (!empty($src)) {
            $map["a.src"] = array("EQ",$src);
        }

        if (!empty($group)) {
            $map["s.groupid"] = array("EQ",$group);
        }

        return M("company_consult")->where($map)->alias("a")
                                   ->join("left join qz_order_source s on s.src = a.src")
                                   ->join("left join qz_order_source_group g on g.id = s.groupid")
                                   ->join("left join qz_quyu q on q.cid = a.cs")
                                   ->join("left join qz_area area on area.qz_areaid = a.qx")
                                   ->field("a.add_time,s.name as source_name,g.name as group_name,a.src,q.cname,area.qz_area,a.tel,a.linkman,a.name")
                                   ->limit($pageIndex.",".$pageCount)->order("a.id desc")
                                   ->select();
    }
}