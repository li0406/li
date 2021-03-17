<?php

namespace Home\Controller;

use Common\Enums\ThematicWordsCodeEnum;
use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\DecorationLogicModel;
use Common\Model\Logic\ThreeDLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Home\Common\Controller\HomeBaseController;

class DecorationController extends HomeBaseController
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

    //婚房装修
    public function marriageDecorate()
    {
        $info = [];
        //婚房美图↓
        $info['hf_tu'] = S('Cache:Home:Decoration:Marriage:Meitu');
        if (empty($info['hf_tu'])) {
            $decoration = new DecorationLogicModel();
            $info['hf_tu'] = $decoration->getThematicWordsByModule('婚房', '',ThematicWordsCodeEnum::MODULE_CODE_JZ);
            S('Cache:Home:Decoration:Marriage:Meitu', $info['hf_tu'], 900);
        }
        //推荐全国装修公司排行榜↓
        $info["koubei"] = S('Cache:Home:Decoration:Marriage:koubei');
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany('', 5);
            S('Cache:Home:Decoration:Marriage:koubei', $info["koubei"], 960);
        }

        //局部装修↓
        //攻略
        $info["gonglue"] = S('Cache:Home:Decoration:Marriage:gonglue');
        if (!$info["gonglue"]) {
            $decoration = new DecorationLogicModel();
            $info['gonglue'] = $decoration->getThematicWordsByModule('','婚房', ThematicWordsCodeEnum::MODULE_CODE_GL, 8);
            S('Cache:Home:Decoration:Marriage:gonglue', $info["gonglue"], 1020);
        }
        //问答
        $info["wenda"] = S('Cache:Home:Decoration:Marriage:wenda');
        if (!$info["wenda"]) {
            $decoration = new DecorationLogicModel();
            $info['wenda'] = $decoration->getThematicWordsByModule('','婚房', ThematicWordsCodeEnum::MODULE_CODE_WD, 8);
            S('Cache:Home:Decoration:Marriage:wenda', $info["wenda"], 1080);
        }
        //百科
        $info["baike"] = S('Cache:Home:Decoration:Marriage:baike');
        if (!$info["baike"]) {
            $decoration = new DecorationLogicModel();
            $info['baike'] = $decoration->getThematicWordsByModule('','婚房', ThematicWordsCodeEnum::MODULE_CODE_BK, 8);
            S('Cache:Home:Decoration:Marriage:baike', $info["baike"], 1140);
        }

        $tdk = [
            'title' => '婚房装修效果图 - 婚房装饰布置 - 结婚婚房设计',
            'keywords' => '婚房装修,婚房装修效果图,婚房装饰,婚房布置,结婚婚房,婚房设计',
            'description' => '齐装网婚房装修基于广大业主装修真实经历和心得的装修全攻略。分享关于婚房装修,婚房装修效果图,婚房装饰,婚房布置,结婚婚房,婚房设计等婚房设计知识。',
        ];
        $this->assign('info', $info);
        $this->assign('keys', $tdk);
        $this->display('marriage');
    }

    //局部装修
    public function partDecorate()
    {
        //推荐全国装修公司排行榜↓
        $info["koubei"] = S('Cache:Home:Decoration:Part:koubei');
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany('', 4);
            S('Cache:Home:Decoration:Part:koubei', $info["koubei"], 900);
        }
        //效果图↓
        //客厅
        $info['tu_kt'] = S('Cache:Home:Decoration:Part:Kt');
        if (empty($info['tu_kt'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_kt'] = $decoration->getThematicWordsByModule('客厅', '', ThematicWordsCodeEnum::MODULE_CODE_JZ, 2);
            S('Cache:Home:Decoration:Part:Kt', $info['tu_kt'], 960);
        }
        //卧室
        $info['tu_ws'] = S('Cache:Home:Decoration:Part:Ws');
        if (empty($info['tu_ws'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_ws'] = $decoration->getThematicWordsByModule('卧室', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Home:Decoration:Part:Ws', $info['tu_ws'], 1020);
        }
        //卫生间
        $info['tu_wsj'] = S('Cache:Home:Decoration:Part:Wsj');
        if (empty($info['tu_wsj'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_wsj'] = $decoration->getThematicWordsByModule('卫生间', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Home:Decoration:Part:Wsj', $info['tu_wsj'], 1080);
        }
        //厨房
        $info['tu_cf'] = S('Cache:Home:Decoration:Part:Cf');
        if (empty($info['tu_cf'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_cf'] = $decoration->getThematicWordsByModule('厨房', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Home:Decoration:Part:Cf', $info['tu_cf'], 1140);
        }
        //书房
        $info['tu_sf'] = S('Cache:Home:Decoration:Part:Sf');
        if (empty($info['tu_sf'])) {
            $decoration = new DecorationLogicModel();
            $info['tu_sf'] = $decoration->getThematicWordsByModule('书房', '',ThematicWordsCodeEnum::MODULE_CODE_JZ,2);
            S('Cache:Home:Decoration:Part:Sf', $info['tu_sf'], 1200);
        }

        //攻略
        $info["gonglue"] = S('Cache:Home:Decoration:part:gonglue');
        if (!$info["gonglue"]) {
            $decoration = new DecorationLogicModel();
            $info['gonglue'] = $decoration->getThematicWordsByModule('','局部装修', ThematicWordsCodeEnum::MODULE_CODE_GL,10);
            S('Cache:Home:Decoration:part:gonglue', $info["gonglue"], 1260);
        }
        //问答
        $info["wenda"] = S('Cache:Home:Decoration:part:wenda');
        if (!$info["wenda"]) {
            $decoration = new DecorationLogicModel();
            $info['wenda'] = $decoration->getThematicWordsByModule('','局部装修', ThematicWordsCodeEnum::MODULE_CODE_WD,10);
            S('Cache:Home:Decoration:part:wenda', $info["wenda"], 1320);
        }
        //百科
        $info["baike"] = S('Cache:Home:Decoration:part:baike');
        if (!$info["baike"]) {
            $decoration = new DecorationLogicModel();
            $info['baike'] = $decoration->getThematicWordsByModule('','局部装修', ThematicWordsCodeEnum::MODULE_CODE_BK,10);
            S('Cache:Home:Decoration:part:baike', $info["baike"], 1380);
        }

        $tdk = [
            'title' => '局部装修改造 - 客厅卧室卫生间厨房书房装修效果图大全',
            'keywords' => '局部装修,装修改造,客厅装修,卧室装修,卫生间装修,厨房装修,书房装修',
            'description' => '齐装网局部装修基于广大业主装修真实经历和心得的装修全攻略。分享关于局部装修,装修改造,客厅装修,卧室装修,卫生间装修,厨房装修,书房装修等空间局部装修知识。',
        ];
        $this->assign('info', $info);
        $this->assign('keys', $tdk);
        $this->display('part');
    }

    //豪华设计
    public function luxuryDesign()
    {
        $tdk = [
            'title' => '豪华设计 - 豪华装修设计 - 豪华设计效果图',
            'keywords' => '豪华设计,豪华装修设计,豪华设计效果图',
            'description' => '齐装网豪华设计栏目为大家提供了家庭装修、别墅装修以及工装豪华装修设计相关资讯,海量豪华设计效果图供大家参考,齐装网可以为装修业主提供免费量房,免费设计方案,免费做预算,装修网让您体验到装修省钱、省事、省心！',
        ];
        $this->assign('keys', $tdk);
        $this->display('luxury');
    }

    // 极速装修
    public function speedDecorate()
    {
        $xiaoguotuInfo = array();
        $pageCount = 12;
        $result = S('Cache:Sub:Decoration:speed:list');
        if(!$result){
            $decorationLogic = new DecorationLogicModel();
            // 获取案例图片列表
            $result = $decorationLogic->getCaseImagesList($pageCount,[],null);
            S('Cache:Sub:Decoration:speed:list', $result, 1380);
        }
        
        if(!empty($result)){
            $xiaoguotuInfo["images"] = $result["images"];
        }
        $tdk = [
            'title' => '毛坯房装修 - 精装修 -90天极速装修改造',
            'keywords' => '毛坯房装修,精装修,极速装修,装修改造',
            'description' => '齐装网品质装企基于广大业主装修真实经历和心得的装修全攻略。分享关于装修公司排名,十大装修公司榜单,装修公司报价等装修公司知识。',
        ];
        $this->assign('xiaoguotu',$xiaoguotuInfo);
        $this->assign('keys', $tdk);
        $this->display('speed');
    }

    // 改造大师
    public function reformMaster()
    {
        //推荐该地区装修公司排行榜↓
        $info["koubei"] = S('Cache:Home:Decoration:reform:koubei');
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany('', 9);
            S('Cache:Home:Decoration:reform:koubei', $info["koubei"], 900);
        }
        // 获取推荐设计师
        $info['designer'] =  [];//S('Cache:Home:Decoration:reform:designer');
        if (!$info["designer"]) {
            $decorationLogic = new DecorationLogicModel();
            $info["designer"] = $decorationLogic->getRecoomendDesigner('',9,null);
            S('Cache:Home:Decoration:reform:designer', $info["designer"], 900);
        }
        $tdk = [
            'title' => '旧房改造装修步骤 - 旧房改造装修案例 - 旧房改造省钱方法',
            'keywords' => '旧房改造,旧房装修步骤,改造装修案例,旧房改造省钱方法',
            'description' => '齐装网改造大师基于广大业主装修真实经历和心得的装修全攻略。分享关于旧房改造,旧房装修步骤,改造装修案例,旧房改造省钱方法等旧房改造知识。',
        ];
        $this->assign('designers',$info['designer']);
        $this->assign('koubei',$info['koubei']);
        $this->assign('keys', $tdk);
        $this->display('master');
    }

    // 创意设计
    public function originDesign()
    {
        $decorationLogic = new DecorationLogicModel();
        $es = new ElasticSearchServiceModel();

        $list = S('Cache:Sub:Decoration:origin:list');
        if(!$list){
            // 获取带有创意标签的攻略5篇
            $listArticle = $es->searchWwwArticle('创意',1,5);
            
            // 获取攻略列表页相关的信息
            $list['article'] = $decorationLogic->getFullArticleList($listArticle);

            // 获取190平或者100万造价以上的设计师
            $list['designer'] = $decorationLogic->getCaseDesigner([],16);
            S('Cache:Sub:Decoration:origin:list', $list, 900);
        }
        
        $tdk = [
            'title' => '创意别墅装修效果图 - 创意别墅装修设计',
            'keywords' => '别墅装修,创意别墅装修,别墅装修效果图,别墅设计',
            'description' => '齐装网创意设计基于广大业主装修真实经历和心得的装修全攻略。分享关于别墅装修,创意别墅装修,别墅装修效果图,别墅设计等创意别墅装修知识。',
        ];
        $this->assign('list',$list);
        $this->assign('keys', $tdk);
        $this->display('origin');
    }

    // 二手房装修
    public function secondDecorate()
    {
        $decorationLogic = new DecorationLogicModel();
        $threeDLogic = new ThreeDLogicModel();
        //获取局部装修、旧房改造、3d效果图各5个
        $list = S('Cache:Home:Decoration:second:list');
        if(empty($list)){
            //获取局部装修、旧房改造、3d效果图各5个
            $list['jbzx'] = $decorationLogic->getThematicWordsByModule('局部装修','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['jfgz'] = $decorationLogic->getThematicWordsByModule('旧房改造','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['3d'] = $threeDLogic->getThreeDList(0, '', 0, 0, 3);

            // 装修宝典
            $list['zx']['jfgz'] = $decorationLogic->getNewsByTagName('旧房改造',4,3,3);
            $list['zx']['jbzx'] = $decorationLogic->getNewsByTagName('局部装修',4,3,3);
            $list['zx']['sqgl'] = $decorationLogic->getNewsByTagName('省钱攻略',4,3,3);

            S('Cache:Home:Decoration:second:list', $list, 960);
        }

        //推荐装修公司
        $info["koubei"] = S('Cache:Home:Decoration:second:koubei');
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany('', 5);
            S('Cache:Home:Decoration:second:koubei', $info["koubei"], 900);
        }
        $tdk = [
            'title' => '二手房简约装修 - 二手房装修注意事项 - 二手房翻新效果图',
            'keywords' => '二手房简约装修,二手房装修注意事项,二手房翻新效果图',
            'description' => '齐装网二手房装修基于广大业主装修真实经历和心得的装修全攻略。分享关于二手房简约装修,二手房装修注意事项,二手房翻新效果图等二手房装修知识。',
        ];
        $this->assign('koubei',$info['koubei']);
        $this->assign('list',$list);
        $this->assign('keys', $tdk);
        $this->display('second');
    }

    // 新房装修
    public function newDecorate()
    {
        $decorationLogic = new DecorationLogicModel();
        $threeDLogic = new ThreeDLogicModel();

        $list = S('Cache:Home:Decoration:new:list');
        if(empty($list)){

            //获取新房美图、婚房美图、3d效果图各3个
            $list['xfzx'] = $decorationLogic->getThematicWordsByModule('新房','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['hfzx'] = $decorationLogic->getThematicWordsByModule('婚房','', ThematicWordsCodeEnum::MODULE_CODE_JZ, 3);
            $list['3d'] = $threeDLogic->getThreeDList(0, '', 0, 0, 3);

            // 装修宝典
            $list['zx']['xfzx'] = $decorationLogic->getNewsByTagName('新房',4,3,3);
            $list['zx']['hfzx'] = $decorationLogic->getNewsByTagName('婚房',4,3,3);
            $list['zx']['zxxb'] = $decorationLogic->getNewsByTagName('装修小白',4,3,3);

            S('Cache:Home:Decoration:new:list', $list, 960);
        }
        
        //推荐该地区装修公司排行榜↓
        $info["koubei"] = S('Cache:Home:Decoration:new:koubei');
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany('', 5);
            S('Cache:Home:Decoration:new:koubei', $info["koubei"], 900);
        }

        
        $tdk = [
            'title' => '新房装修效果图 - 新房装修流程步骤 - 新房装修设计报价',
            'keywords' => '新房装修效果图,新房装修流程步骤,新房装修设计报价',
            'description' => '齐装网新房装修基于广大业主装修真实经历和心得的装修全攻略。分享关于新房装修效果图,装修流程步骤,新房装修设计,报价等新房装修知识',
        ];
        $this->assign('info',$info);
        $this->assign('list',$list);
        $this->assign('keys', $tdk);
        $this->display('new');
    }

    // 小白tips装修
    public function tipsDecorate()
    {
        $service = new ElasticSearchServiceModel();
        $list = S('Cache:Home:Decoration:tips:list');
        if(empty($list)){
            $list = array();
            $zxcs = $service->searchWwwArticle('装修经验',1,10);
            $fkzn = $service->searchWwwArticle('装修猫腻',1,10);
            array_multisort(array_column($zxcs, 'createtime'),SORT_DESC,$zxcs);
            array_multisort(array_column($fkzn, 'createtime'),SORT_DESC,$fkzn);
            $list['article']['zxcs'] = $zxcs;
            $list['article']['fkzn'] = $fkzn;
            $decoration = new DecorationLogicModel();
            $list['video']['zxcs'] = $decoration->getVideoListData(1,10,13);
            $list['video']['fkzn'] = $decoration->getVideoListData(1,10,12);
            S('Cache:Home:Decoration:tips:list', $list, 960);
        }
        $tdk = [
            'title' => '房子装修流程步骤 - 房子装修注意事项',
            'keywords' => '房子装修流程,装修流程步骤,装修注意事项',
            'description' => '齐装网小白装修基于广大业主装修真实经历和心得的装修全攻略。分享关于房子装修流程,装修流程步骤,装修注意事项等装修知识。',
        ];
        $this->assign('list',$list);
        $this->assign('keys', $tdk);
        $this->display('tips');
    }
    //商业装修
    public function businessDecorate()
    {
        //推荐装修公司
        $info["koubei"] = S('Cache:Home:Decoration:business:koubei');
        if (!$info["koubei"]) {
            $db = new CompanyLogicModel();
            $info["koubei"] = $db->getRecommendCompany('', 10);
            S('Cache:Home:Decoration:business:koubei', $info["koubei"], 900);
        }
        $tdk = [
            'title' => '公装装修公司 - 公装装修设计 - 公装装修效果图',
            'keywords' => '办公室装修,厂房装修,店铺装修,餐厅装修,酒店装修,写字楼装修,公装,商业装修',
            'description' => '齐装网商业装修栏目分享关于办公室装修、厂房装修、店铺装修、餐厅装修、酒店装修、写字楼装修等公装的装修知识。',
        ];
        $this->assign('info',$info);
        $this->assign('keys', $tdk);
        $this->display('business');
    }


}
