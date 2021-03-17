<?php


namespace Common\Model;
use Think\Model;

class BaiduReplyModel extends Model
{
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

    public function getReplyByKeyWord($keyword)
    {
        $map = array(
            "a.keyword" => array("EQ",$keyword)
        );
        return M("baidu_reply_keyword")->where($map)->alias("a")
                        ->join("join qz_baidu_reply b on a.reply_id = b.id")
                        ->field("b.msgtype,b.content")
                        ->select();
    }
}