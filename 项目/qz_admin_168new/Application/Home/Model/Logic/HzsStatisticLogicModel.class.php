<?php

namespace Home\Model\Logic;

use Home\Model\Db\HzsSourceModel;
use Home\Model\Db\HzsCompanyModel;
use Home\Model\Db\OrderSourceModel;

class HzsStatisticLogicModel {

    private $hzsSourceModel;
    private $hzsCompanyModel;

    CONST NEW_DAYS = 30;
    CONST DEFAULT_SEARCH_DAYS = 30;

    // 合作模式
    private $cooperate_mode = array(
        "1" => "CPA(分单)",
        "2" => "CPT",
        "3" => "CPM",
        "4" => "CPC",
        "5" => "CPD",
        "6" => "CPS",
        "7" => "免费",
        "8" => "CPA(发单)",
    );

    // 结款方式
    private $pay_mode = array(
        "1" => "预付",
        "2" => "周结",
        "3" => "半月结",
        "4" => "月结"
    );

    public function __construct(){
        $this->hzsSourceModel = new HzsSourceModel();
        $this->hzsCompanyModel = new HzsCompanyModel();
    }

    public function getCodeItemName($field, $key, $default = ""){
        if (isset($this->$field)) {
            if (array_key_exists($key, $this->$field)) {
                return $this->$field[$key];
            }
        }

        return $default;
    }

    // 获取合作商信息
    public function getHzsCompany($id){
        return $this->hzsCompanyModel->getById($id);
    }

    // 获取合作商统计
    public function getHzsCompanyStatList($input, $pageIndex = 1, $pageLimit = 20) {
        $count = $this->hzsCompanyModel->getCompanyListCount($input);
        if ($count > 0) {
            $offset = ($pageIndex - 1) * $pageLimit;
            $list = $this->hzsCompanyModel->getCompanyList($input, $offset, $pageLimit);
            $list = $this->setHzsCompanyStatFormatter($list, $input["begin"], $input["end"]);
        }

        return ["list" => $list, "count"=> $count];
    }

    // 获取合作商统计导出数据
    public function getHzsCompanyStatExportList($input){
        $list = $this->hzsCompanyModel->getCompanyList($input);
        $list = $this->setHzsCompanyStatFormatter($list, $input["begin"], $input["end"]);
        return $list;
    }

    // 获取合作商统计数据处理
    private function setHzsCompanyStatFormatter($list, $begin, $end){
        if (count($list) > 0) {
            $companyIds = array_column($list, "id");
            $sourceList = $this->hzsSourceModel->getSourceListByCompanyIds($companyIds);

            $orderSourceList = [];
            $orderSourceLfList = [];
            $companySourceList = [];
            $orderSourceRealList = [];
            if (!empty($sourceList)) {
                foreach ($sourceList as $key => $item) {
                    $companySourceList[$item["companyid"]][] = $item;
                }

                // 获取渠道订单统计
                $orderSourceModel = new OrderSourceModel();
                $sourceIds = array_column($sourceList, "sourceid");
                $orderSourceList = $orderSourceModel->getOrderSourceStat($sourceIds, $begin, $end);
                $orderSourceList = array_column($orderSourceList, null, "sourceid");

                $lf_end = $begin;
                $lf_begin = date("Y-m-d", strtotime($begin) - 30 * 86400);

                // 查询量房量
                $orderSourceLfList = $orderSourceModel->getOrderSourceLfStat($sourceIds, $lf_begin, $lf_end);
                $orderSourceLfList = array_column($orderSourceLfList, null, "sourceid");

                // 查询上月实际分单量
                $orderSourceRealList = $orderSourceModel->getOrderSourceRealStat($sourceIds, $lf_begin, $lf_end);
                $orderSourceRealList = array_column($orderSourceRealList, null, "sourceid");

                // 查询当月实际分单量
                $orderSourceNowRealList = $orderSourceModel->getOrderSourceRealStat($sourceIds, $begin, $end);
                $orderSourceNowRealList = array_column($orderSourceNowRealList, null, "sourceid");
            }

            $newdaytime = time() - static::NEW_DAYS * 86400;
            foreach ($list as $key => $item) {
                $list[$key]["pay_mode_name"] = $this->getCodeItemName("pay_mode", $item["pay_mode"], "-");
                $list[$key]["cooperate_mode_name"] = $this->getCodeItemName("cooperate_mode", $item["cooperate_mode"], "-");
                $list[$key]["is_new"] = 0;
                if ($item["create_time"] >= $newdaytime) {
                    $list[$key]["is_new"] = 1;
                }

                $list[$key]["order_count"] = 0;
                $list[$key]["order_fen_count"] = 0;
                $list[$key]["order_zen_count"] = 0;
                $list[$key]["order_lf_count"] = 0;
                $list[$key]["order_real_fen"] = 0;

                if (array_key_exists($item["id"], $companySourceList)) {
                    $source_list = $companySourceList[$item["id"]];
                    foreach ($source_list as $source) {
                        $sourceid = $source["sourceid"];
                        if (array_key_exists($sourceid, $orderSourceList)) {
                            $list[$key]["order_count"] += $orderSourceList[$sourceid]["order_count"];
                            $list[$key]["order_fen_count"] += $orderSourceList[$sourceid]["order_fen_count"];
                            $list[$key]["order_zen_count"] += $orderSourceList[$sourceid]["order_zen_count"];
                        }

                        if (array_key_exists($sourceid, $orderSourceLfList)) {
                            $list[$key]["order_lf_count"] += $orderSourceLfList[$sourceid]["order_lf_count"];
                        }

                        if (array_key_exists($sourceid, $orderSourceRealList)) {
                            $list[$key]["order_real_fen"] += $orderSourceRealList[$sourceid]["order_real_fen"];
                        }

                        if (array_key_exists($sourceid, $orderSourceNowRealList)) {
                            $list[$key]["order_now_real_fen"] += $orderSourceNowRealList[$sourceid]["order_real_fen"];
                        }
                    }
                }

                $lf_ratio = 0;
                if ($list[$key]["order_real_fen"] > 0) {
                    $lf_ratio = sprintf("%.2f", $list[$key]["order_lf_count"] / $list[$key]["order_real_fen"]);
                }
                $list[$key]["lf_ratio_text"] = ($lf_ratio * 100) . "%";
                $list[$key]["lf_ratio"] = $lf_ratio;
                unset($source_list, $lf_ratio);
            }
        }

        return $list;
    }

