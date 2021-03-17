<?php
/**
 *  家居美图
 */
namespace Common\Model;
use Think\Model;

class PubmeituModel extends Model{

    /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMeituInfo($id,$params=0){
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
                              ->join("INNER JOIN qz_pubmeitu_img as b on b.caseid = t1.id")
                              ->field("t1.*,b.img_path,b.img_host,b.description as imgdescription")
                              ->order("b.img_on desc ,b.px")
                              ->select();
    }

    //获取相关美图
    public function getRelatedMeitu($map,$id){
        $map['id'] = array('neq',$id);
        $map["visible"] = array("EQ",0);

        $result = M("pubmeitu")->field('id,title')->where($map)->limit('0,9')->order("time desc")->select();
        $num = count($result);
        $ids = [];
        if($num == 0){
            $ids[] = $id;
        }
        if($num < 9 ){
            $s = 9 - $num;
            foreach ($result as $key => $value) {
                $ids[] = $value["id"];
            }
            $subMaps["id"] = array("NOT IN",$ids);
            $subMaps["is_single"] = $map['is_single'];
            $subMaps["visible"] = array("EQ",0);
            $otherResult = M("pubmeitu")->field('id,title')->where($subMaps)->limit('0,'.$s)->order("time desc")->select();
            $result = array_merge($result,$otherResult);
        }
        foreach ($result as $key => $value) {
            $submap['caseid'] = $value['id'];
            $imgList = M("pubmeitu_img")->field('id,img_path,img_host')->where($submap)->order("px desc")->select();
            $result[$key]['img_path'] = $imgList['0']['img_path'];
            $result[$key]['img_host'] = $imgList['0']['img_host'];
            $result[$key]['count'] = count($imgList);
            unset($imgList);
        }
        return $result;
    }

        //查询单个图片前后图集
    public function getSingleCases($id,$type,$num = 9,$params='',$uid=''){
        if(!empty($params)){
            if($params['location'] > 0){
                $map[] = array("find_in_set(".$params['location'].",location)");
            }
            if($params['fengge'] > 0){
                $map[] = array("find_in_set(".$params['fengge'].",fengge)");
            }
            if($params['huxing'] > 0){
                $map[] = array("find_in_set(".$params['huxing'].",huxing)");
            }
            if($params['color'] > 0){
                $map[] = array("find_in_set(".$params['color'].",color)");
            }
        }

        $map['m.is_single'] = array("EQ",'1');
        $map["m.visible"] = array("EQ",0);

        if(empty($uid)){
            if($type == 'pre'){
                $map['m.id'] = array("GT",$id);
                $result = M("pubmeitu")->alias("m")
                                    ->field('m.id,m.title,m.time,b.img_path,b.img_host')
                                    ->join("INNER JOIN qz_pubmeitu_img as b on m.id = b.caseid")
                                    ->where($map)
                                    ->order("m.id ASC,b.img_on DESC,b.px  ")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
                //sort($result);
            }else{
                $map['m.id'] = array("LT",$id);
                $result = M("pubmeitu")->alias("m")
                                    ->field('m.id,m.title,m.time,b.img_path,b.img_host')
                                    ->join("left JOIN qz_pubmeitu_img as b on b.caseid = m.id")
                                    ->where($map)
                                    ->order("m.id DESC,b.img_on DESC,b.px")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
            }
        }else{
            //用户收藏表的用户id和收藏的类型
            $uid = intval($uid);
            $classtype = '4';

            if($type == 'pre'){
                $map['m.id'] = array("GT",$id);
                $result = M("pubmeitu")->alias("m")
                                    ->field('m.id,m.title,m.time,b.img_path,b.img_host')
                                    ->join("INNER JOIN qz_pubmeitu_img as b on m.id = b.caseid")
                                    //->join("LEFT JOIN qz_user_collect as c ON c.userid = $uid AND c.classtype = $classtype AND c.classid = b.caseid")
                                    ->where($map)
                                    ->order("m.id ASC,b.img_on DESC,b.px  ")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
                //sort($result);
            }else{
                $map['m.id'] = array("LT",$id);
                $result = M("pubmeitu")->alias("m")
                                    ->field('m.id,m.title,m.time,b.img_path,b.img_host')
                                    ->join("LEFT JOIN qz_pubmeitu_img as b on b.caseid = m.id")
                                    //->join("LEFT JOIN qz_user_collect as c ON c.userid = $uid AND c.classtype = $classtype AND c.classid = b.caseid")
                                    ->where($map)
                                    ->order("m.id DESC,b.img_on DESC,b.px")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
            }
        }
        return $result;
    }

