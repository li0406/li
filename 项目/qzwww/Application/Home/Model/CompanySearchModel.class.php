<?php

/**
 * 装修公司搜索页
 */

namespace Home\Model;
use Think\Model;

class CompanySearchModel extends Model{

    protected $autoCheckFields = false;


    public function getList($condition='',$pagesize= 1,$pageRow = 10){
        $map["classid"] = array("EQ",3);

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }

        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }
        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            //$map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = (int)$condition['fw'];
            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['gm'])){
            $map["q.guimo"] = array("EQ",$condition['gm']);
        }

        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';
        //dump($map);

        $orderby = $condition['orderby'];
        unset($condition['orderby']);

        $Db = M('user');

        $buildSql = $Db->where($map)->alias("a")
                    ->field("a.`id`,q.team_num,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,a.dz,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count,a.activte_score,e.bm")
                    ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                    ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
                    ->order("`on` desc,q.fake,".$orderby." desc")
                    ->limit($pagesize.",".$pageRow)
                    ->buildSql();
        $result = $Db->table($buildSql)->alias("t1")
                    ->field("t1.*")
                    ->group('t1.id')
                    ->order("`on` desc,fake,".$orderby." desc")
                    ->select();

        return array("result"=>$result);
    }


    /**
     * 获取用户信息列表数量
     * @param  string $keyword [关键字]
     * @param  string $cs      [所在城市]
     * @return [type]          [description]
     */
    public function getCompanyCount($condition){

        $map["classid"] = array("EQ",3);

        if(!empty($condition['cs'])){
            $map["cs"] = array("EQ",$condition['cs']);
        }
        if(!empty($condition['keyword'])){
            $map['qc']  = array('LIKE','%'.$condition['keyword'].'%');
        }
        //认证
        if(!empty($condition['vip'])){
            $map["on"] = array("EQ",2);
            $map['fake'] = array('EQ',0);
        }
        if(!empty($condition['fw'])){
            $fw = (int)$condition['fw'];
            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
        }
        if(!empty($condition['fg'])){
            $fg = (int)$condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if(!empty($condition['gm'])){
            $map["q.guimo"] = array("EQ",$condition['gm']);
        }

        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';

        $result = M('user')->where($map)->alias("a")
                    ->field("a.`id`,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count")
                    ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
                    ->count();

        return $result;
    }

    //取最新优惠活动
    public function getSaleInfo($row='5',$cityid = ''){
        if(!empty($cityid)){
            $map["cs"] = array("EQ",$cityid);
        }
        $map['type'] = '1';
        //$map['time'] = array('EGT',strtotime(date('Y-m-d',time()))  - 86400 * 30); //30天内的装修公司活动
        $buildSql = M('info')->field('id,title,cs')->order('time DESC')->limit("0,".$row)->where($map)->buildSql();
        $result = M('info')->table($buildSql)->alias("t")
                    ->field("t.*,q.bm")
                    ->join("LEFT JOIN qz_quyu as q on q.cid = t.cs")
                    ->select();
        return $result;
    }

}