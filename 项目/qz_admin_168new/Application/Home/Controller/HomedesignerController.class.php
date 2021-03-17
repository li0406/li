<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class HomedesignerController extends HomeBaseController
{
    public function _initialize(){
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','5');
    }

    public function index(){
        $citys= getUserCitys();
        $this->assign('citys',$citys);
        $cityids = getMyCityIds();
        if(!empty($_GET['cs'])){
            if(in_array($_GET['cs'],$cityids)){
                $map['d.city_id'] = intval($_GET['cs']);
            }else{
                $this->_error('您没有该城市的权限！');
            }
        }else{
            if(empty($cityids)){
                $this->_error('您没有管辖城市！');
            }else{
                $map['d.city_id'] = array("IN",$cityids);
            }
        }

        if(!empty($_GET['condition'])){
            $condition = trim($_GET['condition']);
            if(0 == intval($condition)){
                $map['d.uname'] = array("like","%$condition%");
            }else{
                $map['d.uid'] = array("EQ",$condition);
            }
        }

        $pageIndex = 1;
        if(!empty($_GET['p'])){
            $pageIndex = intval($_GET['p']);
        }
        $info = $this->getListFilter($map,$pageIndex);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [新增精英设计师 description]
     */
    public function add(){
    	if(IS_POST){
    		$data = $_POST['data'];
    		$data['op_time'] = time();
    		$data['op_uid'] = getAdminUser('id');
    		$data['op_uname'] = getAdminUser('name');
            if($id = D('Advdesigner')->addDesigner($data)){
                $log = array(
                                'remark' => '新增精英设计师！',
                                'logtype' => 'homedesigner',
                                'action_id' => $id,
                                'info' => $data
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>$result,'info'=>'添加精英设计师成功！','status'=>1));
            }
    		$this->ajaxReturn(array('data'=>'','info'=>'添加精英设计师失败！请联系技术部门','status'=>0));
    	}
        $this->display();
    }

    /**
     * [编辑设计师 description]
     * @return [type] [description]
     */
    public function edit(){
        if(IS_POST){
            $data = $_POST['data'];
            $id = intval($data['id']);
            unset($data['id']);
            $data['op_time'] = time();
            $data['op_uid'] = getAdminUser('id');
            $data['op_uname'] = getAdminUser('name');
            if(D('Advdesigner')->editDesigner($id,$data)){
                $log = array(
                                'remark' => '编辑精英设计师！',
                                'logtype' => 'advbrands',
                                'action_id' => $id,
                                'info' => $data
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>$result,'info'=>'编辑精英设计师成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'编辑精英设计师失败！请联系技术部门','status'=>0));
        }
        $id = intval($_GET['id']);
        $info = D('Advdesigner')->getAdvDesignerById($id);
        if(empty($info)){
            $this->_error();
        }
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [更改设计师位置 description]
     * @return [type] [description]
     */
    public function reset(){
        if(IS_POST){
            $data = $_POST['reset'];
            if(!empty($data)){
                $log = array(
                                'remark' => '修改品牌位置!',
                                'logtype' => 'homedesigner',
                             );
                foreach ($data as $k => $v) {
                    M('adv_designer')->where(array('uid'=>$v['id']))->save(array('sort'=>$v['sort']));
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
     * [更改设计师状态是否可见 description]
     */
    public function setStatus(){
        $id = intval($_POST['id']);
        $status = intval($_POST['status']);
        if(!empty($id)){
            if(D('Advdesigner')->setStatus($id,$status)){
                $log = array(
                                'remark' => '新增精英设计师！',
                                'logtype' => 'homedesigner',
                                'action_id' => $id,
                                'info' => $status
                             );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('data'=>'','info'=>'更改精英设计师状态成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'更改精英设计师状态失败！','status'=>0));
    }

    /**
     * [deleteAll 批量将设计师状态设为不可用]
     * @return [type] [description]
     */
    public function deleteAll(){
        if(IS_POST){
            $data = $_POST['allde'];
            if(!empty($data)){
                $log = array(
                                'remark' => '将设计师状态设为不可用!',
                                'logtype' => 'homedesigner',
                             );
                foreach ($data as $k => $v) {
                    M('adv_designer')->where(array('uid'=>$v['id']))->save(array('status'=>'0'));
                    $log['action_id'] = $v['id'];
                    $log['info'] = $v;
                    D('LogAdmin')->addLog($log);
                }
                $this->ajaxReturn(array('data'=>'','info'=>'将设计师状态设为不可用成功!','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'将设计师状态设为不可用失败!请联系技术部门','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'将设计师状态设为不可用失败','status'=>0));
    }

    public function getPreview(){
        $cs = intval($_GET['cs']);
        if(!empty($cs)){
            $limit = '';
            if($cs == '000001'){
                $limit = 25;
            }
            $data = D('Advdesigner')->getDesignerListPreview($cs, $limit);
            if(!empty($data)){
                $this->ajaxReturn(array('data'=>$data,'info'=>'查找成功','status'=>1));
            }else{
                $this->ajaxReturn(array('data'=>'','info'=>'该城市没有可用LOGO','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'没有找到该城市','status'=>0));
    }

    public function getVipDesignerList(){
        $condition = trim($_GET['condition']);
        $result = D('User')->getVipDesignerList($condition,10);
        if(empty($result)){
            $this->ajaxReturn(array('data'=>'','info'=>'','status'=>0));
        }
        $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
    }

    //获取列表并分页
    private function getListFilter($map,$pageIndex=1,$pageCount = 16)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $res =  D('Advdesigner')->getDesignerList($map,($pageIndex-1) * $pageCount,$pageCount,'city_id,status DESC,sort');
        $result['info'] = $res['result'];
        $count = $res['count'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $result['page'] =  $page->show();
        return $result;
    }
}