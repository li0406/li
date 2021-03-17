<?php
// +----------------------------------------------------------------------
// | OrderPondModel  订单池模型
// +----------------------------------------------------------------------

namespace Home\Model\Db;

use Think\Model;

class OrderPondModel extends Model
{
    protected $autoCheckFields = false;
    protected $table = 'qz_order_pond';
    /**
     * 新增编辑操作
     * @param $data
     * @param array $map
     */
    public function addOrderPond($data,$map = [])
    {
        if (empty($map)){
            //新增操作
            $result = $this->add($data);
        } else {
            //修改操作
            $result = $this->where($map)->save($data);
        }
        return $result;
    }

    /**
     * 删除操作
     * @param $map
     * @return mixed
     */
    public function delOrderPond($map)
    {
        return $this->where($map)->delete();
    }

    /**
     * 获取订单池列表信息
     */
    public function getPondList()
    {
        $buildSql = $this->field('a.id,a.pond_name,count(ct.id) as city_num,a.pond_dimension,a.pond_type')->alias('a')
            ->join('left join qz_order_pond_city ct on ct.pond_id = a.id')->group('a.id')->buildSql();
        return $this->table($buildSql)->alias('t')
            ->field('t.*,count(kf.id) AS kf_num')
            ->join('left join qz_order_pond_service kf on kf.pond_id = t.id')
            ->group('t.id')->order("t.pond_name")
            ->select();
    }

    /**
     * 获取订单池列详细
     */
    public function getPondDetail($id)
    {
        $map['a.id'] = $id;
        $detail = $this->getDetail($map);
        $cityList = $this->field('a.id,a.pond_name,ct.city_id')
            ->alias('a')
            ->join('join qz_order_pond_city ct on ct.pond_id = a.id')
            ->where($map)
            ->select();
        $kflist = $this->alias('a')
            ->field('a.id,a.pond_name,kf.kf_id')
            ->join('left join qz_order_pond_service kf on kf.pond_id = a.id')
            ->where($map)
            ->select();
        return ['detail' => $detail,'kflist' => $kflist ,'cityList' =>$cityList];
    }

    /**
     * 获取池子详情
     * @return string
     */
    public function getDetail($map)
    {
        return $this->alias('a')->where($map)->find();
    }

    /**
     * 根据客服ID获取订单池列表详细信息
     * @param int|string $kfid 客服ID
     * @param boolean $withMainPond 是否关联所有主池城市
     * @return array [订单池，城市IDS，客服ID]
     */
    public function getPondCityByKf($kfid, $withMainPond = false)
    {
        $map['kf.kf_id'] = $kfid;
        if ($withMainPond !== false) {
            $map['a.id'] = 1;
            unset($map['kf.kf_id']);
        }
        return $this->field('a.id,ct.city_id,kf.kf_id')->alias('a')
            ->join('left join qz_order_pond_service kf on kf.pond_id = a.id')
            ->join('left join qz_order_pond_city ct on ct.pond_id = a.id')
            ->where($map)
            ->select();
    }

    /**
     * 获取订单池城市
     * @return [type] [description]
     */
    public function getPondCity()
    {
        return $this->alias("a")
                   ->join("join qz_order_pond_city as b on a.id = b.pond_id")
                   ->field("a.id,b.city_id")
                   ->select();
    }

    /**
     * 获取订单池客服
     * @return [type] [description]
     */
    public function getPondKf()
    {
        return $this->alias("a")
                   ->join("join qz_order_pond_service as b on a.id = b.pond_id")
                   ->join("join qz_adminuser u on u.id = b.kf_id")
                   ->field("a.id,b.kf_id,u.name")
                   ->select();
    }

    public function getPondListById($pond_id)
    {
        $map = array(
            "a.id" => array("IN",$pond_id)
        );

        return $this->where($map)->alias("a")
                         ->join("left join qz_order_pond_city c on c.pond_id = a.id")
                         ->field("a.id,a.pond_dimension,a.pond_type,group_concat(c.city_id ORDER BY c.city_id) as cities,count(c.city_id) as count")
                         ->order("a.pond_type desc,a.id desc,count desc")
                         ->group("a.id")->select();
    }
}