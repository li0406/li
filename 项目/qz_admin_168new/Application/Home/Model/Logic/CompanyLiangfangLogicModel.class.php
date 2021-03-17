<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 * 未量房订单/二次回访订单
 */

namespace Home\Model\Logic;


use Common\Enums\CompanyInfo;
use Common\Enums\LiangFangInfo;
use Home\Model\Db\CompanyLiangFangModel;

class CompanyLiangfangLogicModel
{

    private $dept = array(
        "总裁办" => "zcb",
        "推广二部" => "tg2",
        "推广一部" => "tg1",
        "渠道部" => "qd",
        "公装部" => "gz",
        "新媒体部" => "media",
        "线下广告" => "online",
        "品牌部" => 'brand'
    );
    /**
     * 获取未量房订单总数据
     * @param $data
     * @return array
     */
    public function getNotLfListCount($data)
    {
        $map = $this->_getNotLfMap($data);
        //获取能看到的客服
        $op_uid = $this->getKfList();
        //未回访总数(默认查询)
        $where['t1.xianshi'] = $map['t1.xianshi'];//将固定条件取出
        if($op_uid){
            $where['p.op_uid'] = ['in',$op_uid];
        }
        $where['lf_status'] = '-1';//未回访的数据
        $statistics['no_back'] = D("Home/Db/OrderCompanyReview")->getNotLfListCount($where);
        //已回访总数
        $where['lf_status'] = '-2';//已回访的数据
        $statistics['back'] = D("Home/Db/OrderCompanyReview")->getNotLfListCount($where);
        if ($data['status'] == '-2') {
            $count = $statistics['back'];
        } else {
            $count = $statistics['no_back'];
        }
        return ['count' => $count, 'statistics' => $statistics];
    }

    /**
     * 获取未回访的未量房订单数
     */
    public function getNotBackCount()
    {
        $map = $this->_getNotLfMap(['status'=>'-1']);
        //获取能看到的客服
        $op_uid = $this->getKfList();
        //未回访总数(默认查询)
        $where['t1.xianshi'] = $map['t1.xianshi'];//将固定条件取出
        if($op_uid){
            $where['p.op_uid'] = ['in',$op_uid];
        }
        $where['lf_status'] = '-1';//未回访的数据
        return D("Home/Db/OrderCompanyReview")->getNotLfListCount($where);
    }

    public function getNotLfList($data,$count, $pageIndex = 1, $pageCount = '20')
    {
        $map = $this->_getNotLfMap($data);
        //强制数字整数
        $pageIndex = intval($data['p']) <= 0 ? $pageIndex : intval($data['p']);
        $pageCount = intval($pageCount);
        $list = D("Home/Db/OrderCompanyReview")->getNotLfList($map,($pageIndex - 1) * $pageCount, $pageCount);
        $remark_status = LiangFangInfo::getBackRemarkBust()+LiangFangInfo::getBackRemarkFalse();
        $back_status = LiangFangInfo::getBackStatus();
        foreach ($list as $k => $v) {
            $list[$k]['back_remark'] = $remark_status[$v['remark_status']];
            $list[$k]['b_status'] = $v['back_status'];
            $list[$k]['back_status'] = $back_status[$v['back_status']];
        }
        import('Library.Org.Util.Page');
        $p = new \Page($count, $pageCount);
        $show = $p->show();
        return ['list' => $list, 'page' => $show];
    }

    /**
     * 获取未量房原因
     * @param $id
     * @return mixed
     */
    public function getNotLfReason($id)
    {
        $data = D("Home/Db/OrderCompanyReview")->getReviewInfoByOrderId($id);
        $list = [];
        $reason = [];
        foreach ($data as $k => $v) {
            if($v['reason']){
                if (explode(',', $v['reason'])!= '') {
                    $reason = array_merge(explode(',', $v['reason']), $reason);
                }
            }
            if($v['remark'] != ''){
                $list['remark'] .=$v['remark'] .';';
            }
        }
        foreach (array_count_values($reason) as $k=>$v){
            $info = LiangFangInfo::getFalseReason($k);
            $list['reason'][$info] = $v;
        }
        return $list;
    }

    public function getNotLfCompanyList($id)
    {
        $list = D("Home/Db/OrderCompanyReview")->getNotLfCompanyList($id);
        foreach ($list as $k=>$v){
            if($v['tags']){
                $list[$k]['tags'] = CompanyInfo::getCompanyTags(explode(',', $v['tags']));
            }
        }
        return $list;
    }

    /**
     * 保存回访数据
     * @param $data
     * @return mixed
     */
    public function saveBackInfo($data)
    {
        $admin = getAdminUser();
        $save['op_uid'] = $admin['id'];
        //订单id
        if ($data['orderid']) {
            $save['order_id'] = $data['orderid'];
        }
        //回访状态
        if ($data['backstatus']) {
            $save['status'] = $data['backstatus'];
        }
        //回访备注
        if ($data['remark']) {
            $save['back_remark'] = $data['remark'];
        }
        //二次回访需求
        if ($data['two_remark']) {
            $save['remark'] = $data['two_remark'];
        }
        //获取量房信息
        $info = D('Home/Db/CompanyLiangFang')->findLiangfangInfo(['order_id' => ['eq', $data['orderid']]]);
        if($info){
            $save['update_time'] = time();
            return D('Home/Db/CompanyLiangFang')->saveLiangfangInfo(['order_id' => ['eq', $data['orderid']]],$save);
        }else{
            $save['create_time'] = time();
            $save['update_time'] = time();
            return D('Home/Db/CompanyLiangFang')->addLiangfangInfo($save);
        }
    }

