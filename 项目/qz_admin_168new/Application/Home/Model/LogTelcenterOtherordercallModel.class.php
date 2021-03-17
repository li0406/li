<?php

namespace Home\Model;

Use Think\Model;

class LogTelcenterOtherordercallModel extends Model{

    protected $autoCheckFields = false;

    public function addLog($data)
    {
        return M("log_telcenter_otherordercall")->add($data);
    }
    /**
     * 根据部门获取通话记录数量
     * @param string $orderid    订单ID
     * @param array  $department 部门ID数组
     */
    public function LogTelcenterOtherordercallCountByDepartment($orderid = '', $department = array())
    {
        if (!empty($orderid)) {
            $map['t.orderid'] = $orderid;
        }
        if (!empty($department)) {
            if (!is_array($department)) {
                $department = array(intval($department));
            }
            $map['d.department_id'] = array('IN', $department);
        }
        return M('log_telcenter_otherordercall')->alias('t')
                                                ->join('INNER JOIN qz_adminuser AS u ON u.id = t.admin_id')
                                                ->join('INNER JOIN qz_role_department AS d ON d.role_id = u.uid')
                                                ->where($map)
                                                ->count();
    }

    /**
     * 根据部门获取通话记录
     * @param string $orderid    订单ID
     * @param array  $department 部门ID数组
     * @param integer $start     开始位置
     * @param integer $limit     查询数量
     */
    public function LogTelcenterOtherordercallListByDepartment($orderid = '', $department = array(), $start = 0, $limit = 10)
    {
        if (!empty($orderid)) {
            $map['t.orderid'] = $orderid;
        }
        if (!empty($department)) {
            if (!is_array($department)) {
                $department = array(intval($department));
            }
            $map['d.department_id'] = array('IN', $department);
        }
        return M('log_telcenter_otherordercall')->alias('t')
                                                ->field('t.orderid, t.callSid, t.admin_user, t.time_add')
                                                ->join('INNER JOIN qz_adminuser AS u ON u.id = t.admin_id')
                                                ->join('INNER JOIN qz_role_department AS d ON d.role_id = u.uid')
                                                ->where($map)
                                                ->order('t.id DESC')
                                                ->limit($start, $limit)
                                                ->select();
    }

    /**
     * 获取电话记录数量
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getOrderTelRecordCount($ids)
    {
        $map = array(
            "orderid" => array("IN",$ids)
        );
        return M("log_telcenter_otherordercall")->where($map)->field("orderid,count(orderid) as count")->group("orderid")->select();
    }

    /**
     * 通过订单号获取 通话次数
     * @param    $orderId 订单号
     * @return   通话列表 或 false
     */
    public function getOrderCallListByOrderId($orderId)
    {
        if (!empty($orderId)) {
            $map = array(
                'o.orderid' => $orderId
            );

            $result = M('log_telcenter_otherordercall')->alias('o')
                                                  ->field('*, o.orderid AS orders_id')
                                                  ->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                                                  ->where($map)
                                                  ->order('t.time_add')
                                                  ->select();
            return $result;
        }
        return false;
    }
}