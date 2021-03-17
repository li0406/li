<?php
/**
 * Logo广告 advs表
 */
namespace Common\Model;
use Think\Model;
class AdvsModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 获取LOGO广告列表
     * @param  integer $limit [显示数量]
     * @param  string  $cs    [所在城市]
     * @param  string  $ids    [不包括的ID]
     * @return [type]         [description]
     */
    public function getLogoList($limit = 10,$cs ='',$position = 1,$ids){
        if(!empty($cs)){
            $map["city_id"] = array("EQ",$cs);
        }

        if(count($ids) > 0){
           $map["id"] = array("NOT IN",$ids);
        }
        $date = date("Y-m-d");
        $map['stat']=1;//必须是已经在使用的广告
        $map["position"] = $position;
        $where = array(
                array(
                    "time"=>array(array("ELT",$date),array("NEQ","0000-00-00 00:00:00")),
                    "end_time"=>array("EGT",$date)
                      ),
                array(
                    "time"=>array(array("ELT",$date),array("NEQ","0000-00-00 00:00:00")),
                    "end_time"=>array("EXP","IS NULL")
                      ),
                "_logic"=>"OR"
                );
        $map["_complex"] = $where;
        return M("advs")->where($map)->order("orders desc")
                        ->limit($limit)
                        ->select();
    }

   /**
    * 获取移动版的公司LOGO
    * @param  [type] $cityId [城市编号]
    * @param  [type] $limit [偏移量]
    * @param  [type] $isAll [是否取全部]
    * @return [type]         [description]
    */
    public function getMobileLogoList($cityId,$limit,$isAll = false){
        $begin = mktime(0,0,0,date("m"),1,date("Y"));
        $end = mktime(11,59,59,date("m"),date("d"),date("Y"));
        $map = array(
              "cs"=>array("EQ",$cityId),
              "time"=>array(
                        array("EGT",$begin),
                        array("ELT",$end)
                            )
                     );
        if($isAll){
           $map = array(
                  "cs"=>array("EQ",$cityId)
                        );
        }
        //1.查询当月的上传案例最多的会员装修公司
        $buildSql = M("cases")->where($map)
                              ->group("uid")
                              ->field("uid,count(id) as casecount")
                              ->order("casecount desc")
                              ->buildSql();
        //2.查询出相关的装修公司信息
        return  M("cases")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_user as a on a.id = t.uid AND a.on = 2")
                              ->field("a.id,a.jc,a.cs,a.logo")
                              ->order("t.casecount desc")
                              ->limit($limit)
                              ->select();
    }

    /**
     * 修改LOGO广告
     * @param  [type] $comid [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function editAdvs($comid,$data){
        $map = array(
                "company_id"=>$comid
                     );
        return M("advs")->where($map)->save($data);
    }
}