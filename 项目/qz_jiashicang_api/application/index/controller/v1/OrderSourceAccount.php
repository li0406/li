<?php

// +----------------------------------------------------------------------
// | 渠道账户数据控制器
// +----------------------------------------------------------------------

namespace app\index\controller\v1;

use think\Request;
use think\Exception;
use think\Controller;

use app\index\model\logic\AdminAuthLogic;
use app\index\model\logic\OrderSourceAccountLogic;
use app\index\validate\OrderSourceAccountValidate;
use app\common\enum\BaseStatusCodeEnum;

class OrderSourceAccount extends Controller {

    // 获取所有渠道账户
    public function getAccountAll(Request $request, OrderSourceAccountLogic $sourceAccountLogic){
        $authUsers = AdminAuthLogic::getMarketAuthUsers();
        $list = $sourceAccountLogic->getAccountAll($authUsers);

        $data = [
            "list" => $list,
            "count" => count($list)
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 渠道账户消耗数据列表
    public function getAccountExpendList(Request $request, OrderSourceAccountLogic $sourceAccountLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        OrderSourceAccountValidate::checkAccountData($input);

        $input["auth_users"] = AdminAuthLogic::getMarketAuthUsers();
        $data = $sourceAccountLogic->getAccountExpendPageList($input, $page, $limit);

        $data["options"] = [
            "date_begin" => $input["date_begin"] ?? "",
            "date_end" => $input["date_end"] ?? "",
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 编辑渠道账户消耗
    public function setAccountExpend(Request $request, OrderSourceAccountLogic $sourceAccountLogic){
        $id = $request->post("id");
        $input = [
            "expend_amount" => $request->post("expend_amount"),
            "account_id" => $request->post("account_id"),
            "date" => $request->post("date"),
        ];

        $scene = $id ? "edit" : "add";
        $accountValidate = new OrderSourceAccountValidate();
        if ($accountValidate->scene($scene)->check($input) == false) {
            return json(sys_response(4000002, $accountValidate->getError()));
        }

        $operator = $request->user["user_id"];
        $authUsers = AdminAuthLogic::getMarketAuthUsers();
        $ret = $sourceAccountLogic->saveAccountExpendData($input, $operator, $id, $authUsers);

        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    // 删除渠道账户消耗
    public function deleteAccountExpend(Request $request, OrderSourceAccountLogic $sourceAccountLogic){
        $id = $request->post("id");

        if (empty($id)) {
            return json(sys_response(4000002, BaseStatusCodeEnum::CODE_4000002));
        }

        $operator = $request->user["user_id"];
        $authUsers = AdminAuthLogic::getMarketAuthUsers();
        $ret = $sourceAccountLogic->deleteAccountExpendData($id, $operator, $authUsers);

        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    // 渠道消耗数据上传
    public function uploadAccountExpend(Request $request, OrderSourceAccountLogic $sourceAccountLogic){
        ini_set("max_execution_time", 0);
        ini_set("memory_limit", "500M");

        try {
            $file = $request->file("file");
            if (empty($file)) {
                throw new Exception("请先上传EXCEL文件", 4000002);
            }

            $filename = $file->getInfo("tmp_name");
            $data = sys_read_excel($filename, 0, 3, 2);
            if (empty($data)) {
                throw new Exception("请先录入导入数据", 4000002);
            }

            $operator = $request->user["user_id"];
            $number = $sourceAccountLogic->uploadAccountExpendData($data, $operator);

            $msg = sprintf("成功导入%s条数据", $number);
            $response = sys_response(0, $msg);
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $errcode = $e->getCode() ? : "1000001";
            $response = sys_response($errcode, $errmsg);
        }

        return json($response);
    }

}