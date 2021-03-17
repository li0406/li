<?php
/**
 *  公司自主活动表 company_activity
 */
namespace Common\Model;
use Think\Model;
class OneselfModel extends Model{
    protected $tableName = "company_activity";

    /**
     * 根据装修公司获取活动信息列表
     * @param string $value [description]
     */
    public function getallEvent($cid,$condition,$pageIndex,$pageCount)
    {
        $map = array(
                "cid"=>array("EQ",$cid),
                "del"=>array("EQ",1)
                    );

        if($condition != ''){
            $map['types'] = array('EQ',$condition['types']);
            //正在进行时
            if ($condition['types'] == '1') {
                $map['types'] = array('EQ',$condition['types']);
                $map['state'] = array('EQ','1');
            }
            //暂停状态时
            elseif ($condition['types'] == '2') {
                $map['types'] = array('EQ',1);
                $map['state'] = array('EQ','0');
            }
        }

        return M("company_activity")->field('id,check,cid,title,end,start,types,time,state')
                                    ->where($map)
                                    ->order("time DESC")
                                    ->limit($pageIndex.",".$pageCount)
                                    ->select();
    }

    /**
     * 根据装修公司获取活动信息数量
     * @param string $value [description]
     */
    public function getallEventCount($cid,$condition)
    {
        $map = array(
                "cid"=>array("EQ",$cid),
                "del"=>array("EQ",1)
                    );
        return M("company_activity")->field('id,check,cid,title,end,start,types,time,state')
                                    ->where($map)
                                    ->count();
    }

    /**
     * 添加活动
     */
    public function addEvent($data){
        //M("info")->add($data);
        return M("company_activity")->add($data);
    }

    /**
     * 编辑活动
     * @param  [type] $id    [活动编号]
     * @param  [type] $comid [公司编号]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function editInfo($id,$cid,$data){
        $map = array(
                "id"=>array("EQ",$id),
                "cid"=>array("EQ",$cid)
                     );
        return M("company_activity")->where($map)->save($data);
    }


    /**
     * 根据装修公司获取某一活动信息
     * @param string $value [description]
     */
    public function getEventByCid($id,$cid)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
                'id'=>array('EQ',$id),
                "cid"=>array("EQ",$cid)
                    );
        return M("company_activity")->field('id,check,cid,text,title,end,start')->where($map)->select();
    }

    /**
     * 修改订单活动状态，暂停
     * @param  [type] $id    [活动编号]
     * @param  [type] $comid [公司编号]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function stopEvent($id,$cid,$data){
        $map = array(
                "id"=>array("EQ",$id),
                "cid"=>array("EQ",$cid)
                     );
        return M("company_activity")->where($map)->save($data);
    }

    /**
     * 删除活动,逻辑删除
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delEvent($id,$cid)
    {
        $map = array(
                "id"=>array("EQ",$id),
                "cid"=>array("EQ",$cid)
                     );
        $data['del'] = '0';
        return M("company_activity")->where($map)->save($data);
    }
}