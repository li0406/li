<?php
/**
*
*/
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class MarketController extends HomeBaseController{
    /**
     * [category 运营中心业绩]
     * @return [type] [description]
     */
    public function index()
    {
        //月概览
        $result = $this->getCenterMonthStat(I("get.date"));
        $this->assign("orderInfo",$result["order"]);
        $this->assign("uvInfo",$result["uv"]);
        $this->display("yyzxyjygl");
    }

    /**
     * 财年目标设置
     */
    public function cainianmbsz()
    {
        //获取筛选的年份
        $year = I('get.year');
        $year = empty($year) ? date('Y') : $year;

        //获取并处理财年数据集
        $main['info'] = D('MarketYearPlan')->getYearPlanByPlanYear($year);
        if (!empty($main['info'])) {
            $sum = array(
                'plan_month' => '汇总',
                'shiji_fendan_zhuanhuanlv' => '-'
            );
            foreach ($main['info'] as $key => $value) {
                $main['info'][$key]['plan_month'] = date('n', strtotime($value['plan_month'])) . '月';
                $sum['tuiguangbu_fufei_shiji_fendan']   = $sum['tuiguangbu_fufei_shiji_fendan'] + $value['tuiguangbu_fufei_shiji_fendan'];
                $sum['tuiguangbu_mianfei_shiji_fendan'] = $sum['tuiguangbu_mianfei_shiji_fendan'] + $value['tuiguangbu_mianfei_shiji_fendan'];
                $sum['liuliangbu_mianfei_shiji_fendan'] = $sum['liuliangbu_mianfei_shiji_fendan'] + $value['liuliangbu_mianfei_shiji_fendan'];
                $sum['shiji_fendan_zongliang']          = $sum['shiji_fendan_zongliang'] + $value['shiji_fendan_zongliang'];
                $sum['ziran_liuliang']            = $sum['ziran_liuliang'] + $value['ziran_liuliang'];
                $main['info'][$key]['shiji_fendan_zhuanhuanlv'] = ($value['shiji_fendan_zhuanhuanlv'] * 100) . '%';
            }
        }

        //设置可选择年份
        $option = array();
        $start_year = 2017;
        $end_year = date('Y', strtotime('+1 year'));
        for ($start_year; $start_year <= $end_year; $start_year++) {
            $option[] = $start_year;
        }

        //分配变量
        $main['info'][] = $sum;
        $main['year']   = $year;
        $main['option'] = $option;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 导入财年计划
     * @return ajax 返回结果
     */
    public function exportYearPlan()
    {
        //处理文件上传
        if (!empty($_FILES)) {

            //判断年份
            $year = I('post.year');
            if (empty($year)) {
                $this->ajaxReturn(array('status' =>0, 'info' => '请选择年份'));
            }
            $year = date('Y', strtotime($year . '-01-01'));

            //解析excel文件
            $fileType = explode(".", $_FILES["file_data"]["name"]);
            $ext = $fileType[1];
            $filePath = TEMP_PATH;
            if(!is_dir($filePath)){
                mkdir($filePath,0777);
            }
            $path = $_FILES["file_data"]["tmp_name"];
            $filePath = $filePath.time().".".$ext;
            move_uploaded_file($path, $filePath);
            import('Library.Org.Phpexcel.PHPExcel',"",".php");
            import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
            if($ext == "xls"){
                import("Library.Org.PHPExcel.Reader.Excel5","",".php");
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            }elseif($ext == "xlsx"){
                import("Library.Org.PHPExcel.Reader.Excel2007","",".php");
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            }
            $objPHPExcel = $objReader->load($filePath);
            $data = array();
            $save = array();
            for($j=3; $j <= 14; $j++) {
                $str = "";
                for($k = 'A'; $k <= 'H'; $k++) {
                    $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
                }
                $data[] = array_filter(explode("|*|",$str));

            }
            $add_time = date('Y-m-d H:i:s');
            //添加其他数据信息
            foreach ($data as $key => $value) {
                $month = trim(str_replace('月', '', $value[0]));
                if ($month == 1) {
                    $plan_month = date('Y-m', strtotime($year + 1 . '-' . $month));
                } else {
                    $plan_month = date('Y-m', strtotime($year . '-' . $month));
                }
                $save[] = array(
                    'plan_year'                              => intval($year),
                    'plan_month'                             => $plan_month,
                    'tuiguangbu_fufei_shiji_fendan'          => intval($value[1]),
                    'tuiguangbu_mianfei_shiji_fendan'        => intval($value[2]),
                    'liuliangbu_mianfei_shiji_fendan'        => intval($value[3]),
                    'shiji_fendan_zongliang'                 => intval($value[4]),
                    'ziran_liuliang'                         => intval($value[5]),
                    'shiji_fendan_zhuanhuanlv'               => number_format($value[6], 5),
                    'status'                                 => 1,
                    'add_time'                               => $add_time
                );
            }
            //删除本地的文件
            if(file_exists($filePath)){
                unlink($filePath);
            }
            D('MarketYearPlan')->setYearPlayDisabledByPlanYear($year);
            D('MarketYearPlan')->addAllYearPlan($save);
            $this->ajaxReturn(array('status' =>1));
        }
        $this->ajaxReturn(array('status' =>0, 'info' => '操作失败'));
    }


    /**
     * 微信数据录入列表
     */
    public function wenxinFollower()
    {
        //请求参数处理
        $add_time_start = I('get.add_time_start');
        $add_time_end = I('get.add_time_end');
        $add_time_start = empty($add_time_start) ? '' : date('Y-m-d H:i:s', strtotime($add_time_start));
        $add_time_end = empty($add_time_end) ? '' : date('Y-m-d H:i:s', strtotime($add_time_end));

        //每页显示数量
        $each = 20;
        $count = D('MarketWeixinFollower')->getCount($add_time_start, $add_time_end);
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $each);
            $main['info']['page'] = $p->show();
            $main['info']['list'] = D('MarketWeixinFollower')->getList($add_time_start, $add_time_end, $p->firstRow, $p->listRows);
        }

        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 新增微信数据录入
     */
    public function addWenxinFollower()
    {
        $data = I('post.');
        $last = D('MarketWeixinFollower')->getList('', '', 0, 1)[0];
        //如果是post请求则为新增数据
        if (!empty($data)) {
            $follower = $last['current_follower'] + $last['increased_follower'];
            if ($follower + $data['increased_follower'] < 0) {
                $this->ajaxReturn(array('info' => '新增失败，新增后的粉丝数量不能为负数','status' =>0));
                exit();
            }
            $save = array(
                'current_follower'   => $follower,
                'increased_follower' => $data['increased_follower'],
                'adminuser_id'       => getAdminUser('id'),
                'add_time'           => date('Y-m-d H:i:s')
            );
            $result = D('MarketWeixinFollower')->add($save);
            if ($result) {
                $this->ajaxReturn(array('status' =>1));
            }
            $this->ajaxReturn(array('info' => '新增失败','status' =>0));
        }
        //get请求则为获取最新数据，用于下一步新增操作
        $this->ajaxReturn(array('data' => $last, 'status' =>1));
    }

     /**
     * [category 运营中心业绩-财年概览]
     */
    public function yyzxcngl()
    {
        //获取财年数据
        $result = $this->getCenterYearStat();
        $this->assign("conversion",$result["conversion"]);
        $this->assign("uv",$result["uv"]);
        $this->assign("order",$result["order"]);
        $this->assign("month",$result["month"]);
        $this->assign("year",$result["year"]);
        $this->display();
    }

    /**
     * [category 运营中心业绩-数据详情]
     */
    public function yyzxyjsjxq()
    {
        $result = $this->getCenterDetails(I("get.begin"),I("get.end"));
        $this->assign("order",$result["order"]);
        $this->assign("month",$result["month"]);
        $this->display();
    }


    /**
     * 流量部业绩-月概览
     */
    public function llbyjygl()
    {
        $condition = I('get.condition');
        if (empty($condition)) {
            $condition = date('Y-m');
        }
        /* S-获取上月信息 */
        $end_time = strtotime($condition) - 1;
        $start_time = strtotime(date('Y-m-01 00:00:00', $end_time));
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        $last = array();
        foreach ($result as $key => $value) {
            $time = date('m-d', $value['time']);
            //部门为流量部且为自然流量的
            if ($value['dept_id'] == 2) {
                $last['zrll']['total']['uv']     = $last['zrll']['total']['uv'] + $value['uv'];
                $last['mffd']['real_fen_count'] = $last['mffd']['real_fen_count'] + $value['real_fen_count'];
            }
        }
        /* E-获取上月信息 */

        /* S-获取本月信息 */
        $start_time = strtotime($condition);
        $end_time = strtotime($condition . ' +1 month') - 1;
        if ($end_time > strtotime(date('Y-m-d 23:59:59'))) {
            $end_time = strtotime(date('Y-m-d 23:59:59'));
        }
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        $current = array();
        foreach ($result as $key => $value) {
            $time                                = date('m-d', strtotime($value['time']));
            $current['uv'][$time]                = $current['uv'][$time] + $value['uv'];
            $current['total']['uv']              = $current['total']['uv'] + $value['uv'];
            $current['total']['order_count']     = $current['total']['order_count'] + $value['order_count'];
            $current['total']['order_fen_count'] = $current['total']['order_fen_count'] + $value['order_fen_count'];
            $current['total']['real_fen_count']  = $current['total']['real_fen_count'] + $value['real_fen_count'];
            //部门为流量部且为自然流量的
            if ($value['dept_id'] == 2) {
                $current['zrll']['uv'][$time]       = $current['zrll']['uv'][$time] + $value['uv'];
                $current['zrll']['order_count']     = $current['zrll']['order_count'] + $value['order_count'];
                $current['zrll']['order_fen_count'] = $current['zrll']['order_fen_count'] + $value['order_fen_count'];
                $current['zrll']['real_fen_count']  = $current['zrll']['real_fen_count'] + $value['real_fen_count'];
                $current['zrll']['total']['uv']     = $current['zrll']['total']['uv'] + $value['uv'];
                $current['mffd']['real_fen_count'] = $current['mffd']['real_fen_count'] + $value['real_fen_count'];
            }
        }
        /* E-获取本月信息 */

        //获取财年目标设置
        $plan = array(
            'last'    => D('MarketYearPlan')->getYearPlanByPlanMonth(date('Y-m', $start_time - 86400)),
            'current' => D('MarketYearPlan')->getYearPlanByPlanMonth(date('Y-m', $start_time))
        );

        //获取图标数据
        $echart = $xAxis = array();
        while ($start_time < $end_time) {
            $time       = date('m-d', $start_time);
            $xAxis[]    = $time;
            $series[]   = empty($current['zrll']['uv'][$time]) ? 0 : $current['zrll']['uv'][$time];
            $start_time = $start_time + 86400;
        }

        $echart = array(
            'xAxis'  => json_encode($xAxis),
            'series' => json_encode($series)
        );

        $main['last']      = $last;
        $main['current']   = $current;
        $main['plan']      = $plan;
        $main['echart']    = $echart;
        $main['condition'] = $condition;
        $this->assign('main', $main);
        $this->display();
    }


    /**
     * 流量部业绩-财年概览
     */
    public function llbyjcngl()
    {
        $year = I('get.year');
        if (empty($year)) {
            $year = date('Y');
        }
        $start_time = strtotime($year . '-02-01 00:00:00');
        $end_time   = strtotime(($year + 1) . '-01-31 23:59:59');
        if ($end_time > time()) {
            $end_time = strtotime(date('Y-m-01') . ' +1 month') - 1;
        }

        //记录时间
        $time = array();
        $start = $start_time;
        $end = $end_time;
        while ($start < $end) {
            $time[date('Y-m', $start)] = date('Y-m', $start);
            $start                     = strtotime(date('Y-m-01', $start) . ' +1 month');
        }

        //查询结果
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        //自然流量UV,分单量,免费分单,付费分单
        $uv = $order = array();
        foreach ($result as $key => $value) {
            if ($value['dept_id'] == 2) {
                $month = date('Y-m', strtotime($value['time']));
                //自然流量UV
                $uv[$month]   = $uv[$month] + $value['uv'];
                //分单量
                $order[$month] = $order[$month] + $value['real_fen_count'];
            }
        }

        //20170724-获取以前的数据,进行覆盖
        $before = D('MarketSummary')->getBeforeData('llb');
        foreach ($time as $key => $value) {
            if (!empty($before['uv'][$key])) {
                $uv[$key] = $before['uv'][$key];
            }
            if (!empty($before['mianfei'][$key])) {
                $order[$key] = $before['mianfei'][$key];
            }
        }

        //获取财年计划
        $plan = array();
        $result = D('MarketYearPlan')->getYearPlanByPlanYear($year);
        foreach ($result as $key => $value) {
            $plan[$value['plan_month']] = $value;
        }
        //图表数据
        $echart_uv['xAxis'] = $echart_uv['series']['mb'] = $echart_uv['series']['wc'] = $echart_order['xAxis'] = $echart_order['series']['mb'] = $echart_order['series']['wc'] = $list_uv = $list_order = array();
        foreach ($time as $key => $value) {
            //uv图表
            $echart_uv['xAxis'][]           = date('m', strtotime($key . '-01')) . '月';
            $echart_uv['series']['mb'][]    = $plan[$key]['ziran_liuliang'];
            $echart_uv['series']['wc'][]    = $uv[$key];
            //订单图表
            $echart_order['xAxis'][]        = date('m', strtotime($key . '-01')) . '月';
            $echart_order['series']['mb'][] = $plan[$key]['liuliangbu_mianfei_shiji_fendan'];
            $echart_order['series']['wc'][] = $order[$key];
            //uv列表
            $list_uv[] = array(
                'month' => date('Y年m月', strtotime($key . '-01')),
                'mb'    => $plan[$key]['ziran_liuliang'],
                'wc'    => $uv[$key],
                'ljmb'  => end($list_uv)['ljmb'] + $plan[$key]['ziran_liuliang'],
                'ljwc'  => end($list_uv)['ljwc'] + $uv[$key]
            );
            //订单列表
            $list_order[] = array(
                'month' => date('Y年m月', strtotime($key . '-01')),
                'mb'    => $plan[$key]['liuliangbu_mianfei_shiji_fendan'],
                'wc'    => $order[$key],
                'ljmb'  => end($list_order)['ljmb'] + $plan[$key]['liuliangbu_mianfei_shiji_fendan'],
                'ljwc'  => end($list_order)['ljwc'] + $order[$key]
            );
        }

        $main = array(
            'year'         => $year,
            'echart_uv'    => $echart_uv,
            'echart_order' => $echart_order,
            'list_uv'      => $list_uv,
            'list_order'   => $list_order
        );
        $this->assign('main', $main);
        $this->display();
    }
    /**
     * [category 推广部业绩-月概览]
     * @return [type] [description]
     */
    public function tgbyjygl()
    {
        //获取部门月概览
        $result = $this->getDepMonthStat(I("get.date"));
        $this->assign("orderInfo",$result["order"]);
        $this->assign("list",$result["list"]);
        $this->display();
    }
    /**
     * [category 推广部业绩-财年概览]
     * @return [type] [description]
     */
    public function tgbyjcngl()
    {
        $result = $this->getDeptYearStat(I("get.year"));
        $this->assign("order",$result["order"]);
        $this->assign("month",$result["month"]);
        $this->assign("year",$result["year"]);
        $this->display();
    }
    /**
     * [category 推广部业绩-数据详情]
     * @return [type] [description]
     */
    public function tgbyjsjxq()
    {
        $result = $this->getDeptDetails(I("get.begin"),I("get.end"),I("get.state"),I("get.source"));
        $this->assign("order",$result["order"]);
        $this->assign("month",$result["month"]);
        $this->display();
    }

    /**
     * 每月目标录入
     */
    public function mymblr()
    {
        //新增操作
        if (IS_AJAX) {
            $data = I('post.');
            $save = array(
                'plan_month'             => $data['month'],
                'shipinzu_shiji_fendan'  => intval($data['shipin']),
                'zimeitizu_shiji_fendan' => intval($data['zimeiti']),
                'status'                 => 1,
                'add_time'               => date('Y-m-d H:i:s')
            );

            //判断月份是否正确
            if (false == check_date($save['plan_month'], 'Y-m')) {
                $this->ajaxReturn(array('status' =>0, 'info' => '请选择正确的月份'));
            }

            //根据月份计算出该月属于哪一财年 如果在02-01之前，则归为去年的
            $time = strtotime($save['plan_month']);
            if (strtotime(date('Y', $time) . '-02-01') > $time) {
                $save['plan_year'] = date('Y', $time) - 1;
            } else {
                $save['plan_year'] = date('Y', $time);
            }

            //将之前的月份数据设置为不可用
            D('MarketTuiguangMonthPlan')->setMonthPlayDisabledByPlanMonth($save['plan_month']);
            //插入新数据
            $result = D('MarketTuiguangMonthPlan')->addMarketTuiguangMonthPlan($save);
            if ($result) {
                $this->ajaxReturn(array('status' =>1));
            }
            $this->ajaxReturn(array('status' =>0, 'info' => '操作失败'));
        }

        //获取筛选的年份
        $year = I('get.year');
        $year = empty($year) ? date('Y') : $year;

        //获取记录
        $main['info'] = D('MarketTuiguangMonthPlan')->getMonthPlanByPlanYear($year);

        //获取最新的记录时间
        foreach ($main['info'] as $key => $value) {
            if ($main['last']['id'] < $value['id']) {
                $main['last'] = $value;
            }
        }

        //设置可选择年份
        $option = array();
        $start_year = 2017;
        $end_year = date('Y', strtotime('+1 year'));
        for ($start_year; $start_year <= $end_year; $start_year++) {
            $option[] = $start_year;
        }

        $main['option'] = $option;
        $main['year']   = $year;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 产品部业绩-月概览
     */
    public function cpbyjygl()
    {
        $condition = I('get.condition');
        if (empty($condition)) {
            $condition = date('Y-m');
        }
        /* S-获取上月信息 */
        $end_time = strtotime($condition) - 1;
        $start_time = strtotime(date('Y-m-01 00:00:00', $end_time));
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        $last = array();
        foreach ($result as $key => $value) {
            $time = date('m-d', $value['time']);
            //部门为流量部且为自然流量的
            if ($value['dept_id'] == 2) {
                $last['zrll']['total']['uv']     = $last['zrll']['total']['uv'] + $value['uv'];
            }
            //四大渠道(百度 360 视频 微信)的实际分单
            if (in_array($value['groupid'], array('8', '40', '7', '3'))) {
                $last['sjfd']['uv'] = $last['sjfd']['uv'] + $value['uv'];
                $last['sjfd']['real_fen_count'] = $last['sjfd']['real_fen_count'] + $value['real_fen_count'];
            }
        }
        /* E-获取上月信息 */

        /* S-获取本月信息 */
        $start_time = strtotime($condition);
        $end_time = strtotime($condition . ' +1 month') - 1;
        if ($end_time > strtotime(date('Y-m-d 23:59:59'))) {
            $end_time = strtotime(date('Y-m-d 23:59:59'));
        }
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        $current = array();
        foreach ($result as $key => $value) {
            $time                                = date('m-d', strtotime($value['time']));
            $current['uv'][$time]                = $current['uv'][$time] + $value['uv'];
            $current['total']['uv']              = $current['total']['uv'] + $value['uv'];
            $current['total']['order_count']     = $current['total']['order_count'] + $value['order_count'];
            $current['total']['order_fen_count'] = $current['total']['order_fen_count'] + $value['order_fen_count'];
            $current['total']['real_fen_count']  = $current['total']['real_fen_count'] + $value['real_fen_count'];
            //部门为流量部且为自然流量的
            if ($value['dept_id'] == 2) {
                $current['zrll']['uv'][$time]       = $current['zrll']['uv'][$time] + $value['uv'];
                $current['zrll']['total']['uv']     = $current['zrll']['total']['uv'] + $value['uv'];
            }
            //四大渠道(百度 360 视频 微信)的实际分单
            if (in_array($value['groupid'], array('8', '40', '7', '3'))) {
                $current['sjfd']['uv'] = $current['sjfd']['uv'] + $value['uv'];
                $current['sjfd']['real_fen_count']  = $current['sjfd']['real_fen_count'] + $value['real_fen_count'];
            }
        }
        /* E-获取本月信息 */

        //获取财年目标设置
        $plan = array(
            'last'    => D('MarketYearPlan')->getYearPlanByPlanMonth(date('Y-m', $start_time - 86400)),
            'current' => D('MarketYearPlan')->getYearPlanByPlanMonth(date('Y-m', $start_time))
        );

        //获取图标数据
        $echart = $xAxis = array();
        while ($start_time < $end_time) {
            $time       = date('m-d', $start_time);
            $xAxis[]    = $time;
            $series[]   = empty($current['zrll']['uv'][$time]) ? 0 : $current['zrll']['uv'][$time];
            $start_time = $start_time + 86400;
        }

        $echart = array(
            'xAxis'  => json_encode($xAxis),
            'series' => json_encode($series)
        );

        $main['last']     = $last;
        $main['current']  = $current;
        $main['plan']     = $plan;
        $main['echart']   = $echart;
        $main['condition'] = $condition;

        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 产品部业绩-财年概览
     */
    public function cpbyjcngl()
    {
        $year = I('get.year');
        if (empty($year)) {
            $year = date('Y');
        }
        $start_time = strtotime($year . '-02-01 00:00:00');
        $end_time   = strtotime(($year + 1) . '-01-31 23:59:59');
        if ($end_time > time()) {
            $end_time = strtotime(date('Y-m-01') . ' +1 month') - 1;
        }

        //记录时间
        $time = array();
        $start = $start_time;
        $end = $end_time;
        while ($start < $end) {
            $time[date('Y-m', $start)] = date('Y-m', $start);
            $start                     = strtotime(date('Y-m-01', $start) . ' +1 month');
        }

        //查询结果
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        //自然流量UV,分单量,免费分单,付费分单
        $uv = $order = $sjfd = array();
        foreach ($result as $key => $value) {
            $month = date('Y-m', strtotime($value['time']));
            if ($value['dept_id'] == 2) {
                //自然流量UV
                $uv[$month]   = $uv[$month] + $value['uv'];
            }
            //四大渠道(百度 360 视频 微信)的实际分单
            if (in_array($value['groupid'], array('8', '40', '7', '3'))) {
                $sjfd['uv'][$month]   = $sjfd['uv'][$month] + $value['uv'];
                $order[$month] = $order[$month] + $value['real_fen_count'];
            }
        }

        //20170724-获取以前的数据,进行覆盖
        $before = D('MarketSummary')->getBeforeData('cpb');
        foreach ($time as $key => $value) {
            if (!empty($before['uv'][$key])) {
                $uv[$key] = $before['uv'][$key];
            }
            if (!empty($before['turn_rate_uv'][$key])) {
                $sjfd['uv'][$key] = $before['turn_rate_uv'][$key];
            }
            if (!empty($before['turn_rate_order'][$key])) {
                $order[$key] = $before['turn_rate_order'][$key];
            }
        }

        //获取财年计划
        $plan = array();
        $result = D('MarketYearPlan')->getYearPlanByPlanYear($year);
        foreach ($result as $key => $value) {
            $plan[$value['plan_month']] = $value;
        }

        //图表数据
        $echart_uv['xAxis'] = $echart_uv['series']['mb'] = $echart_uv['series']['wc'] = $echart_order['xAxis'] = $echart_order['series']['mb'] = $echart_order['series']['wc'] = $list_uv = $list_order = array();
        foreach ($time as $key => $value) {
            //uv图表
            $echart_uv['xAxis'][]           = date('m', strtotime($key . '-01')) . '月';
            $echart_uv['series']['mb'][]    = $plan[$key]['ziran_liuliang'];
            $echart_uv['series']['wc'][]    = $uv[$key];
            //实际分单转化率财年达成图表
            $echart_order['xAxis'][]        = date('m', strtotime($key . '-01')) . '月';
            $echart_order['series']['mb'][] = $plan[$key]['shiji_fendan_zhuanhuanlv'] * 100;
            $echart_order['series']['wc'][] = number_format(($order[$key] / $sjfd['uv'][$key] * 100), 3);

            //uv列表
            $list_uv[] = array(
                'month' => date('Y年m月', strtotime($key . '-01')),
                'mb'    => $plan[$key]['ziran_liuliang'],
                'wc'    => $uv[$key],
                'ljmb'  => end($list_uv)['ljmb'] + $plan[$key]['ziran_liuliang'],
                'ljwc'  => end($list_uv)['ljwc'] + $uv[$key]
            );
            //实际分单转化率财年达成列表
            $list_order[] = array(
                'month' => date('Y年m月', strtotime($key . '-01')),
                'mb'    => $plan[$key]['shiji_fendan_zhuanhuanlv'] * 100,
                'wc'    => number_format(($order[$key] / $sjfd['uv'][$key] * 100), 3),
                'ljmb'  => end($list_order)['ljmb'] + $plan[$key]['shiji_fendan_zhuanhuanlv'],
                'ljwc'  => end($list_order)['ljwc'] + ($order[$key] / $sjfd['uv'][$key])
            );
        }

        $main = array(
            'year'         => $year,
            'echart_uv'    => $echart_uv,
            'echart_order' => $echart_order,
            'list_uv'      => $list_uv,
            'list_order'   => $list_order
        );
        $this->assign('main', $main);
        $this->display();
    }
    /**
     * [category 推广部业绩-订单跟踪]
     * @return [type] [description]
     */
    public function tgbyjddgz()
    {
        //获取订单详细信息、
        $result = $this->getOrderDetailsList(I("get.id"),I("get.begin"),I("get.end"));
         $this->assign("tag",$result["list"][0]["tag"]);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 每月访客按城市分析
     * @return [type] [description]
     */
    public function cityorderstat()
    {
        $list = $this->getCityOrderstat(I("get.date"));
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 运营中心月概览
     * @param  [date] $date [查询时间]
     * @return array
     */
    private function getCenterMonthStat($date)
    {
        if (empty($date)) {
            $date = date("Y-m");
        }

        $time = strtotime($date);
        $begin = date("Y-m-d" , mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $end = date("Y-m-d", mktime(23,59,59,date("m",$time),date("t",$time),date("Y",$time)));

        //获取运营中心数据
        $result = D("MarketSummary")->getCenterMonthOrder($begin,$end);
        foreach ($result as $key => $value) {
            $order[$value["dept_id"]] = $value;
            $order["all"]["uv"] += $value["uv"];
            $order["all"]["pv"] += $value["pv"];
            $order["all"]["order_count"] += $value["order_count"];
            $order["all"]["order_fen_count"] += $value["order_fen_count"];
            $order["all"]["real_fen_count"] += $value["real_fen_count"];
            //计算分单率
            $order[$value["dept_id"]]["real_fen_rete"] = round($value["real_fen_count"]/$value["order_count"],4)*100;
            $order["all"]["real_fen_rete"] = round($order["all"]["real_fen_count"]/$order["all"]["order_count"],4)*100;
        }

        //运营中心UV统计
        $result = D("MarketSummary")->getCenterMonthUv($begin,$end);
        foreach ($result as $key => $value) {
            $uv["data"][$value["time"]] = $value;
            $uv["Month"]["uv"] += $value["uv"];
        }
        $day = date("t" ,$time);
        if (date("m" ,$time) == date("m") && date("d" ,$time) != date("t",$time)) {
            $day = date("d" ,strtotime("-1 day"));
        }

        for ($i=0; $i < $day ; $i++) {
            $date = date("Y-m-d", strtotime("+$i day", strtotime($begin)));
            $uv["date"][] = date("m月d日", strtotime("+$i day", strtotime($begin)));
            $uv["uv"][] = $uv["data"][$date]["uv"] == null?0:$uv["data"][$date]["uv"];
        }

        //获取上月完成UV量
        $beforeBegin = strtotime("-1 month",strtotime($begin));
        $beforeEnd = strtotime($end);
        $beforeEnd = strtotime("-1 month",mktime(23,59,59,date("m",$beforeEnd),date("t",$beforeEnd),date("Y",$beforeEnd)));

        $result = D("MarketSummary")->getCenterMonthUv(date("Y-m-d", $beforeBegin),date("Y-m-d",$beforeEnd));
        foreach ($result as $key => $value) {
            $uv["Month"]["before_uv"] +=  $value["uv"];
        }

        //获取当月的目标量
        $month = date("Y-m",$time);
        $result = D("MarketYearPlan")->getYearPlanByPlanMonth($month);
        $order["dept"][1]["now"]["fufei"] = $result["tuiguangbu_fufei_shiji_fendan"];
        $order["dept"][1]["now"]["mianfei"] = $result["tuiguangbu_mianfei_shiji_fendan"] ;
        $order["dept"][2]["now"]["mianfei"] = $result["liuliangbu_mianfei_shiji_fendan"];
        $order["dept"]["all"]["now"]["all"] = $order["dept"][1]["now"]["fufei"]+$order["dept"][1]["now"]["mianfei"]+$order["dept"][2]["now"]["mianfei"];

        $order["conversion"]["now"]["conversion_target_rate"] = $result["shiji_fendan_zhuanhuanlv"]*100;
        $uv["Month"]["uv_goals"] = empty($result["ziran_liuliang"])?0:$result["ziran_liuliang"];


        //uv完成进度
        $uv["Month"]["uv_schedule"] = round($uv["Month"]["uv"]/$uv["Month"]["uv_goals"],4)*100;
        //时间进度
        $uv["Month"]["day_schedule"] = round($day/date("t",$time),4)*100;
        //进度差
        $uv["Month"]["offset"] = round($uv["Month"]["uv_schedule"] - $uv["Month"]["day_schedule"],2);

        //付费免费分单量统计
        $result = D("MarketSummary")->getCenterMonthChannelOrder($begin,$end);
        foreach ($result as $key => $value) {
            $order["dept"][$value["dept_id"]]["now"][$value["charges"]]["real_fen_count"] = $value["real_fen_count"];
            if ($value["dept_id"] == 1) {
                if ($value["charges"] == 1) {
                    $order["dept"][$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] = round($value["real_fen_count"]/$order["dept"][1]["now"]["mianfei"],4)*100;
                } else {
                    $order["dept"][$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] = round($value["real_fen_count"]/$order["dept"][1]["now"]["fufei"],4)*100;
                }
            } else {
                $order["dept"][$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] = round($value["real_fen_count"]/$order["dept"][2]["now"]["mianfei"],4)*100;
            }
            $order["dept"][$value["dept_id"]]["now"][$value["charges"]]["offset"] = $order["dept"][$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] - $uv["Month"]["day_schedule"];

            $order["dept"]["all"]["now"]["real_fen_count"] += $value["real_fen_count"];
            $order["dept"]["all"]["now"]["fen_schedule"] = round($order["dept"]["all"]["now"]["real_fen_count"]/$order["dept"]["all"]["now"]["all"],4)*100;
            $order["dept"]["all"]["now"]["offset"] = round($order["dept"]["all"]["now"]["fen_schedule"] - $uv["Month"]["day_schedule"],2);
        }


        //上月付费免费分单量统计
        $result = D("MarketSummary")->getCenterMonthChannelOrder(date("Y-m-d",$beforeBegin),date("Y-m-d",$beforeEnd));

        foreach ($result as $key => $value) {
            $order["dept"][$value["dept_id"]]["before"][$value["charges"]]["real_fen_count"] = $value["real_fen_count"];
            $order["dept"]["all"]["before"]["real_fen_count"] += $value["real_fen_count"];
        }

        //实际分单转化率
        $result = D("MarketSummary")->getCenterMonthChannelOrderByGroup($begin,$end);
        $order["conversion"]["now"]["conversion_rate"] = round($result["real_fen_count"]/$result["uv"],5)*100;
        $order["conversion"]["now"]["conversion_schedule"] = round($order["conversion"]["now"]["conversion_rate"]/$order["conversion"]["now"]["conversion_target_rate"],4)*100;

        //上月4大渠道完成量
        $result = D("MarketSummary")->getCenterMonthChannelOrderByGroup( date("Y-m-d",$beforeBegin), date("Y-m-d",$beforeEnd));
        $order["conversion"]["before"]["conversion_rate"] = round($result["real_fen_count"]/$result["uv"],5)*100;
        return array("order"=>$order,"uv"=>$uv);
    }

    /**
     * 财年概览
     * @param  [string] $year [年份]
     * @return array
     */
    private function getCenterYearStat($year)
    {
        if (empty($year)) {
            $year = date("Y");
        }

        $startMonth = date("Y-m-d", mktime(0,0,0,2,1,$year));
        $endMonth =date("Y-m-d", mktime(0,0,0,2,1,$year+1));
        $nowMonth = date("m" ,strtotime($year));
        $time = strtotime($year);

        //时间刻度
        $timeScale = array("02"=>"1","03"=>"2","04"=>"3","05"=>"4","06"=>"5","07"=>"6","08"=>"7","09"=>"8","10"=>"9","11"=>"10","12"=>"11","01"=>"12");

        //时间进度
        $month_schedule = round($timeScale[$nowMonth]/12,4)*100;

        //获取财年
        for ($i = 2017; $i <= date("Y") ; $i++) {
            $years[] = $i;
        }

        //财年分单
        //获取财年的月目标数据
        $result = D("MarketYearPlan")->getYearPlanByPlanYear($year);
        foreach ($result as $key => $value) {
            if ($value["plan_month"] <= date("Y-m",$time)) {
                $month[] = date("m月", strtotime($value["plan_month"]));
                $order["target"][] = $value["shiji_fendan_zongliang"];
                $order["list"][$value["plan_month"]]["target"] = $value["shiji_fendan_zongliang"];

                $uv["target"][] = $value["ziran_liuliang"];
                $uv["list"][$value["plan_month"]]["target"] = $value["ziran_liuliang"];

                $conversion["target"][] = $value["shiji_fendan_zhuanhuanlv"]*100;
                $conversion["list"][$value["plan_month"]]["target"] = $value["shiji_fendan_zhuanhuanlv"]*100;
            }
        }

        //获取2017年6月之前的数据
        if ($year == "2017") {
            $result = D("MarketSummary")->getBeforeData();

            for ($i = 0; $i < 5; $i++) {
                $m = date("Y-m", strtotime("+$i month", strtotime($startMonth)));
                $data[$m]["real_fen_count"] += $result["mianfei"][$m] + $result["fufei"][$m];
                $data[$m]["normal_uv"] += $result["uv"][$m];
                $data[$m]["conversion"] = round(($result["turn_rate_order"][$m]/$result["turn_rate_uv"][$m])*100,2);
            }
        }

        //获取财年数据
        $result = D("MarketSummary")->getCenterYearData($startMonth,$endMonth);
        foreach ($result as $key => $value) {
            $data[$value["time"]]["real_fen_count"] = $value["real_fen_count"];
            $data[$value["time"]]["uv"] = $value["uv"];
            $data[$value["time"]]["normal_uv"] = $value["normal_uv"];
        }

        //实际分单转化率
        $result = D("MarketSummary")->getChannelOrderByGroup($startMonth,$endMonth);

        foreach ($result as $key => $value) {
            $data[$value["date"]]["conversion"] = round($value["real_fen_count"]/$value["uv"],5)*100;
        }

        //添加数据到每个月
        $length = 12;
        if ($nowMonth != "2") {
            $length = $timeScale[date("m",$time)];
        }


        for ($i = 0; $i < $length; $i++) {
            $m = date("Y-m", strtotime("+$i month", strtotime($startMonth)));
            $order['now'][] = empty($data[$m]["real_fen_count"])?0:$data[$m]["real_fen_count"];
            $order["list"][$m]["now"] = empty($data[$m]["real_fen_count"])?0:$data[$m]["real_fen_count"];

            $uv["now"][] = empty($data[$m]["normal_uv"])?0:$data[$m]["normal_uv"];
            $uv["list"][$m]["now"] = empty($data[$m]["normal_uv"])?0:$data[$m]["normal_uv"];

            $conversion["now"][] = empty($data[$m]["conversion"])?0:$data[$m]["conversion"];
            $conversion["list"][$m]["now"] =  empty($data[$m]["conversion"])?0:$data[$m]["conversion"];
        }

        //累计值
        for ($i = 0; $i < $length; $i++) {
            $m = date("Y-m", strtotime("+$i month", strtotime($startMonth)));
            $mm = date("m", strtotime("+$i month", strtotime($startMonth)));

            for ($j = 0; $j < $length; $j++) {
                $m_sub =  date("Y-m", strtotime("+$j month", strtotime($startMonth)));
                if ($m_sub <= $m) {
                   $order["list"][$m]["now_all"] += $order["list"][$m_sub]["now"];
                   $order["list"][$m]["target_all"] += $order["list"][$m_sub]["target"];

                   $uv["list"][$m]["now_all"] += $uv["list"][$m_sub]["now"];
                   $uv["list"][$m]["target_all"] += $uv["list"][$m_sub]["target"];

                   $conversion["list"][$m]["now_all"] += $conversion["list"][$m_sub]["now"];
                   $conversion["list"][$m]["target_all"] += $conversion["list"][$m_sub]["target"];
                }
            }


            $order["list"][$m]["month_schedule"] =  round($timeScale[$mm]/12,4)*100;
            $order["list"][$m]["schedule"] = round(($order["list"][$m]["now_all"]/$order["list"][$m]["target_all"])*$order["list"][$m]["month_schedule"]/100,4)*100;
            $order["list"][$m]["year_schedule"] = round($order["list"][$m]["schedule"] - $order["list"][$m]["month_schedule"],2);

            $uv["list"][$m]["month_schedule"] =  round($timeScale[$mm]/12,4)*100;
            $uv["list"][$m]["schedule"] = round(($uv["list"][$m]["now_all"]/$uv["list"][$m]["target_all"])*$uv["list"][$m]["month_schedule"]/100,4)*100;
            $uv["list"][$m]["year_schedule"] =  round($uv["list"][$m]["schedule"] - $uv["list"][$m]["month_schedule"],2);

            $conversion["list"][$m]["now_all"] = round($conversion["list"][$m]["now_all"]/$timeScale[$mm],2);
            $conversion["list"][$m]["target_all"] = round($conversion["list"][$m]["target_all"]/$timeScale[$mm],2);

            $conversion["list"][$m]["month_schedule"] =  round($timeScale[$mm]/12,4)*100;
            $conversion["list"][$m]["schedule"] =  round(($conversion["list"][$m]["now_all"]/$conversion["list"][$m]["target_all"])*$conversion["list"][$m]["month_schedule"]/100,4)*100;
            $conversion["list"][$m]["year_schedule"] = round($conversion["list"][$m]["schedule"] - $conversion["list"][$m]["month_schedule"],2);
        }

        return array("year"=>$years,"month"=>$month,"order"=>$order,"uv"=>$uv,"conversion"=>$conversion);
    }

    /**
     * 运营中心-数据详情
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    private function getCenterDetails($begin,$end)
    {
        $time = strtotime("-1 day");
        $startMonth = date("Y-m-d", mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $endMonth =date("Y-m-d");

        if (!empty($begin) && !empty($begin)) {
            $startMonth = date("Y-m-d", strtotime($begin));
            $endMonth =date("Y-m-d", strtotime($end)+86400);
        }

        //获取渠道数据
        $result = D("MarketSummary")->getCenterChannelOrder($startMonth,$endMonth);

        foreach ($result as $key => $value) {
            //分单
            $order["part1"]["fen"][$value["dept_id"]][$value["charges"]]["count"] += $value["order_fen_count"];
            $order["part2"]["fen"][$value["dept_id"]][$value["time"]][$value["charges"]]["count"] += $value["order_fen_count"];
            //发单
            $order["part1"]["order"][$value["dept_id"]][$value["charges"]]["count"] += $value["order_count"];
            $order["part2"]["order"][$value["dept_id"]][$value["time"]][$value["charges"]]["count"] += $value["order_count"];

            //实际分单
            $order["part1"]["real"][$value["dept_id"]][$value["charges"]]["count"] += $value["real_fen_count"];
            $order["part2"]["real"][$value["dept_id"]][$value["time"]][$value["charges"]]["count"] += $value["real_fen_count"];

            //uv
            $order["part1"]["uv"][$value["dept_id"]][$value["charges"]]["count"] += $value["uv"];
            $order["part2"]["uv"][$value["dept_id"]][$value["time"]][$value["charges"]]["count"] += $value["uv"];

            //列表
            $order["list"][$value["source_name"]]["name"] = $value["source_name"];
            $order["list"][$value["source_name"]]["uv"] += $value["uv"];
            $order["list"][$value["source_name"]]["order_count"] += $value["order_count"];
            $order["list"][$value["source_name"]]["order_fen_count"] += $value["order_fen_count"];
            $order["list"][$value["source_name"]]["real_fen_count"] += $value["real_fen_count"];
            $order["list"][$value["source_name"]]["turn_rate"] = round($order["list"][$value["source_name"]]["order_count"] / $order["list"][$value["source_name"]]["uv"],5)*100;
            $order["list"][$value["source_name"]]["fen_rate"] = round($order["list"][$value["source_name"]]["order_fen_count"] / $order["list"][$value["source_name"]]["order_count"],5)*100;
            $order["list"][$value["source_name"]]["real_fen_rete"] = round($order["list"][$value["source_name"]]["real_fen_count"] / $order["list"][$value["source_name"]]["order_count"],5)*100;

            //汇总
            $order["all"]["uv"] += $value["uv"];
            $order["all"]["order_count"] += $value["order_count"];
            $order["all"]["order_fen_count"] += $value["order_fen_count"];
            $order["all"]["real_fen_count"] += $value["real_fen_count"];
            $order["all"]["turn_rate"] = round($order["all"]["order_count"] / $order["all"]["uv"],5)*100;
            $order["all"]["fen_rate"] = round($order["all"]["order_fen_count"] / $order["all"]["order_count"],5)*100;
            $order["all"]["real_fen_rete"] = round($order["all"]["real_fen_count"] / $order["all"]["order_count"],5)*100;

        }

        //添加数据到每个月
        $length = (strtotime($endMonth) - strtotime($startMonth) )/86400;
        for ($i = 0; $i < $length; $i++) {
            $day = date("Y-m-d", strtotime("+$i day", strtotime($startMonth)));
            $month[] = date("d日", strtotime("+$i day", strtotime($startMonth)));

            //分单
            if (!array_key_exists($day, $order["part2"]["fen"][1])) {
               $order["char2"]["fen"][1][1][] = 0;
               $order["char2"]["fen"][1][2][] = 0;
            } else {
               $order["char2"]["fen"][1][1][] = $order["part2"]["fen"][1][$day][1]["count"];
               $order["char2"]["fen"][1][2][] = $order["part2"]["fen"][1][$day][2]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["fen"][2])) {
                $order["char2"]["fen"][2][1][] = 0;
            } else {
                $order["char2"]["fen"][2][1][] = $order["part2"]["fen"][2][$day][1]["count"];
            }

            //实际分单
            if (!array_key_exists($day, $order["part2"]["real"][1])) {
               $order["char2"]["real"][1][1][] = 0;
               $order["char2"]["real"][1][2][] = 0;
            } else {
               $order["char2"]["real"][1][1][] = $order["part2"]["real"][1][$day][1]["count"];
               $order["char2"]["real"][1][2][] = $order["part2"]["real"][1][$day][2]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["real"][2])) {
                $order["char2"]["real"][2][1][] = 0;
            } else {
                $order["char2"]["real"][2][1][] = $order["part2"]["real"][2][$day][1]["count"];
            }


            //发单
            if (!array_key_exists($day, $order["part2"]["order"][1])) {
               $order["char2"]["order"][1][1][] = 0;
               $order["char2"]["order"][1][2][] = 0;
            } else {
               $order["char2"]["order"][1][1][] = $order["part2"]["order"][1][$day][1]["count"];
               $order["char2"]["order"][1][2][] = $order["part2"]["order"][1][$day][2]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["order"][2])) {
                $order["char2"]["order"][2][1][] = 0;
            } else {
                $order["char2"]["order"][2][1][] = $order["part2"]["order"][2][$day][1]["count"];
            }

            //uv
            if (!array_key_exists($day, $order["part2"]["uv"][1])) {
               $order["char2"]["uv"][1][1][] = 0;
               $order["char2"]["uv"][1][2][] = 0;
            } else {
               $order["char2"]["uv"][1][1][] = $order["part2"]["uv"][1][$day][1]["count"];
               $order["char2"]["uv"][1][2][] = $order["part2"]["uv"][1][$day][2]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["uv"][2])) {
                $order["char2"]["uv"][2][1][] = 0;
            } else {
                $order["char2"]["uv"][2][1][] = $order["part2"]["uv"][2][$day][1]["count"];
            }
        }

        return array("month" => $month,"order" => $order);
    }

    /**
     * 获取部门月概览数据
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    private function getDepMonthStat($date)
    {
        if (empty($date)) {
            $date = date("Y-m");
        }
        $time = strtotime($date);
        $begin = date("Y-m-d" , mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $end = date("Y-m-d" ,strtotime("+1 day",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time))));
        $day = date("d" ,$end);

        if (date("m" ,$time) == date("m") && date("d" ,$time) != date("t",$time)) {
            $day = date("d" ,strtotime("-1 day"));
        }

        //获取当月的目标量
        $month = date("Y-m",$time);
        $result = D("MarketYearPlan")->getYearPlanByPlanMonth($month);

        $list[1]["target"][1]["mianfei"] = 0;
        $list[1]["target"][2]["fufei"] = $result["tuiguangbu_fufei_shiji_fendan"];
        $list[9]["target"][1]["mianfei"] = $result["tuiguangbu_mianfei_shiji_fendan"];
        // $list[7]["target"][1]["mianfei"] = $result["tuiguangbu_mianfei_meiti_shiji_fendan"];
        $list["all"]["target"]["all"] += $result["tuiguangbu_fufei_shiji_fendan"] + $result["tuiguangbu_mianfei_shiji_fendan"];

        //获取视频、自媒体组月目标
        $monthPlan = D("MarketTuiguangMonthPlan")->getMonthPlanByPlanMonth($date);
        $list["mianfei"][7]["target"] = $monthPlan["shipinzu_shiji_fendan"];
        $list["mianfei"][6]["target"] = $monthPlan["zimeitizu_shiji_fendan"];

        //获取订单数据
        $result = D("MarketSummary")->getDeptMonthOrder(array(1,6,7),$begin,$end);

        foreach ($result as $key => $value) {
            //免费、付费数据
            $order[$value["charges"]]["order_fen_count"] += $value["order_fen_count"];
            $order[$value["charges"]]["order_count"] += $value["order_count"];
            $order[$value["charges"]]["real_fen_count"] += $value["real_fen_count"];
            $order[$value["charges"]]["fen_rate"] = round($order[$value["charges"]]["order_fen_count"]/$order[$value["charges"]]["order_count"],4)*100;
            $order[$value["charges"]]["real_fen_rate"] = round($order[$value["charges"]]["order_fen_count"]/$order[$value["charges"]]["order_count"],4)*100;

            $order["all"]["order_fen_count"] += $value["order_fen_count"];
            $order["all"]["order_count"] += $value["order_count"];
            $order["all"]["real_fen_count"] += $value["real_fen_count"];
            $order["all"]["fen_rate"] = round($order["all"]["order_fen_count"]/$order["all"]["order_count"],4)*100;
            $order["all"]["real_fen_rate"] = round($order["all"]["real_fen_count"]/$order["all"]["order_count"],4)*100;

            //合并免费数据
            if ($value["charges"] == 1) {
                $list["mianfei"][$value["dept_id"]]["real_fen_count"] += $value["real_fen_count"];
                $list["mianfei"][$value["dept_id"]]["fen_schedule"] =  round( $list["mianfei"][$value["dept_id"]]["real_fen_count"]/ $list["mianfei"][$value["dept_id"]]["target"],4)*100;
                //时间进度
                $list["mianfei"][$value["dept_id"]]["day_schedule"] = round($day/date("t",$time),4)*100;
                $list["mianfei"][$value["dept_id"]]["offset"] =  round($list["mianfei"][$value["dept_id"]]["fen_schedule"] - $list["mianfei"][$value["dept_id"]]["day_schedule"] ,2);

                $value["dept_id"] = 9;
            }

            //分单量
            $list[$value["dept_id"]]["now"][$value["charges"]]["real_fen_count"] += $value["real_fen_count"];

            //分单完成进度
            if ($value["charges"] == 1) {
                $list[$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] = round( $list[$value["dept_id"]]["now"][$value["charges"]]["real_fen_count"]/$list[$value["dept_id"]]["target"][$value["charges"]]["mianfei"],4)*100;
            } else {
                $list[$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] = round($list[$value["dept_id"]]["now"][$value["charges"]]["real_fen_count"]/$list[$value["dept_id"]]["target"][$value["charges"]]["fufei"],4)*100;
            }

            //时间进度
            $list[$value["dept_id"]]["now"][$value["charges"]]["day_schedule"] = round($day/date("t",$time),4)*100;
            //进度差
            $list[$value["dept_id"]]["now"][$value["charges"]]["offset"] =  round($list[$value["dept_id"]]["now"][$value["charges"]]["fen_schedule"] - $list[$value["dept_id"]]["now"][$value["charges"]]["day_schedule"],2);

            $list["all"]["now"]["real_fen_count"] += $value["real_fen_count"];
            $list["all"]["now"]["fen_schedule"] = round($list["all"]["now"]["real_fen_count"]/$list["all"]["target"]["all"],4)*100;

            $list["all"]["now"]["day_schedule"] = round($day/date("t",$time),4)*100;

            $list["all"]["now"]["offset"] = round($list["all"]["now"]["fen_schedule"] - $list["all"]["now"]["day_schedule"],2);


        }

        //获取上个月订单数据
        $beforeBegin = date("Y-m-d", strtotime("-1 month",strtotime($begin)));
        $end = strtotime($beforeBegin);
        $beforeEnd = date("Y-m-d",mktime(0,0,0,date("m",$end),date("t",$end),date("Y",$end))+86400);

        $result = D("MarketSummary")->getDeptMonthOrder(array(1,6,7),$beforeBegin,$beforeEnd);
        foreach ($result as $key => $value) {
            //合并免费数据
            if ($value["charges"] == 1) {
                $list["mianfei"][$value["dept_id"]]["before"]["real_fen_count"] += $value["real_fen_count"];
                $value["dept_id"] = 9;
            }
            $list[$value["dept_id"]]["before"][$value["charges"]]["real_fen_count"] += $value["real_fen_count"];
            $list["all"]["target"]['real_fen_count'] +=  $value["real_fen_count"];
        }

        return array("order"=>$order,"list"=>$list);
    }

    /**
     * 部门财年概览
     * @param  [date] $year [年份]
     */
    private function getDeptYearStat($year)
    {
        if (empty($year)) {
            $year = date("Y");
        }

        $startMonth = date("Y-m-d", mktime(0,0,0,2,1,$year));
        $endMonth =date("Y-m-d", mktime(0,0,0,2,1,$year+1));
        $nowMonth = date("m" ,strtotime($year));
        $time = strtotime($year);
        //时间刻度
        $timeScale = array("02"=>"1","03"=>"2","04"=>"3","05"=>"4","06"=>"5","07"=>"6","08"=>"7","09"=>"8","10"=>"9","11"=>"10","12"=>"11","01"=>"12");

        //时间进度
        $month_schedule = round($timeScale[$nowMonth]/12,4)*100;

        //获取财年
        for ($i = 2017; $i <= date("Y") ; $i++) {
            $years[] = $i;
        }

        $length = 12;
        if ($nowMonth != "2") {
            $length = $timeScale[date("m",$time)];
        }


        //财年分单
        //获取财年的月目标数据
        $result = D("MarketYearPlan")->getYearPlanByPlanYear($year);
        foreach ($result as $key => $value) {
            if ($value["plan_month"] <= date("Y-m",$time)) {
                $month[] = date("m月", strtotime($value["plan_month"]));
                $order["target"]["fen"][]  = $value["tuiguangbu_fufei_shiji_fendan"] + $value["tuiguangbu_mianfei_shiji_fendan"];
                $order["target"]["fufei"][]  = $value["tuiguangbu_fufei_shiji_fendan"];
                $order["target"]["mianfei"][]  = $value["tuiguangbu_mianfei_shiji_fendan"];


                $order["list"][$value["plan_month"]]["target"] =  $value["tuiguangbu_fufei_shiji_fendan"] + $value["tuiguangbu_mianfei_shiji_fendan"];
                $order["mianfei"][$value["plan_month"]]["target"] = $value["tuiguangbu_mianfei_shiji_fendan"];
                $order["fufei"][$value["plan_month"]]["target"] =  $value["tuiguangbu_fufei_shiji_fendan"] ;

                for ($j = 0; $j < $length; $j++) {
                    $m_sub =  date("Y-m", strtotime("+$j month", strtotime($startMonth)));
                    if ($m_sub <= $value["plan_month"]) {
                        $order["list"][$value["plan_month"]]["target_all"] += $order["list"][$m_sub]["target"];
                        $order["fufei"][$value["plan_month"]]["target_all"] += $order["fufei"][$m_sub]["target"];
                        $order["mianfei"][$value["plan_month"]]["target_all"] += $order["mianfei"][$m_sub]["target"];
                    }
                }
            }

        }

        //获取2017年6月之前的数据
        if ($year == "2017") {
            $result = D("MarketSummary")->getBeforeData("tgb");

            for ($i = 0; $i < 5; $i++) {
                $m = date("Y-m", strtotime("+$i month", strtotime($startMonth)));
                $data[$m]["fen"] += $result["fufei"][$m] + $result["mianfei"][$m];
                $data[$m]["mianfei"] += $result["mianfei"][$m];
                $data[$m]["fufei"] = $result["fufei"][$m];
            }
        }

        //获取财年数据
        $result = D("MarketSummary")->getDeptYearData($startMonth,$endMonth,array(1,6,7));

        foreach ($result as $key => $value) {
            $data[$value["time"]]["fen"] += $value["real_fen_count"];
            if ($value['charges'] ==  2) {
                $data[$value["time"]]["fufei"] = $value["real_fen_count"];
            } else {
                $data[$value["time"]]["mianfei"] = $value["real_fen_count"];
            }
        }

        for ($i = 0; $i < $length; $i++) {
            $m = date("Y-m", strtotime("+$i month", strtotime($startMonth)));
            $mm = date("m", strtotime("+$i month", strtotime($startMonth)));
            $order['now']["fen"][] = ($data[$m]["mianfei"] + $data[$m]["fufei"]) == 0?0:$data[$m]["mianfei"] + $data[$m]["fufei"];

            $order['now']["mianfei"][] = empty($data[$m]["mianfei"])?0:$data[$m]["mianfei"];
            $order['now']["fufei"][] = empty($data[$m]["fufei"])?0:$data[$m]["fufei"];

            $order["list"][$m]["now"] = $data[$m]["mianfei"] + $data[$m]["fufei"];
            $order["fufei"][$m]["now"] =  $data[$m]["fufei"];

            $order["mianfei"][$m]["now"] = $data[$m]["mianfei"];

            for ($j = 0; $j < $length; $j++) {
                $m_sub =  date("Y-m", strtotime("+$j month", strtotime($startMonth)));
                if ($m_sub <= $m) {
                    $order["list"][$m]["now_all"] += $order["list"][$m_sub]["now"];
                    $order["fufei"][$m]["now_all"] +=  $order["fufei"][$m_sub]["now"];
                    $order["mianfei"][$m]["now_all"] += $order["mianfei"][$m_sub]["now"];
                }
            }

            //分单
            $order["list"][$m]["month_schedule"] = round($timeScale[$mm]/12,4)*100;
            $order["list"][$m]["schedule"] = round(($order["list"][$m]["now_all"]/$order["list"][$m]["target_all"])*$order["list"][$m]["month_schedule"]/100,4)*100;
            $order["list"][$m]["year_schedule"] = $order["list"][$m]["schedule"] - $order["list"][$m]["month_schedule"];
            $order["list"][$m]["schedule"] = round(($order["list"][$m]["now_all"]/$order["list"][$m]["target_all"])*$month_schedule/100,4)*100;
            $order["list"][$m]["year_schedule"] = round($order["list"][$m]["schedule"] - $order["list"][$m]["month_schedule"],2);

            //付费
            $order["fufei"][$m]["month_schedule"] = round($timeScale[$mm]/12,4)*100;
            $order["fufei"][$m]["schedule"] = round(($order["fufei"][$m]["now_all"]/$order["fufei"][$m]["target_all"])*$order["fufei"][$m]["month_schedule"]/100,4)*100;
            $order["fufei"][$m]["year_schedule"] = round($order["fufei"][$m]["schedule"] - $order["list"][$m]["month_schedule"],2);

            //免费
            $order["mianfei"][$m]["month_schedule"] = round($timeScale[$mm]/12,4)*100;
            $order["mianfei"][$m]["schedule"] = round(($order["mianfei"][$m]["now_all"]/$order["mianfei"][$m]["target_all"])* $order["mianfei"][$m]["month_schedule"]/100,4)*100;
            $order["mianfei"][$m]["year_schedule"] = round($order["mianfei"][$m]["schedule"] - $order["mianfei"][$m]["month_schedule"],2);


        }
        return array("year"=>$years,"month"=>$month,"order"=>$order);
    }

    /**
     * 获取部门数据详情
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @param  [int] $state [渠道]
     * @param  [int] $source[来源]
     * @return array
     */
    private function getDeptDetails($begin,$end,$state,$source)
    {
        $time = strtotime("-1 day");
        $startMonth = date("Y-m-d", mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $endMonth = date("Y-m-d");

        if (!empty($begin) && !empty($begin)) {
            $startMonth = date("Y-m-d", strtotime($begin));
            $endMonth =date("Y-m-d", strtotime($end)+86400);
        }

        //获取渠道数据
        $result = D("MarketSummary")->getDeptChannelOrder($startMonth,$endMonth,$state,$source);

        foreach ($result as $key => $value) {
            $order["part1"]["order"][$value["dept_id"]]["count"] += $value["order_count"];
            $order["part1"]["real"][$value["dept_id"]]["count"] += $value["real_fen_count"];
            $order["part1"]["fen"][$value["dept_id"]]["count"] += $value["order_fen_count"];
            $order["part1"]["uv"][$value["dept_id"]]["count"] += $value["uv"];

            $order["part2"]["order"][$value["dept_id"]][$value["time"]]["count"] += $value["order_count"];
            $order["part2"]["real"][$value["dept_id"]][$value["time"]]["count"] += $value["real_fen_count"];
            $order["part2"]["fen"][$value["dept_id"]][$value["time"]]["count"] += $value["order_fen_count"];
            $order["part2"]["uv"][$value["dept_id"]][$value["time"]]["count"] += $value["uv"];

            $order["list"][$value["source_name"]]["source_id"] = $value["source_id"];
            $order["list"][$value["source_name"]]["name"] = $value["source_name"];
            $order["list"][$value["source_name"]]["uv"] += $value["uv"];
            $order["list"][$value["source_name"]]["order_count"] += $value["order_count"];
            $order["list"][$value["source_name"]]["real_fen_count"] += $value["real_fen_count"];
            $order["list"][$value["source_name"]]["order_fen_count"] += $value["order_fen_count"];
            $order["list"][$value["source_name"]]["turn_rate"] = round($order["list"][$value["source_name"]]["order_count"]/$order["list"][$value["source_name"]]["uv"],4)*100;
            $order["list"][$value["source_name"]]["fen_rate"] = round($order["list"][$value["source_name"]]["order_fen_count"]/$order["list"][$value["source_name"]]["order_count"],4)*100;
            $order["list"][$value["source_name"]]["real_fen_rete"] = round($order["list"][$value["source_name"]]["real_fen_count"]/$order["list"][$value["source_name"]]["order_count"],4)*100;

            $order["all"]["uv"] += $value["uv"];
            $order["all"]["order_count"] += $value["order_count"];
            $order["all"]["real_fen_count"] += $value["real_fen_count"];
            $order["all"]["order_fen_count"] += $value["order_fen_count"];
            $order["all"]["turn_rate"] = round($order["all"]["order_count"]/$order["all"]["uv"],4)*100;
            $order["all"]["fen_rate"] = round($order["all"]["order_fen_count"]/$order["all"]["order_count"],4)*100;
            $order["all"]["real_fen_rete"] = round($order["all"]["real_fen_count"]/$order["all"]["order_count"],4)*100;
        }

        //添加数据到每个月
        $length = (strtotime($endMonth) - strtotime($startMonth) )/86400;
        for ($i = 0; $i < $length; $i++) {
            $day = date("Y-m-d", strtotime("+$i day", strtotime($startMonth)));
            $month[] = date("d日", strtotime("+$i day", strtotime($startMonth)));

            //发单
            if (!array_key_exists($day, $order["part2"]["order"][1])) {
               $order["char2"]["order"][1][] = 0;
            } else {
               $order["char2"]["order"][1][] = $order["part2"]["order"][1][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["order"][6])) {
               $order["char2"]["order"][6][] = 0;
            } else {
               $order["char2"]["order"][6][] = $order["part2"]["order"][6][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["order"][7])) {
               $order["char2"]["order"][7][] = 0;
            } else {
               $order["char2"]["order"][7][] = $order["part2"]["order"][7][$day]["count"];
            }

            //分单
            if (!array_key_exists($day, $order["part2"]["fen"][1])) {
               $order["char2"]["fen"][1][] = 0;
            } else {
               $order["char2"]["fen"][1][] = $order["part2"]["fen"][1][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["fen"][6])) {
               $order["char2"]["fen"][6][] = 0;
            } else {
               $order["char2"]["fen"][6][] = $order["part2"]["fen"][6][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["fen"][7])) {
               $order["char2"]["fen"][7][] = 0;
            } else {
               $order["char2"]["fen"][7][] = $order["part2"]["fen"][7][$day]["count"];
            }

            //实际分单
            if (!array_key_exists($day, $order["part2"]["real"][1])) {
               $order["char2"]["real"][1][] = 0;
            } else {
               $order["char2"]["real"][1][] = $order["part2"]["real"][1][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["real"][6])) {
               $order["char2"]["real"][6][] = 0;
            } else {
               $order["char2"]["real"][6][] = $order["part2"]["real"][6][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["real"][7])) {
               $order["char2"]["real"][7][] = 0;
            } else {
               $order["char2"]["real"][7][] = $order["part2"]["real"][7][$day]["count"];
            }

            //uv
            if (!array_key_exists($day, $order["part2"]["uv"][1])) {
               $order["char2"]["uv"][1][] = 0;
            } else {
               $order["char2"]["uv"][1][] = $order["part2"]["uv"][1][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["uv"][6])) {
               $order["char2"]["uv"][6][] = 0;
            } else {
               $order["char2"]["uv"][6][] = $order["part2"]["uv"][6][$day]["count"];
            }

            if (!array_key_exists($day, $order["part2"]["uv"][7])) {
               $order["char2"]["uv"][7][] = 0;
            } else {
               $order["char2"]["uv"][7][] = $order["part2"]["uv"][7][$day]["count"];
            }
        }

        return array("month" => $month,"order" => $order);
    }

    /**
     * 订单详细信息
     * @param  [int] $source_id [来源ID]
     * @param  [date] $begin     [开始时间]
     * @param  [date] $end       [结束时间]
     * @return array
     */
    public function getOrderDetailsList($source_id,$begin,$end)
    {
        $time = strtotime("-1 day");
        $startMonth = mktime(0,0,0,date("m",$time),1,date("Y",$time));
        // $endMonth = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;
        $endMonth = strtotime(date("Y-m-d"))-1;

        if (!empty($begin) && !empty($begin)) {
            $startMonth = strtotime($begin);
            $endMonth = strtotime($end)+86400;
        }

        $count = D("MarketSummary")->getOrderDetailsListCount($source_id,$startMonth,$endMonth);

        if ($count[0]["count"] > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count[0]["count"],50);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $result = D("MarketSummary")->getOrderDetailsList($source_id,$startMonth,$endMonth,$p->firstRow,$p->listRows);
        }
        return array("list"=>$result,"page"=>$show);
    }

    /**
     * 各部门业绩统计---推广部
     */
    public function gbmyjtjtgb(){
        $year = I('get.year');
        if (empty($year)) {
            $year = date('Y');
        }

        $start = $start_time = strtotime($year . '-02-01 00:00:00');
        $end = $end_time   = strtotime(($year + 1) . '-01-31 23:59:59');
        if ($end_time > time()) {
            $end_time = strtotime(date('Y-m-01') . ' +1 month') - 1;
        }

        //计算本财年有多少天和已经过去了多少天
        $year_days = date('L', $year) == 1 ? 365 : 366;
        $past_days = ceil(($end_time - $start) / 86400);

        //记录时间
        $time = array();
        while ($start < $end) {
            $time[date('Y-m', $start)] = date('Y-m', $start);
            $start                     = strtotime(date('Y-m-01', $start) . ' +1 month');
        }

        //查询结果
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        //自然流量UV,分单量,免费分单,付费分单
        $ff = $mf = array();
        foreach ($result as $key => $value) {
            if (in_array($value['dept_id'], array(1, 6, 7))) {
                $month = date('Y-m', strtotime($value['time']));
                //判断逻辑经讨论与函数getDeptYearStat相同
                if ($value['charge'] ==  2) {
                    //推广部付费实际分单量
                    $ff[$month] = $ff[$month] + $value['real_fen_count'];
                } else {
                    //推广部免费实际分单量
                    $mf[$month] = $mf[$month] + $value['real_fen_count'];
                }
            }
        }

        //20170724-获取以前的数据,进行覆盖
        $before = D('MarketSummary')->getBeforeData('tgb');
        foreach ($time as $key => $value) {
            if (!empty($before['mianfei'][$key])) {
                $mf[$key] = $before['mianfei'][$key];
            }
            if (!empty($before['fufei'][$key])) {
                $ff[$key] = $before['fufei'][$key];
            }
        }

        //获取财年计划
        $summary = $plan = array();
        $result = D('MarketYearPlan')->getYearPlanByPlanYear($year);
        foreach ($result as $key => $value) {
            $plan[$value['plan_month']] = $value;
            if (strtotime($value['plan_month']) < time()) {
                $summary['mf'] = $summary['mf'] + $value['tuiguangbu_mianfei_shiji_fendan'];
                $summary['ff'] = $summary['ff'] + $value['tuiguangbu_fufei_shiji_fendan'];
            }
        }

        $list_mf = $list_ff = array();
        foreach ($time as $key => $value) {
            //免费实际分单量
            $wc            = $mf[$key];
            $wc_add        = $wc + end($list_mf)['wc_add'];
            $wcl           = number_format($mf[$key] / $plan[$key]['tuiguangbu_mianfei_shiji_fendan'] * 100, 2);
            $days          = date('t', strtotime($key));
            $days_add      = $days + end($list_mf)['days_add'];
            $plan_mf       = $plan[$key]['tuiguangbu_mianfei_shiji_fendan'];
            $plan_mf_add   = $plan[$key]['tuiguangbu_mianfei_shiji_fendan'] + end($list_mf)['plan_order_add'];
            $days_rate     = number_format($days / $year_days * 100, 2);
            $days_progress = number_format($days_add / $year_days * 100, 2);
            $wc_progress   = number_format($wc_add / $summary['mf'] * 100, 2);
            $overflow      = number_format($wc_progress - $days_progress, 2);
            $list_mf[$key] = array(
                'wc'            => $wc,
                'wc_add'        => $wc_add,
                'wcl'           => $wcl,
                'days'          => $days,
                'days_add'      => $days_add,
                'plan_mf'       => $plan_mf,
                'plan_mf_add'   => $plan_mf_add,
                'days_rate'     => $days_rate,
                'days_progress' => $days_progress,
                'wc_progress'   => $wc_progress,
                'overflow'      => $overflow
            );
            $summary['mf_wc'] = $wc_add;

            //付费实际分单量
            $wc            = $ff[$key];
            $wc_add        = $wc + end($list_ff)['wc_add'];
            $wcl           = number_format($ff[$key] / $plan[$key]['tuiguangbu_fufei_shiji_fendan'] * 100, 2);
            $days          = date('t', strtotime($key));
            $days_add      = $days + end($list_ff)['days_add'];
            $plan_ff       = $plan[$key]['tuiguangbu_fufei_shiji_fendan'];
            $plan_ff_add   = $plan[$key]['tuiguangbu_fufei_shiji_fendan'] + end($list_ff)['plan_order_add'];
            $days_rate     = number_format($days / $year_days * 100, 2);
            $days_progress = number_format($days_add / $year_days * 100, 2);
            $wc_progress   = number_format($wc_add / $summary['ff'] * 100, 2);
            $overflow      = number_format($wc_progress - $days_progress, 2);
            $list_ff[$key] = array(
                'wc'            => $wc,
                'wc_add'        => $wc_add,
                'wcl'           => $wcl,
                'days'          => $days,
                'days_add'      => $days_add,
                'plan_ff'       => $plan_ff,
                'plan_ff_add'   => $plan_ff_add,
                'days_rate'     => $days_rate,
                'days_progress' => $days_progress,
                'wc_progress'   => $wc_progress,
                'overflow'      => $overflow
            );
            $summary['ff_wc'] = $wc_add;
        }

        $main = array(
            'year'      => $year,
            'list_mf'   => $list_mf,
            'list_ff'   => $list_ff,
            'time'      => $time,
            'year_days' => $year_days,
            'past_days'  => $past_days,
            'summary'   => $summary
        );

        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 各部门业绩统计---流量部
     */
    public function gbmyjtjllb(){
        $year = I('get.year');
        if (empty($year)) {
            $year = date('Y');
        }

        $start = $start_time = strtotime($year . '-02-01 00:00:00');
        $end = $end_time   = strtotime(($year + 1) . '-01-31 23:59:59');
        if ($end_time > time()) {
            $end_time = strtotime(date('Y-m-01') . ' +1 month') - 1;
        }

        //计算本财年有多少天和已经过去了多少天
        $year_days = date('L', $year) == 1 ? 365 : 366;
        $past_days = ceil(($end_time - $start) / 86400);

        //记录时间
        $time = array();
        while ($start < $end) {
            $time[date('Y-m', $start)] = date('Y-m', $start);
            $start                     = strtotime(date('Y-m-01', $start) . ' +1 month');
        }

        //查询结果
        $result = D('MarketSummary')->getList(date('Y-m-d', $start_time), date('Y-m-d', $end_time));
        //自然流量UV,分单量,免费分单,付费分单
        $uv = $order = array();
        foreach ($result as $key => $value) {
            if ($value['dept_id'] == 2) {
                $month = date('Y-m', strtotime($value['time']));
                //自然流量UV
                $uv[$month]   = $uv[$month] + $value['uv'];
                //分单量
                $order[$month] = $order[$month] + $value['real_fen_count'];
            }
        }

        //20170724-获取以前的数据,进行覆盖
        $before = D('MarketSummary')->getBeforeData('llb');
        foreach ($time as $key => $value) {
            if (!empty($before['uv'][$key])) {
                $uv[$key] = $before['uv'][$key];
            }
            if (!empty($before['mianfei'][$key])) {
                $order[$key] = $before['mianfei'][$key];
            }
        }

        //获取财年计划
        $summary = $plan = array();
        $result = D('MarketYearPlan')->getYearPlanByPlanYear($year);
        foreach ($result as $key => $value) {
            $plan[$value['plan_month']] = $value;
            if (strtotime($value['plan_month']) < time()) {
                $summary['uv'] = $summary['uv'] + $value['ziran_liuliang'];
                $summary['order'] = $summary['order'] + $value['liuliangbu_mianfei_shiji_fendan'];
            }
        }

        $list_order = $list_uv = array();
        foreach ($time as $key => $value) {
            //分单量
            $wc             = $order[$key];
            $wc_add         = $wc + end($list_order)['wc_add'];
            $wcl            = number_format($order[$key] / $plan[$key]['liuliangbu_mianfei_shiji_fendan'] * 100, 2);
            $days           = date('t', strtotime($key));
            $days_add       = $days + end($list_order)['days_add'];
            $plan_order     = $plan[$key]['liuliangbu_mianfei_shiji_fendan'];
            $plan_order_add = $plan[$key]['liuliangbu_mianfei_shiji_fendan'] + end($list_order)['plan_order_add'];
            $days_rate      = number_format($days / $year_days * 100, 2);
            $days_progress  = number_format($days_add / $year_days * 100, 2);
            $wc_progress    = number_format($wc_add / $summary['order'] * 100, 2);
            $overflow       = number_format($wc_progress - $days_progress, 2);
            $list_order[$key] = array(
                'wc'             => $wc,
                'wc_add'         => $wc_add,
                'wcl'            => $wcl,
                'days'           => $days,
                'days_add'       => $days_add,
                'plan_order'     => $plan_order,
                'plan_order_add' => $plan_order_add,
                'days_rate'      => $days_rate,
                'days_progress'  => $days_progress,
                'wc_progress'    => $wc_progress,
                'overflow'       => $overflow
            );
            $summary['order_wc'] = $wc_add;

            //自然流量UV
            $wc            = $uv[$key];
            $wc_add        = $wc + end($list_uv)['wc_add'];
            $wcl           = number_format($uv[$key] / $plan[$key]['ziran_liuliang'] * 100, 2);
            $days          = date('t', strtotime($key));
            $days_add      = $days + end($list_uv)['days_add'];
            $plan_uv       = $plan[$key]['ziran_liuliang'];
            $plan_uv_add   = $plan[$key]['ziran_liuliang'] + end($list_uv)['plan_uv_add'];
            $days_rate     = number_format($days / $year_days * 100, 2);
            $days_progress = number_format($days_add / $year_days * 100, 2);
            $wc_progress   = number_format($wc_add / $summary['uv'] * 100, 2);
            $overflow      = number_format($wc_progress - $days_progress, 2);
            $list_uv[$key] = array(
                'wc'            => $wc,
                'wc_add'        => $wc_add,
                'wcl'           => $wcl,
                'days'          => $days,
                'days_add'      => $days_add,
                'plan_uv'       => $plan_uv,
                'plan_uv_add'   => $plan_uv_add,
                'days_rate'     => $days_rate,
                'days_progress' => $days_progress,
                'wc_progress'   => $wc_progress,
                'overflow'      => $overflow
            );
            $summary['uv_wc'] = $wc_add;
        }

        $main = array(
            'year'       => $year,
            'list_uv'    => $list_uv,
            'list_order' => $list_order,
            'time'       => $time,
            'year_days'  => $year_days,
            'past_days'  => $past_days,
            'summary'    => $summary
        );

        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 每月访客按城市分析
     * @return array
     */
    private function getCityOrderstat($date)
    {
        //获取7月份发单量最高的前50个城市
        $result = D("MarketSummary")->getOrderCity("2017-06-30","2017-07-31");

        foreach ($result as $key => $value) {
            $citys[$value["cid"]]["info"] = $value;
            $cid []= $value["cid"];
        }

        if (empty($date)) {
            $date = date("Y-m");
        }

        $cid = implode(",", $cid);
        $date = strtotime($date);
        $year = date("Y",$date);

        //财年时间
        $timeScale = array("02"=>"1","03"=>"2","04"=>"3","05"=>"4","06"=>"5","07"=>"6","08"=>"7","09"=>"8","10"=>"9","11"=>"10","12"=>"11","01"=>"12");
        $timeScale_flip = array_flip($timeScale);

        $monthStart = date("Y-01-31",$date);
        $monthEnd =  date("Y-m-d", strtotime("+1 Year",strtotime($monthStart)));

        //补全没有的月份数据
        $m = date("m",strtotime("-1 month"));
        for ($i = 1; $i <= $timeScale[$m] ; $i++) {
            if ($i == 12) {
                $year = $year + 1;
            }
            $month[] = $year."/".$timeScale_flip[$i];
        }

        foreach ($citys as $key => $value) {
            foreach ($month as $k => $val) {
               $citys[$key]["date"][$val]["orders"] = 0;
               $citys[$key]["date"][$val]["realCount"] = 0;
            }
        }

        //获取这些城市的财年每月发单量
        $result = D("MarketSummary")->getCityOrder($monthStart, $monthEnd, $cid);
        foreach ($result as $key => $value) {
            if ($value["date"] < date("Y/m")) {
                $citys[$value['cid']]["date"][$value["date"]]["orders"] = $value["count"];
            }
        }

        $monthStart = date("Y-02-01",$date);
        $monthEnd =  date("Y-m-d", strtotime("+1 Year",strtotime($monthStart)));

        //获取这些城市的财年每月实际分单量
        $result = D("MarketSummary")->getCityRealOrder($monthStart, $monthEnd, $cid);
        foreach ($result as $key => $value) {
            if ($value["date"] < date("Y/m")) {
                $citys[$value['cid']]["date"][$value["date"]]["realCount"] = $value["count"];
                $citys[$value['cid']]["date"][$value["date"]]["rate"] = round($value["count"]/ $citys[$value['cid']]["date"][$value["date"]]["orders"],2)*100;
            }
        }

        foreach ($citys as $key => $value) {
            $count = count($value['date']);
            foreach ($value['date'] as $k => $val) {
                $citys[$key]["all"] += $val["orders"];
                $citys[$key]["fen_all"] += $val["realCount"];
            }
            $citys[$key]["avg"] = round( $citys[$key]["all"]/$count,2);
            $citys[$key]["fen_avg"] = round( $citys[$key]["fen_all"]/$count,2);
            $citys[$key]["rate_avg"] = round( $citys[$key]["fen_all"]/$citys[$key]["all"],2)*100;
        }
        return $citys;
    }
}
