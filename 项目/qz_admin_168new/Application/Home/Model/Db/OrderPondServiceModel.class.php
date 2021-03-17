<?php
// +----------------------------------------------------------------------
// | OrderPondModel  订单池所属客服关联模型
// +----------------------------------------------------------------------

namespace Home\Model\Db;

use Think\Model;

class OrderPondServiceModel extends Model
{
    protected $autoCheckFields = false;
    protected $table = 'qz_order_pond_service';
    /**
     * 新增操作
     * @param $data
     * @param array $map
     */
    public function addOrderPondService($data)
    {
        return $this->addAll($data);
    }

    /**
     * 编辑操作
     */
    public function editOrderPondService($data,$map = [])
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
    public function delOrderPondService($map)
    {
        return $this->where($map)->delete();
    }

    /**
     * 获取已经分配的客服
     */
    public function getServerUsed($exceptid = false)
    {
        $map['pond_id'] = ['not in', [1]];
        if ($exceptid !== false) {
            $map['pond_id'] = ['not in', [$exceptid, 1]];
        }
        $result = $this->where($map)->getField('kf_id',true);
        if (empty($result)){
            return [];
        } else {
            return $result;
        }
    }

    /**
     * 查询客服是否分配
     */
    public function isFind($kfid)
    {
        $map['kf_id'] = $kfid;
        return $this->where($map)->find();
    }

    /**
     * 获取客服数量
     */
    public function getKfNum($map = [])
    {
        return $this->where($map)->count('kf_id');
    }

    public function getPondServiceList($ids)
    {
        $map = array(
            "a.kf_id" => array("IN",$ids)
        );
        return $this->where($map)->alias("a")
            ->join("join qz_order_pond b on a.pond_id = b.id")
            ->join("join qz_adminuser u on u.id = a.kf_id")
            ->field("a.kf_id,u.name,b.pond_name")
            ->select();
    }

    /**
     * @param $获取非当前池子可使用城市列表
     */
    public function getPondUseServiceList($id)
    {
        $map = array(
            "pond_id" => array("NEQ",$id)
        );
        return $this->where($map)->field("kf_id")->select();
    }
}