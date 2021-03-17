<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class QckindlyremindController extends HomeBaseController{

    /**
     * [index 质检温馨提示]
     * @return [type] [description]
     */
    public function index()
    {
        $publish_time_end = I('get.publish_time_end');
        if (!empty($publish_time_end)) {
            $publish_time_end = strtotime($publish_time_end);
        }
        $main['info'] = $this->getQcKindlyRemindList(0, '', '', $publish_time_end);
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * [operate 新增温馨提示记录]
     * @return [type] [description]
     */
    public function operate()
    {
        $content = I('post.content');
        if (empty($content)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请填写温馨提示内容'));
        }
        $save = array(
            'content'      => $content,
            'publish_time' => time()
        );
        $result = D('QcKindlyRemind')->addQcKindlyRemind($save);
        if ($result) {
            $this->ajaxReturn(array('status'=>1));
        }
        $this->ajaxReturn(array('status'=>0, 'info'=>'操作失败'));
    }

    /**
     * [getQcKindlyRemindList 获取温馨提示列表以及分页]
     * @param  integer $id                 [记录ID]
     * @param  string  $content            [记录内容]
     * @param  string  $publish_time_start [发布开始时间]
     * @param  string  $publish_time_end   [发布结束时间]
     * @param  integer $each               [每页显示数量]
     * @return [type]                      [description]
     */
    public function getQcKindlyRemindList($id, $content, $publish_time_start, $publish_time_end, $each = 10)
    {
        import('Library.Org.Util.Page');
        $count = D('QcKindlyRemind')->getQcKindlyRemindCount($id, $content, $publish_time_start, $publish_time_end);
        $Page  = new \Page($count,$each);
        if ($count > $each) {
            $result['page'] = $Page->show();
        }
        $result['list'] = D('QcKindlyRemind')->getQcKindlyRemindList($id, $content, $publish_time_start, $publish_time_end, $Page->firstRow,$Page->listRows);
        return $result;
    }
}