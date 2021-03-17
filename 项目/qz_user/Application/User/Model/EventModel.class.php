<?php

/**
 * 活动表
 */
namespace Common\Model;
use Think\Model;

class EventModel extends Model{

    protected $autoCheckFields = false;

      //获取列表
    public function getList($condition,$pageIndex,$pageCount){
        $Db = M('activity_city');
        $count  = $Db->alias("c")->join("left join qz_activity as a on c.aid = a.id")->where($condition)->count();
        $result = $Db->alias("c")->field('c.*,a.status as a_status,a.title,a.start_time,a.end_time,a.enroll_time,a.enroll_count')
                                ->join("left join qz_activity as a on c.aid = a.id")
                                ->where($condition)
                                ->limit($pageIndex.",".$pageCount)->select();
        return array("result"=>$result,"count"=>$count);
    }

    //检查真会员数量
    public function checkVipNum($cs){
        $map['classid'] = '3';
        $map['on'] = '2';
        $map['cs'] = $cs;
        return M('user')->where($map)->count();
    }

    //取已报名的活动 根据活动id
    public function getEnrollByAid($aid,$uid){
        $map = array(
            'uid' => array("EQ",$uid),
            'aid' => array("IN",$aid)
        );
        return M('activity_enroll')->field('*')->where($map)->select();
    }


    //取单个活动
    public function getById($id){
        $map = array(
            'id' => array("EQ",$id)
        );
        return M('activity')->field('*')->where($map)->find();
    }



    //获取列表
    public function getAllList($condition,$pageIndex,$pageCount){
        $Db = M('activity');
        $count  = $Db->where($condition)->count();
        $result = $Db->field('*')->where($condition)->order($orderby)->limit($pageIndex.",".$pageCount)->select();
        return array("result"=>$result,"count"=>$count);
    }


    //取真会员数大于10个的城市
    public function getVipCitys(){
        return M('user')->alias("a")
                        ->field('count(*) AS ucount,cs')
                        ->join("left join qz_user_company as c on c.fake ='0' and c.userid = a.id")
                        ->where("a.classid = '3' AND a.on = '2' ")
                        ->group('a.cs')
                        ->select();
    }

    //添加城市关联
    public function addActivityCity($data){
        return  M('activity_city')->add($data);
    }

    //获取招募城市
    public function getActivityCity($id,$status = ''){
        $map['c.aid'] = $id;
        if($status == '2'){
            $map['c.status'] = '1';
        }
        return M('activity_city')->alias("c")
                                ->field('c.*,q.cname,q.bm')
                                ->join("left join qz_quyu as q on q.cid = c.cid")
                                ->where($map)
                                ->order('c.counts DESC,q.bm ')
                                ->select();
    }

    //获取活动下单个城市报名的装修公司
    public function getCompanyList($aid,$cid){
        $map['e.aid'] = $aid;
        $map['e.cid'] = $cid;
        return M('activity_enroll')->alias("e")
                                ->field('e.*,u.user,u.jc')
                                ->join("left join qz_user as u on u.id = e.uid AND u.classid = '3' AND u.on = '2' ")
                                ->where($map)
                                ->order('e.time DESC')
                                ->select();
    }


}