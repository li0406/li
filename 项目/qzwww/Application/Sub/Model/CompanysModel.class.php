<?php

//分站装修公司 Model

namespace Common\Model;
use Think\Model;

class CompanysModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 获取装修公司团队设计师
     * @return [type] [description]
     */
    public function getCompanyTeamList($comid='',$zw='',$zt ="",$pageIndex = 0,$pageCount=10){
        $map = array(
            "b.comid"=>array("EQ",$comid),
            "c.isdelete"=>array("EQ",1),
        );
        if(!empty($zw)){
            $map["zw"] = array("EQ",$zw);
        }
        if(!empty($zt)){
            $map["zt"] = array("EQ",$zt);
        }
        return M("user")->where($map)->alias("a")
                        ->field("a.id as uid,a.logo,a.name,b.zw,b.px,b.zt,b.xs,d.job,d.jobtime,d.fengge,d.text,count(c.id) as case_counts")
                        ->join("INNER JOIN qz_team as b on b.userid = a.id ")
                        ->join("LEFT JOIN qz_cases as c on c.userid = b.userid")
                        ->join("LEFT JOIN qz_user_des d on d.userid = b.userid")
                        ->group("a.id")
                        ->order("b.px desc,a.id desc")
                        ->limit($pageIndex.",".$pageCount)
                        ->select();
    }

    /**
     * 获取装修公司首页设计师列表
     * @return [type] [description]
     */
    public function getDesignerListByCompany($comid ='',$zw='',$zt ="",$pageIndex = 0,$pageCount=10){
        $map = array(
            "b.comid"=>array("EQ",$comid)
        );

        $buildSql = M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_team as b on b.userid = a.id and b.zt = 2")
                        ->field("a.*,b.zw,b.px,b.xs,b.zt")
                        ->order("b.px desc")
                        ->buildSql();
        return M("user")->table($buildSql)->alias("t")
                        ->join("LEFT JOIN qz_cases as b on t.id = b.userid and b.isdelete = 1 ")
                        ->join("LEFT JOIN qz_user_des as d on t.id = d.userid")
                        ->field("count(b.id) as case_counts,d.jobtime,d.linian,d.cost,t.*")
                        ->group("t.id ")
                        ->order("t.px desc")
                        ->limit($pageIndex.",".$pageCount)
                        ->select();

    }


    //获取列表
    public function getEventList($condition,$pageIndex,$pageCount){
        $Db = M('company_activity');

        $count  = $Db->alias("c")->join("")->where($condition)->count();
        $result = $Db->alias("c")->field('id,cid,title,text,start,end,time,check,types,del,state')
                                ->where($condition)
                                ->order("types desc,time desc")
                                ->limit($pageIndex.",".$pageCount)->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询单个
    public function getEvent($id){
        $map['check'] = array('EQ','1');
        $map['del'] = array('EQ','1');
        $map["id"] = array("EQ",$id);
        return M('company_activity')->field('*')->where($map)->find();
    }

    //更新浏览量
    public function updateEventViews($id){
        return M("company_activity")->where(array('id' => $id))->setInc('views');
    }

    /**
     * 获取用户的信息
     * @return [type] [description]
     */
    public function getVipUserInfoById($id){
        $map = array(
            "id" => array("EQ",$id)
                     );
        return  M("user")->where($map)->field("user_type,classid")->find();
    }

}