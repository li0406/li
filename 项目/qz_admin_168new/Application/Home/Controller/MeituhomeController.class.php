<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

//美图首页管理

class MeituhomeController extends HomeBaseController {

    //定义模块对应的ID
    public $moduleId = array(
        'location' => '1',      //局部
        'huxing' => '2',        //户型
        'fengge' => '3',        //设计
        'pubmeitu' => '4',      //公装
        '3d' => '5',            //3D
        'mingshi' => '6',       //名师
    );

    //美图
    public function index() {

        $category = I('get.category');
        if(empty($category)){
            $category = 'location';
        }

        //局部
        $cateArray['location'] = array(            
            array('name' => '客厅','id' => '4'),
            array('name' => '卧室','id' => '5'),
            array('name' => '厨房','id' => '7'),
            array('name' => '卫生间','id' => '8'),
            array('name' => '儿童房','id' => '12'),
        );

        //户型
        $cateArray['huxing'] = array(            
            array('name' => '小户型','id' => '10'),
            array('name' => '二居','id' => '5'),
            array('name' => '三居','id' => '6'),
            array('name' => '大户型','id' => '7'),
            array('name' => '复式','id' => '9'),
        );

        //设计
        $cateArray['fengge'] = array(            
            array('name' => '欧式','id' => '8'),
            array('name' => '现代','id' => '5'),
            array('name' => '中式','id' => '4'),
            array('name' => '日式','id' => '10'),
            array('name' => '简欧','id' => '15'),
        );

        if(empty($cateArray[$category])){
            $this->error('不存在此分类');
        }

        $map['h.module'] = $this->moduleId[$category];

        //获取结果
        $result = D("Meituhome")->getMeituItemList($map);
        foreach ($result as $k => $v) {
            $list[$v['category']][] = $v;
        }

        $info['category'] = $category;

        $this->assign('info',$info);
        $this->assign('category',$cateArray[$category]);
        $this->assign("list",$list);
        $this->display($category);
    }

    //美图 弹窗
    public function meituBox(){
        $data = I('get.');
        $is_choice = I('get.is_choice');
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }
        
        $ids = $this->getIdsList($data['module'].'-'.$data[$data['module']]);

        if(!empty($ids)){
             $condition['ids'] = $ids;
            if($is_choice == '1'){
                $condition['is_choice'] = '1';               
            }elseif($is_choice == '2'){
                $condition['is_choice'] = '2';
            }
        }

        $condition['fengge'] = $data['fengge'];
        $condition['huxing'] = $data['huxing'];
        $condition['location'] = $data['location'];
        $condition['color'] = $data['color'];
        $condition['title'] = $data['title'];

        //获取美图列表
        $info = $this->getMeituList($condition, $pageIndex=1, $pageCount = 15, $data['keyword'], $data['state']);
        $info['list'] = $this->getChoiceList($info['list'],$ids);

        //获取美图属性
        $info['attribute'] = D('Meitu')->getMeituAttribute('', ['enabled' => 1]);
        $info['module'] = $data['module'].'-'.$data[$data['module']];

