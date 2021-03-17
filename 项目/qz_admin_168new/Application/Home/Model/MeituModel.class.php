<?php

namespace Home\Model;
Use Think\Model;

class MeituModel extends Model{
    protected $autoCheckFields = false;
    protected $tableName = 'meitu';

    /**
     * 获取美图列表
     * @param [type] $[query] [搜索的文字]
     * @return [type] 搜索文字相匹配的列表
     */
    public function getList($value){
        /*$map['module'] = 'home_meitu';
        $map['value'] = $value;*/
        $map = array(
                     "module" => $value,
                     //"value" => $value
                     );
        $Db = M('adv_banner');
        $count  = $Db->where($map)->count();
        $result = $Db->field('*')
                      ->where($map)
                      ->order("sort asc")
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 获取美图标题里列表,ajax调用
     * @param [type] $[query] [搜索的文字]
     * @return [type] 搜索文字相匹配的列表
     */
    public function getMeituTitle($query){
        $map = array(
            "title"=>array("LIKE","%$query%")
                     );
        return M("meitu")->where($map)->field("id,title")->select();
    }

    /**
     * 获取文字标题里列表,ajax调用
     * @param [type] $[query] [搜索的文字]
     * @return [type] 搜索文字相匹配的列表
     */
    public function getTextTitle($query){
        $map = array(
            "name"=>array("LIKE","%$query%")
                     );
        $subQuery = M("meitu_fengge")->field("id,name,'fengge' as type")
                         ->table("qz_meitu_fengge")
                         ->where("enabled = 1")
                         ->union("SELECT id,name,'huxing' FROM qz_meitu_huxing where enabled = 1",true)
                         ->union("SELECT id,name,'location' FROM qz_meitu_location where enabled = 1",true)
                         ->buildSql();
        return M("meitu_fengge")->table($subQuery.'a')->where($map)->select();
    }

    /**
     * 删除广告
     * @return [type] [description]
     */
    public function delBanner($id){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('adv_banner')->where($map)->delete();
    }


    /**
     * [getGoodImg 获取推荐好图]
     * @return [type]       [description]
     */
    public function getGoodImg()
    {
        $map = array(
            "goodimg"=>array("NEQ",0)
                     );
        return M("meitu")->field("id,title,goodimg,zt,time")->where($map)->order("goodimg asc")->select();
    }


    /**
     * [getOnePubmeitu 获得美图]
     * @return
     */
    public function getOnemeitu($id)
    {
        $map['a.id'] = $id;
        return M("meitu")->where($map)->alias("a")
                         ->field("a.id,a.title,a.goodimg,a.zt")
                         ->find();
    }

    /**
     * [getzhuanti 获取热门专题]
     * @return [type]       [description]
     */
    public function getZhuanti()
    {
        $map = array(
            "zt"=>array("NEQ",0)
                     );
        return M("meitu")->field("id,title,goodimg,zt,time")->where($map)->order("zt asc")->select();
    }

    /**
     * [getGoodImg 获取推荐好图的数量]
     * @return [type]       [description]
     */
    public function getGoodImgCount()
    {
        $map = array(
            "goodimg"=>array("NEQ",0)
                     );
        return M("meitu")->where($map)->count();
    }

    /**
     * [getzhuanti 获取热门专题]
     * @return [type]       [description]
     */
    public function getZhuantiCount()
    {
        $map = array(
            "zt"=>array("NEQ",0)
                     );
        return M("meitu")->where($map)->count();
    }

    /**
     * [editPubmeitu 保存公装美图]
     * @return
     */
    public function editMeitu($id,$data)
    {
        $map = array(
            "id"=>array("EQ",$id)
        );
        return M("meitu")->where($map)->save($data);
    }

    /**
     * [addMeitu 新增美图]
     * @param [type] $data [description]
     */
    public function addMeitu($data){
        $result = M("meitu")->add($data);
        return $result;
    }

    /**
     * [addMeituImages 新增美图图片]
     * @param [type] $uid    [description]
     * @param [type] $images [description]
     */
    public function addMeituImages($uid, $images){
        foreach ($images as $key => $value) {
            $images[$key]['caseid'] = $uid;
        }
        $result = M('meitu_img')->addAll($images);
        return $result;
    }

    /**
     * [getMeituById 通过美图id获取美图]
     * @param  [type] $id [美图id]
     * @return [type]     [description]
     */
    public function getMeituById($id){
        if(empty($id)){
            return false;
        }
        $map = array(
                     'm.id' => $id
                     );
        $result = M('meitu')->alias('m')
                            ->field('m.*,GROUP_CONCAT(t.name) as tagname')
                            ->join('LEFT JOIN qz_tags AS t ON FIND_IN_SET(t.id,m.tags)')
                            ->where($map)
                            ->find();
        $result['tagname'] = array_filter(explode(',', $result['tagname']));
        $result['images'] = M('meitu_img')->where(['caseid' => $result['id']])->select();
        return $result;
    }

    /**
     * [deleteMeituById 通过美图id删除美图]
     * @param  [type] $id [美图id]
     * @return [type]     [description]
     */
    public function deleteMeituById($id){
        if(empty($id)){
            return false;
        }
        $result = M('meitu')->where(['id' => $id])->delete();
        return $result;
    }

    /**
     * [deleteMeituImgById 通过美图图片id删除美图]
     * @param  [type] $id [美图图片id]
     * @return [type]     [description]
     */
    public function deleteMeituImgById($id){
        if(empty($id)){
            return false;
        }
        $result = M('meitu_img')->where(['id' => $id])->delete();
        return $result;
    }

    /**
     * [editPubmeitu 保存美图]
     * @return
     */
    public function editMeituImg($id,$data)
    {
        $map = array(
            "id"=>array("EQ",$id)
        );
        return M("meitu_img")->where($map)->save($data);
    }

    /**
     * [getMeituCount 获取美图数量]
     * @param  [type] $params  [参数]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getMeituCount($params ,$keyword, $state){

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
            if($params['is_choice'] == '1' && !empty($params['ids'])){
                $map['id'] = array('IN',$params['ids']);
            }
            if($params['is_choice'] == '2' && !empty($params['ids'])){
                $map['id'] = array('NOT IN',$params['ids']);
            }
        }

        if(!empty($keyword)){
            $map["_complex"] = array(
                'id' => array('LIKE', '%'.$keyword.'%'),
                'title' => array('LIKE', '%'.$keyword.'%'),
                '_logic' => 'OR'
            );
        }

        if(!empty($state)){
            $map['state'] = $state;
        }

        $result = M('meitu')->where($map)->count();
        return $result;
    }

    /**
     * [getMeituList 获取美图列表]
     * @param  [type]  $params    [参数：风格户型面积颜色]
     * @param  integer $pageIndex [开始页]
     * @param  integer $pageCount [每页数量]
     * @param  string  $keyword   [搜索关键字]
     * @param  string  $order     [排序]
     * @return [type]             [description]
     */
    public function getMeituList($params, $pageIndex=1, $pageCount = 16,$keyword='', $state, $order = 'id DESC'){

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
            if($params['is_choice'] == '1' && !empty($params['ids'])){
                $map['id'] = array('IN',$params['ids']);
            }
            if($params['is_choice'] == '2' && !empty($params['ids'])){
                $map['id'] = array('NOT IN',$params['ids']);
            }
        }

        if(!empty($keyword)){
            $map["_complex"] = array(
                'id' => array('LIKE', '%'.$keyword.'%'),
                'title' => array('LIKE', '%'.$keyword.'%'),
                '_logic' => 'OR'
            );
        }

        if(!empty($state)){
            $map['state'] = $state;
        }

        $build = M('meitu')->alias('m')->where($map)
                           ->order($order)
                           ->limit($pageIndex, $pageCount)
                           ->buildSql();

        //两次关联查询要分开，不然会重复好多标签
        $build = M()->table($build)
                    ->alias('m')
                    ->join("LEFT JOIN qz_tags as t on FIND_IN_SET(t.id,m.tags)")
                    ->field("m.*,GROUP_CONCAT(t.name) as tagname")
                    ->group("m.id")
                    ->buildSql();

        $result = M()->table($build)
                     ->alias('z')
                     ->field("z.*,g.img_path,u.name as last_username")
                     ->join("LEFT JOIN qz_meitu_img as g on g.caseid = z.id")
                     ->join("LEFT JOIN qz_adminuser as u on u.id = z.update_uid")
                     ->order($order)
                     ->group("z.id")
                     ->select();
        return $result;
    }

