<?php

namespace Home\Controller;

use Common\Model\Logic\BaikeSubjectLogicModel;
use Common\Model\Logic\SpecialkeywordLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\WwwArticleTagsLogicModel;
use Home\Common\Controller\HomeBaseController;

/**
 * @description: 问答知识专题页
 */
class BaikeSubjectController extends HomeBaseController
{
	
	public function _initialize()
	{
		parent::_initialize();
		//判断是否是搜索引擎蜘蛛
		$robotIsTrue = B("Common\Behavior\RobotCheck");
		//$robotIsTrue = true; //debug 警告线上应该为注释状态
		if (true === $robotIsTrue) {
			$this->assign('robot', 1);
		}
		
		$uri = $_SERVER['REQUEST_URI'];
		preg_match('/html$/', $uri, $m);
		if (count($m) == 0) {
			preg_match('/\/$/', $uri, $m);
			$parse = parse_url($uri);
			if (count($m) == 0 && empty($parse["query"])) {
				header("HTTP/1.1 301 Moved Permanently");
				header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") . $uri . "/");
			}
		}
		
		if (!empty($this->cityInfo["bm"])) {
			if (!$robotIsTrue) {
				$this->assign("headerTmp", 1);
			}
		}
		if (empty($this->cityInfo["bm"])) {
			$t = T("Home@Index:header");
		} else {
			if (!$robotIsTrue) {
				$t = T("Sub@Index:header");
			}
		}
		$headerTmp = $this->fetch($t);
		$this->assign("headerTmp", $headerTmp);
		$this->assign("header_search", 3);
		//添加顶部搜索栏信息
		$this->assign('serch_uri', 'wenda/search');
		$this->assign('serch_type', '装修问答');
		$this->assign('holdercontent', '专业解决您的所有装修问题');
	}
	
	public function index()
	{
	    //跳转到手机端
		if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
			header("Location: " . $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
			exit();
		}

        $id = I("get.id");
        $pageIndex = max(1, I('get.p'));
        $logic = new ThematicWordsLogicModel();

        $info = S("Cache:Them:".$id);
        if (!$info) {
            $info = $logic->getThematicInfoById($id,3);
            if (count($info) == 0) {
                $this->_error();
                die();
            }
            S("Cache:Them:".$id,$info,900);
        }
        $pageCount = 15;
        //获取列表
        $result = $logic->getWenDaSearch($info["name"],$pageIndex,$pageCount);

        $this->assign('seo_info', $info);
        $this->assign("thematic",$info["name"]);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("tabIndex",5);
		$this->display('Subject/index');
	}
}