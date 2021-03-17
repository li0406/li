<?php

namespace Common\Model\Db;

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

    public function deleteSpecialkeywordById($map){
        return M('special_keywords')->where($map)->delete();
    }

    public function getSpecialkeywordByHref($map){
        return M('special_keywords')->where($map)->find();
    }

    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $map   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeywordList($map, $pageIndex = 0, $pageCount = 10,$field='*'){
        return M("special_keywords")->field($field)->where($map)->order("time desc,id desc")
            ->limit($pageIndex.",".$pageCount)
            ->select();
    }
	
    /**
     * [getSpecialkeywordList 获取最新关键字列表]
     * @param  [type] $map   [搜索关键字]
     * @return [type]            [description]
     */
    public function getNewSpecialkeyword($map,$limit=20){
        return M("special_keywords")->where($map)->order("time desc,id desc")
            ->limit($limit)
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

    /**
     * [getSpecialkeywordList 获取关键字列表]
     * @param  [type] $map   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getActriceBySpecialkeywordList($keyword_module,$name,$tempword, $pageIndex = 0, $pageCount = 12,$notid= 0){
        if($keyword_module == 1){
            $map = "a.title like '%".$name."%'";
            $field = "a.addtime,a.id, a.title, a.subtitle as description, a.face, b.shortname,case when a.title like '%".$name."%' then 1 ";
            foreach ($tempword as $key => $value) {
                $map .= " or a.title like '%".$value."%' ";
                $field .= "when a.title like '%".$value."%' then ".($key+2)." ";
            }
            $map .= " and (state = '2') ";
            $field .= "else 999 end paixu";
            return M("www_article")->alias("a")
                ->join("inner join qz_www_article_class_rel as c on c.article_id = a.id")
                ->join("inner join qz_www_article_class as b on b.id = c.class_id")
                ->field($field)
                ->where($map)
                ->order('paixu desc,addtime desc')
                ->limit($pageIndex.",".$pageCount)
                ->select();
        }elseif ($keyword_module == 2){
            if (empty($notid)){
                $map = "(item like '%".$name."%'";
            }else{
                $map = "id <> ".$notid." and (item like '%".$name."%'";
            }
            $field = "item,id,thumb,title,description,post_time,case when item like '%".$name."%' then 1 ";
            foreach ($tempword as $key => $value) {
                $map .= " or item like '%".$value."%' ";
                $field .= "when item like '%".$value."%' then ".($key+2)." ";
            }
            $map .= ") and (visible = '0' and remove = '0') ";
            $field .= "else 999 end paixu";
            return  M("baike")
                ->field($field)
                ->where($map)
                ->order('paixu desc,post_time desc,id desc')
                ->limit($pageIndex.",".$pageCount)
                ->select();
        }
    }

    /**
     * [getSpecialkeywordCount 获取关键字数量]
     * @param  [type] $map [搜索关键字]
     * @return [type]          [description]
     */
    public function getActriceBySpecialkeywordCount($keyword_module,$name,$tempword,$notid = 0){
        if($keyword_module == 1){
            $map = "title like '%".$name."%'";
            if(count($tempword)>0){
                foreach ($tempword as $key => $value) {
                    $map .= " or title like '%".$value."%'";
                }
            }
            $map .= " and (state = '2') ";
            return M("www_article")->where($map)
                ->count();
        }elseif ($keyword_module == 2){
            if (empty($notid)){
                $map = "(item like '%".$name."%'";
            }else{
                $map = "id <> ".$notid." and (item like '%".$name."%'";
            }
            if(count($tempword)>0){
                foreach ($tempword as $key => $value) {
                    $map .= " or item like '%".$value."%' ";
                }
            }
            $map .= ") and (visible = '0' and remove = '0') ";
            return M("baike")->where($map)
                ->count();
        }
    }

    /**
     * [getSpecialkeywordByAbout 获取关键字列表]
     * @param  [type] $map   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getSpecialkeywordByAbout($keyword_module,$name,$tempword){
        $map = "keyword_module = ".$keyword_module." and name <> '".$name."' and (name like '%".$name."%'";
        $field = "keyword_module,href,name,case when name like '%".$name."%' then 1 ";
        foreach ($tempword as $key => $value) {
            $map .= " or name like '%".$value."%' ";
            $field .= "when name like '%".$value."%' then ".($key+2)." ";
        }
        $map .= ")";
        $field .= "else 999 end paixu";
        return  M("special_keywords")
            ->field($field)
            ->where($map)
            ->order('paixu desc,time desc')
            ->limit(20)
            ->select();
    }

    public function getSpecialkeywordByAboutv2($keyword_module,$name,$tempword){
        $map = "keyword_module = ".$keyword_module." and name <> '".$name."' and (name like '%".$name."%'";
        $field = "keyword_module,href,name,case when name like '%".$name."%' then 1 ";
        foreach ($tempword as $key => $value) {
            $map .= " or name like '%".$value."%' ";
            $field .= "when name like '%".$value."%' then ".($key+2)." ";
        }
        $map .= ")";
        $field .= "else 999 end paixu";
        return  M("special_keywords")
            ->field($field)
            ->where($map)
            ->order('paixu desc,time desc,id desc')
            ->limit(20)
            ->select();
    }
	
	/**
	 * 根据相关知识分词获取关键词
	 * @param $keyModule
	 * @param $linkWords
	 * @param int $count
	 * @return mixed
	 */
	public function getLinkWordsByAboutWords($keyModule,$linkWords,$count=20){
		
		foreach ($linkWords as $key => $value) {
			$title[] = array("LIKE", "%$value%");
		}
		
		$title[] = "OR";
		
		$map = array(
			"title" => $title,
			"keyword_module" => array("EQ", $keyModule),
		);
		$ret = M("special_keywords")
			->where($map)
			->field("distinct name,href")
			->order("time desc,id desc")
			->limit($count)
			->select();
		return $ret;
	}
	
	public function isExist($column,$value,$module)
	{
		$map = [
			$column => array("EQ", $value),
			"keyword_module" => array("EQ", $module),
		];
		$ret = M("special_keywords")
			->where($map)
			->count();
		return $ret;
	}


    //P.2.18.0 （PC+m）新增装修知识页面
    public function getGongLveZhuanTiList($type=1,$limit = 8)
    {
        $map = [];
        $map['gonglve.keyword_module'] = $type;
        $buildSql = M('special_keywords')->alias('gonglve')
            ->where($map)
            ->field('gonglve.id gonglve_id,gonglve.name zhuanti_name,gonglve.href zhuanti_href,gonglve.time gonglve_time,CASE WHEN skr.type = 1 THEN 999 ELSE 1 END paixu')
            ->join('left join qz_special_keyword_recommend skr on skr.specialword_id = gonglve.id and type = 1')
            ->order('paixu desc,gonglve.time desc,gonglve.id desc')
            ->limit($limit)
            ->buildSql();
        $list = M()->table($buildSql)->alias('t')
            ->field('t.*,article.id article_id,article.title article_title,article.face article_face')
            ->join("left join qz_www_article as article on article.title like concat('%',t.zhuanti_name,'%')")
            ->group('t.gonglve_id')
            ->order('t.paixu DESC,t.gonglve_time DESC,t.gonglve_id DESC')
            ->select();
        return $list ? $list : [];
    }

    //
    public function getBaikeZhuanTiList($type=2,$limit = 8)
    {
        $map = [];
        $map['baike.keyword_module'] = $type;
        $buildSql = M('special_keywords')->alias('baike')
            ->where($map)
            ->field('baike.id baikeid,baike.name zhuanti_name,baike.href zhuanti_href,baike.time baike_time,CASE WHEN skr.type = 1 THEN 999 ELSE 1 END paixu')
            ->join('left join qz_special_keyword_recommend skr on skr.specialword_id = baike.id and type = 1')
            ->order('paixu desc,baike.time desc,baike.id desc')
            ->limit($limit)
            ->buildSql();
        $list = M()->table($buildSql)->alias('t')
            ->field('t.*,article.id article_id,article.item article_title,article.description article_description,article.thumb article_face')
            ->join("left join qz_baike as article on article.item like concat('%',t.zhuanti_name,'%')")
            ->group('t.baikeid')
            ->order('t.paixu DESC,t.baike_time DESC,t.baikeid DESC')
            ->select();
        return $list ? $list : [];
    }


    //获取推荐到专题知识页的问答专题列表
    public function getWendaZhunTiList($limit)
    {
        $map = [];
        $map['wenda.keyword_module'] = 3;
        return M('special_keywords')->alias('wenda')
            ->where($map)
            ->field('href,name')
            ->join('left join qz_special_keyword_recommend skr on skr.specialword_id = wenda.id and type = 1')
            ->order('skr.type desc,wenda.time desc,wenda.id desc')
            ->group('wenda.id')
            ->limit($limit)
            ->select();
    }


}