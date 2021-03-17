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
    public function getAdvList($module,$city_id,$limit = 10){
        $date = mktime(23,59,59,date("m"),date("d"),date("Y"));

        $map = array(
            "status"=>array("EQ",1),
            "module"=>array("EQ",$module),
            array(
                array(
                    "start_time" => array("ELT",$date),
                    "end_time"   => array("EGT",$date)
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
            "img_url_mobile" => array("NEQ",'')
        );

        $buildSql = M("adv_banner")->where($map)->order("sort,id desc")->limit($limit)->buildSql();
        return M("adv_banner")->table($buildSql)->alias("a")
                              ->join("LEFT JOIN qz_user as c on a.company_id = c.id")
                              ->join("LEFT JOIN qz_quyu as b on c.cs = b.cid")
                              ->field("a.*,b.bm,c.jc")->select();
    }

    /**
     * 获取品牌榜列表
     * @return [type] [description]
     */
    public function getBrandList($cs,$limit,$start= '0'){
        $map = array(
            "module"=>"home_brand",
            "city_id"=>array("EQ",$cs),
            "status"=>array("EQ",1)
        );
        $buildSql = M("adv_banner")->where($map)->order("sort,id desc")->limit($start.','.$limit)->buildSql();
        $buildSql = M("adv_banner")->table($buildSql)->alias("a")
                                   ->join("LEFT JOIN qz_comment as b on b.comid = a.company_id")
                                   ->field("a.*,b.sj,b.fw,b.sg,b.count")
                                   ->buildSql();
        $buildSql = M("adv_banner")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_user as b on b.id = t.company_id")
                              ->join("INNER JOIN qz_quyu as c on c.cid = b.cs")
                              ->field("t.*,b.dz, b.case_count,b.qc,b.jc,c.bm,count(t.id) as commentcount, round(avg(IF(sj<>0,sj,null)),1) avgsj,round( avg(IF(sg<>0,sg,null)),1) avgsg,round(avg(IF(fw<>0,fw,null)),1) avgfw")
                              ->group("t.company_id")
                              ->buildSql();

        return M("adv_banner")->table($buildSql)->alias("t1")
                              ->join("LEFT JOIN qz_team as team on team.comid = t1.company_id")
                              ->field("t1.*,count(team.id) as descount")
                              ->group("t1.company_id")
                              ->order("t1.sort,t1.id desc")
                              ->select();
    }

    public function getCaseList($city_id, $limit)
    {
        $date = mktime(23,59,59,date("m"),date("d"),date("Y"));

        $map = array(
            "status" => array("EQ", 1),
            "module" => 'home_cases',
            array(
                array(
                    "start_time" => array("ELT",$date),
                    "end_time"   => array("EGT",$date)
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
            )
        );

        $buildSql = M("adv_banner")->where($map)->order("city_id DESC, sort, id desc")->limit($limit)->buildSql();
        return M("adv_banner")->table($buildSql)->alias("a")
                              ->join("LEFT JOIN qz_user as c on a.company_id = c.id")
                              ->join("LEFT JOIN qz_quyu as b on c.cs = b.cid")
                              ->field("a.*,b.bm,c.jc")->select();
    }
}
