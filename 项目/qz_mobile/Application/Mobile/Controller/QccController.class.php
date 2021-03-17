<?php
/**
 * 企查查
 */

namespace Mobile\Controller;

use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\QccLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class QccController extends MobileBaseController
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
            $search_count = S('Cache:Mobile:Qcc:SearchCount:' . $this->day . ':' . $this->uid);
            if (empty($search_count)) {
                S('Cache:Mobile:Qcc:SearchCount:' . $this->day . ':' . $this->uid, 0, 90000);
                $this->is_search = true;
            } elseif ($search_count < $count) {
                $this->is_search = true;
            }
        }

        $this->assign("is_top", "0"); //客服挂件控制为不显示
        $this->assign('is_login', $this->is_login);
        $this->assign('is_search', $this->is_search);
    }

    public function index()
    {
        $keyword = I('get.keyword');
        if (!empty($keyword)) {
            $this->qccList(trim($keyword));
            exit;
        }

        //企查查首页
        //区分是否有分站访问记录
        $companyLogic = new CompanyLogicModel();
        if (!empty($this->cityInfo['id'])) {
            //分站装修公司口碑排行榜
            $info["koubei"] = S('Cache:Mobile:Qcc:Index:koubei' . $this->cityInfo['bm']);
            if (!$info["koubei"]) {
                $info["koubei"] = $companyLogic->getKoubeiList($this->cityInfo['id'], 8);
                S('Cache:Mobile:Qcc:Index:koubei' . $this->cityInfo['bm'], $info["koubei"], random_int(900, 1080));
            }
        } else {
            //全国装修公司口碑排行榜.
            $info["koubei"] = S('Cache:Mobile:Qcc:Index:koubei');
            if (!$info["koubei"]) {
                $info["koubei"] = $companyLogic->getKoubeiList('', 8);
                S('Cache:Mobile:Qcc:Index:koubei', $info["koubei"], random_int(900, 1080));
            }
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

    public function qccList($keyword)
    {
        //登陆和查询次数符合才查询
        if ($this->is_login && $this->is_search) {
            $qccLogic = new QccLogicModel();
            $info = $qccLogic->searchQccCompany($keyword);
            //设置查询次数
            $count = (int)S('Cache:Mobile:Qcc:SearchCount:' . $this->day . ':' . $this->uid);
            S('Cache:Mobile:Qcc:SearchCount:' . $this->day . ':' . $this->uid, $count + 1, 90000);
            //添加查询数据
            $qccLogic->addQccSearchContent($this->uid, $keyword, $this->cityInfo['id']);
        }
        $companyLogic = new CompanyLogicModel();
        if (!empty($this->cityInfo['id'])) {
            //分站装修公司口碑排行榜.
            $info["koubei"] = S('Cache:Mobile:Qcc:List:koubei' . $this->cityInfo['bm']);
            if (!$info["koubei"]) {
                $info["koubei"] = $companyLogic->getKoubeiList($this->cityInfo['id'], 10);
                S('Cache:Mobile:Qcc:List:koubei' . $this->cityInfo['bm'], $info["koubei"], random_int(900, 1080));
            }
        } else {
            //全国装修公司口碑排行榜.
            $info["koubei"] = S('Cache:Mobile:Qcc:List:koubei');
            if (!$info["koubei"]) {
                $info["koubei"] = $companyLogic->getKoubeiList('', 10);
                S('Cache:Mobile:Qcc:List:koubei', $info["koubei"], random_int(900, 1080));
            }
        }

        $tdk = [
            'title' => $keyword . '相关企业工商信息查询',
            'keywords' => $keyword . '、' . $keyword . '工商信息、' . $keyword . '企业信息、' . $keyword . '信息查询',
            'description' => '齐装网装企查栏目为业主提供装修公司信息查询功能，在这里您可以查询' . $keyword . '相关信息，包括' . $keyword . '工商信息、注册信息等',
        ];
        $this->assign('keys', $tdk);
        $this->assign('info', $info);
        return $this->display('qcc_list');
    }

    public function qccDetail()
    {
        $key = trim(I('get.key', ''));
        $info = S('Cache:Mobile:Qcc:Company:' . $key);
        if (!$info) {
            $qccLogic = new QccLogicModel();
            $info = $qccLogic->getQccCompanyDetail($key);
            if ($info) {
                S('Cache:Mobile:Qcc:Company:' . $key, $info, 60 * 60 * 24 * 7);
            }
        }

        $tdk = [
            'title' => $info['Name'] . ' - 工商信息',
            'keywords' => '装修公司全称、工商信息查询、企业信用查询、企业信息查询',
            'description' => '齐装网装企查栏目提供{装修公司全称}的相关企业信息查询功能：查询工商注册信息，注册资本，公司地址，等多个企业信息维度。给您提供真实有效的企业信息查询服务',
        ];
        $this->assign('keys', $tdk);
        $this->assign('info', $info);
        return $this->display('qcc_detail');
    }
}