<?php
// +----------------------------------------------------------------------
// | OrdersLogic 订单逻辑层
// +----------------------------------------------------------------------
namespace app\common\model\logic;
use app\common\model\db\LogOrderCheckSheet;
use app\common\model\db\UserCompanyAccount;
use Home\Model\Logic\CompanyLogicModel;
use think\Db;
use think\Exception;
use think\Collection;
use app\common\model\db\User;
use app\common\model\db\Jiage;
use app\common\model\db\Orders;
use app\common\model\db\Fangshi;
use app\common\model\db\OrderInfo;
use app\common\model\db\LogQiandanchk;
use app\common\model\db\OrderCompanyReview;
use app\common\model\db\LogTelcenterOrdercall;

use app\common\model\service\MsgService;
use app\common\enum\MsgTemplateCodeEnum;
use think\Request;

class OrdersLogic
{
    /**
     * 获取签单订单列表
     *
     * @param array $map
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getQiandanList($param, $page, $pageSize)
    {
        $map['ck.order_on'] = 4;
        $map['o.qiandan_status'] = 1;
        //默认30天申请签单时间数据
        $qd_start_time = strtotime('-31 day', strtotime('tomorrow'));
        $qd_end_time = strtotime('tomorrow');
        $map['o.qiandan_addtime'] = ['between', [$qd_start_time, $qd_end_time]];

        //申请签单时间
        if (!empty($param['sign_start']) && !empty($param['sign_end'])) {
            $qd_start_time = strtotime($param['sign_start']);
            $qd_end_time = strtotime($param['sign_end'] . ' 23:59:59');
            $map['o.qiandan_addtime'] = ['between', [$qd_start_time, $qd_end_time]];
        } else if (empty($param['sign_start']) && !empty($param['sign_end'])) {
            $qd_start_time = '';
            $qd_end_time = '';
            return sys_response(4000002, '请选择开始时间', []);
        } else if (!empty($param['sign_start']) && empty($param['sign_end'])) {
            $qd_start_time = '';
            $qd_end_time = '';
            return sys_response(4000002, '请选择结束时间', []);
        }

        //审核查询时间
        if (!empty($param['verify_start']) && !empty($param['verify_end'])) {
            $map['o.qiandan_chktime'] = ['between', [strtotime($param['verify_start']), strtotime($param['verify_end'] . ' 23:59:59')]];
            unset($map['o.qiandan_addtime']);
        } else if (empty($param['verify_start']) && !empty($param['verify_end'])) {
            return sys_response(4000002, '请选择开始时间', []);
        } else if (!empty($param['verify_start']) && empty($param['verify_end'])) {
            return sys_response(4000002, '请选择结束时间', []);
        }

        //订单发布时间
        if (!empty($param['publish_start']) && !empty($param['publish_end'])) {
            $map['o.time_real'] = ['between', [strtotime($param['publish_start']), strtotime($param['publish_end'] . ' 23:59:59')]];
            unset($map['o.qiandan_addtime']);
        } else if (empty($param['publish_start']) && !empty($param['publish_end'])) {
            return sys_response(4000002, '请选择开始时间', []);
        } else if (!empty($param['publish_start']) && empty($param['publish_end'])) {
            return sys_response(4000002, '请选择结束时间', []);
        }

        //订单分配时间
        if (!empty($param['allocate_start']) && !empty($param['allocate_end'])) {
            $map['ck.lasttime'] = ['between', [strtotime($param['allocate_start']), strtotime($param['allocate_end'] . ' 23:59:59')]];
            unset($map['o.qiandan_addtime']);
        } else if (empty($param['allocate_start']) && !empty($param['allocate_end'])) {
            return sys_response(4000002, '请选择开始时间', []);
        } else if (!empty($param['allocate_start']) && empty($param['allocate_end'])) {
            return sys_response(4000002, '请选择结束时间', []);
        }


        if (!empty($param['fw'])) {
            $param['fw'] = intval($param['fw']);
            if ($param['fw'] == 1) {
                $map['o.type_fw'] = 1;
            }
            if ($param['fw'] == 2) {
                $map['o.type_fw'] = 2;
            }
        }

        if (!empty($param['company'])) {
            if (ctype_digit((string)$param['company'])) {
                $map['o.qiandan_companyid'] = $param['company'];
            } else {
                //如果有查询公司 , 先去获取匹配对应的公司id
                $user = new User();
                $comid = $user->getCompanyByNameMatchAll(['company_name' => $param['company']]);
                if (empty($comid)) {
                    return sys_response(0, '', [
                        'list' => [],
                        'page' => sys_paginate(0, $pageSize, $page)
                    ]);
                } else {
                    $map['o.qiandan_companyid'] = ['in', array_column($comid->toArray(), 'id')];
                }
            }
        }

        // 非超级管理员获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if (!empty($citys)) {
            $map['o.cs'] = ['in', $citys];
            if (!empty($param['cs'])) {
                if (in_array($param['cs'], $citys)) {
                    $map['o.cs'] = $param['cs'];
                } else {
                    return sys_response(4000002, '暂无权限查看数据', []);
                }
            }
        } else {
            return sys_response(4000002, '暂无权限查看数据', []);
        }

        $orderModel = new Orders();

        $field = [
            'o.id',
            'o.name',
            'o.cs',
            'o.qx',
            'o.xiaoqu',
            'o.mianji',
            'o.qiandan_status',
            'o.qiandan_companyid',
            'o.qiandan_mianji',
            'o.qiandan_jine',
            'o.time_real' => 'publish_time',
            'o.type_fw' => 'order_status',
            'o.qiandan_addtime' => 'sign_time',
            'o.qiandan_chktime' => 'verify_time',
            'ck.lasttime' => 'allocate_time',
            'u.jc' => 'qiandan_company',
            'q.cname' => 'city',
            'a.qz_area' => 'area',
        ];

        if (!empty($param['down'])) {
            $list = $orderModel->getQiandanList($map, null, null, [], $field);
            return sys_response(0, '', ['list' => $list]);
        } else {
            $count = $orderModel->getQiandanCount($map);
            $list = [];
            if ($count) {
                $list = $orderModel->getQiandanList($map, $page, $pageSize, [], $field);
            }
            $qiandanLog = new LogQiandanchk();
            $list = array_map(function ($value) use ($qiandanLog){
                $value['opname'] = $qiandanLog->getLastLog($value['id'],'opname');
                array_walk($value, function (&$val, $key) {
                    if ($val && is_string($key) && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                        $val = date('Y-m-d', $val);
                    } else {
                        $val = is_null($val) ? '' : $val;
                    }
                });
                return $value;
            }, Collection::make($list)->toArray());

            return sys_response(0, '', [
                'list' => $list,
                'page' => sys_paginate($count, $pageSize, $page),
                'search' => ['qd_start_time' => $qd_start_time, 'qd_end_time' => $qd_end_time],
            ]);
        }
    }

    /**
     * 获取订单详细信息
     *
     * @param $oid
     * @return array|null|\PDOStatement|string|\think\Model
     */
    public function orderInfo($oid, $with = ['companys'])
    {
        $map['o.id'] = $oid;
        $orderModel = new Orders();
        $info = $orderModel->orderinfo($map, $with);
        if ($info) {
            $info = $info->toArray();
            $info['allot_num'] = (!empty($info['allot_num']) ? $info['allot_num']['allot_num'] : 0);
            array_walk($info, function (&$val, $key) {
                if ($val && is_string($key) && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                    $val = date('Y-m-d H:i:s', $val);
                } elseif ($key !== 'qiandan_status') {
                    $val = is_null($val) ? '' : $val;
                }
            });
        }
        //获取小报备记录
        $reportLogic = new ReportPaymentLogic();
        $report = $reportLogic->getOrderBackPaymentCount($oid);
        $info['report_count'] = isset($report[$oid]) ? $report[$oid] : 0;
        return $info;
    }

