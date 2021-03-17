<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/2/13
 * Time: 14:06
 */
namespace Home\Model\Logic;
use Home\Model\Db\QcInfoModel;
use Home\Model\Db\QcItemsScoreModel;
use Home\Model\Db\QcOrdersApplyTelModel;

class QcInfoLogicModel
{
    private $typefw = [1=>'分单',2=>'赠单',3=>'分没人跟',4=>'赠没人跟',5=>'未分配'];
    private $type = [1=>'抽听',2=>'回听'];
    private $qcItemInfo = [1=>'礼貌服务',6=>'语音语速抢话口头禅',11=>'倾听能力',16=>'表达能力',21=>'引导能力',26=>'疑难解决',31=>'业务熟练',36=>'营销意识'];

    //质检结论订单
    public function recordgrade($where){
        $qcinfomodel = new QcInfoModel();
        $count = $qcinfomodel->getqcinfocount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $qcinfomodel->getqcinfolist($where,$p->firstRow, $p->listRows);
            $info = $this->recortgradelist($list);
            return ['list' => $info, 'page' => $show];
        }
    }

    //处理列表数据
    public function recortgradelist($list){
        foreach($list as $val){
            $info[$val['order_id']]['time'] = date('Y-m-d H:i:s',$val['time']);
            $info[$val['order_id']]['order_id'] = $val['order_id'];
            $info[$val['order_id']]['type'] = $this->type[$val['type']];
            $info[$val['order_id']]['op_name'] = $val['op_name'];
            $info[$val['order_id']]['type_fw'] = $this->typefw[$val['type_fw']];
            $info[$val['order_id']]['record_name'] = $val['record_name'];
            $info[$val['order_id']]['kf_name'] = $val['kf_name'];
            $info[$val['order_id']]['kf_group_name'] = empty($val['kf_group'])?$val['kf_group']:$val['kf_group'].'团|'.$val['kf_group_name'];
            $info[$val['order_id']]['kf_manager_name'] = $val['kf_manager_name'];
            if(!empty($val['qc_item_id'])&&!empty($val['score'])){
                $info[$val['order_id']][$val['parentid']] = $val['score'];
                $info[$val['order_id']]['sum_score'] += $val['score'];
            }
        }
        return $info;
    }

    //导出表格数据
    public function recortgradeexport($where){
        $qcinfomodel = new QcInfoModel();
        $list = $qcinfomodel->getqcinfolistexport($where);
        return $this->recortgradelist($list);
    }



    //按时间
    public function recordgradebytime($get,$info){
        foreach($info as $val){
            $list[date('Y-m-d',$val['time'])]['date'] = date('Y-m-d',$val['time']);
            if(!in_array( $val['order_id'] ,$list[date('Y-m-d',$val['time'])]['order'])){
                $list[date('Y-m-d',$val['time'])]['order'][] = $val['order_id'];
                $list[date('Y-m-d',$val['time'])]['order_num']++;
            }
            if(!empty($val['qc_item_id'])&&!empty($val['score'])){
                $list[date('Y-m-d',$val['time'])][$val['parentid']] += $val['score'];
                $list[date('Y-m-d',$val['time'])]['sum_score'] += $val['score'];
            }
        }
        ksort($list);
        if(!empty($get['start_time'])){
            $begin = $get['start_time'];
        }else{
            $begin = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
        }

        if(!empty($get['end_time'])){
            $end = $get['end_time'];
        }else{
            $end = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")));
        }
        $date = $this->getDateFromRange($begin,$end);
        $date = array_reverse($date);

        foreach($date as $val){
            $datelist[$val] = $list[$val];
            if(empty($list[$val])){
                $datelist[$val]['date'] = $val;
            }
        }

        return $datelist;
    }

    /**
     * 获取指定日期段内每一天的日期
     * @param  Date  $startdate 开始日期
     * @param  Date  $enddate   结束日期
     * @return Array
     */
    public function getDateFromRange($startdate, $enddate){
        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);
        // 计算日期段内有多少天
        $days = ($etimestamp-$stimestamp)/86400+1;

        // 保存每天日期
        $date = array();

        for($i=0; $i<$days; $i++){
            $date[] = date('Y-m-d', $stimestamp+(86400*$i));
        }

        return $date;
    }

    //按人
    public function recordgradebyperson($info){
        foreach($info as $val){
            $list[$val['kf_id']]['kf_name'] = $val['kf_name'];
            $list[$val['kf_id']]['kf_group_name'] = empty($val['kf_group'])?$val['kf_group_name']:$val['kf_group'].'团|'.$val['kf_group_name'];
            $list[$val['kf_id']]['kf_manager_name'] = $val['kf_manager_name'];
            if(!in_array( $val['order_id'] ,$list[$val['kf_id']]['order'])){
                $list[$val['kf_id']]['order'][] = $val['order_id'];
                $list[$val['kf_id']]['order_num']++;
            }
            if(!empty($val['qc_item_id'])&&!empty($val['score'])){
                $list[$val['kf_id']][$val['parentid']] += $val['score'];
                $list[$val['kf_id']]['sum_score'] += $val['score'];
            }
        }
        return $list;
    }

    //按团
    public function recordgradebygroup($info){
        foreach($info as $val){
            $list[$val['kf_group']]['kf_group_name'] = empty($val['kf_group'])?$val['kf_group_name']:$val['kf_group'].'团|'.$val['kf_group_name'];
            $list[$val['kf_group']]['kf_manager_name'] = $val['kf_manager_name'];
            if(!in_array( $val['order_id'] ,$list[$val['kf_group']]['order'])){
                $list[$val['kf_group']]['order'][] = $val['order_id'];
                $list[$val['kf_group']]['order_num']++;
            }
            if(!empty($val['qc_item_id'])&&!empty($val['score'])){
                $list[$val['kf_group']][$val['parentid']] += $val['score'];
                $list[$val['kf_group']]['sum_score'] += $val['score'];
            }
        }
        return $list;
    }

    //按师
    public function recordgradebymanager($info){

        foreach($info as $val){
            $list[$val['kf_manager']]['kf_manager_name'] = $val['kf_manager_name'];
            if(!in_array( $val['order_id'] ,$list[$val['kf_manager']]['order'])){
                $list[$val['kf_manager']]['order'][] = $val['order_id'];
                $list[$val['kf_manager']]['order_num']++;
            }
            if(!empty($val['qc_item_id'])&&!empty($val['score'])){
                $list[$val['kf_manager']][$val['parentid']] += $val['score'];
                $list[$val['kf_manager']]['sum_score'] += $val['score'];
            }
        }
        return $list;
    }

    public function recordgradetab($get){
        $qcinfomodel = new QcInfoModel();
        return $qcinfomodel->getqcinfoItem($get);
    }

    public function getRecordAvgScore($begin,$end)
    {
        $month_start = mktime(0,0,0,date("m"),1,date("Y"));
        $month_end = mktime(23,59,59,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $month_start = strtotime($begin);
            $month_end = strtotime($end) + 86400 - 1;
        }

        $qcinfomodel = new QcInfoModel();
        $result = $qcinfomodel->getRecordAvgScore($month_start,$month_end);
        foreach ($result as $key => $value) {
            $list[$value["id"]] = $value["avg_score"];
        }
        return $list;
    }

    public function getLastOrderApplyTelInfo($order_id)
    {
        $model = new QcOrdersApplyTelModel();
        return  $model->getLastOrderApplyTelInfo($order_id);
    }

    public function editOrderApplyTelInfo($id,$data)
    {
        $model = new QcOrdersApplyTelModel();
        return $model->editOrderApplyTelInfo($id,$data);
    }

    public function addOrderApplyTelInfo($order_id,$apply_reason)
    {
        $data = [
            "order_id" => $order_id,
            "apply_id" => session("uc_userinfo.id"),
            "apply_reason" =>$apply_reason,
            "apply_time" => time()
        ];
        $model = new QcOrdersApplyTelModel();
        return $model->addOrderApplyTelInfo($data);
    }

    public function getOrderApplyTelList($list)
    {
        $ids = array_filter(array_column($list["list"],"id"));
        if (count($ids) > 0) {
            //获取显号数据
            $model = new QcOrdersApplyTelModel();
            $result = $model->getOrderApplyTelList($ids);
            foreach ($result as $item) {
                foreach ($list["list"] as $k => $val) {
                    if ($item["order_id"] == $val["id"]) {
                        $list["list"][$k]["apply_mark"] = 1;
                    }
                }
            }
        }
        return $list;
    }

    public function getApplyList($id)
    {
        $model = new QcOrdersApplyTelModel();
        $result = $model->getapplylist($id);

        $list = [];
        foreach ($result as $item) {
            $time  = strtotime("+3 day",$item["apply_time"]);
            if ($item["status"] == 1 && $time < time()) {
                $item["status"] = 4;
            }
            $item["apply_time"] = date("Y-m-d H:i:s",$item["apply_time"]);

            if (!empty($item["pass_time"])) {
                $item["pass_time"] = date("Y-m-d H:i:s",$item["pass_time"]);
            } else {
                $item["pass_time"] = "-";
            }

            if (empty($item["pass_name"])) {
                $item["pass_name"] = "-";
            }

            switch ($item["status"]){
                case 1: $item["status_result"] = "申请显号"; break;
                case 2: $item["status_result"] = "同意显号"; break;
                case 3: $item["status_result"] = "拒绝显号"; break;
                case 4: $item["status_result"] = "已过期"; break;
            }

            $list[] = $item;
        }
        return $list;
    }

    public function editApplyTelInfo($id,$data)
    {
        $model = new QcOrdersApplyTelModel();
        return $model->editApplyTelInfo($id,$data);
    }
}