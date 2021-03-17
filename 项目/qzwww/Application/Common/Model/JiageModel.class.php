<?php
/**
 * 造价表
 */
namespace Common\Model;
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
}