    /**
     * 获取订单查看时间列表
     *
     * @param $map
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getReadOrderList($param, $page, $pageSize)
    {
        $start_time = strtotime('-31 day', strtotime('tomorrow'));
        $end_time = strtotime('tomorrow');
        $map['i.addtime'] = ['between', [$start_time, $end_time]];
        if (!empty($param['start']) && !empty($param['end'])) {
            $start_time = strtotime($param['start']);
            $end_time = strtotime($param['end'] . ' 23:59:59');
            $map['i.addtime'] = ['between', [$start_time, $end_time]];
        } elseif (!empty($param['start']) && empty($param['end'])) {
            $start_time = strtotime($param['start']);
            $end_time = '';
            $map['i.addtime'] = ['egt', $start_time];
        }

        if (!empty($param['company'])) {
            if (ctype_digit((string)$param['company'])) {
                $map['i.com'] = $param['company'];
            } else {
                //如果有查询公司 , 先去获取匹配对应的公司id
                $user = new User();
                $comid = $user->getCompanyByNameMatchAll(['company_name' => $param['company']]);
                if (count($comid) > 0) {
                    $map['i.com'] = ['in', array_column($comid->toArray(), 'id')];
                } else {
                    return sys_response(0, '', ['list' => [], 'page' => sys_paginate(0, $pageSize, $page)]);
                }
            }
        }

        if (!empty($param['order'])) {
            $map['i.order'] = $param['order'];
            unset($map['i.addtime']);
            $start_time = "";
            $end_time = "";
        }
        if (!empty($param['isread'])) {
            $map['i.isread'] = $param['isread'] - 1;
        }
        if (!empty($param['orderby'])) {
            $map['orderby'] = $param['orderby'];
        }

        // 非超级管理员获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if (!empty($citys)) {
            $map['u.cs'] = ['in', $citys];
            if (!empty($param['cs'])) {
                if (in_array($param['cs'], $citys)) {
                    $map['u.cs'] = $param['cs'];
                } else {
                    return sys_response(4000002, '暂无权限查看数据', []);
                }
            }
        } else {
            return sys_response(4000002, '暂无权限查看数据', []);
        }

        //获取API列表
        $orderInfoModel = new OrderInfo();
        $count = $orderInfoModel->getReadOrderCount($map);
        if ($count) {
            $field = [
                'i.order',
                'i.order' => 'order_id',
                'u.cs',
                'q.cname',
                'i.addtime' => 'fen_time',
                'i.addtime',
                'i.isread',
                'i.readtime',
                'i.allot_source',
                'u.id' => 'company_id',
                'u.qc',
                'u.jc',
                '(IF(i.readtime > 0,(i.readtime - i.addtime),0)) as diff_time',
            ];
            $list = $orderInfoModel->getReadOrderList($map, $page, $pageSize, $field, request()->isMobile() ? ['orderTime'] : []);
            $list->append(['diff_time']);
            $list = array_map(function ($value) {
                array_walk($value, function (&$val, $key) {
                    if ($val && is_string($key) && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {

                        $val = date(request()->isMobile() ? 'Y-m-d H:i:s' : 'Y-m-d', $val);
                    } else {
                        $val = is_null($val) ? '' : $val;
                    }
                });
                return $value;
            }, Collection::make($list)->toArray());
        }
        return sys_response(0, '', [
            'list' => !empty($list) ? $list : [],
            'page' => sys_paginate($count, $pageSize, $page),
            'search' => ['start_time' => $start_time,
                         'end_time' => $end_time]
        ]);
    }

    /**
     * 获取签单订单列表
     *
     * @param $param
     * @param $p
     * @param $psize
     * @return array
     */
    public function qdOrders($param, $p, $psize)
    {
        // 分单/赠单
        if (!empty($param['fw'])) {
            $map['o.type_fw'] = intval($param['fw']);
        }

        // 签单状态
        if (isset($param['qdstatus']) && $param['qdstatus'] !== '' && $param['qdstatus'] !== null) {
            $map['o.qiandan_status'] = intval($param['qdstatus']);
        }

        // 申请签单时间
        if (!empty($param['start']) && !empty($param['end'])) {
            $map['o.qiandan_addtime'] = ['between', [strtotime($param['start']), strtotime($param['end'] . ' 23:59:59')]];
        } else {
            $map['o.qiandan_addtime'] = ['egt', strtotime('-30 days')];
        }

        // 非超级管理员获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if (!empty($citys)) {
            $map['o.cs'] = ['in', $citys];
            if (!empty($param['cs'])) {
                if (in_array($param['cs'], $citys)) {
                    $map['o.cs'] = $param['cs'];
                } else {
                    return sys_response(4000002, '暂无权限查看数据', []);
                }
            }
        } else {
            return sys_response(4000002, '暂无权限查看数据', []);
        }

