<?php


namespace app\common\model\logic;


use app\common\model\db\Adminuser;
use app\common\model\db\Area;
use app\common\model\db\City;
use app\common\model\db\Province;
use app\common\model\db\Quyu;

class CityLogic
{

    public function getCitys()
    {
        $model = new Quyu();
        $cityids = AdminuserLogic::getCityIds();
        $map = [];
        $map[] = ["cid", "in", $cityids];
        $info = $model->getAllQuyu2($map);
        return $info;
    }


}