<?php
/**
 * 品牌榜页面
 */
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class CompanybrandsController extends SubBaseController{
    public function index(){
        $bm = $this->cityInfo["bm"];
        $info["famous"] = S("Cache:ppb:famous".$bm);
        //知名装修公司
        if(!$info["famous"]){
            //获取当前的知名装修公司列表
            $company = $this->getFamousCompany($this->cityInfo["id"]);
            $info["famous"] = $company;
            S("Cache:ppb:famous".$bm,$company,900);
        }

        //装修公司设计排行榜
        $info["sj"] = S("Cache:ppb:sj".$bm);
        if(!$info["sj"]){
            $list = $this->getVipUserDesignScoreGrading($this->cityInfo["id"],10);
            $info["sj"] = $list;
            S("Cache:ppb:sj".$bm,$list,900);
        }

        //装修公司施工排行榜
        $info["sg"] = S("Cache:ppb:sg".$bm);
        if(!$info["sg"]){
            $list = $this->getVipUserBuildScoreGrading($this->cityInfo["id"],10);
            $info["sg"] = $list;
            S("Cache:ppb:sg".$bm,$list,900);
        }

        //装修公司施工排行榜
        $info["fw"] = S("Cache:ppb:fw".$bm);
        if(!$info["fw"]){
            $list = $this->getVipUserServingScoreGrading($this->cityInfo["id"],10);
            $info["fw"] = $list;
            S("Cache:ppb:fw".$bm,$list,900);
        }

        //装修公司信赖排行榜
        $info["trust"] = S("Cache:ppb:trust".$bm);
        if(!$info["trust"]){
            $list = $this->getVipUserTrustList($this->cityInfo["id"],10);
            $info["trust"] = $list;
            S("Cache:ppb:trust".$bm,$list,900);
        }

        //装修公司活跃排行榜
        $info["activity"] = S("Cache:ppb:activity".$bm);
        if(!$info["activity"]){
            $list = $this->getVipUserActivityList($this->cityInfo["id"],10);
            $info["activity"] = $list;
            S("Cache:ppb:activity".$bm,$list,900);
        }

        //装修公司人气排行榜
        $info["popularity"] = S("Cache:ppb:popularity".$bm);
        if(!$info["popularity"]){
            $list = $this->getVipUserPopularityList($this->cityInfo["id"],10);
            $info["popularity"] = $list;
            S("Cache:ppb:popularity".$bm,$list,900);
        }

        //最热心装修公司
        $info["enthusiastic"] = S("Cache:ppb:enthusiastic".$bm);
        if(!$info["enthusiastic"]){
            $list = $this->getVipUserEnthusiasticList($this->cityInfo["id"],4);
            $info["enthusiastic"] = $list;
            S("Cache:ppb:enthusiastic".$bm,$list,900);
        }

        //最新签单公司
        $info["latest"] = S("Cache:ppb:latest".$bm);
        if(!$info["latest"]){
            $list = $this->getVipUserLatestList($this->cityInfo["id"],4);
            $info["latest"] = $list;
            S("Cache:ppb:latest".$bm,$list,900);
        }

        $keys["title"] = $this->cityInfo["name"]."装修公司排名_".$this->cityInfo["name"]."装修公司品牌排行榜-".$this->cityInfo["name"]."齐装网";
        $keys["keywords"] = $this->cityInfo["name"]."装修公司,".$this->cityInfo["name"]."装修公司排名,".$this->cityInfo["name"]."装修公司排行榜";
        $keys["description"] = "齐装网装修公司频道为您提供:深圳,上海,北京,广州,苏州,郑州,成都,沈阳,武汉,重庆,合肥,西安,厦门,大连等全国各城市装修公司品牌排名信息。";
        //导航栏标识
        $this->assign("tabIndex",2);
        $this->assign("keys",$keys);
        $this->assign("info",$info);
        $this->display();
    }

    private function getFamousCompany($city_id){
        //获取广告表中的数据
        $brands = D("Advbanner")->getAdvList("brand_topic",$city_id,6);
        if(count($brands) > 0){
            foreach ($brands as $key => $value) {
                //查询装修公司的检查分数
                $score = D("User")->getVipUserInfoById($value["company_id"]);
                $brands[$key]["check_score"] = $score["check_score"];
            }

        }else{
            //如果没有广告推荐的装修公司
            $brands = D("User")->getVipUserListByCity($city_id);
            foreach ($brands as $key => $value) {
                if(strpos($value["img_url"], $this->SCHEME_HOST['scheme_full']) !== false){
                    $brands[$key]["img_url"] = str_replace(C('QINIU_SCHEME')."://".C("QINIU_DOMAIN")."/","", $value["img_url"]);
                }elseif(strpos($value["img_url"], "//") !== false){
                    $brands[$key]["img_url"] = str_replace("//".C("QINIU_DOMAIN")."/","", $value["img_url"]);
                    $brands[$key]["img_url"] = str_replace("//".C("STATIC_HOST1")."/","", $brands[$key]["img_url"]);
                }elseif(strpos($value["img_url"], "/upload/img_url") === 0){
                    $brands[$key]["img_url"] = substr($value["img_url"], 1);
                }
            }
        }
        return $brands;
    }

    private function getVipUserDesignScoreGrading($city_id,$limit){
        $list = D("User")->getVipUserDesignScoreGrading($city_id,$limit);
        $list = $this->getStar($list);
        return $list;
    }

    private function getVipUserBuildScoreGrading($city_id,$limit){
        $list = D("User")->getVipUserBuildScoreGrading($city_id,$limit);
        $list = $this->getStar($list);
        return $list;
    }

    private function getVipUserServingScoreGrading($city_id,$limit){
        $list = D("User")->getVipUserServingScoreGrading($city_id,$limit);
        $list = $this->getStar($list);
        return $list;
    }

    private function getVipUserTrustList($city_id,$limit){
        $list = D("User")->getVipUserTrustList($city_id,$limit);
        $list = $this->getStar($list);
        return $list;
    }

    private function getVipUserActivityList($city_id,$limit){
        $list = D("User")->getVipUserActivityList($city_id,$limit);
        $list = $this->getStar($list);
        return $list;
    }

    private function getVipUserPopularityList($city_id,$limit){
        $list = D("User")->getVipUserPopularityList($city_id,$limit);
        $list = $this->getStar($list);
        return $list;
    }

    private function getVipUserEnthusiasticList($city_id,$limit){
        $list = D("User")->getVipUserEnthusiasticList($city_id,$limit);
        return $list;
    }

    private function getVipUserLatestList($city_id,$limit){
        $list = D("User")->getVipUserLatestList($city_id,$limit);
        return $list;
    }

    /**
     * 计算星星
     */
    private function getStar($list){
        foreach ($list as $key => $value) {
            if($value["avg"] >= 9 ){
                $list[$key]["star"] = 5;
            }elseif($value["avg"] >= 8 && $value["avg"] < 9){
                $list[$key]["star"] = 4;
            }elseif($value["avg"] >= 6 && $value["avg"] < 8){
                $list[$key]["star"] = 3;
            }elseif($value["avg"] >= 4 && $value["avg"] < 6){
                $list[$key]["star"] = 2;
            }else{
                $list[$key]["star"] = 1;
            }
        }
        return $list;
    }
}