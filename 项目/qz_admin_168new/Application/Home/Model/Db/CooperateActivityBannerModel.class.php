<?php
namespace Home\Model\Db;
use Think\Model;
class CooperateActivityBannerModel extends Model
{
    protected $tableName = 'cooperate_activity_banner';
    public function getBannerInfo($type){
        $map['type'] = ['eq',$type];
        return  $this->field('id,pc_url,m_url')->where($map)->select();
    }

    public function addBanner($data){
        return $this->add($data);
    }

    public function updateBanner($type,$data){
        $map['type'] = ['eq',$type];
        return $this->where($map)->save($data);
    }

}