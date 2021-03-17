<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/11/22
 * Time: 9:43
 */

namespace Home\Controller;

use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\MallGoodsLogicModel;


class GoodsController extends HomeBaseController
{
    protected $goodsLogic;
    public function __construct()
    {
        parent::__construct();
        $this->goodsLogic = new MallGoodsLogicModel();
    }

    //状态
    private $status = [1=>'上架',2=>'下架'];
    //列表
    public function index(){
        $get = I('get.');
        $list = $this->goodsLogic->getList($get);
        //积分等级
        $scoreLevel = $this->goodsLogic->getLevelInfo();
        $this->assign('status',$this->status);
        $this->assign('level',$scoreLevel);
        $this->assign('list',$list);
        $this->display();
    }

    //添加/编辑页面
    public function add(){
        if(!empty(I('get.id'))){
            $info = $this->goodsLogic->getGoodsInfo(I('get.id'));
        }
// var_dump($info);
        //积分等级
        $scoreLevel = $this->goodsLogic->getLevelInfo();
        $this->assign('level',$scoreLevel);
        $this->assign('info',$info);
        $this->display();
    }

    //ajax - 添加/编辑接口
    public function save(){
        
        if($_POST){
           
            $data = $_POST;
            //检查必填项
            $data['stock'] = empty($data['stock'])? 0:$data['stock'];
            $data['exchange_num'] = empty( $data['exchange_num'] )?0:$data['exchange_num'];

            $validateRequire = $this->goodsLogic->checkRequire($data);
            if($validateRequire){
                $this->ajaxReturn($validateRequire);
            }
           
            //名称不可重复，否则提示“礼品名称重复，请重新输入"
            if($this->goodsLogic->checkGoodsName($data)>0){
                $this->ajaxReturn(['error_code'=>ApiConfig::DATA_HAD_IN_TABLE,'error_msg'=>'礼品名称重复，请重新输入']);
            }
            
            if(empty($data['id'])){
                //添加商品
                $result = $this->goodsLogic->addGoods($data);

            }else{
                
                //编辑商品
                if(!$this->goodsLogic->getExistGoods($data['id'])){
                    $this->ajaxReturn(['error_code'=>ApiConfig::DATA_HAD_IN_TABLE,'error_msg'=>'数据不存在']);
                }
               
                $result = $this->goodsLogic->editGoods($data);
                // var_dump($result);exit;
            }

            $this->ajaxReturn($result) ;
        }
    }

    //ajax-列表页修改商品状态
    public function changestatus(){
        if($_POST){
            if($this->goodsLogic->changeStatus($_POST['id'],$_POST['status'])){
                //添加操作日志
                $log = array(
                    'remark' => '修改商品【' .$_POST['id'] . '】上下架状态',
                    'logtype' => 'changestatus',
                    'action_id' => $_POST['id'],
                    'info' => $_POST
                );
                D('LogAdmin')->addLog($log);

                $this->ajaxReturn(['error_code'=>0]);
            }else{
                $this->ajaxReturn(['error_code'=>1,'error_msg'=>'操作失败']);
            }
        }else{
            $this->ajaxReturn(['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'缺少参数']) ;
        }
    }

    //ajax-删除
    public function delete(){
        if($_POST['id']){
            $this->goodsLogic->deleteInfo($_POST['id']);
            //添加操作日志
            $log = array(
                'remark' => '删除商品【' .$_POST['id'] . '】',
                'logtype' => 'delete',
                'action_id' => $_POST['id'],
                'info' => $_POST
            );
            D('LogAdmin')->addLog($log);

            $this->ajaxReturn(['error_code'=>0]);
        }else{
            $this->ajaxReturn(['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'缺少参数']) ;
        }
    }

    //兑换统计
    public function exchange()
    {
        $get = I('get.');
        $list = $this->goodsLogic->getExchangeList($get);
        $this->assign('list',$list);
        $this->display();
    }
     

}