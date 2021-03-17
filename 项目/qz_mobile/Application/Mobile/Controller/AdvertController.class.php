<?php

namespace Mobile\Controller;

use Mobile\Common\Controller\MobileBaseController;
use Common\Model\Logic\AdvertLogicModel;

class AdvertController extends MobileBaseController{

    /**
     * 根据广告位显示广告
     * @return [type] [description]
     */
    public function positionAdvert(){
        $position_id = I("get.position_id");
        $cs = I("get.cs");
        $pidPrefix = 'pixiu-pid-' . $position_id; //加前缀的pid

        // 城市默认全站
        if ( empty($cs) ) {
            $cs = "000001";
        }

        // 页面全局模板变量
        $this->assign("pid", $position_id);
        $this->assign("cs", $cs);
        $this->assign("pidPrefix", $pidPrefix);

        // 参数验证
        if (empty($position_id)) {
            $this->display("error");
            exit();
        }

        $advertList = S("C:M:pixiushow:". $position_id .":". $cs);
        if (empty($advertList)) {
            // 获取广告位信息
            $advertLogic = new AdvertLogicModel();
            $advertPosition = $advertLogic->getAdvertPosition($position_id);

            // 广告位不存在或已禁用
            if (!empty($advertPosition) && $advertPosition["enabled"] == 1) {
                // 查询广告位下的有效广告
                $advertList = $advertLogic->getActiveAdvert($position_id, $cs);
                S("C:M:pixiushow:". $position_id .":". $cs, $advertList, 600);
            }
        }

        if (empty($advertList)) {
            $this->display("error");
            exit();
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

        exit(); // 不显示页面trace
    }

}
