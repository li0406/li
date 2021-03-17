<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2018/8/14
 * Time: 13:55
 */
//ERP装修公司管理
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class YxbController extends HomeBaseController{

    //装修公司管理
    public function index(){
        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $post = I('get.');
        $ordertype = I('get.ordertype');
        $order = I('get.order');
        //获取order条件(排序)
        $order = D('Home/Logic/YxbLogic')->getCompanyPaixuInfo($ordertype,$order);
        //获取公司的个数
        $count = D('Home/Logic/YxbLogic')->getErpCompanyCount($post);

        //获取公司列表
        $info = D('Home/Logic/YxbLogic')->getErpCompanyList($post,$order['order'],$count);
        //ERP状态列表
        $type =  D('Home/Logic/YxbLogic')->getType();

        $this->assign('uid', session("uc_userinfo.id"));
        $this->assign('ordertype', $order['assign']);
        $this->assign('status', $type);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->assign('city', $city);
        $this->display();
    }

    //ERP审核管理
    public function examine(){
        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $post = I('get.');
        $ordertype = I('get.ordertype');
        $order = I('get.order');
        //获取order条件(排序)
        $order = D('Home/Logic/YxbLogic')->getOrderTimeInfo($ordertype,$order);
        //获取公司个数
        $count = D('Home/Logic/YxbLogic')->getErpCompanyTimeCount($post);

        //获取公司列表
        $info = D('Home/Logic/YxbLogic')->getErpCompanyTimeList($post,$order['order'],$count);

        //审核状态
        $type =  D('Home/Logic/YxbLogic')->getShengheType();
        $this->assign('uid', session("uc_userinfo.id"));
        $this->assign('ordertype', $order['assign']);
        $this->assign('status', $type);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->assign('city', $city);
        $this->display();
    }


    //创建账号
    public function add(){
        //公司id是否符合
        if(I('get.userid')){
            $userid = trim(I('get.userid'));
            $result =  D('Home/Logic/YxbLogic')->getUserInfo($userid);
            if(!$result['status']){
                $this->error($result['info']);
            }

            $this->assign('info',$result['data']);
        }

        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $this->assign('city',$city);
        $this->assign('company_id',$userid);
        $this->display();
    }

    //ajax创建ERP
    public function addCompany(){
        if($_POST){
            $post = I('post.');

            $post['userid'] = empty($post['userid'])?'':trim($post['userid']);
            $post['jc'] = empty($post['jc'])?'':trim($post['jc']);
            $post['cs'] = empty($post['cs'])?'':trim($post['cs']);
            $post['account'] = empty($post['account'])?'':trim($post['account']);
            $post['qc'] = empty($post['qc'])?'':trim($post['qc']);
            $post['dz'] = empty($post['dz'])?'':trim($post['dz']);
            $post['contact_name'] = empty($post['contact_name'])?'':trim($post['contact_name']);
            $post['contact_tel'] = empty($post['contact_tel'])?'':trim($post['contact_tel']);
            $post['contact_wx'] = empty($post['contact_wx'])?'':trim($post['contact_wx']);
            $post['start_time'] = empty($post['start_time'])?'':trim($post['start_time']);
            $post['end_time'] = empty($post['end_time'])?'':trim($post['end_time']);

            //验证唯一性 输入的全称简称账号user表不存在 账号erp表不存在
            $verify = D('Home/Logic/YxbLogic')->getCompanyVerify($post);

            if($verify['status'] == 1){
                //验证有效时间范围
                $nowtime = strtotime(date('Y-m-d 00:00:00'));
                $start_time = strtotime($post['start_time']);
                if($nowtime>$start_time){
                    $this->ajaxReturn(array("info"=>'开始日期必须大于等于今天',"status"=>0));
                }else{
                    //获取公司id
                    $info = D('Home/Logic/YxbLogic')->getUserId($post);
                    if(!empty($info['status'])&&isset($info['status'])){
                        //添加erp信息
                        $result = D('Home/Logic/YxbLogic')->addErp($info['data'],$post);
                        $this->ajaxReturn(array("info"=>$result['info'],'data'=>$result['nopass'],"status"=>$result['status']));
                    }else{
                        $this->ajaxReturn(array("info"=>$info['info'],"status"=>$info['status']));
                    }
                }
            }else{
                $this->ajaxReturn(array("info"=>'该公司已存在',"status"=>$verify['status']));
            }
        }
    }

   public function editErp(){
       if($_POST){
           $name = trim(I('post.name'));
           $tel = trim(I('post.tel'));
           $wx = trim(I('post.wx'));
           $id = trim(I('post.id'));
           $info = D('Home/Logic/YxbLogic')->editErp($name,$tel,$wx,$id);
           $this->ajaxReturn(array("info"=>$info['info'],"status"=>$info['status']));
       }
   }



    //重置密码
    public function makeErpPsw(){
        if($_POST){
            $id = trim(I('post.id'));
            $info = D('Home/Logic/YxbLogic')->makeErpPsw($id);
            $this->ajaxReturn(array("info"=>$info['info'],'data'=>$info['data'],"status"=>$info['status']));
        }
    }


    //编辑账号
    public function edit(){

        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $pageType =  trim(I('get.pagetype'));
        $userid = trim(I('get.userid'));
        $timeid = trim(I('get.timeid'));
        $tiao  = I('get.tiao');
        $tiao = empty($tiao)?1:$tiao;
        $info =  D('Home/Logic/YxbLogic')->getErpInfo($userid,$timeid);
        if($info['row']['type'] == 12 || $info['row']['type'] == 13 || $info['row']['type'] == 4){
            if($pageType !=2 ){
                unset($info['row']['start_time']);
                unset($info['row']['end_time']);
                $info['row']['sum_day'] = 0;
                $info['row']['sum_month'] = 0;
                $info['row']['remain_day'] = 0;
            }
        }
        //获取日志表相关记录
        $logList = D('Home/Logic/YxbLogic')->getErpLogInfo($info['row']['time_id']);
        if(empty($info['row'])){
            $this->_error('参数错误');
        }

        $this->assign('tiao',$tiao);
        $this->assign('logList',$logList);
        $this->assign('pageType',$pageType);
        $this->assign('timeList',$info['list']);
        $this->assign('info',$info['row']);
        $this->assign('city',$city);
        $this->display();
    }

    //查看账号
    public function chakan(){
        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $pageType =  trim(I('get.pagetype'));
        $userid = trim(I('get.userid'));
        $timeid = trim(I('get.timeid'));
        $info =  D('Home/Logic/YxbLogic')->getErpInfo($userid,$timeid);
        if($info['row']['type'] == 12 || $info['row']['type'] == 13 || $info['row']['type'] == 4){
            if($pageType !=2 ){
                $info['row']['sum_day'] = 0;
                $info['row']['sum_month'] = 0;
                $info['row']['remain_day'] = 0;
            }
        }
        //获取日志表相关记录
        $logList = D('Home/Logic/YxbLogic')->getErpLogInfo($info['row']['time_id']);
        if(empty($info['row'])){
            $this->_error('参数错误');
        }

        $this->assign('logList',$logList);
        $this->assign('pageType',$pageType);
        $this->assign('timeList',$info['list']);
        $this->assign('info',$info['row']);
        $this->assign('city',$city);
        $this->display('edit');
    }

    //审核账号
    public function shenghe(){
        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $pageType =  trim(I('get.pagetype'));
        $userid = trim(I('get.userid'));
        $timeid = trim(I('get.timeid'));
        $info =  D('Home/Logic/YxbLogic')->getErpInfo($userid,$timeid);

        if($info['row']['type'] == 12 || $info['row']['type'] == 13 || $info['row']['type'] == 4){
            if($pageType !=2 ){
                unset($info['row']['start_time']);
                unset($info['row']['end_time']);
                $info['row']['sum_day'] = 0;
                $info['row']['sum_month'] = 0;
                $info['row']['remain_day'] = 0;
            }
        }
        //获取日志表相关记录
        $logList = D('Home/Logic/YxbLogic')->getErpLogInfo($info['row']['time_id']);

        $this->assign('logList',$logList);
        $this->assign('pageType',$pageType);
        $this->assign('timeList',$info['list']);
        $this->assign('info',$info['row']);
        $this->assign('city',$city);
        $this->display();
    }

    public function getErpLog(){
        if($_POST){
            $company_id = trim(I('post.company_id'));
            $info = D('Home/Logic/YxbLogic')->getErpLog($company_id);
            $this->ajaxReturn(array("status"=>1,'data'=>$info));
        }
    }

    //更改审核状态
    public function editErpType(){
        if($_POST){
            $type = intval(I('post.type'));
            $time_id = intval(I('post.time_id'));
            $old_type = intval(I('post.old_type'));
            $log_type = intval(I('post.log_type'));
            $id = intval(I('post.id'));
            $remark = trim(I('post.remark'));
            $result =  D('Home/Logic/YxbLogic')->editErpType($type,$old_type,$log_type,$time_id,$id,$remark);
            $this->ajaxReturn(array("status"=>$result['status'],'info'=>$result['info']));
        }
    }



    //续费
    public function addErpTime(){
        if($_POST){
            $id = trim(I('post.id'));
            $post['start_time'] =  trim(I('post.start_time'));
            $post['end_time'] =  trim(I('post.end_time'));
            $result =  D('Home/Logic/YxbLogic')->addErpTime($id,$post);
            $this->ajaxReturn(array("status"=>$result['status'],'info'=>$result['info']));
        }
    }

    //申请开通
    public function editErpTime(){
        if($_POST){
            $id = trim(I('post.id'));
            $time_id = trim(I('post.time_id'));
            $data['start_time'] =  trim(I('post.start_time'));
            $data['end_time'] =  trim(I('post.end_time'));
            $result =  D('Home/Logic/YxbLogic')->editErpTime($id,$time_id,$data);
            $this->ajaxReturn(array("status"=>$result['status'],'info'=>$result['info']));
        }
    }


    //账号详情
    public function detail()
    {
        $userid = trim(I('get.userid'));
        $timeid = trim(I('get.timeid'));
        $info = D('Home/Logic/YxbLogic')->getErpInfo($userid,$timeid);
        $accountList = D('Home/Logic/YxbLogic')->getErpAccountList($userid);
//        $stationList = D('Home/Logic/YxbLogic')->getErpGroupList($userid);
        $this->assign('info', $info['row']);
        $this->assign('accountList', $accountList);
//        $this->assign('stationList', $stationList);
        $this->display();
    }

    //ERP订单列表
    public function orderlist(){
        $company_id = I('get.cid');
        //排序
        $ordertype = I('get.ordertype');
        $order = I('get.order');
        //切换按钮
        $btype = I('get.btype');
        //查询条件
        $where['dd_type'] = I('get.dd_type');
        if(!empty($where['dd_type'])&&isset($where['dd_type'])){
            $where['dd_type'] =  explode("-",$where['dd_type']); // 订单类型
        }
        $where['dd_status'] =  I('get.dd_status'); // 订单状态
        if(!empty( $where['dd_status'])&&isset( $where['dd_status'])){
            $where['dd_status'] =  explode("-",$where['dd_status'] );
        }
        $where['sg_status'] =  I('get.sg_type'); // 施工状态
        if(!empty( $where['sg_status'])&&isset( $where['sg_status'])){
            $where['sg_status'] =  explode("-", $where['sg_status']);
        }

        $where["company_id"] = $company_id;
        //获取状态条件
        $where['state'] =  D('Home/Logic/YxbLogic')->getButtonType($btype);
        //获取order条件(排序)
        $order = D('Home/Logic/YxbLogic')->getPaixuOrderInfo($ordertype,$order);

        //获取公司个数
        $count = D('Home/Logic/YxbLogic')->getOrdersAccountCount($where);
        if($count>0){
            //获取公司列表
            $info = D('Home/Logic/YxbLogic')->getOrdersAccountList($where,$order['order'],$count);
        }

        //页面订单类型
        $result['dd_type'] = D('Home/Logic/YxbLogic')->getOrderSource();
        $dd_status_all = D('Home/Logic/YxbLogic')->getOrderStatus();
        //页面订单状态
        if(!empty($where['state'])&&isset($where['state'])){
            //获取对应状态
            foreach($where['state'] as $val){
                $result["dd_status"][$val] = $dd_status_all[$val];
            }
        }else{
            //获取全部状态
            $result["dd_status"] = $dd_status_all;
        }
        unset($dd_status_all);
        //页面施工状态
        $result["sg_type"] = D('Home/Logic/YxbLogic')->getShiGongStatus();
        $result=json_encode( $result );
        $this->assign('type', $result);
        $this->assign('paixustatus',  I('get.ordertype'));
        $this->assign('paixu', I('get.order'));

        $this->assign('ordertype', $order['assign']);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->display();
    }

    //ERP装修订单
    public function order(){
        //获取城市信息
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $post = I('get.');
        $ordertype = I('get.ordertype');
        $order = I('get.order');
        //获取order条件(排序)
        $order = D('Home/Logic/YxbLogic')->getPaixuInfo($ordertype,$order);

        //获取公司个数
        $count = D('Home/Logic/YxbLogic')->getOrdersCount($post);
        if($count>0){
            //获取公司列表
            $info = D('Home/Logic/YxbLogic')->getOrdersList($post,$order['order'],$count);
        }

        //订单来源
        $type["orderSource"] =  D('Home/Logic/YxbLogic')->getOrderSource();
        //订单状态
        $type["orderStatus"] =  D('Home/Logic/YxbLogic')->getOrderStatus();
        //施工状态
        $type["shiGongStatus"] = D("Home/Logic/YxbLogic")->getShiGongStatus();


        $this->assign('type',$type);
        $this->assign('ordertype', $order['assign']);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->assign('city', $city);
        $this->display();
    }

    //ERP订单详情
    public function orderdetail(){
        $id = I('get.id');
        $cid = I('get.cid');
        //获取erp账号信息
        $info = D('Home/Logic/YxbLogic')->getOrderErpInfo($id,$cid);
        $type = D('Home/Logic/YxbLogic')->getorderStatusRange();
        if(!empty($info['state'])&&isset($info['state'])){
            foreach($type as $key=>$val){
                if(in_array($info['state'], $val["list"])){
                    $current = $val;
                    break;
                }
            }
        }

        //获取日志信息
        $log = D('Home/Logic/YxbLogic')->getorderLog($id);
        $this->assign('log',$log);
        $this->assign('current',$current);
        $this->assign('type',$type);
        $this->assign('info',$info);
        $this->display();
    }

    //ERP意见反馈
    public function suggest(){
        $city =  D('Home/Logic/YxbLogic')->getAllCity();
        $this->assign("city", $city);
        $ordertype = I('get.ordertype');
        $order = I('get.order');
        if(!$ordertype){
            $ordertype = 2;  //1为公司ID排序， 2为反馈时间排序，3为最新处理时间排序
        }
        if(!$order){
            $order = 1; //1表示降序，2表示升序。 默认为1
        }
        //获取查询条件并查询
        $list = $this->getSuggestList(I('get.'),$ordertype,$order);
        //获取反馈渠道
        $fankui = D('Home/Db/YxbAccount')->getFeedbackchannel();
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this -> assign('handlestatus',I('handlestatus'));
        $this -> assign('qudao',I('qudao'));
        $this -> assign('channel',$fankui);
        $this->display();
    }

    //ERP意见反馈 / 查看
    public function sugcheck(){
        $loginuser['id'] = session('uc_userinfo.id');
        $loginuser['name'] = session('uc_userinfo.name');
        $feedbackid = trim(I('get.id'));
        $param['id'] = $feedbackid;
        //获取意见反馈信息
        $feedbackinfo = D('Home/Logic/YxbLogic')->getFeedbackById($param);
        //获取处理记录
        $handleList = D('Home/Db/YxbAccount')->getFeedbackHandle($param);
        //改变状态名称
        $newHandleList = D('Home/Logic/YxbLogic')->changeHandleStatus($handleList);
        $this -> assign('feedbackinfo',$feedbackinfo);
        $this -> assign('handlelist',$newHandleList);
        $this -> assign('loginuser',$loginuser);
        $this->display();
    }

    /**
     * 获取意见反馈列表
     * @param  [type] $param     [查询条件]
     * @param  [type] $ordertype [排序类型] 1为公司ID排序， 2为反馈时间排序，3为最新处理时间排序
     * @param  [type] $order     [排序顺序] 1表示降序，2表示升序。 默认为1
     * @return [type]            [description]
     */
    public function getSuggestList($param,$ordertype,$order){
        import('Library.Org.Util.Page');
        //正常的查询列表
        $model = D('Home/Db/YxbAccount');
        $count = $model->getSuggestListCount($param,$ordertype,$order);
        if ($count > 0) {
            $p = new \Page($count, 20);
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $show = $p->show();
            $list = $model->getSuggestList($param,$ordertype,$order,$p->firstRow,$p->listRows);
        }
        $newlist = D('Home/Logic/YxbLogic')->changeHandleStatus($list);
        return array("page" => $show, "list" => $newlist);
    }

    //接收提交的意见反馈处理的内容
    public function submithandle(){
        $data = $_POST;
        $getRuquest =  D('Home/Logic/YxbLogic')->saveFeedbackHandle($data);
        $this -> ajaxReturn($getRuquest);
    }

}