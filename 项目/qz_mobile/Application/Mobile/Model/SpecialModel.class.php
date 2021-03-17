<?php
/**
 *  美图专题管理
 */
namespace Mobile\Model;
use Think\Model;

class SpecialModel extends Model{

    protected $tableName = 'meitu_zt';
    protected $autoCheckFields = false;

    //取列表
    public function getList($map,$pagesize= 1,$pageRow = 10){
        $count  = M('meitu_zt')->where($map)->count();
        $result = M('meitu_zt')   
                            ->where($map)
                            ->order("time DESC")
                            ->limit($pagesize.",".$pageRow)
                            ->select();

        return array("result"=>$result,"count"=>$count);
    }

    //取单个专题
    public function getSpecial($id){
        $map['id'] = $id;
        return M('meitu_zt')->where($map)->find();
    }

    //取专题下的内容
    public function getSpecialItem($map){
        return M("meitu_zt_item")->where($map)->select();
    }

    //查询 案例标题
    public function getArticleListss($ids){
        $map['id'] = array('IN',$ids);
        return M('www_article')->field('id,title,face')->where($map)->select();
    }

    //获取攻略列表
    public function getArticleList($ids){
        $map = array(
            'a.id' => array('IN',$ids)
        );
        return M("www_article")->where($map)
                               ->alias("a")
                               ->join('INNER JOIN qz_www_article_class_rel as b on a.id = b.article_id')
                               ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                               ->field("a.id,a.title,a.face,c.shortname")
                               ->select();
    }

    //获取美图列表
    public function getMeituList($ids){
        $map = array(
            'id' => array('IN',$ids)
        );
        $buildSql = M("meitu")->where($map)->order("id desc")->buildSql();
        $buildSql = M("meitu")->table($buildSql)->alias("t")
                              ->join("INNER JOIN qz_meitu_img as f on f.caseid = t.id")
                              ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                              ->buildSql();
        return M("meitu")->table($buildSql)->alias("t1")
                         ->group("t1.id")
                         ->order("img_on desc,px,id")
                         ->select();
    }

    //获取案例列表
    public function getCaseList($ids){
        $map = array(
            'a.id' => array('IN',$ids)
        );
        $buildSql = M("cases")->alias('a')
                    ->join("INNER JOIN qz_quyu as q on q.cid = a.cs")
                    ->join("LEFT JOIN qz_fengge as g on g.id = a.fengge")
                    ->join("LEFT JOIN qz_huxing as h on h.id = a.huxing")
                    ->field('a.id,a.title,a.mianji,q.bm,g.name as fengge,h.name AS huxing')
                    ->where($map)
                    ->buildSql();

        $buildSql = M("cases")->table($buildSql)->alias("t")
                      ->join("INNER JOIN qz_case_img as f on f.caseid = t.id")
                      ->field("t.*,f.img_path,f.img_host,f.img_on,f.px")
                      ->buildSql();

        return M("cases")->table($buildSql)->alias("t1")
                     ->group("t1.id")
                     ->order("img_on desc,px,id")
                     ->select();

    }

    //取列表
    public function getBannerList($map){
        return M('meitu_zt_banner')->where($map)->order("time DESC")->limit(5)->select();
    }

}