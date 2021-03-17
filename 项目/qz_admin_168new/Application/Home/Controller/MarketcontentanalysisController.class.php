<?php

//市场系统 内容统计 文章业绩分析
// Market Content Analysis

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\MarketContentLogicModel;
use Home\Model\Logic\MeituV2LogicModel;
use Home\Model\Logic\RbacRoleLogicModel;

class MarketcontentanalysisController extends HomeBaseController
{
	
	public function _initialize()
	{
		parent::_initialize();
		$this->assign('sidebarid', '1');
		$this->assign('side_sid', '2');
	}
	
	//主站文章业绩分析
	public function index()
	{
		$categorytree = D('WwwArticleClass')->getArticleClassListByLevel(0, 1, 1);
		//获取一级分类
		$this->assign('bigCate', $categorytree);
		

		//分页
		$pageIndex = 1;
		$pageCount = 20;
		if (!empty($_GET["p"])) {
			$pageIndex = $_GET["p"];
		}
		
		//创建时间
		$crate_start = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
		$create_end = date("Y-m-d");
		$map['a.createtime'] = array(
			array("EGT", strtotime($crate_start)),
			array("LT", strtotime($create_end) + 86400)
		);
		
		if (I('get.create_start') !== "" && I('get.create_end') !== "") {
			$map['a.createtime'] = array(
				array("EGT", strtotime(I('get.create_start'))),
				array("LT", strtotime(I('get.create_end')) + 86400)
			);
		} elseif (I('get.create_start') !== "" && I('get.create_end') === "") {
			$map['a.createtime'] = array("EGT", strtotime(I('get.create_start')));
		} elseif (I('get.create_start') === "" && I('get.create_end') !== "") {
			$map['a.createtime'] = array("LT", strtotime(I('get.create_end')) + 86400);
		}
		
		//正式发布时间
		$start_time = strtotime(I('get.start'));
		$end_time = strtotime(I('get.end'));
		
		if (!empty($start_time) && !empty($end_time)) {
			$end_time = mktime(23, 59, 59, date("m", $end_time), date("d", $end_time), date("Y", $end_time));
			$map['a.addtime'] = array('between', array($start_time, $end_time));
			if (I('get.create_start') == "" && I('get.create_end') == "") {
				unset($map["a.createtime"]);
			}
		}
		
		// 用户
		$userid = I('get.uid');
		if (!empty($userid)) {
			$map['a.userid'] = array('EQ', $userid);
			$info['uid'] = $userid;
		}
		
		
		// 发布类型
		$release = I('get.release');
		if (!empty($release)) {
			$map['a.pre_release'] = array('EQ', $release);
			$info['release'] = $release;
			if ($release == 2) {
				$map['a.state'] = array('NEQ', '-1');
			}
		}
		
		// 发布状态
		if (I('get.state') !== "") {
			$map['a.state'] = array('EQ', I('get.state'));
		}
		
		// 2017-11-06 添加搜索条件:id、title、分类，增加分类查询
		$title = I('get.title');
		if (!empty($title)) {
			//$map["a.id|a.title"] = trim($title);
			$map['_string'] = "(a.id = '" . $title . "') or (a.title like '" . $title . "%')";
		}
		if (!empty($_GET['thirdType'])) {
			//三级分类
			$map['r.class_id'] = I('get.thirdType');
		} else {
			if (!empty($_GET['secondType'])) {
				//二级分类
				$map['r.class_id'] = I('get.secondType');
			} else {
				if (!empty($_GET['firstType'])) {
					//一级分类
					$map['r.class_id'] = I('get.firstType');
				}
			}
		}

		//查询下级分类  下下级分类
		$childCate = D('WwwArticleClass')->getChildClassList($map['r.class_id'], 1);
		foreach ($childCate as $k => $v) {
			$ids[] = $v['id'];
			$grandChildCate = D('WwwArticleClass')->getChildClassList($v['id'], 1);
			foreach ($grandChildCate as $key => $value) {
				$ids[] = $value['id'];
			}
		}
		if (!empty($ids)) {
			$map['r.class_id'] = array('IN', $ids);
		}

		//排序
		$order = [
			'orderU' => 1,
			'orderF' => 1,
			'orderS' => 1,
			'orderT' => 1,
			'orderO' => 1
		];
		
		// UV排序
		if (!empty($_GET['orderU'])) {
			$orderU = I('get.orderU');
			if ($orderU == 1) {
				$map['order'] = 'b.search_ip desc';//1倒序
				$order['orderU'] = 2;
			} elseif ($orderU == 2) {
				$map['order'] = 'b.search_ip asc';//2正序
			}
		}
		
		// IP排序
		if (!empty($_GET['orderF'])) {
			$orderF = I('get.orderF');
			if ($orderF == 1) {
				$map['order'] = 'b.realview desc';//1倒序
				$order['orderF'] = 2;
			} elseif ($orderF == 2) {
				$map['order'] = 'b.realview asc';//2正序
			}
		}
		
		//发单量排序
		if (!empty($_GET['orderS'])) {
			$orderS = I('get.orderS');
			if ($orderS == 1) {
				$map['order'] = 'order_num desc';//1倒序
				$order['orderS'] = 2;
			} elseif ($orderS == 2) {
				$map['order'] = 'order_num asc';//2正序
			}
		}
		
		//分单量排序
		if (!empty($_GET['orderT'])) {
			$orderT = I('get.orderT');
			if ($orderT == 1) {
				$map['order'] = 'fendans desc';//1倒序
				$order['orderT'] = 2;
			} elseif ($orderT == 2) {
				$map['order'] = 'fendans asc';//2正序
			}
		}
		
		//实际分单量排序
		if (!empty($_GET['orderO'])) {
			$orderO = I('get.orderO');
			if ($orderO == 1) {
				$map['order'] = 'real_num desc';//1倒序
				$order['orderO'] = 2;
			} elseif ($orderO == 2) {
				$map['order'] = 'real_num asc';//2正序
			}
		}
		$this->assign('orders', $order);
		if (I('get.dl') == '1') {
			$pageCount = 3000;
			$result = $this->getWwwArticleList($map, $pageIndex, $pageCount);
			foreach ($result['list'] as $k => $v) {
				foreach ($result['fen_num'] as $key => $value) {
					if ($v['id'] == $value['id']) {
						$result['list'][$k]['real_fen_num'] = $value['real_num'];
					}
				}
				if (empty($result['list'][$k]['real_fen_num'])) {
					$result['list'][$k]['real_fen_num'] = 0;
				}
			}
			foreach ($result['fen_num'] as $k => $v) {
				$result['real_fen_num'] += $v['real_num'];
			}
			if (empty($result['real_fen_num'])) {
				$result['real_fen_num'] = 0;
			}
			$this->downExcel($result);
			die;
		}
		
		$result = $this->getWwwArticleList($map, $pageIndex, $pageCount);
		
		foreach ($result['list'] as $k => $v) {
			foreach ($result['fen_num'] as $key => $value) {
				if ($v['id'] == $value['id']) {
					$result['list'][$k]['real_fen_num'] = $value['real_num'];
				}
			}
			if (empty($result['list'][$k]['real_fen_num'])) {
				$result['list'][$k]['real_fen_num'] = 0;
			}
		}
		foreach ($result['fen_num'] as $k => $v) {
			$result['real_fen_num'] += $v['real_num'];
		}
		if (empty($result['real_fen_num'])) {
			$result['real_fen_num'] = 0;
		}
		
		//按时间取订单信息
		$orderNums = D('MarketContent')->getOrdersNumByTime($map);
		$info['orderNum'] = $orderNums['order_num'];
		$info['fendanNum'] = $orderNums['fendan_num'];
		
		// 取所有编辑帐号
		$info['editUsers'] = D('MarketContent')->getEditUsers();
		
		// 查询推送结果
		$linksubmit = D('MarketContent')->getLinkSubmitCon();
		
		$all_num['original'] = S('Cache:sent:wwwarticle:original');//原创剩余数量
		$all_num['normal'] = S('Cache:sent:wwwarticle:normal');//主动剩余数量
		$this->assign('all_num', $all_num);
		$this->assign('linksubmit', $linksubmit);
		$info['real_fen_num'] = $result['real_fen_num'];
		$info['firstType'] = I('get.firstType');
		$info['secondType'] = I('get.secondType');
		$info['thirdType'] = I('get.thirdType');
		//子分类
		if (!empty($info['firstType'])) {
			$secondCate = D('WwwArticleClass')->getArticleClassListByLevel($info['firstType'], 2, 1);
			$secondTypeStr = '<option value="">请选择</option>';
			foreach ($secondCate as $k => $v) {
				if ($v['id'] == $info['secondType']) {
					$secondTypeStr .= '<option value="' . $v['id'] . '" selected="selected">' . $v['name'] . '</option>';
				} else {
					$secondTypeStr .= '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
				}
			}
		}
		if (!empty($info['secondType'])) {
			$thirdCate = D('WwwArticleClass')->getArticleClassListByLevel($info['secondType'], 3, 1);
			$thirdTypeStr = '<option value="">请选择</option>';
			foreach ($thirdCate as $k => $v) {
				if ($v['id'] == $info['thirdType']) {
					$thirdTypeStr .= '<option value="' . $v['id'] . '" selected="selected">' . $v['name'] . '</option>';
				} else {
					$thirdTypeStr .= '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
				}
			}
		}
		
		if (empty($result['totaluv'])) {
			$result['totaluv'] = 0;
		}
		if (empty($result['totalview'])) {
			$result['totalview'] = 0;
		}
		if (empty($info['orderNum'])) {
			$info['orderNum'] = 0;
		}
		if (empty($info['fendanNum'])) {
			$info['fendanNum'] = 0;
		}

		$this->assign('secondTypeStr', $secondTypeStr);
		$this->assign('thirdTypeStr', $thirdTypeStr);
		$this->assign("totaluv", $result['totaluv']);
		$this->assign("totalview", $result['totalview']);
		$this->assign("totalnum", $result['totalnum']);
		$this->assign("list", $result['list']);
		$this->assign('page', $result['page']);
		$this->assign('info', $info);
		$this->display();
	}
	
