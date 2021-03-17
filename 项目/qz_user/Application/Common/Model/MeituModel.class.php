<?php
/**
 *  家居美图
 */
namespace Common\Model;
use Think\Model;
class MeituModel extends Model{

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
        if($order == "desc"){
            M("meitu")->where($paramsMap)->order("id desc");
        }else{
            M("meitu")->where($paramsMap)->order("id");
        }
        $result = M("meitu")->find();
        $map = array(
            "caseid"=>array("EQ",$result['id'])
        );
        $result['child'] = M("meitu_img")->field('id,img_path,img_host')->where($map)->order("px")->select();
        return $result;
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
    * 获取颜色信息
    * @param  boolean $isTop [是否推荐]
    * @return [type]         [description]
    */
    public function getColor($limit="",$isTop = false){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
             $map["istop"] = array("EQ",1);
        }
        M("meitu_color")->where($map)->order("istop desc,px");
        if(!empty($limit)){
            M("meitu_color")->limit($limit);
        }
        return M("meitu_color")->select();
    }

    /**
     * 获取轮播信息
     * @return [type] [description]
     */
    public function getLunbo(){
        $map = array(
                "enabled"=>array("EQ",1)
                     );
        return M("meitu_lunbo")->where($map)->order("px desc")->select();
    }

    /**
     * 获取推荐热图
     * @return [type] [description]
     */
    public function getHotMeitu($limit){
        $map = array(
                "istop"=>array("EQ",1),
                "is_single"=>array("EQ",0)
                     );
        //1.获取美图信息
        $buildSql = M("meitu")->where($map)->order("id desc")->limit($limit)->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("a")
                              ->join("INNER JOIN qz_meitu_location as b on find_in_set(b.id,a.location)")
                              ->join("INNER JOIN qz_meitu_fengge as c on find_in_set(c.id,a.fengge) ")
                              ->join("INNER JOIN qz_meitu_huxing as d on find_in_set(d.id,a.huxing) ")
                              ->join("INNER JOIN qz_meitu_color as e on find_in_set(e.id,a.color) ")
                              ->field("a.*,GROUP_CONCAT(b.name) as wz,GROUP_CONCAT(c.name) as fg,GROUP_CONCAT(d.name) as hx,GROUP_CONCAT(e.name) as ys")
                              ->group("a.id")
                              ->buildSql();
        //2.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->order("px")
                              ->buildSql();

        $buildSql = M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")
                         //->order("img_on desc,px,t1.id desc")
                         ->buildSql();
        return M("meitu")->table($buildSql)->alias("t2")->group("t2.id")->order("id desc")->select();
    }

    /**
     * 获取点击率最高的图片信息
     * @return [type] [description]
     */
    public function getRankMeitu($limit){
        //1.获取美图信息
        $buildSql = M("meitu")->alias("a")
                         ->field("a.*")
                         ->order("pv desc,id desc")->limit($limit)
                         ->buildSql();

        //2.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        return M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")
                         ->order("img_on desc,px")
                         ->select();
    }

        /**
     * 获取点击率最高的图片信息
     * @return [type] [description]
     */
    public function getNewMeitu($limit){
        //1.获取美图信息
        $buildSql = M("meitu")->alias("a")
                         ->field("a.*")
                         ->where("is_single = '0' ")
                         ->order("time desc")
                         ->limit($limit)
                         ->buildSql();

        //2.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        return M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")
                         ->order("img_on desc,px")
                         ->select();
    }

    /**
     *
     * @return [type] [description]
     */
    public function getMeituListByPart($type,$limit){
        switch ($type) {
            case 'location':
                    $map["_complex"] = array(
                                    array("find_in_set(4,location)"),
                                    array("find_in_set(5,location)"),
                                    array("find_in_set(8,location)"),
                                    "_logic"=>"OR"
                                    );
                break;
            case "fengge":
                    $map["_complex"] = array(
                                    array("find_in_set(5,fengge)"),
                                    array("find_in_set(8,fengge)"),
                                    array("find_in_set(9,fengge)"),
                                    array("find_in_set(12,fengge)"),
                                    array("find_in_set(14,fengge)"),
                                      "_logic"=>"OR"
                                    );
                break;
            case 'cl':
                    $map["_complex"] = array(
                                    array("find_in_set(26,fengge)"),
                                    array("find_in_set(27,fengge)"),
                                    array("find_in_set(28,fengge)"),
                                     "_logic"=>"OR"
                                    );
                break;
            default:
                return null;
                break;
        }

        $map['is_single'] = array('EQ',0);

        //1.获取美图信息
        $buildSql = M("meitu")->where($map)->order("id desc")->limit($limit)->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_meitu_location as b on find_in_set(b.id,a.location)")
                         ->join("INNER JOIN qz_meitu_fengge as c on find_in_set(c.id,a.fengge) ")
                         ->join("INNER JOIN qz_meitu_huxing as d on find_in_set(d.id,a.huxing) ")
                         ->join("INNER JOIN qz_meitu_color as e on find_in_set(e.id,a.color) ")
                         ->field("a.*,GROUP_CONCAT(b.name) as wz,GROUP_CONCAT(c.name) as fg,GROUP_CONCAT(d.name) as hx,GROUP_CONCAT(e.name) as ys")
                         ->group("a.id")
                         ->order("a.id desc")
                         ->buildSql();
        //2.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        return M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")
                         ->order("img_on desc,px")
                         ->select();
    }

    /**
     * 获取大师设计作品
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getDesignerImg($limit){
        $map = array(
                "enabled"=>array("EQ",1)
                     );
        //1.获取推荐的设计师
        $buildSql = M("meitu_designer")->where($map)->order("px desc")->limit($limit)->buildSql();

        //2.获取设计师的信息
         $buildSql = M("meitu_designer")->table($buildSql)->alias("t4")
                                        ->join("inner join qz_user as d on d.id = t4.uid")
                                        ->field("t4.*,d.logo")
                                        ->buildSql();
        //2.获取设计师的作品
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t")
                                       ->join("inner join qz_meitu as b on b.master = t.uid")
                                       ->order("b.id desc")
                                       ->field("t.name,t.logo,t.shortname,b.*")
                                       ->buildSql();
        //3.获取作品
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t1")->group("t1.id")
                                       ->buildSql();

        //4.获取图片
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t2")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t2.id")
                              ->field("t2.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();

        return M("meitu_designer")->table($buildSql)->alias("t3")
                         ->group("t3.master")
                         ->order("img_on desc,px")
                         ->select();
    }

    /**
     * 获取美图列表数量
     * @return [type] [description]
     */
    public function getMeiTuListCount($location="",$fengge="",$huxing="",$color="",$keyword){
        if(!empty($location)){
            $complex[] = "find_in_set($location,location)";
        }

        if(!empty($fengge)){
            $complex[] = "find_in_set($fengge,fengge)";
        }
        if(!empty($huxing)){
            $complex[] = "find_in_set($huxing,huxing)";
        }

        if(!empty($color)){
             $complex[] = "find_in_set($color,color)";
        }

        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }

        if(!empty($keyword)){
            $map["title"] = array("LIKE","%".$keyword."%");
        }

        $result =  M("meitu")->where($map)->count();
        return $result;
    }

    /**
     * 获取美图列表
     * @return [type] [description]
     */
    public function getMeiTuList($pageIndex,$pageCount,$location="",$fengge="",$huxing="",$color="",$keyword)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($location)){
            $complex[] = "find_in_set($location,location)";
        }
        if(!empty($fengge)){
            $complex[] = "find_in_set($fengge,fengge)";
        }

        if(!empty($huxing)){
            $complex[] = "find_in_set($huxing,huxing)";
        }

        if(!empty($color)){
             $complex[] = "find_in_set($color,color)";
        }
        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }

        if(!empty($keyword)){
            $map["title"] = array("LIKE","%".$keyword."%");
        }
        //1.查询美图的基本信息
        $buildSql = M("meitu")->where($map)->order("id desc")
                              ->limit($pageIndex.",".$pageCount)
                              ->buildSql();

        //2.查询美图的其他信息
        $buildSql = M("meitu")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_meitu_location as b on find_in_set(b.id,a.location)")
                         ->field("a.*,GROUP_CONCAT(b.name) as wz")
                         ->group("a.id")
                         ->buildSql();

        $buildSql = M("meitu")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_meitu_fengge as c on find_in_set(c.id,a.fengge) ")
                         ->field("a.*,GROUP_CONCAT(c.name) as fg")
                         ->group("a.id")
                         ->buildSql();

        $buildSql = M("meitu")->table($buildSql)->alias("a")
                              ->join("INNER JOIN qz_meitu_huxing as d on find_in_set(d.id,a.huxing) ")
                              ->field("a.*,GROUP_CONCAT(d.name) as hx")
                              ->group("a.id")
                              ->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("a")
                              ->join("INNER JOIN qz_meitu_color as e on find_in_set(e.id,a.color) ")
                              ->field("a.*,GROUP_CONCAT(e.name) as ys")
                              ->group("a.id")
                              ->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("a")->order("a.id desc")->buildSql();
 //
        //3.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("t1")
                             ->group("t1.id")
                             ->order("img_on desc,px")
                             ->buildSql();
        $result = M("meitu")->table($buildSql)->alias("t1")->order("t1.id desc")->select();
        return $result;
    }

    /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMeituInfo($id,$params=0){
        $map = array(
            "a.id"=>array("EQ",$id),
        );
        // dump($params);
        // dump($map);

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

        // dump($prvMap);
        // dump($nextMap);

        //1.查询美图信息
        $buildSql = M("meitu")->where($map)->alias("a")
                              ->field("a.*,@class:= a.type as class,'now' as action")
                              ->buildSql();
        //2.上一图集
        $preSql =  M("meitu")->where($prvMap)->alias("d")
                             ->field("d.*,d.type as class,'prv' as action")
                             ->order("d.id desc")
                             ->limit(1)
                             ->buildSql();
        // dump($preSql);
        // die;

        //3.下一图集
        $nextSql =  M("meitu")->where($nextMap)->alias("a")
                              ->field("a.*,a.type as class,'next' as action")
                              ->limit(1)
                              ->buildSql();
        //4.合并SQL
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->union($preSql,true)
                              ->union($nextSql,true)
                              ->buildSql();

        //2.获取美图大师信息
        $buildSql = M("meitu")->table($buildSql)->alias("t2")
                              ->join("LEFT JOIN qz_meitu_designer as u on u.uid = t2.master")
                              ->field("t2.*,u.name as username,u.shortname")
                              ->buildSql();

        //查询美图的图片信息
        return M("meitu")->table($buildSql)->alias("t1")
                              ->join("INNER JOIN qz_meitu_img as b on b.caseid = t1.id")
                              ->field("t1.*,b.img_path,b.img_host,b.description as imgdescription")
                              ->order("b.img_on desc ,b.px")
                              ->select();
    }

    //查询单个图片前后图集
    public function getSingleCases($id,$type,$num = 9,$params=''){
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
        if($type == 'pre'){
            $map['m.id'] = array("GT",$id);
            $result = M("meitu")->alias("m")
                                ->field('m.id,m.title,m.time,b.img_path,b.img_host')
                                ->join("INNER JOIN qz_meitu_img as b on m.id = b.caseid")
                                ->where($map)
                                ->order("m.id ASC,b.img_on DESC,b.px  ")
                                ->limit('0,'.$num)
                                ->group('m.id')
                                ->select();
            //sort($result);
        }else{
            $map['m.id'] = array("LT",$id);
            $result = M("meitu")->alias("m")
                                ->field('m.id,m.title,m.time,b.img_path,b.img_host')
                                ->join("left JOIN qz_meitu_img as b on b.caseid = m.id")
                                ->where($map)
                                ->order("m.id DESC,b.img_on DESC,b.px")
                                ->limit('0,'.$num)
                                ->group('m.id')
                                ->select();
        }
        return $result;
    }


    public function getMoreCases($start,$num){
        $map['m.is_single'] = array("EQ",'1');
        return M("meitu")->alias("m")
                                ->field('m.id,m.title,b.img_path,b.img_host')
                                ->join("INNER JOIN qz_meitu_img as b on m.id = b.caseid")
                                ->where($map)
                                ->order("m.id DESC,b.img_on DESC,b.px")
                                ->limit($start.','.$num)
                                ->group('m.id')
                                ->select();

    }

    /**
     * 获取美图的推荐案例
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getRecommendCases($type){
        $map = array(
                "type"=>array("EQ",$type),
                "istop"=>array("EQ",1)
                     );
        //1.取前100个美图案例
        $buildSql = M("meitu")->where($map)->limit(20)->order("id desc")->buildSql();
        //2.获取案例图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t1")
                              ->join("INNER JOIN qz_meitu_img as b on b.caseid = t1.id")
                              ->field("t1.*,b.img_path,b.img_host")
                              ->order("b.img_on desc ,b.px")
                              ->buildSql();
        return  M("meitu")->table($buildSql)->alias("t")
                          ->group("t.id")
                          ->select();
    }


    /**
     * 获取名师列表
     * @return [type] [description]
     */
    public function getMingshiList(){
        $map = array(
                "enabled"=>array("EQ",1)
                     );
        return M("meitu_designer")->where($map)->order("px desc,id")->select();
    }

    /**
     * 获取名师美图列表数量
     * @return [type] [description]
     */
    public function getMingshiCaseListCount($name){
         $map = array(
                "enabled"=>array("EQ",1)
                     );
        if(!empty($name)){
            $map["shortname"] = array("EQ",$name);
        }

        //1.获取名师列表
        $buildSql = M("meitu_designer")->where($map)->order("px")->buildSql();
        //2.获取名师的美图信息
        return M("meitu_designer")->table($buildSql)->alias("t")
                                  ->join("INNER JOIN qz_meitu as a on a.master = t.uid")
                                  ->count();

    }

    /**
     * 获取名师美图列表
     * @return [type] [description]
     */
    public function getMingshiCaseList($name,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "enabled"=>array("EQ",1)
                     );
        if(!empty($name)){
            $map["shortname"] = array("EQ",$name);
        }

        //1.获取名师列表
        $buildSql = M("meitu_designer")->where($map)->order("px")->buildSql();
        //2.获取名师的美图信息
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t")
                                       ->join("INNER JOIN qz_meitu as a on a.master = t.uid")
                                       ->field("a.*,t.uid as userid,t.name as username,t.name")
                                       ->limit($pageIndex.",".$pageCount)
                                       ->buildSql();
        //3.获取美图的其他信息
        $buildSql = M("meitu_designer")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_meitu_location as b on find_in_set(b.id,a.location)")
                         ->join("INNER JOIN qz_meitu_fengge as c on find_in_set(c.id,a.fengge) ")
                         ->join("INNER JOIN qz_meitu_huxing as d on find_in_set(d.id,a.huxing) ")
                         ->join("INNER JOIN qz_meitu_color as e on find_in_set(e.id,a.color) ")
                         ->field("a.*,GROUP_CONCAT(b.name) as wz,GROUP_CONCAT(c.name) as fg,GROUP_CONCAT(d.name) as hx,GROUP_CONCAT(e.name) as ys")
                         ->group("a.id")
                         ->buildSql();

        //4.获取设计师的美图图片信息
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t1")
                                       ->join("INNER JOIN qz_meitu_img as b on b.caseid = t1.id")
                                       ->field("t1.*,b.img_path,b.img_host")
                                       ->buildSql();
        return $buildSql = M("meitu_designer")->table($buildSql)->alias("t2")
                                              ->group("t2.id")
                                              ->order("t2.id desc")
                                              ->select();
    }

    /**
     * 根据美图的类型获取美图信息
     * @param  [type] $type  [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getMeituListByType($type,$limit,$not = ""){
        $map = array(
                "enabled"=>array("EQ",1)
        );
         switch ($type) {
            case 'location':
                 $types = M("meitu_location")->where($map)->field("id")->select();
                break;
            case 'huxing':
                  $types = M("meitu_huxing")->where($map)->field("id")->select();
                 break;
            case 'fengge':
                  $types = M("meitu_fengge")->where($map)->field("id")->select();
                 break;
            case 'color':
                  $types = M("meitu_color")->where($map)->field("id")->select();
                 break;
         }



         foreach ($types as $key => $value) {
            $ids[] = $value["id"];
         }


         $subMap = array(
                    $type=>array("IN",$ids),
                    "istop"=>array("EQ",1)
                         );
         if(count($not) > 0){
            $subMap["id"] = array("NOT IN",$not);
         }


        //获取最新的推荐的美图信息
        $buildSql = M("meitu")->where($subMap)->order("id desc")->limit($limit)->buildSql();

        //4.获取设计师的美图图片信息
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t1")
                                       ->join("INNER JOIN qz_meitu_img as b on b.caseid = t1.id")
                                       ->field("t1.*,b.img_path,b.img_host")
                                       ->order("img_on desc,px")
                                       ->buildSql();
        return $buildSql = M("meitu_designer")->table($buildSql)->alias("t2")
                                              ->group("t2.id")
                                              ->order("t2.id desc")
                                              ->select();


    }

    //获取相关美图
    public function getRelatedMeitu($map){
        $result = M("meitu")->field('id,title')->where($map)->limit('0,9')->order("time desc")->select();
        $num = count($result);
        if($num < 9 ){
            $s = 9 - $num;
            foreach ($result as $key => $value) {
                $ids[] = $value["id"];
            }
            $subMaps["id"] = array("NOT IN",$ids);
            $otherResult = M("meitu")->field('id,title')->where($subMaps)->limit('0,'.$s)->order("time desc")->select();
            $result = array_merge($result,$otherResult);
        }
        foreach ($result as $key => $value) {
            $submap['caseid'] = $value['id'];
            $imgList = M("meitu_img")->field('id,img_path,img_host')->where($submap)->order("px desc")->select();
            $result[$key]['img_path'] = $imgList['0']['img_path'];
            $result[$key]['img_host'] = $imgList['0']['img_host'];
            $result[$key]['count'] = count($imgList);
            unset($imgList);
        }
        return $result;
    }
}