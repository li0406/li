<?php

namespace Home\Model\Logic;

use Home\Model\Db\HzsCompanyModel;
use Home\Model\OrdersModel;
use Home\Model\PartnerModel;
use Home\Model\Db\HzsSourceModel;

class HzsLogicModel
{
    public function checkSource($source)
    {
        if (count($source) == 0) {
            return ['error_code' => 400, 'error_msg' => '请选择渠道标识!'];
        }
        $where = [
            's.sourceid' => ['in', $source],
            's.status' => ['eq', 1]
        ];
        $list = (new PartnerModel())->getHzsInfoBySource($where, 'src.name,c.name hzs_name');
        if (count($list) > 0) {
            $msg = '';
            foreach ($list as $v) {
                $msg .= $v['name'] . '已被' . $v['hzs_name'] . '添加；';
            }
            return ['error_code' => 400, 'error_msg' => $msg];
        }
        return ['error_code' => 0];
    }


    public function getSourceListByCompanyId($company_id){
        $hzsSourceModel = new HzsSourceModel();
        return $hzsSourceModel->getSourceListByCompanyId($company_id);
    }

    public function findHzsList($query)
    {
        $hzsSourceModel = new HzsCompanyModel();
        return $hzsSourceModel->findHzsList($query);
    }

    public function getHzsInfo($company_id)
    {
        $hzsSourceModel = new HzsCompanyModel();
        return $hzsSourceModel->getById($company_id);
    }

    public function getPayMode($mode){
        $pay_mode = '';
        switch ($mode) {
            case 1:
                $pay_mode = '预付';
                break;
            case 2:
                $pay_mode = '周结';
                break;
            case 3:
                $pay_mode = '半月结';
                break;
            case 4:
                $pay_mode = '月结';
                break;
            default:
                break;
        }
        return $pay_mode;
    }

    public function getCooperateMode($cooperate_mode){
        switch ($cooperate_mode) {
            case 1:
                $hzs_mode = 'CPA（分单）';
                break;
            case 2:
                $hzs_mode = 'CPT';
                break;
            case 3:
                $hzs_mode = 'CPM';
                break;
            case 4:
                $hzs_mode = 'CPC';
                break;
            case 5:
                $hzs_mode = 'CPD';
                break;
            case 6:
                $hzs_mode = 'CPS';
                break;
            case 7:
                $hzs_mode = '免费';
                break;
            case 8:
                $hzs_mode = 'CPA（发单）';
                break;
            default:
                break;
        }
        return $hzs_mode;
    }

    public function getHzsOrder($company_id,$begin,$end)
    {
        $monthStart = strtotime($begin);
        $monthEnd = strtotime($end) + 86400 -1;
        $hzsSourceModel = new HzsCompanyModel();
        return $hzsSourceModel->getHzsOrder($company_id,$monthStart,$monthEnd);
    }

    public function getHzsRealOrder($company_id,$begin,$end)
    {
        $monthStart = strtotime($begin);
        $monthEnd = strtotime($end) + 86400 -1;
        $hzsSourceModel = new HzsCompanyModel();
        return $hzsSourceModel->getHzsRealOrder($company_id,$monthStart,$monthEnd);
    }

    public function getHzsLiangfangOrder($company_id,$date)
    {
        $monthStart = strtotime("-30 day",strtotime($date));
        $monthEnd = strtotime($date);
        $hzsSourceModel = new HzsCompanyModel();
        return $hzsSourceModel->getHzsLiangfangOrder($company_id,$monthStart,$monthEnd);

    }

    //合作商管理列表
    public function getHzsList($get, $offset, $limit)
    {
        $model = new HzsCompanyModel();
        $result = $model->getHzsList($get, $offset, $limit);
        $list = array();
        foreach (@$result as $k => $v) {
            $list[$k] = $v;
            $list[$k]['psw'] = authcode($v['psw'], 'DECODE', 'asdfasdfasdflkas;ldfkjs;dlfkj');
            //获取账户下的子账户
            $childAccountInfo = $model->getChildHzsInfo($v['id'],null);
            $list[$k]['account_child_count'] = count($childAccountInfo);
            $accountId = array_column($childAccountInfo, 'id');
            $id = count($accountId)<1?current($accountId):$accountId;
            //获取子账户数及渠道数
            $list[$k]['number'] = D('Partner')->getCountSource($id) ?? 0;
        }

        return $list;
    }

