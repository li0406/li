<?php
namespace Home\Model\Db;
use Think\Model;
class CooperateActivityCityModel extends Model
{
    protected $tableName = 'cooperate_activity_city';
    public function addCity($data){
        return  $this->addAll($data);
    }

    public function deleteCity($activity_id){
        $map['aid'] = ['eq',$activity_id];
        return $this->where($map)->delete();
    }

}