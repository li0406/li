<?php


namespace Home\Model;
Use Think\Model;

/**
*  专题文章分类
*/
class ArticleSpecialClassModel extends Model
{
    protected $autoCheckFields = false;
    protected $_validate = array(
        array('classname','require','请填写分类名称',1,"",1),
        array('shortname','require','请填写分类缩写',1,"",1)
    );

    /**
     * 添加分类
     * @param [type] $data [description]
     */
    public function addClass($data)
    {
        return M("article_special_class")->add($data);
    }

    public function editClass($id, $data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("article_special_class")->where($map)->save($data);
    }

    /**
     * 获取所有的文章分类
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getAtricleClassList()
    {
        return M("article_special_class")->order("level,px")->select();
    }

    /**
     * 获取分类信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findClassInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("article_special_class")->where($map)->find();
    }
}