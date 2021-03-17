<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Qiniu\Auth;

class YyController extends HomeBaseController{
    public function index(){
        $this->display();
    }
    /**
     * 页面指标概览
     */
    public function indexOverview(){

        //查询条件
        $query = $this->parseIndexOverviewQueryString();

        //查询数据
        $timerange = I('get.timerange');
        if (!empty($timerange)) {
            $count = D('Yy')->getYySummaryCountGroupByUrl($query['map']);
            if ($count > 0) {
                import('Library.Org.Util.Page');
                $p = new \Page($count, 50);
                $vars['info']['page'] =  $p->show();
                $vars['info']['list'] = $this->getYySummaryCalculateData(D('Yy')->getYySummaryListGroupByUrl($query['map'], $query['order'], array($p->firstRow,$p->listRows)));
            }
        }

        //获取日期
        $time['recent_1_day'] = date('Y/m/d', strtotime('-1 day')) . ' - ' . date('Y/m/d', strtotime('-1 day'));
        $time['recent_7_day'] = date('Y/m/d', strtotime('-7 day')) . ' - ' . date('Y/m/d', strtotime('-1 day'));
        $time['recent_30_day'] = date('Y/m/d', strtotime('-30 day')) . ' - ' . date('Y/m/d', strtotime('-1 day'));
        $vars['time'] = $time;

        //模板赋值
        $this->assign('sort', I('get.sort'));
        $this->assign('indexStart', (($p->nowPage - 1) * 50));
        $this->assign('vars', $vars);
        $this->assign('timerange', $timerange);
        $this->display();
    }

    /**
     * 解析页面指标概览查询条件
     * @return array
     */
    public function parseIndexOverviewQueryString()
    {
        //查询参数
        $url = I('get.url');
        $timerange = I('get.timerange');
        $complete = intval(I('get.complete'));
        if (empty($complete) || !in_array($complete, array(1, 2))) {
            $complete = 1;
        }
        $sort = I('get.sort');

        //查询时间
        $satrt_day = date('Y/m/d');
        $end_day = date('Y/m/d');
        if (!empty($timerange)) {
            $exp = explode('-', $timerange);
            $satrt_day = trim($exp[0]);
            $end_day = trim($exp[1]);
        }
        $satrt_day = date('Y-m-d', strtotime($satrt_day));
        $end_day = date('Y-m-d', strtotime($end_day));

        //查询条件
        if (!empty($url)) {
            $map['url'] = ($complete == 1) ? $url : array('LIKE', $url . '%');
        }

        if (empty($satrt_day) || empty($end_day)) {
            exit('非法请求，查询时间不能为空');
        }

        $map['day'] = array(
            array('EGT', $satrt_day),
            array('ELT', $end_day)
        );

        $sortArray = array(
            'pv-1'   => 'pv ASC',
            'pv-2'   => 'pv DESC',
            'uv-1'   => 'uv ASC',
            'uv-2'   => 'uv DESC',
            'fd-1'   => 'order_count ASC',
            'fd-2'   => 'order_count DESC',
            'sjfd-1' => 'real_order_count ASC',
            'sjfd-2' => 'real_order_count DESC',
        );

        $order = '';
        if (!empty($sortArray[$sort]) ) {
            $order = $sortArray[$sort];
        }

        return array(
            'map' => $map,
            'order' => $order
        );
    }

    /**
     * 获取页面指标概览计算数据
     * @param  array  $data 查询的数据
     * @return array
     */
    public function getYySummaryCalculateData($data = array())
    {
        foreach ($data as $key => $value) {
            $data[$key]['pv']               = intval($data[$key]['pv']);
            $data[$key]['uv']               = intval($data[$key]['uv']);
            $data[$key]['bounce_count']     = intval($data[$key]['bounce_count']);
            $data[$key]['exit_count']       = intval($data[$key]['exit_count']);
            $data[$key]['visit_time']       = intval($data[$key]['visit_time']);
            $data[$key]['visit_count']      = intval($data[$key]['visit_count']);
            $data[$key]['entry_count']      = intval($data[$key]['entry_count']);
            $data[$key]['order_count']      = intval($data[$key]['order_count']);
            $data[$key]['real_order_count'] = intval($data[$key]['real_order_count']);
            //跳出率
            $data[$key]['bounce_rate']      = number_format($value['bounce_count'] / $value['pv'] * 100, 2);
            //退出率
            $data[$key]['exit_rate']        = number_format($value['exit_count'] / $value['pv'] * 100, 2);
            //发单转化率
            $data[$key]['fd_zh_rate']       = number_format($value['order_count'] / $value['uv'] * 100, 2);
            //实际分单转化率
            $data[$key]['sjfd_zh_rate']     = number_format($value['real_order_count'] / $value['uv'] * 100, 4);
            //平均访问时长
            $data[$key]['visit_time_avg']   = number_format($value['visit_time'] / $value['visit_count'], 2);
        }
        return $data;
    }

