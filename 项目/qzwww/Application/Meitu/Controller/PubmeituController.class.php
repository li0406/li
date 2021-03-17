<?php

namespace Meitu\Controller;

use Meitu\Common\Controller\MeituBaseController;

class PubmeituController extends MeituBaseController
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

        //导航栏标识
        $this->assign("tabIndex", 2);

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot', 1);
        }

        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'meitu');
        $this->assign('serch_type', '装修效果图');
        $this->assign('holdercontent', '海量精选美图任你选');
        //添加选中效果
        $this->assign('choose_menu', 'gongzhuang');
        $t = T("Home@Index:header");
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp", $headerTmp);
    }

    //美图列表页
    public function pubMeituList()
    {
        //跳转到手机端
        if (ismobile()) {
            $uri = $_SERVER['REQUEST_URI'];
            preg_match('/\/$/',$uri,$m);

            if (count($m) > 0) {
                $uri = rtrim($uri,"/");
            }
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES')."/meitu".$uri );
            exit();
        }

        /*S-动态参数分页跳转到静态参数分页*/
        if (isset($_GET['a1'])) {
            $arr = array(
                'l' => 'a1',
                'f' => 'a2',
                'm' => 'a3'
            );
            //拼接静态URL
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.' . C('QZ_YUMING') . '/gongzhuang-';
            foreach ($arr as $key => $value) {
                $url = $url . $key . intval($_GET[$value]);
            }
            if (isset($_GET['q'])) {
                $url = $url . 'q' . intval($_GET['q']);
            } else if (intval($_GET['p']) > 1) {
                $url = $url . 'p' . intval($_GET['p']);
            }
            $url = $url . '?';
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('a1', 'a2', 'a3', 'p', 'q'))) {
                    $url = $url . $key . '=' . $_GET[$key] . '&';
                }
            }
            $url = rtrim($url, '&');
            $url = rtrim($url, '?');
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        /*E-动态参数分页跳转到静态参数分页*/

        $patternq = '/q\d+/i';
        preg_replace($patternq, '', $_SERVER['REQUEST_URI'], -1, $countq);
        $patternp = '/p\d+/i';
        preg_replace($patternp, '', $_SERVER['REQUEST_URI'], -1, $countp);
        if ($countp > 0 || $countq > 0) {
            $info["noindex"] = '<meta name="robots" content="noindex,follow"/>';
        }
        /*E-SEO的canonical标签生成以及301跳转*/

        /* S 排序、单图、套图 Cookie 处理 */
        $orderBy = I('get.order');
        if (empty($orderBy)) {
            $orderBy = cookie('pubmeitu_orderby');
        } else {
            cookie('pubmeitu_orderby', $orderBy);
        }
        $info['orderby'] = $orderBy;

        //如果没有设置 单图套图 Cookie
        $multi = I('get.multi');
        if (empty($multi)) {
            $multi = cookie('pubmeitu_multi');
        } else {
            cookie('pubmeitu_multi', $multi);
        }

        //去除单图和套图的查询条件
        $single = 0;
        if(empty($multi) || $multi == 3){
            $single = '99';
        }
        $this->assign("multi", $multi);

        $multi = $multi == '1' ? true : false;
        $info['multi'] = $multi;

        //获取美图列表
        $each = 40;
        //搜索功能
        $keyword = I("get.keyword");
        if (!empty($keyword)) {
            if (!checkKeyword($keyword)) {
                $this->_error();
            }
            $keyword = remove_xss($keyword);
        }
        $meitu = $this->getMeiTuList($each, $keyword, $multi, $single);
        //图片搜索无结果时
        $info['otherMeitu'] = $meitu['other'];
        $info['otherList'] = $meitu['otherList'];
        //分配图片列表和分页
        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];

        /*S-导航条件筛选URL生成*/
        //获取导航栏局部短链接
        //第一个参数为该类型下的全部类型，传入当前链接动态参数和静态参数，对对应的参数逐一替换

        //获取导航栏局部短链接
        $info["wz"] = S('Cache:Meitu:Pubmeitu:location');
        if (!$info["wz"]) {
            $location = D("Pubmeitu")->getPubMeituAttr('1');
            $info["wz"] = $this->getStaticNavUrl($location, array('statics' => 'l', 'dynamic' => 'a1'), $meitu['urls'], $multi);
            S('Cache:Meitu:Pubmeitu:location', $info["wz"], 900);
        }

        //获取导航栏风格短链接
        $info["fg"] = S('Cache:Meitu:Pubmeitu:fengge');
        if (!$info["fg"]) {
            $fengge = D("Pubmeitu")->getPubMeituAttr('2');
            $info["fg"] = $this->getStaticNavUrl($fengge, array('statics' => 'f', 'dynamic' => 'a2'), $meitu['urls'], $multi);
            S('Cache:Meitu:Pubmeitu:fengge', $info["fg"], 900);
        }

        //获取导航栏户型短链接
        $info["mj"] = S('Cache:Meitu:Pubmeitu:mianji');
        if (!$info["mj"]) {
            $mianji = D("Pubmeitu")->getPubMeituAttr('3');
            $info["mj"] = $this->getStaticNavUrl($mianji, array('statics' => 'm', 'dynamic' => 'a3'), $meitu['urls'], $multi);
            S('Cache:Meitu:Pubmeitu:mianji', $info["mj"], 900);
        }

        /*E-导航条件筛选URL生成*/
        /*S-面包屑导航动态生成绑定参数*/
        $arrays = explode('?', $meitu['urls']['dynamic']);
        $arrays = array_filter(explode('&', $arrays[1]));
        $count = count(array_filter($meitu['params']));
        //下面的foreach循环：$key为该参数 //$value为该参数的值，根据该参数，传入函数将该参数的值设置为0，就是绑定的参数对应的链接
        foreach ($meitu["params"] as $key => $value) {
            switch ($key) {
                case in_array($key, array('l', 'a1')):
                    $key = 'location';
                    $sub = $this->getStaticSelectedUrl('l', $value, $meitu["urls"]['statics'], $count, $info["wz"], $multi);
                    $info["params"]['location'] = $sub;
                    break;
                case in_array($key, array('f', 'a2')):
                    $key = 'fengge';
                    $sub = $this->getStaticSelectedUrl('f', $value, $meitu["urls"]['statics'], $count, $info["fg"], $multi);
                    $info["params"]['fengge'] = $sub;
                    break;
                case in_array($key, array('m', 'a3')):
                    $key = 'mianji';
                    $sub = $this->getStaticSelectedUrl('m', $value, $meitu["urls"]['statics'], $count, $info["mj"], $multi);
                    $info["params"]['mianji'] = $sub;
                    break;
            }
            $info["navParams"][$key] = $value;
        }
        /*E-面包屑导航动态生成绑定参数*/

        /*S—SEO标题关键字描述相关*/

        if (!empty($keyword)) {
            $this->assign("keyword", $keyword);
            $keys["title"] = "搜索结果页 - 齐装网";
            $keys["keywords"] = "";
            $keys["description"] = "";
        } else {
            $mianji = isset($info['params']['mianji']['name'])?$info['params']['mianji']['name']:'';
            $fengge = isset($info['params']['fengge']['name'])?$info['params']['fengge']['name']:'';
            $location = isset($info['params']['location']['name'])?$info['params']['location']['name']:'';
            $all_condition_name = $mianji . $fengge .$location;

            $keys["title"] = $all_condition_name . '公装装修效果图_' . $all_condition_name . '工装装修效果图_'.$all_condition_name.'公装图片-齐装网装修效果图';
            $keys["keywords"] = $all_condition_name . '公装装修效果图,' . $all_condition_name . '工装装修图片,' . $all_condition_name . '公装图片';
            $keys["description"] = '齐装网' . $all_condition_name . '公装装修效果图专区，提供国内外流行的' . $all_condition_name . '公装设计效果图，更多' . $all_condition_name . '公装图片尽在齐装网装修效果图频道';
        }
        $this->assign("keys", $keys);
        $info["params"] = array_filter($info["params"]);

        /*E—SEO标题关键字描述相关*/
        /*S-底部设计浮动框*/
        $t = T("Common@Order/zb_bottom_s");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s", $zb_bottom_s);
        //获取是否显示获取报价弹层
        if (!isset($_COOKIE["w_index"])) {
            setcookie("w_index", 1, time() + (3600 * 24), '/', '.' . APP_HTTP_DOMAIN);
            $this->assign("openSJBJ", true);
        }
        /*E-底部设计浮动框*/


        /*S-友情链接模块：以下链接添加友情链接模块*/
        $linktypes = S('Home:Pubmeitu:FriendLinkCategory');
        if (empty($linktypes)) {
            $linkcategory = D('FriendLinkCategory')->getFriendLinkCategoryList(['link_page' => ['like', 'gongzhuang%']]);
            foreach ($linkcategory as $key => $value) {
                if (!empty($value['link_page_url'])) {
                    $linktypes[$value['link_page_url']] = $value['link_page'];
                }
            }
            S('Home:Pubmeitu:FriendLinkCategory', $linktypes, 360000);
        }
        $type = '';
        foreach ($linktypes as $key => $value) {
            $count = 0;
            str_ireplace($key, '&###&', $_SERVER['REQUEST_URI'], $count);
            if ($count > 0) {
                $type = $value;
                break;
            }
        }
        if (trim($_SERVER['REQUEST_URI'], '/') == 'gongzhuang') {
            $type = 'gongzhuang';
        }
        if (!empty($type)) {
            if ($meitu['current'] == 1 && $meitu['current'] == 1) {
                $friendLink['link'] = S('Cache:Meitu:Pubmeitu:Friendlink:link-' . $type);
                if (!$friendLink['link']) {
                    $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001', '1', $type);
                    S('Cache:Meitu:Pubmeitu:Friendlink:link-' . $type, $friendLink['link'], 900);
                }
            }

            $friendLink['tags'] = S('Cache:Meitu:Pubmeitu:Friendlink:tags-' . $type);
            if (!$friendLink['tags']) {
                $friendLink['tags'] = D('Friendlink')->getFriendLinkList('000001', '3', $type);
                S('Cache:Meitu:Pubmeitu:Friendlink:tags-' . $type, $friendLink['tags'], 900);
            }

            $this->assign('friendLink', $friendLink);
        }

        /*E-友情链接模块：以下链接添加友情链接模块*/

        /*S-时间和人气排序URL拼接*/
        if (false === strpos($_SERVER['REQUEST_URI'], '?')) {
            $info['order']['hot'] = $_SERVER['REQUEST_URI'] . '?order=hot';
            $info['order']['new'] = $_SERVER['REQUEST_URI'] . '?order=new';
        } else {
            if (false != strpos($_SERVER['REQUEST_URI'], 'order=hot')) {
                $info['order']['hot'] = $_SERVER['REQUEST_URI'];
                $info['order']['new'] = str_ireplace('order=hot', 'order=new', $_SERVER['REQUEST_URI']);
            } elseif (false != strpos($_SERVER['REQUEST_URI'], 'order=new')) {
                $info['order']['hot'] = str_ireplace('order=new', 'order=hot', $_SERVER['REQUEST_URI']);
                $info['order']['new'] = $_SERVER['REQUEST_URI'];
            } else {
                $info['order']['hot'] = $_SERVER['REQUEST_URI'] . '&order=hot';
                $info['order']['new'] = $_SERVER['REQUEST_URI'] . '&order=new';
            }
        }
        /*E-时间和人气排序URL拼接*/

        if (!isset($_GET['keyword']) && !isset($_GET['a1'])) {
            $pattern = '/^\/gongzhuang-(l[\d+]+f[\d+]+m[\d+]+)/';
            $result = preg_replace($pattern, '', $_SERVER['REQUEST_URI'], '-1', $count);
            if (empty($result) || $count == 0) {
                if ($count == 0) {
                    $canonical = '/gongzhuang/';
                } else {
                    $canonical = $meitu['urls']['statics'];
                }
            } else {
                $canonical = $meitu['urls']['statics'];
                $pattern = '/^p[\d+]+/';
                preg_match($pattern, $result, $matche);
                if (!empty($matche['0'])) {
                    if ($i == 0) {
                        $canonical = '/gongzhuang/';
                    } else {
                        $canonical = $meitu['urls']['statics'];
                    }
                } else {
                    $pattern = '/^q[\d+]+/';
                    preg_match($pattern, $result, $matche);
                    if (!empty($matche['0'])) {
                        $canonical = $meitu['urls']['statics'] . 'q1';
                    }
                }
            }
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . 'meitu.' . C('QZ_YUMING') . $canonical;
            $info['header']['mobile_agent'] = $this->SCHEME_HOST['scheme_full'] . 'm.' . C('QZ_YUMING') . '/meitu' . $canonical;
        }

        //图片图集切换URL
        $statics = $meitu['urls']['statics'];
        if ('/gongzhuang-l0f0m0' == $statics && true == $multi) {
            $statics = '/gongzhuang/';
        }
        $this->assign("statics", $statics);

        /*S-初始化图片图集Tab框*/
        $pattern = '/q\d+/i';
        preg_replace($pattern, '', $_SERVER['REQUEST_URI'], $limit = -1, $count);
        if (isset($_GET['q']) || $count > 0) {
            $info['tab'] = 'map';
        }
        /*E-初始化图片图集Tab框*/
        $info['otherText'] = '';
        foreach ($info['params'] as $val) {
            if (!empty($val['name'])) {
                $info['otherText'] .= $val['name'] . '/';
            }
        }
        $info['otherText'] = substr($info['otherText'], 0, -1);
        $this->assign("info", $info);
        //不可去掉pubmeitulist，否则会出现大小写问题
        $this->display("pubmeitulist_p260");
    }

    //美图详情页
    public function pubMeituInfo()
    {
        if (ismobile()) {
            header("Location: " . $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . '/meitu' . $_SERVER['REQUEST_URI']);
            exit();
        }
        $p = intval(I("get.id"));
        //判断来源
        $referer = parse_url($_SERVER['HTTP_REFERER'])['path'];
        $params = array();
        if (!empty($referer)) {
            $match = array();
            if (1 === preg_match('/gongzhuang-(l([\d+]+)f([\d+]+)m([\d+]+)(p[\d+]+)?+(q[\d+]+)?)$/', $referer, $match)) {
                $params = array(
                    'location' => intval($match['2']),
                    'fengge' => intval($match['3']),
                    'mianji' => intval($match['4'])
                );
            } else if ('/' === $referer) {
                $temp = intval(cookie('index_pubmeitu_params'));
                if ($temp > 0) {
                    $params = array(
                        'location' => intval($temp),
                        'fengge' => 0,
                        'mianji' => 0
                    );
                }
            } else if (1 === preg_match('/g(\d+)\.html/', $referer, $match)) {
                $temp = json_decode(cookie('meitu_terminal_params'));
                if (array_sum($temp) > 0) {
                    $params = array(
                        'location' => intval($temp['0']),
                        'fengge' => intval($temp['1']),
                        'mianji' => intval($temp['2'])
                    );
                }
            }
        }
        cookie('meitu_terminal_params', null);
        if (intval(I("get.type")) == 1) {
            $params = array();
        }
        $cacheKey = $p . md5(serialize($params));
        $info = S("Cache:Meitu:Pubmeitu:pubMeituInfo:info:" . $cacheKey);
        if (!$info) {
            //查询当前美图信息
            $map = array(
                'id' => intval($p)
            );
            $info["case"]["now"] = $this->getMeituInfo($map, $params);
            if (empty($info["case"]["now"])) {
                $this->_error();
            }
            //循环将每张图片的描述格式化
            if ($info["case"]['now']['child']) {
                $info["case"]['now']['imgdescription'] = html_entity_decode($info["case"]['now']['imgdescription']);
                foreach ($info["case"]['now']['child'] as $k => $v) {
                    if ($v['imgdescription']) {
                        $info["case"]['now']['child'][$k]['imgdescription'] = html_entity_decode($v['imgdescription']);
                    }
                }
            }
            $info['case']['attribute'] = $this->getMeituAttribute($info["case"]['now']);
            S("Cache:Meitu:Pubmeitu:pubMeituInfo:info:" . $cacheKey, $info, 3600);
        }

        //相关美图缓存
        $relation = S('Meitu:Pubmeitu:pubMeituInfo:relation:' . $p);
        if (empty($relation)) {
            $relation["relatedMeitu"] = $this->getRelatedMeitu($info["case"]['now']);
            S('Meitu:Pubmeitu:pubMeituInfo:relation:' . $p, $relation);
        }
        $info = array_merge($info, $relation);

        //公用缓存
        $other = S('Meitu:Pubmeitu:pubMeituInfo:other');
        if (empty($other)) {
            //获取标签
            $other["newTags"] = D("Common/Tags")->getHotTags('2', '15');
            S('Meitu:Pubmeitu:pubMeituInfo:other', $other, 3600);
        }
        $info = array_merge($info, $other);

        //获取小导航
        $info['attrhref'] = S('Home:PubMeitu:PubMeituInfo:Attrhref');
        if (empty($info['attrhref'])) {
            $info['attrhref'] = $this->getMeituAttributeHref(1);
            S('Home:PubMeitu:PubMeituInfo:Attrhref', $info['attrhref'], 3600);
        }

        //查看推荐图集
        $cache_recommend_key_postfix = $info['case']['attribute']['location']['id'] . ':' . $info['case']['attribute']['fengge']['id'];
        $info['recommend'] = S('Cache:Pubmeitu:Case:Recommend:' . $cache_recommend_key_postfix);
        if (empty($info['recommend'])) {
            $info['recommend'] = D('Pubmeitu')->getRecommendMeituByAttr($info['case']['attribute']['location']['id'], $info['case']['attribute']['fengge']['id']);
            //如果数据不够10条 , 取最新的数据
            $count = count($info['recommend']);
            if ($count < 10) {
                $data = D('Pubmeitu')->getRecommendMeituByAttr('', '', '', (10 - $count), 'id desc');
                $info['recommend'] = array_merge($info['recommend'], $data);
                unset($count);
                unset($data);
            }
            S('Cache:Pubmeitu:Case:Recommend:' . $cache_recommend_key_postfix, $info['recommend'], 21600);
        }

        $info["collect"] = false;
        //查询用户是否关注过该案例
        if (isset($_SESSION["u_userInfo"])) {
            $uid = $_SESSION['u_userInfo']['id'];
            $count = D("Usercollect")->getCollectCount($p, $uid, 4);
            if ($count > 0) {
                $info["collect"] = true;
            }
        } else {
            $uid = '';
        }

        //单图
        if ($info['case']['now']['is_single'] == '1') {
            $template = 'caseinfo_single_p260';

            if ($info["collect"]) {
                $info['case']['now']['collect'] = $info['case']['now']['id'];
            } else {
                $info['case']['now']['collect'] = null;
            }

            unset($info['case']['now']['child']);
            //定义要查询的前后图集数量
            $preNum = $nextNum = 7;
            //查询前面的单图（id大于当前单图）
            $singleCaseList['pre'] = D('Pubmeitu')->getSingleCases($p, 'pre', $preNum, $params, $uid);
            krsort($singleCaseList['pre']);

            //上一页，下一页
            $imgList['pre'] = $imgList['next'] = array();

            //如果前面单图数量不足（id大于当前单图的）
            if (count($singleCaseList['pre']) < $preNum) {

                //查询的后面的单图数量
                $nextNum = $nextNum + ($preNum - count($singleCaseList['pre']));

                //查询后面的单图（id小于当前单图）
                $singleCaseList['next'] = D('Pubmeitu')->getSingleCases($p, 'next', $nextNum, $params, $uid);

                //如果前面单图数量为空
                if (empty($singleCaseList['pre'])) {
                    //如果后面单图也为空（前后都为空）
                    if (empty($singleCaseList['next'])) {
                        //上一页取当前页
                        $imgList['pre'] = $info['case']['now'];
                    } else {
                        //如果后面单图数量不足（说明所有符合条件的图片都查出来了，上一页就取后面图集（前面的为空了）的最后一个）
                        if (count($singleCaseList['next']) < $nextNum) {
                            $imgList['pre'] = $singleCaseList['next'][count($singleCaseList['next']) - 1];
                        } else {
                            //该条件下的第一张单图
                            $temp = D("Pubmeitu")->getFirstOrLastMeitu("first", $params, 1);
                            if (!empty($temp)) {
                                $imgList['pre'] = array(
                                    'id' => $temp['id'],
                                    'title' => $temp['title'],
                                    'time' => $temp['time'],
                                    'likes' => $temp['likes'],
                                    'img_path' => $temp['child']['0']['img_path'],
                                    'img_host' => $temp['child']['0']['img_host'],
                                    'imgdescription' => $temp['child']['0']['imgdescription'],
                                    'top_title' => $temp['title'] . '-齐装网装修效果图'
                                );
                            }
                        }
                    }
                    //如果前面单图数量不为空
                } else {
                    //如果后面单图为空(说明当前单图是ID最小)，上一页取前面的图集
                    if (empty($singleCaseList['next'])) {
                        $imgList['pre'] = $singleCaseList['pre'][0];
                    } else {
                        //如果后面单图数量不足（说明所有符合条件的图片都查出来了，上一页就取前面图集的第一个）
                        if (count($singleCaseList['next']) < $nextNum) {
                            $imgList['pre'] = $singleCaseList['pre'][0];
                        } else {
                            //该条件下的第一张单图
                            $temp = D("Pubmeitu")->getFirstOrLastMeitu("first", $params, 1);
                            if (!empty($temp)) {
                                $imgList['pre'] = array(
                                    'id' => $temp['id'],
                                    'title' => $temp['title'],
                                    'time' => $temp['time'],
                                    'likes' => $temp['likes'],
                                    'img_path' => $temp['child']['0']['img_path'],
                                    'img_host' => $temp['child']['0']['img_host'],
                                    'imgdescription' => $temp['child']['0']['imgdescription'],
                                    'top_title' => $temp['title'] . '-齐装网装修效果图'
                                );
                            }
                        }
                    }
                }
            } else {
                $singleCaseList['next'] = D('Pubmeitu')->getSingleCases($p, 'next', $nextNum, $params, $uid);
                //上一个单图取第一条
                $tmp_preid = count($singleCaseList['pre']) - 1;
                $imgList['pre'] = $singleCaseList['pre'][$tmp_preid];
                unset($singleCaseList['pre'][$tmp_preid]);
            }

            //如果下一单图数量不足（id小于当前单图）
            if (count($singleCaseList['next']) < $nextNum) {
                $imgList['next'] = array();
                //取该条件下的最后一个单图
                $temp = D("Pubmeitu")->getFirstOrLastMeitu("last", $params, 1);
                if (!empty($temp)) {
                    $imgList['next'] = array(
                        'id' => $temp['id'],
                        'title' => $temp['title'],
                        'time' => $temp['time'],
                        'likes' => $temp['likes'],
                        'img_path' => $temp['child']['0']['img_path'],
                        'img_host' => $temp['child']['0']['img_host'],
                        'imgdescription' => $temp['child']['0']['imgdescription'],
                        'top_title' => $temp['title'] . '-齐装网装修效果图'
                    );
                }
            } else {
                $tmp_lastid = count($singleCaseList['next']) - 1;
                $imgList['next'] = $singleCaseList['next'][$tmp_lastid];
                unset($singleCaseList['next'][$tmp_lastid]);
            }

            $newSingle = array_merge($singleCaseList['pre'], $singleCaseList['next']);

            array_unshift($newSingle, $info['case']['now']);

            $this->assign('singleCaseList', $newSingle);
            $this->assign('imgList', $imgList);
        } else {
            //套图
            $template = 'caseinfo_p260';

            $info['case']['prv'] = S('C:Pubmeitu:pubMeituInfo:prv:' . $cacheKey);
            if (empty($info['case']['prv'])) {
                //上一个图集（id大于当前图集）
                $map = array(
                    'id' => array('GT', $p),
                    'is_single' => 0
                );
                $info['case']['prv'] = $this->getMeituInfo($map, $params, 'asc');
                //如果上一个图集（id越来越大）为空，则获取第一个图集
                if (empty($info['case']['prv'])) {
                    $info['case']['prv'] = D("Pubmeitu")->getFirstOrLastMeitu("first", $params, '0');
                }
                S('C:Pubmeitu:pubMeituInfo:prv:' . $cacheKey, $info['case']['prv'], 900);
            }

            $info['case']['next'] = S('C:Pubmeitu:pubMeituInfo:next:' . $cacheKey);
            if (empty($info['case']['next'])) {
                //下一个图集（id小于当前图集）
                $map = array(
                    'id' => array('LT', $p),
                    'is_single' => 0
                );
                $info['case']['next'] = $this->getMeituInfo($map, $params, 'desc');
                //如果下一个图集（id越来越小）为空，则获取最后一个图集
                if (empty($info['case']['next'])) {
                    $info['case']['next'] = D("Pubmeitu")->getFirstOrLastMeitu("last", $params, '0');
                }
                S('C:Pubmeitu:pubMeituInfo:next:' . $cacheKey, $info['case']['next'], 900);
            }
        }

        //seo 标题/描述/关键字
        $keys["title"] = $info["case"]["now"]["title"] . "-齐装网装修效果图";
        $keys["keywords"] = $info["case"]["now"]["title"];
        $keys["description"] = '齐装网装修效果图频道，提供' . date('Y') . '全新' . $info["case"]["now"]["title"] . '，定期更新上百套' . $info["case"]["now"]["title"] . '，为您带来精彩的装修设计灵感。';
        $this->assign("keys", $keys);

        //获取是否显示获取报价弹层
        if (!isset($_COOKIE["meitu_tips"])) {
            $this->assign("isMeituTip", true);
        }

        //流量部推广统计
        $this->promoStats($p);

        //根据标签ID获取标签名
        if (!empty($info['case']['now']['tags'])) {
            $info['case']['tagsInfo'] = S('Meitu:caseInfo:tagsInfo:' . md5($info['case']['now']['tags']));
            if (empty($info['case']['tagsInfo'])) {
                $info['case']['tagsInfo'] = D('Tags')->getTagsByTagsId($info['case']['now']['tags']);
                S('Meitu:caseInfo:tagsInfo:' . md5($info['case']['now']['tags']), $info['case']['tagsInfo'], 120);
            }
        }

        $this->assign("relatedMeitu", $info["relatedMeitu"]);
        $this->assign("html_type", 'gongzhuang');
        $this->assign("caseInfo", $info);
        $this->assign("params", json_encode(array_values($params)));
        $this->display($template);
    }

    //喜欢
    public function like()
    {
        //判断是否登录
        if (!isset($_SESSION["u_userInfo"])) {
            //die('login');
        }
        $tempData = I('post.');
        $id = $tempData['id'];

        if (empty($id) || !is_numeric($id)) {
            $this->ajaxReturn(array("data" => "", "info" => "数据错误", "status" => 0));
        } else {
            //喜欢数+1
            M("pubmeitu")->where(array('id' => $id))->setInc('likes');
            $this->ajaxReturn(array("data" => "", "info" => "成功", "status" => 1));
        }
    }

    //设置筛选Cookie
    public function setListCookie($orderBy = '', $multi = '')
    {
        if (!empty($orderBy)) {
            cookie('pubmeitu_orderby', $orderBy);
        }
        if (!empty($multi)) {
            cookie('pubmeitu_multi', $multi);
        }
    }

    /**
     * 获取美图信息
     * @param  [type] $map    查询条件
     * @param  [type] $params 额外参数
     * @param  string $order 排序
     */
    private function getMeituInfo($map, $params, $order = 'asc')
    {
        $temp = D("Pubmeitu")->getMeituInfo($map, $params, $order);
        $result = array();
        foreach ($temp as $key => $value) {
            if (!isset($result['id'])) {
                $result = $value;
            }
            $result["child"][] = $value;
            $result["count"]++;
        }
        return $result;
    }

    //流量部推广统计
    public function promoStats($id)
    {
        //获取Cookie
        $isMark = cookie('contentPromoMark');

        //如果Cookie不存在
        if (empty($isMark['module'])) {
            //过期时间 = 今天最后一秒时间戳 - 当前时间戳
            $expireTime = strtotime(date('Y-m-d') . ' 23:59:59') - time();
            $cookieVar = array('module' => 'pubmeitu', 'id' => $id);
            //指定cookie保存时间
            cookie('contentPromoMark', $cookieVar, array('expire' => $expireTime, 'domain' => '.' . C('QZ_YUMING')));
        }
    }

    //获取相关美图
    private function getRelatedMeitu($info)
    {
        $map['fengge'] = $info['fengge'];
        $map['huxing'] = $info['huxing'];
        $map['location'] = $info['location'];
        $map['is_single'] = 0;//只获取图集(p.2.12.6)
        $id = $info['id'];
        $result = D('Pubmeitu')->getRelatedMeitu($map, $id);
        return $result;
    }

    /**
     * 获取导航URL
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
     * @param  [type] $multi [图片图集]
     * @return [type]        [description]
     */
    public function getStaticNavUrl($datas, $type, $urls, $multi)
    {
        //参数替换
        $pattern = '/' . $type['statics'] . '\d+/i';
        foreach ($datas as $key => $value) {
            $datas[$key]["link"] = preg_replace($pattern, $type['statics'] . $value["id"], $urls['statics']);
            $datas[$key]["nofollow"] = 'follow';
            if ($multi == true) {
                $datas[$key]["link"] = $datas[$key]["link"] . 'q1';
            }
        }
        return $datas;
    }

    /**
     * [getNavUrl 获取导航URL]
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
     * @return [type]        [description]
     */
    public function getNavUrl($datas, $type, $urls, $multi)
    {
        //去掉图集的参数当前的
        $pattern = '/q\d+/i';
        $urls['statics'] = preg_replace($pattern, '', $urls['statics'], $limit = -1, $count);
        $pattern = '/&q=\d+/i';
        $urls['dynamic'] = preg_replace($pattern, '', $urls['dynamic'], $limit = -1, $count);

        //判断是否带有参数a1
        if (!isset($_GET['a1'])) {
            //去掉当前的
            $pattern = '/' . $type['statics'] . '\d+/i';
            //去掉自己之后的链接
            $str = preg_replace($pattern, '', $urls['statics'], $limit = -1, $count);
            //如果去掉自己之后的分类数后分类ID组合小于等于零,说明是初始化
            if (preg_replace('/\D/s', '', $str) > 0) {
                $reg = '/' . $type['dynamic'] . '=\d+/i';
                foreach ($datas as $key => $value) {
                    $datas[$key]["link"] = preg_replace($reg, $type['dynamic'] . '=' . $value["id"], $urls['dynamic']);
                    if ($multi == true) {
                        $datas[$key]["link"] = $datas[$key]["link"] . '&q=1';
                    }
                }
            } else {
                $reg = '/' . $type['statics'] . '\d+/i';
                foreach ($datas as $key => $value) {
                    $datas[$key]["link"] = preg_replace($reg, $type['statics'] . $value["id"], $urls['statics']);
                    $datas[$key]["nofollow"] = 'follow';
                    if ($multi == true) {
                        $datas[$key]["link"] = $datas[$key]["link"] . 'q1';
                    }
                }
            }
        } else {
            $reg = '/' . $type['dynamic'] . '=\d+/i';
            $page = '/&p=\d+/i';
            $urls['dynamic'] = preg_replace($page, '', $urls['dynamic']);
            foreach ($datas as $key => $value) {
                $datas[$key]["link"] = preg_replace($reg, $type['dynamic'] . '=' . $value["id"], $urls['dynamic']);
                if ($multi == true) {
                    $datas[$key]["link"] = $datas[$key]["link"] . '&q=1';
                }
            }
        }
        return $datas;
    }

    /**
     * 获取面包屑导航已选择条件
     * @param  [type] $type  [类型]
     * @param  [type] $value [类型的值]
     * @param  [type] $url   [当前页面URL]
     * @param  [type] $count [条件个数]
     * @param  [type] $info  [description]
     * @return [type]        [description]
     */
    public function getStaticSelectedUrl($type, $value, $url, $count, $info, $multi, $link = '/gongzhuang')
    {
        $result = array();
        if (false == $multi) {
            foreach ($info as $k => $v) {
                if ($v['id'] == $value || $value == 0) {
                    $reg = '/' . $type . '\d+/i';
                    $link = preg_replace($reg, $type . "0", $url);
                    $result = array(
                        'name' =>  $value == 0 ? '' : $v['name'],
                        'link' => $link
                    );
                }
            }
        } else {
            //判断有几个参数，如果只有一个参数直接返回/gongzhuang/
            if ($count <= 1) {
                $result = array(
                    'name' => '',
                    'link' => $link
                );
                foreach ($info as $k => $v) {
                    if ($v['id'] == $value) {
                        $result = array(
                            'name' => $v['name'],
                            'link' => $link
                        );
                    }
                }
            } else {
                foreach ($info as $k => $v) {
                    if ($v['id'] == $value || $value == 0) {
                        $reg = '/' . $type . '\d+/i';
                        $link = preg_replace($reg, $type . "0", $url);
                        $result = array(
                            'name' => $v['name'],
                            'link' => $link
                        );
                    }
                }
            }
        }
        return $result;
    }

    /**
     * [getSelectedUrl 获取面包屑导航已选择条件]
     * @param  [type] $type  [类型]
     * @param  [type] $value [类型的值]
     * @param  [type] $url   [当前页面URL]
     * @param  [type] $count [条件个数]
     * @param  [type] $info  [description]
     * @return [type]        [description]
     */
    public function getSelectedUrl($type, $value, $url, $count, $info, $link = '/gongzhuang')
    {
        //判断有几个参数，如果只有一个参数直接返回/meitu/gongzhuang/
        $result = array();
        if ($count <= 1) {
            foreach ($info as $k => $v) {
                if ($v['id'] == $value) {
                    $result = array(
                        'name' => $v['name'],
                        'link' => $link
                    );
                }
            }
        } else {
            foreach ($info as $k => $v) {
                if ($v['id'] == $value) {
                    $reg = '/' . $type . '=\d+/i';
                    $link = preg_replace($reg, $type . "=0", $url);
                    $result = array(
                        'name' => $v['name'],
                        'link' => $link
                    );
                }
            }
        }
        return $result;
    }


    /**
     * [getMeituAttributeHref 获取美图属性的链接]
     * @param  integer $type [属性，值为0,1,2,3]
     * @return [type]        [description]
     */
    private function getMeituAttributeHref($type = 0)
    {
        if (!empty($type)) {
            $return = [];
            $field = 'id,name';
            $map['enabled'] = 1;
            switch ($type) {
                case 1:
                    $map['type'] = 1;
                    $result = M("pubmeitu_att")->field($field)->where($map)->select();
                    foreach ($result as $key => $value) {
                        $return[] = ['name' => $value['name'], 'href' => '/gongzhuang-l' . $value['id'] . 'f0m0'];
                    }
                    break;
                case 2:
                    $map['type'] = 2;
                    $result = M("pubmeitu_att")->field($field)->where($map)->select();
                    foreach ($result as $key => $value) {
                        $return[] = ['name' => $value['name'], 'href' => '/gongzhuang-l0f' . $value['id'] . 'm0'];
                    }
                    break;
                case 3:
                    $map['type'] = 3;
                    $result = M("pubmeitu_att")->field($field)->where($map)->select();
                    foreach ($result as $key => $value) {
                        $return[] = ['name' => $value['name'], 'href' => '/gongzhuang-l0f0m' . $value['id']];
                    }
                    break;
                default:
                    break;
            }
            return $return;
        }
        return false;
    }

    /**
     * [getMeituAttribute 获取美图属性的第一个属性的详细信息]
     * @param  [type] $meitu [description]
     * @return [type]        [description]
     */
    private function getMeituAttribute($meitu)
    {
        $field = 'id,name';
        if (!empty($meitu['location'])) {
            $location = M("pubmeitu_att")->field($field)->where(['id' => explode(',', $meitu['location'])[0]])->find();
            if (!empty($location)) {
                $location['href'] = '/gongzhuang-l' . $location['id'] . 'f0m0';
                $result['location'] = $location;
            }
        }

        if (!empty($meitu['fengge'])) {
            $fengge = M("pubmeitu_att")->field($field)->where(['id' => explode(',', $meitu['fengge'])[0]])->find();
            if (!empty($fengge)) {
                $fengge['href'] = '/gongzhuang-l0f' . $fengge['id'] . 'm0';
                $result['fengge'] = $fengge;
            }
        }
        if (!empty($meitu['mianji'])) {
            $mianji = M("pubmeitu_att")->field($field)->where(['id' => explode(',', $meitu['mianji'])[0]])->find();
            if (!empty($mianji)) {
                $mianji['href'] = '/gongzhuang-l0f0m' . $mianji['id'];
                $result['mianji'] = $mianji;
            }
        }
        return $result;
    }

    /**
     * [getMeiTuList 获取美图列表]
     * @param  integer $each [每页显示数目]
     * @param  [type]  $keyword [搜索关键字]
     * @return [type]           [description]
     */
    private function getMeiTuList($each = 40, $keyword, $multi, $single="")
    {
        if ($multi == true) {
            $pageTemp = 'p';
            $isSingle = '0';
        } else {
            $pageTemp = 'p';
            $isSingle = '1';
        }
        if($single == "99"){
            $isSingle = "99";
        }

        import('Library.Org.Page.ShortPage');

        $orderby = cookie('pubmeitu_orderby');
        if ($orderby == 'hot') {
            $order = '`likes` desc';
        }

        //获取单图的分页
        if (!isset($_GET['a1'])) {
            $options = array(
                'prefix' => '/gongzhuang-',
                'dynamic' => '/gongzhuang/',
                'short' => array('l' => 'a1', 'f' => 'a2', 'm' => 'a3'),
                'sort' => array('l', 'f', 'm', $pageTemp)
            );
        }
        $Page = new \Page($each, $options, $sline = false, $dline = true, $p = $pageTemp);

        $Page->setConfig('theme', '%UP_PAGE%%FIRST%%OMIT%%LINK_PAGE%%OMIT_PAGE%%END%%DOWN_PAGE%');
        $result = $Page->analyse();

        if (array_key_exists('a1', $result['param'])) {
            $params['l'] = $result['param']['a1'];
            $params['f'] = $result['param']['a2'];
            $params['m'] = $result['param']['a3'];
        } else {
            $params = $result['param'];
        }

        //404判断
        if ( isset($params['l']) && isset($params['f']) && isset($params['m']) ){
            if ($params['l'] != 0){    //检测类型
                $mapl = [
                    'enabled' => ['EQ', 1],
                    'type'    => ['EQ', 1],
                    'id'      => ['EQ', $params['l']],
                ];
                $pubmeituType = D("PubmeituAtt")->where($mapl)->find();
                if (empty($pubmeituType)){
                    $this->_empty();exit();
                }
            }
            if ($params['f'] != 0) {   //检测风格
                $mapf = [
                    'enabled' => ['EQ', 1],
                    'type'    => ['EQ', 2],
                    'id'      => ['EQ', $params['f']],
                ];
                $pubmeituFengge = D("PubmeituAtt")->where($mapf)->find();
                if (empty($pubmeituFengge)){
                    $this->_empty();exit();
                }
            }
            if ($params['m'] != 0) {   //检测面积
                $mapm = [
                    'enabled' => ['EQ', 1],
                    'type'    => ['EQ', 3],
                    'id'      => ['EQ', $params['m']],
                ];
                $pubmeituMianji = D("PubmeituAtt")->where($mapm)->find();
                if (empty($pubmeituMianji)){
                    $this->_empty();exit();
                }
            }
        }

        $count = D("Pubmeitu")->getPubMeiTuListCount($params["l"], $params["f"], $params["m"], $keyword, $isSingle);

        if ($count > 0) {
            $other = 0;
            $show = $Page->show($count);
            $list = D("Pubmeitu")->getPubMeiTuList(($Page->nowPage - 1) * $each, $each, $params["l"], $params["f"], $params["m"], $keyword, $isSingle, $order);
            foreach ($list as $key => $value) {
                //取每个分类的第一个元素
                $exp = array_filter(explode(",", $value["wz"]));
                $list[$key]["wz"] = $exp[0];

                $exp = array_filter(explode(",", $value["fg"]));
                $list[$key]["fg"] = $exp[0];

                $exp = array_filter(explode(",", $value["mj"]));
                $list[$key]["mj"] = $exp[0];
            }
        } else {

            $count = D("Pubmeitu")->getPubMeiTuListCount($params["l"], $params["f"], 0, $keyword, $isSingle);
            if ($count > 0) {
                $other = 1;
                $otherList = $this->getOtherMeituList(($Page->nowPage - 1) * $each, $each, $params["l"], $params["f"], 0, $keyword, $isSingle, $order);
            } else {
                $count = D("Pubmeitu")->getPubMeiTuListCount($params["l"], 0, 0, $keyword, $isSingle);
                if ($count > 0) {
                    $other = 1;
                    $otherList = $this->getOtherMeituList(($Page->nowPage - 1) * $each, $each, $params["l"], 0, 0, $keyword, $isSingle, $order);
                } else {
                    $count = D("Pubmeitu")->getPubMeiTuListCount(0, 0, 0, $keyword, $isSingle);
                    if ($count > 0) {
                        $other = 1;
                        $otherList = $this->getOtherMeituList(($Page->nowPage - 1) * $each, $each, 0, 0, 0, $keyword, $isSingle, $order);
                    }
                }
            }

        }
        return array("list" => $list, "other" => $other, "otherList" => $otherList, "page" => $show, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);
    }

    private function getOtherMeituList($page, $each, $l, $f, $m, $keyword, $isSingle, $order)
    {
        $list = D("Pubmeitu")->getOtherPubMeiTuList($page, $each, $l, $f, $m, $keyword, $isSingle, $order);
        foreach ($list as $key => $value) {
            //取每个分类的第一个元素
            $exp = array_filter(explode(",", $value["wz"]));
            $list[$key]["wz"] = $exp[0];

            $exp = array_filter(explode(",", $value["fg"]));
            $list[$key]["fg"] = $exp[0];

            $exp = array_filter(explode(",", $value["mj"]));
            $list[$key]["mj"] = $exp[0];
        }
        return $list;
    }
}
