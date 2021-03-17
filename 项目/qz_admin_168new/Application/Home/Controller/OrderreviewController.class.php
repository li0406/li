<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/16
 * Time: 11:35
 */

namespace Home\Controller;

use Common\Enums\ApiConfig;
use Common\Enums\ClinkEnum;
use Common\Enums\RbacRoleEnum;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\CompanyVisitLogicModel;
use Home\Model\Logic\HollyLogicModel;
use Home\Model\Logic\JiajuOrdersLogicModel;
use Home\Model\Logic\LogOrderRouteLogicModel;
use Home\Model\Logic\NewReviewLogicModel;
use Home\Model\Logic\OrderCompanyReviewLogicModel;
use Home\Model\Logic\OrderReviewLogicModel;
use Home\Model\Logic\LogSmsSendLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\OrderSourceInLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\Service\MsgServiceModel;
use Home\Model\Service\PhoneCallClinkServiceModel;
use Home\Model\Service\SmsServiceModel;
use Think\Exception;

class OrderreviewController extends HomeBaseController
{
	protected $orderReviewLogic;
	
	public function __construct()
	{
		parent::__construct();
		$this->orderReviewLogic = new OrderReviewLogicModel();
	}

    /**
     * 回访列表
     * return void
     */
    public function index()
    {
        $params = I('get.');

        // 如果是客服只能看帮打客服
        $cs_help_user = [];
        if ($this->User["uid"] == RbacRoleEnum::ROLE_KF) {
            $cs_help_user = $this->User["cs_help_user"];
            $cs_help_user = array_filter(array_unique(explode(",", $cs_help_user)));
            if (!in_array($this->User["id"], $cs_help_user)) {
                $cs_help_user[] = $this->User["id"];
            }

            // 如果没有选择客服或者选择的客服不在帮打客服里默认帮打客服
            if (empty($params["kf"]) || !in_array($params["kf"], $cs_help_user)) {
                $params["kf"] = $cs_help_user;
            }
        }

        // 查询客服
        $uids = RbacRoleEnum::getReviewRoles();
        $kflist = D("Adminuser")->getKfList(true, false, $cs_help_user, true, $uids);

        // 获取所有城市
        $params['review_type'] = $params['review_type'] ? (int)$params['review_type'] : 0;
        $ret = $this->orderReviewLogic->getReviewOrders($params, $this->User);

        // 获取当前新订单总数/当前已抢未拨打新单
        $reviewCount = $this->getNewReviewCount();

        // 显号审核权限
        $show_apply_tel = false;
        if ($this->User["uid"] != RbacRoleEnum::ROLE_ZZKF_KF) {
            $show_apply_tel = check_user_menu_auth('/orderreview/pass_apply_tel');
        }

        $this->assign([
            'params' => $params,
            'review_types' => $this->orderReviewLogic->reviewType,
            'review_remarks' => $this->orderReviewLogic->reviewRemark,
            'states' => $this->orderReviewLogic->state,
            'ret' => $ret,
            'cities' => $this->orderReviewLogic->getCities(),
            'opuser' => session('uc_userinfo'),
            'show_apply_tel' => $show_apply_tel,
            'kflist' => $kflist,
            'order_count' => $reviewCount,
        ]);
        
        $this->display();
    }

	public function editReview()
	{
		if (IS_POST) {
			try {
				$params = I('post.');

				if (empty($params)) {
					throw new Exception('没有数据', 404);
				}
				if (empty($params['id'])
					|| !array_key_exists($params['review_type'], $this->orderReviewLogic->reviewType)
					|| (!array_key_exists($params['review_remark'], $this->orderReviewLogic->reviewRemark) &&
						$params['review_type'] != 3)
					|| empty($params['review_record'])
				) {
					throw new Exception('数据有误', 404);
				}
				
				if ($params['review_type'] == 3 && empty($params['wait_order_time'])) {
					throw new Exception('请填写待定单时间', 404);
				}

				if (!empty($params['next_visit_step']) && empty($params['next_visit_time'])) {
                    throw new Exception('请选择下次回访时间', 404);
				}

                if (empty($params['next_visit_step']) && !empty($params['next_visit_time'])) {
                    throw new Exception('请选择回访阶段', 404);
                }

				if ($params['review_type'] == 99) {
				    unset($params['review_type']);
				}

				$ret = $this->orderReviewLogic->editReview($params);
				if (empty($ret)) {
					throw new Exception('编辑失败', 401);
				}

                //如果是次新单/待定单/无效单/有效单 添加回访记录
                if (in_array($params['review_type'],array(2, 3, 4, 5))) {
                    //查询订单是否有未回访的订单记录
                    $companyVisitLogic = new CompanyVisitLogicModel();
                    $visitInfo = $companyVisitLogic->getOrderNoReturnVisitInfo($params['id']);

                    $status = 1;
                    if ($params['review_type'] == 4) {
                        $status = 3; // 无效单
                    } else if($params['review_type'] == 5) {
                        $status = 2; // 有效单
                    } else if($params['review_type'] == 2) {
                        $status = 4; // 次新单
                    } else if($params['review_type'] == 3) {
                        $status = 5; // 待定单
                    }

                    if (count($visitInfo) > 0) {
                        // 编辑回访记录
                        $data = [
                            "visit_content" => $params['feedback'],
                            "review_remark" => $params['review_remark'],
                            "visit_time" => time(),
                            "visit_user" => session("uc_userinfo.id"),
                            "visit_username" => session("uc_userinfo.name"),
                            "visit_status" => $status,
                            "updated_at" => time(),
                            "read_mark" => 1,
                        ];
                        $companyVisitLogic->editVisitInfo($visitInfo["id"],$data);
                    } else {
                        // 查询是否有已回访但未推送的的记录
                        $lastInfo = $companyVisitLogic->getOrderLastVisitInfo($params['id']);

                        if (count($lastInfo) > 0 && $lastInfo["visit_push"] == 1) {
                            // 未推送的状态下，可以编辑回访反馈内容
                            $data = [
                                "visit_content" => $params['feedback'],
                                "updated_at" => time(),
                                "read_mark" => 1
                            ];
                            $companyVisitLogic->editVisitInfo($lastInfo['id'],$data);
                        } else {
                            // 查询当前订单的量房状态
                            $companyLogic = new OrderCompanyReviewLogicModel();
                            $lfStatus = $companyLogic->getOrderLiangfangStatus($params['id']);

                            // 添加回访记录
                            $data = [
                                "order_id" => $params['id'],
                                "visit_type" => 2,
                                "visit_time" => time(),
                                "visit_user" => session("uc_userinfo.id"),
                                "visit_username" => session("uc_userinfo.name"),
                                "visit_content" => $params['feedback'],
                                "visit_status" => $status,
                                "creator" => session("uc_userinfo.id"),
                                "created_at" => time(),
                                "updated_at" => time(),
                                "order_lf_status" => $lfStatus,
                                "visit_step" => $params['visit_step'],
                                "review_remark" => $params['review_remark'],
                                "read_mark" => 1
                            ];

                            $companyVisitLogic->addVisitInfo($data);
                        }
                    }

                    // 推送已回访消息给订单所在城市销售
                    $ordersLogicModel = new OrdersLogicModel();
                    $orderDetail = $ordersLogicModel->getInfoByOrderId($params["id"], "id,cs");
                    if (!empty($orderDetail)) {
                        $adminuserLogic = new AdminuserLogicModel();
                        $salerList = $adminuserLogic->getSalersByCity($orderDetail["cs"]);
                        $salerIds = array_column($salerList, "id");
                        if (count($salerIds) > 0) {
                            $linkparam = "?order_id=" . $params["id"];
                            $msgServiceModel = new MsgServiceModel();
                            $msgServiceModel->sendSystemMsg("201912181012", $salerIds, $linkparam, $params["id"], "客服订单回访");
                        }
                    }
                }


                // 条件1：回访单审核为次新单-未接通、开场挂、不配合回访
                // 条件2：无效单-拒接/未接通、拒绝服务、不配合回访
                $condition1 = $params['review_type'] == 2 && in_array($params['review_remark'], [1, 2, 3]);
                $condition2 = $params['review_type'] == 4 && in_array($params['review_remark'], [6, 7, 11]);
                if ($condition1 || $condition2) {
                    // 查询业主电话
                    $ordersLogic = new OrdersLogicModel();
                    $telInfo = $ordersLogic->getOrderRealTel($params['id']);
                    if (!empty($telInfo)) {
                        $tel = $telInfo["tel8"];

                        // 发送短信频率：1天最多1次，次日0点清零重计
                        $today = date("Y-m-d");
                        $logSmsSendLogic = new LogSmsSendLogicModel();
                        $count = $logSmsSendLogic->getOrderTypeDayCount($params['id'], LogSmsSendLogicModel::LOG_TYPE_HFWJT, $today);
                        if ($count == 0) {
                            $smsServiceModel = new SmsServiceModel();
                            $ret = $smsServiceModel->sendSms("202007071042", $tel);
                            if (isset($ret["error_code"]) && $ret["error_code"] == 0) {
                                import('Library.Org.Util.App');
                                $app = new \App();

                                $tel_encrypt = substr_replace($tel, "*****", 3, 5); //做一个中间为星号的号码
                                $tel_md5 = $app->order_tel_encrypt($tel); //做一个加密后的号码

                                // 短信发送成功增加日志
                                $logSmsSendLogic->addLog([
                                    "type" => LogSmsSendLogicModel::LOG_TYPE_HFWJT,
                                    "op_id" => $this->User["id"],
                                    "op_user" => $this->User["name"],
                                    "orderid" => $params['id'],
                                    "addtime" => date("Y-m-d H:i:s"),
                                    "ip" => $app->get_client_ip(),
                                    "tel_encrypt" => $tel_md5,
                                    "tel" => $tel_encrypt,
                                ]);
                            }
                        }
                    }
                }

                $code = 0;
				$msg = '编辑成功!';
			} catch (Exception $e) {
				$code = $e->getCode();
				$msg = $e->getMessage();
			}
			$this->ajaxReturn(['error_code' => $code, 'error_msg' => $msg]);
		}
	}
	