    //页面指标概览-历史趋势
    public function historicalTrend(){
        //查询参数
        $url = I('get.url');
        $timerange = I('get.timerange');
        $group = intval(I('get.group'));
        if (empty($group) || !in_array($group, array(1, 2))) {
            $group = 1;
        }
        //查询时间
        $satrt_day = date('Y/m/d');
        $end_day = date('Y/m/d');
        if (!empty($timerange)) {
            $exp = explode('-', $timerange);
            $satrt_day = trim($exp[0]);
            $end_day = trim($exp[1]);
        }
        $satrt_day = date('Y-m-d', strtotime($satrt_day));
        $end_day = date('Y-m-d', strtotime($end_day));

        //查询条件
        if (!empty($url)) {
            $map['url'] = $url;
        }

        if (empty($satrt_day) || empty($end_day)) {
            $this->error('非法请求，查询时间不能为空');
        }

        $map['day'] = array(
            array('EGT', $satrt_day),
            array('ELT', $end_day)
        );

        $temp = D('Yy')->getYySummaryListGroupByDay($map);
        $result = array();
        foreach ($temp as $key => $value) {
            if ('1' == $group) {
                $result[$value['day']] = $value;
            } else {
                $month = date('Y-m', strtotime($value['day']));
                $result[$month]['url']              = $value['url'];
                $result[$month]['pv']               += $value['pv'];
                $result[$month]['uv']               += $value['uv'];
                $result[$month]['bounce_count']     += $value['bounce_count'];
                $result[$month]['exit_count']       += $value['exit_count'];
                $result[$month]['visit_time']       += $value['visit_time'];
                $result[$month]['visit_count']      += $value['visit_count'];
                $result[$month]['entry_count']      += $value['entry_count'];
                $result[$month]['order_count']      += $value['order_count'];
                $result[$month]['real_order_count'] += $value['real_order_count'];
                $result[$month]['day']              = $month;
            }
        }


        //获取日期
        $timeKey = array();
        $each_start = strtotime($satrt_day);
        $each_end = strtotime($end_day) + 86400;
        if ('1' == $group) {
            while($each_start < $each_end){
                $timeKey[date('Y-m-d',$each_start)] = date('Y-m-d',$each_start);
                $each_start += strtotime('+1 day',$each_start) - $each_start;
            }
        } else {
            while($each_start < $each_end){
                $timeKey[date('Y-m',$each_start)] = date('Y-m',$each_start);
                $each_start += strtotime('+1 day',$each_start) - $each_start;
            }
        }

        $temp = array();
        foreach ($timeKey as $key => $value) {
            $temp[$value] = $result[$value];
        }

        //获取计算数据
        $result = $this->getYySummaryCalculateData($temp);

        //导出数据
        if ('1' == I('get.export')) {
            import('Library.Org.PHP_XLSXWriter.xlsxwriter');
            try {
                $writer = new \XLSXWriter();
                //标题
                $herder = array(
                    '日期',
                    '浏览量PV',
                    '访客数量UV',
                    '跳出率',
                    '退出率',
                    '平均访问时长',
                    '发单量',
                    '实际分单量',
                    '发单转化率',
                    '实际分单转化率',
                    '入口页次数',
                    '退出页次数'
                );
                $wArr = array_values($herder);
                $writer->writeSheetRow('Sheet1', $herder);

                //数据
                foreach ($result as $key => $value) {
                    $v = array(
                        $value['day'],
                        $value['pv'],
                        $value['uv'],
                        $value['bounce_rate'] . '%',
                        $value['exit_rate'] . '%',
                        $value['visit_time_avg'],
                        $value['order_count'],
                        $value['real_order_count'],
                        $value['fd_zh_rate'] . '%',
                        $value['sjfd_zh_rate'] . '%',
                        $value['entry_count'],
                        $value['exit_count']
                    );
                    $wArr = array_values($v);
                    $writer->writeSheetRow('Sheet1', $v);
                }
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");;
                header('Content-Disposition:attachment;filename="页面指标概览-历史趋势-'.date('YmdHis').'.xlsx"');
                header("Content-Transfer-Encoding:binary");
                $writer->writeToStdOut("php://output");
            }catch (Exception $e){
                if($_SESSION["uc_userinfo"]["uid"] == 1){
                    var_dump($e);
                }
            }
            exit();
        }

        $seriesData = $xAxisData = $info = $total = array();
        foreach ($result as $key => $value) {
            //获取总数据
            $total['pv'] += $value['pv'];
            $total['uv'] += $value['uv'];
            $total['order_count'] += $value['order_count'];
            $total['real_order_count'] += $value['real_order_count'];
            //获取表格
            $seriesData['pv'][] = $value['pv'];
            $seriesData['uv'][] = $value['uv'];
            $seriesData['order_count'][] = $value['order_count'];
            $seriesData['real_order_count'][] = $value['real_order_count'];

            $xAxisData[] = $key;
        }
        $seriesData=json_encode(array($seriesData['pv'], $seriesData['uv'], $seriesData['order_count'], $seriesData['real_order_count']));
        $xAxisData = json_encode($xAxisData);


        //获取日期
        $time['recent_1_day'] = date('Y/m/d', strtotime('-1 day')) . ' - ' . date('Y/m/d', strtotime('-1 day'));
        $time['recent_7_day'] = date('Y/m/d', strtotime('-7 day')) . ' - ' . date('Y/m/d', strtotime('-1 day'));
        $time['recent_30_day'] = date('Y/m/d', strtotime('-30 day')) . ' - ' . date('Y/m/d', strtotime('-1 day'));
        $time['last_month'] = date('Y/m/01/', strtotime('-1 month')) . ' - ' . date('Y/m/t/', strtotime('-1 month'));
        $time['this_year'] = date('Y/01/01/') . ' - ' . date('Y/12/31/');
        $vars['time'] = $time;

        //模板赋值
        $this->assign('timerange', date('Y/m/d', strtotime($satrt_day)) . ' - ' . date('Y/m/d', strtotime($end_day)));
        $this->assign('url', $url);
        $this->assign('info', $result);
        $this->assign('seriesData', $seriesData);
        $this->assign('xAxisData', $xAxisData);
        $this->assign('total', $total);
        $this->assign('from', I('get.from'));
        $this->assign('vars', $vars);
        $this->display();
    }


