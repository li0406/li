<?php


namespace Home\Model;
Use Think\Model;

class BaiduReplyModel extends Model
{

    public function addReply($data)
    {
        return M("baidu_reply")->add($data);
    }

    public function editReply($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("baidu_reply")->where($map)->save($data);
    }

    public function getKeyWordList()
    {
        $map = array(
            "a.event" => array("EQ","keyword")
        );
        return M("baidu_reply")->where($map)->alias("a")
                        ->join("left join qz_baidu_reply_keyword b on a.id = b.reply_id")
                        ->field("a.id, a.rule,group_concat(b.keyword) as keyword,a.content,a.msgtype")
                        ->group("a.id")
                        ->order("a.id desc")
                        ->select();
    }

    /**
     * 获取回复
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public function getReplyByEvent($event)
    {
        $map = array(
            "event" => array("EQ",$event)
        );
        return M("baidu_reply")->where($map)->find();
    }

    /**
     * 删除回复
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function removeReplyById($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("baidu_reply")->where($map)->delete();
    }

    /**
     * 删除关键字
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function removeReplyKeyWord($id)
    {
        $map = array(
            "reply_id" => array("EQ",$id)
        );
        return M("baidu_reply_keyword")->where($map)->delete();
    }

    /**
     * 根据ID查询关键字
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findReplyById($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        return M("baidu_reply")->where($map)->alias("a")
                        ->join("left join qz_baidu_reply_keyword b on a.id = b.reply_id")
                        ->field("a.id, a.rule,group_concat(b.keyword) as keyword,a.content,a.msgtype")
                        ->group("a.id")
                        ->find();

    }

    /**
     * 根据事件类型删除数据
     * @param  [type] $event [事件类型]
     * @return [type]        [description]
     */
    public function delReplyByEvent($event)
    {
        $map = array(
            "event" => array("EQ",$event)
        );
        return M("baidu_reply")->where($map)->delete();
    }

    public function addAllKeyWord($data)
    {
        return M("baidu_reply_keyword")->addAll($data);
    }
}