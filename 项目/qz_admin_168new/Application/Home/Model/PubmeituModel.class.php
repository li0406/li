<?php
/**
 * 公装美图
 */
namespace Home\Model;

use Think\Model;

class PubmeituModel extends Model
{

    /**
     * [addPubmeitu 添加公装美图]
     * @return
     */
    public function addPubmeitu($data)
    {
        return M("Pubmeitu")->add($data);
    }

    /**
     * [addPubmeituImg 添加公装美图的图片]
     * @return
     */
    public function addPubmeituImg($data)
    {
        return M("Pubmeitu_img")->add($data);
    }

    /**
     * [addPubmeituAtt 添加公装美图的属性]
     * @return
     */
    public function addPubmeituAtt($data)
    {
        return M("Pubmeitu_att")->add($data);
    }


    /**
     * [getOnePubmeitu 获得公装美图]
     * @return
     */
    public function getOnePubmeitu($id)
    {
        $map['a.id'] = $id;
        $result = M("Pubmeitu")->where($map)->alias("a")
                               ->join("LEFT JOIN qz_tags as b on FIND_IN_SET(b.id,a.tags)")
                               ->group("a.id")
                               ->field("a.*,GROUP_CONCAT(b.name) as tagname")
                               ->find();
        if (!empty($result)) {
            $result['images'] = M('pubmeitu_img')->field(true)->where(array('caseid' => $id))->select();
            return $result;
        }
        return false;
    }


    /**
     * [getPubmeituList 获取公装美图案例]
     * @param  [type]
     * @param  [type]
     * @return [type]       [description]
     */
    public function getPubmeituList($condition,$pageIndex,$pageCount)
    {
        /*->join("LEFT JOIN qz_meitu_location as b on b.id = a.location")
                         ->join("LEFT JOIN qz_meitu_fengge as c on c.id = a.fengge")
                         ->join("LEFT JOIN qz_meitu_huxing as d on d.id = a.huxing")
                         ->join("LEFT JOIN qz_meitu_color as e on e.id = a.color")*/
        if(!empty($condition['title']) || !empty($condition['id'])){
            if(!empty($condition['title'])){
                $map['a.title']  = array('like' , "%".$condition['title']."%");
            }
            if(!empty($condition['id'])){
                $map['a.id'] = $condition['id'];
            }
        }else{
            if(!empty($condition['lx'])){
                $map['location'] = $condition['lx'];
            }
            if(!empty($condition['fg'])){
                $map['fengge'] = $condition['fg'];
            }
            if(!empty($condition['mj'])){
                $map['mianji'] = $condition['mj'];
            }
        }

        if($condition['is_choice'] == '1' && !empty($condition['ids'])){
            $map['a.id'] = array('IN',$condition['ids']);
        }
        if($condition['is_choice'] == '2' && !empty($condition['ids'])){
            $map['a.id'] = array('NOT IN',$condition['ids']);
        }

        return M("Pubmeitu")->where($map)->alias("a")
                         ->field("a.id,a.title,a.uname,a.tags,a.description,a.keyword,a.is_single,a.time,a.visible,a.init_visible,a.createtime,g.img_path")
                         ->join("LEFT JOIN qz_pubmeitu_img as g on g.caseid = a.id")
                         ->limit($pageIndex.",".$pageCount)
                         ->order("a.id desc")
                         ->group("a.id")
                         ->select();
    }


    /**
     * [getPubmeituImg 获取美图的图片列表]
     * @return
     */
    public function getPubmeituImg($id)
    {
        $map['caseid'] = $id;
        return M("Pubmeitu_img")->where($map)->select();
    }


    /**
     * [getPubmeituListCount 获取公装美图案例数]
     * @param  [type]
     * @param  [type]
     * @return [type]       [description]
     */
    public function getPubmeituListCount($condition)
    {
        if(!empty($condition['title']) || !empty($condition['id'])){
            if(!empty($condition['title'])){
                $map['title']  = array('like' , "%".$condition['title']."%");
            }
            if(!empty($condition['id'])){
                $map['id'] = $condition['id'];
            }
        }else{
            if(!empty($condition['lx'])){
                $map['location'] = $condition['lx'];
            }
            if(!empty($condition['fg'])){
                $map['fengge'] = $condition['fg'];
            }
            if(!empty($condition['mj'])){
                $map['mianji'] = $condition['mj'];
            }
        }

        if($condition['is_choice'] == '1' && !empty($condition['ids'])){
            $map['id'] = array('IN',$condition['ids']);
        }
        if($condition['is_choice'] == '2' && !empty($condition['ids'])){
            $map['id'] = array('NOT IN',$condition['ids']);
        }
        return M("Pubmeitu")->where($map)->alias("a")->count();
    }

    /**
     * [getOneAtt 获得公装美图]
     * @return
     */
    public function getOneAtt($id)
    {
        $map['id'] = $id;
        return M("Pubmeitu_att")->where($map)->find();
    }

