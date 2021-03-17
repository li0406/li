<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/17
 * Time: 14:23
 */

namespace Home\Model\Logic;


use Home\Model\Db\AdminuserModel;
use Home\Model\Db\LogTelcenterReviewOrdercallModel;
use Home\Model\Db\OrderCompanyReviewModel;
use Home\Model\Db\OrderInfoModel;
use Home\Model\Db\OrderReviewFeedbackModel;
use Home\Model\Db\OrderReviewModel;
use Home\Model\Db\OrdersModel;
use Home\Model\Db\ReviewCityModel;
use Home\Model\Db\OrderReviewApplyTelModel;
use Think\Exception;

class OrderReviewLogicModel
{
	/**
	 * @var ReviewCityModel
	 */
	protected $reviewCityModel;
	
	/**
	 * @var OrderReviewModel
	 */
	protected $orderReviewModel;
	
	/**
	 * @var LogTelcenterReviewOrdercallModel
	 */
	public $logTelReviewOrderCallModel;
	
	public $logTelOrderCallModel;
	/**
	 * @var OrderCompanyReviewModel
	 */
	protected $orderCompanyReviewModel;
	
	private $lf_time = array(
		0 => "随时,下班,今明",
		1 => "1周内,本周末",
		2 => "2周内,下周末",
		3 => "3周内",
		4 => "4周内",
		5 => "1个月以上",
		6 => "其他"
	);
	
	private $noLfReason = array(
		1 => "业主无法联系",
		2 => "业主无装修需求",
		3 => "业主已经签约",
		4 => "业主无法量房",
		5 => "业主仅咨询了解",
		6 => "业主有户型图"
	);
	/**
	 * @var array
	 */
	public $state = [
		1 => '自动',
		2 => '被动',
		3 => '会员量房',
	];
	
	/**
	 * 订单状态
	 * @var array
	 */
	public $reviewType = [
		1 => '新单',
		2 => '次新单',
		3 => '待定单',
		4 => '无效单',
		5 => '有效单',
	];
	
	/**
	 * 订单备注
	 * @var array
	 */
	public $reviewRemark = [
		1 => '未接通',
		2 => '开场挂',
		3 => '不配合回访',
		4 => '晚点联系',
		5 => '回访中断',
		6 => '拒接/未接通',
		7 => '拒绝服务',
		8 => '装修公司原因未量房',
		9 => '已量房/已到店',
		10 => '再约量房/到店成功',
        11 => '签单',
        12 => '再约签单成功',
        13 => '推荐服务',
        14 => '再约推荐服务成功',
        15 => '不配合回访'
	];
	
	/**
	 * 回访订单状态-订单备注
	 * 回访备注;2.次新单(1:未接通,2:开场挂,3:不配合回访,4:晚点联系,5:回访中断),4.无效单(6:拒接/未接通,7:拒绝服务,8:装修公司原因未量房),5.有效单(9:已量房/已到店,10:再约量房/到店成功)
	 * @var array
	 */
	protected $typeMapRemark = [
		2 => [
			'type' => 'data',
			'list' => [
				[
					'remark' => 1,
					'desc' => '未接通'
				],
				[
					'remark' => 2,
					'desc' => '开场挂'
				],
				[
					'remark' => 3,
					'desc' => '不配合回访'
				],
				[
					'remark' => 4,
					'desc' => '晚点联系'
				],
				[
					'remark' => 5,
					'desc' => '回访中断'
				],
			],
		],
		3 => [
			'type' => 'time',
			'list' => [],
		],
		4 => [
			'type' => 'data',
			'list' => [
				[
					'remark' => 6,
					'desc' => '拒接/未接通'
				],
				[
					'remark' => 7,
					'desc' => '拒绝服务'
				],
				[
					'remark' => 8,
					'desc' => '装修公司原因未量房'
				],
				[
					'remark' => 15,
					'desc' => '不配合回访'
				],
			],
		],
		5 => [
			'type' => 'data',
			'list' => [
				[
					'remark' => 9,
					'desc' => '已量房/已到店'
				],
				[
					'remark' => 10,
					'desc' => '再约量房/到店成功'
				],
                [
                    'remark' => 11,
                    'desc' => '签单'
                ],
                [
                    'remark' => 12,
                    'desc' => '再约签单成功'
                ],
                [
                    'remark' => 13,
                    'desc' => '推荐服务'
                ],
                [
                    'remark' => 14,
                    'desc' => '再约推荐服务成功'
                ],
			],
		],
	];
	public $lfState = [
		1 => '未量房',
		2 => '已量房',
	];
	
