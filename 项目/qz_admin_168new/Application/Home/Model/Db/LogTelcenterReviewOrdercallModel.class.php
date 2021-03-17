<?php

namespace Home\Model\Db;

Use Think\Model;

/**
 *   订单回访电话日志表
 */
class LogTelcenterReviewOrdercallModel extends Model
{
	protected $tableName = "log_telcenter_review_ordercall";
	
	/**
	 * 获取订单最后一次呼叫信息
	 * @param string $value [description]
	 * @return [type]        [description]
	 */
	public function findLastTelInfo($id)
	{
		$map = array(
			"orderid" => array("EQ", $id)
		);
		return M("log_telcenter_review_ordercall")->where($map)->order("id desc")->find();
	}
	
	/**
	 * 获取最近一天的电话日志记录
	 * @param string $value [description]
	 * @return [type]        [description]
	 */
	public function getOrderLastLogOneDay($orderid, $date)
	{
		$map = array(
			"orderid" => array("EQ", $orderid),
			"time_add" => array("EGT", $date)
		);
		return M("log_telcenter_review_ordercall")->where($map)->select();
	}
	
	/**
	 * 添加记录
	 * @param [type] $data [description]
	 */
	public function addLog($data)
	{
		return M('log_telcenter_review_ordercall')->add($data);
	}
	
	/**
	 * @param $data
	 * @return bool|string
	 */
	public function addAllLog($data)
	{
		return M('log_telcenter_review_ordercall')->addAll($data);
	}
	
	/**
	 * 电话记录日志
	 * @param  [type] $orderid [description]
	 * @return [type]          [description]
	 */
	public function tellCall($orderid)
	{
		$map = array(
			"a.orderid" => array("EQ", $orderid)
		);
		
		$buildSql = M('log_telcenter_review_ordercall a')->where($map)
			->join("join qz_log_telcenter tel on tel.callSid = a.callSid ")
			->field("a.time_add,a.admin_user,tel.byetype,tel.starttime,tel.endtime,a.callSid,a.fromtel,tel.recordurl,tel.action")
			->order("tel.id desc,a.id desc")->buildSql();
		return M('log_telcenter_review_ordercall')->alias("t")->table($buildSql)
			->field("t.*,IF(t.action = 'Hangup',1,0) mark")
			->group("t.callSid")->select();
	}
	
	/**
	 * 通过订单号获取 通话次数
     * @param  string $orderIds 订单号数组
     * @return  mixed
	 */
	public function getOrderCallCountByOrderIds($orderIds)
	{
		if (!empty($orderIds)) {
			if (!is_array($orderIds)) {
				$orderIds = array($orderIds);
			}
			$map = array(
				'o.orderid' => array('IN', $orderIds)
			);
			$build = M('log_telcenter_ordercall')->alias('o')
				->field('o.orderid AS id')
				->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
				->where($map)
				->group('t.callSid')
				->buildSql();
			$result = M()->table($build)->alias('z')->field('z.id, count(*) AS repeat_count')->group('z.id')->select();
			return $result;
		}
		return false;
	}

	public function getHollyOrderCallCountByOrderIds($orderIds){
        if (!empty($orderIds)) {
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            $map = array(
                'o.order_id' => array('IN', $orderIds)
            );
            $build = M('log_holly_order_telcenter')->alias('o')
                ->field('o.order_id AS id')
                ->join('qz_log_holly_telcenter AS t ON o.call_sid = t.call_sid')
                ->where($map)
                ->group('t.call_sid')
                ->buildSql();
            $result = M()->table($build)->alias('z')->field('z.id, count(*) AS repeat_count')->group('z.id')->select();
            return $result;
        }
        return false;
    }

    /**
     * 通过订单号获取 通话次数
     * @param  string $orderIds 订单号数组
     * @return  mixed
     */
	public function getReviewOrderCallCount($orderIds, $adminIds = [])
	{
		if (!empty($orderIds)) {
			if (!is_array($orderIds)) {
				$orderIds = array($orderIds);
			}

			$map = array(
				'o.orderid' => array('IN', $orderIds)
			);

			if (!empty($adminIds)) {
				$map["o.admin_id"] = array("IN", $adminIds);
			}

			$build = M('log_telcenter_review_ordercall')->alias('o')->where($map)
				->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
				->field('o.orderid AS id')
				->group('t.callSid')
				->buildSql();

			$result = M()->table($build)->alias('z')
				->field('z.id, count(*) AS repeat_count')
				->group('z.id')
				->select();

			return $result;
		}

		return false;
	}


