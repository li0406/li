<?php
/**
 *  家居美图
 */
namespace Common\Model;
use Think\Model;
class MeituModel extends Model{

    //获取 美图 Item 列表
    public function getHomeMeituItemList($where){
        $where['h.module'] = array('IN','1,2,3');
        return M('meitu_home')->alias('h')
            ->field("h.*,m.title,f.img_path,f.img_host,f.img_on,f.px")
            ->join('LEFT JOIN qz_meitu AS m ON m.id = h.item_id')
            ->join("LEFT JOIN qz_meitu_img as f on f.caseid = m.id")
            ->group('h.id')
            ->where($where)
            ->select();
    }

    //获取 工装美图 Item 列表
    public function getHomePubMeituItemList($where){
        $where['h.module'] = '4';
        return M('meitu_home')->alias('h')
                    ->field("h.*,m.title,m.keyword,f.img_path,f.img_host,f.img_on,f.px")
                    ->join('LEFT JOIN qz_pubmeitu AS m ON m.id = h.item_id')
                    ->join("INNER JOIN qz_pubmeitu_img as f on f.caseid = m.id")
                    ->group('h.id')
                    ->where($where)
                    ->select();
    }

    //查询 3D美图 Item 列表
    public function getHomeThreeDItemList($where){
        $where['h.module'] = '5';
        return M('meitu_home')->alias('h')
            ->field("h.*,x.title,x.face")
            ->join('LEFT JOIN qz_xiaoguotu_threedimension AS x ON x.id = h.item_id')
            ->where($where)
            ->limit(5)
            ->select();
    }

    //获取设计师 列表
    public function getHomeDesignerList($condition){
        $where['h.module'] = '6';
        return M('meitu_home')->alias('h')
            ->field("h.*,d.*")
            ->join('LEFT JOIN qz_meitu_designer AS d ON d.id = h.item_id')
            ->where($where)
            ->limit(5)
            ->select();
    }


    /**
     * 获取点击率最高的图片信息
     * @return [type] [description]
     */
    public function getNewMeitussss($limit){
        //限制状态
        $map['state'] = 1;

        //1.获取美图信息
        $buildSql = M("meitu")->where($map)->order("time desc")->limit($limit)->buildSql();

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
    * 获取位置信息
    * @param  boolean $isTop [是否推荐]
    * @return [type]         [description]
    */
    public function getLocation($limit="",$isTop = false,$fix){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
            $map["istop"] = array("EQ",1);
        }
        if(!empty($fix)){
            $map['id'] = array('in',$fix);
        }
        M("meitu_location")->where($map)->order("istop desc,px");
        if(!empty($limit)){
            M("meitu_location")->limit($limit);
        }
        return M("meitu_location")->select();
    }

    //取第一个或者最后一个美图
    public function getFirstOrLastMeitu($order = "first",$params = 0, $is_single = 0){
        //限制状态
        $paramsMap['m.state'] = 1;
        //单图套图
        if (1 == intval($is_single)) {
            $paramsMap['m.is_single'] = 1;
        } else {
            $paramsMap['m.is_single'] = 0;
        }
        //参数限制
        if(!empty($params)){
            if($params['location'] > 0){
                $paramsMap[] = array("find_in_set(".$params['location'].",m.location)");
            }
            if($params['fengge'] > 0){
                $paramsMap[] = array("find_in_set(".$params['fengge'].",m.fengge)");
            }
            if($params['huxing'] > 0){
                $paramsMap[] = array("find_in_set(".$params['huxing'].",m.huxing)");
            }
            if($params['color'] > 0){
                $paramsMap[] = array("find_in_set(".$params['color'].",m.color)");
            }
            if($params['tags'] > 0){
                $paramsMap[] = array("find_in_set(".$params['tags'].",m.tags)");
            }
        }
        //查询
        M("meitu")->alias('m')
                  ->join('qz_meitu_img AS i ON i.caseid = m.id')
                  ->where($paramsMap);
        if($order == "first"){
            M("meitu")->order("m.id asc, i.px asc");
        }else{
            M("meitu")->order("m.id desc, i.px asc");
        }
        $result = M("meitu")->field('m.*,i.id AS meitu_img_id,i.img_path AS meitu_img_img_path,i.img_host AS meitu_img_img_host,i.description AS meitu_img_description')
                            ->find();
        //图片，必须先判断是否为空
        if (!empty($result)) {
            $result['child'] = array(
                array(
                    'id'       => $result['meitu_img_id'],
                    'img_path' => $result['meitu_img_img_path'],
                    'img_host' => $result['meitu_img_img_host'],
                    'imgdescription' => $result['meitu_img_description'],
                )
            );
            unset($result['meitu_img_id']);
            unset($result['meitu_img_img_path']);
            unset($result['meitu_img_img_host']);
            unset($result['meitu_img_description']);
        }
        return $result;
    }