	protected $perCount = 10;
	
	public function __construct()
	{
		$this->reviewCityModel = new ReviewCityModel();
		$this->orderReviewModel = new OrderReviewModel();
		$this->logTelReviewOrderCallModel = new LogTelcenterReviewOrdercallModel();
		$this->orderCompanyReviewModel = new OrderCompanyReviewModel();
		$this->reviewOrderApplyTelModel = new OrderReviewApplyTelModel();
	}
	
	public function insertToReview($orderid, $lftime)
	{
		//若回访池已存在该订单,若当前时间小于订单入池时间且是自动加入,更新订单入池时间,否则,不做处理
		$reviewModel = new OrderReviewModel();
		$reviewInfo = $reviewModel->getReviewInfoByOrderId('order_id,toreview_at,state', ['order_id' => $orderid]);
		//获取入池时间
		$data['toreview_at'] = $this->getToReviewTime($lftime);
		//获取已有回访记录
		$orderModel = new OrdersModel();
		$huifan = $orderModel->getInfoByOrderId('huifan', ['id' => $orderid]);
		$data['review_record'] = $huifan['huifan'];
		
		if (!empty($reviewInfo['order_id']) && isset($reviewInfo['order_id'])) {
			if ((time() < $reviewInfo['toreview_at']) && ($reviewInfo['state'] == 1)) {
				$where['order_id'] = $orderid;
				if ($data['toreview_at'] == 0) {
					$reviewModel->deleteReview($where);
				} else {
					
					$data['updated_at'] = time();
					$reviewModel->updateReview($orderid, $data);
				}
			}
		} else {
			if ($data['toreview_at'] != 0) {
				$data['order_id'] = $orderid;
				$data['created_at'] = time();
				$data['updated_at'] = time();
				$data['state'] = 1;
				$reviewModel->add($data);
			}
		}
	}
	
	private function getToReviewTime($lftime)
	{
		switch ($lftime) {
			case $this->lf_time[0]:
				return time() + 3 * 3600 * 24;
			case $this->lf_time[1]:
				return time() + 5 * 3600 * 24;
			case $this->lf_time[2]:
				return time() + 15 * 3600 * 24;
			case $this->lf_time[3]:
				return time() + 23 * 3600 * 24;
			case $this->lf_time[4]:
				return time() + 30 * 3600 * 24;
			case $this->lf_time[5]:
				return time() + 35 * 3600 * 24;
			default:
				return 0;
		}
	}
	
