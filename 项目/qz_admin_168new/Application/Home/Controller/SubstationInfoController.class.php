<?php


namespace Home\Controller;


use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\SubstationInfoLogicModel;
use Common\Enums\ApiConfig;

class SubstationInfoController extends HomeBaseController
{
    public function index()
    {
        $logic = new SubstationInfoLogicModel();
        $param = I('get.');
        $stat = isset($param['stat']) ? $param['stat'] : '';
        $search_text = isset($param['search_text']) ? ( $param['search_text'] ? $param['search_text'] : "" ) : "";
        $list = $logic->getSubstationList($stat,$search_text);

        $this->assign("list",$list['list']);
        $this->assign("page",$list['page']);
        $this->assign("getdata",$param);

        $this->display("Substationinfo/index");

    }

    /**
     * 获取单条办事处信息
     */
    public function  getSubstationInfo()
    {
        $logic = new SubstationInfoLogicModel();
        $id = I('get.id');
        if(!$id){
            $return = [];
            $return['error_code'] = ApiConfig::CANNOT_GETDATA;
            $return['error_msg'] = "未获取到id";
            $this->ajaxReturn($return);
        }else{
            $info = $logic->getSubstationInfoById(intval($id));
            if($info){
                $return = [];
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = "获取成功";
                $return['data'] = $info;
                $this->ajaxReturn($return);
            }else{
                $return = [];
                $return['error_code'] = ApiConfig::REQUEST_FAILL;
                $return['error_msg'] = "该办事处不存在";
                $this->ajaxReturn($return);
            }

        }

    }


    // 编辑/添加办事处信息
    public function savestation()
    {
        $logic = new SubstationInfoLogicModel();
        $data = $_POST;

        $nowtime = time();
        $map = [];
        $savedata = [];
        if(isset($data['substation_name']) && $data['substation_name']){
            $savedata['substation_name'] = $data['substation_name'];
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::CANNOT_GETDATA,"error_msg"=>"分布名称必填"));
        }

        if(isset($data['city']) && $data['city']){
            $savedata['city'] = $data['city'];
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::CANNOT_GETDATA,"error_msg"=>"城市名称必填"));
        }

        if(isset($data['tel']) && $data['tel']){
            $savedata['tel'] = $data['tel'];
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::CANNOT_GETDATA,"error_msg"=>"联系电话必填"));
        }

        if(isset($data['contact_name']) && $data['contact_name']){
            $savedata['contact_name'] = $data['contact_name'];
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::CANNOT_GETDATA,"error_msg"=>"联系人必填"));
        }

        if(isset($data['address'])){
            $savedata['address'] = $data['address'];
        }

        if(isset($data['sort_num'])){
            $savedata['sort_num'] = $data['sort_num'];
        }

        if(isset($data['stat'])){
            $savedata['stat'] = $data['stat'];
        }

        if(isset($data['id']) && $data['id']){
            $map['id'] = $data['id'];
        }
        if(isset($map['id']) && $map['id']){        //修改
            $savedata['update_time'] = $nowtime;    //修改时间
            $result = $logic->saveSubstation($map,$savedata);
        }else{          //添加
            $savedata['add_time'] = $nowtime;
            $savedata['update_time'] = $nowtime;
            $result = $logic->addSubstation($savedata);
        }
        if($result){
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = "操作成功";
            $this->ajaxReturn($return);
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = "操作失败";
            $this->ajaxReturn($return);
        }
    }

    //停用/启用办事处信息    如果没有传stat则停用该办事处
    public function changeSubstationStat()
    {
        $logic = new SubstationInfoLogicModel();
        $param = I('post.');
        $id = isset($param['id'])?intval($param['id']):0;
        if(isset($param ['stat'])){
            $stat = $param['stat'];
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::CANNOT_GETDATA,"error_msg"=>"未获取到状态信息"));
        }
        $info = $logic->getSubstationInfoById($id);
        if($info){
            $map= [];
            $map['id'] = array('eq',$id);
            $savedata = [];
            $savedata['stat'] = $stat;
            $savedata['update_time'] = time();    //修改时间
            $result = $logic->saveSubstation($map,$savedata);
            if($result){
                $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_SUCCESS,"error_msg"=>"操作成功！"));
            }else{
                $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_FAILL,"error_msg"=>"操作失败！"));
            }
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_FAILL,"error_msg"=>"该办事处不存在！"));
        }

    }






}