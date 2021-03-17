<?php

namespace Home\Model\Logic;
use Home\Model\Db\PolicyApplyModel;

class PolicyLogicModel
{
    private $auditStatus = [
        0 => '待提交',
        3 => '待审核',
        1 => '审核通过',
        2 => '审核不通过'
    ];

    private $companyStatus = [
        0 => '待确认',
        1 => '已确认'
    ];

    public function getPolicyList($begin,$end,$order_id,$apply,$jc,$sh_status,$zq_status)
    {
        $begin = strtotime($begin);
        $end = strtotime($end) + 86400 -1;

        $model = new PolicyApplyModel();
        $count = $model->getPolicyListCount($begin,$end,$order_id,$apply,$jc,$sh_status,$zq_status);
        $list = [];
        $show = "";
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $model->getPolicyList($begin,$end,$order_id,$apply,$jc,$sh_status,$zq_status,$p->firstRow,$p->listRows);
            foreach ($list as $key => $item) {
                $list[$key]["amount"] = empty($item["amount"])?"":$item["amount"];
                $list[$key]["audit_status"] = $this->auditStatus[$item["audit_status"]];
                $list[$key]["company_status"] = $this->companyStatus[$item["company_status"]];
                $list[$key]["starttime"] = empty($item["starttime"])?"":date("Y-m-d",$item["starttime"]);
                $list[$key]["tel"] = substr($item["tel"],0,3)."*****".substr($item["tel"],-3);
            }
        }
        return ["list" => $list,"page" => $show];
    }

    public function getPolicyInfo($id)
    {
        $model = new PolicyApplyModel();
        $result = $model->getPolicyInfo($id);
        $info = [];
        if (count($result) > 0) {
            foreach ($result as $item) {
                if (count($info) == 0) {
                    $info = [
                        "uuid" => $item["uuid"],
                        "id" => $item["id"],
                        "order_id" => $item["order_id"],
                        "company_id" => $item["company_id"],
                        "created_at" => $item["created_at"],
                        "name" => $item["name"],
                        "tel" => substr($item["tel"],0,3)."*****".substr($item["tel"],-3),
                        "address" => $item["address"],
                        "jc" => $item["jc"],
                        "amount" =>  empty($item["amount"])?"":$item["amount"],
                        "starttime" => empty($item["starttime"])?"":date("Y-m-d",$item["starttime"]),
                        "audit_status" => $item["audit_status"],
                        "audit_status_mark" => $item["audit_status"] == 1 ?0:1,
                        "op_name" => $item["op_name"],
                        "fail_cause" => $item["fail_cause"],
                        "detail_address" => $item["detail_address"],
                        "xiaoqu_name" => $item["xiaoqu_name"]
                    ];
                }

                if (!isset($info["face_img"]) && $item["type"] == 1) {
                    $info["face_img"] = C("ZXS_QINIU_DOMAIN")."/".$item["img_url"];
                }

                if (!isset($info["pay_img"]) && $item["type"] == 2) {
                    $info["pay_img"] = C("ZXS_QINIU_DOMAIN")."/".$item["img_url"];
                }

                if (!empty($item["img_url"]) && $item["type"] == 1) {
                    $info["face_imgs"][] = C("ZXS_QINIU_DOMAIN")."/".$item["img_url"];
                }
                $info["face_count"] = count( $info["face_imgs"]);
                if (!empty($item["img_url"]) && $item["type"] == 2) {
                    $info["pay_imgs"][] = C("ZXS_QINIU_DOMAIN")."/".$item["img_url"];
                }
                $info["pay_count"] = count($info["pay_imgs"]);
            }
        }

        return $info;
    }

    public function updateInfo($id,$data)
    {
        $model = new PolicyApplyModel();
        return $model->updateInfo($id,$data);
    }
}