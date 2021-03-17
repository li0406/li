<?php

namespace Home\Model\Db;
use Think\Model;
class AdvPartnerModel extends Model
{
    public function getPartneradListCount($where){
        if(!empty($where['state'])|| $where['state'] === '0'){
            $map["a.is_use"] = $where['state'];
        }
        if(!empty($where['partner_name'])){
            $map["a.title"] = array("LIKE","%".$where['partner_name']."%");
        }
        if(!empty($where['start_time'])){
            $map['a.start'] = ['ELT', strtotime(trim($where['start_time']))];
        }
        if (!empty($where['end_time'])){
            $map['a.end'] = ['EGT', strtotime(trim($where['end_time']))];
        }
        return M('adv_partner')->where($map)->alias("a")
            ->field("a.id")
            ->count();
    }

    public function getPartneradList($where,$pageIndex,$pageCount){
        if(!empty($where['state'])|| $where['state'] === '0'){
            $map["a.is_use"] = $where['state'];
        }
        if(!empty($where['partner_name'])){
            $map["a.title"] = array("LIKE","%".$where['partner_name']."%");
        }
        if(!empty($where['start_time'])){
            $map['a.start'] = ['ELT', strtotime(trim($where['start_time]']))];
        }
        if (!empty($where['end_time'])){
            $map['a.end'] = ['EGT', strtotime(trim($where['end_time]']))];
        }
        return M('adv_partner')->where($map)->alias("a")
            ->field("a.id,title,pc_img,m_img,start,end,is_use,uv,click_num,time,pc_coefficient,m_coefficient,m_jiaju,m_zx,pc_zx,pc_jiaju")
            ->order("a.time desc")
            ->limit($pageIndex . "," . $pageCount)
            ->select();
    }

    //异业广告位置管理
    public function getPartneradLocationList(){
        $map['a.is_use'] = ['EQ', 1];
        return M('adv_partner_location')->where($map)->alias("a")
            ->field("a.id,name,url,module")
            ->order("a.id")
            ->select();
    }

    //更新异业广告
    public function savePartnerad($data,$id){
        $map["id"] = array("EQ",$id);
        return M('adv_partner')->where($map)->save($data);
    }

    //新增异业广告
    public function addPartnerad($data){
        return M('adv_partner')->add($data);
    }

    //删除异业广告
    public function deletePartnerad($map){
        return M('adv_partner')->where($map)->delete();
    }

    //获取异业广告信息
    public function getPartneradById($id){
        $map["id"] = array("EQ",$id);
        return M('adv_partner')->where($map)->find();
    }

    public function getLocationListCount($where){
        if(!empty($where['enabled'])|| $where['enabled'] === '0'){
            $map["a.is_use"] = $where['enabled'];
        }
        if(!empty($where['page_name'])){
            $map["a.name"] = array("LIKE","%".$where['page_name']."%");
        }
        if (!empty($where['belong-site'])){
            $map['a.module'] = $where['belong-site'];
        }
        return M('adv_partner_location')->where($map)->alias("a")
            ->field("a.id")
            ->count();
    }

    public function getLocationList($where,$pageIndex,$pageCount){
        if(!empty($where['enabled']|| $where['enabled'] === '0')){
            $map["a.is_use"] = $where['enabled'];
        }
        if(!empty($where['page_name'])){
            $map["a.name"] = array("LIKE","%".$where['page_name']."%");
        }
        if (!empty($where['belong-site'])){
            $map['a.module'] = $where['belong-site'];
        }
        return M('adv_partner_location')->where($map)->alias("a")
            ->field("a.id,name,url,module,is_use")
            ->order("a.id desc")
            ->limit($pageIndex . "," . $pageCount)
            ->select();
    }

    //更新异业广告位置
    public function saveLocation($data,$id){
        $map["id"] = array("EQ",$id);
        return M('adv_partner_location')->where($map)->save($data);
    }

    //新增异业广告位置
    public function addLocation($data){
        return M('adv_partner_location')->add($data);
    }

    //删除异业广告位置
    public function deleteLocation($map){
        return M('adv_partner_location')->where($map)->delete();
    }

    //获取异业广告位置信息
    public function getLocationById($id){
        $map["id"] = array("EQ",$id);
        return M('adv_partner_location')->where($map)->find();
    }
}