        // 关键词查询条件
        if (!empty($param['search'])) {
            $map['search'] = $param['search'];
        }

        // 分页查询
        $orderModel = new Orders();
        $count = $orderModel->qdOrdersCount($map);
        $respData['list'] = [];
        $respData['page'] = sys_paginate($count, $psize, $p);
        $respData['timestamp'] = $map['o.qiandan_addtime'][1];
        if ($count > 0) {
            $lists = $orderModel->qdOrdersList($map, $p, $psize, ['city', 'area'], 'o.id,o.cs,o.qx,o.on,o.type_fw,o.mianji,o.qiandan_mianji,o.qiandan_jine,o.qiandan_addtime,o.qiandan_status,o.qiandan_chktime,o.time_real time,o.xiaoqu,o.name,o.sex,o.qiandan_companyid,u.qc,u.jc');
            $respData['list'] = array_map(function ($value) {
                array_walk($value, function (&$val, $key) {
                    if ($val && is_string($key) && stripos($key, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                        $val = date('Y-m-d H:i:s', $val);
                    } else {
                        $val = is_null($val) ? '' : $val;
                    }
                });
                return $value;
            }, Collection::make($lists)->toArray());
        }
        return sys_response(0, '', $respData);
    }

    /**
     * 签单订单审核
     *
     * @param array $post 订单数据
     * @return array
     */
    public function qd_status_change($post = [],$user = null)
    {
        Db::startTrans();
        try {
            $orderModel = new Orders();
            $qiandanLogModel = new LogQiandanchk();

            //获取订单信息
            $info = $orderModel->orderinfo(['o.id' => $post['id']], []);

            if (empty($info)) {
                throw new Exception("订单不存在");
            }

            // 保留原始信息
            $company_id = $info->qiandan_companyid;
            $qiandan_status = $info->qiandan_status;

            $info->qiandan_status = $post['qiandan_status'];
            $info->qiandan_chktime = time();

            // 获取签单公司信息
            $companyLogic = new CompanyLogic();
            $userCompany = $companyLogic->getUserCompanyInfoByUserId($company_id);

            if (empty($userCompany)) {
                throw new Exception("签单公司不存在");
            }

            //签单金额同步到账户表中
            $accountModel = new UserCompanyAccount();
            $amount = $info->qiandan_jine < 1000 ? $info->qiandan_jine * 10000 : $info->qiandan_jine;

            //获取订单的分配时间
            $infoDb = new OrderInfo();
            $where = ['order' => ['eq', $info->id], 'com' => ['eq', $company_id]];
            $orderInfo = $infoDb->getOrderCompanyInfo($where,"addtime,cooperate_mode")->toArray();
            $fen_time = date("Y-m-d",$orderInfo[0]["addtime"]);

            $mode = $orderInfo[0]["cooperate_mode"];

            //判断当前的订单的分配时间是否在会员有效期内
            $userModel = new UserVipLogic();
            $contractInfo = $userModel->getCompanystanrdContractInfo($company_id,$mode);

            if ($post['qiandan_status'] == 0) {
                //取消审核则直接删除已经生成的工地直播数据
                $workSiteLiveLogic = new WorkSiteLiveLogic();
                $workSiteLiveLogic->delWorkSite($post['id']);

                // 重置装修公司为量房状态
                $OrderCompanyReviewmodel = new OrderCompanyReview();
                $OrderCompanyReviewmodel->editReview(['orderid' => $info->id, 'comid' => $company_id], ['status' => 1]);

                // 重新统计订单量房数据
                $orderStatsLogic = new OrderStatsLogic();
                $orderStatsLogic->setOrderLfStats($info["id"]);

                //如果审核不通过，默认取消审核操作
                $info->qiandan_companyid = 0;
                $info->qiandan_mianji = 0;
                $info->qiandan_jine = 0;
                $info->qiandan_addtime = 0;
                $info->qiandan_chktime = 0;
                $info->qiandan_status = 0;
                $info->qiandan_remark = "";
                $info->qiandan_info = "";
                $info->qiandan_distance = 0; // 取消审核签单距离也置为0

                if (in_array($orderInfo[0]["cooperate_mode"],[3,4])) {
                    //分配时间再合同有效内的
                    if (count($contractInfo) > 0 && $contractInfo["end_time"] <= $fen_time && $contractInfo["start_time"] >= $fen_time) {
                        //取消当前账户的签约金额
                        $accountModel->setGuaranteedAmountDec($company_id,$amount);
                    }


                    //添加标杆会员签单日志
                    $logModel = new LogOrderCheckSheet();
                    $data = [
                        "order_id" => $info->id,
                        "company_id" => $company_id,
                        "contract_start" => $contractInfo["start_time"],
                        "contract_end" => $contractInfo["end_time"],
                        "amount" => $amount,
                        "state" => 2,
                        "op_uid" => $user["user_id"],
                        "op_name"=> $user["nick_name"],
                    ];

                    $logModel->addLog($data);
                }
            } else if ($post['qiandan_status'] == 1) {
                // 确认签单计算签单返点金额
                $companyPackageOrderLogic = new CompanyPackageOrderLogic();
                $companyPackageOrderLogic->signbackOrder($info["id"], $company_id, $info->qiandan_jine);

                // 确认签单计算新签单返点金额
                $companyRoundOrderLogic = new CompanyRoundOrderLogic();
                $companyRoundOrderLogic->signbackRoundOrder($info["id"], $company_id, $info->qiandan_jine);

                // 计算签单审核通过后的，签单距离
                $info->qiandan_distance = 0;
                if (!empty($info->lng) && !empty($info->lat) && !empty($userCompany->lng) && !empty($userCompany->lat)) {
                    $info->qiandan_distance = get_distance(array($info->lng, $info->lat), array($userCompany->lng, $userCompany->lat));
                }

                if (in_array($orderInfo[0]["cooperate_mode"],[3,4])) {
                    if (count($contractInfo) > 0 && $contractInfo["end_time"] <= $fen_time && $contractInfo["start_time"] >= $fen_time) {
                        //添加当前账户的签约金额
                        $accountModel->setGuaranteedAmountInc($company_id,$amount);
                    }

                    //添加标杆会员签单日志
                    $logModel = new LogOrderCheckSheet();
                    $data = [
                        "order_id" => $info->id,
                        "company_id" => $company_id,
                        "contract_start" => $contractInfo["start_time"],
                        "contract_end" => $contractInfo["end_time"],
                        "amount" => $amount,
                        "state" => 1,
                        "op_uid" => $user["user_id"],
                        "op_name"=> $user["nick_name"],
                    ];

                    $logModel->addLog($data);
                }
            }

            $result = $info->save();
            if ($result === false) {
                throw new Exception("操作失败");
            }

            //添加签单日志
            if ($info['qiandan_status'] == 0) {
                $action = 'qd_chkc';
            } elseif ($info['qiandan_status'] == 2) {
                $action = 'qd_chk_on';
            } else {
                $action = 'qd_chks';
            }

            $post['qiandan_chktime'] = $info->qiandan_chktime;
            $status = $qiandanLogModel->addLog('fenpei', $info->id, $action, $post);
            if(!$status){
                throw new Exception("添加日志失败");
            }

            // 向公司推送消息
            if (!empty($userCompany)) {
                $params = [];
                $msg_template_id = "";
                if ($post['qiandan_status'] == 1 && $qiandan_status != $post['qiandan_status']) {
                    // 签单审核通过通知
                    $msg_template_id = MsgTemplateCodeEnum::QIANDAN_CHECK_PASS;
                } else if ($post['qiandan_status'] == 0) {
                    // 签单审核不通过
                    $msg_template_id = MsgTemplateCodeEnum::QIANDAN_CHECK_FAIL;
                    $params[] = $info["id"];
                }

                // QZMSG 发送消息提醒
                if (!empty($msg_template_id)) {
                    $msgService = new MsgService();
                    $linkParam = "?order_id=" . $info["id"];
                    $msgService->sendCompanyMsg($msg_template_id, $company_id, $linkParam, $info["id"], "签单审核", $params);

                    // 查询员工向员工发送消息
                    $companyEmployeeLogic = new CompanyEmployeeLogic();
                    $companyEmployees = $companyEmployeeLogic->getOrderCompanyEmployees($company_id, $info["id"]);
                    if (!empty($companyEmployees[$company_id])) {
                        $employeeIds = array_column($companyEmployees[$company_id], "employee_id");
                        $msgService->sendCompanyMsg($msg_template_id, $company_id, $linkParam, $info["id"], "签单审核", $params, $employeeIds);
                    }
                }
            }

            Db::commit();
            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            Db::rollback();
            $errcode = 5000002;
            $errmsg = $e->getMessage();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 获取用户工作列表
     * @param $citys
     * @return array
     */
    public function getUserJobOrders($citys)
    {
        //获取最新的五条量房订单
        $reviewDb = new OrderCompanyReview();
        $data = $reviewDb->getNewestLfOrder($citys)->toArray();
        //获取最新的五条签单
        $orderDb = new Orders();
        $qian_orders = $orderDb->getQianOrdersOrdersByCity($citys, 5, 'o.qiandan_addtime DESC,o.qiandan_status DESC ');
        $data = count($qian_orders) > 0 ? array_merge($data, $qian_orders->toArray()) : $data;
        //根据时间排序
        $order_time = array_column($data, 'order_time');
        array_multisort($order_time, SORT_DESC, $data);
        //取出前五条 , 并区分
        $returnData = [];
        foreach ($data as $k => $v) {
            //区分查看订单/审核订单
            $returnData[$k] = [
                'order_time' => date('Y-m-d', $v['order_time']),
                'order_id' => $v['order_id']
            ];
            if (isset($v['qiandan_status'])) {
                $returnData[$k]['order_info'] = '有签单需要审核';
                $returnData[$k]['order_status'] = $v['qiandan_status'];
            } else {
                $returnData[$k]['order_info'] = '有新的量房订单';
                $returnData[$k]['order_status'] = null;
            }
            unset($data[$k]);
        }
        return $returnData;
    }

    /**
     * 会员分单明细
     * 如果查询订单则不需要默认管辖城市和默认查询时间
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getOrderInfoList($input, $page = 1, $limit = 10){
        if (!empty($input["order"])) {
            unset($input["citys"]); // 查询订单时不限制管辖城市
        } else if (empty($input["start"]) || empty($input["end"])) {
            $input["start"] = date("Y-m-d", strtotime("-30 days"));
            $input["end"] = date("Y-m-d");
        }

        $list = [];
        $orderInfoModel = new OrderInfo();
        $count = $orderInfoModel->getOrderInfoPageCount($input);
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            if (!empty($input["down"])) {
                $offset = $limit = 0;
            }

            $list = $orderInfoModel->getOrderInfoPageList($input, $offset, $limit);

            foreach ($list as $key => &$item) {
                $item["time_real"] = date("Y-m-d", $item["time_real"]);
                $item["fen_time"] = date("Y-m-d", $item["fen_time"]);

                // 只有签单公司显示签单记录
                if ($item["qiandan_companyid"] == $item["company_id"]) {
                    $item["qiandan_addtime"] = $item["qiandan_addtime"] ? date("Y-m-d", $item["qiandan_addtime"]) : "";
                    $item["qiandan_chktime"] = $item["qiandan_chktime"] ? date("Y-m-d", $item["qiandan_chktime"]) : "";
                } else {
                    $item["qiandan_companyid"] = "";
                    $item["qiandan_addtime"] = "";
                    $item["qiandan_chktime"] = "";
                    $item["qiandan_mianji"] = "";
                    $item["qiandan_jine"] = "0";
                    $item["qiandan_status"] = "";
                }

                // 会员量房状态（1：已量房；2：未量房；3：未知）
                $item["liangfang_status"] = 3;
                if ($item["status"] == 3) {
                    $item["liangfang_status"] = 2;
                } else if (in_array($item["status"], [1, 2, 4])) {
                    $item["liangfang_status"] = 1;
                }

                // 订单量房状态（1：已量房；2：未量房；3：未知）
                $item["order_liangfang_status"] = 3;
                if ($item["order_liangfang"] >= 1) {
                    $item["order_liangfang_status"] = 1;
                } else if ($item["order_no_liangfang"] >= $item["order_fen_count"] - 1) {
                    $item["order_liangfang_status"] = 2;
                }

                unset($item["order_liangfang"], $item["order_no_liangfang"], $item["order_fen_count"]);
            }
        }

        $options = [
            "start" => !empty($input["start"]) ? $input["start"] : "",
            "end" => !empty($input["end"]) ? $input["end"] : ""
        ];

        return ["list" => $list, "count" => $count, "options" => $options];
    }


    public function getCityOrderStat($city,$begin,$end,$yusuan,$lx,$fangshi)
    {
        if (!is_array($city)) {
            $city = array_filter(explode(",",$city));
        }

        if (count($city) == 0) {
            return [];
        }
        $begin = strtotime($begin);
        $end = strtotime($end) + 86400 - 1;

        $model = new Orders();
        //发单数据
        $result = $model->getCityOrderStat($city,$begin,$end,$yusuan,$lx,$fangshi);

        $list = [];
        if (count($result) > 0) {
            $result = $result->toArray();
            array_walk($result,function(&$value,$key) use (&$list){
                if (!empty($value["cs"])) {
                    $list[$value["cs"]] = [
                        "cname" => $value["cname"],
                        "count" => $value["count"],
                        "fen_count" => $value["fen_count"],
                        "yusuan_count" => 0,
                        "lx_count" => 0,
                        "fangshi_count" => 0,
                    ];

                    if (isset($value["yusuan_count"])) {
                        $list[$value["cs"]]["yusuan_count"] = $value["yusuan_count"];
                    }

                    if (isset($value["lx_count"])) {
                        $list[$value["cs"]]["lx_count"] = $value["lx_count"];
                    }

                    if (isset($value["fangshi_count"])) {
                        $list[$value["cs"]]["fangshi_count"] = $value["fangshi_count"];
                    }
                }
            });
        }
        return $list;
    }

    public function getYuSuan()
    {
        $model = new Jiage();
        return $model->getJiage();
    }

    public function getFangshi()
    {
        $model = new Fangshi();
        return $model->getFs();
    }

    /**
     * 城市分单统计
     * @param $input
     * @return array
     */
    public function orderReview($input) {
        $city_id = check_variate($input["city_id"], 0);
        $starttime = check_variate($input["starttime"]);
        $endtime = check_variate($input["endtime"]);

        $ordersModel = new Orders();
        $list = $ordersModel->orderReview($city_id, $starttime, $endtime);

        // 总计
        $countinfo = array(
            "all_fa" => 0,
            "all_weihu" => 0,
            "all_yihu" => 0,
            "all_fen" => 0,
            "all_fenother" => 0,
            "all_fen_lv" => 0,
            "all_zen" => 0,
            "all_zenother" => 0,
            "all_zen_lv" => 0,
            "all_zong_lv" => 0,
            "all_validnum" => 0,
            "all_qiandan" => 0,
            "all_qiandan_lv" => 0,
            "all_csos_fendan" => 0,
            "all_csos_zendan" => 0
        );

        if (count($list) > 0) {
            foreach ($list as $key => $val) {
                foreach ($val as $k => $v) {
                    if (is_null($v)) {
                        $val[$k] = 0;
                        $list[$key][$k] = 0;
                    }
                }

                $list[$key]["weihu"] = $val["xin"] + $val["cixin"];         // 未呼量
                $list[$key]["yihu"] = $val["fadan"] - $list[$key]["weihu"]; // 已呼量
                $list[$key]["fendan_lv"] = $val["fadan"] ? round($val["fen"] / $val["fadan"], 4) * 100 : 0; // 分单率
                $list[$key]["zendan_lv"] = $val["fadan"] ? round($val["zen"] / $val["fadan"], 4) * 100 : 0; // 赠单率
                $list[$key]["zongx_lv"] = round($list[$key]["fendan_lv"] + $list[$key]["zendan_lv"], 2);
                $list[$key]["qiandan_lv"] = $val["validnum"] ? round($val["qiandan"] / $val["validnum"], 4) * 100 : 0; // 签单率
                $list[$key]['csos_fendan'] = $val['csos_fendan'];
                $list[$key]['csos_zendan'] =  $val['csos_zendan'];

                $countinfo["all_fa"] = $countinfo["all_fa"] + $val["fadan"];
                $countinfo["all_weihu"] = $countinfo["all_weihu"] + $list[$key]["weihu"];
                $countinfo["all_yihu"] = $countinfo["all_yihu"] + $list[$key]["yihu"];
                $countinfo["all_fen"] = $countinfo["all_fen"] + $val["fen"];
                $countinfo["all_fenother"] = $countinfo["all_fenother"] + $val["fen_other"];
                $countinfo["all_zen"] = $countinfo["all_zen"] + $val["zen"];
                $countinfo["all_zenother"] = $countinfo["all_zenother"] + $val["zen_other"];
                $countinfo["all_validnum"] = $countinfo["all_validnum"] + $list[$key]["validnum"];
                $countinfo["all_qiandan"] = $countinfo["all_qiandan"] + $list[$key]["qiandan"];
                $countinfo["all_csos_fendan"] = $countinfo["all_csos_fendan"] + $val['csos_fendan'];
                $countinfo["all_csos_zendan"] = $countinfo["all_csos_zendan"] + $val['csos_zendan'];
            }

            $countinfo["all_fen_lv"] = $countinfo["all_fa"] ? round($countinfo["all_fen"] / $countinfo["all_fa"], 4) * 100 : 0; //合计- 分单率
            $countinfo["all_zen_lv"] = $countinfo["all_fa"] ? round($countinfo["all_zen"] / $countinfo["all_fa"], 4) * 100 : 0; //合计- 赠单率
            $countinfo["all_qiandan_lv"] = $countinfo["all_validnum"] ? round($countinfo["all_qiandan"] / $countinfo["all_validnum"], 4) * 100 : 0; //合计- 签单率
            $countinfo["all_zong_lv"] = round($countinfo["all_fen_lv"] + $countinfo["all_zen_lv"], 2);

        }

        //分单率
        if (!empty($input["fen_lv"])) {
            //1.分单率从低到高 2.分单率从高到低
            $sort = $input["fen_lv"] == 1 ? SORT_ASC : SORT_DESC;
            $list = sys_array_multisort($list, "fendan_lv", $sort);
        }

        // 赠单率
        if (!empty($input["zen_lv"])) {
            //1.赠单率从低到高 2.赠单率从高到低
            $sort = $input["zen_lv"] == 1 ? SORT_ASC : SORT_DESC;
            $list = sys_array_multisort($list, "zendan_lv", $sort);
        }

        // 总有效率
        if (!empty($input["zongx_lv"])) {
            //1.总有效率从低到高 2.总有效率从高到低
            $sort = $input["zongx_lv"] == 1 ? SORT_ASC : SORT_DESC;
            $list = sys_array_multisort($list, "zongx_lv", $sort);
        }

        // 签单率
        if (!empty($input["qiandan_lv"])) {
            //1.签单率从低到高 2.签单率从高到低
            $sort = $input["qiandan_lv"] == 1 ? SORT_ASC : SORT_DESC;
            $list = sys_array_multisort($list, "qiandan_lv", $sort);
        }

        return [
            "list" => $list,
            "countinfo" => $countinfo
        ];
    }


    /**
     * 获取签单个数
     * @param $cs
     * @param $input
     * @return array|float|string
     */
    public function getQianDanOrderCount($cs, $input)
    {
        $ordersDb = new Orders();
        return $ordersDb->getQianDanOrderCount($cs, $input);
    }

    public function getQianDanOrderList($cs, $input, $offset, $limit)
    {
        $ordersDb = new Orders();
        $list = $ordersDb->getQianDanOrderList($cs, $input, $offset, $limit)->toArray();
        if (count($list)) {
            $orderId = array_column($list, 'id');
            //获取电话记录
            $logic = new TelcenterCallLogic();
            $logs = $logic->getQiandanCall($orderId);
            $hollyLogs = $logic->getQiandanHollyCall($orderId);
            if (count($hollyLogs) > 0) {
                if (count($logs) > 0) {
                    $logs = array_merge($logs, $hollyLogs);
                } else {
                    $logs = $hollyLogs;
                }
            }
            $logs = array_column($logs, null, 'order_id');

            //获取小报备记录
            $reportLogic = new ReportPaymentLogic();
            $report = $reportLogic->getOrderBackPaymentCount($orderId);
            //整合数据
            foreach ($list as $k => $v) {
                $list[$k]['tel_count'] = isset($logs[$v['id']]["count"]) ? $logs[$v['id']]["count"] : 0;
                $list[$k]['is_qf'] = (isset($v['classid']) && $v['classid'] == 6) ? 1 : (isset($v['cooperate_mode']) && $v['cooperate_mode'] == 2) ? 1 : 0;
                $list[$k]['report_count'] = isset($report[$v['id']]) ? $report[$v['id']] : 0;
            }
        }
        return $list;
    }

    public function getNoPassQianDanOrderCount($order_id)
    {
        $chkDb = new LogQiandanchk();
        return $chkDb->getLogCount($order_id);
    }

    public function getNoPassQianDanOrderList($order_id, $offset, $limit)
    {
        $chkDb = new LogQiandanchk();
        $list = $chkDb->getLogList($order_id, $offset, $limit)->toArray();
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $sub = json_decode($v["orign_data"], true);
                if (empty($sub["qiandan_addtime"])) {
                    $sub["qiandan_addtime"] = "-";
                } else {
                    $sub["qiandan_addtime"] = date("Y-m-d", $sub["qiandan_addtime"]);
                }

                if (empty($sub["cname"])) {
                    $sub["cname"] = "-";
                }

                if (empty($sub["qz_area"])) {
                    $sub["qz_area"] = "-";
                }

                $sub["id"] = $v["orderid"];
                $sub["chk_user"] = $v["opname"];
                $list[$k] = $sub;
            }
        }
        return $list;
    }

