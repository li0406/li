<?php
/**
 * 户型表
 */
namespace Home\Model;
use Think\Model;
class HuxingModel extends Model{
    /**
     * 获取户型列表
     * @param  [type] $isPart [获取全部内容]
     * @return [type]        [description]
     */
    public function gethx(){
        $map['type'] = array('eq','huxing');

        $list = M("huxing")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }

    public function getHxByIds($ids){
        $map['type'] = array('eq','huxing');
        $map['id'] = array('in', $ids);

        $list = M("huxing")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }
}