    /**
     * [getMeituImgById id获取美图图片]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMeituImgById($id){
        if (empty($id)) {
            return false;
        }
        $result = M('meitu_img')->where(['id' => $id])->find();
        return $result;
    }

    /**
     * [getMeituAttribute description]
     * @param  string $type [0:获取所有，1：获取位置，2：获取风格，3：获取户型，4：获取颜色]
     * @return [type]       [description]
     */
    public function getMeituAttribute($type = '', $map = []){
        if(empty($map)){
            $map['id'] = ['GT', 0];
        }
        if(empty($type)){
            $result['location'] = M('meitu_location')->where($map)->select();
            $result['fengge'] = M('meitu_fengge')->where($map)->select();
            $result['huxing'] = M('meitu_huxing')->where($map)->select();
            $result['color'] = M('meitu_color')->where($map)->select();
        }else{
            switch ($type) {
                case 'location':
                    $result = M('meitu_location')->where($map)->select();
                    break;
                case 'fengge':
                    $result = M('meitu_fengge')->where($map)->select();
                    break;
                case 'huxing':
                    $result = M('meitu_huxing')->where($map)->select();
                    break;
                case 'color':
                    $result = M('meitu_color')->where($map)->select();
                    break;
                default:
                    break;
            }
        }

        return $result;
    }

