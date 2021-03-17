<?php
/**
 *  评论回复表
 */
namespace Common\Model;
use Think\Model;
class ReplyModel extends Model{
    /**
     * 添加回复
     */
    public function addReply($data){
        return M("reply")->add($data);
    }

    /**
     * 删除评论
     * @param  [type] $id    [评论编号]
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function delReply($id,$comid){
        $map = array(
                "commid"=>array("EQ",$id),
                "userid"=>array("EQ",$comid)
                     );
        return M("reply")->where($map)->delete();
    }

    /**
     * 编辑评论
     * @param  [type] $id    [评论编号]
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function editReply($id,$comid,$data){
         $map = array(
                "commid"=>array("EQ",$id),
                "userid"=>array("EQ",$comid)
                     );
        return M("reply")->where($map)->save($data);
    }


     /**
     * 查询评论信息
     * @param  [type] $id    [评论编号]
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function getReplyByCommid($id,$comid){
        $map = array(
                "commid"=>array("EQ",$id),
                "userid"=>array("EQ",$comid)
                     );
        return M("reply")->where($map)->count();
    }
}