        $this->assign('info',$info);
        $this->display();
    }

    //名师
    public function mingShi(){
        $map['module'] = $this->moduleId['mingshi'];

        //获取结果
        $result = D("Meituhome")->getItemList($map);
        foreach ($result as $key => $value) {
            $idList[] = $value['item_id'];
        }
        if(!empty($idList)){
            $condition['id'] = array('IN',implode(',',$idList));
        }        
        $result = D('Meituhome')->getMeituDesignerList($condition);
        
        $info['category'] = 'mingshi';

        $this->assign('info',$info);
        $this->assign("list",$result);
        $this->display();
    }

    //名师 弹窗
    public function mingShiBox(){
        $is_choice = I('get.is_choice');

        $pageIndex = I('get.p') > 0 ? intval(I('get.p')) : 1;
        $pageCount = 10;

        $module = 'mingshi';
        
        $ids = $this->getIdsList($module);
        if(!empty($ids)){
            if($is_choice == '1'){
                $condition['id'] = array('IN',$ids);
            }elseif($is_choice == '2'){
                $condition['id'] = array('NOT IN',$ids);
            }
        }

        //装修公司名字
        $company = I('get.company');
        if (!empty($company)) {
            $condition['comname']  = array("LIKE","%$company%");
        }

        //设计师
        $designer = I('get.designer');
        if (!empty($designer)) {
            $condition['name']  = array("LIKE","%$designer%");
        }

        $info = $this->getMeituDesignerList($condition,$pageIndex,$pageCount);

        $info['list'] = $this->getChoiceList($info['list'],$ids);            
        $info['module'] = $module;

        $this->assign('info',$info);
        $this->display();
    }

    //3D效果图
    public function threeD(){

        $map['h.module'] = $this->moduleId['3d'];

        //获取结果
        $result = D("Meituhome")->getThreeDItemList($map);

        $info['category'] = '3d';

        $this->assign('info',$info);
        $this->assign("list",$result);
        $this->display('3d');
    }

    //3D效果图 弹窗
    public function threedBox(){
        $is_choice = I('get.is_choice');

        $pageIndex = I('get.p') > 0 ? intval(I('get.p')) : 1;
        $pageCount = 10;

        $module = '3d-meitu';
        
        $ids = $this->getIdsList($module);        
        if(!empty($ids)){
            if($is_choice == '1'){
                $condition['id'] = array('IN',$ids);
            }elseif($is_choice == '2'){
                $condition['id'] = array('NOT IN',$ids);
            }
        } 

        $condition['fengge'] = I('get.fengge');
        $condition['huxing'] = I('get.huxing');
        $condition['adminuser_id'] = I('get.adminuser_id');
        $condition['title'] = I('get.title');

        $info = $this->get3DMeituList($condition,$pageIndex,$pageCount);

        $info['list'] = $this->getChoiceList($info['list'],$ids);
        $info['category'] = M('xiaoguotu_threedimension_category')->where(array(
            'status' => 1
        ))->select();
        $info['adminuser'] = D('Adminuser')->getAdminuserListByUid(26);         
        $info['module'] = $module;

        $this->assign('info',$info);
        $this->display();
    }

    //工装美图
    public function pubmeitu(){
        //户型
        $cateArray = array(            
            array('name' => '办公室','id' => '8'),
            array('name' => '酒店','id' => '7'),
            array('name' => '商铺','id' => '52'),
            array('name' => '餐厅','id' => '1'),
            array('name' => '宾馆','id' => '2'),
        );

        $map['h.module'] = $this->moduleId['pubmeitu'];
        //获取结果
        $result = D("Meituhome")->getPubMeituItemList($map);
        foreach ($result as $k => $v) {
            $list[$v['category']][] = $v;
        }

        $info['category'] = 'pubmeitu';

        $this->assign('category',$cateArray);
        $this->assign('info',$info);
        $this->assign("list",$list);
        $this->display();
    }

    //工装美图 弹窗
    public function pubMeituBox(){
        $data = I('get.');
        $is_choice = I('get.is_choice');
        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }

        $module = 'pubmeitu-'.$data['location'];
        
        $ids = $this->getIdsList($module);
        if(!empty($ids)){
             $condition['ids'] = $ids;
            if($is_choice == '1'){
                $condition['is_choice'] = '1';               
            }elseif($is_choice == '2'){
                $condition['is_choice'] = '2';
            }
        }


        $info["location"] = D("Pubmeitu")->getPubmeitulocation();
        $info["fengge"] = D("Pubmeitu")->getPubmeitufengge();
        $info["mianji"] = D("Pubmeitu")->getPubmeitumianji();

        //类型
        $location = I("get.location");
        if(!empty($location)){
            $condition['lx'] = $location;
        }
        //风格
        $fengge = I("get.fengge");
        if(!empty($fengge)){
            $condition['fg'] = $fengge;
        }
        //面积
        $mianji = I("get.mianji");
        if(!empty($mianji)){
            $condition['mj'] = $mianji;
        }

        $list = $this->getPubmeituList($condition,$pageIndex,$pageCount);
        //dump($list['list']);
        //die;
        $info['list'] = $this->getChoiceList($list['list'],$ids);
        $info['page'] = $list['page']; 
   
        $info['module'] = $module;
        $this->assign('info',$info);
        $this->display();
    }




    //更新数据并返回标题
    public function updateMeituItem(){
        $module = I('get.module');
        if(empty($module)){
            return false;
        }
        $idList = str_replace(array('[',']','&quot;'),'',I('cookie.'.$module));
        $idList = array_unique(array_filter(explode(',',$idList)));

        if($module != '3d-meitu' AND $module != 'mingshi'){
            if(count($idList) != 8){
                $this->ajaxReturn(array('status' => 0, 'msg'=>'选取数量必须为8'));
            }
        }else{
            if(count($idList) != 5){
                $this->ajaxReturn(array('status' => 0, 'msg'=>'选取数量必须为5'));
            }
        }
        $oldModule = $module;

        $module = explode('-',$module);

        $data['module'] = $this->moduleId[$module['0']];        
        $data['category'] = empty($module['1']) ? 0 : $module['1'];        
        $itemList = D('Meituhome')->getItemList($data);

        //定义入库数据
        $data['last_time'] = time();
        $data['last_uid'] = session("uc_userinfo.id");

        //如果不为空
        if(!empty($itemList)){//修改旧数据
            
            foreach ($itemList as $k => $v) {
                $oldItemList[$v['id']] = $v['item_id'];                
            }

            //多余
            $destroyDiff = array_diff($oldItemList,$idList);
            foreach ($destroyDiff as $k => $v) {
                D('Meituhome')->removeItem($k);       
            }

            //新增
            $newDiff = array_diff($idList,$oldItemList);
            foreach ($newDiff as $k => $v) {
                $data['item_id'] = $v;
                D('Meituhome')->addItem($data);          
            }
        }else{
            //新增数据
            foreach ($idList as $k => $v) {
                $data['item_id'] = $v;
                D('Meituhome')->addItem($data);          
            }
        }


        if(!empty($idList)){
            //3D效果图
            if($oldModule == '3d-meitu'){
                $result = D('Meituhome')->get3DMeituItemById($idList);
            //公装美图
            }elseif($oldModule == 'pubmeitu'){
                $result = D('Meituhome')->getPubMeituItemById($idList);
            //名师
            }elseif($oldModule == 'mingshi'){
                $condition['id'] = array('IN',implode(',',$idList));
                $result = D('Meituhome')->getMeituDesignerList($condition);
            //美图
            }else{
                $result = D('Meituhome')->getMeituItemById($idList);
            }            
        }
        $this->ajaxReturn(array('status'=>1, 'data'=>$result));
    }


    //获取 Cookie中选择的ID
    public function getIdsList($module){
        $ids = str_replace(array('[',']','&quot;'),'',I('cookie.'.$module));
        if(empty($ids)){
            return '';
        }
        return explode(',',$ids);
    }


    //获取选择状态后列表
    public function getChoiceList($list,$ids){
        foreach ($list as $k => $v) {
            if(in_array($v['id'],$ids)){
                $list[$k]['toggleIcon'] = 'fa-toggle-on';
            }else{
                $list[$k]['toggleIcon'] = 'fa-toggle-off';
            }
        }
        return $list;
    }



    //获取 名师 列表并分页
    private function getMeituDesignerList($condition,$pageIndex=1,$pageCount = 16){
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D('Meituhome')->getMeituDesignerCount($condition);
        $result['list'] = D('Meituhome')->getMeituDesignerList($condition,($pageIndex-1)*$pageCount,$pageCount);

        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        return $result;
    }

    //获取 3D 列表并分页
    public function get3DMeituList($condition,$pageIndex=1,$pageCount = 16) {
        $count = D('Meituhome')->get3DCount($condition);
        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $info['page'] =  $page->show();
            $pageIndex = $page->nowPage;
        }
        $info['list'] = D('Meituhome')->get3DList($condition,($pageIndex - 1)*$pageCount,$pageCount);
        return $info;
    }

    //获取 公装案例 列表并分页
    private function getPubmeituList($condition,$pageIndex,$pageCount){
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Pubmeitu")->getPubmeituListCount($condition,($pageIndex-1) * $pageCount,$pageCount);
        if($count > 0){
            import('Library.Org.Page.Page');
            $result = D("Pubmeitu")->getPubmeituList($condition,($pageIndex-1) * $pageCount,$pageCount);
            //查询热门标签
            foreach ($result as $key => $value) {
                $result[$key]["time"] = date("Y-m-d H:i:s",$value['time']);
                $tags =  array_filter(explode(",",$value["tags"]));
                $result[$key]["tagname"] ="";
                if(count($tags) > 0){
                    //查询标签信息
                    $tags = D("Admintags")->getTagsInfo($tags);
                    foreach ($tags as $k => $val) {
                       $result[$key]["tagname"] .=" ".$val["name"];
                    }
                }
            }
            $config  = array("prev","first","last","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            return array("list"=>$result,"page"=>$pageTmp);
        }
    }

    //获取 美图 列表并分页
    public function getMeituList($params,$pageIndex=1,$pageCount = 16,$keyword,$state) {
        $count = D('Meitu')->getMeituCount($params,$keyword,$state);

        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
            $pageIndex = $page->nowPage;
        }

        $result['list'] = D('Meitu')->getMeituList($params,($pageIndex - 1)*$pageCount,$pageCount,$keyword,$state);
        foreach ($result['list'] as $key => $value) {
            $result['list'][$key]["tagname"] = str_replace(",", " ", $value["tagname"]);
        }
        return $result;
    }  
}