	//分站文章业绩分析
	public function subart()
	{
		//获取文章分类
		$type = D("LittleArticle")->getArticleClassList();
		$this->assign('bigCate', $type);
		
		//分页
		$pageIndex = 1;
		$pageCount = 20;
		if (!empty($_GET["p"])) {
			$pageIndex = $_GET["p"];
		}
		
		//创建时间
		$crate_start = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
		$create_end = date("Y-m-d");
		
		$start_time = I('get.create_start');
		$end_time = I('get.create_end');
		if (!empty($start_time) && !empty($end_time)) {
			$map['a.createtime'] = array('between', array(strtotime($start_time), strtotime($end_time) + 86400 - 1));
		}
		
		//正式发布时间
		if (I('get.start') !== "" && I('get.end') !== "") {
			$start_time = strtotime(I('get.start'));
			$end_time = strtotime(I('get.end'));
			$end_time = mktime(23, 59, 59, date("m", $end_time), date("d", $end_time), date("Y", $end_time));
			$map['a.addtime'] = array('between', array($start_time, $end_time));
			if (empty($start_time) && empty($end_time)) {
				unset($map['a.createtime']);
			}
		}
		
		//用户
		$userid = I('get.uid');
		if (!empty($userid)) {
			$map['a.authid'] = array('EQ', $userid);
			$info['uid'] = $userid;
		}
		
		//发布类别
		$release = I('get.release');
		if (!empty($release)) {
			$map['a.pre_release'] = array('EQ', $release);
			$info['replease'] = $release;
			if ($release == 2) {
				$map['a.state'] = array('NEQ', '-1');
			}
		}
		
		//发布状态
		if (I('get.state') !== "") {
			$map['a.state'] = array('EQ', I('get.state'));
		}
		
		//2017-11-06 添加搜索条件:id、title、分类，增加分类查询
		$title = I('get.title');
		if (!empty($title)) {
			$map['_string'] = "(a.id = '" . $title . "') or (a.title like '" . $title . "%')";
		}
		if (!empty($_GET['firstType'])) {
			//三级分类
			$map['a.classid'] = I('get.firstType');
		}
		
		//IP量排序
		$order = [
			'orderF' => 1,
			'orderS' => 1,
			'orderT' => 1,
			'orderO' => 1
		];
		if (!empty($_GET['orderF'])) {
			$orderF = I('get.orderF');
			if ($orderF == 1) {
				$map['order'] = 't.realview desc';//1倒序
				$order['orderF'] = 2;
			} elseif ($orderF == 2) {
				$map['order'] = 't.realview asc';//2正序
			}
		}
		//发单量排序
		if (!empty($_GET['orderS'])) {
			$orderS = I('get.orderS');
			if ($orderS == 1) {
				$map['order'] = 'order_num desc';//1倒序
				$order['orderS'] = 2;
			} elseif ($orderS == 2) {
				$map['order'] = 'order_num asc';//2正序
			}
		}
		//分单量排序
		if (!empty($_GET['orderT'])) {
			$orderT = I('get.orderT');
			if ($orderT == 1) {
				$map['order'] = 'fendans desc';//1倒序
				$order['orderT'] = 2;
			} elseif ($orderT == 2) {
				$map['order'] = 'fendans asc';//2正序
			}
		}
		//实际分单量排序
		if (!empty($_GET['orderO'])) {
			$orderO = I('get.orderO');
			if ($orderO == 1) {
				$map['order'] = 'real_num desc';//1倒序
				$order['orderO'] = 2;
			} elseif ($orderO == 2) {
				$map['order'] = 'real_num asc';//2正序
			}
		}
		$this->assign('orders', $order);
		//如果是下载操作
		if (I('get.dl') == '1') {
			$pageCount = 3000;
			$result = $this->getSubArticleList($map, $pageIndex, $pageCount);
			foreach ($result['list'] as $k => $v) {
				foreach ($result['fen_num'] as $key => $value) {
					if ($v['id'] == $value['id']) {
						$result['list'][$k]['real_fen_num'] = $value['real_num'];
					}
				}
				if (empty($result['list'][$k]['real_fen_num'])) {
					$result['list'][$k]['real_fen_num'] = 0;
				}
			}
			foreach ($result['fen_num'] as $k => $v) {
				$result['real_fen_num'] += $v['real_num'];
			}
			if (empty($result['real_fen_num'])) {
				$result['real_fen_num'] = 0;
			}
			$this->downSubExcel($result);
			die;
		}
		
		$result = $this->getSubArticleList($map, $pageIndex, $pageCount);
		foreach ($result['list'] as $k => $v) {
			foreach ($result['fen_num'] as $key => $value) {
				if ($v['id'] == $value['id']) {
					$result['list'][$k]['real_fen_num'] = $value['real_num'];
				}
			}
			if (empty($result['list'][$k]['real_fen_num'])) {
				$result['list'][$k]['real_fen_num'] = 0;
			}
		}
		foreach ($result['fen_num'] as $k => $v) {
			$result['real_fen_num'] += $v['real_num'];
		}
		if (empty($result['real_fen_num'])) {
			$result['real_fen_num'] = 0;
		}
		//按时间取订单信息
		$orderNums = D('MarketContent')->getSubOrdersNumByTime($map);
		$info['orderNum'] = $orderNums['order_num'];
		$info['fendanNum'] = $orderNums['fendan_num'];
		
		//取所有编辑帐号
		$info['editUsers'] = D('MarketContent')->getEditUsers();
		$info['real_fen_num'] = $result['real_fen_num'];
		$info['firstType'] = I('get.firstType');
		if (empty($result['totalview'])) {
			$result['totalview'] = 0;
		}
		if (empty($info['orderNum'])) {
			$info['orderNum'] = 0;
		}
		if (empty($info['fendanNum'])) {
			$info['fendanNum'] = 0;
		}
		$this->assign("totalview", $result['totalview']);
		$this->assign("totalnum", $result['totalnum']);
		$this->assign("list", $result['list']);
		$this->assign('page', $result['page']);
		$this->assign('info', $info);
		$this->display();
	}
	
