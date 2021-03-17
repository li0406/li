<?php

namespace Common\Model;
Use Think\Model;

class MeituModel extends Model{
    protected $autoCheckFields = false;
    protected $tableName = 'meitu';

    /**
     * [getMeituList 获取美图列表]
     * @param  [type]  $params    [参数：风格户型面积颜色]
     * @param  integer $pageIndex [开始页]
     * @param  integer $pageCount [每页数量]
     * @param  string  $keyword   [搜索关键字]
     * @param  string  $order     [排序]
     * @return [type]             [description]
     */
    public function getMeituList($params, $pageIndex, $pageCount,$keyword='', $state =1, $order = 'id DESC'){

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

        $builds = M('meitu')->alias('m')->where($map)
                            ->order($order)
                            ->limit($pageIndex, $pageCount)
                            ->buildSql();

        $builds = M()->table($builds)
                     ->alias('m')
                     ->field("m.id,m.title,m.description,GROUP_CONCAT(t.name order by t.id desc) as tagname,GROUP_CONCAT(i.img_path order by i.id) as img_path,m.location,m.fengge,m.huxing,m.color,m.pv")
                     ->join("LEFT JOIN qz_tags as t on FIND_IN_SET(t.id,m.tags)")
                     ->join("LEFT JOIN qz_meitu_img as i on i.caseid = m.id")
                     ->group("m.id")
                     ->buildSql();
        $result = M()->table($builds)
                    ->alias('m1')
                    ->field("m1.*,b.name as wz,c.name as fg,d.name as hx,e.name as ys")
                    ->join("left JOIN qz_meitu_location as b on find_in_set(b.id,m1.location)")
                    ->join("left JOIN qz_meitu_fengge as c on find_in_set(c.id,m1.fengge) ")
                    ->join("left JOIN qz_meitu_huxing as d on find_in_set(d.id,m1.huxing) ")
                    ->join("left JOIN qz_meitu_color as e on find_in_set(e.id,m1.color) ")
                    ->group("m1.id")
                    ->select();
        return $result;
    }

    /**
     * [getMeituImgById id获取美图图片]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMeituImgById($id)
    {
        if (empty($id)) {
            return false;
        }
        $builds = M('meitu')->alias('m')
            ->field('i.id,if(i.title <> "",i.title,m.title) as title,i.img_path,i.time,i.caseid,m.pv,m.location,m.fengge,m.huxing,m.color')
            ->join("LEFT JOIN qz_meitu_img as i on i.caseid = m.id")
            ->where(['i.caseid' => $id])
            ->buildSql();
        $result = M()->table($builds)
            ->alias('m1')
            ->field("m1.*,b.name as wz,c.name as fg,d.name as hx,e.name as ys")
            ->join("left JOIN qz_meitu_location as b on find_in_set(b.id,m1.location)")
            ->join("left JOIN qz_meitu_fengge as c on find_in_set(c.id,m1.fengge) ")
            ->join("left JOIN qz_meitu_huxing as d on find_in_set(d.id,m1.huxing) ")
            ->join("left JOIN qz_meitu_color as e on find_in_set(e.id,m1.color) ")
            ->group("m1.id")
            ->select();
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

    public function getTagNameById($tag, $id)
    {
        $tagName = '';
        switch ($tag) {
            case 'location':
                $tagName = M('meitu_location')->field('id,name')->where(['id' => $id])->find();
                break;
            case 'fengge':
                $tagName = M('meitu_fengge')->field('id,name')->where(['id' => $id])->find();
                break;
            case 'huxing':
                $tagName = M('meitu_huxing')->field('id,name')->where(['id' => $id])->find();
                break;
            case 'color':
                $tagName = M('meitu_color')->field('id,name')->where(['id' => $id])->find();
                break;
        }
        return $tagName;
    }

}