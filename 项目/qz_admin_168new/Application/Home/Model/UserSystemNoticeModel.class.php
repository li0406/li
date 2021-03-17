<?php

namespace Home\Model;
use Think\Model;

/**
*   用户中心公告表
*/
class UserSystemNoticeModel extends Model
{
    protected $autoCheckFields = false;
    protected $_validate = array(
        array('title','require','请填写标题',1,"",1),
        array('text','require','请填写发布内容',1,"",1)
    );
    /**
     * 添加公告
     */
    public function addNotice($data) {
       return M("user_system_notice")->add($data);
    }

    /**
     * 编辑公告
     * @param  [type] $id   [公告编号]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editNotice($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
                     );
        return M("user_system_notice")->where($map)->save($data);
    }

    /**
     * 获取公告列表的数量
     * @param  [type] $title [description]
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getNoticeListCount($title,$begin,$end)
    {
        $begin = strtotime($begin);
        $end = strtotime($end)-1;

        if (!empty($title)) {
            $map['title'] = array('LIKE',"%$title%");
        }

        if (!empty($begin) && !empty($end)) {
            $map['time'] = array("BETWEEN",array($begin,$end));
        }

        return M("user_system_notice")->where($map)->count();
    }

    /**
     * 获取公告/站内信列表
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $title     [description]
     * @param  [type] $begin     [description]
     * @param  [type] $end       [description]
     * @return [type]            [description]
     */
    public function getNoticeList($pageIndex,$pageCount,$title,$begin,$end)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $begin = strtotime($begin);
        $end = strtotime($end)-1;

        if (!empty($title)) {
            $map['title'] = array('LIKE',"%$title%");
        }

        if (!empty($begin) && !empty($end)) {
            $map['time'] = array("BETWEEN",array($begin,$end));
        }

        return M("user_system_notice")->where($map)->limit($pageIndex.",".$pageCount)->order("enabled desc,id desc")->select();
    }

    /**
     * 获取公告信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getNoticeInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id)
                     );
        return M("user_system_notice")->where($map)->find();
    }

    /**
     * 删除公告
     * @param  string $noticeId [description]
     * @return [type]           [description]
     */
    public function delNotice($noticeId='')
    {
        $map = array(
            "id" => array("EQ",$noticeId)
                     );
        return M("user_system_notice")->where($map)->delete();
    }
}