    /**
     * 通过订单号获取 通话次数
     * @param  string $orderIds 订单号数组
     * @return  mixed
     */
    public function getHeLiReviewOrderCallCount($orderIds, $adminIds = [])
    {
        if (!empty($orderIds)) {
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }

            $map = array(
                'o.order_id' => array('IN', $orderIds)
            );

            if (!empty($adminIds)){
            	$map["o.op_uid"] = array("IN", $adminIds);
            }

            $build = M('log_holly_review_telcenter')->alias('o')->where($map)
                ->join('qz_log_holly_telcenter AS t ON o.call_sid = t.call_sid')
                ->field('o.order_id AS id')
                ->group('t.call_sid')
                ->buildSql();

            $result = M()->table($build)->alias('z')
            	->field('z.id, count(*) AS repeat_count')
            	->group('z.id')
            	->select();

            return $result;
        }
        return false;
    }

    /**
     * 通过订单号获取 通话次数
     * @param  string $orderIds 订单号数组
     * @return  mixed
     */
    public function getClinkReviewOrderCallCount($orderIds, $adminIds = [])
    {
        if (!empty($orderIds)) {
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            
            $map = array(
                'o.order_id' => array('IN', $orderIds)
            );

            if (!empty($adminIds)){
            	$map["o.op_uid"] = array("IN", $adminIds);
            }

            $build = M('log_clink_review_telcenter')->alias('o')->where($map)
                ->join('qz_log_clink_telcenter AS t ON o.call_sid = t.call_sid')
                ->field('o.order_id AS id')
                ->group('t.call_sid')
                ->buildSql();

            $result = M()->table($build)->alias('z')
            	->field('z.id, count(*) AS repeat_count')
            	->group('z.id')
            	->select();

            return $result;
        }
        return false;
    }


	/**
	 * 通过订单号获取 通话次数
	 * @param    $orderId 订单号
	 * @return   通话列表 或 false
	 */
	public function getOrderCallListByOrderId($orderId, $adminIds = [])
	{
		if (!empty($orderId)) {
			$map = array(
				'o.orderid' => $orderId
			);

            if (!empty($adminIds)) {
                $map["o.admin_id"] = array("IN", $adminIds);
            }

			$result = M('log_telcenter_review_ordercall')->alias('o')->where($map)
				->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
                ->field('*, o.orderid AS orders_id')
				->order('t.time_add')
				->select();

			return $result;
		}
		return false;
	}
	
	/**
	 * 通过订单号获取 通话次数
	 * @param    $orderId 订单号
	 * @return   通话列表 或 false
	 */
	public function getBackOrderCallListByOrderId($orderId)
	{
		$data = D('Home/Db/CompanyLiangFangTelBack')->getOrderTelBack($orderId);
		if (!$data['ordercall_id']) {
			return [];
		}
		if (!empty($orderId)) {
			$map = array(
				'o.orderid' => $orderId,
				'o.id' => ['in', $data['ordercall_id']],
			);
			$result = M('log_telcenter_review_ordercall')->alias('o')
				->field('*, o.orderid AS orders_id')
				->join('qz_log_telcenter AS t ON o.callSid = t.callSid')
				->where($map)
				->order('t.time_add')
				->select();
			return $result;
		}
		return false;
	}
	
	//挂机代码对应的信息，鉴权挂机类型说明
	//@http://docs.yuntongxun.com/index.php/%E9%89%B4%E6%9D%83%E6%8C%82%E6%9C%BA%E7%B1%BB%E5%9E%8B%E8%AF%B4%E6%98%8E
	/**
	 * [get_byetype_info 挂机代码对应的信息]
	 * @param  [int] $byetype [挂机代码]
	 * @return [string]          [信息]
	 */
	public function getByeTypeInfo($byetype)
	{
		$reinfo = '';
		if (empty($byetype)) {
			return "";
		}
		switch ($byetype) {
			case '1':
				$reinfo = "通话中取消回拨、直拨和外呼的正常结束通话";
				break;
			case '2':
				$reinfo = "账户欠费或者设置的通话时间到";
				break;
			case '3':
				$reinfo = "回拨通话中主叫挂断，正常结束通话";
				break;
			case '4':
				$reinfo = "回拨通话中被叫挂断，正常结束通话";
				break;
			case '-1':
				$reinfo = "被叫没有振铃就收到了挂断消息";
				break;
			case '-2':
				$reinfo = "呼叫超时没有接通被挂断";
				break;
			case '-5':
				$reinfo = "被叫通道建立了被挂断";
				break;
			case '-6':
				$reinfo = "系统鉴权失败";
				break;
			case '-7':
				$reinfo = "第三方鉴权失败";
				break;
			case '-8':
				$reinfo = "直拨被叫振铃了挂断";
				break;
			case '-3':
				$reinfo = "回拨主叫接通了主叫挂断";
				break;
			case '-4':
				$reinfo = "回拨主叫通道创建了被挂断";
				break;
			case '-9':
				$reinfo = "回拨被叫振铃了挂断";
				break;
			case '-10':
				$reinfo = "回拨主叫振铃了挂断";
				break;
			default:
				# code...
				break;
		}
		return $reinfo;
	}

	/**
	 * 通过订单号获取 通话次数
	 * @param    $orderIds 订单号数组
	 * @return   通话次数 或 false
	 */
	public function getOrderCallCountDetailByOrderIds($orderIds)
	{
		if (!empty($orderIds)) {
			if (!is_array($orderIds)) {
				$orderIds = array($orderIds);
			}
			$map = array(
				'orderid' => array('IN', $orderIds)
			);
			$buildSql = M('log_telcenter_review_ordercall')
				->field('orderid,count(id) as boda_count,callSid')
				->where($map)
				->group('orderid')
				->buildSql();
			return M('log_telcenter_review_ordercall')->table($buildSql)->alias('a')
				->field('a.*,count(if(b.action = "Hangup" AND endtime > 0,1,null)) as botong_count')
				->join('JOIN qz_log_telcenter b ON a.callSid = b.callSid')
				->group('b.callSid')
				->select();
		}
		return false;
	}
}