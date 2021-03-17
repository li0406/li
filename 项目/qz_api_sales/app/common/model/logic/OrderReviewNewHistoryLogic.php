<?php


namespace app\common\model\logic;


use app\common\model\db\OrderReviewNewHistory;
use app\index\enum\CompanyVisitCodeEnum;

class OrderReviewNewHistoryLogic
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderReviewNewHistory();
    }

    public function getAll($orderId)
    {
        $data = $this->model->getAll($orderId);
        $formatted = [];
        foreach ($data as $k => $v) {
            $formatted[] = $this->transformData($v);
        }
        return $formatted;
    }

    /**
     * 根据订单id查询新回访中是否有该订单
     * @param $ordersId
     * @return array
     */
    public function hasOrders($ordersId)
    {
        return $this->model->hasOrders($ordersId);
    }

    private function transformData(array $data)
    {
        $formatted = [];
        if (empty($data)) {
            return [];
        }
        $formatted['visit_date'] = date('Y-m-d H:i:s', $data['updated_at']);
        $formatted['visit_step_name'] = CompanyVisitCodeEnum::getItem("visit_step", $data["next_visit_step"]);
        $formatted['visit_content'] = $data['review_feedback'];
        $formatted['company_jc'] = '';
        return $formatted;
    }
}