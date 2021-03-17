<?php

namespace Home\Model;
Use Think\Model;

/**
*   专题文章模块
*/
class ArticleSpecialModuleModel extends Model
{
    protected $autoCheckFields = false;

    protected $_validate = array(
        array('title','require','请填写标题',1,"",1),
        array('type','require','请选择类型',1,"",1),
        // array('cover_img','require','请上传封面图片',1,"",1)
    );

    /**
     * 添加专题模块
     */
    public function addModule($data)
    {
        return M("article_special_module")->add($data);
    }

    /**
     * 编辑模块
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editModule($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
                    );
        return M("article_special_module")->where($map)->save($data);
    }

    /**
     * 添加模块分类
     * @param string $value [description]
     */
    public function addModuleClass($data)
    {
        return M("article_special_module_class")->addAll($data);
    }

    /**
     * 删除模块的分类
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delModuleClass($module)
    {
        $map = array(
            "module" => array("EQ",$module)
        );
        return M("article_special_module_class")->where($map)->delete();
    }

    /**
     * 获取专题模块的列表数量
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function getSpeciAlarticleModuleCount($title)
    {
        if (!empty($title)) {
            $map["title"] = array("LIKE","%$title%");
        }
        return  M("article_special_module")->where($map)->count();
    }

    /**
     * 获取专题模块的列表
     */
    public function getSpeciAlarticleModuleList($pageIndex,$pageCount,$title)
    {
        if (!empty($title)) {
            $map["a.title"] = array("LIKE","%$title%");
        }

        return  M("article_special_module")->where($map)->alias("a")
                                           ->join("join qz_article_special_class as b on b.id = a.type")
                                           ->field("a.id,a.title,a.time,b.classname,a.isdelete")
                                           ->limit($pageIndex.",".$pageCount)->order("a.id desc")->select();
    }

    /**
     * 查询专题模块信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findArticleModuleInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        return  M("article_special_module")->where($map)->alias("a")
                                           ->join("join qz_article_special_class c on c.id = a.type")
                                           ->join("left join qz_article_special_module_class b on a.id = b.module")
                                           ->field("a.id,a.title,a.description,a.istop,a.isbanner,a.type,a.cover_img,a.cover_bigimg,b.type as subtype,b.title as subtitle,b.article_id,b.sort,b.subtitle as article_title")
                                           ->order("b.sort")
                                           ->select();
    }
}