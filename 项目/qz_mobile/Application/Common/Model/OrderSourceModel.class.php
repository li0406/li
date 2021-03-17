<?php

namespace Common\Model;
use Think\Model;

class OrderSourceModel extends Model
{
    /**
     * 获取所有来源标识
     * @return [type] [description]
     */
    public function findAllSource()
    {
        if (!$src) {
            $map = array(
                "type" => array("EQ",1)
            );
            $result = M("order_source")->where($map)->select();
            foreach ($result as $key => $value) {
                $src[$value["src"]] = $value;
            }
        }
        return $src;
    }

    /**
     * 获取来源标识
     * @param  [type] $src [description]
     * @return [type]      [description]
     */
    public function findSource($tag)
    {
        $src = S('C:S:SRC:'.$tag);
        if (!$src) {
            $map = array(
                "type" => array("EQ",1),
                "src" => array("EQ",$tag)
            );
            $result = M("order_source")->where($map)->find();
            $src[$result['src']] = $result;
            S('C:S:SRC:'.$tag, $src,900);
        }
        return $src;
    }

    //查询src获取一条记录
    public function getOne($src){
        $map['src'] = $src;
        return M("order_source")->where($map)->find();
    }
}
