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
    public function getUnSystemNoticeCount($uid){
        $map = array(
                "uid" => array("EQ",$uid),
                "isread" => array("EQ",0)
                     );
        return M("user_notice_related")->where($map)->count();

    }

    /**
     * 获取用户的消息数量
     * @param  string $uid [用户编号]
     * @param  string $cs [所在城市]
     * @return [type]     [description]
     */
    public function getSystemNoticeCount($uid="",$isread){
        $map = array(
            "uid" => array("EQ",$uid)
                     );
        if(!empty($isread)){
            $map["isread"] = array("EQ",0);
        }
        return M("user_notice_related")->where($map)->count();

    }

    /**
     * 获取用户的消息列表
     * @param  string $uid [用户编号]
     * @param  string $cs [所在城市]
     * @return [type]     [description]
     */
    public function getSystemNotice($uid="",$isread,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
            "uid" => array("EQ",$uid)
                     );
        if(!empty($isread)){
            $map["isread"] = array("EQ",0);
        }

        $buildSql = M("user_notice_related")->where($map)->buildSql();

        return M("user_notice_related")->table($buildSql)->alias("t")
                                            ->join("INNER JOIN qz_user_system_notice as b on b.id = t.noticle_id")
                                            ->field("b.id,b.title,b.time,t.isread")
                                            ->order("b.enabled desc,b.id desc")
                                            ->limit($pageIndex.",".$pageCount)
                                            ->select();
    }

    /**
     * 根据编号获取消息信息
     * @param  [type] $id  [消息编号]
     * @param  [type] $uid [用户编号]
     * @return [type]      [description]
     */
    public function getNoticeInfoById($id,$uid)
    {
        $map = array(
                "b.noticle_id"=>array("EQ",$id),
                "b.uid" => array("EQ",$uid)
                     );
        return M("user_system_notice")->where($map)->alias("a")
                                      ->join("INNER JOIN qz_user_notice_related as b on b.noticle_id = a.id")
                                      ->field("a.title,a.time,a.text,b.isread")
                                      ->find();
    }

    /**
     * 添加阅读标识
     * @param  [type] $id  [消息编号]
     * @param  [type] $uid [用户编号]
     * @return [type]      [description]
     */
    public function setRead($id,$uid)
    {
        $map = array(
                "noticle_id"=>array("EQ",$id),
                "uid" => array("EQ",$uid)
                     );
        $data = array(
            "isread" => 1
                      );
        return M("user_notice_related")->where($map)->save($data);
    }

    /**
     * 查询是否是自己的站内信
     * @param  [type] $id  [消息编号]
     * @param  [type] $uid [用户编号]
     * @return [type]      [description]
     */
    public function findMyNoticeCount($id,$uid)
    {
        $map = array(
                "noticle_id"=>array("IN",$id),
                "uid" => array("EQ",$uid)
                     );
        return M("user_notice_related")->where($map)->count();
    }

    /**
     * 删除站内信
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delNotice($id,$uid)
    {
        $map = array(
                "noticle_id"=>array("IN",$id),
                "uid" => array("EQ",$uid)
                     );
        return M("user_notice_related")->where($map)->delete();
    }
}