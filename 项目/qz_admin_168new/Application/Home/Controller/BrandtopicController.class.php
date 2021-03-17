<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class BrandtopicController extends HomeBaseController
{
    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','7');
    }

    public function index(){
        //将城市排序
        $usercity= getUserCitys();
        if('- 全站 -' == $usercity[0]['cname']){
            unset($usercity[0]);
        }
        $this->assign('citys',$usercity);
        $cityids = getMyCityIds();
        if(!empty($_GET['cs'])){
            if(in_array($_GET['cs'],$cityids)){
                $map['a.city_id'] = intval($_GET['cs']);
            }else{
                $this->_error('您没有该城市的权限！');
            }
        }else{
            if(empty($cityids)){
                $this->_error('您没有管辖城市！');
            }else{
                $map['a.city_id'] = array("IN",$cityids);
            }
        }

        if(!empty($_GET['condition'])){
            $condition = trim($_GET['condition']);
            if(0 == intval($condition)){
                $map['a.company_name'] = array("like","%$condition%");
            }else{
                $map['a.company_id'] = array("EQ",$condition);
            }
        }

        $pageIndex = 1;
        if(!empty($_GET['p'])){
            $pageIndex = intval($_GET['p']);
        }
        $map['a.module'] = 'brand_topic';
        $info = $this->getListFilter($map,$pageIndex);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [新增品牌 description]
     */
    public function add(){
        if(IS_POST){
            $data = $_POST['data'];
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            $data['module'] = 'brand_topic';
            $data['img_host'] = 'qiniu';
            if($id = D('Advbanner')->addBanner($data)){
                $log = array(
                                'remark' => '新增装修公司品牌！',
                                'logtype' => 'brandtopic',
                                'action_id' => $id,
                                'info' => $data
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>$result,'info'=>'添加品牌成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'添加品牌失败！请联系技术部门','status'=>0));
        }
        $this->display();
    }

    /**
     * [编辑品牌 description]
     * @return [type] [description]
     */
    public function edit(){
        if(IS_POST){
            $data = $_POST['data'];
            $id = intval($data['id']);
            unset($data['id']);
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            $data['module'] = 'brand_topic';
            if(D('Advbanner')->editBanner($id,$data)){
                $log = array(
                                'remark' => '编辑装修公司品牌！',
                                'logtype' => 'brandtopic',
                                'action_id' => $id,
                                'info' => $data
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>$result,'info'=>'添加品牌成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'添加品牌失败！请联系技术部门','status'=>0));
        }
        $id = intval($_GET['id']);
        $info = D('Advbanner')->getBannerById($id,'brand_topic');
        if(empty($info)){
            $this->_error();
        }
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [更改品牌位置 description]
     * @return [type] [description]
     */
    public function reset(){
        if(IS_POST){
            $data = $_POST['reset'];
            if(!empty($data)){
                $log = array(
                                'remark' => '修改装修公司品牌位置！',
                                'logtype' => 'brandtopic'
                             );
                foreach ($data as $k => $v) {
                    M('adv_banner')->where(array('id'=>$v['id']))->save(array('sort'=>$v['sort']));
                    $log['action_id'] = $v['id'];
                    $log['info'] = $v;
                    D('LogAdmin')->addLog($log);
                }
                $this->ajaxReturn(array('data'=>'','info'=>'修改品牌位置成功','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'修改品牌位置失败','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'修改品牌位置失败','status'=>0));
    }

    /**
     * [更改品牌状态是否可见 description]
     */
    public function setStatus(){
        $id = intval($_POST['id']);
        $status = intval($_POST['status']);
        if(!empty($id)){
            if(D('Advbanner')->setStatus($id,$status)){
                $log = array(
                                'remark' => '修改装修公司品牌状态是否可用，将状态改为:'.$status,
                                'logtype' => 'brandtopic',
                                'action_id' => $id,
                                'info' => $status
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>'','info'=>'更改品牌状态成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'更改品牌状态失败！','status'=>0));
    }


    /**
     * [deleteAll 批量将品牌榜状态设为不可用]
     * @return [type] [description]
     */
    public function deleteAll(){
        if(IS_POST){
            $data = $_POST['allde'];
            if(!empty($data)){
                $log = array(
                                'remark' => '修改装修公司状态为不可用！',
                                'logtype' => 'brandtopic'
                             );
                foreach ($data as $k => $v) {
                    $flag = M('adv_banner')->where(array('id'=>$v['id']))->save(array('status'=>'0'));
                    if($flag){
                        $log['action_id'] = $v['id'];
                        $log['info'] = $v;
                        D('LogAdmin')->addLog($log);
                    }
                }
                $this->ajaxReturn(array('data'=>'','info'=>'将品牌设为不可用成功','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'将品牌设为不可用失败！请联系技术部门！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'将品牌设为不可用失败','status'=>0));
    }

    //获取列表并分页
    private function getListFilter($map,$pageIndex=1,$pageCount = 16)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $res =  D('Advbanner')->getBannerList($map,($pageIndex-1) * $pageCount,$pageCount,'city_id,status DESC,sort');
        $result['info'] = $res['result'];
        $count = $res['count'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $result['page'] =  $page->show();
        return $result;
    }
}