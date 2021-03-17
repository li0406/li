<?php
//订单显号记录表
namespace Home\Model;

use Think\Model;

class OrdersApplyTelModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * [getApplyTelListByOrdersId 获取显号列表]
     * @param  integer $orders_id      [订单ID]
     * @param  boolean $add_time_start [开始添加时间]
     * @param  boolean $status         [状态，是否审核通过]
     * @return [type]                  [description]
     */
    public function getApplyTelListByOrdersId($orders_id = 0, $add_time_start = false, $status = false)
    {
        if (empty($orders_id)) {
            return array();
        }

        $map['orders_id'] = $orders_id;

        if ($add_time_start != false) {
            $map['apply_time'] = array('EGT', $add_time_start);
        }

        if ($status != false) {
            $map['status'] = intval($status);
        }

        return M('orders_apply_tel')->alias('t')
                                    ->field('t.id,t.status,t.entrance, z.name AS apply_name, t.apply_reason, t.apply_time, y.name AS passer_name, t.pass_time')
                                    ->join('LEFT JOIN qz_adminuser AS z ON z.id = t.apply_id')
                                    ->join('LEFT JOIN qz_adminuser AS y ON y.id = t.passer_id')
                                    ->where($map)
                                    ->order('status, id DESC')
                                    ->select();
    }

    /**
     * [addOrdersApplyTel 新增申请记录]
     * @param [type] $save [存储的数据]
     */
    public function addOrdersApplyTel($save)
    {
        return M('orders_apply_tel')->add($save);
    }

    /**
     * [saveOrdersApplyTel 更新显号审核表]
     * @param  [type] $id   [自增ID]
     * @param  [type] $save [存储的数据]
     * @return [type]       [description]
     */
    public function saveOrdersApplyTel($id, $save)
    {
        if (empty($id)) {
            return false;
        }

        $map = array(
            'id'=>$id
        );

        return M('orders_apply_tel')->where($map)->save($save);
    }

    /**
     * [getApplyTelListByOrdersIdAndApplyId 根据订单id和申请人id获取显号记录]
     * @param  string $orders_id [订单id]
     * @param  string $apply_id  [申请人id]
     * @return [type]            [description]
     */
    public function getApplyTelByOrdersIdAndApplyId($orders_id = '', $apply_id = '')
    {
        if (empty($orders_id) || empty($apply_id)) {
            return false;
        }

        $map = array(
            'orders_id' => $orders_id,
            'apply_id' => $apply_id
        );

        return M('orders_apply_tel')->where($map)->find();
    }

    /**
     * @param string $where
     * @param string $groupBy
     * @return mixed
     */
    public function getApplyTel($where = '')
    {
        $where['a.stat'] = ['eq',1];
//        $where["a.uid"] = array(
//            array("EQ",2),
//            array("EQ",84),
//            "or"
//        );
        return M('orders_apply_tel')->alias('o')
            ->field('o.id,o.apply_id,a.uid dd,o.status,a.kfgroup,a.kfmanager,a.name uname,count(o.id) totalcount')
            ->join('inner join qz_adminuser a on a.id = o.apply_id')
            ->where($where)
            ->group('o.apply_id,o.status')
            ->select();
    }

    /**
     * 获取客服申请次数
     * @param string $where
     * @return mixed
     */
    public function getApplyTelNum($where = '')
    {
        $where['a.stat'] = ['eq',1];
        return M('orders_apply_tel')->alias('o')
            ->field('o.id,o.apply_id,o.orders_id,a.name uname,count(o.orders_id) num_count')
            ->join('inner join qz_adminuser a on a.id = o.apply_id')
            ->where($where)
            ->group('o.orders_id')
            ->select();
    }

    public function getApplyTelByAppId($where,$app_id,$status = '')
    {
        if ($status == 0) {
            //不通过个数
            $where['o.status'] = ['in', '1,3,4'];
        } elseif ($status == 1) {
            //通过个数
            $where['o.status'] = ['eq', 2];
        }
        $where['o.apply_id'] = ['eq', $app_id];
        return M('orders_apply_tel')->alias('o')
            ->where($where)
            ->count();
    }

    public function getApplyTelByPassId($where)
    {
        $where['a.stat'] = ['eq',1];
        return M('orders_apply_tel')->alias('o')
            ->field('o.id,o.status,o.passer_id,b.kfgroup,b.kfmanager,b.name uname,count(o.id) totalcount')
            ->join('inner join qz_adminuser a on a.id = o.apply_id')
            ->join('inner join qz_adminuser b on b.id = o.passer_id')
            ->where($where)
            ->group('o.passer_id,o.status')
            ->select();
    }

}