    /**
     * [getAttlist 根据属性获取列表]
     * @return [type]       [description]
     */
    public function getAttlist($condition,$pageIndex,$pageCount)
    {
        $map = array(
            "type"=>array("EQ",$condition['type'])
                     );
        if(!empty($condition['keyword'])){
            $map['name'] = array('like' , '%'.$condition['keyword'].'%');
        }

        return M("Pubmeitu_att")->where($map)->order("enabled desc, px asc")
                                ->limit($pageIndex.",".$pageCount)
                                ->select();
    }

    /**
     * [getAttlist 根据属性获取列表]
     * @return [type]       [description]
     */
    public function getAttlistCount($condition,$pageIndex,$pageCount)
    {
        $map = array(
            "type"=>array("EQ",$condition['type'])
                     );
        if(!empty($condition['keyword'])){
            $map['name'] = array('like','%'.$condition['keyword'].'%');
        }else{
            $map['enabled'] = array("EQ",1);
        }
        return M("Pubmeitu_att")->where($map)->order("px asc")->count();
    }

    /**
     * [getPubmeitulocation 获取美图类型]
     * @return [type]       [description]
     */
    public function getPubmeitulocation()
    {
        $map = array(
            "type"=>array("EQ",1),
            "enabled"=>array("EQ",1)
                     );
        return M("Pubmeitu_att")->where($map)->order("px asc")->select();
    }

    /**
     * [getPubmeitufengge 获取美图风格]
     * @return [type]       [description]
     */
    public function getPubmeitufengge()
    {
        $map = array(
            "type"=>array("EQ",2),
            "enabled"=>array("EQ",1)
                     );
        return M("Pubmeitu_att")->where($map)->order("px asc")->select();
    }

    /**
     * [getPubmeitumianji 获取美图面积]
     * @return [type]       [description]
     */
    public function getPubmeitumianji()
    {
        $map = array(
            "type"=>array("EQ",3),
            "enabled"=>array("EQ",1)
                     );
        return M("Pubmeitu_att")->where($map)->order("px asc")->select();
    }



    /**
     * 删除公装美图
     * @return [type] [description]
     */
    public function delPubmeitu($id)
    {
        $map = array(
                "id"=>array("EQ",$id)
                     );
        $map_img = array(
                "caseid"=>array("EQ",$id)
                     );
        $result = M("Pubmeitu_img")->where($map_img)->delete();
        if(false === $result){
            return false;
        }
        return M("Pubmeitu")->where($map)->delete();
    }

    /**
     * 获取设计师
     * @return [type] [description]
     */
    public function getDesigner()
    {
        return M("meitu_designer")->field("name,uid,comname")->where($map)->order("enabled desc,px desc")->select();
    }

    /**
     * 删除公装美图的图片
     * @return [type] [description]
     */
    public function delImg($id,$key)
    {
        $map = array(
            "id"=>array("EQ",$id),
            "img_path"=>array("EQ",$key)
                     );
        return M('Pubmeitu_img')->where($map)->delete();
    }

    /**
     * [getMeituImgById 根据id获取美图]
     * @param  [type] $id [美图id]
     * @return [type]     [description]
     */
    public function getMeituImgById($id){
        if (!empty($id)) {
            $result = M('pubmeitu_img')->where(array('id' => $id))->find();
            return $result;
        }
        return false;
    }

    /**
     * [editPubmeituAtt 保存公装美图的属性]
     * @return
     */
    public function delPubmeituAtt($id,$on)
    {
        $map = array(
                "id"=>array("EQ",$id)
                     );
        if($on == "0"){
            $data['enabled'] = "1";
        }else{
            $data['enabled'] = "0";
        }

        return M("Pubmeitu_att")->where($map)->save($data);
    }




    /**
     * [editPubmeitu 保存公装美图]
     * @return
     */
    public function editPubmeitu($id,$data)
    {
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return M("Pubmeitu")->where($map)->save($data);
    }

    /**
     * [editPubmeituImg 保存公装美图的图片]
     * @return
     */
    public function editPubmeituImg($id,$data)
    {
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return M("Pubmeitu_img")->where($map)->save($data);
    }

    /**
     * [editPubmeituAtt 保存公装美图的属性]
     * @return
     */
    public function editPubmeituAtt($id,$data)
    {
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return M("Pubmeitu_att")->where($map)->save($data);
    }


    /**
     * [getBiaotiByName 根据标题名获取标题]
     * @param  [type]  $name  [标题名]
     * @param  integer $limit [获取条数]
     * @param  string  $istop [是否推荐]
     * @param  string  $order [排序]
     * @return [type]         [标题数组]
     */
    public function getBiaotiByName($title,$limit = 15,$istop = '',$order = 'istop DESC'){
        $map['title'] =  array('EQ',$title);
        if(!empty($istop) && ($istop == 1 || $istop == 0)){
            $map['istop'] = intval($istop);
        }
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('Pubmeitu')->where($map)->find();
        if (!empty($complete) && !empty($limit)) {
            $limit = $limit - 1;
        }

        //重新定义名字查询条件
        $map['title'] =  array('like','%'. $title .'%');
        $result = M('Pubmeitu')->where($map)->limit($limit)->order($order)->select();
        if (!empty($complete)) {
            array_unshift($result, $complete);
        }
        return $result;
    }

}