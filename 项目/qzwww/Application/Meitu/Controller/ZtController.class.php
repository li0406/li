<?php

namespace Meitu\Controller;
use Meitu\Common\Controller\MeituBaseController;

//美图专题列表页

class ZtController extends MeituBaseController {
    public function _initialize()
    {
        parent::_initialize();
        if (IS_GET) {
            $uri = $_SERVER['REQUEST_URI'];
            preg_match('/html$/', $uri, $m);
            if (count($m) == 0) {
                preg_match('/\/$/', $uri, $m);
                $parse = parse_url($uri);
                if (count($m) == 0 && empty($parse["query"])) {
                    header("HTTP/1.1 301 Moved Permanently");
                    if (isSsl()) {
                        $http = "https://";
                    } else {
                        $http = "http://";
                    }
                    header("Location: " . $http . $_SERVER["HTTP_HOST"] . $uri . "/");
                    die();
                }
            }
        }
    }

    //首页
    public function index() {
        $pageCount = 12;
        $pageIndex = I('get.page');
        $pageIndex = empty($pageIndex) ? 1 : $pageIndex;
        $map['status'] = 1;

        //获取结果
        $result = D("Special")->getList($map,($pageIndex-1) * $pageCount,$pageCount);

        import('Library.Org.Page.SPage');
        $page = new \SPage($result['count'], $pageCount, array(
            'templet' => '/zt-p[PAGE].html',
            'firstUrl' => '/zt/'
        ));
        $pageTmp =  $page->show();

        $banner = S('Cache:Meitu:Zt:Index:banner');
        if (!$banner) {
            $banner = D('Special')->getBannerList(array('status'=>'1','type'=>2));
            $banner = multi_array_sort($banner, 'order_id', SORT_DESC);
            S('Cache:Meitu:Zt:Index:banner', $banner, 900);
        }

        $this->assign('banner',$banner);
        $this->assign("list",$result['result']);
        $this->assign('page',$page->show());
        $this->display();
    }


    //美图专题详情页
    public function detail() {
        $id = I('get.id');
        if (empty($id)) {
            $this->_error('数据错误！');
        }

        $info = S('Cache:Meitu:Zt:Detail:info:' . $id);
        if (!$info) {
            $info = D('Special')->getSpecial($id);
            S('Cache:Meitu:Zt:Detail:info:' . $id, $info, 900);
        }

        $cache_key = 'Cache:Meitu:Zt:Detail:itemList:' . $id;

        $itemList = S($cache_key);
        if (!$itemList) {
            $result = D('Special')->getSpecialItem(array('zid'=>$id));
            foreach ($result as $key => $value) {
                $itemList[$value['type']][] = $value['item_id'];
            }
            S($cache_key, $itemList, 900);
        }

        //获取美图列表
        if(!empty($itemList['1'])){
            $meituList = S($cache_key . ':meituList');
            if (!$meituList) {
                $meituList = D('Special')->getMeituList($itemList['1']);
                S($cache_key . ':meituList', $meituList, 900);
            }

            $this->assign('meituList', $meituList);
        }

        //获取案例列表
        if(!empty($itemList['2'])){
            $caseList = S($cache_key . ':caseList');
            if (!$caseList) {
                $caseList = D('Special')->getCaseList($itemList['2']);
                S($cache_key . ':caseList', $caseList, 900);
            }

            $this->assign('caseList', $caseList);
        }

        //获取攻略列表
        if(!empty($itemList['3'])){
            $articleList = S($cache_key . ':articleList');
            if (!$articleList) {
                $articleList = D('Special')->getArticleList($itemList['3']);
                S($cache_key . ':articleList', $articleList, 900);
            }

            $this->assign('articleList', $articleList);
        }

        //获取最新专题
        $ztList = S('Cache:Meitu:Zt:Detail:ztList');
        if (!$ztList) {
            $map['status'] = 1;
            $result = D("Special")->getList($map,0,4);
            $ztList = $result['result'];
            S('Cache:Meitu:Zt:Detail:ztList', $ztList, 900);
        }
        
        $this->assign('ztList', $ztList);
        $this->assign('info',$info);
        $this->display();
    }
}