<?php

namespace User\Controller;

use User\Common\Controller\CompanyBaseController;

use Common\Model\Logic\WorksiteLogicModel;
use Common\Model\Logic\WorksiteExplainLogicModel;

class WorksiteLiveController extends CompanyBaseController{

    const MENU_INDEX = 13;
    /**
     * 工地直播页面
     * @return [type] [description]
     */
    public function index(){
        $userinfo = session("u_userInfo");

        // $worksiteExplainLogic = new WorksiteExplainLogicModel();
        // $explain_status = $worksiteExplainLogic->getExplainStatus($userinfo["id"]);
        // $this->assign("explain_status", $explain_status);

        $keyword = I("get.keyword");
        $page = intval(I("get.p", 1));

        $worksiteLogic = new WorksiteLogicModel();
        $data = $worksiteLogic->getLiveList($keyword, $page, $limit = 10);
        $data["user"] = $this->baseInfo;
        $this->assign("info", $data);
        $this->assign("nav", static::MENU_INDEX);
        $this->display();
    }

    /**
     * 工地直播详情页
     * @return [type] [description]
     */
    public function detail(){
        $id = I("get.id");
        $worksiteLogic = new WorksiteLogicModel();
        $data = $worksiteLogic->getLiveDetail($id);

        $info["user"] = $this->baseInfo;

        $this->assign("data", $data);
        $this->assign("info", $info);
        $this->assign("nav", static::MENU_INDEX);
        $this->display();
    }

    /**
     * 生成工地直播
     * @return [type] [description]
     */
    public function created(){
        $order_id = I("post.order_id");
        $worksiteLogic = new WorksiteLogicModel();
        $response = $worksiteLogic->createdLive($order_type = 1, $order_id);
        $this->ajaxReturn($response);
    }
    
    /**
     * 施工信息列表页
     * @return [type] [description]
     */
    public function infoList(){
        $live_id = I("get.live_id");
        $step = I("get.step", 0);
        $page = intval(I("get.page", 1));
        $limit = intval(I("get.limit", 5));

        $worksiteLogic = new WorksiteLogicModel();
        $response = $worksiteLogic->getInfoList($live_id, $step, $page, $limit);

        $this->ajaxReturn($response);
    }

    /**
     * 施工信息详情
     * @return [type] [description]
     */
    public function infoDetail(){
        $id = I("get.id");
        $worksiteLogic = new WorksiteLogicModel();
        $response = $worksiteLogic->getInfoDetail($id);
        $this->ajaxReturn($response);
    }

    /**
     * 施工信息编辑
     * @return [type] [description]
     */
    public function infoEdit(){
        $input = array(
            "id" => I("post.id"),
            "step" => I("post.step"),
            "content" => I("post.content"),
            "media_urls" => I("post.media_urls"),
            "media_type" => I("post.media_type"),
        );

        $worksiteLogic = new WorksiteLogicModel();
        $response = $worksiteLogic->editInfo($input);
        $this->ajaxReturn($response);
    }

    /**
     * 删除施工信息
     * @return [type] [description]
     */
    public function infoDelete(){
        $id = I("post.id");
        $worksiteLogic = new WorksiteLogicModel();
        $response = $worksiteLogic->deleteInfo($id);
        $this->ajaxReturn($response);
    }





    /**
     * 工地直播说明页
     * @return [type] [description]
     */
    public function explain(){
        $info["user"] = $this->baseInfo;
        $this->assign("info", $info);

        $this->assign("nav", static::MENU_INDEX);
        $this->display();
    }

    /**
     * 关闭提示
     * @return [type] [description]
     */
    public function explain_close(){
        if (IS_AJAX) {
            $userinfo = $_SESSION["u_userInfo"];
            $worksiteExplainLogic = new WorksiteExplainLogicModel();
            $worksiteExplainLogic->setExplain($userinfo["id"]);

            $this->ajaxReturn(["status" => 1, "info" => "成功"]);
        }
    }

    /**
     * 输出二维码
     * @return [type] [description]
     */
    public function liveQrcode(){
        $code = I("get._code");
        $worksiteLogic = new WorksiteLogicModel();
        $imgbase64 = $worksiteLogic->liveQrcode($code);
        if (!empty($imgbase64)) {
            ob_clean();
            header("Content-Type:image/jpeg;text/html; charset=utf-8");
            echo base64_decode($imgbase64);
            exit;
        } else {
            echo "图片生成失败";
            exit;
        }
    }

    public function infoComment()
    {
        $worksiteLogic = new WorksiteLogicModel();
        $status = $worksiteLogic->setInfoComment(I('post.'));
        if ($status) {
            $this->ajaxReturn(['error_code' => 0]);
        } else {
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '回复失败! ']);
        }
    }
}