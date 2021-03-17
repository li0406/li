<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/17
 * Time: 14:23
 */

namespace Home\Model\Logic;

use Home\Model\Db\OrderReviewNewRemarkModel;

class OrderReviewNewRemarkLogicModel
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderReviewNewRemarkModel();
    }

    public function getAll($reviewType='')
    {
        return $this->model->getAll($reviewType);
    }
}