    public function addMeituAttribute($type, $save){
        switch ($type) {
            case 'location':
                $result = M('meitu_location')->add($save);
                break;
            case 'fengge':
                $result = M('meitu_fengge')->add($save);
                break;
            case 'huxing':
                $result = M('meitu_huxing')->add($save);
                break;
            case 'color':
                $result = M('meitu_color')->add($save);
                break;
            default:
                break;
        }
        return $result;
    }

    /**
     * [saveMeituAttribute 保存美图属性]
     * @param  [type] $type [属性类型]
     * @param  [type] $id   [属性id]
     * @param  [type] $save [保存的信息]
     * @return [type]       [description]
     */
    public function saveMeituAttribute($type, $id, $save){
        $map = ['id' => $id];
        switch ($type) {
            case 'location':
                $result = M('meitu_location')->where($map)->save($save);
                break;
            case 'fengge':
                $result = M('meitu_fengge')->where($map)->save($save);
                break;
            case 'huxing':
                $result = M('meitu_huxing')->where($map)->save($save);
                break;
            case 'color':
                $result = M('meitu_color')->where($map)->save($save);
                break;
            default:
                break;
        }
        return $result;
    }

    /**
     *  根据标题查询美图列表
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function getMeituByTitle($title,$limit = 10)
    {
        $map = array(
            "title" => array("LIKE","%$title%")
        );
        return M('meitu')->where($map)->limit($limit)->field("id,title")->select();
    }


    /**
     * [getJiajumeituByName 根据标题名获取标题]
     * @param  [type]  $name  [标题名]
     * @param  integer $limit [获取条数]
     * @param  string  $istop [是否推荐]
     * @param  string  $order [排序]
     * @return [type]         [标题数组]
     */
    public function getJiajumeituByName($title,$limit = 15,$istop = '',$order = 'istop DESC'){
        $map['title'] =  array('EQ',$title);
        if(!empty($istop) && ($istop == 1 || $istop == 0)){
            $map['istop'] = intval($istop);
        }
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('Meitu')->where($map)->find();
        if (!empty($complete) && !empty($limit)) {
            $limit = $limit - 1;
        }

        //重新定义名字查询条件
        $map['title'] =  array('like','%'. $title .'%');
        $result = M('Meitu')->where($map)->limit($limit)->order($order)->select();
        if (!empty($complete)) {
            array_unshift($result, $complete);
        }
        return $result;
    }
}