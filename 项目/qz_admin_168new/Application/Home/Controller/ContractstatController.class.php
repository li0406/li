<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;


class ContractstatController extends HomeBaseController
{

    public function index()
    {
      $this->_error();
    }

    public function contractStockStat()
    {
        //每月的合同库存
        $list = $this->getContractStockStat(I("get.begin"),I("get.end"),I("get.dep"));
        $this->assign("list",$list);
        $this->display();
    }

    public function contractDetailsStat()
    {

        //商务外销只能查看本部门的合同信息
        if (!in_array(session("uc_userinfo.uid"),array(1,46,37,51))) {
            //商务部门只能看商务的
            if (session("uc_userinfo.department_id") == 6) {
                //商务助理/商务经理 查看全部商务
                //销售只看自己的或者自己管辖的
                $result = $this->getDeptCityList(1);
                if (!in_array(session("uc_userinfo.uid"),array(39,45))) {
                    if (count($result["manager"][session("uc_userinfo.id")]) > 0) {
                       $ids = $result["manager"][session("uc_userinfo.id")];
                    } elseif (count($result["group"][session("uc_userinfo.id")]) > 0){
                       $ids = $result["group"][session("uc_userinfo.id")];
                    }
                    $ids[] = session("uc_userinfo.id");
                } else {
                    $ids = $result["all"];
                }
                $isshow = $dept = 1;
            } else {
                //外销经理、外销助理查看外销部
                $result = $this->getDeptCityList(2);
                if (!in_array(session("uc_userinfo.uid"),array(59,67))) {
                    if (count($result["manager"][session("uc_userinfo.id")]) > 0) {
                       $ids = $result["manager"][session("uc_userinfo.id")];
                    } elseif (count($result["group"][session("uc_userinfo.id")]) > 0){
                       $ids = $result["group"][session("uc_userinfo.id")];
                    } else {
                        $ids[] = session("uc_userinfo.id");
                    }
                } else {
                    $ids = $result["all"];
                }
                $isshow = $dept = 2;
            }
        }
        $ids = array_unique($ids);
        if (I("get.dep") !== "") {
            $dept = I("get.dep") ;
        }
        //$code,$state,$age,$addr,$sale,$type,$ids
        $list = $this->getContractDetailsStat(I("get.code"),I("get.state"),I("get.age"),I("get.addr"),I("get.sale"),$dept,$ids);
        $this->assign("dept",$isshow);
        $this->assign("list",$list);
        $this->display();
    }

    public function contractStateStat()
    {
        $list = $this->getContractStateStat(I("get.type"),I("get.begin"),I("get.end"));
        $this->assign("list",$list);
        $this->display();
    }

    /*
    * 合同票据总览
    */
    public function contractoverview(){
        //商务外销只能查看本部门的
        if (!in_array(session("uc_userinfo.uid"),array(1,37,46,55,51))) {
            //商务部门只能看商务的
            if (session("uc_userinfo.department_id") == 6) {
                //商务助理/商务经理 查看全部商务
                //销售只看自己的或者自己管辖的
                $result = $this->getDeptCityList(1);
                if (!in_array(session("uc_userinfo.uid"),array(39,45))) {
                    if (count($result["manager"][session("uc_userinfo.id")]) > 0) {
                       $ids = $result["manager"][session("uc_userinfo.id")];
                    } elseif (count($result["group"][session("uc_userinfo.id")]) > 0){
                       $ids = $result["group"][session("uc_userinfo.id")];
                    }
                    $ids[] = session("uc_userinfo.id");
                } else {
                    $ids = $result["all"];
                }
                $dept = 1;
                $show = 1;
            } else {
                //外销经理、外销助理查看外销部
                $result = $this->getDeptCityList(2);
                if (!in_array(session("uc_userinfo.uid"),array(59,67))) {
                    if (count($result["manager"][session("uc_userinfo.id")]) > 0) {
                       $ids = $result["manager"][session("uc_userinfo.id")];
                    } elseif (count($result["group"][session("uc_userinfo.id")]) > 0){
                       $ids = $result["group"][session("uc_userinfo.id")];
                    } else {
                        $ids[] = session("uc_userinfo.id");
                    }
                } else {
                    $ids = $result["all"];
                }
                $dept = 2;
                $show = 2;
            }
        } else {
            if (I("get.dept") !== "") {
                $dept = $deptartment = I("get.dept");
            }
            $result = $this->getDeptCityList($deptartment);
            $ids = $result["all"];
        }
        $ids = array_unique($ids);
        //获取销售名单
        $users = $this->getSalerList($ids);

        if (I("get.uid") !== "") {
            $ids = array(I("get.uid"));
        }


        //获取合同总数、已使用合同数、剩余合同数
        $contract = $this->getContractAll($dept);
        //获取合同详细列表
        $list = $this->getUserContractDetails($ids);

        $this->assign("list",$list );
        $this->assign("users",$users);
        $this->assign("dept",$show);
        $this->assign("contract",$contract);
        $this->display();
    }

