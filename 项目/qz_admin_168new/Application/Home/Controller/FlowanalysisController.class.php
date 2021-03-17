<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Qiniu\Auth;

class FlowanalysisController extends HomeBaseController{
    //流量趋势分析
    public function flowTrend(){
        $type    = array_filter(explode(',', I('get.type')));
        $city    = I('get.city');
        $tag     = intval(I('get.tag'));
        $channel = intval(I('get.channel'));
        //时间范围
        $timerange = I('get.timerange');
        //如果查询时间为空，则默认为昨天的
        if (!empty($timerange)) {
            //获取查询时间
            $timerange = explode('-',$timerange);
            $start = strtotime(trim($timerange[0]));
            $end = strtotime(trim($timerange[1]));
            if (false == $start || false == $end || $start > $end) {
                $start = $end = strtotime('-1 day');
            }
        } else {
            $start = $end = strtotime('-1 day');
        }
        $vars['timerange'] = date("Y/m/d", $start) . ' - ' . date("Y/m/d", $end);
        $start = date("Y-m-d", $start);
        $end = date("Y-m-d", ($end + 86400 - 1));

        //图表预设数据，避免切换图表JS报错
        $series = $preinstall = array(
            'day' => array(),
            'month' => array()
        );

        //四个查询条件必须有一个不为空才进行查询
        if (!empty($type) || !empty($city) || !empty($tag) || !empty($channel)) {
            //获取待匹配URL
            $url = array();
            if (!empty($tag)) {
                $temp = D('Flowanalysis')->getChannelByChannelTagId($tag);
                if (!empty($temp)) {
                    foreach ($temp as $key => $value) {
                        $url[] = $value['url'];
                    }
                }
            }
            if (!empty($channel)) {
                $temp = D('Flowanalysis')->getChannelByChannelId($channel);
                if (!empty($temp)) {
                    if (!empty($url)) {
                        $url = array_intersect($url, array($temp['url']));
                        if (empty($url)) {
                            $this->error('频道标签与频道匹配错误！');
                        }
                    } else {
                        $url = array($temp['url']);
                    }
                }
            }

            //获取统计汇总数据
            if (!empty($type) || !empty($city) || !empty($url)) {
                $temp = D('Flowanalysis')->getFlowTrendList($type, $city, $url, $start, $end);
                if (!empty($temp)) {
                    $stat = array();
                    foreach ($temp as $key => $value) {
                        //按照日期汇总
                        $dayDimension = $value['day'];
                        $stat['day'][$dayDimension]['dimension'] = $dayDimension;
                        $stat['day'][$dayDimension]['uv'] += $value['uv'];
                        $stat['day'][$dayDimension]['pv'] += $value['pv'];
                        $stat['day'][$dayDimension]['bounce_count'] += $value['bounce_count'];
                        $stat['day'][$dayDimension]['exit_count'] += $value['exit_count'];
                        $stat['day'][$dayDimension]['visit_time'] += $value['visit_time'];
                        $stat['day'][$dayDimension]['visit_count'] += $value['visit_count'];
                        $stat['day'][$dayDimension]['entry_count'] += $value['entry_count'];
                        $stat['day'][$dayDimension]['order_count'] += $value['order_count'];
                        $stat['day'][$dayDimension]['order_fen_count'] += $value['order_fen_count'];
                        $stat['day'][$dayDimension]['order_real_fen_count'] += $value['order_real_fen_count'];
                        //安装月份汇总
                        $monthDimension = date('Y-m', strtotime($value['day']));
                        $stat['month'][$monthDimension]['dimension'] = $monthDimension;
                        $stat['month'][$monthDimension]['uv'] += $value['uv'];
                        $stat['month'][$monthDimension]['pv'] += $value['pv'];
                        $stat['month'][$monthDimension]['bounce_count'] += $value['bounce_count'];
                        $stat['month'][$monthDimension]['exit_count'] += $value['exit_count'];
                        $stat['month'][$monthDimension]['visit_time'] += $value['visit_time'];
                        $stat['month'][$monthDimension]['visit_count'] += $value['visit_count'];
                        $stat['month'][$monthDimension]['entry_count'] += $value['entry_count'];
                        $stat['month'][$monthDimension]['order_count'] += $value['order_count'];
                        $stat['month'][$monthDimension]['order_fen_count'] += $value['order_fen_count'];
                        $stat['month'][$monthDimension]['order_real_fen_count'] += $value['order_real_fen_count'];
                        //渠道来源汇总
                        $srcDimension = $value['order_source_src'];
                        $stat['src'][$srcDimension]['dimension'] = $srcDimension;
                        $stat['src'][$srcDimension]['uv'] += $value['uv'];
                        $stat['src'][$srcDimension]['pv'] += $value['pv'];
                        $stat['src'][$srcDimension]['bounce_count'] += $value['bounce_count'];
                        $stat['src'][$srcDimension]['exit_count'] += $value['exit_count'];
                        $stat['src'][$srcDimension]['visit_time'] += $value['visit_time'];
                        $stat['src'][$srcDimension]['visit_count'] += $value['visit_count'];
                        $stat['src'][$srcDimension]['entry_count'] += $value['entry_count'];
                        $stat['src'][$srcDimension]['order_count'] += $value['order_count'];
                        $stat['src'][$srcDimension]['order_fen_count'] += $value['order_fen_count'];
                        $stat['src'][$srcDimension]['order_real_fen_count'] += $value['order_real_fen_count'];
                    }
                }
            }

            //预设的时间和月份
            $preinstall['day'] = get_day_list(strtotime($start), strtotime($end));
            $preinstall['month'] = get_month_list(strtotime($start), strtotime($end));

            //获取图表数据
            $temp = array();
            foreach ($preinstall['day'] as $key => $value) {
                $temp['day']['PV'][] = intval($stat['day'][$value]['pv']);
                $temp['day']['UV'][] = intval($stat['day'][$value]['uv']);
                $temp['day']['发单量'][] = intval($stat['day'][$value]['order_count']);
                $temp['day']['分单量'][] = intval($stat['day'][$value]['order_fen_count']);
                $temp['day']['实际分单量'][] = intval($stat['day'][$value]['order_real_fen_count']);
            }
            foreach ($preinstall['month'] as $key => $value) {
                $temp['month']['PV'][] = intval($stat['month'][$value]['pv']);
                $temp['month']['UV'][] = intval($stat['month'][$value]['uv']);
                $temp['month']['发单量'][] = intval($stat['month'][$value]['order_count']);
                $temp['month']['分单量'][] = intval($stat['month'][$value]['order_fen_count']);
                $temp['month']['实际分单量'][] = intval($stat['month'][$value]['order_real_fen_count']);
            }
            foreach ($temp['day'] as $key => $value) {
                $series['day'][] = array(
                    'name' => $key,
                    'type' => 'line',
                    'stack'=> $key,
                    'data' => $value
                );
            }
            foreach ($temp['month'] as $key => $value) {
                $series['month'][] = array(
                    'name' => $key,
                    'type' => 'line',
                    'stack'=> $key,
                    'data' => $value
                );
            }

            /*S-获取渠道来源属性*/
            $temp = M('order_source')->alias('a')
                                     ->field('a.src, a.name, d.name AS department,a.charge, g. NAME AS group_name')
                                     ->join('LEFT JOIN qz_order_source_group AS g ON a.groupid = g.id')
                                     ->join('LEFT JOIN qz_department_identify AS d ON d.id = a.dept')
                                     ->group('a.id')
                                     ->select();
            $srcArray = array();
            foreach ($temp as $key => $value) {
                $srcArray[$value['src']] = $value;
            }
            $vars['srcArray'] = $srcArray;
            /*E-获取渠道来源属性*/
            $vars['isShow'] = 'show-yes';
        } else {
            $vars['isShow'] = 'show-not';
        }

        //标签和频道互绑参数
        $query = array(
            'tagType'      => intval(I('get.tagType')),
            'tagChose'     => intval(I('get.tagChose')),
            'channelType'  => intval(I('get.channelType')),
            'channelChose' => intval(I('get.channelChose')),
        );
        $vars['query'] = $query;
        //统计数据
        $vars['stat'] = $stat;
        //图标数据
        $vars['xAxis'] = json_encode($preinstall);
        $vars['series'] = json_encode($series);
        //预定义日期和月份，用于循环
        $vars['preinstall'] = $preinstall;
        //标签频道联动
        $vars['relation'] = json_encode(D('Flowanalysis')->getChannelTagAndChannel());
        //获取城市信息
        $vars['city'] = D('Quyu')->getQuyuList();

        $this->assign('vars', $vars);
        $this->display();
    }
    //频道标签列表
    public function channellabel(){
        $keyword = $_GET;
        //查询所有的频道标签名称
        $tags = D('Flowanalysis')->getAllChannelTags();

        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 20;

        $result = D('Flowanalysis')->getChannelTagsList($keyword,($pageIndex-1)*$pageCount,$pageCount);
        if($result['count'] > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($result['count'],$pageCount);
            $result['page'] = $page->show();
        }

        //初始化时间
        $lastDate = strtotime("-1 month") * 1000;
        $this->assign('keyword',$keyword);
        $this->assign('lastDate',$lastDate);
        $this->assign('tags',$tags);
        $this->assign('page',$result['page']);
        $this->assign('list',$result['list']);
        $this->display();
    }
    //添加新的频道标签
    public function channellabelAdd(){
        //查询所有频道
        $channels = D('Flowanalysis')->selectAllChannelNames();

        $this->assign('channels',$channels);
        $this->display();
    }
    //修改频道标签内容
    public function channellabelEdit(){
        if($_POST){
            $id = I('post.id');
            $name = I('post.name');
            $channel = I('post.channel');
            if(!empty($channel)){
                $ch_arr = explode(',', trim($channel));
            }
            $result = D('Flowanalysis')->editTagNow($id,$name,$ch_arr);
            if($result){
                $this->ajaxReturn(array("info"=>"频道标签修改成功！","status"=>1 ));
            }else{
                $this->ajaxReturn(array('data'=>$_POST,"info"=>"操作失败，请稍后再试！","status"=>0 ));
            }
        }
        $id = I('get.id');
        if($id == ''){
            $this->error('要编辑的标签ID不能为空！');
        }
        $channels = D('Flowanalysis')->selectAllChannelNames();
        //被选中频道
        $checkedCh = D('Flowanalysis')->getCheckedChannel($id);
        foreach ($checkedCh as $k => $v) {
            $hotids[$k] = $v['channel_id'];
        }
        $tag = D('Flowanalysis')->getTagById($id);
        $this->assign('hotids',json_encode($hotids));
        $this->assign('tag',$tag);
        $this->assign('channels',$channels);
        $this->display();
    }
    //频道管理
    public function channelManage()
    {
        $keyword = $_GET;
        //查询所有频道名称
        $channelNames = D('Flowanalysis')->selectAllChannelNames();

        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 20;

        $result = D('Flowanalysis')->getChannelList($keyword,($pageIndex-1)*$pageCount,$pageCount);
        if($result['count'] > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($result['count'],$pageCount);
            $result['page'] = $page->show();
        }
        //初始化时间
        $lastDate = strtotime("-1 month") * 1000;
        $this->assign('page',$result['page']);
        $this->assign('list',$result['list']);
        $this->assign('keyword',$keyword);
        $this->assign('channels',$channelNames);
        $this->assign('lastDate',$lastDate);
        $this->display();
    }
    //新增频道
    public function channelManageAdd()
    {
        $this->display();
    }
    //编辑频道
    public function channelManageEdit()
    {
        if($_POST){
            $id = I('post.id');
            $name = I('post.name');
            $url = I('post.url');

            $data['name'] = trim($name);
            $data['url'] = trim($url);
            $data['edit_time'] = time();
            $isHave = D('Flowanalysis')->checkChannelName($id);
            if(!empty($isHave)){
                $result = D('Flowanalysis')->editChannelById($id,$data);
                if($result){
                    $this->ajaxReturn(array("info"=>"频道修改成功！","status"=>1 ));
                }else{
                    $this->ajaxReturn(array('data'=>$_POST,"info"=>"编辑失败，请稍后再试！","status"=>0 ));
                }
            }else{
                $this->ajaxReturn(array('data'=>$_POST,"info"=>"要编辑的频道不存在，请检查！","status"=>0 ));
            }
        }
        $id = I('get.id');
        if($id == ''){
            $this->error('要编辑的问题ID不能为空！');
        }
        $channel = D('Flowanalysis')->getChannelById($id);
        $this->assign('channel',$channel);
        $this->display();
    }

