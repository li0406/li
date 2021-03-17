<?php
namespace Common\Model;
use Think\Model;

class YusuanModel extends Model{

    //查询所有预算
    public function getAllYusuan(){
        return M('yusuan')->select();
    }
}