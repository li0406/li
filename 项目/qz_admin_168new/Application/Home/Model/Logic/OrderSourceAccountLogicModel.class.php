<?php

namespace Home\Model\Logic;

use Think\Exception;
use Home\Model\Db\OrderSourceAccountModel;

class OrderSourceAccountLogicModel {

    protected $sourceAccountModel;

    public function __construct(){
        $this->sourceAccountModel = new OrderSourceAccountModel();
    }

    // 获取所有渠道账户
    public function getSourceAccountAll(){
        $list = $this->sourceAccountModel->getAccountAll();

        return $list;
    }

    // 获取渠道账户分页数据
    public function getSourceAccountPageList($input, $limit = 20){
        $count = $this->sourceAccountModel->getAccountCount($input);

        import('Library.Org.Util.Page');
        $pageClass = new \Page($count, $limit);
        $pageshow = $pageClass->show();

        if ($count > 0) {
            $list = $this->sourceAccountModel->getAccountList($input, $pageClass->firstRow, $pageClass->listRows);
            if (count($list) > 0) {
                $accountIds = array_column($list, "id");
                $related = $this->sourceAccountModel->getRelatedSrcNumbers($accountIds);
                $related = array_column($related, null, "account_id");

                foreach ($list as $key => $item) {
                    $id = $item["id"];
                    $list[$key]["index"] = $pageClass->firstRow + $key + 1;
                    $list[$key]["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
                    $list[$key]["updated_date"] = date("Y-m-d H:i:s", $item["updated_at"]);
                    $list[$key]["enabled_name"] = $item["enabled"] == 1 ? "开启" : "关闭";

                    // 渠道标识数量
                    $list[$key]["src_number"] = $related[$id]["src_number"] ?? 0;
                }
            }
        }

        return [
            "list" => $list,
            "count" => $count,
            "pageshow" => $pageshow,
        ];
    }

    // 保存渠道账户信息
    public function setSourceAccount($account_name, $enabled, $operator, $id = 0){
        try {
            // 验证账号名唯一
            $checked = $this->sourceAccountModel->getInfoByAccount($account_name);
            if (!empty($checked) && $checked["id"] != $id) {
                throw new Exception("渠道账户名称已存在", 500);
            }

            $data = [
                "account_name" => $account_name,
                "operator" => $operator,
                "enabled" => $enabled,
                "updated_at" => time()
            ];

            if (empty($id)) {
                $data["creator"] = $operator;
                $data["created_at"] = time();

                $ret = $this->sourceAccountModel->addInfo($data);
            } else {
                $ret = $this->sourceAccountModel->updateInfo($id, $data);
            }

            if ($ret == false) {
                throw new Exception("操作失败", 500);
            }

            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = 500;
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg
        ];
    }

}

