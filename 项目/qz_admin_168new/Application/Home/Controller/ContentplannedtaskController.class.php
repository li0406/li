<?php

//内容自动发布计划任务

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class ContentplannedtaskController extends HomeBaseController{

    public function index()
    {
        /*S-获取所有可用规则*/
        $together = array();
        //日常规则
        $map = array(
            'status' => 1,
            'start_day' => '00-00-00',
            'end_day' => '00-00-00'
        );
        $routine = M('content_planned_task')->where($map)->select();
        if (!empty($routine)) {
            $routineIdArray = array();
            foreach ($routine as $key => $value) {
                $routineIdArray[] = $value['id'];
            }
            $map = array('content_planned_task_id' => array('IN', $routineIdArray));
            $tiemRange = M('content_planned_task_info')->where($map)->select();
            foreach ($routine as $key => $value) {
                $together['routine'][$value['type']] = $value;
            }
            foreach ($tiemRange as $key => $value) {
                foreach ($together['routine'] as $k => $v) {
                    if ($value['content_planned_task_id'] == $v['id']) {
                        $together['routine'][$k]['tiemRange'][] = $value;
                    }
                }
            }
        }
        //特殊规则
        $map = array(
            'status' => 1,
            'start_day' => array('NEQ', '00-00-00'),
            'end_day' => array('NEQ', '00-00-00'),
        );
        $special = M('content_planned_task')->where($map)->select();
        if (!empty($special)) {
            $routineIdArray = array();
            foreach ($special as $key => $value) {
                $routineIdArray[] = $value['id'];
            }
            $map = array('content_planned_task_id' => array('IN', $routineIdArray));
            $tiemRange = M('content_planned_task_info')->where($map)->select();
            foreach ($special as $key => $value) {
                $together['special'][$value['type']] = $value;
            }
            foreach ($tiemRange as $key => $value) {
                foreach ($together['special'] as $k => $v) {
                    if ($value['content_planned_task_id'] == $v['id']) {
                        $together['special'][$k]['tiemRange'][] = $value;
                    }
                }
            }
        }
        /*S-获取所有可用规则*/

        /*S-获取主站文章设置*/
        $wwwarticle = array(
            'routine' => $together['routine'][1],
            'special' => $together['special'][1],
        );
        //今日总发布文章
        $map = array(
            'init_state' => 3,
            'addtime' => array(
                array('EGT', strtotime(date('Y-m-d'))),
                array('LT', strtotime(date('Y-m-d')) + 86400)
            )
        );
        $wwwarticle['count'] = M('www_article')->where($map)->count();
        $vars['wwwarticle'] = $wwwarticle;
        /*S-获取主站文章设置*/

        /*S-获取家居美图设置*/
        $meitu = array(
            'routine' => $together['routine'][2],
            'special' => $together['special'][2],
        );
        //获取美图未发布数据总数
        $map = array(
            'state' => 2,
            'type'=>2,
        );
        $meitu['count'] = M('tu')->where($map)->count();
        $vars['meitu'] = $meitu;
        /*E-获取家居美图设置*/

        /*S-获取公装美图设置*/
        $pubmeitu = array(
            'routine' => $together['routine'][3],
            'special' => $together['special'][3],
        );
        //获取美图未发布数据总数
        $map = array(
            'state' => 2,
            'type'=>1,
        );
        $pubmeitu['count'] = M('tu')->where($map)->count();
        $vars['pubmeitu'] = $pubmeitu;
        /*E-获取公装美图设置*/

        /*S-获取3D效果图设置*/
        $threedimensionxiaoguotu = array(
            'routine' => $together['routine'][4],
            'special' => $together['special'][4],
        );
        //获取美图未发布数据总数
        $map = array(
            'status' => 3
        );
        $threedimensionxiaoguotu['count'] = M('xiaoguotu_threedimension')->where($map)->count();
        $vars['threedimensionxiaoguotu'] = $threedimensionxiaoguotu;
        /*E-获取3D效果图设置*/

        /*S-获取美图专题设置*/
        $meituzt = array(
            'routine' => $together['routine'][5],
            'special' => $together['special'][5],
        );
        //获取美图未发布数据总数
        $map = array(
            'status' => 3
        );
        $meituzt['count'] = M('meitu_zt')->where($map)->count();
        $vars['meituzt'] = $meituzt;
        /*E-获取美图专题设置*/

        /*S-获取美图专题设置*/
        $ask = array(
            'routine' => $together['routine'][6],
            'special' => $together['special'][6],
        );
        //获取问答未发布数据总数
        $map = array(
            'visible' => 3
        );
        $ask['count'] = M('ask')->where($map)->count();
        $vars['ask'] = $ask;
        /*E-获取美图专题设置*/

        //获取小时分钟
        $time = array();
        for ($i = 0; $i <= 23; $i++) {
            $time['h'][] = ($i < 10) ? ('0' . $i) : (string)$i;
        }
        for ($i = 0; $i <= 59; $i++) {
            $time['m'][] = ($i < 10) ? ('0' . $i) : (string)$i;
        }
        $vars['time'] = $time;
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 主站文章保存
     */
    public function wwwarticle()
    {
        $data = I('post.');

        //解析日期和各个时间范围比例数据
        $result = $this->getDayAndTimeRange($data['specialDate'], $data['range']);
        //判断是否出现错误
        if (!empty($result['errorInfo'])) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result['errorInfo']));
        }

        //保存数据
        $result = $this->saveDayAndTimeRange('1', $result['day'], '', $result['timeRange']);
        //判断是否出现错误
        if (true !== $result) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result));
        }

        $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
    }

    /**
     * 家居美图保存
     */
    public function meitu()
    {
        $data = I('post.');

        if (intval($data['param']) < 1) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请填写每日发布数，该值必须大于0'));
        }

        //解析日期和各个时间范围比例数据
        $result = $this->getDayAndTimeRange($data['specialDate'], $data['range']);
        //判断是否出现错误
        if (!empty($result['errorInfo'])) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result['errorInfo']));
        }

        //保存数据
        $result = $this->saveDayAndTimeRange('2', $result['day'], intval($data['param']), $result['timeRange']);
        //判断是否出现错误
        if (true !== $result) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result));
        }

        $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
    }

    /**
     * 公装美图保存
     */
    public function pubMeitu()
    {
        $data = I('post.');

        if (intval($data['param']) < 1) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请填写每日发布数，该值必须大于0'));
        }

        //解析日期和各个时间范围比例数据
        $result = $this->getDayAndTimeRange($data['specialDate'], $data['range']);
        //判断是否出现错误
        if (!empty($result['errorInfo'])) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result['errorInfo']));
        }

        //保存数据
        $result = $this->saveDayAndTimeRange('3', $result['day'], intval($data['param']), $result['timeRange']);
        //判断是否出现错误
        if (true !== $result) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result));
        }

        $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
    }

    /**
     * 3D效果图保存
     */
    public function threedimensionxiaoguotu()
    {
        $data = I('post.');

        if (intval($data['param']) < 1) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请填写每日发布数，该值必须大于0'));
        }

        //解析日期和各个时间范围比例数据
        $result = $this->getDayAndTimeRange($data['specialDate'], $data['range']);
        //判断是否出现错误
        if (!empty($result['errorInfo'])) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result['errorInfo']));
        }

        //保存数据
        $result = $this->saveDayAndTimeRange('4', $result['day'], intval($data['param']), $result['timeRange']);
        //判断是否出现错误
        if (true !== $result) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result));
        }

        $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
    }

    /**
     * 美图专题保存
     */
    public function meituzt()
    {
        $data = I('post.');

        if (intval($data['param']) < 1) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请填写每日发布数，该值必须大于0'));
        }

        //解析日期和各个时间范围比例数据
        $result = $this->getDayAndTimeRange($data['specialDate'], $data['range']);
        //判断是否出现错误
        if (!empty($result['errorInfo'])) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result['errorInfo']));
        }

        //保存数据
        $result = $this->saveDayAndTimeRange('5', $result['day'], intval($data['param']), $result['timeRange']);
        //判断是否出现错误
        if (true !== $result) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result));
        }

        $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
    }

    /**
     * 问答保存
     */
    public function ask()
    {
        $data = I('post.');

        if (intval($data['param']) < 1) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请填写每日发布数，该值必须大于0'));
        }

        //解析日期和各个时间范围比例数据
        $result = $this->getDayAndTimeRange($data['specialDate'], $data['range']);
        //判断是否出现错误
        if (!empty($result['errorInfo'])) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result['errorInfo']));
        }

        //保存数据
        $result = $this->saveDayAndTimeRange('6', $result['day'], intval($data['param']), $result['timeRange']);
        //判断是否出现错误
        if (true !== $result) {
            $this->ajaxReturn(array('status'=>0, 'info'=>$result));
        }

        $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
    }

    /**
     * 解析日期和各个时间范围比例数据
     * @param  array  $specialDate 特殊日期
     * @param  array  $range       时间范围
     */
    public function getDayAndTimeRange($specialDate = array(), $range = array()){
        if (!empty($specialDate)) {
            $day['start'] = $specialDate['0'];
            $day['end'] = $specialDate['1'];
            if (empty($day['start']) || empty($day['end'])) {
                return array('errorInfo' => '请填写特殊日期的开始日期，结束日期');
            } else if (strtotime($day['start']) > strtotime($day['end'])) {
                return array('errorInfo' => '特殊日期的开始日期不能大于结束日期');
            }
        } else {
            $day['start'] = $day['end'] = '00-00-00';
        }

        if (empty($range)) {
            return array('errorInfo' => '缺失时间范围参数');
        }

        $rateSum = 0;
        //获取时间段开始时间结束时间
        $temp = array();
        foreach ($range as $key => $value) {
            if (empty($value['4'])) {
                $errorInfo = '时间段[' . $value['0'] . ':' . $value['1'] . ' - ' . $value['2'] . ':' . $value['3'] . ']' . '的占比为空';
                return array('errorInfo' => $errorInfo);
                break;
            }
            $rateSum = $rateSum + $value['4'];
            $temp[] = array(
                'start' => strtotime(date("Y-m-d ") . $value['0'] . ':' . $value['1']),
                'end' => strtotime(date("Y-m-d ") . $value['2'] . ':' . $value['3']),
                'param' => $value
            );
        }
        if ($rateSum != 100) {
            return array('errorInfo' => '各时间段发布占比总和不等于100%');
        }

        //重新排序
        $timeRange = multi_array_sort($temp, 'start');

        //判断时间间隔是否一致
        foreach ($timeRange as $key => $value) {
            //如果开始时间大于等于结束时间
            if ($value['start'] >= $value['end']) {
                $errorInfo = '时间段['.date('H:i', $value['start']).' - '.date('H:i', $value['end']).']'.'开始时间必需小于结束时间';
                return array('errorInfo' => $errorInfo);
                break;
            }
            //如果当前时间段的开始时间小于前一个时间段的结束时间
            if (!empty($timeRange[$key-1])) {
                if ($value['start'] < $timeRange[$key-1]['end']) {
                    $errorInfo = '时间段['.date('H:i', $value['start']).' - '.date('H:i', $value['end']).']'.'与时间段['.date('H:i', $timeRange[$key-1]['start']).' - '.date('H:i', $timeRange[$key-1]['end']).']'.'存在时间交叉';
                    return array('errorInfo' => $errorInfo);
                    break;
                }
            }
        }
        return array('day' => $day, 'timeRange' => $timeRange, 'errorInfo' => '');
    }

    /**
     * 保存计划任务数据
     * @param  integer $type      类型
     * @param  array   $day       时间
     * @param  string  $param     计划任务主表参数
     * @param  array   $timeRange 时间范围
     */
    public function saveDayAndTimeRange($type = 0, $day = array(), $param = '', $timeRange = array()){

        if (empty($type)) {
            return '缺少类别参数';
        }

        //写入数据库
        if ('00-00-00' == $day['start'] && '00-00-00' == $day['end']) {
            //禁用之前的数据
            M('content_planned_task')->where(array(
                'type' => $type,
                'start_day' => '00-00-00',
                'end_day' => '00-00-00',
            ))->save(array('status' => '2'));
        } else {
            M('content_planned_task')->where(array(
                'type' => $type,
                'start_day' => array('NEQ', '00-00-00'),
                'end_day' => array('NEQ', '00-00-00')
            ))->save(array('status' => '2'));
        }
        //新增日期主表
        $id = M('content_planned_task')->add(array(
            'type' => $type,
            'adminuser_id' => getAdminUser('id'),
            'start_day' => $day['start'],
            'end_day' => $day['end'],
            'param' => $param,
            'status' => '1',
            'add_time' => date('Y-m-d H:i:s')
        ));
        //新增各个时间短的数据
        $save = array();
        foreach ($timeRange as $key => $value) {
            $save[] = array(
                'content_planned_task_id' => $id,
                'start_h' => $value['param'][0],
                'start_m' => $value['param'][1],
                'end_h' => $value['param'][2],
                'end_m' => $value['param'][3],
                'param' => $value['param'][4],
                'add_time' => date('Y-m-d H:i:s')
            );
        }
        M('content_planned_task_info')->addAll($save);
        return true;
    }
}