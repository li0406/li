<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CooperateLogicModel;

class CooperateController extends HomeBaseController{
    private $type = 1; //合作商家

    //列表页
    public function index(){
        $logic = new CooperateLogicModel();
        //获取活动列表
        $list = $logic->getAcitivityList(I('get.'),I('get.p'));
        $this->assign("list",$list);
        $this->assign("citys",$logic->getAllCity());
        $banner = $logic->getBanner($this->type);
        $this->assign("banner",$banner);
        $this->display();
    }


    //编辑页展示
    public function edit(){
        $id = I('get.id');
        $logic = new CooperateLogicModel();
        if(!empty($id)&&isset($id)){
            //获取活动详情
            $info = $logic->getActivityInfo($id);
            if(!empty($info)){
                $this->assign("info",$info);
            }else{
                $this->_error('数据不存在');
            }
        }

        $this->assign("citys",$logic->getAllCity());
        $this->display();
    }

    //ajax添加编辑数据
    public function editInfo(){
        $data = I('post.');
        $logic = new CooperateLogicModel();
        //必填数据
        if(empty($data['name'])|| empty($data['start_time']) || empty($data['end_time'])|| empty($data['prize'])|| empty($data['sj_name'])||empty($data['detail'])){
            $this->ajaxReturn(['error_code'=>2,'error_msg'=>'必填参数不能为空']);
        }

        if(!empty($data['id'])){
            $result = $logic->editInfo($data['id'],$data);
        }else{
            $result = $logic->addInfo($data);
        }

        if($result>0){
            $this->ajaxReturn(['error_code'=>0]);
        }else{
            $this->ajaxReturn(['error_code'=>1,'error_msg'=>'操作失败,请稍后再试']);
        }
    }

    //ajax编辑顶部广告
    public function editBanner(){
        //获取活动广告
        $logic = new CooperateLogicModel();
        $banner = $logic->getBanner($this->type);
        if(!empty($banner)){
            //编辑
            $result = $logic->editBanner($this->type,I('post.'));
        }else{
            //添加
            $result = $logic->addBanner($this->type,I('post.'));
        }

        if($result>0){
            $this->ajaxReturn(['error_code'=>0]);
        }else{
            $this->ajaxReturn(['error_code'=>1,'操作失败,请稍后再试']);
        }

    }

    //ajax 禁用/启用
    public function changeState(){
        $post = I('post.');
        if(!empty($post['id'])&&!empty($post['state'])){
            $logic = new CooperateLogicModel();
            $result = $logic->changeState($post['id'],$post['state']);
            if($result>0){
                $this->ajaxReturn(['error_code'=>0]);
            }else{
                $this->ajaxReturn(['error_code'=>1,'操作失败,请稍后再试']);
            }
        }else{
            $this->ajaxReturn(['error_code'=>1,'缺失参数']);
        }
    }

    //ajax 删除
    public function deleteActivity(){
        $id = I('post.id');
        if(!empty($id)&&isset($id)){
            $logic = new CooperateLogicModel();
            $logic->deleteActivity($id);
            $this->ajaxReturn(['error_code'=>0]);
        }else{
            $this->ajaxReturn(['error_code'=>1,'缺失参数']);
        }
    }

}