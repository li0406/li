<?php

/**
 * 工作汇报控制器
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-10 16:26:27
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-05-21 14:57:47
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\ReportWorkLogic;
use app\index\validate\ReportWork as ReportWorkValidate;

class ReportWork extends Controller {
      
    /**
     * 工作汇报列表
     * @param int       $page         [选填]分页页码（默认：1）
     * @param int       $page_size    [选填]每页显示条数（默认：20）
     * @param string    $user_search  [选填]汇报人关键字
     * @param string    $date_begin   [选填]开始日期
     * @param string    $date_end     [选填]结束日期
     * @Author   liuyupeng
     * @DateTime 2019-05-10T16:26:56+0800
     */
    public function rwList(Request $request, ReportWorkLogic $reportWorkLogic){
        $page = $request->get('page', 1, 'intval');
        $page_size = $request->param('page_size', 20, 'intval');

        $date_begin = $request->param('date_begin', '', 'trim');
        $date_end = $request->param('date_end', '', 'trim');

        // 不传日期时默认查询30天
        if (empty($date_begin) && empty($date_end)) {
            $date_begin = date('Y-m-d', strtotime('-30 days'));
            $date_end = date('Y-m-d');
        }

        $data = $reportWorkLogic->getRwPageList([
                'user_search' => $request->param('user_search', '', 'trim'),
                'date_begin' => $date_begin,
                'date_end' => $date_end
            ], $page, $page_size);

        $response = sys_response(0, '', [
                'list' => $data['list'],
                'page' => sys_paginate($data['count'], $page_size, $page),
                'query' => [
                    'date_begin' => $date_begin,
                    'date_end' => $date_end
                ]
            ]);

        return json($response);
    }

    /**
     * 获取工作汇报详情
     * @param int  $id     [必填]工作汇报ID
     * @Author   liuyupeng
     * @DateTime 2019-05-13T14:17:19+0800
     */
    public function rwDetail(Request $request, ReportWorkLogic $reportWorkLogic){
        $id = $request->param('id');
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $response = $reportWorkLogic->getRwDetail($id);
        return json($response);
    }

    /**
     * 工作汇报新增
     * @param string    $date           [必填]日期（格式：2019-05-10）
     * @param string    $content        [必填]工作内容
     * @param string    $question       [选填]遇到问题
     * @param string    $solution       [选填]解决方案
     * @param string    $assist         [选填]需求协助
     * @param string    $plan           [选填]明日计划
     * @Author   liuyupeng
     * @DateTime 2019-05-10T17:07:07+0800
     */
    public function rwAdd(Request $request, ReportWorkLogic $reportWorkLogic){
        // 当前登录用户（发布人）
        $user = $this->request->user;

        $data = [
            'uid' => $this->request->user['user_id'],
            'date' => $this->request->post('date'),
            'content' => $this->request->post('content'),
            'question' => $this->request->post('question', ''),
            'solution' => $this->request->post('solution', ''),
            'assist' => $this->request->post('assist', ''),
            'plan' => $this->request->post('plan', '')
        ];

        // 失败后的返回数据（前端需求失败后返回当前日期）
        $return = ['date' => date('Y-m-d')];

        // 验证
        $rwValidate = new ReportWorkValidate();
        if ($rwValidate->scene('add')->check($data) == false) {
            return json(sys_response(4000002, $rwValidate->getError(), $return));
        }

        // 验证日期是否是当天日期
        if ($data['date'] != $return['date']) {
            return json(sys_response(4000002, '发布日期和当前日期不符', $return));
        }

        // 保存
        if ($reportWorkLogic->saveRwData($data) == false) {
            return json(sys_response(5000002, '保存失败', $return));
        }

        return json(sys_response(0, '保存成功'));
    }


    /**
     * 工作汇报回复
     * @param int       $id           [必填]工作汇报ID
     * @param string    $reply_text   [必填]回复内容
     * @Author   liuyupeng
     * @DateTime 2019-05-13T10:13:27+0800
     */
    public function rwReply(Request $request, ReportWorkLogic $reportWorkLogic){
        $id = $request->post('id');
        $reply_text = $request->post('reply_text');

        if (empty($id)) {
            return json(sys_response(4000002));
        } else if (empty($reply_text)) {
            return json(sys_response(4000002, '请先填写回复内容'));
        }

        $response = $reportWorkLogic->saveRwReply($id, $reply_text);
        return json($response);
    }

}