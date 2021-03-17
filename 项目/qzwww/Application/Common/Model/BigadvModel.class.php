<?php
/**
 * 通栏广告 qz_bigadv
 */
namespace Common\Model;
use Think\Model;
class BigadvModel extends Model{
    /**
     * 获取通栏列表
     * @param  [type] $cs       [所在城市]
     * @param  [type] $position [所属位置]
     * @return [type]           [description]
     */
    public function getBigadvList($cs,$position = ""){
        $time = time();
        $map = array(
            "disabled"=>array("EQ",0),
            array(
                  "city_id"=>array("EQ",$cs),
                  "city_ids"=>array("EQ",$cs),
                  "_logic"=>"OR"
                  ),
            array(
                array(
                    "starttime"=>array("ELT",$time),
                    "endtime"=>array("EGT",$time)
                      ),
                array(
                    "starttime"=>array("EXP","IS NULL"),
                    "endtime"=>array("EXP","IS NULL")
                      ),
                array(
                    "starttime"=>array("EXP","IS NULL"),
                    "endtime"=>array("EGT",$time)
                      ),
                array(
                    "starttime"=>array("ELT",$time),
                    "endtime"=>array("EXP","IS NULL")
                      ),
                "_logic"=>"OR"
                )
        );
        if(!empty($position)){
            $map["position"] = $position;
        }

        $buildSql = M("bigadv")->where($map)->order("addtime desc")
                              ->buildSql();
        return M("bigadv")->table($buildSql)->alias("t")
                          ->join("LEFT JOIN qz_user as a on a.id = t.comid")
                          ->join("LEFT JOIN qz_quyu as b on a.cs = b.cid")
                          ->field("t.*,b.bm")
                          ->group("position")
                          ->select();
    }
}