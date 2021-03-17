<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/17
 * Time: 14:23
 */

namespace Home\Model\Logic;

use Home\Model\Db\LogTelcenterReviewOrdercallModel;
use Home\Model\Db\OrderCompanyReviewModel;
use Home\Model\Db\OrderReviewApplyTelModel;
use Home\Model\Db\OrderReviewModel;
use Home\Model\Db\OrderReviewNewHistoryModel;
use Home\Model\Db\OrderReviewNewModel;
use Home\Model\Db\ReviewCityModel;
use Home\Model\Db\OrdersModel;
use Think\Exception;

class OrderReviewNewLogicModel
{

    protected $perCount = 10;

    protected $model;

    const REVIEW_TYPE_YCL_NUM = 1;
    const REVIEW_TYPE_XD_NUM = 2;
    const REVIEW_TYPE_YLF_NUM = 3;
    const REVIEW_TYPE_WLF_NUM = 4;
    const REVIEW_TYPE_YQD_NUM = 5;
    const REVIEW_TYPE_DDD_NUM = 6;
    const REVIEW_TYPE_ZJD_NUM = 7;
    const REVIEW_TYPE_GZD_NUM = 8;

    public $reviewType = [
        SELF::REVIEW_TYPE_YCL_NUM => '预处理',
        SELF::REVIEW_TYPE_XD_NUM => '新单',
        SELF::REVIEW_TYPE_GZD_NUM => '跟踪单',
        SELF::REVIEW_TYPE_YLF_NUM => '已量房',
        SELF::REVIEW_TYPE_WLF_NUM => '未量房',
        SELF::REVIEW_TYPE_YQD_NUM => '已签单',
        SELF::REVIEW_TYPE_DDD_NUM => '待定单',
        SELF::REVIEW_TYPE_ZJD_NUM => '终结单'
    ];

    public $nextVisitStep = [
        1 => '促量房',
        2 => '促到店',
        3 => '促签单',
        4 => '推荐服务',
    ];

    public $orderType = [
        1 => '分单',
        2 => '赠单'
    ];

    /**
     * @var ReviewCityModel
     */
    protected $reviewCityModel;

    public $logTelReviewOrderCallModel;

    public $logTelOrderCallModel;

    protected $orderCompanyReviewModel;

    protected $orderReviewHistoryModel;
    protected $orderReviewNewApplyLogicModel;

    protected $uid;
    
    public function __construct($uid = 0)
    {
        $this->uid = $uid;
        $this->model = new OrderReviewNewModel();
        $this->reviewCityModel = new ReviewCityModel();
        $this->logTelReviewOrderCallModel = new LogTelcenterReviewOrdercallModel();
        $this->orderCompanyReviewModel = new OrderCompanyReviewModel();
        $this->orderReviewNewApplyLogicModel = new OrderReviewNewApplyLogicModel();
        $this->orderReviewHistoryModel = new OrderReviewNewHistoryModel();
    }

    public function getAll(array $params, $kfIds = [])
    {
        $reqParams = $params;
        if (!empty($params['end'])) {
            $reqParams['end'] = strtotime($params['end'] . ' 23:59:59');
        }

        //订单总览
        if($params['review_type'] == 999){
            unset($reqParams['review_type']);
        }

        if (!empty($params['start'])) {
            $reqParams['start'] = strtotime($params['start']);
        }

        $uid = $params['user_id'];
    
        $map = $this->model->setMap($reqParams);
        $count = $this->model->getAllCount($uid, $map);

        $ret = ['page' => '', 'list' => []];
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $this->perCount);
            $result = $this->model->getAll($uid, $map, $page->firstRow, $page->listRows);
            $list = $this->transform($result, $uid, $kfIds);

