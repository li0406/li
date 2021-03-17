<?php

//渠道来源统计

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class OrderlocationstatsController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
    }

    //首页
    public function index(){
        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        //开始时间和结束时间
        $start_time = I('get.start_time');
        if(!empty($start_time)){
            $date = strtotime($start_time);
            $start_time = mktime(0,0,0,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $start_time = mktime(0,0,0,date("m"),date("d"),date("Y"));
        }
        $end_time = I('get.end_time');
        if(!empty($end_time)){
            $date = strtotime($end_time);
            $end_time   = mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $end_time   = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }


        $info['start_time'] = date('Y-m-d',$start_time);
        $info['end_time'] = date('Y-m-d',$end_time);

        $info['start_times'] = $start_time;
        $info['end_times'] = $end_time;

        $info['set_start_time'] = date('Y-m-d',strtotime("-6 day"));
        $info['set_end_time'] = date('Y-m-d',time());

        $timeDiff = $end_time - $start_time;
        if($timeDiff > 1 && $timeDiff <= 86400){
            $timeNum = '1';
        }else{
            $timeNum = floor($timeDiff/86400);
        }

        if($timeNum > 31){
            $this->error('时间不能大于一个月');
        }

        $condition['time_real']  = array('between',array($start_time,$end_time));

        /*
        审核有效 on = 4
        单子类型: type_fw  0默认状态 1分 2问 3分没人跟 4问没人跟 问的不算单量
        分：1 或 3 增：2 或 4
        */

        $result = D("OrderSourceStats")->getOrderLocationList($condition);
        //dump($result);

        //订单量 分单量 有效分单
        $info['allCounts'] = $info['fendan'] = $info['youxiao'] = 0;

        foreach ($result as $k => $v) {
            //总分单量
            if($v['type_fw'] == '1'){
                $info['fendan'] = $info['fendan'] + $v['num'];
            }
            //总有效分单
            if($v['on'] == '4'){
                $info['youxiao'] = $info['youxiao'] + $v['num'];
            }
            $info['allCounts'] = $info['allCounts'] + $v['num'];

            //成生新的分组数据
            if(empty($group[$v['groupid']])){
                $group[$v['groupid']] = array('id' => $v['groupid'],'group_name' => $v['group_name'],);
            }
            $group[$v['groupid']]['sub'][] = $v;

            //合并每天数据
            if($timeNum >= '1'){
                if(empty($day[$v['days']][$v['groupid']])){
                    $day[$v['days']][$v['groupid']] = array('gid' => $v['groupid'],'group_name' => $v['group_name'],);
                }
                $day[$v['days']][$v['groupid']]['count'] = $day[$v['days']][$v['groupid']]['count'] + $v['num'];
            }
        }

        //取当前时段总数据
        $_allCountByDay = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allCountByDay as $key => $value) {
            $allCountByDay[$value['source']][$value['days']] = $value['num'];
        }
        S("Cache:OrderStats:allCountByDay:".session("uc_userinfo.id"),$allCountByDay,9000);

        //处理分组数据
        foreach ($group as $k => $v) {
            $subFendan = $subYouxiao = $subCount = 0;

            //处理子分组
            foreach ($v['sub'] as $key => $value) {

                if(empty($group[$k]['subGroup'][$value['source']])){
                    $group[$k]['subGroup'][$value['source']] = array(
                        'source' => $value['source'],
                        'source_name' => $value['sourcename'],
                    );
                }
                $group[$k]['subGroup'][$value['source']]['count'] = $group[$k]['subGroup'][$value['source']]['count'] + $value['num'];

                //分单量
                if($value['type_fw'] == '1'){
                    $subFendan = $subFendan + $value['num'];
                    $group[$k]['subGroup'][$value['source']]['fendan'] = $group[$k]['subGroup'][$value['source']]['fendan'] + $value['num'];
                }

                //有效分单
                if($value['on'] == '4'){
                    $subYouxiao = $subYouxiao + $value['num'];
                    $group[$k]['subGroup'][$value['source']]['youxiao'] = $group[$k]['subGroup'][$value['source']]['youxiao'] + $value['num'];
                }
                $subCount = $subCount + $value['num'];
            }

            $group[$k]['count'] = $subCount;
            $group[$k]['sub_youxiao'] = $subYouxiao;
            $group[$k]['sub_fendan'] = $subFendan;
            $group[$k]['sub_fendan_lv'] = round(($subFendan / $subCount) * 100, 2);
            $group[$k]['sub_youxiao_lv'] = round(($subYouxiao / $subCount) * 100, 2);

            foreach ($day as $keys => $values) {
                if(!empty($values[$v['id']])){
                    $group[$k]['days'][$keys] = $values[$v['id']]['count'];
                }else{
                    $group[$k]['days'][$keys] = 0;
                }
            }
            $group[$k]['dayData'] = implode($group[$k]['days'],',');
            //$group[$k]['subGroupCount'] = count($v['subGroup']);
        }

        $group = multi_array_sort($group,'count',SORT_DESC);
        //dump($group);

        S("Cache:OrderStats:LocationGroup:".session("uc_userinfo.id"),$group,9000);

        //有效订单率
        $info['youxiaolv'] =  round(($info['youxiao'] / $info['allCounts'])*100, 2);
        //分单率
        $info['fendanlv'] =  round(($info['fendan'] / $info['allCounts'])*100, 2);
        $this->assign('group',$group);
        $this->assign('info',$info);
        $this->assign('days',$day);
        $this->display('Orderstat/orderLocationStats');
    }

    //获取统计细节
    public function getStatsDetails(){
        $gid = I('get.gid');
        $type = I('get.subtype');

        $group = S("Cache:OrderStats:LocationGroup:".session("uc_userinfo.id"));
        //dump($group);
        if(empty($group)){
            $this->ajaxReturn(array('data'=>'','info'=>'本次数据已过期，请重新查看！','status'=>0));
        }
        if(!empty($gid)){
            $gid = str_replace('#group-','',$gid);
        }
        //取图表总量
        if($type == 'groupAllData'){
            foreach ($group as $key => $value) {
                if($value['id'] == $gid){
                    $dayList = array_keys($value['days']);
                    //dump($dayList);
                    $allCountByDay = S("Cache:OrderStats:allCountByDay:".session("uc_userinfo.id"));
                    //dump($allCountByDay);

                    foreach ($value['subGroup'] as $k => $v) {
                        $dayCount = '';
                        $data['item'][] = $v['source_name'];
                        $data['pieData'][] = array(
                              'value'=>$v['count'],
                              'name'=>$v['source_name'],
                        );
                        foreach ($dayList as $ks => $vs) {
                            if(!empty($allCountByDay[$v['source']][$vs])){
                                $dayCount[] = $allCountByDay[$v['source']][$vs];
                            }else{
                                $dayCount[] = '0';
                            }
                        }
                        $data['lineData'][]= array(
                            'name'=>$v['source_name'],
                            'type'=> 'line','smooth'=> 'true',
                            'data'=> $dayCount
                        );
                        $data['dayList'] = $dayList;
                    }

                    //dump($data);
                    $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
                    die;
                }
            }
        }
        //表格详细数据列表
        if($type == 'itemList'){
            foreach ($group as $key => $value) {
                if($value['id'] == $gid){
                    //dump($value);
                    foreach ($value['subGroup'] as $k => $v) {
                        $v['youxiao'] = empty($v['youxiao']) ? '0' : $v['youxiao'];
                        $v['fendan'] = empty($v['fendan']) ? '0' : $v['fendan'];
                        $html .= '<tr class="warning subList" ><td class="first-td">&nbsp;&nbsp;|—&nbsp;&nbsp;'.$v['source_name'].'</td>
                                <td>'.$v['count'].'</td>
                                <td>'.$v['youxiao'].'</td>
                                <td>'.round(($v['youxiao'] / $v['count']) * 100, 2).' %</td>
                                <td>'.$v['fendan'].'</td>
                                <td>'.round(($v['fendan'] / $v['count']) * 100, 2).' %</td>
                            </tr>';
                    }
                    $this->ajaxReturn(array('data'=>$html,'info'=>'成功','status'=>1));
                    die;
                }
            }
            $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据','status'=>0));
            die;
        }
    }

    //取图表子细节
    public function getSubDetails(){
        $gid = I('get.gid');
        $type = I('get.subtype');
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        $condition['time_real']  = array('between',array($start_time,$end_time));

        $group = S("Cache:OrderStats:LocationGroup:".session("uc_userinfo.id"));
        //dump($group);

        if(empty($group)){
            $this->ajaxReturn(array('data'=>'','info'=>'本次数据已过期，请重新查看！','status'=>0));
        }
        if(!empty($gid)){
            $gid = str_replace('#group-','',$gid);
        }

        //有效订单量
        if($type == 'youxiao'){
            if($gid == '0'){
                //总的有效订单量
                $condition['on']  = array('EQ',4);
                $data = $this->getAllYouXiaoCount($group,$condition);
            }else{
                $data = $this->getSubYouXiaoCount($group,$gid,$condition);
            }
            //dump($data);
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //有效订单率
        if($type == 'youxiaolv'){
            if($gid == '0'){
                //总的有效订单量
                $data = $this->getAllYouXiaoLv($group,$condition);
            }else{
                $data = $this->getSubYouXiaoLv($group,$gid,$condition);
            }
            //dump($data);
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //分单量
        if($type == 'fendan'){
            if($gid == '0'){
                //总的分单量
                $data = $this->getAllFendan($group,$condition);
            }else{
                $data = $this->getSubFendan($group,$gid,$condition);
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //分单率
        if($type == 'fendanlv'){
            if($gid == '0'){
                //总的分单率
                $data = $this->getAllFendanLv($group,$condition);
            }else{
                $data = $this->getSubFendanLv($group,$gid,$condition);
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }
    }


    //////////////////////////////////////////////////////////////////////////////////////////////

    /*
    审核有效 on = 4
    单子类型: type_fw  0默认状态 1分 2问 3分没人跟 4问没人跟 问的不算单量
    分：1 或 3 增：2 或 4
    */

    //有效订单量 总的
    private function getAllYouXiaoCount($group,$condition){
        $_allYouxiao = D("OrderSourceStats")->getOrderLoactionsAllByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['groupid']][$value['days']] = $allYouxiao[$value['groupid']][$value['days']] + $value['num'];
        }
        foreach ($group as $key => $value) {
            $dayList = array_keys($value['days']);
            $dayCount = '';
            $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
            $data['dayList'] = $dayList;
            $data['item'][] = $value['group_name'];
            $data['pieData'][] = array(
                  'value'=>$value['sub_youxiao'],
                  'name'=>$value['group_name'],
            );

            foreach ($dayList as $ks => $vs) {
                if(!empty($allYouxiao[$value['id']][$vs])){
                    $dayCount[] = $allYouxiao[$value['id']][$vs];
                }else{
                    $dayCount[] = '0';
                }
            }
            $data['lineData'][]= array(
                'name'=>$value['group_name'],
                'type'=> 'line','smooth'=> 'true',
                'data'=> $dayCount
            );
            $data['dayList'] = $dayList;
        }
        return $data;
    }

    //有效订单量 子分类
    private function getSubYouXiaoCount($group,$gid,$condition){
        $condition['on']  = array('EQ',4);
        $_allYouxiao = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['source']][$value['days']] = $value['num'];
        }

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['youxiao'] = empty($v['youxiao']) ? 0 : $v['youxiao'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value'=>$v['youxiao'],
                          'name'=>$v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allYouxiao[$v['source']][$vs])){
                            $dayCount[] = $allYouxiao[$v['source']][$vs];
                        }else{
                            $dayCount[] = '0';
                        }
                    }
                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //有效订单率 总的
    private function getAllYouXiaoLv($group,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderLoactionsAllByDay($condition);
        foreach ($_allCount as $key => $value) {
            $allCount[$value['groupid']][$value['days']] = $allCount[$value['groupid']][$value['days']] + $value['num'];
        }
        //dump($allCount);

        $condition['on']  = array('EQ',4);
        $_allYouxiao = D("OrderSourceStats")->getOrderLoactionsAllByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['groupid']][$value['days']] = $allYouxiao[$value['groupid']][$value['days']] + $value['num'];
        }
        //dump($allYouxiao);

        //dump($group);
        foreach ($group as $key => $value) {

            unset($value['sub']);
            unset($value['subGroup']);
            //dump($value);

            $dayList = array_keys($value['days']);
            $dayCount = '';
            $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
            $data['dayList'] = $dayList;
            $data['item'][] = $value['group_name'];
            $data['pieData'][] = array(
                  'value' => round(($value['sub_youxiao'] / $value['count']) * 100, 2),
                  'name' => $value['group_name'],
            );

            foreach ($dayList as $ks => $vs) {
                if(!empty($allYouxiao[$value['id']][$vs])){
                    $dayCount[] = round(($allYouxiao[$value['id']][$vs] / $allCount[$value['id']][$vs]) * 100, 2);
                }else{
                    $dayCount[] = '0';
                }
            }
            $data['lineData'][]= array(
                'name'=>$value['group_name'],
                'type'=> 'line','smooth'=> 'true',
                'data'=> $dayCount
            );
            $data['dayList'] = $dayList;
        }
        //dump($data);
        //die;
        return $data;
    }

    //有效订单率 子分类
    private function getSubYouXiaoLv($group,$gid,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allCount as $key => $value) {
            $allCount[$value['source']][$value['days']] = $value['num'];
        }

        $condition['on']  = array('EQ',4);
        $_allYouxiao = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['source']][$value['days']] = $value['num'];
        }

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['youxiao'] = empty($v['youxiao']) ? 0 : $v['youxiao'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value' => round(($v['youxiao'] / $v['count']) * 100, 2),
                          'name' => $v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allYouxiao[$v['source']][$vs])){
                            $dayCount[] = round(($allYouxiao[$v['source']][$vs] / $allCount[$v['source']][$vs]) * 100, 2);
                        }else{
                            $dayCount[] = '0';
                        }
                    }

                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //分单量 总的
    private function getAllFendan($group,$condition){
        $condition['type_fw']  = array('EQ',1);
        //dump($condition);
        $_allYouxiao = D("OrderSourceStats")->getOrderLoactionsAllByDay($condition);
        //dump($_allYouxiao);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['groupid']][$value['days']] = $allYouxiao[$value['groupid']][$value['days']] + $value['num'];
        }
        //dump($group);
        foreach ($group as $key => $value) {
            //dump($value);
            $dayList = array_keys($value['days']);
            $dayCount = '';
            $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
            $data['dayList'] = $dayList;
            $data['item'][] = $value['group_name'];
            $data['pieData'][] = array(
                  'value'=>$value['sub_fendan'],
                  'name'=>$value['group_name'],
            );
            unset($value['sub']);

            foreach ($dayList as $ks => $vs) {
                if(!empty($allYouxiao[$value['id']][$vs])){
                    $dayCount[] = $allYouxiao[$value['id']][$vs];
                }else{
                    $dayCount[] = '0';
                }
            }
            $data['lineData'][]= array(
                'name'=>$value['group_name'],
                'type'=> 'line','smooth'=> 'true',
                'data'=> $dayCount
            );
            $data['dayList'] = $dayList;
        }
        return $data;
    }

    //分单量 子分类
    private function getSubFendan($group,$gid,$condition){
        $condition['type_fw']  = array('EQ',1);
        $_allYouxiao = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['source']][$value['days']] = $value['num'];
        }
        //dump($allYouxiao);

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['fendan'] = empty($v['fendan']) ? 0 : $v['fendan'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value'=>$v['fendan'],
                          'name'=>$v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allYouxiao[$v['source']][$vs])){
                            $dayCount[] = $allYouxiao[$v['source']][$vs];
                        }else{
                            $dayCount[] = '0';
                        }
                    }
                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //分单率 总的
    private function getAllFendanLv($group,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderLoactionsAllByDay($condition);
        foreach ($_allCount as $key => $value) {
            $allCount[$value['groupid']][$value['days']] = $allCount[$value['groupid']][$value['days']] + $value['num'];
        }
        //dump($allCount);

        $condition['type_fw']  = array('EQ',1);
        $_allFendan = D("OrderSourceStats")->getOrderLoactionsAllByDay($condition);
        foreach ($_allFendan as $key => $value) {
            $allFendan[$value['groupid']][$value['days']] = $allFendan[$value['groupid']][$value['days']] + $value['num'];
        }
        //dump($allFendan);

        //dump($group);
        foreach ($group as $key => $value) {

            unset($value['sub']);
            unset($value['subGroup']);
            //dump($value);

            $dayList = array_keys($value['days']);
            $dayCount = '';
            $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
            $data['dayList'] = $dayList;
            $data['item'][] = $value['group_name'];
            $data['pieData'][] = array(
                  'value' => round(($value['sub_fendan'] / $value['count']) * 100, 2),
                  'name' => $value['group_name'],
            );

            foreach ($dayList as $ks => $vs) {
                if(!empty($allFendan[$value['id']][$vs])){
                    $dayCount[] = round(($allFendan[$value['id']][$vs] / $allCount[$value['id']][$vs]) * 100, 2);
                }else{
                    $dayCount[] = '0';
                }
            }
            $data['lineData'][]= array(
                'name'=>$value['group_name'],
                'type'=> 'line','smooth'=> 'true',
                'data'=> $dayCount
            );
            $data['dayList'] = $dayList;
        }
        return $data;
    }

    //分单率 子分类
    private function getSubFendanLv($group,$gid,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allCount as $key => $value) {
            $allCount[$value['source']][$value['days']] = $value['num'];
        }

        $condition['type_fw']  = array('EQ',1);
        $_allFendan = D("OrderSourceStats")->getOrderLoactionsByDay($condition);
        foreach ($_allFendan as $key => $value) {
            $allFendan[$value['source']][$value['days']] = $value['num'];
        }

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    //dump($v);
                    $dayCount = '';
                    $v['fendan'] = empty($v['fendan']) ? 0 : $v['fendan'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value' => round(($v['fendan'] / $v['count']) * 100, 2),
                          'name' => $v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allFendan[$v['source']][$vs])){
                            $dayCount[] = round(($allFendan[$v['source']][$vs] / $allCount[$v['source']][$vs]) * 100, 2);
                        }else{
                            $dayCount[] = '0';
                        }
                    }

                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }



}