<?php

namespace Home\Model\Db;

use Think\Model;

class SubTagModel extends Model{

    protected $autoCheckFields = false;

    public function getInfo($id){
        $map = array(
            "s.id" => array("EQ", $id)
        );

        return $this->alias("s")->where($map)
            ->join("left join qz_quyu as q on q.cid = s.cs")
            ->field("s.*,q.cname")
            ->find();
    }

    /**
     * 验证城市下名称唯一
     * @param  [type]  $name [description]
     * @param  [type]  $cs   [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function validateName($name, $cs, $id = 0){
        $map = array(
            "name" => array("EQ", $name),
            "cs" => array("EQ", $cs),
            "id" => array("NEQ", $id)
        );

        return $this->where($map)->count("id");
    }

    /**
     * 分页查询条件
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function getPageMap($input){
        $map = [];

        // 城市筛选
        if (!empty($input["cs"])) {
            $map["s.cs"] = array("EQ", $input["cs"]);
        }
        
        // 状态筛选
        if (!empty($input["enabled"])) {
            $map["s.enabled"] = array("EQ", $input["enabled"]);
        }
        
        // 名称筛选
        if (!empty($input["name"])) {
            $map["s.name"] = array("LIKE", "%" . $input["name"] . "%");
        }

        // 时间筛选
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $map["s.created_at"] = array("BETWEEN", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);
        } else if (!empty($input["date_begin"])) {
            $map["s.created_at"] = array("EGT", strtotime($input["date_begin"]));
        } else if (!empty($input["date_end"])) {
            $map["s.created_at"] = array("ELT", strtotime($input["date_end"]) + 86399);
        }

        return $map;
    }

    /**
     * 获取分页数量
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function getPageCount($input){
        $map = $this->getPageMap($input);
        return $this->alias("s")->where($map)->count("s.id");
    }

    /**
     * 获取分页数据
     * @param  [type]  $input  [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getPageList($input, $offset = 0, $limit = 0){
        $map = $this->getPageMap($input);
        return $this->alias("s")->where($map)
            ->join("left join qz_quyu as q on q.cid = s.cs")
            ->field("s.*,q.cname")
            ->order("s.created_at desc,s.id desc")
            ->limit($offset, $limit)
            ->select();
    }


    public function getCityTags($cityNames){
        $map = array(
            "q.cname" => array("IN", $cityNames)
        );

        return $this->alias("t")->where($map)
            ->join("right join qz_quyu as q on q.cid = t.cs")
            ->field("q.cid,q.cname,t.name")
            ->select();
    }


    




    /**
     * 获取公司标签
     * @param  [type] $companyIds [description]
     * @return [type]             [description]
     */
    public function getTagByCompany($companyIds){
        $map = array(
            "c.company_id" => array("IN", $companyIds)
        );

        return $this->alias("s")->where($map)
            ->join("qz_sub_tag_company as c on c.tag_id = s.id")
            ->field("s.id,s.name,c.company_id")
            ->select();
    }