    public function getPushCompanyInfo($id)
    {
        $list = D('Home/Db/CompanyLiangFang')->getPushCompanyInfo(['order_id' => ['eq', $id]]);
        $returnData = [];
        foreach ($list as $k => $v) {
            $returnData[$v['company_id']] = $v;
        }
        return $returnData;
    }
    /**
     * 将回访数据推送装修公司
     * @param $data
     */
    public function pushCompanyInfo($data)
    {
        $save = [];
        //为下面拼接保存数据做准备
        foreach ($data['choose_lf_status'] as $k=>$v){
            $data['choose_lf_status'][$v] = $v;
            unset($data['choose_lf_status'][$k]);
        }
        foreach ($data['choose_qy_status'] as $k=>$v){
            $data['choose_qy_status'][$v] = $v;
            unset($data['choose_qy_status'][$k]);
        }
        //拼接保存数据
        foreach ($data['choose_company'] as $k=>$v){
            $save[$v]['company_id'] = $v;
            $save[$v]['order_id'] = $data['orderid'];
            $save[$v]['create_time'] = time();
            $save[$v]['status'] = 0;
            $save[$v]['is_read'] = 1;
            //已量房状态
            if($data['choose_lf_status'][$v]){
                $save[$v]['status'] = 1;
            }
            //已签约状态
            if($data['choose_qy_status'][$v]){
                $save[$v]['status'] = 2;
            }
        }
        return D('Home/Db/CompanyLiangFang')->addAllLfCompany($save);
    }

    private function _getNotLfMap($data)
    {
        //默认查询 订单分配的所有装修公司,全部勾选未量房数据
        $map = ['t1.xianshi' => ['eq', 1]];
        if (!empty($data['displaynumber'])) {
            //显号审核
            switch ($data['displaynumber']) {
                case '1':
                    $map['o.openeye_st'] = array('EXP', ' IS NULL ');
                    break;
                case '2':
                    $map['o.openeye_st'] = ['eq', 1];
                    break;
                case '3':
                    $map['o.openeye_st'] = ['eq', 2];
                    break;
                case '4':
                    $map['o.openeye_st'] = ['eq', 3];
                    break;
            }
        }
        //订单号
        if (!empty($data['condition'])) {
            $map['o.id'] = ['eq', $data['condition']];
        }
        //订单城市
        if (!empty($data['city'])) {
            $map['o.cs'] = ['eq', $data['city']];
        }

        //回访状态
        //选择未量房,就是未做回访的订单 , 则是过滤掉qz_company_liangfang表里的数据(回访后则会在该表添加数据),反则同理
        //-1 :未做回访的未量房订单 , -2:已回访的未量房订单
        if ($data['status'] == '-1' || $data['status'] == '-2') {
            $map['lf_status'] = $data['status'];
        } elseif (!empty($data['status'])) {
            $map['l.status'] = ['eq', $data['status']];
        }elseif ($data['status'] == 0 && isset($data['status'])){
            //如果搜索框出结果,则不添加任何状态查询
            unset($map['lf_status']);
        } else {
            $map['lf_status'] = '-1';
        }
        //回访订单备注
        if (!empty($data['remarks'])) {
            $map['t1.back_remark'] = ['eq', $data['remarks']];
        }

        //获取可查看 客服
        $op_uid = $this->getKfList($data);
        if (!empty($op_uid)) {
            $map['p.op_uid'] = array('IN', $op_uid);
        }
        //如果是ajax请求,则不添加查询状态
        if ($data['is_ajax'] == 1) {
            unset($map['lf_status']);
        }
        return $map;
    }

    private function getKfList($data = []){

        $admin = getAdminUser();
        $operaters = D('Adminuser')->getLiangfangKfManagerList($admin['id'], $admin['uid']);
        //1.如果是客服，客服组长(团长) 可以查看本组数据，客服主管(师长)可以查看管辖客服
        //1.1限制客服,客服组长,客服主管
        $op_uid = $operaters['ids'];
        if (in_array($admin['uid'], array(2, 31, 30))) {
            $op_uid = in_array($data['op_uid'], $operaters['ids']) ? intval($data['op_uid']) : $operaters['ids'];
        }
        //1.2不限制,能查看所有客服
        if (!empty($data['op_uid'])) {
            $op_uid = $data['op_uid'];
        }
        return $op_uid;
    }