    public function getQiandanInfo($order_id)
    {
        $orderDb = new Orders();
        $info = $orderDb->getQiandanOrderInfo($order_id, ['companys','qdcompanys']);
        //如果经度纬度存在
        if (!empty(floatval($info["lng"])) && !empty(floatval($info["lat"]))) {
            $info["jingwei"] = $info["lng"] . "," . $info["lat"];
        }
        if (!empty($info["lx"])) {
            $info["lx_name"] = $info["lx"] == 1 ? '家装' : '公装';
        }
        return $info;
    }

    // 排序

    /**
     * @param $list     对象数组
     * @param $field    字段
     * @param int $sort 1:正序， 2：倒序
     * @return mixed
     */
    private function sort_array($list, $field, $sort = 1)
    {
        $n = count($list);
        $k = $n - 1;
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $k; $j++) {
                if ($sort == 1) {
                    if ($list[$j + 1][$field] < $list[$j][$field]) {
                        $temp = $list[$j + 1];
                        $list[$j + 1] = $list[$j];
                        $list[$j] = $temp;
                    }
                } else {
                    if ($list[$j + 1][$field] > $list[$j][$field]) {
                        $temp = $list[$j + 1];
                        $list[$j + 1] = $list[$j];
                        $list[$j] = $temp;
                    }
                }

            }
        }

        return $list;
    }
}