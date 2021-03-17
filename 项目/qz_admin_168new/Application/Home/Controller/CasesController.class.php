<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\MsgServiceModel;
use Home\Model\Service\ElasticsearchServiceModel;
use Common\Enums\MsgTemplateCodeEnum;

class CasesController extends HomeBaseController{

    /**
     * 案例列表页
     */
    public function index(){
        //获取GET参数
        $cases_id         = I('get.cases_id');
        $cs               = I('get.cs');
        $designer_id      = I('get.designer_id');
        $company_id       = I('get.company_id');
        $cases_time_start = I('get.cases_time_start');
        $cases_time_end   = I('get.cases_time_end');

        //时间参数转换
        $cases_time_start = empty($cases_time_start) ? '' : strtotime($cases_time_start);
        $cases_time_end   = empty($cases_time_end) ? '' : strtotime($cases_time_end);

        $main['info'] = $this->getCasesList($cases_id, $cs, $cases_time_start, $cases_time_end);
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->assign('main', $main);
        $this->display();

    }

    /**
     * 获取案例列表和分页
     * @param  integer $cases_id         案例ID
     * @param  integer $cs               城市
     * @parma  string  $title            案例标题
     * @param  string  $cases_time_start 案例发布开始时间
     * @param  string  $cases_time_end   案例发布结束时间
     * @param  integer $on               是否审核
     * @return array                     案例列表和分页
     */
    public function getCasesList($cases_id = 0, $cs = 0, $cases_time_start = '', $cases_time_end = '', $title, $each = 20,$on)
    {
        $count =  D("Cases")->getCasesCount($cases_id, $cs, $cases_time_start, $cases_time_end, $title, $on);
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $each);
            $page = $p->show();
            $list = D("Cases")->getCasesList($cases_id, $cs, $cases_time_start, $cases_time_end, $title, $p->firstRow, $p->listRows, $on);