    /**
     * 合同票据库存
     * @param  [type]  $begin [开始时间]
     * @param  [type]  $end   [结束时间]
     * @return [type]         [description]
     */
    private function getContractStockStat($begin,$end,$type)
    {
        //按月
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"))+1;
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end));
        }

        $dep = array(1,2,3,4,5,6);

        if ($type == 1) {
            $dep = array(1,2,5);
        }else if ($type == 2) {
            $dep = array(3,4,6);
        }

        $result = D("Contractmanage")->getContractStockStat($monthStart,$monthEnd,$dep);
        foreach ($result as $key => $value) {
            //按月
            if (!array_key_exists($value["time"],$list["days"])) {
                $list["days"][$value["time"]]["time"] = $value["time"];
            }
            $list["days"][$value["time"]][$value["state"]] = $value["count"];
            $list["all"]["days"][$value["state"]] += $value["count"];
            if (strpos($value["state"],"ht_") !== false) {
               $list["days"][$value["time"]]["ht_all"] += $value["count"];
               $list["all"]["days"]["ht_all"] += $value["count"];
            }

            if (strpos($value["state"],"pj_") !== false) {
               $list["days"][$value["time"]]["pj_all"] += $value["count"];
               $list["all"]["days"]["pj_all"] += $value["count"];
            }
        }

        //按年
        $monthStart = mktime(0,0,0,1,1,date("Y"));
        $monthEnd = mktime(23,59,59,12,date("t"),date("Y"))+1;

        $result = D("Contractmanage")->getContractStockStat($monthStart,$monthEnd,$dep);
        foreach ($result as $key => $value) {
            //按年
            if (!array_key_exists($value["month"],$list["month"])) {
                $list["month"][$value["month"]]["month"] = $value["month"];
            }

            $list["month"][$value["month"]][$value["state"]] += $value["count"];
            $list["all"]["month"][$value["state"]] += $value["count"];
            if (strpos($value["state"],"ht_") !== false) {
               $list["month"][$value["month"]]["ht_all"] += $value["count"];
               $list["all"]["month"]["ht_all"] += $value["count"];
            }

            if (strpos($value["state"],"pj_") !== false) {
               $list["month"][$value["month"]]["pj_all"] += $value["count"];
               $list["all"]["month"]["pj_all"] += $value["count"];
            }
        }

        return $list;
    }

    private function getContractDetailsStat($code,$state,$age,$addr,$sale,$type,$ids)
    {
        //合同
        $dep = array(1,2,3,4);
        if ($type == 1) {
            $dep = array(1,2);
        } else  if($type == 2){
            $dep = array(3,4);
        }

        if (!empty($sale)) {
            $userInfo = D("Adminuser")->getUserInfoByName($sale);
            $sale = $userInfo["id"];
        }

        if (in_array(session("uc_userinfo.uid"),array(39,45,59,67))) {
            $isaudit = 1;
        }

        $ht = D("Contractmanage")->getContractDetailsStat($code,$state,$age,$addr,$sale,$dep,1,$ids,$isaudit);

        foreach ($ht as $key => $value) {
            if (!empty($value["qx_time"])) {
                $offset = ceil((time() - $value["qx_time"])/86400);
                $ht[$key]["offset"] = $offset;
            }
            $ht[$key]["state"] = "/";
            if (in_array($value["status"],array(3,6))) {
                $ht[$key]["state"] = "在途";
            } elseif ( in_array($value["status"],array(4,5,9))){
                $ht[$key]["state"] = "销售处";
            } elseif (in_array($value["status"],array(2,7,8))){
                $ht[$key]["state"] = "公司";
            }

            //在途，使用中，遗失，作废，已签约，归档
            $ht[$key]["type"] = "/";
            if (in_array($value["status"],array(3,6)) && $value["special"] == 1) {
                $ht[$key]["type"] = "在途";
            } else if ( ($value["status"] == 5  || $value["status"] == 7) && $value["special"] == 1) {
                $ht[$key]["type"] = "已签约";
            }else if ($value["status"] == 8 && $value["special"] == 1) {
                $ht[$key]["type"] = "归档";
            } elseif ($value["status"] == 4 && $value["special"] == 1) {
                $ht[$key]["type"] = "使用中";
            } elseif ($value["special"] == 3) {
                $ht[$key]["type"] = "遗失";
            } elseif ($value["special"] == 2) {
                $ht[$key]["type"] = "作废";
            } elseif ($value["status"] == 9 && in_array($value["special"],array(4,5))) {
                $ht[$key]["type"] = "待审核";
            } elseif ($value["status"] == 7 && $value["special"] == 4) {
                $ht[$key]["type"] = "申请签约";
            }
        }

        //票据
        $dep = array(5,6);
        if ($type == 1) {
            $dep = array(5);
        } else  if ($type == 2){
            $dep = array(6);
        }

        $pj = D("Contractmanage")->getContractDetailsStat($code,$state,$age,$addr,$sale,$dep,2,$ids);

        foreach ($pj as $key => $value) {
            if (!empty($value["qx_time"])) {
                $offset = ceil((time() - $value["qx_time"])/86400);
                $pj[$key]["offset"] = $offset;
            }

            if (in_array($value["status"],array(3,6))) {
                $pj[$key]["state"] = "在途";
            } elseif ( in_array($value["status"],array(4,5,9))){
                $pj[$key]["state"] = "销售处";
            } elseif (in_array($value["status"],array(2,7,8))){
                $pj[$key]["state"] = "公司";
            }

            //在途，使用中，遗失，作废，已签约，归档
            $pj[$key]["type"] = "/";
            if (in_array($value["status"],array(3,6)) && $value["special"] == 1) {
                $pj[$key]["type"] = "在途";
            } else if ($value["status"] == 5 && $value["special"] == 1) {
                $pj[$key]["type"] = "已签约";
            }else if ($value["status"] == 8 && $value["special"] == 1) {
                $pj[$key]["type"] = "归档";
            } elseif ($value["status"] == 4 && $value["special"] == 1) {
                $pj[$key]["type"] = "使用中";
            } elseif ($value["special"] == 3) {
                $pj[$key]["type"] = "遗失";
            } elseif ($value["special"] == 2) {
                $pj[$key]["type"] = "作废";
            }  elseif ($value["status"] == 9 && in_array($value["special"],array(4,5))) {
                $pj[$key]["type"] = "待审核";
            }
        }

        return array("ht" => $ht,"pj" => $pj);
    }

    private function getContractStateStat($type,$begin,$end)
    {
        //按月
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"))+1;
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end));
        }

        $dep = array(1,2,3,4,5,6);

        if ($type == 1) {
            $dep = array(1,2,5);
        }else if ($type == 2) {
            $dep = array(3,4,6);
        }

        $result = D("Contractmanage")->getContractStateStat($monthStart,$monthEnd,$dep);

        foreach ($result as $key => $value) {
            //按月
            if (!array_key_exists($value["time"],$list["days"])) {
                $list["days"][$value["time"]]["time"] = $value["time"];
            }
            $list["days"][$value["time"]][$value["state"]] = $value["count"];
            $list["all"]["days"][$value["state"]] += $value["count"];
            if (strpos($value["state"],"ht_") !== false) {
               $list["days"][$value["time"]]["ht_all"] += $value["count"];
               $list["all"]["days"]["ht_all"] += $value["count"];
            }

            if (strpos($value["state"],"pj_") !== false) {
               $list["days"][$value["time"]]["pj_all"] += $value["count"];
               $list["all"]["days"]["pj_all"] += $value["count"];
            }

        }


        //按年
        $time = $monthStart;
        $monthStart = mktime(0,0,0,1,1,date("Y",$time));
        $monthEnd = mktime(23,59,59,12,date("t"),date("Y",$time))+1;

        $result = D("Contractmanage")->getContractStateStat($monthStart,$monthEnd,$dep);

        foreach ($result as $key => $value) {
            if (!array_key_exists($value["date"],$list["month"])) {
                $list["month"][$value["date"]]["date"] = $value["date"];
            }
            $list["month"][$value["date"]][$value["state"]] += $value["count"];
            $list["all"]["month"][$value["state"]] += $value["count"];
            if (strpos($value["state"],"ht_") !== false) {
               $list["month"][$value["date"]]["ht_all"] += $value["count"];
               $list["all"]["month"]["ht_all"] += $value["count"];
            }

            if (strpos($value["state"],"pj_") !== false) {
               $list["month"][$value["date"]]["pj_all"] += $value["count"];
               $list["all"]["month"]["pj_all"] += $value["count"];
            }

        }

        return $list;
    }

    /**
     * 获取我的管辖人员
     * @param  [type] $uid   [用户ID]
     * @param  [type] $depId [部门ID]
     * @return [type]        [description]
     */
    private function getDeptCityList($depId)
    {
        $result = D("Contractmanage")->getDeptCityList($depId);
        foreach ($result as $key => $value) {
            //销售师长
            $manager[$value["dev_division"]][] = $value["dev_regiment"];
            $manager[$value["dev_division"]][] = $value["dev_manage"];

            //销售团长
            $group[$value["dev_regiment"]] =  $value["dev_manage"];

            //品牌师
            $brandManager[$value["brand_division"]][] = $value["brand_regiment"];
            $brandManager[$value["brand_division"]][] = $value["brand_manage"];

            //品牌
            $brandGroup[$value["brand_regiment"]] =  $value["brand_manage"];

            //全部
            $all[] = $value["dev_division"];
            $all[] = $value["dev_regiment"];
            $all[] = $value["dev_manage"];
            $all[] = $value["brand_division"];
            $all[] = $value["brand_regiment"];
            $all[] = $value["brand_manage"];
        }

        return array("manager"=>$manager,"group"=>$group,"brandManager"=>$brandManager,"brandGroup"=>$brandGroup,"all"=>$all);
    }

    /**
     * 获取合同总数
     * @param  string $dept [部门]
     * @return [type]        [description]
     */
    private function getContractAll($dept)
    {
        $type = array(1,2,3,4);
        if ($dept == 1) {
            # 商务
            $type = array(1,2);
        } elseif ($dept == 2) {
            # 外销
            $type = array(3,4);
        }

        $result = D("Contractmanage")->getContractAll($type);
        $result["other_count"] = $result["all"] - $result["audit_count"];
        return $result;
    }

    /**
     * 获取销售信息
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    private function getSalerList($ids)
    {
        $result = D("Adminuser")->getUserInfoByIds($ids);
        return $result;
    }

    private function getUserContractDetails($ids)
    {
        $result = D("Contractmanage")->getUserContractDetails($ids);
        return $result;
    }
}
