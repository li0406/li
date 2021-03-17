<?php

namespace Common\Model\Db;
use Think\Model;
class AdvPartnerModel extends Model
{
    //获取异业广告信息
    public function getPartneradLocationInfo($post){
        $map["b.id"] = array("EQ",$post);
        $map["b.is_use"] = array("EQ",1);
        return  M('adv_partner_location')->alias("b")
                ->field("id,module")
                ->where($map)
                ->find();
    }
    //获取异业广告信息
    public function getPartneradInfo($where){
        return M('adv_partner')->alias("a")
            ->field("id,title,partner,start,end,pc_img,pc_url,m_img,m_url,uv,click_num,time,pc_coefficient,m_coefficient")
            ->where($where)
            ->select();
    }

    //新增异业广告uv
    public function addUv($post){
        $map["id"] = array("EQ",$post);
        return M('adv_partner')->where($map)->setInc('uv',1);
    }

    //新增异业广告click
    public function addClick($post){
        $map["id"] = array("EQ",$post);
        return M('adv_partner')->where($map)->setInc('click_num',1);
    }

}