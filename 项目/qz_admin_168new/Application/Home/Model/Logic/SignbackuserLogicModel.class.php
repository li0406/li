<?php
// +----------------------------------------------------------------------
// | SignbackuserLogicModel  签单返点会员逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;

use Exception;
use Home\Model\Db\UserModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\CompanyPackageModel;

class SignbackuserLogicModel {

    const CONTRACT_TYPE = 6;

    public function getUserSignbackInfo($user_id){
        $userModel = new UserModel();
        return $userModel->getUserSignbackInfo($user_id);
    }

    public function editCompanyOrderStatus($user_id, $order_status = 1){
        $userModel = new UserModel();
        $info = $userModel->getUserSignbackInfo($user_id);
        if (empty($info)) {
            $data = array(
                "user_id" => $user_id,
                "order_stop_status" => $order_status,
                "created_at" => time(),
                "updated_at" => time()
            );

            $ret = $userModel->addUserSignbackInfo($data);
        } else {
            $data = array(
                "order_stop_status" => $order_status,
                "updated_at" => time()
            );

            $ret = $userModel->editUserSignbackInfo($user_id, $data);
        }

        if ($ret) {
            //添加操作日志
            $log = array(
                'remark' => '修改会员【' . $user_id . '】派单状态',
                'logtype' => 'edituserorderstatus',
                'action_id' => $user_id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);
        }

        return $ret;
    }

    // 签单返点会员列表
    public function getAccountList($input, $limit = 20){
        $userModel = new UserModel();
        $count = $userModel->getSignBackUserCount($input);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $pageClass = new \Page($count, $limit);
            $page = $pageClass->show();

            $list = $userModel->getSignBackUserList($input, $pageClass->firstRow, $pageClass->listRows);
            $list = $this->setFormatter($list, true);

            return ["list" => $list, "page" => $page];
        }

