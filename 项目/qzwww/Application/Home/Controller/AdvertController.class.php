<?php

namespace Home\Controller;

use Exception;
use Common\Model\Logic\AdvertLogicModel;
use Home\Common\Controller\HomeBaseController;

class AdvertController extends HomeBaseController {

    private $advertLogic;

    public function _initialize(){
        parent::_initialize();
        $this->advertLogic = new AdvertLogicModel();
    }

    /**
     * 根据广告位显示广告
     * @return [type] [description]
     */
    public function positionAdvert(){
        try {
            // 参数验证
            $position_id = I("get.position_id");
            if (empty($position_id)) {
                throw new Exception("缺少参数");
            }

            $cs = "000001"; // 默认全站
            $cityInfoJson = cookie("QZ_CITY");
            $cityInfo = json_decode($cityInfoJson, true);
            if (!empty($cityInfo) && !empty($cityInfo["id"])) {
                $cs = $cityInfo["id"];
            }

            // 获取广告位信息
            $advertPosition = $this->advertLogic->getAdvertPosition($position_id);

            // 广告位不存在或已禁用
            if (empty($advertPosition) || $advertPosition["enabled"] != 1) {
                throw new Exception("广告位不存在或已禁用");
            }

            // 查询广告位下的有效广告
            $advertList = $this->advertLogic->getActiveAdvert($position_id, $cs);
            if (empty($advertList)) {
                throw new Exception("广告位下没有有效广告");
            }

            foreach ($advertList as $key => $val) {
                $advertList[$key]['js'] = $val['js'] ? htmlspecialchars_decode($val['js']) : '';
            }
            switch ($advertPosition["type"]) {
                case "1":
                    // 单图
                    $this->assign("info", $advertList[0]);
                    $this->display("single_picture");
                    break;

                case "2":
                    // 多图
                    $this->assign("list", $advertList);
                    $this->display("multi_picture");
                    break;
                
                case "3":
                    // 轮播
                    $this->assign("list", $advertList);
                    $this->display("slide_picture");
                    break;

                case "4":
                    // 单图文
                    $this->assign("info", $advertList[0]);
                    $this->display("single_picture_text");
                    break;

                case "5":
                    // 多图文
                    $this->assign("list", $advertList);
                    $this->display("multi_picture_text");
                    break;
                case "6":
                    //单JS广告
                    $this->assign("info", $advertList);
                    $this->display("single_js");
                    break;
                case "7":
                    //双排JS广告
                    $this->assign("info", $advertList);
                    $this->display("single_js");
                    break;
            }
        } catch (Exception $e) {
            $this->display("error");
        }

        exit(); // 不显示页面trace
    }
}