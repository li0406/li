<?php
//订单表
namespace Home\Model;
use Think\Model;

class OrderPoolModel extends Model
{
    protected $autoCheckFields = false;

    public function getOrderPool($orderid)
    {
        $map = array(
            "orderid" => array("EQ", $orderid)
        );
        return M("order_pool")->where($map)->find();
    }

    public function addOrder($data)
    {
        
        return M("order_pool")->add($data);
    }

    public function updateOrder($orderid, $data)
    {
        $map = array(
            "orderid" => array("EQ", $orderid)
        );
        return M("order_pool")->where($map)->save($data);
    }

    /**
     * 删除订单信息
     * @param  array $orderid [订单ID]
     * @return bool
     */
    public function delOrder($orderid)
    {
        $map = array(
            "orderid" => array("IN",$orderid)
        );
        return M('order_pool')->where($map)->delete();
    }


    /**
     * [getNoOwnerNewOrderCount 获取没有认领人的新订单的数量]
     * @return [type] [description]
     */
    public function getNoOwnerNewOrderCount($cids = false){
        $map = array(
            'p.status' => 1,
            'p.op_uid' => 0,
            'o.on' => 0,
            'o.on_sub' => 10,
            'o.time_real' => array(array('EGT', strtotime(date('Y-m-d 00:00:00') . '-30 days')),array('LT', time() - 180),'AND'),
            'b.status' => array(array('EQ', 0),array('EXP',' IS NULL '),'OR')
        );
        if ((false !== $cids) && !empty($cids)) {
            if (!is_array($cids)) {
                $cids = array($cids);
            }
            $map['o.cs'] = array('IN', $cids);
        }
        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o  FORCE INDEX(idx_time_real_cs) ON o.id = p.orderid')
                                ->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getUnCalledNewOrderCount 获取当前已抢未拨打新订单总数]
     * @param  boolean $cids [城市ID数组]
     * @return [type]        [description]
     */
    public function getUnCalledNewOrderCount($cids = false){
        $map = array(
            'p.status' => 0,
            'p.op_uid' => array('NEQ', 0),
            'o.on' => 0,
            'o.on_sub' => 10,
            'o.time_real' => array(array('EGT', strtotime(date('Y-m-d 00:00:00') . '-30 days')),array('ELT', time() - 180),'AND'),
            'b.status' => array(array('EQ', 0),array('EXP',' IS NULL '),'OR'),
            't.id' => array('EXP', ' IS NULL ')
        );
        if ((false !== $cids) && !empty($cids)) {
            if (!is_array($cids)) {
                $cids = array($cids);
            }
            $map['o.cs'] = array('IN', $cids);
        }
        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o FORCE INDEX(idx_time_real_cs) ON o.id = p.orderid')
                                ->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt')
                                ->join('LEFT JOIN qz_log_telcenter_ordercall AS t ON t.orderid = o.id')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getUnCalledNewOrderList 获取当前已抢未拨打新订单]
     * @param  boolean $cids [城市ID数组]
     * @return [type]        [description]
     */
    public function getUnCalledNewOrderList($cids = false, $field, $group, $page = 0, $limit = 20){
        $map = array(
            'p.status' => 0,
            'p.op_uid' => array('NEQ', 0),
            'o.on' => 0,
            'o.on_sub' => 10,
            'o.time_real' => array(array('EGT', strtotime(date('Y-m-d 00:00:00') . '-30 days')), array('ELT', time() - 180), 'AND'),
            'b.status' => array(array('EQ', 0), array('EXP', ' IS NULL '), 'OR'),
            't.id' => array('EXP', ' IS NULL ')
        );
        if ((false !== $cids) && !empty($cids)) {
            if (!is_array($cids)) {
                $cids = array($cids);
            }
            $map['o.cs'] = array('IN', $cids);
        }
        $count = M('order_pool')->alias('p')
            ->field($field)
            ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
            ->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt')
            ->join('LEFT JOIN qz_quyu q ON q.cid = o.cs')
            ->join('LEFT JOIN qz_area a ON a.qz_areaid = o.qx')
            ->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id')
            ->where($map)
            ->limit($page,$limit)
            ->group($group)
            ->select();
        return $count;
    }

    /**
     * [getNoOwnerNewOrderList 获取没有认领人的新订单的数量]
     * @return [type] [description]
     */
    public function getNoOwnerNewOrderList($cids = false, $limit = 1, $field = 'p.orderid', $order='o.time_real DESC',$page = 0,$group = ''){
        //查询没有认领人的新订单,并且时间在3分钟之前的
        $map = array(
            'p.status' => 1,
            'p.op_uid' => 0,
            'o.on' => 0,
            'o.on_sub' => 10,
            'o.time_real' => array(array('EGT', strtotime(date('Y-m-d 00:00:00') . '-30 days')),array('ELT', time() - 180),'AND'),
            'o.cs' => array('IN', $cids),
            'b.status' => array(array('EQ', 0),array('EXP',' IS NULL '),'OR'),
            'o.source' => array("NEQ",164)
        );
        if ((false !== $cids) && !empty($cids)) {
            $map['o.cs'] = array('IN', $cids);
        }

        $result = M('order_pool')->alias('p')
                                 ->field($field)
                                 ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                 ->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt')
                                 ->join('LEFT JOIN qz_quyu q ON q.cid = o.cs')
                                 ->join('LEFT JOIN qz_area a ON a.qz_areaid = o.qx')
                                 ->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id')
                                 ->where($map)
                                 ->limit($page,$limit)
                                 ->group($group)
                                 ->order($order)
                                 ->select();
        return $result;
    }

    /**
     * [getUnfinishedOrderCountByUid 根据用户ID获取该用户未完成的订单量：新单+次新单+扫单+被撤订单*系数+当天无效单*系数-当天有效单*系数（去除被撤订单）]
     * @param  [type] $adminId [用户ID]
     * @return [type]          [description]
     */
    public function getUnfinishedOrderCountByUid($adminId)
    {
        if (empty($adminId)) {
            return false;
        }

        //新单+次新单+扫单+待定单
        $map = array(
            'o.on' => 0,
            'o.on_sub' => array('IN', array(8,9,10)),
            'p.op_uid' => $adminId
        );
        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                ->where($map)
                                ->count();

        //+被撤订单*系数
        $result = $this->getRevokedOrderCount($adminId);
        $count = $count + $result * OP('revoke_rete');

        //+当天无效单*系数
        $result = $this->getTodayInvalidOrderCount($adminId);
        $count = $count + $result * OP('invalid_rate');

        //-当天有效单*系数（去除被撤订单）
        $result = $this->getTodayEffectiveOrderCount($adminId);
        $count = $count - $result * OP('effective_rate');

        return $count;
    }

    /**
     * [getRetractedOrderCount 根据用户ID获取被撤回订单数量]
     * @param  [type] $adminId [用户ID]
     * @return [type]          [description]
     */
    public function getRevokedOrderCount($adminId){
        if (empty($adminId)) {
            return false;
        }

        $map = array(
            'p.op_uid' => $adminId,
            'o.on' => '99'
        );

        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getTodayInvalidOrderCount 根据用户ID获取当天无效订单数量]
     * @param  [type] $adminId [用户ID]
     * @return [type]          [description]
     */
    public function getTodayInvalidOrderCount($adminId){
        if (empty($adminId)) {
            return false;
        }

        $map = array(
            'p.op_uid' => $adminId,
            'o.on' => '5',
            'p.addtime' => array('EGT', strtotime(date('Y-m-d 00:00:00')))
        );

        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getTodayEffectiveOrderCount 根据用户ID获取当天有效单数量]
     * @param  [type] $adminId [用户ID]
     * @return [type]          [description]
     */
    public function getTodayEffectiveOrderCount($adminId){
        if (empty($adminId)) {
            return false;
        }

        $map = array(
            'p.op_uid' => $adminId,
            'o.on' => '4',
            'p.addtime' => array('EGT', strtotime(date('Y-m-d 00:00:00')))
        );

        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getUnfinishedNewOrderCountByUid 根据用户ID获取该用户未完成的新单量]
     * @param  [type] $adminId [用户ID]
     * @return [type]          [description]
     */
    public function getUnfinishedNewOrderCountByUid($adminId)
    {
        $map = array(
            'o.on' => 0,
            'o.on_sub' => 10,
            'p.op_uid' => $adminId
        );
        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getCanGetNewOrderCount 获取当天可获取订单总数量，已开站的城市且不在黑名单的订单总数量]
     * @param  boolean $cids  [城市ID]
     * @param  string  $start [开始时间]
     * @param  string  $end   [结束时间]
     * @return [type]         [description]
     */
    public function getCanGetNewOrderCount($cids = false, $start = '', $end = ''){
        if (empty($start) || empty($end)) {
            return false;
        }
        $map = array(
            'o.time_real' => array(array('EGT', $start),array('LT', $end),'AND'),
            'b.status' => array(array('EQ', 0),array('EXP',' IS NULL '),'OR')
        );
        if ((false !== $cids) && !empty($cids)) {
            if (!is_array($cids)) {
                $cids = array($cids);
            }
            $map['o.cs'] = array('IN', $cids);
        }
        $count = M('order_pool')->alias('p')
                                ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
                                ->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt')
                                ->where($map)
                                ->count();
        return $count;
    }

