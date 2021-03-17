<?php

namespace Common\Model\Db;

use Think\Model;

class CasesModel extends Model
{
    protected $autoCheckFields = false;


    //获取风格分类
    public function getFenggeCategory($comid = '', $cs = '')
    {
        $where['c.cs'] = ['eq', $cs];
        $where['c.uid'] = ['eq', $comid];
        $where['c.isdelete'] = ['in', [1, 3]];
        $where['c.on'] = ['eq', 1];
        return M('fengge')->alias('f')
            ->field('f.id,f.name')
            ->join('qz_cases as c on c.fengge = f.id')
            ->where($where)
            ->group('f.id')
            ->select();
    }

    private function getCaseMap($comid = '', $cs = '', $fengge = '', $class = '')
    {
        $map = array(
            "cs" => array("EQ", $cs),
            "uid" => array("EQ", $comid),
            "isdelete" => array("IN", array(1, 3)),
            "on" => array("EQ", 1),
        );

        if (!empty($fengge)) {
            $map["fengge"] = array("EQ", $fengge);
        }
        if (!empty($class)) {
            $map['classid']  = array(array('neq',4),array('eq',$class),'and');
        }else {
            //去掉类别为3d效果图
            $map['classid'] = ['NEQ', 4];

        }
        return $map;
    }

    /**
     * 获取案例图片列表
     * @param string $comid [公司编号]
     * @param string $cs [城市信息]
     * @param string $classid [类别]
     * @return [type]          [description]
     */
    public function getCasesListByComIdCount($comid = '', $cs = '', $fengge = '', $class = '')
    {
        $map = $this->getCaseMap($comid, $cs, $fengge, $class);
        return M("cases")->where($map)->count();
    }

    /**
     * 根据装修公司编号获取最新的案例
     * @param string $comid [公司编号]
     * @param string $cs [所在城市]
     * @param string $classid [案例类型]
     * @param string $on [审核状态]
     * @return [type]        [description]
     */
    public function getCasesListByComId($pageIndex = 0, $pageCount = 10, $comid = '', $cs = '', $fengge = "", $class = '')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = $this->getCaseMap($comid, $cs, $fengge, $class);

        $buildSql = M("cases")->where($map)
            ->order("time desc")
            ->limit($pageIndex . "," . $pageCount)
            ->field("id,title,mianji,fengge,zaojia,time,userid,huxing,leixing,isdelete,on,classid")
            ->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_jiage as b on b.id = t.zaojia")
            ->join("LEFT JOIN qz_fengge as c on c.id = t.fengge")
            ->join("LEFT JOIN  qz_user as d on d.id = t.userid")
            ->join("LEFT JOIN qz_huxing as e on e.id = t.huxing")
            ->field("t.*,b.name as jiage,c.name as fg,d.name as dname,e.name as hx")
            ->buildSql();
        $buildSql = M("cases")->table($buildSql)->alias("t1")
            ->field("t1.id as id,t1.on, t1.title,t1.mianji,t1.fg,t1.jiage,t1.time,d.img,d.img_host,d.img_path,t1.hx,t1.dname,t1.isdelete,t1.classid")
            ->join("LEFT JOIN qz_case_img as d on d.caseid = t1.id")
            ->order("d.img_on desc,d.px")
            ->buildSql();
        return M("cases")->table($buildSql)->alias("t2")
            ->group("t2.id")->order("time desc")
            ->select();
    }

    // 获取推荐装修案例列表
    public function getRecommendCasesList($city_id, $limit = 20, $offset = 0){
        $map = array(
            "c.cs" => array("EQ", $city_id),
            "c.isdelete" => array("IN", [1, 3]),
            "c.`on`" => array("EQ", 1),
        );

        $sql = M("cases")->alias("c")->where($map)
            ->field(["c.id", "c.title", "c.thumb", "c.time", "c.cs"])
            ->order("c.time desc")
            ->limit($offset, $limit)
            ->buildSql();

        $sql2 = M()->table($sql)->alias("t")
            ->join("inner join qz_case_img as i on i.caseid = t.id")
            ->field(["t.*", "i.img", "i.img_path", "i.img_host"])
            ->order("i.img_on desc,i.px")
            ->buildSql();

        return M()->table($sql2)->alias("t2")
            ->join("left join qz_quyu as q on q.cid = t2.cs")
            ->field(["t2.*", "q.bm"])
            ->order("t2.time desc")
            ->group("t2.id")
            ->select();
    }

}