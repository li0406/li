<?php

namespace Home\Controller;

use Common\Enums\DepartmentEnum;
use Common\Enums\RbacRoleEnum;
use Home\Model\Db\HzsCompanyModel;
use Home\Model\Logic\HzsLogicModel;
use Home\Model\Logic\HzsStatisticLogicModel;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\RoundOrderApplyLogicModel;
use Think\Exception;

class HzsstatisticController extends HomeBaseController {

    /**
     * 合作商统计列表
     * @return [type] [description]
     */
    public function index(){
        $this->display();
    }

    public function gethzsstatistic()
    {
        if ($_POST) {
            $company_id = I("post.name");
            $begin = I("post.begin");
            $end = I("post.end");

            if (empty($begin) || empty($end)) {
                return $this->ajaxReturn(['error_code' => 1,'error_msg' => '请选择时间']);
            }

            //唯一编码
            $data['uuid'] = md5($begin.$end.$company_id);

            //结算时间
            $data['company_id'] = $company_id;
            $data['begin'] = $begin;
            $data['end'] = $end;

            //获取合作商信息
            $hzsLogic = new HzsLogicModel();
            $info = $hzsLogic->getHzsInfo($company_id);
            $data['name'] = $info['name'];
            $data['cooperate_mode'] = $hzsLogic->getCooperateMode($info['cooperate_mode']);
            $data['pay_mode'] = $hzsLogic->getPayMode($info['pay_mode']);
            $data['yy_name'] = $info["yy_name"];
            $data['create_time'] = date("Y-m-d",$info["create_time"]);
            $data['cooperate_time'] = $begin." - ".$end;

            //获取分单/发单数据
            $result = $hzsLogic->getHzsOrder($company_id,$begin,$end);
            $data['count'] = empty($result['count'])?0:$result['count'];//发单量
            $data['fen'] =  empty($result['fen'])?0:$result['fen'];//分单量

            //实际分单量
            $result = $hzsLogic->getHzsRealOrder($company_id,$begin,$end);
            $data['real_fen'] = empty($result['fen'])?0:$result['fen'];//实际分单量

            //量房量
            $result = $hzsLogic->getHzsLiangfangOrder($company_id,$begin);
            $data['lf_count'] = $result["count"];

            //前30天的分单量
            $monthStart = date("Y-m-d",strtotime("-30 day",strtotime($begin)));
            $monthEnd = date("Y-m-d",strtotime($begin));
            $result = $hzsLogic->getHzsOrder($company_id,$monthStart,$monthEnd);
           
            //量房率
            $data['lf_rate'] = 0;
            if ($result['count'] > 0 ) {
                $data['lf_rate'] = setInfNanToN(round($data['lf_count']/$result['fen'],2)*100);
            }

            return $this->ajaxReturn(['error_code' => 0,'error_msg' => '','data' => $data]);

        }
    }

    /**
     * 合作商统计详情页
     * 合作商下渠道标识统计结果
     * @return [type] [description]
     */
    public function company(){
        $page = I("get.p", 1);
        $limit = I("get.limit", 20);
        $companyid = I("get.companyid");

        $input = array(
            "src" => trim(I("get.src")),
            "sname" => trim(I("get.sname")),
            "begin" => I("get.begin"),
            "end" => I("get.end")
        );

        // 默认查询30天
        if (empty($input["begin"]) && empty($input["end"])) {
            $days = HzsStatisticLogicModel::DEFAULT_SEARCH_DAYS;
            $input["begin"] = date("Y-m-d", strtotime("-$days days"));
            $input["end"] = date("Y-m-d");
        }

        $hzsStatisticLogic = new HzsStatisticLogicModel();
        $data = $hzsStatisticLogic->getHzsSourceStatList($companyid, $input, $page, $limit);

        $info = $hzsStatisticLogic->getHzsCompany($companyid);

        $this->assign("info", $info);
        $this->assign("data", $data);
        $this->assign("input", $input);
        $this->display();
    }