	//文章分类、分站文章统计
	public function artstat()
	{
		
		//文章分类统计
		$articleStat = $this->getArticleStat();
		
		//分站文章统计
		$subarticleStat = $this->getSubArticleStat();
		
		$this->assign("subarticleStat", $subarticleStat);
		$this->assign("articleStat", $articleStat);
		$this->display();
	}
	
	//美图业绩分析
	public function meitu()
	{
        $meiTuLogic = new MeituV2LogicModel();
		//分页
		$pageIndex = 1;
		$pageCount = 20;
		$page = I('get.p');
		if (!empty($page)) {
			$pageIndex = $page;
		}
		
		//开始时间和结束时间
		$start_time = I('get.start');
		$end_time = I('get.end');
		
		if (!empty($start_time)) {
			$date = strtotime($start_time);
			$start_time = mktime(0, 0, 0, date("m", $date), date("d", $date), date("Y", $date));
		} else {
			$start_time = mktime(0, 0, 0, date("m"), 1, date("Y"));
		}
		
		if (!empty($end_time)) {
			$date = strtotime($end_time);
			$end_time = mktime(23, 59, 59, date("m", $date), date("d", $date), date("Y", $date));
		} else {
			$end_time = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
		}
		
		$map['created_at'] = array('EGT', $start_time);
		$map['created_at'] = array('ELT', $end_time);
		
		$time = $end_time - $start_time;
		if ($time < '1') {
			$this->error('开始时间不能大于结束时间');
		}
		if ($time > 2678500) {
			$this->error('查询时间不能大于31天');
		}
		
		if (!empty($start_time) && !empty($end_time)) {
			$map['created_at'] = array('between', array($start_time, $end_time));
		}

		//美图分类
        $type = I('get.type');
		if (!empty($type)) {
		    $map['type'] = $type;
		    $info['type'] = $type;
        }
		
		$keywords = I('get.keywords');
		if (!empty($keywords)) {
			$map['keywords'] = $keywords;
			$info['keywords'] = $keywords;
		}
		
		$userid = I('get.uid');
		if (!empty($userid)) {
			$map['creator'] = array('EQ', $userid);
			$info['uid'] = $userid;
		}
		
		$state = I('get.state');
		if (!empty($state)) {
			$map['state'] = array('EQ', $state);
			$info['state'] = $state;
		}
		
		$orderNum = I('get.orderCount');
		if (!empty($orderNum)) {
			$map['orderNum'] = $orderNum;
			$info['orderCount'] = $orderNum;
		}

		//如果是下载操作
		if (I('get.dl') == '1') {
			$pageCount = 30000;
			$result = $this->getMeituList($map, 1, $pageCount);
			$this->downMeituExcel($result);
			die;
		}
		$result = $this->getMeituList($map, $pageIndex, $pageCount);

        //数据图数据
		$chartData = $this->getMeituChart($map);



		//取所有编辑帐号 716
        $meiTuLogic = new MeituV2LogicModel();
        $info['editUsers'] = $meiTuLogic->getMeituUsers();
		
		$info['start_time'] = date('Y-m-d', $start_time);
		$info['end_time'] = date('Y-m-d', $end_time);
		$info['count'] = $result['count'];


		$this->assign("totalnum", $result['totalnum']);
		$this->assign("list", $result['list']);
		$this->assign('page', $result['page']);
		$this->assign('chartData', $chartData);
		$this->assign('info', $info);
		$this->display();
	}
	
