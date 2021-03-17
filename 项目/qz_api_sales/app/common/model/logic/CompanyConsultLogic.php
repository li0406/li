<?php

namespace app\common\model\logic;

use think\Db;
use think\Exception;
use think\facade\Request;
use Util\Page;

use app\common\model\db\RoleDepartment;
use app\common\model\db\CompanyConsult;
use app\common\model\db\CompanyConsultRecord;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\CompanyConsultCodeEnum;

class CompanyConsultLogic {

    protected $consultModel;
    protected $consultRecordModel;

    public function __construct(){
        $this->consultModel = new CompanyConsult();
        $this->consultRecordModel = new CompanyConsultRecord();
    }

    // 会员咨询分页列表
    public function getConsultPageList($input, $page = 1, $limit = 10){
        $count = $this->consultModel->getConsultPageCount($input);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();

        $list = [];
        if ($count > 0) {
            $list = $this->consultModel->getConsultPageList($input, $pageobj->firstrow, $pageobj->pageSize);
            foreach ($list as $key => $item) {
                $list[$key] = $this->setFormatter($item);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 获取装企咨询详情
    public function getConsultDetail($id){
        $detail = $this->consultModel->getDetail($id);
        if (!empty($detail)) {
            $detail = $this->setFormatter($detail);
        }

        return $detail;
    }

    // 装企咨询处理
    public function setConsultDeal($id, $user){
        try {
            $info = $this->consultModel->getInfo($id);
            if (empty($info)) {
                throw new Exception(BaseStatusCodeEnum::CODE_4000001, 4000001);
            } else if ($info["operate_status"] != 1) {
                throw new Exception("该咨询信息已处理", 1000001);
            }

            $update = [
                "operate_status" => 2,
                "operator" => $user["user_id"],
                "operate_time" => time()
            ];
            $this->consultModel->updateInfo($id, $update);

            $errcode = 0;
            $errmsg = "请求成功";
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : 1000001;
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg
        ];
    }


    // 装企咨询添加记录
    public function addConsultRecord($consult_id, $data){

        try {
            $this->consultModel->startTrans();
            $info = $this->consultModel->getInfo($consult_id);
            if (empty($info)) {
                throw new Exception(BaseStatusCodeEnum::CODE_4000001, 4000001);
            }

            // 查询所在部门
            $roleDepartmentModel = new RoleDepartment();
            $deptInfo = $roleDepartmentModel->getDepartmentByRoleId($data["role_id"]);

            $record = [
                "consult_id" => $consult_id,
                "deal_man" => $data["deal_man"],
                "address" => $data["address"] ?? "",
                "deal_type" => $data["deal_type"],
                "success_level" => $data["success_level"],
                "communication" => $data["communication"],
                "status" => $data["status"],
                "user_id" => $data["user_id"],
                "dept" => $deptInfo["name"] ?? "",
                "time" => time(),
            ];

            $ret = $this->consultRecordModel->insert($record);

            if ($info["record_status"] != $record["status"]) {
                $update = [
                    "record_status" => $record["status"]
                ];
                $this->consultModel->updateInfo($consult_id, $update);
            }

            $errcode = 0;
            $errmsg = "请求成功";
            $this->consultModel->commit();
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : 1000001;
            $this->consultModel->rollback();
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg
        ];
    }

    // 装企咨询历史记录
    public function getRecordPageList($consult_id, $page = 1, $limit = 10){
        $count = $this->consultRecordModel->getRecordPageCount($consult_id);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();

        $list = [];
        if ($count > 0) {
            $list = $this->consultRecordModel->getRecordPageList($consult_id, $pageobj->firstrow, $pageobj->pageSize);
            foreach ($list as $key => &$item) {
                $item["add_date"] = date("Y-m-d",$item["time"]);
                $item["success_level_name"] = CompanyConsultCodeEnum::getItem("success_level", $item["success_level"]);
                $item["deal_type_name"] = CompanyConsultCodeEnum::getItem("deal_type", $item["deal_type"]);
                $item["status_name"] = CompanyConsultCodeEnum::getItem("record_status", $item["status"]);
                $item["communication"] = strval($item["communication"]);
                $item["address"] = strval($item["address"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 处理数据
    private function setFormatter($item){
        if (!empty($item)) {
            $item["add_date"] = date("Y-m-d", $item["add_time"]);
            $item["operate_date"] = $item["operate_time"] ? date("Y-m-d", $item["operate_time"]) : "";
            $item["operate_status_name"] = CompanyConsultCodeEnum::getItem("operate_status", $item["operate_status"]);
            $item["cooperation_type_name"] = CompanyConsultCodeEnum::getItem("cooperation_type", $item["cooperation_type"]);
            $item["record_status_name"] = CompanyConsultCodeEnum::getItem("record_status", $item["record_status"]);

            $carea = [$item["city_name"], $item["area_name"]];
            $item["city_area"] = implode("-", array_filter($carea));

            // 转化null值
            foreach ($item as $key => $value) {
                if (is_null($value)) {
                    $item[$key] = strval($value);
                }
            }
        }

        return $item;   
    }

}