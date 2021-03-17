<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 13:36
 */
namespace Common\Model;

use Think\Model;

class YySrcWeixinModel extends Model{

    //获取默认的数据值
    public function getDefault(){
        $map['is_default'] = '1';
        $result = M("yy_src_weixin")->where($map)->find();
        return $result;
    }

    //根据sourceid获取一条记录
    public function getOneBySourceid($sourceid){
        $map['source_id'] = $sourceid;
        return M("yy_src_weixin")->where($map)->find();
    }

    //获取默认的记录
    public function getDefaultData(){
        $map['is_default'] = '1';
        return M("yy_src_weixin")->where($map)->find();
    }

    //添加数据
    public function addData($data){
        return M("yy_src_weixin")->add($data);
    }

    //修改数据
    public function saveData($map, $data){
        return M("yy_src_weixin")->where($map)->save($data);
    }

    //批量添加数据
    public function addAllData($data){
        return M("yy_src_weixin")->addAll($data);
    }

}