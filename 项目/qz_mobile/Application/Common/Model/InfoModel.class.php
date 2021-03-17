<?php
/**
 *  公司资讯文章表 qz_info
 */
namespace Common\Model;
use Think\Model;
class InfoModel extends Model{
    /**
     * 根据公司编号查询文章信息
     * @param  string $comid [description]
     * @param  string $notClass [不包含的类型]
     * @return [type]        [description]
     */
    public function getArticlesByComId($comid ='',$cs = '',$pageIndex = 1,$pageCount = 10,$type ='',$active = '',$notClass = "",$on = true)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "a.uid"=>array("EQ",$comid),
                "a.cs" =>array("EQ",$cs)
                    );
        if(!empty($type) && !empty($notClass)){
            $map["a.type"] = array(array("EQ",$type),array("NOT IN",$notClass));
        }else if(!empty($type)){
            $map["a.type"] = array("EQ",$type);
        }else if(!empty($notClass)){
            $map["a.type"] = array("NOT IN",$notClass);
        }

        if(!$on){
            $map["a.on"] = array("NEQ",0);
        }


        if(!empty($active) && !empty($type) && $type == 1){
            $time = time();
            if($active == 1){
                $where =array(
                            array(
                                array(
                                    "a.start" => array(array("EQ",0),array("EQ",""),array("EXP","IS NULL")),
                                    "a.end"=>array("GT",$time)
                                     ),
                                array(
                                    "a.start"=>array("ELT",$time),
                                    "a.end"=>array("GT",$time)
                                      ),
                                "_logic"=>"OR"
                            )
                        );
                $map["_complex"] = $where;
            }else{
                $where = array(
                            "a.end"=>array(array("LT",$time),array("NEQ",0),array("NEQ",""),array("EXP","IS NOT NULL")),
                          array(
                            "a.start"=>array(array("EQ",0),array("EQ",""),array("EXP","IS NULL"),"OR"),
                            "a.end"=>array(array("EQ",0),array("EQ",""),array("EXP","IS NULL"),"OR")
                                ),
                          "_logic"=>"OR"
                            );
                $map["_complex"] = $where;
            }
        }

        $buildSql = M("info")->where($map)->alias("a")
                             ->order("a.id desc,ckinfo desc")
                             ->limit($pageIndex.",".$pageCount)
                             ->field("a.id,a.title,a.on,a.subtitle,a.time,a.start,a.end,a.type")
                             ->buildSql();
        return M("info")->table($buildSql)->alias("a")
                        ->join("INNER JOIN qz_infotype as b on b.id = a.type")
                        ->field("a.*,b.name as bname")
                        ->select();
    }

    /**
     * 根据公司编号查询文章信息数
     * @param  string $comid  [description]
     * @param  string $cs     [description]
     * @param  string $type   [description]
     * @param  string $active [description]
     * @param  string $notClass [不再范围内的文章类别] 类型: 数组
     * @param  string $on     [审核状态]
     * @return [type]         [description]
     */
    public function getArticlesByComIdCount($comid ='',$cs = '',$type ='',$active = '',$notClass ="",$on = true){
        $map = array(
                "a.uid"=>array("EQ",$comid),
                "a.cs" =>array("EQ",$cs)
                    );

        if(!$on){
            $map["a.on"] = array("NEQ",0);
        }

        if(!empty($type) && !empty($notClass)){
            $map["a.type"] = array(array("EQ",$type),array("NOT IN",$notClass));
        }else if(!empty($type)){
            $map["a.type"] = array("EQ",$type);
        }else if(!empty($notClass)){
            $map["a.type"] = array("NOT IN",$notClass);
        }

        if(!empty($active) && !empty($type) && $type == 1){
            $time = time();
            if($active == 1){
                $where =array(
                            array(
                                array(
                                    "a.start" => array(array("EQ",0),array("EQ",""),array("EXP","IS NULL"),"OR"),
                                    "a.end"=>array("GT",$time)
                                     ),
                                array(
                                    "a.start"=>array("ELT",$time),
                                    "a.end"=>array("GT",$time)
                                      ),
                                "_logic"=>"OR"
                            )
                        );
                $map["_complex"] = $where;
            }else{
                $where = array(
                            "a.end"=>array(array("LT",$time),array("NEQ",0),array("NEQ",""),array("EXP","IS NOT NULL"),"OR"),
                              array(
                                "a.start"=>array(array("EQ",0),array("EQ",""),array("EXP","IS NULL"),"OR"),
                                "a.end"=>array(array("EQ",0),array("EQ",""),array("EXP","IS NULL"),"OR")
                                    ),
                              "_logic"=>"OR"
                            );
                $map["_complex"] = $where;
            }
        }

        return M("info")->where($map)->alias("a")
                             ->order("a.id desc,ckinfo desc")
                             ->limit($pageIndex.",".$pageCount)
                             ->count();
    }

    /**
     * 获取装修公司资讯的分类信息
     * @return [type] [description]
     */
    public function getZixunTypeList($comid='',$cs =''){
        $map = array(
                "b.uid"=>array("EQ",$comid),
                "b.cs" =>array("EQ",$cs)
                    );
        $time = time();
        $buildSql = M("infotype")->order("px")
                               ->buildSql();
        return M("info")->table($buildSql)->alias("a")
                        ->where($map)
                        ->join("INNER JOIN qz_info as b on b.type = a.id")
                        ->group("b.type")
                        ->field("a.name,a.px,a.id,count(b.type) as count,
count(IF((
((b.`start` = 0 or b.`start` is null or b.`start` = '') and b.end >=$time)
OR (b.`start` <= $time and b.end >=$time)
OR (b.`start` <= $time and (b.end = 0 or b.end is null or b.end = '' ) and b.`start` <> 0 )
OR ((b.`start` = 0 or b.`start` is null or b.`start` = '') and b.end >= $time))   and a.id = 1 ,b.id,null)) as yxcount,
count(IF((b.end < $time and b.end <> 0 and b.`end` is not null and b.`end`<> '' OR ((b.`start` = 0 or b.`start` is null or b.`start` = '') and (b.end = 0 or b.end is null or b.end = '' ))) and a.id = 1,b.id,null)) as wxcount")
                        ->order("px")
                        ->select();
    }

    /**
     * 获取装修公司资讯详细信息
     * @param  string $cid [公司编号]
     * @param  string $id  [文章编号]
     * @param  string $cs  [所在城市]
     * @return [type]      [description]
     */
    public function getArticleInfo($cid = '',$id ='',$cs =''){
        $map = array(
                "a.uid"=>array("EQ",$cid),
                "a.cs" =>array("EQ",$cs),
                "a.id"=>array("EQ",$id)
                    );
        return  M("info")->where($map)->alias("a")
                         ->find();

    }

    /**
     * 根据公司文章id查询公司信息
     * @param  string $cid [公司编号]
     * @param  string $id  [文章编号]
     * @param  string $cs  [所在城市]
     * @return [type]      [description]
     */
    public function getCompanyInfoByArticleId($id =''){
        if (empty($id)) {
            return false;
        }
        $map = array(
                'a.id'=>array('EQ',$id)
                    );
        $articleInfo = M("info")->field('id,uid')->where($map)->alias("a")->find();
        if (!$articleInfo) {
            return false;
        }
        $mapu = array (
                    'id' => array('EQ',$articleInfo['uid'])
                       );
        return M('user')->field('id,`on`,qc,jc')->where($mapu)->find();
    }

    /**
     * 根据编号获取单个装修公司文章信息
     * @return [type] [description]
     */
    public function getSingleInfoById($id){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
      return M("info")->where($map)->alias("a")
                      ->join("inner join qz_quyu as b on a.cs = b.cid")
                      ->field("a.*,b.bm")
                      ->find();
    }

    /**
     * 根据文章类型查询文章信息
     * @param  integer $type [文章类型]
     * @param  string  $cs   [所在城市]
     * @return [type]        [description]
     */
    public function getInfosListByType($type=1,$cs="",$limit){
        $buildMap = array(
                "type"=>array("EQ",$type),
                "on"=>array("EQ",1)
                     );
        if(!empty($cs)){
           $map["b.cs"]=array("EQ",$cs);
            $map["b.on"]=array("EQ",2);
        }

        //1.取出符合类型的文章
        $buildSql = M("info")->where($buildMap)
                             ->field("id,title,uid,time")
                             ->order("id desc")
                             ->buildSql();
        $buildSql = M("info")->table($buildSql)->alias("t")
                            ->group("uid")
                            ->buildSql();
        //2.取出装修公司的信息
        return M("info")->table($buildSql)->alias("t")->where($map)
                        ->join("INNER JOIN qz_user as b on b.id = t.uid")
                        ->order("t.id desc")
                        ->field("t.*,b.jc")
                        ->limit($limit)
                        ->select();
    }

    /**
     * 添加文章
     */
    public function addInfo($data){
        M("info")->add($data);
        return M("info")->getLastInsID();
    }

    /**
     * 编辑文章
     * @param  [type] $id    [文章编号]
     * @param  [type] $comid [公司编号]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function editInfo($id,$comid,$data){
        $map = array(
                "id"=>array("EQ",$id),
                "uid"=>array("EQ",$comid)
                     );
        return M("info")->where($map)->save($data);
    }

    /**
     * 删除文章
     * @param  [type] $id    [文章编号]
     * @param  [type] $comid [公司编号]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function delInfo($id,$comid){
        $map = array(
                "id"=>array("EQ",$id),
                "uid"=>array("EQ",$comid)
                     );
        return M("info")->where($map)->delete();
    }

    /**
     * 根据编号查询文章信息
     * @param  [type] $id    [description]
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getInfoById($id,$comid){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.uid"=>array("EQ",$comid)
                     );
        return M("info")->where($map)->alias("a")
                        ->field("a.*")
                        ->find();
    }

    /**
     * 获取最新的优惠活动
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getNewActivityInfoByComid($comid){
        $time = time();
        $map =array(
                array(
                    array(
                        "start" => array(array("EQ",0),array("EQ",""),array("EXP","IS NULL"),"OR"),
                        "end"=>array("GT",$time)
                         ),
                    array(
                        "start"=>array("ELT",$time),
                        "end"=>array("GT",$time)
                          ),
                    "_logic"=>"OR"
                ),
                "uid"=>array("EQ",$comid),
                "type"=>array("EQ",1),
                "on"=>array("EQ",1)
            );
        $buildSql = M("info")->where($map)->order("id desc")
                             ->buildSql();
        return M("info")->table($buildSql)->alias("t")->limit(1)
                        ->find();

    }
}