<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class OrderNoticeTemplateModel extends Model
{

	/**
	 * 添加模版
	 */
	public function addTemplate($data)
	{
		return M("order_notice_template")->add($data);
	}

	/**
	 * 添加模版
	 */
	public function editTemplate($id,$data)
	{
		$map = array(
		    "id" => array("EQ",$id)
		);
		return M("order_notice_template")->where($map)->save($data);
	}

	public function getTemplateInfo($id)
	{
		$map = array(
		    "id" => array("EQ",$id)
		);
		return M("order_notice_template")->where($map)->find();
	}

	/**
	 * 获取模版列表
	 * @return [type] [description]
	 */
	public function getTemplateList($cs)
	{
		return M("order_notice_template")->alias("a")
										 ->join("left join qz_quyu as q on find_in_set(q.cid,a.city)")
										 ->field("a.*,group_concat(q.cname) as city_names")
										 ->group("a.id")
		                                 ->order("id desc")->select();
	}

	/**
	 * 获取城市模版
	 * @param  [type] $cs [description]
	 * @return [type]     [description]
	 */
	public function getCityTemplate($cs)
	{
		$map["_complex"] = array(
		   "_string" => "find_in_set($cs,city) or city = '' "
		);
		return M("order_notice_template")->where($map)->field("id,title")->select();
	}

	/**
	 * 根据模版ID查询模版信息
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getTemplateById($id)
	{
		$map = array(
		    "id" => array("EQ",$id)
		);
		return M("order_notice_template")->where($map)->find();
	}

	/**
	 * 删除模版
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function delTemplate($id)
	{
		$map = array(
		    "id" => array("EQ",$id)
		);
		return M("order_notice_template")->where($map)->delete();
	}
}