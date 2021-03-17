<?php

/**
 * 营业执照审核逻辑层
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-07-16 09:54:22
 */

namespace app\common\model\logic;

use think\Db;
use think\db\Query;
use think\facade\Request;

use app\common\model\db\Adminuser;
use app\common\model\db\SaleBusinessLicence;
use app\common\model\logic\SalesSettingLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\LogAdminLogic;

use app\index\enum\BusinessLicenceCodeEnum;

class BusinessLicenceLogic {
   
    /**
     * 获取营业执照审核列表
     * @return [type] [description]
     */
    public function getBlPageList($actfrom, $options, $page = 1, $page_size = 20){
        switch ($actfrom) {
            case "examine":
                $state = [1];
                break;

            case "confirm":
                $state = [2,3];
                break;

            case "record":
                $state = [4,5];
                break;
            
            default:
                $state = [1];
                break;
        }

        $count = 0;
        $list = [];

        // 管理员能够看到的所有城市
        $cityIds = AdminuserLogic::getCityIds();
        if (isset($options["city_manager"]) && !empty($options["city_manager"])) {
            $user = Adminuser::findInfoById($options["city_manager"], "id,cs");
            $cs = array_filter(array_unique(explode(",", $user["cs"])));

            // 交集取公共城市
            $cityIds = array_values(array_intersect($cityIds, $cs));
        }

        // 城市检索
        if (isset($options["cs"]) && !empty($options["cs"])) {
            if (in_array($options["cs"], $cityIds)) {
                $cityIds = $options["cs"];
            } else {
                $cityIds = "";
            }
        }

        $options["citys"] = $cityIds;
        if (empty($options["citys"])) {
            return ["count" => $count, "list" => $list];
        }

        // 查询列表总数量
        $sblModel = new SaleBusinessLicence();
        $count = $sblModel->getListCount($state, $options);
        if ($count > 0) {
            $page = intval($page) <= 0 ? 1 : intval($page); 
            $page_size = intval($page_size) <= 0 ? 20 : intval($page_size);

            // 查询列表数据
            $list = $sblModel->getList($state, $options, ($page - 1) * $page_size, $page_size, $actfrom);
            $list = $this->setFormatter($list->toArray());
            $list = $this->setCityManager($list);
        }

        return ["count" => $count, "list" => $list];
    }
    
    /**
     * 格式化数据
     * @param [type] $list [description]
     */
    public function setFormatter($list) {

        foreach ($list as $key => $item) {
            $list[$key]["child"] = [
                "img1" => $item["img1"],
                "img2" => $item["img2"],
                "img3" => $item["img3"],
                "img4" => $item["img4"]
            ];

            if (!empty($item["gszj_img"])) {
                $list[$key]["gszj"] = [
                    "img1" => $item["gszj_img"],
                    "img2" => "",
                    "img3" => "",
                    "img4" => ""
                ];
            } else {
                $list[$key]["gszj"]= null;
            }
            
            $list[$key]["type_name"] = BusinessLicenceCodeEnum::getItem("type", $item["type"]);
            $list[$key]["state_name"] = BusinessLicenceCodeEnum::getItem("state", $item["state"]);
            $list[$key]["audit_date"] = $item["audit_time"] ? date("Y-m-d", $item["audit_time"]) : "";

            $list[$key]["user_on"] = $item["user_on"] == 2 ? 2 : 1;

            unset($list[$key]["img1"], $list[$key]["img2"], $list[$key]["img3"], $list[$key]["img4"]);
            unset($list[$key]["gszj_img"]);
        }

        return $list;
    }

    /**
     * 设置城市负责人
     * @param [type] $cityManagers [description]
     */
    public function setCityManager($list){
        $citys = array_filter(array_unique(array_column($list, "cs")));
        $cityManagers = Adminuser::getCityManagersByCitys($citys);

        foreach ($list as $key => $item) {
            // 设置城市负责人
            $city_manager = "";
            foreach ($cityManagers as $user) {
                $citys = explode(",", $user["cs"]);
                if (in_array($item["cs"], $citys)) {
                    $city_manager .= $user["name"] . ",";
                }
            }

            $list[$key]["city_manager"] = trim($city_manager, ",");
            unset($city_manager);
        }

        return $list;
    }