    /**
     * 搜索订单
     * 可按照订单号单独查询
     * 按照装修公司、会员类型、订单状态查询时，必须选择时间段
     * @return [type] [description]
     */
	public function search()
	{
        $page = I("get.p", 1);
        $limit = I("get.limit", 20);

        $input = array(
            "ordertel" => I("get.ordertel"),
            "company" => I("get.company"),
            "classid" => I("get.classid"),
            "type_fw" => I("get.type_fw"),
            "begin" => I("get.begin"),
            "end" => I("get.end")
        );

        $searchret = 0;
        $data = ["list" => [], "count" => 0, "pageshow" => ""];
		if (!empty($input["ordertel"]) || ($input["begin"] && $input["end"])) {
            if ($input["ordertel"] || $input["company"] || $input["classid"] || $input["type_fw"]) {
                $data = $this->orderReviewLogic->getOrderSearchList($input, $page, $limit);
                if (!empty($data["list"])) {
                    $searchret = 1;
                }

                import('Library.Org.Util.Page');
                $page = new \Page($data["count"], $limit);
                $data["pageshow"] = $page->show();
            }
        }

        $this->assign('data', $data);
        $this->assign('input', $input);
        $this->assign("searchret", $searchret);
		$this->display();
	}

    // 导入回访单
    public function importall(){
        $input = array(
            "ordertel" => I("post.ordertel"),
            "company" => I("post.company"),
            "classid" => I("post.classid"),
            "type_fw" => I("post.type_fw"),
            "begin" => I("post.begin"),
            "end" => I("post.end")
        );

        if (!empty($input["ordertel"]) || ($input["begin"] && $input["end"])) {
            if ($input["ordertel"] || $input["company"] || $input["classid"] || $input["type_fw"]) {
                $list = $this->orderReviewLogic->getOrderSearchImportList($input);
                if (count($list)) {
                    $orderIds = array_column($list, "id");
                    $result = $this->orderReviewLogic->orderReviewImportByOrderIds($orderIds);
                }

                $this->ajaxReturn(['error_code' => 0, 'error_msg' => '导入成功']);
            }
        }

        $this->ajaxReturn(['error_code' => 1, 'error_msg' => '缺少必填参数']);
    }
	
	// 回访城市设置列表
	public function setcity() {
		$input = I("get.");

        // 获取城市分页列表
        $data = $this->orderReviewLogic->getReviewCityList($input);

        // 获取所有已开站城市
        $cityList = $this->orderReviewLogic->getAllOpenCity();

        $this->assign("data", $data);
        $this->assign("input", $input);
        $this->assign("cityList", $cityList);
		$this->display();
	}

