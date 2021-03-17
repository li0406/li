<?php

namespace Home\Model\Db;

class SpecialkeywordModel
{
    protected $autoCheckFields = false;

    /**
     * [addWwwarticlekeywords 新增关键字]
     * @param [type] $save [description]
     */
    public function addSpecialkeyword($save){
        return $id = M('special_keywords')->add($save);
    }

    /**
     * [editSpecialkeyword 编辑关键字]
     * @param  [type] $id   [ID]
     * @param  [type] $save [编辑内容]
     * @return [type]       [description]
     */
    public function editSpecialkeyword($save,$map){
        return M('special_keywords')->where($map)->save($save);
    }

    /**
     * [validateSpecialkeyword 验证去重]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateSpecialkeyword($map)
    {
        return M("special_keywords")->where($map)->find();
    }

    /**
     * [getSpecialkeywordById 根据ID获取关键字]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getSpecialkeywordById($map){
        return M('special_keywords')->where($map)->find();
    }

    public function getSpecialkeywordByIdV2($map,$type = 1){
        $type = $type ? intval($type) : 1;  //默认是1  装修知识
        return M('special_keywords')->alias('sk')
            ->where($map)
            ->join('left join qz_special_keyword_recommend skr on skr.specialword_id = sk.id and type = '.$type)
            ->find();
    }

    public function deleteSpecialkeywordById($map){
        return M('special_keywords')->where($map)->delete();
    }

    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $map   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeywordList($map, $pageIndex = 0, $pageCount = 10){
        return M("special_keywords")->where($map)->order("time desc")
            ->limit($pageIndex, $pageCount)
            ->select();
    }

    public function getSpecialkeywordListV2($map, $pageIndex = 0, $pageCount = 10){
        return M("special_keywords")->alias('sk')
            ->where($map)
            ->join('left join qz_special_keyword_recommend skr on skr.specialword_id = sk.id and type = 1')
            ->order("sk.time desc")
            ->limit($pageIndex, $pageCount)
            ->select();
    }

    /**
     * [getSpecialkeywordCount 获取关键字数量]
     * @param  [type] $map [搜索关键字]
     * @return [type]          [description]
     */
    public function getSpecialkeywordCount($map){
        return M("special_keywords")->where($map)->count();
    }

    /**
     * [addAllSpecialkeyword 批量新增关键字]
     * @param [type] $save [description]
     */
    public function addAllSpecialkeyword($save){
        return M('special_keywords')->addAll($save);
    }
}