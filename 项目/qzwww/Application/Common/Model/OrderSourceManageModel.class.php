<?php


namespace Common\Model;

Use Think\Model;

/**
 *
 */
class OrderSourceManageModel extends Model
{
    protected $autoCheckFields = false;

    //根据用户组获取列表
    public function getSrcByGroup($group_id)
    {
        $map['groupid'] = ['eq',$group_id];
        $map['type'] = ['eq',1];  //推广的
        $map['visible'] = ['eq',0];    //显示的
        return M('order_source')->alias("a")->where($map)->field("id,src,name")->order('a.id DESC')->select();
    }

    /**
     * 根据查询条件获取列表
     * @param $map
     * @param $page
     * @param $count [int] 查询数量为0代表所有数据
     * @return mixed
     */
    public function getList($map,$page,$count = 0)
    {
        $result= M("order_source_manage")->alias("a")->field("a.*,g.name as group_name")
            ->join("left join qz_order_source_group as g on a.groupid = g.id")
            ->where($map);
        if ($count != 0){
            $result = $result->page("$page,$count");
        }
        $list = $result->select();
        $mycount= M("order_source_manage")->alias("a")->join("left join qz_order_source_group as g on a.groupid = g.id")->where($map)->count('a.id');
        return ['list'=>$list,'count'=>$mycount];
    }


    /**
     * 根据ID获取具体信息
     */
    public function getInfoById($id,$map = [])
    {
        if (!isset($map['id'])||!is_numeric($map['id'])){
            $map['id'] = ['eq',$id];
        }
        return M("order_source_manage")->where($map)->find();
    }


    /**
     * 根据src,path,templete获取具体信息
     */
    public function getInfoByMap($map = [])
    {
        return M("order_source_manage")->where($map)->find();
    }
}