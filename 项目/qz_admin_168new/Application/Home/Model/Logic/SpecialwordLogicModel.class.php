<?php

namespace Home\Model\Logic;

class SpecialwordLogicModel
{
    /**
     * 获取关键字列表
     * @return [type] [description]
     */
    public function getSpecialWordList()
    {
        $list = D("Home/Db/Specialword")->getSpecialWordList();
        foreach ($list as $key => $value) {
            if (!array_key_exists($value["character"], $words)) {
                $words[$value["character"]] = $value["character"];
            }
        }
        return array("list" => $list,"words" => $words);
    }

    /**
     * 添加生僻字
     * @param [type] $data [description]
     */
    public function addSpecialWord($data)
    {
        return D("Home/Db/Specialword")->addSpecialWord($data);
    }

    /**
     * 编辑生僻字
     * @param [type] $data [description]
     */
    public function editSpecialWord($id,$data)
    {
        return D("Home/Db/Specialword")->editSpecialWord($id,$data);
    }

    /**
     * 删除生僻字
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delSpecialWord($id)
    {
        return D("Home/Db/Specialword")->delSpecialWord($id);
    }
}