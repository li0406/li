<?php

//首页案例推荐

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class HomecaseController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','2');
    }

    //首页
    public function index(){

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
        $pageCount = 20;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
         //状态
        if(!empty($_GET["status"])){
            $condition['a.status'] = array("EQ",I('get.status'));
        }
        //城市
        if('' != $_GET['city_id']){
            $condition['a.city_id']  = array("EQ",I('get.city_id'));
        }
        //排序
        if(isset($_GET["sortby"])){
            $by = $_GET["sortby"];
            if($by > 6 || $by < 0 ){
                $this->_error('排序错误');
            }
            $sortby[$by]['now'] = 'selected';
            $condition['orderBy'] = $sortby[$by]['sql'];
        }else{
            $sortby['4']['now'] = 'selected';
            $condition['orderBy'] = 'a.op_time DESC';
        }

        //搜索
        if(!empty($_GET["keyword"])){
            $search['a.company_id'] = array('like','%'.I('get.keyword').'%');
            $search['a.company_name'] = array('like','%'.I('get.keyword').'%');
            $search['a.title'] = array('like','%'.I('get.keyword').'%');
            $search['_logic'] = 'OR';
            $condition['_complex'] = $search;
        }

        $condition['a.module'] = 'home_cases';

        $result = $this->getList($condition,$pageIndex,$pageCount);
        //dump($result);
        $time = strtotime(date("Y-m-d"));
        foreach ($result['list'] as $key => $value) {
            $remainder = $value['end_time'] - $time;
            $result['list'][$key]['date'] = 0;
            if($remainder > 0){
                $result['list'][$key]['date'] = ceil($remainder/86400)+1;
            }
        }
        $this->assign("sortby",$sortby);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->assign("citys",getUserCitys());
        $this->display();
    }

    //增加案例推荐
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
            $data['description'] = preg_replace("@\s@is",'',$post['description']);
            $data['sort'] = $post['sort'];
            $data['module'] = 'home_cases';
            $data['start_time'] = strtotime($post['start_time']);
            $data['end_time'] = strtotime($post['end_time']);
            $data['op_time'] = time();
            $data['op_uid'] = session('uc_userinfo.id');
            $data['op_uname'] = session('uc_userinfo.name');

            $count = D('Advbanner')->checkAdvBannerNum('home_cases',$data['city_id']);
            if($count > 15){
                $this->_error('当前城市案例数已超过最大限制');
            }
            //Ajax 验证
            if(IS_AJAX){

            }

            if(mb_strlen($data['title'],'utf-8') > 55){
                $this->_error('标题最多输入55个字 :(');
            }
            if ($Db->addBanner($data)){
                $log = array(
                    'remark' => '增加案例',
                    'logtype' => 'home_cases',
                    //'action_id' => $id,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);
                $this->success('增加案例推荐成功 :)');
            }else{
                $this->_error('增加案例推荐失败 :)');
            }
        }else{
            //1 超级 2客服 5复制 6总站编辑 9秘书助理
            /*
            $group_uid = getAdminUser('uid');
            if(in_array($group_uid,array('1','2','6'))){
                $info['cityArray'] = '<option value="0">- 全站 -</option>';
            }
            */
            $info = array();
            $info['start_time'] = time(); //默认今天开始
            $info['end_time']   = strtotime('2020-01-01'); //默认结束时间


            $this->assign("info",$info);
            $this->assign("citys",getUserCitys());
            $this->display();
        }
    }

    public function edit(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('不是正确的案例 :(');
        }
        $Db = D('Advbanner');
        $cases = $Db->getBannerById($id,'home_cases');
        if(empty($cases)){
            $this->_error('不是正确的案例 :(');
        }

        if(IS_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['company_id'] = $post['company_id'];
            $companyname = D('Advbanner')->getCompanyJc($data['company_id'] );
            $data['company_name'] =$companyname[0]['jc'];
            $data['city_id'] = $post['city_name'];
            $data['img_url'] = $post['img_url'];
            $data['url'] = $post['url'];
            $data['description'] = $data['description'] = preg_replace("@\s@is",'',$post['description']);
            $data['sort'] = $post['sort'];
            $data['module'] = 'home_cases';
            $data['start_time'] = strtotime($post['start_time']);
            $data['end_time'] = strtotime($post['end_time']);
            $data['op_time'] = time();
            $data['op_uid'] = session('uc_userinfo.id');
            $data['op_uname'] = session('uc_userinfo.name');

            $count = D('Advbanner')->checkAdvBannerNum('home_cases',$data['city_id']);
            if($count > 15){
                $this->_error('当前城市案例数已超过最大限制');
            }

            if(mb_strlen($data['title'],'utf-8') > 55){
                $this->_error('标题最多输入55个字 :(');
            }
            if ($Db->editBanner($id,$data)){
                $log = array(
                    'remark' => '修改案例',
                    'logtype' => 'home_cases',
                    'action_id' => $id,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);
                $this->success('修改案例推荐成功 :)');
            }else{
                $this->_error('增修改案例推荐失败 :)');
            }
        }else{
            $cases['company'] = empty($cases['company_id']) ? '' : $cases['company_id'].'|'.$cases['company_name'];

            $citys = getUserCitys();
            //1 超级 2客服 5复制 6总站编辑 9秘书助理
            /*
            $group_uid = getAdminUser('uid');
            if(in_array($group_uid,array('1','2','6'))){
                $allCity = array('cname' => '- 全站 -','cid' => '0');
                array_unshift($citys,$allCity);
            }
            */
            $this->assign("citys",$citys);
            $this->assign("info",$cases);
            $this->display();
        }
    }

    //更改状态
    public function status(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        $Db = D('Advbanner');
        $cases = $Db->getBannerById($id);

        if($cases['status'] == '1'){
            $type = '0';
            $action = '停用';
        }else{
            $type = '1';
            $action = '启用';
        }
        if ($Db->setStatus($id,$type)){
            $log = array(
                    'remark' => $action.'案例',
                    'logtype' => 'home_cases',
                    'action_id' => $id,
                    //'info' => $data
            );
            D('LogAdmin')->addLog($log);
            $this->success($action.'成功！');
        }else{
            $this->_error($action.'失败！');
        }
    }

    //检测当前城市数量是否超过  案例：每个城市15个上限
    public function checkCount(){
        $city_id = I('get.city_id');
        $city_id = intval($city_id);
        $count = D('Advbanner')->checkAdvBannerNum('home_cases',$city_id);
        $status = $count > 15 ? '2' : '1';
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
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp);
    }

}