<?php

namespace Home\Model\Db;
use Think\Model;

class CompanyVisitModel extends Model
{
    public function getOrderVisitInfo($map)    {

        return $this->where($map)->alias("a")
            ->join("left join qz_company_visit_related b on a.id = b.visit_id")
            ->join("left join qz_user u on u.id = b.company_id")
            ->field("a.*,group_concat(u.jc) as company")
            ->group("a.id")->find();
    }

    public function editInfo($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return $this->where($map)->save($data);
    }

    public function addVisitInfo($data)
    {
        return $this->add($data);
    }

    public function getVisitReturnList($order_id)
    {
        $map = [
            "a.order_id" => $order_id,
            "a.visit_status" => ["NEQ",1]
        ];

        return $this->where($map)->alias("a")
            ->join("left join qz_company_visit_related b on a.id = b.visit_id")
            ->join("left join qz_user u on u.id = b.company_id")
            ->field("a.id,a.visit_time,u.jc,b.related_visit,b.related_push,a.visit_step,a.visit_user_need,a.visit_need,a.visit_status,a.visit_content")
            ->order("a.created_at desc")
            ->select();
    }

    public function getOrderVisitListWithEnd($map) {

        $buildSql = $this->where($map)->alias("a")->order("id desc")->buildSql();
        return $this->table($buildSql)->alias("t")->group("t.order_id")->field("t.order_id,t.feedbacker")->select();
    }
}