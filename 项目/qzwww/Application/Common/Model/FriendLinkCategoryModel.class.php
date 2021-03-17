<?php
/**
 *  友情链接 qz_friend_link_category
 */
namespace Common\Model;

use Think\Model;

class FriendLinkCategoryModel extends Model{

    /**
     * [getFriendLinkCategoryList 获取友情链接分类]
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function getFriendLinkCategoryList($map)
    {
        if(empty($map)){
            $map['id'] = array('GT',0);
        }
        $result = M('friend_link_category')->where($map)->select();
        return $result;
    }
}