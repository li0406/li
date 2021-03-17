<?php
// +----------------------------------------------------------------------
// | OrderPondModel  订单池所属城市关联模型
// +----------------------------------------------------------------------

namespace Home\Model\Db;

use Think\Model;

class OrderPondCityModel extends Model
{
    protected $autoCheckFields = false;
    protected $table = 'qz_order_pond_city';
    /**
     * 新增操作
     * @param $data
     * @param array $map
     */
    public function addOrderPondCity($data)
    {
        return $this->addAll($data);
    }
    /**
     * 编辑操作
     */
    public function editOrderPondCity($data,$map = [])
    {
        if (empty($map)) {
            return false;
        }
        return $this->where($map)->save($data);
    }
    /**
     * 删除操作
     * @param $map
     * @return mixed
     */
    public function delOrderPondCity($map)
    {
        return $this->where($map)->delete();
    }

    /**
     * 获取已经分配的城市
     */
    public function getCityUsed($exceptid = false)
    {
        $map['pond_id'] = ['not in', [1]];
        if ($exceptid !== false) {
            $map['pond_id'] = ['not in', [$exceptid, 1]];
        }
        $result = $this->where($map)->getField('city_id',true);
        if (empty($result)){
            return [];
        } else {
            return $result;
        }
    }

    /**
     * 查找有无城市在表
     */
    public function findCity($cityid)
    {
        $map['city_id'] = $cityid;
        return $this->where($map)->find();
    }

    /**
     * 获取城市列表信息
     * @param $cities
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPondCityList($cities,$type)
    {
        $map = array(
            "a.city_id" => array("IN",$cities),
            "b.pond_type" => array("EQ",$type)
        );
        return $this->where($map)->alias("a")
                    ->join("join qz_order_pond b on a.pond_id = b.id")
                    ->join("join qz_quyu q on q.cid = a.city_id")
                    ->field("a.city_id,q.cname,b.pond_name")
                    ->select();
    }

    /**
     * @param $获取非当前池子可使用城市列表
     */
    public function getPondUseCityList($id,$type)
    {
        $map = array(
            "a.pond_id" => array("NEQ",$id),
            "b.pond_type" => array("EQ",$type)
        );
        return $this->where($map)->alias("a")
                                 ->join("join qz_order_pond b on a.pond_id = b.id")
                                 ->field("city_id")->select();
    }
}