    /**
     * 获取选择装修公司列表
     * @param  [type]  $type   [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getCompanyList($type, $tag_id, $cs, $input, $offset = 0, $limit = 0){
        $map = [
            "u.cs" => array("EQ", $cs),
            "uc.is_show" => array("EQ", 1)
        ];

        // 区域
        if (!empty($input["qx"])) {
            $map["u.qx"] = array("EQ", $input["qx"]);
        }

        // 装修公司
        if (!empty($input["keyword"])) {
            $map["_complex"] = array(
                "u.jc" => array("like", "%" . $input["keyword"] . "%"),
                "u.id" => array("EQ", $input["keyword"]),
                "_logic" => "or"
            );
        }

        // 选取状态
        if (!empty($input["checked"])) {
            if ($input["checked"] == 1) {
                $map[] = "sc.tag_id is not null";
            } else {
                $map[] = "sc.tag_id is null";
            }
        }
        
        $map[] = "uc.fake = 1 OR u.`on` NOT IN(2,4)";
        $dbQuery = M("user")->alias("u")->where($map)
            ->join("inner join qz_user_company as uc on u.id = uc.userid")
            ->join("left join qz_sub_tag_company as sc on sc.company_id = u.id and sc.tag_id = '$tag_id'");

        if ($type == "count") {
            $result = $dbQuery->count("u.id");
        } else {
            $result = $dbQuery
                ->join("left join qz_area as area on area.qz_areaid = u.qx")
                ->field("u.id,u.`on`,u.jc,u.cs,u.qx,area.qz_area as area_name,uc.fuwu,if(sc.tag_id is null, 0, 1) as checked")
                ->order("u.id desc")
                ->limit($offset, $limit)
                ->select();
        }
        
        return $result;
    }

    /**
     * 获取分站标签关联会员公司数量
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getCompanyCountByIds($ids){
        $map = array(
            "tag_id" => array("IN", $ids)
        );

        return M("sub_tag_company")->where($map)
            ->field("tag_id,count(id) as count_company")
            ->group("tag_id")
            ->select();
    }

    /**
     * 删除装修公司关联数据
     * @param  [type] $tag_id [description]
     * @return [type]         [description]
     */
    public function deleteCompanyByTagIds($tag_ids){
        $map = array(
            "tag_id" => array("IN", $tag_ids)
        );

        return M("sub_tag_company")->where($map)->delete();
    }

    /**
     * 删除装修公司关联数据
     * @param  [type] $tag_id     [description]
     * @param  [type] $company_id [description]
     * @return [type]             [description]
     */
    public function deleteCompanyByUnique($tag_id, $company_id){
        $map = array(
            "tag_id" => array("EQ", $tag_id),
            "company_id" => array("EQ", $company_id),
        );

        return M("sub_tag_company")->where($map)->delete();
    }

    /**
     * 增加装修公司关联数据
     * @param [type] $tag_id     [description]
     * @param [type] $company_id [description]
     */
    public function addCompany($tag_id, $company_id){
        $data = array(
            "tag_id" => $tag_id,
            "company_id" => $company_id,
            "created_at" => time(),
            "updated_at" => time()
        );

        return M("sub_tag_company")->data($data)->add();
    }








    /**
     * 获取案例标签
     * @param  [type] $caseIds [description]
     * @return [type]          [description]
     */
    public function getTagByCase($caseIds){
        $map = array(
            "c.case_id" => array("IN", $caseIds)
        );

        return $this->alias("s")->where($map)
            ->join("qz_sub_tag_case as c on c.tag_id = s.id")
            ->field("s.id,s.name,c.case_id")
            ->select();
    }