    public function getHzsListCount($get)
    {
        $model = new HzsCompanyModel();
        return $model->getHzsListCount($get);
    }

    //保存合作商设置
    public function hzsSave($post)
    {
        $model = new PartnerModel();

        $input = [
            'account' => trim($post['account']),
            'psw' => $post['psw'],
            'name' => trim($post['name']),
            'short' => trim($post['short']),
            'join_time' => time(),
            'join_capital' => $post['join_capital'] ?? '',
            'test_starttime' => $post['$post'] ?? 0,
            'test_endtime' => $post['test_endtime'] ?? 0,
            'cooperate_starttime' => $post['cooperate_starttime'] ?? 0,
            'cooperate_endtime' => $post['cooperate_endtime'] ?? 0,
            'cooperate_mode' => $post['cooperate_mode'] ?? 0,
            'pay_mode' => $post['pay_mode'] ?? 0,
            'cooperate_price' => $post['cooperate_price'] ?? '',
            'cooperate_link' => $post['cooperate_link'] ?? '',
            'licence_logo' => $post['licence_logo'] ?? '',
            'linkman' => $post['linkman'] ?? '',
            'linkposition' => $post['linkposition'] ?? '',
            'linkwx' => $post['linkwx'] ?? '',
            'linkqq' => $post['linkqq'] ?? '',
            'yy_id' => $post['yy_id'],
            'classify' => $post['classify'] ?? 1,
            'parent_id' => $post['parent_id'] ?? 0,
            'arrears_order' => $post['arrears_order'] ?? 0,
            'create_time' => time(),
            'update_time' => time(),
            'status' => $post['status'] ?? 1,
        ];

        return $model->addCompany($input);
    }

    public function hzsUpdate($id, $input)
    {
        $model = new HzsCompanyModel();
        return $model->hzsUpdate($id,$input);
    }

    /**
     * @des:获取所属合作商的合作渠道信息
     * @param $name
     * @param $account
     * @param $childId
     * @param $begin
     * @param $end
     * @return array
     */
    public function getHzsChildInfo($id, $account, $childId, $begin, $end)
    {
        $result = [];
        $model = new HzsCompanyModel();
        $hzsLogic = new HzsLogicModel();
        //查询总合作商账户下数据
        if (strlen($id) > 0 || !empty($account) && (empty($begin) || empty($end))) {
            $list = $model->getChildAccountInfoByParent($id ? [$id] : [], $account);

            //数据整理
            foreach (@$list as $k => $v) {
                $result[$k]['id'] = $v['id'];
                $result[$k]['name'] = $v['name'];
                $result[$k]['cooperate_mode'] = $hzsLogic->getCooperateMode($v['cooperate_mode']);
                $result[$k]['pay_mode'] = $hzsLogic->getPayMode($v['pay_mode']);
                $result[$k]['yy_name'] = $v['yy_name'] ? : "-";
                $result[$k]['begin'] = $begin;
                $result[$k]['end'] = $end;
                $result[$k]['create_time'] = !empty($v['create_time']) ? date('Y-m-d', $v['create_time']) : '';
                //发单量
                $result[$k]['count'] = 0;
                //实际分单量
                $result[$k]['real_fen'] = 0;
                //量房量
                $result[$k]['lf_count'] = 0;
                //量房率
                $result[$k]['lf_rate'] = 0;
            }
            return $result;
        } else {
            $list = $model->getChildAccountInfo($childId);
            //数据整理
            foreach (@$list as $k => $v) {
                $result[$k]['id'] = $v['id'];
                $result[$k]['name'] = $v['name'];
                $result[$k]['cooperate_mode'] = $hzsLogic->getCooperateMode($v['cooperate_mode']);
                $result[$k]['pay_mode'] = $hzsLogic->getPayMode($v['pay_mode']);
                $result[$k]['yy_name'] = $v['yy_name'] ? : "-";
                $result[$k]['begin'] = $begin;
                $result[$k]['end'] = $end;
                $result[$k]['create_time'] = !empty($v['create_time']) ? date('Y-m-d', $v['create_time']) : '';

                //获取分单/发单数据
                $data = $hzsLogic->getHzsOrder($v['id'], $begin, $end);
                $result[$k]['count'] = empty($data['count']) ? 0 : $data['count'];//发单量

                //实际分单量
                $data = $hzsLogic->getHzsRealOrder($v['id'], $begin, $end);
                $result[$k]['real_fen'] = empty($data['fen']) ? 0 : $data['fen'];//实际分单量

                //量房量
                $data = $hzsLogic->getHzsLiangfangOrder($v['id'], $begin);
                $result[$k]['lf_count'] = $data["count"];

                //前30天的分单量
                $monthStart = date("Y-m-d", strtotime("-30 day", strtotime($begin)));
                $monthEnd = date("Y-m-d", strtotime($begin));
                $data = $hzsLogic->getHzsOrder($v['id'], $monthStart, $monthEnd);

                //量房率
                $result[$k]['lf_rate'] = 0;
                if ($data['count'] > 0 ) {
                    $result[$k]['lf_rate'] = setInfNanToN(round($result[$k]['lf_count']/$result[$k]['fen'],2)*100);
                }
            }
            if (empty($list)) {
                $result[0] = [
                    'id' => $childId,
                    'lf_rate' => 0,
                    'count' => 0,
                    'fen' => 0,
                    'lf_count' => 0
                ];
            }
            return $result;
        }
    }

