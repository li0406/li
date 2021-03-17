<?php

namespace Home\Model;

use Think\Model;

/**
*   文章分类
*/
class WwwArticleClassModel extends Model
{
    /**
    * 添加数据
    */
    public function addArticleClass($data){
        return M("www_article_class")->add($data);
    }

    /**
     * 更新文章分类
     * @return [type] [description]
     */
    public function editArticleClass($ids,$data){
        $map = array(
                        "id"=>array("IN",$ids)
                        );
        return M("www_article_class")->where($map)->save($data);
    }


    /**
     * [getArticleClassIdsByClass 更加分类id获取属于该分类的所有分类]
     * @param  [type] $groupid [description]
     * @return [type]          [description]
     */
    public  function getArticleClassIdsByClass($groupid)
    {
        $result = array();
        if (empty($groupid)){
            return  $result;
        }

        $map = array();
        $map["is_new"] = array("EQ",1);
        $map["_complex"] = array(
                "pid"=>array("EQ",$groupid),
                "id"=>array("EQ",$groupid),
                "_logic"=>"OR"
                        );
        $sub = M("www_article_class")->where($map)->select();
        if(count($sub) > 0){
            foreach ($sub as $key => $value) {
                $ids[] = $value["id"];
            }
            $submap = array();
            $submap["is_new"] = array("EQ",1);
            $submap["_complex"] = array(
                    "pid"=>array("IN",$ids),
                    "id"=>array("IN",$ids),
                    "_logic"=>"OR"
                            );

            $result = M("www_article_class")->where($submap)->select();
        }
        return  $result;
    }



    /**
     * 根据编号获取文章分类的信息
     * @return [type] [description]
     */
    public function getArticleClassById($id){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        return M("www_article_class")->where($map)->alias("a")
                            ->join("left join qz_content_category_participle b on a.id = b.category_id and b.module = 1 ")
                            ->field("a.*,GROUP_CONCAT(b.word SEPARATOR ';') as search_word")
                            ->group("a.id")
                            ->find();
    }



    /**
     * [getArticleClassTree 获取文章分类树]
     * @return [type]           [description]
     */
    public  function getArticleClassTree($extra = true)
    {
        $tree   = array();
        $map    = array();
        $map['is_new']=1;//只查询新增版本的分类
        $map['obsolete']=0;
        $list   = M('www_article_class')->where($map)
                                        ->field('id,pid,shortname,classname as text')
                                        ->order('id ASC')->select();
        $nodes  = array(
                '0' => array(
                    'id'    => 0,
                    'text'  => '栏目分类',
                    'state' => 'open'
                    ),
                );
        if (!$list){
            return  $nodes;
        }
        foreach ($list as &$row){
            $nodes[$row['id']]   = &$row;
        }
        unset($row);
        foreach ($nodes as $node) {
            $id = $node['id'];
            $nodes[$id]['attr'] = array( 'id' => $id,"data-name"=>$node["shortname"] );
            if (isset($nodes[$node['pid']])) {
                $p  =&$nodes[$node['pid']];
                $p['children'][]= &$nodes[$id];
            } else{
                //只有根分类才添加
                if($node['pid'] == 0){
                    $tree[] = &$nodes[$id];
                }
            }
        }

        if($extra){
            return $tree;
        }else{
            return $tree[0]['children'];
        }
    }

    // 取得主站文章分类
    public  function getArticleClassList()
    {
        $map    = array(
                'obsolete'  => 0,
                "is_new" =>array("EQ",1)
                );
        return  M('www_article_class')->where($map)
            ->order('pid,seq')->field('id,pid,classname as name,level')
            ->select();
    }

    // 按级别取得主站文章分类
    /*
     * @param int $pid              父级分类
     * @param int $level            分类级别
     * @param int $type             0老版分配, 1新版分类
     * @return array  $result       查询结果
     */
    public  function getArticleClassListByLevel($pid=0,$level=1,$type=1)
    {
        $map    = array(
                'level'  => $level,
                'obsolete'  => 0,
                'pid'  => $pid,
                "is_new" =>array("EQ",$type)
                );
        return  M('www_article_class')->where($map)
            ->order('id')->field('id,pid,classname as name,level')
            ->select();
    }

    // 获取分类下属的子分类
    /*
     * @param int $pid              父级分类
     * @param int $type             0老版分配, 1新版分类
     * @return array  $result       查询结果
     */
    public  function getChildClassList($pid=0,$type=1)
    {
        $map    = array(
                'obsolete'  => 0,
                'pid'  => $pid,
                "is_new" =>array("EQ",$type)
                );
        return  M('www_article_class')->where($map)
            ->order('id')->field('id,pid,classname as name,level')
            ->select();
    }

    /**
     * 获取分类最大id
     * @return [type] [description]
     */
    public function getArticleClassMaxId(){
        return M("www_article_class")->max("id");
    }

    /**
     * 根据文章的编号获取文章分类信息
     * @param int id 文章id
     * @param str type old老文章分配, new 新文章分类
     * @return mysql
     */
    public function getArticleClassByArticleId($id,$type = 'old'){
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
}