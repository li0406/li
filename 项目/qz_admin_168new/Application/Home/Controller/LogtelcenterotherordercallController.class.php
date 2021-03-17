<?php

/**
* 签单审核通话录音
*/

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class LogtelcenterotherordercallController extends HomeBaseController
{
    public function index()
    {
        //获取部门
        $department = I('get.department');
        if ('1' == $department) {
            $department = array('5');
        } else if ('2' == $department) {
            $department = array('6');
        } else {
            $department = array('5', '6');
        }
        //获取订单ID
        $orderid = I('get.condition');

        $count = D('LogTelcenterOtherordercall')->LogTelcenterOtherordercallCountByDepartment($orderid, $department);
        import('Library.Org.Util.Page');
        $p = new \Page($count, 10);
        $vars['info']['page'] =  $p->show();
        $info = D('LogTelcenterOtherordercall')->LogTelcenterOtherordercallListByDepartment($orderid, $department, $p->firstRow, $p->listRows);

        //查看该记录是否有录音
        $callSid = array();
        foreach ($info as $key => $value) {
            $callSid[] = $value['callsid'];
        }
        if (!empty($callSid)) {
            $temp = D('LogTelcenter')->getLogTelcenterByCallSid($callSid);
            $record = array();
            foreach ($temp as $key => $value) {
                if ('0' === $value['byetype']) {
                    $record[$value['callsid']] = 1;
                }
            }
            foreach ($info as $key => $value) {
                if (!empty($record[$value['callsid']])) {
                    $info[$key]['record'] = 1;
                }
            }
        }

        $vars['info']['list'] = $info;
        $this->assign('vars', $vars);
        $this->display();
    }
}