    /*
     *  新增频道写入
     */
    public function addNewChannel()
    {
        if($_POST){
            $name   = I('post.name');
            $url    = I('post.url');

            $data['name']           = trim($name);
            $data['url']            = trim($url);
            $data['status']         = 1;
            $data['add_time']    = time();
            $data['edit_time']    = time();

            //生成新的频道code  先查询最新的code
            $code = D('Flowanalysis')->getLastCode();
            if(empty($code[0])){
                //今天没有写
                $data['code'] = date('Ymd',time()).'001';
            }else{
                //截取最后3位
                $code = intval(substr($code[0]['code'], -3));
                $code = $code + 1;
                $length = strlen($code);
                for ($i=0; $i < 3-$length; $i++) {
                    $code = '0'.$code;
                }
                $data['code'] = date('Ymd',time()).$code;
            }
            $isHave = D('Flowanalysis')->checkChannelName($data['name']);
            if(empty($isHave)){
                //写入新的
                $result = D('Flowanalysis')->addNewChannel($data);
                if($result){
                    $this->ajaxReturn(array('data'=>$result,"info"=>"添加成功！","status"=>1));
                }else{
                    $this->ajaxReturn(array("info"=>"添加失败，请重试！","status"=>0 ));
                }
            }else{
                $this->ajaxReturn(array("info"=>"要添加的频道名称已存在！","status"=>0));
            }
        }else{
            $this->ajaxReturn(array("info"=>"请检查输入值是否正确！","status"=>0));
        }
    }
    //删除频道
    public function delChannelNow()
    {
        if($_POST){
            $id = I('post.id');
            $result = D('Flowanalysis')->delChannelNow($id);
            if($result){
                $this->ajaxReturn(array('data'=>$result,"info"=>"删除成功！","status"=>1));
            }else{
                $this->ajaxReturn(array("info"=>"删除失败，请重试！","status"=>0 ));
            }
        }else{
            $this->ajaxReturn(array("info"=>"删除失败，请重试！","status"=>0 ));
        }
    }
    //添加频道标签
    public function addnewchanneltags()
    {
        if($_POST){
            $name = I('post.name');
            $channels = I('post.checkedchannels');

            //生成新的频道code  先查询最新的code
            $code = D('Flowanalysis')->getLastTagCode();
            if(empty($code[0])){
                //今天没有写
                $data['code'] = date('Ymd',time()).'001';
            }else{
                //截取最后3位
                $code = intval(substr($code[0]['code'], -3));
                $code = $code + 1;
                $length = strlen($code);
                for ($i=0; $i < 3-$length; $i++) {
                    $code = '0'.$code;
                }
                $data['code'] = date('Ymd',time()).$code;
            }

            $data['name'] = trim($name);
            if(!empty($data['name'])){
                $result = D('Flowanalysis')->addChannelTag($data);
                if($result){
                    $channelArr = explode(',', $channels);
                    foreach ($channelArr as $k => $v) {
                        $c_data['channel_id'] = $v;
                        $c_data['channel_tag_id'] = $result;
                        $c_data['add_time'] = time();
                        $c_data['edit_time'] = time();
                        $saveData[] = $c_data;
                    }
                    $c_result = D('Flowanalysis')->saveTagChannels($saveData);

                    $this->ajaxReturn(array("info"=>"添加成功！","status"=>1 ));
                }else{
                    $this->ajaxReturn(array("info"=>"添加失败，请重试！","status"=>0 ));
                }
            }else{
                $this->ajaxReturn(array("info"=>"添加失败，请检查标签名称是否正确！","status"=>0 ));
            }

        }else{
            $this->ajaxReturn(array("info"=>"添加失败，请重试！","status"=>0 ));
        }
    }

    //删除频道标签
    public function delChannelTag()
    {
        if($_POST){
            $id = I('post.id');
            $result = D('Flowanalysis')->delTagNow($id);
            if($result){
                $this->ajaxReturn(array('data'=>$result,"info"=>"删除成功！","status"=>1));
            }else{
                $this->ajaxReturn(array("info"=>"删除失败，请重试！","status"=>0 ));
            }
        }else{
            $this->ajaxReturn(array("info"=>"删除失败，请重试！","status"=>0 ));
        }
    }

    //获取标签下频道及分页
    public function getChannelsInTag()
    {
        $id = intval(I('post.id'));
        $result = D('Flowanalysis')->getAllChannels($id);
        $content = '没有查询到下属频道！';
        if(!empty($result['list'])){
            $this->assign('list',$result['list']);
            $this->assign('tag',$result['tag']);
            $content = $this->fetch("details");
        }
        $this->ajaxReturn(array('status'=>1, 'data'=>$content));
    }


}