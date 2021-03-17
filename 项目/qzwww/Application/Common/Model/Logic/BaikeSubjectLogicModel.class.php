<?php
/**
 *  百科逻辑
 */

namespace Common\Model\Logic;


use Common\Model\AskModel;
use Common\Model\Db\SpecialkeywordModel;

/**
 * @description: 问答专题
 */
class BaikeSubjectLogicModel
{
	/**
	 * @var SpecialkeywordModel
	 */
	protected $specialKeywordModel;
	
	/**
	 * @var AskModel
	 */
	protected $askModel;
	
	protected $perCount = 15;
	
	public function __construct()
	{
		$this->specialKeywordModel = new SpecialkeywordModel();
		$this->askModel = new AskModel();
	}
	
	/**
	 * 根据href查找纪录
	 * @param $href
	 * @return mixed
	 */
	public function getWords($href)
	{
		$map = [
			'href' => $href,
			'keyword_module' => 3,
		];
		$words = $this->specialKeywordModel->getSpecialkeywordByHref($map);
		return $words;
	}
	
	public function getAnwserByFenCi($keywords, $pageIndex)
	{
		$count = $this->askModel->getAskByFenCiCount($keywords);
		$show = '';
		$list = [];
		if ($count > 0) {
			import('Library.Org.Page.LitePage');
			//自定义配置项
			$config = array("prev", "next");
			$page = new \LitePage($pageIndex, $this->perCount, $count, $config);
			$show = $page->pindaoshow();//,$p->firstRow,$p->listRows
			$list = $this->askModel->getAskByFenCi($keywords, $pageIndex, $this->perCount);
			
			if(!empty($list)){
			    //获取最新的回复
				foreach ($list as $key=>$value) {
					$answer = $this->askModel->getAnswerByAskId($value['id']);
					if(!empty($answer['content'])){
						$answer['content'] = mb_substr(str_ireplace(['<br>','<br />','<br/>'],'',$answer['content']), 0, 120).'...';
					}
					$list[$key]['created_at'] = date('Y-m-d',$answer['create_time']);
					$list[$key]['answer'] = $answer;
					$list[$key]['answer_count'] = $this->askModel->getAnswerCountByAskId($value['id']);
				}
			}
		}
		return array("list" => $list, "page" => $show);
	}
	
	public function isExist($column,$value,$module)
	{
		$keyword_model = new SpecialkeywordModel();
		return $keyword_model->isExist($column,$value,$module);
	}
}