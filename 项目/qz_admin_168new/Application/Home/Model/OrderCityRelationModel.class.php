<?php
/**
 * 订单关联城市
 */
namespace Home\Model;

use Think\Model;

class OrderCityRelationModel extends Model
{
    /**
     * [saveRelation 保存城市订单相邻城市]
     * @param  [type] $cid  [城市ID]
     * @param  [type] $data [数组]
     * @return [type]       [description]
     */
    public function saveRelation($cid, $data)
    {
        $map['cid'] = intval($cid);
        if(!empty($map['cid'])){
            $result = M('order_city_relation')->where($map)->find();
            //判断该城市是否已有相邻城市，没有则新增，有则修改
            if(empty($result)){
                $data['cid'] = $map['cid'];
                $res = M('order_city_relation')->add($data);
            }else{
                $res = M('order_city_relation')->where($map)->save($data);
            }
            if($res){
                return $map['cid'];
            }
        }
        return false;
    }

    /**
     * [getRelationByCid 获取某城市订单相邻城市和该城市的相邻城市]
     * @param  [type] $cid [城市ID]
     * @return [type]      [description]
     */
    public function getRelationByCid($cid)
    {
        $cid = intval($cid);
        if(!empty($cid)){
            //查询出关联的城市
            $relation = M('order_city_relation')->where(array('cid' => $cid))->field('relation')->find();
            if(!empty($relation['relation'])){
                $cids = array_filter(explode(',', $relation['relation']));
            }

            //关联城市是否是数组
            if(is_array($cids)){
                array_push($cids, $cid);
            }else{
                $cids = array($cid);
            }

            $string = '';
            foreach ($cids as $key => $value) {
                $string = $string . 'FIND_IN_SET("' . $value . '",a.relation) OR ';
            }
            $string = rtrim($string, 'OR ');

            $map = array(
                            'a.cid'   => array('IN',$cids),
                            '_string' => $string,
                            '_logic' => 'OR'
                        );
            $result = M('order_city_relation')->alias('a')
                                        ->join('qz_quyu AS b ON b.cid = a.cid')
                                        ->field('a.cid,b.cname,a.relation,b.bm')
                                        ->where($map)
                                        ->select();
            return $result;
        }
        return false;
    }

    /**
     * 获取当前城市相邻的城市
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getRelationCity($cs)
    {
        $map = array(
            "a.cid" => array("EQ",$cs)
        );
        return M('order_city_relation')->where($map)->alias("a")
                                ->join("join qz_quyu as q on find_in_set(q.cid,a.relation)")
                                ->field("a.cid as cs,q.cname,q.cid")
                                ->select();

    }
}