<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class ReviewCityModel extends Model
{
	protected $tableName = "review_city";
	
	/**
	 * 获取不在review_city 表中的数据
	 * @return mixed
	 */
	public function getNotInCities()
	{
		$buildSql = $this->alias('t1')->field('cs')->where('a.cid = t1.cs')->order('t1.id asc')->buildSql();
		$ret = M("quyu")->alias("a")
			->where("a.type=1 AND cid != 000001")
			->where("not exists ($buildSql)")
			->field("a.cid,concat(upper(left(a.px_abc,1)),' ',a.cname) AS first_abc")
			->order("a.bm,xh")
			->select();
		return $ret;
	}
	
	/**
	 * 获取review_city 表中的数据
	 * @return mixed
	 */
	public function getReviewFieldCities()
	{
		$ret = $this->order('id asc')->getField('cs',true);
		return $ret;
	}
	
	/**
	 * 删除所有
	 * @return mixed
	 */
	public function drop()
	{
	    return $this->where(1)->delete();
	}

    public function getReviewCityInfo($cs)
    {
        return $this->where(['cs' => ['eq', $cs]])->find();
    }


    // 回访城市查询条件
    public function getCityPageMap($input){
    	$map = array(
    		"q.is_open_city" => array("EQ", 1),
    		"q.cid" => array("NEQ", "000001")
    	);

    	if (!empty($input["cid"])) {
    		$map["q.cid"] = array("EQ", $input["cid"]);
    	}

    	if (!empty($input["state"])) {
    		if ($input["state"] == "1") {
    			$map["a.id"] = array("EXP", "is not null");
    		} else {
    			$map["a.id"] = array("EXP", "is null");
    		}
    	}

    	return $map;
    }

    // 回访城市查询数量
    public function getCityPageCount($input){
    	$map = $this->getCityPageMap($input);

    	return M("quyu")->alias("q")->where($map)
    		->join("left join qz_review_city as a on a.cs = q.cid")
    		->count("q.cid");
    }

    // 回访城市查询数据
    public function getCityPageList($input, $offset = 0, $limit = 0){
    	$map = $this->getCityPageMap($input);

    	return M("quyu")->alias("q")->where($map)
    		->join("left join qz_review_city as a on a.cs = q.cid")
    		->join("left join qz_adminuser as u on u.id = a.operator")
    		->field([
    				"q.cid", "q.cname",
                    "a.visit_begin", "a.operator", "a.updated_at", "a.mianji_min", "a.mianji_max",
    				"u.`user` as operator_name", "if(a.id is null, 2, 1) as state"
    			])
    		->order("state asc,a.visit_begin desc,q.cid desc")
    		->limit($offset, $limit)
    		->select();
    }

    // 获取城市信息
    public function getCityInfo($city_id){
        $map = array(
            "q.cid" => array("EQ", $city_id)
        );

        return M("quyu")->alias("q")->where($map)
            ->join("left join qz_review_city as a on a.cs = q.cid")
            ->field(["q.cid", "q.cname", "a.*"])
            ->find();
    }

    // 编辑
    public function updateInfo($cs, $data){
        $map = array(
            "cs" => array("EQ", $cs)
        );

        return M("review_city")->where($map)->save($data);
    }

    // 删除
    public function deleteInfo($cs){
        $map = array(
            "cs" => array("EQ", $cs)
        );

        return M("review_city")->where($map)->delete();
    }

    // 获取城市设置日志
    public function getCityLog($cs){
        $map = array(
            "cs" => array("EQ", $cs)
        );

        return M("review_city_log")->where($map)->order("created_at desc")->select();
    }

    // 增加日志
    public function addCityLog($data){
        return M("review_city_log")->add($data);
    }


    public function getAllOpenCity(){
        $map = array(
            "q.is_open_city" => array("EQ", 1),
            "q.cid" => array("NEQ", "000001")
        );

        return M("quyu")->alias("q")->where($map)
            ->join("left join qz_review_city as a on a.cs = q.cid")
            ->field([
                    "q.cid", "q.cname", "if(a.id is null, 2, 1) as state",
                    "upper(left(q.px_abc,1)) AS first_abc"
                ])
            ->order("q.bm,q.xh")
            ->select();
    }
}