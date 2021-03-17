<?php
/**
 *  案例图片
 */
namespace Common\Model;
use Think\Model;
class CaseimgModel extends Model{
    protected $tableName = "case_img";

    /**
     * 添加案例图片
     */
    public function addImg($data){
        return M("case_img")->add($data);
    }

    /**
     * 删除案例图片
     * @return [type] [description]
     */
    public function removeImg($id,$path){
        $map = array(
                "id"=>array("EQ",$id),
                "img_path"=>array("EQ",$path)
                     );
        return M("case_img")->where($map)->delete();
    }

    /**
     * 编辑案例图片
     * @return [type] [description]
     */
    public function editCase($id,$data){
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return  M("case_img")->where($map)->save($data);
    }

}