    //UV来源
    public function uvResource(){
        //查询参数
        $url = I('get.url');
        $timerange = I('get.timerange');

        //查询时间
        $satrt_day = date('Y/m/d');
        $end_day = date('Y/m/d');
        if (!empty($timerange)) {
            $exp = explode('-', $timerange);
            $satrt_day = trim($exp[0]);
            $end_day = trim($exp[1]);
        }
        $satrt_day = date('Y-m-d', strtotime($satrt_day));
        $end_day = date('Y-m-d', strtotime($end_day));

        //查询条件
        if (!empty($url)) {
            $map['url'] = $url;
        }

        if (empty($satrt_day) || empty($end_day)) {
            exit('非法请求，查询时间不能为空');
        }

        $map['day'] = array(
            array('EGT', $satrt_day),
            array('ELT', $end_day)
        );


        $result = D('Yy')->getYyUvSourceGroupBySourceGroup($map);
        $this->assign('info', multi_array_sort($result, 'uv_count', SORT_DESC));
        $this->assign('url', $url);
        $this->display();
    }
    //页面上线分析-按时间
    public function onlineAnalysis(){
        //查询条件 页面名称  分类  上线版本号 上线时间 页面地址
        //页面分类
        $category = D('Yy')->getPageCategoryList();
        $vars['category'] = build_tree(1, $category, 'parent');
        //页面名称
        $vars['names'] = D("Yy")->getAllPageNames();

        $keyword = $_GET;
        if(!empty($keyword['ptype'])){
            $cates = D('Yy')->getChildCate($keyword['ptype']);
            $optionstr = '<option value="">请选择</option>';
            foreach ($cates as $k => $v) {
                if($v['id'] == $keyword['ctype']){
                    $optionstr .= '<option selected="selected" value="'.$v['id'].'">'.$v['name'].'</option>';
                }else{
                    $optionstr .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
                }
            }
        }
        $this->assign('keyword',$keyword);
        $this->assign('optionstr',$optionstr);

        //查询结果
        if($keyword['chooseType'] == 1){
            unset($keyword['freestart']);//按天数查，去掉时间
            unset($keyword['freeend']);
        }elseif($keyword['chooseType'] == 2){
            unset($keyword['before']);//自定义时间查，去掉天数
            unset($keyword['after']);
        }
        unset($keyword['chooseType']);//清除没必要的搜索条件
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 25;

        if(!empty($keyword)){
            $result = $this->getUrlDatas($keyword,$pageIndex,$pageCount);
        }

        $this->assign('page', $result['page']);
        $this->assign('list', $result['list']);
        $this->assign('vars', $vars);
        $this->display();
    }

    /*
    * 根据父级分类ID查询子分类
    */
    public function getChildCate()
    {
        $bigcate = I('post.bigcate');
        $cates = D('Yy')->getChildCate($bigcate);
        if(!empty($cates)){
            $optionstr = '<option value="0">请选择</option>';
            foreach ($cates as $k => $v) {
                $optionstr .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
            }
            $this->ajaxReturn(array('data'=> $optionstr,'status' => 1, 'info' => ''));
        }else{
            $this->ajaxReturn(array('data'=> '','status' => 0, 'info' => '网络错误，请刷新重试'));
        }
    }

