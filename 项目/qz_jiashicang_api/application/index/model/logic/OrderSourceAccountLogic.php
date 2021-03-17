<?php
// +----------------------------------------------------------------------
// | OrderSourceAccountLogic 渠道账户相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use think\Exception;
use app\index\model\db\OrderSourceAccount;
use app\index\model\db\OrderSourceAccountExpend;
use app\common\model\logic\LogAdminLogic;
use app\index\enum\CacheKeyCodeEnum;
use Util\Page;

class OrderSourceAccountLogic {

    protected $accountModel;
    protected $expendModel;

    public function __construct(){
        $this->accountModel = new OrderSourceAccount();
        $this->expendModel = new OrderSourceAccountExpend();
    }

    // 渠道账户列表
    public function getAccountAll($userIds = []){
        return $this->accountModel->getAccountAll($userIds);
    }

    // 渠道账户消耗数据列表
    public function getAccountExpendPageList($input, $page = 1, $limit = 20){
        $count = $this->expendModel->getAccountExpendCount($input);
        $pageobj = new Page($page, $limit, $count);

        if ($count > 0){
            $offset = $pageobj->firstrow;
            $list = $this->expendModel->getAccountExpendList($input, $offset, $limit);

            foreach ($list as $key => &$item) {
                $item["date"] = date("Y-m-d", $item["datetime"]);
                $item["updated_date"] = date("Y-m-d H:i:s", $item["updated_at"]);
                $item["expend_amount"] = floatval($item["expend_amount"]);
            }
        }

        return [
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }

    // 保存数据
    public function saveAccountExpendData($data, $operator, $id = 0, $authUsers = []){
        try {
            $row = [
                "expend_amount" => $data["expend_amount"],
                "operator" => $operator,
                "updated_at" => time()
            ];

            if (!empty($id)){
                $info = $this->expendModel->getById($id);
                if (empty($info)) {
                    throw new Exception("该消耗记录不存在", 500);
                } else if (!empty($authUsers) && !in_array($info["account_creator"], $authUsers)) {
                    throw new Exception("您没有权限进行此操作", 500);
                }
            } else {
                $row["creator"] = $operator;
                $row["created_at"] = time();
                $row["account_id"] = $data["account_id"];
                $row["datetime"] = strtotime($data["date"]);
                $info = $this->expendModel->getByUnique($row["account_id"], $row["datetime"]);
                if (!empty($info) && $info["is_delete"] == 2) {
                    throw new Exception("该消耗记录已存在", 500);
                } else if (!empty($info)) {
                    $row["is_delete"] = 2;
                    $id = $info["id"];
                }
            }

            if (!empty($id)) {
                $ret = $this->expendModel->updateInfo($id, $row);
            } else {
                $ret = $this->expendModel->addInfo($row);
                $id = $ret;
            }

            if ($ret == false) {
                throw new Exception("操作失败", 500);
            }

            // 增加操作日志
            LogAdminLogic::addLog("os_a_expend_save", "渠道账户消耗数据保存", $row, $id);

            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            $errcode = 5000001;
            $errmsg = $e->getMessage();
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg,
        ];
    }

    // 删除数据（逻辑删除）
    public function deleteAccountExpendData($id, $operator, $authUsers = []){
        try {
            $info = $this->expendModel->getById($id);
            if (empty($info)) {
                throw new Exception("数据不存在", 500);
            } else if ($info["is_delete"] == 1) {
                throw new Exception("该数据已删除", 500);
            } else if (!empty($authUsers) && !in_array($info["account_creator"], $authUsers)) {
                throw new Exception("您没有权限进行此操作", 500);
            }

            $row = [
                "is_delete" => 1,
                "operator" => $operator,
                "updated_at" => time()
            ];

            $ret = $this->expendModel->updateInfo($id, $row);
            if ($ret == false) {
                throw new Exception("操作失败", 500);
            }

            // 增加操作日志
            LogAdminLogic::addLog("os_a_expend_del", "渠道账户消耗数据删除", $row, $id);

            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            $errcode = 5000001;
            $errmsg = $e->getMessage();
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg,
        ];
    }

    // 导入数据
    public function uploadAccountExpendData($dataList, $operator){
        $number = 0;
        if (count($dataList) > 0) {
            $newRows = [];
            foreach ($dataList as $key => $rows) {
                $account_name = trim($rows["B"]);

                $data = [
                    "datetime" => strtotime(trim($rows["A"])),
                    "expend_amount" => floatval(trim($rows["C"])),
                    "operator" => $operator,
                    "updated_at" => time(),
                    "is_delete" => 2,
                ];

                if ($data["datetime"] == false) {
                    $datetime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($rows["A"]);
                    $data["datetime"] = strtotime(date("Y-m-d", $datetime));
                }

                if ($data["datetime"] && $account_name && $data["expend_amount"]) {
                    $account = $this->getAccountInfo($account_name);
                    if (!empty($account)) {
                        $data["account_id"] = $account["id"];
                        $expend_id = $this->checkExpendInfo($data["account_id"], $data["datetime"]);
                        if (!empty($expend_id)) {
                            $this->expendModel->updateInfo($expend_id, $data);
                            $number++;
                        } else {
                            $data["creator"] = $operator;
                            $data["created_at"] = time();

                            // 组合一个key防止重复插入
                            $gkey = sprintf("%s-%s", $data["account_id"], $data["datetime"]);
                            $newRows[$gkey] = $data;
                        }
                    }
                }

                unset($data, $account_name, $account, $expend_id, $gkey);
            }

            if (count($newRows) > 0){
                $newRows = array_values($newRows);
                $res = $this->expendModel->addAllInfo($newRows);
                $number += $res;
            }
        }

        return $number;
    }

    // 获取账号消耗是否已存在
    public function checkExpendInfo($account_id, $datetime){
        $cachekey = sprintf(CacheKeyCodeEnum::SOURCE_ACCOUNT_EXPEND, $account_id, $datetime);
        $expendId = cache($cachekey);
        if (empty($expendId)) {
            $expendInfo = $this->expendModel->getByUnique($account_id, $datetime);
            if (!empty($expendInfo["id"])) {
                $expendId = $expendInfo["id"];
                cache($cachekey, $expendId, 300);
            }
        }

        return $expendId;
    }

    // 获取账号名称
    public function getAccountInfo($account_name){
        $cachekey = sprintf(CacheKeyCodeEnum::SOURCE_ACCOUNT_INFO, $account_name);
        $account = cache($cachekey);
        if (empty($account)) {
            $account = $this->accountModel->getByAccount($account_name);
            cache($cachekey, $account, 300);
        }

        return $account;
    }

}