	/**
	 * 获取美图统计图数据
	 * @param  [type] $map [description]
	 * @return [type]      [description]
	 */
	public function getMeituChart($map)
	{
		$meiTuLogic = new MeituV2LogicModel();
        $_result = $meiTuLogic->getMarketContentMeituByDay($map);
		
		foreach ($_result as $k => $v) {
			$dateValue[] = date('d', strtotime($v['days'])) . '号';
			$dateList[$v['days']]['meitu_num'] = $v['meitu_num'];
		}
		foreach ($dateList as $k => $v) {
			$_chartData['meitu_num'][$k] = empty($v['meitu_num']) ? '0' : $v['meitu_num'];
		}
		foreach ($_chartData as $k => $v) {
			foreach ($v as $key => $value) {
				$chartData[$k][] = $value;
			}
		}
		
		$chartData['meitu'] = implode(',', $chartData['meitu_num']);
		
		$dateValue = implode("','", $dateValue);
		
		return array('dateList' => $dateValue, 'chartData' => $chartData);
	}
	
	public function wenda()
	{
		
		//获取问答分类
		$category = $this->getWenDaCategory();
		$list = $this->getWenDaList(I("get.begin"), I("get.end"), I("get.title"), I("get.type"), I("get.sub_type"));
		$list["info"]["begin"] = I("get.begin") == "" ? date("Y-m-1") : I("get.begin");
		$list["info"]["end"] = I("get.end") == "" ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) : I("get.end");
		
