<?php
// +----------------------------------------------------------------------
// | SignbackuserController  签单返点会员
// +----------------------------------------------------------------------

namespace Home\Controller;

use Exception;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\SignbackuserLogicModel;
use Home\Model\Logic\CompanyPackageLogicModel;
use Home\Model\Logic\CompanyExpendLogicModel;

class SignbackuserController extends HomeBaseController {

    // 签单返点会员列表
    public function accountlist(){
        $input = array(
            "uid" => I("get.uid"),
            "city" => I("get.city"),
            "jc" => I("get.jc"),
            "saler" => I("get.saler"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "on_status" => I("get.on_status"),
        );

        $signbackuserLogic = new SignbackuserLogicModel();
        $data = $signbackuserLogic->getAccountList($input);

        $citys = getUserCitys();

        $this->assign("data", $data);
        $this->assign("citys", $citys);
        $this->assign("input", $input);
        $this->display();
    }

    // 签单返点会员添加/编辑页面
    public function accountup(){
        $id = I("param.id");

        if (!empty($id)) {
            $userModel = D('Home/User');
            $info = $userModel->findCompanyInfo($id, $this->city);

            $signbackuserLogic = new SignbackuserLogicModel();
            $signbackinfo = $signbackuserLogic->getUserSignbackInfo($id);

            $this->assign("info", $info);
            $this->assign("signbackinfo", $signbackinfo);
            $this->display("accountedit");
        } else {
            $this->display();
        }
    }

    // 会员编辑
    private function accountEdit($id){
        $userModel = D('Home/User');
        $info = $userModel->findCompanyInfo($id, $this->city);

        $signbackuserLogic = new SignbackuserLogicModel();
        $signbackinfo = $signbackuserLogic->getUserSignbackInfo($id);

        $this->assign("userinfo", $info);
        $this->assign("signbackinfo", $signbackinfo);
    }

    // 暂停
    public function pause(){
        if ($_POST) {
            //暂停会员合同操作
            $result = $this->accountPause();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    // 转让
    public function returnvip(){
        if ($_POST) {
            $result = $this->vipReturn();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    // 查询装修公司信息
    public function findcomnapnyinfo(){
        if ($_POST) {
            $id = I("post.q");
            $signbackuserLogic = new SignbackuserLogicModel();
            $result = $signbackuserLogic->getCompanyList($id);
            $this->ajaxReturn(array("items" => $result));
        }
    }

    // 保存会员公司信息
    public function extendinfo(){
        if ($_POST) {
            // 编辑会员信息
            $company_id = I("post.company_id");
            $signbackuserLogic = new SignbackuserLogicModel();
            $result = $signbackuserLogic->editCompanyExtendInfo($company_id);
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    // 修改用户派单状态
    public function setorderstatus(){
        if ($_POST) {
            // 编辑会员信息
            $company_id = I("post.company_id");
            $order_status = I("post.order_status");
            if (empty($company_id) || !in_array($order_status, [1, 2])) {
                $this->ajaxReturn(array("code" => 404, "errmsg" => "参数不完善"));
            }

            $signbackuserLogic = new SignbackuserLogicModel();
            $result = $signbackuserLogic->editCompanyOrderStatus($company_id, $order_status);
            if ($result) {
                $this->ajaxReturn(array("code" => 200, "errmsg" => "编辑成功"));
            } else {
                $this->ajaxReturn(array("code" => 404, "errmsg" => "编辑失败"));
            }
        }
    }

    // 查找合同
    public function findcontract(){
        $action = I("param.action");
        $company_id = I("param.company_id");

        $userModel = D("Home/user");
        $companyInfo = $userModel->findCompanyInfo($company_id);
        $this->assign("companyInfo", $companyInfo);

        switch ($action) {
            case 1:
                // 获取新加总合同模板
                $temp = $this->fetch("contractNewAllTemp");
                break;
            case 2:
                // 获取新加本次合同模板
                $temp = $this->fetch("contractNewTemp");
                break;
            default:
                $signbackuserLogic = new SignbackuserLogicModel();
                $info = $signbackuserLogic->getNewContractInfo($company_id);

                // $newInfo = [];
                if (count($info) > 0) {
                    // 如果返回的当前合同已结束删除当前合同
                    if (!empty($info["now"])) {
                        $info["now"]["is_can_edit"] = 1;
                        if ($companyInfo["on"] == -1 || $info["now"]["end_time"] < date("Y-m-d")) {
                            $info["now"]["is_can_edit"] = 2;
                        }
                    }
                }

                //     // 查询当前总合同下的预约合同
                //     $newInfo = $signbackuserLogic->getContractOtherInfo($info["all"]["id"], $info["now"]["id"]);
                //     // 如果当前同合同的当前合同和预约合同都是空则删除当前合同
                //     if (empty($newInfo) && empty($info["now"])) {
                //         $info = [];
                //     }
                // }

                // 未开始总合同
                $noAllInfo = $signbackuserLogic->getNoStartContractAllList($company_id);
                if (count($info) > 0) {
                    $newInfo = $signbackuserLogic->getContractOtherInfo($info["all"]["id"], $info["now"]["id"]);

                    $advreport = $signbackuserLogic->getadvreport($company_id, $info["now"]["start_time"]);
                    $newAdvreport = $signbackuserLogic->getadvreport($company_id, $newInfo["start_time"]);

                    $this->assign("info", $info);
                    $this->assign("newInfo", $newInfo);
                    $this->assign("noInfo", $noAllInfo);
                    $this->assign("advreport", $advreport);
                    $this->assign("newAdvreport", $newAdvreport);
                    $temp = $this->fetch("contractTemp");
                } else if (count($noAllInfo) > 0) {
                    $noInfo = $signbackuserLogic->getContractOtherInfo($noAllInfo["id"]);

                    $this->assign("noInfo", $noInfo);
                    $this->assign("noAllInfo", $noAllInfo);
                    $temp = $this->fetch("contractNoTemp");
                } else {
                    $temp = $this->fetch("contractEmptyTemp");
                }
                break;
        }

        $this->ajaxReturn(["temp" => $temp]);
    }

    // 新增总合同
    public function addCompanyContract(){
        $company_id = I("param.company_id");

        try {
            $input = [
                "allbegin" => I("post.allbegin"),
                "allend" => I("post.allend"),
                "begin" => I("post.begin"),
                "end" => I("post.end"),
                "company_name" => I("post.company_name", ""),
                "saler_id" => I("post.saler_id", 0),
                "saler_name" => I("post.saler_name", ""),
                "lunbo" => I("post.lunbo", ""),
                "tl_A" => I("post.tl_A", ""),
                "tl_B" => I("post.tl_B", ""),
                "tl_C" => I("post.tl_C", ""),
                "tl_D" => I("post.tl_D", ""),
                "type" => I("post.type", 0),
            ];

            if (empty($input["allbegin"]) && empty($input["allend"])) {
                throw new Exception("请选择总合同时间", 404);
            }

            if (!empty($input["allbegin"]) && empty($input["allend"])) {
                throw new Exception("请选择总合同结束时间", 404);
            }

            if (empty($input["allbegin"]) && !empty($input["allend"])) {
                throw new Exception("请选择总合同开始时间", 404);
            }

            if ($input["allbegin"] > $input["allend"]) {
                throw new Exception("总合同结束时间不得小于开始时间", 404);
            }

            if (empty($input["begin"]) && empty($input["end"])) {
                throw new Exception("请选择本次合同时间", 404);
            }

            if (!empty($input["begin"]) && empty($input["end"])) {
                throw new Exception("请选择本次合同结束时间", 404);
            }

            if (empty($input["begin"]) && !empty($input["end"])) {
                throw new Exception("请选择本次合同开始时间", 404);
            }

            if ($input["begin"] > $input["end"]) {
                throw new Exception("本次合同结束时间不得小于开始时间", 404);
            }

            if ($input["begin"] < $input["allbegin"]) {
                throw new Exception("本次合同开始时间不能小于总合同开始时间", 404);
            }

            if ($input["allend"] < $input["end"]) {
                throw new Exception("本次合同结束时间不能大于总合同的结束时间", 404);
            }

            if ($input["allend"] < date("Y-m-d")) {
                throw new Exception("总合同结束时间不能小于当前时间", 404);
            }

            if ($input["end"] < date("Y-m-d")) {
                throw new Exception("本次合同结束时间不能小于当前时间", 404);
            }

        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
            $this->ajaxReturn(array("code" => $code, "errmsg" => $errmsg));
        }

        $signbackuserLogic = new SignbackuserLogicModel();
        $data = $signbackuserLogic->addCompanyContract($company_id, $input);
        $this->ajaxReturn($data);
    }


    // 添加本次合同
    public function addnewcontract(){
        $company_id = I("param.company_id");

        try {
            $input = [
                "parentid" => I("parentid"),
                "begin" => I("post.begin"),
                "end" => I("post.end"),
                "company_name" => I("post.company_name", ""),
                "saler_id" => I("post.saler_id", 0),
                "saler_name" => I("post.saler_name", ""),
                "lunbo" => I("post.lunbo", ""),
                "tl_A" => I("post.tl_A", ""),
                "tl_B" => I("post.tl_B", ""),
                "tl_C" => I("post.tl_C", ""),
                "tl_D" => I("post.tl_D", ""),
                "type" => I("post.type", 0),
            ];

            if (empty($input["parentid"])) {
                throw new Exception("总合同参数非法!", 404);
            }

            if (empty($input["begin"]) && empty($input["end"])) {
                throw new Exception("请选择本次合同时间", 404);
            }

            if (!empty($input["begin"]) && empty($input["end"])) {
                throw new Exception("请选择本次合同结束时间", 404);
            }

            if (empty($input["begin"]) && !empty($input["end"])) {
                throw new Exception("请选择本次合同开始时间", 404);
            }

            if ($input["begin"] > $input["end"]) {
                throw new Exception("本次合同结束时间不得小于开始时间", 404);
            }

            if ($input["end"] < date("Y-m-d")) {
                throw new Exception("本次合同结束时间不能小于当前时间", 404);
            }

        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
            $this->ajaxReturn(array("code" => $code, "errmsg" => $errmsg));
        }

        $signbackuserLogic = new SignbackuserLogicModel();
        $data = $signbackuserLogic->addNewContract($company_id, $input);
        $this->ajaxReturn($data);
    }

    // 会员时间
    public function usercontract(){
        $company_id = I("param.company_id");

        // 会员编辑部分
        $this->accountEdit($company_id);

        // 当前正在使用的合同
        $contract_name = "总合同";
        $signbackuserLogic = new SignbackuserLogicModel();
        $info = $signbackuserLogic->getContractNowList($company_id);

        $otherList = [];
        $noAllInfo = [];
        if (!empty($info)) {
            $otherList = $signbackuserLogic->getNowContractOtherList($info["id"]);

            $noAllInfo = $signbackuserLogic->getNoStartContractAllList($company_id);
        }

        // 未开始的合同
        if (empty($info)) {
            $contract_name = "新合同";
            $info = $signbackuserLogic->getNoStartContractNowList($company_id);
        }

        // 历史合同
        if (empty($info)) {
            $contract_name = "历史合同";
            $info = $signbackuserLogic->getEndContractNowList($company_id);
        }

        // 获取广告报备信息
        if (!empty($info)) {
            $advreport = $signbackuserLogic->getadvreport($company_id, $info["start_time"]);
        }

        $this->assign("info", $info);
        $this->assign("noAllInfo", $noAllInfo);
        $this->assign("otherList", $otherList);
        $this->assign("advreport", $advreport);
        $this->assign("company_id", $company_id);
        $this->assign("contract_name", $contract_name);
        $this->display();
    }

    // 查找订单包
    public function findpackage(){
        $action = I("param.action");
        $company_id = I("param.company_id");

        switch ($action) {
            case 1:
                // 获取新加订单包模板
                $temp = $this->fetch("packageNewTemp");
                break;
            default:
                $companyPackageLogic = new CompanyPackageLogicModel();
                $list = $companyPackageLogic->getCompanyPackageList($company_id);

                if (count($list) > 0) {
                    $nowInfo = $list[0];
                    unset($list[0]);

                    $this->assign("list", $list);
                    $this->assign("nowInfo", $nowInfo);
                    $temp = $this->fetch("packageTemp");
                } else {
                    $temp = $this->fetch("packageEmptyTemp");
                }
                break;
        }

        $this->ajaxReturn(["temp" => $temp]);
    }

    // 添加会员公司订单包
    public function addCompanyPackage(){
        $company_id = I("post.company_id");

        $input = array(
            "fen_number" => I("post.fen_number"),
            "zen_number" => I("post.zen_number"),
            "deposit_money" => I("post.deposit_money"),
            "back_ratio" => I("post.back_ratio")
        );

        try {
            if ($input["fen_number"] == 0 && $input["zen_number"] == 0) {
                throw new Exception("分单数和赠单数不能同时为空", 404);
            }

            if ($input["fen_number"] != abs(intval($input["fen_number"]))) {
                throw new Exception("分单数只能输入正整数", 404);
            }

            if ($input["zen_number"] != abs(intval($input["zen_number"]))) {
                throw new Exception("赠单数只能输入正整数", 404);
            }

            if ($input["deposit_money"] == 0) {
                throw new Exception("请先输入保证金", 404);
            }

            if ($input["deposit_money"] != abs(intval($input["deposit_money"]))) {
                throw new Exception("保证金只能输入正整数", 404);
            }

            if ($input["back_ratio"] == 0) {
                throw new Exception("请先输入返点比例", 404);
            }

            if ($input["back_ratio"] != abs(intval($input["back_ratio"]))) {
                throw new Exception("返点比例只能输入正整数", 404);
            }

            if ($input["back_ratio"] < 1 || $input["back_ratio"] > 99) {
                throw new Exception("返点比例只可输入1-99间的整数", 404);
            }
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
            $this->ajaxReturn(array("code" => $code, "errmsg" => $errmsg));
        }

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->addCompanyPackage($company_id, $input);
        $this->ajaxReturn($data);
    }

    // 补单
    public function setPackageBudan(){
        $package_id = I("post.package_id");
        $fen_number = intval(I("post.fen_number"));
        $zen_number = intval(I("post.zen_number"));

        if ($fen_number == 0 && $zen_number == 0) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "分单数和赠单数不能同时为空"));
        }

        if ($fen_number != abs(intval($fen_number))) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "分单数只能输入正整数"));
        }

        if ($zen_number != abs(intval($zen_number))) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "赠单数只能输入正整数"));
        }

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->setPackageBudan($package_id, $fen_number, $zen_number);
        $this->ajaxReturn($data);
    }

    // 退费
    public function setPackageRefund(){
        $package_id = I("post.package_id");
        $refund_date = trim(I("post.refund_date"));
        $refund_date_confirm = trim(I("post.refund_date_confirm"));

        if (empty($refund_date)) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "请先选择退费日期"));
        } else if ($refund_date != $refund_date_confirm) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "两次日期选择不一致"));
        } else if ($refund_date != date("Y-m-d")) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "退费只能选择当天日期退费"));
        }

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->setPackageRefund($package_id, $refund_date);
        $this->ajaxReturn($data);
    }

    // 转让
    public function setPackageTrans(){
        $package_id = I("post.package_id");
        $trans_company_id = I("post.trans_company_id");

        if (empty($trans_company_id)) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "请先选择转让公司"));
        }

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->setPackageTrans($package_id, $trans_company_id);
        $this->ajaxReturn($data);
    }

    // 签单记录
    public function signlist(){
        $company_id = I("param.company_id");
        $back_status = I("param.back_status", 2);

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->getCompanySignList($back_status, $company_id);
     
        // 会员编辑部分
        $this->accountEdit($company_id);

        $data["total"]["qiandan_jine"] = intval($data["total"]["qiandan_jine"] * 10000);
        $data["total"]["back_pay_money"] = intval($data["total"]["back_pay_money"]);

        $this->assign("data", $data);
        $this->assign("company_id", $company_id);
        $this->assign("back_status", $back_status);
        $this->display();
    }

    // 收款
    public function signreceivemonmey(){
        $id = I("param.id");
        $pay_money = floatval(I("param.pay_money"));

        if ($pay_money <= 0) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "请先填写收款金额"));
        }

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->setSignPayMoney($id, $pay_money);
        $this->ajaxReturn($data);
    }

    // 审核
    public function signordercheck(){
        $id = I("param.id");
        $exam_status = I("param.exam_status");


        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->setSignOrderExam($id, $exam_status);
        $this->ajaxReturn($data);
    }

    // 日志
    public function signorderlog(){
        $id = I("param.id");
        $companyPackageLogic = new CompanyPackageLogicModel();
        $list = $companyPackageLogic->getSignOrderLog($id);

        $this->assign("list", $list);
        $temp = $this->fetch("signorderlog");
        $this->ajaxReturn(array("code" => 200, "errmsg" => "", "temp" => $temp));
    }

    // 订单数量
    public function packageinfo(){
        $company_id = I("param.company_id");

        // 会员编辑部分
        $this->accountEdit($company_id);

        $companyPackageLogic = new CompanyPackageLogicModel();
        $packageList = $companyPackageLogic->getCompanyPackageList($company_id);
        $packageHisList = $companyPackageLogic->getCompanyPackageHistoryList($company_id);

        $packageList = array_merge($packageList, $packageHisList);

        $this->assign("list", $packageList);
        $this->assign("company_id", $company_id);
        $this->display();
    }


    // 保证金
    public function packagelist(){
        $company_id = I("param.company_id");

        // 会员编辑部分
        $this->accountEdit($company_id);

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->getCompanyPackagePageList($company_id);

        $this->assign("data", $data);
        $this->assign("company_id", $company_id);
        $this->display();
    }

    // 保证金审核
    public function packageexam(){
        $id = I("param.id");
        $back_status = I("param.back_status");
        $back_money = floatval(I("param.back_money"));

        if ($back_status == 2 && empty($back_money)) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "请先输入返还金额"));
        }

        $companyPackageLogic = new CompanyPackageLogicModel();
        $data = $companyPackageLogic->setPackageExam($id, $back_status, $back_money);
        $this->ajaxReturn($data);
    }

    // 保证金日志
    public function packagelog(){
        $id = I("param.id");

        $companyPackageLogic = new CompanyPackageLogicModel();
        $list = $companyPackageLogic->getPackageLog($id);

        $this->assign("list", $list);
        $temp = $this->fetch("packagelog");
        $this->ajaxReturn(array("code" => 200, "errmsg" => "", "temp" => $temp));
    }

    // 修改广告报备
    public function editAdvReport(){
        $company_id = I("param.company_id");

        $signbackuserLogic = new SignbackuserLogicModel();
        $data = $signbackuserLogic->editAdvReport($company_id, I("post."));
        $this->ajaxReturn($data);
    }

    // 导出
    public function accountExport(){
        $input = array(
            "uid" => I("get.uid"),
            "city" => I("get.city"),
            "jc" => I("get.jc"),
            "saler" => I("get.saler"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "on_status" => I("get.on_status"),
        );

        $signbackuserLogic = new SignbackuserLogicModel();
        $data = $signbackuserLogic->getAccountExportList($input);

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();

            //标题
            $herder = array(
                '会员ID',
                '会员简称',
                '所属城市',
                '会员状态',
                '所属销售',
                '总合同开始时间',
                '总合同结束时间',
                '本次合同开始时间',
                '本次合同结束时间',
                '保证金',
                '返点比例'
            );
            $wArr = array_values($herder);
            $writer->writeSheetRow('Sheet1', $herder);

            foreach ($data["list"] as $key => $value) {
                switch ($value["on"]) {
                    case '2':
                        $on_name = "VIP";
                        break;
                    case '-4':
                        $on_name = "暂停";
                        break;
                    case '-1':
                        $on_name = "到期";
                        break;
                    
                    default:
                        $on_name = "-";
                        break;
                }

                $v = array(
                    $value["id"],
                    $value["jc"],
                    $value["city_name"],
                    $on_name,
                    $value["saler"],
                    $value["contract_start_date"],
                    $value["contract_end_date"],
                    $value["start"],
                    $value["end"],
                    $value["deposit_money"],
                    $value["back_ratio_text"]
                );
                $wArr = array_values($v);
                $writer->writeSheetRow('Sheet1', $v);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="签返会员.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    // 暂停
    private function accountPause(){
        $vip = D('Home/UserVip');
        //获取本次合同的详细信息
        $info = $vip->getContractInfo(I("post.parentid"));

        if (I("post.start") == "") {
            return array("code" => "404", "errmsg" => "请先选择暂停开始时间");
        }

        if (I("post.end") == "") {
            return array("code" => "404", "errmsg" => "请先选择暂停结束时间");
        }

        if (I("post.end") < I("post.start")) {
            return array("code" => "404", "errmsg" => "暂停结束时间不得小于暂停开始时间");
        }

        if ($info["start_time"] > I("post.start")) {
            return array("code" => "404", "errmsg" => "暂停开始时间要大于本次合同开始时间");
        }

        if ($info["end_time"] < I("post.start")) {
           return array("code" => "404", "errmsg" => "暂停开始时间要小于本次合同结束时间");
        }

        if ($info["end_time"] < I("post.end")) {
            return array("code" => "404", "errmsg" => "暂停结束时间要小于本次合同结束时间");
        }

        if (I("post.start") != date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "暂停开始时间必须是当天");
        }

        // 如果输入延期天数
        $delay_day = I("post.delay_day", 0);
        $delay_time = I("post.delay_time", "");
        if ($delay_day) {
            if (!is_numeric($delay_day) || $delay_day < 0) {
                return array("code" => "404", "errmsg" => "请输入正确的延期天数");
            }

            if ($delay_time == "") {
                return array("code" => "404", "errmsg" => "请输入延期到期时间");
            }

            if ($delay_day != getTwoDateDays($info["end_time"], $delay_time) - 1) {
                return array("code" => "404", "errmsg" => "延期天数于延期到期时间不一致");
            }

            if ($delay_time < $info["end_time"]) {
                return array("code" => "404", "errmsg" => "输入延期到期时间必须大于本次结束合同时间");
            }
        }

        // 验证暂停时间段是否已暂停
        $ret = $vip->validateContractPause(I("post.parentid"), I("post.start"), I("post.end"));
        if ($ret > 0) {
            return array("code" => "404", "errmsg" => "当前时间段已有暂停记录");
        }

        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 4,
            "viptype" => $info["viptype"],
            "contract_type" => $info["contract_type"],
            "start_time" => I("post.start"),
            "end_time" => I("post.end") == "" ? "" : I("post.end"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "parentid" => I("post.parentid"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "delay_day" => $delay_day,
            "delay_time" => $delay_time,
            "time" => time(),
        );

        $vip = D('Home/UserVip');
        $vipid = $vip->addVip($data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】暂停操作',
                'logtype' => 'addvippause',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //获取总合同信息
            $allInfo = $vip->getContractInfo($info["parentid"]);

            //同步更新合同表的总合同和本次合同
            if ($delay_day > 0) {
                //如果暂停延长时间大于总合同的结束时间，则延补总合同结束时间
                if ($allInfo["end_time"] < $delay_time) {
                    // $offset = (strtotime($delay_time) - strtotime($allInfo['end_time'])) / 86400;
                    $allEnd = date("Y-m-d", strtotime("+" . $delay_day . " day", strtotime($allInfo['end_time'])));

                    $data = array(
                        "end_time" => $allEnd
                    );
                    $vip->editVip($info["parentid"], $data);

                    //更新扩展中的总合同时间
                    $model = D('Home/UserCompany');
                    $data = array(
                        "contract_end" => strtotime($allEnd)
                    );
                    $model->editCompanyExtendInfo(I("post.company_id"), $data);
                }

                //更新本次合同的结束时间
                $data = array(
                    "end_time" => $delay_time
                );
                $vip->editVip($info["id"], $data);
            }

            //判断暂停开始时间是否是当天，如果是当天则修改VIP会员状态
            if (I("post.start") == date("Y-m-d")) {
                //更新用户数据
                $user = D('Home/User');
                $data = array(
                    "on" => "-4"
                );

                if (I("post.end") != "") {
                    $data["end"] = $end;
                }
                $user->editCompanyInfo($info["company_id"], $data);

                // 暂停时间为当天需要更新会员日消耗会员VIP状态
                CompanyExpendLogicModel::editCompanyVipStatus($info["company_id"]);
            }
        }

        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 转让操作
     * @return [type] [description]
     */
    private function vipReturn()
    {
        //获取编辑前合同的合同信息
        $vip = D('Home/UserVip');
        //查询本次合同时间
        $info = $vip->getContractInfo(I("post.parentid"));

        if (I("post.delay_time") == "") {
            return array("code" => "404", "errmsg" => "请选择转让开始日期");
        }

        if (I("post.delay_time") != date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "转让时间必须是当天");
        }

        if ($info["end_time"] < date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "本次合同时间已结束，无法转让");
        }

        // 转让时间不能大于本次合同结束时间
        if (I("post.delay_time") > $info["end_time"]) {
            return array("code" => "404", "errmsg" => "转让时间不能大于本次合同结束时间");
        }

        if (I("post.to_company_id") == "") {
            return array("code" => "404", "errmsg" => "选择转让公司");
        }

        if (I("post.to_company_id") == $info["company_id"]) {
            return array("code" => "404", "errmsg" => "转让操作不能自己转让自己");
        }

        if (I("post.delay_time") != date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "转让时间必须是当天");
        }

        //计算转让天数
        $offset = (strtotime($info["end_time"]) - strtotime(I("post.delay_time"))) / 86400;
        $offset = $offset + 1;

        //计算到期时间
        $end = date("Y-m-d", strtotime("+" . $offset . " day", strtotime(I("post.delay_time"))));

        //查询被转让装修公司信息
        $user = D('Home/user');
        $vip = D('Home/UserVip');
        //被转让装修公司信息
        $to_company_id = I("post.to_company_id");
        $companyInfo = $user->findCompanyInfo($to_company_id);

        // 如果转让会员公司是会员状态并且会员类型和被转让的合同类型不一致
        if ($companyInfo["on"] == 2 && $companyInfo["classid"] != $info["contract_type"]) {
            return array("code" => "404", "errmsg" => "只能转让给返返会员公司");
        }

        // 查找转让公司要延长的合同
        $signbackuserLogic = new SignbackuserLogicModel();
        $to_contract = $signbackuserLogic->getContractNowList($to_company_id);
        if (empty($to_contract)) {
            $to_contract = $signbackuserLogic->getNoStartContractNowList($to_company_id);
        }

        // 计算验证延长后的合同时间
        if (!empty($to_contract)) {
            $new_end_time = date("Y-m-d", strtotime("+" . $offset . " day", strtotime($to_contract["end_time"])));

            $newInfo = array(
                "scene" => "edit",
                "now" => array(
                    "id" => $to_contract["id"],
                    "start_time" => $to_contract["start_time"],
                    "end_time" => $new_end_time,
                )
            );

            if ($new_end_time > $to_contract["all_end_time"]) {
                $newInfo["all"] = array(
                    "id" => $to_contract["all_id"],
                    "start_time" => $to_contract["all_start_time"],
                    "end_time" => $new_end_time,
                );
            }
        } else {
            $newInfo = array(
                "scene" => "add",
                "all" => array(
                    "id" => 0,
                    "start_time" => date("Y-m-d"),
                    "end_time" => $info["end_time"],
                ),
                "now" => array(
                    "id" => 0,
                    "start_time" => date("Y-m-d"),
                    "end_time" => $info["end_time"],
                )
            );
        }

        // 验证延长后的本次合同时间是否与已有本次合同时间冲突
        $result = $vip->validateNowContractDate($to_company_id, $newInfo["now"]["start_time"], $newInfo["now"]["end_time"], $newInfo["now"]["id"]);
        if ($result > 0) {
            return array("code" => "404", "errmsg" => "转让公司延长后的本次合同时间与已有本次合同时间冲突");
        }

        if (isset($newInfo["all"])) {
            // 验证延长后的总合同时间是否与已有总合同时间冲突
            $result = $vip->validateAllContractDate($to_company_id, $newInfo["all"]["start_time"], $newInfo["all"]["end_time"], $newInfo["all"]["id"]);
            if ($result > 0) {
                return array("code" => "404", "errmsg" => "转让公司延长后的总合同时间与已有总合同时间冲突");
            }
        }

        //添加转让记录
        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 9,
            "viptype" => $info["viptype"],
            "contract_type" => $info["contract_type"],
            "delay_day" => $offset,
            "delay_time" => I("post.delay_time"),
            "end_time" => I("post.end") == "" ? "" : I("post.end"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "parentid" => $info["id"],
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "to_company" => I("post.to_company_id"),
            "to_name" => I("post.to_compnay_name"),
            "time" => time()
        );

        $vipid = $vip->addVip($data);

        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //解除微信绑定
            D("Home/Logic/OrderWechatLogic")->unBindWechatAccount($data["company_id"]);

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】转让操作',
                'logtype' => 'addvipdelay',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //如果是当天转让，则修改VIP用户的状态
            if (I("post.delay_time") == date("Y-m-d")) {

                //修改总合同的结束时间
                // $data = array(
                //     "end_time" => I("post.delay_time")
                // );
                // $vip->editVip($info["parentid"], $data);

                //修改本次合同时间
                $data = array(
                    "end_time" => I("post.delay_time")
                );
                $vip->editVip($info["id"], $data);

                //更新用户数据
                $user = D('Home/User');
                $data = array(
                    "on" => "-1",
                    "end" => I("post.delay_time")
                );
                $user->editCompanyInfo($info["company_id"], $data);

                //更新扩展中的总合同时间
                // $model = D('Home/UserCompany');
                // $data = array(
                //     "contract_end" => strtotime(I("post.delay_time"))
                // );
                // $model->editCompanyExtendInfo($info["company_id"], $data);

                //查询总合同时间
                $allInfo = $vip->getContractInfo($info["parentid"]);

                //添加日志
                $log = D('Home/LogVip');
                $data = array(
                    "comid" => $info["company_id"],
                    "old_state" => 2,
                    "new_state" => 9,
                    "opid" => session("uc_userinfo.id"),
                    "optime" => time(),
                    "operator" => session("uc_userinfo.name"),
                    "start" => $info["start_time"],
                    "end" => I("post.delay_time"),
                    "contract_start" => strtotime($allInfo["start_time"]),
                    "contract_end" => strtotime($allInfo["end_time"]),
                    "viptype" => $info["viptype"]
                );

                // 转让需要删除会员日消耗当天落档记录
                CompanyExpendLogicModel::deleteCompanyExpendInfo($info["company_id"]);


                // 处理总合同时间
                if ($newInfo["scene"] == "edit") {
                    // 修改总合同
                    if (!empty($newInfo["all"])) {
                        $data = array(
                            "end_time" => $newInfo["all"]["end_time"],
                            "op_uid" => session("uc_userinfo.id"),
                            "op_uname" => session("uc_userinfo.name"),
                            "time" => time()
                        );
                        $vip->editVip($newInfo["all"]["id"], $data);
                    }

                    //修改本次合同信息
                    $data = array(
                        "end_time" => $newInfo["now"]["end_time"],
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    $vipId = $vip->editVip($newInfo["now"]["id"], $data);

                    // 如果会员公司当前是会员状态
                    if ($companyInfo["on"] == 2) {
                        //修改会员的信息
                        $data = array(
                            "end" => $newInfo["now"]["end_time"]
                        );
                        $user->editCompanyInfo($companyInfo["id"], $data);

                        //更新扩展中的总合同时间
                        $model = D('Home/UserCompany');
                        $data = array(
                            "contract_end" => strtotime($newInfo["all"]["end_time"])
                        );
                        $model->editCompanyExtendInfo($companyInfo["id"], $data);

                        //添加日志
                        $log = D('Home/LogVip');
                        $data = array(
                            "comid" => $companyInfo["id"],
                            "old_state" => $companyInfo["on"],
                            "new_state" => $companyInfo["on"],
                            "opid" => session("uc_userinfo.id"),
                            "optime" => time(),
                            "operator" => session("uc_userinfo.name"),
                            "start" => $companyInfo["start"],
                            "end" => $newInfo["now"]["end_time"],
                            "contract_start" => strtotime($newInfo["all"]["start_time"]),
                            "contract_end" => strtotime($newInfo["all"]["end_time"]),
                            "viptype" => $companyInfo["viptype"]
                        );

                        $log->addLog($data);
                    }
                } else {
                    //被转让的公司是非会员公司
                    //添加被转让公司的总合同
                    $data = array(
                        "company_id" => $companyInfo["id"],
                        "company_name" => $companyInfo["jc"],
                        "type" => 1,
                        "viptype" => $info["viptype"],
                        "contract_type" => $info["contract_type"],
                        "start_time" => $newInfo["all"]["start_time"],
                        "end_time" => $newInfo["all"]["end_time"],
                        "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
                        "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );

                    $vipId = $vip->addVip($data);

                    $data = array(
                        "company_id" => $companyInfo["id"],
                        "company_name" => $companyInfo["jc"],
                        "type" => 2,
                        "viptype" => $info["viptype"],
                        "contract_type" => $info["contract_type"],
                        "start_time" => $newInfo["now"]["start_time"],
                        "end_time" => $newInfo["now"]["end_time"],
                        "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
                        "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
                        "parentid" => $vipId,
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    $vip->addVip($data);

                    //调整被转让VIP的会员状态
                    $data = array(
                        "on" => "2",
                        "classid" => "6",
                        "start" => $newInfo["now"]["start_time"],
                        "end" => $newInfo["now"]["end_time"]
                    );
                    $user->editCompanyInfo($companyInfo["id"], $data);

                    //更新扩展中的总合同时间
                    $model = D('Home/UserCompany');
                    $data = array(
                        "contract_start" => strtotime($newInfo["all"]["start_time"]),
                        "contract_end" => strtotime($newInfo["all"]["end_time"]),
                        "viptype" => $info["viptype"],
                        "fake" => 0
                    );

                    $model->editCompanyExtendInfo($companyInfo["id"], $data);

                    //添加日志
                    $log = D('Home/LogVip');
                    $data = array(
                        "comid" => $companyInfo["id"],
                        "old_state" => $companyInfo["on"],
                        "new_state" => 2,
                        "opid" => session("uc_userinfo.id"),
                        "optime" => time(),
                        "operator" => session("uc_userinfo.name"),
                        "start" => $newInfo["now"]["start_time"],
                        "end" => $newInfo["now"]["end_time"],
                        "contract_start" => strtotime(I("post.delay_time")),
                        "contract_end" => strtotime($end),
                        "viptype" => $companyInfo["viptype"]
                    );
                    $log->addLog($data);
                }
            }
        }

        return array("code" => $code, "errmsg" => $errmsg);
    }

}