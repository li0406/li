<?php
/**
 * 户型表
 */
namespace Common\Model;
use Think\Model;
class HuxingModel extends Model{
    /**
     * 获取户型列表
     * @param  [type] $isPart [获取全部内容]
     * @return [type]        [description]
     */
    public function gethx($isPart = true){
        $map['type'] = array('eq','huxing');
        if($isPart){
            $map["id"] = array("BETWEEN","10,15");
        }
        $list = M("huxing")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }
}