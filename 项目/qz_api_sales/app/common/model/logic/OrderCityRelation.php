<?php
// +----------------------------------------------------------------------
// | OrderCityRelation 相邻城市
// +----------------------------------------------------------------------
namespace app\common\model\logic;
use \app\common\model\db\OrderCityRelation as CityRelationDb;

class OrderCityRelation
{
    public function getRelationCity($cs){
        $relationDb = new CityRelationDb();
        return $relationDb->getRelationCityList($cs);
    }
}