            return array("list"=>$list,"page"=>$page);
        }
        return null;
    }

    /**
     * 获取图片审核模板
     * @return mix 图片审核模板
     */
    public function images()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->ajaxReturn(array("info"=>"数据错误！","status"=>0));
        }
        $images = D('Cases')->getCaseImagesByCaseId($id);

        $this->assign('id', $id);
        $this->assign('images', $images);
        $html = $this->fetch();
        $this->ajaxReturn(array("info"=>"删除成功！","status"=>1, 'data' => $html));
    }

    /**
     * 删除案例
     * @return mix 是否删除成功
     */
    public function delete()
    {
        //获取ID
        $id = I('get.id');
        if(empty($id) || !is_numeric($id)){
            $this->ajaxReturn(array("info"=>"数据错误！","status"=>0));
        }

        //获取案例
        $case = D('Cases')->getCaseById($id);

        if (!empty($case)) {
            $result = D("Cases")->deleteCaseById($case['id']);
            if ($result) {
                if (!empty($case['uid'])) {
                    M('user')->where(array('id'=>$case['uid']))->setDec('case_count');
                }
                $this->ajaxReturn(array("info"=>"删除成功！","status"=>1));
            }
            $this->ajaxReturn(array("info"=>"删除失败！","status"=>0));
        }
        $this->ajaxReturn(array("info"=>"非法请求！","status"=>0));
    }

    /**
     * 删除案例图片
     * @return mix 删除结果
     */
    public function deleteImages()
    {
        $ids = array_filter(explode(',', I('get.ids')));
        if (empty($ids)) {
            $this->ajaxReturn(array("info"=>"数据错误！","status"=>0));
        }
        $result = D('Cases')->deleteCaseImagesByCaseImagesIds($ids);
        if ($result) {
            $this->ajaxReturn(array("info"=>"删除成功！","status"=>1));
        }
        $this->ajaxReturn(array("info"=>"删除失败！","status"=>0));
    }

    /**
     * 修改案例状态
     * @return mix 修改结果
     */
    public function setCasesStatus()
    {
        $id = I('get.id');
        $on = I('get.on');
        if (empty($id) || empty($on) || !in_array($on, array(1, 2, 3))) {
            $this->ajaxReturn(array("info"=>"数据错误！","status"=>0));
        }

        $result = D('Cases')->setCasesOn($id, $on);
        if ($result) {
        	//清除前台关于案例的缓存

			//装修公司页面
			$case = D('Cases')->getCaseById($id);
			if(!empty($case['uid'])){
				D('Home/Logic/RedisLogic')->del('Cache:SubUserInfoUpdate:'.$case['uid']);
			}

            // 审核通过添加标签
            if ($on == 1) {
                // 关联标签
                $searchService = new ElasticsearchServiceModel();
                $result = $searchService->getContentLabel($case["title"], 2, 10);
                if (count($result) > 0) {
                    $allLabel = [];
                    foreach ($result as $v) {
                        $allLabel[] = [
                            "thematic_id" => $v["id"],
                            "content_id" => $id,
                            "module" => 8
                        ];
                    }
                    if (count($allLabel) > 0) {
                        $thematicLogic = new ThematicWordsLogicModel();
                        $thematicLogic->addThematicRelated($allLabel);
                    }
                }
            }

            // QZMSG 发送消息提醒
            if ($on == 3) {
                $msgService = new MsgServiceModel();
                $template_id = MsgTemplateCodeEnum::EXAM_CASE_FAIL;
                $linkparam = "?active_name=case";
                $msgService->sendCompanyMsg($template_id, $case["uid"], $linkparam, $id, "案例审核", [], $case["userid"]);
            }

            $this->ajaxReturn(array("info"=>"操作成功！","status"=>1));
        }
        $this->ajaxReturn(array("info"=>"操作失败！","status"=>0));
    }

    /**
     * 3D效果图审核页面
     */
    public function threed(){
        $cid                = I('get.cid');
        $cs                 = I('get.cs');
        $des_id             = I('get.des_id');
        $com_id             = I('get.com_id');
        $status             = I('get.status');
        $time_start         = I('get.time_start');
        $time_end         = I('get.time_end');

        $number = 30;

        if(empty($status)){
            $status = '';
        }
        $time_start = empty($time_start) ? '' : strtotime($time_start);
        $time_end = empty($time_end) ? '' : strtotime($time_end);
        $info = $this->getThreedList($cid, $cs, $des_id, $com_id, $status, $time_start,$time_end,$number);
        $info['list'] = D('Cases')->changeThreedFacePic($info['list']);
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->assign('info', $info);
        $this->assign('status',$status);
        $this->display();
    }

    /**
     * 根据条件查询3D效果图
     * @param    $cid         案例id
     * @param    $cs          城市id
     * @param    $des_id      设计师id
     * @param    $com_id      装修公司id
     * @param    $time_start  发布时间
     * @param    $status      审核状态
     * $number      分页
     */
    public function getThreedList($cid,$cs,$des_id,$com_id,$status,$time_start,$time_end,$num=24){
        $count =  D("ImageVerify")->getThreedCount($cid,$cs,$des_id,$com_id,$status,$time_start);
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,$num);
            $page = $p->show();
            $list = D("ImageVerify")->getThreedList($cid,$cs,$des_id,$com_id,$time_start,$time_end,$status,$p->firstRow,$p->listRows);
            return array("list"=>$list,"page"=>$page);
        }
        return null;
    }


    public function getThreedInfo(){
        $id = I('get.id');
        $imgid = I('get.imgid');
        $status = I('get.status');
        if(empty($imgid)){
            $imgid = '0';
        }
        //获取3D案例审核状态并修改
        D('ImageVerify')->getThreedStatusAndChange($id);

        $info['caseList'] = D('ImageVerify')->getThreedInfoById($id,$status);
        $info['caseList'] = D('Cases')->changeThreedShowPic($info['caseList']);
        // $info['casereview'] = D('cases')->getCasesReview($id);
        if(!empty($imgid)){
            foreach ($info['caseList'] as $key => $v) {
                if($v['imgid'] == $imgid){
                    $info['case'] = $info['caseList'][$key];
                }
            }
        }else{
            $info['case'] = $info['caseList'][$imgid];
        }
        //此处导致查询图片失败
        if(empty($status)){
            $status = '';
        }

        $cs = I('get.cs');
        $cid = I('get.cid');
        $des_id = I('get.des_id');
        $com_id = I('get.com_id');
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        $time_start = empty($time_start) ? '' : strtotime($time_start);
        $time_end   = empty($time_end) ? '' : strtotime($time_end);
        //查询上一个和下一个
        $info['caseNav']['prev']  = D('Cases')->getPrevCase($id,$cid,$cs,$des_id,$com_id,$time_start,$time_end,4,$status)['id'];
        $info['caseNav']['next'] = D('Cases')->getNextCase($id,$cid,$cs,$des_id,$com_id,$time_start,$time_end,4,$status)['id'];
        $info['caseInfo'] = D('ImageVerify')->getCaseById($id);
        $this->assign('info', $info);
        $this->display('Imageverify/imgBoxTpl3d');
    }


    //审核不通过
    /**
     * Sets the case image status.
     */
    public function setCaseImgStatus(){
        $id = I('post.id');
        $status = I('post.status');
        if (empty($id) || empty($status)) {
            $this->ajaxReturn(array("info"=>"数据错误！","status"=>0));
        }
        $result = D('Cases')->setThreedStatus($id,$status);
        //Case image review reason.
        if($status == 3){
            $reason = I('post.reason');
            $reason = implode(',',$reason);
            $otherReason = I('post.otherReason');
            if(!empty($reason)){
                $data['status'] = $reason;
            }
            if(!empty($otherReason)){
                $data['reason'] = $otherReason;
            }
            if(!empty($data)){
                $data['caseid'] = $id;
                $data['review_time'] = time();
                M('case_review')->add($data);
            }

            $threecase = D('Cases')->getCaseById($id);
            if (!empty($threecase)) {
                // QZMSG 发送消息提醒
                $msgService = new MsgServiceModel();
                $template_id = MsgTemplateCodeEnum::EXAM_XIAOGUOTU_3D_FAIL;
                $linkparam = "?active_name=effect";
                $msgService->sendCompanyMsg($template_id, $threecase["uid"], $linkparam, $id, "3D效果图审核", [], $threecase["userid"]);
            }
        }else{
            $this->ajaxReturn(array("info"=>"操作成功！","status"=>1));
        }

        //暂不检查是否操作成功
        $this->ajaxReturn(array("info"=>"操作成功！","status"=>1));
    }

    public function getCaseReview(){
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array("info"=>"数据错误！","status" => 0));
        }
        $review = D('Cases')->getReviewByCasesId($id);
        if (!empty($review)) {
            $review['status'] = array_filter(explode(',',$review['status']));
        }

        $review['casesStatus'] = $review['status'];
        $this->ajaxReturn(array('data' => $review,"status" => 1));
    }




}