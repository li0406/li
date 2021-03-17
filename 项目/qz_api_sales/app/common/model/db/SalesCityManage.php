<?php

/**
 * 
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-16 09:54:22
 */

namespace app\common\model\db;

use think\Model;

class SalesCityManage extends Model{

    protected $autoWriteTimestamp = false;

    /**
     * 获取列表
     * @param  [type] $where [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function getList($where, $field = '*'){
        return $this->where($where)->field($field)->select()->toArray();
    }

    /**
     * [getAllCitys 获取所有城市]
     * @return array    $result         [所有城市数组]
     */
    public function getAllCitys($role = 0) {
        $dbQuery = $this->alias("a");

        if(!empty($role)){
            $dbQuery->where("a.dept", $role);
        }

        return $dbQuery
            ->join("qz_quyu q", "q.cname = a.city", "left")
            ->field("a.id,a.city,q.cid")->order("id asc")
            ->select()->toArray();
    }

    /**
     * [getManageCitys 获取所有城市]
     * @param  array    $where          查询条件
     * @return array    $result         返回城市数组
     */
    public function getManageCitys($where = []) {
        return $this->alias("a")->where($where)
            ->join("qz_quyu q", "q.cname = a.city", "left")
            ->field("a.id,a.city,q.cid")->order("id asc")
            ->select()->toArray();
    }
}