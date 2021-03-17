<?php

namespace Home\Model\Logic;

use Home\Model\Db\SpecialkeywordModel;
use Home\Model\Db\SpecialkeywordrecommentModel;

class SpecialkeywordLogicModel
{
    protected $autoCheckFields = false;

    /**
     * [addWwwarticlekeywords 新增内链关键字]
     * @param [type] $save [description]
     */
    public function addSpecialkeyword($save){
        if (empty($save)) {
            return false;
        }
        return D('Home/Db/Specialkeyword')->addSpecialkeyword($save);
    }


    /**
     * [editSpecialkeyword 编辑关键字]
     * @param  [type] $id   [ID]
     * @param  [type] $save [编辑内容]
     * @return [type]       [description]
     */
    public function editSpecialkeyword($id, $save){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return D('Home/Db/Specialkeyword')->editSpecialkeyword($save,$map);
    }

    /**
     * [validateSpecialkeywordByName 验证关键词重复性]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateSpecialkeywordByName($name,$keyword_module,$id="")
    {
        $map['name']  = array('eq',$name);
        $map['keyword_module']  = array('eq',$keyword_module);
        if(!empty($id)){
            $map['id']  = array('neq',$id);
        }
        return D('Home/Db/Specialkeyword')->validateSpecialkeyword($map);
    }

    /**
     * [validateSpecialkeywordByUrl 验证关键词重复性]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateSpecialkeywordByUrl($href,$keyword_module,$id="")
    {
        $map['href']  = array('eq',$href);
        $map['keyword_module']  = array('eq',$keyword_module);
        if(!empty($id)){
            $map['id']  = array('neq',$id);
        }
        return D('Home/Db/Specialkeyword')->validateSpecialkeyword($map);
    }

    /**
     * [validateSpecialkeywordByName 验证关键词重复性]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateAllSpecialkeywordByName($name,$keyword_module)
    {
        $map['name']  = array('IN',$name);
        $map['keyword_module']  = array('eq',$keyword_module);
        return D('Home/Db/Specialkeyword')->validateSpecialkeyword($map);
    }

    /**
     * [validateSpecialkeywordByUrl 验证关键词重复性]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateAllSpecialkeywordByUrl($href,$keyword_module)
    {
        $map['href']  = array('IN',$href);
        $map['keyword_module']  = array('eq',$keyword_module);
        return D('Home/Db/Specialkeyword')->validateSpecialkeyword($map);
    }

    /**
     * [getSpecialkeywordById 根据ID获取关键字]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getSpecialkeywordById($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return D('Home/Db/Specialkeyword')->getSpecialkeywordById($map);
    }

    //获取
    public function getSpecialkeywordByIdV2($id,$type = 1){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'sk.id' => $id
        );
        return D('Home/Db/Specialkeyword')->getSpecialkeywordByIdV2($map,$type);
    }

    public function deleteSpecialkeywordById($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return D('Home/Db/Specialkeyword')->deleteSpecialkeywordById($map);
    }

    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $keyword   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeywordList($keyword_module = 0, $keyword,$start,$end, $pageIndex = 0, $pageCount = 10){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if(!empty($start) && !empty($end)){
            $map['time'] = [['EGT', strtotime(trim($start))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($end))))], 'and'];
        }elseif (!empty($start) && empty($end)){
            $map['time'] = [['EGT', strtotime(date('Y-m-d 00:00:00',strtotime(trim($start))))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($start))))], 'and'];
        }elseif (empty($start) && !empty($end)){
            $map['time'] = [['EGT', strtotime(date('Y-m-d 00:00:00',strtotime(trim($end))))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($end))))], 'and'];
        }
        if(!empty($keyword)){
            $map["_complex"] = array(
                "name" => array("LIKE","%$keyword%"),
                "href" => array("LIKE","%$keyword%"),
                "_logic" => "OR"
            );
        }
        return D('Home/Db/Specialkeyword')->getSpecialkeywordList($map,$pageIndex,$pageCount);
    }

    public function getSpecialkeywordListV2($keyword_module = 0, $keyword,$start,$end, $pageIndex = 0, $pageCount = 10){
        if (!empty($keyword_module)) {
            $map['sk.keyword_module'] = $keyword_module;
        }
        if(!empty($start) && !empty($end)){
            $map['sk.time'] = [['EGT', strtotime(trim($start))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($end))))], 'and'];
        }elseif (!empty($start) && empty($end)){
            $map['sk.time'] = [['EGT', strtotime(date('Y-m-d 00:00:00',strtotime(trim($start))))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($start))))], 'and'];
        }elseif (empty($start) && !empty($end)){
            $map['sk.time'] = [['EGT', strtotime(date('Y-m-d 00:00:00',strtotime(trim($end))))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($end))))], 'and'];
        }
        if(!empty($keyword)){
            $map["_complex"] = array(
                "sk.name" => array("LIKE","%$keyword%"),
                "sk.href" => array("LIKE","%$keyword%"),
                "_logic" => "OR"
            );
        }
        return D('Home/Db/Specialkeyword')->getSpecialkeywordListV2($map,$pageIndex,$pageCount);
    }

    /**
     * [getSpecialkeywordCount 获取关键字数量]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getSpecialkeywordCount($keyword_module = 0, $keyword,$start,$end){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if(!empty($start) && !empty($end)){
            $map['time'] = [['EGT', strtotime(trim($start))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($end))))], 'and'];
        }elseif (!empty($start) && empty($end)){
            $map['time'] = [['EGT', strtotime(date('Y-m-d 00:00:00',strtotime(trim($start))))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($start))))], 'and'];
        }elseif (empty($start) && !empty($end)){
            $map['time'] = [['EGT', strtotime(date('Y-m-d 00:00:00',strtotime(trim($end))))], ['ELT', strtotime(date('Y-m-d 23:59:59',strtotime(trim($end))))], 'and'];
        }
        if(!empty($keyword)){
            $map["_complex"] = array(
                "name" => array("LIKE","%$keyword%"),
                "href" => array("LIKE","%$keyword%"),
                "_logic" => "OR"
            );
        }
        return D('Home/Db/Specialkeyword')->getSpecialkeywordCount($map);
    }

    /**
     * [addAllSpecialkeyword 批量新增关键字]
     * @param [type] $save [description]
     */
    public function addAllSpecialkeyword($save){
        if (empty($save)) {
            return false;
        }
        return D('Home/Db/Specialkeyword')->addAllSpecialkeyword($save);
    }

    //
    public function recommendToZhishiOrCancel($info,$id)
    {
        $model = new SpecialkeywordrecommentModel();
        if(!$info || !$id){
            $k = 0;
            return $k;
        }else{
            if($info['id'] != $id){
                $k = 0;
                return $k;
            }
            $k = 0;
            if($info['type'] == 1){        //已推荐  取消推荐
                $deletedata = [];
                $deletedata['specialword_id'] = $id;
                $deletedata['type'] = 1;      //1表示推荐到装修知识页
                $result = $model->deletedata($deletedata);
                if($result){
                    $k = 2;
                }
            }else{
                //未推荐。   推荐到装修知识页
                $savedata = [];
                $savedata['specialword_id'] = $id;
                $savedata['type'] = 1;           //1表示推荐到装修知识页
                $result = $model->adddata($savedata);
                if($result){
                    $k = 1;
                }
            }

            return $k;

        }

    }
}