<?php

namespace Home\Model;
use Think\Model;

/**
*
*/
class UserNoticeRelatedModel extends Model
{
    /**
     * 添加关联
     * @param [type] $data [description]
     */
    public function addRelated($data)
    {
        return M("user_notice_related")->add($data);
    }

    /**
     * 添加关联
     * @param [type] $data [description]
     */
    public function addAllRelated($data)
    {
        return M("user_notice_related")->addAll($data);
    }

    /**
     * 删除公告关联
     * @param  string $noticleId [description]
     * @return [type]            [description]
     */
    public function delRelated($noticleId='')
    {
        $map = array(
            "noticle_id" => array("EQ",$noticleId)
                     );
        return  M("user_notice_related")->where($map)->delete();
    }

    /**
     * 获取公告/站内信的已读信息
     * @param  [type] $noticleId [description]
     * @return [type]            [description]
     */
    public function getReadRelatedInfo($noticleId)
    {
        $map = array(
            "noticle_id" => array("EQ",$noticleId),
            "isread" => array("EQ",1)
                     );
        return  M("user_notice_related")->where($map)->field("uid")->select();
    }

    /**
     * 获取公告/站内信的信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getNoticeRelated($noticleId)
    {
        $map = array(
            "a.noticle_id" => array("EQ",$noticleId)
                     );
        return  M("user_notice_related")->where($map)->alias("a")
                                        ->join("INNER JOIN qz_user as b on a.uid = b.id")
                                        ->join("INNER JOIN qz_quyu as q on q.cid = b.cs")
                                        ->field("q.cid,a.uid,b.name,b.jc,b.classid,b.on")
                                        ->select();
    }
}