    /**
     *  @des:获取所属合作列表
     * @param null $name
     * @param null $account
     * @return mixed
     */
    public function findHzsParentList($name, $account)
    {
        $hzsSourceModel = new HzsCompanyModel();
        return $hzsSourceModel->findHzsParentList($name, $account);
    }

    //按合作商统计
    public function getHzsAnalysisByHzsParent(array $id_arr, $account, $begin, $end)
    {
        $return = [];
        $model = new HzsCompanyModel();
        $count = $model->getHzsListCount(['id_arr' => $id_arr, 'account' => $account]);
        //分页预处理
        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, 20);
        $pageshow = $pageClass->show();
        //分页数据处理
        if ($count > 0) {
            $list = $model->getHzsList(['id_arr' => $id_arr, 'account' => $account], $pageClass->firstRow, $pageClass->listRows);
        } else {
            return [
                "list" => $return,
                "count" => $count,
                "page" => $pageshow
            ];
        }
        $parent_id = [];
        foreach (@$list as $value) {
            array_push($parent_id, (int)$value['id']);
        }
        $orderModel = new OrdersModel();
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['name'] = $v['name'];
            $orderInfoUv = $orderModel->getOrderSourceParent($v['id'], $begin, $end);
            $return[$k]['uv'] = $orderInfoUv['count'] ?? 0;
            //根据渠道统计订单
            $orderInfo = $orderModel->getHzsAnalysisByHzsParent($v['id'], $begin, $end);
            $return[$k]['fadan'] = intval($orderInfo['fadan_count'] ?? 0);
            $faOrderInfo = $orderModel->getHzsAnalysisFaByHzsParent($v['id'], $begin, $end);
            $return[$k]['fendan'] = $faOrderInfo ?? 0;
            $return[$k]['fendan_rate'] = setInfNanToN(round($return[$k]['fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $realFenDanInfo = $orderModel->getHzsAnalysisByHzsParentRealFenDan($v['id'], $begin, $end);
            $return[$k]['real_fendan'] = intval($realFenDanInfo['count'] ?? 0);
            $return[$k]['real_fedan_rate'] = setInfNanToN(round($return[$k]['real_fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $return[$k]['zhuche'] = intval($return[$k]['fendan']);
            $return[$k]['real_zhuche'] = intval($return[$k]['real_fendan']);
        }
        return [
            "list" => $return,
            "count" => $count,
            "page" => $pageshow
        ];
    }

    //按合作商账户统计
    public function getHzsAnalysisByHzsId(array $id_arr,$account, $begin, $end)
    {
        $return = [];
        $model = new HzsCompanyModel();
        $count = $model->getChildHzsInfoCount($id_arr, $account, true);
        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, 20);
        $pageshow = $pageClass->show();
        //分页数据处理
        if ($count > 0) {
            $list = $model->getChildHzsInfo($id_arr, $account, $pageClass->firstRow, $pageClass->listRows, true);
        } else {
            return [
                "list" => $return,
                "count" => $count,
                "page" => $pageshow
            ];
        }
        $parent_id = [];
        foreach (@$list as $value) {
            array_push($parent_id, (int)$value['id']);
        }
        $orderModel = new OrdersModel();
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['name'] = $v['name'];
            $orderInfoUv = $orderModel->getOrderSourceByHzs($v['id'], $begin, $end);
            $return[$k]['uv'] = $orderInfoUv['count'] ?? 0;
            //根据渠道统计订单
            $orderInfo = $orderModel->getHzsAnalysisByHzsId($v['id'], $begin, $end);
            $return[$k]['fadan'] = $orderInfo['fadan_count'] ?? 0;
            $faOrderInfo = $orderModel->getHzsAnalysisFaByHzsId($v['id'], $begin, $end);
            $return[$k]['fendan'] = $faOrderInfo ?? 0;
            $return[$k]['fendan_rate'] = setInfNanToN(round($return[$k]['fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $realFenDanInfo = $orderModel->getHzsAnalysisByHzsIdRealFenDan($v['id'], $begin, $end);
            $return[$k]['real_fendan'] = $realFenDanInfo['count'] ?? 0;
            $return[$k]['real_fedan_rate'] = setInfNanToN(round($return[$k]['real_fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $return[$k]['zhuche'] = $return[$k]['fendan'];
            $return[$k]['real_zhuche'] = $return[$k]['real_fendan'];
        }

        return [
            "list" => $return,
            "count" => $count,
            "page" => $pageshow
        ];
    }

    //按渠道来源统计
    public function getHzsAnalysisBySource($begin, $end, $id_arr, $account, $source)
    {
        $return = [];
        $model = new HzsCompanyModel();
        $count = $model->getHzsSourceListCount($id_arr,$account,$source);
        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, 20);
        $pageshow = $pageClass->show();
        //分页数据处理
        if ($count > 0) {
            $list = $model->getHzsSourceList($id_arr,$account, $source, $pageClass->firstRow, $pageClass->listRows);
        } else {
            return [
                "list" => $return,
                "count" => $count,
                "page" => $pageshow
            ];
        }
        $orderModel = new OrdersModel();
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['name'] = $v['name'];
            $orderInfoUv = $orderModel->getOrderSourceUvBySource($v['id'], $begin, $end);
            $return[$k]['uv'] = $orderInfoUv['count'] ?? 0;
            //根据渠道统计订单
            $orderInfo = $orderModel->getHzsAnalysisBySource($v['id'], $begin, $end);
            $return[$k]['fadan'] = $orderInfo['fadan_count'] ?? 0;
            $faOrderInfo = $orderModel->getHzsAnalysisFaBySource($v['id'], $begin, $end);
            $return[$k]['fendan'] = $faOrderInfo['fendan_count'] ?? 0;
            $return[$k]['fendan_rate'] = setInfNanToN(round($return[$k]['fendan'] / $return[$k]['fadan'], 2) * 100);
            $realFenDanInfo = $orderModel->getHzsAnalysisBySourceRealFenDan($v['id'], $begin, $end);
            $return[$k]['real_fendan'] = $realFenDanInfo['count'] ?? 0;
            $return[$k]['real_fedan_rate'] = setInfNanToN(round($return[$k]['real_fendan'] / $return[$k]['fadan'], 2) * 100);
            $return[$k]['zhuche'] = $return[$k]['fendan'];
            $return[$k]['real_zhuche'] = $return[$k]['real_fendan'];
        }
        return [
            "list" => $return,
            "count" => $count,
            "page" => $pageshow
        ];
    }

    //订单详情
    public function getHzsAnalysisOrderInfoByHzsParent($begin, $end, $id_arr, $account, $source)
    {
        $return = [];
        $orderModel = new OrdersModel();
        $count = $orderModel->getHzsAnalysisOrderInfoByHzsParentCount($id_arr, $account, $source, $begin, $end);
        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, 20);
        $pageshow = $pageClass->show();
        //分页数据处理
        if ($count > 0) {
            $list = $orderModel->getHzsAnalysisOrderInfoByHzsParent($id_arr, $account, $source, $begin, $end, $pageClass->firstRow, $pageClass->listRows);
        } else {
            return [
                "list" => $return,
                "count" => $count,
                "page" => $pageshow
            ];
        }
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['time_real'] = date('Y-m-d',$v['time_real']);
            $return[$k]['name'] = $v['name'];
            $return[$k]['reamks'] = $v['remarks'];
            $return[$k]['cs'] = $v['cname'];
            $return[$k]['tel'] = $v['tel'];
            $return[$k]['on'] = $v['on'];
        }
        return [
            "list" => $return,
            "count" => $count,
            "page" => $pageshow
        ];
    }


    //按合作商统计-export
    public function getHzsAnalysisByHzsParentExport(array $id_arr, $begin, $end)
    {
        $return = [];
        $model = new HzsCompanyModel();
        $list = $model->getHzsList(['id_arr' => $id_arr]);
        $parent_id = [];
        foreach (@$list as $value) {
            array_push($parent_id, (int)$value['id']);
        }
        $orderModel = new OrdersModel();
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['name'] = $v['name'];
            $orderInfoUv = $orderModel->getOrderSourceParent($v['id'], $begin, $end);
            $return[$k]['uv'] = $orderInfoUv['count'] ?? 0;
            //根据渠道统计订单
            $orderInfo = $orderModel->getHzsAnalysisByHzsParent($v['id'], $begin, $end);
            $return[$k]['fadan'] = intval($orderInfo['fadan_count'] ?? 0);
            $faOrderInfo = $orderModel->getHzsAnalysisFaByHzsParent($v['id'], $begin, $end);
            $return[$k]['fendan'] = $faOrderInfo ?? 0;
            $return[$k]['fendan_rate'] = setInfNanToN(round($return[$k]['fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $realFenDanInfo = $orderModel->getHzsAnalysisByHzsParentRealFenDan($v['id'], $begin, $end);
            $return[$k]['real_fendan'] = intval($realFenDanInfo['count'] ?? 0);
            $return[$k]['real_fedan_rate'] = setInfNanToN(round($return[$k]['real_fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $return[$k]['zhuche'] = intval($return[$k]['fendan']);
            $return[$k]['real_zhuche'] = intval($return[$k]['real_fendan']);
        }
        return $return;
    }

    //按合作商账户统计-export
    public function getHzsAnalysisByHzsIdExport(array $id_arr, $account,$begin, $end)
    {
        $return = [];
        $model = new HzsCompanyModel();
        $list = $model->getChildHzsInfo($id_arr, $account, null, null, null, true);
        $parent_id = [];
        foreach (@$list as $value) {
            array_push($parent_id, (int)$value['id']);
        }
        $orderModel = new OrdersModel();
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['name'] = $v['name'];
            $orderInfoUv = $orderModel->getOrderSourceByHzs($v['id'], $begin, $end);
            $return[$k]['uv'] = $orderInfoUv['count'] ?? 0;
            //根据渠道统计订单
            $orderInfo = $orderModel->getHzsAnalysisByHzsId($v['id'], $begin, $end);
            $return[$k]['fadan'] = $orderInfo['fadan_count'] ?? 0;
            $faOrderInfo = $orderModel->getHzsAnalysisFaByHzsId($v['id'], $begin, $end);
            $return[$k]['fendan'] = $faOrderInfo ?? 0;
            $return[$k]['fendan_rate'] = setInfNanToN(round($return[$k]['fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $realFenDanInfo = $orderModel->getHzsAnalysisByHzsIdRealFenDan($v['id'], $begin, $end);
            $return[$k]['real_fendan'] = $realFenDanInfo['count'] ?? 0;
            $return[$k]['real_fedan_rate'] = setInfNanToN(round($return[$k]['real_fendan'] / $return[$k]['fadan'], 4) * 100).'%';
            $return[$k]['zhuche'] = $return[$k]['fendan'];
            $return[$k]['real_zhuche'] = $return[$k]['real_fendan'];
        }
        return $return;
    }

    //按渠道来源统计-export
    public function getHzsAnalysisBySourceExport(array $id_arr, $account, $source, $begin, $end)
    {
        $return = [];
        $model = new HzsCompanyModel();
        $list = $model->getHzsSourceList($id_arr, $account, $source, null, null);
        $orderModel = new OrdersModel();
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['name'] = $v['name'];
            $orderInfoUv = $orderModel->getOrderSourceUvBySource($v['id'], $begin, $end);
            $return[$k]['uv'] = $orderInfoUv['count'] ?? 0;
            //根据渠道统计订单
            $orderInfo = $orderModel->getHzsAnalysisBySource($v['id'], $begin, $end);
            $return[$k]['fadan'] = $orderInfo['fadan_count'] ?? 0;
            $faOrderInfo = $orderModel->getHzsAnalysisFaBySource($v['id'], $begin, $end);
            $return[$k]['fendan'] = $faOrderInfo['fendan_count'] ?? 0;
            $return[$k]['fendan_rate'] = setInfNanToN(round($return[$k]['fendan'] / $return[$k]['fadan'], 2) * 100);
            $realFenDanInfo = $orderModel->getHzsAnalysisBySourceRealFenDan($v['id'], $begin, $end);
            $return[$k]['real_fendan'] = $realFenDanInfo['count'] ?? 0;
            $return[$k]['real_fedan_rate'] = setInfNanToN(round($return[$k]['real_fendan'] / $return[$k]['fadan'], 2) * 100);
            $return[$k]['zhuche'] = $return[$k]['fendan'];
            $return[$k]['real_zhuche'] = $return[$k]['real_fendan'];
        }
        return $return;
    }

    public function getHzsAnalysisOrderInfoByHzsParentExport($id_arr, $account, $source, $begin, $end)
    {
        $return = [];
        $orderModel = new OrdersModel();
        $list = $orderModel->getHzsAnalysisOrderInfoByHzsParent($id_arr, $account, $source, $begin, $end, null, null);
        foreach (@$list as $k => $v) {
            $return[$k]['id'] = $v['id'];
            $return[$k]['time_real'] = date('Y-m-d',$v['time_real']);
            $return[$k]['name'] = $v['name'];
            $return[$k]['reamks'] = $v['remarks'];
            $return[$k]['cs'] = $v['cname'];
            $return[$k]['tel'] = $v['tel'];
            $return[$k]['on'] = $v['on'];
        }
        return $return;
    }

    // 获取合作商下拉数据
    public function getHzsCompanySelectList($classify = 1, $yy_id = 0){
        $hzsCompanyModel = new HzsCompanyModel();
        return $hzsCompanyModel->getHzsCompanyList($classify, $yy_id);
    }

    // 获取合作商渠道来源下拉数据
    public function getHzsSourceSelectList($yy_id = 0){
        $hzsCompanyModel = new HzsCompanyModel();
        return $hzsCompanyModel->getHzsSourceList($yy_id);
    }

    // 渠道数据统计-按合作商统计
    public function getHzsCompanyAnalysis($input, $limit = 20){
        $yy_id = $input["yy_id"];
        $begin = $input["begin"];
        $end = $input["end"];

        $hzsCompanyModel = new HzsCompanyModel();
        if ($input["export"] == false) {
            $count = $hzsCompanyModel->getHzsCompanyPageCount($input);

            import("Library.Org.Util.Page");
            $pageClass = new \Page($count, $limit);
            $pageshow = $pageClass->show();
        }

        if ($count > 0 || $input["export"] == true) {
            $list = $hzsCompanyModel->getHzsCompanyPageList($input, $pageClass->firstRow, $pageClass->listRows);
            if (!empty($list)) {
                $hzsCompanyIds = array_column($list, "id");

                // 查询uv
                $uvList = $hzsCompanyModel->getHzsCompanyUvStat($hzsCompanyIds, $begin, $end, $yy_id);
                $uvList = array_column($uvList, null, "company_id");

                // 按发单量查询
                $fadanList = $hzsCompanyModel->getHzsCompanyOrderFadanStat($hzsCompanyIds, $begin, $end, $yy_id);
                $fadanList = array_column($fadanList, null, "company_id");

                // 按实际分单量查询
                $csosList = $hzsCompanyModel->getHzsCompanyOrderCsosStat($hzsCompanyIds, $begin, $end, $yy_id);
                $csosList = array_column($csosList, null, "company_id");

                foreach ($list as $key => &$item) {
                    $id = $item["id"];
                    $item["uv"] = $uvList[$id]["uv"] ?? 0;

                    $item["fadan"] = $fadanList[$id]["fadan"] ?? 0;
                    $item["fendan"] = $fadanList[$id]["fendan"] ?? 0;
                    $item["valid"] = $fadanList[$id]["valid"] ?? 0;

                    $item["fendan_rate"] = sys_devision($item["fendan"], $item["fadan"], 4) * 100;
                    $item["fendan_rate"] = $item["fendan_rate"] . "%";

                    $item["csos_fendan"] = $csosList[$id]["csos_fendan"] ?? 0;
                    $item["csos_valid"] = $csosList[$id]["csos_valid"] ?? 0;

                    $item["csos_fendan_rate"] = sys_devision($item["csos_fendan"], $item["fadan"], 4) * 100;
                    $item["csos_fendan_rate"] = $item["csos_fendan_rate"] . "%";
                }
            }
        }

        return [
            "list" => $list,
            "count" => $count,
            "pageshow" => $pageshow,
        ];
    }

    // 渠道数据统计-按合作商账户统计
    public function getHzsAccountAnalysis($input, $limit = 20){
        $yy_id = $input["yy_id"];
        $begin = $input["begin"];
        $end = $input["end"];

        $hzsCompanyModel = new HzsCompanyModel();
        if ($input["export"] == false) {
            $count = $hzsCompanyModel->getHzsAccountPageCount($input);

            import("Library.Org.Util.Page");
            $pageClass = new \Page($count, $limit);
            $pageshow = $pageClass->show();
        }

        if ($count > 0 || $input["export"] == true) {
            $list = $hzsCompanyModel->getHzsAccountPageList($input, $pageClass->firstRow, $pageClass->listRows);
            if (!empty($list)) {
                $hzsAccountIds = array_column($list, "id");

                // 查询uv
                $uvList = $hzsCompanyModel->getHzsAccountUvStat($hzsAccountIds, $begin, $end, $yy_id);
                $uvList = array_column($uvList, null, "account_id");

                // 按发单量查询
                $fadanList = $hzsCompanyModel->getHzsAccountOrderFadanStat($hzsAccountIds, $begin, $end, $yy_id);
                $fadanList = array_column($fadanList, null, "account_id");

                // 按实际分单量查询
                $csosList = $hzsCompanyModel->getHzsAccountOrderCsosStat($hzsAccountIds, $begin, $end, $yy_id);
                $csosList = array_column($csosList, null, "account_id");

                foreach ($list as $key => &$item) {
                    $id = $item["id"];
                    $item["uv"] = $uvList[$id]["uv"] ?? 0;

                    $item["fadan"] = $fadanList[$id]["fadan"] ?? 0;
                    $item["fendan"] = $fadanList[$id]["fendan"] ?? 0;
                    $item["valid"] = $fadanList[$id]["valid"] ?? 0;

                    $item["fendan_rate"] = sys_devision($item["fendan"], $item["fadan"], 4) * 100;
                    $item["fendan_rate"] = $item["fendan_rate"] . "%";

                    $item["csos_fendan"] = $csosList[$id]["csos_fendan"] ?? 0;
                    $item["csos_valid"] = $csosList[$id]["csos_valid"] ?? 0;

                    $item["csos_fendan_rate"] = sys_devision($item["csos_fendan"], $item["fadan"], 4) * 100;
                    $item["csos_fendan_rate"] = $item["csos_fendan_rate"] . "%";
                }
            }
        }

        return [
            "list" => $list,
            "count" => $count,
            "pageshow" => $pageshow,
        ];
    }

    // 渠道数据统计-按渠道来源统计
    public function getHzsSourceAnalysis($input, $limit = 20){
        $yy_id = $input["yy_id"];
        $begin = $input["begin"];
        $end = $input["end"];

        $hzsCompanyModel = new HzsCompanyModel();
        if ($input["export"] == false) {
            $count = $hzsCompanyModel->getHzsSourcePageCount($input);

            import("Library.Org.Util.Page");
            $pageClass = new \Page($count, $limit);
            $pageshow = $pageClass->show();
        }

        if ($count > 0 || $input["export"] == true) {
            $list = $hzsCompanyModel->getHzsSourcePageList($input, $pageClass->firstRow, $pageClass->listRows);
            if (!empty($list)) {
                $sourceIds = array_column($list, "id");

                // 查询uv
                $uvList = $hzsCompanyModel->getHzsSourceUvStat($sourceIds, $begin, $end);
                $uvList = array_column($uvList, null, "source_id");

                // 按发单量查询
                $fadanList = $hzsCompanyModel->getHzsSourceOrderFadanStat($sourceIds, $begin, $end);
                $fadanList = array_column($fadanList, null, "source_id");

                // 按实际分单量查询
                $csosList = $hzsCompanyModel->getHzsSourceOrderCsosStat($sourceIds, $begin, $end);
                $csosList = array_column($csosList, null, "source_id");

                foreach ($list as $key => &$item) {
                    $id = $item["id"];
                    $item["uv"] = $uvList[$id]["uv"] ?? 0;

                    $item["fadan"] = $fadanList[$id]["fadan"] ?? 0;
                    $item["fendan"] = $fadanList[$id]["fendan"] ?? 0;
                    $item["valid"] = $fadanList[$id]["valid"] ?? 0;

                    $item["fendan_rate"] = sys_devision($item["fendan"], $item["fadan"], 4) * 100;
                    $item["fendan_rate"] = $item["fendan_rate"] . "%";

                    $item["csos_fendan"] = $csosList[$id]["csos_fendan"] ?? 0;
                    $item["csos_valid"] = $csosList[$id]["csos_valid"] ?? 0;

                    $item["csos_fendan_rate"] = sys_devision($item["csos_fendan"], $item["fadan"], 4) * 100;
                    $item["csos_fendan_rate"] = $item["csos_fendan_rate"] . "%";
                }
            }
        }

        return [
            "list" => $list,
            "count" => $count,
            "pageshow" => $pageshow,
        ];
    }

    // 渠道数据统计-订单详情
    public function getHzsOrderAnalysis($input, $limit = 20){
        $yy_id = $input["yy_id"];
        $begin = $input["begin"];
        $end = $input["end"];

        $hzsCompanyModel = new HzsCompanyModel();
        if ($input["export"] == false) {
            $count = $hzsCompanyModel->getHzsOrderPageCount($input);

            import("Library.Org.Util.Page");
            $pageClass = new \Page($count, $limit);
            $pageshow = $pageClass->show();
        }

        if ($count > 0 || $input["export"] == true) {
            $list = $hzsCompanyModel->getHzsOrderPageList($input, $pageClass->firstRow, $pageClass->listRows);
            if (!empty($list)) {
                
                foreach ($list as $key => &$item) {
                    $item["date_real"] = date("Y-m-d H:i:s", $item["time_real"]);

                    $item["type_fw_name"] = "其他";
                    if ($item["type_fw"] == 1) {
                        $item["type_fw_name"] = "分单";
                    } else if ($item["type_fw"] == 2) {
                        $item["type_fw_name"] = "赠单";
                    }
                }
            }
        }

        return [
            "list" => $list,
            "count" => $count,
            "pageshow" => $pageshow,
        ];
    }
    
}
