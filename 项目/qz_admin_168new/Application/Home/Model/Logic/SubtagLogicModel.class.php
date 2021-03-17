<?php

namespace Home\Model\Logic;

use Home\Model\CasesModel;
use Home\Model\Db\SubTagModel;
use Home\Model\Db\CompanyTagsModel;

class SubtagLogicModel {

    const NAME_MAX_LENGTH = 15;

    public function getInfo($id){
        $subTagModel = new SubTagModel();
        return $subTagModel->getInfo($id);
    }

    /**
     * 分站标识分页列表
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getPageList($input, $page = 1, $limit = 20){
        $subTagModel = new SubTagModel();
        $count = $subTagModel->getPageCount($input);

        $list = [];
        if ($count > 0) {
            $list = $subTagModel->getPageList($input, ($page - 1) * $limit, $limit);
            $list = $this->setFormatter($list);
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 格式处理、关联数据数量获取
     * @param [type] $list [description]
     */
    public function setFormatter($list){
        $ids = array_column($list, "id");

        $subTagModel = new SubTagModel();

        // 查询关联会员公司数量
        $companys = $subTagModel->getCompanyCountByIds($ids);
        $companys = array_column($companys, "count_company", "tag_id");

        // 查询关联装修案例数量
        $cases = $subTagModel->getCaseCountByIds($ids);
        $cases = array_column($cases, "count_case", "tag_id");
        
        // 查询关联文章数量
        $articles = $subTagModel->getArticleCountByIds($ids);
        $articles = array_column($articles, "count_article", "tag_id");

        // 查询关联设计师数量
        $designers = $subTagModel->getDesignerCountByIds($ids);
        $designers = array_column($designers, "count_designer", "tag_id");

        foreach ($list as $key => $item) {
            $id = $item["id"];
            $list[$key]["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);

            // 会员公司数量
            $list[$key]["count_company"] = 0;
            if (array_key_exists($id, $companys)) {
                $list[$key]["count_company"] = $companys[$id];
            }

            // 装修案例数量
            $list[$key]["count_case"] = 0;
            if (array_key_exists($id, $cases)) {
                $list[$key]["count_case"] = $cases[$id];
            }

            // 文章数量
            $list[$key]["count_article"] = 0;
            if (array_key_exists($id, $articles)) {
                $list[$key]["count_article"] = $articles[$id];
            }

            // 设计师数量
            $list[$key]["count_designer"] = 0;
            if (array_key_exists($id, $designers)) {
                $list[$key]["count_designer"] = $designers[$id];
            }
        }

        return $list;
    }

    /**
     * 验证名称唯一性
     * @param  [type] $name [description]
     * @param  [type] $cs   [description]
     * @return [type]       [description]
     */
    public function validateName($name, $cs, $id = 0){
        $subTagModel = new SubTagModel();
        $result = $subTagModel->validateName($name, $cs, $id);
        return $result ? false : true;
    }

    /**
     * 保存数据
     * @param  [type] $id    [description]
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function saveInfo($id, $input){
        $subTagModel = new SubTagModel();

        $data = array(
            "cs" => $input["cs"],
            "name" => $input["name"],
            "enabled" => $input["enabled"] == 1 ? 1 : 2,
            "updated_at" => time()
        );

        if (!empty($id)) {
            $data["id"] = $id;
            $result = $subTagModel->save($data);
        } else {
            $data["created_at"] = time();
            $result = $subTagModel->data($data)->add();
        }

        return $result;
    }

    /**
     * 设置标识启用、禁用
     * @param [type] $id      [description]
     * @param [type] $enabled [description]
     * @return [type]        [description]
     */
    public function setEnabled($id, $enabled){
        $data = array(
            "id" => $id,
            "enabled" => $enabled == 1 ? 1 : 2,
            "updated_at" => time()
        );

        $subTagModel = new SubTagModel();
        $result = $subTagModel->save($data);
        return $result;
    }

    /**
     * 删除标识（同步删关联数据）
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteInfo($ids){
        $ids = explode(",", $ids);

        if (!empty($ids)) {
            $subTagModel = new SubTagModel();
            $result = $subTagModel->where(["id" => array("IN", $ids)])->delete();
            
            // 删除关联数据库
            if (!empty($result)) {
                $subTagModel->deleteCompanyByTagIds($ids);
                $subTagModel->deleteCaseByTagIds($ids);
                $subTagModel->deleteDesignerByTagIds($ids);
                $subTagModel->deleteArticleByTagIds($ids);
            }
        }

        return $result;
    }

    /**
     * 设置关联
     * @param [type] $type        [description]
     * @param [type] $tag_id      [description]
     * @param [type] $relation_id [description]
     * @param [type] $is_relation [description]
     */
    public function setRelation($type, $tag_id, $relation_id, $is_relation){
        try {
            $subTagModel = new SubTagModel();
            switch ($type) {
                case 'company':
                    if ($is_relation == 1) {
                        $result = $subTagModel->addCompany($tag_id, $relation_id);
                    } else {
                        $result = $subTagModel->deleteCompanyByUnique($tag_id, $relation_id);
                    }
                    break;
                
                case 'case':
                    if ($is_relation == 1) {
                        $result = $subTagModel->addCase($tag_id, $relation_id);
                    } else {
                        $result = $subTagModel->deleteCaseByUnique($tag_id, $relation_id);
                    }

                    // 案例更新时间
                    $casesModel = new CasesModel();
                    $data = array("update_time" => time());
                    $casesModel->saveData($relation_id, $data);
                    break;

                case 'article':
                    if ($is_relation == 1) {
                        $result = $subTagModel->addArticle($tag_id, $relation_id);
                    } else {
                        $result = $subTagModel->deleteArticleByUnique($tag_id, $relation_id);
                    }
                    break;

                case 'designer':
                    if ($is_relation == 1) {
                        $result = $subTagModel->addDesigner($tag_id, $relation_id);
                    } else {
                        $result = $subTagModel->deleteDesignerByUnique($tag_id, $relation_id);
                    }
                    break;

                default:
                    $result = false;
                    break;
            }
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * 保存Excel数据到数据库
     * @param  [type] $sheetData [description]
     * @return [type]            [description]
     */
    public function saveExcelData($sheetData){
        $result = false;
        if (is_array($sheetData) && count($sheetData > 1)) {
            try {
                $cityTagList = [];
                foreach ($sheetData as $key => $item) {
                    if ($key == 0) continue;

                    $tag_name = trim($item["0"]);
                    $city_name = trim($item["1"]);
                    $enabled_name = trim($item["2"]);

                    if ($tag_name && $city_name && mb_strlen($tag_name) <= static::NAME_MAX_LENGTH) {
                        if (in_array($enabled_name, ["启用", "禁用"])) {
                            $cityTagList[$city_name][] = array(
                                "tag_name" => $tag_name,
                                "city_name" => $city_name,
                                "enabled" => $enabled_name == "启用" ? 1 : 2
                            );
                        }
                    }

                    unset($tag_name, $city_name, $enabled_name);
                }

                // 查询城市标识
                $cityNames = array_keys($cityTagList);
                $subTagModel = new SubTagModel();
                $tagList = $subTagModel->getCityTags($cityNames);

                $cityTags = []; // 城市标识
                foreach ($tagList as $key => $item) {
                    $cname = $item["cname"];
                    if (!array_key_exists($cname, $cityTags)) {
                        $cityTags[$cname] = array(
                            "cid" => $item["cid"],
                            "tag_name" => []
                        );
                    }

                    if ($item["name"]) {
                        $cityTags[$cname]["tag_name"][] = $item["name"];
                    }
                }

                // 遍历添加
                $insertData = [];
                foreach ($cityTagList as $cname => $tags) {
                    if (array_key_exists($cname, $cityTags)) {
                        foreach ($tags as $k => $item) {
                            if (!in_array($item["tag_name"], $cityTags[$cname]["tag_name"])) {
                                $cityTags[$cname]["tag_name"][] = $item["tag_name"];

                                $insertData[] = array(
                                    "name" => $item["tag_name"],
                                    "cs" => $cityTags[$cname]["cid"],
                                    "enabled" => $item["enabled"],
                                    "created_at" => time(),
                                    "updated_at" => time()
                                );
                            }
                        }
                    }
                }

                $result = $subTagModel->addAll($insertData);
            } catch (\Exception $e) {
                $result = false;
            }
        }

        return $result;
    }


    /**
     * 选取装修公司列表
     * @param  [type]  $cs    [description]
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getCompanyList($tag_id, $cs, $input, $page = 1, $limit = 15){
        $subTagModel = new SubTagModel();
        $count = $subTagModel->getCompanyList("count", $tag_id, $cs, $input);

        $list = [];
        if ($count > 0) {
            $list = $subTagModel->getCompanyList("list", $tag_id, $cs, $input, ($page - 1) * $limit, $limit);

            $companyIds = array_column($list, "id");
            $taglist = $subTagModel->getTagByCompany($companyIds);

            $companyTags = [];
            foreach ($taglist as $key => $item) {
                $company_id = $item["company_id"];
                $companyTags[$company_id][] = $item["name"];
            }

            // 获取所有服务类型
            $map = array("type" => array("EQ", "leixing"));
            $fuwuList = M("leixing")->where($map)->field("id,name")->select();
            $fuwuList = array_column($fuwuList, null, "id");

            foreach ($list as $key => $item) {
                // 分配服务类型
                $fuwu = explode(",", $item["fuwu"]);
                $fuwu = array_filter(array_unique($fuwu));

                $fuwuleixing = [];
                foreach ($fuwu as $k => $lx) {
                    $fuwuleixing[] = $fuwuList[$lx]["name"];
                }

                $list[$key]["fuwuleixing"] = "-";
                if (count($fuwuleixing) > 0) {
                    $list[$key]["fuwuleixing"] = implode(",", $fuwuleixing);
                }

                $id = $item["id"];
                $list[$key]["tagnames"] = implode(",", $companyTags[$id]);

                switch ($item["on"]) {
                    case '0':
                        $list[$key]["on_name"] = "注册会员";
                        break;

                    case '-1':
                        $list[$key]["on_name"] = "过期会员";
                        break;
                    
                    default:
                        $list[$key]["on_name"] = "假会员";
                        break;
                }
            }
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 获取选取设计师列表
     * @param  [type]  $tag_id [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $page   [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getDesignerList($tag_id, $cs, $input, $page = 1, $limit = 10){
        $subTagModel = new SubTagModel();
        $count = $subTagModel->getDesignerList("count", $tag_id, $cs, $input);

        $list = [];
        if ($count > 0) {
            $list = $subTagModel->getDesignerList("list", $tag_id, $cs, $input, ($page - 1) * $limit, $limit);

            $userids = array_column($list, "id");
            $tags = $subTagModel->getTagByDesigner($userids);

            $userTags = [];
            foreach ($tags as $key => $item) {
                $designer = $item["designer"];
                $userTags[$designer][] = $item["name"];
            }

            foreach ($list as $key => $item) {
                $designer = $item["id"];
                if (array_key_exists($designer, $userTags)) {
                    $list[$key]["tags"] = implode(",", $userTags[$designer]);
                } else {
                    $list[$key]["tags"] = "";
                }

                if (empty($item["lingyu"])) {
                    $list[$key]["lingyu"] = "-";
                }

                if (empty($item["fengge"])) {
                    $list[$key]["fengge"] = "-";
                }

                $list[$key]["on_name"] = "";
                if (!empty($item["comid"])) {
                    switch ($item["on"]) {
                        case '0':
                            $list[$key]["on_name"] = "注册会员";
                            break;

                        case '-1':
                            $list[$key]["on_name"] = "过期会员";
                            break;
                        
                        default:
                            $list[$key]["on_name"] = "假会员";
                            break;
                    }
                }
            }
        }
        
        return ["count" => $count, "list" => $list];
    }

    /**
     * 获取选取文章列表
     * @param  [type]  $tag_id [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $page   [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getArticleList($tag_id, $cs, $input, $page = 1, $limit = 10){
        $subTagModel = new SubTagModel();
        $count = $subTagModel->getArticleList("count", $tag_id, $cs, $input);

        $list = [];
        if ($count > 0) {
            $list = $subTagModel->getArticleList("list", $tag_id, $cs, $input, ($page - 1) * $limit, $limit);
            
            $articleIds = array_column($list, "id");
            $tags = $subTagModel->getTagByArticle($articleIds);

            $artTags = [];
            foreach ($tags as $key => $item) {
                $article_id = $item["article_id"];
                $artTags[$article_id][] = $item["name"];
            }

            foreach ($list as $key => $item) {
                $article_id = $item["id"];
                if (array_key_exists($article_id, $artTags)) {
                    $list[$key]["tags"] = implode(",", $artTags[$article_id]);
                } else {
                    $list[$key]["tags"] = "";
                }
            }
        }
        
        return ["count" => $count, "list" => $list];
    }

    /**
     * 获取选取装修案例列表
     * @param  [type]  $tag_id [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $page   [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getCaseList($tag_id, $cs, $input, $page = 1, $limit = 10){
        $subTagModel = new SubTagModel();
        $count = $subTagModel->getCaseList("count", $tag_id, $cs, $input);

        $list = [];
        if ($count > 0) {
            $list = $subTagModel->getCaseList("list", $tag_id, $cs, $input, ($page - 1) * $limit, $limit);
            
            $caseIds = array_column($list, "id");
            $tags = $subTagModel->getTagByCase($caseIds);

            $caseTags = [];
            foreach ($tags as $key => $item) {
                $case_id = $item["case_id"];
                $caseTags[$case_id][] = $item["name"];
            }

            foreach ($list as $key => $item) {
                $case_id = $item["id"];
                if (array_key_exists($case_id, $caseTags)) {
                    $list[$key]["tags"] = implode(",", $caseTags[$case_id]);
                } else {
                    $list[$key]["tags"] = "";
                }

                $list[$key]["on_name"] = "";
                if (!empty($item["userid"])) {
                    switch ($item["com_on"]) {
                        case '0':
                            $list[$key]["on_name"] = "注册会员";
                            break;

                        case '-1':
                            $list[$key]["on_name"] = "过期会员";
                            break;
                        
                        default:
                            $list[$key]["on_name"] = "假会员";
                            break;
                    }
                }

                if ($item["img_host"] != "qiniu") {
                    $list[$key]["img_path"] = $item["img_path"] . $item["img"];
                }
            }
        }
        
        return ["count" => $count, "list" => $list];
    }
}