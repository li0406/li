<?php

//装修地图

namespace Sub\Controller;

use Common\Model\Logic\DecorationmapLogicModel;
use Common\Model\Logic\QuyuLogicModel;
use Sub\Common\Controller\SubBaseController;

class DecorationmapController extends SubBaseController
{
    public function index()
    {
        $quyulogic = new QuyuLogicModel();
        $list = S('Citycache:Cache:AllOpenCity');
        if (empty($list)) {
            $list = $quyulogic->getAllOpenCitys();
            $newCitys = [];
            foreach ($list as $key => $value) {
                $data = [
                    'id' => $value['cid'],
                    'oldname' => $value['cname'],
                    'cname' => $value['cname_u'],
                ];
                $newCitys[] = $data;
            }
            $list = $newCitys;
            S('Citycache:Cache:Allopencity', $list, 86400);
        }
        $this->assign("citylist", json_encode($list));
        return $this->display();
    }

    public function getCityMap(){
        $input = I('get.');
        $input['city'] = !empty($input['city']) ? $input['city'] : $this->cityInfo['id'];
        $page = !empty($input['p']) ? $input['p'] : 1;
        $pageCount = !empty($input['limit']) ? $input['limit'] : 10;
        $mapLogic = new DecorationmapLogicModel();
        $count = $mapLogic->getDecorationMapCount($input);
        $list = [];
        $pageTmp = '';
        if ($count > 0) {
            import('Library.Org.Page.SnewPage');
            $pageClass = new \SnewPage($count, $pageCount,
                [
                    'templet' => '/zxdt?p=[PAGE]/',
                    'firstUrl' => '/zxdt/'
                ]);
            $pageTmp = $pageClass->show();
            $list = $mapLogic->getDecorationMapList($input, ($page - 1) * $pageCount, $pageCount);
        }
        $this->ajaxReturn(['error_code' => 0, 'data' => ['list' => $list, 'page' => $pageTmp, 'total_count' => $count]]);
    }
}