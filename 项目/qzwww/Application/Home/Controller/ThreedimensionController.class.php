<?php

/*3D效果图*/

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class ThreedimensionController extends HomeBaseController
{
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

        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'meitu');
        $this->assign('serch_type', '装修效果图');
        $this->assign('holdercontent', '海量精选美图任您挑选');
        //导航栏标识
        $this->assign('choose_menu', '3dxgt');
        $this->assign("tabIndex", 2);
    }

    /**
     * 3D效果图列表页
     */
    public function category()
    {
        $p = 1;
        if (I("get.p") !== "") {
            $p = I("get.p");
        }

        $fengge = intval(str_replace("-", "", I('get.fengge')));
        $huxing = intval(str_replace("-", "", I('get.huxing')));
        $category = intval(str_replace("-", "", I('get.category')));

        $type = I('get.type');

        $result = D('XiaoguotuThreedimension')->getThreedimensionCategory();
        foreach ($result as $key => $value) {
            if ($category == $value['id']) {
                if ($value['type'] == 1) {
                    $fengge = $category;
                } else {
                    $huxing = $category;
                }
            }
            $vars['category'][$value['id']] = $value;
        }

        $vars['fengge'] = $fengge;
        $vars['huxing'] = $huxing;


        $vars['search'] = array(
            'fengge' => $fengge,
            'huxing' => $huxing
        );

        $result = $vars['info'] = $this->getList($type, $category, $fengge, $huxing, $p, 12);
        $vars["info"] = $result["info"];
        $vars["show"] = $result["show"];

        //如果没有筛选结果
        $vars['noResult'] = 0; //是否有搜索结果
        if (empty($vars['info'])) {
            $vars['noResult'] = 1;
            $limit = 3;
            $noHuxing = 0;
            $vars['otherInfo'] = D('XiaoguotuThreedimension')->getOtherList($id, $title, $fengge, $noHuxing, $limit);
            if (empty($vars['info'])) {
                $vars['otherInfo'] = D('XiaoguotuThreedimension')->getOtherList($id, $title, $fengge, $huxing, $limit);
                if (empty($vars['otherInfo'])) {
                    $noFengge = 0;
                    $vars['otherInfo'] = D('XiaoguotuThreedimension')->getOtherList($id, $title, $noFengge, $huxing, $limit);
                }
            }
        }

        $vars['page'] = ($p == 1) ? '' : '第' . $p . '页';


        //友情链接
        $friendLinks = $this->getThreedFriendLink();
        $this->assign('friendLink', $friendLinks);


        //弹窗发单标识
        $this->assign("tmpsource", 18030644);
        $this->assign("tmpsource1", 18030600);

        $this->assign('vars', $vars);
        $this->display('category_20180702');
    }


    /**
     * 3D效果图终端页
     */
    public function terminal()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->_error();
        }

        //引导层24小时之内显示一次
        if (cookie("QZ_3D_GUIDE") === null) {
            $this->assign("isguide", 1);
            cookie("QZ_3D_GUIDE", 1, time() + 86400);
        }

        $vars['info'] = S('Cache:Meitu:3d:content:' . $id);
        if (!$vars['info']) {
            $vars['info'] = D('XiaoguotuThreedimension')->getThreedimensionById($id);
            S('Cache:Meitu:3d:content:' . $id, $vars['info'], 900);
        }

        $vars['info']['update_time'] = time();

        $tdk = [];
        $tdk['title'] = $vars['info']['title'].' - 齐装网装修效果图';
        $tdk['keywords'] = $vars['info']['title'];
        $tdk['description'] = '齐装网3D装修效果图专区为您提供' . $vars['info']['title'] . '，还有更多其他3D美图案例供您欣赏！';
        $this->assign('tdk',$tdk);



        $this->assign('vars', $vars);
        $this->display("terminal_20180704");
    }

    /**
     * 装修案例3D效果图演示
     */
    public function caseThreedShow()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->_error();
        }

        //引导层24小时之内显示一次
        if (cookie("QZ_3D_GUIDE") === null) {
            $this->assign("isguide", 1);
            cookie("QZ_3D_GUIDE", 1, time() + 86400);
        }

        $vars['info'] = D('Cases')->getThreedimensionById($id);
        $vars['info']['path'] = D('Cases')->getThreedimagePathById($id);
        $vars['info']['update_time'] = time();

        $tdk = [];
        $tdk['title'] = $vars['info']['title'].' - 齐装网装修效果图';
        $tdk['keywords'] = $vars['info']['title'];
        $tdk['description'] = '齐装网3D装修效果图专区为您提供' . $vars['info']['title'] . '，还有更多其他3D美图案例供您欣赏！';
        $this->assign('tdk',$tdk);

        $this->assign('vars', $vars);
        $this->display("terminal_20180929");
    }

    /**
     * 生成xml文件
     */
    public function xml()
    {
        $path = str_replace('threedimension/xml', '', trim(explode('?', trim($_SERVER['REQUEST_URI']))[0], '/'));
        $path = C('QINIU_SCHEME') .'://' . C('QINIU_DOMAIN') . '/threedimensional' . rtrim($path, 'pano.xml');
        $update_time = I('get.update_time');
        $skin = C('QINIU_SCHEME') .'://' . C('QINIU_DOMAIN') . '/threedimension/skin20180704/defaultskin.xml';
        $string = '<krpano>' .
            '<include url="' . $skin . '" />' .
            '<view hlookat="0" vlookat="0" maxpixelzoom="1.0" fovmax="150" limitview="auto" />' .
            '<preview url="' . $path . 'preview.jpg?update_time=' . $update_time . '" />' .
            '<image><cube url="' . $path . '3d_%s.jpg?update_time=' . $update_time . '" /></image>' .
            '</krpano>';
        echo($string);
        exit();
    }

    /**
     * 点赞
     * @return [type] [description]
     */
    public function dolike()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("XiaoguotuThreedimension")->setLike($id);
            if ($i !== false) {
                $this->ajaxReturn(array("data" => "", "info" => "", "status" => 1));
            }
            $this->ajaxReturn(array("data" => "", "info" => "操作失败,请刷新重试！", "status" => 0));
        }
    }

    private function getList($type, $category, $fengge, $huxing, $pageIndex, $pageCount)
    {
        $count = D('XiaoguotuThreedimension')->getCount($id, $title, $fengge, $huxing);
        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $config = array(
                '1' => array(
                    'templet' => '/tu/3d[PAGE]/',
                    'firstUrl' => '/tu/3d/'
                ),
                '2' => array(
                    'templet' => '/tu/3d[PAGE]-' . $category . '/',
                    'firstUrl' => '/tu/3d-' . $category . '/'
                ),
                '3' => array(
                    'templet' => '/tu/3d[PAGE]-' . $fengge . '-' . $huxing . '/',
                    'firstUrl' => '/tu/3d-' . $fengge . '-' . $huxing . '/'
                ),
            );
            $page = new \SPage($count, $pageCount, $config[$type]);
            $show = $page->show();
            $result = D('XiaoguotuThreedimension')->getList($id, $title, $fengge, $huxing, ($pageIndex - 1) * $pageCount, $pageCount);
            $result = array_chunk($result, 6);
            $list["first"] = $result[0];
            $list["last"] = $result[1];
        }
        return array("info" => $list, "show" => $show);
    }


    /**
     * 获取3D友情链接
     * @return array
     */
    public function getThreedFriendLink()
    {
        $type = 'meitu-three-d';
        $friendLink = S('Cache:3DMeitu:List:Friendlink:link-' .$type);
        $friendLink = [];
        if(empty($friendLink)){
            $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001', '1', $type);
            S('Cache:3DMeitu:List:Friendlink:link-' .$type, $friendLink['link'], 900);
        }
        return $friendLink;
    }


}