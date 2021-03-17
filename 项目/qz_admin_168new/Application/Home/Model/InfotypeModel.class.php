<?php
/**
 *  文章分类
 */
namespace Home\Model;
use Think\Model;
class InfotypeModel extends Model{
    /**
     * 获取文章类别信息
     * @param  integer $type [文章类别 1.装修公司文章类别 2.分站文章类别]
     * @return [type]        [description]
     */
    public function getTypes($type = 1){
        $map = array(
                "enabled"=>1,
                "type"=>array("EQ",$type)
                     );
        return M("infotype")->where($map)
                            ->order("px")
                            ->select();
    }

   /**
    * 根据文章类别获取文章信息
    * @param  [type] $shortname [名称缩写]
    * @return [type]            [description]
    */
    public function getTypeInfoByshortname($shortname){
        $map = array(
                "shortname"=>array("EQ",$shortname)
                     );
        return M("infotype")->where($map)->find();
    }
}