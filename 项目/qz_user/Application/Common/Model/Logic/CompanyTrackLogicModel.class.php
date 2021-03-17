<?php

namespace Common\Model\Logic;

use Exception;
use Common\Model\Db\CompanyTrackModel;

class CompanyTrackLogicModel {

    public $trackStep = [
        "1" => "初次跟进",
        "2" => "量房",
        "3" => "方案",
        "4" => "签单"
    ];

    public function getTrackStepName($step){
        $step_name = "";
        if (array_key_exists($step, $this->trackStep)) {
            $step_name = $this->trackStep[$step];
        }

        return $step_name;
    }


    /**
     * 根据订单和装修公司联合ID查询记录
     * @param $company_id
     * @param $order_id
     * @return mixed
     */
    public function getAllByUnique($company_id, $order_id){
        $companyTrackModel = new CompanyTrackModel();
        $list = $companyTrackModel->getAllByUnique($company_id, $order_id);

        foreach ($list as $key => $item) {
            $list[$key]["track_date"] = date("Y-m-d H:i:s", $item["track_time"]);
            $list[$key]["track_step_name"] = $this->getTrackStepName($item["track_step"]);
        }

        return $list;
    }

    /**
     * 获取最新一条跟单小计信息
     * @param  [type] $company_id [description]
     * @param  [type] $order_id   [description]
     * @return [type]             [description]
     */
    public function getLastTrack($company_id, $order_id){
        $companyTrackModel = new CompanyTrackModel();
        $info = $companyTrackModel->getLastTrack($company_id, $order_id);
        return $info;
    }

    /**
     * 保存跟单小计信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveTrack($id, $data){
        try {
            $companyTrackModel = new CompanyTrackModel();
            if (empty($id)) {
                $data["created_at"] = time();
                $data["updated_at"] = time();
                $result = $companyTrackModel->add($data);
            } else {
                $info = $companyTrackModel->getById($id);
                if (empty($info)) {
                    throw new Exception("数据不存在", 4000001);
                } else if ($info["company_id"] != $data["company_id"]) {
                    throw new Exception("您无权进行此操作", 3000001);
                } else if ($info["order_id"] != $data["order_id"]) {
                    throw new Exception("订单不对应", 4000001);
                }

                $lastTrack = $companyTrackModel->getLastTrack($data["company_id"], $data["order_id"]);
                if (empty($lastTrack) || $lastTrack["id"] != $id) {
                    throw new Exception("该跟进小计不是最新记录不能修改", 5000003);
                }

                $data["id"] = $id;
                $data["updated_at"] = time();
                $result = $companyTrackModel->save($data);
            }

            if (empty($result)) {
                throw new Exception("保存失败", 5000002);
            }

            $error_code = 0;
            $error_msg = "保存成功";
        } catch (Exception $e) {
            $error_code = $e->getCode();
            $error_msg = $e->getMessage();
        }

        return ["error_code" => $error_code, "error_msg" => $error_msg];
    }

    // 删除跟单小计
    public function deleteTrack($id, $user){
        try {
            $companyTrackModel = new CompanyTrackModel();
            $info = $companyTrackModel->getById($id);
            if (empty($info)) {
                throw new Exception("数据不存在", 4000001);
            } else if ($info["company_id"] != $user["id"]) {
                throw new Exception("您无权进行此操作", 3000001);
            }

            $lastTrack = $companyTrackModel->getLastTrack($info["company_id"], $info["order_id"]);
            if (empty($lastTrack) || $lastTrack["id"] != $id) {
                throw new Exception("该跟进小计不是最新记录不能删除", 5000003);
            }

            $result = $companyTrackModel->deleteInfo($id);
            if (empty($result)) {
                throw new Exception("删除失败", 5000002);
            }

            $error_code = 0;
            $error_msg = "删除成功";
        } catch (Exception $e) {
            $error_code = $e->getCode();
            $error_msg = $e->getMessage();
        }

        return ["error_code" => $error_code, "error_msg" => $error_msg];
    }

    // 获取装修公司订单跟单小计（根据订单分组）
    public function getCompanyOrderTrack($company_id, $orderIds){
        $companyTrackModel = new CompanyTrackModel();
        $list = $companyTrackModel->getAllByUnique($company_id, $orderIds);

        $orderTrackList = [];
        foreach ($list as $key => $item) {
            $item["track_date"] = date("Y-m-d H:i:s", $item["track_time"]);
            $item["track_step_name"] = $this->getTrackStepName($item["track_step"]);
            $orderTrackList[$item["order_id"]][] = $item;
        }

        return $orderTrackList;
    }

}