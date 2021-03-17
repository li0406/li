<?php
//统计管理接口
namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\OrdersCompanyReportLogic;
use app\common\model\logic\OrdersLogic;
use app\common\model\logic\TeamLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\StatisticsLogic;
use think\Controller;
use think\Request;

Class Statistics extends Controller
{
    /**
     * 会员分单/签单统计
     *
     * @param Request $request
     * @param \app\index\validate\Statistics $statistics
     * @return array|\think\response\Json
     */
    public function user_statistics(Request $request, \app\index\validate\Statistics $statistics) {
        $page = $request->param('page', 1, 'intval');
        $page_count = $request->param('page_count', 20, 'intval');
        $input = $request->get();

        if (!$statistics->scene('getList')->check($input)) {
            return sys_response(4000002, $statistics->getError());
        }

        // 管辖城市
        if ($request->user["role_id"] != 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            } else if (!empty($input["cs"]) && !in_array($input["cs"], $input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            }
        }

        $staticLogic = new StatisticsLogic();
        $data = $staticLogic->getCompanyOrdersStatistics($input, $page, $page_count);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => $data["page"]
        ]);

        return json($response);
    }

    /**
     * 未读订单统计
     * @param  Request                        $request    [description]
     * @param  \app\index\validate\Statistics $statistics [description]
     * @return [type]                                     [description]
     */
    public function unread_statistics(Request $request, \app\index\validate\Statistics $statistics)
    {
        $page = $request->param('page', 1, 'intval');
        $page_count = $request->param('page_count', 20, 'intval');

        if (!$statistics->scene('getUnreadList')->check($request->get())) {
            return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002 . '：' . $statistics->getError());
        }
        $staticLogic = new StatisticsLogic();
        //获取符合装修的公司
        $company_id = $staticLogic->getCompanyList($request->param());
        $data = $staticLogic->getCompanyUnreadOrders($company_id, $request->param(), $page, $page_count);

        $response = sys_response(0, '', [
            'list' => $data["list"],
            'page' => sys_paginate($data["count"], $page_count, $page), #分页信息
            'options' => $data["options"]
        ]);
        
        return json($response);
    }

    /**
     * 统计管理 —— 城市签单汇总统计
     */
    public function consultation_count(OrdersCompanyReportLogic $orderLogic)
    {
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 20, 'intval');
        $get = $this->request->get();

        return $orderLogic->consultation_count($get, $p, $psize);
    }

    public function getCityOrderStat()
    {
        $param = input("get.");
        $validate = new \app\index\validate\Statistics();
        if (!$validate->scene('CityOrder')->check($param)) {
            return sys_response(4000002, $validate->getError());
        }

        if ( strtotime($param["begin"]) < strtotime("-1 year",strtotime($param["end"])) ) {
            return sys_response(4000002, "最多查询1年的数据");
        }

        //中文逗号处理
        $param["city"] = str_replace( "，",",",$param["city"]);

        //处理换行
        $param["city"] = str_replace(array("/r/n", "/r", "/n"), "", $param["city"]);


        $logic = new OrdersLogic();
        $result = $logic->getCityOrderStat($param["city"],$param["begin"],$param["end"],$param["yusuan"],$param["lx"],$param["fangshi"]);

        if (count($result) > 0) {
            return json(["error_code" => 0 ,"error_msg" => BaseStatusCodeEnum::CODE_0,"data" => $result]);
        }
        return json(["error_code" => 4000001,"error_msg" => BaseStatusCodeEnum::CODE_4000001]);
    }

    /**
     * 小报备业绩明细统计
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getPaymentDetailed(Request $request, StatisticsLogic $statisticsLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 默认查询时间
        if (empty($input["payment_date_begin"]) || empty($input["payment_date_end"])) {
            $input["payment_date_begin"] = date("Y-m-01");
            $input["payment_date_end"] = date("Y-m-d");
        }

        $data = $statisticsLogic->getStatisticPaymentDetailed($input, $page, $limit);

        $response = sys_response(0, "", [
                "list" => $data["list"],
                "page" => $data["page"],
                "options" => [
                    "payment_date_begin" => $input["payment_date_begin"],
                    "payment_date_end" => $input["payment_date_end"]
                ]
            ]);

        return json($response);
    }

    /**
     * 小报备业绩明细统计导出
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getPaymentDetailedExport(Request $request, StatisticsLogic $statisticsLogic){
        $input = $request->get();

        // 默认查询时间
        if (empty($input["payment_date_begin"]) || empty($input["payment_date_end"])) {
            $input["payment_date_begin"] = date("Y-m-01");
            $input["payment_date_end"] = date("Y-m-d");
        }

        $list = $statisticsLogic->getStatisticPaymentDetailedExport($input);

        $response = sys_response(0, "", [
                "list" => $list
            ]);

        return json($response);
    }

    /**
     * 小报备个人业绩统计
     * @param  Request         $request         [description]
     * @param  StatisticsLogic $statisticsLogic [description]
     * @return [type]                           [description]
     */
    public function getStatisticPaymentSaler(Request $request, StatisticsLogic $statisticsLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 默认查询时间
        if (empty($input["date_begin"]) || empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-t");
        }

        $data = $statisticsLogic->getStatisticPaymentSaler($input, $page, $limit);

        $response = sys_response(0, "请求成功", [
            "list" => $data["list"],
            "page" => $data["page"],
            "options" => [
                "date_begin" => $input["date_begin"],
                "date_end" => $input["date_end"]
            ]
        ]);

        return json($response);
    }

    /**
     * 小报备业绩统计导出
     * @param  Request         $request         [description]
     * @param  StatisticsLogic $statisticsLogic [description]
     * @return [type]                           [description]
     */
    public function getStatisticPaymentSalerExport(Request $request, StatisticsLogic $statisticsLogic){
        $input = $request->get();

        // 默认查询时间
        if (empty($input["month_begin"]) || empty($input["month_end"])) {
            $input["month_begin"] = date("Y-m");
            $input["month_end"] = date("Y-m");
        }

        $list = $statisticsLogic->getStatisticPaymentSalerExport($input);

        $response = sys_response(0, "", [
                "list" => $list,
            ]);

        return json($response);
    }

    /**
     * 小报备业绩明细统计合计
     * @param Request $request [description]
     * @return [type]           [description]
     */
    public function getPaymentSum(Request $request, StatisticsLogic $statisticsLogic)
    {
        $input = $request->get();

        // 默认查询时间
        if (empty($input["payment_date_begin"]) || empty($input["payment_date_end"])) {
            $input["payment_date_begin"] = date("Y-m-01");
            $input["payment_date_end"] = date("Y-m-d");
        }

        $data = $statisticsLogic->getStatisticPaymentSum($input);

        $response = sys_response(0, "", [
            "info" => $data,
        ]);

        return json($response);
    }

    public function cityVipCount(Request $request, StatisticsLogic $statisticsLogic)
    {
        $input = $request->get();

        if(empty($input['start_date'])){
            $input['start_date'] = date('Y-m-d');
        }

        if(empty($input['end_date'])){
            $input['end_date'] = date('Y-m-d');
        }

        $data = $statisticsLogic->getVipCountReport($input);

        return json(sys_response(0, '', $data));
    }
}