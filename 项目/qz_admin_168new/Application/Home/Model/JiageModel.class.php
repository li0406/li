<?php
/**
 * 造价表
 */
namespace Home\Model;
use Think\Model;
class JiageModel extends Model{
    /**
     * 获取价格信息
     * @return [type] [description]
     */
    public function getJiage(){
        $map['type'] = array('eq','jiage');
        $list = M("jiage")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }

    /**
     * 获取家居价格信息
     * @return [type] [description]
     */
    public function getJiajuJiage(){
        $list = M("jiaju_yusuan")->order('px')->field('id,name')->select();
        return $list;
    }
}