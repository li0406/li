<?php
/**
 * 用户收藏表
 */
namespace Common\Model;
use Think\Model;
class UsercollectModel extends Model{
    protected $tableName ="user_collect";

    /**
     * 添加收藏
     */
    public function addCollect($data){
        return M("user_collect")->add($data);
    }

    /**
     * 删除收藏
     * @return [type] [description]
     */
    public function delcollect($id,$userid){
        $map = array(
                "classid"=>array("EQ",$id),
                "userid"=>array("EQ",$userid),
                     );
        return M("user_collect")->where($map)->delete();
    }

    /**
     * 获取关注记录
     * @param  [type] $id     [关注对象编号]
     * @param  [type] $userid [用户编号]
     * @return [type]         [description]
     */
    public function getCollectCount($id,$userid,$type){
        $map = array(
                "classid"=>array("EQ",$id),
                "userid"=>array("EQ",$userid),
                "classtype"=>array("EQ",$type)
                     );
        return M("user_collect")->where($map)->count();
    }
    /**
     * 获取关注列表的数量
     * @param  [type] $id   [用户编号]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function getCollectListCount($id,$type = ""){
        //如果是文章，查询小站文章和总站文章
        if($type == 1){
            $type = array(1,3);
        }elseif($type == 2){
            $type = array(2,4);
        }else{
            $type = array(5);
        }

        $map = array(
                "userid"=>array("EQ",$id),
                "classtype"=>array("IN",$type)
                     );

        return M("user_collect")->where($map)->count();
    }

    /**
     * 获取关注列表的数量
     * @param  [type] $id   [用户编号]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function getCollectList($id,$type = "",$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //如果是文章，查询小站文章和总站文章
        if($type == 1){
            $classType = array(1,3);
        }elseif($type == 2){
            $classType = array(2,4);
        }else{
            $classType = array(5);
        }

        $map = array(
                "userid"=>array("EQ",$id),
                "classtype"=>array("IN",$classType)
                     );

        //1.获取收藏的信息
        $buildSql = M("user_collect")->where($map)
                                     ->field("classid,time,classtype")
                                     ->limit($pageIndex.",".$pageCount)
                                     ->order("time desc")
                                     ->buildSql();
        //type  1 文章 2 图片案例
        switch ($type) {
            case '1':
                //1.查询总站文章
                $joinSql = M("user_collect")->table($buildSql)->alias("t")
                                            ->join("INNER JOIN qz_www_article as a on a.id = t.classid and t.classtype = 1")
                                            ->field("t.time,a.id as cid,a.title,a.face as img_path, '' as cs")
                                            ->buildSql();
                $joinSql = M("user_collect")->table($joinSql)->alias("t1")
                                            ->join("INNER JOIN qz_www_article_class_rel as c on c.article_id = t1.cid")
                                            ->join("INNER JOIN qz_www_article_class as b on c.class_id = b.id")
                                            ->field("t1.*,c.article_id as infoid, b.shortname,b.classname,'1' as classtype")
                                            ->buildSql();


                //2.查询分站文章
                $littleSql = M("user_collect")->table($buildSql)->alias("t2")
                                              ->join("INNER JOIN qz_little_article as a on a.id = t2.classid and t2.classtype = 3")
                                              ->field("t2.time,a.id as cid,a.title,a.face as img_path,a.cs,a.classid as infoid")
                                              ->buildSql();
                $littleSql = M("user_collect")->table($littleSql)->alias("t3")
                                              ->join("INNER JOIN qz_infotype as b on b.id = t3.infoid")
                                              ->field("t3.*,b.shortname,b.name as classname,'3' as classtype")
                                              ->buildSql();

                $buildSql =  M("user_collect")->table($joinSql)->alias("t")
                                              ->union($littleSql,true)
                                              ->buildSql();
                return  M("user_collect")->table($buildSql)->alias("t")
                                              ->join("LEFT join qz_quyu as q on q.cid = t.cs")
                                              ->field("t.*,q.bm")
                                              ->select();

                break;
            case '2':
                //装修公司案例
                $caseSql = M("user_collect")->table($buildSql)->alias("t")
                                         ->join("INNER JOIN qz_cases as t1 on t1.id = t.classid and t.classtype = 2")
                                         ->field("t.time,t1.id as cid,t1.title,t1.fengge,t1.cs,'2' as classtype")
                                         ->order("t.time desc")
                                         ->buildSql();
                //获取装修公司案例其他信息
                $caseSql = M("user_collect")->table($caseSql)->alias("t2")
                            ->join("INNER JOIN qz_fengge as c on c.id = t2.fengge")
                            ->join("INNER JOIN qz_quyu as f on f.cid = t2.cs")
                            ->field("t2.*,c.name as classname,f.bm")
                            ->buildSql();
                //获取装修公司图片信息
                $caseSql = M("user_collect")->table($caseSql)->alias("t3")
                            ->join("INNER JOIN qz_case_img as d on d.caseid = t3.cid")
                            ->order("d.img_on desc,d.px")
                            ->field("t3.*,d.img_host,d.img_path,d.img")
                            ->buildSql();
                //获取公司案例
                $caseSql = M("user_collect")->table($caseSql)->alias("t4")
                                            ->group("t4.cid")
                                            ->buildSql();

                //获取家居美图案例
                $meituSql =  M("user_collect")->table($buildSql)->alias("t5")
                                              ->join("INNER JOIN qz_meitu as m on m.id = t5.classid and t5.classtype = 4")
                                              ->field("t5.time,m.id as cid,m.title,m.fengge,'' as cs,'4' as classtype,'' as img , '' as bm ")
                                              ->buildSql();
                //获取家居美图案例其他信息
                $meituSql =  M("user_collect")->table($meituSql)->alias("t6")
                                              ->join("INNER JOIN qz_meitu_fengge as c on find_in_set(c.id,t6.fengge)")
                                              ->field("t6.*,GROUP_CONCAT(c.name) as classname")
                                              ->group("t6.cid")
                                              ->buildSql();
                //获取美图的图片信息
                $meituSql =  M("user_collect")->table($meituSql)->alias("t7")
                                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t7.cid")
                                              ->field("t7.time,t7.cid,t7.title,t7.fengge,'' as cs,t7.classtype,t7.classname,'' as bm,f.img_host,f.img_path,'' as img")
                                              ->order("img_on desc,px")
                                              ->buildSql();
                $meituSql = M("user_collect")->table($meituSql)->alias("t8")
                                             ->group("t8.cid")
                                             ->buildSql();
                $buildSql = M("user_collect")->table($caseSql)->alias("t")
                                            ->union($meituSql,true)
                                            ->buildSql();
                return  M("user_collect")->table($buildSql)->alias("t4")
                                        ->group("t4.cid")
                                        ->select();
                break;
            case "3":
                $buildSql = M("user_collect")->table($buildSql)->alias("t")
                                            ->join("INNER JOIN qz_info as i on i.id = t.classid and t.classtype = 5")
                                            ->field("t.time,i.id as cid ,i.cs ,i.title,i.uid")
                                            ->buildSql();
               return M("user_collect")->table($buildSql)->alias("t1")
                                            ->join("INNER join qz_quyu as q on q.cid = t1.cs")
                                            ->field("t1.*,q.bm")
                                            ->select();

                break;
            default:
            return false;
            break;
        }



    }
}