<?php
/**
 *  案例评论
 */
namespace Common\Model;
use Think\Model;
class MessageModel extends Model{
    /**
     * 获取案例的评论列表数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCaseCommentListCount($id){
        $map = array(
                "caseid"=>array("EQ",$id)
                     );
        return M("message")->where($map)->count();
    }

    /**
     * 获取案例的评论列表
     * @param  [type] $id [案例编号]
     * @return [type]     [description]
     */
    public function getCaseCommentList($id,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "caseid"=>array("EQ",$id),
                "isverify"=>array("EQ",0)
                     );
        return M("message")->where($map)->alias("a")
                           ->join("LEFT JOIN qz_user as b on a.userid = b.id")
                           ->field("a.*,b.name")
                           ->order("checked desc,time desc")->limit($pageIndex.",".$pageCount)->select();
    }

    /**
     * 添加评论
     */
    public function addMessage($data){
        return M("message")->add($data);
    }
}