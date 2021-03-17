<?php
// +----------------------------------------------------------------------
// | 百科分类逻辑层
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

class AdminbaikeLogicModel
{
    public function checkCateForm($data)
    {
        if(empty($data['name'])||mb_strlen($data['name'],'utf-8') > 15){
           return ['info' => '分类名不能为空且最多15个字', 'status' => 0];
        }
        if(mb_strlen($data['title'],'utf-8') > 254){
            return ['info' => 'title最多255个字', 'status' => 0];
        }
        if(mb_strlen($data['keywords'],'utf-8') > 254){
            return ['info' => 'description最多255个字', 'status' => 0];
        }
        if(mb_strlen($data['description'],'utf-8') > 254){
            return ['info' => 'keywords最多255个字', 'status' => 0];
        }
        return true;
    }
}