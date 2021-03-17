<?php
/**
 * 移动版案例详情页
 */
namespace Mobile\Controller;
use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\SubthematicLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class CasesController extends MobileBaseController{

    public function index(){
        $cityInfo = $this->cityInfo;
        $id = I("get.id");
        if(!is_numeric($id)){
            $this->_error();
        }


        $info = S("Cache:".I("get.bm").I("get.id"));
        if(!$info){
            //查询案例的详细信息
            $case = $this->getCaseInfo(I("get.id"),$cityInfo["id"]);
            if(!empty($case)){
                if(!empty($case['qc']) || empty($case['jc'])){
                    if(!empty($case['jc'])){
                        $case['comapny'] = $case['jc'];
                    }else{
                        $case['comapny'] = $case['qc'];
                    }
                }
                $info["case"] = $case;
                $info["title"] = "装修案例";
                S("Cache:".I("get.bm").I("get.id"),$info,900);
            }else{
                //根据编号再查询一次，防止SESSION丢失后的404
                $case = $this->getCaseInfo(I("get.id"));
                if(count($case) > 0){
                    if(!empty($case["cs"])){
                        $cs = $case["cs"];
                        //根据城市编号查询城市信息
                        $area = D("Quyu")->getCityById($cs);
                        $url = $this->SCHEME_HOST['scheme_full']."m.".C("QZ_YUMING")."/".$area["bm"]."/caseinfo/".I("get.id").".shtml";
                        header( "HTTP/1.1 301 Moved Permanently" );
                        header( "Location:".$url);
                        die();
                    }
                }
                $this->_error();
                die();
            }
        }

        session("m_wanshan_tmp","wanshan");
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["case"]["title"].$info["case"]["mianji"]."平米".$info["case"]["hname"].$info["case"]["fengge"]."家装装修图片设计";
        $basic["head"]["keywords"] = $info["case"]["title"].",".$info["case"]["fengge"].$info["case"]["hname"]
                            .",".$info["case"]["fengge"].$info["case"]["hname"]
                            ."设计,".$info["case"]["fengge"].$info["case"]["hname"]
                            ."装修图片,".$info["case"]["mianji"]."平米".$info["case"]["now"]["hname"]."装修图片";
        $basic["head"]["description"] ="齐装网装修效果图频道为您提供".$info["case"]["title"].$info["case"]["mianji"]."平米".$info["case"]["hname"].$info["case"]["fengge"].
                            "家装装修图片设计，以及".date("Y")."年国内外流行的装修效果图大全，".$info["case"]["text"];
        $basic["body"]["title"] = $info["case"]["title"];

        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). '/caseinfo/' . intval(I("get.id")).'.shtml';
        //获取该城市第一个区，用于显示默认城市
        $cid = $_SESSION["m_mapUseInfo"]['id'];
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->display();
    }
    public function indextwo(){
        $cityInfo = $this->cityInfo;
        $id = I("get.id");
        if(!is_numeric($id)){
            $this->_error();
        }

        $info = S("Cache:Mobile:CaseInfo:".I("get.bm").I("get.id"));
        if(!$info){
            //查询案例的详细信息
            $logic = new CasesLogicModel();
            $case = $logic->getCaseInfo(I("get.id"),$cityInfo["id"]);
            if(!empty($case)){
                if(!empty($case['qc']) || empty($case['jc'])){
                    if(!empty($case['jc'])){
                        $case['comapny'] = $case['jc'];
                    }else{
                        $case['comapny'] = $case['qc'];
                    }
                }
                $info["case"] = $case;
                $info["title"] = "装修案例";
                S("Cache:Mobile:CaseInfo:".I("get.bm").I("get.id"),$info,900);
            }else{
                //根据编号再查询一次，防止SESSION丢失后的404
                $case = $logic->getCaseInfo(I("get.id"));
                if(count($case) > 0){
                    if(!empty($case["cs"])){
                        $cs = $case["cs"];
                        //根据城市编号查询城市信息
                        $area = D("Quyu")->getCityById($cs);
                        $url = $this->SCHEME_HOST['scheme_full']."m.".C("QZ_YUMING")."/".$area["bm"]."/caseinfo/".I("get.id").".shtml";
                        header( "HTTP/1.1 301 Moved Permanently" );
                        header( "Location:".$url);
                        die();
                    }
                }
                $this->_error();
                die();
            }
        }

        session("m_wanshan_tmp","wanshan");
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //seo 标题/描述/关键字
        // $basic["head"]["title"] = $info["case"]["title"].$info["case"]["mianji"]."平米".$info["case"]["hname"].$info["case"]["fengge"]."家装装修图片设计";
        // $basic["head"]["keywords"] = $info["case"]["title"].",".$info["case"]["fengge"].$info["case"]["hname"]
        //     .",".$info["case"]["fengge"].$info["case"]["hname"]
        //     ."设计,".$info["case"]["fengge"].$info["case"]["hname"]
        //     ."装修图片,".$info["case"]["mianji"]."平米".$info["case"]["now"]["hname"]."装修图片";
        // $basic["head"]["description"] ="齐装网装修效果图频道为您提供".$info["case"]["title"].$info["case"]["mianji"]."平米".$info["case"]["hname"].$info["case"]["fengge"].
        //     "家装装修图片设计，以及".date("Y")."年国内外流行的装修效果图大全，".$info["case"]["text"];
        // $basic["body"]["title"] = $info["case"]["title"];


        //seo 标题/描述/关键字
        $basic["head"]["title"] = sprintf("%s%s装修设计案例_%s%s㎡%s装修效果图_齐装网", $cityInfo["name"], $info["case"]["title"], $cityInfo["name"], $info["case"]["mianji"], $info["case"]["fengge"]);
        $basic["head"]["keywords"] = sprintf("%s%s装修设计案例,%s㎡装修效果图,%s装修效果图", $cityInfo["name"], $info["case"]["title"], $info["case"]["mianji"], $info["case"]["fengge"]);
        $basic["head"]["description"] = sprintf("%s齐装网为您带来的%s装修设计案例，为您呈现%s㎡%s装修效果图，让您在装修设计房屋的时候有更多的装修设计案例参考。", $cityInfo["name"], $info["case"]["title"], $info["case"]["mianji"], $info["case"]["fengge"]);

        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). '/caseinfo/' . intval(I("get.id")).'.shtml';
        //获取该城市第一个区，用于显示默认城市
        $cid = $_SESSION["m_mapUseInfo"]['id'];
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        //案例标识对应专题页
        $zhuanti = (new SubthematicLogicModel())->getNameByCaseTag(I("get.id"),$cityInfo["id"]);
        $this->assign("zhuanti", $zhuanti);

        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("cityInfo",$cityInfo);
        $this->display();
    }
     /**
     * 获取案例信息
     * @param  string $id [案例编号]
     * @param  string $cs [所在城市]
     * @return [type]     [description]
     */
    private function getCaseInfo($id = '',$cs = ''){
       $caseInfo = D("Common/Cases")->getMobileCaseInfo($id,$cs);
       if(count($caseInfo) > 0){
            foreach ($caseInfo as $key => $value) {
                if($key == 0){
                    $case = $value;
                }
                $case["child"][] = $value;
            }
            return $case;
       }
       return null;
    }

}