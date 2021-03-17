<?php
namespace Home\Model\Logic;
use Home\Model\Db\CompanyTrackModel;
use Home\Model\Db\CompanyVisitModel;
use Home\Model\Db\CompanyVisitRelatedModel;
use Home\Model\Db\LogUserOrderChangeModel;

/**
 *
 */
class CompanyVisitLogicModel
{
    /**
     * 被动回访订单
     * @param $order_id
     * @return mixed
     */
    public function getOrderPassiveVisitInfo($order_id)
    {
        $model = new CompanyVisitModel();
        $map = array(
            "order_id" => array("EQ",$order_id),
            "visit_type" => array("EQ", 1),
            "visit_status" => array("IN", [1, 4, 5])
        );
        $info =  $model->getOrderVisitInfo($map);
        switch ($info["visit_step"]){
            case 1: $info["visit_step"] = "促量房";break;
            case 2: $info["visit_step"] = "促到店";break;
            case 3: $info["visit_step"] = "促签单";break;
            case 4: $info["visit_step"] = "推荐服务";break;
        }
        return $info;
    }

    public function editVisitInfo($id,$data)
    {

        $model = new CompanyVisitModel();
        return $model->editInfo($id,$data);
    }

    /**
     * 未回访
     * @param string $value
     * @return mixed
     */
    public function getOrderNoReturnVisitInfo($order_id)
    {
        $model = new CompanyVisitModel();
        $map = array(
            "order_id" => array("EQ",$order_id),
            "visit_status" => array("IN", [1, 4, 5])
        );
        return  $model->getOrderVisitInfo($map);
    }

    public function addVisitInfo($data)
    {
        $model = new CompanyVisitModel();
        return $model->addVisitInfo($data);
    }

    public function getComapnyTrackList($order_id)
    {
        $model = new CompanyTrackModel();
        $result = $model->getComapnyTrackList($order_id);
        //获取装修公司订单操作记录
        $orderChangeDb = new LogUserOrderChangeModel();
        $changeLog = $orderChangeDb->getOrderChange($order_id);
        $result = array_merge($result?:[],$changeLog?:[]);
        //排序
        $sort = array_column($result,'track_time');
        array_multisort($sort,SORT_DESC,$result);
        $list = [];
        foreach ($result as $value) {
            if($value['record_type'] == 1){
                //跟单小计
                switch ($value['track_step']){
                    case 1:
                        $value['step_name'] = '初次跟进';
                        break;
                    case 2:
                        $value['step_name'] = '量房';
                        break;
                    case 3:
                        $value['step_name'] = '方案';
                        break;
                    case 4:
                        $value['step_name'] = '签单';
                        break;
                }
            }elseif ($value['record_type'] == 2){
                //公司操作订单记录
                switch ($value['status']){
                    case 1:
                        $value['step_name'] = '已量房';
                        break;
                    case 2:
                        $value['step_name'] = '已见面/已到店';
                        break;
                    case 3:
                        $value['step_name'] = '未量房';
                        break;
                    case 4:
                        $value['step_name'] = '已签约';
                        break;
                }
            }
            $list[$value["company_id"]]["name"] = $value["jc"];
            $list[$value["company_id"]]["child"][] = $value;
        }
        return $list;
    }

    // 装修公司跟单小计列表
    public function getComapnyTrackInfoList($order_id) {
        $model = new CompanyTrackModel();
        $result = $model->getComapnyTrackList($order_id);

        $list = [];
        foreach ($result as $value) {
            if($value['record_type'] == 1){
                //跟单小计
                switch ($value['track_step']){
                    case 1:
                        $value['step_name'] = '初次跟进';
                        break;
                    case 2:
                        $value['step_name'] = '量房';
                        break;
                    case 3:
                        $value['step_name'] = '方案';
                        break;
                    case 4:
                        $value['step_name'] = '签单';
                        break;
                }
            }

            $list[$value["company_id"]]["name"] = $value["jc"];
            $list[$value["company_id"]]["child"][] = $value;
        }

        return $list;
    }

    public function getOrderLastVisitInfo($order_id)
    {
        $model = new CompanyVisitModel();
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return  $model->getOrderVisitInfo($map);
    }

    public function getVisitReturnList($order_id)
    {
        $model = new CompanyVisitModel();
        $result = $model->getVisitReturnList($order_id);

        $list = [];
        foreach ($result as $value) {
            $value["visit_time"] = date("Y-m-d H:i:s", $value["visit_time"]);
            switch ($value["visit_step"]){
                case 1:
                    $value["visit_step"] = "促量房";
                    break;
                case 2:
                    $value["visit_step"] = "促到店";
                    break;
                case 3:
                    $value["visit_step"] = "促签单";
                    break;
                case 4:
                    $value["visit_step"] = "推荐服务";
                    break;
                default:
                    $value["visit_step"] = "-";
                    break;
            }

            switch ($value["visit_status"]){
                case 2:
                    $value["visit_status"] = "有效";
                    break;
                case 3:
                    $value["visit_status"] = "无效";
                    break;
            }


            if (!array_key_exists($value["id"],$list)) {
                $list[$value["id"]]["visit_time"] = $value["visit_time"];
                $list[$value["id"]]["visit_step"] = $value["visit_step"];
                $list[$value["id"]]["visit_user_need"] = empty($value["visit_user_need"])?"-":$value["visit_user_need"];
                $list[$value["id"]]["visit_status"] = $value["visit_status"];
                $list[$value["id"]]["visit_content"] = empty($value["visit_content"])?"-":$value["visit_content"];
                $list[$value["id"]]["visit_need"] = empty($value["visit_need"])?"-":$value["visit_need"];
            }

            if ($value["related_visit"] == 1) {
                $list[$value["id"]]["related_company"] .= $value["jc"].",";
            }

            if ($value["related_push"] == 1) {
                $list[$value["id"]]["push_company"] .= $value["jc"].",";
            }

            if (empty( $list[$value["id"]]["related_company"])) {
                $list[$value["id"]]["related_company"] = "-";
            }

            if (empty( $list[$value["id"]]["push_company"])) {
                $list[$value["id"]]["push_company"] = "-";
            }
        }
        return $list;
    }

    public function addVisitRelatedInfo($data)
    {
        $model = new CompanyVisitRelatedModel();
        return $model->addVisitRelatedInfo($data);
    }

    public function getOrderVisitListWithEnd($order_id)
    {
        $model = new CompanyVisitModel();
        $map = array(
            "a.order_id" => array("In",$order_id)
        );
        return  $model->getOrderVisitListWithEnd($map);
    }
}