    /**
     * 获取案例列表
     * @param  [type]  $type   [description]
     * @param  [type]  $tag_id [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getCaseList($type, $tag_id, $cs, $input, $offset = 0, $limit = 0){
        $map = array(
            "a.cs" => array("EQ", $cs),
            "a.`on`" => array("EQ", 1),
            "a.`status`" => array("EQ", 2),
        );

        // 案例类型
        if (!empty($input["classid"])) {
            $map["a.classid"] = array("EQ", $input["classid"]);
        }

        // 户型
        if (!empty($input["huxing"])) {
            $map["a.huxing"] = array("EQ", $input["huxing"]);
        }

        // 类型
        if (!empty($input["leixing"])) {
            $map["a.leixing"] = array("EQ", $input["leixing"]);
        }

        // 风格
        if (!empty($input["fengge"])) {
            $map["a.fengge"] = array("EQ", $input["fengge"]);
        }

        // 小区名
        if (!empty($input["xiaoqu"])) {
            $map["a.title"] = array("LIKE", "%" . $input["xiaoqu"] . "%");
        }

        // 选取状态
        if (!empty($input["checked"])) {
            if ($input["checked"] == 1) {
                $map[] = "t.tag_id is not null";
            } else {
                $map[] = "t.tag_id is null";
            }
        }

        $map[] = "uc.userid is null or (uc.is_show = 1 AND (uc.fake = 1 or u.`on` NOT IN(2,4)))";
        $dbQuery = M("cases")->alias("a")->where($map)
            ->join("left join qz_sub_tag_case as t on t.case_id = a.id and t.tag_id = '$tag_id'")
            ->join("left join qz_user as u on u.id = a.uid")
            ->join("left join qz_user_company as uc on uc.userid = u.id");

        if ($type == "count") {
            $result = $dbQuery->count("a.id");
        } else {
            $subSql = $dbQuery->order("a.id desc")
                ->join("left join qz_fengge as fg on fg.id = a.fengge")
                ->field("a.id,a.title,uc.userid,u.jc,u.`on` as com_on,fg.`name` as fengge_name,if(t.tag_id is null, 0, 1) as checked")
                ->limit($offset, $limit)
                ->buildSql();

            $subSql = M()->table($subSql)->alias("t1")
                ->join("left join qz_case_img as i on i.caseid = t1.id and i.`status` < 3")
                ->field("t1.*,i.img,i.img_path,i.img_host")
                ->order("i.img_on desc,i.px asc,i.id desc")
                ->buildSql();

            $result = M()->table($subSql)->alias("t2")
                ->group("t2.id")
                ->order("t2.id desc")
                ->select();
        }

        return $result;
    }


    /**
     * 获取分站标签关联装修案例数量
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getCaseCountByIds($ids){
        $map = array(
            "tag_id" => array("IN", $ids)
        );

        return M("sub_tag_case")->where($map)
            ->field("tag_id,count(id) as count_case")
            ->group("tag_id")
            ->select();
    }

    /**
     * 删除装修案例关联数据
     * @param  [type] $tag_id [description]
     * @return [type]         [description]
     */
    public function deleteCaseByTagIds($tag_ids){
        $map = array(
            "tag_id" => array("IN", $tag_ids)
        );

        return M("sub_tag_case")->where($map)->delete();
    }

    /**
     * 删除装修案例关联数据
     * @param  [type] $tag_id  [description]
     * @param  [type] $case_id [description]
     * @return [type]          [description]
     */
    public function deleteCaseByUnique($tag_id, $case_id){
        $map = array(
            "tag_id" => array("EQ", $tag_id),
            "case_id" => array("EQ", $case_id),
        );

        return M("sub_tag_case")->where($map)->delete();
    }

    /**
     * 增加装修案例关联数据
     * @param [type] $tag_id  [description]
     * @param [type] $case_id [description]
     */
    public function addCase($tag_id, $case_id){
        $data = array(
            "tag_id" => $tag_id,
            "case_id" => $case_id,
            "created_at" => time(),
            "updated_at" => time()
        );

        return M("sub_tag_case")->data($data)->add();
    }












    /**
     * 获取文章标签
     * @param  [type] $articleIds [description]
     * @return [type]             [description]
     */
    public function getTagByArticle($articleIds){
        $map = array(
            "a.article_id" => array("IN", $articleIds)
        );

        return $this->alias("s")->where($map)
            ->join("qz_sub_tag_article as a on a.tag_id = s.id")
            ->field("s.id,s.name,a.article_id")
            ->select();
    }