    /**
     * [getCanGetNewOrderList 获取当天可获取订单总数量，已开站的城市且不在黑名单的订单]
     * @param  boolean $cids  [城市ID]
     * @param  string  $start [开始时间]
     * @param  string  $end   [结束时间]
     * @return [type]         [description]
     */
    public function getCanGetNewOrderList($cids = false, $start = '', $end = '', $field = '', $page = 0, $limit = 20, $group = ''){
        if (empty($start) || empty($end)) {
            return false;
        }
        $map = array(
            'o.time_real' => array(array('EGT', $start), array('LT', $end), 'AND'),
            'b.status' => array(array('EQ', 0), array('EXP', ' IS NULL '), 'OR')
        );
        if ((false !== $cids) && !empty($cids)) {
            if (!is_array($cids)) {
                $cids = array($cids);
            }
            $map['o.cs'] = array('IN', $cids);
        }
        $count = M('order_pool')->alias('p')
            ->field($field)
            ->join('INNER JOIN qz_orders AS o ON o.id = p.orderid')
            ->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt')
            ->join('LEFT JOIN qz_quyu q ON q.cid = o.cs')
            ->join('LEFT JOIN qz_area a ON a.qz_areaid = o.qx')
            ->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id')
            ->where($map)
            ->limit($page, $limit)
            ->group($group)
            ->select();
        return $count;
    }

