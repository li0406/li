<?php


namespace Common\Model\Logic;


use Common\Model\AskModel;
use Common\Model\BaikeModel;
use Common\Model\CompanyModel;
use Common\Model\Db\AdvBannerModel;
use Common\Model\Db\AdvertModel;
use Common\Model\Db\DecorationMapModel;
use Common\Model\Db\TuCategoryModel;
use Common\Model\LittlearticleModel;
use Common\Model\OrdersModel;
use Common\Model\WwwArticleClassModel;
use Common\Model\WwwArticleModel;

class SubIndexLogicModel
{

    // 首页广告位/ 分站&主站共用
    public function advSpaceListForIndex($city = "000001")
    {
        $advertModel = new AdvertModel();
        $advInfo = [];
        // 以下广告位是写死的，由产品提供
        $advInfo["adv_left"] = $this->getSingleImgAdvInfo("36", $city);
        $advInfo["adv_rightU"] = $this->getSingleImgAdvInfo("37", $city);
        $advInfo["adv_rightUa"] = $this->getSingleImgAdvInfo("38", $city);
        $advInfo["adv_rightUb"] = $this->getSingleImgAdvInfo("39", $city);

        return $advInfo;
    }

    // 获取单图广告信息    分站&主站共用
    private function getSingleImgAdvInfo($location_id, $city = "")
    {
        $advertModel = new AdvertModel();
        $info = $advertModel->getSingleImgAdvInfo($location_id, $city);
        if (!empty($info)) {
            $info["img_url"] = changeImgUrl($info["img_url"]);
        }
        return $info ? $info : [];
    }

    //开工大吉    分站&主站共用
    public function getPassTheaAuditOrder($cityId = 0)
    {
        $ordersModel = new OrdersModel();
        if ($cityId > 0) {
            $list1 = $ordersModel->getPassTheaAuditOrder($cityId, 0, 10);
            $countNum = count($list1);
            $list2 = [];
            if ($countNum < 10) {
                $list2 = $ordersModel->getPassTheaAuditOrder(0, $cityId, (10 - $countNum));
            }
            if (($countNum > 0) && (count($list2) > 0)) {
                $list = array_merge($list1, $list2);
            } elseif (($countNum > 0) && (count($list2) == 0)) {
                $list = $list1;
            } elseif (($countNum == 0) && (count($list2) > 0)) {
                $list = $list2;
            } else {
                $list = [];
            }

        } else {
            $list = $ordersModel->getPassTheaAuditOrder(0, 0, 10);
        }

        return $list;
    }

    // 装修案例    分站&主站共用
    public function getHomeCases($cityId = 0)
    {
        $advBannerModel = new AdvBannerModel();
        $list = $advBannerModel->getHomeCases($cityId);
        $result = [];
        foreach ($list as $key => $val) {
            $newData = [];
            $newData["title"] = $val["title"];
            $newData["img_url"] = $val["img_url_mobile"] ? changeImgUrl($val["img_url_mobile"]) : ($val["img_url"] ? changeImgUrl($val["img_url"]) : "");
            $newData["url"] = $val["url_mobile"] ? $val["url_mobile"] : ($val["url"] ? $val["url"] : "");
            $newData["company_name"] = $val["company_name"];
            $result[] = $newData;
        }

        return $result;
    }

    // 装修效果图    分站&主站共用
    public function getHomeTuCategoryInfo()
    {
        $tuCategoryModel = new TuCategoryModel();
        $result = [
            0 => [
                "id" => 7,
                "name" => "空间",
                "parent_sortname" => "kj",
            ],
            1 => [
                "id" => 8,
                "name" => "局部",
                "parent_sortname" => "jb",
            ],
            2 => [
                "id" => 9,
                "name" => "风格",
                "parent_sortname" => "fg",
            ],
            3 => [
                "id" => 10,
                "name" => "户型",
                "parent_sortname" => "hx",
            ],
        ];
        $imgid = [];
        foreach ($result as $key => $val) {
            $result[$key]["child"] = $tuCategoryModel->getHomeTuChildCategoryInfo($val["id"]);
            foreach ($result[$key]["child"] as $k => $v) {
                $imgs = $tuCategoryModel->getCategoryTuImg($v["id"], $val["id"], $imgid);
                if (!empty($imgs["id"])) {
                    $imgid[] = $imgs["id"];
                }
                $result[$key]["child"][$k]["img_url"] = changeImgUrl($imgs["img_url"]);
            }
        }

        return $result;

    }

    //装修资讯  分站首页使用
    public function getHomeZixun($cityId = 0)
    {
        $littleArticleModel = new LittlearticleModel();
        $list = $littleArticleModel->getHomeZixun($cityId);
        foreach ($list as $key => $val) {
            $imgs = explode(",", $val["img"]);
            $list[$key]["face"] = $val["face"] ? $val["face"] : (changeImgUrl($imgs[0]));

            unset($list[$key]["img"]);
        }
        return $list ? $list : [];
    }

    //获取装修学堂    分站&主站共用
    public function getHomeXueTang()
    {
        //攻略
        $wwwArticleClassModel = new WwwArticleClassModel();
        $articleClassIds = $wwwArticleClassModel->getArticleClassIds("装修流程");
        $ids = [];
        foreach ($articleClassIds as $key => $val) {
            $ids[] = $val["id"];
        }
        $wwwArticleModel = new WwwArticleModel();
        $wwwArticleList = $wwwArticleModel->getHomeWwwArticle($ids, 3);
        foreach ($wwwArticleList as $key => $val) {
            $imgs = explode(",", $val["imgs"]);
            $wwwArticleList[$key]["face"] = $val["face"] ? changeImgUrl($val["face"]) : (changeImgUrl($imgs[0]));

            unset($wwwArticleList[$key]["imgs"]);
        }


        //百科
        $baikeModel = new BaikeModel();
        $baikList = $baikeModel->getHomeBaike(3);
        foreach ($baikList as $key => $val) {
            $baikList[$key]["thumb"] = changeImgUrl($val["thumb"]);
        }

        //问答
        $askModel = new AskModel();
        $ids = [1,2];
        $askList = $askModel->getHomeAsk($ids, 3);
        foreach ($askList as $key => $val) {
            $askList[$key]["logo"] = $val["logo"] ? changeImgUrl($val["logo"], 2) : 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
            $askList[$key]["username"] = $val["username"] ? $val["username"] : ($val["old_username"] ? $val["old_username"] : "");
        }
        $homexuetang = [];
        $homexuetang["article"] = $wwwArticleList ? $wwwArticleList : [];
        $homexuetang["baike"] = $baikList ? $baikList : [];
        $homexuetang["ask"] = $askList ? $askList : [];

        return $homexuetang;

    }

    // 获取首页装修公司列表
    public function getHomeCompanyList($cityId = 0)
    {
        $companyModel = new CompanyModel();
        $companyList = $companyModel->getHomeCompanyRank($cityId, 12);
        $countCompany = count($companyList);
        if ($countCompany < 12) {
            $limit = 12 - $countCompany;
            $list = $companyModel->getHomeOtherCompany($cityId, $limit);
            foreach ($list as $key => $val)
            {
                $companyList[] = $val;
            }
        }
        foreach ($companyList as $key => $val) {
            if (empty($val["logo"])) {
                $companyList[$key]["logo"] = $val["logo"] ? changeImgUrl($val["logo"]) : 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
            }
        }

        return $companyList ? $companyList : [];
    }


    public function getDecorationMaoCount($cityId = "")
    {
        $decorationMapModel = new DecorationMapModel();
        $map["city"] = $cityId;
        return $decorationMapModel->getMapCount($map);
    }


}
