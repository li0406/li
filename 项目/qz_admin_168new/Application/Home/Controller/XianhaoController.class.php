<?php

//显号

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class XianhaoController extends HomeBaseController
{
    public function index()
    {
        $where = $this->getWhere();
        $groupBy = 'o.apply_id';
        $data = $this->getData($where, $groupBy);
        $this->assign('dataInfo', $data);
        $this->display();
    }

    /**
     *  显号统计（团） antuan
     */
    public function antuan()
    {
        $where = $this->getWhere();
        $groupBy = 'o.apply_id';
        $data = $this->getData($where,$groupBy,2);
        $this->assign('dataInfo', $data);
        $this->display();
    }

    /**
     *  显号统计（师） anshi
     */
    public function anshi()
    {
        $where = $this->getWhere();
        $groupBy = 'o.apply_id';
        $data = $this->getData($where, $groupBy, 3);
        $this->assign('dataInfo', $data);
        $this->display();
    }

    /**
     *  显号审批 审批
     */
    public function shenpi()
    {
        $where = $this->getWhere();
        $data = $this->getShenpiData($where);
        $this->assign('dataInfo', $data);
        $this->display();
    }

    /**
     * 获取筛选条件
     * @return mixed
     */
    private function getWhere()
    {
        $s_time = date('Y-m-01', strtotime(date("Y-m-d")));
        $e_time = date('Y-m-d', strtotime("$s_time +1 month -1 day"));
        $search_time['start_time'] = I('get.start_time');
        $search_time['end_time'] = I('get.end_time');
        if(empty($search_time['start_time'])){
            $search_time['start_time'] = $s_time . ' 00:00:00';
        }else{
            $search_time['start_time'] = $search_time['start_time'] . ' 00:00:00';
        }
        if(empty($search_time['end_time'])){
            $search_time['end_time'] = $e_time . ' 23:59:59';
        }else{
            $search_time['end_time'] = $search_time['end_time'] . ' 23:59:59';
        }
        $where['o.apply_time'] = ['BETWEEN', strtotime($search_time['start_time']) . ',' . strtotime($search_time['end_time'])];
        return $where;
    }

    /**
     * @param $p  当前页
     * @param $pageSize 每页个数
     * @param $where 条件
     * @param $state 通过/不通过 1/3/4 不通过 2通过
     * @return mixed
     */
    private function getData($where,$groupBy,$location = 1)
    {
        //location 1:按人 2.安团 3.按师
        $data = D('OrdersApplyTel')->getApplyTel($where,$groupBy);

        $returnData = '';
        $sum_pass = 0; //通过总个数
        $sum_not_pass = 0; //不通过总个数
        $sum_total = 0; //总条数
        $sum_fen_total = 0; //分单总条数
        //获取客服的发单量
        $map = [];
        if($where){
            $map['a.addtime'] = $where['o.apply_time'];
        }
        $kf_orders = D("OrderPool")->getKfOrders($map);
        $shenqing_nums = D("OrderPool")->getKfOrdersCount($map);
        //区分 按人,按师,按组
        switch ($location){
            case '1':
                $anren_data = [];
                //将获取的发单数拼接 按组 所需的格式
                foreach ($kf_orders as $kf_order){
                    $anren_data[$kf_order['op_uid']]['all'] += $kf_order['all'];
                    $anren_data[$kf_order['op_uid']]['op_uid'] = $kf_order['op_uid'];
                }
                //算出每个人的申请个数(不是申请次数)
//                $shenqing_nums = D('OrdersApplyTel')->getApplyTelNum($where);
                $dd = '';
                foreach ($shenqing_nums as $shenqing_num){
                    $dd[$shenqing_num['op_uid']]['count'] += $shenqing_num['all'];
                }
                //算出申请个数总和
                foreach ($dd as $d){
                    $dd['sq_total_count'] += $d['count'];
                }
                foreach ($data as $k => $val) {
                    //获取每个客服的发单量
                    if($anren_data[$val['apply_id']]){
                        $returnData[$val['apply_id']]['kf_fen'] += $anren_data[$val['apply_id']]['all'];
                        $sum_fen_total += $anren_data[$val['apply_id']]['all'];
                        unset($anren_data[$val['apply_id']]);
                    }
                    //获取师长的信息
                    $shi = D('adminuser')->findKfInfo(str_replace(',', '', $val['kfmanager']));
                    $returnData[$val['apply_id']]['shi_name'] = $shi['name'];
                    unset($shi);
                    $returnData[$val['apply_id']]['uname'] = $val['uname'];
                    $returnData[$val['apply_id']]['kfgroup'] = $val['kfgroup'];
                    //申请个数
                    $returnData[$val['apply_id']]['sq_num'] = $dd[$val['apply_id']]['count'];
                    $returnData[$val['apply_id']]['kfgroup'] = $val['kfgroup'];
                    if ($val['status'] == 2) {
                        $returnData[$val['apply_id']]['passCount'] += $val['totalcount'];
                        $sum_pass += $val['totalcount'];
                    }
                    if ($val['status'] == 1 || $val['status'] == 3 || $val['status'] == 4) {
                        $returnData[$val['apply_id']]['notPassCount'] = $val['totalcount'];
                        $sum_not_pass += $val['totalcount'];
                    }
                    $returnData[$val['apply_id']]['totalcount'] += $val['totalcount'];
                    //汇总使用
                    $sum_total += $val['totalcount'];
                }
                $data = $returnData;
                break;
            case '2':
                $dd = '';
                foreach ($shenqing_nums as $shenqing_num){
                    $dd[$shenqing_num['kfgroup']]['count'] += $shenqing_num['all'];
                }
                //算出申请个数总和
                foreach ($dd as $d){
                    $dd['sq_total_count'] += $d['count'];
                }
                $anzu_data = [];
                //将获取的发单数拼接 按组 所需的格式
                foreach ($kf_orders as $kf_order){
                    $anzu_data[$kf_order['kfgroup']]['all'] += $kf_order['all'];
                    $anzu_data[$kf_order['kfgroup']]['kfgroup'] = $kf_order['kfgroup'];
                }
                //拼接页面数据
                foreach ($data as $k => $val){
                    $returnData[$val['kfgroup']]['totalCount'] += $val['totalcount'];
                    if($anzu_data[$val['kfgroup']]){
                        $returnData[$val['kfgroup']]['kf_fen'] += $anzu_data[$val['kfgroup']]['all'];
                        $sum_fen_total += $anzu_data[$val['kfgroup']]['all'];
                        unset($anzu_data[$val['kfgroup']]);
                    }
                    //通过个数
                    if($val['status'] == 2){
                        $returnData[$val['kfgroup']]['passCount'] += $val['totalcount'];
                        $sum_pass += $val['totalcount'];
                    }
                    //不通过个数
                    if ($val['status'] == 1 || $val['status'] == 3 || $val['status'] == 4){
                        $returnData[$val['kfgroup']]['notPassCount'] += $val['totalcount'];
                        $sum_not_pass += $val['totalcount'];
                    }
                    //获取师长的信息
                    $shi = D('adminuser')->findKfInfo(str_replace(',', '', $val['kfmanager']));
                    if($val['kfgroup'] == '0'){
                        $shi['name'] = $val['uname'];
                    }
                    $returnData[$val['kfgroup']]['shi_name'] = $shi['name'];
                    $returnData[$val['kfgroup']]['kfgroup'] = $val['kfgroup'];
                    //申请个数
                    $returnData[$val['kfgroup']]['sq_num'] = $dd[$val['kfgroup']]['count'];
                    unset($shi);
                    //汇总使用
                    $sum_total += $val['totalcount'];
                }
                $data = $returnData;
                break;
            case '3':
                $dd = '';
                foreach ($shenqing_nums as $shenqing_num){
                    $dd[$shenqing_num['kfmanager']]['count'] += $shenqing_num['all'];
                }
                //算出申请个数总和
                foreach ($dd as $d){
                    $dd['sq_total_count'] += $d['count'];
                }
                $anshi_data = [];
                //将获取的发单数拼接 按组 所需的格式
                foreach ($kf_orders as $kf_order){
                    $anshi_data[$kf_order['kfmanager']]['all'] += $kf_order['all'];
                    $anshi_data[$kf_order['kfmanager']]['kf_fen'] = $kf_order['kfmanager'];
                }
                //拼接页面数据
                foreach ($data as $k => $val){
                    //判断 师长自己添加时候 将自己id给自己的组
                    if($val['kfmanager'] == ''){
                        $val['kfmanager'] = $val['apply_id'] . ',';
                    }
                    $returnData[$val['kfmanager']]['totalCount'] += $val['totalcount'];
                    if($anshi_data[$val['kfmanager']]){
                        $returnData[$val['kfmanager']]['kf_fen'] += $anshi_data[$val['kfmanager']]['all'];
                        $sum_fen_total += $anshi_data[$val['kfmanager']]['all'];
                        unset($anshi_data[$val['kfmanager']]);
                    }
                    //通过个数
                    if($val['status'] == 2){
                        $returnData[$val['kfmanager']]['passCount'] += $val['totalcount'];
                        $sum_pass += $val['totalcount'];
                    }
                    //不通过个数
                    if ($val['status'] == 1 || $val['status'] == 3 || $val['status'] == 4){
                        $returnData[$val['kfmanager']]['notPassCount'] += $val['totalcount'];
                        $sum_not_pass += $val['totalcount'];
                    }
                    //获取师长的信息
                    $shi = D('adminuser')->findKfInfo(str_replace(',', '', $val['kfmanager']));
                    $returnData[$val['kfmanager']]['shi_name'] = $shi['name'];
                    $returnData[$val['kfmanager']]['kfmanager'] = str_replace(',', '', $val['kfmanager']);
                    //申请个数
                    $returnData[$val['kfmanager']]['sq_num'] = $dd[$val['kfmanager']]['count'];
                    unset($shi);
                    //汇总使用
                    $sum_total += $val['totalcount'];
                }
                $data = $returnData;
                break;
        }
        $returnSum = [
            'passCount'=>$sum_pass,
            'notPassCount'=>$sum_not_pass,
            'totalcount'=>$sum_total,
        ];
        //按人 添加申请个数
        if ($dd) {
            $returnSum['sum_sq_total'] = $dd['sq_total_count'];
        }
        //按组, 按师 分单汇总
        if ($sum_fen_total) {
            $returnSum['sumFenTotal'] = $sum_fen_total;
        }
        $this->assign('sum_data',$returnSum);
        $result['data'] = $data;
        return $result;
    }

    private function getShenpiData($where)
    {
        $sum_pass = 0; //通过总个数
        $sum_not_pass = 0; //不通过总个数
        $sum_total = 0; //总条数
        $returnData = [];
        $data = D('OrdersApplyTel')->getApplyTelByPassId($where);
        foreach ($data as $k => $val){
            $returnData[$val['passer_id']]['uname'] = $val['uname'];
            //1.只有点通过和不通过 才去记录
            //通过个数
            if($val['status'] == 2){
                $returnData[$val['passer_id']]['passCount'] += $val['totalcount'];
                $sum_pass += $val['totalcount'];
                $returnData[$val['passer_id']]['totalCount'] += $val['totalcount'];
                //汇总使用
                $sum_total += $val['totalcount'];
            }
            //不通过个数
            if ($val['status'] == 3){
                $returnData[$val['passer_id']]['notPassCount'] += $val['totalcount'];
                $sum_not_pass +=  $val['totalcount'];
                $returnData[$val['passer_id']]['totalCount'] += $val['totalcount'];
                //汇总使用
                $sum_total += $val['totalcount'];
            }
        }
        $returnSum = [
            'passCount'=>$sum_pass,
            'notPassCount'=>$sum_not_pass,
            'totalcount'=>$sum_total,
        ];
        $this->assign('sum_data',$returnSum);
        return $returnData;
    }
}