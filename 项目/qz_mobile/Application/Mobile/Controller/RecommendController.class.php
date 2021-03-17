<?php

namespace Mobile\Controller;

use Common\Model\Logic\TuLogicModel;
use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class RecommendController extends MobileBaseController {

    // 主站文章详情推荐
    public function article_recommend(){
        // 推荐公司
        $companyLogic = new CompanyLogicModel();
        $recommend["company_list"] = $companyLogic->getRecommendCompanyList($this->cityInfo["cid"], 3);

        // 推荐效果图
        $tuLogic = new TuLogicModel();
        $recommend["tu_list"] = $tuLogic->getRecommendTuHome(4);
        
        // 推荐装修案例
        $casesLogic = new CasesLogicModel();
        $recommend["case_list"] = $casesLogic->getRecommendCases($this->cityInfo["cid"], 4);

        $this->assign("recommend", $recommend);
        $template = $this->fetch("article_recommend");

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    // 分站文章详情推荐装修公司
    public function article_recommend_company(){
        $page = I("get.page", 1);
        $limit = 20;

        import('Library.Org.Page.SnewPage');
        $snewPage = new \SnewPage($page, $limit, 0);
        $offset = $snewPage->firstrow;

        $companyLogic = new CompanyLogicModel();
        $list = $companyLogic->getRecommendCompanyList($this->cityInfo["cid"], $limit, $offset);

        $template = "";
        if (count($list) > 0) {
            $this->assign("list", $list);
            $template = $this->fetch("article_recommend_company");
        }

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    // 分站文章详情推荐家装效果图
    public function article_recommend_tu_home(){
        $page = I("get.page", 1);
        $limit = 20;

        import('Library.Org.Page.SnewPage');
        $snewPage = new \SnewPage($page, $limit, 0);
        $offset = $snewPage->firstrow;

        $tuLogic = new TuLogicModel();
        $list = $tuLogic->getRecommendTuHome($limit, $offset);

        $template = "";
        if (count($list) > 0) {
            $this->assign("list", $list);
            $template = $this->fetch("article_recommend_tu_home");
        }

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    // 分站文章详情推荐装修案例
    public function article_recommend_cases(){
        $page = I("get.page", 1);
        $limit = 20;

        import('Library.Org.Page.SnewPage');
        $snewPage = new \SnewPage($page, $limit, 0);
        $offset = $snewPage->firstrow;

        $casesLogic = new CasesLogicModel();
        $list = $casesLogic->getRecommendCases($this->cityInfo["cid"], $limit, $offset);

        $template = "";
        if (count($list) > 0) {
            $this->assign("list", $list);
            $template = $this->fetch("article_recommend_cases");
        }

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    // 分站文章详情推荐阅读
    public function article_recommend_read(){
        $classid = I("get.classid");
        $limit = 20;

        $time = strtotime("-2 years");
        $list = D("Littlearticle")->getRecommendArticlesByTime($classid, $limit, $this->cityInfo["cid"], $time);

        $template = "";
        if (count($list) > 0) {
            $this->assign("list", $list);
            $this->assign("cityInfo", $this->cityInfo);
            $template = $this->fetch("article_recommend_read");
        }

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }


}