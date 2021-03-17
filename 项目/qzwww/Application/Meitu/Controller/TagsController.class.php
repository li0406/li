<?php

namespace Meitu\Controller;

use Meitu\Common\Controller\MeituBaseController;

class TagsController extends MeituBaseController
{
    public function _initialize(){
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
    public function index()
    {
        $map = array(
            'meitu_count' => array('GT', 0)
        );
        $count = D("Common/Tags")->getTagsCount($map);
        if ($count > 0) {
            $pageCount = 90;
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/tags-p[PAGE].html',
                'firstUrl' => '/tags/'
            ));
            $result =  D("Common/Tags")->getTagsList($map, 'id, name', 'meitu_count DESC',($page->nowPage-1)*$pageCount, $pageCount);
            //模板赋值
            $vars['page'] = $page->show();
            $vars['list'] = $result;
        }
        $this->assign('vars', $vars);
        $this->display();
    }

    public function category()
    {

        $category = I('get.cate');//取分类
        $id = I('get.id');//取Tags ID
        $page = (I('get.page')) > 0 ? I('get.page') : 1;
        if (!empty($this->redirct[$id])) {
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.' . C('QZ_YUMING') . '/tags/' . $category . $this->redirct[$id];
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }

        //带有动态分页跳转到静态分页
        if (!empty($_GET['p'])) {
            $temp = intval($_GET['p']) > 0 ? intval($_GET['p']) : 1;
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.' . C('QZ_YUMING') . '/tags/' . I('get.cate') . I('get.id') . 'p-' . $temp . '.html';
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }

        $DB = D('Common/Tags');

        $tags = $DB->getTags($id);
        if (empty($tags)) {
            $this->_error();
        }

        $title = $tags['name'];
        $keyword = $tags['name'];

        $pageIndex = $page;
        $pageCount = 10;
        if ($pageIndex > 1) {
            $pageContent = "第" . $pageIndex . "页";
        }
        $condition['tags'] = $id;

        //美图
        if ($category == 'meitu') {
            $this->assign("headerTmp", null);
            $pageCount = 20;
            $result = $this->getMeituList($condition, $pageIndex, $pageCount);
            $count = count($result['list']);
            //如果数量不够一页则根据标签获取相关的
            if ($count < $pageCount) {
                $ids = array();
                $keys = array();
                foreach ($result['list'] as $key => $value) {
                    $ids[] = $value['id'];
                    $tagids = array_filter(explode(',', $value['tags']));
                    foreach ($tagids as $k => $v) {
                        array_push($keys, $v);
                    }
                }
                $keys = array_flip(array_flip($keys));
                if (!empty($ids)) {
                    $map['id'] = array('NOTIN', $ids);
                }
                if (!empty($keys)) {
                    foreach ($keys as $key => $value) {
                        $map['_string'] = $map['_string'] . ' ( FIND_IN_SET("' . $value . '",tags) )' . ' OR';
                    }
                    $map['_string'] = trim($map['_string'], 'OR');
                    $temp = D('Meitu')->getMeituForTag($map, $pageCount - $count);
                    if (!empty($temp)) {
                        foreach ($temp as $key => $value) {
                            array_push($result['list'], $value);
                        }
                    }
                }
                //重新赋值数量
                $result['num'] = count($result['list']);
            }

            //查询用户是否收藏该美图
            $collect = array();
            if (isset($_SESSION["u_userInfo"]) && $_SESSION['u_userInfo']['classid'] != 3) {
                $ids = array();
                foreach ($result['list'] as $key => $value) {
                    $ids[] = $value['id'];
                }
                $collect = D("Usercollect")->getCollectIdsByIds($ids, $_SESSION['u_userInfo']['id'], 4);
            }
            $this->assign("collect", $collect);

            $hotTags = S('C:HotTag:2:9');
            if (empty($hotTags)) {
                $hotTags = $DB->getHotTags(2, 9);
                S('C:HotTag:2:9', $hotTags, 10800);
            }
            $newTags = $DB->getNewTags(2, 9);
            $tkd['title'] = $tags['name'];
            $tkd['keyword'] = $tags['name'] . '，' . $this->mbrtrim($tags['name'], '图片') . '图片，' . $this->mbrtrim($tags['name'], '设计') . '设计';
            $tkd['description'] = $tags['name'] . '频道，提供了'.date('Y').'流行的' . $tags['name'] . '和全新' . $this->mbrtrim($tags['name'], '装修样板') . '装修样板案例。每日更新大量' . $this->mbrtrim($tags['name'], '图片') . '图片，齐装网' . $this->mbrtrim($tags['name'], '图库') . '图库供您自由欣赏和选择。';
            if (empty($_GET['p'])) {
                $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.' . C('QZ_YUMING') . '/tags/meitu' . $id;
            }
        }
        //若没有该标签
        if($result["num"] == 0){
            $this->_error();
            die();
        }
        $this->assign("list", $result["list"]);
        $this->assign("tagid", $id);
        $this->assign("num", $result["num"]);
        $this->assign("hotTags", $hotTags);
        $this->assign("newTags", $newTags);
        $this->assign("page", $result["page"]);
        if ($category != 'meitu') {
            $this->assign("title", $title);
            $this->assign("keyword", $keyword);
            $this->assign("description", $description);
        } else {
            $this->assign("tkd", $tkd);
        }
        $this->assign('baseUrl', $this->SCHEME_HOST['scheme_full'] .C("QZ_YUMINGWWW"));
        $this->assign("info", $info);
        $this->display($category);
    }

    /**
     * [mbrtrim 去掉字符串右边相同部分]
     * @param  [type] $string  [字符串]
     * @param  [type] $replace [去掉的部分]
     * @return [type]          [description]
     */
    private function mbrtrim($string, $replace)
    {
        $location = mb_strrpos($string, $replace);
        if ($location === mb_strlen($string) - mb_strlen($replace)) {
            $string = mb_substr($string, 0, $location);
        }
        return $string;
    }

    //取美图列表
    private function getMeituList($condition, $pageIndex = 1, $pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $result = D("Common/Tags")->getMeituList($condition, ($pageIndex - 1) * $pageCount, $pageCount);
        $count = $result['count'];
        $list = $result['result'];
        if ($count > $pageCount) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/tags/meitu' . $condition['tags'] . 'p-[PAGE].html',
                'firstUrl' => '/tags/meitu' . $condition['tags']
            ));
            $pageTmp = $page->show();
        }
        return array("list" => $list, "page" => $pageTmp, "num" => $count);
    }
}