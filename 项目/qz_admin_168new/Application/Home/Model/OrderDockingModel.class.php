<?php
namespace Home\Model;
Use Think\Model;

/**
*	对接记录表
*/
class OrderDockingModel extends Model
{
	/**
	 * 查询对接信息
	 * @param  [string] $orderid [订单ID]
	 * @return array
	 */
	public function getDockingInfoCount($orderid)
	{
		$map = array(
		   "order_id" => array("EQ",$orderid)
		);
		return M("order_docking")->where($map)->count();
	}

	/**
	 * 添加对接记录
	 * @param [type] $data [添加数据]
	 */
	public function addDocking($data)
	{
		return M("order_docking")->add($data);
	}

	/**
	 * 删除对接信息
	 * @param  [type] $order_id [description]
	 * @return [type]           [description]
	 */
	public function delDocking($order_id)
	{
		$map = array(
		   "order_id" => array("EQ",$order_id)
		);
		return M("order_docking")->where($map)->delete();
	}
}