		$this->assign("category", $category);
		$this->assign("list", $list["list"]);
		$this->assign("page", $list["page"]);
		$this->assign("date", $list["date"]);
		$this->assign("data", $list["data"]);
		$this->assign("info", $list["info"]);
		$this->display();
	}
	
	//获取美图列表并分页
	private function getMeituList($condition, $pageIndex = 1, $pageCount = 20)
	{
		$meiTuLogic = new MeituV2LogicModel();

        $count = $meiTuLogic->getMarketContentMeituCount($condition);

		if ($count > $pageCount) {
			import('Library.Org.Util.Page');
			$page = new \Page($count, $pageCount);
			$result['page'] = $page->show();
			$pageIndex = $page->nowPage;
		}
		$result['list'] = $meiTuLogic->getMarketContentMeituList($condition, ($pageIndex - 1) * $pageCount, $pageCount);
		
		$result['count'] = $count;
		return $result;
	}
	
	//获取列表并分页
	private function getWwwArticleList($map, $pageIndex = 1, $pageCount = 10)
	{
		//强制数字整数
		$pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
		$pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
		
		$count = D('MarketContent')->getWwwArticleCount($map);
		
		$result['list'] = D('MarketContent')->getWwwArticleList($map, ($pageIndex - 1) * $pageCount, $pageCount, '');
		
		$totalnum = D('WwwArticle')->getTotalNum($map);
		$totalSearchIPNum = D('WwwArticle')->getTotalSearchIP($map);
		
		//查询所有实际分单数
		$realnum = D('MarketContent')->getRealFenByTime($map);
		
		if ($count > $pagecount) {
			import('Library.Org.Util.Page');
			$page = new \Page($count, $pageCount);
			$result['page'] = $page->show();
		}
		$result['fen_num'] = $realnum;
		$result['totaluv'] = $totalSearchIPNum[0]['totalnum'];
		$result['totalview'] = $totalnum[0]['totalnum'];
		$result['totalnum'] = $count;
		return $result;
	}
	
	//获取分站文章列表并分页
	private function getSubArticleList($map, $pageIndex = 1, $pageCount = 10)
	{
		
		$pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
		$pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
		
		$count = D('MarketContent')->getSubArticleListCount($map);
		$result['list'] = D('MarketContent')->getSubArticleList($map, ($pageIndex - 1) * $pageCount, $pageCount, '');
		
		$totalnum = D('MarketContent')->getSubArticleUVNum($map);
		
		//查询所有实际分单数
		$realnum = D('MarketContent')->getSubRealFenByTime($map);
		
		if ($count > $pagecount) {
			import('Library.Org.Util.Page');
			$page = new \Page($count, $pageCount);
			$result['page'] = $page->show();
		}
		$result['fen_num'] = $realnum;
		$result['totalview'] = $totalnum[0]['totalnum'];
		$result['totalnum'] = $count;
		return $result;
	}
	
	//文章分类统计
	private function getArticleStat()
	{
		$result = D('WwwArticle')->getArticleStat();
		
		foreach ($result as $key => $value) {
			$list[$value["id"]]["id"] = $value["id"];
			$list[$value["id"]]["name"] = $value["classname"];
			$list[$value["id"]]["child"][$value["second_id"]]["name"] = $value["second_name"];
			$list[$value["id"]]["child"][$value["second_id"]]["id"] = $value["second_id"];
			if (!empty($value["third_id"])) {
				$list[$value["id"]]["child"][$value["second_id"]]["child"][$value["third_id"]]["name"] = $value["third_name"];
				$list[$value["id"]]["child"][$value["second_id"]]["child"][$value["third_id"]]["id"] = $value["third_id"];
			}
			
			if (!empty($value["article_id"])) {
				$list[$value["id"]]["count"]++;
				$list[$value["id"]]["child"][$value["second_id"]]["count"]++;
				if (!empty($value["third_id"])) {
					$list[$value["id"]]["child"][$value["second_id"]]["child"][$value["third_id"]]["count"]++;
				}
			}
		}
		return $list;
	}
	
	/**
	 * 获取分站文章统计
	 * @return [type] [description]
	 */
	private function getSubArticleStat()
	{
		$result = D('LittleArticle')->getArticleStat();
		$index = 0;
		foreach ($result as $key => $value) {
			if ($key % 9 == 0) {
				$index++;
			}
			
			$list[$index][] = $value;
		}
		
		return $list;
	}
	
	//下载美图Excel
	public function downMeituExcel($list)
	{
		import('Library.Org.Phpexcel.PHPExcel', "", ".php");
		import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', "", ".php");
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		$cacheSettings = array('cacheTime' => 300);
		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		$phpExcel = new \PHPExcel();
		
		//设置表头
		$title = array(
			'编号',
			'标题',
			'URL',
			'创建时间',
			'美图类型',
			'发布时间',
			'发布人',
			'状态',
		);
		
		$i = 0;
		foreach ($title as $key => $value) {
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$value);
		}
		
		//设置表内容
		$j = 1;
		foreach ($list['list'] as $k => $v) {
			//初始化$i
			$i = 0;
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['id']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['title']);

			//美图类型
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			if($v['type'] == 1){    //公装
                $phpExcel->getActiveSheet()->setCellValue($num, 'https://'.C('QZ_YUMINGWWW').'/tu/g' . (string)$v['id'] . '.html');
            }else{
                $phpExcel->getActiveSheet()->setCellValue($num, 'https://'.C('QZ_YUMINGWWW').'/tu/j' . (string)$v['id'] . '.html');
            }

			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['created_at']));
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            if($v['type'] == 1){    //公装
                $phpExcel->getActiveSheet()->setCellValue($num, '公装美图');
            }else{
                $phpExcel->getActiveSheet()->setCellValue($num, '家装美图');
            }
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['publish_time']));
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['uname']);
			//美图状态：1:未发布，2:预发布，3:已发布',
			if ($v['state'] == 1) {
				$type = '未审核';
			}
			if ($v['state'] == 2) {
				$type = '预发布';
			}
			if ($v['state'] == 3) {
				$type = '正常';
			}
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$type);
			
			$j++;
		}
		ob_end_clean();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="美图业绩分析.xls"');
		header("Content-Transfer-Encoding:binary");
		$writer = new \PHPExcel_Writer_Excel2007($phpExcel);
		$writer->save('php://output');
		exit();
	}
	
	//下载Excel
	public function downExcel($list)
	{
		import('Library.Org.Phpexcel.PHPExcel', "", ".php");
		import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', "", ".php");
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		$cacheSettings = array('cacheTime' => 300);
		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		$phpExcel = new \PHPExcel();
		
		//设置表头
		$title = array(
			'编号',
			'标题',
			'关键词',
			'URL',
			'创建时间',
			'正式发布时间',
			'发布人',
			'分类',
			'UV量',
			'IP量',
			'发单量',
			'分单量',
			'实际分单量',
			'发布类别',
			'发布状态'
		);
		
		$i = 0;
		foreach ($title as $key => $value) {
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$value);
		}
		//设置表内容
		$j = 1;
		foreach ($list['list'] as $k => $v) {
			//初始化$i
			$i = 0;
			
			$url = 'http://www.' . C('QZ_YUMING') . '/gonglue/' . $v['shortname'] . '/' . $v['id'] . '.html';
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['id']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['title']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['keywords']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$url);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['createtime']));
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['addtime']));
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['uname']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['classname']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['search_ip']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['realview']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['order_num']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['fendans']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['real_fen_num']);
			
			if ($v['pre_release'] == 1) {
				$type = '正式发布';
			} else {
				$type = '预发布';
			}
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$type);
			
			if ($v['state'] == 2) {
				$type = '已发布';
			} else {
				$type = '未发布';
			}
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$type);
			
			$j++;
		}
		ob_end_clean();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="主站文章业绩分析.xls"');
		header("Content-Transfer-Encoding:binary");
		$writer = new \PHPExcel_Writer_Excel2007($phpExcel);
		$writer->save('php://output');
		exit();
	}
	
	//下载分站文章Excel
	public function downSubExcel($list)
	{
		
		import('Library.Org.Phpexcel.PHPExcel', "", ".php");
		import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', "", ".php");
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		$cacheSettings = array('cacheTime' => 300);
		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		$phpExcel = new \PHPExcel();
		
		//设置表头
		$title = array(
			'编号',
			'标题',
			'关键词',
			'URL',
			'创建时间',
			'发布时间',
			'发布人',
			'分类',
			'IP量',
			'发单量',
			'分单量',
			'实际分单量',
			'发布类别',
			'发布状态'
		);
		
		$i = 0;
		foreach ($title as $key => $value) {
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$value);
		}
		
		//设置表内容
		$j = 1;
		foreach ($list['list'] as $k => $v) {
			//初始化$i
			$i = 0;
			
			$url = 'http://' . $v['bm'] . '.qizuang.com/zxinfo/' . $v['id'] . '.html';
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['id']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['title']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['keywords']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$url);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['createtime']));
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['addtime']));
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['name']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['typename']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['realview']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['order_num']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['fendans']);
			
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$v['real_fen_num']);
			
			if ($v['pre_release'] == 1) {
				$type = '直接发布';
			} else {
				$type = '预发布';
			}
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$type);
			
			if ($v['state'] == 2) {
				$type = '已发布';
			} else {
				$type = '未发布';
			}
			$num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
			$phpExcel->getActiveSheet()->setCellValue($num, (string)$type);
			
			$j++;
		}
		
		ob_end_clean();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="分站文章业绩分析.xls"');
		header("Content-Transfer-Encoding:binary");
		$writer = new \PHPExcel_Writer_Excel2007($phpExcel);
		$writer->save('php://output');
		exit();
	}
	
	//获取相关分类
	public function getNextTypes()
	{
		$id = I('post.id');
		$level = I('post.level');
		
		$categorytree = D('WwwArticleClass')->getArticleClassListByLevel($id, $level, 1);
		$str = '<option value="">请选择</option>';
		foreach ($categorytree as $k => $v) {
			$str .= '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
		}
		//$sql = M()->getlastSql();
		$this->ajaxReturn(array('data' => $str, "info" => 'success', "status" => 1));
	}
	
	//新增百科业绩分析
	public function baike()
	{
		//获取百科分类
		$category = $this->getCategoryTree();
		//主站编辑/美图编辑 1177添加 编辑组长/兼职编辑
        $users = D("Adminuser")->getAdminuserListByUid([24, 26, 69, 151]);
//		foreach ($users as $key => $value) {
//			$ids[] = $value["id"];
//		}
        $ids = array_column($users,'id');

        $roleLogic = new RbacRoleLogicModel();
        $roles = $roleLogic->getRoleInfo();
        $this->assign('roles', $roles);
		//获取百科列表
		$list = $this->getBaikeList(I("get.begin"), I("get.end"), I("get.category"), I("get.sub_category"), I("get.title"), I("get.uid"), $ids, I('get.role_id'));
		$this->assign("date", $list["date"]);
		$this->assign("data", $list["data"]);
		$this->assign("list", $list["list"]);
		$this->assign("page", $list["page"]);
		$this->assign("users", $users);
		$this->assign("category", json_encode($category));
		$this->display();
	}
	
	/**
	 * 获取问答分类列表
	 * @return [type] [description]
	 */
	private function getWenDaCategory()
	{
		$result = D("Adminask")->getCategory();
		foreach ($result as $key => $value) {
			if ($value["pid"] == 0) {
				$list[$value["cid"]]["name"] = $value["name"];
				$list[$value["cid"]]["cid"] = $value["cid"];
			} else {
				$list[$value["pid"]]["child"][] = array(
					"name" => $value["name"],
					"cid" => $value["cid"]
				);
			}
		}
		return $list;
	}
	
	/**
	 * 获取问答数据
	 * @param string $value [description]
	 * @return [type]        [description]
	 */
	private function getWenDaList($begin, $end, $title, $type, $sub_type)
	{
		//获取问题数
		$monthStart = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$monthEnd = mktime(23, 59, 59, date("m"), date("d"), date("Y")) + 1;
		if (!empty($begin) && !empty($end)) {
			$monthStart = strtotime($begin);
			$monthEnd = strtotime($end) + 86400;
		}
		
		$offset = ($monthEnd - $monthStart) / 86400;
		if ($offset > 31) {
			$this->error('查询时间不能大于31天');
		}
		
		// $t = date("t",$monthStart);
		
		$count = D("Adminask")->getAskListCount($monthStart, $monthEnd, $title, $type, $sub_type);
		
		if ($count > 0) {
			import('Library.Org.Util.Page');
			$p = new \Page($count, 20);
			$p->setConfig('prev', "上一页");
			$p->setConfig('next', '下一页');
			$show = $p->show();
			$result = D("Adminask")->getAskList($monthStart, $monthEnd, $title, $type, $sub_type, $p->firstRow, $p->listRows);
			
			foreach ($result as $key => $value) {
				$list[$value["id"]] = $value;
			}
		}
		
		//获取统计数据
		$result = D("Adminask")->getAskList($monthStart, $monthEnd, $title, $type, $sub_type);
		foreach ($result as $key => $value) {
			$ask[$value["date"]]++;
			$ids[$value["id"]] = $value["id"];
			$info["ask"]++;
		}
		
		
		//获取页面采集数据
		$result = D("MarketSummary")->getWenDaList($monthStart, $monthEnd);
		
		$reg = '/\d+/';
		foreach ($result as $key => $value) {
			preg_match($reg, $value["url"], $m);
			if (array_key_exists($m[0], $list)) {
				$list[$m[0]]["order_count"] += $value["order_count"];
				$list[$m[0]]["real_order_count"] += $value["real_order_count"];
				$list[$m[0]]["uv"] += $value["uv"];
			}
			
			if (array_key_exists($m[0], $ids)) {
				$order[$value["day"]] += $value["order_count"];
				$real[$value["day"]] += $value["real_order_count"];
				$pv[$value["day"]] += $value["uv"];
				
				$info["order_count"] += $value["order_count"];
				$info["real_order_count"] += $value["real_order_count"];
				$info["uv"] += $value["uv"];
			}
		}
		for ($i = 0; $i < $offset; $i++) {
			$day = date("Y-m-d", strtotime("+" . $i . " day", $monthStart));
			$date[] = $day;
			$data["ask"][] = empty($ask[$day]) ? 0 : $ask[$day];
			$data["order"][] = empty($order[$day]) ? 0 : $order[$day];
			$data["real"][] = empty($real[$day]) ? 0 : $real[$day];
			$data["uv"][] = empty($pv[$day]) ? 0 : $pv[$day];
		}
		
		
		return array("list" => $list, "page" => $show, "date" => $date, "data" => $data, "info" => $info);
	}
	
	/**
	 * 百科分类树
	 * @return [type] [description]
	 */
	private function getCategoryTree()
	{
		$temp = D('Adminbaike')->getCategorys();
		$temp_category = array();
		$category = array(
			array(
				'cid' => 0,
				'name' => '请选择',
				'children' => array(
					array(
						'cid' => 0,
						'name' => '请选择'
					)
				)
			)
		);
		foreach ($temp as $key => $value) {
			if (empty($value['pid'])) {
				$temp_category[$value['cid']] = $value;
			}
		}
		foreach ($temp as $key => $value) {
			if (!empty($value['pid'])) {
				$temp_category[$value['pid']]['children'][] = $value;
			}
		}
		foreach ($temp_category as $key => $value) {
			array_unshift($value['children'], array(
				'cid' => 0,
				'name' => '请选择'
			));
			$category[] = $value;
		}
		return $category;
	}
	
	/**
	 * 获取百科列表
	 * @param  [type] $beign        [开始时间]
	 * @param  [type] $end          [结束时间]
	 * @param  [type] $category     [第一分类]
	 * @param  [type] $sub_category [第二分类]
	 * @param  [type] $title        [标题/ID]
	 * @return [type]               [description]
	 */
	public function getBaikeList($begin, $end, $category, $sub_category, $title, $uid, $ids, $role_id)
	{
		ini_set('memory_limit', '512M');
		$monthStart = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$monthEnd = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
		if (!empty($begin) && !empty($end)) {
			$monthStart = strtotime($begin);
			$monthEnd = strtotime("+1 day", strtotime($end)) - 1;
		} else {
			$month = date("m", $monthStart);
			if ($month == date("m") && date("d") < date("t")) {
				$monthEnd = strtotime(date("Y-m-d")) + 86400 - 1;
			}
		}
		
		//获取百科列表
		$count = D("Adminbaike")->getBaikeStatListCount($monthStart, $monthEnd, $category, $sub_category, $title, $uid, $ids, $role_id);
		if ($count > 0) {
			import('Library.Org.Util.Page');
			$p = new \Page($count, 20);
			$show = $p->show();
			$result = D("Adminbaike")->getBaikeStatList($monthStart, $monthEnd, $category, $sub_category, $title, $uid, $ids, $role_id, $p->firstRow, $p->listRows);
			foreach ($result as $key => $value) {
				$value["nickname"] = $value["name"];
				$list[$value["id"]] = $value;
			}
			$data["item"]["count"] = $count;
			//获取图表数据
			$result = D("Adminbaike")->getBaikeStatListByDay($monthStart, $monthEnd);
			foreach ($result as $key => $value) {
				$counts[$value["date"]] = $value["count"];
			}
			
			//查询所有的数据
			$result = D("Adminbaike")->getBaikeStatList($monthStart, $monthEnd, $category, $sub_category, $title, $uid, $ids, $role_id);
			foreach ($result as $key => $value) {
				$all[$value["id"]] = $value["id"];
			}
			
			//获取发单量、分单量、IP等数据
			$result = D("MarketSummary")->getBaikeList($monthStart, $monthEnd);
			foreach ($result as $key => $value) {
				$exp = array_filter(explode("/", $value["url"]));
				$baikeId = str_replace(".html", "", $exp[2]);
				if (array_key_exists($baikeId, $list)) {
					$list[$baikeId]["uv"] += $value["uv"];
					$list[$baikeId]["order_count"] += $value["order_count"];
					$list[$baikeId]["real_order_count"] += $value["real_order_count"];
				}
				if (array_key_exists($baikeId, $all)) {
					$data["item"]["uv"] += $value["uv"];
					$data["item"]["order_count"] += $value["order_count"];
					$data["item"]["real_order_count"] += $value["real_order_count"];
					$uv[$value["day"]] += $value["uv"];
					$order[$value["day"]] += $value["order_count"];
					$real_order[$value["day"]] += $value["real_order_count"];
				}
			}
			
			//获取时间
			$offset = ($monthEnd - $monthStart) / 86400;
			if ($offset > 31) {
				$this->error('查询时间不能大于31天');
			}
			
			for ($i = 0; $i < $offset; $i++) {
				$day = date("Y-m-d", strtotime("+" . $i . " day", $monthStart));
				$date[] = date("d号", strtotime("+" . $i . " day", $monthStart));
				$data["count"][] = empty($counts[$day]) ? 0 : $counts[$day];
				$data["order"][] = empty($order[$day]) ? 0 : $order[$day];
				$data["real"][] = empty($real_order_count[$day]) ? 0 : $real_order_count[$day];
				$data["uv"][] = empty($uv[$day]) ? 0 : $uv[$day];
			}
		}
		
		return array("list" => $list, "page" => $show, "data" => $data, "date" => $date);
	}
	
	//关键字查重
	public function keyword()
	{
		$get = I('get.');
		//展示页面类型
		$viewtype = [1 => '装修攻略详情页', 2 => '装修百科详情页', 3 => '装修问答详情页', 4 => '装修百科标题页'];
		if (empty($get['start_time']) && empty($get['end_time'])) {
			$get['start_time'] = date("Y-m-d", strtotime("-30 day"));
			$get['end_time'] = date('Y-m-d 23:59:59');
		}
		if (!in_array($get['type'], [1, 2, 3, 4])) {
			$get['type'] = 0;
		}
		$marketContentLogic = new MarketContentLogicModel();
		
		$keywordList = $marketContentLogic->getKeyWords();
		if (count($keywordList) > 0) {
			//获取攻略标题
			$list['article'] = [];
			if ($get['type'] == 1) {
				$list['article'] = $marketContentLogic->getArticleKeywords($get['start_time'], $get['end_time']);  // 攻略
			}
			//获取百科条目
			$list['baike'] = [];
			if ($get['type'] == 2) {
				$list['baike'] = $marketContentLogic->getBaikeItemKeywords($get['start_time'], $get['end_time']); // 百科
			}
			//获取问答子标题
			$list['wenda'] = [];
			if ($get['type'] == 3) {
				$list['wenda'] = $marketContentLogic->getAskKeywords($get['start_time'], $get['end_time']); // 问答
			}
			
			//获取百科标题页
			$list['baike_title'] = [];
			if ($get['type'] == 4) {
				$list['baike_title'] = $marketContentLogic->getBaikeTitleKeywords($get['start_time'], $get['end_time']); // 百科
			}
			
			//获取问答子标题
			if ($get['type'] == 0) {
				$list['article'] = $marketContentLogic->getArticleKeywords($get['start_time'], $get['end_time']);  // 攻略
				$list['baike'] = $marketContentLogic->getBaikeItemKeywords($get['start_time'], $get['end_time']); // 百科
				$list['baike_title'] = $marketContentLogic->getBaikeTitleKeywords($get['start_time'], $get['end_time']); // 百科
				$list['wenda'] = $marketContentLogic->getAskKeywords($get['start_time'], $get['end_time']); // 问答
			}
			
			//获取关键字出现次数
			$info = $marketContentLogic->calcKeywordCount($keywordList, $list['article'], $list['baike'], $list['wenda'], $list['baike_title']);
			$this->assign('info', $info);
		}
		
		$this->assign('viewtype', $viewtype);
		$this->display();
	}
	
	
	/**
	 * 关键字查重-导入
	 */
	public function uploadExcel()
	{
		//分析Excel内容
		$ex = $_FILES['excel'];
		$filename = TEMP_PATH . '/' . time() . substr($ex['name'], stripos($ex['name'], '.'));
		move_uploaded_file($ex['tmp_name'], $filename);
		$excel = importExcel($filename);
		
		if (count($excel['0']) != 2) {
			$this->ajaxReturn(array('data' => '', 'info' => '数据格式不正确', 'status' => 'error'));
		}
		unset($excel['0']);
		
		//逐行导入数据
		$exist = [];
		foreach ($excel as $k => $v) {
			if (empty($v) || empty($v[1])) {
				continue;
			}
			if (!in_array($v[1], $exist)) {
				$exist[] = $v[1];
			}
		}
		
		if (count($exist) > 0) {
			//清空
			M('check_keyword')->where(1)->delete();
			//添加
			foreach ($exist as $val) {
				$data[]['keyword'] = $val;
			}
			M('check_keyword')->addAll($data);
			$this->ajaxReturn(array('data' => '', 'info' => '操作成功', 'status' => 0));
		} else {
			$this->ajaxReturn(array('data' => '', 'info' => '导入失败', 'status' => 1));
		}
		
		
	}
}