    /*
     *   查询URL对应的统计数据
     *  @param  asrray  $keyword        搜索条件
     *  @param  intger  $pageIndex      页码
     *  @param  intger  $pageCount      分页长度
     *  @return bool
     *
     */
    private function getUrlDatas($map,$pageIndex,$pageCount){
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['oltime'])){
            $times = explode(' - ', $map['oltime']);
            $map['v_start'] = date('Y-m-d H:i:s',strtotime($times[0]));
            $map['v_end'] = date('Y-m-d H:i:s',strtotime($times[1].' 23:59:59'));
            unset($map['oltime']);
        }
        if(!empty($map['freestart'])){
            $time_s = explode(' - ', $map['freestart']);
            $map['freestart_s'] = date('Y-m-d H:i:s',strtotime($time_s[0]));
            $map['freestart_e'] = date('Y-m-d H:i:s',strtotime($time_s[1]));
            unset($map['freestart']);
        }
        if(!empty($map['freeend'])){
            $time_e = explode(' - ', $map['freeend']);
            $map['freeend_s'] = date('Y-m-d H:i:s',strtotime($time_e[0]));
            $map['freeend_e'] = date('Y-m-d H:i:s',strtotime($time_e[1]));
            unset($map['freeend']);
        }
        $list = D('Yy')->getUrlDatasContent($map, 'p.id desc',($pageIndex-1)*$pageCount,$pageCount);
        if($list['count'] > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($list['count'],$pageCount);
            $result['page'] = $page->show();
        }
        $result['list'] = $list['urls'];
        $result['totalnum'] = $list['count'];
        S("Cache:onlineanalysis:datatongji",$result['list'],3600);
        return $result;
    }


    /*********统计链接相关内容管理部分**********/
    /**
     *   上线页面管理 pagemanagement
     */
    public function pageManagement(){
        //初始化时间
        $lastDate = strtotime("-1 month") * 1000;
        $this->assign('lastDate',$lastDate);
        //查询条件 页面名称  分类  上线版本号 上线时间 页面地址
        //页面分类
        $category = D('Yy')->getPageCategoryList();
        $vars['category'] = json_encode(build_tree(1, $category, 'parent'));
        //版本号 取自qz_yy_version
        $vars['version'] = D('Yy')->getVersionNumberList();
        //页面名称
        $vars['names'] = D("Yy")->getAllPageNames();

        //查询内容
        //处理时间，如果时间为空，默认查最近一个月的
        $time = I('get.time');
        if(empty($time)){
            $start = date('Y-m-d H:i:s',strtotime("-1 month"));
            $timeNow = date('Y-m-d',time());
            $end = date('Y-m-d H:i:s',strtotime($timeNow.' 23:59:59'));
        }else{
            $time = explode(' - ', $time);
            $start = date('Y-m-d H:i:s',strtotime($time[0].' 00:00:00'));
            $end = date('Y-m-d H:i:s',strtotime($time[1].' 23:59:59'));
        }
        $keyword = $_GET;
        //var_dump($keyword);
        if(empty($time)){
            $keyword['time'] = date("Y/m/d",strtotime("-1 month")).' - '.date("Y/m/d",time());
        }
        $this->assign('keyword',$keyword);
        $keyword['start'] = $start;
        $keyword['end'] = $end;
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 25;
        $list = $this->getPageManageContent($pageIndex, $pageCount, $keyword);

        //模板赋值
        $this->assign('list', $list);
        $this->assign('vars', $vars);
        $this->assign('indexStart', (($pageIndex - 1) * 25));
        $this->display();
    }

    /*
    *  添加新版本
    */
    public function addNewVersion()
    {
        $data['pageid'] = I("post.pageid");
        $data['vid'] = I("post.vid");
        $data['time'] = time();
        $where['id'] = $data['pageid'];
        //先查询当前的版本号
        $isHave = D("Yy")->getPageById($data['pageid']);
        if($isHave['vid'] == $data['vid']){
            $this->ajaxReturn(array('data'=> '','status' => 0, 'info' => '版本已存在，请不要重复添加！'));
        }else{
            $m_data['vid'] = $data['vid'];
            $result = M('yy_page_manage')->where($where)->save($m_data);
            $id = M("yy_page_version")->add($data);//新写版本数据
            $this->ajaxReturn(array('data'=> $isHave,'status' => 1, 'info' => '添加成功'));
        }
    }

    /*
    *  清空一个链接的数据
    */
    public function emptyUrlContents()
    {
        $map['id'] = I("post.pageid");
        //删除yy_page_manage
        $result = D("Yy")->delPageById($map);
        $this->ajaxReturn(array('data'=> "",'status' => 1, 'info' => '删除成功'));
    }

    /*
     * 查询页面链接列表
     * @param  int    $pageIndex    页码
     * @param  int    $pageCount    分页数
     * @param  array  $map          查询条件
     * @return array
    */
    private function getPageManageContent($pageIndex, $pageCount, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }
        $count = D('Yy')->getPageManageNum($map);
        $list = D('Yy')->getPageManageContent($map, 'p.id desc',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;

        return $result;
    }

    /**
     *   上线页面-页面添加 addpage
     */
    public function addPage(){
        if($_POST){
            //写入新url
            $name   = trim(I("post.name"));
            $cid    = trim(I("post.cid"));
            $text   = trim(I("post.text"));
            $url    = trim(I("post.urls"));
            $vid    = trim(I("post.vid"));
            //写入yy_page_manage
            //判断 url 是否存在
            $isHave = D("Yy")->getPageByUrl($url);
            if(empty($isHave)){
                //新增一条
                $data['name'] = $name;
                $data['url'] = $url;
                $data['categoryid'] = $cid;
                $data['vid'] = $vid;
                $data['text'] = $text;
                $data['status'] = 1;
                $id = M("yy_page_manage")->add($data);//新写入URL并获取ID
                //版本VID单独写入
                if($id){
                    $v_data['vid'] = $vid;
                    $v_data['pageid'] = $id;
                    //检查版本是否存在
                    $haveVersion = D("Yy")->getVersionContentById($v_data);
                    if(empty($haveVersion)){
                        $v_data['time'] = time();
                        M("yy_page_version")->add($v_data);//新写版本数据
                    }
                    $this->ajaxReturn(array('data'=> $id,'status' => 1, 'info' => '添加成功'));
                }else{
                    $this->ajaxReturn(array('data'=> '','status' => 0, 'info' => '添加失败，请稍后重试'));
                }

            }else{
                //$this->ajaxReturn(array('status' => 0, 'info' => '抱歉，添加失败，请重新输入！'));
                //更新原纪录
                $data['name'] = $name;
                $data['url'] = $url;
                $data['categoryid'] = $cid;
                $data['vid'] = $vid;
                $data['text'] = $text;
                $data['status'] = 1;
                $map['id'] = $isHave['id'];
                $id = M("yy_page_manage")->where($map)->save($data);//新写入URL并获取ID
                //版本VID单独写入
                if($id){
                    $v_data['vid'] = $vid;
                    $v_data['pageid'] = $isHave['id'];
                    //检查版本是否存在
                    $haveVersion = D("Yy")->getVersionContentById($v_data);
                    if(empty($haveVersion)){
                        $v_data['time'] = time();
                        M("yy_page_version")->add($v_data);//新写版本数据
                    }
                    $this->ajaxReturn(array('data'=> $id,'status' => 1, 'info' => '添加成功'));
                }else{
                    $this->ajaxReturn(array('data'=> '','status' => 0, 'info' => '添加失败，请稍后重试'));
                }
            }
        }else{
            //页面分类
            $category = D('Yy')->getPageCategoryList();
            $vars['category'] = json_encode(build_tree(1, $category, 'parent'));

            //版本号 取自qz_yy_version
            $vars['version'] = D('Yy')->getVersionNumberList();

            //模板赋值
            $this->assign('vars', $vars);
        }

        $this->display();
    }

    /*
    *   查询数据库中的URL 取自qz_yy_href_collect
    */
    public function findUrl($value='')
    {
        if ($_POST) {
            $query = I("post.query");
            $result = D('Yy')->findUrl($query);
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));
        }
    }

    /*
    *   查询数据库中的URL 取自qz_yy_href_collect
    */
    public function getVersionTime($value='')
    {
        if ($_POST) {
            $vid = I("post.vid");
            $result = D('Yy')->getVersionTime($vid);
            return $this->ajaxReturn(array("data"=>$result['online_time'],"status"=>1));
        }
    }

    /**
     *   上线页面-历史版本 historyversion
     */
    public function historyVersion(){
        $url_id = I("get.urid");
        //查询url的基本信息
        $info['content'] = D('Yy')->getUrlContentById($url_id);

        //查询版本信息
        $version = D('Yy')->getUrlVersionById($url_id);
        foreach ($version as $k => $v) {
            $version[$k]['px'] = $k +1;
        }
        $info['version'] = $version;
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 链接采集管理
     */
    public function hrefCollect()
    {
        $cid  = I('get.cid');
        $href = I('get.href');

        //获取列表与分页
        import('Library.Org.Util.Page');
        $count = D('Yy')->getHrefCollectCount($cid, $href);
        $page = new \Page($count, 50);
        $vars['page'] =  $page->show();
        $vars['info'] = D('Yy')->getHrefCollectList($cid, $href, $page->firstRow,$page->listRows);

        //获取城市信息
        $vars['quyu'] = D('Quyu')->getQuyuList();

        //模板赋值
        $this->assign('indexStart', (($page->nowPage - 1) * 50));
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 上线页面-版本管理
     */
    public function version()
    {
        //筛选条件
        $cid_parent     = I('get.cid_parent');
        $cid            = I('get.cid');
        $version_number = I('get.version_number');
        $version_title  = I('get.version_title');
        $timerange      = I('get.timerange');

        $online_time_start = $online_time_end = '';

        //查询时间
        if (!empty($timerange)) {
            $exp = explode('-', $timerange);
            $online_time_start = date('Y-m-d', strtotime(trim($exp[0])));
            $online_time_end = date('Y-m-d', strtotime(trim($exp[1])));
        }
        //获取列表与分页
        import('Library.Org.Util.Page');
        $count = D('Yy')->getVersionCount($cid_parent, $cid, $version_number, $version_title, $online_time_start, $online_time_end);
        $page = new \Page($count, 50);
        $vars['page'] =  $page->show();
        $vars['info'] = D('Yy')->getVersionList($cid_parent, $cid, $version_number, $version_title, $online_time_start, $online_time_end, $page->firstRow,$page->listRows);

        //页面分类
        $category = D('Yy')->getPageCategoryList();
        $vars['category'] = json_encode(build_tree(1, $category, 'parent'));

        //版本
        $vars['version'] = D('Yy')->getVersionNumberList();

        //模板赋值
        $this->assign('indexStart', (($page->nowPage - 1) * 50));
        $this->assign('timerange', $timerange);
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 新增编辑版本
     */
    public function versionOperate(){

        //新增或编辑
        if (IS_POST) {
            $id = I('post.id');
            $save = array(
                'yy_page_category_id' => intval(I('post.yy_page_category_id')),
                'version_number'               => I('post.version_number'),
                'version_title'              => I('post.version_title'),
                'online_time'                => I('post.online_time'),
                'remark'                     => I('post.remark'),
                'enclosure'                  => I('post.enclosure'),
                'update_time'                => date('Y-m-d H:i:s')
            );

            //检查分类
            if (empty($save['yy_page_category_id'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请选择所属分类'));
            }

            //检查版本号
            if (empty($save['version_number'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请填写版本号'));
            }

            //检查标题
            if (empty($save['version_title'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请填写标题'));
            }

            //检查上线时间
            if (empty($save['online_time'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '请填写上线时间'));
            }

            //检查该版本号是否存在
            if (D('Yy')->chechVersionNumberExist($id, $save['version_number'])) {
                $this->ajaxReturn(array('status' => 0, 'info' => '该版本号已存在'));
            }

            //编辑
            if (!empty($id)) {
                $result = D('Yy')->editVersion($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功'));
                }
                $this->ajaxReturn(array('status' => 0, 'info' => '编辑失败'));
            }

            //新增
            $save['add_time'] = $save['update_time'];
            $result = D('Yy')->addVersion($save);
            if ($result) {
                $this->ajaxReturn(array('status' => 1, 'info' => '新增成功'));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => '新增失败'));
        }

        //获取待编辑版本
        $id = I('get.id');
        if (!empty($id)) {
            $vars['info'] = D('Yy')->getVersionById($id);
            if (!empty($vars['info']['enclosure'])) {
                $vars['info']['enclosureName'] = end(array_filter(explode('/', $vars['info']['enclosure'])));
            }
        }

        //页面分类
        $category = D('Yy')->getPageCategoryList();
        $vars['category'] = json_encode(build_tree(1, $category, 'parent'));

        //获取上传Token
        $auth = new Auth(OP('QINIU_AK'), OP('QINIU_CK'));
        $policy = array(
            'returnBody' => '{"name":$(key)}',
            'saveKey' => 'enclosure/$(year)$(mon)$(day)$(hour)$(min)$(sec)/$(fname)'
        );
        $vars['token'] = $auth->uploadToken(OP('QINIU_PRIVATE_BUCKET'), null, 3600, $policy);
        //模板赋值
        $this->assign('vars', $vars);
        $this->assign('operate', empty($id) ? '新增' : '编辑');
        $this->display();
    }

    /**
     * 下载版本附件
     */
    public function versionDownloadEnclosure()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
        }
        $info = D('Yy')->getVersionById($id);
        if (empty($info['enclosure'])) {
            $this->ajaxReturn(array('status' => 0, 'info' => '该版本无附件'));
        }
        //生成带有有效期的下载链接
        $auth = new Auth(OP('QINIU_AK'), OP('QINIU_CK'));
        $url = 'http://' . C('QINIU_PRIVATE_DOMAIN') . '/' . $info['enclosure'];
        $url = $auth->privateDownloadUrl($url);
        $this->ajaxReturn(array('status' => 1, 'data' => array('url' => $url), 'info' => '获取成功'));
    }

    /**
     * 删除版本
     */
    public function versionDelete()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
        }
        $result = D('Yy')->deleteVersion($id);
        if ($result) {
            $this->ajaxReturn(array('status' => 1, 'info' => '删除成功'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '删除失败'));
    }

    /**
     * 页面分类
     */
    public function pageCategory(){
        //新增编辑
        if (IS_POST) {
            //新增
            if ('1' == I('post.type')) {
                $save = array(
                    'parent'      => intval(I('post.parent')),
                    'name'        => I('post.name'),
                    'status'      => 1,
                    'add_time'    => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                );

                //判断该分类是否已存在
                $info = D('Yy')->getPageCategory(0, $save['parent'], $save['name'], $save['status']);
                if (count($info) > 0) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，分类名称已存在，请重新输入'));
                }

                //新增
                $result = D('Yy')->addPageCategory($save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功'));
                }
                $this->ajaxReturn(array('status' => 0, 'info' => '新增失败'));
            }
            //编辑
            if ('2' == I('post.type')) {
                $id = I('post.id');
                $save = array(
                    'name'        => I('post.name'),
                    'update_time' => date('Y-m-d H:i:s')
                );
                if (empty($id)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
                }

                //判断该分类是否已存在
                $info = D('Yy')->getPageCategory($id, 0, '', 1)[0];
                if ($save['name'] != $info['name']) {
                    if (empty($info)) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '该分类不存在'));
                    }
                    $temp = D('Yy')->getPageCategory(0, $info['parent'], $save['name'], $info['status']);
                    if (count($temp) > 0) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，分类名称已存在，请重新输入'));
                    }
                }

                //编辑
                $result = D('Yy')->editPageCategory($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功'));
                }
                $this->ajaxReturn(array('status' => 0, 'info' => '编辑失败'));
            }
            //删除
            if ('3' == I('post.type')) {
                $id = I('post.id');

                if (empty($id)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
                }
                $result = D('Yy')->getPageCategoryByParent($id);
                if (!empty($result)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '该分类下尚有其它分类，不可删除'));
                }

                //判断该分类下是否有其它页面
                $temp = D('Yy')->getYyPageManageByCategoryid($id);
                if (!empty($temp)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '该分类已被页面管理使用，不可删除'));
                }
                //判断版本管理是否使用该分类
                $temp = D('Yy')->getVersionByYyPageCategoryId($id);
                if (!empty($temp)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '该分类已被版本管理使用，不可删除'));
                }

                $save = array(
                    'status'      => '2',
                    'update_time' => date('Y-m-d H:i:s')
                );
                $result = D('Yy')->editPageCategory($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '删除成功'));
                }
                $this->ajaxReturn(array('status' => 0, 'info' => '删除失败'));
            }
        }
        //分类列表
        $category = D('Yy')->getPageCategoryList();
        $vars['info'] = build_tree(1, $category, 'parent');
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 新增编辑部门
     */
    public function departmentContents(){
        //新增编辑
        if (IS_POST) {
            //新增
            if ('1' == I('post.type')) {
                $save = array(
                    'parent'      => intval(I('post.parent')),
                    'name'        => I('post.name'),
                    'status'      => 1,
                    'add_time'    => time(),
                    'update_time' => time()
                );

                //判断该分类是否已存在
                $info = D('Yy')->getDepartmentConn(0, $save['parent'], $save['name'], $save['status']);
                if (count($info) > 0) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，部门名称已存在，请重新输入'));
                }

                //新增
                $result = D('Yy')->addNewDepartment($save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功'));
                }
                $this->ajaxReturn(array('data' => $save,'status' => 0, 'info' => '新增失败'));
            }
            //编辑
            if ('2' == I('post.type')) {
                $id = I('post.id');
                $save = array(
                    'name'        => I('post.name'),
                    'update_time' => time()
                );
                if (empty($id)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
                }

                //判断该分类是否已存在
                $info = D('Yy')->getDepartmentConn($id, 0, '', 1)[0];
                if ($save['name'] != $info['name']) {
                    if (empty($info)) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '该分类不存在'));
                    }
                    $temp = D('Yy')->getDepartmentConn(0, $info['parent'], $save['name'], $info['status']);
                    if (count($temp) > 0) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，分类名称已存在，请重新输入'));
                    }
                }

                //编辑
                $result = D('Yy')->editDepartment($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功'));
                }
                $this->ajaxReturn(array('data' => $info,'status' => 0, 'info' => '编辑失败'));
            }
            //删除
            if ('3' == I('post.type')) {
                $id = I('post.id');
                $level = I('post.level');

                if (empty($id) || empty($level)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '删除失败'));
                }
                $save = array(
                    'status'      => '2',
                    'update_time' => time()
                );
                $result = D('Yy')->delDepartment($id, $save, $level);
                if ($result) {
                    $this->ajaxReturn(array('data' => $result,'status' => 1, 'info' => '删除成功'));
                }
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '删除失败'));
            }
        }
        //分类列表
        $category = D('Yy')->getDepartmentList();
        $vars['info'] = build_tree(0, $category, 'parent');
        $this->assign('vars', $vars);
        $this->display();
    }

    /*
     *  新增/编辑微信公众号
     */
    public function addWeiXin()
    {
        //页面分类
        $dept = D('Yy')->getDeptList();
        $vars['dept'] = json_encode(build_tree(0, $dept, 'parent'));
        $type = 1;
        if(!empty($_GET['id'])){
            //如果有传入ID，是编辑
            $wx = D("Yy")->getWXConnById($_GET['id']);
            $wx['regtime'] = date('Y-m-d H:i:s',$wx['regtime']);
            $wx['auttime'] = date('Y-m-d H:i:s',$wx['auttime']);
            $type = 2;
        }
        $this->assign('wx',$wx);
        $this->assign('type',$type);
        $this->assign('vars',$vars);
        $this->display();
    }

    /*
     *  ajax操作（1新增 2编辑 3删除）
     */
    public function manageWeiXin()
    {
        //新增编辑
        if (IS_POST) {
            //新增
            if ('1' == I('post.type')) {
                $save = array(
                    'appid'             => trim(I('post.appid')),
                    'appsecret'         => trim(I('post.appsecret')),
                    'wxname'            => trim(I('post.wxname')),
                    'wxtype'            => I('post.wxtype'),
                    'wxdept'            => I('post.wxdept'),
                    'wxmanager'         => trim(I('post.wxmanager')),
                    'wx_mwx'            => trim(I('post.wx_mwx')),
                    'wxmobile'          => trim(I('post.wxmobile')),
                    'wxmail'            => trim(I('post.wxmail')),
                    'regtime'           => strtotime(I('post.regtime')),
                    'auttime'           => strtotime(I('post.auttime')),
                    'addtime'           => time(),
                    'status'            => 1
                );

                //判断该分类是否已存在
                $info = D('Yy')->getWXConn(0, $save['appid'], $save['wxname'], $save['wxmanager'], 1);
                if (count($info) > 0) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，相同公众号已存在，请重新输入'));
                }

                //新增
                $result = D('Yy')->addNewWX($save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功'));
                }
                $this->ajaxReturn(array('data' => $_POST,'status' => 0, 'info' => '新增失败'));
            }
            //编辑
            if ('2' == I('post.type')) {
                $id = I('post.id');
                $save = array(
                    'appid'             => trim(I('post.appid')),
                    'appsecret'         => trim(I('post.appsecret')),
                    'wxname'            => trim(I('post.wxname')),
                    'wxtype'            => I('post.wxtype'),
                    'wxdept'            => I('post.wxdept'),
                    'wxmanager'         => trim(I('post.wxmanager')),
                    'wx_mwx'            => trim(I('post.wx_mwx')),
                    'wxmobile'          => trim(I('post.wxmobile')),
                    'wxmail'            => trim(I('post.wxmail')),
                    'regtime'           => strtotime(I('post.regtime')),
                    'auttime'           => strtotime(I('post.auttime')),
                    'status'            => 1
                );
                if (empty($id)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
                }

                //判断该分类是否已存在
                $info = D("Yy")->getWXConnById($id);
                if (!empty($info)) {
                    $temp = D('Yy')->getWXConnByAppid($save['appid'],$save['appsecret']);
                    if (count($temp) > 0 && $id != $temp['id']) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '抱歉，微信APPID已存在，请重新输入'));
                    }
                }else{
                    $this->ajaxReturn(array('status' => 0, 'info' => '该微信数据不存在'));
                }

                //编辑
                $result = D('Yy')->editWXConn($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功'));
                }
                $this->ajaxReturn(array('data' => $info,'status' => 0, 'info' => '编辑失败'));
            }
            //删除
            if ('3' == I('post.type')) {
                $id = I('post.id');

                if (empty($id)) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '删除失败'));
                }
                $save = array(
                    'status'      => '2'
                );
                $result = D('Yy')->editWXConn($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('data' => $result,'status' => 1, 'info' => '删除成功'));
                }
                $this->ajaxReturn(array('data' => $result,'status' => 0, 'info' => '删除失败'));
            }
        }else{
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败，请稍后重试！'));
        }
    }

    /**
     * 公众号列表
     */
    public function weiXinList()
    {
        //部门分类
        $dept = D('Yy')->getDeptList();
        $dept = build_tree(0, $dept, 'parent');
        $bumen = array();
        foreach ($dept as $k => $v) {
            $bumen = array_merge($bumen,$v['children']);
        }

        // //筛选条件
        $search['appid']            = I('get.appid');
        $search['appsecret']        = I('get.appsecret');
        $search['dept']             = I('get.dept');
        $search['wxmanager']        = I('get.wxmanager');
        $search['regtime']          = I('get.regtime');
        $search['auttime']          = I('get.auttime');

        //获取列表与分页
        import('Library.Org.Util.Page');
        $count = D('Yy')->getWXNumCount($search);
        $page = new \Page($count, 2);
        $vars['page'] =  $page->show();
        $vars['info'] = D('Yy')->getWXList($search, $page->firstRow,$page->listRows);
        foreach ($vars['info'] as $k => $v) {
            $vars['info'][$k]['xuhao'] = $k + $page->firstRow +1;
        }
        $this->assign('list',$vars['info']);
        $this->assign('page',$vars['page']);
        $this->assign('keyword',$search);
        $this->assign('bumen',$bumen);
        $this->display();
    }

    /*
    * 批量导入微信
    *
    */
    public function uploadWXForm(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  城市        系数月份
        //数据          城市名称    系数数据
        // $d_title = $excel[0];
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if($k == 0){
                continue;//第一行是表格的title ,直接跳过
            }

            
            //微信号类别
            $wxtype_arr = [
                '公众号' => 1,
                '服务号' => 2,
                '企业号' => 3,
            ];
            $save = array(
                'appid'             => $v[0],
                'appsecret'         => $v[1],
                'wxname'            => $v[2],
                'wxtype'            => $wxtype_arr[$v[3]],
                //'wxdept'            => I('post.wxdept'),
                'wxmanager'         => $v[5],
                'wx_mwx'            => $v[6],
                'wxmobile'          => $v[7],
                'wxmail'            => $v[8],
                'regtime'           => strtotime($v[9]),
                'auttime'           => strtotime($v[10]),
                'addtime'           => time(),
                'status'            => 1
            );
            $data[] = $save;
            //所属部门
            $dept = explode('-', $v[4]);
            $dept = trim($dept[1]);
            $dept = D('Yy')->getDeptByName($dept);
            if(!empty($dept)){
                $save['wxdept'] = $dept['id'];
                //新增
                $result = D('Yy')->addNewWX($save);
            }
        }
        $this->ajaxReturn(array("data"=>$data,"info"=>'',"status"=>1));
    }

}