    // 获取合作商渠道统计
    public function getHzsSourceStatList($companyid, $input, $pageIndex = 1, $pageLimit = 20) {
        $count = $this->hzsSourceModel->getSourceListCount($companyid, $input);

        if ($count > 0) {
            $list = $this->hzsSourceModel->getSourceList($companyid, $input);
            $list = $this->setHzsSourceStatFormatter($list, $input["begin"], $input["end"]);
        }
      
        return ["list" => $list, "count"=> $count];
    }

    // 获取合作商渠道统计数据处理
    private function setHzsSourceStatFormatter($list, $begin, $end){
        if (count($list) > 0) {
            // 获取渠道订单统计
            $orderSourceModel = new OrderSourceModel();
            $sourceIds = array_column($list, "sourceid");
            $orderSourceList = $orderSourceModel->getOrderSourceStat($sourceIds, $begin, $end);
            $orderSourceList = array_column($orderSourceList, null, "sourceid");

            $lf_end = $begin;
            $lf_begin = date("Y-m-d", strtotime($begin) - 30 * 86400);

            // 查询量房量
            $orderSourceLfList = $orderSourceModel->getOrderSourceLfStat($sourceIds, $lf_begin, $lf_end);
            $orderSourceLfList = array_column($orderSourceLfList, null, "sourceid");

            // 查询上月实际分单量
            $orderSourceRealList = $orderSourceModel->getOrderSourceRealStat($sourceIds, $lf_begin, $lf_end);
            $orderSourceRealList = array_column($orderSourceRealList, null, "sourceid");

            // 查询当月实际分单量
            $orderSourceNowRealList = $orderSourceModel->getOrderSourceRealStat($sourceIds, $begin, $end);
            $orderSourceNowRealList = array_column($orderSourceNowRealList, null, "sourceid");

            //上个月的分单量
            $beforeOrderList = $orderSourceModel->getOrderSourceStat($sourceIds, $lf_begin, $lf_end);
            $beforeOrderList = array_column($beforeOrderList, null, "sourceid");

            foreach ($list as $key => $item) {
                $sourceid = $item["sourceid"];
                if (array_key_exists($sourceid, $orderSourceList)) {
                    $list[$key]["order_count"] = $orderSourceList[$sourceid]["order_count"];
                    $list[$key]["order_fen_count"] = $orderSourceList[$sourceid]["order_fen_count"];
                    $list[$key]["order_zen_count"] = $orderSourceList[$sourceid]["order_zen_count"];
                } else {
                    $list[$key]["order_count"] = 0;
                    $list[$key]["order_fen_count"] = 0;
                    $list[$key]["order_zen_count"] = 0;
                }

                $list[$key]["order_lf_count"] = 0;
                if (array_key_exists($sourceid, $orderSourceLfList)) {
                    $list[$key]["order_lf_count"] = $orderSourceLfList[$sourceid]["order_lf_count"];
                }

                $list[$key]["order_real_fen"] = 0;
                if (array_key_exists($sourceid, $orderSourceRealList)) {
                    $list[$key]["order_real_fen"] = $orderSourceRealList[$sourceid]["order_real_fen"];
                }

                $list[$key]["order_now_real_fen"] = 0;
                if (array_key_exists($sourceid, $orderSourceNowRealList)) {
                    $list[$key]["order_now_real_fen"] = $orderSourceNowRealList[$sourceid]["order_real_fen"];
                }

                $lf_ratio = 0;
                if (array_key_exists($sourceid, $beforeOrderList)) {
                    $lf_ratio = round($list[$key]["order_lf_count"] / $beforeOrderList[$sourceid]["order_fen_count"],2);
                }

                $list[$key]["lf_ratio_text"] = ($lf_ratio * 100) . "%";
                $list[$key]["lf_ratio"] = $lf_ratio;
                unset($sourceid, $lf_ratio);
            }
        }

        //发单、分单、实际分单、量房量为0时，删除该数据
        foreach ($list as $key => $value) {
            if($value["order_count"] == 0 && $value["order_fen_count"] == 0 && $value["order_zen_count"] == 0 && $value["order_lf_count"] == 0 &&  $value["order_real_fen"] == 0){
                unset($list[$key]);
            }
        }

        return $list;
    }

    // 获取合作商渠道统计导出数据
    public function getHzsSourceStatExplodeList($companyid, $input) {
        $list = $this->hzsSourceModel->getSourceList($companyid, $input);
        $list = $this->setHzsSourceStatFormatter($list, $input["begin"], $input["end"]);

        return $list;
    }
}
