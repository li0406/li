<?php
/**
 * 新回访推送装修公司数据表
 */

namespace Common\Model\Db;

use Think\Model;

class OrderReviewNewPushModel extends Model
{
    public function getOrderPushList($company_id, $order_id)
    {
        $where = [
            'order_id' => $order_id,
            'company_id' => $company_id,
            'visit_push' => 2,
        ];
        return $this->field('visit_step,visit_content,visit_content_sales,created_at')->where($where)->select();
    }
}