<?php

namespace Mobile\Controller;

use Common\Model\Logic\BaikeSubjectLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Mobile\Common\Controller\MobileBaseController;

/**
 * @description: 问答知识专题页
 */
class BaikeSubjectController extends MobileBaseController
{
	
	public function _initialize(){
		parent::_initialize();
		$uri = $_SERVER['REQUEST_URI'];
		preg_match('/html$/',$uri,$m);
		if (count($m) == 0) {
			preg_match('/\/$/',$uri,$m);
			$parse = parse_url($uri);
			if (count($m) == 0 && empty($parse["query"])) {
				$SCHEME_HOST = $this->SCHEME_HOST;
				header( "HTTP/1.1 301 Moved Permanently");
				header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
			}
		}
	}
	public function index()
	{
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

        $basic = [
            'head'=>[
                'title'=>$info['title'],
                'keywords'=>$info['keywords'],
                'description'=>$info['description'],
            ],
        ];

        $pageCount = 15;
        //获取列表
        $result = $logic->getWenDaSearch($info["name"],$pageIndex,$pageCount);

        $this->assign('basic', $basic);
        $this->assign('seo_info', $info);
        $this->assign("thematic",$info["name"]);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display('Subject/index');
	}
}