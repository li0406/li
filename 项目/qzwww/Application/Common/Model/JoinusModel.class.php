<?php
/**
 *  加盟表
 */
namespace Common\Model;
use Think\Model;
class JoinusModel extends Model{
    /**
     * 添加数据
     * @param [type] $data [description]
     */
    public function addUs($data){
        return M("joinus")->add($data);
    }

    /**
     * 查询申请人的申请信息
     * @param  [type] $name [申请人名称]
     * @param  [type] $tel  [联系电话]
     * @return [type]       [description]
     */
    public function findUsInfo($name,$tel){
        $map = array(
                    "name"=>I("post.name"),
                    "tel"=>I("post.tel")
                             );
        return M("joinus")->where($map)->count();
    }
}