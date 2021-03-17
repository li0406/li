<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/3/18
 * Time: 9:23
 */

namespace Home\Model\Db;


use Think\Model;

class BaikeModel extends Model
{

    public function getArticle($fields,$where){
        return M('baike')->field($fields)->where($where)->select();
    }
}