<?php

namespace Home\Model\Db;
Use Think\Model;

class SpecialwordModel extends Model
{
    /**
     * 获取关键字列表
     * @return [type] [description]
     */
    public function getSpecialWordList()
    {
        return M("specialword")->order("`character`")->select();
    }

    /**
     * 添加生僻字
     * @param [type] $data [description]
     */
    public function addSpecialWord($data)
    {
        return M("specialword")->add($data);
    }

    /**
     * 添加生僻字
     * @param [type] $data [description]
     */
    public function editSpecialWord($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("specialword")->where($map)->save($data);
    }

    /**
     * 删除生僻字
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delSpecialWord($id){
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("specialword")->where($map)->delete();
    }
}
