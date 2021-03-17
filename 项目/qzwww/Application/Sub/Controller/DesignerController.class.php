<?php

namespace Sub\Controller;

use Common\Model\Logic\DesignerLogicModel;
use Sub\Common\Controller\SubBaseController;

class DesignerController extends SubBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/', $uri, $m);
        if (count($m) == 0) {
            preg_match('/\/$/', $uri, $m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $this->SCHEME_HOST['scheme_full']. $this->bm . "." . C("QZ_YUMING") . $uri . "/");
            }
        }
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'xgt');
        $this->assign('serch_type', '装修案例');
        $this->assign('holdercontent', '全国超过十万家装修公司为您免费设计');
        //导航栏标识
        $this->assign("tabIndex", 2);
        $this->assign("choose_menu", 'xgt');
    }

    public function index()
    {
        $reqPrice = I("get.price", 1);
        $sortValue = I("get.sort");
        $bm = $this->bm;
        $designersinfo = S('Cache:DesignersInfo:' . $bm);
        $cityInfo = $this->cityInfo;

        if (!$designersinfo) {
            $designersinfo["worktime"] = array(
                array('id' => '', 'name' => '不限'),
                array('id' => '1', 'name' => '应届'),
                array('id' => '2', 'name' => '一年'),
                array('id' => '3', 'name' => '两年'),
                array('id' => '4', 'name' => '三年~五年'),
                array('id' => '5', 'name' => '五年~八年'),
                array('id' => '6', 'name' => '八年~十年'),
                array('id' => '7', 'name' => '十年以上')
            );

            // $designersinfo["level"] = array(
            //     array('id' => '', 'name' => '不限'),
            //     array('id' => '1', 'name' => '设计师'),
            //     array('id' => '4', 'name' => '首席设计师'),
            //     array('id' => '6', 'name' => '设计总监'),
            // );

            $designersinfo["level"] = array(
                '0' => ['id' => '', 'name' => '不限'],
                '1' => ['id' => '1', 'name' => '设计师'],
                '4' => ['id' => '4', 'name' => '首席设计师'],
                '6' => ['id' => '6', 'name' => '设计总监'],
            );

            //设计师收费区间
            $designersinfo['price'] = [
                ['id' => '1', 'name' => '不限'],
                ['id' => '2', 'name' => '面议'],
                ['id' => '3', 'name' => '1-100元/m²'],
                ['id' => '4', 'name' => '101-200元/m²'],
                ['id' => '5', 'name' => '201-300元/m²'],
                ['id' => '6', 'name' => '301元/m²以上'],
            ];
            //获取装修风格列表
            $fg = D("Common/Fengge")->getfg();
            $top = array(
                "id" => "0",
                "name" => "不限"
            );
            array_unshift($fg, $top);
            $designersinfo["fengge"] = $fg;
            S('Cache:DesignersInfo:' . $bm, $designersinfo, 900);
            $designersinfo = S('Cache:DesignersInfo:' . $bm);
        }
        //获取当前URL，切割URL
        preg_match("/[a-z0-9]{2}-[a-z0-9]{2}-[a-z0-9]{2}/", __SELF__, $url);
        if (!empty($url)) {
            $keyword = preg_split("/-/", $url['0']);
            //赋值对应的搜索条件
            foreach ($keyword as $key => $value) {
                $condition[$value['0']] = $value['1'];
                if ($value['1'] != '0') {
                    switch ($value['0']) {
                        case 't':
                            $condition['jobtime'] = $designersinfo["worktime"][$value['1']]['name'];
                            $map['ud.jobtime'] = array("EQ", $condition['jobtime']);
                            break;
                        case 'l':
                            $condition['zw'] = $designersinfo["level"][$value['1']]['name'];
                            $map['m.zw'] = array("EQ", $condition['zw']);
                            break;
                        case 'f':
                            $condition['fengge'] = $designersinfo["fengge"][$value['1']]['name'];
                            $map['ud.fengge'] = array("like", '%' . $condition['fengge'] . '%');
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }
        } else {
            $condition['t'] = $condition['l'] = $condition['f'] = '0';
        }

        //添加价格条件
        if ($reqPrice == 2) {
            //面议，没有价格
            $map['_string'] = "ud.cost ='' or ud.cost=0";
        } elseif ($reqPrice == 3) {
            //0-100
            $map['_string'] = "substring_index(ud.cost, '-', 1)>=1 and substring_index(ud.cost, '-', 1) <=100 and ud.cost !=''";
        } elseif ($reqPrice == 4) {
            //100-200
            $map['_string'] = "substring_index(ud.cost, '-', 1)>=101 and substring_index(ud.cost, '-', 1) <=200 and ud.cost !=''";
        } elseif ($reqPrice == 5) {
            //200-300
            $map['_string'] = "substring_index(ud.cost, '-', 1)>=201 and substring_index(ud.cost, '-', 1) <=300 and ud.cost !=''";
        } elseif ($reqPrice == 6) {
            //>300
            $map['_string'] = "substring_index(ud.cost, '-', 1)>=301";
        }

        //排序
        $order = '';
        //1 作品量,2 人气值
        // u.on desc, fake,t1.time desc,count desc,t1.id desc
        if ($sortValue == 1) {
            $order = 'count desc,convert(a.pv , UNSIGNED) desc,a.id desc';
        } elseif ($sortValue == 2) {
            $order = 'convert(a.pv , UNSIGNED) desc,count desc,a.id desc';
        }
        //dump($condition);
        /*//处理搜索条件，去除“不限”
        foreach ($condition as $key => $value) {
            if($value != "不限"){
                switch ($key) {
                    case 'jobtime':
                        $map['ud.jobtime'] = array("EQ",$condition['jobtime']);
                        break;
                    case 'zw':
                        $map['t1.zw'] = array("EQ",$condition['zw']);
                        break;
                    case 'fengge':
                        $map['ud.fengge'] = array("like",'%'.$condition['fengge'].'%');
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }*/
        //dump($map);
        //拼接短链接
        $designersinfo["worktime"] = $this->getParams("t", $url, $designersinfo["worktime"], $condition['t']);
        $designersinfo["level"] = $this->getParams("l", $url, $designersinfo["level"], $condition['l']);
        $designersinfo["fengge"] = $this->getParams("f", $url, $designersinfo["fengge"], $condition['f']);
        //分页
        $pageIndex = 1;
        $pageCount = 8;
        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }

        $designerLogic = new DesignerLogicModel();
        $designers = $designerLogic->getDesignersV2($_SESSION["cityId"], $pageIndex, $pageCount, $condition, $reqPrice, $sortValue);

        //获取设计师列表
        $info["designers"] = $designers["users"];
        $info["page"] = $designers["page"];
        $this->assign("info", $info);

        //获取4个人气最高设计师
        $designersTopPvKey = 'Cache:DesignersTopPv:' . $_SESSION["cityId"];
        $designersTopPv = S($designersTopPvKey) ?: [];
        if (empty($designersTopPv)) {
            $designersTopPv = D("Common/Designer")->getCityDesigner($_SESSION["cityId"]);
            S($designersTopPvKey, $designersTopPv, 900);
        }

        //获取本站作品最多的设计师及其最新通过审核的案例
        $designersTopCaseKey = 'Cache:DesignersTopCase:' . $_SESSION["cityId"];
        $designersTopCase = S($designersTopCaseKey) ?: [];
        if (empty($designersTopCase)) {
            $designersTopCase = D("Common/Designer")->getTopCaseDesigner($_SESSION["cityId"]);
            S($designersTopCaseKey, $designersTopCase, 900);
        }


        //获取最新预约
        $lastAppointment = D("Common/Designer")->getLastAppointment($_SESSION["cityId"], 10);

        //设计师前5
        $listFive = S('Cache:Designersfive:' . $bm);
        if (!$listFive) {
            $listFive = D("Common/Designer")->getDesignerListFive($_SESSION["cityId"]);
            S('Cache:Designersfive:' . $bm, $listFive, 900);
            $listFive = S('Cache:Designersfive:' . $bm);
        }

        /*if($condition['t'] == '0' && $condition['l'] == '0' && $condition['f'] == '0'){
            $keys['title'] = $cityInfo['name']."齐装网-中国领先的装修门户网站-装修_装修公司_装修效果图";
            $keys['keywords'] = "装修公司,装修网,装修效果图,装饰,齐装网";
            $keys['description'] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比高的家居装修装饰公司，为您提供最专业的装修服务以及装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
        }else{
            $keys['title'] = $condition['jobtime'].$condition['fengge'].$condition['zw']."-齐装网";
            $keys['keywords'] = $condition['jobtime'].$condition['fengge'].$condition['zw']."-齐装网";
            $keys['description'] = "齐装网设计师频道是国内专业室内装修网站及设计招标服务平台，每日更新大量室内设计效果图、优质室内设计案例等，并提供专业的软装搭配、室内装修设计知识。";
        }*/

        $keys['title'] = $cityInfo['name'] . "装修设计师_" . $cityInfo['name'] . "室内设计师-" . $cityInfo['name'] . "齐装网";
        $keys['keywords'] = $cityInfo['name'] . "装修设计师," . $cityInfo['name'] . "室内设计师," . $cityInfo['name'] . "家装设计师";
        $keys['description'] = $cityInfo['name'] . "齐装网装修设计师频道汇集了" . $cityInfo['name'] . "工装、家装、别墅等优秀的室内设计师,按照设计师从业时间、设计师设计级别和设计师擅长风格来综合考核,供您选设计师时参考";

        if ('/designer/' == $_SERVER['REQUEST_URI']) {
            $info['head']['canonical'] = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'] . '.' . C('QZ_YUMING') . '/designer/';
        } else {
            $staticUrl = explode('?', $_SERVER['REQUEST_URI']);
            $info['head']['canonical'] = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'] . '.' . C('QZ_YUMING') . $staticUrl['0'];
        }

        //url链接
        //前3个筛选组成的url
        $baseURL = !empty($url) && isset($url[0]) ? $url[0] . '/' : '';

        //价格用
        $priceUrl = '';
        if (!empty($sortValue)) {
            $priceUrl .= '&sort=' . $sortValue;
        }
        //sort=1,作品量,sort=2,人气值

        //前3个筛选用
        $designUrl = '';
        if (!empty($reqPrice) && !empty($sortValue)) {
            $designUrl .= '?price=' . $reqPrice . '&sort=' . $sortValue;
        } elseif (!empty($reqPrice)) {
            $designUrl .= '?price=' . $reqPrice;
        } elseif (!empty($sortValue)) {
            $designUrl .= '?sort=' . $sortValue;
        }

        //排序用
        $sortDefaultUrl = '';
        if (!empty($reqPrice)) {
            $sortUrl = '?price=' . $reqPrice . '&';
            $sortDefaultUrl = '?price=' . $reqPrice;
        } else {
            $sortUrl = '?';
        }
        //排序-默认排序用

        //设计师筛选项
        $this->assign("designersinfo", $designersinfo);
        //设置导航栏
        $this->assign("tabIndex", 2);
        //城市bm头
        $this->assign("bm", $bm);
        //设计榜
        $this->assign("listFive", $listFive);
        //TDK
        $this->assign("keys", $keys);
        //获取报价模版
        $this->assign("order_source", 25);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp", $orderTmp);
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode", $safe["safecode"]);
        $this->assign("safekey", $safe["safekey"]);
        $this->assign("ssid", $safe["ssid"]);
        $this->assign("info", $info);
        $this->assign("priceUrl", $priceUrl);
        $this->assign("reqPrice", $reqPrice);
        $this->assign("designUrl", $designUrl);
        $this->assign("sortValue", $sortValue);
        $this->assign("sortUrl", $sortUrl);
        $this->assign("sortDefaultUrl", $sortDefaultUrl);
        $this->assign("baseURL", $baseURL);
        $this->assign("designersTopPv", $designersTopPv);
        $this->assign("designersTopCase", $designersTopCase);
        $this->assign("cityInfo", $cityInfo);
        $this->assign("lastAppointment", $lastAppointment);
        $this->display();
    }

    /**
     * 获取当前城市设计师列表
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    private function getDesigners($cs, $map, $pageIndex, $pageCount, $order, $sortValue)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Common/Designer")->getDesignerListCount2($cs, $map);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config = array("prev", "next");
            $page = new \LitePage($pageIndex, $pageCount, $count, $config);
            $pageTmp = $page->show();
            //获取当前城市设计案例最多的设计师,会员公司设计师优先
            $users = D("Common/Designer")->getDesignerList2($cs, $map, ($page->pageIndex - 1) * $pageCount, $pageCount, $order);

            foreach ($users as $key => $value) {
                $ids[] = $value["userid"];
                if ($value['zw'] == '0') {
                    $users[$key]['zw'] = '';
                }
            }

            if (empty($ids)) {
                $ids = '';
            }
            //获取设计师最新的2个案例数

            foreach ($users as $key => $value) {
                //清洗数据
                $users[$key]['fengge'] = !empty($value['fengge']) ? $value['fengge'] : '';
                $users[$key]['lingyu'] = !empty($value['lingyu']) ? $value['lingyu'] : '';
                $users[$key]['linian'] = !empty($value['linian']) ? $value['linian'] : '';

                //cost 设计师价格
                $costPrice = !empty($value['cost']) ? $value['cost'] . '元/平米' : '面议';
                $users[$key]['cost_price'] = $costPrice;
                $users[$key]["child"] = $cases = D("Common/Designer")->getLastDesigner($value['userid'], $value['comid'], 3);;

                $appointmentName = $value['name'];
                if (mb_strlen($appointmentName) > 6) {
                    $appointmentName = mb_substr($appointmentName, 0, 6);
                }
                $users[$key]['appointment_name'] = $appointmentName;
//                if (empty($value['linian'])) {
//                    $value['linian'] = ' 这个人很懒,什么都没有留下';
//                } elseif (mb_strlen($value['linian']) > 80) {
//                    $value['linian'] = mb_substr($value['linian'], 0, 80) . '...';
//                }
//                foreach ($cases as $k => $val) {
//                    if ($value["userid"] == $val["userid"]) {
//                        $users[$key]["child"][] = $val;
//                    }
//                }
            }
            return array("users" => $users, "page" => $pageTmp);
        }
        return null;
    }


    /**
     * @param $cs           // 城市
     * @param $map          // 查询条件？  这边不需要这个
     * @param $pageIndex    // 当前页码
     * @param $pageCount    // 每页显示的数量
     * @param $order        // 排序规则？  这边不需要这个
     * @param $condition    // 包含了风格【fengge】，从业时间【jobtime】，设计级别【zw】
     * @param $reqPrice     // 收费  1:不限，2:面议，3:1-100，4:101-200，5:201-300，6: 大于300
     * @param $sortValue    // 排序 ''表示默认， 1表示作品量 ， 2表示人气值
     * @return array|null
     */
    private function getDesignersV2($cs, $map, $pageIndex, $pageCount, $order, $condition, $reqPrice, $sortValue = '')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Common/Designer")->getDesignerListCount2($cs, $map);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config = array("prev", "next");
            $page = new \LitePage($pageIndex, $pageCount, $count, $config);
            $pageTmp = $page->show();
            //获取当前城市设计案例最多的设计师,会员公司设计师优先
            $users = D("Common/Designer")->getDesignerList2($cs, $map, ($page->pageIndex - 1) * $pageCount, $pageCount, $order);

            foreach ($users as $key => $value) {
                $ids[] = $value["userid"];
                if ($value['zw'] == '0') {
                    $users[$key]['zw'] = '';
                }
            }

            if (empty($ids)) {
                $ids = '';
            }
            //获取设计师最新的2个案例数

            foreach ($users as $key => $value) {
                //清洗数据
                $users[$key]['fengge'] = !empty($value['fengge']) ? $value['fengge'] : '';
                $users[$key]['lingyu'] = !empty($value['lingyu']) ? $value['lingyu'] : '';
                $users[$key]['linian'] = !empty($value['linian']) ? $value['linian'] : '';

                //cost 设计师价格
                $costPrice = !empty($value['cost']) ? $value['cost'] . '元/平米' : '面议';
                $users[$key]['cost_price'] = $costPrice;
                $users[$key]["child"] = $cases = D("Common/Designer")->getLastDesigner($value['userid'], $value['comid'], 3);;

                $appointmentName = $value['name'];
                if (mb_strlen($appointmentName) > 6) {
                    $appointmentName = mb_substr($appointmentName, 0, 6);
                }
                $users[$key]['appointment_name'] = $appointmentName;
//                if (empty($value['linian'])) {
//                    $value['linian'] = ' 这个人很懒,什么都没有留下';
//                } elseif (mb_strlen($value['linian']) > 80) {
//                    $value['linian'] = mb_substr($value['linian'], 0, 80) . '...';
//                }
//                foreach ($cases as $k => $val) {
//                    if ($value["userid"] == $val["userid"]) {
//                        $users[$key]["child"][] = $val;
//                    }
//                }
            }
            return array("users" => $users, "page" => $pageTmp);
        }
        return null;
    }

    /**
     * [getParams description]
     * @param  [type] $prefix [前缀]
     * @param  [type] $url    [url]
     * @param  [type] $data   [数据源]
     * @param  [type] $val    [选中值]
     * @return [type]         [description]
     */
    private function getParams($prefix, $url, $data, $val)
    {
        if (!empty($url)) {
            $url = $url["0"];
        } else {
            $links = "t0-l0-f0";
        }

        foreach ($data as $key => $value) {
            $reg = '/' . $prefix . '\d+/i';
            if (empty($value["id"])) {
                $value["id"] = 0;
            }
            if (!empty($url)) {
                $link = preg_replace($reg, $prefix . $value["id"], $url);
                preg_match($reg, $url, $m);
            } else {
                $link = preg_replace($reg, $prefix . $value["id"], $links);
            }
            $data[$key]["link"] = $link;
            $data[$key]["checked"] = 0;
            if ($val == $value["id"]) {
                $data[$key]["checked"] = 1;
            }
        }
        return $data;
    }
}