    /**
     * 获取客服数据分析数据
     * @param  [type] $id      [description]
     * @param  [type] $group   [description]
     * @param  [type] $manager [description]
     * @param  [type] $begin   [description]
     * @param  [type] $end     [description]
     * @return [type]          [description]
     */
    public function getcustomerorderanalysis($begin,$end)
    {
        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("neq","0"),
            "o.time_real" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

        $buildSql = M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_adminuser u on u.id = a.op_uid and u.uid = 2')
                              ->group('a.op_uid,date,o.cs')
                              ->field("a.op_uid,a.op_name,count(a.orderid) as `all`,case
                                    when o.time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,'%Y-%m-%d'),' ','17:30:00'))
                                    and o.time_real < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,'%Y-%m-%d'),' ','23:59:59'))
                                    then DATE_ADD(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),INTERVAL 1 day)
                                    else FROM_UNIXTIME(a.addtime,'%Y-%m-%d')
                                    end as date,o.cs,u.kfgroup,u.kfmanager")
                              ->order("o.id desc,date")
                              ->buildSql();

        $list1 =  M("order_pool")->table($buildSql)->alias("t")
                                 ->join("join qz_quyu as q on q.cid = t.cs")
                                 ->join('join qz_adminuser m on m.id = substring_index(t.kfmanager,",",1)')
                                 ->field("t.*,q.cname,m.id as kfmanager,m.name as manager")
                                 ->select();

        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            )
        );
        $buildSql = M("order_csos_new")->where($map)->alias("a")
                                      ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                                      ->join('join qz_adminuser u on u.id = a.user_id and u.uid = 2')
                                      ->field("a.user_id,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,FROM_UNIXTIME(a.lasttime,'%Y-%m-%d') as date,o.cs,u.name,u.kfgroup,u.kfmanager")
                                      ->group("a.user_id,date,o.cs")
                                      ->order("o.id desc,date")
                                      ->buildSql();
        $list2 =  M("order_csos_new")->table($buildSql)->alias("t")
                                 ->join("join qz_quyu as q on q.cid = t.cs")
                                 ->join('join qz_adminuser m on m.id = substring_index(t.kfmanager,",",1)')
                                 ->field("t.*,q.cname,m.id as kfmanager,m.name as manager")
                                 ->select();
        return array($list1,$list2);
    }

    /*
     * [orderStatList description]
     * @param  array  $user_id         [客服ID数组]
     * @param  array  $cs              [筛选城市]
     * @param  [type] $time_real_start [订单发布开始时间]
     * @param  [type] $time_real_end   [订单发布结束时间]
     * @return [type]                  [description]
     */
    public function orderStatList($user_id = array(), $cs = array(), $time_real_start, $time_real_end)
    {
        if ($user_id === false) {
            return array();
        }

        if (!empty($user_id)) {
            if (!is_array($user_id)) {
                $user_id = array_filter(explode(',', $user_id));
            }
            $map['p.op_uid'] = array('IN', $user_id);
        } else {
            $map['p.op_uid'] = array('NEQ', '0');
        }

        if (!empty($cs)) {
            if (!is_array($cs)) {
                $cs = array_filter(explode(',', $cs));
            }
            $map['o.cs'] = array('IN', $cs);
        }

        //处理时间范围，默认为当月，左闭右开
        if (!empty($time_real_start) && !empty($time_real_end)) {
            $map['o.time_real'] = array(
                array('EGT', $time_real_start),
                array('LT', $time_real_end)
            );
        } elseif (!empty($time_real_start) && empty($time_real_end)) {
            $map['o.time_real'] = array(
                array('EGT', strtotime(date('Y-m-01 00:00:00'))),
                array('LT', strtotime(date('Y-m-01 00:00:00', strtotime('+1 month'))))
            );
        }

        return M('order_pool')->alias('p')
                              ->field('p.op_uid, o.on, o.on_sub, o.type_fw, o.cs, sum(IF(c.orderid is null,0,1)) AS ch_count')
                              ->join('qz_orders AS o ON o.id = p.orderid')
                              ->join('LEFT JOIN qz_log_order_csos AS c ON (c.orderid = p.orderid AND c.new_on = 99 AND c.old_on = 4)')
                              ->where($map)
                              ->group('p.orderid')
                              ->select();
    }

    /**
     * 获取客服操作订单信息
     * @param  [type] $id      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服师]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @return mixed
     */
    public function getKfOrdereffective($id,$group,$manager,$begin,$end)
    {
        $map = array(
            "a.op_uid" => array("neq","0"),
            "a.addtime" => array(
                array("EGT",$begin),
                array("LT",$end)
            )
        );

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_adminuser u on u.id = a.op_uid')
                              ->group('a.op_uid')
                              ->field("a.op_uid,a.op_name,count(a.orderid) as `all`")
                              ->buildSql();

        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($id)) {
            $map["a.user_id"] = array("EQ",$id);
        }

        $nextSql = M("order_csos_new")->where($map)->alias("a")
                                      ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                                      ->join('left join qz_order_rob_pool rp on rp.order_id = o.id and rp.is_rob = 2 and rp.status = 2')
                                      ->field("a.user_id,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,count(if(rp.status <> 0 and  rp.status is not null && rp.status <> o.type_fw,1,null)) as zentofen")
                                      ->group("a.user_id")
                                      ->buildSql();
        return M("order_pool")->table($buildSql)->alias("t")
                       ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id")
                       ->field("t.*,t1.fen,t1.zen,t1.zentofen")
                       ->select();

    }

    /**
     * 获取客服量房操作订单信息
     * @param  [type] $id      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服师]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @return [type]          [description]
     */
    public function getKfOrderliangfang($id,$group,$manager,$begin,$end,$type)
    {
        $monthEnd = strtotime(date("Y-m-d", $end) . " 17:30:00");
        $monthBegin = strtotime(date("Y-m-d", strtotime("-1 day", $begin)). " 17:30:00");

        //获取发单量
        if($type == 1){
            $map = array(
                "a.op_uid" => array("neq","0"),
                "a.addtime" => array(
                    array("EGT",$monthBegin),
                    array("LT",$monthEnd)
                )
            );
            if (!empty($id)) {
                $map["a.op_uid"] = array("EQ",$id);
            }
            if (!empty($group)) {
                $map["u.kfgroup"] = array("EQ",$group);
            }
            if (!empty($manager)) {
                $map["_string"] = "find_in_set($manager,u.kfmanager)";
            }
            $buildSql = M("order_pool")->where($map)->alias("a")
                ->join('join qz_orders o on o.id = a.orderid')
                ->join('join qz_adminuser u on u.id = a.op_uid')
                ->join('left join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
                ->join('join qz_adminuser u2 on u.kfgroup = u2.kfgroup and u2.uid = 31')
                ->group('a.op_uid')
                ->field("a.op_uid,a.op_name,count(a.orderid) as `all`")
                ->buildSql();

            $map = array(
                "a.lasttime" => array(
                    array("EGT",$begin),
                    array("ELT",$end)
                )
            );
            if (!empty($id)) {
                $map["a.user_id"] = array("EQ",$id);
            }
            $nextSql = M("order_csos_new")->where($map)->alias("a")
                ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                ->field("a.user_id,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen")
                ->group("a.user_id")
                ->buildSql();
            return M("order_pool")->table($buildSql)->alias("t")
                ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id")
                ->field("t.*,t1.fen,t1.zen")
                ->select();
        //获取客服的量房数
        }else if($type == 2){
            $map = array(
                "r.time" => array(
                    array("EGT", $begin),
                    array("ELT", $end)
                ),
                "r.status" => array("IN",array(1,2,4)),
                "l.id" => array("exp", 'is NULL')
            );
            if (!empty($id)) {
                $map["a.user_id"] = array("EQ",$id);
            }
            $tempSql = M("order_company_review")->where($map)->alias("r")
                ->join("join qz_orders o on o.id = r.orderid and o.on = 4 and o.type_fw in (1,2)")
                ->join("left join qz_order_csos_new a ON a.order_id = r.orderid")
                ->join("left join qz_company_liangfang l ON l.order_id = r.orderid")
                ->field("a.user_id,sum(IF (o.ON = 4 AND o.type_fw = 1,1,NULL)) AS scfenlfNum,sum(IF (o.ON = 4 AND o.type_fw = 2,1,NULL)) AS sczenlfNum")
                ->group("r.orderid")
                ->buildSql();

            return M("order_company_review")->table($tempSql)->alias("w")
                ->field("w.user_id as user_id,count(w.scfenlfNum) as scfenlfNum,count(w.sczenlfNum) as sczenlfNum")
                ->group("w.user_id")
                ->select();
        }else if($type == 3){
            $map = array(
                "l.update_time" => array(
                    array("EGT",$begin),
                    array("ELT",$end)
                ),
                "l.STATUS" => array("IN",array(1,2,3))
            );
            if (!empty($id)) {
                $map["a.user_id"] = array("EQ",$id);
            }
            $tempSql = M("company_liangfang")->where($map)->alias("l")
                ->join("join qz_orders o on o.id = l.order_id and o.on = 4 and o.type_fw in (1,2)")
                ->join("left join qz_order_csos_new a ON a.order_id = l.order_id")
                ->field("a.user_id,sum(IF (o.ON = 4 AND o.type_fw = 1,1,NULL)) AS ecfenhfNum,sum(IF (o.ON = 4 AND o.type_fw = 2,1,NULL)) AS eczenhfNum")
                ->group("l.order_id")
                ->buildSql();

            return M("company_liangfang")->table($tempSql)->alias("w")
                ->field("w.user_id as user_id,count(w.ecfenhfNum) as ecfenhfNum,count(w.eczenhfNum) as eczenhfNum")
                ->group("w.user_id")
                ->select();
        }else if($type == 4){
            $map = array(
                "r.time" => array(
                    array("EGT",$begin),
                    array("ELT",$end)
                ),
                "r.status" => array("IN",array(1,2,4)),
                "l.id" => array("exp",'is not NULL'),
                "l.STATUS" => array("IN",array(1,2,3)),
                "l.create_time" => array("exp",'< r.time')
            );
            if (!empty($id)) {
                $map["a.user_id"] = array("EQ",$id);
            }
            $tempSql = M("order_company_review")->where($map)->alias("r")
                ->join("join qz_orders o on o.id = r.orderid and o.on = 4 and o.type_fw in (1,2)")
                ->join("left join qz_order_csos_new a ON a.order_id = r.orderid")
                ->join("left join qz_company_liangfang l ON l.order_id = r.orderid")
                ->field("a.user_id,sum(IF (o.ON = 4 AND o.type_fw = 1,1,NULL)) AS ecfenlfNum,sum(IF (o.ON = 4 AND o.type_fw = 2,1,NULL)) AS eczenlfNum")
                ->group("r.orderid")
                ->buildSql();

            return M("order_company_review")->table($tempSql)->alias("w")
                ->field("w.user_id as user_id,count(w.ecfenlfNum) as ecfenlfNum,count(w.eczenlfNum) as eczenlfNum")
                ->group("w.user_id")
                ->select();
        }
    }
    /**
     * 获取客服有效量房操作订单信息
     * @param  [type] $id      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服师]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $end     [前三月结束时间]
     * @param  [type] $end     [前三月结束时间]
     * @return [type]          [description]
     */
    public function getKfOrderyouxiaoliangfang($id,$group,$manager,$monthStart,$monthEnd,$monthThreeStart,$monthThreeEnd,$type)
    {
        $tempmonthThreeStart = strtotime(date("Y-m-d",strtotime("-1 day",$monthThreeStart))." 17:30:00");
        $tempmonthStart = strtotime(date("Y-m-d",strtotime("-1 day",$monthStart))." 17:30:00");
        $tempmonthEnd = strtotime(date("Y-m-d",$monthEnd)." 17:30:00");

        //获取发单量
        if($type == 1) {
            $map = array(
                "a.op_uid" => array("neq", "0"),
                "a.addtime" => array(
                    array("EGT", $tempmonthThreeStart),
                    array("LT", $tempmonthEnd)
                )
            );

            if (!empty($id)) {
                $map["a.op_uid"] = array("EQ",$id);
            }
            if (!empty($group)) {
                $map["u.kfgroup"] = array("EQ", $group);
            }

            if (!empty($manager)) {
                $map["_string"] = "find_in_set($manager,u.kfmanager)";
            }

            $buildSql = M("order_pool")->where($map)->alias("a")
                ->join('join qz_orders o on o.id = a.orderid')
                ->join('join qz_adminuser u on u.id = a.op_uid')
                ->join('join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
                ->join('join qz_adminuser u2 on u.kfgroup = u2.kfgroup and u2.uid = 31')
                ->group('a.op_uid')
                ->field("a.op_uid,a.op_name,count(a.orderid) as `all`")
                ->buildSql();

            $map = array(
                "a.lasttime" => array(
                    array("EGT", $monthThreeStart),
                    array("ELT", $monthThreeEnd)
                )
            );

            if (!empty($id)) {
                $map["a.user_id"] = array("EQ", $id);
            }

            $nextSql = M("order_csos_new")->where($map)->alias("a")
                ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                ->field("a.user_id,sum(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,sum(if(o.on = 4 and o.type_fw = 2,1,null)) as zen")
                ->group("a.user_id")
                ->buildSql();

            return M("order_pool")->table($buildSql)->alias("t")
                ->join("left join (" . $nextSql . ") t1 on t.op_uid = t1.user_id")
                ->field("t.*,t1.fen,t1.zen")
                ->select();
        }else if($type == 2){
            $map = array(
                "r.time" => array(
                    array("EGT",$monthStart),
                    array("ELT",$monthThreeEnd)
                ),
                "a.lasttime" => array(
                    array("EGT",$monthThreeStart),
                    array("ELT",$monthThreeEnd)
                ),
                "op.addtime" => array(
                    array("EGT",$tempmonthThreeStart),
                    array("ELT",$tempmonthEnd)
                ),
                "r.status" => array("IN",array(1,2,4))
            );
            if (!empty($id)) {
                $map["a.user_id"] = array("EQ",$id);
            }
            $tempSql = M("order_company_review")->where($map)->alias("r")
                ->join("join qz_orders o on o.id = r.orderid and o.on = 4 and o.type_fw in (1,2)")
                ->join("join qz_order_csos_new a ON a.order_id = r.orderid")
                ->join("join qz_order_pool op ON op.orderid = r.orderid")
                ->field("a.user_id,r.orderid")
                ->group("r.orderid")
                ->buildSql();

            return M("order_company_review")->table($tempSql)->alias("w")
                ->field("w.user_id as user_id,count(w.orderid) as youxiao")
                ->group("w.user_id")
                ->select();
        }
    }

    /**
     * 获取客服有效量房操作订单详情
     * @param  [type] $id      [客服ID]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $end     [前三月结束时间]
     * @param  [type] $end     [前三月结束时间]
     * @return [type]          [description]
     */
    public function OrderyouxiaoliangfangDetailList($id,$monthStart,$monthEnd,$monthThreeStart,$monthThreeEnd)
    {
        $tempmonthThreeStart = strtotime(date("Y-m-d",strtotime("-1 day",$monthThreeStart))." 17:30:00");
        $tempmonthStart = strtotime(date("Y-m-d",strtotime("-1 day",$monthStart))." 17:30:00");
        $tempmonthEnd = strtotime(date("Y-m-d",$monthEnd)." 17:30:00");
        $map = array(
            "a.user_id" => array("eq",$id),
            "r.STATUS" => array("IN",array(1, 2, 4)),
            "a.lasttime" => array(
                array("EGT",$monthThreeStart),
                array("ELT",$monthThreeEnd)
            ),
            "op.addtime" => array(
                array("EGT",$tempmonthThreeStart),
                array("ELT",$tempmonthEnd)
            ),
             "r.time" => array(
                array("EGT",$monthStart),
                array("LT",$monthEnd)
            )
        );

        return M("order_company_review")->where($map)->alias("r")
            ->join("JOIN qz_order_csos_new a on a.order_id = r.orderid")
            ->join("JOIN qz_orders o ON o.id = r.orderid AND o.ON = 4 AND o.type_fw IN (1, 2)")
            ->join("JOIN qz_order_pool op ON op.orderid = r.orderid")
            ->join("LEFT JOIN qz_user u ON u.id = r.comid AND u.classid in (3,6)")
            ->join("LEFT JOIN qz_quyu qy on qy.cid = o.cs")
            ->field("a.user_id,a.order_id,FROM_UNIXTIME(op.`addtime`, '%Y-%m-%d %H:%i:%S') as fdtime,GROUP_CONCAT(u.`jc`) as jc,GROUP_CONCAT(case when r.`lf_time` > 0 then FROM_UNIXTIME(r.`lf_time`, '%Y-%m-%d') else '' end) as lftime,GROUP_CONCAT((case r.STATUS when 1 then '已量房' when 2 then '已见面/已到店' when 4 then '已签约' else '' end)) as dj_status,case when qy.cname is null then '' else qy.cname end as cname,op.op_name")
            ->group('r.orderid')
            ->select();
    }
    /**
     * 获取订单池订单信息
     * @param  [type] $orderid [description]
     * @return [type]          [description]
     */
    public function getOrderInfo($orderid)
    {
            $map = array(
                    "orderid" => array("EQ",$orderid)
            );

            return M("order_pool")->where($map)->find();
    }

    /**
     * 获取城市订单统计
     * @param  [type] $id      [description]
     * @param  [type] $group   [description]
     * @param  [type] $manager [description]
     * @param  [type] $begin   [description]
     * @param  [type] $end     [description]
     * @return [type]          [description]
     */
    public function getCityOrdereffective($id,$cs,$kfgroup,$kfmanager,$begin,$end)
    {
        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("neq","0"),
            "o.time_real" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

        if (!empty($id)) {
            $map["a.op_uid"] = array("EQ",$id);
        }

        if (count($cs) > 0) {
            $map["o.cs"] = array("IN",$cs);
        }

        if (!empty($kfgroup)) {
            $map["u.kfgroup"] = array("EQ",$kfgroup);
        }

        if (!empty($kfmanager)) {
            $map["_string"] = "find_in_set($kfmanager,u.kfmanager)";
        }

        return M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_adminuser u on u.id = a.op_uid')
                              ->join('join qz_quyu q on q.cid = o.cs ')
                              ->group('a.op_uid,o.cs')
                              ->field("a.op_uid,a.op_name,o.cs,count(a.orderid) as `all`,q.cname,q.little,q.manager")
                              ->order("o.cs")
                              ->select();
    }

    /**
     * 获取客服分单操作数据
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getKFOrderOperate($id,$cs,$kfgroup,$kfmanager,$begin,$end)
    {
        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            )
        );

        if (!empty($id)) {
            $map["a.user_id"] = array("EQ",$id);
        }

        if (count($cs) > 0) {
            $map["o.cs"] = array("IN",$cs);
        }

        if (!empty($kfgroup)) {
            $map["u.kfgroup"] = array("EQ",$kfgroup);
        }

        if (!empty($kfmanager)) {
            $map["_string"] = "find_in_set($kfmanager,u.kfmanager)";
        }


        return M("order_csos_new")->where($map)->alias("a")
                                      ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                                      ->join('join qz_adminuser u on u.id = a.user_id')
                                      ->field("a.user_id,o.cs,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,count(if( o.on = 4 and o.type_fw = 3,1,null)) as fen_other,count(if( o.on = 4 and o.type_fw = 4,1,null)) as zen_other")
                                      ->group("a.user_id,o.cs")
                                      ->select();
    }

    /**
     * 获取客服分单操作数据(按月)
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getMonthOrderOperate($begin,$end)
    {
        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            )
        );

        return M("order_csos_new")->where($map)->alias("a")
                                      ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                                      ->join('join qz_adminuser u on u.id = a.user_id')
                                      ->field("o.cs,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,count(if( o.on = 4 and o.type_fw = 3,1,null)) as fen_other,count(if( o.on = 4 and o.type_fw = 4,1,null)) as zen_other,FROM_UNIXTIME(a.lasttime,'%Y-%m') as month")
                                      ->group("o.cs,month")
                                      ->select();
    }

    /**
     * 获取城市订单统计
     * @param  [type] $id      [description]
     * @param  [type] $group   [description]
     * @param  [type] $manager [description]
     * @param  [type] $begin   [description]
     * @param  [type] $end     [description]
     * @return [type]          [description]
     */
    public function getMonthCityOrdereffective($begin,$end)
    {

        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("neq","0"),
            "o.time_real" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

        return M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_adminuser u on u.id = a.op_uid')
                              ->join('join qz_quyu q on q.cid = o.cs ')
                              ->group('o.cs,month')
                              ->field("o.cs,count(a.orderid) as `all`,q.cname,q.little,q.manager,date_format(case
                                    when o.time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),' ','17:30:00'))
                                    and o.time_real < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),' ','23:59:59'))
                                    then DATE_ADD(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),INTERVAL 1 day)
                                    else FROM_UNIXTIME(o.time_real,'%Y-%m-%d') end,'%Y-%m') as month")
                              ->order("o.cs,month")
                              ->select();
    }

    /**
     * 获取客服坐席数
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function customerseatsstat($begin,$end)
    {
        $sql = 'select
            date,
            if(max(group1) is null,0,max(group1)) as group1,
            if(max(group2) is null,0,max(group2)) as group2,
            if(max(group3) is null,0,max(group3)) as group3,
            if(max(group4) is null,0,max(group4)) as group4,
            if(max(group5) is null,0,max(group5)) as group5,
            if(max(group6) is null,0,max(group6)) as group6,
            if(max(group7) is null,0,max(group7)) as group7,
            if(max(group8) is null,0,max(group8)) as group8,
            if(max(group9) is null,0,max(group9)) as group9,
            if(max(group11) is null,0,max(group11)) as group11,
            if(max(group12) is null,0,max(group12)) as group12,
            if(max(group13) is null,0,max(group13)) as group13,
            if(max(group14) is null,0,max(group14)) as group14,
            if(max(group15) is null,0,max(group15)) as group15,
            if(max(group1) is null,0,max(group1))+if(max(group2) is null,0,max(group2))+if(max(group3) is null,0,max(group3))+if(max(group4) is null,0,max(group4))
            +if(max(group5) is null,0,max(group5))+if(max(group6) is null,0,max(group6))+if(max(group7) is null,0,max(group7)) +if(max(group8) is null,0,max(group8)) 
            +if(max(group9) is null,0,max(group9))+if(max(group11) is null,0,max(group11))+if(max(group12) is null,0,max(group12))
            +if(max(group13) is null,0,max(group13))+if(max(group14) is null,0,max(group14))+if(max(group15) is null,0,max(group15))
            as `all`
            from (
                select
                date,
                CASE
                    when kfgroup = 1 then count
                end as group1,
                CASE
                    when kfgroup = 2 then count
                end as group2,
                CASE
                    when kfgroup = 3 then count
                end as group3,
                CASE
                    when kfgroup = 4 then count
                end as group4,
                CASE
                    when kfgroup = 5 then count
                end as group5,
                CASE
                    when kfgroup = 6 then count
                end as group6,
                CASE
                    when kfgroup = 7 then count
                end as group7,
                CASE
                    when kfgroup = 8 then count
                end as group8,
                CASE
                    when kfgroup = 9 then count
                end as group9,
                CASE
                    when kfgroup = 11 then count
                end as group11,
                CASE
                    when kfgroup = 12 then count
                end as group12,
                CASE
                    when kfgroup = 13 then count
                end as group13,
                CASE
                    when kfgroup = 14 then count
                end as group14,
                CASE
                    when kfgroup = 15 then count
                end as group15
                from (
                    select
                    u.kfgroup,
                    t2.date,
                    sum(mark) as count
                    from (
                        select
                        t1.op_uid,
                        t1.op_name,
                        t1.date,
                        case
                                when sum(first_half) > 0 and sum(second_half) > 0 then 1
                                when sum(first_half) > 0 and sum(second_half) = 0 then 0.5
                                when sum(first_half) = 0 and sum(second_half) > 0 then 0.5
                                when sum(first_half) = 0 and sum(second_half) > 0 and count(op_uid) >=60 then 0.5
                                else 0
                        end as mark
                        from (
                            select
                            t.op_uid,
                            t.op_name,
                            t.date,
                            t.hour,
                            if(t.hour >= "09:00:00" and t.hour <= "12:00:00",1,0) as first_half,
                            if(t.hour >= "13:15:00" and t.hour <= "17:30:00",1,0) as second_half
                            from (
                                    select op_uid,op_name,FROM_UNIXTIME(addtime,"%H:%i:%s") as `hour`,FROM_UNIXTIME(addtime,"%Y-%m-%d") as `date` from qz_order_pool where addtime >= UNIX_TIMESTAMP("'.$begin.'") and addtime < UNIX_TIMESTAMP("'.$end.'") and type = 2
                                ) t
                        ) t1 group by t1.op_uid,date
                    ) t2
                    join qz_adminuser u on u.id = t2.op_uid
                    group by u.kfgroup,t2.date
                    order by kfgroup,date
                )t3
            ) t4 group by date';
        return M()->query($sql);
    }

    /**
     * 客服活动订单
     * @param  [type] $id      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服师]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $source  [活动发单位置]
     * @return array
     */
    public function getActivityOrdereffective($id,$group,$manager,$begin,$end,$source)
    {
        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("neq","0"),
            "a.addtime" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            ),
            "o.source" => array("IN",$source)
        );

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_adminuser u on u.id = a.op_uid')
                              ->group('a.op_uid')
                              ->field("a.op_uid,a.op_name,count(a.orderid) as `all`")
                              ->buildSql();

        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            ),
            "o.source" => array("IN",$source)
        );
        $nextSql = M("order_csos_new")->where($map)->alias("a")
                                      ->join("join qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)")
                                      ->field("a.user_id,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen")
                                      ->group("a.user_id")
                                      ->buildSql();


        return M("order_pool")->table($buildSql)->alias("t")
                       ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id")
                       ->field("t.*,t1.fen,t1.zen")
                       ->select();
    }

    /**
     * 获取对应客服的发单量
     * @param $where
     * @return mixed
     */
    public function getKfOrders($where)
    {
        $where['a.op_uid'] = ["neq", "0"];
        return M("order_pool")->where($where)->alias("a")
            ->join('join qz_orders o on o.id = a.orderid')
            ->join('join qz_adminuser u on u.id = a.op_uid')
            ->group('a.op_uid')
            ->field("a.op_uid,a.op_name,count(a.orderid) as `all`,u.kfgroup,u.kfmanager")
            ->select();
    }

    /**
     * 获取对应客服的申请个数
     * @param $where
     * @return mixed
     */
    public function getKfOrdersCount($where)
    {
        $where['a.op_uid'] = ["neq", "0"];
        return  M("order_pool")->where($where)->alias("a")
            ->join('join qz_orders o on o.id = a.orderid')
            ->join('join qz_adminuser u on u.id = a.op_uid')
            ->join('join qz_orders_apply_tel d ON d.orders_id = a.orderid')
            ->group('a.op_uid')
            ->field("a.op_uid,a.op_name,count(a.orderid) as `all`,u.kfgroup,u.kfmanager")
            ->select();
    }

    /**
     * 按日统计客服分单量，增单量，赠转分
     *
     * @param $id
     * @param $group
     * @param $manager
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getKfOrdereffectiveByDay($id, $group, $manager, $begin, $end)
    {
        $monthEnd = $end;
        $monthBegin = $begin;

        $map = array(
            "a.op_uid" => array("NEQ",0),
            "a.addtime" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

         if (!empty($id)) {
           $map["op_uid"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_adminuser u on u.id = a.op_uid')
                              ->join('join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
                              ->join('join qz_adminuser u2 on u.kfgroup = u2.kfgroup and u2.uid = 31')
                              ->field('a.orderid,a.op_uid,a.op_name,
                                       case when a.addtime >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","17:30:00"))
                                and a.addtime < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","23:59:59"))
                                then DATE_ADD(FROM_UNIXTIME(a.addtime,"%Y-%m-%d"),INTERVAL 1 day)
                                else FROM_UNIXTIME(a.addtime,"%Y-%m-%d")
                                end as date,u1.name,u.kfgroup,u.kfmanager,u2.name as group_name')
                              ->buildSql();
        $buildSql = M("order_pool")->table($buildSql)->alias("t")
                                   ->group("t.op_uid,t.date")
                                   ->field("t.op_uid,t.op_name,count(t.orderid) as `all`,t.date,t.kfgroup,t.name,REPLACE(t.kfmanager,',','') as kfmanager,t.group_name")
                                ->buildSql();

        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($id)) {
           $map["user_id"] = array("EQ",$id);
        }

        $nextSql = M("order_csos_new")
            ->where($map)
            ->alias("a")
            ->field("a.order_id,a.user_id,from_unixtime(lasttime,'%Y-%m-%d') as date")
            ->buildSql();
        $nextSql = M("order_csos_new")
            ->table($nextSql)
            ->alias("t")
            ->join("join qz_orders o on o.id = t.order_id and o.on = 4 and o.type_fw in (1,2)")
            ->join('left join qz_order_rob_pool rp on rp.order_id = o.id and rp.status = 2 and rp.is_rob = 2')
            ->field("t.user_id,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,count(if(rp.status <> 0 and  rp.status is not null && rp.status <> o.type_fw,1,null)) as zentofen,t.date")
            ->group("t.user_id,t.date")
            ->buildSql();

        if (!empty($id)) {
            return M("order_pool")->table($buildSql)->alias("t")
                       ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id and t.date = t1.date")
                       ->field("t.*,t1.fen,t1.zen,t1.zentofen")
                       ->order("t.date")
                       ->select();
        } elseif(!empty($group)) {
            $buildSql = M("order_pool")->table($buildSql)->alias("t")
                       ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id and t.date = t1.date")
                       ->field("t.kfgroup,t.group_name,t.date,sum(t.all) as `all`,sum(t1.fen) as fen,sum(t1.zen) as zen,sum(t1.zentofen) as zentofen")
                       ->group("t.date,t.kfgroup")
                       ->buildSql();
            return  M("order_pool")->table($buildSql)->alias("t")->order("t.date")->select();
        } elseif(!empty($manager)){
            $buildSql = M("order_pool")->table($buildSql)->alias("t")
                       ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id and t.date = t1.date")
                       ->field("t.kfmanager,t.name,t.date,sum(t.all) as `all`,sum(t1.fen) as fen,sum(t1.zen) as zen,sum(t1.zentofen) as zentofen")
                       ->group("t.date,t.kfmanager")
                       ->buildSql();
            return  M("order_pool")->table($buildSql)->alias("t")->order("t.date")->select();
        } else {
            return M("order_pool")
                ->table($buildSql)
                ->alias("t")
                ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id and t.date = t1.date")
                ->field("t.*,t1.fen,t1.zen,t1.zentofen")
                ->order("t.date")
                ->select();
        }
    }

    public function getKfOrderyouxiaoliangfangByDayPool($id,$group,$manager,$begin,$end)
    {
        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("NEQ",0),
            "a.addtime" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

        if (!empty($id)) {
            $map["op_uid"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
            ->join('join qz_orders o on o.id = a.orderid')
            ->join('join qz_adminuser u on u.id = a.op_uid')
            ->join('left join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
            ->join('join qz_adminuser u2 on u.kfgroup = u2.kfgroup and u2.uid = 31')
            ->field('a.orderid,
                                       case when a.addtime >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","17:30:00"))
                                and a.addtime < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","23:59:59"))
                                then DATE_ADD(FROM_UNIXTIME(a.addtime,"%Y-%m-%d"),INTERVAL 1 day)
                                else FROM_UNIXTIME(a.addtime,"%Y-%m-%d")
                                end as date')
            ->buildSql();
        return M("order_pool")->table($buildSql)->alias("t")
            ->group("t.date")
            ->field("count(t.orderid) as `all`,t.date")
            ->select();
    }

    public function getKfOrderliangfangByDay($id,$group,$manager,$begin,$end)
    {

        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("NEQ",0),
            "a.status" => array("EQ",0),
            "u.stat" => array("EQ",1),
            "a.addtime" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

        if (!empty($id)) {
            $map["op_uid"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
            ->join('join qz_orders o on o.id = a.orderid')
            ->join('join qz_adminuser u on u.id = a.op_uid')
            ->join('join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
            ->join('join qz_adminuser u2 on u.kfgroup = u2.kfgroup and u2.uid = 31')
            ->field('a.orderid,a.op_uid,a.op_name,
                                       case when a.addtime >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","17:30:00"))
                                and a.addtime < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","23:59:59"))
                                then DATE_ADD(FROM_UNIXTIME(a.addtime,"%Y-%m-%d"),INTERVAL 1 day)
                                else FROM_UNIXTIME(a.addtime,"%Y-%m-%d")
                                end as date,u1.name,u.kfgroup,u.kfmanager,u2.name as group_name')
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->group("t.op_uid,t.date")
            ->field("t.op_uid,t.op_name,count(t.orderid) as `all`,t.date,t.kfgroup,t.name,REPLACE(t.kfmanager,',','') as kfmanager,t.group_name")
            ->buildSql();

        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        $nextSql = M("order_csos_new")->where($map)->alias("a")
            ->field("a.order_id,a.user_id,from_unixtime(lasttime,'%Y-%m-%d') as date")
            ->buildSql();
        $nextSql = M()->table($nextSql)->alias("t")
            ->join("join qz_orders o on o.id = t.order_id and o.on = 4 and o.type_fw in (1,2)")
            //->join("left join qz_order_company_review r on r.orderid = t.order_id")
            //->join("left join qz_company_liangfang l on l.order_id = t.order_id")
            ->field("t.user_id,sum(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,sum(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,t.date")
            ->group("t.order_id")
            ->buildSql();
        $nextSql = M()->table($nextSql)->alias("w")
            ->field("w.user_id,count(w.fen) as fen,count(w.zen) as zen,w.date")
            ->group("w.user_id,w.date")
            ->buildSql();

        // $map = array(
        //     "r.status" => array("IN",array(1,2,4)),
        //     "l.id" => array("exp","IS NULL"),
        //     "r.time" => array(
        //         array("EGT",$begin),
        //         array("ELT",$end)
        //     )
        // );

        // $nextSql2 = M("order_company_review")->where($map)->alias("r")
        //     ->join("join qz_orders o on o.id = r.orderid and o.on = 4 and o.type_fw in (1,2)")
        //     ->join("left join qz_order_csos_new a ON a.order_id = r.orderid")
        //     ->join("left join qz_company_liangfang l on l.order_id = r.orderid")
        //     ->field("a.user_id,sum(if(o.on = 4 and o.type_fw = 1,1,null)) as scfenlfNum,sum(if(o.on = 4 and o.type_fw = 2,1,null)) as sczenlfNum,from_unixtime(r.time, '%Y-%m-%d') AS date")
        //     ->group("r.orderid")
        //     ->buildSql();
        // $nextSql2 = M("order_company_review")->table($nextSql2)->alias("w")
        //     ->field("w.user_id,count(w.sczenlfNum) as sczenlfNum,count(w.scfenlfNum) as scfenlfNum,w.date")
        //     ->group("w.user_id,w.date")
        //     ->buildSql();

        // $map = array(
        //     "l.STATUS" => array("IN",array(1,2,3)),
        //     "l.update_time" => array(
        //         array("EGT",$begin),
        //         array("ELT",$end)
        //     )
        // );

        // $nextSql3 = M("company_liangfang")->where($map)->alias("l")
        //     ->join("join qz_orders o on o.id = l.order_id and o.on = 4 and o.type_fw in (1,2)")
        //     ->join("left join qz_order_csos_new a ON a.order_id = l.order_id")
        //     ->field("a.user_id,sum(if(o.on = 4 and o.type_fw = 1,1,null)) as ecfenhfNum,sum(if(o.on = 4 and o.type_fw = 2,1,null)) as eczenhfNum,from_unixtime(l.update_time, '%Y-%m-%d') AS date")
        //     ->group("l.order_id")
        //     ->buildSql();
        // $nextSql3 = M("company_liangfang")->table($nextSql3)->alias("w")
        //     ->field("w.user_id,count(w.ecfenhfNum) as ecfenhfNum,count(w.eczenhfNum) as eczenhfNum,w.date")
        //     ->group("w.user_id,w.date")
        //     ->buildSql();

        // $map = array(
        //     "r.status" => array("IN",array(1,2,4)),
        //     "l.id" => array("exp",'is not NULL'),
        //     "l.STATUS" => array("IN",array(1,2,3)),
        //     "l.create_time" => array("exp",'< r.time'),
        //     "r.time" => array(
        //         array("EGT",$begin),
        //         array("ELT",$end)
        //     )
        // );

        // $nextSql4 = M("order_company_review")->where($map)->alias("r")
        //     ->join("join qz_orders o on o.id = r.orderid and o.on = 4 and o.type_fw in (1,2)")
        //     ->join("left join qz_order_csos_new a ON a.order_id = r.orderid")
        //     ->join("left join qz_company_liangfang l on l.order_id = r.orderid")
        //     ->field("a.user_id,sum(if(o.on = 4 and o.type_fw = 1,1,null)) as ecfenlfNum,sum(if(o.on = 4 and o.type_fw = 2,1,null)) as eczenlfNum,from_unixtime(r.time, '%Y-%m-%d') AS date")
        //     ->group("r.orderid")
        //     ->buildSql();
        // $nextSql4 = M("order_company_review")->table($nextSql4)->alias("w")
        //     ->field("w.user_id,count(w.ecfenlfNum) as ecfenlfNum,count(w.eczenlfNum) as eczenlfNum,w.date")
        //     ->group("w.user_id,w.date")
        //     ->buildSql();

        $map = array(
            "_complex" => array(
                array(
                    "a.lf_time" => array(
                        array("EGT", $begin),
                        array("ELT", $end)
                    ),
                    "a.`status`" => array("IN", "1,2")
                ),
                array(
                    "o.qiandan_chktime" => array(
                        array("EGT", $begin),
                        array("ELT", $end)
                    ),
                    "a.`status`" => array("EQ", 4)
                ),
                "_logic" => "or"
            )
        );
        
        $nextSql5 = M("order_company_review")->where($map)->alias("a")
            ->join("join qz_orders o on o.id = a.orderid")
            ->field("orderid,IF(a.status = 4, from_unixtime(o.qiandan_chktime,'%Y-%m-%d'), from_unixtime(a.lf_time,'%Y-%m-%d')) as date")
            ->group("orderid")
            ->buildSql();
        $nextSql5 = M()->table($nextSql5)->alias("t")
            ->join("join qz_order_csos_new new on new.order_id = t.orderid")
            ->join("join qz_adminuser u on u.id = new.user_id")
            ->field("u.id as user_id,count(t.orderid) as lf_count,u.kfgroup, substring_index(u.kfmanager,',',1) as kfmanager,t.date")
            ->group("u.id,t.date")->buildSql();

        $totalSql = M()->table($buildSql)->alias("t")
            ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id and t.date = t1.date")
            // ->join("left join (".$nextSql2.") t3 on t.op_uid = t3.user_id and t.date = t3.date")
            // ->join("left join (".$nextSql3.") t4 on t.op_uid = t4.user_id and t.date = t4.date")
            // ->join("left join (".$nextSql4.") t5 on t.op_uid = t5.user_id and t.date = t5.date")
            // ->field("t.*, t1.fen,t1.zen,t3.scfenlfNum,t3.sczenlfNum,t4.ecfenhfNum,t4.eczenhfNum,t5.ecfenlfNum,t5.eczenlfNum")
            ->join("left join $nextSql5 t6 on t.op_uid = t6.user_id and t.date = t6.date")
            ->field("t.*, t1.fen,t1.zen,t6.lf_count")
            ->buildSql();

        if (!empty($id)) {
            return M()->table($totalSql)->alias("t2")
                ->field("t2.*")
                ->group("t2.date")
                ->select();
        } elseif(!empty($group)) {
            $buildSql = M()->table($totalSql)->alias("t2")
                // ->field("t2.kfgroup,t2.group_name,t2.date,sum(t2.all) as `all`,sum(t2.fen) as fen,sum(t2.zen) as zen,sum(t2.scfenlfNum) as scfenlfNum,sum(t2.sczenlfNum) as sczenlfNum,sum(t2.ecfenhfNum) as ecfenhfNum,sum(t2.eczenhfNum) as eczenhfNum,sum(t2.ecfenlfNum) as ecfenlfNum,sum(t2.eczenlfNum) as eczenlfNum")
            ->field("t2.kfgroup,t2.group_name,t2.date,sum(t2.all) as `all`,sum(t2.fen) as fen,sum(t2.zen) as zen,sum(t2.lf_count) as lf_count")
                ->group("t2.date,t2.kfgroup")
                ->buildSql();
            return M()->table($buildSql)->alias("t")->order("t.date")->select();
        } elseif(!empty($manager)){
            $buildSql = M()->table($totalSql)->alias("t2")
                // ->field("t2.name,t2.date,sum(t2.all) as `all`,sum(t2.fen) as fen,sum(t2.zen) as zen ,sum(t2.scfenlfNum) as scfenlfNum,sum(t2.sczenlfNum) as sczenlfNum,sum(t2.ecfenhfNum) as ecfenhfNum,sum(t2.eczenhfNum) as eczenhfNum,sum(t2.ecfenlfNum) as ecfenlfNum,sum(t2.eczenlfNum) as eczenlfNum")
            ->field("t2.name,t2.date,sum(t2.all) as `all`,sum(t2.fen) as fen,sum(t2.zen) as zen,sum(t2.lf_count) as lf_count")
                ->group("t2.date,t2.kfmanager")
                ->buildSql();
            return M()->table($buildSql)->alias("t")->order("t.date")->select();
        } else {
            return M()->table($totalSql)->alias("t2")
                // ->field("t2.date,sum(t2.fen) AS fen,sum(t2.zen) AS zen,sum(t2.`all`) AS `all`,sum(t2.scfenlfNum) AS scfenlfNum,sum(t2.`sczenlfNum`) AS `sczenlfNum`,sum(t2.ecfenhfNum) AS ecfenhfNum,sum(t2.`eczenhfNum`) AS `eczenhfNum`,sum(t2.ecfenlfNum) AS ecfenlfNum,sum(t2.`eczenlfNum`) AS `eczenlfNum`")
            ->field("t2.date,sum(t2.fen) AS fen,sum(t2.zen) AS zen,sum(t2.`all`) AS `all`,sum(t2.lf_count) as lf_count")
                ->group("t2.date")
                ->select();
        }
    }

    public function getKfOrderyouxiaoliangfangByDay($id,$group,$manager,$monthStart,$monthEnd,$monthThreeStart,$monthThreeEnd,$monthTotalEnd)
    {
        $tempmonthThreeStart = strtotime(date("Y-m-d",strtotime("-1 day",$monthThreeStart))." 17:30:00");
        $tempmonthStart = strtotime(date("Y-m-d",strtotime("-1 day",$monthStart))." 17:30:00");
        $tempmonthEnd = strtotime(date("Y-m-d",$monthEnd)." 17:30:00");
            $map = array(
                "a.op_uid" => array("NEQ",0),
                "a.status" => array("EQ",0),
                "u.stat" => array("EQ",1),
                "a.addtime" => array(
                    array("EGT",$tempmonthStart),
                    array("LT",$tempmonthEnd)
                )
            );

            if (!empty($id)) {
                $map["op_uid"] = array("EQ",$id);
            }

            if (!empty($group)) {
                $map["u.kfgroup"] = array("EQ",$group);
            }

            if (!empty($manager)) {
                $map["_string"] = "find_in_set($manager,u.kfmanager)";
            }

            $buildSql = M("order_pool")->where($map)->alias("a")
                ->join('join qz_orders o on o.id = a.orderid')
                ->join('join qz_adminuser u on u.id = a.op_uid')
                ->join('join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
                ->join('join qz_adminuser u2 on u.kfgroup = u2.kfgroup and u2.uid = 31')
                ->field('a.orderid,a.op_uid,case when a.addtime >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","17:30:00"))
                                    and a.addtime < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(a.addtime,"%Y-%m-%d")," ","23:59:59"))
                                    then DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(a.addtime, "%Y-%m-%d"),INTERVAL 1 DAY),"%Y-%m")
                                    else FROM_UNIXTIME(a.addtime,"%Y-%m")
                                    end as date')
                ->buildSql();
            $buildSql = M("order_pool")->table($buildSql)->alias("t")
                ->group("t.op_uid,t.date")
                ->field("t.op_uid,count(t.orderid) as `all`,t.date")
                ->buildSql();

            $map = array(
                "a.lasttime" => array(
                    array("EGT",$monthStart),
                    array("ELT",$monthEnd)
                )
            );

            $nextSql = M("order_csos_new")->where($map)->alias("a")
                ->field("a.order_id,a.user_id,from_unixtime(lasttime,'%Y-%m') as date")
                ->buildSql();
            $nextSql = M("order_csos_new")->table($nextSql)->alias("t")
                ->join("join qz_orders o on o.id = t.order_id and o.on = 4 and o.type_fw in (1,2)")
                ->field("t.user_id,sum(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,sum(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,t.date")
                ->group("t.order_id")
                ->buildSql();
            $nextSql = M("order_csos_new")->table($nextSql)->alias("w")
                ->field("w.user_id,count(w.fen) as fen,count(w.zen) as zen,w.date")
                ->group("w.user_id,w.date")
                ->buildSql();

            $map = array(
                "r.status" => array("IN",array(1,2,4)),
                "r.time" => array(
                    array("EGT",$monthStart),
                    array("ELT",$monthEnd)
                ),
                "op.addtime" => array(
                    array("EGT",$tempmonthThreeStart),
                    array("ELT",$tempmonthEnd)
                )
            );

            $nextSql2 = M("order_company_review")->where($map)->alias("r")
                ->join("join qz_orders o on o.id = r.orderid and o.on = 4 and o.type_fw in (1,2)")
                ->join("left join qz_order_csos_new a ON a.order_id = r.orderid")
                ->join("left join qz_company_liangfang l on l.order_id = r.orderid")
                ->join("JOIN qz_order_pool op ON op.orderid = r.orderid")
                ->field("a.user_id ,r.orderid,from_unixtime(r.time, '%Y-%m') AS date")
                ->group("r.orderid")
                ->buildSql();
            $nextSql2 = M("order_company_review")->table($nextSql2)->alias("w")
                ->field("w.user_id,count(w.orderid) as youxiao,w.date")
                ->group("w.user_id,w.date")
                ->buildSql();
            $totalSql = M("order_pool")->table($buildSql)->alias("t")
            ->join("left join (".$nextSql.") t1 on t.op_uid = t1.user_id and t.date = t1.date")
            ->join("left join (".$nextSql2.") t3 on t.op_uid = t3.user_id and t.date = t3.date")
            ->field("t.*, t1.fen,t1.zen,t3.youxiao")
            ->buildSql();

            return M("order_pool")->table($totalSql)->alias("t2")
            ->field("t2.date,sum(t2.fen) AS fen,sum(t2.zen) AS zen,sum(t2.`all`) AS `all`,sum(t2.youxiao) AS youxiao")
            ->group("t2.date")
            ->order("t2.date")
            ->select();
    }

    /**
     * 获取客服每个时间段的座席数
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getKfRseatByTimeRange($start,$end)
    {
        $map = array(
            "addtime" => array(
                array("EGT",$start),
                array("ELT",$end)
            ),
            "status" => array("EQ",0)
        );

        $buildSql = M("order_pool")->where($map)->field("op_uid,op_name,from_unixtime(addtime,'%Y-%m-%d') as date,from_unixtime(addtime,'%H:%i') as addtime")->buildSql();

        $buildSql = M("order_pool")->table($buildSql)->alias("t")
                                         ->field("t.op_uid,t.op_name,case when addtime >= '08:00' and addtime < '09:30' then 0
                                                            when addtime >= '09:30' and addtime < '10:30' then 1
                                                            when addtime >= '10:30' and addtime < '11:30' then 2
                                                            when addtime >= '11:30' and addtime < '12:30' then 3
                                                            when addtime >= '12:30' and addtime < '13:30' then 4
                                                            when addtime >= '13:30' and addtime < '14:30' then 5
                                                            when addtime >= '14:30' and addtime < '15:30' then 6
                                                            when addtime >= '15:30' and addtime < '16:30' then 7
                                                            when addtime >= '16:30' and addtime < '17:30' then 8
                                                            when addtime >= '17:30' and addtime < '18:30' then 9
                                                            end as mark,t.date")
                                         ->buildSql();
        //每天每个时间时间段的客服坐席人数
        $buildSql = M("order_pool")->table($buildSql)->alias("t1")->where()
                                                ->join("join qz_adminuser u on u.id = t1.op_uid and u.uid in (2,31) and u.kfgroup <> 0")
                                                ->field("t1.op_uid,t1.mark,t1.date,u.kfgroup")
                                                ->group("t1.mark,t1.op_uid,t1.date")
                                                ->buildSql();
        return M("order_pool")->table($buildSql)->alias("t2")->where()
                                                ->field("t2.mark,t2.kfgroup,count(t2.mark) as count")
                                                ->group("t2.mark,t2.kfgroup")
                                                ->select();
    }

    public function getOrderByIds($ids)
    {
        $map = array(
            "orderid" => array("IN",$ids)
        );
        return M("order_pool")->where($map)->select();
    }

    public function getCustomerLfList($begin,$end)
    {
        $monthEnd = strtotime(date("Y-m-d",$end)." 17:30:00");
        $monthBegin = strtotime(date("Y-m-d",strtotime("-1 day",$begin))." 17:30:00");

        $map = array(
            "a.op_uid" => array("neq","0"),
            "o.time_real" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            ),
            "o.on" => array("EQ",4),
            "o.type_fw" => array("IN",array(1,2))
        );

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
                              ->join('join qz_orders o on o.id = a.orderid')
                              ->join('join qz_order_csos_new new on o.id = new.order_id')
                              ->join('join qz_adminuser u on u.id = a.op_uid')
                              ->field("o.id,user_id,u.name,o.on,o.type_fw")
                              ->buildSql();
        $buildSql = M("order_pool")->table($buildSql)->alias("t")
                                   ->join("qz_order_company_review r on r.orderid = t.id")
                                   ->field("t.id,count(t.id) as fen_count,count(if(r.`status` in (1,2,4),1,null)) as lf_count,count(if(r.`status` = 3,1,null)) as un_lf_count,t.user_id,t.name,t.on,t.type_fw")
                                   ->group("t.id")->buildSql();
        $buildSql = M("order_pool")->table($buildSql)->alias("t1")
                                   ->field("t1.id, t1.user_id,t1.name,t1.on,t1.type_fw ,if( t1.un_lf_count > (t1.fen_count/2) or t1.lf_count > 0 ,1,0) as yx_mark,if(t1.lf_count = 0 and t1.un_lf_count = 0,0,1) as wx_mark,t1.lf_count,t1.un_lf_count")
                                   ->buildSql();
        $map = array(
            "t2.yx_mark" => array("EQ",1),
            "t2.wx_mark" => array("EQ",1)
        );
        return  M("order_pool")->table($buildSql)->alias("t2")->where($map)
                             ->field("t2.user_id,count(if(t2.lf_count > 0 and t2.on = 4 and t2.type_fw = 1,1,null)) as fen_lf_count,count(if(t2.lf_count = 0 and t2.un_lf_count > 0 and t2.on = 4 and t2.type_fw = 1,1,null)) as fen_un_lf_count,count(if(t2.lf_count > 0 and t2.on = 4 and t2.type_fw = 2,1,null)) as zen_lf_count,count(if(t2.lf_count = 0 and t2.un_lf_count > 0 and t2.on = 4 and t2.type_fw = 2,1,null)) as zen_un_lf_count")
                             ->group("t2.user_id")
                             ->select();
    }

    /**
     * 订单池中可派送订单统计
     * 按照不同的池聚合
     * 本函数取数据的逻辑和派单逻辑保持一致,应该同步修改
     */
    public function getPoolEnableOrdersCount()
    {
        $map = array(
            "a.status" => array("EQ", 1),
            "a.op_uid" => array("EQ", 0),
            "b.`on`" => array("EQ", 0),
            "b.`on_sub`" => array("EQ", 10),
            "a.time" => array(
                array("EGT", strtotime("-30 day", strtotime(date("Y-m-d")))),
                array("ELT", strtotime(date("Y-m-d")) + 86400 - 1)
            ),
            "b.cs" => array("NEQ", "000001")
        );

        $buildSql = M("order_pool")->where($map)->alias("a")
            ->join("join qz_orders b on a.orderid = b.id")
            ->field("a.orderid,b.cs, a.time as time_real")
            ->order("a.time")
            ->buildSql();

        unset($map);
        $map = array(
            "a.`on`" => array("EQ", 2),
            "a.classid" => array('IN',array(3,6))
        );
        $nextSql = M("user")->where($map)->alias("a")
            ->join("join qz_user_company b ON b.userid = a.id and b.fake = 0")
            ->group("a.cs")
            ->field("a.cs")
            ->buildSql();
        return M()->table($buildSql)->alias("t")
            ->join("join($nextSql) t1 ON t.cs = t1.cs")
            ->join("join qz_order_pond_city opc ON opc.city_id = t.cs")
            ->field("opc.pond_id,COUNT(t.orderid) AS 'un_order_num'")
            ->group('opc.pond_id')
            ->having('opc.pond_id > 0')
            ->select();
    }

    /**
     * 获取转化每日发单统计
     *
     * @param $id
     * @param $group
     * @param $manager
     * @param $monthStart
     * @param $monthEnd
     */
    public function getTransferOrderList($id,$group,$manager,$monthStart,$monthEnd)
    {
        $map = array(
            "b.type" => array("EQ",1),
            "a.addtime" => array(
                array("EGT",$monthStart),
                array("ELT",$monthEnd)
            ),
            "b.type_sub" => array("EQ",1),
            "a.status" => array("EQ",0),
            "a.op_uid" => array("NEQ",0)
        );

        if (!empty($id)) {
            $where["u.id"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $where["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $where["_string"] = "find_in_set($manager,u.kfmanager)";
        }


        $buildSql = M("order_pool")->where($map)->alias("a")
                       ->join("join qz_log_order_route b on a.orderid = b.order_id")
                       ->field("a.orderid,a.op_uid,FROM_UNIXTIME(a.addtime,'%Y-%m-%d') as date")->buildSql();

        return M("order_pool")->table($buildSql)->alias("t")->where($where)
                       ->join("join qz_adminuser u on u.id = t.op_uid")
                       ->field("u.id as op_uid,u.name,count(t.orderid) as all_count,u.kfgroup,u.kfmanager,t.date")
                       ->group("t.op_uid,t.date")
                       ->select();
    }

    /**
     * 获取实际转化每日发单统计
     *
     * @param $id
     * @param $group
     * @param $manager
     * @param $monthStart
     * @param $monthEnd
     */
    public function getTransferRealOrderList($id,$group,$manager,$monthStart,$monthEnd)
    {
        $map = array(
            "b.type" => array("EQ",1),
            "new.lasttime" => array(
                array("EGT",$monthStart),
                array("ELT",$monthEnd)
            ),
            "b.type_sub" => array("EQ",1),
            "a.status" => array("EQ",0),
            "a.op_uid" => array("NEQ",0)
        );

        $buildSql = M("order_pool")->where($map)->alias("a")
            ->join("join qz_log_order_route b on a.orderid = b.order_id")
            ->join("join qz_order_csos_new new on new.order_id = a.orderid")
            ->join("join qz_orders o on o.id = a.orderid and o.on = 4")
            ->field("a.orderid,new.user_id,FROM_UNIXTIME(new.lasttime,'%Y-%m-%d') as date,o.on,o.type_fw")
            ->buildSql();

        if (!empty($id)) {
            $where["u.id"] = array("EQ",$id);
        }

        if (!empty($group)) {
            $where["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $where["_string"] = "find_in_set($manager,u.kfmanager)";
        }
        return  M("order_pool")->table($buildSql)->where($where)->alias("t")
                               ->join("join qz_adminuser u on u.id = t.user_id")
                               ->field("u.id as op_uid,u.name,count(if(t.on = 4 and t.type_fw = 1,1,null)) as fen_count,count(if(t.on = 4 and t.type_fw = 2,1,null)) as zen_count,u.kfgroup,u.kfmanager,t.date")
                               ->group("t.user_id,t.date")
                               ->select();

    }

    /**
     * 可派送订单列表
     */
    public function getRemainingOrderList()
    {
        $map = array(
            "a.status" => array("EQ", 1),
            "a.op_uid" => array("EQ", 0),
            "b.`on`" => array("EQ", 0),
            "b.`on_sub`" => array("EQ", 10),
            "a.time" => array(
                array("EGT", strtotime("-30 day", strtotime(date("Y-m-d")))),
                array("ELT", strtotime(date("Y-m-d")) + 86400 - 1)
            ),
            "b.cs" => array("NEQ", "000001")
        );

        $buildSql = M("order_pool")->where($map)->alias("a")
            ->join("join qz_orders b on a.orderid = b.id")
            ->field("a.orderid,b.cs, b.source_in")
            ->order("a.time")
            ->buildSql();

        $map = array(
            "a.`on`" => array("EQ", 2),
            "a.classid" => array('IN',array(3,6))
        );
        $nextSql = M("user")->where($map)->alias("a")
            ->join("join qz_user_company b ON b.userid = a.id and b.fake = 0")
            ->group("a.cs")
            ->field("a.cs")
            ->buildSql();
        return M()->table($buildSql)->alias("t")
            ->join("join($nextSql) t1 ON t.cs = t1.cs")
            ->field("t.*,count(t.orderid) as count")
            ->group("t.cs,t.source_in")
            ->order("t.source_in desc,count desc")
            ->select();
    }
}
