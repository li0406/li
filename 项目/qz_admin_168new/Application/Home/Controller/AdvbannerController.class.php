<?php

//全屏轮播 通栏广告控制器

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class AdvbannerController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','0');
    }

    //首页
    public function index(){

        //将城市排序
        $citys = getUserCitys();
        $this->assign("citys",$citys);
        $sortby = array(
            '1'=> array('id'=> '1','name'=> '标题','sql'=> 'a.company_name DESC'),
            '2'=> array('id'=> '2','name'=> '城市','sql'=> 'a.city_id DESC'),
            '3'=> array('id'=> '3','name'=> '状态','sql'=> 'a.status DESC'),
            '4'=> array('id'=> '4','name'=> '增加时间','sql'=> 'a.op_time DESC'),
            '5'=> array('id'=> '5','name'=> '开始时间','sql'=> 'a.start_time DESC'),
            '6'=> array('id'=> '6','name'=> '结束时间','sql'=> 'a.end_time DESC'),
        );

        //分页
        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
         //状态
        $status = I('get.status');
        if(!empty($status)){
            if($status == '2'){
                $status = '0';
            }
            $condition['a.status'] = array("EQ",$status);
        }else{
            $_GET['status'] = 1;
            $condition['a.status'] = array("EQ",1);
        }
        //排序方式
        $order = I('get.order');

        if (!empty($order)) {
            switch ($order) {
                case '1':
                    $condition['orderBy'] = 'a.sort asc';
                    break;
                case '2':
                    $condition['orderBy'] = 'a.sort desc';
                    break;
                case '3':
                    $condition['orderBy'] = '';
                    break;
            }
        } else {
            $_GET['order'] = 1;
            $condition['orderBy'] = 'a.sort asc';
        }
        //筛选时间
        $choose_time = I('get.choose_time');
        if (!empty($choose_time)) {
            $condition['a.end_time'] = ['EGT', strtotime($choose_time)];
        }
        //城市
        $cityId = I('get.city_id');
        if(!empty($cityId) || $cityId == '0'){
            $condition['a.city_id']  = array("EQ",I('get.city_id'));
        }
        //标题
        $title = I('get.title');
        if(!empty($title)){
            $condition['a.title'] = array('like','%'.I('get.title').'%');
        }

        //搜索
        if(!empty($_GET["company"])){
            $search['a.company_id'] = array('like','%'.I('get.company').'%');
            $search['a.company_name'] = array('like','%'.I('get.company').'%');
            $search['_logic'] = 'OR';
            $condition['_complex'] = $search;
        }
        $condition['a.module'] = array('LIKE',"%home_advbanner%");

        $result = $this->getList($condition,$pageIndex,$pageCount);

        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //增加轮播推荐 轮播：每个城市5个上限
    public function add(){
        $Db = D('Advbanner');

        if(IS_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['company_id'] = $post['company_id'];
            $data['company_name'] = $post['company_name'];
            $data['city_id'] = $post['city_name'];
            $data['img_url'] = $post['img_url'];
            $data['url'] = $post['url'];
            $data['img_url_mobile'] = $post['img_url_mobile'];
            $data['url_mobile'] = $post['url_mobile'];
            $data['img_url_applet'] = $post['img_url_applet']?:'';
            $data['url_applet'] = $post['url_applet']?:'';
            $data['description'] = preg_replace("@\s@is",'',$post['description']);
            $data['sort'] = $post['sort'];
            $data['status'] = $post['status'];
            $data['module'] = 'home_advbanner';
            $data['adv_type'] = $post['adv_type'];
            $data['start_time'] = strtotime($post['start_time']);
            //自动加到当天最后一秒 a.17.10.11新首页管理-轮播广告-时间精确度调整
            $data['end_time'] = strtotime($post['end_time']) + 86399;
            $data['op_time'] = time();
            $data['op_uid'] = session('uc_userinfo.id');
            $data['op_uname'] = session('uc_userinfo.name');
            switch ($post['adv_type']){
                case 1:
                    $data['module'] = 'home_advbanner';
                    break;
                case 2:
                    $data['module'] = 'home_advbanner_a';
                    if(!$post['company_id']){
                        $this->_error('装修公司不能为空 :(');
                    }
                    break;
                case 3:
                    $data['module'] = 'home_advbanner_applet';
                    break;
            }

            if(!in_array(session("uc_userinfo.uid"),getAllCityManager())){
                if($data['sort'] < 3){
                    //$this->_error('排序位置只能选择3，4，5号位！');
                }
            }

            $advBanners = D('Advbanner')->getActiveAdvBanners('home_advbanner',$data['city_id']);
            //$count = count($advBanners);

            if($count > 5){
                //$this->_error('在线轮播展示张数已达限，最早可以在***时间新增轮播。');
            }

            if(mb_strlen($data['title'],'utf-8') > 55){
                $this->_error('标题最多输入55个字 :(');
            }

            $result = $Db->addBanner($data);
            if ($result){
                $log = array(
                    'remark' => '增加轮播',
                    'logtype' => 'home_advbanner',
                    'action_id' => $result,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);
                $this->success('增加轮播成功 :)','/advbanner');
            }else{
                $this->_error('增加轮播失败 :)');
            }
        }else{
            $info['city_id'] = I('get.cityid');

            $this->assign("info",$info);
            $this->assign("citys",getUserCitys());
            $this->display();
        }
    }

    //修改轮播推荐
    public function edit(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('不是正确的轮播 :(');
        }
        $Db = D('Advbanner');
        $advBanner = $Db->getBannerById($id,'home_advbanner');
        if(empty($advBanner)){
            $this->_error('不是正确的轮播 :(');
        }

        if(IS_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['company_id'] = $post['company_id'];
            $data['company_name'] = $post['company_name'];
            $data['city_id'] = $post['city_name'];
            $data['img_url'] = $post['img_url'];
            $data['url'] = $post['url'];
            $data['img_url_mobile'] = $post['img_url_mobile'];
            $data['url_mobile'] = $post['url_mobile'];
            $data['img_url_applet'] = $post['img_url_applet']?:'';
            $data['url_applet'] = $post['url_applet']?:'';
            $data['description'] = $data['description'] = preg_replace("@\s@is",'',$post['description']);
            $data['sort'] = $post['sort'];
            $data['status'] = $post['status'];
            $data['module'] = 'home_advbanner';
            $data['start_time'] = strtotime($post['start_time']);
            //自动加到当天最后一秒 a.17.10.11新首页管理-轮播广告-时间精确度调整
            $data['end_time'] = strtotime($post['end_time']) + 86399;
            $data['op_time'] = time();
            $data['op_uid'] = session('uc_userinfo.id');
            $data['op_uname'] = session('uc_userinfo.name');
            switch ($post['adv_type']){
                case 1:
                    $data['module'] = 'home_advbanner';
                    break;
                case 2:
                    $data['module'] = 'home_advbanner_a';
                    break;
                case 3:
                    $data['module'] = 'home_advbanner_applet';
                    break;
            }

            if(!in_array(session("uc_userinfo.uid"),getAllCityManager())){
                if($data['sort'] < 3){
                    //$this->_error('排序位置只能选择3，4，5号位！');
                }
            }

            if(mb_strlen($data['title'],'utf-8') > 55){
                $this->_error('标题最多输入55个字 :(');
            }
            if ($Db->editBanner($id,$data)){
                $log = array(
                    'remark' => '编辑轮播',
                    'logtype' => 'home_advbanner',
                    'action_id' => $id,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);
                $this->success('修改轮播成功 :)','/advbanner');
            }else{
                $this->_error('修改轮播失败 :)');
            }

        }else{
            $advBanner['company'] = empty($advBanner['company_id']) ? '' : $advBanner['company_id'].'|'.$advBanner['company_name'];
            $citys = getUserCitys();
            $this->assign("info",$advBanner);
            $this->assign("citys",$citys);
            $this->display();
        }
    }

    //轮播日志
    public function lunbolog(){
        $cid = I('get.companyid');

        $pageIndex = intval(I('get.p'));
        if(empty($pageIndex)){
            $pageIndex = 1;
        }
        $pageCount = 15;

        $condition['cid'] = $cid;
        $result = D("Advbanner")->getAdvBannerShowLog($condition,($pageIndex-1) * $pageCount,$pageCount);
        $list = $result['result'];

        import('Library.Org.Page.Page');
        $page = new \Page($pageIndex,$pageCount,$result['count'],array("prev","first","last","next"));
        $pageTmp =  $page->show();

        $this->assign("list",$list);
        $this->assign("page",$pageTmp);
        $this->display();
        die;
    }

    //删除轮播
    public function remove(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        $Db = D('Advbanner');
        $advBanner = D('Advbanner')->getBannerById($id);
        if(empty($advBanner)){
            $this->_error('不是正确的轮播 :(');
        }

        if (D('Advbanner')->removeBanner($id)){
            $log = array(
                'remark' => '删除轮播',
                'logtype' => 'home_advbanner',
                'action_id' => $id,
            );
            D('LogAdmin')->addLog($log);
            $this->success('删除轮播成功！');
        }else{
            $this->_error('删除轮播失败！');
        }
    }

    /**
    * 十六进制转 RGB
    * @param string $hexColor 十六颜色 ,如：#ff00ff
    * @return array RGB数组
    */
    public function colorToRgb($hexColor) {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
        $rgb = array(
        'r' => hexdec(substr($color, 0, 2)),
        'g' => hexdec(substr($color, 2, 2)),
        'b' => hexdec(substr($color, 4, 2))
        );
        } else {
        $color = str_replace('#', '', $hexColor);
        $r = substr($color, 0, 1) . substr($color, 0, 1);
        $g = substr($color, 1, 1) . substr($color, 1, 1);
        $b = substr($color, 2, 1) . substr($color, 2, 1);
        $rgb = array(
        'r' => hexdec($r),
        'g' => hexdec($g),
        'b' => hexdec($b)
        );
        }
        return $rgb;
    }

    //更改状态
    public function status(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        $Db = D('Advbanner');
        $advBanner = $Db->getBannerById($id);
        if(empty($advBanner)){
            $this->_error('不是正确的轮播 :(');
        }

        if($advBanner['status'] == '1'){
            $type = '0';
            $action = '停用';
        }else{
            $type = '1';
            $action = '启用';
        }
        if ($Db->setStatus($id,$type)){
            $log = array(
                    'remark' => $action.'轮播',
                    'logtype' => 'home_advbanner',
                    'action_id' => $id,
                    //'info' => $data
            );
            D('LogAdmin')->addLog($log);
            $this->success($action.'成功！');
        }else{
            $this->_error($action.'失败！');
        }
    }

    //更新轮播排序
    public function updateSort(){
        $id = I('get.id');
        $sort = I('get.sort');
        D('Advbanner')->updateSort($id,$sort);
        $this->ajaxReturn(array("info"=>'',"status"=>1));
    }

    //检测当前城市数量是否超过  轮播：每个城市5个上限
    public function checkCount(){
        $city_id = I('get.city_id');
        $city_id = intval($city_id);
        $count = D('Advbanner')->checkAdvBannerNum('home_advbanner',$city_id);
        $status = $count > 5 ? '2' : '1';
        $this->ajaxReturn(array("data"=>$count,"info"=>'',"status"=>$status));
    }

    //获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Advbanner")->getAdvBannerList($condition,($pageIndex-1) * $pageCount,$pageCount);

        foreach ($result['result'] as $key => $value) {
            if ($value["company_id"] != 0) {
                $ids[] = $value["company_id"];
            }
        }

        if (count($ids) > 0) {
            //获取已使用的广告天数
            $reports = D("AdvertisingReport")->getAdvReportByType($ids,1);
            foreach ($reports as $value) {
                $useDay[$value["comid"]] = $value["total"] - $value["use_day"];
            }

            foreach ($result['result'] as $key => $value) {
                $result['result'][$key]["date"] =  $useDay[$value["company_id"]];
            }
        }

        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp);
    }

}