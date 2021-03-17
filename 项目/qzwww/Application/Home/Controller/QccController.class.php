<?php

namespace Home\Controller;

use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\QccLogicModel;
use Home\Common\Controller\HomeBaseController;

class QccController extends HomeBaseController
{
    private $is_login;
    private $is_search;
    private $day;
    private $uid;

    public function _initialize()
    {
        parent::_initialize();
        $this->is_login = false; //是否登陆
        $this->is_search = false; //是否能查询
        $count = 3; //设置一天默认查询次数
        $this->day = date('Y-m-d'); //当天
        $this->uid = session('u_userInfo.id');
        if ($this->uid) {
            $this->is_login = true; //用户登陆
            //验证当前用户查询次数
            $search_count = S('Case:Home:Qcc:SearchCount:' . $this->day . ':' . $this->uid);
            if (empty($search_count)) {
                S('Case:Home:Qcc:SearchCount:' . $this->day . ':' . $this->uid, 0, 90000);
                $this->is_search = true;
            } elseif ($search_count < $count) {
                $this->is_search = true;
            }
        }

        $this->assign("is_top","0"); //客服挂件控制为不显示
        $this->assign('is_login', $this->is_login);
        $this->assign('is_search', $this->is_search);
    }

    public function index()
    {
        //企查查搜索列表页
        $keyword = I('get.keyword');
        if (!empty($keyword)) {
            //企查查搜索列表页查询后跳转查询列表页(产品指定路由)
            $this->search_list(trim($keyword));
            exit;
        }

        //企查查首页
        $SCHEME_HOST = $this->SCHEME_HOST;
        //区分是否有分站访问记录
        if (!empty($this->cityInfo['id'])) {
            //分站装修公司口碑排行榜
            $info["koubei"] = S('Cache:Qcc:Index:koubei' . $this->cityInfo['bm']);
            if (!$info["koubei"]) {
                $companyLogic = new CompanyLogicModel();
                $info["koubei"] = $companyLogic->getSubKoubeiList($this->cityInfo['id'], 8);
                S('Cache:Qcc:Index:koubei' . $this->cityInfo['bm'], $info["koubei"], random_int(900, 1080));
            }
        }else{
            //全国装修公司口碑排行榜.
            $info["koubei"] = S('Cache:Qcc:Index:koubei');
            if (!$info["koubei"]) {
                $info["koubei"] = D('Company')->getKoubeiRank('', 8);
                S('Cache:Qcc:Index:koubei', $info["koubei"], random_int(900, 1080));
            }
        }
        //通栏b
        $info["bigbanner_b"] = S('Cache:Qcc:Index:bigbanner_b');
        if (!$info["bigbanner_b"]) {
            //获取通栏B
            $infoBigbannerB = D("Advbanner")->getAdvList("home_bigbanner_b", "000001", 3);
            $info["bigbanner_b"] = $infoBigbannerB;
            S('Cache:Qcc:Index:bigbanner_b', $info["bigbanner_b"], random_int(900, 1080));
        }
        //设置默认值
        if (empty($info["bigbanner_b"])) {
            $info["bigbanner_b"][0] = [
                'img_url' => 'bigadv/20191203/5de5cc320c671-b.jpg',
                'url' => '/zhaobiao/'
            ];
        }

        $tdk = [
            'title' => '全国装修公司企业信息查询',
            'keywords' => '企业信息查询、企业工商信息查询、企业注册信息查询',
            'description' => '齐装网装企查栏目为业主提供装修公司信息查询功能，让业主更全面了解装修公司的工商信息、注册信息及信用信息等相关信息，装企查，查装企，装修保障乐无忧!',
        ];
        $this->assign('keys', $tdk);
        $this->assign('info', $info);
        return $this->display();
    }

    public function search_list($keyword)
    {
        //登陆和查询次数符合才查询
        if ($this->is_login && $this->is_search) {
            $qccLogic = new QccLogicModel();
            $info = $qccLogic->searchQccCompany($keyword);
            //设置查询次数
            $count = (int)S('Case:Home:Qcc:SearchCount:' . $this->day . ':' . $this->uid);
            S('Case:Home:Qcc:SearchCount:' . $this->day . ':' . $this->uid, $count + 1, 90000);
            //添加查询数据
            $qccLogic->addQccSearchContent($this->uid, $keyword);
        }
        if (!empty($this->cityInfo['id'])) {
            //分站装修公司口碑排行榜.
            $info["koubei"] = S('Cache:Qcc:List:koubei' . $this->cityInfo['bm']);
            if (!$info["koubei"]) {
                $companyLogic = new CompanyLogicModel();
                $info["koubei"] = $companyLogic->getSubKoubeiList($this->cityInfo['id'], 10);
                S('Cache:Qcc:List:koubei' . $this->cityInfo['bm'], $info["koubei"], random_int(900, 1080));
            }
        } else {
            //全国装修公司口碑排行榜.
            $info["koubei"] = S('Cache:Qcc:List:koubei');
            if (!$info["koubei"]) {
                $info["koubei"] = D('Company')->getKoubeiRank('', 10);
                S('Cache:Qcc:List:koubei', $info["koubei"], random_int(900, 1080));
            }
        }

        $tdk = [
            'title' => $keyword . '相关企业工商信息查询',
            'keywords' => $keyword . '、' . $keyword . '工商信息、' . $keyword . '企业信息、' . $keyword . '信息查询',
            'description' => '齐装网装企查栏目为业主提供装修公司信息查询功能，在这里您可以查询' . $keyword . '相关信息，包括' . $keyword . '工商信息、注册信息等',
        ];
        $this->assign('keys', $tdk);
        $this->assign('info', $info);
        return $this->display('list');
    }

    public function search_detail()
    {
        $key = trim(I('get.key', ''));
        $info = S('Case:Home:Qcc:Company:' . $key);
        if (!$info) {
            $qccLogic = new QccLogicModel();
            $info = $qccLogic->getQccCompanyDetail($key);
            if ($info) {
                S('Case:Home:Qcc:Company:' . $key, $info, 60 * 60 * 24 * 7);
            }
        }

        $tdk = [
            'title' => $info['Name'] . ' - 工商信息',
            'keywords' => '装修公司全称、工商信息查询、企业信用查询、企业信息查询',
            'description' => '齐装网装企查栏目提供{装修公司全称}的相关企业信息查询功能：查询工商注册信息，注册资本，公司地址，等多个企业信息维度。给您提供真实有效的企业信息查询服务',
        ];
        $this->assign('keys', $tdk);
        $this->assign('info', $info);
        return $this->display('detail');
    }
}
