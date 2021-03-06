<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/11/22
 * Time: 10:40
 */

namespace Home\Model\Db;


use Think\Model;

class MallGiftModel extends Model
{
    protected $tableName = "mall_gift";

    public function insertInfo($data){
        $data['created_at'] = time();
        $data['updated_at'] = time();
        return $this->add($data);
    }

    public function saveInfo($data,$goods_id){
        if(!empty($goods_id)){
            $data['updated_at'] = time();
            $map['goods_id'] = ['eq',$goods_id];
            return $this->where($map)->save($data);
        }
    }




}