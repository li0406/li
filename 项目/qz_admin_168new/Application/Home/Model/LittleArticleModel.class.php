<?php


namespace Home\Model;
Use Think\Model;

/**
* 分站文章
*/
class LittleArticleModel extends Model
{

    protected $autoCheckFields = false;

    protected $_validate = array(
        array('title','require','请填写文章标题',1,"",1),
        array('classid','require','请选择文章类型',1,"",1),
        array('content','require','请填写文章内容',1,"",1),
        array('cs','require','请选择分站城市',1,"",1),
        array('face','require','请上传封面图片',1,"",1),
        array('description','require','请填写文章描述',1,"",1),
         array('keywords','require','请填写关键字',1,"",1)
    );


    public function getArticleListCount($title,$city,$id,$state=0)
    {
        if (!empty($title)) {
            $map["a.title"] = array("LIKE","%$title%");
        }

        if (!empty($city)) {
            $map["a.cs"] = array("EQ",$city);
        }

        if (!empty($id)) {
            $map["a.id"] = array("EQ",$id);
        }

        if (!empty($state)) {
            $map["a.state"] = array("EQ",$state);
        }

        return M("little_article")->where($map)->alias("a")
                                       ->join("join qz_infotype as b on a.classid = b.id and b.enabled = 1")
                                       ->count();
    }

    public function getArticleList($pageIndex,$pageCount,$title,$city,$id,$state=0)
    {
        if (!empty($title)) {
            $map["a.title"] = array("LIKE","%$title%");
        }

        if (!empty($city)) {
            $map["a.cs"] = array("EQ",$city);
        }

        if (!empty($id)) {
            $map["a.id"] = array("EQ",$id);
        }

        if (!empty($state)) {
            $map["a.state"] = array("EQ",$state);
        }

        $buildSql = M("little_article")->where($map)->alias("a")
                                       ->field("a.*,b.name as typename,b.shortname")
                                       ->join("join qz_infotype as b on a.classid = b.id and b.enabled = 1")
                                       ->limit($pageIndex.",".$pageCount)
                                       ->order("a.id desc")
                                       ->buildSql();
        $buildSql = M("little_article")->table($buildSql)->alias("t")
                           ->join("join qz_adminuser as u on u.id = t.authid")
                           ->join("join qz_quyu as q on q.cid = t.cs")
                           ->field("t.*,u.name,q.cname,q.bm")
                           ->buildSql();
        return M("little_article")->table($buildSql)->alias("t1")
                                  ->join("LEFT JOIN qz_tags as t on FIND_IN_SET(t.id,t1.tags)")
                                  ->field("t1.*,GROUP_CONCAT(t.name) as tagname")
                                  ->group("t1.id")
                                  ->order("t1.id desc")
                                  ->select();
    }

    /**
     * 获取文章类型
     * @return [type] [description]
     */
    public function getArticleClassList()
    {
        $map = array(
            "type"=>array("EQ",2),
            "enabled"=>array("EQ",1)
        );
        return M('infotype')->where($map)->select();
    }

    /**
     * 添加文章
     * @param [type] $data [description]
     */
    public function addArticle($data)
    {
        return M("little_article")->add($data);
    }

    /**
     * 编辑文章
     * @param [type] $data [description]
     */
    public function editArticle($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("little_article")->where($map)->save($data);
    }

    /**
     * 查询文章信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findArticleInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        $buildSql = M("little_article")->where($map)->alias("a")
                                  ->join("join qz_quyu as q on q.cid = a.cs")
                                  ->field("a.*,q.cname")
                                  ->buildSql();
        return M("little_article")->table($buildSql)->alias("t")
                           ->join("left join qz_tags tag on FIND_IN_SET(tag.id,t.tags)")
                           ->group("t.id")
                           ->field("t.*,GROUP_CONCAT(tag.name) as tagnames")
                           ->find();

    }

    /**
     * 查询文章信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findArticleInfoV2($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        $buildSql = M("little_article")->where($map)->alias("a")
            ->join("join qz_quyu as q on q.cid = a.cs")
            ->field("a.*,q.cname")
            ->buildSql();
        return M("little_article")->table($buildSql)->alias("t")
            ->join('left join qz_sub_tag_article article_tag on article_tag.article_id = t.id ')
            ->join("left join qz_sub_tag tag on article_tag.tag_id = tag.id")
            ->group("t.id")
            ->field("t.*,GROUP_CONCAT(tag.name) as sub_tagnames,GROUP_CONCAT(tag.id) as sub_tags")
            ->find();

    }

    /**
     * 分站文章统计
     * @return [type] [description]
     */
    public function getArticleStat()
    {
        $sql = 'select q.cid, q.cname ,count(b.id) as count from qz_quyu q
                left join qz_little_article b on q.cid = b.cs
                where q.is_open_city = 1
                group by q.cid order by count desc';
        return M()->query($sql);
    }

    /**
     * 根据类型ID获取文章类型
     * @param  [string] $classid [类型ID]
     * @return [array]           [类型数组]
     */
    public function getArticleClassById($classid)
    {
        $map = array(
            "type"=>array("EQ",2),
            "enabled"=>array("EQ",1),
            "id"=>array("EQ",$classid),
        );
        return M('infotype')->where($map)->select();
    }

    /**
     * 根据城市ID获取城市BM
     * @param  [string] $cityid  [城市ID]
     * @return [array]           [类型数组]
     */
    public function getCityBmById($cityid)
    {
        $map = array(
            "cid"=>array("EQ",$cityid)
        );
        return M('quyu')->where($map)->select();
    }

    public function getListByTime($start,$end)
    {
        $map = array(
            "createtime" => array(
                array("EGT",$start),
                array("ELT",$end),
            )
        );
        return  M("little_article")->where($map)->field("id,title")->select();

    }
}