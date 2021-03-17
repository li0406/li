<?php

namespace Sub\Controller;

use Common\Enums\ThematicWordsCodeEnum;
use Home\Model\CompanyModel;
use Sub\Common\Controller\SubBaseController;
use Common\Model\Logic\DecorationLogicModel;
use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\ThreeDLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;

class DecorationController extends SubBaseController
{
    private $MenuIndex = [
        "xfzx" => 2,//新房装修
        "hfzx" => 2,
        "xbzx" => 2,
        "esfzx" => 3,//二手房装修
        "jbzx" => 3,
        "jszx" => 3,
        "gzds" => 3,
        "hhsj" => 4,//别墅装修
        "cysj" => 4
    ];

    public function _initialize($value='')
    {
        parent::_initialize();
        //临时性添加头部菜单高亮
        $uri = $_SERVER['REQUEST_URI'];
        $exp = array_filter(explode("/",$uri));
        if (count($exp) > 0) {
            if (array_key_exists($exp[1],$this->MenuIndex)) {
                $this->assign("tabIndex",$this->MenuIndex[$exp[1]]);
            }
        }
    }

	// 极速装修
    public function speedDecorate()
    {
        $cityInfo = $this->cityInfo;
        $xiaoguotuInfo = array();
        $pageCount = 12;

        $result = S('Cache:Sub:Decoration:speed:list'.$cityInfo['bm']);
        if(!$result){
            $decorationLogic = new DecorationLogicModel();
            // 获取案例图片列表
            $result = $decorationLogic->getCaseImagesList($pageCount,[],$cityInfo['id']);
            S('Cache:Sub:Decoration:speed:list'.$cityInfo['bm'], $result, 1380);
        }
        
        if(!empty($result)){
            $xiaoguotuInfo["images"] = $result["images"];
        }
        $tdk = [
            'title' => $cityInfo['name'].'极速装修-'.$cityInfo['name'].'家庭极速装修-'.$cityInfo['name'].'极速装修公司',
            'keywords' => $cityInfo['name'].'极速装修,'.$cityInfo['name'].'家庭极速装修,'.$cityInfo['name'].'极速装修公司',
            'description' => $cityInfo['name'].'齐装网极速装修栏目为大家提供极速装修服务,让大家了解到什么是极速装修,家庭极速装修靠谱吗,'.$cityInfo['name'].'极速装修公司哪家好等信息,齐装网可以为装修业主提供免费量房,免费设计方案,免费做预算,'.$cityInfo['name'].'预约装修平台让您体验到装修省钱、省事、省心！',
        ];

        $this->assign("xiaoguotu",$xiaoguotuInfo);
        $this->assign("cityinfo",$this->cityInfo);
        $this->assign('keys', $tdk);
        $this->display('speed');
    }

    // 改造大师
    public function reformMaster()
    {
        $cityInfo = $this->cityInfo;

        //推荐该地区装修公司排行榜↓
        $info["koubei"] =  S('Cache:Sub:Decoration:reform:koubei' . $this->cityInfo['bm']);
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany($cityInfo['id'], 9);
            S('Cache:Sub:Decoration:reform:koubei' . $this->cityInfo['bm'], $info["koubei"], 900);
        }
        // 获取推荐设计师
        $info['designer'] =  S('Cache:Sub:Decoration:reform:designer' . $this->cityInfo['bm']);
        if (!$info["designer"]) {
            $decorationLogic = new DecorationLogicModel();
            $info["designer"] = $decorationLogic->getRecoomendDesigner($cityInfo['id'],9);
            S('Cache:Sub:Decoration:reform:designer' . $this->cityInfo['bm'], $info["designer"], 900);
        }

