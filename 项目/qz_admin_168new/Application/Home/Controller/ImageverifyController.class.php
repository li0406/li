<?php

/**
 * 综合图片审核
 */

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Service\MsgServiceModel;
use Common\Enums\MsgTemplateCodeEnum;

class ImageVerifyController extends HomeBaseController
{

    //综合图片审核原因列表
    protected $resonList =[
        '1' => [
            'class' => 'has-contact',
            'content'  => '含有广告（联系方式、网址等）',
        ],
        '2' => [
            'class' => 'not-just',
            'content'  => '不符合要求的logo、水印（如使用禁用的logo、水印或logo过大）',
        ],
        '3' => [
            'class' => 'not-match',
            'content'  => '与图片主题不符',
        ],
        '4' => [
            'class' => 'low-quality',
            'content'  => '质量差（图片模糊不清、尺寸比例严重变形等）',
        ],
        '5' => [
            'class' => 'other-input',
            'content'  => '其他原因',
        ],
    ];

    /**
     * 案例列表页
     */
    public function index()
    {
        $cid = I('get.cid');
        $cs = I('get.cs');
        $des_id = I('get.des_id');
        $com_id = I('get.com_id');
        $status = I('get.status');
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        $number = 30;

        if ($status == '' || $status === null) {
            $status = 1;
        }

        $time_start = empty($time_start) ? '' : strtotime($time_start);
        $time_end = empty($time_end) ? '' : strtotime($time_end);

        $info = $this->getCasesList($cid, $cs, $des_id, $com_id, $status, $time_start, $time_end, $number);

        $this->assign('citys', D('Quyu')->getQuyuList());
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 获取案例所有信息
     */
    public function getCaseInfo()
    {
        $id = intval(I('get.id'));
        $imgid = I('get.imgid');
        $status = I('get.status');

        if (empty($imgid)) {
            $imgid = 0;
        }

        $info['caseList'] = D('ImageVerify')->getCaseInfoById($id, $status);
        if (empty($info['caseList'])) {
            $this->_error();
        }

        if (!empty($imgid)) {
            foreach ($info['caseList'] as $key => $v) {
                if ($v['imgid'] == $imgid) {
                    $info['case'] = $info['caseList'][$key];
                }
            }
        } else {
            $info['case'] = $info['caseList'][$imgid];
        }
        //此处导致查询图片失败
        if ($status == '' || $status === null) {
            $status = 1;
        }

        $cs = I('get.cs');
        $des_id = I('get.des_id');
        $com_id = I('get.com_id');
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        $time_start = empty($time_start) ? '' : strtotime($time_start);
        $time_end = empty($time_end) ? '' : strtotime($time_end);

        //获取当前案例的上一个案例和下一个案例
        $info['caseNav']['prev'] = D('ImageVerify')->getPrevCase($id, $cs, $des_id, $com_id, $status, $time_start, $time_end)['id'];
        $info['caseNav']['next'] = D('ImageVerify')->getNextCase($id, $cs, $des_id, $com_id, $status, $time_start, $time_end)['id'];

        //案例具体信息
        $caseInfo['id'] = $info['caseList'][0]['id'];
        $caseInfo['on'] = $info['caseList'][0]['caseon'];
        $caseInfo['title'] = $info['caseList'][0]['title'];
        $caseInfo['time'] = $info['caseList'][0]['time'];
        $caseInfo['cs'] = $info['caseList'][0]['cs'];
        $caseInfo['cname'] = $info['caseList'][0]['cname'];

        //$info['caseInfo'] = D('ImageVerify')->getCaseById($id);
        $info['caseInfo'] = $caseInfo;

        $this->assign('info', $info);
        $this->assign('reson_list', $this->resonList);
        $this->display('imgBoxTpl');
    }

    /**
     * 获取单张图片审核记录.
     */
    public function getImgReview()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array("info" => "数据错误！", "status" => 0));
        }
        $review = D('ImageVerify')->getReviewByImgId($id);
        $caseImg = D('ImageVerify')->getCaseImg($id);

        $review['imgStatus'] = $caseImg['status'];

        if (!empty($review)) {
            $review['appeal_time'] = date('Y-m-d H:i', $review['appeal_time']);
            $review['status'] = array_filter(explode(',', $review['status']));
        }
        $this->ajaxReturn(array('data' => $review, "status" => 1));
    }


    /**
     * 设置案例评分
     */
    public function setCaseGrade()
    {
        $id = I('post.id');
        $grade = I('post.grade');

        if (empty($id) || empty($grade)) {
            $this->ajaxReturn(array("info" => "数据错误！", "status" => 0));
        }
        $result = D('ImageVerify')->setCaseGrade($id, $grade);
        if ($result !== false) {
            $this->ajaxReturn(array("info" => "设置成功！", "status" => 1));
        }
        $this->ajaxReturn(array("info" => "设置失败！", "status" => 0));
    }

    /**
     * 设置案例图片审核状态
     */
    public function setCaseImgStatus()
    {
        $id = I('post.id');
        $status = I('post.status');
        $reasonId = I('post.reason');
        $otherReason = I('post.otherReason');
        $caseId = I('post.caseid');
        if (empty($id) || empty($status)) {
            $this->ajaxReturn(array("info" => "数据错误！", "status" => 0));
        }
        D('ImageVerify')->setCaseImgStatus($id, $status);

        //获取图片信息
        $imgInfo = D('ImageVerify')->getCaseImg($id);
        if (empty($imgInfo)) {
            $this->ajaxReturn(array("info" => '审核的图片不存在', "status" => 0));
        }
        //获取案例信息
        $caseInfo = D('ImageVerify')->getCaseById($imgInfo['caseid']);
        if (empty($caseInfo)) {
            $this->ajaxReturn(array("info" => '审核图片没有所属案例', "status" => 0));
        }
        $where = [
            'caseid' => $imgInfo['caseid'],
            'status' => ['neq', 2],
        ];
        $imgInfoCount = D('ImageVerify')->getCaseInfoCount($where);
        //所有案例图片通过,该案例也通过审核
        if ($imgInfoCount == 0) {
            D('ImageVerify')->setCasesOn($imgInfo['caseid'], 1);
            //同时修改装修公司案例数字段
            $case_count = D('ImageVerify')->getCaseCount($caseInfo['uid']);
            D('user')->editCompanyInfo($caseInfo['uid'], ['case_count' => $case_count]);
        }
        $where = [
            'caseid' => $imgInfo['caseid'],
            'status' => ['eq', 3],
        ];
        $imgInfoNotCount = D('ImageVerify')->getCaseInfoCount($where);
        $where = [
            'caseid' => $imgInfo['caseid'],
        ];
        $imgInfoAllCount = D('ImageVerify')->getCaseInfoCount($where);
        //所有案例图片不通过,该案例也不通过审核
        if ($imgInfoNotCount == $imgInfoAllCount) {
            D('ImageVerify')->setCasesOn($imgInfo['caseid'], 2);
            D('ImageVerify')->setCaseStatus($imgInfo['caseid'], 3);
            //同时修改装修公司案例数字段
            $case_count = D('ImageVerify')->getCaseCount($caseInfo['uid']);
            D('user')->editCompanyInfo($caseInfo['uid'], ['case_count' => $case_count]);
        }else{
            D('ImageVerify')->setCasesOn($imgInfo['caseid'], 1);
            D('ImageVerify')->setCaseStatus($imgInfo['caseid'], 2);
            //同时修改装修公司案例数字段
            $case_count = D('ImageVerify')->getCaseCount($caseInfo['uid']);
            D('user')->editCompanyInfo($caseInfo['uid'], ['case_count' => $case_count]);
        }
        if (empty($reasonId) && $status == 2) {
            $data['status'] = '';
        } elseif(!empty($reasonId) && $status != 2 ) {
            $data['status'] = implode(',', $reasonId);
        } else {
            $data['status'] = null;
        }

        if (!empty($otherReason) && $status == 2) {
            $data['reason'] = '';
        } elseif(!empty($reasonId) && $status != 2 ) {
            $data['reason'] = $otherReason;
        } else {
            $data['reason'] = null;
        }
        if (!empty($data)) {
            $data['caseid'] = I('post.caseid');
            $data['review_time'] = time();
            $isReview = D('ImageVerify')->getCaseImgReview($id);
            if ($isReview) {
                D('ImageVerify')->setCaseImgReview($id, $data);
            } else {
                $data['imgid'] = $id;
                M('case_review')->add($data);
            }
        }

        if ($status == '3') {
            if ($caseInfo['id'] == $caseId) {
                $this->sendMessage($caseInfo['uid'], $caseInfo, $id);
            }

            $msgService = new MsgServiceModel();
            $template_id = MsgTemplateCodeEnum::EXAM_CASE_FAIL;
            $linkparam = "?active_name=case";
            $msgService->sendCompanyMsg($template_id, $caseInfo["uid"], $linkparam, $id, "案例图片审核");
            $msgService->sendCompanyMsg($template_id, $caseInfo["uid"], $linkparam, $id, "案例图片审核", [], $caseInfo["userid"]);
        }
        //暂不检查是否操作成功
        $this->ajaxReturn(array("info" => "操作成功！", "status" => 1));
    }

    /**
     * 设置案例审核状态
     */
    public function setCaseStatus()
    {
        $id = I('post.id');
        $status = I('post.status');
        if (empty($id) || empty($status)) {
            $this->ajaxReturn(array("info" => "数据错误！", "status" => 0));
        }

        $result = D('ImageVerify')->setCaseStatus($id, $status);

        if ($result !== false) {
            //如果状态为 3 审核不通过
            if ($status == '3') {
                D('ImageVerify')->setCaseOn($id, '2');
            } else {
                D('ImageVerify')->setCaseOn($id, '1');
            }

            D('ImageVerify')->setCaseImgStatusByCaseId($id, $status);

            $case = D('Cases')->getCaseById($id);

            // QZMSG 发送消息提醒
            if ($status == 3 && !empty($case)) {
                $msgService = new MsgServiceModel();
                $template_id = MsgTemplateCodeEnum::EXAM_CASE_FAIL;
                $linkparam = "?active_name=case";
                $msgService->sendCompanyMsg($template_id, $case["uid"], $linkparam, $id, "案例审核");
                $msgService->sendCompanyMsg($template_id, $case["uid"], $linkparam, $id, "案例审核", [], $case["userid"]);
            }

            $this->ajaxReturn(array("info" => "操作成功！", "status" => 1));
        }
        $this->ajaxReturn(array("info" => "操作失败！", "status" => 0));
    }

    /**
     * Gets the cases list.
     * @param      <type>   $cid         The case id
     * @param      <type>   $cs          The city
     * @param      <type>   $des_id      The designer identifier
     * @param      <type>   $com_id      The company identifier
     * @param      <type>   $status      The status
     * @param      <type>   $time_start  The time start
     * @param      <type>   $time_end    The time end
     * @param      <type>   $num         The number of pages per page
     * @return     <type>   The cases list.
     */
    public function getCasesList($cid, $cs, $des_id, $com_id, $status, $time_start, $time_end, $num = 24)
    {
        $count = D("ImageVerify")->getCasesCount($cid, $cs, $des_id, $com_id, $status, $time_start, $time_end);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $num);
            $page = $p->show();
            $list = D("ImageVerify")->getCasesList($cid, $cs, $des_id, $com_id, $status, $time_start, $time_end, $p->firstRow, $p->listRows);
            return array("list" => $list, "page" => $page);
        }
        return null;
    }

    /**
     * 向上传用户发送通知
     * @param int   $uid    用户ID
     * @param array $case   案例信息
     * @param int   $imgid  案例具体图片信息
     * @return mixed
     */
    public function sendMessage($uid, $case, $imgid)
    {
        $user = M('user')->field('*')->where(array('id' => $uid))->find();
        $img = D('ImageVerify')->getCaseImg($imgid);
        $review = D('ImageVerify')->getReviewByImgId($imgid);
        $status = [];
        if(!empty($review['status'])){
            $_status = array_filter(explode(',',$review['status']));
            foreach ($_status as $k => $v) {
                if(!empty($this->resonList[$v]['content'])){
                    $status[] = $this->resonList[$v]['content'];
                }
            }
        }
        $reason = '';
        if(!empty($status) && !empty($review['reason'])){
            $reason = '● 原因：'.implode('，',$status).'，'.$review['reason'];
        }elseif (!empty($status) && empty($review['reason'])) {
            $reason = '● 原因：'.implode('，',$status);
        } elseif (!empty($review['reason']) && empty($status)){
            $reason = '● 原因：'.$review['reason'];
        }

        $imgurl = '<img src="//'.C('QINIU_DOMAIN').'/'.$img['img_path'].'-s3.jpg" style="max-width:100%">';

        $html = '亲爱的用户您好：<br><br>';
        $html .= '您于'.date('Y/m/d H:i:s',$case['time']).'上传的图片经管理员审核不通过，现已被删除。<br><br>';
        if (!empty($reason)) {
            $html .= $reason.' <br><br>';
        }
        $html .= '● 图片所属楼盘名：'.$case['title'].'<br><br>';
        $html .= $imgurl.'<br><br>如有疑问，请联系客服。谢谢！<br>';

        $notice['type'] = '2';
        $notice['title'] = '案例删除通知';
        $notice['cs'] = $user['cs'];
        $notice['text'] = htmlspecialchars_decode($html);
        $notice['userid'] = $uid;
        $notice['username'] = '系统';
        $notice['time'] = time();
        $noticle_id = M("user_system_notice")->add($notice);
        return M("user_notice_related")->add(array('noticle_id'=>$noticle_id,'uid'=>$uid));
    }

    //设置图集审核结果
    public function setThreedStatus()
    {
        $id = I('post.id');
        $status = I('post.status');
        if (empty($id) || empty($status)) {
            $this->ajaxReturn(array("info" => "数据错误！", "status" => 0));
        }
        if ($status == 2) {
            $returnRuquest = D('cases')->setThreedStatus($id, 2);
            $this->ajaxReturn($returnRuquest);
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));

    }
}
