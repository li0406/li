<?php

/**
 * 装修公司列表页
 */

namespace Common\Model;

use Think\Model;

class CompanyModel extends Model
{

    protected $autoCheckFields = false;


    public function getList($condition = '', $pagesize = 1, $pageRow = 10)
    {
        $map["a.classid"] = array("EQ", 3);
        $map["q.fake"] = array("EQ", 0);

        //城市
        if (!empty($condition['cs'])) {
            $map["a.cs"] = array("EQ", $condition['cs']);
        }

        //区县
        if (!empty($condition['qx'])) {
            $map["a.qx"] = array("EQ", $condition['qx']);
        }

        //认证
        if (!empty($condition['vip'])) {
            $map["a.on"] = array("EQ", 2);
            //$map['fake'] = array('EQ',0);
        }
        if (!empty($condition['fw'])) {
            $fw = $condition['fw'];
            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
        }
        //风格
        if (!empty($condition['fg'])) {
            $fg = $condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        //规模
        if (!empty($condition['gm'])) {
            $map["q.guimo"] = array("EQ", $condition['gm']);
        }

        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';
        //dump($map);

        $orderby = $condition['orderby'];
//        unset($condition['orderby']);

        $Db = M('user');
        $buildSql = $Db->where($map)->alias("a")
            ->field("a.`id`,a.on,a.jc,a.qc,a.cs,a.qx,a.logo,q.fengge,q.guimo,q.comment_count,q.comment_score,q.fake,a.activte_score,e.cname,a.case_count as anli_count")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
            ->join("INNER JOIN qz_quyu as e on e.cid = a.cs")
            ->join("INNER JOIN qz_cases as c on a.id = c.uid")
            ->order("`on` desc,q.fake," . $orderby . " desc")
            ->group('a.id')
            ->limit($pagesize . "," . $pageRow)
            ->buildSql();

        $result = $Db->table($buildSql)->alias("t1")
            ->field("t1.*")
            ->order("`on` desc,fake," . $orderby . " desc")
            ->select();

        return array("result" => $result);
    }

    public function getCompanyDetails($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M('user')->alias("a")
            ->join("left join qz_user_company as b on a.id = b.userid")
            ->join("LEFT JOIN qz_comment as g on g.comid = a.id and g.isveritfy = 0")
            ->field("a.id,a.on,a.cs,a.qc,a.logo,a.dz,a.kouhao,b.text as jianjie,b.img_host,avg(IF(g.sj<>0,g.sj,null)) avgsj,avg(IF(g.fw<>0,g.fw,null)) avgfw,avg(IF(g.sg<>0,g.sg,null)) avgsg,avg(IF(g.sj=0,g.count,null)) avgcount,count(IF(g.count>=9 and g.sj = 0,g.count,null)) as oldgood, count(IF(g.sj>=9 and g.fw>=9 and g.sg >=9,g.id,null)) as good,count(IF(g.sj <>0,g.id,null)) as newcount,count(IF(g.sj =0,g.id,null)) as oldcount")
            ->where( $map)->find();
    }

    public function getCompanyCases($id, $pageIndex = 0, $pageCount = 10)
    {
        $map = array(
            "uid" => array("EQ", $id),
            "isdelete" => array("IN", array(1, 3))
        );
        $buildSql = M("cases")->where($map)
            ->order("time desc")
            ->limit($pageIndex . "," . $pageCount)
            ->field("id,title,mianji,fengge,zaojia,time,userid,huxing,leixing,isdelete,on")
            ->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t")
            ->join("INNER JOIN qz_jiage as b on b.id = t.zaojia")
            ->join("INNER JOIN qz_fengge as c on c.id = t.fengge")
            ->join("LEFT JOIN  qz_user as d on d.id = t.userid")
            ->join("LEFT JOIN qz_huxing as e on e.id = t.huxing")
            ->join("LEFT JOIN qz_leixing as f on f.id = t.leixing")
            ->field("t.*,b.name as jiage,c.name as fg,d.name as dname,e.name as hx,f.name as lx")
            ->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t1")
            ->field("t1.id as id,t1.on, t1.title,t1.mianji,t1.fg,t1.jiage,t1.lx,t1.time,d.img,d.img_host,d.img_path,t1.hx,t1.dname,t1.isdelete")
            ->join("LEFT JOIN qz_case_img as d on d.caseid = t1.id")
            ->order("d.img_on desc,d.px")
            ->buildSql();
        return M("cases")->table($buildSql)->alias("t2")
            ->group("t2.id")->order("time desc")
            ->select();
    }

    /**
     * 获取用户信息列表数量
     * @param  string $keyword [关键字]
     * @param  string $cs [所在城市]
     * @return [type]          [description]
     */
    public function getCompanyCount($condition)
    {

        $map["classid"] = array("EQ", 3);

        if (!empty($condition['cs'])) {
            $map["cs"] = array("EQ", $condition['cs']);
        }
        if (!empty($condition['keyword'])) {
            $map['qc'] = array('LIKE', '%' . $condition['keyword'] . '%');
        }
        //认证
        if (!empty($condition['vip'])) {
            $map["on"] = array("EQ", 2);
            $map['fake'] = array('EQ', 0);
        }
        if (!empty($condition['fw'])) {
            $fw = $condition['fw'];
            $map["_string"] = "FIND_IN_SET($fw,q.quyu)";
        }
        if (!empty($condition['fg'])) {
            $fg = $condition['fg'];
            $map["_string"] = "FIND_IN_SET($fg,q.fengge)";
        }
        if (!empty($condition['gm'])) {
            $map["q.guimo"] = array("EQ", $condition['gm']);
        }

        $isSale = !empty($condition['sale']) ? "AND a.sales_count > '0'  " : '';

        $result = M('user')->where($map)->alias("a")
            ->field("a.`id`,a.on,a.qc,a.pv,a.uv,a.cs,a.logo,a.uptime,a.info_time,a.case_count,q.team_count,q.comment_count,q.comment_score,q.fake,a.sales_count")
            ->join("INNER JOIN qz_user_company as q on q.userid = a.id $isSale ")
            ->count();

        return $result;
    }
}