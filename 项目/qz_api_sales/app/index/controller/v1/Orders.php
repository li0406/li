<?php
// +----------------------------------------------------------------------
// | Orders 订单控制器
// +----------------------------------------------------------------------
namespace app\index\controller\v1;

use think\Loader;
use think\Request;
use think\Controller;
use app\common\model\logic\OrderLogic;
use app\common\model\logic\OrdersLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\OrdersCompanyReportLogic;
use PHP_XLSXWriter\XLSXWriter;
use Util\Page;

class Orders extends Controller
{
    /**
     * 会员分单明细 — 列表
     *
     * @param Request $request
     * @return array
     */
    public function fen_companys(Request $request, OrdersLogic $ordersLogic)
    {
        $input = $request->get();
        $page = sys_page_format($request->p, 1);
        $limit = sys_page_format($request->psize, 10);

        // 管辖城市
        if ($request->user["role_id"] != 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(4000002, '暂无权限查看数据', []);
            } else if (!empty($input["cs"]) && !in_array($input["cs"], $input["citys"])) {
                return sys_response(4000002, '暂无权限查看数据', []);
            }
        }

        // 获取列表数据
        $data = $ordersLogic->getOrderInfoList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $limit, $page),
            "options" => $data["options"]
        ]);

        return json($response);
    }

    /**
     * 会员分单明细 — 查看分单信息
     *
     * @param Request $request
     * @return array
     */
    public function fen_order_info(Request $request, OrdersLogic $ordersLogic)
    {
        $order_id = $request->get('id', '');
        if ($order_id) {
            return sys_response(0, '',
                $ordersLogic->orderInfo($order_id, ['companys', 'qdcompanys', 'allotNum', 'liangfangCompanys'])
            );
        } else {
            return sys_response(9000001);
        }
    }


    /**
     * 订单查看时间 — 列表
     *
     * @param Request $request
     * @return array
     */
    public function read_orders(Request $request, OrdersLogic $ordersLogic)
    {
        $p = $request->get('p', '1', 'intval');
        $psize = $request->get('psize', '10', 'intval');

        //获取API列表
        return $ordersLogic->getReadOrderList($request->get(), $p, $psize);
    }

    /**
     * 签单订单 —— 订单列表
     *
     * @param Request $request
     * @return array
     */
    public function qdOrders(OrdersLogic $orderLogic)
    {
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 20, 'intval');
        $get = $this->request->get();
        if (($error_msg = $this->validate($get, 'app\index\validate\Qiandan.qdlist')) !== true) {
            return sys_response(4000600, $error_msg, []);
        }
        return $orderLogic->qdOrders($get, $p, $psize);
    }

    /**
     * 签单订单 —— 签单审核
     *
     * @param Request $request
     * @return array
     */
    public function qdup(OrdersLogic $orderLogic,Request  $request)
    {
        $user = $request->user;
        $post = $this->request->post();
        if (empty($post['qiandan_status'])) {
            $post['qiandan_status'] = 0;
        }
        if (($error_msg = $this->validate($post, 'app\index\validate\Qiandan.qdup')) !== true) {
            return sys_response(4000600, $error_msg, []);
        }

        $ret = $orderLogic->qd_status_change($post,$user);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 会员签单管理 —— 咨询签单列表
     */
    public function consultation(OrdersCompanyReportLogic $orderLogic)
    {
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 20, 'intval');
        $get = $this->request->get();
        return $orderLogic->consultation($get, $p, $psize);
    }

    /**
     * 会员签单管理 —— 咨询签单详情
     */
    public function consultation_detail(OrdersCompanyReportLogic $orderLogic)
    {
        $id = $this->request->get('id', 0, 'intval');
        return $orderLogic->consultation_detail($id);
    }

    /**
     * 会员签单管理 —— 咨询签单审核
     */
    public function consultation_check(OrdersCompanyReportLogic $orderLogic)
    {
        $id = $this->request->post('id', 0, 'intval');
        return $orderLogic->consultation_check($id, $this->request->user);
    }

    /**
     * 统计管理-城市分单统计
     */
    public function orderReview(OrdersLogic $ordersLogic) {
        $input = $this->request->get();
        if (!empty($input["starttime"]) && !empty($input["endtime"])) {
            if (strtotime($input["starttime"]) > strtotime($input["endtime"])) {
                // 开始时间不能大于结束时间   CODE_4000019
                return sys_response(4000019, "开始时间不能大于结束时间");
            }

            if(strtotime("-6 month", strtotime($input["endtime"])) > strtotime($input["starttime"])){
                // 最多只能查询6个月的数据
                return sys_response(4000019, "最长查询时间不能超过6个月");
            }
        } else if (empty($input["starttime"]) && !empty($input["endtime"])) {
            $input["starttime"] = date("Y-m-d", strtotime("-6 month", strtotime($input["endtime"])));
        } else if (!empty($input["starttime"]) && empty($input["endtime"])) {
            $input["endtime"] = date("Y-m-d", strtotime("+6 month", strtotime($input["starttime"])));
        } else {
            $input["starttime"] = date("Y-m-d", strtotime("-6 month"));
            $input["endtime"] = date("Y-m-d");
        }

        $data = $ordersLogic->orderReview($input);
        if(count($data["list"]) > 0) {
            foreach ($data["list"] as $key => $val) {
                $data["list"][$key]["fendan_lv"] = $val["fendan_lv"] > 0 ? $val["fendan_lv"] . "%" : 0;
                $data["list"][$key]["zendan_lv"] = $val["zendan_lv"] > 0 ? $val["zendan_lv"] . "%" : 0;
                $data["list"][$key]["zongx_lv"] = $val["zongx_lv"] > 0 ? $val["zongx_lv"] . "%" : 0;
                $data["list"][$key]["qiandan_lv"] = $val["qiandan_lv"] > 0 ? $val["qiandan_lv"] . "%" : 0;
            }
        }

        $data["countinfo"]["all_fen_lv"] = $data["countinfo"]["all_fen_lv"] > 0 ? $data["countinfo"]["all_fen_lv"] . "%" : 0;
        $data["countinfo"]["all_zen_lv"] = $data["countinfo"]["all_zen_lv"] > 0 ? $data["countinfo"]["all_zen_lv"] . "%" : 0;
        $data["countinfo"]["all_zong_lv"] = $data["countinfo"]["all_zong_lv"] > 0 ? $data["countinfo"]["all_zong_lv"] . "%" : 0;
        $data["countinfo"]["all_qiandan_lv"] = $data["countinfo"]["all_qiandan_lv"] > 0 ? $data["countinfo"]["all_qiandan_lv"] . "%" : 0;


        return json(sys_response(0, "", $data));
    }

    /**
     * @des:(与前端确认，该接口前端已经不调用了，导出使用列表接口)
     * @param OrdersLogic $ordersLogic
     * @return array
     */
    public function orderReviewWritertoFile(OrdersLogic $ordersLogic) {
        $input = $this->request->get();
        if (!empty($input["starttime"]) && !empty($input["endtime"])) {
            if (strtotime($input["starttime"]) > strtotime($input["endtime"])) {
                // 开始时间不能大于结束时间   CODE_4000019
                return sys_response(4000019, "开始时间不能大于结束时间");
            }

            if(strtotime("-6 month", strtotime($input["endtime"])) > strtotime($input["starttime"])){
                // 最多只能查询6个月的数据
                return sys_response(4000019, "最长查询时间不能超过6个月");
            }
        } else if (empty($input["starttime"]) && !empty($input["endtime"])) {
            $input["starttime"] = date("Y-m-d", strtotime("-6 month", strtotime($input["endtime"])));
        } else if (!empty($input["starttime"]) && empty($input["endtime"])) {
            $input["endtime"] = date("Y-m-d", strtotime("+6 month", strtotime($input["starttime"])));
        } else {
            $input["starttime"] = date("Y-m-d", strtotime("-6 month"));
            $input["endtime"] = date("Y-m-d");
        }

        $data = $ordersLogic->orderReview($input);
        if(count($data["list"]) > 0){
            $data_list = $data["list"];
            foreach($data_list as $key => $val){
                $k = $key + 1;
                $data_array[$k] = [
                    $val["cname"],
                    $val["fadan"],
                    $val["weihu"],
                    $val["yihu"],
                    $val["fen"],
                    $val["fen_other"],
                    $val["fendan_lv"] > 0 ? $val["fendan_lv"] . "%" : 0,
                    $val["zen"],
                    $val["zen_other"],
                    $val["zendan_lv"] > 0 ? $val["zendan_lv"] . "%" : 0,
                    $val["zongx_lv"] > 0 ? $val["zongx_lv"] . "%" : 0,
                    $val["validnum"],
                    $val["qiandan"],
                    $val["qiandan_lv"] > 0 ? $val["qiandan_lv"] . "%" : 0,
                ];
            }

            //合计插入
            $countArr = count($data_array);
            $data_array[$countArr + 1] = [
                "合计",
                $data["countinfo"]["all_fa"],
                $data["countinfo"]["all_weihu"],
                $data["countinfo"]["all_yihu"],
                $data["countinfo"]["all_fen"],
                $data["countinfo"]["all_fenother"],
                $data["countinfo"]["all_fen_lv"] > 0 ? $data["countinfo"]["all_fen_lv"] . "%" : 0,
                $data["countinfo"]["all_zen"],
                $data["countinfo"]["all_zenother"],
                $data["countinfo"]["all_zen_lv"] > 0 ? $data["countinfo"]["all_zen_lv"] . "%" : 0,
                $data["countinfo"]["all_zong_lv"] > 0 ? $data["countinfo"]["all_zong_lv"] . "%" : 0,
                $data["countinfo"]["all_validnum"],
                $data["countinfo"]["all_qiandan"],
                $data["countinfo"]["all_qiandan_lv"] > 0 ? $data["countinfo"]["all_qiandan_lv"] . "%" : 0,
            ];



            $writer = new XLSXWriter();

            //文件名
            $filename = "城市分单统计-".date("YmdHis").".xlsx";
            //设置 header，用于浏览器下载
            header('Content-disposition: attachment; filename="'.$filename.'"');
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            //每列的标题头
            $title = array (
                0 => '城市',
                1 => '发单量',
                2 => '未呼量',
                3 => '已呼量',
                4 => '分单量',
                5 => '分没人跟',
                6 => '分单率',
                7 => '赠单量',
                8 => '赠没人跟',
                9 => '赠单率',
                10 => '总有效率',
                11 => '有效单量',
                12 => '签单量',
                13 => '签单率',
            );
            //工作簿名称
            $sheet1 = 'sheet1';

            //对每列指定数据类型，对应单元格的数据类型
            foreach ($title as $key => $item){
                $col_style[] = $key ==5 ? 'price': 'string';
            }

            //设置列格式，suppress_row: 去掉会多出一行数据；widths: 指定每列宽度
            $writer->writeSheetHeader($sheet1, $col_style, ['suppress_row'=>true,'widths'=>[20,20,20,20,20,20,20,20,20,20,20,20,20,20]] );
            //写入第二行的数据，顺便指定样式
            $writer->writeSheetRow($sheet1, ['城市分单统计：'.$input["starttime"]."—".$input["endtime"]],
                ['height'=>32,'font-size'=>20,'font-style'=>'bold','halign'=>'center','valign'=>'center']);

            /*设置标题头，指定样式*/
            $styles1 = array( 'font'=>'宋体','font-size'=>10,'font-style'=>'bold', 'fill'=>'#eee',
                'halign'=>'center', 'border'=>'left,right,top,bottom');
            $writer->writeSheetRow($sheet1, $title, $styles1);
            // 最后是数据，foreach写入
            foreach ($data_array as $value) {
                foreach ($value as $item) { $temp[] = $item;}
                $rows[] = $temp;
                unset($temp);
            }

            $styles2 = ['height'=>16];

            foreach($rows as $row){
                $writer->writeSheetRow($sheet1, $row, $styles2);
            }

            //合并单元格，第一行的大标题需要合并单元格
            $writer->markMergedCell($sheet1, $start_row = 0, $start_col = 0, $end_row = 0, $end_col = 13);
            //输出文档
            $writer->writeToStdOut();
            exit(0);
        }
    }


    public function orderQiandan(Request $request, OrdersLogic $ordersLogic)
    {
        $param = $request->param();

        if (!empty($param['start']) && !empty($param['end'])) {
            $param['start'] = strtotime($param['start']);
            $param['end'] = strtotime($param['end']) + 86400 - 1;
        } else {
            $param['end'] = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
            $param['start'] = strtotime("-3 month", $param['end']);
        }

        $page = isset($param['page']) ? $param['page'] : 1;
        $limit = isset($param['limit']) ? $param['limit'] : 10;

        $citys = AdminuserLogic::getCityIds();
        $count = $ordersLogic->getQianDanOrderCount($citys, $param);

        $pageClass = new Page($page, $limit, $count);
        $list = [];
        if ($count > 0) {
            $list = $ordersLogic->getQianDanOrderList($citys, $param, $pageClass->firstrow, $limit);
        }
        
        return sys_response(0, '', [
            'list' => $list,
            'search' => [
                'start' => empty($param['condition'])?date('Y-m-d', $param['start']):"",
                'end' => empty($param['condition'])?date('Y-m-d', $param['end']):"",
            ],
            'page' => $pageClass->show()
        ]);
    }

    public function noPassQiandanOrder(Request $request, OrdersLogic $ordersLogic)
    {
        $param = $request->param();

        $order_id = isset($param['order_id']) ? $param['order_id'] : '';
        $page = isset($param['page']) ? $param['page'] : 1;
        $limit = isset($param['limit']) ? $param['limit'] : 10;

        $count = $ordersLogic->getNoPassQianDanOrderCount($order_id);

        $pageClass = new Page($page, $limit, $count);
        $list = [];
        if ($count > 0) {
            $list = $ordersLogic->getNoPassQianDanOrderList($order_id, $pageClass->firstrow, $limit);
        }
        return sys_response(0, '', ['list' => $list, 'page' => $pageClass->show()]);
    }

    public function orderQiandanDetail(Request $request, OrdersLogic $ordersLogic)
    {
        $param = $request->param();
        $order_id = isset($param['order_id']) ? $param['order_id'] : '';
        if (empty($order_id)) {
            return sys_response(4000002);
        }
        //获取订单签单信息
        $info = $ordersLogic->getQiandanInfo($order_id);
        return sys_response(0, '', ['info' => $info]);
    }
}