    /**
     * 营业执照上传列表
     * @return [type] [description]
     */
    public function getSaleCityList($user_keyword, $page = 1, $page_size = 20){
        $count = 0;
        $list = [];
        $cityIds = AdminuserLogic::getCityIds();
        if (empty($cityIds)) {
            return ["count" => $count, "list" => $list];
        }

        $sblModel = new SaleBusinessLicence();
        $count = $sblModel->getSaleCityUserCompanyList("count", $cityIds, $user_keyword);
        if ($count > 0) {
            $page = intval($page) <= 0 ? 1 : intval($page); 
            $page_size = intval($page_size) <= 0 ? 20 : intval($page_size);
            $result = $sblModel->getSaleCityUserCompanyList("list", $cityIds, $user_keyword, ($page - 1) * $page_size, $page_size);

            foreach ($result as $key => $value) {
                if ($value["type"] != 4) {
                    if (!array_key_exists($value["id"], $list)) {
                        $list[$value["id"]] = array(
                            "id" => $value["id"],
                            "qc" => $value["qc"],
                            "cs" => $value["cs"],
                            "cname" => $value["cname"],
                            "user_on" => $value["user_on"] == 2 ? 2: 1,
                            "city_manager" => "占位",
                            "child_1" => null,
                            "child_3" => null,
                            "is_save" => 0
                        );
                    }

                    if (!empty($value["type"])) {
                        $field = "child_" . $value["type"];
                        $list[$value["id"]][$field] = array(
                            "img1" => $value["img1"],
                            "img2" => $value["img2"],
                            "img3" => $value["img3"],
                            "img4" => $value["img4"]
                        );
                    }

                    if ($list[$value["id"]]["child_1"] && $list[$value["id"]]["child_3"]) {
                        $list[$value["id"]]["is_save"] = 1;
                    }
                }
            }

            $list = array_values($list);
            $list = $this->setCityManager($list);
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 上传图片
     * @return [type] [description]
     */
    public function saveUpFile($company_id, $img1 = "", $img3 = "", $img4 = ""){
        Db::startTrans();
        try {
            $sblModel = new SaleBusinessLicence();
            if (!empty($img1)) {
                $sblModel->delRecord($company_id, 1);
                $result = $sblModel->addRecord($company_id, 1, $img1, "qiniu", 1);
                if (empty($result)) {
                    throw new \Exception("保存失败");
                }
            }

            if (!empty($img3)) {
                $res = $sblModel->delRecord($company_id, 3);
                $result = $sblModel->addRecord($company_id, 3, $img3, "qiniu", 1);
                if (empty($result)) {
                    throw new \Exception("保存失败");
                }
            }

            if (!empty($img4)) {
                $sblModel->delRecord($company_id, 4);
                $result = $sblModel->addRecord($company_id, 4, $img4, "qiniu", 3);
                if (empty($result)) {
                    throw new \Exception("保存失败");
                }
            }
            
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }

        return true;
    }

    /**
     * 记录审核（初审）
     * @return [type] [description]
     */
    public function examRecordFirst($company_id, $type, $state, $remark = ""){
        $user = Request::instance()->user;
        $sblModel = new SaleBusinessLicence();

        // 如果是营业执照审核需要验证工商总局查询是否上传
        if ($type == 1) {
            $row = $sblModel->findByUnique($company_id, 4);
            if (empty($row)) {
                return sys_response(4000001, "尚未获取到工商总局查询图片,请先上传！");
            }
        }

        $data = [
            "remark" => $remark,
            "audit_time" => time(),
            "uid" => $user["user_id"],
            "uname" => $user["user_name"],
            "state" => $state == 1 ? 3 : 2
        ];

        $result = $sblModel->editRecord($company_id, $type, $data);
        if (empty($result)) {
            return sys_response(5000002, "审核失败");
        }

        $info = ["company_id" => $company_id, "type" => $type];
        LogAdminLogic::addLog("businesslicence", 0, "营业执照审核（初审）", $info);

        return sys_response(0);
    }

    /**
     * 记录审核（终审）
     * @return [type] [description]
     */
    public function examRecordFinal($company_id, $type){
        $sblModel = new SaleBusinessLicence();
        $row = $sblModel->findByUnique($company_id, $type);

        if (empty($row)) {
            return sys_response(4000001, "审核项目不存在");
        } else if ($row["state"] == 4 || $row["state"] == 5) {
            return sys_response(4000001, "该项目已审核");
        } else if ($row["state"] < 2) {
            return sys_response(4000001, "请先完成初审");
        }

        $type_name = BusinessLicenceCodeEnum::getItem("type", $type);
        if ($row["state"] == 3) {
            $data = ["state" => 4];
            $msg = sprintf("您的%s审核已通过！", $type_name);
        } else {
            $data = ["state" => 5];
            $msg = sprintf("您的%s审核未通过，原因是:%s", $type_name, $row["remark"]);
        }

        $result = $sblModel->editRecord($company_id, $type, $data);
        if ($result && $row["type"] == 1 && $row["state"] == 3) {
            // 如果是营业执照审核把工商查询记录也审核
            $sblModel->editRecord($company_id, 4, $data);
        }

        if (empty($result)) {
            return sys_response(5000002, "审核失败");
        }

        $info = ["company_id" => $company_id, "type" => $type];
        LogAdminLogic::addLog("businesslicence", 0, "营业执照审核（复审）", $info);

        return sys_response(0);
    }

    /**
     * 记录重审/撤回
     * @return [type] [description]
     */
    public function examRecordReset($company_id, $type){
        $data = array(
            "state" => 1,
            "audit_time" => 0
        );

        $sblModel = new SaleBusinessLicence();
        $result = $sblModel->editRecord($company_id, $type, $data);

        if (empty($result)) {
            return sys_response(5000002, "操作失败");
        }

        $info = ["company_id" => $company_id, "type" => $type];
        LogAdminLogic::addLog("businesslicence", 0, "营业执照重审/撤回", $info);

        return sys_response(0);
    }

    /**
     * 清空
     * @return [type] [description]
     */
    public function examRecordClean($company_id){
        $sblModel = new SaleBusinessLicence();
        $sblModel->delRecord($company_id, 1);
        $sblModel->delRecord($company_id, 3);
        $sblModel->delRecord($company_id, 4);

        LogAdminLogic::addLog("businesslicence", $company_id, "营业执照清空");
        return sys_response(0);
    }
    
    
    /**
     * 营业执照数据统计
     *
     * @param array $param 查询参数
     * @param int $p 分页
     * @param int $psize 每页数量
     * @return array
     */
    public function licenceStatistics($param, $p, $psize)
    {
        $map = [];

        // 非超级管理员获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if (!empty($citys)) {
            $map['cs'] = $citys;
            if (!empty($param['city'])) {
                if (in_array($param['city'], $citys)) {
                    $map['cs'] = $param['city'];
                } else {
                    return sys_response(4000002, '暂无权限查看数据', []);
                }
            }
        } else {
            return sys_response(4000002, '暂无权限查看数据', []);
        }

        if (!empty($param['ying'])) {
            $map['ying'] = $param['ying'];
        }
        if (!empty($param['zhuang'])) {
            $map['zhuang'] = $param['zhuang'];
        }
        if (!empty($param['company'])) {
            $map['company'] = $param['company'];
        }

        $saleBusinessLicence = new SaleBusinessLicence();
        $count = $saleBusinessLicence->getLicenceStatisticsCount($map);
        $list = [];
        if ($count > 0) {
            $list = $saleBusinessLicence->getLicenceStatisticsList($map, $p, $psize);
        }
        return sys_response(0, '', [
            'list' => $list,
            'page' => sys_paginate($count, $psize, $p),
        ]);
    }
}