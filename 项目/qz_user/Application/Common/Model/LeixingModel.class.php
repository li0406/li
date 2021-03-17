<?php
/**
 * 类型表  leixing
 */
namespace Common\Model;
use Think\Model;
class LeixingModel extends Model{
    // 获取类型列表
    public function getlx(){
        $map['type'] = array('eq','leixing');
        $list = M("leixing")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }

    // 获取保障列表
    public function getbg(){
        $map['type'] = array('eq','baozhang');
        $list = M("leixing")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }
}