    // 回访城市设置详情
    public function setcity_info() {
        $city_id = I("get.city_id");

        // 获取城市设置信息列表
        $detail = $this->orderReviewLogic->getReviewCityInfo($city_id);

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "detail" => $detail
            ]
        ]);
    }


    // 回访城市设置日志
    public function setcity_log(){
        if (IS_AJAX) {
            $city_id = I("get.city_id");
            $logList = $this->orderReviewLogic->getReviewCityLog($city_id);

            $this->assign("logList", $logList);
            $template = $this->fetch("setcity_log");

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "请求成功",
                "data" => [
                    "template" => $template
                ]
            ]);
        }
    }

    // 设置回访城市
    public function setcity_submit(){
        if (IS_AJAX) {
            $city_id = I("post.city_id");
            $visit_begin = I("post.visit_begin");
            $mianji_min = I("post.mianji_min", 0);
            $mianji_max = I("post.mianji_max", 0);
            $userinfo = session("uc_userinfo");

            if (empty($city_id)) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "请选择回访城市"
                ]);
            } else if (empty($visit_begin)) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "请选择回访时间"
                ]);
            }

            $mindatetime = strtotime(date("Y-m-d", strtotime("-7 days")));
            $maxdatetime = strtotime(date("Y-m-d"));

            $visit_begin_time = strtotime($visit_begin);
            if ($visit_begin_time > $maxdatetime || $visit_begin_time < $mindatetime) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "只能选择当前日期一周内的日期"
                ]);
            }

            $ret = $this->orderReviewLogic->setReviewCity($city_id, $visit_begin, $mianji_min, $mianji_max, $userinfo);
            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }

    // 取消回访城市
    public function setcity_cancel(){
        if (IS_AJAX) {
            $city_id = I("post.city_id");
            $userinfo = session("uc_userinfo");

            $ret = $this->orderReviewLogic->cancelReviewCity($city_id, $userinfo);
            if (empty($ret)) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "取消回访城市失败"
                ]);
            }

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "请求成功"
            ]);
        }
    }

    //回访设置
    public function setcityold()
    {
        if ($_POST) {
            try {
                $reviewCities = I('post.city');
                $this->orderReviewLogic->saveReviewCities($reviewCities);
                $code = 0;
                $msg = '保存成功!';
            } catch (Exception $e) {
                $code = $e->getCode();
                $msg = $e->getMessage();
            }
            $data = [
                'code' => $code,
                'msg' => $msg,
            ];
            $this->ajaxReturn($data);
        } else {
            $cities = $this->orderReviewLogic->getCities();
            $reviewFieldCities = $this->orderReviewLogic->getReviewFieldCities();
            $this->assign('cities', $cities);
            $this->assign('reviewFieldCities', $reviewFieldCities);
            $this->display();
        }
    }
	   
    // 回访城市设置导出
    public function setcityExport(){
        $input = I("get.");
        $list = $this->orderReviewLogic->getReviewCityExportList($input);

        try {
            // 标题
            $header = array(
                "城市",
                "是否回访城市",
                "回访开始时间",
                "订单面积",
                "操作人",
                "操作时间"
            );

            import("Library.Org.PHP_XLSXWriter.xlsxwriter");
            $writer = new \XLSXWriter();
            $writer->writeSheetRow("Sheet1", $header);

            foreach ($list as $key => $item) {
                $row = [
                    $item["cname"],
                    $item["state_name"],
                    $item["visit_begin_date"],
                    $item["state"] == 1 ? sprintf("%s 至 %s", $item["mianji_min"], $item["mianji_max_show"]) : "",
                    $item["operator_name"],
                    $item["updated_date"]
                ];

                $writer->writeSheetRow("Sheet1", $row);
            }

            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="回访城市设置.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        } catch (Exception $e) {

        }

        exit();
    }



	//显示订单分配的公司
	public function showordercompany()
	{
		if (empty(I('get.orderno'))) {
			$this->ajaxReturn(['error_code' => 1, 'error_msg' => '缺少必填参数']);
		}
		$orderno = I('get.orderno');
		//检查是否是分单且已分配装修公司
		if (!$this->orderReviewLogic->getExistFenOrderInfo($orderno)) {
			$this->ajaxReturn(['error_code' => 1, 'error_msg' => '该订单还未分单，请分单后再次尝试']);
		} else {
			//获取分配公司
			$data = $this->orderReviewLogic->getOrderCompany($orderno);
			$this->ajaxReturn(['error_code' => 0, 'data' => $data]);
		}
	}

    /**
     * 申请显号
     * return void
     */
    public function tel_openeye()
    {
        if (IS_POST) {
            $post = I('post.');
            if (empty($post['text'])) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请填写申请理由']);
            }
            if (mb_strlen($post['text']) > 50) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '申请理由50字符以内']);
            }
            $result = $this->orderReviewLogic->addOrderEyeInfo($post);
            if (is_string($result) && !ctype_digit($result)) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => $result]);
            } else {
                if ($result !== false) {
                    $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '申请成功']);
                } else {
                    $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_SAVE_ERROR, 'error_msg' => '申请失败']);
                }
            }
        }
        $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请求错误']);
    }

    /**
     * 获取显号记录列表
     * return void
     */
    public function get_apply_tel_list()
    {
        if (IS_POST) {
            $id = I('post.id', '', 'trim');
            if (empty($id)) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '缺少参数！']);
            }
            $result = $this->orderReviewLogic->getApplyTelListByOrdersId($id);
            if ($result !== false) {
                $this->assign('list',$result);
                $html = $this->fetch("orders_apply_tel_modal");
                $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '获取成功', 'data' => $html]);
            } else {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_SAVE_ERROR, 'error_msg' => '获取失败', 'data' => '']);
            }
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请求错误']);
        }
    }

    /**
     * 显号审核操作
     * return void
     */
    public function pass_apply_tel()
    {
        if (IS_POST) {
            //判断是否有查看呼叫记录的权限
            if (check_user_menu_auth('/orderreview/pass_apply_tel') === false) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '您无权审核！']);
            }
            $id = I('post.id', '', 'trim');
            //检查参数
            if (empty($id)) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '缺少参数！']);
            }
            $save['status'] = I('post.status', 1, 'intval');
            $save['passer_id'] = session('uc_userinfo.id');
            $save['pass_time'] = time();
            $result = $this->orderReviewLogic->saveOrdersApplyTel($id, $save);
            if ($result) {
                $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_SAVE_ERROR, 'error_msg' => '操作失败']);
            }
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请求错误']);
        }
    }

	/**
	 * 订单详细页面
	 * @return void
	 */
	public function operate()
	{
		//第一次通话结束--第三次通话开始的时间差
		//控制器
		$ordersController = new OrdersController();
		if ($_POST) {
			$this->assign('is_show', I('post.is_show'));
			$results = $this->getVoipRecordList(I("post.id"));
			foreach ($results as $result) {
				if ($result['action'] == "开始") {
					$kaishi[] = $result;
				} elseif ($result['action'] == "挂断") {
					$jieshu[] = $result;
				}
			}
			$countkaishi = count($kaishi);
			
			//新逻辑:拨号满两次且（当前时间-第一次结束通话）≥30分钟，才能正常申请显号，否则通过紧急入口
			if ($countkaishi >= 2) {
				$endtime = $jieshu[0]['time_add'];
				$time = time() - strtotime($endtime);
				$timemin = $time / 60;
				if ($timemin >= 30) {
					$jinji = 0;
				} else {
					$jinji = 1;
				}
			} else {
				$jinji = 1;
			}
			//查询订单信息
			$info = $this->getOrderInfo(I("post.id"));
			//过滤不规则字符串
			$reg = '/[\`~!@#$%^&*\(\)+<>?"{},\/;\'\"\s]/';
			$info["xiaoqu"] = preg_replace($reg, "", $info["xiaoqu"]);
			
			//查询是否是活动订单
			if (!empty($info['source'])) {
				$info["activity"] = D("Home/Logic/ActivityLogic")->getActivityInfo($info["source"]);
			}
			$this->assign("info", $info);
			$this->assign("source_in", $ordersController->source_in);
			//后台转发人数组
			$ids = D("Options")->getOptionNameCC("kf_admin_order_users");
			$names = D("User")->getAdminNamesById($ids['option_value']);
			foreach ($names as $k => $v) {
				$zhuanfaren[] = $v['name'];
			}
			$this->assign("zhuanfaren", $zhuanfaren);
			//获取订单城市和区县
			$city = D("Quyu")->getCityInfoById($info["cs"]);
			$this->assign("city", $city);
			//户型
			$huxing = D("Huxing")->gethx();
			//预算
			$yusuan = D("Jiage")->getJiage();
			//装修方式
			$fangshi = D("Fangshi")->getfs();
			//风格
			$fengge = D("Fengge")->getfg();
			//获取最后审核人信息
			$csos_new = D("OrderCsosNew")->getCsosInfo(I("post.id"));
			//获取 未接通电话短信通知 短信记录
			$logCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 2);
			
            // 未接通短信
            $logSmsSendLogic = new LogSmsSendLogicModel();
            $smsCount = $logSmsSendLogic->getOrderTypeDayCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_HFWJT, date("Y-m-d"));
			
			//获取订单分配信息
			$company = D("OrderInfo")->getOrderComapny($info["id"]);
			
			//有分配订单的情况下，查询微信是否发送
			if (count($company) > 0) {
				//获取订单微信发送记录
				$wx = D("LogWxOrdersend")->getWeixinLog($info["id"]);
				if (count($wx) > 0) {
					$this->assign("wxMark", 1);
				}
			}
			
			//获取城市信息模板
			$cityTmp = $this->getCityInfoTmp($info["cs"]);
			
			//获取城市信息
			$quyu = D('Quyu')->getQuyuList();
			$this->assign('quyu', $quyu);
			
			//获取该订单所在城市的的会员公司
			$companyList = $this->getCompanyList($info["cs"], $info["lng"], $info["lat"]);
			$this->assign('companyList', $companyList);
			$nowCompanys = $this->fetch("nowCompanys");
			$otherCompanys = $this->fetch("otherCompanys");
			
			//查询小区历史签单公司
			$history = $this->orderHistory($info["xiaoqu"], $info['cs']);
			if (count($history) > 0) {
				$this->assign("history", $history);
			}
			
			//获取最近30天过期的会员信息
			$lostCompany = $this->getLastExpireCompanyList($info["cs"], date("Y-m-d"));
			
			//获取订单IP是否重复
			$ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));
			
			if ($ipCount[0]["repeat_count"] > 1) {
				$this->assign("ipMark", $ipCount[0]["repeat_count"] - 1);
			}

			//获取回访订单数据
			$reviewOrder = $this->orderReviewLogic->getDetail(I("post.id"));
			$reviewTypes = array_slice($this->orderReviewLogic->reviewType, 1, null, true);
			$this->assign('review_types', $reviewTypes);
			$this->assign('review_type_name', $this->orderReviewLogic->reviewType[$reviewOrder['review_type']]);
			$review_remark = ($reviewOrder['review_type']==1)?'':$this->orderReviewLogic->reviewRemark;
			$this->assign('review_remarks', $review_remark);
			$this->assign("review_order", $reviewOrder);
			unset($review_remark);
			//右侧显示
			$orderno = I("post.id");
			// 获取分配公司信息
			$reviewLogic = new OrderReviewLogicModel();
			$company_fp = $reviewLogic->getOrderCompany($orderno);
			//要求回访公司会员
			$reviewCompany = $reviewLogic->getReviewCompany($orderno);
			//反馈详情
			$feedback = $reviewLogic->getReviewInfoByOrderId($orderno, 2);
			//未量房原因
			$noLfReason = $reviewLogic->getNoLfReason($orderno);
			//根据订单号获取当前城市的会员数量
			$vipCount = $reviewLogic->getVipUserCount($orderno);

            //获取回访需求
            $logic = new CompanyVisitLogicModel();
            $companyReviewInfo = $logic->getOrderPassiveVisitInfo($orderno);//装修公司回访需求

            //获取装修公司跟进情况
            $trackList = $logic->getComapnyTrackList($orderno);

            //获取已量房公司信息
            $lfCompany = $reviewLogic->getOrderLiangfangInfo($orderno);

            //获取订单标识
            $inLogic = new OrderSourceInLogicModel();
            $orderType = $inLogic->getSourceInSelect();

            //获取订单公司相关服务
            $logic = new OrderCompanyReviewLogicModel();
            $result = $logic->getReviewInfoByOrderId($orderno,"a.*,u.jc");
            $reviewList = [];
            if (count($result) > 0) {
                foreach ($result as $item) {
                    $reviewList["header"][] =  $item["jc"];
                    $item["fixup_special"] = empty($item["fixup_special"])?"暂无":$item["fixup_special"];
                    $item["company_discount"] = empty($item["company_discount"])?"暂无":$item["company_discount"];
                    $item["yz_worry"] = empty($item["yz_worry"])?"暂无":$item["yz_worry"];
                    $reviewList["body"][] = "<b>装修特色</b><span style='color: #c4c4c4;'>（主要说明主做楼盘、售后服务保障等）</span><br/>".$item["fixup_special"] ."<br/><b>装企优惠</b><span style='color: #c4c4c4;'>（主要说明可使用的活动以及价格说明）</span><br/>". $item["company_discount"] ."<br/><b>业主顾虑点</b><span style='color: #c4c4c4;'>（简述该业主装修的顾虑点以及其他特殊说明）</span><br/>" . $item["yz_worry"];
                }
            }
            $this->assign("reviewList",$reviewList);
            $this->assign('orderType',$orderType);

            $this->assign("lfCompany", $lfCompany);
            $this->assign("trackList", $trackList);
            $this->assign("companyReviewInfo", $companyReviewInfo);
			$this->assign("vipCount", $vipCount);
			$this->assign("company_fp", $company_fp);
			$this->assign("reviewCompany", $reviewCompany);
			$this->assign("feedback", $feedback['feedback']);
			$this->assign("noLfReason", $noLfReason);
			
			$this->assign("jinji", $jinji);
			$this->assign("timemin", $timemin);
			$this->assign("lostCompany", $lostCompany);
			$this->assign("company", $company);
			$this->assign("smsCount", $smsCount);
			$this->assign("nowCompanys", $nowCompanys);
			$this->assign("otherCompanys", $otherCompanys);
			$this->assign("logCount", $logCount);
			$this->assign("csos_new", $csos_new);
			$this->assign("status", $ordersController->status);
			$this->assign("keys", $ordersController->keys);
			$this->assign("lf_time", $ordersController->lf_time);
			$this->assign("start_time", $ordersController->start_time);
			$this->assign("shi", $ordersController->shi);
			$this->assign("lxs", $ordersController->lxs);
			$this->assign("fengge", $fengge);
			$this->assign("fangshi", $fangshi);
			$this->assign("yusuan", $yusuan);
			$this->assign("huxing", $huxing);
			$this->assign("cityTmp", $cityTmp);
			
			$tmp = $this->fetch("operate");
			$this->ajaxReturn(array('data' => $tmp, 'info' => $info, 'status' => 1));
		}
	}

	public function getVoipRecordList($orderid)
	{
		$db = $this->orderReviewLogic->logTelReviewOrderCallModel;
		//兼容回访电话记录(老)
		$result = $db->getOrderCallListByOrderId($orderid);
        $result = D("LogTelcenterOrdercall")->callistHuman($result, 1); //数据再加工
		foreach ($result as $key => &$value) {
			$value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
			//呼叫动作
			switch ($value['action']) {
				case 'CallAuth':
					$value['action'] = "开始";
					break;
				case 'CallEstablish':
					$value['action'] = "接通";
					break;
				case 'Hangup':
					$value['action'] = "挂断";
					break;
				case 'CallEstablish_Sub':
					$value['action'] = "主叫接通";
					break;
				case 'Hangup_Sub':
					$value['action'] = "主叫挂断";
					break;
				default:
					# code...
					break;
			}
			//拨打方式
			switch ($value['calltype']) {
				case 'callBack':
					$value['calltype'] = "回拨拨打";
					break;
				default:
					break;
			}
			//挂断方式
			if ($value['TelCenter_Channel'] == 'cuct') {
				//对联通云总机的挂机代码处理
				//state：状态：0-呼叫挂机（默认值）；1-通话失败。
				switch ($value['byetype']) {
					case '0':
						$value['byetypewz'] = '呼叫挂机';
						break;
					case '1':
						$value['byetypewz'] = '通话失败';
						break;
					default:
						$value['byetypewz'] = '-';
						break;
				}
			} else {
				// 默认是 云通讯的挂机代码
				$value['byetypewz'] = $db->getByeTypeInfo($value['byetype']);
			}
		}

        //查询合力的审核客服电话记录(新)
        $model = new HollyLogicModel();
        $kf_list = $model->getOrderTelLogById($orderid);
        $kf_list = D("LogTelcenterOrdercall")->callistHuman($kf_list, 1); //数据再加工

        //查询合力的电话记录(新)
        $model = new HollyLogicModel();
        $list = $model->getOrderReviewTelLogById($orderid);
        $list = D("LogTelcenterOrdercall")->callistHuman($list, 1); //数据再加工

        if (count($kf_list) > 0) {
            $result = array_merge($result, $kf_list);
        }
        if (count($list) > 0) {
            $result = array_merge($result, $list);
        }

        //查询天润通话记录
        $model = new PhoneCallClinkServiceModel();
        $list = $model->getReviewOrderTelLogById($orderid);

        if (count($list) > 0) {
            foreach ($list as $item) {
                $result[] = [
                    "time_add" => date("Y-m-d H:i:s", $item["created_at"]),
                    "byetype" => ClinkEnum::getStatus($item["cdr_status"]),
                    "call_time_length" => $item["cdr_bridge_time"],
                    "callsid" => $item["call_sid"],
                    "caller" => $item["cust_callee_clid"],
                    "called" => $item["cdr_customer_number"],
                    "admin_user" => $item["op_uname"],
                    "action" => $item["cdr_call_type"] == 1 ? "呼入" : "外呼",
                    "TelCenter_Channel" => "clink"
                ];
            }
        }

        //按时间重新排序
        foreach ($result as $key => $item) {
            $edition[] = $item['time_add'];
        }

        array_multisort($edition, SORT_DESC, $result);

		return $result;
	}

	/**
	 * 获取订单信息
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getOrderInfo($id, $is_jiaju = false)
	{
		//查询订单信息
		if ($is_jiaju) {
			//如果是家具订单 , 则查询家具数据
			$jiajuLogic = new JiajuOrdersLogicModel();
			$info = $jiajuLogic->getJiajuOrdersInfo($id);
			if (count($info) > 0) {
				$info = $info[0];
				$info['time_real'] = date("Y-m-d H:i:s", $info['time_real']);
				$info['time'] = date("Y-m-d H:i:s", $info['time']);
				$info['lasttime'] = date("Y-m-d H:i:s", $info['lasttime']);
			}
		} else {
			$order = D('Home/Orders');
			$info = $order->findOrderInfo($id);
		}
		//如果经度纬度存在
		if (!empty(floatval($info["lng"])) && !empty(floatval($info["lat"]))) {
			$info["jingwei"] = $info["lng"] . "," . $info["lat"];
		}

		if (count($info) == 0) {
			$this->ajaxReturn(array("code" => 404, 'info' => '该订单不存在'));
		}

		if ($info["nf_time"] == "0000-00-00") {
			$info["nf_time"] = "";
		}

		//计算订单状态
		if ($is_jiaju) {
			//家具转装修 , 订单状态就是新单
			$info["on"] = 0;
		} else {
			if ($info['on'] == 0 && $info['on_sub'] == 9) {
				$info["orderstatus"] = 1;
			} elseif ($info['on'] == 2) {
				$info["orderstatus"] = 2;
			} elseif ($info['on'] == 4 && $info['type_fw'] == 0) {
				$info["orderstatus"] = 3;
			} elseif ($info['on'] == 4 && $info['type_fw'] == 1) {
				$info["orderstatus"] = 4;
			} elseif ($info['on'] == 4 && $info['type_fw'] == 2) {
				$info["orderstatus"] = 6;
			} elseif ($info['on'] == 4 && $info['type_fw'] == 3) {
				$info["orderstatus"] = 5;
			} elseif ($info['on'] == 4 && $info['type_fw'] == 4) {
				$info["orderstatus"] = 7;
			} elseif ($info['on'] == 5) {
				$info["orderstatus"] = 8;
			} elseif ($info['on'] == 98) {
				$info["orderstatus"] = 98;
			}
		}

		if ($info["lng"] > 0 && $info["lat"] > 0) {
			$info["coordinate"] = $info["lng"] . "," . $info["lat"];
		}

		$exp = array_filter(explode("；", $info["text"]));
		$info["text_array"] = $exp;

		//获取审核显号
		$admin = getAdminUser();
		$info['apply_tel'] = D('OrdersApplyTel')->getApplyTelByOrdersIdAndApplyId($info['id'], $admin['id']);

		if ($info["apply_tel"]['status'] == 2) {
			$info["tel"] = $info["tel8"];
		}

		$info["lasttime"] = empty($info["lasttime"]) ? "" : $info["lasttime"];

		//获取家居订单
		$logLogic = new LogOrderRouteLogicModel();
		$route = $logLogic->getOrderLog('', $id);
		$info["jiaju_order_id"] = '';
		if ($route) {
			$info["jiaju_order_id"] = $route['jiaju_order_id'];
		}
		$info['cname_ip'] = $info['cname'];
		//查询ip城市 , 如果没有 , 就用订单城市
		if ($info['ip']) {
			$city = get_city_by_ip($info['ip']);
			if ($city[2]) {
				$info['cname_ip'] = $city[2];
			}
		}
        $info['apply_tel'] =$this->orderReviewLogic->getUserApplyByOrderid($info['id'],$admin['id']);
		if (!empty($info['apply_tel']) && $info['apply_tel']['status'] == 2) {
            $info['tel'] = $info['tel8'];
        }
		return $info;
	}

	/**
	 * 获取订单城市信息页模版
	 * @param  [type] $cs [description]
	 * @return [type]     [description]
	 */
    public function getCityInfoTmp($cs, $flag = false)
	{
		//获取订单的城市信息
		$info = D("OrderCityInfo")->findCityInfo($cs);
		if (count($info) > 0) {
			if ($flag) {
				$this->assign("isDocking", 1);
			}
			$this->assign("cityInfo", $info);
		}
		return $this->fetch("cityTmp");
	}

	/**
	 * 获取当前城市装修公司和相邻的装修公司
	 * @param  [type] $cs [description]
	 * @return [type]     [description]
	 */
    public function getCompanyList($cs, $lng, $lat)
	{
		//获取相邻城市
		$result = D("OrderCityRelation")->getRelationCity($cs);

		//获取订单城市会员列表
		$list = $this->getCompanyDetailsList(array($cs), 2);
		$other = $now = [];

		foreach ($list as $key => $value) {
			if (!array_key_exists($value["qx"], $now)) {
				$now[$value["qx"]]['area_id'] = $value["qx"];
				$now[$value["qx"]]['qz_area'] = $value["qz_area"];
			}

			if ((int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0 && (int)$value["lng"] != 0) {
				$value["distance"] = get_distance(array($lng, $lat), array($value["lat"], $value["lng"]), true, 0);
			} else {
				$value["distance"] = "无";
			}

			$now[$value["qx"]]["child"][] = $value;
		}

		if (count($result) > 0) {
			foreach ($result as $key => $value) {
				if ($cs != $value["cid"]) {
					$adjacentCity[] = $value["cid"];
				}
			}

			if (count($adjacentCity) > 0) {
				//相邻城市会员
				$result = $this->getCompanyDetailsList($adjacentCity, 2);

				foreach ($result as $key => $value) {
					if (!array_key_exists($value["cs"], $other)) {
						$other[$value["cs"]]["cid"] = $value["cs"];
						$other[$value["cs"]]["cname"] = $value["cname"];
					}

					if ((int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0 && (int)$value["lng"] != 0) {
						$value["distance"] = get_distance(array($lng, $lat), array($value["lat"], $value["lng"]), true, 0);
					} else {
						$value["distance"] = "无";
					}

					$other[$value["cs"]]["child"][] = $value;
				}
			}
		}

		return array($now, $other);
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

    public function getLastExpireCompanyList($cs, $date)
	{
		$lostCompany = D("User")->getLastExpireCompanyList($cs, $date);
		foreach ($lostCompany as $key => $value) {
			$offset = (strtotime($date) - strtotime($value["end"])) / 86400 + 1;
			$lostCompany[$key]["day"] = $offset;
		}
		return $lostCompany;
	}

	/**
	 * 获取装修公司详细信息
	 * @param  [type] $companys [description]
	 * @param  [type] $on       [description]
	 * @return [type]           [description]
	 */
    public function getCompanyDetailsList($cs, $on, $companys = [], $getGiftOrder = '')
	{
		$companys = D("User")->getCompanyDetailsList($cs, $on, $companys, $getGiftOrder);

		foreach ($companys as $key => $value) {
			//计算到期时间
			$offset = (strtotime($value["end"]) - strtotime(date("Y-m-d"))) / 86400 + 1;

			if ($offset <= 15 && empty($value["start_time"])) {
				$companys[$key]["end_time"] = $offset;
			}

			//合同开始时间如果大于本月1号显示新
			if ($value["start"] >= date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")))) {
				$companys[$key]["newMark"] = 1;
			}
			$ids[] = $value["id"];
		}

		//获取装修公司暂停信息
		if (count($ids) > 0) {
			$result = D("UserVip")->getParseContractList($ids);
			foreach ($result as $key => $value) {
				//计算到期时间
				$offset = (strtotime(date("Y-m-d")) - strtotime($value["end_time"])) / 86400 + 1;
				if ($offset <= 15 && empty($value["start_time"])) {
					$parseList[$value["company_id"]]["parseMark"] = 1;
				}
			}

			foreach ($companys as $key => $value) {
				if (array_key_exists($value["id"], $parseList)) {
					$companys[$key]["parseMark"] = $parseList[$value["id"]];
				}
			}
		}
		return $companys;
	}

	/**
	 * 获取VOIP电话回访列表
	 * @param  [type] $orderid 订单id
	 * @return [type]       [description]
	 */
    public function getVoipBackRecordList($orderid)
	{
		$db = $this->orderReviewLogic->logTelReviewOrderCallModel;
		$result = $db->getBackOrderCallListByOrderId($orderid);
		$result = $db->callistHuman($result, 1); //数据再加工
		foreach ($result as $key => &$value) {
			$value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
			//呼叫动作
			switch ($value['action']) {
				case 'CallAuth':
					$value['action'] = "开始";
					break;
				case 'CallEstablish':
					$value['action'] = "接通";
					break;
				case 'Hangup':
					$value['action'] = "挂断";
					break;
				case 'CallEstablish_Sub':
					$value['action'] = "主叫接通";
					break;
				case 'Hangup_Sub':
					$value['action'] = "主叫挂断";
					break;
				default:
					# code...
					break;
			}
			//拨打方式
			switch ($value['calltype']) {
				case 'callBack':
					$value['calltype'] = "回拨拨打";
					break;
				default:
					break;
			}
			//挂断方式
			if ($value['TelCenter_Channel'] == 'cuct') {
				//对联通云总机的挂机代码处理
				//state：状态：0-呼叫挂机（默认值）；1-通话失败。
				switch ($value['byetype']) {
					case '0':
						$value['byetypewz'] = '呼叫挂机';
						break;
					case '1':
						$value['byetypewz'] = '通话失败';
						break;
					default:
						$value['byetypewz'] = '-';
						break;
				}
			} else {
				// 默认是 云通讯的挂机代码
				$value['byetypewz'] = $db->getByeTypeInfo($value['byetype']);
			}
		}

		return $result;
	}

	public function voiprecord()
	{
		//判断是否是专门查询回访电话 , 不传callback 则取订单所有童话记录
		$orderId = I('get.id');
		$result = [];
		$orderVoipRecord = [];
		if (I("get.callback") == 1) {
			$result = $this->getVoipBackRecordList($orderId);
		} else {
			$result = $this->getVoipRecordList($orderId);
			$ordersController = new OrdersController();

            // 兼容审核客服电话记录(老)
			$orderVoipRecord = $ordersController->getVoipRecordList($orderId);
		}

		$result = array_merge($result, $orderVoipRecord);
		$this->assign("list", $result);
		if (IS_AJAX) {
			if (I("get.type") == "qc") {
				$tmp = $this->fetch("Order/qc_tel_history");
			} else {
				$tmp = $this->fetch("Order/tel_history");
			}

			$this->ajaxReturn(array("status" => 1, "data" => $tmp));
		} else {
			$this->display();
		}
	}
	
	public function getOrderRemark()
	{
		try {
			$type = I('get.review_type');
			$ret = $this->orderReviewLogic->getOrderRemark($type);
			$code = 0;
			$msg = 'ok';
			$data = $ret;
		} catch (Exception $e) {
			$code = $e->getCode();
			$msg = $e->getMessage();
			$data = [];
		}
		
		$this->ajaxReturn(['error_code' => $code, 'error_msg' => $msg, 'data' => $data]);
	}
	
	
	/**
	 * 对接编辑页面
	 * @return void
	 */
	public function editDocking()
	{
		if ($_POST) {
			//控制器
			$ordersController = new OrdersController();
			//查询订单信息
			$info = $this->getOrderInfo(I("post.id"));
			//过滤不规则字符串
			$reg = '/[\`~!@#$%^&*\(\)+<>?"{},.\/;\'\"\s]/';
			$info["xiaoqu"] = preg_replace($reg, "", $info["xiaoqu"]);
			
			//生成手机访问的短网址
			$orderdwz = url_getdwz($info['id']);
			$info['dwz'] = $orderdwz;
			
			$this->assign("info", $info);
			$this->assign("source_in", $this->source_in);
			
			//后台转发人数组
			$ids = D("Options")->getOptionNameCC("kf_admin_order_users");
			$names = D("User")->getAdminNamesById($ids['option_value']);
			foreach ($names as $k => $v) {
				$zhuanfaren[] = $v['name'];
			}
			$this->assign("zhuanfaren", $zhuanfaren);
			/*$this->assign("zhuanfaren",$this->zhuanfaren);*/
			//获取订单城市和区县
			$city = D("Quyu")->getCityInfoById($info["cs"]);
			$this->assign("city", $city);
			//户型
			$huxing = D("Huxing")->gethx();
			//预算
			$yusuan = D("Jiage")->getJiage();
			//装修方式
			$fangshi = D("Fangshi")->getfs();
			//风格
			$fengge = D("Fengge")->getfg();
			//获取最后审核人信息
			$csos_new = D("OrderCsosNew")->getCsosInfo(I("post.id"));
			
			//获取 未接通电话短信通知 短信记录
			$logCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 2);
			
			//获取 通知业主分配的装修公司 短信记录
			$smsCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 1);
			
			//获取订单分配信息
			$company = D("OrderInfo")->getOrderComapny($info["id"]);
			
			//有分配订单的情况下，查询微信是否发送
			if (count($company) > 0) {
				//获取订单微信发送记录
				$wx = D("LogWxOrdersend")->getWeixinLog($info["id"]);
				if (count($wx) > 0) {
					$this->assign("wxMark", 1);
				}
			}
			
			//获取该订单所在城市的的会员公司
			$result = $this->getCompanyList($info["cs"], $info["lng"], $info["lat"]);
			
			//如果是已分配公司,默认选中
			foreach ($company as $key => $value) {
				$compnay_id[$value["id"]] = $value["id"];
			}
			$choose_company = [];
			foreach ($company as $key => $value) {
				$choose_company[$value["id"]]['company_id'] = $value["id"];
				$choose_company[$value["id"]]['allot_source'] = $value["allot_source"];
			}
			foreach ($result[0] as $key => $value) {
				foreach ($value["child"] as $k => $val) {
					if (array_key_exists($val["id"], $compnay_id)) {
						$result[0][$key]["child"][$k]["ischeck"] = 1;
						if ($choose_company[$val["id"]]['allot_source'] == 3) {
							$result[0][$key]["child"][$k]["no_change"] = 1;
						} else {
							$result[0][$key]["child"][$k]["no_change"] = 0;
						}
					}
				}
			}
			
			foreach ($result[1] as $key => $value) {
				foreach ($value["child"] as $k => $val) {
					if (array_key_exists($val["id"], $compnay_id)) {
						$result[1][$key]["child"][$k]["ischeck"] = 1;
					}
					if ($choose_company[$val["id"]]['allot_source'] == 3) {
						$result[1][$key]["child"][$k]["no_change"] = 1;
					} else {
						$result[1][$key]["child"][$k]["no_change"] = 0;
					}
				}
			}
			
			//查询上次分配装修公司
			$fenpei_company = D("OrderInfo")->getLastTypeFw($info["id"], $info["cs"]);
			
			//本地装修公司
			foreach ($fenpei_company as $k => $val) {
				if ($val["cs"] == $info["cs"]) {
					$fenpei_now_company[] = $val;
					unset($fenpei_company[$k]);
				}
			}
			
			//其他城市
			foreach ($result[1] as $key => $value) {
				foreach ($fenpei_company as $k => $val) {
					if ($val["cs"] == $key) {
						$result[1][$key]["fen_company"][] = $val;
						unset($fenpei_company[$k]);
					}
				}
			}
			
			//查询小区历史签单公司
			$history = $this->orderHistory($info["xiaoqu"], $info['cs']);
			if (count($history) > 0) {
				$this->assign("history", $history);
			}
			
			//获取最近30过期的会员信息
			$lostCompany = $this->getLastExpireCompanyList($info["cs"], date("Y-m-d"));

//            //获取订单IP是否重复
//            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));
//            $ipCount = $ipCount[0]["repeat_count"] - 1;
//            if ($ipCount > 0) {
//                $this->assign("ipMark",$ipCount);
//            }
			
			//获取群公布模版信息
			$notice = D("OrderNoticeTemplate")->getCityTemplate($info["cs"]);
			
			//获取城市信息模版
			$tmp = $this->getCityInfoTmp($info["cs"], true);
			
			//获取申请记录
			$apply = D("Orders")->getOrderApplyEdit($info["id"], 1);
			
			$this->assign("apply", $apply);
			$this->assign("notice", $notice);
			$this->assign("tmp", $tmp);
			$this->assign("lostCompany", $lostCompany);
			$this->assign("company", $company);
			$this->assign("smsCount", $smsCount);
			$this->assign("fenpei_now_company", $fenpei_now_company);
			$this->assign("nowCompanys", $result[0]);
			$this->assign("otherCompanys", $result[1]);
			$this->assign("logCount", $logCount);
			$this->assign("csos_new", $csos_new);
			//未分配页面 订单出现的时间超过5分钟后，才能现在待分配
			if (I('post.type') != 1) {
				unset($this->docking_status[3]);
				unset($this->docking_status[4]);
			}
			
			//如果不是是赠单,删除分单选项
			if (!($info["on"] == 4 && $info["type_fw"] == 2) && !($info["on"] == 4 && $info["type_fw"] == 6)) {
				unset($ordersController->docking_status[5]);
			}
			//如果是赠单 , 审核状态修改(k1.7.0),如果是抢单撤回管理页面,订单状态审核只能撤回
			if (($info["on"] == 4 && $info["type_fw"] == 2) || (I('post.type') == 4)) {
				unset($ordersController->docking_status[0]);
				unset($ordersController->docking_status[1]);
				unset($ordersController->docking_status[3]);
				//赠单情况下 , 先不删除 [分单] 操作
				if (!($info["on"] == 4 && $info["type_fw"] == 2)) {
					unset($ordersController->docking_status[5]);
				}
				
				//如果已经分配结束 , 就不能直接赠送/推送至抢单池
				if ((count($company) != $info['allot_num']) && I('post.type') != 4) {
					$ordersController->docking_status = array_merge($ordersController->new_docking_status, $ordersController->docking_status);
				}
			}
			
			//如果是待分配赠单 , 审核状态修改(k1.7.0)
			if ($info["on"] == 4 && $info["type_fw"] == 6) {
				unset($ordersController->docking_status[0]);
				unset($ordersController->docking_status[1]);
				unset($ordersController->docking_status[3]);
				//如果已经分配结束 , 就不能直接赠送/推送至抢单池
				if (count($company) != $info['allot_num']) {
					$ordersController->docking_status = array_merge($ordersController->new_docking_status, $ordersController->docking_status);
				}
			}
			
			//如果是抢单撤回页面 , 则只保留 撤回操作
			if (I('post.type') == 4) {
				unset($ordersController->docking_status[5]);
			}
			$this->assign("gettype", I('post.type'));
			$this->assign("status", $ordersController->docking_status);
			$this->assign("keys", $ordersController->keys);
			$this->assign("lf_time", $ordersController->lf_time);
			$this->assign("start_time", $ordersController->start_time);
			$this->assign("shi", $ordersController->shi);
			$this->assign("lxs", $ordersController->lxs);
			$this->assign("fengge", $fengge);
			$this->assign("fangshi", $fangshi);
			$this->assign("yusuan", $yusuan);
			$this->assign("huxing", $huxing);
			$this->assign("applyname", session("uc_userinfo.name"));
			$tmp = $this->fetch("editDocking");
			$this->ajaxReturn(array("code" => 200, "data" => $tmp, 'info' => $info));
		}
	}

	public function getHistory()
	{
	    if (IS_AJAX && IS_POST) {
	        $id = I("post.id");
	        $logic = new CompanyVisitLogicModel();
            $list = $logic->getVisitReturnList($id);
            $this->ajaxReturn(array("error_code" => 0, "data" => $list));
	    }
	}

	public function addvisit()
	{
        if (IS_AJAX && IS_POST) {
            try {
                $data = I("post.");
                if (empty($data["feedbacker"])) {
                    $this->ajaxReturn(array("error_code" => 1, "error_msg" => "请填写反馈人姓名"));
                }

                if (count($data["company"]) == 0) {
                    $this->ajaxReturn(array("error_code" => 1, "error_msg" => "请选择要求回访的装修公司名称"));
                }

                if (empty($data["visit_step"])) {
                    $this->ajaxReturn(array("error_code" => 1, "error_msg" => "请选择回访阶段"));
                }

                if (empty($data["visit_need"])) {
                    $this->ajaxReturn(array("error_code" => 1, "error_msg" => "请填写需要回访的内容"));
                }

                //判断订单是否已生成
                //检查是否在订单池中
                $reviewLogic = new OrderReviewLogicModel();
                $reviewInfo = $reviewLogic->getReviewInfoByOrderId($data["id"]);

                //检查是否是分单且已分配装修公司
                $flag = $reviewLogic->getExistFenOrderInfo($data["id"]);

                if (!$flag) {
                    $this->ajaxReturn(['error_code' => 1, 'error_msg' => '该订单还未分单，请分单后再次尝试']);
                }

                //todo 验证是否在主动回访表中
                $reviewNewLogic = new NewReviewLogicModel();
                $info = $reviewNewLogic->getReviewInfo($data["id"]);
                if($info){
                    $this->ajaxReturn(['error_code' => 1, 'error_msg' => '该订单已在新回访订单中']);
                }

                if ($reviewInfo["toreview_at"] > time()) {
                    //更新回访信息
                    $subData = [
                        "toreview_at" => time(),
                        "updated_at" => time()
                    ];
                    $reviewLogic->editReviewByOrderId($reviewInfo["order_id"],$subData);

                    //判断是否有未回访回访工单信息
                    $visitLogic = new CompanyVisitLogicModel();
                    $result = $visitLogic->getOrderNoReturnVisitInfo($data["id"]);
                   
                    //如果没有未回访信息添加工单信息
                    if (count($result) == 0) {
                        //添加新的订单
                        $is_save = true;
                        $lf_status = 0;
                    }

                } else {
                    //将订单加入回访池
                    $type = !empty($reviewInfo) ? 1 : 2;
                    $result = $reviewLogic->ForceSaveReview($data["id"], $data["company"], $data["visit_user_need"], $type);
                    if ($result[0] !== false) {
                        $is_save = true;
                        $lf_status = $result[1];
                    }
                }

                if ($is_save) {
                    //添加回访表信息
                    $saveData = [
                        "order_id" => $data["id"],
                        "order_lf_status" => $lf_status,
                        "visit_need" => $data["visit_need"],
                        "visit_user_need" => $data["visit_user_need"],
                        "visit_type" => 1,
                        "visit_step" =>  $data["visit_step"],
                        "creator" => session("uc_userinfo.id"),
                        "created_at" => time(),
                        "updated_at" => time(),
                        "feedbacker" => $data["feedbacker"]
                    ];

                    $logic = new CompanyVisitLogicModel();
                    $i = $logic->addVisitInfo($saveData);

                    //添加回访表关联表
                    $all = [];
                    foreach ($data["company"] as $value) {
                        $all[] = [
                            "visit_id" => $i,
                            "company_id" => $value,
                            "related_visit" => 1,
                            "created_at" => time(),
                            "updated_at" => time()
                        ];
                    }
                    $logic->addVisitRelatedInfo($all);
                }
                $this->ajaxReturn(['error_code' => 0]);
            } catch (\Exception $e){
                $this->ajaxReturn(['error_code' => 1, 'error_msg' => "请求失败"]);
            }
        }
	}

    // 获取新的订单
    public function getNewReviewOrder()
    {
        $privRoleIds = RbacRoleEnum::getNewOrderRoles();
        if (!in_array($this->User['uid'], $privRoleIds)) {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '当前角色不支持获取新订单！']);
        }
        //获取没有归属人新单
        $reviewLogic = new OrderReviewLogicModel();
        $status = $reviewLogic->getNoVestInOrderByUid($this->User['id']);
        if ($status) {
            $this->ajaxReturn(['error_code' => 0, 'error_msg' => '获取成功!']);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '获取失败! 请稍后再试']);
        }
    }

    public function getNewReviewCount()
    {      

        //获取当前新订单总数/当前已抢未拨打新单
        $reviewLogic = new OrderReviewLogicModel();
        $reviewCount = $reviewLogic->getNewReviewInfo();
        if (IS_AJAX) {
            $this->ajaxReturn(['error_code' => 0, 'error_msg' => '获取成功!', 'info' => $reviewCount]);
        }
        return $reviewCount;
    }

    //转入回访订单池
    private function toorderreview()
    {
        if (empty(I('post.orderno')) || (count(I('post.com_list')) <= 0) || empty(I('post.feedback'))) {
            $this->ajaxReturn(['error_code' => 1, 'error_msg' => '缺少必填参数']);
        }
        $orderno = I('post.orderno');
        $com_list = I('post.com_list');
        //检查是否在订单池中
        $reviewLogic = new OrderReviewLogicModel();
        $reviewInfo = $reviewLogic->getReviewInfoByOrderId($orderno);

        if (!empty($reviewInfo['order_id']) && isset($reviewInfo['order_id'])) {
            //已在回访列表
            if (($reviewInfo['toreview_at'] <= time())) {
                $this->ajaxReturn(['error_code' => 1, 'error_msg' => '订单已存在于回访列表中']);
            } else {
                //更新成被动入池
                $reviewLogic->ForceSaveReview($orderno, $com_list, I('post.feedback'), 1);
            }
        } else {
            //检查是否是分单且已分配装修公司
            if (!$reviewLogic->getExistFenOrderInfo($orderno)) {
                $this->ajaxReturn(['error_code' => 1, 'error_msg' => '该订单还未分单，请分单后再次尝试']);
            }
            //将订单加入回访池
            $reviewLogic->ForceSaveReview($orderno, $com_list, I('post.feedback'), 2);
        }

        $this->ajaxReturn(['error_code' => 0]);
    }
}