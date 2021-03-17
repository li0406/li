<?php
namespace User\Controller;
use User\Common\Controller\CompanyBaseController;
use Common\Model\Logic\CardLogicModel;
use Common\Enums\ApiConfig;
class OneselfeventController extends CompanyBaseController{
    private $common_status = [
        2=> '待确认',
        6=> '未生效',
        7=> '可用',
        4=> '失效',
        1=> '撤回',
        3=> '下架',
        8=> '未通过',
        5=> '已禁用'
    ];
    public function _initialize()
    {
        parent::_initialize();//先去走父类的构造
        //为了避免SESSION的丢失,ON字段获取不到,现从数据库中查询ON字段
        $userInfo = D("User")->getSingleUserInfoById($_SESSION["u_userInfo"]["id"]);
        if(count($userInfo) == 0){
             $this->ajaxReturn(array("data"=>"","info"=>"您的登录超时了,请重新登录！","status"=>0));
        }
        $this->assign("nav",11);
    }


    public function index()
    {
        $info["user"] = $this->baseInfo;
        //获取自主活动列表
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        if (intval(I('get.state')) != '') {
            $state = intval(I('get.state'));
            //正在进行
            if($state == '1'){
                $condition['types'] = '1';
            }
            //等待中
            elseif ($state == '2') {
                $condition['types'] = '0';
            }
            //过期活动
            elseif ($state == '3') {
                $condition['types'] = '-1';
            }
            //暂停
            elseif ($state == '4') {
                $condition['types'] = '2';
            }else{
                $condition = '';
            }
        }

        $event = $this->getallevent($_SESSION["u_userInfo"]["id"],$condition,$pageIndex,$pageCount);

        $this->assign('event',$event['info']);
        $this->assign('info',$info);
        $this->assign('page',$event['page']);
        $this->display();
    }

