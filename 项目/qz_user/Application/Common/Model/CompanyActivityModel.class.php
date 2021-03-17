<?php

/**
 * 装修公司活动表
 */

namespace Common\Model;
use Think\Model;

class CompanyActivityModel extends Model{

    protected $autoCheckFields = false;

    /**
     * [getCompanyActiveListByIds 通过ids获取装修公司活动]
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getCompanyActiveListByIds($ids)
    {
        $map = array(
                     'cid'   => array('IN',$ids),
                     'start' => array('ELT',time()),
                     'end'   => array('EGT',time()),
                     'del'   => 1,
                     'state' => 1
                     );
        $result = M('company_activity')->field('id,cid,title')->where($map)->order('end')->select();
        $info = [];
        foreach ($result as $key => $value) {
            if(empty($info[$value['cid']])){
                $info[$value['cid']] = $value;
            }
        }
        return $info;
    }

    public function getCompanyActiveCountByIds($id)
    {
        $map = array(
            'cid'   => array('eq',$id),
//                     'start' => array('ELT',time()),
            'end'   => array('EGT',time()),
            'del'   => 1,
            'check'   => 1,
            'state' => 1
        );
        return M('company_activity')->field('id')->where($map)->order('end')->count();
    }

    public function getCompanyActiveInfoById($id)
    {
        return M('company_activity')->field('id,cid')->where(['id'=>$id])->order('end')->find();
    }


    //取最新优惠活动
    public function getCompanyActiveList($row='5',$cityid = ''){
        if(!empty($cityid)){
            $map["u.cs"] = array("EQ",$cityid);
        }
        $map['a.check'] = '1';
        $map['a.del'] = '1';
        $map['a.state'] = '1';
        //正在进行的活动
        $map['a.start'] = array('ELT',time());
        $map['a.end'] = array('EGT',time());
        $results = M('company_activity')->alias('a')
                                        ->field('a.id,a.title,q.bm')
                                        ->join('qz_user AS u ON u.id = a.cid')
                                        ->join('qz_quyu AS q ON q.cid = u.cs')
                                        ->limit("0,".$row)
                                        ->where($map)
                                        ->order('a.id DESC')
                                        ->select();
        //等待中的活动
        $count = $row - count($results);
        if($count > 0){
            unset($map['a.end']);
            $map['a.start'] = array('EGT',time()); //开始时间大于当前时间
            $waiting = M('company_activity')->alias('a')
                                            ->field('a.id,a.title,q.bm')
                                            ->join('qz_user AS u ON u.id = a.cid')
                                            ->join('qz_quyu AS q ON q.cid = u.cs')
                                            ->limit("0,".$count)
                                            ->where($map)
                                            ->order('a.id DESC')
                                            ->select();
            if(!empty($waiting)){
                $results = array_merge($results,$waiting);
            }
        }

        //已过期的活动
        $count = $row - count($results);
        if($count > 0){
            unset($map['a.start']);
            $map['a.end'] = array('ELT',time()); //结束时间小于当前时间
            $expired = M('company_activity')->alias('a')
                                            ->field('a.id,a.title,q.bm')
                                            ->join('qz_user AS u ON u.id = a.cid')
                                            ->join('qz_quyu AS q ON q.cid = u.cs')
                                            ->limit("0,".$count)
                                            ->where($map)
                                            ->order('a.id DESC')
                                            ->select();
            if(!empty($expired)){
                $results = array_merge($results,$expired);
            }
        }
        return $results;
    }
}