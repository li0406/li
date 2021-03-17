<?php
/**
 * 风格表
 */
namespace Common\Model;
use Think\Model;
class FenggeModel extends Model{
    // 获取风格列表
    public function getfg(){
        $map['type'] = array('eq','fengge');
        $list = M("fengge")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }
}