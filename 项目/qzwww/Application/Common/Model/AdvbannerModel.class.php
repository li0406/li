<?php
/**
 *  活动配置表
 */
namespace Common\Model;
use Think\Model;
class AdvbannerModel extends Model{
    protected $tableName = "adv_banner";
    protected $autoCheckFields = false;
    /**
     * 获取广告列表
     * @param  [type] $module [模块名称]
     * @param  [type] $limit  [获取数量]
     * @return [type]         [description]
     */
    public function getAdvList($module,$city_id,$limit){
        $start = time();
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));

        $map = array(
            "status"=>array("EQ",1),
            "module"=>array("EQ",$module),
            array(
                array(
                  "start_time" => array("ELT",$start),
                  "end_time"   => array("EGT",$start)
                 ),
                array(
                    "start_time" => array("ELT",$start),
                    "end_time"   => array("EGT",$end)
                     ),
                array(
                    "start_time" => array("EQ",0),
                    "end_time"   => array("EQ",0),
                     ),
                "_logic"=>"OR"
            ),
            "city_id"=>array(
                    array("EQ",$city_id),
                    array("EQ",""),
                    array("EXP","IS NULL"),
                    array("EQ","0"),
                    "OR"
            ),
            "img_url" => array("NEQ",'')
        );
        $buildSql = M("adv_banner")->where($map)->order("sort,id desc")->limit($limit)->buildSql();
        return M("adv_banner")->table($buildSql)->alias("a")
                              ->join("LEFT JOIN qz_user as c on a.company_id = c.id")
                              ->join("LEFT JOIN qz_quyu as b on c.cs = b.cid")
                              ->field("a.*,b.bm,c.jc")->select();
    }


    /**
     * 获取轮播广告列表
     * @param  [type] $module [模块名称]
     * @param  [type] $limit  [获取数量]
     * @return [type]         [description]
     */
    public function getAdvBannerList($city_id,$limit){
        $start = time();
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));

        $map = array(
            "status"=>array("EQ",1),
            "module"=>array("EQ",'home_advbanner'),
            array(
                array(
                  "start_time" => array("ELT",$start),
                  "end_time"   => array("EGT",$start)
                 ),
                array(
                    "start_time" => array("ELT",$start),
                    "end_time"   => array("EGT",$end)
                     ),
                array(
                    "start_time" => array("EQ",0),
                    "end_time"   => array("EQ",0),
                     ),
                "_logic"=>"OR"
            ),
            "city_id" => array("EQ",$city_id),
            "img_url" => array("NEQ",'')
        );

        $buildSql = M("adv_banner")->where($map)->order("sort,id desc")->limit($limit)->buildSql();
        return M("adv_banner")->table($buildSql)->alias("a")
                              ->join("LEFT JOIN qz_user as c on a.company_id = c.id AND c.on = '2' ")
                              ->join("LEFT JOIN qz_quyu as b on c.cs = b.cid")
                              ->field("a.*,b.bm,c.jc")
                              ->order('a.sort,a.op_time DESC')
                              ->select();
    }

    /**
     * 获取品牌榜列表
     * @return [type] [description]
     */
    public function getBrandList($cs,$limit,$start= '0',$map = ''){
        $map["module"] = "home_brand";
        $map["status"] = array("EQ",1);
        $map["img_path"] = array("NEQ",'');
        if($cs){
            $map['city_id'] = array("EQ",$cs);
        }
        $buildSql = M("adv_banner")->where($map)->order("sort,id desc")->limit(100)->buildSql();
        $buildSql = M("adv_banner")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_user as b on b.id = t.company_id")
                              ->join("INNER JOIN qz_user_company as q on q.userid = b.id")
                              ->join("INNER JOIN qz_quyu as c on c.cid = b.cs")
                              ->join("LEFT JOIN qz_area as z on b.qx = z.qz_areaid")
                              ->field("t.*,b.dz, b.case_count,b.qc,b.jc,c.bm,q.comment_count,q.img,z.qz_area as area_name,b.on as 'real',q.fake")
                              ->group("t.company_id")
                              ->buildSql();

        return M("adv_banner")->table($buildSql)->alias("t1")
                              ->join("LEFT JOIN qz_team as team on team.comid = t1.company_id")
                              ->field("t1.*,count(team.id) as descount")
                              ->limit($start.','.$limit)
                              ->group("t1.company_id")
                              ->order("t1.sort,t1.id desc")
                              ->select();
    }

    /**
     * 获取顶部广告列表
     * @param  [type] $module [模块名称]
     * @param  [type] $limit  [获取数量]
     * @return [type]         [description]
     */
    public function getTopList($module,$city_id){
        $map = array(
            "status" => array("EQ", 1),
            "module" => array("EQ", $module),
            "start_time" => array("ELT", time()),
            "end_time" => array("EGT", time()),
            "city_id" => array("EQ",$city_id),
        );
        $buildSql = M("adv_banner")->where($map)->order("id desc")->buildSql();
        return M("adv_banner")->table($buildSql)->alias("a")
            ->join("LEFT JOIN qz_user as c on a.company_id = c.id")
            ->join("LEFT JOIN qz_quyu as b on c.cs = b.cid")
            ->field("a.*,b.bm,c.jc")->find();
    }
    /**
     *
     */
    public function getCarousel($num = 10){
        $map = array(
            "module" => 'home_advbanner_a',
            "status" => array("EQ", 1),
        );

        return M('adv_banner')->alias('a')
              ->where($map)
              ->join('LEFT JOIN qz_user as u on u.id = a.company_id')
              ->join("LEFT JOIN qz_quyu as q on q.cid = u.cs ")
              ->field('a.*,q.bm')
              ->limit($num)->order('sort')->select();
    }

}