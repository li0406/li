<?php
/**
 *  文章类别表 qz_www_article_class
 */
namespace Common\Model;
use Think\Model;
class WwwArticleClassModel extends Model{
    private $articleClass = null;
    public function _initialize(){
         $this->articleClass = S("Cache:Article:Class");
         if(!$this->articleClass){
            $map = array(
                    "is_new"=>array("EQ",1),
                    "obsolete"=>array("EQ",0)
                         );
            $class = M("www_article_class")->order("pid,level,id")->where($map)->select();
            $class = $this->getArticleClass($class);
            $this->articleClass = $class;
            S("Cache:Article:Class",$class,3600*24);
         }
    }

    /**
     * 获取所有文章的分类编号
     * @return [type] [description]
     */
    public function getAllArticleClass(){
        $ids = array();
        foreach ($this->articleClass as $key => $value) {
            foreach ($value["ids"] as $k => $val) {
                $ids[] =$val;
            }
        }
        return array_unique($ids);
    }

    /**
     * 根据ID获取该分类及子类信息非树模式
     * @return [type] [description]
     */
    public function getArticleClassById($id){
        return $this->articleClass[$id];
    }

    private function getArticleClass($list,$parent=0){
        $tree = array();
        foreach ($list as $row){
            $nodes[$row['id']]   = $row;
        }
        $id =0;
        foreach ($nodes as $k => $v) {
            if ($v['pid'] == '0') {
                $tree[$v['id']] = $v;
            }else{
                if($v["level"] == 2){
                    $p  = $nodes[$v['pid']];
                    $tree[$p['id']]["ids"][] = $v["id"];
                    $tree[$p['id']]["child"][$v["id"]] = $v;
                }else{
                    // //3级分类
                    $p1  = $nodes[$v['pid']];
                    $p2  = $nodes[$p1['pid']];
                    $tree[$p2['id']]["child"][$p1["id"]]["ids"][] = $v["id"];
                    $tree[$p2['id']]["child"][$p1["id"]]["names"][] = $v["classname"];
                    $tree[$p2['id']]['ids'][] = $v['id'];

                    $tree[$p2['id']]["child"][$p1["id"]]["child"][$v["id"]] = $v;
                }
            }
        }
        return $tree;
    }

    /**
     * 根据类别简称获取类别信息
     * @param  [type] $shortname [description]
     * @return [type]            [description]
     */
    public function getArticleClassByShortname($shortname){
        $map = array(
                "shortname" => array("EQ",$shortname),
                "obsolete" => array('EQ',0)
                     );
        $result = M("www_article_class")->where($map)->find();
        if(!empty($result['pid'])){
            $result['parent'] = M("www_article_class")->where(['id'=>$result['pid'],'obsolete'=>0])->find();
            if(!empty($result['parent']['pid'])){
                $result['parent']['parent'] = M("www_article_class")->where(['id'=>$result['parent']['pid'],'obsolete'=>0])->find();
            }
        }
        return $result;
    }


    /**
     * 根据类别id获取类别信息
     * @param  [type] $id        [description]
     * @return [type]            [description]
     */
    public function getArticleClassByCatagoryId($id){
        $map = array(
                "id" => array("EQ",$id),
                "obsolete" => array('EQ',0)
                     );
        $result = M("www_article_class")->where($map)->find();
        if(!empty($result['pid'])){
            $result['parent'] = M("www_article_class")->where(['id'=>$result['pid'],'obsolete'=>0])->find();
            if(!empty($result['parent']['pid'])){
                $result['parent']['parent'] = M("www_article_class")->where(['id'=>$result['parent']['pid'],'obsolete'=>0])->find();
            }
        }
        return $result;
    }

    /**
     * 获取老站的目录
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getOldArticleClassId($id){
        $map = array(
                "id"=>array("EQ",$id),
                "pid"=>array("EQ",$id),
                "_logic"=>"OR"
                     );
        return M("www_article_class")->where($map)->field("id")->select();
    }

    /**
     * 获取所有老的文章分类
     * @return [type] [description]
     */
    public function getAllOldArticleClassId(){
        $map = array(
                    "is_new"=>array("EQ",0),
                    "obsolete"=>array("EQ",0)
                         );
        return M("www_article_class")->order("id")->where($map)->select();
    }

    /**
     * 根据文章的编号获取文章分类信息
     * @param int id 文章id
     * @param str type old老文章分配, new 新文章分类
     * @return mysql
     */
    public function getArticleClassByArticleId($id,$type="old"){
        $map = array(
                "b.article_id"=>array("EQ",$id)
                     );
        switch ($type) {
            case 'old':
                $map["a.id"] = array("ELT","86");
                break;
            case 'new':
                $map["a.id"] = array("GT","86");
                break;
            default:
                # code...
                break;
        }
        return M("www_article_class")->where($map)->alias("a")
                                    ->join("INNER JOIN qz_www_article_class_rel as b on a.id = b.class_id")
                                    ->find();
    }

    public function getArticleClassByIds($ids)
    {
        $where = [
            'rel.article_id' => ['in', $ids]
        ];
        return M('www_article_class_rel')->field('rel.article_id,c.shortname')->alias('rel')
            ->join('qz_www_article_class c on c.id = rel.class_id')
            ->where($where)
            ->select();
    }


    //
    public function getArticleClassIds($className)
    {
        $map = [
            "classname" => array("eq", $className),
            "obsolete" => array("eq", "0"),
            "is_new" => array("eq", "1"),
        ];

        $buildSql = M("www_article_class")      // 第一层
            ->where($map)
            ->field("id")
            ->limit(1)
            ->order("id desc")
            ->buildSql();

        $buildSql2 = M()->table($buildSql)->alias("t")
            ->field("ac1.id")
            ->join("INNER JOIN qz_www_article_class ac1 on ac1.pid = t.id and ac1.obsolete = '0' and ac1.is_new = '1'")
            ->buildSql();

        return M()->table($buildSql2)->alias("t2")
            ->field("ac2.id")
            ->join("INNER JOIN qz_www_article_class ac2 on ac2.pid = t2.id and ac2.obsolete = '0' and ac2.is_new = '1'")
            ->select();

    }


}