    /**
     * 获取文章列表
     * @param  [type]  $tag_id [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getArticleList($type, $tag_id, $cs, $input, $offset = 0, $limit = 0){
        $map = [
            "a.cs" => array("EQ", $cs)
        ];

        // 发布状态
        if (!empty($input["state"])) {
            $map["a.state"] = array("EQ", $input["state"]);
        } else {
            $map["a.state"] = array("NEQ", "-1");
        }

        // 文章标题/ID
        if (!empty($input["keyword"])) {
            $map["_complex"] = array(
                "a.title" => array("like", "%" . $input["keyword"] . "%"),
                "a.id" => array("EQ", $input["keyword"]),
                "_logic" => "or"
            );
        }

        // 选取状态
        if (!empty($input["checked"])) {
            if ($input["checked"] == 1) {
                $map[] = "t.tag_id is not null";
            } else {
                $map[] = "t.tag_id is null";
            }
        }
        
        $dbQuery = M("little_article")->alias("a")->where($map)
            ->join("left join qz_infotype as b on b.id = a.classid")
            ->join("left join qz_sub_tag_article as t on t.article_id = a.id and t.tag_id = '$tag_id'");

        if ($type == "count") {
            $result = $dbQuery->count("a.id");
        } else {
            $result = $dbQuery
                ->field("a.id,a.`title`,b.`name` as type_name,a.realview,if(t.tag_id is null, 0, 1) as checked")
                ->order("a.id desc")
                ->limit($offset, $limit)
                ->select();
        }
        
        return $result;
    }

    /**
     * 获取分站标签关联文章数量
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getArticleCountByIds($ids){
        $map = array(
            "tag_id" => array("IN", $ids)
        );

        return M("sub_tag_article")->where($map)
            ->field("tag_id,count(id) as count_article")
            ->group("tag_id")
            ->select();
    }

    /**
     * 删除文章关联数据
     * @param  [type] $tag_id [description]
     * @return [type]         [description]
     */
    public function deleteArticleByTagIds($tag_ids){
        $map = array(
            "tag_id" => array("IN", $tag_ids)
        );

        return M("sub_tag_article")->where($map)->delete();
    }

    /**
     * 删除文章关联数据
     * @param  [type] $tag_id     [description]
     * @param  [type] $article_id [description]
     * @return [type]             [description]
     */
    public function deleteArticleByUnique($tag_id, $article_id){
        $map = array(
            "tag_id" => array("EQ", $tag_id),
            "article_id" => array("EQ", $article_id),
        );

        return M("sub_tag_article")->where($map)->delete();
    }

    /**
     * 增加文章关联数据
     * @param [type] $tag_id     [description]
     * @param [type] $article_id [description]
     */
    public function addArticle($tag_id, $article_id){
        $data = array(
            "tag_id" => $tag_id,
            "article_id" => $article_id,
            "created_at" => time(),
            "updated_at" => time()
        );

        return M("sub_tag_article")->data($data)->add();
    }









    /**
     * 获取设计师标签
     * @param  [type] $designer [description]
     * @return [type]           [description]
     */
    public function getTagByDesigner($designer){
        $map = array(
            "d.designer" => array("IN", $designer)
        );

        return $this->alias("s")->where($map)
            ->join("qz_sub_tag_designer as d on d.tag_id = s.id")
            ->field("s.id,s.name,d.designer")
            ->select();
    }

    /**
     * 获取选取设计师列表
     * @param  [type]  $type   [description]
     * @param  [type]  $tag_id [description]
     * @param  [type]  $cs     [description]
     * @param  [type]  $input  [description]
     * @param  integer $offset [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getDesignerList($type, $tag_id, $cs, $input, $offset = 0, $limit = 0){
        $map = array(
            "u.cs" => array("EQ", $cs),
            "u.classid" => array("EQ", 2),
        );

        // 擅长风格
        if (!empty($input["fengge"])) {
            $map[] = sprintf("FIND_IN_SET('%s', des.fengge)", $input["fengge"]);
        }

        // 擅长领域
        if (!empty($input["lingyu"])) {
            $map[] = sprintf("FIND_IN_SET('%s', des.lingyu)", $input["lingyu"]);
        }

        // 设计师姓名/ID：
        if (!empty($input["keyword"])) {
            $map["_complex"] = array(
                "u.name" => array("like", "%" . $input["keyword"] . "%"),
                "u.id" => array("EQ", $input["keyword"]),
                "_logic" => "or"
            );
        }

        // 选取状态
        if (!empty($input["checked"])) {
            if ($input["checked"] == 1) {
                $map[] = "sd.tag_id is not null";
            } else {
                $map[] = "sd.tag_id is null";
            }
        }
        
        $map[] = "uc.userid is null OR (uc.is_show = 1 AND (uc.fake = 1 OR cu.`on` NOT IN(2,4)))";
        $dbQuery = M("user")->alias("u")->where($map)
            ->join("left join qz_user_des as des on des.userid = u.id")
            ->join("left join qz_sub_tag_designer as sd on sd.designer = u.id and sd.tag_id = '$tag_id'")
            ->join("left join qz_team as team on team.userid = u.id")
            ->join("left join qz_user as cu on cu.id = team.comid")
            ->join("left join qz_user_company as uc on uc.userid = cu.id");
        
        if ($type == "count") {
            $result = $dbQuery->count("u.id");
        } else {
            $result = $dbQuery
                ->field("u.id,u.`user`,u.`name`,u.logo,des.fengge,des.lingyu,team.comid,cu.jc,cu.`on`,if(sd.tag_id is null, 0, 1) as checked")
                ->order("u.time desc")
                ->limit($offset, $limit)
                ->select();
        }

        return $result;
    }

    /**
     * 获取分站标签关联设计师数量
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getDesignerCountByIds($ids){
        $map = array(
            "tag_id" => array("IN", $ids)
        );

        return M("sub_tag_designer")->where($map)
            ->field("tag_id,count(id) as count_designer")
            ->group("tag_id")
            ->select();
    }

    /**
     * 删除设计师关联数据
     * @param  [type] $tag_id [description]
     * @return [type]         [description]
     */
    public function deleteDesignerByTagIds($tag_ids){
        $map = array(
            "tag_id" => array("IN", $tag_ids)
        );

        return M("sub_tag_designer")->where($map)->delete();
    }