        $tdk = [
            'title' => $cityInfo['name'].'旧房老房改造-'.$cityInfo['name'].'旧房改造图片-'.$cityInfo['name'].'旧房改造大师',
            'keywords' => $cityInfo['name'].'旧房老房改造,'.$cityInfo['name'].'旧房改造图片,'.$cityInfo['name'].'旧房改造大师',
            'description' => $cityInfo['name'].'齐装网改造大师栏目为大家整理了老房旧房翻新改造的旧关知识,让大家了解旧房存在问题以及旧房改造方法,对于'.$cityInfo['name'].'旧房改造费用,'.$cityInfo['name'].'旧房改造案例,'.$cityInfo['name'].'旧房改造图片对比都有介绍,为广大'.$cityInfo['name'].'旧房业主指点旧房改造装修相关装修策略.'
        ];
        $this->assign('designers',$info['designer']);
        $this->assign('koubei',$info['koubei']);
        $this->assign('keys',$tdk);
        $this->display('master');
    }

    // 创意设计
    public function originDesign()
    {
        $cityInfo = $this->cityInfo;
        $decorationLogic = new DecorationLogicModel();
        $es = new ElasticSearchServiceModel();
        $list = S('Cache:Sub:Decoration:origin:list' . $this->cityInfo['bm']);
        // 获取带有创意标签的攻略5篇
        // $listArticle = $decorationLogic->getNewsByTagName('创意',5,0,0);
        if(!$list){
            $listArticle = $es->searchWwwArticle('创意',1,5);
        
            // 获取攻略列表页相关的信息
            $list['article'] = $decorationLogic->getFullArticleList($listArticle);

            // 获取190平或者100万造价以上的设计师
            $list['designer'] = $decorationLogic->getCaseDesigner([$cityInfo['id']],16);
            S('Cache:Sub:Decoration:origin:list' . $this->cityInfo['bm'], $list, 900);
        }
        
        $tdk = [
            'title' => $cityInfo['name'].'创意设计-'.$cityInfo['name'].'创意家居设计-'.$cityInfo['name'].'创意家装设计效果图',
            'keywords' => $cityInfo['name'].'创意设计,'.$cityInfo['name'].'创意家居设计,'.$cityInfo['name'].'创意家装设计效果图',
            'description' => $cityInfo['name'].'齐装网创意设计栏目为大家提供了装修创意专区,提供各类创意家居设相关的居家装修装饰以及创意家装设计效果图,在这个性装修时代创艺设计很多人都比较关注,齐装网可以为装修业主提供免费量房,免费设计方案,免费做预算让您体验到装修省钱、省事、省心！'
        ];
        $this->assign('list',$list);
        $this->assign('keys', $tdk);
        $this->display('origin');
    }

    // 二手房装修
    public function secondDecorate()
    {
        $cityInfo = $this->cityInfo;
        $decorationLogic = new DecorationLogicModel();
        $threeDLogic = new ThreeDLogicModel();

        $list = S('Cache:Sub:Decoration:second:list'. $this->cityInfo['bm']);
        if(empty($list)){
            //获取局部装修、旧房改造、3d效果图各3个
            $list['jbzx'] = $decorationLogic->getThematicWordsByModule('局部装修','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['jfgz'] = $decorationLogic->getThematicWordsByModule('旧房改造','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['3d'] = $threeDLogic->getThreeDList(0, '', 0, 0, 3);

            // 装修宝典
            $list['zx']['jfgz'] = $decorationLogic->getNewsByTagName('旧房改造',4,3,3);
            $list['zx']['jbzx'] = $decorationLogic->getNewsByTagName('局部装修',4,3,3);
            $list['zx']['sqgl'] = $decorationLogic->getNewsByTagName('省钱攻略',4,3,3);

            S('Cache:Sub:Decoration:second:list'. $this->cityInfo['bm'], $list, 960);
        }

        //推荐装修公司
        $info["koubei"] = S('Cache:Sub:Decoration:second:koubei' . $this->cityInfo['bm']);
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany($cityInfo['id'], 5);
            S('Cache:Sub:Decoration:second:koubei' . $this->cityInfo['bm'], $info["koubei"], 900);
        }
        $tdk = [
            'title' => $cityInfo['name'].'二手房装修-'.$cityInfo['name'].'二手房装修公司-'.$cityInfo['name'].'二手房装修效果图',
            'keywords' => $cityInfo['name'].'二手房装修,'.$cityInfo['name'].'二手房装修公司,'.$cityInfo['name'].'二手房装修效果图',
            'description' => $cityInfo['name'].'齐装网二手房装修栏目是'.$cityInfo['name'].'二手房装修,'.$cityInfo['name'].'二手房装修翻新专栏,为业主提供'.$cityInfo['name'].'二手房装修公司,'.$cityInfo['name'].'二手房装修费用,'.$cityInfo['name'].'二手房装修翻新效果图等资讯,对于'.$cityInfo['name'].'二手房厨房翻新,'.$cityInfo['name'].'二手房卫生间翻新及'.$cityInfo['name'].'二手房局部翻新相关服务都可在线咨询.',
        ];
        $this->assign('koubei',$info['koubei']);
        $this->assign('list',$list);
        $this->assign('keys',$tdk);
        $this->display('second');
    }

    // 新房装修
    public function newDecorate(){
        $cityInfo = $this->cityInfo;
        $decorationLogic = new DecorationLogicModel();
        $threeDLogic = new ThreeDLogicModel();

        $list = S('Cache:Sub:Decoration:new:list'. $this->cityInfo['bm']);
        if(empty($list))
        {
            //获取局部装修、旧房改造、3d效果图各5个
            $list['xfzx'] = $decorationLogic->getThematicWordsByModule('新房','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['hfzx'] = $decorationLogic->getThematicWordsByModule('婚房','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['3d'] = $threeDLogic->getThreeDList(0, '', 0, 0, 3);

            // 装修宝典
            $list['zx']['xfzx'] = $decorationLogic->getNewsByTagName('新房',4,3,3);
            $list['zx']['hfzx'] = $decorationLogic->getNewsByTagName('婚房',4,3,3);
            $list['zx']['zxxb'] = $decorationLogic->getNewsByTagName('装修小白',4,3,3);
            S('Cache:Sub:Decoration:new:list'. $this->cityInfo['bm'], $list, 960);
        }
        

        //推荐装修公司
        $info["koubei"] = S('Cache:Sub:Decoration:new:koubei' . $this->cityInfo['id']);
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany($cityInfo['id'], 5);
            S('Cache:Sub:Decoration:new:koubei' . $this->cityInfo['id'], $info["koubei"], 900);
        }
        $tdk = [
            'title' => $cityInfo['name'].'新房装修-'.$cityInfo['name'].'新房装修报价-'.$cityInfo['name'].'新房装修效果图',
            'keywords' => $cityInfo['name'].'新房装修,'.$cityInfo['name'].'新房装修报价,'.$cityInfo['name'].'新房装修设计效果图',
            'description' => $cityInfo['name'].'齐装网新房装修栏目为您提供'.$cityInfo['name'].'新房装修公司,新房装修设计效果图,'.$cityInfo['name'].'新房装修案例以及'.$cityInfo['name'].'新房装修报价等相关咨询,让您全面了解新房装修设计,新房装修注意事项,新房装修流程等知识点,为您的新房装修提供最有价值的案例参考知识,服务于更'.$cityInfo['name'].'成新房装修业主！',
        ];
        $this->assign('keys',$tdk);
        $this->assign('info',$info);
        $this->assign('list',$list);
        $this->display('new');
    }

    //婚房装修
    public function marriageDecorate()
    {
        $info = [];
        //婚房美图↓
        $info['hf_tu'] = S('Cache:Sub:Decoration:Marriage:Meitu');
        if (empty($info['hf_tu'])) {
            $decoration = new DecorationLogicModel();
            $info['hf_tu'] = $decoration->getThematicWordsByModule('婚房', '',ThematicWordsCodeEnum::MODULE_CODE_JZ);
            S('Cache:Sub:Decoration:Marriage:Meitu', $info['hf_tu'], 900);
        }
        //推荐装修公司排行榜↓
        $info["koubei"] = S('Cache:Sub:Decoration:Marriage:koubei' . $this->cityInfo['id']);
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany($this->cityInfo['id'], 5);
            S('Cache:Sub:Decoration:Marriage:koubei' . $this->cityInfo['id'], $info["koubei"], 960);
        }

        //局部装修↓
        //攻略
        $info["gonglue"] = S('Cache:Sub:Decoration:Marriage:gonglue');
        if (!$info["gonglue"]) {
            $decoration = new DecorationLogicModel();
            $info['gonglue'] = $decoration->getThematicWordsByModule('','婚房', ThematicWordsCodeEnum::MODULE_CODE_GL, 8);
            S('Cache:Sub:Decoration:Marriage:gonglue', $info["gonglue"], 1020);
        }
        //问答
        $info["wenda"] = S('Cache:Sub:Decoration:Marriage:wenda');
        if (!$info["wenda"]) {
            $decoration = new DecorationLogicModel();
            $info['wenda'] = $decoration->getThematicWordsByModule('','婚房', ThematicWordsCodeEnum::MODULE_CODE_WD, 8);
            S('Cache:Sub:Decoration:Marriage:wenda', $info["wenda"], 1080);
        }
        //百科
        $info["baike"] = S('Cache:Sub:Decoration:Marriage:baike');
        if (!$info["baike"]) {
            $decoration = new DecorationLogicModel();
            $info['baike'] = $decoration->getThematicWordsByModule('','婚房', ThematicWordsCodeEnum::MODULE_CODE_BK, 8);
            S('Cache:Sub:Decoration:Marriage:baike', $info["baike"], 1140);
        }

        $tdk = [
            'title' => $this->cityInfo["cname"].'婚房装修-'.$this->cityInfo["cname"].'婚房装修设计-'.$this->cityInfo["cname"].'婚房装修效果图',
            'keywords' => $this->cityInfo["cname"].'婚房装修,'.$this->cityInfo["cname"].'婚房装修设计,'.$this->cityInfo["cname"].'婚房装修效果图',
            'description' => $this->cityInfo["cname"].'齐装网'.$this->cityInfo["cname"].'婚房装修专区整理了关于'.$this->cityInfo["cname"].'婚房装修公司,'.$this->cityInfo["cname"].'婚房装修费用以及'.$this->cityInfo["cname"].'婚房装修设计效果图等相关服务,还有'.$this->cityInfo["cname"].'婚房装修案例美图欣赏,免费为婚房业主提供婚房装修报价以及婚房装修设计方案,让您装修婚房更省钱更放心',
        ];
        $this->assign('keys', $tdk);
        $this->assign('info', $info);
        $this->display('marriage');
    }

    //局部装修
    public function partDecorate()
    {
        //推荐装修公司排行榜↓
        $info["koubei"] = S('Cache:Sub:Decoration:Part:koubei' . $this->cityInfo['id']);
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany($this->cityInfo['id'], 4);
            S('Cache:Sub:Decoration:Part:koubei' . $this->cityInfo['id'], $info["koubei"], 900);
        }
        //效果图↓
        //客厅
        $info['tu_kt'] = S('Cache:Sub:Decoration:Part:Kt');
        if (empty($info['tu_kt'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_kt'] = $decoration->getThematicWordsByModule('客厅', '', ThematicWordsCodeEnum::MODULE_CODE_JZ, 2);
            S('Cache:Sub:Decoration:Part:Kt', $info['tu_kt'], 960);
        }
        //卧室
        $info['tu_ws'] = S('Cache:Sub:Decoration:Part:Ws');
        if (empty($info['tu_ws'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_ws'] = $decoration->getThematicWordsByModule('卧室', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Sub:Decoration:Part:Ws', $info['tu_ws'], 1020);
        }
        //卫生间
        $info['tu_wsj'] = S('Cache:Sub:Decoration:Part:Wsj');
        if (empty($info['tu_wsj'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_wsj'] = $decoration->getThematicWordsByModule('卫生间', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Sub:Decoration:Part:Wsj', $info['tu_wsj'], 1080);
        }
        //厨房
        $info['tu_cf'] = S('Cache:Sub:Decoration:Part:Cf');
        if (empty($info['tu_cf'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_cf'] = $decoration->getThematicWordsByModule('厨房', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Sub:Decoration:Part:Cf', $info['tu_cf'], 1140);
        }
        //书房
        $info['tu_sf'] = S('Cache:Sub:Decoration:Part:Sf');
        if (empty($info['tu_sf'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_sf'] = $decoration->getThematicWordsByModule('书房', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Sub:Decoration:Part:Sf', $info['tu_sf'], 1200);
        }

        //攻略
        $info["gonglue"] = S('Cache:Sub:Decoration:part:gonglue');
        if (!$info["gonglue"]) {
            $decoration = new DecorationLogicModel();
            $info['gonglue'] = $decoration->getThematicWordsByModule('','局部装修', ThematicWordsCodeEnum::MODULE_CODE_GL,10);
            S('Cache:Sub:Decoration:part:gonglue', $info["gonglue"], 1260);
        }
        //问答
        $info["wenda"] = S('Cache:Sub:Decoration:part:wenda');
        if (!$info["wenda"]) {
            $decoration = new DecorationLogicModel();
            $info['wenda'] = $decoration->getThematicWordsByModule('','局部装修', ThematicWordsCodeEnum::MODULE_CODE_WD,10);
            S('Cache:Sub:Decoration:part:wenda', $info["wenda"], 1320);
        }
        //百科
        $info["baike"] = S('Cache:Sub:Decoration:part:baike');
        if (!$info["baike"]) {
            $decoration = new DecorationLogicModel();
            $info['baike'] = $decoration->getThematicWordsByModule('','局部装修', ThematicWordsCodeEnum::MODULE_CODE_BK,10);
            S('Cache:Sub:Decoration:part:baike', $info["baike"], 1380);
        }

        $tdk = [
            'title' => $this->cityInfo["cname"] . '局部装修-' . $this->cityInfo["cname"] . '局部装修改造-' . $this->cityInfo["cname"] . '局部装修公司哪家好',
            'keywords' => $this->cityInfo["cname"] . '局部装修,' . $this->cityInfo["cname"] . '局部装修改造,' . $this->cityInfo["cname"] . '局部装修公司哪家好',
            'description' => $this->cityInfo["cname"] . '局部装修栏目为大家整理了最专业的局部装修知识,为您提供局部改造装修设计,卫生间厨房装修改造,局部改造装修公司推荐,局部改造费用,局部改造装修设计案例,局部改造风水,局部改造装修设计师等信息.',
        ];
        $this->assign('info', $info);
        $this->assign('keys', $tdk);
        $this->display('part');
    }
    public function businessDecorate()
    {
        //推荐装修公司
        $info["koubei"] = S('Cache:Sub:Decoration:business:koubei'.$this->cityInfo['id']);
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany($this->cityInfo['id'], 10);
            S('Cache:Sub:Decoration:business:koubei'.$this->cityInfo['id'], $info["koubei"], 900);
        }

        $tdk = [
            'title' => $this->cityInfo["cname"] . '公装装修公司 - ' . $this->cityInfo["cname"] . '公装装修设计 - ' . $this->cityInfo["cname"] . '公装装修效果图',
            'keywords' =>$this->cityInfo["cname"] . '办公室装修,' . $this->cityInfo["cname"] . '厂房装修,' . $this->cityInfo["cname"] . '店铺装修,' . $this->cityInfo["cname"] . '餐厅装修,' . $this->cityInfo["cname"] . '酒店装修,' . $this->cityInfo["cname"] . '写字楼装修,' . $this->cityInfo["cname"] . '公装,' . $this->cityInfo["cname"] . '商业装修',
            'description' => $this->cityInfo["cname"] . '齐装网商业装修栏目分享关于' . $this->cityInfo["cname"] . '办公室装修、厂房装修、店铺装修、餐厅装修、酒店装修、写字楼装修等公装的装修知识。',
        ];
        $this->assign('info', $info);
        $this->assign('keys', $tdk);
        $this->display('business');
    }
}
