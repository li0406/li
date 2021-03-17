<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class HomemeituController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','3');
    }

    /**
     * 美图管理
     * @return [type] [description]
     */
    public function index(){
        //获取装修效果图的列表
        $meitu = "home_meitu";
        $text = "home_text";
        $result = D("meitu")->getList($meitu);
        $list = $result["result"];
        $count = $result["count"];

        $resultText = D("meitu")->getList($text);
        $textList = $resultText["result"];
        $textCount = $resultText["count"];
        //dump($textCount);
        $this->assign("list",$list);
        $this->assign("count",$count);
        $this->assign("textList",$textList);
        $this->assign("textCount",$textCount);
        $this->assign("title","装修效果图管理");
        $this->display();
    }

    /**
     * 新增美图
     * @return [type] [description]
     */
    public function add(){
        if($_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['company_id'] = intval($post['search']);
            $data['url'] = $post['url'];
            $data['sort'] = intval($post['sort']);
            $data['img_url'] = $post['files'];
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            $data['module'] = 'home_meitu';
            $data['img_host'] = 'qiniu';
            //$data['value'] = '1';
            //$data['city_id'] = '000001';
            //判断首页美图推荐是否达到4个
            $list = D("meitu")->getList($data['module']);
            if($list["count"] >= "4"){
                $this->_error("首页美图推荐最多只能有4个！");
            }
            $result = D("Advbanner")->addBanner($data);
            //dump($result);
            if($result){
                //添加操作日志
                $logData = array(
                    'logtype'=>'addhomemeitu',
                    'action_id' => $result,
                    'remark' => '新增装修效果图推荐：id='.$result,
                    'info'=>$data,
                                 );
                D('LogAdmin')->addLog($logData);
                $this->success("新增装修效果图成功！",'/homemeitu');
                exit();
            }else{
                $this->_error("编辑失败！请稍后再试！",'/homemeitu');
            }
        }

        $this->assign("title","新增推荐");
        $this->display();
    }

    /**
     * 编辑美图
     * @return [type] [description]
     */
    public function edit(){
        $id = I("get.id");
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        if($_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['company_id'] = intval($post['search']);
            $data['url'] = $post['url'];
            $data['sort'] = intval($post['sort']);
            $data['img_url'] = $post['files'];
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            //dump($data);
            //die;
            $result = D("Advbanner")->editBanner($id,$data);
            if($result){
                //添加操作日志
                $logData = array(
                    'logtype'=>'edithomemeitu',
                    'remark' => '编辑装修效果图推荐：'.$id,
                    'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
                    'action_id' => $id,
                    'info'=>$data,
                                 );
                D('LogAdmin')->addLog($logData);
                $this->success("编辑装修效果图成功！",'/homemeitu');
                exit();
            }else{
                $this->_error("编辑失败！请稍后再试！",'/homemeitu');
            }
        }

        $info = D("Advbanner")->getBannerById($id,"home_meitu");
        //dump($info);
        $this->assign("info",$info);
        $this->assign("title","编辑推荐");
        $this->display();
    }

    /**
     * 新增文字
     * @return [type] [description]
     */
    public function addText(){
        if($_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['url'] = $post['url'];
            $data['sort'] = intval($post['sort']);
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            $data['module'] = 'home_text';
            //$data['value'] = '2';
            //$data['city_id'] = '000001';
            //判断首页美图推荐是否达到17个
            $list = D('meitu')->getList($data['module']);
            if($list['count'] >= '17'){
                $this->_error("首页美图推荐最多只能有17个！");
            }
            //dump($data);
            $result = D("Advbanner")->addBanner($data);
            if($result){
                //添加操作日志
                $logData = array(
                    'logtype'=>'addhometext',
                    'remark' => '新增文字推荐：id='.$result,
                    'action_id' => $result,
                    'info'=>$data,
                                 );
                D('LogAdmin')->addLog($logData);

                $this->success("新增文字成功！",'/homemeitu');
                exit();
            }else{
                $this->_error("编辑失败！请稍后再试！",'/homemeitu');
            }
        }

        $this->assign("title","新增推荐");
        $this->display();
    }

    /**
     * 编辑文字
     * @return [type] [description]
     */
    public function editText(){
        $id = I("get.id");
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        if($_POST){
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['url'] = $post['url'];
            $data['sort'] = intval($post['sort']);
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            //dump($data);
            //die;
            $result = D("Advbanner")->editBanner($id,$data);
            if($result){
                //添加操作日志
                $logData = array(
                    "logtype"=>"edithometext",
                    'remark' => '编辑文字推荐：'.$id,
                    'action_id' => $id,
                    "info"=>$data,
                                 );
                D('LogAdmin')->addLog($logData);
                $this->success("编辑文字成功！","/homemeitu");
                exit();
            }else{
                $this->_error("编辑失败！请稍后再试！",'/homemeitu');
            }
        }

        $info = D("Advbanner")->getBannerById($id,"home_text");

        $this->assign("info",$info);
        $this->assign("title","编辑推荐");
        $this->display();
    }

    //删除文字推荐
    public function delText(){
        $id = I("get.id");
        if(empty($id) || !is_numeric($id)){
            $this->_error('数据错误！');
        }
        $Db = D('Advbanner');
        if ($result = $Db->delBanner($id)){
            //添加操作日志
            $logData = array(
                "logtype"=>"deletehometext",
                'remark' => '删除文字推荐：'.$id,
                'action_id' => $id,
                "info"=>'',
                             );
            D('LogAdmin')->addLog($logData);
            $this->success('删除成功！');
        }else{
            $this->_error('删除失败！');
        }
    }

    /**
     * 获取效果图信息
     * @return [type] [description]
     */
    public function getmeitutitle(){
        $query = trim(I("get.query"));
        if(!empty($query)){
            $list = D("Meitu")->getMeituTitle($query);
        }
        $this->ajaxReturn(array("data"=>$list,"info"=>"","status"=>1));
    }

    /**
     * 获取文字导航信息
     * @return [type] [description]
     */
    public function gettexttitle(){
        $query = trim(I("get.query"));
        if(!empty($query)){
            $list = D("Meitu")->getTextTitle($query);
        }
        $this->ajaxReturn(array("data"=>$list,"info"=>"","status"=>1));
    }

}