<?php

namespace Common\Model\Logic;

class PartneradLogicModel
{
    //获取广告详细信息
    public function getPartneradInfo($post){
        $location = D('Common/Db/AdvPartner')->getPartneradLocationInfo($post);
        if($location){
            $where['is_use']  = array('EQ',1);
            switch ($location["module"]){
                case 1:
                    $where["_string"]  = "FIND_IN_SET(".$location["id"].",pc_zx)";
                    break;
                case 2:
                    $where["_string"]  = "FIND_IN_SET(".$location["id"].",m_zx)";
                    break;
                case 3:
                    $where["_string"]  = "FIND_IN_SET(".$location["id"].",pc_jiaju)";
                    break;
                case 4:
                    $where["_string"]  = "FIND_IN_SET(".$location["id"].",m_jiaju)";
                    break;
                default:
                    break;
            }
//            $where[]  = array('end','GT',time());
//            $where[]  = array('start','LT',time());
            $info = D('Common/Db/AdvPartner')->getPartneradInfo($where);
            if($info){
                $weight = 0;
                $tempdata = array ();
                foreach ($info as $one) {
                    if($location["module"] == 1 || $location["module"] == 3){
                        $weight += $one['pc_coefficient'];
                        for ($i = 0; $i < $one['pc_coefficient']; $i++) {
                            $tempdata[] = $one;
                        }
                    }elseif($location["module"] == 2 || $location["module"] == 4){
                        $weight += $one['m_coefficient'];
                        for ($i = 0; $i < $one['m_coefficient']; $i++) {
                            $tempdata[] = $one;
                        }
                    }
                }
                $use = rand(0, $weight -1);
                $one = $tempdata[$use];
                return $one;
            }
        }
        return false;
    }

    //新增广告Uv
    public function addUv($post=""){
        if(!empty($post)){
            return D('Common/Db/AdvPartner')->addUv($post);
        }
        return false;
    }

    //新增广告click
    public function addClick($post=""){
        if(!empty($post)){
            return D('Common/Db/AdvPartner')->addClick($post);
        }
        return false;
    }
}
