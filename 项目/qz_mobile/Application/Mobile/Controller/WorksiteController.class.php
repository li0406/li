<?php
/**
 * About
 */
namespace Mobile\Controller;
use Common\Model\Logic\WorksiteLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class WorksiteController extends MobileBaseController{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function workSiteList(){
        $workLogic = new WorksiteLogicModel();
        $page = !empty(I('get.page'))?(int)I('get.page'):1;
        $limit = !empty(I('get.limit'))?(int)I('get.limit'):10;
        $data = $workLogic->getLiveList(I('get.'),$page,$limit);
        if(IS_AJAX){
            $this->ajaxReturn(['error_code'=>0,'data'=>$data]);
        }
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('list');
    }

    public function workSiteDetail(){
        $workLogic = new WorksiteLogicModel();
        $page = !empty(I('get.page'))?(int)I('get.page'):1;
        $limit = !empty(I('get.limit'))?(int)I('get.limit'):5;
        $data = $workLogic->getLiveDetail(I('get.live_id'),$page,$limit);
        if(IS_AJAX){
            $this->ajaxReturn(['error_code'=>0,'data'=>$data['info']]);
        }
        $this->assign('info',$data);
        $this->display('detail');
    }

    public function workSiteFans()
    {
        $workLogic = new WorksiteLogicModel();
        $fans = $workLogic->getLiveFans(I('get.live_id'));
        $this->assign('list', $fans['info']);
        $this->display('fans');
    }

    public function workSiteWxQrcode()
    {
        $live_id = I('post.live_id');
        if (empty($live_id)) {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '获取失败!(workSiteWxQrcode)']);
        }
        $workLogic = new WorksiteLogicModel();
        $url = $workLogic->getLiveQrcode($live_id);
        if ($url) {
            $this->ajaxReturn(['error_code' => 0, 'info' => $url['url'], 'content' => $url['content']]);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '获取失败!(workSiteWxQrcode)']);
        }
    }

}
