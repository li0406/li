<?php


namespace Mobile\Controller;


use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\DecorationmapLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class DecorationmapController extends MobileBaseController
{
    public function getCityMap(){

        $input = I('get.');
        $input['city'] = !empty($input['city']) ? $input['city'] : ($this->cityInfo['id'] ? $this->cityInfo['id'] : "320500");  // 没有城市信息默认苏州
        $page = !empty($input['p']) ? $input['p'] : 1;
        $pageCount = !empty($input['limit']) ? $input['limit'] : 20;
        $mapLogic = new DecorationmapLogicModel();
        $companylogic = new CompanyLogicModel();
        $count = $mapLogic->getDecorationMapCount($input);
        $list = [];
        $pageTmp = ["page_total_number" => 0, "total_number" => 0, "page_size" => 0, "page_current" => 0];
        if ($count > 0) {
            import('Library.Org.Page.SnewPage');
            $pageClass = new \SnewPage($page, $pageCount, $count);
            $pageTmp = $pageClass->show();
            $list = $mapLogic->getDecorationMapList($input, $pageClass->firstrow, $pageClass->pageSize);
            foreach ($list as $key => $val) {
                $list[$key]["kouhao"] = $val["kouhao"] ? $val["kouhao"] : "";
                // 重新计算星星
                $startinfo = $companylogic->getCompanyScoreInfo($val["company_id"]);
                $list[$key]["comment_score"] = $startinfo ? $startinfo : 1;
            }
            $list = $this->getStar($list);
        }
        $this->ajaxReturn(['error_code' => 0, 'data' => ['list' => $list, 'page' => $pageTmp, 'total_count' => $count]]);
    }




    //计算星星
    private function getStar($list)
    {
        $logo = OP('DEFAULT_LOGO');
        foreach ($list as $key => $value) {
            if (empty($value['logo'])) {
                $list[$key]["logo"] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . '/' . $logo;
            }
            if($value["comment_score"] >= 9 ){
                $list[$key]["star"] = 5;
            }elseif($value["comment_score"] >= 8 && $value["comment_score"] < 9){
                $list[$key]["star"] = 4;
            }elseif($value["comment_score"] >= 6 && $value["comment_score"] < 8){
                $list[$key]["star"] = 3;
            }elseif($value["comment_score"] >= 4 && $value["comment_score"] < 6){
                $list[$key]["star"] = 2;
            }else{
                $list[$key]["star"] = 1;
            }

            //超过10个字符以“...”做结尾
            if(mb_strlen($value['jc'])>8){
                $list[$key]["jc"] = mb_substr($value['jc'],0,8).'...';
            }
        }
        return $list;
    }

}