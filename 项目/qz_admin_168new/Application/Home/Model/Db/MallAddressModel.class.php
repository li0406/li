<?php


namespace Home\Model\Db;


use Think\Model;

class MallAddressModel extends Model
{
    protected $tableName = "mall_address";

    /**
     * 检查用户是否有收货地址
     * @param $uuids
     * @return mixed
     */
    public function checkHadAddress($uuids)
    {
        $map = [];
        if (is_array($uuids)) {
            $map['uuid'] = array('in', $uuids);
        } else {
            $map['uuid'] = $uuids;
        }
        return $this->where($map)->field("uuid")->group('uuid')->select();

    }


    public function getAllAddressListByuuid($uuid)
    {
        $map = [];
        $map['ma.uuid'] = $uuid;
        return $this->alias('ma')
            ->where($map)
            ->field('ma.*,p.qz_province as province_name,q.cname as city_name,a.qz_area as area_name')
            ->join("qz_province p on p.qz_provinceid = ma.province")
            ->join("qz_quyu q on q.cid = ma.city")
            ->join("qz_area a on a.qz_areaid = ma.area")
            ->order('ma.created_at desc,ma.id desc')
            ->select();

    }

}