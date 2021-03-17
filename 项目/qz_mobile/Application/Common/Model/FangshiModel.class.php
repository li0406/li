<?php
/**
 *  方式表 qz_fangshi
 */
namespace Common\Model;
use Think\Model;
class FangshiModel extends Model{
    // 获取风格列表
    public function getfs(){
        $map['type'] = array('eq','fangshi');
        $list = M("fangshi")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }
}