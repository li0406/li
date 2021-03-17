<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/11/22
 * Time: 10:40
 */

namespace Home\Model\Db;


use Think\Model;

class MallGoodsImgModel extends Model
{
    protected $tableName = "mall_goods_img";

    public function insertInfo($data){
        return $this->addAll($data);
    }

    public function deleteInfo($goods_id){
        if(!empty($goods_id)){
            $map['goods_id'] = ['eq',$goods_id];
            return $this->where($map)->delete();
        }
    }




}