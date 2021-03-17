<?php
/**
 * 户型表
 */
namespace Common\Model;
use Think\Model;
class GuimoModel extends Model{
    // 获取户型列表
    public function gethGm(){
        $map['type'] = array('eq','guimo');
        $list = M("guimo")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }
}