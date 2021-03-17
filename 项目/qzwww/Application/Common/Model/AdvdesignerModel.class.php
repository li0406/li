<?php
/**
 *  推荐设计师表
 */
namespace Common\Model;
use Think\Model;
class AdvdesignerModel extends Model{
    protected $tableName = "adv_designer";

    /**
     * 获取推荐设计师列表
     * @param  [type] $cs [城市ID]
     * @return [type]     [description]
     */
    public function getDesignerList($cs,$limit){
        $map = array(
            "status"=>array("EQ",1),
            "city_id"=>array("EQ",$cs)
                     );
        $buildSql = M("adv_designer")->where($map)->order("sort,id desc")->limit($limit)->buildSql();
        return M("adv_designer")->table($buildSql)->alias("t")
                                ->join("INNER JOIN qz_user as b on b.id = t.uid")
                                ->join("LEFT JOIN qz_team as c on c.userid = t.uid AND c.zt = '2' ")
                                ->join("INNER JOIN qz_quyu as d on d.cid = b.cs")
                                ->join("INNER JOIN qz_user as e on e.id = t.company_id")
                                ->field("t.*,c.zw,b.logo,d.bm,e.jc")
                                ->select();
    }

    /**
     * 获取总站设计师列表
     * @param  [type] $cs [城市ID]
     * @return [type]     [description]
     */
    public function getHomeDesignerList($limit){
        $map = array(
            "status"=>array("EQ",1),
            "city_id" => '000001'
        );

        $buildSql = M("adv_designer")->where($map)->order("sort,id desc")->limit("0,".$limit)->buildSql();
        return M("adv_designer")->table($buildSql)->alias("t")
                                ->join("INNER JOIN qz_user as b on b.id = t.uid")
                                ->join("LEFT JOIN qz_team as c on c.userid = t.uid AND c.zt = '2' ")
                                ->join("INNER JOIN qz_quyu as d on d.cid = b.cs")
                                ->join("INNER JOIN qz_user as e on e.id = t.company_id")
                                ->field("t.*,c.zw,b.logo,d.bm,e.jc")
                                ->select();
    }
}