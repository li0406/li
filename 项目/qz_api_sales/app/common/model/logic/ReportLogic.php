<?php
// +----------------------------------------------------------------------
// | ReportLogic  新销售报备
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use think\Db;
use think\Exception;

use app\common\model\db\Quyu;
use app\common\model\db\SaleReportCityQuote;
use app\common\model\db\SaleReportErpCompany;
use app\common\model\db\SaleReportImg;
use app\common\model\db\SaleReportLog;
use app\common\model\db\SaleReportPayment;
use app\common\model\db\SaleReportPaymentImg;
use app\common\model\db\SaleReportQuote;
use app\common\model\db\SaleReportRelated;
use app\common\model\db\SaleReportSwjCompany;
use app\common\model\db\SaleReportZxCompany;
use app\common\model\db\SaleReportDelayCompany;
use app\common\model\service\MsgService;

use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\SalesReportCodeEnum;
use app\index\enum\CompanyCodeEnum;
use app\index\enum\Report;

class ReportLogic
{

    private $cooperationType = [
        1 => '装修会员',
        2 => '独家会员',
        3 => '垄断会员',
        6 => '签单返点会员',
        7 => '全屋定制会员',
        4 => '三维家会员',
        5 => 'ERP会员',
        8 => '会员延期',
        14 => '常规标杆会员',
        15 => '新签返标杆会员',
    ];

    public function __construct()
    {
        $this->reportZxModel = new SaleReportZxCompany();
        $this->reportErpModel = new SaleReportErpCompany();
        $this->reportSwjModel = new SaleReportSwjCompany();
        $this->reportDelayModel = new SaleReportDelayCompany();
    }

