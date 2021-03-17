<?php

namespace Home\Model\Logic;

class PartneradLogicModel
{
    //获取广告列表
    public function getPartneradList($where){
        $count =  D('Home/Db/AdvPartner')->getPartneradListCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,8);
            $show = $p->show();
            $list = D('Home/Db/AdvPartner')->getPartneradList($where,$p->firstRow, $p->listRows);
            //处理页面位置数量
            foreach ($list as $key => $value) {
                $pc_zx = 0;$pc_jiaju = 0;$m_zx = 0;$m_jiaju = 0;
                if(!empty($value["pc_zx"])){
                    $pc_zx = count(explode(",",$value["pc_zx"]));
                }
                if(!empty($value["pc_jiaju"])){
                    $pc_jiaju = count(explode(",",$value["pc_jiaju"]));
                }
                if(!empty($value["m_zx"])){
                    $m_zx = count(explode(",",$value["m_zx"]));
                }
                if(!empty($value["m_jiaju"])){
                    $m_jiaju = count(explode(",",$value["m_jiaju"]));
                }
                $list[$key]["pc"] = $pc_zx+$pc_jiaju;
                $list[$key]["m"] = $m_zx+$m_jiaju;
            }
            return ['list' => $list, 'page' => $show];
        }
    }

    //获取广告详细信息
    public function getPartneradById($id){
        $info = D('Home/Db/AdvPartner')->getPartneradById($id);
        $list = D('Home/Db/AdvPartner')->getPartneradLocationList();
        //处理广告位置数据
        $location=[];$location2=[];
        $locationtemp2 = array_merge(explode(',',$info["pc_zx"]),explode(',',$info["m_zx"]),explode(',',$info["pc_jiaju"]),explode(',',$info["m_jiaju"]));
        foreach ($list as $key => $value) {
            $locationtemp[] = $value["id"];
        }
        $locationtemp1 = array_diff($locationtemp,$locationtemp2);
        foreach ($list as $key => $value) {
            foreach ($locationtemp1 as $key1 => $value1) {
                if($value["id"] == $value1){
                    $location[$value["module"]][] = $value;
                }
            }
            foreach ($locationtemp2 as $key2 => $value2) {
                if($value["id"] == $value2){
                    $location2[$value["module"]][] = $value;
                }
            }
        }
        return ['info' => $info, 'location' => $location,'locationtemp' => $location2];
    }

    public function deletePartnerad($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id,
        );
        return D('Home/Db/AdvPartner')->deletePartnerad($map);
    }

    //获取广告位置列表
    public function getPartneradLocationList(){
        $list = D('Home/Db/AdvPartner')->getPartneradLocationList();
        $location = [];
        foreach ($list as $key => $value) {
            $location[$value["module"]][] = $value;
        }
        return $location;
    }

    //更新广告
    public function savePartnerad($data,$post=""){
        if(!empty($post)){
            return D('Home/Db/AdvPartner')->savePartnerad($data,$post);
        }else{
            return D('Home/Db/AdvPartner')->addPartnerad($data);
        }
    }

    //获取广告列表
    public function getLocationList($where){
        $count =  D('Home/Db/AdvPartner')->getLocationListCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,8);
            $show = $p->show();
            $list = D('Home/Db/AdvPartner')->getLocationList($where,$p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
    }

    //获取广告详细信息
    public function getLocationById($id){
        $info = D('Home/Db/AdvPartner')->getLocationById($id);
        return $info;
    }

    public function deleteLocation($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id,
        );
        return D('Home/Db/AdvPartner')->deleteLocation($map);
    }

    //更新广告
    public function saveLocation($data,$post=""){
        if(!empty($post)){
            return D('Home/Db/AdvPartner')->saveLocation($data,$post);
        }else{
            return D('Home/Db/AdvPartner')->addLocation($data);
        }
    }

    //获取api列表
    public function getHzsApiList($where){
        $count =  D('Partner')->getHzsApiListCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D('Partner')->getHzsApiList($where,$p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
    }
}
