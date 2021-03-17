<?php
/**
 * 搜索
 */
namespace Mobile\Controller;
use Common\Model\Logic\HomeSearchLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Mobile\Common\Controller\MobileBaseController;

class SearchController extends MobileBaseController {
    public function _initialize(){
        parent::_initialize();

    }

    public function search()
    {
        $keyword = I("get.keyword");
        $module = I("get.module");


        if (!empty($module) || !empty($keyword)) {
            $modules = [
                ['module'=>"",'title'=>'全部'],
                ['module'=>"gonglue",'title'=>'资讯'],
                ['module'=>"baike",'title'=>'百科'],
                ['module'=>"wenda",'title'=>'问答'],
            ];
            $columns = array_column($modules,"module");
            if (!in_array($module,$columns)) {
                $this->_error();
            }
            $service = new ElasticSearchServiceModel();
            if (empty($module)) {
                //综合搜索
                $list = $service->getColligateSearch($keyword);
            } else {
                //分类搜索
                $list = $this->getSearchModel($module,$keyword);
            }

            $this->assign('module',$module);
            $this->assign('is_more',$list["is_more"]);
            $this->assign('list',$list["list"]);
            $this->assign('modules',$modules);
            $this->display("search_list");
        } else {
            $this->display();
        }
    }

    public function getSearchList()
    {
        if (IS_AJAX) {
            if (IS_POST) {
                $page = I("post.page");
                $module = I("post.module");
                $keyword = I("post.keyword");
                $list = $this->getSearchModel($module,$keyword,$page);
                $this->ajaxReturn(["error_code" => 0,"error_msg" => "","data" => $list["list"][$module]]);
            }
        }
    }

    private function getSearchModel($module,$keyword,$pageIndex,$pageSize = 10)
    {
        if (empty($pageIndex)) {
            $pageIndex = 1;
        }

        $service = new ElasticSearchServiceModel();
        $is_more = false;
        switch ($module) {
            case "gonglue":
                $list = $service->getModuleSearch("www",$keyword,$pageIndex,$pageSize);
                break;
            case "baike":
                $list = $service->getModuleSearch("baike",$keyword,$pageIndex,$pageSize);
                break;
            case "wenda":
                $list = $service->getModuleSearch("wenda",$keyword,$pageIndex,$pageSize);
                break;
        }

        if ($list["page"]["page_total_number"] > 1) {
            $is_more = true;
        }

        return array("list" => $list["list"],"is_more" => $is_more);
    }
}