    /**
     * 新增/编辑模块
     * @return [type] [description]
     */
    public function eventup()
    {
        $info["user"] = $this->baseInfo;
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        if ($_POST) {
            //为了避免SESSION的丢失,ON字段获取不到,现从数据库中查询ON字段
            $userInfo = D("User")->getSingleUserInfoById($_SESSION["u_userInfo"]["id"]);
            if(count($userInfo) == 0){
                 $this->ajaxReturn(array("data"=>"","info"=>"您的登录超时了,请重新登录！","status"=>0));
            }

            $content =htmlspecialchars_decode(I("post.content"));

            if($userInfo["on"] == 2){
                $content = $filter->filter_common($content,array("Sbc2Dbc","filter_empty",array("filter_sensitive_words",array(2,3,4,5)),"filter_link"));

            }else{
                $content = $filter->filter_common($content,array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,4,5)),"filter_script"));
            }
            $title =  $filter->filter_title(I("post.title"));
            $data = array(
                    "title"=>$title,
                    "text"=> $content,
                    "start"=>strtotime(I('post.start')),
                    "end"=>strtotime(I('post.end')),
                          );
            if (I("post.id") !== "") {
                //编辑提交
                $id = I("post.id");
                $log_id = $id;
                $data["uptime"] = time();
                $result = D("Oneself")->editInfo($id,$_SESSION["u_userInfo"]["id"],$data);
                $msg = "用户编辑文章【".$title."】 成功";
            }else{
                //新建提交
                $data["cid"] = $_SESSION["u_userInfo"]["id"];
                $data["time"] = time();
                $data["uptime"] = time();
                $result = D("Oneself")->addEvent($data);
                $log_id = $result;
                $msg = "用户添加文章【".$title."】 成功";
            }

            if ($result !== false) {
                //改变装修公司表活动记录数
                $this->changeUserSales($log_id);

                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                //记录日志
                $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
            }

        }else{
            if(I("get.id") !== ""){
                //编辑状态
                $id = I("get.id");
                $event = D("Oneself")->getEventByCid($id,$_SESSION["u_userInfo"]["id"]);
                //$article["text"] = preg_replace("/\s/","",$article["text"]);
                foreach ($event as $key => $value) {
                    $event[$key]['start'] = date('Y-m-d', $value['start']);
                    $event[$key]['end'] = date('Y-m-d', $value['end']);
                }
                $info['event'] = $event['0'];
            }
/*            //文章分类
            $articleType = D("Infotype")->getTypes();
            //删除优惠信息,暂时先删除最后一个
            array_pop($articleType);
            $info["articleType"] = $articleType;*/
            //基本信息
            //$info["user"] = $this->baseInfo;
            $this->assign("info",$info);
             //tab栏
            //$this->assign("tabNav",1);
            //侧边栏

            $this->display();
        }
    }
    /**
     * 暂停活动
     * @return [type] [description]
     */
    public function stopevent()
    {
        if ($_POST) {
            $id = intval(I('post.id'));
            $state = intval(I('post.state'));
            if ($state == 1) {
                $data['state'] = 0;
            }else{
                $data['state'] = 1;
            }
            $result = D('Oneself')->stopEvent($id,$_SESSION["u_userInfo"]["id"],$data);
            if ($result !== false) {
                //改变装修公司表活动记录数
                $this->changeUserSales($id);
                //记录日志
                $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>'',"info"=>"操作失败","status"=>0));
    }

    /**
     * 获取所有自主活动列表
     * @return [type] [description]
     */
    public function getallevent($cid,$condition,$pageIndex,$pageCount)
    {
        $count = D('Oneself')->getallEventCount($cid,$condition);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();

            $info = D('Oneself')->getallEvent($cid,$condition,($page->pageIndex-1)*$pageCount,$pageCount);
            $time = time();
            foreach ($info as $key => $value) {
                //根据时间判断状态

                //默认状态是0：等待中，当处于活动时间中，将状态变成正在经行中“1”
                if ($value['start'] <= $time && $value['end'] >= $time) {
                    if ($info[$key]['types'] != '1') {
                        $info[$key]['types'] = '1';
                        $data['types'] = 1;
                        D('Oneself')->stopEvent($value['id'],$cid,$data);
                    }
                }
                //如果结束时间小于当前时间，则活动过期
                elseif ($value['end'] < $time) {
                    if ($info[$key]['types'] != '-1') {
                        $info[$key]['types'] = '-1';
                        $data['types'] = -1;
                        D('Oneself')->stopEvent($value['id'],$cid,$data);
                    }
                }
                elseif ($value['start'] > $time) {
                    if ($info[$key]['types'] != '0') {
                        $info[$key]['types'] = '0';
                        $data['types'] = 0;
                        D('Oneself')->stopEvent($value['id'],$cid,$data);
                    }

                }

                $info[$key]['start'] = date('Y-m-d', $value['start']);
                $info[$key]['end'] = date('Y-m-d', $value['end']);
                $info[$key]['time'] = date('Y-m-d', $value['time']);
            }
            return array("info"=>$info,"page"=>$pageTmp);
        }
    }

    /**
     * 删除活动
     * @return [type] [description]
     */
    public function delevent()
    {
        if($_POST){
            $id = intval(I("post.id"));
            $i = D('Oneself')->delEvent($id,$_SESSION["u_userInfo"]["id"]);
            if($i !== false){
                //改变装修公司表活动记录数
                $this->changeUserSales($id);
                //导入扩展文件
                /*import('Library.Org.Util.App');
                $app = new \App();
                //记录日志
                $data = array(
                  "username"=>$_SESSION["u_userInfo"]["name"],
                  "userid"=>$_SESSION["u_userInfo"]["id"],
                  "ip"=>$app->get_client_ip(),
                  "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                  "info"=>"用户删除文章【id:".$id."】 成功",
                  "time"=>date("Y-m-d H:i:s"),
                  "action"=>CONTROLLER_NAME."/".ACTION_NAME
                );
                D("Loguser")->addLog($data);*/
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"操作失败！","status"=>0));
    }

    /**
     * 改变装修公司表活动记录数
     * @param $log_id 活动id
     */
    private function changeUserSales($log_id)
    {
        $articleInfo = D('CompanyActivity')->getCompanyActiveInfoById($log_id); //获取公司id
        $articleCount = D('CompanyActivity')->getCompanyActiveCountByIds($articleInfo['cid']);
        D('User')->edtiUserInfo($articleInfo['cid'], ['sales_count' => $articleCount]);
    }

    /**
     * 专用页面：获取当前装修公司的专用礼券
     */
    public function special(){
        $cardlogic = new CardLogicModel();
        $pageIndex = I('get.p')?I('get.p'):1;
        $pageCount = 10;
        $count = $cardlogic->getSpecialCardByIdCount(session('u_userInfo.id'),I('get.'));//获取满足条件的专用券总量

        import('Library.Org.Page.Page');
        //自定义配置项
        $config  = array("prev","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();

        $list = $cardlogic->getSpecialCardById(session('u_userInfo.id'),I('get.'),$pageIndex,$pageCount); //获取满足条件的专用券列表

        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);

        $this->assign('list',$list);
        $this->assign('page',$pageTmp);
        $this->assign('cardstatus',I('get.cardstatus'));
        $this->assign('checkstatus',I('get.checkstatus'));
        $this->assign('type',2);
        $this->display();
    }
    /**
     * 添加专用礼券
     *
     * */
    public function addgift(){
        $cardlogic = new CardLogicModel();

        if($_POST){
            $add = $cardlogic->addSpecialCard($_POST);
            if($add){
                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                $this->ajaxReturn(array("error_code"=>ApiConfig::ADD_SUCCESS,"error_msg"=>"添加成功"));
            }else{
                $this->ajaxReturn(array("error_code"=>ApiConfig::ADD_ERROR,"error_msg"=>"添加失败"));
            }
        }

        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);

        $this->assign('type',2);
        $this->display();
    }

    /**
     * 重新申请专用礼券
     */
    public function editgift(){
        $cardlogic = new CardLogicModel();

        if($_POST){
            $add = $cardlogic->editSpecialCard($_POST);
            if($add){
                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                $this->ajaxReturn(array("error_code"=>ApiConfig::EDIT_SUCCESS,"error_msg"=>"保存成功"));
            }else{
                $this->ajaxReturn(array("error_code"=>ApiConfig::EDIT_ERROR,"error_msg"=>"保存失败"));
            }
        }else{
            $cardId = I('get.id');
            $info = array();
            if($cardId){
                $info = $cardlogic->getSpecialCardInfoById($cardId);
                $this->assign('info',$info);
            }else{
                $this->_error('无法获取礼券信息 :(');
            }
        }

        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);

        $this->assign('type',2);
        $this->display();
    }

    /**
     * regressionAction 撤回操作
     */
    public function regressionAction(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $return = $cardlogic->regressionAction($_POST);
            if($return){
                $this->ajaxReturn(array("error_code"=>ApiConfig::EDIT_SUCCESS,"error_msg"=>"撤回成功"));
            }else{
                $this->ajaxReturn(array("error_code"=>ApiConfig::EDIT_ERROR,"error_msg"=>"撤回失败"));
            }
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_NODATA,"error_msg"=>"无请求数据"));
        }
    }

    /**
     * 专用礼券详情页
     */
    public function specialdetail(){
        $cardlogic = new CardLogicModel();
        $cardId = I('get.id'); // 礼券id
        $cardinfo = array();
        if($cardId){
            $cardinfo = $cardlogic->getSpecialCardInfoById($cardId);
            if($cardinfo){
                $cardinfo['money1'] = $cardinfo['money1'] ? intval($cardinfo['money1']) : 0;
                $cardinfo['money2'] = $cardinfo['money2'] ? intval($cardinfo['money2']) : 0;
            }
        }
        // dump($cardinfo);die;


        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        //var_dump($cardinfo);
        $this->assign('type',2);
        $this->assign('cardinfo',$cardinfo);
        $this->display();
    }


    /**
     * 上架/下架专用券
     */
    public function showOrHidenCard(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $return = $cardlogic->showOrHidenCard($_POST);
            if($return){
                $this->ajaxReturn(array("error_code"=>ApiConfig::EDIT_SUCCESS,"error_msg"=>"保存成功"));
            }else{
                $this->ajaxReturn(array("error_code"=>ApiConfig::EDIT_ERROR,"error_msg"=>"保存失败"));
            }
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_NODATA,"error_msg"=>"无请求数据"));
        }
    }


    /**
     * getsearch  领券查询页面
     */
    public function getsearch(){
        $cardlogic = new CardLogicModel();
        $pageIndex = I('get.p')?I('get.p'):1;
        $pageCount = 10;
        $count = $cardlogic->getReceiveCardsCount(session('u_userInfo.id'),I('get.'));//获取满足条件的专用券总量
        if(!I('get.id')){
            $this->_error();
        }
        import('Library.Org.Page.Page');
        //自定义配置项
        $config  = array("prev","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        $list = $cardlogic->getReceiveCards(session('u_userInfo.id'),I('get.'),$pageIndex,$pageCount); //获取满足条件的专用券列表

        //公司信息
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);


        $this->assign('type',2);
        $this->assign('list',$list);
        $this->assign('page',$pageTmp);
        $this->display();
    }

    /**
     * 通用页面
     */
    public function common(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $cardid = I('post.cardid');
            $status = I('post.status');
            $result = $cardlogic->changeRecord($cardid,$status);
            if($result){
                $this->ajaxReturn(['status'=>0,'info'=>'操作成功']);
            }else{
                $this->ajaxReturn(['status'=>1,'info'=>'操作失败']);
            }
        }
        $where = I('get.');
        $where['id'] = session('u_userInfo.id');
        $result = $cardlogic->common($where);
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        $this->assign('status',$this->common_status);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('type',3);
        $this->display();
    }

    /**
     * 可领用礼券
     */
    public function receivegift(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $data['card_id'] = trim(I('post.cardid'));
            //判断是否可用
            $info = $cardlogic->getCardInfo($data['card_id']);
            if(!empty($info)&&isset($info)){
                if($info['enable'] == 2 && time()>$info['disable_time']){
                    $this->ajaxReturn(['status' => 1 , 'info' => '抱歉,该礼券已禁用,无法领取']);
                }
            }else{
                $this->ajaxReturn(['status' => 1 , 'info' => '抱歉,该礼券不存在']);
            }

            $data['com_id'] =  session('u_userInfo.id');
            $dataRecord['amount'] = trim(I('post.number'));
            $dataRecord['activity_start'] = strtotime(I('post.hdtime_start'));
            $dataRecord['activity_end'] = strtotime(I('post.hdtime_end'));
            $dataRecord['start'] = strtotime(I('post.time_start'));
            $dataRecord['end'] = strtotime(I('post.time_end'));

            //添加领用信息
            $result = $cardlogic->addCardCom($data['card_id'],$data['com_id'],$dataRecord);
            if($result>0){
                //更新最后操作时间（op_info_last_time字段）
                $changedata = [];
                $changedata['op_info_last_time'] = time();
                D("Usercompany")->editUserCompany($_SESSION["u_userInfo"]["id"],$changedata);

                $this->ajaxReturn(['status' => 0 , 'info' => '操作成功']);
            }else{
                $this->ajaxReturn(['status' => 1 , 'info' => '操作失败']);
            }
        }
        $result = $cardlogic->receivegift();
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('type',3);
        $this->display();
    }

    //可领用详情页
    public function receivedetail(){
        $cardlogic = new CardLogicModel();
        $info = $cardlogic->getCardInfo(I('get.id'));
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        $this->assign('info',$info);
        $this->assign('type',3);
        $this->display('commondetail');

    }

    //通用券详情
    public function commondetail(){
        $cardlogic = new CardLogicModel();
        $info = $cardlogic->getCommonInfo(I('get.id'));
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        $this->assign('type',3);
        $this->display();
    }

    //领用查询
    public function getsearchcommon(){
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->getcarduser(I('get.'));
        $info["user"] = $this->baseInfo;
        $this->assign('info',$info);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('type',3);
        $this->display();
    }
}
