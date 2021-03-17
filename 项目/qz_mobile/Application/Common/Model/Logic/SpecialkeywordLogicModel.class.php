<?php

namespace Common\Model\Logic;

use Common\Model\Db\SpecialkeywordModel;

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
        return D('Common/Db/Specialkeyword')->addSpecialkeyword($save);
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
        return D('Common/Db/Specialkeyword')->editSpecialkeyword($save,$map);
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
        return D('Common/Db/Specialkeyword')->validateSpecialkeyword($map);
    }

    /**
     * [validateSpecialkeywordByUrl 验证关键词重复性]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateSpecialkeywordByUrl($href,$id="")
    {
        $map['href']  = array('eq',$href);
        if(!empty($id)){
            $map['id']  = array('neq',$id);
        }
        return D('Common/Db/Specialkeyword')->validateSpecialkeyword($map);
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
        if(!empty($id)){
            $map['id']  = array('neq',$id);
        }
        return D('Common/Db/Specialkeyword')->validateSpecialkeyword($map);
    }

    /**
     * [validateSpecialkeywordByUrl 验证关键词重复性]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function validateAllSpecialkeywordByUrl($href)
    {
        $map['href']  = array('IN',$href);
        return D('Common/Db/Specialkeyword')->validateSpecialkeyword($map);
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
        return D('Common/Db/Specialkeyword')->getSpecialkeywordById($map);
    }

    public function deleteSpecialkeywordById($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return D('Common/Db/Specialkeyword')->deleteSpecialkeywordById($map);
    }

    public function getSpecialkeywordByHref($keyword_module,$href){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if (!empty($href)) {
            $map['href'] = $href;
        }
        return D('Common/Db/Specialkeyword')->getSpecialkeywordByHref($map);
    }

    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $keyword   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeywordList($keyword_module = 0, $is_hot, $pageIndex = 0, $pageCount = 10){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if (!empty($is_hot)) {
            $map['is_hot'] = $is_hot;
        }
        return D('Common/Db/Specialkeyword')->getSpecialkeywordList($map,$pageIndex,$pageCount);
    }

    /**
     * [getSpecialkeywordCount 获取关键字数量]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getSpecialkeywordCount($keyword_module = 0, $is_hot){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if (!empty($is_hot)) {
            $map['is_hot'] = $is_hot;
        }
        return D('Common/Db/Specialkeyword')->getSpecialkeywordCount($map);
    }

    /**
     * [addAllSpecialkeyword 批量新增关键字]
     * @param [type] $save [description]
     */
    public function addAllSpecialkeyword($save){
        if (empty($save)) {
            return false;
        }
        return D('Common/Db/Specialkeyword')->addAllSpecialkeyword($save);
    }

    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $keyword   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getActriceBySpecialkeywordList($keyword_module,$name,$tempword, $pageIndex = 0, $pageCount = 12,$notid = 0){
        if(empty($name)){
            return false;
        }
        return D('Common/Db/Specialkeyword')->getActriceBySpecialkeywordList($keyword_module,$name,$tempword,$pageIndex,$pageCount,$notid);
    }

    /**
     * [getSpecialkeywordCount 获取关键字数量]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getActriceBySpecialkeywordCount($keyword_module,$name, $tempword,$notid = 0){
        if(empty($name)){
            return false;
        }
        return D('Common/Db/Specialkeyword')->getActriceBySpecialkeywordCount($keyword_module,$name,$tempword,$notid);
    }
    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $keyword   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeywordByAbout($name,$tempword){
        if(!empty($name) && count($tempword)>0){
            return D('Common/Db/Specialkeyword')->getSpecialkeywordByAbout($name,$tempword);
        }
        return false;
    }

    public function getTitleKeywords($title){
        //调用分词接口
        $data['word'] = $title;
        $result = curl(C('FENCI_DONAMES'),$data);//urldecode(http_build_query($param))
        //获取最新三条关键词
        if(count($result)>0){
                $keyword_model = new SpecialkeywordModel();
                $map['keyword_module'] = 3;
                $map['name'] = ['in',$result];
                return $keyword_model->getSpecialkeywordList($map,0, 3,'id,name,href');
        }
    }

    public function getKeywords(){
        $keyword_model = new SpecialkeywordModel();
        $map['keyword_module'] = 3;
        return $keyword_model->getSpecialkeywordList($map,0, 20,'id,name,href');
    }
	
	/**
	 * [getSpecialkeywordList 获取最新关键字列表]
	 * @param  [type] $keyword   [搜索关键字]
	 * @param  [type] $pageIndex [开始页]
	 * @param  [type] $pageCount [结束页]
	 * @return [type]            [description]
	 */
	public function getNewSpecialkeyword($keyword_module,$time,$id){
		if (!empty($keyword_module)) {
			$map['keyword_module'] = $keyword_module;
		}
		if (!empty($time)) {
			$map['time'] = array('ELT',$time);
		}
		if (!empty($id)) {
			$map['id'] = array('ELT',$id);
		}
		return D('Common/Db/Specialkeyword')->getNewSpecialkeyword($map);
	}

    /**
     * [getSpecialkeyByArray 根据数组中的词获取三个相关的关键字]
     * @param  [type] $keyword_module   [所属模块]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeyByArray($keyword_module,$data){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if($data){
//            $map['name'] = array('in',$data);
            $where = [];
            foreach ($data as $key => $val){
                $where[] = array('LIKE','%'.$val.'%');
            }
            if($where){
                $where[] = 'OR';
                $map['name'] = $where;
            }
        }else{
            return array();
        }
        return D('Common/Db/Specialkeyword')->getNewSpecialkeyword($map);
    }


    //P.2.18.0 （PC+m）新增装修知识页面
    public function getGongLveZhuanTiList($limit)
    {
        $model = new SpecialkeywordModel();
        $gongluelist = $model->getGongLveZhuanTiList(1,$limit); //专题栏目，1：装修攻略 2：装修百科 3: 装修问答'
        foreach ($gongluelist as $key => $val){
            if($val['article_face']){
                $gongluelist[$key]['article_face'] = changeImgUrl($val['article_face']);
            }
            $gongluelist[$key]['zhuanti_name'] = mbstr($val['zhuanti_name'],0,10);
        }
        return $gongluelist;
    }

    public function getBaikeZhuanTiList($limit)
    {
        $model = new SpecialkeywordModel();
        $baikelist = $model->getBaikeZhuanTiList(2,$limit); //专题栏目，1：装修攻略 2：装修百科 3: 装修问答'
        foreach ($baikelist as $key => $val){
            if($val['article_face']){
                $baikelist[$key]['article_face'] = changeImgUrl($val['article_face']);
            }
            $baikelist[$key]['zhuanti_name'] = mbstr($val['zhuanti_name'],0,10);
            $baikelist[$key]['article_description'] = mbstr($val['article_description'],0,50);
        }
        return $baikelist;
    }

    public function getWendaZhuanTiList($limit)
    {
        $model = new SpecialkeywordModel();
        $wendalist = $model->getWendaZhunTiList($limit); //专题栏目，1：装修攻略 2：装修百科 3: 装修问答'
        foreach ($wendalist as $key => $val){
            $wendalist[$key]['name'] = mbstr($val['name'],0,10);
        }
        return $wendalist;
    }
}