        //取最旧的一篇美图
    public function getFirstMeitu($order = "asc",$params=0){
        if(!empty($params)){
            if($params['location'] > 0){
                $paramsMap[] = array("find_in_set(".$params['location'].",location)");
            }
            if($params['fengge'] > 0){
                $paramsMap[] = array("find_in_set(".$params['fengge'].",fengge)");
            }
            if($params['huxing'] > 0){
                $paramsMap[] = array("find_in_set(".$params['huxing'].",huxing)");
            }
            if($params['color'] > 0){
                $paramsMap[] = array("find_in_set(".$params['color'].",color)");
            }
        }

        $paramsMap["visible"] = array("EQ",0);

        if($order == "desc"){
            M("pubmeitu")->where($paramsMap)->order("id desc");
        }else{
            M("pubmeitu")->where($paramsMap)->order("id");
        }
        $result = M("pubmeitu")->find();
        $map = array(
            "caseid"=>array("EQ",$result['id'])
        );
        $result['child'] = M("pubmeitu_img")->field('id,img_path,img_host')->where($map)->order("px")->select();
        return $result;
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
     * 获取美图列表数量
     * @return [type] [description]
     */
    public function getPubMeiTuListCount($location="",$fengge="",$mianji="",$keyword,$is_single = '99'){
        if(!empty($location)){
            $location = (int)$location;
            $complex[] = "find_in_set($location,location)";
        }

        if(!empty($fengge)){
            $fengge = (int)$fengge;
            $complex[] = "find_in_set($fengge,fengge)";
        }
        if(!empty($mianji)){
            $mianji = (int)$mianji;
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
    public function getPubMeiTuList($pageIndex,$pageCount,$location="",$fengge="",$mianji="",$keyword,$is_single = '99',$order){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($location)){
            $location = (int)$location;
            $complex[] = "find_in_set($location,location)";
        }
        if(!empty($fengge)){
            $fengge = (int)$fengge;
            $complex[] = "find_in_set($fengge,fengge)";
        }

        if(!empty($mianji)){
            $mianji = (int)$mianji;
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

        if(empty($order)){
            $order='id desc';
        }

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
     * [getRecommendMeituByAttr 根据美图属性获取推荐美图]
     * @param  string  $location [位置]
     * @param  string  $fengge   [风格]
     * @param  string  $huxing   [户型]
     * @param  string  $color    [颜色]
     * @param  integer $limit    [数量]
     * @param  string  $order    [排序]
     * @return [type]            [description]
     */
    public function getRecommendMeituByAttr($location="",$fengge="",$mianji="",$limit =10){
        if(!empty($location)){
            $location = (int)$location;
            $complex[] = "find_in_set($location,location)";
        }
        if(!empty($fengge)){
            $fengge = (int)$fengge;
            $complex[] = "find_in_set($fengge,fengge)";
        }

        if(!empty($mianji)){
            $mianji = (int)$mianji;
            $complex[] = "find_in_set($mianji,mianji)";
        }

        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }

        $map["visible"] = array("EQ",0);

        $result = M("pubmeitu")->field('id,title')->where($map)->order($order)->limit($limit)->select();
        return $result;
    }

}