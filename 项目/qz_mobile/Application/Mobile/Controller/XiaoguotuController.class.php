<?php
/**
 * 移动版效果图
 */
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;

class XiaoguotuController extends MobileBaseController{
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

	public function index(){
		$cityInfo = $this->cityInfo;
		$info["title"] = "装修案例";

		//获取效果图片列表
		$pageIndex = 1;
		$pageCount = 10;
		if(I("get.p") !== ""){
			$pageIndex = I("get.p");
			$pageContent ="第".$pageIndex."页";
		}
		$keyword = trim(I('get.keyword'));

		//获取风格
		$fg = D("Common/Fengge")->getfg();
		$top = array(
			"id"=>"",
			"type"=>"fengge",
			"name" =>"不限"
		);
		array_unshift($fg,$top);
		unset($fg[count($fg)-1]);
		$info["fg"] = $fg;
		if(I("get.fengge") !== ""){
			$fengge = I("get.fengge");
			$info["fengge"] = I("get.fengge");
			$flag = false;
			foreach ($fg as $key => $value) {
				if ($fengge == $value['id']) {
					$flag = true;
					$info['selected']['fengge'] = $value;
				}
			}
			if ($flag == false) {
				$this->_error();
			}
		}

		//获取户型
		$hx = D("Common/Huxing")->gethx();
		$top = array(
			"id"=>"",
			"type"=>"hx",
			"name" =>"不限"
		);
		array_unshift($hx,$top);
		unset($hx[count($hx)-1]);
		$info["hx"] = $hx;

		if(I("get.hx") !== ""){
			$huxing = I("get.hx");
			$info["huxing"] = I("get.hx");
			$flag = false;
			foreach ($hx as $key => $value) {
				if ($huxing == $value['id']) {
					$flag = true;
					$info['selected']['huxing'] = $value;
				}
			}
			if ($flag == false) {
				$this->_error();
			}
		}

		//获取价格
		$jg = D("Common/Jiage")->getJiage();
		$top = array(
			"id"=>"",
			"type"=>"jg",
			"name" =>"不限"
		);
		array_unshift($jg,$top);
		unset($jg[count($jg)-1]);
		$info["jg"] = $jg;
		if(I("get.jg") !== ""){
			$jiage = I("get.jg");
			$info["jiage"] = I("get.jg");
			$flag = false;
			foreach ($jg as $key => $value) {
				if ($jiage == $value['id']) {
					$flag = true;
					$info['selected']['jiage'] = $value;
				}
			}
			if ($flag == false) {
				$this->_error();
			}
		}

		$result = $this->getCaseImagesList($pageIndex,$pageCount,"",$huxing,$fengge,$jiage,"","",$keyword,$cityInfo["id"],false);
		$info["cases"] = $result["images"];
		$info["page"] = $result["page"];
		if (IS_AJAX) {
			$this->assign('info', $info);
			$content = $this->fetch('list-content');
            echo $content;
            die();
		}

		//推荐数据
		if (empty($info["cases"])) {
			$info["recommend"] = S('m:xgt:index:recommend:'.$cityInfo["id"]);
			if (empty($info["recommend"])) {
				$info["recommend"] = D("Common/Cases")->getRecommend(3, $cityInfo["id"]);
				S('m:xgt:index:recommend:'.$cityInfo["id"], $info["recommend"], 600);
			}
		}

		//SEO-TKD
		$content = $cityInfo["name"];
		//风格
		foreach ($info["fg"] as $key => $value) {
			if($value["id"] == $fengge && !empty($fengge)){
				$content .= $value["name"];
				$typeName .=$value["name"];
				break;
			}
		}
		//户型
		foreach ($info["hx"] as $key => $value) {
			if($value["id"] == $huxing && !empty($huxing)){
				$content .= $value["name"];
				$typeName .=$value["name"];
				break;
			}
		}
		//价格
		foreach ($info["jg"] as $key => $value) {
			if($value["id"] == $jiage && !empty($jiage)){
				$content .= $value["name"];
				$typeName .=$value["name"];
				break;
			}
		}

		//关键字、描述、标题
		// $basic["head"]["title"] = $typeName."装修案例_".$typeName."装修设计效果图片".$pageContent."-齐装网";
		// $basic["head"]["keywords"] =  $content."装修案例,".$content."装修效果图,".$content."装修设计";
		// $basic["head"]["description"] = "齐装网为您提供".date("Y")."年新流行的".$content."装修案例设计效果图片,以及".$content."装修样板房图片！找装修案例设计效果图片就上齐装网！";

		// SEO 替换配置
		$params = array(
			"（城市）" => $cityInfo["name"]
		);

		//关键字、描述、标题
		$basic["head"]["title"] = strtr("（城市）装修设计_（城市）装修案例_（城市）装修效果图-齐装网", $params);
		$basic["head"]["keywords"] = strtr("（城市）装修设计,（城市）装修案例,（城市）装修效果图", $params);
		$basic["head"]["description"] = strtr("（城市）齐装网为大家带来的是（城市）装修设计案例和精美的装修案例效果图，（城市）装修网为您提供海量的精美装修设计效果图，帮助您了解更多潮流的装修风格案例。", $params);

		if((count($_GET) <= 1)){
			if(!empty($cityInfo["bm"])){
				$info["canonical"] = $this->SCHEME_HOST['scheme_full'].$cityInfo["bm"].".".C('QZ_YUMING')."/xgt/";
			}else{
				$info["canonical"] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW')."/xgt/";
			}
		}

		//dump($info);

		//获取该城市第一个区，用于显示默认城市
		$info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

		$url_no_page = '?fengge=' . $info['selected']['fengge']['id'] . '&hx=' .$info['selected']['huxing']['id'] . '&jg=' . $info['selected']['jiage']['id'] . '&keyword=' . $keyword;

		$this->assign("info",$info);
		$this->assign("basic",$basic);
		$this->assign("pageid",$pageIndex);
		$this->assign("totalpage",$result['totalpage']);
		$this->assign("url_no_page",$url_no_page);
		$this->assign("redPacket",array('source' => 316));
		$this->assign("cityInfo",$cityInfo);
		$this->display();
	}

	/**
	 * 获取装修案例效果图
	 * @return [type] [description]
	 */
	private function getCaseImagesList($pageIndex = 1,$pageCount = 10,$classid = 1,$huxing="",$fengge="",$jiage,$ys,$sm,$keyword,$cs)
	{
		//强制数字整数
		$pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
		$pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
		$count =  D("Common/Cases")->getCaseImagesListCount($classid,$huxing,$fengge,$jiage,$ys,$sm,$keyword,$cs);
		if($count > 0){
			import('Library.Org.Page.MobilePage');
			//自定义配置项
			$config  = array("prev","next");
			$page = new \MobilePage($pageIndex,$pageCount,$count,$config,"html");
			$pageTmp =  $page->show();
			$result = D("Common/Cases")->getCaseImagesList(($page->pageIndex-1)*$pageCount,$pageCount,$classid,$huxing,$fengge,$jiage,$ys,$sm,$keyword,$cs);

			return array("images"=>$result,"page"=>$pageTmp,"totalpage" => $page->pageTotalCount);
		}
		return null;
	}
}