<?php


namespace Home\Controller;


use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderReviewNewHistoryLogicModel;
use Think\Exception;

class OrderReviewNewHistoryController extends HomeBaseController
{
    protected $logic;

    public function __construct()
    {
        parent::__construct();
        $this->logic = new OrderReviewNewHistoryLogicModel();
    }

    public function getAll()
    {
        try {
            $cond['order_id'] = I('get.order_id');
            if (empty($cond['order_id'])) {
                throw new Exception('请求参数不合法', ApiConfig::PARAMETER_ILLEGAL);
            }
            $data = $this->logic->getAll($cond);
            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = '';
        }
        $ret = ['error_code' => $code, 'error_msg' => $msg, 'data' => $data];
        $this->ajaxReturn($ret);
    }
}