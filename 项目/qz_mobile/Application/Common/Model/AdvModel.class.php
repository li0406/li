<?php
/**
 *  轮播广告图片
 */
namespace Common\Model;
use Think\Model;
class AdvModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    /**
     * 获取首页轮播图片
     * @return [type] [description]
     */
    public function getIndexAdv($city = ''){
        $date = date("Y-m-d");
        $map = array(
                "is_delete"=>array("EQ",0),
                array(
                    array(
                        "a.start" => array("ELT",$date),
                        "a.end"   =>array("EGT",$date),
                        "_logic"=>"and"
                         ),
                    array(
                        "a.start" =>array(array("EXP","IS NULL"),array("EQ","","or")),
                        "a.end"   =>array("EGT",$date),
                         ),
                    array(
                        "a.start" => array("ELT",$date),
                        "a.end"   => array(array("EXP","IS NULL"),array("EQ","","or")),
                         ),
                    array(
                        "a.start" => array(array("EXP","IS NULL"),array("EQ","","or")),
                        "a.end"   => array(array("EXP","IS NULL"),array("EQ","","or")),
                         ),
                    array(
                        "a.comid" =>array("EQ",0)
                          ),
                    "_logic"=>"OR"
                    )
                );

        $map["_complex"] = $where1;
        if($city == "000001"){
            //如果是总站,排除全站轮播
            $where["city_id"] =array("EQ",$city);
        }else{
            $where["city_id"] =array(array("EQ",$city),array("EQ",0),"OR");
        }

        $where["_logic"]  ="OR";
        $map["_complex"] = $where;

        return M("adv")->where($map)->alias("a")
                       ->join("LEFT JOIN qz_user as b on a.comid = b.id")
                       ->join("LEFT JOIN qz_quyu as c on c.cid = b.cs")
                       ->field("a.*,c.bm")
                       ->order("orders desc,end ")
                       ->select();
    }
}