            $ret['page'] = $page->show();
            $ret['list'] = $list;
        }

        return $ret;
    }

    /**
     * 清洗数据
     * @param array $data
     * @return array
     */
    private function transform(array $list, $uid, $kfIds = [], $actfrom = 1)
    {
        if (empty($list)) {
            return [];
        }

        if ($actfrom == 1) {
            $orderIds = array_column($list, "order_id");
            $logTelcenterLogic = new LogTelcenterOrdercallLogicModel();
            $telLogList = $logTelcenterLogic->getReviewOrderCallCount($orderIds, $kfIds);
        }

        foreach ($list as $key => &$data) {
            $order_id = $data["order_id"];
            $data['uid'] = $uid;

            $data['tel_log_count'] = $telLogList[$order_id]["tel_log_count"] ?? 0;

            // 是否显示跟单
            $data['is_track'] = 0;
            $today = date('Y-m-d');
            $isTrack = $this->model->hasOrderTrack($data['order_id'], $today);
            $isCompanyReview = $this->model->hasOrderCompanyReview($data['order_id'], $today);
            if ($isTrack) {
                $data['is_track'] = 1;
            } elseif ($isCompanyReview) {
                $data['is_track'] = 1;
            }

            //获取最新的量房时间
            $data['is_lf'] = $this->orderCompanyReviewModel->isLf($data['order_id']);

            //是否量房
            $data['is_lf_name'] = '未量房';
            if ($data['is_lf'] > 0) {
                $data['is_lf_name'] = '已量房';
            }

            //是否显示号码

            //获取到自己的申请纪录，且在7天内的
            $isApplySuccess = $this->orderReviewNewApplyLogicModel->isEnable($this->uid,$data['order_id']);
            $data['is_show_number'] = 0;
            if (
                $isApplySuccess
            ) {
                $data['is_show_number'] = 1;
                $data['tel'] = $data['tel8'];
            }

            //是否显示手机号码旁边的眼睛
            $data['is_show_eye'] = 0;
            if (in_array($data['apply_tel_status'], [2, 3])) {
                $data['is_show_eye'] = 2;
            } elseif ($data['apply_tel_status'] == 1) {
                $data['is_show_eye'] = 1;
            }

            if ($data['next_visit_time']) {
                $data['next_visit_time'] = date('Y-m-d', $data['next_visit_time']);
            }
            $data['updated_at_time'] = date('Y-m-d H:i:s', $data['updated_at']);

            //订单回访状态
            $data['review_type_name'] = $this->reviewType[$data['review_type']];

            $keys = array_keys($data);
            foreach ($keys as $key) {
                $data[$key] = $data[$key] ?: '';
            }

            $data['type_fw_str'] = $this->orderType[$data['type_fw']];
        }

        return $list;
    }

    public function getDetail($uid, $orderId, $kfIds = [])
    {
        $reqParams['search'] = $orderId;
        $map = $this->model->setMap($reqParams);
        $data = $this->model->getDetail($uid, $map);

        if (!empty($data)){
            $list = $this->transform([$data], $uid, $kfIds, 2);
            $data = $list[0];
        }
        
        return $data;
    }

    /**
     * 保存回访信息
     * @param array $params
     * @throws Exception
     */
    public function update($uid, array $params, $return_order = false)
    {
        try {
            $this->model->startTrans();

            //0.获取数据
            // 1.更新当前回访订单的数据
            $reqParams['search'] = $params['order_id'];
            $map = $this->model->setMap($reqParams);
            $data = $this->model->getDetail($uid,$map);
            if (empty($data)) {
                throw new Exception('没有该回访订单数据', 404);
            }

            // 只有订单为预处理时候，回访状态才可更改为新单
            // if ($params['review_type'] == 2 && $data['review_type'] != 1) {
            //     throw new Exception('只有订单为预处理的时候，回访状态才可更改为新单', 404);
            // }

            // 下次回访时间：客服选择的下次回访时间，精确到日，不可超出当前回访时间30天，必选（提示：请选择下次回访时间）
            $params['next_visit_time'] = strtotime($params['next_visit_time']);
            $curTime  = time();
            if ($params['next_visit_time']) {
                $timeDiff = ceil(abs($curTime - $params['next_visit_time']) / 86400);
                if ($timeDiff > 30) {
                    throw new Exception('请选择下次回访时间', 404);
                }
            }


            $adminUser = getAdminUser();

            $formatted = [
                'review_type' => (int)$params['review_type'],
                'remark_type' => (int)$params['remark_type'],
                'review_record' => $params['review_record'],
                'review_feedback' => $params['review_feedback'],
                // 'next_visit_step' => (int)$params['next_visit_step'],
                'next_visit_time' => (int)$params['next_visit_time'],
                'review_uid' => $adminUser['id'],
                'review_name' => $adminUser['user'],
                'updated_at' => time(),
                'read_mark' => 1,
            ];

            // 924 补充逻辑 , 客服重新回访后处理操作
            $formatted['visit_content_sales'] = $formatted['review_feedback']; //更新销售推送装修公司的显示数据
            $formatted['visit_push'] = 1; //更新销售推送装修公司的状态 为未推送
            $formatted['push_company'] = ''; //清空销售推送的装修公司

            $saveRet = $this->model->editReviewInfo($params['order_id'], $formatted);
            if (empty($saveRet)) {
                throw new Exception('保存回访信息失败', 400);
            }
            //2.插入一条新的纪录到 历史回访订单数据中
            $historyData = [
                'order_id' => (int)$data['order_id'],
                'review_type' => (int)$formatted['review_type'],
                'remark_type' => (int)$formatted['remark_type'],
                'review_record' => $formatted['review_record'],
                'review_feedback' => $formatted['review_feedback'],
                'next_visit_step' => (int)$formatted['next_visit_step'],
                'next_visit_time' => (int)$formatted['next_visit_time'],
                'review_uid' => $formatted['review_uid'],
                'review_name' => $formatted['review_name'],
                'visit_push' => $data['visit_push'],
                'push_company' => $data['push_company'],
                'visit_content_sales' => $data['visit_content_sales'],
                'created_at' => $data['created_at'],
                'updated_at' => $formatted['updated_at'],
                'is_delete' => $data['is_delete'],
            ];
            $inserted = $this->orderReviewHistoryModel->add($historyData);

            if (empty($inserted)) {
                throw new Exception('保存回访信息失败', 400);
            }
            $this->model->commit();
        } catch (Exception $e) {
            $this->model->rollback();
            throw $e;
        }

        return $return_order ? $data : $data["tel8"];
    }

    public function getOrderQiandanCompany($order_id){
        $ordersModel = new OrdersModel();
        $qiandan =  $ordersModel->getQiandanCompany($order_id);
        if($qiandan){
            $qiandan['qiandan_date'] = date('Y-m-d', $qiandan['qiandan_addtime']);    
        }
        
        return $qiandan;
    }
}