    /**
     * 删除设计师关联数据
     * @param  [type] $tag_id   [description]
     * @param  [type] $designer [description]
     * @return [type]           [description]
     */
    public function deleteDesignerByUnique($tag_id, $designer){
        $map = array(
            "tag_id" => array("EQ", $tag_id),
            "designer" => array("EQ", $designer),
        );

        return M("sub_tag_designer")->where($map)->delete();
    }

    /**
     * 增加设计师关联数据
     * @param [type] $tag_id   [description]
     * @param [type] $designer [description]
     */
    public function addDesigner($tag_id, $designer){
        $data = array(
            "tag_id" => $tag_id,
            "designer" => $designer,
            "created_at" => time(),
            "updated_at" => time()
        );

        return M("sub_tag_designer")->data($data)->add();
    }

    /**
     * [getTagsByName 根据标签名获取标签]
     * @param  [type]  $name  [标签名]
     * @param  integer $limit [获取条数]
     * @param  string  $istop [是否推荐]
     * @param  string  $order [排序]
     * @return [type]         [标签数组]
     */
    public function getTagsByName($name,$cs,$limit = 20){
        if(!empty($cs)){
            $map['cs'] =  array('EQ',$cs);
//            $map['enabled'] =  array('EQ',1);
            if(!empty($name)){
                //重新定义名字查询条件
                $map['name'] =  array('like','%'.$name.'%');
            }

            $result = M('sub_tag')->field('id,name')->where($map)->limit($limit)->order('created_at desc,id desc')->select();
            return $result;
        }

    }

    public function insertInfo($data){
        return  M('sub_tag_article')->addAll($data);
    }

    public function deleteInfo($id){
        $map["article_id"] = ['eq',$id];
        return M('sub_tag_article')->where($map)->delete();
    }

	 public function getSubTag($name,$id,$limit=10){
         if(!empty($name)){
             //重新定义名字查询条件
             $map['name'] =  array('like','%'.$name.'%');
         }

         if(!empty($id)){
             $map2['id'] = ['eq',$id];
             $select = M('sub_tag')->field('id,name')->where($map2)->find();
             $map['id'] =  ['neq',$id];
         }
         $result = M('sub_tag')->field('id,name')->where($map)->limit($limit)->order('created_at desc,id desc')->select();
         if(!empty($select)){
             array_unshift($result,$select);
         }
         return $result;
    }


    public function getCompanyByTag($tagid,$limit = 8){
        $map['tag_id'] = ['eq',$tagid];
        return M('sub_tag_company')->field('company_id')->where($map)->order('id desc')->limit($limit)->select();
    }

    public function getCaseByTag($tagid,$limit = 8){
        $map['tag_id'] = ['eq',$tagid];
        return M('sub_tag_case')->field('case_id')->where($map)->order('id desc')->limit($limit)->select();
    }


}