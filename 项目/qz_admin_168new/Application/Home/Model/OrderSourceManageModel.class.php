<?php


namespace Home\Model;

Use Think\Model;

/**
 *
 */
class OrderSourceManageModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 进行数据保存
     * @param $data
     * @return bool+
     */
    public function saveData($data)
    {
        if (!isset($data['id'])&&!isset($data['create_time'])){
            $data['create_time'] = time();
        }
        if (!isset($data['update_time'])){
            $data['update_time'] = time();
        }
        
        if (isset($data['id'])){
            $result = M("order_source_manage")->where(['id'=>$data['id']])->save($data);
        }else{
            $srcArray = explode(',',$data['src']);
            $allData = [];
            foreach ($srcArray as $key =>$src ){
                $allData[] = array_merge($data,['src'=>$src]);
            }
            $result = M("order_source_manage")->addAll($allData);
        }
        return $result;
    }


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
    public function getList($map,$page,$count = 0,$order = '')
    {
        $result= M("order_source_manage")->alias("a")->field("a.*,g.name as group_name")
            ->join("left join qz_order_source_group as g on a.groupid = g.id")
            ->where($map);
        if ($count != 0){
            $result = $result->page("$page,$count");
        }
        if (!empty($order)){
            $result = $result->order($order);
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
            $map['a.id'] = ['eq',$id];
        }
        return M("order_source_manage a")->join('qz_order_source_group g ON g.id = a.groupid')->where($map)->field('a.*,g.category')->find();
    }

    /**
     * 根据ID删除具体信息
     */
    public function delInfoById($id)
    {
        $map['id'] = ['eq',$id];
        return M("order_source_manage")->where($map)->delete();
    }

    /**
     * 根据src,path,templete获取具体信息
     */
    public function getInfoByMap($map = [])
    {
        return M("order_source_manage")->where($map)->find();
    }

}