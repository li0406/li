<?php
namespace Home\Model\Db;

Use Think\Model;
/**
 *
 */
class PolicyApplyModel extends Model
{
    public function getPolicyListCount($begin,$end,$order_id,$apply,$jc,$sh_status,$zq_status)
    {
        $map = [
            "a.created_at" => [
                ["EGT",$begin],
                ["ELT",$end]
            ]
        ];

        if (!empty($order_id)) {
            $map["a.order_id"] = ["EQ",$order_id];
        }

        if (!empty($apply)) {
            $map["a.name"] = ["like","%".$apply."%"];
        }

        if (!empty($jc)) {
            $map["_complex"] =[
                "u.id" => ["EQ",$jc],
                "u.jc" => ["LIKE","%".$jc."%"],
                "_logic" => "OR"
            ];
        }

        if ($sh_status !== "") {
            $map["a.audit_status"] = ["EQ",$sh_status];
        }

        if ($zq_status !== "") {
            $map["a.company_status"] = ["EQ",$zq_status];
        }

       return $this->where($map)->alias("a")
                   ->join("left join qz_user u on u.id = a.company_id")
                   ->count();
    }

    public function getPolicyList($begin,$end,$order_id,$apply,$jc,$sh_status,$zq_status,$pageIndex,$pageCount)
    {
        $map = [
            "a.created_at" => [
                ["EGT",$begin],
                ["ELT",$end]
            ]
        ];

        if (!empty($order_id)) {
            $map["a.order_id"] = ["EQ",$order_id];
        }

        if (!empty($apply)) {
            $map["a.name"] = ["like","%".$apply."%"];
        }

        if (!empty($jc)) {
            $map["_complex"] =[
                "u.id" => ["eq",$jc],
                "u.jc" => ["LIKE","%".$jc."%"],
                "_logic" => "OR"
            ];
        }

        if ($sh_status !== "") {
            $map["a.audit_status"] = ["EQ",$sh_status];
        }

        if ($zq_status !== "") {
            $map["a.company_status"] = ["EQ",$zq_status];
        }

        return $this->where($map)->alias("a")
             ->join("left join qz_user u on u.id = a.company_id")
             ->field("a.id,a.created_at,a.order_id,a.name,a.tel,a.address,u.jc,a.amount,a.starttime,a.audit_status,a.company_status,a.op_name")
             ->order("find_in_set(a.audit_status,'3,0,1,2'),a.created_at desc")
             ->limit($pageIndex,$pageCount)->select();
    }

    public function getPolicyInfo($id)
    {
        $map = [
            "a.id" => ["EQ",$id]
        ];
        return $this->where($map)->alias("a")
            ->join("left join qz_user u on u.id = a.company_id")
            ->join("left join qz_policy_apply_img i on a.id = i.policy_id")
            ->field("a.uuid,a.company_id, a.id,a.created_at,a.order_id,a.name,a.tel,a.address,a.detail_address,a.xiaoqu_name,u.jc,a.amount,a.starttime,a.audit_status,a.company_status,a.op_name,i.img_url,i.type,a.fail_cause")
            ->order("i.type")
            ->select();
    }

    public function updateInfo($id,$data)
    {
        $map = [
            "id" => ["EQ",$id]
        ];
        return $this->where($map)->save($data);
    }
}