    /**
    * 获取户型信息
    * @param  boolean $isTop [是否推荐]
    * @return [type]         [description]
    */
    public function getHuxing($limit="",$isTop = false,$fix=''){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
            $map["istop"] = array("EQ",1);
        }
        if(!empty($fix)){
            $map['id'] = array('in',$fix);
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
    public function getFengge($limit="",$isTop = false,$fix=''){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
            $map["istop"] = array("EQ",1);
        }
        if(!empty($fix)){
            $map['id'] = array('in',$fix);
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
    public function getColor($limit="",$isTop = false,$fix){
        $map = array(
            "enabled"=>array("EQ",1)
                     );
        if($isTop){
             $map["istop"] = array("EQ",1);
        }
        if(!empty($fix)){
            $map['id'] = array('in',$fix);
        }
        M("meitu_color")->where($map)->order("istop desc,px");
        if(!empty($limit)){
            M("meitu_color")->limit($limit);
        }
        return M("meitu_color")->select();
    }

    /**
     * 获取公装信息
     * @param  boolean $isTop [是否推荐]
     * @return [type]         [description]
     */
    public function getPublic($limit = "",$isTop = false, $fix = '')
    {
        $map = array(
            "enabled" => '1',
            'type' => '1',
        );

        if($isTop === true){
            $map["istop"] = array("EQ",1);
        }
        if (!empty($fix)) {
            $map['id'] = array('in', $fix);
        }
        M("pubmeitu_att")->where($map)->order("istop desc,id asc");
        if (!empty($limit)) {
            M("pubmeitu_att")->limit($limit);
        }
        return M("pubmeitu_att")->select();
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
                "is_single"=>array("EQ",0),
                "state" => 1
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
        //限制状态
        $map['state'] = 1;

        //1.获取美图信息
        $buildSql = M("meitu")->alias("a")
                         ->field("a.*")
                         ->where($map)
                         ->order("pv desc,id desc")
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


    public function getMeituListDescription($name,$type)
    {
        if(!empty($name) && !empty($type)){
            $map['name'] = array('EQ',$name);
            switch ($type) {
                case 'l':
                    $table = 'meitu_location';
                    break;
                case 'h':
                    $table = 'meitu_huxing';
                    break;
                case 'f':
                    $table = 'meitu_fengge';
                    break;
                case 'c':
                    $table = 'meitu_color';
                    break;
                default:
                    break;
            }
            $result = M($table)->where($map)->find();
            return $result;
        }
        return false;
    }

        /**
     * 获取点击率最高的图片信息
     * @return [type] [description]
     */
    public function getNewMeitu($limit){
        //限制状态
        $map['state'] = 1;
        $map['is_single'] = 0;

        //1.获取美图信息
        $buildSql = M("meitu")->alias("a")
                              ->field("a.*")
                              ->where($map)
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
        //限制状态
        $map['state'] = 1;

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
     * 获取大师设计作品 -- 美图首页
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getDesignerImg($limit){

        $where['d.enabled'] = '1';
        $where['h.module'] = '6';

        //1.获取推荐的设计师
        $buildSql = M('meitu_home')->alias('h')
            ->field("h.item_id,d.*")
            ->join('LEFT JOIN qz_meitu_designer AS d ON d.id = h.item_id')
            ->where($where)
            ->limit($limit)
            ->buildSql();

        //2.获取设计师的信息
         $buildSql = M("meitu_designer")->table($buildSql)->alias("t4")
                                        ->join("inner join qz_user as d on d.id = t4.uid")
                                        ->join("LEFT JOIN qz_user_des as e on e.userid = t4.uid")
                                        ->field("t4.*,d.logo,e.jobtime")
                                        ->buildSql();
        //2.获取设计师的作品
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t")
                                       ->join("inner join qz_meitu as b on (b.master = t.uid) AND (b.state = 1)")
                                       ->order("b.id desc")
                                       ->field("t.name,t.logo,t.shortname,b.*,t.jobtime,t.uid as userid,t.bm")
                                       ->buildSql();
        //3.获取作品
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t1")->group("t1.id")
                                       ->buildSql();

        //4.获取图片
        $buildSql = M("meitu_designer")->table($buildSql)->alias("t2")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t2.id")
                              ->field("t2.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();

        $buildSql = M("meitu_designer")->table($buildSql)->alias("t3")
                         ->group("t3.master")
                         ->order("img_on desc,px")
                         ->buildSql();

        return M("meitu_designer")->table($buildSql)->alias("t5")
                        ->field("count(b.id) as case_counts,t5.*")
                        ->join("left join qz_cases as b on t5.userid = b.userid and b.isdelete = 1")
                        ->group("t5.userid")
                        ->order("t5.px")
                        ->select();
    }

    /**
     * 获取美图列表数量
     * @return [type] [description]
     */
    public function getMeiTuListCount($location="",$fengge="",$huxing="",$color="",$keyword,$is_single = '99'){
        if(!empty($location)){
            $location = (int)$location;
            $complex[] = "find_in_set($location,location)";
        }

        if(!empty($fengge)){
            $fengge = (int)$fengge;
            $complex[] = "find_in_set($fengge,fengge)";
        }
        if(!empty($huxing)){
            $huxing = (int)$huxing;
            $complex[] = "find_in_set($huxing,huxing)";
        }

        if(!empty($color)){
            $color = (int)$color;
             $complex[] = "find_in_set($color,color)";
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

        //限制状态
        $map['state'] = 1;

        $result =  M("meitu")->where($map)->count();
        return $result;
    }

    /**
     * 获取美图列表
     * @return [type] [description]
     */
    public function getMeiTuList($pageIndex,$pageCount,$location="",$fengge="",$huxing="",$color="",$keyword,$is_single = '99',$order,$type=0,$limit=8)
    {
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

        if(!empty($huxing)){
            $huxing = (int)$huxing;
            $complex[] = "find_in_set($huxing,huxing)";
        }

        if(!empty($color)){
             $color = (int)$color;
             $complex[] = "find_in_set($color,color)";
        }
        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }

        if(!empty($keyword)){
            $map["title"] = array("LIKE","%".$keyword."%");
        }

        if(empty($order)){
            $order='id desc';
        }

        if('99' != $is_single ){
            $map["is_single"] = array("EQ",$is_single);
        }

        //限制状态
        $map['state'] = 1;

        //1.查询美图的基本信息
        $buildSql = M("meitu")->where($map)->order($order)
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
        //3.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("t1")
                             ->group("t1.id")
                             ->order("img_on desc,px")
                             ->buildSql();
        if($type == 1){
            $result = M("meitu")->table($buildSql)->alias("t1")->order("rand()")->limit($limit)->select();
        }else{
            $result = M("meitu")->table($buildSql)->alias("t1")->order("t1.id desc")->select();
        }

        return $result;
    }

    /**
     * [getMeituForTag 为标签页获取美图]
     * @param  array   $map   [查询条件]
     * @param  integer $limit [限制数量]
     * @return [type]         [description]
     */
    public function getMeituForTag($map = array(), $limit = 10)
    {
        $map['state'] = 1;
        $result = M('meitu')->field('id,title,tags')->where($map)->limit($limit)->select();
        //循环取图片列表
        foreach ($result as $k => $v) {
            $maps['caseid'] = $v['id'];
            $result[$k]['imgs'] = M('meitu_img')->field('img_path')->order('px')->limit('0,1')->where($maps)->select();
        }
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
    public function getRecommendMeituByAttr($location="",$fengge="",$huxing="",$color="",$limit = 10,$order='rand()'){
        //限制状态
        $map['state'] = 1;
        //只取图片
        $map['is_single'] = 1;
        if(!empty($location)){
            $location = (int)$location;
            $complex[] = "find_in_set($location,location)";
        }
        if(!empty($fengge)){
            $fengge = (int)$fengge;
            $complex[] = "find_in_set($fengge,fengge)";
        }

        if(!empty($huxing)){
            $huxing = (int)$huxing;
            $complex[] = "find_in_set($huxing,huxing)";
        }

        if(!empty($color)){
            $color = (int)$color;
             $complex[] = "find_in_set($color,color)";
        }
        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }
        $result = M("meitu")->field('id,title')->where($map)->order($order)->limit($limit)->select();
        return $result;
    }

    /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMeituInfo($map = array(), $params = array(), $order = 'asc'){
        //可见
        $map["a.state"] = 1;
        //参数设置
        if(!empty($params)){
            if($params['location'] > 0){
                $map[] = array("find_in_set(".$params['location'].",a.location)");
            }
            if($params['fengge'] > 0){
                $map[] = array("find_in_set(".$params['fengge'].",a.fengge)");
            }
            if($params['huxing'] > 0){
                $map[] = array("find_in_set(".$params['huxing'].",a.huxing)");
            }
            if($params['color'] > 0){
                $map[] = array("find_in_set(".$params['color'].",a.color)");
            }
        }

        //排序
        if ('asc' == $order) {
            $order = 'a.id asc';
        } else {
            $order = 'a.id desc';
        }

        //1.查询美图信息
        $buildSql = M("meitu")->alias("a")->where($map)->order($order)->limit(1)->buildSql();
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
            if($params['tags'] > 0){
                $map[] = array("find_in_set(".$params['tags'].",tags)");
            }
        }

        //限制单图和状态
        $map['m.is_single'] = array("EQ",'1');
        $map['m.state'] = 1;

        if(empty($uid)){
            if($type == 'pre'){
                $map['m.id'] = array("GT",$id);
                $result = M("meitu")->alias("m")
                                    ->field('m.id,m.title,m.time,m.likes,b.img_path,b.img_host,b.description imgdescription')
                                    ->join("INNER JOIN qz_meitu_img as b on m.id = b.caseid")
                                    ->where($map)
                                    ->order("m.id ASC,b.img_on DESC,b.px")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
            }else{
                $map['m.id'] = array("LT",$id);
                $result = M("meitu")->alias("m")
                                    ->field('m.id,m.title,m.time,m.likes,b.img_path,b.img_host,b.description imgdescription')
                                    ->join("left JOIN qz_meitu_img as b on b.caseid = m.id")
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
                $result = M("meitu")->alias("m")
                                    ->field('m.id,m.title,m.time,m.likes,b.img_path,b.img_host,c.id AS collect')
                                    ->join("INNER JOIN qz_meitu_img as b on m.id = b.caseid")
                                    ->join("LEFT JOIN qz_user_collect as c ON c.userid = $uid AND c.classtype = $classtype AND c.classid = b.caseid")
                                    ->where($map)
                                    ->order("m.id ASC,b.img_on DESC,b.px")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
            }else{
                $map['m.id'] = array("LT",$id);
                $result = M("meitu")->alias("m")
                                    ->field('m.id,m.title,m.time,m.likes,b.img_path,b.img_host,c.id AS collect')
                                    ->join("LEFT JOIN qz_meitu_img as b on b.caseid = m.id")
                                    ->join("LEFT JOIN qz_user_collect as c ON c.userid = $uid AND c.classtype = $classtype AND c.classid = b.caseid")
                                    ->where($map)
                                    ->order("m.id DESC,b.img_on DESC,b.px")
                                    ->limit('0,'.$num)
                                    ->group('m.id')
                                    ->select();
            }
        }
        foreach ($result as $k=> $v) {
            $result[$k]['top_title'] = $v['title'].'-齐装网装修效果图';
        }
        return $result;
    }


    public function getMoreCases($start,$num){
        //限制状态
        $map['m.state'] = 1;
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
            "type"  => array("EQ",$type),
            "istop" => array("EQ",1),
            "state" => 1
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
     * 获取美图列表
     * @param  array   $map   查询条件
     * @param  integer $limit 查询数量
     * @return array          美图列表数组
     */
    public function getMeituByMap($map = array(), $limit = 10)
    {
        $map['state'] = 1;
        $buildSql = M("meitu")->where($map)->limit($limit)->order("id desc")->buildSql();
        //2.获取案例图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t1")
                              ->join("INNER JOIN qz_meitu_img as b on b.caseid = t1.id")
                              ->field("t1.*,b.img_path,b.img_host")
                              ->order("b.img_on desc ,b.px")
                              ->buildSql();
        return  M("meitu")->table($buildSql)->alias("t")->group("t.id")->select();
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
                                  ->join("INNER JOIN qz_meitu as a on (a.master = t.uid) AND (a.state = 1)")
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
                                       ->join("INNER JOIN qz_meitu as a on (a.master = t.uid) AND (a.state = 1)")
                                       ->field("a.*,t.uid as userid,t.name as username,t.name")
                                       ->limit($pageIndex.",".$pageCount)
                                       ->buildSql();
        //3.获取美图的其他信息
        $buildSql = M("meitu_designer")->table($buildSql)->alias("a")
                         ->join("INNER JOIN qz_meitu_location as b on find_in_set(b.id,a.location)")
                         ->join("INNER JOIN qz_meitu_fengge as c on find_in_set(c.id,a.fengge) ")
                         ->field("a.*,GROUP_CONCAT(b.name) as wz,GROUP_CONCAT(c.name) as fg")
                         ->group("a.id")
                         ->buildSql();

        $buildSql = M("meitu_designer")->table($buildSql)->alias("t")
                                       ->join("INNER JOIN qz_meitu_huxing as d on find_in_set(d.id,t.huxing) ")
                                       ->join("INNER JOIN qz_meitu_color as e on find_in_set(e.id,t.color) ")
                                       ->field("t.*,GROUP_CONCAT(d.name) as hx,GROUP_CONCAT(e.name) as ys")
                                       ->group("t.id")
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
            $type   => array("IN",$ids),
            "istop" => array("EQ",1),
            "state" => 1
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
    public function getRelatedMeitu($map,$id){
        //限制状态
        $map['state'] = 1;
        $map['id'] = array('neq',$id);
        //参数限制
        $result = M("meitu")->field('id,title')->where($map)->limit('0,9')->order("time desc")->select();
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
            //限制状态
            $subMaps['state'] = 1;
            $subMaps["id"] = array("NOT IN",$ids);
            $subMaps["is_single"] = $map['is_single'];
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

    //按分类取美图
    public function getMeituByCategory($category,$id,$num){
        //限制状态
        $map['a.state'] = 1;
        $map["_complex"] = array("find_in_set(".$id.",$category)");

        //1.获取美图信息
        $buildSql = M("meitu")->alias("a")
                              ->field("a.*")
                              ->where($map)
                              ->order("id desc")->limit('0,'.$num)
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

    //获取美图首页专题
    public function getHomeZt()
    {
        //限制状态
        $map['a.state'] = 1;
        $map['a.zt'] = array('NEQ','0');

        //1.获取美图信息
        $buildSql = M("meitu")->alias("a")
                              ->field("a.*")
                              ->where($map)
                              ->order("zt desc")->limit('0,6')
                              ->buildSql();

        //2.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("left JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        return M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")
                         ->order("zt")
                         ->select();
    }


    /**
     * [getGoodImg 获取推荐好图]
     * @return [type]       [description]
     */
    public function getGoodImg()
    {
        $map = array(
            "a.goodimg" => array("NEQ",0),
            "a.state" => 1
        );
        $buildSql = M("meitu")->alias('a')->where($map)->order("a.goodimg asc")->buildSql();
        $buildSql = M("meitu_img")->alias('i')
                                  ->field("t.*,i.id as imgid,i.img_path,i.img_host,i.img_on,i.title as imgtitle,i.description as imgdes")
                                  ->join("INNER JOIN ".$buildSql." as t on t.id = i.caseid")
                                  ->order("id,i.px")
                                  ->buildSql();

        return M("meitu")->table($buildSql)->alias("tt")
                            ->group("tt.id")
                            ->order("tt.goodimg asc")
                            ->select();
    }

    /**
     * [getzhuanti 获取热门专题]
     * @return [type]       [description]
     */
    public function getZhuanti()
    {
        $map = array(
            "a.zt" => array("NEQ",0),
            "a.state" => 1
        );
        $buildSql = M("meitu")->alias('a')->where($map)->order("a.zt asc")->buildSql();

        $buildSql = M("meitu_img")->alias('i')
                                  ->field("t.*,i.id as imgid,i.img_path,i.img_host,i.img_on,i.title as imgtitle,i.description as imgdes")
                                  ->join("INNER JOIN ".$buildSql." as t on t.id = i.caseid")
                                  ->order("id,i.px")
                                  ->buildSql();

        return M("meitu")->table($buildSql)->alias("tt")
                            ->group("tt.id")
                            ->order("tt.zt asc")
                            ->select();
    }

    /**
     * 获取标签页的美图信息
     * @param  [type] $fen_word [description]
     * @return [type]           [description]
     */
    public function getBiaoQianList($fen_word,$limit)
    {
        if (!is_array($fen_word)) {
            return false;
        }

        foreach ($fen_word as $key => $value) {
            $title[] = array("LIKE","%$value%");
        }
        $title[] = "OR";

        $map = array(
            "title" => $title,
            "is_single" => array("EQ",0),
            "state" => array("EQ",1)
        );

        //获取美图信息
        $buildSql = M("meitu")->where($map)->field("id,title")->order("id desc")->limit($limit)->buildSql();
        //获取美图封面图
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("join qz_meitu_img b on t.id = b.caseid")
                              ->field("t.*,b.img_path")
                              ->order("b.img_on desc,b.px")
                              ->buildSql();
        return M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")->order("t1.id desc")->select();

    }

    /**
     * 更新真实阅读量（按IP单倍计算，即单个IP每天只算一次）
     * @return [$id] [文章ID]
     */
    public function updateRealView($id){
        $map['id'] = $id;
        M('meitu')->where($map)->setInc('realview',1);
    }

    //获取装修落地页的图片信息
    public function getLandPageMeiTuList($location=0,$fengge=0,$huxing=0,$color=0,$limit='',$order = 0)
    {
        //过滤
        if(!empty($location)){
            $location = (int)$location;
            $complex[] = "find_in_set($location,location)";
        }
        if(!empty($fengge)){
            $fengge = (int)$fengge;
            $complex[] = "find_in_set($fengge,fengge)";
        }

        if(!empty($huxing)){
            $huxing = (int)$huxing;
            $complex[] = "find_in_set($huxing,huxing)";
        }

        if(!empty($color)){
            $color = (int)$color;
            $complex[] = "find_in_set($color,color)";
        }
        if(count($complex)  > 0){
            $map["_complex"] = $complex;
        }
        if($order!=0){
            $order = 'likes desc';
        }else{
            $order = 'id desc';
        }
        //限制状态
        $map['state'] = 1;
        //1.查询美图的基本信息
        $buildSql = M("meitu")->where($map)
            ->buildSql();

        //3.获取美图图片信息
        $buildSql = M("meitu")->table($buildSql)->alias("t")
            ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
            ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
            ->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("t1")
            ->order($order.",img_on desc,px")
            ->group("t1.id")
            ->buildSql();

        $result = M("meitu")->table($buildSql)->alias("t1")->limit($limit)->select();
        return $result;


//        $result = M('meitu')->alias('m')
//            ->field("m.*,f.img_path,f.img_host,f.img_on,f.px")
//            ->join("LEFT JOIN qz_meitu_img as f on f.caseid = m.id")
//            ->where($map)
//            ->order('id desc')
//            ->limit($limit)
//            ->select();
//
//        return $result;
    }

    //获取装修攻略banner
    public function getGonglueBanner(){
        return M("meitu_zt_banner")->where(["status"=>1, "type"=>1])->order("time DESC")->limit(5)->field("id, title, thumb, url,order_id")->select();
    }

    /**
     * 获取家装美图标签
     * @param $id
     * @return mixed
     */
    public function getMeituTags($id)
    {
        $where['m1.id'] = ['eq', $id];
        return M('meitu')
            ->alias('m1')
            ->field("m1.id,b.name as wz,c.name as fg,d.name as hx,e.name as ys,b.id AS wz_id,c.id AS fg_id,d.id AS hx_id,e.id AS ys_id")
            ->join("left JOIN qz_meitu_location as b on find_in_set(b.id,m1.location)")
            ->join("left JOIN qz_meitu_fengge as c on find_in_set(c.id,m1.fengge) ")
            ->join("left JOIN qz_meitu_huxing as d on find_in_set(d.id,m1.huxing) ")
            ->join("left JOIN qz_meitu_color as e on find_in_set(e.id,m1.color) ")
            ->where($where)
            ->find();
    }

    /**
     * 获取公装美图标签
     * @param $id
     * @return mixed
     */
    public function getPubMeituTags($id){
        //获取公装美图位置标签
        $where['id'] = ['eq', $id];
        $pubmeitu = M('Pubmeitu') -> field('location, fengge, mianji,likes') -> where($where) -> find();
        if (!empty($pubmeitu['location'])) {
            $map['type'] = ['eq',1];
            $map['id'] = ['in',$pubmeitu['location']];
        }
        $location = M('PubmeituAtt')->field('id, name')->where($map)->find();
        $returnData['wz'] = $location['name'];
        $returnData['wz_id'] = $location['id'];

        //获取公装美图风格标签
        if (!empty($pubmeitu['fengge'])) {
            $map['id'] = ['in',$pubmeitu['fengge']];
            $map['type'] = ['eq',2];
        }
        $fengge = M('PubmeituAtt')->field('id, name')->where($map)->find();
        $returnData['fg'] = $fengge['name'];
        $returnData['fg_id'] = $fengge['id'];

        //获取公装美图面积标签
        if (!empty($pubmeitu['mianji'])) {
            $map['id'] = ['in',$pubmeitu['mianji']];
            $map['type'] = ['eq',3];
        }
        $mianji = M('PubmeituAtt')->field('id, name')->where($map)->find();
        $returnData['mj'] = $mianji['name'];
        $returnData['mj_id'] = $mianji['id'];
        if(!empty($pubmeitu['likes'])){
            $returnData['likes'] = $pubmeitu['likes'];
        }
        return $returnData;
    }
    public function getMeituByTagName($page = 1,$size = 5,$tagName,$type,$field){
        $map = [
            'a.state' => array('EQ',3),
            'a.type' => array('EQ',$type),
            't.name' => array('EQ',$tagName)
        ];
        $map['ar.module'] = $type == 1 ? array('EQ',7):array('EQ',5); 
        return M('tu')->alias('a')
            ->join('INNER JOIN qz_tu_image ti ON ti.meitu_id = a.id')
            ->join('INNER JOIN qz_thematic_content_related ar on ar.content_id = a.id')
            ->join('INNER JOIN qz_thematic_words t on t.id = ar.thematic_id')
            ->field($field)
            ->where($map)
            ->order('a.publish_time DESC,a.id DESC')
            ->page($page,$size)
            ->select();
    }
}
