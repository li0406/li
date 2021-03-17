<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/17
 * Time: 14:23
 */

namespace Home\Model\Logic;

use Home\Model\Db\OrderReviewNewApplyTelModel;
use Home\Model\Db\OrderReviewNewModel;
use Think\Exception;

class OrderReviewNewApplyLogicModel
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderReviewNewApplyTelModel();
    }

    public function getAll($orderId)
    {
        $cond['order_id'] = $orderId;
        return $this->model->getAll($cond);
    }

    public function isEnable($uid, $orderId)
    {
        $map['apply_id'] = ['EQ', $uid];
        $map['order_id'] = ['EQ', $orderId];
        $map['status'] = ['EQ', 2];
        $map['pass_time'] = ['GT', 0];
        $data = $this->model->getUserApplyByOrderid($map);
        if (!empty($data) && ($data['pass_time'] + 86400 * 7) >= strtotime('today')) {
            return true;
        }
        return false;
    }

    public function save(array $data)
    {
        $now = time();
        $admin = getAdminUser();
        $map['order_id'] = ['EQ', $data['id']];
        $map['apply_id'] = ['EQ', $admin['id']];
        $apply = $this->model->getUserApplyByOrderid($map);

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
            $result = $this->model->addOrdersApplyTel($save, ['id' => $apply['id']]);
        } else {
            $save = [
                'order_id' => $data['id'],
                'apply_id' => $admin['id'],
                'apply_reason' => $data['text'],
                'apply_time' => $now,
                'status' => 1
            ];
            $result = $this->model->addOrdersApplyTel($save);
        }
        return $result;
    }

    public function saveOrdersApplyTel($id, $save)
    {
        $result = $this->model->addOrdersApplyTel($save, ['id' => $id]);
        return $result;
    }
}