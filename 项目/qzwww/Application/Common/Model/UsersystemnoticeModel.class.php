<?php
/**
 *  用户系统消息表
 */
namespace Common\Model;
use Think\Model;
class UsersystemnoticeModel extends Model{
    protected  $tableName="user_system_notice";

    /**
     * 获取用户未读的系统消息数量
     * @return [type] [description]
     */
    public function getUnSystemNoticeCount($id="",$cs="",$class=3){
        $map = array(
                "a.id"=>array("EQ",$id),
                "c.enabled"=>array("EQ",1),
                "c.classid"=>array(array("EQ",$class),array("EQ",""),"OR")
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("INNER JOIN qz_user_system_notice as c on FIND_IN_SET(a.cs,c.cs)")
                             ->field("a.id,c.id as cid")
                             ->buildSql();
        return M("user")->table($buildSql)->alias("t")
                        ->join("left join qz_log_user_system_notice as b on t.cid = b.noticeid and t.id = b.userid")
                        ->field("count(if(b.id is null,1,null)) as unreadsystem")
                        ->group("t.id")
                        ->find();
    }

    /**
     * 获取用户的消息数量
     * @param  string $id [description]
     * @param  string $cs [description]
     * @return [type]     [description]
     */
    public function getSystemNoticeCount($id="",$cs="",$class=3){
        $map = array(
                "a.id"=>array("EQ",$id),
                "c.enabled"=>array("EQ",1),
                "c.classid"=>array(array("EQ",$class),array("EQ",""),"OR")
                     );
        return  M("user")->where($map)->alias("a")
                         ->join("INNER JOIN qz_user_system_notice as c on FIND_IN_SET(a.cs,c.cs)")
                         ->count();
    }

    /**
     * 获取用户的消息列表
     * @param  string $id [用户编号]
     * @param  string $cs [所在城市]
     * @return [type]     [description]
     */
    public function getSystemNotice($id="",$cs="",$pageIndex,$pageCount,$class = 3)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "a.id"=>array("EQ",$id),
                "c.enabled"=>array("EQ",1),
                "c.classid"=>array(array("EQ",$class),array("EQ",""),"OR")
                     );
        $buildSql = M("user")->where($map)->alias("a")
                             ->join("INNER JOIN qz_user_system_notice as c on FIND_IN_SET(a.cs,c.cs)")
                             ->field("a.id,c.id as cid,c.time,c.title")
                             ->buildSql();

        return M("user")->table($buildSql)->alias("t")
                        ->join("left join qz_log_user_system_notice as b on t.cid = b.noticeid and t.id = b.userid")
                        ->field("t.id as uid,t.title,t.time,t.cid,b.id")
                        ->group("t.cid")
                        ->limit($pageIndex.",".$pageCount)
                        ->order("t.time desc")
                        ->select();
    }

    /**
     * 根据编号获取消息信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getNoticeInfoById($id){
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return M("user_system_notice")->where($map)
                                      ->find();
    }
}