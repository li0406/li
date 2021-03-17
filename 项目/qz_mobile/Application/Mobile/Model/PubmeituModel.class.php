<?php
/**
 *  公装美图
 */
namespace Mobile\Model;
use Think\Model;

class PubmeituModel extends Model{

    protected $autoCheckFields = false;

   /**
    * 获取位置信息
    * @param  boolean $isTop [是否推荐]
    * @return [type]         [description]
    */
    public function getLocation($limit="",$isTop = false){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
            $map["istop"] = array("EQ",1);
        }

        M("meitu_location")->where($map)->order("istop desc,px");
        if(!empty($limit)){
            M("meitu_location")->limit($limit);
        }
        return M("meitu_location")->select();
    }

    /**
    * 获取户型信息
    * @param  boolean $isTop [是否推荐]
    * @return [type]         [description]
    */
    public function getHuxing($limit="",$isTop = false){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
            $map["istop"] = array("EQ",1);
        }
        M("meitu_huxing")->where($map)->order("istop desc,px");
        if(!empty($limit)){
            M("meitu_huxing")->limit($limit);
        }
        return M("meitu_huxing")->select();
    }

    /**
    * 获取风格信息
    * @param  boolean $isTop [是否推荐]
    * @return [type]         [description]
    */
    public function getFengge($limit="",$isTop = false){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
            $map["istop"] = array("EQ",1);
        }
        M("meitu_fengge")->where($map)->order("istop desc,px");
        if(!empty($limit)){
            M("meitu_fengge")->limit($limit);
        }
        return M("meitu_fengge")->select();
    }

    /**
     * 获取美图列表数量
     * @return [type] [description]
     */
    public function getPubMeiTuListCount($location="",$fengge="",$huxing="",$color="",$keyword,$is_single = '99'){
        if(!empty($location)){
            $complex[] = "find_in_set($location,location)";
        }

        if(!empty($fengge)){
            $complex[] = "find_in_set($fengge,fengge)";
        }
        if(!empty($mianji)){
            $complex[] = "find_in_set($mianji,mianji)";
        }

        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }

        if(!empty($keyword)){
            $map["title"] = array("LIKE","%".$keyword."%");
        }

        if('99' != $is_single ){
            $map["is_single"] = array("EQ",$is_single);
        }

        $map["visible"] = array("EQ",0);

        $result =  M("pubmeitu")->where($map)->count();
        return $result;
    }


    /**
     * 获取美图列表
     * @return [type] [description]
     */
    public function getPubMeiTuList($pageIndex,$pageCount,$location="",$fengge="",$mianji="",$keyword,$order,$is_single = '99'){

        if(!empty($location)){
            $complex[] = "find_in_set($location,location)";
        }
        if(!empty($fengge)){
            $complex[] = "find_in_set($fengge,fengge)";
        }

        if(!empty($mianji)){
            $complex[] = "find_in_set($mianji,mianji)";
        }

        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }

        if(empty($order)){
            $order='id desc';
        }

        if('99' != $is_single ){
            $map["is_single"] = array("EQ",$is_single);
        }

        $map["visible"] = array("EQ",0);

        //1.查询美图的基本信息
        $buildSql = M("pubmeitu")->where($map)->order($order)
                              ->limit($pageIndex.",".$pageCount)
                              ->buildSql();

        //2.查询美图的其他信息
        $buildSql = M("pubmeitu")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_pubmeitu_att as b on find_in_set(b.id,a.location)")
                         ->field("a.*,GROUP_CONCAT(b.name) as wz")
                         ->group("a.id")
                         ->buildSql();

        $buildSql = M("pubmeitu")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_pubmeitu_att as c on find_in_set(c.id,a.fengge) ")
                         ->field("a.*,GROUP_CONCAT(c.name) as fg")
                         ->group("a.id")
                         ->buildSql();

        $buildSql = M("pubmeitu")->table($buildSql)->alias("a")
                              ->join("INNER JOIN qz_pubmeitu_att as d on find_in_set(d.id,a.mianji) ")
                              ->field("a.*,GROUP_CONCAT(d.name) as mj")
                              ->group("a.id")
                              ->buildSql();
        $buildSql = M("pubmeitu")->table($buildSql)->alias("a")->order("a.id desc")->buildSql();

        //3.获取美图图片信息
        $buildSql = M("pubmeitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_pubmeitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        $buildSql = M("pubmeitu")->table($buildSql)->alias("t1")
                             ->group("t1.id")
                             ->order("img_on desc,px")
                             ->buildSql();
        $result = M("pubmeitu")->table($buildSql)->alias("t1")->order("t1.id desc")->select();
        return $result;
    }

    /**
     * 根据条件获取单个公装美图
     * @param  array  $map    条件
     * @param  array  $params 额外参数
     * @param  string $order  排序
     * @return
     */
    public function getPubMeituInfoByMap($map = array(), $params = array(), $order = 'asc'){
        //可见
        $map["a.visible"] = array("EQ",0);
        //参数设置
        if(!empty($params)){
            if($params['location'] > 0){
                $map[] = array("find_in_set(".$params['location'].",a.location)");
            }
            if($params['fengge'] > 0){
                $map[] = array("find_in_set(".$params['fengge'].",a.fengge)");
            }
            if($params['mianji'] > 0){
                $map[] = array("find_in_set(".$params['mianji'].",a.mianji)");
            }
        }
        //排序
        if ('asc' == $order) {
            $order = 'a.id asc';
        } else {
            $order = 'a.id desc';
        }
        //查询美图信息
        $buildSql = M("pubmeitu")->alias("a")->where($map)->order($order)->limit(1)->buildSql();
        //查询美图的图片信息
        return M("pubmeitu")->table($buildSql)
                            ->alias("t1")
                            ->join("INNER JOIN qz_pubmeitu_img as b on b.caseid = t1.id")
                            ->field("t1.*,b.img_path,b.img_host,b.description as imgdescription")
                            ->order("b.img_on desc ,b.px")
                            ->select();
    }

    /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getPubMeituInfo($id,$params=0){
        $map = array(
            "a.id"=>array("EQ",$id),
        );

        //上一图集
        $prvMap = array(
            "d.id"=>array("LT",$id),
            "_string"=>"d.type = @class"
        );

        //下一图集
        $nextMap = array(
            "a.id"=>array("GT",$id),
            "_string"=>"a.type = @class"
        );

        if(!empty($params)){
            if($params['location'] > 0){
                $prvMap[] = array("find_in_set(".$params['location'].",location)");
                $nextMap[] = array("find_in_set(".$params['location'].",location)");
            }
            if($params['fengge'] > 0){
                $prvMap[] = array("find_in_set(".$params['fengge'].",fengge)");
                $nextMap[] = array("find_in_set(".$params['fengge'].",fengge)");
            }
            if($params['huxing'] > 0){
                $prvMap[] = array("find_in_set(".$params['huxing'].",huxing)");
                $nextMap[] = array("find_in_set(".$params['huxing'].",huxing)");
            }
            if($params['color'] > 0){
                $prvMap[] = array("find_in_set(".$params['color'].",color)");
                $nextMap[] = array("find_in_set(".$params['color'].",color)");
            }
        }

        $map["visible"] = array("EQ",0);
        $prvMap["visible"] = array("EQ",0);
        $nextMap["visible"] = array("EQ",0);

        //1.查询美图信息
        $buildSql = M("pubmeitu")->where($map)->alias("a")
                              ->field("a.*,@class:= a.type as class,'now' as action")
                              ->buildSql();
        //2.上一图集
        $preSql =  M("pubmeitu")->where($prvMap)->alias("d")
                             ->field("d.*,d.type as class,'prv' as action")
                             ->order("d.id desc")
                             ->limit(1)
                             ->buildSql();
        // dump($preSql);
        // die;

        //3.下一图集
        $nextSql =  M("pubmeitu")->where($nextMap)->alias("a")
                              ->field("a.*,a.type as class,'next' as action")
                              ->limit(1)
                              ->buildSql();
        //4.合并SQL
        $buildSql = M("pubmeitu")->table($buildSql)->alias("t")
                              ->union($preSql,true)
                              ->union($nextSql,true)
                              ->buildSql();

        //查询美图的图片信息
        return M("pubmeitu")->table($buildSql)->alias("t1")
                              ->join("LEFT JOIN qz_pubmeitu_img as b on b.caseid = t1.id")
                              ->field("t1.*,b.img_path,b.img_host,b.description as imgdescription")
                              ->order("b.img_on desc ,b.px")
                              ->select();
    }


    /**
     * [getPubMeituAttr 获取公装美图属性信息]
     * @param  string  $type  [属性类型]
     * @param  string  $limit [取几条]
     * @param  boolean $istop [是否置顶]
     * @return [type]         [description]
     */
    public function getPubMeituAttr($type='',$limit="",$istop = false){
        $map['enabled'] = array('EQ',1);
        if(!empty($type)){
            $map['type'] = array('EQ',$type);
        }
        if($istop){
            $map["istop"] = array("EQ",1);
        }
        if(!empty($limit)){
            $result = M("pubmeitu_att")->where($map)->order("istop desc,px")->limit($limit)->select();
        }else{
            $result = M("pubmeitu_att")->where($map)->order("istop desc,px")->select();
        }
        return $result;
    }

    /**
     * 获取落地页美图列表
     * @return [type] [description]
     */
    public function getLandPubMeiTuList($is_single = '1',$limit,$orders = 0){
        if(empty($order)){
            $order='id desc';
        }

        $map["is_single"] = array("EQ",$is_single);

        $map["visible"] = array("EQ",0);

        //1.查询美图的基本信息
        $buildSql = M("pubmeitu")->where($map)->order($order)
            ->buildSql();

        //2.查询美图的其他信息
//        $buildSql = M("pubmeitu")->table($buildSql)->alias("a")->order("a.id desc")->buildSql();
        if($orders == 0){
            $order = "t1.id desc";
        }else{
            $order = "t1.updatetime desc";
        }
        //3.获取美图图片信息
        $buildSql = M("pubmeitu")->table($buildSql)->alias("t")
            ->join("INNER JOIN qz_pubmeitu_img as f on f.caseid = t.id")
            ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
            ->buildSql();
        $buildSql = M("pubmeitu")->table($buildSql)->alias("t1")
            ->group("t1.id")
            ->order("img_on desc,px")
            ->buildSql();
        $result = M("pubmeitu")->table($buildSql)->alias("t1")->order($order)->limit($limit)->select();
        return $result;
    }


}