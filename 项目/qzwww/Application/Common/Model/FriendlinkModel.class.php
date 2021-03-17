<?php
/**
 *  友情链接 qz_friend_link
 */
namespace Common\Model;
use Think\Model;
class FriendlinkModel extends Model{
    protected $tableName = 'friend_link';

    /**
     * 获取友情链接的列表
     * @return [type] [description]
     */
    public function getFriendLinkList($cs = "000001",$type = 1,$page=""){
        $map = array(
            "a.cs"=>array("EQ", $cs),
            "a.show_class"=>array("EQ", $type),
            "a.show_on"=>array("EQ", 1),
            "a.link_page"=>array("EQ", $page)
        );

        return M("friend_link")->where($map)->alias("a")
           ->join("LEFT JOIN qz_quyu as b on find_in_set(b.cid,a.show_cs)")
           ->field("a.link_name,a.link_url,a.link_page,b.cname,b.bm")
           ->order("addtime desc")
           ->select();
    }
}