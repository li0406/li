<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/17
 * Time: 14:23
 */

namespace Home\Model\Logic;

use Home\Model\Db\OrderReviewNewHistoryModel;

class OrderReviewNewHistoryLogicModel
{
    public $model;

    protected $orderReviewNewLogic;

    public function __construct()
    {
        $this->model = new OrderReviewNewHistoryModel();
        $this->orderReviewNewLogic = new OrderReviewNewLogicModel();
    }

    public function getAll(array $cond)
    {
        $data = $this->model->getAll($cond);
        $formatted = [];
        foreach ($data as $v => $value) {
            $formatted[] = $this->transformData($value);
        }
        return $formatted;
    }

    public function add(array $data)
    {
        return $this->model->add($data);
    }

    private function transformData($data)
    {
        $formatted = $data;
        $formatted['review_type_name'] = '';
        if (!empty($data['review_type'])) {
            $formatted['review_type_name'] = $this->orderReviewNewLogic->reviewType[$data['review_type']];
        }

        $formatted['next_visit_step_name'] = '';
        if (!empty($data['next_visit_step'])) {
            $formatted['next_visit_step_name'] = $this->orderReviewNewLogic->nextVisitStep[$data['next_visit_step']];
        }
        $formatted['next_visit_time'] = '';
        if (!empty($data['next_visit_time'])) {
            $formatted['next_visit_time'] = date('Y-m-d', $data['next_visit_time']);
        }

        $formatted['updated_at_time'] = date('Y-m-d H:i', $data['updated_at']);

        $keys = array_keys($formatted);
        foreach ($keys as $key) {
            $formatted[$key] = $formatted[$key] ?: '-';
        }
        return $formatted;
    }
}