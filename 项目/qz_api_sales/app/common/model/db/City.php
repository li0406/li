<?php


namespace app\common\model\db;


use think\Model;

class City extends Model
{
    protected $table = "qz_city";

    // 获取所有城市
    public function getAllCity()
    {
        return $this->field('qz_cityid, qz_city, fatherid')->select();
    }

}