    /**
     * 根据条件设置二次回访查询条件数组
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @return array
     */
    protected function _getTwiceBackMap($company_id, $search, $isread)
    {
        $map = array(
            't.com' => array('EQ', $company_id),
            'b.on' => array('EQ', 4)
        );
        if (!empty($search)) {
            $map['_complex'] = array(
                'b.xiaoqu' => array('LIKE', "%$search%"),
                'b.tel' => array('EQ', $search),
                'b.id' => array('LIKE', "%$search%"),
                '_logic' => 'OR'
            );
        }
        if ($isread !== "" && $isread !== null) {
            $map["t.isread"] = array("EQ", $isread);
        }
        return $map;
    }

    /**
     * 获取历史签单小区信息
     * @param  [type] $xiaoqu [description]
     * @return [type]         [description]
     */
    public function orderHistory($xiaoqu, $cs)
    {
        if (!empty($xiaoqu)) {
            //获取分词
            $result = getFenCi($xiaoqu);

            $fxList[] = $xiaoqu;
            foreach ($result as $key => $value) {  //取分词结果为2个字以上的
                if ((mb_strlen($value['word'], 'utf-8')) > 1) {
                    $fxList[] = $value['word'];
                }
            }

            //查询小区签单历史
            $result = D("Orders")->getQianDanHistory($fxList, $cs);
            if (count($result) > 0) {
                $list[$xiaoqu] = array();
                foreach ($result as $key => $value) {
                    if ($value["xiaoqu"] == $xiaoqu) {
                        $list[$xiaoqu]["child"][] = array(
                            "jc" => $value["jc"],
                            "count" => $value["count"],
                            "on" => $value["on"],
                            "time" => date("Y-m-d", $value["qiandan_addtime"])
                        );
                    } else {
                        $list[$value["xiaoqu"]]["child"][] = array(
                            "jc" => $value["jc"],
                            "count" => $value["count"],
                            "on" => $value["on"],
                            "time" => date("Y-m-d", $value["qiandan_addtime"])
                        );
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 添加订单二次回访电话表
     * @param $id 对应电话呼叫记录id
     * @param $orderid 订单id
     */
    public function saveTelBackInfo($id, $orderid)
    {
        if ($id) {
            $save = [
                'order_id' => $orderid,
                'ordercall_id' => $id,
                'create_time' => time(),
                'op_uid' => session("uc_userinfo.id"),
            ];
            return D('Home/Db/CompanyLiangFangTelBack')->addOrderTelBack($save);
        }
    }

    /**
     * 获取订单量房统计
     * @param $get[搜索参数]
     * @param int $is_export [是否是导出操作 1:是 0:否]
     * @return array
     */
    public function getOrdersLiangfang($get, $is_export = 0)
    {
        //获取/分单/赠单
        $map = [
            'on' => ['eq', 4],
            'type_fw' => ['in', [1, 2]],
            'time_real' => [['EGT', strtotime(date("Y-m-d").' 00:00:00')],['ELT', time()], 'AND']
        ];
        if ($get['start'] && $get['end']) {
            $map['time_real'] = [['EGT', strtotime($get['start'] . ' 00:00:00')], ['ELT', strtotime($get['end'] . ' 23:59:59')], 'AND'];
        }
        //查询部门
        $depts = [];
        if ($get['dept']) {
            $orderSourceLogic = new OrderSourceLogicModel();
            $department = $orderSourceLogic->getDepartmentList();
            if (!is_numeric($get['dept'])) {
                foreach ($this->dept as $key => $value) {
                    if ($get['dept'] == $value) {
                        $belong = $key;
                        break;
                    }
                }
                foreach ($department[$belong]["child"] as $key => $value) {
                    $depts[] = $value["id"];
                }
                unset($depts[0]);
            } else {
                $depts[] = $get['dept'];
            }
        }
        if (count($depts) > 0) {
            $map['dept'] = ['in', $depts];
        }
        //查询渠道来源组
        if ($get['group']) {
            $map['group_id'] = ['eq', $get['group']];
        }
        //查询渠道来源标识
        if ($get['src']) {
            $map['src'] = ['eq', $get['src']];
        }
        //查询渠道代号
        if ($get['alias']) {
            $map['alias'] = ['eq', $get['alias']];
        }
        //查询订单状态
        if ($get['order_status']) {
            $map['type_fw'] = ['eq', $get['order_status']];
        }
        //量房状态
        if ($get['lf_status']) {
            $map['lf_status'] = $get['lf_status'];
        }
        $list = [];
        $pageIndex = $get['p'];
        $pageCount = 30;
        $liangfangModel = new CompanyLiangFangModel();
        $count = $liangfangModel->getOrdersLiangfangCount($map);
        if ($count > 0) {
            //强制数字整数
            $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
            $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
            //判断如果是导出操作,则不限制查询数
            if($is_export == 1){
                $pageIndex = null;
                $pageCount = null;
            }
            $list = $liangfangModel->getOrdersLiangfangList($map,($pageIndex - 1) * $pageCount, $pageCount);
        }
        import('Library.Org.Util.Page');
        $p = new \Page($count, $pageCount);
        $show = $p->show();
        return ['list' => $list, 'page' => $show];
    }
}