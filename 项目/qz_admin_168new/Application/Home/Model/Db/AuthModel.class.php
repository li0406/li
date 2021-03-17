<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Db;

use Think\Model;

class AuthModel extends Model
{
    protected $tableName = "yxb_orders";

//    public function selectYxbOrder($order_no){
//        return M('yxb_orders')->where(['qz_order'=>$order_no])->select();
//    }
//    public function addOrders($data)
//    {
//        return M('yxb_orders')->addAll($data);
//    }
    public function getAllCityInfo($admin_id){
        $map = array(
            "a.type"=>array("EQ",1)
        );
        if (!empty($admin_id)) {
            $map_kf = array(
                "id"=>array("EQ",$admin_id)
            );
            $result_kf = M("adminuser")->where($map_kf)->find();

            if (!empty($result_kf['cs'])) {
                $cs = explode(',',trim($result_kf['cs'], ','));
                $map["a.cid"] = array("IN",$cs);
            }else{
                $map["a.cid"] = array("IN",'');
            }
        }
        //1.获取所有的城市信息
        $buildSql =  M("quyu")->where($map)->alias("a")
            ->join("left join qz_user as u on u.cs = a.cid and u.on = 2")
            ->field("a.*,count(u.id) as usercount")
            ->group("a.cid")
            ->buildSql();

        $list =  M("quyu")->table($buildSql)->where(array("b.type"=>array("EQ",1)))->alias("a")
            ->join("inner join qz_area as b on a.cid = b.fatherid and b.type = 1")
            ->join("inner join qz_province as c on c.qz_provinceid = a.uid")
            ->field("a.usercount, a.cid as cid,a.cname,a.uid,a.type,a.bm,a.px,a.px_abc,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,b.qz_areaid,b.qz_area,b.orders,c.qz_province,c.qz_bigpart,c.qz_bigpart_name,a.lng,a.lat")
            ->order("a.bm")->select();
        return $list;
    }


    public function getCommunityCount($where){
        //城市
        if(!empty($where["city"])&&isset($where["city"])){
            $map["city"] = array("EQ",$where["city"]);
        }
        //行政区
        if(!empty($where["area"])&&isset($where["area"])){
            $map["area"] = array("EQ",$where["area"]);
        }
        //小区
        if(!empty($where["xiaoqu"])&&isset($where["xiaoqu"])){
            $map["name"] = array("like",'%'.$where["xiaoqu"].'%');
        }
        //是否有坐标
        if(!empty($where["zuobiao"])&&isset($where["zuobiao"])){
            if($where["zuobiao"] == 1){
                //有坐标
                $map["longitude"] = array("NEQ",'');
                $map["latitude"] = array("NEQ",'');
            }else{
                //无坐标
                $map["longitude"] = array("EQ",'');
                $map["latitude"] = array("EQ",'');
            }

        }

        return M('community')->where($map)->count();
    }

    public function getCommunity($where,$page, $pageCount)
    {
        //城市
        if(!empty($where["city"])&&isset($where["city"])){
            $map["a.city"] = array("EQ",$where["city"]);
        }
        //行政区
        if(!empty($where["area"])&&isset($where["area"])){
            $map["a.area"] = array("EQ",$where["area"]);
        }
        //小区
        if(!empty($where["xiaoqu"])&&isset($where["xiaoqu"])){
            $map["a.name"] = array("like",'%'.$where["xiaoqu"].'%');
        }
        //是否有坐标
        if(!empty($where["zuobiao"])&&isset($where["zuobiao"])){
            if($where["zuobiao"] == 1){
                //有坐标
                $map["a.longitude"] = array("NEQ",'');
                $map["a.latitude"] = array("NEQ",'');
            }else{
                //无坐标
                $map["a.longitude"] = array("EQ",'');
                $map["a.latitude"] = array("EQ",'');
            }
        }

        $result = M("community")->alias('a')
            ->field('a.*,q.cname as cityname,ar.qz_area as areaname')
            ->join('left join qz_quyu as q on q.cid = a.city')
            ->join('left join qz_area as ar on ar.qz_areaid = a.area')
            ->where($map)
            ->limit($page,$pageCount)
            ->select();
       
        return $result;
    }

    public function getCommunityNew($where)
    {
        //城市
        if(!empty($where["city"])&&isset($where["city"])){
            $map["a.city"] = array("EQ",$where["city"]);
        }
        //行政区
        if(!empty($where["area"])&&isset($where["area"])){
            $map["a.area"] = array("EQ",$where["area"]);
        }
        //小区
        if(!empty($where["xiaoqu"])&&isset($where["xiaoqu"])){
            $map["a.name"] = array("like",'%'.$where["xiaoqu"].'%');
        }
        //是否有坐标
        if(!empty($where["zuobiao"])&&isset($where["zuobiao"])){
            if($where["zuobiao"] == 1){
                //有坐标
                $map["a.longitude"] = array("NEQ",'');
                $map["a.latitude"] = array("NEQ",'');
            }else{
                //无坐标
                $map["a.longitude"] = array("EQ",'');
                $map["a.latitude"] = array("EQ",'');
            }
        }

        $map["add_time"] = array('between',array($where["start"],$where["end"]));
        $map["read"] = array("EQ",1); //未读

        $result = M("community")->alias('a')
            ->field('a.*,q.cname as cityname,ar.qz_area as areaname')
            ->join('left join qz_quyu as q on q.cid = a.city')
            ->join('left join qz_area as ar on ar.qz_areaid = a.area')
            ->where($map)
            ->order('id desc')
            ->select();

        return $result;
    }


    /**
     * 修改成已读
     * @param $ids
     * @return bool
     */
    public function editRead($ids){
        $data["read"] = 2;
        if(is_array($ids)){
            $map["id"] = array("in",$ids);
        }else{
            $map["id"] = array("EQ",$ids);
        }
        return M('community')-> where($map)->save($data);
    }

    public function getCommunityWuyeType(){
        return M('community_wuye_type')->select();
    }

    /**用过小区名字和区域搜索小区个数
     * @param $xiaoqu
     * @param $city
     * @param $id
     * @return mixed
     */
    public function selectCommunity($xiaoqu,$city,$id=''){
        $map['name'] =  array("EQ",$xiaoqu);
        $map['city'] =  array("EQ",$city);
        if(!empty($id)&&isset($id)){
            $map['id'] =  array("NEQ",$id);
        }
        return M('community')->where($map)->count();
    }

    /**添加小区信息
     * @param $data
     * @return mixed
     */
    public function addCommunity($data){
        return M('community')->add($data);
    }

    /**
     * 修改小区信息
     * @param $data
     * @param $id
     * @return bool
     */
    public function editCommunity($data,$id){
        $map["id"] = array("EQ",$id);
        return M('community')-> where($map)->save($data);
    }

    /**通过id获取小区
     * @param $id
     * @return mixed
     */
    public function getOneArea($id){
        $map['id'] = array('EQ',$id);
        return M('community')-> where($map)->find();
    }

    /**获取区域内小区
     * @param $areaid
     */
    public function getcommunitybycity($areaid,$xiaoqu=''){
        $map['city'] = array('EQ',$areaid);
        if(!empty($xiaoqu)){
            $map['name'] = array('like','%'.$xiaoqu.'%');
        }
        return M('community')->field('type,longitude,latitude,name')->where($map)->limit(10)->select();
    }

    /**
     * 删除小区
     * @param $id
     */
    public function deleteArea($id){
        if(!empty($id)&&isset($id)){
            $map["id"] = array("EQ",$id);
            return M('community')->where($map)->delete();
        }

    }

}