    // 导出
    public function companyExplode(){
        $companyid = I("get.companyid");

        $input = array(
            "src" => trim(I("get.src")),
            "sname" => trim(I("get.sname")),
            "begin" => I("get.begin"),
            "end" => I("get.end")
        );

        // 默认查询30天
        if (empty($input["begin"]) && empty($input["end"])) {
            $input["begin"] = date("Y-m-d", strtotime("-30 days"));
            $input["end"] = date("Y-m-d");
        }

        $hzsStatisticLogic = new HzsStatisticLogicModel();
        $list = $hzsStatisticLogic->getHzsSourceStatExplodeList($companyid, $input);

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();

            //标题
            $herder = array(
                '渠道标识',
                '来源名称',
                '来源组',
                '是否付费',
                '发单',
                '分单',
                '实际分单',
                '量房量',
                '量房率'
            );
            $herder = array_values($herder);
            $writer->writeSheetRow('Sheet1', $herder);

            foreach ($list as $key => $value) {
                $v = array(
                    $value["src"],
                    $value["sname"],
                    $value["group_name"],
                    $value["charge"] == 2 ? "是" : "否",
                    $value["order_count"],
                    $value["order_fen_count"],
                    $value["order_now_real_fen"],
                    $value["order_lf_count"],
                    $value["lf_ratio_text"]
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
            header('Content-Disposition:attachment;filename="渠道商详情数据统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    /**
     * @des:渠道数据结算
     */
    public function hzsIntegration()
    {
        // 渠道专员要限制权限
        if (session("uc_userinfo.uid") == RbacRoleEnum::ROLE_QDZY) {
            $yy_id = session("uc_userinfo.id");
        }
        
        $hzsLogic = new HzsLogicModel();
        $hzs_account = $hzsLogic->getHzsCompanySelectList(1, $yy_id);
        $hzs_account_child = $hzsLogic->getHzsCompanySelectList(2, $yy_id);

        $this->assign('hzs_account', $hzs_account);
        $this->assign('hzs_account_child', $hzs_account_child);
        $this->display();
    }

    /**
     * @des:渠道合作商算数据结算数据
     */
    public function getHzsIntegration()
    {
        if ($_POST) {
            $name = trim(I("post.company_id"));
            $account = trim(I("post.hzs_account"));
            $childId = trim(I("post.child_id"));
            $begin = I("post.begin");
            $end = I("post.end");

            if ((empty($name) && empty($account)) && (empty($childId) || empty($begin) || empty($end))) {
                return $this->ajaxReturn(['error_code' => 1, 'error_msg' => '请选择查询合作商查询范围!']);
            }

            //返回数据,页面合作时间为各数据查询时间
            $hzsLogic = new HzsLogicModel();
            $data = $hzsLogic->getHzsChildInfo($name, $account, $childId, $begin, $end);

            return $this->ajaxReturn(['error_code' => 0, 'error_msg' => '', 'data' => $data]);
        }
    }

    /**
     * @des:渠道商数据统计
     */
    public function hzsAnalysis_bak()
    {
        //数据查询校验
        $param = [];
        $tab = I("get.tab");
        if ($tab == 1) {
            $name = trim(I("get.name"));
            $begin = trim(I("get.begin"));
            $end = trim(I("get.end"));
            $param = [
                'tab' => $tab,
                'name' => $name,
                'begin' => $begin,
                'end' => $end
            ];
        } elseif ($tab == 2) {
            $name = trim(I("get.name1"));
            $account = trim(I("get.account1"));
            $begin = trim(I("get.begin1"));
            $end = trim(I("get.end1"));
            $param = [
                'tab' => $tab,
                'name1' => $name,
                'account1' => $account,
                'begin1' => $begin,
                'end1' => $end
            ];
        } elseif ($tab == 3) {
            $name = trim(I("get.name2"));
            $account = trim(I("get.account2"));
            $source = trim(I("get.source2"));
            $begin = trim(I("get.begin2"));
            $end = trim(I("get.end2"));
            $param = [
                'tab' => $tab,
                'name2' => $name,
                'account2' => $account,
                'source2' => $source,
                'begin2' => $begin,
                'end2' => $end
            ];
        } elseif ($tab == 4) {
            $name = trim(I("get.name3"));
            $account = trim(I("get.account3"));
            $source = trim(I("get.source3"));
            $begin = trim(I("get.begin3"));
            $end = trim(I("get.end3"));
            $param = [
                'tab' => $tab,
                'name3' => $name,
                'account3' => $account,
                'source3' => $source,
                'begin3' => $begin,
                'end3' => $end
            ];
        }

        // 请求参数整理
        $powerParam = [
            'status' => 1
        ];
        // 权限相关校验，渠道部限制，渠道主管看全部，渠道专员看的自己分配到的渠道，其他都能看
        $power = $this->getPower($powerParam);

        if (empty($tab) || empty($begin) || empty($end)) {
            $this->assign('hzs_account', $power['hzs_account']);
            $this->assign('hzs_account_child', $power['hzs_account_child']);
            $this->assign('hzs_source_list',$power['hzs_source_list']);
            $this->display();
        }

        // 查询合作商名称未找到开启的合作商返回空值
        if (empty($power['parent_id'])) {
            $this->assign('hzs_account', $power['hzs_account']);
            $this->assign('hzs_source_list',$power['hzs_source_list']);
            $this->assign('list', array());
            $this->assign('input', $param);
            $this->display();
        }

        $begin = strtotime($begin);
        $end = strtotime($end) + 86399;
        $logic = new HzsLogicModel();
        if ($tab == 1) {
            //按合作商统计
            $data = $logic->getHzsAnalysisByHzsParent(!empty($name) ? [$name] : [], $account, $begin, $end);
            $this->assign('list', $data);
            $this->assign('input', $param);
            $this->assign('hzs_account', $power['hzs_account']);
            $this->assign('hzs_account_child', $power['hzs_account_child']);
            $this->assign('hzs_source_list', $power['hzs_source_list']);
            $this->display();
        } else if ($tab == 2) {
            //按合作商账户统计
            $this->assign('input', $param);
            $data = $logic->getHzsAnalysisByHzsId(!empty($name) ? [$name] : [], $account, $begin, $end);
            $this->assign('list', $data);
            $this->assign('hzs_account', $power['hzs_account']);
            $this->assign('hzs_account_child', $power['hzs_account_child']);
            $this->assign('hzs_source_list', $power['hzs_source_list']);
            $this->display();
        } else if ($tab == 3) {
            //按渠道来源统计
            $data = $logic->getHzsAnalysisBySource($begin, $end, !empty($name) ? [$name] : [], $account, $source);
            $this->assign('list', $data);
            $this->assign('input', $param);
            $this->assign('hzs_account', $power['hzs_account']);
            $this->assign('hzs_account_child', $power['hzs_account_child']);
            $this->assign('hzs_source_list', $power['hzs_source_list']);
            $this->display();
        } else if ($tab == 4) {
            //订单详情
            $data = $logic->getHzsAnalysisOrderInfoByHzsParent($begin, $end, !empty($name) ? [$name] : [], $account, $source);
            $this->assign('list', $data);
            $this->assign('input', $param);
            $this->assign('hzs_account', $power['hzs_account']);
            $this->assign('hzs_account_child', $power['hzs_account_child']);
            $this->assign('hzs_source_list', $power['hzs_source_list']);
            $this->display();
        }
    }

    // 渠道数据统计
    public function hzsanalysis() {
        // 数据查询校验
        $tab = I("get.tab", 1);

        $input = [
            "tab" => $tab,
            "company" => I("get.company"),
            "source" => I("get.source"),
            "account" => I("get.account"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "name" => I("get.name"),
            "export" => false,
            "yy_id" => 0,
        ];

        // 渠道专员要限制权限
        if (session("uc_userinfo.uid") == RbacRoleEnum::ROLE_QDZY) {
            $input["yy_id"] = session("uc_userinfo.id");
        }

        $hzsLogic = new HzsLogicModel();
        $company_list = $hzsLogic->getHzsCompanySelectList(1, $input["yy_id"]);
        $this->assign("company_list", $company_list);

        if (in_array($tab, [2, 3, 4])) {
            $account_list = $hzsLogic->getHzsCompanySelectList(2, $input["yy_id"]);
            $this->assign("account_list", $account_list);
        }

        if (in_array($tab, [3, 4])) {
            $source_list = $hzsLogic->getHzsSourceSelectList($input["yy_id"]);
            $this->assign("source_list", $source_list);
        }

        if (!empty($input["begin"]) && !empty($input["end"])) {
            switch ($tab) {
                case '1':
                    $data = $hzsLogic->getHzsCompanyAnalysis($input);
                    break;

                case '2':
                    $data = $hzsLogic->getHzsAccountAnalysis($input);
                    break;

                case '3':
                    $data = $hzsLogic->getHzsSourceAnalysis($input);
                    break;

                case '4':
                    $data = $hzsLogic->getHzsOrderAnalysis($input);
                    break;
            }
        }

        $name_show_list = [
            "1" => "合作商名称",
            "2" => "合作商账户",
            "3" => "渠道名称",
        ];

        $input["name_show"] = $name_show_list[$tab] ?? "";

        $this->assign("tab", $tab);
        $this->assign("data", $data);
        $this->assign("input", $input);
        $this->display();
    }



    public function findHzsParentList()
    {
        $name = I("post.q");
        $account = I("post.a");
        $model = new HzsCompanyModel();
        $hzsList = $model->getHzsList(['status' => 1, 'name' => $name, 'account' => $account]);
        //权限相关校验，渠道部限制，渠道主管看全部，渠道专员看的自己分配到的渠道，其他都能看
        if (session("uc_userinfo.department_top_id") == 28 && session("uc_userinfo.uid") != 1) {
            //获取用户可见渠道部门
            $userId = session("uc_userinfo.id");
            $accessibleHzsInfo = $model->getHzsList(['yy_id' => $userId]);
            if (empty($accessibleHzsInfo)) {
                $hzsList = [];
            } else {
                $hzsList = array_column($accessibleHzsInfo, 'name', 'id');
            }
        }
        return $this->ajaxReturn(['error_code' => 0, 'error_msg' => '', 'data' => $hzsList]);
    }

    // 渠道数据统计导出
    public function hzsanalysisExport(){
        // 数据查询校验
        $tab = I("get.tab", 1);

        $input = [
            "tab" => $tab,
            "company" => I("get.company"),
            "source" => I("get.source"),
            "account" => I("get.account"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "export" => true,
            "yy_id" => 0,
        ];

        // 渠道专员要限制权限
        if (session("uc_userinfo.uid") == RbacRoleEnum::ROLE_QDZY) {
            $input["yy_id"] = session("uc_userinfo.id");
        }
        
        switch ($tab) {
            case '1':
                $this->exportHzsCompanyAnalysis($input);
                break;

            case '2':
                $this->exportHzsAccountAnalysis($input);
                break;

            case '3':
                $this->exportHzsSourceAnalysis($input);
                break;

            case '4':
                $this->exportHzsOrderAnalysis($input);
                break;
        }
    }

    // 渠道数据统计-按合作商统计导出
    private function exportHzsCompanyAnalysis($input){
        $list = [];
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $hzsLogic = new HzsLogicModel();
            $data = $hzsLogic->getHzsCompanyAnalysis($input);
            $list = $data["list"];
        }

        import("Library.Org.PHP_XLSXWriter.xlsxwriter");
        $writer = new \XLSXWriter();

        $writer->writeSheetRow("Sheet1", array(
            "合作商名称",
            "消费",
            "展现",
            "UV",
            "系统后台UV",
            "点击率",
            "发单",
            "发单率",
            "发单成本",
            "分单",
            "分单率",
            "分单成本",
            "实际分单",
            "实际分单率",
            "实际分单成本",
            "有效注册量",
            "实际有效注册量"
        ));

        foreach ($list as $key => $item) {
            $row = [
                $item["name"],
                "", // 消费
                "", // 展现
                "", // UV
                $item["uv"],
                "", // 点击率
                $item["fadan"],
                "", // 发单率
                "", // 发单成本
                $item["fendan"],
                $item["fendan_rate"],
                "", // 分单成本
                $item["csos_fendan"],
                $item["csos_fendan_rate"],
                "", // 实际分单成本
                $item["valid"],
                $item["csos_valid"]
            ];
            $writer->writeSheetRow("Sheet1", $row);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="渠道数据统计-按合作商统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer->writeToStdOut("php://output");
        exit();
    }

    // 渠道数据统计-按合作商账户统计导出
    private function exportHzsAccountAnalysis($input){
        $list = [];
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $hzsLogic = new HzsLogicModel();
            $data = $hzsLogic->getHzsAccountAnalysis($input);
            $list = $data["list"];
        }

        import("Library.Org.PHP_XLSXWriter.xlsxwriter");
        $writer = new \XLSXWriter();
        $writer->writeSheetRow("Sheet1", [
            "合作商账户",
            "消费",
            "展现",
            "UV",
            "系统后台UV",
            "点击率",
            "发单",
            "发单率",
            "发单成本",
            "分单",
            "分单率",
            "分单成本",
            "实际分单",
            "实际分单率",
            "实际分单成本",
            "有效注册量",
            "实际有效注册量"
        ]);

        foreach ($list as $key => $item) {
            $row = [
                $item["name"],
                "", // 消费
                "", // 展现
                "", // UV
                $item["uv"],
                "", // 点击率
                $item["fadan"],
                "", // 发单率
                "", // 发单成本
                $item["fendan"],
                $item["fendan_rate"],
                "", // 分单成本
                $item["csos_fendan"],
                $item["csos_fendan_rate"],
                "", // 实际分单成本
                $item["valid"],
                $item["csos_valid"]
            ];
            $writer->writeSheetRow("Sheet1", $row);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="渠道数据统计-按合作商账户统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer->writeToStdOut("php://output");
        exit();
    }

    // 渠道数据统计-按渠道来源统计导出
    private function exportHzsSourceAnalysis($input){
        $list = [];
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $hzsLogic = new HzsLogicModel();
            $data = $hzsLogic->getHzsSourceAnalysis($input);
            $list = $data["list"];
        }

        import("Library.Org.PHP_XLSXWriter.xlsxwriter");
        $writer = new \XLSXWriter();
        $writer->writeSheetRow("Sheet1", [
            "合作商账户",
            "渠道来源",
            "消费",
            "展现",
            "UV",
            "系统后台UV",
            "点击率",
            "发单",
            "发单率",
            "发单成本",
            "分单",
            "分单率",
            "分单成本",
            "实际分单",
            "实际分单率",
            "实际分单成本",
            "有效注册量",
            "实际有效注册量"
        ]);

        foreach ($list as $key => $item) {
            $row = [
                $item["account_name"],
                $item["name"],
                "", // 消费
                "", // 展现
                "", // UV
                $item["uv"],
                "", // 点击率
                $item["fadan"],
                "", // 发单率
                "", // 发单成本
                $item["fendan"],
                $item["fendan_rate"],
                "", // 分单成本
                $item["csos_fendan"],
                $item["csos_fendan_rate"],
                "", // 实际分单成本
                $item["valid"],
                $item["csos_valid"]
            ];
            $writer->writeSheetRow("Sheet1", $row);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="渠道数据统计-按渠道来源统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer->writeToStdOut("php://output");
        exit();
    }

    // 渠道数据统计-订单详情导出
    public function exportHzsOrderAnalysis($input){
        $list = [];
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $hzsLogic = new HzsLogicModel();
            $data = $hzsLogic->getHzsOrderAnalysis($input);
            $list = $data["list"];
        }

        import("Library.Org.PHP_XLSXWriter.xlsxwriter");
        $writer = new \XLSXWriter();
        $writer->writeSheetRow("Sheet1", [
            "订单号",
            "发单日期",
            "渠道来源",
            "订单备注",
            "城市区县",
            "手机号",
            "订单状态",
        ]);

        foreach ($list as $key => $item) {
            $row = [
                $item["id"] . " ",
                $item["date_real"],
                $item["source_name"],
                $item["remarks"],
                $item["city_name"] . $item["area_name"],
                $item["tel"],
                $item["type_fw_name"],
            ];
            $writer->writeSheetRow("Sheet1", $row);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="渠道数据统计-订单详情.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer->writeToStdOut("php://output");
        exit();
    }

    // 权限验证
    private function getPower($param)
    {
        $model = new HzsCompanyModel();

        //合作商信息
        $hzsList = $model->getHzsList($param);
        $hzsAccountInfo = array_column($hzsList, null, 'id');
        //合作商id
        $parent_id = [];
        foreach ($hzsList as $value) {
            array_push($parent_id, (int)$value['id']);
        }
        $hzsChildList = $model->getChildAccountInfoByParent();
        //合作商账户信息
        $hzsAccountChild = array_column($hzsChildList, null, 'id');

        //合作商渠道来源信息
        $hzsSourceList = [];
        if (count($parent_id) > 0) {
            $hzsSourceList = $model->getHzsSourceList($parent_id, null, null, null);
        }

        //权限用户控制
        if (session("uc_userinfo.department_top_id") == 28 && session("uc_userinfo.uid") != 1) {
            //获取用户可见合作商
            $userId = session("uc_userinfo.id");
            $hzsAccountInfo = $model->getHzsList(['yy_id' => $userId]);
            //权限可见合作商账户
            $parent_id = [];
            foreach ($hzsList as $value) {
                array_push($parent_id, (int)$value['id']);
            }
            //获取用户可见合作商账户
            $hzsAccountChild = $model->getChildAccountInfoByParent($parent_id);
            //权限可见渠道信息
            $hzsSourceList = [];
            if (count($parent_id) > 0) {
                $hzsSourceList = $model->getHzsSourceList($parent_id, null, null, null);
            }
        }

        return $return = [
            'parent_id' => $parent_id,
            'param' => $param,
            'hzs_account' => $hzsAccountInfo,//合作商信息
            'hzs_account_child' => $hzsAccountChild,//合作商账户信息
            'hzs_source_list'=>$hzsSourceList //合作商渠道来源信息
        ];
    }
}