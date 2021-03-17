<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrderReviewModel extends Model
{
	protected $tableName = 'order_review';
	
	public function getReviewInfoByOrderId($field = '*', $where)
	{
		return $this->field($field)->where($where)->find();
	}

    public function getReviewListByOrderIds($orderIds, $field = "*")
    {
        $map = array(
            "order_id" => array("IN", $orderIds)
        );
        return $this->where($map)->field($field)->select();
    }
	
	public function getOrderByOrderno($orderno_tel)
	{
		//获取用户信息
		$admin = getAdminUser();
		if (!empty($orderno_tel)) {
			if (strlen($orderno_tel) < 12) {
				$map['o.tel_encrypt'] = ['eq', md5($orderno_tel)];
			} else {
				$map['o.id'] = ['eq', $orderno_tel];
			}
		}
		
		$field = 'o.id,o.time_real,o.time,o.cs,o.qx,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.on,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.openeye_st,o.openeye_reger,o.openeye_sqly,o.calllong_time,o.callfast_time,o.wzd,o.source';
		if (1 != $admin['uid']) {
			//注意！更改的话下面同样更改;如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示,
			$field = $field . ',b.status AS order_blacklist_status';
		}
		
		$db = M('orders')->alias('o')->field($field);
		if (1 != $admin['uid']) {
			//如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
			$map['b.status'] = array(array('EQ', 0), array('EXP', ' IS NULL '), 'OR');
			$db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
		}
		
		$build = $db->alias('o')->where($map)
			->buildSql();
		$result = M('orders')->table($build)->alias('z')
			->field('z.*, q.cname AS city, a.qz_area AS area,IFNULL(r.order_id,0) as hasreview,p.op_name')
			->join('LEFT JOIN qz_quyu AS q ON q.cid = z.cs')
			->join('LEFT JOIN qz_area AS a ON a.qz_areaid = z.qx')
			->join('LEFT JOIN qz_order_review AS r ON r.order_id = z.id')
			->join('LEFT JOIN qz_order_pool AS p ON p.orderid = z.id')
			->select();
		return $result;
	}

    public function getOrderSearchList($module, $input, $offset = 0, $limit = 0)
    {
        $map = array(
            "o.`on`" => array("EQ", 4)
        );

        //获取用户信息
        $admin = getAdminUser();
        if (!empty($input["ordertel"])) {
            if (strlen($input["ordertel"]) < 12) {
                $map['o.tel_encrypt'] = ['eq', md5($input["ordertel"])];
            } else {
                $map['o.id'] = ['eq', $input["ordertel"]];
            }
        }

        // 订单状态
        if (!empty($input["type_fw"])) {
            $map["o.type_fw"] = array("EQ", $input["type_fw"]);
        }

        $field = 'o.id,o.time_real,o.time,o.cs,o.qx,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.`on`,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.openeye_st,o.openeye_reger,o.openeye_sqly,o.calllong_time,o.callfast_time,o.wzd,o.source';
        if (1 != $admin['uid']) {
            //注意！更改的话下面同样更改;如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示,
            $field = $field . ',b.status AS order_blacklist_status';
        }
        
        // 此处关联qz_order_info是限制必须分过单
        $db = M('orders')->alias('o')->field($field)
            ->join("inner join qz_order_info as i on i.`order` = o.id")
            ->group("o.id");
            
        if (1 != $admin['uid']) {
            //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
            $map['b.status'] = array(array('EQ', 0), array('EXP', ' IS NULL '), 'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        // 订单分配时间
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $map["new.lasttime"] = array("BETWEEN", [
                strtotime($input["begin"]),
                strtotime($input["end"]) + 86399
            ]);
            $db->join("left join qz_order_csos_new as new on new.order_id = o.id");
        }

        // 装修公司
        if (!empty($input["company"]) || !empty($input["classid"])) {
            if (!empty($input["company"])) {
                $cwhere = [
                    "u.jc" => array("EQ", $input["company"]),
                    "u.id" => array("EQ", $input["company"]),
                    "_logic" => "or"
                ];
                $map["_complex"] = $cwhere;
            }

            if (!empty($input["classid"])) {
                $map["u.classid"] = array("EQ", $input["classid"]);
            }

            $db->join("left join qz_user as u on u.id = i.com");
        }

        $build = $db->where($map)->buildSql();

        if ($module == "count") {
            $result = M()->table($build)->alias('z')->count("z.id");
        } else {
            $result = M()->table($build)->alias('z')
                ->field('z.*, q.cname AS city, a.qz_area AS area,IFNULL(r.order_id,0) as hasreview,p.op_name')
                ->join('LEFT JOIN qz_quyu AS q ON q.cid = z.cs')
                ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = z.qx')
                ->join('LEFT JOIN qz_order_review AS r ON r.order_id = z.id')
                ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = z.id')
                ->order("z.time_real desc")
                ->limit($offset, $limit)
                ->select();
        }
        
        return $result;
    }
	
	/**
	 * 获取数据
	 * @param $orderId
	 * @return mixed
	 */
	public function getDetail($orderId)
	{
		$map['order_id'] = $orderId;
		$ret = $this->where($map)->find();
		return $ret;
	}
	
	/**
	 * 获取总数
	 * @param array $reqParams
	 * @return mixed
	 */
	public function getAllCount(array $map)
	{
        $where = $map['map'];
        $where2 = [];
        if ($where['t.`status`']) {
            $where2['t.`status`'] = $where['t.`status`'];
            unset($where['t.`status`']);
        }
        
        $this->alias('ow')
            ->field('o.id,o.cs,o.qx')
            ->where($where)
            ->join('JOIN qz_orders AS o ON ow.order_id = o.id');

         if (isset($where["_complex"])) {
             $this->join("join qz_order_info i on i.`order` = o.id")
                  ->join("join qz_user u on u.id = i.com");
         }

         $buildSql = $this->group('o.id')->buildSql();

         $buildSql = $this->table($buildSql)->alias("ow")
                 ->join('JOIN safe_order_tel8 AS sot ON ow.id = sot.orderid')
                ->join('LEFT JOIN qz_quyu AS q ON q.cid = ow.cs')
                ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = ow.qx')
                ->join('LEFT JOIN qz_order_review_apply_tel t ON t.orders_id = ow.id')
                ->where($where2)
                ->field("ow.id")
                ->group("ow.id")
                ->buildSql();

         return $this->table($buildSql)->alias('t')->count();
	}
	
	/**
	 * 获取订单数据
	 * @param array $reqParams
	 * @param $start
	 * @param $end
	 * @return mixed
	 */
	public function getAll(array $map, $start, $end)
	{
        $where = $map['map'];
        $order = $map['order'];
        $where2 = [];
        if ($where['t.`status`']) {
            $where2['t.`status`'] = $where['t.`status`'];
            unset($where['t.`status`']);
        }
        $this->alias('ow')
            ->field('ow.*,o.tel,o.tel_encrypt,o.cs,o.qx')
            ->where($where)
            ->join('JOIN qz_orders AS o ON ow.order_id = o.id');


        if (isset($where["_complex"])) {
            $this->join("join qz_order_info i on i.`order` = o.id")
                ->join("join qz_user u on u.id = i.com");
        }

        $buildSql = $this->group('o.id')->buildSql();

        return $this->table($buildSql)->alias("ow")
            ->join('JOIN safe_order_tel8 AS sot ON ow.order_id = sot.orderid')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = ow.cs')
            ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = ow.qx')
            ->join('LEFT JOIN qz_order_review_apply_tel t ON t.orders_id = ow.order_id')
            ->where($where2)
            ->field("ow.*,q.cname AS city,a.qz_area AS area,IF (find_in_set(ow.cs, '". implode(',', $map['review_cs']) . "'),99,0) paixu,GROUP_CONCAT(t.status) AS apply_tel_status,GROUP_CONCAT(t.apply_id) AS apply_tel_admin,IF(ow.state = 1, 4, ow.state) as state_sort,FROM_UNIXTIME(ow.toreview_at, '%Y-%m-%d') as toreview_date")
            ->order($order)
            ->group("ow.updated_at,ow.order_id")
            ->limit($start, $end)
            ->select();

//		$where = $map['map'];
//		$order = $map['order'];
//        return $this->alias('ow')
//			->where($where)
//            ->field('ow.*,o.tel,o.tel_encrypt,q.cname AS city,a.qz_area AS area,IF (find_in_set(o.cs, "' . implode(',', $map['review_cs']) . '"),99,0) paixu,GROUP_CONCAT(t.status) AS apply_tel_status,GROUP_CONCAT(t.apply_id) AS apply_tel_admin')
//			->join('JOIN qz_orders AS o ON ow.order_id = o.id')
//			->join('JOIN safe_order_tel8 AS sot ON ow.order_id = sot.orderid')
//			->join('LEFT JOIN qz_quyu AS q ON q.cid = o.cs')
//			->join('LEFT JOIN qz_area AS a ON a.qz_areaid = o.qx')
//            ->join('LEFT JOIN qz_order_review_apply_tel t ON t.orders_id = o.id')
//            ->group('o.id')
//			->order($order)
//            ->limit($start, $end)
//			->select();
	}
	
	public function setMap(array $reqParams,array $user)
	{
        $map = [
            //目前只获取被动订单
            'ow.state' => ['eq', 2],
            'ow.is_delete' => ['eq', 1],
        ];
		if (!empty($reqParams['search'])) {
			if (preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $reqParams['search'])) {
				$map['sot.tel8'] = ['eq', $reqParams['search']];
			} else {
				$map['ow.order_id'] = ['eq', $reqParams['search']];
			}
		}

        // 查询时间
        if (!empty($reqParams['start']) && !empty($reqParams['end'])) {
            $timeEnd = ($reqParams['end'] > time() + 2) ? time() : $reqParams['end'];
            //只查询被动订单
            $map['ow.toreview_at'] = ['BETWEEN', [$reqParams['start'], $timeEnd]];
        }

		if (!empty($reqParams['type'])) {
			$map['ow.review_type'] = ['eq', $reqParams['type']];
		}

		if (!empty($reqParams['remark'])) {
			$map['ow.review_remark'] = ['eq', $reqParams['remark']];
		}

        if (!empty($reqParams['cs'])) {
            $map['o.cs'] = ['eq', $reqParams['cs']];
        }

        if (!empty($reqParams['kf'])) {
            if (is_array($reqParams['kf'])) {
                $map['ow.vest_in_uid'] = array("IN", $reqParams['kf']);
            } else {
                $map['ow.vest_in_uid'] = $reqParams['kf'];
            }
        }

        if (!empty($reqParams['applytel'])) {
            if ($reqParams['applytel'] == 1) {
                $map['t.`status`'] = ['EXP', 'IS NULL'];
            } else {
                $map['t.`status`'] = $reqParams['applytel'] - 1;
            }
        }

        if (!empty($reqParams['company'])) {
            $map["_complex"] = array(
                "u.id" => array("EQ",$reqParams['company']),
                "u.jc" => array("EQ",$reqParams['company']),
                "_logic" => "OR"
            );
        }

        //查询没有归属人的订单
        if (!empty($reqParams['new_order']) && $reqParams['new_order'] == 1) {
            $map['ow.vest_in_uid'] = ['eq', 0];
            $map['ow.review_type'] = ['eq', 1];
            $map['ow.review_uid'] = ['eq', 0];
            //删除时间筛选
            unset($map['ow.toreview_at']);
        }
        //查询有归属人,但没有记录的订单
        if (!empty($reqParams['no_call_order']) && $reqParams['no_call_order'] == 1) {
            $map['ow.vest_in_uid'] = ['neq', 0];
            $map['ow.review_type'] = ['eq', 1];
            $map['ow.review_uid'] = ['eq', 0];
            //删除时间筛选
            unset($map['ow.toreview_at']);
        }

        //排序
		$order = 'paixu desc,toreview_at desc';
		//待定单
		if ($reqParams['review_type'] == 3) {
			$order = 'paixu desc,ow.wait_order_time asc';
		} elseif ($reqParams['review_type'] == 1 || $reqParams['type'] == 1) {
            // $order = 'ow.state desc,paixu desc,ow.toreview_at desc';
			$order = 'toreview_date desc,state_sort asc';
		}
        //客服只能看到自己的订单
        // if($user['uid'] == 2){
        //     $map['ow.vest_in_uid'] = ['eq', $user['id']];
        // }
		$ret['map'] = $map;
		$ret['order'] = $order;
		$ret['review_cs'] = $this->table('qz_review_city')->getField('cs', true);
		return $ret;
	}
	
	public function updateReview($id, $data)
	{
		$map['order_id'] = $id;
		$ret = $this->where($map)->save($data);
		return $ret;
	}
	
	public function deleteReview($where)
	{
		return $this->where($where)->delete();
	}

    /**
     * 根据订单id获取 订单所属城市的装修公司的数量
     *
     * @param string $orderId
     * @return mixed
     */
    public function getVipUserCount($orderId)
    {
        $orderCond['o.id'] = ['eq', $orderId];
        $userCond['u.on'] = ['eq', 2];
        $userCond['u.classid'] = array('IN',array(3,6));
        $ret = $this->table('qz_user')->alias('u')
            ->join('qz_user_company uc on uc.userid=u.id and uc.fake=0')
            ->join('qz_orders o on o.cs= u.cs and o.id = \'' . $orderId.'\'')
            ->where($userCond)
            ->count();
        return $ret;
    }

    /**
     * 获取新单/未拨打新单 数量
     * @return mixed
     */
    public function getNewOrderReviewCount($cs_help_user = [])
    {
        $where = [
            'r.state' => ['eq', 2],
            'r.review_type' => ['eq', 1],
            'r.review_uid' => ['eq', 0],
            'r.is_delete' => ['eq', 1],
        ];

        if (!empty($cs_help_user)) {
            $where["vest_in_uid"] = array("IN", $cs_help_user);
        }

        $buildSql = $this->alias('r')
            ->field('r.order_id,r.vest_in_uid,r.review_uid')
            ->join("join qz_orders o on o.id = r.order_id")
            ->where($where)
            ->group('r.order_id')
            ->buildSql();

        return $this->table($buildSql)->alias('t')
            ->field('count(if(t.vest_in_uid = 0,1,null)) new_count,count(if(t.vest_in_uid != 0,1,null)) no_call_count')
            ->find();
    }

    public function getNewNoVestInOrder()
    {
        $where = [
            'state' => ['eq', 2],
            'review_type' => ['eq', 1],
            'vest_in_uid' => ['eq', 0],
        ];
        return $this->field('id,vest_in_uid')
            ->lock(true)
            ->where($where)
            ->order('id')
            ->find();
    }

    public function getReviewOrderInfo($id)
    {
        $where = [
            'id' => ['eq', $id]
        ];
        return $this->field('id,order_id,vest_in_uid')->where($where)->find();
    }

    public function editReviewOrderInfo($id, $save)
    {
        $where = [
            'id' => ['eq', $id]
        ];
        return $this->where($where)->save($save);
    }
}