    public function getSaleReportList($param, $page, $page_size)
    {
        // 排序规则
        $orderBy = 'create_time desc';

        // 城市查询
        if (!empty($param['cname'])) {
            $map['t.city_name'] = $param['cname'];
        } else if (empty($param["start"]) || empty($param["end"])) {
            // 如果城市查询为空则需要默认查询时间
            $param["start"] = date("Y-m-d", strtotime("-30 days"));
            $param["end"] = date("Y-m-d");
        }

        // 时间查询
        if (!empty($param['start']) && !empty($param['end'])) {
            $map['t.create_time'] = ['between', [strtotime($param['start']), strtotime($param['end'] . ' 23:59:59')]];
        }

        $authUserids = AdminuserLogic::getAuthUserids();
        // $authUserids = TeamLogic::getAccessAuthUsersIDs();
        if (!empty($authUserids)) {
            $map['t.saler_id'] = ['in', $authUserids];
        }
        if (!empty($param['saler'])) {
            if (ctype_digit((string)$param['saler'])) {
                if (!empty($authUserids) && !in_array($param['saler'], $authUserids)) {
                    return sys_response(0, '', ['list' => [], 'page' => sys_paginate(0, $page_size, $page)]);
                }
                $map['t.saler_id'] = ['=' ,$param['saler']];
            } else {
                $map['t.saler'] = ['like', '%' . $param['saler'] . '%'];
            }
        }

        if (!empty($param['company'])) {
            /*ctype_digit((string)$param['company']) ?
                ($map['t.company_id'] =  ['eq', $param['company']]):*/
            ($map['t.company_name'] = ['like', '%' . $param['company'] . '%']);
        }

        if (!empty($param['cooperation_type'])) {
            $map['t.cooperation_type'] = $param['cooperation_type'];
        }
        if (!empty($param['is_new'])) {
            $map['t.is_new'] = $param['is_new'];
        }
        if (!empty($param['status'])) {
            $map['t.status'] = $param['status'];
        }
        $condition = '';
        if (!empty($param['condition'])) {
            $condition = $param['condition'];
        }
        $count = $this->reportZxModel->getUnionAllCount($map, $condition);
        $list = [];
        if (!empty($count)) {
            $list = $this->reportZxModel->getUnionAllList($map, $page, $page_size, [], $orderBy, $condition);
            $list = $list->toArray();
            $city_name = [];
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = empty($v['create_time']) ? '' : date('Y/m/d H:i', $v['create_time']);
                $list[$k]['update_time'] = empty($v['update_time']) ? '' : date('Y/m/d H:i', $v['update_time']);
                $list[$k]['check_time'] = empty($v['check_time']) ? '' : date('Y/m/d H:i', $v['check_time']);
                $list[$k]['submit_time'] = empty($v['submit_time']) ? '' : date('Y/m/d H:i', $v['submit_time']);
                $list[$k]['kf_check_time'] = empty($v['kf_check_time']) ? '' : date('Y/m/d H:i', $v['kf_check_time']);
                $list[$k]['kf_finish_time'] = empty($v['kf_finish_time']) ? '' : date('Y/m/d H:i', $v['kf_finish_time']);
                $list[$k]['current_payment_time'] = empty($v['current_payment_time']) ? '' : date('Y/m/d', $v['current_payment_time']);
                $list[$k]['current_contract_start'] = empty($v['current_contract_start']) ? '' : date('Y/m/d', $v['current_contract_start']);
                $list[$k]['current_contract_end'] = empty($v['current_contract_end']) ? '' : date('Y/m/d', $v['current_contract_end']);
                $list[$k]['contract_start'] = empty($v['contract_start']) ? '' : date('Y/m/d', $v['contract_start']);
                $list[$k]['contract_end'] = empty($v['contract_end']) ? '' : date('Y/m/d', $v['contract_end']);
                $list[$k]['cooperation_type_name'] = SalesReportCodeEnum::getCooperationType($v['cooperation_type']);
                //只查询装修订单城市数据
                if (in_array($v['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
                    $city_name[] = $v['city_name'];
                }

                $list[$k]['status_name'] = SalesReportCodeEnum::getReportStatus($v['status']);

                // 显示会员倍数/返点比例
                $list[$k]["viptype_ratio_show"] = SalesReportCodeEnum::getViptypeBackRatioShow($v["cooperation_type"], $v["company_mode"]);
            }
        }

        $respData = [
            'list' => $list,
            'page' => sys_paginate($count, $page_size, $page),
            'options' => [
                'start' => $param["start"],
                'end' => $param["end"]
            ]
        ];

        return sys_response(0, '', $respData);
    }

    /**
     * 获取新后台审核列表
     * @param $param
     * @return array
     */
    public function getCheckReportList($param)
    {   
        $param['admin_user'] = $param['admin_user'] ?? 1;
        $page = !empty($param['p']) ? (int)$param['p'] : 1;
        $pageSize = !empty($param['size']) ? (int)$param['size'] : 20;
        $order = null;
        //查询条件
        $map = $map1 = [];
        $search_time = 1;//默认查询是否添加时间查询(除直接选择时间查询)1.添加默认 2.筛选了时间 3.不添加默认
        switch ($param['admin_user']) {
            case 1:
                $map['t.status'] = ['in', SalesReportCodeEnum::getCheckReportStatus()];
                $map['t.submit_time'] = ['between', [strtotime('-30 day'), time()]];
                if (!empty($param['submit_start_time']) && !empty($param['submit_end_time'])) {
                    $search_time = 2;
                    $map['t.submit_time'] = ['between', [strtotime($param['submit_start_time'] . ' 00:00:00'), strtotime($param['submit_end_time'] . ' 23:59:59')]];
                }

                // 如果是查询管理员审核列表，未通过的报备排在最前面,其他的排序按照正常的排序标准
                $order = 'case when status = 4 then 1 else 2 end , FIELD(t.status,' . SalesReportCodeEnum::REPORT_STATUS_TJ . ') desc,t.submit_time desc,t.update_time desc';
                break;
            case 2:
                //区分客服审核页面 审核页面/传凭页面
                if (isset($param['is_voucher']) && $param['is_voucher'] == 1) {
                    $map['t.status'] = ['eq', SalesReportCodeEnum::REPORT_STATUS_KFWAIT];
                } else {
                    $map['t.status'] = ['in', SalesReportCodeEnum::getKfReportStatus()];
                    $map['t.check_time'] = ['between', [strtotime('-30 day'), time()]];
                    if (!empty($param['check_start_time']) && !empty($param['check_end_time'])) {
                        $search_time = 2;
                        $map['t.check_time'] = ['between', [strtotime($param['check_start_time'] . ' 00:00:00'), strtotime($param['check_end_time'] . ' 23:59:59')]];
                    }
                }
                $order = 'FIELD(t.status,' . SalesReportCodeEnum::REPORT_STATUS_SHTG . ') desc,t.check_time desc,t.update_time desc';
                break;
        }
        if (!empty($param['company_name'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $map['t.company_name'] = ['like', '%' . $param['company_name'] . '%'];
        }
        if (!empty($param['op_id'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $map['t.saler_id'] = ['eq',  $param['op_id']];
        }
        if (!empty($param['cs'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $map['t.city_name'] = ['eq',  $param['cs']];
        }
        if (!empty($param['cooperation_type'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $map['t.cooperation_type'] = ['eq',  $param['cooperation_type']];
        }
        if (!empty($param['is_new'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $map['t.is_new'] = ['eq',  $param['is_new']];
        }
        if (!empty($param['status'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $map['t.status'] = ['eq',  $param['status']];
        }

        $condition = '';
        if (!empty($param['condition'])) {
            $search_time = ($search_time == 2) ? 2 : 3;
            $condition = $param['condition'];
        }

        if($search_time == 3){
            unset($map['t.submit_time']);
            unset($map['t.check_time']);
        }

        $count = $this->reportZxModel->getUnionAllCount($map,$condition);
        $list = [];
        if ($count > 0) {
            $list = $this->reportZxModel->getUnionAllList($map,$page, $pageSize,[],$order,$condition)->toArray();
            //获取最近三天报备公司重复
            $repetitionCompany = [];
            if ($param['admin_user'] == 1) {
                $company_id = array_column($list,'company_id');
                $repetitionCompany = $this->reportZxModel->getAllCount($company_id)->toArray();
                $repetitionCompany = array_column($repetitionCompany,'count_num','id');
            }

            $reportIds = array_column($list, "id");
            $cooperationTypes = array_column($list, "cooperation_type");
            $cooperationTypes = array_unique($cooperationTypes);
            
            // 查询关联小报备次数和总金额
            $reportRelatedModel = new SaleReportRelated();
            $relatedList = $reportRelatedModel->getPaymentNumberList($reportIds, $cooperationTypes);
            $relatedList = array_column($relatedList->toArray(), null, "group_key");

            foreach ($list as $k => $v) {
                $list[$k]['cooperation_type_name'] = SalesReportCodeEnum::getCooperationType($v['cooperation_type']);
                $list[$k]['is_new_name'] = SalesReportCodeEnum::getUserType($v['is_new']);
                $list[$k]['status_name'] = SalesReportCodeEnum::getReportStatus($v['status']);

                $list[$k]['current_contract_start_show'] = empty($v['current_contract_start']) ? '' : date('Y/m/d', $v['current_contract_start']);
                $list[$k]['current_contract_end_show'] = empty($v['current_contract_end']) ? '' : date('Y/m/d', $v['current_contract_end']);
                $list[$k]['contract_start_show'] = empty($v['contract_start']) ? '' : date('Y/m/d', $v['contract_start']);
                $list[$k]['contract_end_show'] = empty($v['contract_end']) ? '' : date('Y/m/d', $v['contract_end']);

                // 延期合同时间
                $list[$k]['delay_contract_start_show'] = empty($v['delay_contract_start']) ? '' : date('Y-m-d', $v['delay_contract_start']);
                $list[$k]['delay_contract_end_show'] = empty($v['delay_contract_end']) ? '' : date('Y-m-d', $v['delay_contract_end']);

                $list[$k]['check_btn'] = 2;//审核按钮
                $list[$k]['accomplish_btn'] = 2;//完成按钮
                //根据当前用户判断页面按钮显示
                if($param['admin_user'] == 1){
                    //待审核
                    if($v['status'] == SalesReportCodeEnum::REPORT_STATUS_TJ){
                        $list[$k]['check_btn'] = 1;
                    }
                }
                if($param['admin_user'] == 2){
                    //审核通过
                    if($v['status'] == SalesReportCodeEnum::REPORT_STATUS_SHTG){
                        $list[$k]['check_btn'] = 1;
                    }
                    //客服审核通过
                    if($v['status'] == SalesReportCodeEnum::REPORT_STATUS_KFSHTG) {
                        $list[$k]['accomplish_btn'] = 1;
                    }
                }
                //公司报备重复数(最近三天提交的审核)
                $list[$k]['repetition_num'] = isset($repetitionCompany[$v['id']]) ? $repetitionCompany[$v['id']] - 1 : 0;

                // 显示会员倍数/返点比例
                $company_mode = check_variate($v["company_mode"], 0);
                $list[$k]["viptype_ratio_show"] = SalesReportCodeEnum::getViptypeBackRatioShow($v["cooperation_type"], $company_mode);
                $list[$k]["show_recall"] = in_array($v["status"], SalesReportCodeEnum::getRecallStatus()) ? 1 : 0;

                // 关联小报备次数
                $gkey = sprintf("%s_%s", $v["id"], $v["cooperation_type"]);
                $list[$k]["related_payment_number"] = $relatedList[$gkey]["report_payment_number"] ?? 0;
                $list[$k]["related_payment_status"] = $list[$k]["related_payment_number"] ? 1 : 2;
            }
        }
        return sys_response(0, '', [
            'list' => $list,
            'page' => sys_paginate($count, $pageSize, $page),
        ]);
    }

    /**
     * 保存报备记录
     *
     * @param $post
     * @return bool
     */
    public function saveSaleReport($post, $user)
    {
        try {
            Db::startTrans();

            $map = [];
            $now = time();
            if (!empty($post['id'])) {
                $map['id'] = $post['id'];
                if (in_array($post['cooperation_type'], SalesReportCodeEnum::getCooperationZx() )) {
                    $info = $this->reportZxModel->getZxReportDetail($map);
                    $this->reportZxModel->id  = $post['id'];
                } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                    $info = $this->reportSwjModel->getSwjReportDetail($map);
                    $this->reportSwjModel->id = $post['id'];
                } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
                    $info = $this->reportErpModel->getErpReportDetail($map);
                    $this->reportErpModel->id = $post['id'];
                } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
                    $info = $this->reportDelayModel->getDelayReportDetail($map);
                    $this->reportDelayModel->id = $post['id'];
                }
            }

            unset($post['id']);
            if (empty($info) || empty($info['saler_id'])) {
                $post['saler_id'] = $user['user_id'];
                $post['saler'] = $user['user_name'];
            }

            if (!empty($post['cs'])) {
                $post['city_name'] = Quyu::where('cid', '=', $post['cs'])->value('cname') ?: $post["city_name"];
            }

            if (empty($info)) {
                $post['create_time'] = $now;
            }

            if(!empty($post['cashdeposit_handler'])){
                $post['cashdeposit_handler'] = strtotime($post['cashdeposit_handler']);
            }

            if(!empty($post['total_platform_money'])){
                $post['total_platform_money_start_time'] = strtotime($post['total_platform_money_start_date']);
                $post['total_platform_money_end_time'] = strtotime($post['total_platform_money_end_date']);
            }

            if(!empty($post['current_platform_money'])){
                $post['current_platform_money_start_time'] = strtotime($post['current_platform_money_start_date']);
                $post['current_platform_money_end_time'] = strtotime($post['current_platform_money_end_date']);
            }

            switch ($post['status']) {
                case 1:
                    //如果是普通保存的时候,验证是否需要客服传凭,需要就改变报备状态
                    if(!empty($post['is_kf_voucher']) && $post['is_kf_voucher'] == 1){
                        $post['status'] = SalesReportCodeEnum::REPORT_STATUS_KFWAIT;
                    }
                    break;
                case 2:
                    $post['submit_time'] = $now; break;
                case 3:case 4:
                    $post['check_time'] = $now; break;
                case 5:case 6:case 7:
                    $post['kf_check_time'] = $now; break;
                case 8:
                    $post['kf_finish_time'] = $now; break;
                default: break;
            }

            // 增加校验当状态值为审核通过时验证来源
            if ($post["status"] == SalesReportCodeEnum::REPORT_STATUS_SHTG) {
                if (empty($info) || $info["status"] != SalesReportCodeEnum::REPORT_STATUS_KFWTG) {
                    throw new Exception("状态值有误", 5000002);
                }
            }

            $flag = true;
            if (in_array($post['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
                //2.0和标杆会员去掉承诺订单量
                if (in_array($post['cooperation_type'], [SalesReportCodeEnum::REPORT_COOPERATION_SBACK, SalesReportCodeEnum::REPORT_COOPERATION_NEW_STANDARD])) {
                    $post["current_promised_orders_fen"] = "";
                    $post["current_promised_orders_zeng"] = "";
                    $post["promised_orders_fen"] = "";
                    $post["promised_orders_zeng"] = "";
                }

                $flag = $this->reportZxModel->saveOne($post, $map);
                request()->report_id = $this->reportZxModel->id;
            } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                $flag = $this->reportSwjModel->saveOne($post, $map);
                request()->report_id = $this->reportSwjModel->id;
            } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
                $flag = $this->reportErpModel->saveOne($post, $map);
                request()->report_id = $this->reportErpModel->id;
            } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
                $flag = $this->reportDelayModel->saveOne($post, $map);
                request()->report_id = $this->reportDelayModel->id;
            }

            if ($flag === false) {
                throw new Exception("保存失败", 5000002);
            }

            // 如果小报备ID不为空则关联小报备
            if (empty($info) && !empty($post["report_payment_id"])) {
                $reportRelatedModel = new SaleReportRelated();
                $ret = $reportRelatedModel->addRelated($post["report_payment_id"], request()->report_id, $post["cooperation_type"]);
                if ($ret == false) {
                    throw new Exception("保存失败", 5000002);
                }

                $data = [
                    "is_related" => 2,
                    "updated_at" => time()
                ];
                $reportPaymentModel = new SaleReportPayment;
                $ret = $reportPaymentModel->updateInfo($post["report_payment_id"], $data);
                if ($ret == false) {
                    throw new Exception("保存失败", 5000002);
                }
            }

            // 保存图片
            $saleReportImg = new SaleReportImg();
            $saleReportImg->setSaleReportImg(request()->report_id, $post['cooperation_type'], request()->param("imgs"));

            if ($post['status'] == 2) {
                // 报备提交审核通知蒋总
                $reportMsgSendLogic = new ReportMsgSendLogic();
                $sendusers = $reportMsgSendLogic->getExamFirstUsers();
                
                $msgService = new MsgService();
                $msgService->sendSystemMsg("201909191001", $sendusers, "?", request()->report_id, "会员报备");
            } else if ($post['status'] == 3) {
                // 重新编辑后通知客服
                $reportMsgSendLogic = new ReportMsgSendLogic();
                $sendusers = $reportMsgSendLogic->getExamFinalUsers();

                $msgService = new MsgService();
                $msgService->sendSystemMsg("201909211009", $sendusers, "?", request()->report_id, "会员报备");
            }

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : 1000001;
            Db::rollback();
        }

        return sys_response($errcode, $errmsg);
    }

    /**
     * 删除报备记录
     *
     * @param $post
     * @return array
     */
    public function delSaleReport($post)
    {
        try {
            Db::startTrans();

            $flag = false;
            if (in_array($post['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
                $flag = $this->reportZxModel->delOne($post);
            } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                $flag = $this->reportSwjModel->delOne($post);
            } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
                $flag = $this->reportErpModel->delOne($post);
            } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
                $flag = $this->reportDelayModel->delOne($post);
            }

            if ($flag === false) {
                throw new Exception("操作失败", 5000002);
            }

            // 不是erp删除和小报备的关联关系
            if ($post['cooperation_type'] != SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
                // 同步删除小报备关联和释放小报备关联状态
                $reportPaymentLogic = new ReportPaymentLogic();
                $reportPaymentLogic->setRelatedRemove($post["id"], $post["cooperation_type"]);
            }

            // 删除图片
            $saleReportImg = new SaleReportImg();
            $saleReportImg->deleteByUnique($post['id'], $post['cooperation_type']);

            // 删除记录
            $logModel = new SaleReportLog();
            $logModel->delList(['report_id' => $post['id'], 'cooperation_type' => $post['cooperation_type']]);

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return sys_response($errcode, $errmsg);
    }

    // 大报备信息驳回
    public function setReportRecall($report_id, $cooperation_type, $remark = ""){
        try {
            $map = [];
            $map["id"] = $report_id;
            $map["cooperation_type"] = $cooperation_type;

            Db::startTrans();
            if (in_array($cooperation_type, SalesReportCodeEnum::getCooperationZx() )) {
                $info = $this->reportZxModel->getZxReportDetail($map);
            } else if ($cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                $info = $this->reportSwjModel->getSwjReportDetail($map);
            } else if ($cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
                $info = $this->reportErpModel->getErpReportDetail($map);
            } else if ($cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
                $info = $this->reportDelayModel->getDelayReportDetail($map);
            }

            if (empty($info)) {
                throw new Exception(BaseStatusCodeEnum::CODE_4000001, 4000001);
            } else if (!in_array($info["status"], SalesReportCodeEnum::getRecallStatus())) {
                throw new Exception("该报备信息不能被驳回", 1000001);
            }

            // 状态改为审核未通过
            $info->status = SalesReportCodeEnum::REPORT_STATUS_KFWTG_CHECK;
            $info->admin_remarke = check_variate($remark);
            $info->check_time = time();
            $info->kf_finish_time = 0;
            $info->kf_check_time = 0;

            // 保存并验证
            if ($info->save() === false) {
                throw new Exception(BaseStatusCodeEnum::CODE_5000002, 5000002);
            }

            // 报备不通过记录
            $logData = [];
            $logData["status"] = $info->status;
            $logData["exam_remark"] = $info->admin_remarke;
            $reportUnpassLogLogic = new ReportUnpassLogLogic();
            $ret = $reportUnpassLogLogic->addDefaultLog($info, $logData);
            if ($ret == false) {
                throw new Exception(BaseStatusCodeEnum::CODE_5000002, 5000002);
            }

            // 驳回后添加会员日消耗更新队列
            if (in_array($cooperation_type, SalesReportCodeEnum::getCompanyExpendType())) {
                CompanyExpendLogic::addExpendQueue($info["company_id"], $report_id, $cooperation_type);
            }

            // 给销售发送通知
            $msgService = new MsgService();
            $msgService->sendSystemMsg("201909191002", $info->saler_id, "?", $report_id, "报备驳回");

            $errcode = 0;
            $errmsg = BaseStatusCodeEnum::CODE_0;
            Db::commit();
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : 1000001;
            Db::rollback();
        }

        return sys_response($errcode, $errmsg);
    }

    /**
     * 修改报备记录状态
     *
     * @param $post
     * @return array
     */
    public function changeSaleReportStatus($post)
    {
        $now = time();
        $map['id'] = $post['id'];
        //根据不同类型获取不同数据
        if (in_array($post['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
            $info = $this->reportZxModel->getZxReportDetail($map);
            $this->reportZxModel->id = $post['id'];
        } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            $info = $this->reportSwjModel->getSwjReportDetail($map);
            $this->reportSwjModel->id = $post['id'];
        } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            $info = $this->reportErpModel->getErpReportDetail($map);
            $this->reportErpModel->id = $post['id'];
        } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            $info = $this->reportDelayModel->getDelayReportDetail($map);
            $this->reportDelayModel->id = $post['id'];
        }

        if (empty($info)) {
            return sys_response(4000800, '报备记录已删除', []);
        }
        if ($info['status'] == 8) {
            return sys_response(4000800, '报备记录已完成', []);
        }
        if ($info['status'] == 1 && !in_array($post['status'], [1, 2])) {
            return sys_response(4000800, '报备记录已撤回', []);
        }
        if ($info['status'] > 2 && $post['status'] == 1) {
            return sys_response(4000800, '报备记录已被审核', []);
        }

        unset($post['id']);
        switch ($post['status']) {
            case 1:
                $post['create_time'] = $now; break;
            case 2:
                $post['submit_time'] = $now; break;
            case 3:case 4:
                $post['check_time'] = $now; break;
            case 5:case 6:case 7:
                $post['kf_check_time'] = $now; break;
            case 8:
                $post['kf_finish_time'] = $now; break;
            default: break;
        }

        if (in_array($post['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
            $flag = $this->reportZxModel->saveOne($post, $map);
            request()->report_id = $this->reportZxModel->id;
            $saler_id = $this->reportZxModel->getSalerId(request()->report_id);
        } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            $flag = $this->reportSwjModel->saveOne($post, $map);
            request()->report_id = $this->reportSwjModel->id;
            $saler_id = $this->reportSwjModel->getSalerId(request()->report_id);
        } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            $flag = $this->reportErpModel->saveOne($post, $map);
            request()->report_id = $this->reportErpModel->id;
            $saler_id = $this->reportErpModel->getSalerId(request()->report_id);
        } else if ($post['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            $flag = $this->reportDelayModel->saveOne($post, $map);
            request()->report_id = $this->reportDelayModel->id;
            $saler_id = $this->reportDelayModel->getSalerId(request()->report_id);
        }

        if (isset($flag) && $flag !== false) {
            $msgService = new MsgService();
            $report_id = request()->report_id;
            $send_position = "报备审核";
            if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_SHTG) {
                // 蒋总审核通过通知销售
                $msgService->sendSystemMsg("201909191003", $saler_id, "?", $report_id, $send_position);

                // 蒋总审核通过通知客服
                $reportMsgSendLogic = new ReportMsgSendLogic();
                $sendusers = $reportMsgSendLogic->getExamFinalUsers();
                $msgService->sendSystemMsg("201909191004", $sendusers, "?", $report_id, $send_position);
            } else if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_WTG) {
                // 蒋总审核不通过通知销售
                $msgService->sendSystemMsg("201909191002", $saler_id, "?", $report_id, $send_position);
            } else if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_KFSHTG) {
                // 客服审核通过通知销售
                $msgService->sendSystemMsg("201909201005", $saler_id, "?", $report_id, $send_position);
            } else if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_KFWTG) {
                // 客服审核不通过不需要重新审核通知销售
                $msgService->sendSystemMsg("201909211007", $saler_id, "?", $report_id, $send_position);
            } else if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_KFWTG_CHECK) {
                // 客服审核不通过需要重新审核通知销售
                $msgService->sendSystemMsg("201909201006", $saler_id, "?", $report_id, $send_position);
            } else if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_KFWC) {
                // 客服完成上传通知销售
                $msgService->sendSystemMsg("201909211008", $saler_id, "?", $report_id, $send_position);

                // 如果完成上传添加会员公司日消耗更新队列
                if (in_array($info["cooperation_type"], SalesReportCodeEnum::getCompanyExpendType())) {
                    CompanyExpendLogic::addExpendQueue($info["company_id"], $report_id, $info["cooperation_type"]);
                }
            }

            // 如果审核不通过添加不通过日志
            $failstatus = [
                SalesReportCodeEnum::REPORT_STATUS_WTG,
                SalesReportCodeEnum::REPORT_STATUS_KFWTG,
                SalesReportCodeEnum::REPORT_STATUS_KFWTG_CHECK,
            ];
            if (in_array($post['status'], $failstatus)) {
                $post["exam_remark"] = $post["service_remarke"] ?? "";
                if ($post['status'] == SalesReportCodeEnum::REPORT_STATUS_WTG) {
                    $post["exam_remark"] = $post["admin_remarke"] ?? "";
                }

                $reportUnpassLogLogic = new ReportUnpassLogLogic();
                $reportUnpassLogLogic->addDefaultLog($info, $post);
            }

            //审核通过并且是标杆会员报备
            //重置当前保产值金额和已签约金额
            if ($post['status'] == 3 && in_array($post['cooperation_type'],[14,15])) {
                if (!empty($info["company_id"])) {
                    try {
                        //查询是否有账户数据
                        $accountlogic = new CompanyAccountLogic();
                        $account = $accountlogic->getAccountInfo($info["company_id"]);
                        $company_id = $info["company_id"];

                        if (count($account) == 0) {
                            //未查询到数据的补充添加账户数据
                            $data = [
                                "user_id" => $info["company_id"],
                                "updated_at" => time(),
                            ];
                            $accountlogic->addInfo($data);
                        }

                        if ($company_id > 0) {
                            //查询用户的会员状态
                            $logic = new UserLogic();
                            $userInfo = $logic->getUserInfo($info["company_id"]);

                            //修改账户的信息
                            if ($userInfo["on"] == 2) {
                                //如果是会员状态，累加当前保产值金额
                                $data = [
                                    "current_guaranteed_amount" => $account["current_guaranteed_amount"] + $info["current_contract_amount_guaranteed"],
                                    "total_guaranteed_amount" => $account["total_guaranteed_amount"] + $info["current_contract_amount_guaranteed"],
                                ];
                            } else {
                                //重置保产值数据
                                $data = [
                                    "contracted_amount" => 0,
                                    "current_guaranteed_amount" => $info["current_contract_amount_guaranteed"],
                                    "total_guaranteed_amount" => $account["total_guaranteed_amount"] + $info["current_contract_amount_guaranteed"],
                                ];
                            }
                            $accountlogic->editInfo($company_id,$data);
                        }
                    } catch (\Exception $e) {
                    }
                }
            }

            return sys_response(0, '', []);
        } else {
            return sys_response(5000002, '', []);
        }
    }

    public function getSalesReportDetail($param)
    {
        if (empty($param['id']) || empty($param['cooperation_type'])) {
            return sys_response(4000002);
        }
        $where = [
            'id' => $param['id'],
            'cooperation_type' => $param['cooperation_type'],
        ];

        $info = [];
        $city_quote = false;//能否查询城市报价
        $member_quote = false;//能否查询城市报价
        //根据不同类型获取不同数据
        if (in_array($param['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
            //装修会员
            $info = $this->reportZxModel->getZxReportDetail($where);
            $city_quote = true;

            if (!empty($info)) {
				$info["contract_start_show"] = !empty($info["contract_start"]) ? date("Y/m/d", $info["contract_start"]) : "";
                $info["contract_end_show"] = !empty($info["contract_end"]) ? date("Y/m/d", $info["contract_end"]) : "";
                $info["current_contract_start_show"] = !empty($info["current_contract_start"]) ? date("Y/m/d", $info["current_contract_start"]) : "";
                $info["current_contract_end_show"] = !empty($info["current_contract_end"]) ? date("Y/m/d", $info["current_contract_end"]) : "";

                $info['total_platform_money_start_date'] = $info['total_platform_money_start_time'] ? date('Y-m-d', $info['total_platform_money_start_time']) : '';
                $info['total_platform_money_end_date'] = $info['total_platform_money_end_time'] ? date('Y-m-d', $info['total_platform_money_end_time']) : '';
                $info['current_platform_money_start_date'] = $info['current_platform_money_start_time'] ? date('Y-m-d', $info['current_platform_money_start_time']) : '';
                $info['current_platform_money_end_date'] = $info['current_platform_money_end_time'] ? date('Y-m-d', $info['current_platform_money_end_time']) : '';
                $info['total_contract_amount_guaranteed'] = intval($info['total_contract_amount_guaranteed']);
                $info['current_contract_amount_guaranteed'] = intval($info['current_contract_amount_guaranteed']);

            }
        } else if ($param['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            //三维家会员
            $info = $this->reportSwjModel->getSwjReportDetail($where);
            $member_quote = true;
        } else if ($param['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            //erp会员
            $info = $this->reportErpModel->getErpReportDetail($where);
            $member_quote = true;
        } else if ($param['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            //会员延期
            $info = $this->reportDelayModel->getDelayReportDetail($where);
            $city_quote = true;

            if (!empty($info)) {
                $info["current_contract_start_show"] = date("Y-m-d", $info["current_contract_start"]);
                $info["current_contract_end_show"] = date("Y-m-d", $info["current_contract_end"]);
                $info["current_vip_start_show"] = date("Y-m-d", $info["current_vip_start"]);
                $info["current_vip_end_show"] = date("Y-m-d", $info["current_vip_end"]);
                $info["delay_contract_start_show"] = date("Y-m-d", $info["delay_contract_start"]);
                $info["delay_contract_end_show"] = date("Y-m-d", $info["delay_contract_end"]);
                $info["company_mode_name"] = CompanyCodeEnum::getItem("company_mode", $info["company_mode"]);

                // 合同时间是否一致
                $info["contract_date_compare"] = 1;
                if ($info["current_contract_start"] != $info["current_vip_start"] || 
                    $info["current_contract_end"] != $info["current_vip_end"]) {
                    $info["contract_date_compare"] = 2;
                }
            }
        }

        if (count($info) > 0) {
            $info = is_object($info) ? $info->toArray() : $info;
            $info["cooperation_type_name"] = SalesReportCodeEnum::getCooperationType($info['cooperation_type']);
            if($param['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP){
                unset($info['viptype']);
            }

            if (in_array($info['cooperation_type'], SalesReportCodeEnum::getCooperationZx()) || 
               $info['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY ) {

                // 查询关联的小报备总数和总金额
                $reportRelatedModel = new SaleReportRelated();
                $ret = $reportRelatedModel->getPaymentStatistic($info["id"], $info["cooperation_type"]);
                $info["report_payment_number"] = $ret["report_payment_number"];
                $info["report_payment_money"] = floatval($ret["report_payment_money"]);
                $info["deposit_to_round_money"] = floatval($ret["deposit_to_round_money"]);
                $info["platform_money"] = floatval($ret["platform_money"]);

                $info["report_money_compart"] = intval($info["report_payment_money"] == check_variate($info["current_contract_amount"]));
                $info["company_report_payment_number"] = $ret["report_payment_number"];

                //查询小报备图片
                if(!empty($ret['report_payment_id'])){
                    $ids = explode(',',$ret['report_payment_id']);
                    if(count($ids) > 0){
                        $imgDb = new SaleReportPaymentImg();
                        $img = $imgDb->getPaymentAuthImgs($ids)->toArray();
                        $info['payment_img'] = $img;
                        unset($img);
                    }
                }

                // 查询会员公司的历史大报备次数
                if (!empty($info["company_id"])) {
                    $report_zx_number = $this->reportZxModel->getCompanyReportNumber($info["company_id"]);
                    $report_delay_number = $this->reportDelayModel->getCompanyReportDelayNumber($info["company_id"]);
                    $info["company_report_number"] = $report_zx_number + $report_delay_number;
                }
            }

            if($param['cooperation_type'] != SalesReportCodeEnum::REPORT_COOPERATION_SWJ
                && $param['cooperation_type'] != SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
                //计算总合同与当前合同的天数 
                $info['contract_days'] = ($info['contract_end'] - $info['contract_start']) / 86400 + 1;
                $info['current_contract_days'] = ($info['current_contract_end'] - $info['current_contract_start']) / 86400 + 1;

                $info["contract_days_desc"] = "";
                if ($info["contract_start"] && $info["contract_end"]) {
                    $info["contract_days_desc"] = $this->diffMonthDate($info["contract_start"], $info["contract_end"]);
                }

                $info["current_contract_days_desc"] = "";
                if ($info["current_contract_start"] && $info["current_contract_end"]) {
                    $info["current_contract_days_desc"] = $this->diffMonthDate($info["current_contract_start"], $info["current_contract_end"]);
                }
            }

            $info['total_platform_money_use_days'] = '';
            if(!empty($info['total_platform_money_start_time']) && !empty($info['total_platform_money_end_time'])){
                $info['total_platform_money_use_days'] = $this->diffMonthDate($info['total_platform_money_start_time'], $info['total_platform_money_end_time']);
            }

            $info['current_platform_money_use_days'] = '';
            if(!empty($info['current_platform_money_start_time']) && !empty($info['current_platform_money_end_time'])){
                $info['current_platform_money_use_days'] = $this->diffMonthDate($info['current_platform_money_start_time'], $info['current_platform_money_end_time']);
            }

            
            $info['cooperation_type_name'] = SalesReportCodeEnum::getCooperationType(check_variate($info['cooperation_type']));
            $info['is_new_name'] = SalesReportCodeEnum::getUserType(check_variate($info['is_new']));
            $info['status_name'] = SalesReportCodeEnum::getReportStatus(check_variate($info['status']));
            $info['passage_position_name'] = SalesReportCodeEnum::getPassagePosition(check_variate($info['passage_position']));
            $info["show_recall"] = in_array($info["status"], SalesReportCodeEnum::getRecallStatus()) ? 1 : 0;

            // 显示会员倍数/返点比例
            $company_mode = check_variate($info["company_mode"], 0);
            $info["viptype_ratio_show"] = SalesReportCodeEnum::getViptypeBackRatioShow($info["cooperation_type"], $company_mode);

            $saleReportImg = new SaleReportImg();
            $imgList = $saleReportImg->getByUnique($param['id'], $param['cooperation_type']);
            $info['img_list'] = [];
            $info['kf_voucher_img'] = [];
            if (count($imgList) > 0) {
                foreach ($imgList as $k=>$v){
                    if($v['img_type'] == 1){
                        //销售报备图片
                        $info['img_list'][] = $v['img_path'];
                    }elseif ($v['img_type'] == 2){
                        //客服上传凭证图片
                        $info['kf_voucher_img'][] = $v['img_path'];
                    }
                }
            }

            // 报备人所属顶级团队
            $saler_id = $info["saler_id"];
            $teamLogic = new TeamLogic();
            $topTeamList = $teamLogic->getUserTopTeamId([$saler_id]);
            if (array_key_exists($saler_id, $topTeamList)) {
                $topTeam = $topTeamList[$saler_id];
                $info["top_team_id"] = $topTeam["team_id"];
                $info["top_team_name"] = $topTeam["team_name"];
            } else {
                $info["top_team_id"] = "";
                $info["top_team_name"] = "";
            }

        }else{
            return sys_response(4000001);
        }

        //验证当前合同是否异常
        $info['is_abnormal'] = self::checkContract($info);

        //附属信息(不需要则不查询)
        //1.城市报价
        if ($city_quote && !empty($param['city_quote'])) {
            $quoteLogic = new QuoteLogic();
            $quote = $quoteLogic->get_city_quote($info['city_name'], true);
            $default_quote = [
                'create_time' => 0
            ];
            $info['city_quote'] = !empty($quote) ? $quote : $default_quote;
        }

        //2.会员合同报价
        if ($member_quote && !empty($param['member_quote'])) {
            $quoteDb = new SaleReportQuote();
            $quote = $quoteDb->getList()->toArray();
            if (count($quote) > 0) {
                foreach ($quote as $k => $v) {
                    $quote[$v['cooperation_name']][] = $v;
                    unset($quote[$k]);
                }
            }
            $info['member_quote'] = !empty($quote) ? $quote : [];
        }
        //3.验证审核操作按钮和 审核操作的参数
        if(!empty($param['admin_user']) && $param['admin_user'] == 1){
            $info['check_btn'] = 0;
            if($info['status'] == SalesReportCodeEnum::REPORT_STATUS_TJ){
                $info['check_btn'] = 1;
            }
        }

        //常规、独家、垄断 查询系统折算订单量
        if (in_array($info['cooperation_type'], [SalesReportCodeEnum::REPORT_COOPERATION_ZX, SalesReportCodeEnum::REPORT_COOPERATION_DJ, SalesReportCodeEnum::REPORT_COOPERATION_LD]) && !empty($info['city_quote'])) {
            //本次合同折算
            $info["convert_order_count"] = $this->correctedOrderCount($info["current_contract_start"], $info["current_contract_end"], $info['viptype'], $info['city_quote'],$info["current_year_month_one"],$info["current_year_month_two"]);
            //总合同折算
            $info["total_convert_order_count"] = $this->correctedOrderCount($info["contract_start"], $info["contract_end"], $info['viptype'], $info['city_quote'],$info["year_month_one"],$info["year_month_two"]);
        }

        //获取审核记录
        $SaleReportLogModel = new SaleReportLog();
        $map = [
            'report_id'=>$info['id'],
            'cooperation_type'=>$info['cooperation_type'],
            'status'=>['in',[3,4,5,6,7,8,9,10,11]],
        ];
        $info['check_log'] = $SaleReportLogModel->getList($map, 0, 0)->toArray();

        // 获取城市有效真实会员数
        if(($info['cs'] ?? '') && ($info['city_quote'] ?? '')){
            $mem = $this->getMemberCount($info['cs']);
            $info['city_quote'] = array_merge($info['city_quote'], $mem);
        }

        $info['history_modify'] = $this->getHistroyModifyList($param['id']);

        return sys_response(0, '', ['info' => $info]);
    }

    public function getMemberCount($cs)
    {
        $zxDb = new SaleReportZxCompany();

        // 1.0会员数
        $map1 = [
            'u.cs' => $cs,
            'u.classid' => 3,
            'c.cooperate_mode' => 1
        ];
        $result['memberOneCount'] = $zxDb->getCityMembersCount($map1);

        // 2.0会员数
        $map2 = [
            'u.cs' => $cs,
            'u.classid' => 3,
            'c.cooperate_mode' => 2
        ];
        $result['memberTwoCount'] = $zxDb->getCityMembersCount($map2);

        // 老签返会员数
        $mapOld = [
            'u.cs' => $cs,
            'u.classid' => 6,
        ];
        $result['memberOldCount'] = $zxDb->getCityMembersCount($mapOld);

        $result['memberSumCount'] = $result['memberOneCount'] + $result['memberTwoCount'] + $result['memberOldCount'];

        return $result;
    }

    /**
     * 获取审核记录
     *
     * @param $param
     * @return array
     */
    public function checkLog($param, $page, $page_size)
    {
        $SaleReportLogModel = new SaleReportLog();
        $map['report_id'] = $param['report_id'];
        $map['cooperation_type'] = $param['cooperation_type'];
        if ($page != null) {
            $count = $SaleReportLogModel->getCount($map);
            $response['page'] = sys_paginate($count, $page_size, $page);
        }
        $response['list'] = $SaleReportLogModel->getList($map, $page, $page_size)->toArray();
        foreach ($response['list'] as $k => $v) {
            $response['list'][$k]['created_at'] = empty($v['created_at']) ? '' : date('Y/m/d H:i:s', $v['created_at']);
        }
        return sys_response(0, '', $response);
    }

    public function setKfVoucher($input)
    {
        $info = $this->getReportInfo($input);
        if(count($info) == 0){
            return sys_response(4000001);
        }
        //设置报备 客服凭证
        $status = false;
        $save = [
            'status' => SalesReportCodeEnum::REPORT_STATUS_KFWAIT,
            'update_time' => time()
        ];
        $map[] = ['id', '=', $info['id']];

        if (in_array($input['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
            //装修会员
            $status = $this->reportZxModel->saveOne($save,$map);
        } else if ($input['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            //三维家会员
            $status = $this->reportSwjModel->saveOne($save,$map);
        } else if ($input['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            //erp会员
            $status = $this->reportErpModel->saveOne($save,$map);
        } else if ($input['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            //会员延期
            $status = $this->reportDelayModel->saveOne($save,$map);
        }

        if($status){
            return sys_response(0);
        }else{
            return sys_response(5000002);
        }
    }

    public function uploadKfVoucher($input)
    {
        $info = $this->getReportInfo($input);
        if(count($info) == 0){
            return sys_response(4000001);
        }
        //添加客服凭证
        $saleReportImg = new SaleReportImg();
        $status = $saleReportImg->setSaleReportImg($input['id'], $input['cooperation_type'], $input['img'], SalesReportCodeEnum::REPORT_IMG_PZ);
        if ($status) {
            //修改报备状态
            $this->editSaleReportStatus($input['id'], $input['cooperation_type'], SalesReportCodeEnum::REPORT_STATUS_KFWCUPLOAD);
            // 客服完成通知销售
            $msgService = new MsgService();
            $msgService->sendSystemMsg("201909211009", $info['saler_id'], "?", $input['id'], '会员报备');
            return sys_response(0);
        } else {
            return sys_response(5000002);
        }
    }

    public function getReportInfo($input = []){
        if (empty($input['id']) || empty($input['cooperation_type'])) {
            return [];
        }
        $where = [
            'id' => $input['id'],
            'cooperation_type' => $input['cooperation_type'],
        ];

        $info = [];
        //根据不同类型获取不同数据
        if (in_array($input['cooperation_type'], SalesReportCodeEnum::getCooperationZx())) {
            //装修会员
            $info = $this->reportZxModel->getZxReportDetail($where, [], 'id,saler_id');
        } else if ($input['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            //三维家会员
            $info = $this->reportSwjModel->getSwjReportDetail($where, [], 'id,saler_id');
        } else if ($input['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            //erp会员
            $info = $this->reportErpModel->getErpReportDetail($where, [], 'id,saler_id');
        } else if ($input['cooperation_type'] == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            //会员延期
            $info = $this->reportDelayModel->getDelayReportDetail($where, [], 'id,saler_id');
        }

        return $info;
    }

    public function editSaleReportStatus($id,$cooperation_type,$sale_status){
        //修改报备状态
        $save = [
            'status' => $sale_status,
            'update_time' => time()
        ];
        $map[] = ['id', '=', $id];

        if (in_array($cooperation_type, SalesReportCodeEnum::getCooperationZx())) {
            //装修会员
            $this->reportZxModel->saveOne($save, $map);
        } else if ($cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
            //三维家会员
            $this->reportSwjModel->saveOne($save, $map);
        } else if ($cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_ERP) {
            //erp会员
            $this->reportErpModel->saveOne($save, $map);
        } else if ($cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_DELAY) {
            //会员延期
            $this->reportDelayModel->saveOne($save, $map);
        }
    }

    public function getContractStatus($input){
        $input['current_contract_start'] = strtotime($input['current_contract_start']);
        $input['current_contract_end'] = strtotime($input['current_contract_end']);
        return self::checkContract($input);
    }

    public static function checkContract($info)
    {
        $is_abnormal = 0;//是否异常
        //获取城市数据,计算当前报备是否异常,只计算"装修会员","独家会员","垄断会员"
        if (empty($info['city_name']) || !in_array($info['cooperation_type'], SalesReportCodeEnum::getCooperationAbnormal())) {
            return $is_abnormal;
        }
        //验证必传字段
        if (empty($info['current_contract_amount']) || empty($info['current_contract_end']) || empty($info['current_contract_start']) || empty($info['viptype'])) {
            return $is_abnormal;
        }
        $cityDb = new SaleReportCityQuote();
        $city_info = $cityDb->getNewestCityInfo($info['city_name']);
        if (!empty($city_info)) {
            //（本次到账/（报价/365））/30
            $account_day = $city_info['year_quote'] ? $info['current_contract_amount'] / ((int)$city_info['year_quote'] / 365) : 0;//到账价格所算出天数
            $contract_day = ($info['current_contract_end'] - $info['current_contract_start']) / (60 * 60 * 24); //本次合同所算出天数
            //单倍会员:本次合同时间 超过了 到账价格算出的时间 三个月则提示
            //多倍会员:本次合同时间 超过了 到账价格算出的时间 两个月则提示( 0.5倍按多倍算)
            if ($info['viptype'] == 1 || $info['viptype'] == '1.0') {
                if (($contract_day > $account_day) && floor(($contract_day - $account_day) / 30) >= 3) {
                    $is_abnormal = 1;
                }
            } else {
                if (($contract_day > $account_day) && floor(($contract_day - $account_day) / 30) >= 2) {
                    $is_abnormal = 1;
                }
            }
        }
        return $is_abnormal;
    }


    /**
     * 获取小报备关联选择大报备列表
     * @param  [type] $input [description]
     * @param  [type] $page  [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getSelectPageList($input, $page, $limit){
        $input["saler_ids"] = AdminuserLogic::getAuthUserids();
        // $input["saler_ids"] = TeamLogic::getAccessAuthUsersIDs();
        $input["status"] = SalesReportCodeEnum::getPyamentRelatedStatus();

        $count = $this->reportZxModel->getSelectPageCount($input);
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            $list = $this->reportZxModel->getSelectPageList($input, $offset, $limit);
            $list = $this->setReportFormatter($list, true);
        }

        return [
            "count" => $count,
            "list" => $list ?? []
        ];
    }

    /**
     * 获取小报备关联选择三维家大报备列表
     * @param  [type] $input [description]
     * @param  [type] $page  [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getSelectSwjPageList($input, $page, $limit){
        $input["saler_ids"] = AdminuserLogic::getAuthUserids();
        // $input["saler_ids"] = TeamLogic::getAccessAuthUsersIDs();
        $input["status"] = SalesReportCodeEnum::getPyamentRelatedStatus();

        $count = $this->reportSwjModel->getSelectSwjPageCount($input);
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            $list = $this->reportSwjModel->getSelectSwjPageList($input, $offset, $limit);
            $list = $this->setReportFormatter($list, true);
        }

        return [
            "count" => $count,
            "list" => $list ?? []
        ];
    }


    /**
     * 会员公司大报备列表
     * @param  [type] $company_id [description]
     * @return [type]             [description]
     */
    public function getCompanyReportList($company_id){
        $list = $this->reportZxModel->getCompanyReportList($company_id);
        $list = $this->setReportFormatter($list);
        
        $reportDelayLogic = new ReportDelayLogic();
        $delayList = $reportDelayLogic->getCompanyReportDelayList($company_id);

        // 合并数组后重新排序
        $list = array_merge($list->toArray(), $delayList->toArray());
        $createTimeList = array_column($list, "create_time");
        array_multisort($createTimeList, SORT_DESC, $list);

        return $list;
    }

    // 数据处理
    private function setReportFormatter($list, $related = false){
        if (!empty($list)) {
            $relatedList = [];
            if ($related == true) {
                $reportIds = array_column($list->toArray(), "id");
                $cooperationTypes = array_column($list->toArray(), "cooperation_type");
                $cooperationTypes = array_unique($cooperationTypes);
                
                // 查询关联小报备次数和总金额
                $reportRelatedModel = new SaleReportRelated();
                $relatedList = $reportRelatedModel->getPaymentNumberList($reportIds, $cooperationTypes);
                $relatedList = array_column($relatedList->toArray(), null, "group_key");
            }

            foreach ($list as $key => &$item) {
                $item["cooperation_type_name"] = SalesReportCodeEnum::getCooperationType($item["cooperation_type"]);
                $item["is_new_name"] = !empty($item["is_new"]) ? SalesReportCodeEnum::getUserType($item["is_new"]) : "";
                $item["status_name"] = SalesReportCodeEnum::getReportStatus($item["status"]);

                $item["current_contract_start_date"] = $item["current_contract_start"] ? date("Y/m/d", $item["current_contract_start"]) : "";
                $item["current_contract_end_date"] = $item["current_contract_end"] ? date("Y/m/d", $item["current_contract_end"]) : "";
                $item["contract_start_date"] = !empty($item["contract_start"]) ? date("Y/m/d", $item["contract_start"]) : "";
                $item["contract_end_date"] = !empty($item["contract_end"]) ? date("Y/m/d", $item["contract_end"]) : "";
                $item["check_date"] = $item["check_time"] ? date("Y/m/d", $item["check_time"]) : "";
                $item["submit_date"] = $item["submit_time"] ? date("Y/m/d", $item["submit_time"]) : "";
                $item["created_date"] = date("Y/m/d H:i", $item["create_time"]);
                $item["viptype_ratio_show"] = SalesReportCodeEnum::getViptypeBackRatioShow($item["cooperation_type"]);

                // 小报备次数和总金额
                $group_key = sprintf("%d_%d", $item->id, $item->cooperation_type);
                $item["report_payment_number"] = $relatedList[$group_key]["report_payment_number"] ?? 0;
                $item["report_payment_money"] = $relatedList[$group_key]["report_payment_money"] ?? 0;
            }
        }

        return $list;
    }

	// 计算日期时间差
    private function diffMonthDate($time1, $time2) {
        if ($time1 > $time2) {
            $ymd = $time2;
            $time2 = $time1;
            $time1 = $ymd;
        }

        $time2 = $time2 + 86400;
        list($y1, $m1, $d1) = explode('-', date("Y-m-d", $time1));
        list($y2, $m2, $d2) = explode('-', date("Y-m-d", $time2));
        $y = $m = $d = $_m = 0;
        $math = ($y2 - $y1) * 12 + $m2 - $m1;
        $y = floor($math / 12);
        $m = intval($math % 12);
        $d = (mktime(0, 0, 0, $m2, $d2, $y2) - mktime(0, 0, 0, $m2, $d1, $y2)) / 86400;

        if ($d < 0) {
            $m -= 1;
            $d += date('j', mktime(0, 0, 0, $m2, 0, $y2));
        }

        if ($m < 0) {
            $m += 12;
            $y = $y - 1;
        }

        if ($y > 0) {
            $m = ($y * 12) + $m;
        }

        $res = $m > 0 ? $m . "月" : "";
        $res .= $d > 0 ? $d . "天" : "";
        return $res;
    }

    /**
     * 系统折算订单量
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @param float $viptype 会员类型
     * @param array $city_quote
     * @param string $year_month_one 过年月
     * @param string $year_month_two 过年月
     * @return false|float|int
     */
    private function correctedOrderCount($start, $end, $viptype, $city_quote, $year_month_one = '', $year_month_two = '')
    {
        //合同总时长
        $all_time = $end - $start;
        //获取过年月
        $year_month_one = SalesReportCodeEnum::getYearMonth($year_month_one);
        $year_month_two = SalesReportCodeEnum::getYearMonth($year_month_two);
        //去除过年月的时长
        if (!empty($year_month_one)) {
            $month_start = strtotime($year_month_one['start']);
            $month_end = strtotime($year_month_one['end']);
            //过年月结束时间在合同内
            if ($month_start < $start && $month_end >= $start && $month_end < $end) {
                $all_time -= $month_end - $start;
            } elseif ($month_start >= $start && $month_end <= $end) {
                //过年月开始/结束时间在合同内
                $all_time -= $month_end - $month_start;
            } elseif ($month_start >= $start && $month_start < $end && $end < $month_end) {
                //过年月开始时间在合同内
                $all_time -= $end - $month_start;
            }
        }

        if (!empty($year_month_two)) {
            $month_start = strtotime($year_month_two['start']);
            $month_end = strtotime($year_month_two['end']);
            if ($month_start < $start && $month_end >= $start && $month_end < $end) {
                $all_time -= $month_end - $start;
            } elseif ($month_start >= $start && $month_end <= $end) {
                //过年月开始/结束时间在合同内
                $all_time -= $month_end - $month_start;
            } elseif ($month_start >= $start && $month_start < $end && $end < $month_end) {
                //过年月开始时间在合同内
                $all_time -= $end - $month_start;
            }
        }
        //合同天数
        $contractDay = round($all_time / 86400);
        $grade_order = 0;
        if ($viptype >= 0.5 && $viptype <= 1.5) {
            //0.5~1.5倍：倍数*A档单量
            if (!empty($city_quote['grade_a_order'])) {
                $grade_order = !empty($city_quote['grade_a_order']) ? $viptype * $city_quote['grade_a_order'] : 0;
            }
        } elseif ($viptype >= 2 && $viptype <= 2.5) {
            //2~2.5倍：倍数*B档单量/2
            if (!empty($city_quote['grade_b_order'])) {
                $grade_order = !empty($city_quote['grade_b_order']) ? $viptype * $city_quote['grade_b_order'] / 2 : 0;
            }
        } elseif ($viptype >= 3 && $viptype <= 3.5) {
            //3~3.5倍：倍数*C档单量/3
            if (!empty($city_quote['grade_c_order'])) {
                $grade_order = !empty($city_quote['grade_c_order']) ? $viptype * $city_quote['grade_c_order'] / 3 : 0;
            }
        } elseif ($viptype >= 4 && $viptype <= 10) {
            //4~10倍：倍数*D档单量/4
            if (!empty($city_quote['grade_d_order'])) {
                $grade_order = !empty($city_quote['grade_d_order']) ? $viptype * $city_quote['grade_d_order'] / 4 : 0;
            }
        }
        return $grade_order != 0 ? ceil($grade_order / 365 * $contractDay) : 0;
    }

    /*
    * 查询最近的两条修改记录
    */
    public function getHistroyModifyList($report_id)
    {
        $res = [
            'username' => '',
            'date' => '',
            'count' => 0,
            'list' => []
        ];

        $map = [
            'status'  => 2,
            'report_id' => $report_id
        ];
        $logModel = new SaleReportLog();
        $logList =  $logModel->getHistroyModifyList($map, 2);
        if(empty($logList) || count($logList) != 2){
            return $res;
        }
        
        $res['username'] = $logList[0]['op_name'];
        $res['date'] = date('Y-m-d H:i:s', $logList[0]['created_at']);

        $twoMember = [];
        foreach ($logList as $v) {
            $twoMember[] = json_decode($v['save_info'], true);
        }

        $diff = $this->compareArrDiff($twoMember[0], $twoMember[1]);
        $res['list'] = $this->dealFinalNeed($diff);
        $res['count'] = count($res['list']);

        return $res;
    }

    private function dealFinalNeed($data)
    {
        if(empty($data)) return [];

        $res = [];
        foreach ($data as $key => $value) {
            $isContinue = false;
            switch ($key) {
                case 'cs':
                    $isContinue = true;
                    break;
                case 'imgs':
                    $ready[0] = '修改了上传凭证';
                    $ready[1] = '修改了上传凭证';
                    break;
                case 'cooperation_mode':
                    $ready[0] = $value[0] == 1 ? '一年' : '终身';
                    $ready[1] = $value[1] == 1 ? '一年' : '终身';
                    break;
                case 'cooperation_type':
                    $ready[0] = $this->cooperationType[$value[0]];
                    $ready[1] = $this->cooperationType[$value[1]];
                    break;
                case 'is_new':
                    $ready[0] = $value[0] == 1 ? '新会员' : '老会员';
                    $ready[1] = $value[1] == 1 ? '新会员' : '老会员';
                    break;
                case 'lxs':
                    $ready[0] = Report::lxsType($value[0]);
                    $ready[1] = Report::lxsType($value[1]);
                    break;
                case 'is_kf_voucher':
                    $ready[0] = $value[0] == 1 ? '是' : '否';
                    $ready[1] = $value[1] == 1 ? '是' : '否';
                    break;
                case 'company_mode':
                    $ready[0] = Report::getCompanyMode($value[0]);
                    $ready[1] = Report::getCompanyMode($value[1]);
                    break;
                case 'passage_position':
                    $ready[0] = Report::passagePosition($value[0]);
                    $ready[1] = Report::passagePosition($value[1]);
                    break;
                case 'lave_amount':
                    $ready[0] = $value[0] == -1 ? '' : $value[0];
                    $ready[1] = $value[1] == -1 ? '' : $value[1];
                    break;
                default:
                    $ready = $value;        
                    break;
            }

            if($isContinue) continue;

            $res[] = [ 
                'key' => $key,
                'keyname' => Report::reportFieldName($key),
                'now' => $ready[0],
                'last' => $ready[1]
            ];
        }

        return $res;
    }

    private function compareArrDiff($arr1, $arr2)
    {
        if(empty($arr1) || empty($arr2)){
            return [];
        }

        $diff = [];
        if(count($arr1) >= count($arr2)){
            foreach ($arr1 as $k => $v) {
                if(!isset($arr2[$k])){
                    if($v !== ''){
                        $diff[$k][0] = $v;
                        $diff[$k][1] = '';
                    }
                    continue;
                }

                if($v !== $arr2[$k]){
                    $diff[$k][0] = $v;
                    $diff[$k][1] = $arr2[$k];
                }
            }
        }else{
            foreach ($arr2 as $k => $v) {
                if(!isset($arr1[$k])){
                    if($v !== ''){
                        $diff[$k][0] = '';
                        $diff[$k][1] = $v;
                    }
                    continue;
                }

                if($v !== $arr1[$k]){
                    $diff[$k][0] = $arr1[$k];
                    $diff[$k][1] = $v;
                }
            }
        }

        return $diff;
    }
    
}