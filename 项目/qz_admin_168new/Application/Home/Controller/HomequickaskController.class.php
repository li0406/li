<?php

//首页快速发布问题

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class HomequickaskController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','6');
    }

    //首页
    public function index(){
        //将城市排序
        $this->assign("citys",getUserCitys());
        $sortby = array(
            '1'=> array('id'=> '1','name'=> '电话','sql'=> 'a.phone DESC'),
            '2'=> array('id'=> '2','name'=> '城市','sql'=> 'a.city_id DESC'),
            '3'=> array('id'=> '3','name'=> '状态','sql'=> 'a.status DESC'),
            '4'=> array('id'=> '4','name'=> '时间','sql'=> 'a.post_time DESC'),
        );

        //分页
        $pageIndex = 1;
        $pageCount = 12;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
         //状态
        if(!empty($_GET["status"])){
            $condition['a.status'] = array("EQ",I('get.status'));
        }
        //城市
        $cityId = I('get.city_id');
        if(!empty($cityId)){
            $condition['a.city_id']  = array("EQ",$cityId);
        }
        //排序
        if(!empty($_GET["sortby"])){
            $by = $_GET["sortby"];
            if($by > 6 || $by < 0 ){
                $this->_error('排序错误');
            }
            $sortby[$by]['now'] = 'selected';
            $condition['orderBy'] = $sortby[$by]['sql'];
        }else{
            $sortby['4']['now'] = 'selected';
            $condition['orderBy'] = 'a.post_time DESC';
        }

        //搜索
        if(!empty($_GET["keyword"])){
            $condition['a.content'] = array('like','%'.I('get.keyword').'%');
        }

        $result = $this->getList($condition,$pageIndex,$pageCount);
        $this->assign("sortby",$sortby);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //更改状态
    public function status(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        $map['id'] = $id;

        $Db = M('ask_quick');
        $getQuickAsk = $Db->where($map)->find();
        if(empty($getQuickAsk)){
            $this->_error('不是正确的提问');
        }

        if($getQuickAsk['status'] == '0'){
            $status = '1';
            $action = '删除';
        }else{
            $status = '0';
            $action = '恢复';
        }

        $data['status'] = $status;
        $setStatus = $Db->where($map)->save($data);

        if ($setStatus){
            $this->success($action.'成功！');
        }else{
            $this->_error($action.'失败！');
        }
    }

    //获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $orderBy = $condition['orderBy'];
        unset($condition['orderBy']);
        if(session("uc_userinfo.uid") != 1){
            $cityIds = getMyCityIds();
            $condition['a.city_id'] = array('IN',implode(',',$cityIds));
        }

        $pageSize = ($pageIndex-1) * $pageCount;

        $Db = M('ask_quick');
        $count  = $Db->alias("a")->where($condition)->count();
        $result = $Db->alias("a")->field('a.*,q.cname city_name')
                      ->join("LEFT JOIN ". C('DB_PREFIX') ."quyu as q on a.city_name = q.bm")
                      ->order($orderBy)
                      ->limit($pageSize.",".$pageCount)
                      ->where($condition)
                      ->select();
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$result,"page"=>$pageTmp);
    }

}