	public function existCity($cs)
	{
		$cityModel = new ReviewCityModel();
		if ($cityModel->where(['cs' => $cs])->count() > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取quyu所有cities
	 * @return mixed
	 */
	public function getCities()
	{
		$ret = D("Quyu")->getAllQuyuOnly();
		return $ret;
	}

	/**
	 * 获取不在review_city 表中的数据
	 * @return mixed
	 */
	public function getNotInCities()
	{
		return $this->reviewCityModel->getNotInCities();
	}
	
	/**
	 * 获取review_city 表中的数据
	 * @return mixed
	 */
	public function getReviewFieldCities()
	{
		return $this->reviewCityModel->getReviewFieldCities();
	}
	
	/**
	 * 保存数据
	 * @param array $reviewCities
	 * @throws Exception
	 */
	public function saveReviewCities(array $reviewCities)
	{
		try {
			//1.删除旧的
			$delRet = $this->reviewCityModel->drop();
			//2.保存新的
			$values = [];
			foreach ($reviewCities as $city) {
				$values[] = [
					'cs' => $city,
					'created_at' => time(),
					'updated_at' => time(),
				];
			}
			$insertRet = $this->reviewCityModel->addAll($values);
			if (empty($insertRet)) {
				throw new Exception('保存失败', 401);
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function getReviewInfoByOrderId($orderid, $type = 1)
	{
		$reviewModel = new OrderReviewModel();
		if ($type == 1) {
			$field = 'id,order_id,toreview_at,state,review_record,feedback';
		} else {
			$field = 'feedback';
		}
		return $reviewModel->getReviewInfoByOrderId($field, ['order_id' => $orderid]);
		
	}
	
	public function ForceSaveReview($orderid, $com_list, $feedback, $type = 1)
	{
		//获取订单量房状态
		$companyReviewModel = new OrderCompanyReviewModel();
		//lf_state 入池量房状态
		$map['orderid'] = ['eq', $orderid];
		$map['status'] = ['in', [1, 4]];
		
		if ($companyReviewModel->where($map)->count(1) > 1) {
			$data['lf_state'] = 2;
		} else {
			$data['lf_state'] = 1;
		}
		$data['toreview_at'] = time();
		$data['updated_at'] = time();
		$data['state'] = 2;
		$data['feedback'] = $feedback;
		$data['review_uid'] = session("uc_userinfo.id");
		$data['review_name'] = session("uc_userinfo.user");
		$data['vest_in_uid'] = session("uc_userinfo.id");
		//获取已有回访记录
		$orderModel = new OrdersModel();
		$huifan = $orderModel->getInfoByOrderId('huifan', ['id' => $orderid]);
		$data['review_record'] = empty($huifan['huifan']) ? '' : $huifan['huifan'];
		
		$reviewModel = new OrderReviewModel();
		if ($type == 1) {
			//更新成被动入池
			$where['orderid'] = $orderid;
			$reviewModel->updateReview($orderid, $data);
		} else {
			//添加到回访池
			$data['order_id'] = $orderid;
			$data['created_at'] = time();
			$reviewModel->add($data);
		}
		//保存需要回访的公司
		$result = $this->saveAllCompany($com_list, $orderid);
	    return [$result,$data['lf_state']];
	}

	public function getExistFenOrderInfo($orderno)
	{
		$companyReviewModel = new OrderInfoModel();
		if ($companyReviewModel->getFenAndReviewCompanyCount($orderno) > 0) {
			return true;
		} else {
			return false;
		}
	}

	private function saveAllCompany($com_list, $orderno)
	{
		$addlist = [];
		foreach ($com_list as $key => $val) {
			$addlist[$key]['com_id'] = $val;
			$addlist[$key]['order_id'] = $orderno;
			$addlist[$key]['created_at'] = time();
			$addlist[$key]['updated_at'] = time();
		}
		$feedbackModel = new OrderReviewFeedbackModel();
		$map['order_id'] = ['eq', $orderno];
		$feedbackModel->deleteRow($map);
		return $feedbackModel->addAll($addlist);
	}
	
	/**
	 * 已分配公司
	 * @param $order_no
	 * @return mixed
	 */
	public function getOrderCompany($order_no)
	{
		$companyModel = new OrderInfoModel();
		$list = $companyModel->getOrderCompany($order_no, 'a.isread,u.jc,u.id as comid,a.type_fw,c.cooperate_mode,u.classid');
		//添加第一个签返公司选中效果
		foreach ($list as $k=>$v){
		    if($v['cooperate_mode'] == 2){
                $list[$k]['choose_qianfan'] = 1;
                return $list;
            }
        }
		return $list;
	}
	
	/**
	 * 通过手机号订单号获取订单基本信息
	 * @param $ordertel
	 * @return mixed
	 */
	public function getOrderByOrderno($ordertel)
	{
		$reviewModel = new OrderReviewModel();
		$result = $reviewModel->getOrderByOrderno($ordertel);
		if (!empty($result[0]) && isset($result[0])) {
			return $result[0];
		}
	}
	
	/**
	 * 查询订单
	 * @param  [type] $input 	[description]
	 * @return [type]           [description]
	 */
	public function getOrderSearchList($input, $page = 1, $limit = 20)
	{
		$orderReviewModel = new OrderReviewModel();
		$count = $orderReviewModel->getOrderSearchList("count", $input);
		if ($count > 0) {
			$offset = ($page - 1) * $limit;
			$list = $orderReviewModel->getOrderSearchList("list", $input, $offset, $limit);
		}

		return ["list" => $list, "count" => $count];
	}

	public function getOrderSearchImportList($input)
	{
		$orderReviewModel = new OrderReviewModel();
		return $orderReviewModel->getOrderSearchList("list", $input);
	}


	/**
	 * 要求回访公司
	 * @param $orderno
	 */
	public function getReviewCompany($orderno)
	{
		$reviewModel = new OrderReviewFeedbackModel();
		return $reviewModel->getReviewCompany($orderno);
	}
	
	
	public function getNoLfReason($orderno)
	{
		$reviewModel = new OrderCompanyReviewModel();
		$list = $reviewModel->getNoLfReasonList($orderno);
		$reasonList = [];
		foreach ($list as $val) {
			$val['reason'] = explode(',', $val['reason']);
			foreach ($val['reason'] as $v) {
				$val['reason_list'][] = $this->noLfReason[$v];
			}
			$val['reason_list'] = implode('、', $val['reason_list']);
			$reasonList[] = $val;
		}
		return $reasonList;
	}
	
	/**
	 * 获取订单列表
	 * @param array $params
	 * @return mixed
	 */
	public function getReviewOrders(array $params,array $user)
	{
		$reqParams = $params;
		//验证并格式化请求参数
		$reqParams["default_time"] = 0;
        if (!empty($params['start']) && !empty($params['end'])) {
            $reqParams['start'] = strtotime($params['start']);
            $reqParams['end'] = strtotime($params['end'] . ' 23:59:59');
        } elseif (empty($params['start']) && !empty($params['end'])) {
            $reqParams['start'] = strtotime(date('Y-m-d', strtotime('-30 day')));
            $reqParams['end'] = strtotime($params['end'] . ' 23:59:59');
        } elseif (!empty($params['start']) && empty($params['end'])) {
            $reqParams['start'] = strtotime($params['start']);
            $reqParams['end'] = time();
        } else {
        	$reqParams["default_time"] = 1;
            $reqParams['start'] = strtotime(date('Y-m-d', strtotime('-30 day')));
            $reqParams['end'] = time();
        }
		
		if (array_key_exists($params['type'], $this->state)) {
			$reqParams['type'] = (int)$params['type'];
		}
		
		if (array_key_exists($params['remark'], $this->reviewRemark)) {
			$reqParams['remark'] = (int)$params['remark'];
		}
		
		if (array_key_exists($params['state'], $this->state)) {
			$reqParams['state'] = (int)$params['state'];
		}
        if (!empty($params['cs'])) {
            $reqParams['cs'] = (int)$params['cs'];
        }
		if (!empty($params['applytel'])) {
            $reqParams['applytel'] = (int)$params['applytel'];
        }

        $map = $this->orderReviewModel->setMap($reqParams,$user);
		$count = $this->orderReviewModel->getAllCount($map);

        $result = ['page' => '', 'list' => []];
		if (!empty($count)) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $this->perCount);
            $result = $this->orderReviewModel->getAll($map, $page->firstRow, $page->listRows);
            $vestUser = [];//归属人
            if (!empty($result)) {
                //获取归属人
                $uid = array_column($result, 'vest_in_uid');
                if(count($uid) > 0){
                    $where = [
                        'id'=>['in',$uid]
                    ];
                    $userDb = new AdminuserModel();
                    $vestUser = $userDb->getAdminByMap($where, 'id,name,user');
                    $vestUser = array_column($vestUser, null, 'id');
                }
                foreach ($result as $key => $item) {
                    $list[$item["order_id"]] = $this->transform($item);
                    $list[$item["order_id"]]['vest_in_name'] = isset($vestUser[$item['vest_in_uid']]) ? $vestUser[$item['vest_in_uid']]['user'] : '';
                    $orderId[] = $item["order_id"];
                }

                if (count($orderId) > 0) {
                    //查询销售回访工单情况
                    $logic = new CompanyVisitLogicModel();
                    $result = $logic->getOrderVisitListWithEnd($orderId);
                    foreach ($result as $item) {
                        $list[$item["order_id"]]["feedbacker"] = $item["feedbacker"];
                    }
                }

            }
            $result['page'] = $page->show();
            $result['list'] = $list;
        }
		return $result;
	}
	
	/**
	 * 编辑 更新
	 * @param array $params
	 * @return mixed
	 * @throws Exception
	 */
	public function editReview(array $params)
	{
		$admin = getAdminUser();
		if (empty($admin)) {
			throw new Exception('请登陆', 401);
		}
		$id = $params['id'];
		$data = [
            'review_uid' => $admin['id'],
            'review_name' => $admin['name'],
            'review_type' => $params['review_type'],
            'review_remark' => $params['review_remark'],
            'review_record' => $params['review_record'],
            'feedback' => $params['feedback']?:$params['feedback'],
            'review_remark_time' => time(),
            'updated_at' => time(),
            'next_visit_step' => $params['next_visit_step'],
            'next_visit_time' => strtotime($params['next_visit_time']),
        ];

		//没有下次回访时间
		if (empty($data["next_visit_time"])) {
		   $data["next_visit_time"] = 0;
           $data["next_visit_step"] = 0;
		}

        //待定单，获取待定单时间
		if ($data['review_type'] == 3) {
			$data['review_remark'] = 0;
			$data['review_remark_time'] = 0;
			$data['wait_order_time'] = strtotime($params['wait_order_time']) ?: 0;
		}
		return $this->orderReviewModel->updateReview($id, $data);
	}
	
	public function getDetail($orderId)
	{
		$data = $this->orderReviewModel->getDetail($orderId);
		$ret = $this->transform($data);
		return $ret;
	}
	
	/**
	 * 清洗数据
	 * @param array $data
	 * @return array
	 */
	private function transform(array $data)
	{
		$data['state_name'] = array_key_exists($data['state'], $this->state) ? $this->state[$data['state']] : '';
		$data['review_type_name'] = array_key_exists($data['review_type'], $this->reviewType) ? $this->reviewType[$data['review_type']] : '';
		$data['review_remark_name'] = array_key_exists($data['review_remark'], $this->reviewRemark)
			? $this->reviewRemark[$data['review_remark']] : '';
		$data['toreview_at_more'] = date('Y-m-d H:i:s', $data['toreview_at']);
		$data['toreview_at'] = date('Y-m-d', $data['toreview_at']);
		$data['wait_order_time'] = $data['wait_order_time'] ? date('Y-m-d H:i', $data['wait_order_time']) : '';
		$data['review_remark_time'] = $data['review_remark_time'] ? date('Y-m-d H:i:s', $data['review_remark_time']) : '';
		//新单不显示订单备注
        if ($data['review_type'] == 1) {
            $data['review_remark_name'] = $data['review_remark_time'] = '';
        }
//		$data['is_lf'] = $data['lftime'] ? '已量房' : '未量房';
		$data['lf_state_name'] = array_key_exists($data['lf_state'], $this->lfState) ? $this->lfState[$data['lf_state']] : '';
		
		//回访电话纪录日志(老)
		$telLog = $this->logTelReviewOrderCallModel->getReviewOrderCallCount($data['order_id']);
        //回访电话纪录日志(新)
        $hl_telLog = $this->logTelReviewOrderCallModel->getHeLiReviewOrderCallCount($data['order_id']);
		
		//获取订单通话纪录数量
		$telOrderLog = $this->logTelReviewOrderCallModel->getOrderCallCountByOrderIds($data['order_id']);

        //获取合力订单通话纪录数量
        $telHeLiOrderLog = $this->logTelReviewOrderCallModel->getHollyOrderCallCountByOrderIds($data['order_id']);

        $data['tel_log_count'] = isset($telLog[0]['repeat_count']) ? $telLog[0]['repeat_count'] : 0;
        $data['heli_review_tel_log_count'] = isset($hl_telLog[0]['repeat_count']) ? $hl_telLog[0]['repeat_count'] : 0;
		$data['tel_order_log_count'] = isset($telOrderLog[0]['repeat_count']) ? $telOrderLog[0]['repeat_count'] : 0;
		$data['heli_tel_log_count'] = isset($telHeLiOrderLog[0]['repeat_count']) ? $telHeLiOrderLog[0]['repeat_count'] : 0;

		$data['tel_log_count'] = $data['tel_order_log_count'] + $data['tel_log_count'] + $data['heli_tel_log_count'] + $data['heli_review_tel_log_count'];
		//获取最新的量房时间
		$data['lf_time'] = $this->orderCompanyReviewModel->getLastestLfTime($data['order_id']);

        // 处理订单显号
        $apply_tel_admin = explode(',', $data['apply_tel_admin']);
        $apply_tel_status = explode(',', $data['apply_tel_status']);
        foreach ($apply_tel_admin as $k => $v) {
            $data['apply_tel'][$v] = $apply_tel_status[$k];
        }

		//当进入订单回访池的订单被装修公司标记为已量房后，在展示中将此订单加入底色。
		//入池前未量房,入池后已量房 ->变色
		$data['is_color'] = 0;
		if ($data['lf_state'] == 1 && $data['lf_time']) {
			$data['is_color'] = 1;
		}

        $data['next_visit_time'] = empty( $data['next_visit_time'])?"": date("Y-m-d",$data['next_visit_time']);
        $data['next_visit_step'] = empty( $data['next_visit_step'])?"": $data['next_visit_step'];
		return $data;
	}
	
	/**
	 * 根据type获取订单remark
	 * @param $type
	 * @return mixed
	 * @throws Exception
	 */
	public function getOrderRemark($type)
	{
		try {
			if (!array_key_exists($type, $this->typeMapRemark)) {
				throw new Exception('无效的状态', 401);
			}
			$ret = $this->typeMapRemark[$type];
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}
	
	public function getVipUserCount($orderID)
	{
		$ret = $this->orderReviewModel->getVipUserCount($orderID);
		return $ret;
	}

    /**
     * 显号审核添加/编辑
     * @param $data
     * @return bool|string
     */
    public function addOrderEyeInfo($data)
    {
        $now = time();
        $admin = getAdminUser();
        $map['orders_id'] = ['EQ',$data['id']];
        $map['apply_id'] = ['EQ',$admin['id']];
        $apply = $this->reviewOrderApplyTelModel->getUserApplyByOrderid($map);

        if (!empty($apply)) {
            if ($apply['status'] == 2 && !empty($apply['pass_time']) && ($apply['pass_time'] > strtotime('today') - 86400 * 7 && $apply['pass_time'] <= $now)) {
                return '当前订单已申请显号成功，不能重复申请';
            }
            $save = [
                'apply_reason' => $data['text'],
                'apply_time' => $now,
                'status' => 1,
                //此处重置审核人为空
                'passer_id' => 0,
                'pass_time' => 0,
            ];
            $result = $this->reviewOrderApplyTelModel->addOrdersApplyTel($save, ['id' => $apply['id']]);
        } else {
            $save = [
                'orders_id' => $data['id'],
                'apply_id' => $admin['id'],
                'apply_reason' => $data['text'],
                'apply_time' => $now,
                'status' => 1
            ];
            $result = $this->reviewOrderApplyTelModel->addOrdersApplyTel($save);
        }
        return $result;
    }

    /**
     * 获取显号列表
     * @param  integer $orders_id [订单ID]
     * @param  boolean $add_start [开始添加时间]
     * @param  boolean $status [状态，是否审核通过]
     * @return mixed
     */
    public function getApplyTelListByOrdersId($orders_id = 0, $add_start = false, $status = false)
    {
        if (empty($orders_id)) {
            return [];
        }

        $map['orders_id'] = $orders_id;

        if ($add_start != false) {
            $map['apply_time'] = array('EGT', $add_start);
        }

        if ($status != false) {
            $map['status'] = intval($status);
        }
        return $this->reviewOrderApplyTelModel->getFullListByMap($map);
    }

    /**
     * 显号审核
     */
    public function saveOrdersApplyTel($id, $data)
    {
        return $this->reviewOrderApplyTelModel->addOrdersApplyTel($data, ['id' => $id]);
    }

    public function getUserApplyByOrderid($orderid, $userid)
    {
        $map['orders_id'] = ['EQ', $orderid];
        $map['apply_id'] = ['EQ', $userid];
        $map['pass_time'] = [['between', [strtotime('today') - 86400 * 7, time()]], ['eq', 0], 'OR'];
        return $this->reviewOrderApplyTelModel->getUserApplyByOrderid($map);
    }

    public function getOrderLiangfangInfo($order_id)
    {
        $model = new OrderCompanyReviewModel();
        $result = $model->getReviewInfoByOrderId($order_id,"a.status,u.jc");
        $list = [];
        foreach ($result as $item) {
			if ($item["status"] != 0 && $item["status"] != 3) {
				$list[] = $item["jc"];
			}
        }
        return $list;
    }

    public function editReviewByOrderId($id,$data)
    {
        return $this->orderReviewModel->updateReview($id, $data);
    }

    // 根据订单ID批量导入回访单
    public function orderReviewImportByOrderIds($orderIds){
    	if (count($orderIds) > 0) {
    		$list = $this->orderReviewModel->getReviewListByOrderIds($orderIds);
    		$list = array_column($list, null, "order_id");

    		$newIds = [];
    		foreach ($orderIds as $key => $order_id) {
    			// 回访池已存在时修改
    			if (array_key_exists($order_id, $list)) {
    				$data = array(
    					"state" => 1,
    					"review_type" => 1,
    					"toreview_at" => time(),
    				);
    				$this->orderReviewModel->updateReview($order_id, $data);
    			} else {
    				$newIds[] = $order_id;
    			}
    		}

    		// 回访池不存在新增
    		if (count($newIds) > 0) {
    			// 查询订单的量房状态
	    		$orderCompanyReviewModel = new OrderCompanyReviewModel();
	    		$lfList = $orderCompanyReviewModel->getOrdersLfStatus($newIds);
	    		$lfList = array_column($lfList, null, "orderid");

	    		$newData = [];
	    		foreach ($newIds as $key => $order_id) {
	    			$newData[$key] = array(
	    				"order_id" => $order_id,
    					"state" => 1,
    					"lf_state" => 1,
    					"review_type" => 1,
    					"toreview_at" => time(),
    					"created_at" => time(),
    					"updated_at" => time(),
    				);

	    			if (array_key_exists($order_id, $lfList)) {
	    				if ($lfList["lf_count"] >= 1) {
	    					$newData[$key]["lf_state"] = 2;
	    				}
	    			}
	    		}

	    		$newData = array_values($newData);
	    		$this->orderReviewModel->addAll($newData);
    		}
    	}

    	return true;
    }

    public function getNewReviewInfo($cs_help_user = [])
    {
        $reviewDb = new OrderReviewModel();
        return $reviewDb->getNewOrderReviewCount($cs_help_user);
    }

    public function getNoVestInOrderByUid($user_id)
    {
        $reviewDb = new OrderReviewModel();
        //查询无归属人新单
        $review = $reviewDb->getNewNoVestInOrder();
        if (!empty($review)) {
            //锁住订单
            $reviewDb->editReviewOrderInfo($review['id'], ['updated_at' => time()]);
            //再次查询该订单是否有归属人,防止两人同时操作 , 后者覆盖前者操作
            $info = $reviewDb->getReviewOrderInfo($review['id']);
            if ($info['vest_in_uid'] == 0) {
                //添加归属人
                $save = [
                    'updated_at' => time(),
                    'vest_in_uid' => $user_id
                ];
                $status = $reviewDb->editReviewOrderInfo($info['id'], $save);
                if($status){
                    return true;
                }
            }
        }
        return false;
    }

    public function getOrderReviewInfo($order_id){
        if(count($order_id) == 0){
            return [];
        }
        $reviewDb = new OrderReviewModel();
        return $reviewDb->getReviewListByOrderIds($order_id,'order_id,review_record,feedback,review_uid,review_name');
    }

    // 获取回访城市列表
    public function getReviewCityList($input, $limit = 20){
    	$count = $this->reviewCityModel->getCityPageCount($input);
    	if ($count > 0) {
    		import("Library.Org.Util.Page");
            $pageClass = new \Page($count, $limit);
            $pageshow = $pageClass->show();

            $list = $this->reviewCityModel->getCityPageList($input, $pageClass->firstRow, $pageClass->listRows);

            foreach ($list as $key => &$item) {
            	$item["visit_begin_date"] = $item["visit_begin"] ? date("Y-m-d", $item["visit_begin"]) : "";
            	$item["updated_date"] = $item["updated_at"] ? date("Y-m-d H:i", $item["updated_at"]) : "";
            	$item["mianji_min"] = floatval($item["mianji_min"]);
            	$item["mianji_max"] = floatval($item["mianji_max"]);

            	$item["state_name"] = "否";
            	$item["mianji_max_show"] = "";
            	if ($item["state"] == 1) {
            		$item["state_name"] = "是";
            		$item["mianji_max_show"] = $item["mianji_max"] ? : "∞";
            	}
            }
    	}

    	return [
    		"list" => $list,
    		"count" => $count,
    		"pageshow" => $pageshow
    	];
    }

    // 回访城市导出列表
    public function getReviewCityExportList($input){
    	$list = $this->reviewCityModel->getCityPageList($input, 0, 0);

    	foreach ($list as $key => &$item) {
        	$item["visit_begin_date"] = $item["visit_begin"] ? date("Y-m-d", $item["visit_begin"]) : "";
        	$item["updated_date"] = $item["updated_at"] ? date("Y-m-d H:i", $item["updated_at"]) : "";
        	$item["mianji_min"] = floatval($item["mianji_min"]);
        	$item["mianji_max"] = floatval($item["mianji_max"]);

        	$item["state_name"] = "否";
        	$item["mianji_max_show"] = "";
        	if ($item["state"] == 1) {
        		$item["state_name"] = "是";
        		$item["mianji_max_show"] = $item["mianji_max"] ? : "∞";
        	}
        }

       return $list;
    }

    // 回访城市设置详情
    public function getReviewCityInfo($city_id){
    	$detail = $this->reviewCityModel->getCityInfo($city_id);
    	if (!empty($detail)) {
    		$detail["mianji_max"] = floatval($detail["mianji_max"]) ? : "";
    		$detail["mianji_min"] = empty($detail["id"]) ? "" : floatval($detail["mianji_min"]);
    		$detail["visit_begin"] = $detail["visit_begin"] ? date("Y-m-d", $detail["visit_begin"]) : "";
    	}

    	return $detail;
    }

    // 回访城市设置日志
    public function getReviewCityLog($city_id){
    	$list = $this->reviewCityModel->getCityLog($city_id);

    	if (count($list) > 0) {
    		foreach ($list as $key => &$item) {
    			$item["created_date"] = date("Y-m-d H:i", $item["created_at"]);
    		}
    	}

    	return $list;
    }

    // 取消回访城市设置
    public function cancelReviewCity($city_id, $userinfo){
    	$info = $this->reviewCityModel->getReviewCityInfo($city_id);
    	
    	$ret = true;
    	if (!empty($info)) {
    		$ret = $this->reviewCityModel->deleteInfo($city_id);
    		if ($ret !== false) {
    			$info["visit_begin_date"] = $info["visit_begin"] ? date("Y-m-d", $info["visit_begin"]) : "";

    			$this->reviewCityModel->addCityLog([
    				"cs" => $city_id,
    				"operator" => $userinfo["id"],
    				"operator_name" => $userinfo["name"],
    				"info" => json_encode($info, 320),
    				"created_at" => time(),
    				"act_remark" => "取消回访城市",
    				"act_type" => 2,
    			]);
    		}
    	}

    	return $ret;
    }

    // 取消回访城市设置
    public function setReviewCity($city_id, $visit_begin, $mianji_min, $mianji_max, $userinfo){
    	try {
    		$this->reviewCityModel->startTrans();
    		$info = $this->reviewCityModel->getReviewCityInfo($city_id);
    		
    		$data = [
    			"cs" => $city_id,
    			"visit_begin" => strtotime($visit_begin),
    			"mianji_min" => round($mianji_min, 2),
    			"mianji_max" => round($mianji_max, 2),
    			"operator" => $userinfo["id"],
    			"updated_at" => time(),
    		];

    		if (!empty($info)) {
    			$ret = $this->reviewCityModel->updateInfo($city_id, $data);
    		} else {
    			$data["sync_state"] = 1;
    			$data["created_at"] = time();
    			$ret = $this->reviewCityModel->add($data);
    		}

    		if (empty($ret)) {
    			throw new Exception("设置回访城市失败");
    		}

    		$act_remark_temp = "设置回访城市，回访时间：%s，订单面积：%s 至 %s";
    		$act_remark = sprintf($act_remark_temp, $visit_begin, $data["mianji_min"], $data["mianji_max"] ? : "∞");

    		// 日志
    		$data["visit_begin_date"] = $visit_begin;
    		$this->reviewCityModel->addCityLog([
    			"cs" => $city_id,
    			"operator" => $userinfo["id"],
    			"operator_name" => $userinfo["name"],
    			"act_type" => 1,
    			"act_remark" => $act_remark,
    			"info" => json_encode($data, 320),
    			"created_at" => time(),
    		]);

    		$errcode = 0;
    		$errmsg = "请求成功";
    		$this->reviewCityModel->commit();
    	} catch (Exception $e) {
    		$errcode = 404;
    		$errmsg = $e->getMessage();
    		$this->reviewCityModel->rollback();
    	}

    	return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    public function getAllOpenCity(){
    	return $this->reviewCityModel->getAllOpenCity();
    }
}