        return ["list" => [], "page" => ""];
    }

    // 签单返点会员列表
    public function getAccountExportList($input){
        $userModel = new UserModel();
        $list = $userModel->getSignBackUserList($input);

        if (count($list) > 0) {
            $list = $this->setFormatter($list, true);
        }

        return ["list" => $list];
    }

    // 格式化处理、关联数据
    private function setFormatter($list, $related_package = false){
        if (count($list) > 0) {
            $packageList = [];
            if ($related_package == true) {
                $userIds = array_column($list, "id");
                if (count($userIds)) {
                    $companyPackageModel = new CompanyPackageModel();
                    $packageList = $companyPackageModel->getNewPackageByCompanyIds($userIds);
                    $packageList = array_column($packageList, null, "company_id");
                }
            }

            foreach ($list as $key => $item) {
                $list[$key]["contract_start_date"] = $item["contract_start"] ? date("Y-m-d", $item["contract_start"]) : "";
                $list[$key]["contract_end_date"] = $item["contract_end"] ? date("Y-m-d", $item["contract_end"]) : "";

                $company_id = $item["id"];
                if (array_key_exists($company_id, $packageList)) {
                    $list[$key]["back_ratio"] = $packageList[$company_id]["back_ratio"];
                    $list[$key]["back_ratio_text"] = $packageList[$company_id]["back_ratio"] . "%";
                    $list[$key]["deposit_money"] = floatval($packageList[$company_id]["deposit_money"]);
                }
            }

        }
        return $list;
    }

    // 查询装修公司信息
    public function getCompanyList($company_id){
        $userModel = D('Home/User');
        $info = $userModel->getCompanyList($company_id, [3,6]);
        foreach ($info as $key => $value) {
            // 去除加会员公司
            if ($value["fake"] == 1) {
                unset($info[$key]);
                continue;
            }

            $info[$key]["mark"] = 0;
            if ($value["end"] >= date("Y-m-d") && $value["fake"] == 0) {
                $info[$key]["mark"] = 1;
            }
        }

        return array_values($info);
    }

    // 保存会员信息
    public function editCompanyExtendInfo($company_id){
        try {
            if (empty($company_id)) {
                throw new Exception("请先选择会员公司！", 404);
            }

            //查询本次会员信息
            $userModel = D("Home/User");
            $info = $userModel->findCompanyInfo($company_id);
            if (empty($info)) {
                throw new Exception("会员不存在！", 404);
            }

            $data = array(
                "jd_tel_1" => I("post.jd_tel1"),
                "jd_tel_name_1" => I("post.jd_name1"),
                "jd_tel_2" => I("post.jd_tel2"),
                "jd_tel_name_2" => I("post.jd_name2")
            );

            // 是否是修改，不是修改需要接收销售数据
            if (I("post.is_edit", "") == "") {
                $data["saler"] = I("post.saler_name", "");
                $data["saler_id"] = I("post.saler_id", "");
            }

            if (!empty($data["jd_tel_1"]) && !validate_mobile2($data["jd_tel_1"])) {
                throw new Exception("请输入正确的手机号码！", 404);
            }

            if (!empty($data["jd_tel_2"]) && !validate_mobile2($data["jd_tel_2"])) {
                throw new Exception("请输入正确的手机号码！", 404);
            }

            $model = D('Home/UserCompany');
            $result = $model->editCompanyExtendInfo($company_id, $data);
            if ($result === false) {
                throw new Exception("保存失败！", 404);
            }

            $jdbb = $userModel->getJdbb($company_id);
            $jdbbData = array(
                "jdbb_time" => time(),
                "receive_order_tel1" => $data["jd_tel_1"],
                "receive_order_tel2" => $data["jd_tel_2"],
                "receive_order_tel1_remark" => $data["jd_tel_name_1"],
                "receive_order_tel2_remark" => $data["jd_tel_name_2"]
            );
            if (count($jdbb) == 0) {
                $data["jdbb_comid"] = $company_id;
                $userModel->setJdbb($jdbbData);
            } else {
                $userModel->upJdbb($company_id, $jdbbData);
            }

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 获取签返会员最新的合同信息
    public function getNewContractInfo($company_id){
        //获取最新的合同信息
        $userVipModel = D('Home/UserVip');
        $result = $userVipModel->getNewContractInfo($company_id, static::CONTRACT_TYPE);

        $list = [];
        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                if (!array_key_exists("all", $list)) {
                    $list["all"]["id"] = $value["id"];
                    $list["all"]["start_time"] = $value["start_time"];
                    $list["all"]["end_time"] = $value["end_time"];
                    $list["all"]["day"] = (strtotime($value["end_time"]) - strtotime($value["start_time"])) / 86400 + 1;
                }

                if (!array_key_exists("now", $list) && !empty($value["bid"])) {
                    $list["now"]["id"] = $value["bid"];
                    $list["now"]["start_time"] = $value["b_start"];
                    $list["now"]["end_time"] = $value["b_end"];
                    $list["now"]["day"] = (strtotime($value["b_end"]) - strtotime($value["b_start"])) / 86400 + 1;
                    $list["now"]["editMark"] = 1;
                    if ($list["now"]["end_time"] < date("Y-m-d")) {
                        $list["now"]["editMark"] = 0;
                    }
                }

                if (!empty($value["cid"])) {
                    $sub = array(
                        "start_time" => $value["c_start"],
                        "end_time" => $value["c_end"] == "0000-00-00" ? "" : $value["c_end"],
                        "type" => $value["c_type"],
                        "viptype" => $value["c_viptype"],
                        "delay_day" => $value["delay_day"],
                        "delay_time" => $value["delay_time"],

                    );
                    if ($value["c_end"] != "0000-00-00") {
                        $sub["day"] = (strtotime($value["c_end"]) - strtotime($value["c_start"])) / 86400 + 1;
                    }

                    if (in_array($value["c_type"], array(7, 9))) {
                        $list["now"]["editMark"] = 0;
                    }

                    $list["child"][] = $sub;
                }
            }
        }

        return $list;
    }

    // 获取签返会员未开始的合同信息
    public function getNoStartContractList($company_id){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getNoStartContractList($company_id, static::CONTRACT_TYPE);
    }

    // 获取未开始总合同
    public function getNoStartContractAllList($company_id){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getNoStartContractAllList($company_id, static::CONTRACT_TYPE);
    }

    // 获取当前总合同下的其它合同
    public function getContractOtherInfo($parentid, $id){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getContractOtherInfo($parentid, $id);
    }

    // 获取会员公司当前的本次合同信息
    public function getContractNowList($company_id){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getContractNowList($company_id, static::CONTRACT_TYPE);
    }

    // 获取会员公司当前的本次合同信息
    public function getNoStartContractNowList($company_id){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getNoStartContractNowList($company_id, static::CONTRACT_TYPE);
    }

    // 获取已结束本次合同信息
    public function getEndContractNowList($company_id){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getEndContractNowList($company_id, static::CONTRACT_TYPE);
    }

    // 获取本次合同的操作记录
    public function getNowContractOtherList($parentid){
        $userVipModel = D('Home/UserVip');
        return $userVipModel->getNowContractOtherList($parentid);
    }

    // 获取广告报备情况
    public function getadvreport($company_id, $start_time){
        $userVipModel = D('Home/AdvertisingReport');
        $result = $userVipModel->getNowAdvReport($company_id, $start_time);

        $list = [];
        foreach ($result as $key => $value) {
            $value["total"] = $value["total"] ? : "";
            $list[$value["type"]][$value["location"]]["total"] = $value["total"];

            $list[$value["type"]][$value["location"]]["use_day"] = $value["use_day"];
            if (in_array($value["location"], array(3, 101))) {
                $list["flag"] = 1;
            }
        }

        return $list;
    }

    // 新增总合同
    public function addCompanyContract($company_id, $input){
        try {
            $userModel = D('Home/User');
            $userVipModel = D('Home/UserVip');
            $userCompanyModel = D('Home/UserCompany');
            $advReportModel = D('Home/AdvertisingReport');

            // 验证总合同时间是否有交叉
            // $allNum = $userVipModel->validateAllContractDate($company_id, $input["allbegin"], $input["allend"]);
            // if ($allNum > 0) {
            //     throw new Exception("总合同时间不能和已有总合同时间重叠", 404);
            // }

            // 验证本次合同时间是否有交叉
            $nowNum = $userVipModel->validateNowContractDate($company_id, $input["begin"], $input["end"]);
            if ($nowNum > 0) {
                throw new Exception("本次合同时间不能和已有本次合同时间重叠", 404);
            }

            // 验证新增合同是当前合同还是预约合同
            // 如果是预约合同需要验证是否已经存在预约合同
            if (date("Y-m-d") < $input["allbegin"]) {
                // 查询是否有未开始总合同
                $nostartAll = $userVipModel->validateNoStartAllContract($company_id);
                if ($nostartAll > 0) {
                    throw new Exception("已存在预约合同，不能重复添加", 404);
                }
            }

            //总合同添加
            $data = array(
                "company_id" => $company_id,
                "company_name" => $input["company_name"],
                "start_time" => $input["allbegin"],
                "end_time" => $input["allend"],
                "saler_id" => $input["saler_id"],
                "saler_name" => $input["saler_name"],
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "contract_type" => static::CONTRACT_TYPE,
                "time" => time(),
                "type" => 1,
            );

            $vipid = $userVipModel->addVip($data);
            if ($vipid == false) {
                throw new Exception("编辑失败,请稍后再试！", 404);
            }

            //添加本次合同时间
            $subdata = array(
                "company_id" => $company_id,
                "company_name" => $input["company_name"],
                "start_time" => $input["begin"],
                "end_time" => $input["end"],
                "saler_id" => $input["saler_id"],
                "saler_name" => $input["saler_name"],
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "parentid" => $vipid,
                "contract_type" => static::CONTRACT_TYPE,
                "time" => time(),
                "type" => 2,
            );
            $result = $userVipModel->addVip($subdata);
            if ($result == false) {
                throw new Exception("编辑失败,请稍后再试！", 404);
            }

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . $company_id . '】总合同和本次合同',
                'logtype' => 'addcompanycontract',
                'action_id' => $company_id,
                'info' => [
                    "data" => $data,
                    "subdata" => $subdata
                ]
            );
            D('LogAdmin')->addLog($log);

            $info = $userModel->findCompanyInfo($company_id);

            // 会员派单状态
            $this->editCompanyOrderStatus($company_id, 1);

            //如果是新的本次合同，则修改用户的VIP信息
            if (date("Y-m-d") >= $input["begin"]) {
                $data = array(
                    "contract_start" => strtotime($input["allbegin"]),
                    "contract_end" => strtotime($input["allend"]),
                );
                $userCompanyModel->editCompanyExtendInfo($company_id, $data);

                $data = array(
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "classid" => 6,
                    "on" => 2
                );
                $userModel->editCompanyInfo($company_id, $data);

                //添加日志
                $data = array(
                    "comid" => $company_id,
                    "old_state" => $info["on"],
                    "new_state" => 2,
                    "optime" => time(),
                    "opid" => session("uc_userinfo.id"),
                    "operator" => session("uc_userinfo.name"),
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "contract_start" => strtotime($input["allbegin"]),
                    "contract_end" => strtotime($input["allend"]),
                );
                D('Home/LogVip')->addLog($data);
            }

            //添加轮显广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 1,
                "total" => $input["lunbo"],
                "start" => $input["begin"],
                "end" => $input["end"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReportModel->addReport($data);

            //添加通栏A广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 2,
                "location" => 1,
                "total" => $input["tl_A"],
                "start" => $input["begin"],
                "end" => $input["end"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReportModel->addReport($data);

            //添加通栏B广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 2,
                "location" => 2,
                "total" => $input["tl_B"],
                "start" => $input["begin"],
                "end" => $input["end"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReportModel->addReport($data);

            //老站添加通栏D
            if (I("post.type") == 1) {
                //添加通栏C广告报备
                $data = array(
                    "comid" => $company_id,
                    "type" => 2,
                    "location" => 3,
                    "total" => $input["tl_C"],
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReportModel->addReport($data);

                //添加通栏D广告报备
                $data = array(
                    "comid" => $company_id,
                    "type" => 2,
                    "location" => 101,
                    "total" => $input["tl_D"],
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReportModel->addReport($data);
            }

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 添加本次合同
    public function addNewContract($company_id, $input){
        try {
            $userModel = D('Home/User');
            $userVipModel = D('Home/UserVip');
            $userCompanyModel = D('Home/UserCompany');
            $advReportModel = D('Home/AdvertisingReport');

            // 获取总合同信息
            $allInfo = $userVipModel->getContractInfo($input["parentid"]);
            if (empty($allInfo)) {
                throw new Exception("总合同不存在", 404);
            } else if ($input["begin"] < $allInfo["start_time"]) {
                throw new Exception("本次合同开始时间不能小于总合同开始时间", 404);
            } else if ($input["end"] > $allInfo["end_time"]) {
                throw new Exception("本次合同结束时间不能大于总合同的结束时间", 404);
            }

            // 验证本次合同时间是否有交叉
            $nowNum = $userVipModel->validateNowContractDate($company_id, $input["begin"], $input["end"]);
            if ($nowNum > 0) {
                throw new Exception("本次合同时间不能和已有本次合同时间重叠", 404);
            }

            // 验证新增合同是当前合同还是预约合同
            // 如果是预约合同需要验证是否已经存在预约合同
            if (date("Y-m-d") < $input["begin"]) {
                // 查询是否有未开始总合同
                $nostartNow = $userVipModel->validateNoStartNowContract($company_id, $input["parentid"]);
                if ($nostartNow > 0) {
                    throw new Exception("已存在预约合同，不能重复添加", 404);
                }
            }

            //添加本次合同时间
            $subdata = array(
                "company_id" => $company_id,
                "company_name" => $input["company_name"],
                "start_time" => $input["begin"],
                "end_time" => $input["end"],
                "saler_id" => $input["saler_id"],
                "saler_name" => $input["saler_name"],
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "parentid" => $input["parentid"],
                "contract_type" => static::CONTRACT_TYPE,
                "time" => time(),
                "type" => 2,
            );
            $result = $userVipModel->addVip($subdata);
            if ($result == false) {
                throw new Exception("编辑失败,请稍后再试！", 404);
            }

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . $company_id . '】本次合同',
                'logtype' => 'addcompanycontract',
                'action_id' => $company_id,
                'info' => $subdata
            );
            D('LogAdmin')->addLog($log);

            $info = $userModel->findCompanyInfo($company_id);

            // 如果是新的本次合同，则修改用户的VIP信息
            if (date("Y-m-d") >= $input["begin"]) {
                $data = array(
                    "contract_start" => strtotime($allInfo["start_time"]),
                    "contract_end" => strtotime($allInfo["end_time"]),
                );
                $userCompanyModel->editCompanyExtendInfo($company_id, $data);

                $data = array(
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "classid" => 6,
                    "on" => 2
                );
                $userModel->editCompanyInfo($company_id, $data);

                //添加日志
                $data = array(
                    "comid" => $company_id,
                    "old_state" => $info["on"],
                    "new_state" => 2,
                    "optime" => time(),
                    "opid" => session("uc_userinfo.id"),
                    "operator" => session("uc_userinfo.name"),
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "contract_start" => strtotime($allInfo["start_time"]),
                    "contract_end" => strtotime($allInfo["end_time"]),
                );
                D('Home/LogVip')->addLog($data);
            }

            //添加轮显广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 1,
                "total" => $input["lunbo"],
                "start" => $input["begin"],
                "end" => $input["end"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReportModel->addReport($data);

            //添加通栏A广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 2,
                "location" => 1,
                "total" => $input["tl_A"],
                "start" => $input["begin"],
                "end" => $input["end"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReportModel->addReport($data);

            //添加通栏B广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 2,
                "location" => 2,
                "total" => $input["tl_B"],
                "start" => $input["begin"],
                "end" => $input["end"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReportModel->addReport($data);

            //老站添加通栏D
            if (I("post.type") == 1) {
                //添加通栏C广告报备
                $data = array(
                    "comid" => $company_id,
                    "type" => 2,
                    "location" => 3,
                    "total" => $input["tl_C"],
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReportModel->addReport($data);

                //添加通栏D广告报备
                $data = array(
                    "comid" => $company_id,
                    "type" => 2,
                    "location" => 101,
                    "total" => $input["tl_D"],
                    "start" => $input["begin"],
                    "end" => $input["end"],
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReportModel->addReport($data);
            }

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 编辑广告报备信息
    public function editAdvReport($company_id, $input){
        $advReportModel = D('Home/AdvertisingReport');
        $result = $advReportModel->getNowAdvReport($company_id, $input["start_time"]);

        $advlist = [];
        if (count($result) > 0) {
            foreach ($result as $key => $item) {
                $type = $item["type"];
                $location = $item["location"];
                if (!array_key_exists($type, $advlist)) {
                    $advlist[$type] = [];
                }

                if (!array_key_exists($location, $advlist[$type])) {
                    $advlist[$type][$location] = $item;
                }
            }
        }

        // 编辑轮显广告报备
        $data = array(
            "comid" => $company_id,
            "type" => 1,
            "total" => $input["lunbo"],
            "start" => $input["start_time"],
            "end" => $input["end_time"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "time" => time()
        );
        if (isset($advlist[1][0])) {
            $advReportModel->editReport($advlist[1][0]["id"], $data);
        } else {
            $advReportModel->addReport($data);
        }

        //添加通栏A广告报备
        $data = array(
            "comid" => $company_id,
            "type" => 2,
            "location" => 1,
            "total" => $input["tl_A"],
            "start" => $input["start_time"],
            "end" => $input["end_time"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "time" => time()
        );
        if (isset($advlist[2][1])) {
            $advReportModel->editReport($advlist[2][1]["id"], $data);
        } else {
            $advReportModel->addReport($data);
        }

        //添加通栏B广告报备
        $data = array(
            "comid" => $company_id,
            "type" => 2,
            "location" => 2,
            "total" => $input["tl_B"],
            "start" => $input["start_time"],
            "end" => $input["end_time"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "time" => time()
        );
        if (isset($advlist[2][2])) {
            $advReportModel->editReport($advlist[2][2]["id"], $data);
        } else {
            $advReportModel->addReport($data);
        }
        
        //老站添加通栏D
        if (I("post.type") == 1) {
            //添加通栏C广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 2,
                "location" => 3,
                "total" => $input["tl_C"],
                "start" => $input["start_time"],
                "end" => $input["end_time"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            if (isset($advlist[2][3])) {
                $advReportModel->editReport($advlist[2][3]["id"], $data);
            } else {
                $advReportModel->addReport($data);
            }

            //添加通栏D广告报备
            $data = array(
                "comid" => $company_id,
                "type" => 2,
                "location" => 101,
                "total" => $input["tl_D"],
                "start" => $input["start_time"],
                "end" => $input["end_time"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            if (isset($advlist[2][101])) {
                $advReportModel->editReport($advlist[2][101]["id"], $data);
            } else {
                $advReportModel->addReport($data);
            }
        }

        return ["code" => 200, "errmsg" => ""];
    }

}