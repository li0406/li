<?php

//合同票据

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Qiniu\Auth;

class ContractmanageController extends HomeBaseController
{
    //定义分类数组
    private $typeArray = array(
                    '1' => array('name' => '商务制式', 'code' => 'QZ'),
                    '2' => array('name' => '商务电子', 'code' => 'YWT'),
                    '3' => array('name' => '外销制式', 'code' => 'QZ'),
                    '4' => array('name' => '外销电子', 'code' => 'QZW'),
                    '5' => array('name' => '商务制式', 'code' => 'SJYWT'),
                    '6' => array('name' => '外销制式', 'code' => 'SJYWT'),
                );

    /*********************************外销部分！！！***********************************/
    public function contractPjczwxzl(){
        //查询外销助理待收货列表
        //外销助理寄出合同状态为status=2
        $this->assign('expressstatus',3);
        $type = 6;//助理待收，寄回状态status=6
        $expresslist = D('Contractmanage')->getExpressList($type);
        //查询待审核的合同  status=7 speciel=4 beaccept=2 签约待审核 | status=7 speciel=5 beaccept=2作废待审核 | status=9  special=7 beaccept=2遗失待审核
        $qianyue_list = D('Contractmanage')->getNeedCheckList(7,4,2);
        $zuofei_list = D('Contractmanage')->getNeedCheckList(7,5,2);
        $yishi_list = D('Contractmanage')->getNeedCheckList(9,7,2);
        //查询助理审核过的记录
        $hasChecked = D('Contractmanage')->getAssistantCheckedList();

        $this->assign('isshow',1);
        $this->assign('role',$_SESSION['uc_userinfo']['role_name']);
        $this->assign('expresslist',$expresslist);
        $this->assign('qianyuelist',$qianyue_list);
        $this->assign('zuofeilist',$zuofei_list);
        $this->assign('yishilist',$yishi_list);
        $this->assign('haschecked',$hasChecked);
        $this->assign('bumen',2);
        $this->assign('from',1);
        $this->display();
    }

    //根据传入的合同编号或者销售名称查询对应的订单号
    public function searchForContract()
    {
        if($_POST){
            $keyword = I('post.keyword');
            $position = I('post.position');//1是助理审核查询  2是审核记录查询  3是销售管理合同列表查询
            $user = D("Contractmanage")->findUsers($keyword,1);
            if(!empty($user)){
                $uid = $user['id'];
            }else{
                $uid = $keyword;
            }
            //根据position查询对应数据
            if($position == 1){
                //查询助理待审核 签约和作废
                //status=7 speciel=1 签约待审核 | status=7 speciel=5作废待审核
                //status=9 speciel=4 商务待审核
                $uid = str_replace("SJYWT","",$uid);
                $uid = str_replace("QZ","",$uid);
                if($_SESSION['uc_userinfo']['department_id'] == 5){
                    //外销
                    $type = 7;
                }else{
                    //商务
                    $type = 9;
                }
                $qianyue_list = D('Contractmanage')->getExaminedContract($type,4,$uid);
                $zuofei_list = D('Contractmanage')->getExaminedContract($type,5,$uid);
                //签约列表
                if(!empty($qianyue_list)){
                    foreach ($qianyue_list as $k => $v) {
                        if($v['type'] < 6){
                            $data_type = 'data-type="1"';
                        }else{
                            $data_type = 'data-type="2"';
                        }
                        $qianyue_str .= '<tr>
                        <td>'.$v['contractid'].'</td>
                        <td>'.getSaleUserName($v['sendout']).'</td>
                        <td><button class="button small_button assistantCheckNow" data-beaccpet="3" data-saler="'.$v['sendout'].'" data-id="'.$v['id'].'" data-status="5" data-special="1" data-contractid="'.$v['contractid'].'" '.$data_type.'>通过</button>&nbsp;&nbsp;&nbsp;&nbsp;<button class="button nobg small_button assistantCheckNow" data-beaccpet="4" data-saler="'.$v['sendout'].'"  data-id="'.$v['id'].'" data-status="4" data-special="2" data-contractid="'.$v['contractid'].'" '.$data_type.'>作废</button></td>
                    </tr>';
                    }
                }else{
                    $qianyue_str ="没有搜索到对应内容！";
                }

                //作废列表
                if(!empty($zuofei_list)){
                    foreach ($zuofei_list as $k => $v) {
                        if($v['type'] < 6){
                            $data_type = 'data-type="1"';
                        }else{
                            $data_type = 'data-type="2"';
                        }
                        $zuofei_str .= '<tr>
                        <td>'.$v['contractid'].'</td>
                        <td>'.getSaleUserName($v['sendout']).'</td>
                        <td><button class="button small_button assistantCheckNow"  data-beaccpet="4" data-saler="'.$v['sendout'].'" data-id="'.$v['id'].'" data-status="4" data-special="2" data-contractid="'.$v['contractid'].'" '.$data_type.'>通过</button></td>
                    </tr>';
                    }
                }else{
                    $zuofei_str = "没有搜索到对应内容！";
                }

                $result['qianyue'] = $qianyue_str;
                $result['zuofei'] = $zuofei_str;
                $this->ajaxReturn(array('status'=>1,'data'=>$result));
            }elseif ($position == 2) {
                //查询已经审核过的
                $hasChecked = D('Contractmanage')->getAssistantCheckedList($uid);
                if(!empty($hasChecked)){
                    foreach ($hasChecked as $k => $v) {
                        if($v['beaccept'] == 3){
                            $type = '<span class="has_end">签约</span>';
                        }elseif($v['beaccept'] == 4){
                            $type = '<span class="no_start">作废</span>';
                        }elseif($v['beaccept'] == 5){
                            $type = '<span class="re_write">重填</span>';
                        }
                        $result .= '<tr>
                            <td>'.$v['contractid'].'</td>
                            <td>'.getSaleUserName($v['sendout']).'</td>
                            <td>'.$type.'</td>
                        </tr>';
                    }
                }else{
                    $result = "没有搜索到对应内容！";
                }
                $this->ajaxReturn(array('status'=>1, 'info'=>'网络错误，请稍后重试！','data'=>$result));
            }elseif ($position == 3) {
                //销售合同查询
                $part = I("post.part");//1待签约查询  2签约待审核查询  3作废待审核  4遗失  5审核通过
                if($part == 1){
                    //查询待签约列表  status=4 special=1 sendout=UID
                    $result = D('Contractmanage')->getBeSignedUpList(4,1,$uid);
                    if(!empty($result)){
                        foreach ($result as $k => $v) {
                            if($v['type'] < 6){
                                $type = 'data-type="1"';
                            }else{
                                $type = 'data-type="2"';
                            }
                            $str .='<tr>
                                <td>'.$v['contractid'].'</td>
                                <td>
                                    <button class="button small_button besignedup_qianyue" data-id="'.$v['id'].'" data-special="4" data-contractid="'.$v['contractid'].'" '.$type.'>签约</button>
                                    &nbsp;&nbsp;
                                    <button class="button  small_button besignedup_zuofei" data-id="'.$v['id'].'" data-special="5" data-contractid="'.$v['contractid'].'" '.$type.'>作废</button>
                                    &nbsp;&nbsp;
                                    <button class="button  small_button besignedup_yishi" data-id="'.$v['id'].'" data-special="3" data-contractid="'.$v['contractid'].'" '.$type.'>遗失</button>
                                </td>
                            </tr>';
                        }
                    }else{
                        $str = '没有搜索到内容！';
                    }
                }elseif ($part == 2) {
                    //查询签约待审核状态列表 status=9 special=4 sendout=UID
                    $result = D('Contractmanage')->getBeSignedUpList(9,4,$uid);
                    if(!empty($result)){
                        foreach ($result as $k => $v) {
                            if($v['type'] < 6){
                                $type = 'data-type="1"';
                            }else{
                                $type = 'data-type="2"';
                            }
                            $str .='<tr>
                            <td>'.$v['contractid'].'</td>
                            <td>

                                <button class="button  small_button besignedup_zuofei" data-id="'.$v['id'].'" data-special="5" data-contractid="'.$v['contractid'].'" '.$type.'>作废</button>
                                &nbsp;&nbsp;
                                <button class="button  small_button besignedup_yishi" data-id="'.$v['id'].'" data-special="3" data-contractid="'.$v['contractid'].'" '.$type.'>遗失</button>
                            </td>
                        </tr>';
                        }
                    }else{
                        $str = '没有搜索到内容！';
                    }
                }elseif ($part == 3) {
                    //查询作废待审核状态列表 status=9 special=5 sendout=UID
                    $result = D('Contractmanage')->getBeSignedUpList(9,5,$uid);
                    if(!empty($result)){
                        foreach ($result as $k => $v) {
                            if($v['type'] < 6){
                                $type = 'data-type="1"';
                            }else{
                                $type = 'data-type="2"';
                            }
                            $str .='<tr>
                            <td>'.$v['contractid'].'</td>
                            <td>
                                <button class="button small_button besignedup_qianyue" data-id="'.$v['id'].'" data-special="4" data-contractid="'.$v['contractid'].'" '.$type.'>签约</button>
                                &nbsp;&nbsp;
                                <button class="button  small_button besignedup_yishi" data-id="'.$v['id'].'" data-special="3" data-contractid="'.$v['contractid'].'" '.$type.'>遗失</button>
                            </td>
                        </tr>';
                        }
                    }else{
                        $str = '没有搜索到内容！';
                    }
                }elseif ($part == 4) {
                    //查询遗失状态列表 status=4 special=3 sendout=UID
                    $result = D('Contractmanage')->getBeSignedUpList(4,3,$uid);
                    if(!empty($result)){
                        foreach ($result as $k => $v) {
                            if($v['type'] < 6){
                                $type = 'data-type="1"';
                            }else{
                                $type = 'data-type="2"';
                            }
                            $str .='<tr>
                            <td>'.$v['contractid'].'</td>
                            <td>
                                <button class="button small_button findBack" data-id="'.$v['id'].'" data-special="6" data-contractid="'.$v['contractid'].'" '.$type.'>找回</button>
                            </td>
                        </tr>';
                        }
                    }else{
                        $str = '没有搜索到内容！';
                    }
                }elseif ($part == 5) {
                    //审核通过列表
                    $result = D('Contractmanage')->getHasCheckedList($uid);
                    if(!empty($result)){
                        foreach ($result as $k => $v) {
                            if($v['type'] < 6){
                                $type = 'data-type="1"';
                            }else{
                                $type = 'data-type="2"';
                            }
                            $str .='<tr>
                            <td>'.$v['contractid'].'</td>
                            <td>
                                <button class="button small_button">已审核</button>
                            </td>
                        </tr>';
                        }
                    }else{
                        $str = '没有搜索到内容！';
                    }
                }
                $this->ajaxReturn(array('status'=>1, 'info'=>'网络错误，请稍后重试！','data'=>$str));
            }
        }else{
            $this->ajaxReturn(array('status'=>0, 'info'=>'网络错误，请稍后重试！'));
        }
    }

    public function contractPjczwxxs(){
        //获取上传Token
        $auth = new Auth(OP('QINIU_AK'), OP('QINIU_CK'));
        $policy = array(
            'returnBody' => '{"name":$(key)}',
            'saveKey' => 'commerceenclosure/$(year)$(mon)$(day)$(hour)$(min)$(sec)/$(fname)'
        );
        $vars['token'] = $auth->uploadToken(OP('QINIU_PRIVATE_BUCKET'), null, 3600, $policy);
        //模板赋值
        $this->assign('vars', $vars);
        //查询外销待收货列表
        //外销助理寄出合同状态为status=6
        $this->assign('expressstatus',6);
        $type = 3;//销售待收，寄出状态status=3
        $expresslist = D('Contractmanage')->getExpressList($type);

        //2018-3-8修改
        //查询待签约列表  status=4 special=1 sendout=UID
        $beSignedUp = D('Contractmanage')->getBeSignedUpList(4,1);
        //查询签约待审核状态列表 status=9 special=4 sendout=UID
        $signedForVerify = D('Contractmanage')->getBeSignedUpList(9,4);
        //查询作废待审核状态列表 status=9 special=5 sendout=UID
        $beCancellation = D('Contractmanage')->getBeSignedUpList(9,5);
        //查询遗失待审核状态列表 status=9 special=7 sendout=UID
        $beLost = D('Contractmanage')->getBeSignedUpList(9,7);
        //审核通过列表
        //签约 status=5 special=1 beaccept=3
        //作废 status=4 special=2 beaccept=4
        $hasChecked = D('Contractmanage')->getHasCheckedList();
        //var_dump($hasChecked);
        //查询待寄回公司列表  status=5 special=1签约 status=4 special=2作废
        //2018-3-23修改
        //签约待审核 status=9 special=4 beaccept=2
        //作废待审核 status=9 special=5 beaccept=2
        $sendBack = D('Contractmanage')->getSendBackList();//要寄回的合同（签约、作废状态）
        $this->assign('expresslist',$expresslist);
        $this->assign('sendback',$sendBack);
        $this->assign('besignedup',$beSignedUp);//待签约列表
        $this->assign('signedforverify',$signedForVerify);//签约待审核列表
        $this->assign('becancellation',$beCancellation);//作废待审核列表
        $this->assign('belost',$beLost);//遗失列表
        $this->assign('haschecked',$hasChecked);//已审核列表
        $this->assign('from',1);
        $this->display();
    }

    public function contractpjjzwx(){
        $this->assign('bumen',2);
        $this->display();
    }

    public function heTongpjsmx(){
        $this->display();
    }

    /*
     * 公司寄出合同/票据
     */
    public function sendContract()
    {
        if($_POST){
            //合同票据调整2018-3-23修改
            //合同编号统一为QZ 票据编号统一为SJYWT
            $contractstart  = I('post.contractStart');//合同起始
            $contractend    = I('post.contractEnd');//合同结束
            if(!empty($contractstart)){
                $contractcode   = 'QZ';//合同编号前缀
            }

            $ticketstart    = I('post.ticketStart');//票据开始
            $ticketend      = I('post.ticketEnd');//票据结束
            if(!empty($ticketstart)){
                $ticketcode     = 'SJYWT';//票据编号前缀
            }
            $userid         = I('post.userId');//出库对象（接收人）
            $sendtype       = I('post.sendType');//快递方式
            $danhao = '';
            if($sendtype == 1){
                $danhao         = I('post.danhao');//快递单号
            }
            $status         = I('post.status');//寄出类别 助理寄出为3  外销寄出为6
            $contractContent = $ticketContent = $saveAll = [];
            if(!empty($contractcode)){
                $contractstart = str_replace($contractcode, '', $contractstart);
                $contractend = str_replace($contractcode, '', $contractend);
                //生成合同编号，判断 合同编号是否存在 且 是否通过审核
                if(empty($contractend) || $contractstart == $contractend){
                    //只传了开始值或者开始值等于结束值，对应一份合同编号
                    $contractContent[] = $contractcode.$contractstart;
                }else{
                    //多份合同的情况
                    $contractstart  = intval($contractstart);
                    $contractend    = intval($contractend);
                    if($contractstart > $contractend){
                        $this->ajaxReturn(array('status'=>0, 'info'=>'请输入正确的合同编号范围'));
                    }else{
                        for ($i=$contractstart; $i <= $contractend; $i++) {
                            $contractid = $contractcode . str_repeat('0', 7 - strlen($i)) . $i;
                            $contractContent[] = $contractid;
                        }
                    }
                }
            }
            if(!empty($ticketcode)){
                $ticketstart = str_replace($ticketcode, '', $ticketstart);
                $ticketend = str_replace($ticketcode, '', $ticketend);
                //生成合同编号，判断 合同编号是否存在 且 是否通过审核
                if(empty($ticketend) || $ticketstart == $ticketend){
                    //只传了开始值或者开始值等于结束值，对应一份合同编号

                    $ticketContent[] = $ticketcode.$ticketstart;
                }else{
                    //多份合同的情况
                    $ticketstart  = intval($ticketstart);
                    $ticketend    = intval($ticketend);
                    if($ticketstart > $ticketend){
                        $this->ajaxReturn(array('status'=>0, 'info'=>'请输入正确的合同编号范围'));
                    }else{
                        for ($i=$ticketstart; $i <= $ticketend; $i++) {
                            $ticketid = $ticketcode . str_repeat('0', 7 - strlen($i)) . $i;
                            $ticketContent[] = $ticketid;
                        }
                    }
                }
            }
            //合并数组
            $contractArr = array_merge($contractContent,$ticketContent);
            if(empty($contractArr)){
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的合同/票据编号有误，请确认！'));
            }
            //1，写历史记录表  contractid=合同/票据表的ID  status=2   sendout=当前用户ID  sendtime=time() signfor=销售ID time=time()
            //查询是否全部都在数据库中
            $result = D('Contractmanage')->checkContractExist($contractArr,$status);
            if(count($contractArr) == count($result)){
                //全部编号可用
                foreach ($contractArr as $k => $v) {
                    foreach ($result as $key => $value) {
                        if($v == $value['contractid']){
                            $saveAll[] = [
                                'main_id'=>$value['id'],
                                'status'=>$status,
                                'sendout'=>$_SESSION['uc_userinfo']['id'],
                                'sendtime'=>time(),
                                'express'=>$sendtype,
                                'expressid'=>$danhao,
                                'signfor'=>$userid,
                                'signtime'=>time(),
                                'time'=>time(),
                                'isreceived'=>1
                            ];
                            if($status == 3){
                                $saler_data[] = [
                                    'saler'=>$userid,
                                    'main_cid'=>$value['id'],
                                ];
                            }

                        }
                        $statusIds[] = $value['id'];
                    }
                }
                //1，批量写入操作记录
                $result = M('piaoju_manage_list')->addAll($saveAll);
                if($status == 3){
                    M('piaoju_saler_contract')->addAll($saler_data);
                }
                //2，如果记录写入成功，修改对应的 $statusIds(ID数组) 合同/票据内容status=3 或 6
                if($result){
                    $up = D('Contractmanage')->reFreshContract($statusIds,$status);
                    $this->ajaxReturn(array('status'=>1, 'info'=>'添加记录成功！'));
                }else{
                    $this->ajaxReturn(array('status'=>0, 'info'=>'添加失败，请重试！'));
                }
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的合同/票据编号有误，请确认！',));
            }
        }
    }
    /*
     * 合同/票据寄回公司
     */
    public function sendContractBack()
    {
        if($_POST){
            $ids = I('post.ids');
            $sendtype       = I('post.sendType');//快递方式
            $danhao = '';
            if($sendtype == 1){
                $danhao         = I('post.danhao');//快递单号
            }
            $status         = I('post.status');//寄出类别 助理寄出为3  外销寄出为6
            if(empty($ids)){
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的合同/票据编号有误，请确认！'));
            }
            //1，写历史记录表  contractid=合同/票据表的ID  status=2   sendout=当前用户ID  sendtime=time() signfor=销售ID time=time()
            //查询是否全部都在数据库中
            if(!empty($ids)){
                $contractArr = explode(',', $ids);
                $result = D('Contractmanage')->checkContractExist($ids,$status);
            }
            if(count($contractArr) == count($result)){
                //全部编号可用
                foreach ($contractArr as $k => $v) {
                    foreach ($result as $key => $value) {
                        if($v == $value['id']){
                            $saveAll[] = [
                                'main_id'=>$value['id'],
                                'status'=>$status,
                                'sendout'=>$_SESSION['uc_userinfo']['id'],
                                'sendtime'=>time(),
                                'express'=>$sendtype,
                                'expressid'=>$danhao,
                                'special'=>$value['special'],
                                'signfor'=>0,
                                'signtime'=>time(),
                                'time'=>time(),
                                'isreceived'=>1
                            ];
                        }
                        $statusIds[] = $value['id'];
                    }
                }
                //1，批量写入操作记录
                $result = M('piaoju_manage_list')->addAll($saveAll);
                //2，如果记录写入成功，修改对应的 $statusIds(ID数组) 合同/票据内容status=3 或 6
                if($result){
                    $up = D('Contractmanage')->reFreshContract($statusIds,$status);
                    $this->ajaxReturn(array('status'=>1, 'info'=>'添加记录成功！'));
                }else{
                    $this->ajaxReturn(array('status'=>0, 'info'=>'添加失败，请重试！'));
                }
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的合同/票据编号有误，请确认！','data'=>$result));
            }
        }
    }
    /*
     * 确认收到合同/票据
     */
    public function confirmGet()
    {
        if(!empty($_POST)){
            $ids = I('post.ids');//标记为收货
            $lost = I('post.lost');//标记为遗失
            $from = I('post.from');//from 3为销售待签  6为助理待签
            //确认收到的
            if(!empty($ids)){
                $result = D('Contractmanage')->receiveExpress($ids,$from);
                $msg = '签收成功！';
            }
            if(!empty($lost)){
                //有遗失
                $lost_result = D('Contractmanage')->checkLostExpress($lost,$from);
                $msg = '合同已返回在销售处！';
            }
            if($result || $lost_result){
                $this->ajaxReturn(array('status'=>1, 'info'=> $msg));
            }else{
                $this->ajaxReturn(array('status'=>0, 'data'=>$lost_result));
            }
        }else{
            $this->ajaxReturn(array('status'=>0, 'info'=>'请选择要签收的合同/票据！'));
        }
    }

    /*
     * 修改合同/票据  状态  status=3签约  special=2作废  special=3遗失  special=4申请签约（待助理审核）  special=5申请作废（待助理审核）
     */
    public function salerChangeStatus()
    {
        //根据传入的值  special=1签约  special=2作废  special=3遗失  special=4申请签约（待助理审核）  special=5申请作废（待助理审核） special=6申请遗失（待助理审核）
        //1,根据special修改qz_piaoju_contract表
        //2，special=1时qz_piaoju_manage_list添加记录
        if($_POST){
            $type = I('post.type');//1为合同   2为票据
            $special = I('post.special');//1签约 2作废 3遗失 special=4申请签约（待助理审核）  special=5申请作废（待助理审核）
            $from = I('post.from');//来源 1销售操作  2商务操作
            if($type == 1){
                $contractstart  = I('post.contractStart');//合同起始
                //生成合同编号，判断 合同编号是否存在 且 是否通过审核
                if(!empty($contractstart)){
                    //只传了开始值或者开始值等于结束值，对应一份合同编号
                    $contractIds[] = trim($contractstart);
                }

                if($from == 1){
                    $salerType = array('IN','3,4');
                }else{
                    $salerType = array('IN','1,2');
                }
            }elseif($type ==2){
                $ticketstart    = I('post.ticketStart');//票据开始
                //生成合同编号，判断 合同编号是否存在 且 是否通过审核
                if(!empty($ticketstart) || $ticketstart == $ticketend){
                    //只传了开始值或者开始值等于结束值，对应一份合同编号
                    $contractIds[] = $ticketcode.$ticketstart;
                }
                if($from == 1){
                    $salerType = array('EQ','6');
                }else{
                    $salerType = array('EQ','5');
                }
            }
            //检查合同/票据编号状态是否正常
            if($from == 1){
                if($special == 2){
                    $result = D('Contractmanage')->checkContractAllStatus($contractIds,$salerType);
                }elseif($special == 6){
                    $result = D('Contractmanage')->checkContractFindBack($contractIds,$salerType);
                }else{
                    $result = D('Contractmanage')->checkContractRightStatus($contractIds,$salerType);
                }
            }else{
                //商务操作
                $result = D('Contractmanage')->checkCommerceStatus($contractIds,$salerType);
            }

            if(count($contractIds) == count($result)){
                //全部编号可用
                if($special == 1){
                    foreach ($contractIds as $k => $v) {
                        foreach ($result as $key => $value) {
                            if($v == $value['contractid']){
                                $saveAll[] = [
                                    'main_id'=>$value['id'],
                                    'status'=>5,
                                    'sendout'=>$_SESSION['uc_userinfo']['id'],
                                    'sendtime'=>time(),
                                    'time'=>time()
                                ];
                            }
                            $statusIds[] = $value['id'];
                        }
                    }
                    //1，批量写入操作记录
                    $result = M('piaoju_manage_list')->addAll($saveAll);
                    //2，如果记录写入成功，修改对应的 $statusIds(ID数组) 合同/票据内容status=2 或 4
                    if($result){
                        $up = D('Contractmanage')->reFreshContract($statusIds,5);
                        $this->ajaxReturn(array('status'=>1, 'info'=>'修改状态成功！'));
                    }else{
                        $this->ajaxReturn(array('status'=>0, 'info'=>'操作失败，请重试！'));
                    }
                }elseif($special == 2){
                    //作废
                    $where['id'] = $result[0]['id'];
                    $data['special'] = $special;//2
                    M('piaoju_contract')->where($where)->save($data);
                    //作废操作，$result[0]['id'] 对应图片全部删除 再写入记录
                    $where_img_old['main_id'] = $result[0]['id'];
                    $data_img_old['status'] = 2;
                    M('piaoju_imgs')->where($where_img_old)->save($data_img_old);
                    if($type == 1){
                        $imgs = I('post.contractImage');
                    }elseif($type == 2){
                        $imgs = I('post.piaojuImage');
                    }
                    foreach ($imgs as $key => $value) {
                        $saveImg[] = array(
                            'main_id' => $result[0]['id'],
                            'imgurl' => $value,
                            'time' => time(),
                            'status' => 1
                        );
                    }
                    M('piaoju_imgs')->addAll($saveImg);
                    //作废状态添加记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = $result[0]['status'];
                    $list_data['special'] = $special;//2
                    $list_data['sendout'] = $_SESSION['uc_userinfo']['id'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    M('piaoju_manage_list')->add($list_data);

                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！','data'=>$saveImg));
                }elseif($special == 3){
                    //遗失piaoju_contract修改special
                    $where['id'] = $result[0]['id'];
                    $data['status'] = 4;//status=9为待审核状态
                    $data['special'] = $special;
                    $data['reason'] = I('post.reason');
                    M('piaoju_contract')->where($where)->save($data);

                    //遗失状态添加记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = 4;
                    $list_data['special'] = $special;//2
                    $list_data['sendout'] = $_SESSION['uc_userinfo']['id'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    M('piaoju_manage_list')->add($list_data);

                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！'));
                }elseif($special == 4 || $special == 5){
                    //  销售申请签约/作废（需要助理审核才能通过）
                    $where['id'] = $result[0]['id'];
                    $data['status'] = 9;//status=9为待审核状态
                    $data['special'] = $special;//将合同/票据改为待审核状态
                    $data['beaccept'] = 2;
                    M('piaoju_contract')->where($where)->save($data);

                    //销售申请签约/作废，添加操作记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = 9;
                    $list_data['special'] = $special;//4  5
                    $list_data['sendout'] = $_SESSION['uc_userinfo']['id'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    M('piaoju_manage_list')->add($list_data);

                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！'));
                }elseif($special == 6){
                    //  销售申请签约/作废为已找回状态
                    $where['id'] = $result[0]['id'];
                    $data['status'] = 4;//status=9为待审核状态
                    $data['special'] = 1;//将合同/票据改为初始待签约
                    $data['beaccept'] = 1;
                    M('piaoju_contract')->where($where)->save($data);

                    //销售申请签约/作废，添加操作记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = 4;
                    $list_data['special'] = 1;//4  5
                    $list_data['sendout'] = $_SESSION['uc_userinfo']['id'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    M('piaoju_manage_list')->add($list_data);

                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！'));
                }elseif($special == 7){
                    //销售申请遗失待审核 status=9 special=7 beaccept=2
                    $where['id'] = $result[0]['id'];
                    $data['status'] = 9;//status=9为待审核状态
                    $data['special'] = $special;//将合同/票据改为待审核状态
                    $data['beaccept'] = 2;
                    $data['reason'] = I('post.reason');
                    M('piaoju_contract')->where($where)->save($data);

                    //销售申请签约/作废，添加操作记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = 9;
                    $list_data['special'] = $special;//4  5
                    $list_data['sendout'] = $_SESSION['uc_userinfo']['id'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    M('piaoju_manage_list')->add($list_data);

                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！','data'=>""));
                }

            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的编号有误，请检查！','data'=>$result));
            }
        }
    }

    /*
     * 助理审核合同/票据  状态  status=5签约  special=2作废  special=3遗失  special=4申请签约（待助理审核）  special=5申请作废（待助理审核）
     */
    public function assistantCheck()
    {
        //根据传入的值  special=1签约  special=2作废  special=3遗失  special=4申请签约（待助理审核）  special=5申请作废（待助理审核）
        //1,根据special修改qz_piaoju_contract表
        //2，special=1时qz_piaoju_manage_list添加记录
        if($_POST){
            $type = I('post.type');//1为合同   2为票据
            $special = I('post.special');//1签约 2作废 3遗失 special=4申请签约（待助理审核）  special=5申请作废（待助理审核）
            $from = I('post.from');//来源 1销售操作  2商务操作
            $new_status = I('post.status');
            $new_beaccpet = I('post.beaccpet');
            if($type == 1){
                $contractstart  = I('post.contractStart');//合同起始
                //生成合同编号，判断 合同编号是否存在 且 是否通过审核
                if(!empty($contractstart)){
                    //只传了开始值或者开始值等于结束值，对应一份合同编号
                    $contractIds[] = trim($contractstart);
                }

                if($from == 1){
                    $salerType = array('IN','3,4');
                }else{
                    $salerType = array('IN','1,2');
                }
            }elseif($type ==2){
                $ticketstart    = I('post.ticketStart');//票据开始
                //生成合同编号，判断 合同编号是否存在 且 是否通过审核
                if(!empty($ticketstart) || $ticketstart == $ticketend){
                    //只传了开始值或者开始值等于结束值，对应一份合同编号
                    $contractIds[] = $ticketcode.$ticketstart;
                }
                if($from == 1){
                    $salerType = array('EQ','6');
                }else{
                    $salerType = array('EQ','5');
                }
            }
            //检查合同/票据编号状态是否正常
            if($from == 1){
                if($special == 2){
                    $result = D('Contractmanage')->checkContractAllStatus($contractIds,$salerType);
                }elseif($special == 6){
                    $result = D('Contractmanage')->checkContractFindBack($contractIds,$salerType);
                }else{
                    $result = D('Contractmanage')->checkContractRightStatus($contractIds,$salerType);
                }
            }else{
                //商务操作
                $result = D('Contractmanage')->checkCommerceStatus($contractIds,$salerType);
            }

            if(count($contractIds) == count($result)){
                //全部编号可用
                if($special == 1){
                    foreach ($contractIds as $k => $v) {
                        foreach ($result as $key => $value) {
                            if($v == $value['contractid']){
                                //查询这条合同的申请人
                                $last_list = D('Contractmanage')->findContractLast($value['id']);
                                $saveAll[] = [
                                    'main_id'=>$value['id'],
                                    'status'=>$new_status,
                                    'special'=>$special,
                                    'sendout'=>$last_list['sendout'],
                                    'sendtime'=>time(),
                                    'time'=>time(),
                                    'assistant'=>$_SESSION['uc_userinfo']['id']
                                ];

                                //2018-3-30合同审核后更新为归档
                                //写入新的状态码记录 status=8
                                $guidang[] = [
                                    'main_id'=>$value['id'],
                                    'status'=>8,
                                    'special'=>$special,
                                    'sendout'=>$_SESSION['uc_userinfo']['id'],
                                    'sendtime'=>time(),
                                    'time'=>time()
                                ];

                            }
                            $statusIds[] = $value['id'];
                        }
                    }
                    //1，批量写入操作记录
                    $result = M('piaoju_manage_list')->addAll($saveAll);
                    $result = M('piaoju_manage_list')->addAll($guidang);//更新状态为归档
                    //2，如果记录写入成功，修改对应的 $statusIds(ID数组) 合同/票据内容status=2 或 4
                    if($result){
                        $up = D('Contractmanage')->assisantFreshContract($statusIds,$new_status,$special,$new_beaccpet);
                        //助理审核通过，更改状态为归档 status=8
                        $result = D('Contractmanage')->checkLastStatus($statusIds);
                        $this->ajaxReturn(array('status'=>1, 'info'=>'修改状态成功！'));
                    }else{
                        $this->ajaxReturn(array('status'=>0, 'info'=>'操作失败，请重试！','data'=>$saveAll));
                    }
                }elseif($special == 2){
                    //作废
                    $where['id'] = $result[0]['id'];
                    $data['status'] = 7;//2
                    $data['special'] = 2;//2
                    $data['beaccept'] = $new_beaccpet;

                    M('piaoju_contract')->where($where)->save($data);
                    //作废状态添加记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = 7;
                    $list_data['special'] = 2;//2
                    //查询这条合同的申请人
                    $last_list = D('Contractmanage')->findContractLast($result[0]['id']);
                    $list_data['sendout'] = $last_list['sendout'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    $list_data['assistant'] = $_SESSION['uc_userinfo']['id'];

                    M('piaoju_manage_list')->add($list_data);

                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！','data'=>$saveImg));
                }elseif($special == 3){
                    //遗失
                    $where['id'] = $result[0]['id'];
                    $data['status'] = $new_status;//4
                    $data['special'] = $special;//3
                    $data['beaccept'] = $new_beaccpet;//5遗失审核通过
                    M('piaoju_contract')->where($where)->save($data);

                    //作废状态添加记录
                    $list_data['main_id'] = $result[0]['id'];
                    $list_data['status'] = $new_status;
                    $list_data['special'] = $special;//3  遗失
                    //查询这条合同的申请人
                    $last_list = D('Contractmanage')->findContractLast($result[0]['id']);
                    $list_data['sendout'] = $last_list['sendout'];//操作人ID
                    $list_data['sendtime'] = time();
                    $list_data['time'] = time();
                    $list_data['assistant'] = $_SESSION['uc_userinfo']['id'];
                    M('piaoju_manage_list')->add($list_data);


                    $this->ajaxReturn(array('status'=>1, 'info'=>'操作成功！','data'=>$list_data['main_id']));
                }

            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的编号有误，请检查！','data'=>""));
            }
        }
    }


    //进度查询
    public function getContratProgress($value='')
    {
        if(!empty($_POST)){
            $code = I('post.code');
            if(strlen($code) != 7){
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的合同编号不正确！'));
            }
            //查询
            $result = D('Contractmanage')->getContratProgress($code);

            if(!empty($result)){
                $this->ajaxReturn(array('status'=>1, 'info'=>'查询成功','data'=>$result));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'当前查询的编号不存在！'));
            }
        }
    }

    /*
    * 根据输入内容，查找adminuser
    *
    */
    public function findUsers()
    {
        if ($_POST) {
            $query = I("post.query");
            $result = D("Contractmanage")->findUsers($query);
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));
        }
    }

    /*********************************商务部分！！！***********************************/





    public function heTongpjjzwx(){
        $this->display();
    }
    public function heTongpjjzsw(){
        $this->display();
    }


    /**
     * 商务销售页面
     */
    public function commerceSaler(){

        if (IS_POST) {
            $operate = I('post.operate');
            if ("1" == $operate) {
                // if (!in_array($type, array('1', '2'))) {
                //     $this->ajaxReturn(array('status'=>0, 'info'=>'请选择正确的合同类型'));
                // }
                $sign_city_name = I('post.sign_city_name');
                if (empty($sign_city_name)) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'请填写签约城市'));
                }
                $sign_vip_name = I('post.sign_vip_name');
                if (empty($sign_vip_name)) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'请填写签约会员'));
                }

                $map = array(
                    //'type' => $type,
                    'isused' => '1',
                    'isdel' => '1',
                    'isexamine' => '2',
                    'type' => array("IN",array(1,2))
                );

                $contract = M('piaoju_contract')->where($map)->order('id ASC')->find();

                if (empty($contract)) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'没有可用合同可获取'));
                }

                $map = array('id' => $contract['id']);
                $result = M('piaoju_contract')->where($map)->save(array('isused' => '2', 'status' => '4'));
                if ($result) {
                    $save = array(
                        'saler' => getAdminUser('id'),
                        'main_cid' => $contract['id'],
                        'sign_city_name' => $sign_city_name,
                        'sign_vip_name' => $sign_vip_name
                    );
                    M('piaoju_saler_contract')->add($save);

                    //插入记录表
                    $listInfo = array(
                        'main_id' => $contract['id'],
                        'status' => '4',
                        'time' => time(),
                        'sendout' => $_SESSION['uc_userinfo']['id'],
                        'sendtime' => time()
                    );
                    M('piaoju_manage_list')->add($listInfo);

                    //获取成功
                    $this->ajaxReturn(array('status'=>1, 'data' => $contract));
                }
                $this->ajaxReturn(array('status'=>0, 'info'=>'合同获取失败，请重试'));
            }

            if ("2" == $operate) {
                $id = I('post.id');
                $map = array(
                    's.id' => $id
                );
                $info = M('piaoju_saler_contract')->alias('s')
                                                  ->field('s.*,c.status,c.special,i.id AS image')
                                                  ->join('qz_piaoju_contract AS c ON c.id = s.main_cid')
                                                  ->join('LEFT JOIN qz_piaoju_imgs AS i ON i.main_id = s.main_cid')
                                                  ->where($map)
                                                  ->group('s.id')
                                                  ->find();
                if (empty($info)) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'合同获取失败，请刷新重试'));
                }
                if ($info['saler'] != getAdminUser('id')) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'操作的合同编号不属于当前登录用户'));
                }
                if (!empty($info['main_vid'])) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'该合同已绑定票据，不可撤销'));
                }
                if (!empty($info['image'])) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'该合同已上传过图片，不可撤销'));
                }
                if ("4" != $info['status']) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'该合同已使用，不可撤销'));
                }
                if ("1" != $info['special']) {
                    $this->ajaxReturn(array('status'=>0, 'info'=>'该合同已作废或遗失，不可撤销'));
                }
                //修改合同状态
                M("piaoju_contract")->where(array('id' => $info['main_cid']))->save(array('isused' => '1','status'=>'2'));
                //删除记录表
                M('piaoju_manage_list')->where(array('id' => $info['main_cid'], 'status' => '4'))->delete();
                //删除图片
                M("piaoju_imgs")->where(array('main_id' => $info['main_cid']))->save(array('status' => 2));
                //删除合同销售绑定
                M("piaoju_saler_contract")->where(array('id' => $info['id']))->delete();
                $this->ajaxReturn(array('status'=>1, 'info'=>'撤销成功！'));
            }
        }

        $map = array(
            's.saler' => getAdminUser('id'),
            'c.status' => '4',
            'c.special' => '1',
            'i.id' => array('EXP', 'IS NUll'),
            's.main_vid' => '0',
            'c.type' => array('IN', array('1', '2'))
        );
        $vars['list'] = M('piaoju_saler_contract')->alias('s')
                                                  ->field('s.id, c.contractid, s.sign_city_name, s.sign_vip_name')
                                                  ->join('qz_piaoju_contract AS c ON c.id = s.main_cid')
                                                  ->join('LEFT JOIN qz_piaoju_imgs AS i ON i.main_id = s.main_cid')
                                                  ->where($map)
                                                  ->group('c.id')
                                                  ->select();

        //获取上传Token
        $auth = new Auth(OP('QINIU_AK'), OP('QINIU_CK'));
        $policy = array(
            'returnBody' => '{"name":$(key)}',
            'saveKey' => 'commerceenclosure/$(year)$(mon)$(day)$(hour)$(min)$(sec)/$(fname)'
        );
        $vars['token'] = $auth->uploadToken(OP('QINIU_PRIVATE_BUCKET'), null, 3600, $policy);

        //2018-3-8修改
        //查询待签约列表  status=4 special=1 sendout=UID
        $beSignedUp = D('Contractmanage')->getBeSignedUpList(4,1);
        //查询签约待审核状态列表 status=9 special=4 sendout=UID
        $signedForVerify = D('Contractmanage')->getBeSignedUpList(9,4);
        //查询作废待审核状态列表 status=9 special=5 sendout=UID
        $beCancellation = D('Contractmanage')->getBeSignedUpList(9,5);
        //查询遗失状态列表 status=4 special=3 sendout=UID
        $beLost = D('Contractmanage')->getBeSignedUpList(4,3);
        //审核通过列表
        $hasChecked = D('Contractmanage')->getHasCheckedList();
        $this->assign('besignedup',$beSignedUp);//待签约列表
        $this->assign('signedforverify',$signedForVerify);//签约待审核列表
        $this->assign('becancellation',$beCancellation);//作废待审核列表
        $this->assign('belost',$beLost);//遗失列表
        $this->assign('haschecked',$hasChecked);//已审核列表

        //模板赋值
        $this->assign('vars', $vars);
        $this->assign('from',2);
        $this->assign('bumen',1);
        $this->display();
    }

    /**
     * 商务助理页面
     */
    public function commerceAssistant(){
        //查询待审核的合同  status=9 speciel=4签约待审核 | status=9 speciel=5作废待审核
        $qianyue_list = D('Contractmanage')->getExaminedContract(9,4);
        $zuofei_list = D('Contractmanage')->getExaminedContract(9,5);
        $yishi_list = D('Contractmanage')->getExaminedContract(9,7);

        //查询助理审核过的记录
        $hasChecked = D('Contractmanage')->getAssistantCheckedList();
        $this->assign('isshow',1);
        $this->assign('haschecked',$hasChecked);
        $this->assign('yishilist',$yishi_list);
        $this->assign('qianyuelist',$qianyue_list);
        $this->assign('zuofeilist',$zuofei_list);
        //$this->assign('bumen',2);
        $this->assign('from',2);
        $this->display();
    }

    /**
     * 商务军长页面
     */
    public function commerceExecutive(){
        $this->assign('bumen',1);
        $this->display();
    }

    /**
     * 入库申请
     */
    public function stockApply(){
        if (IS_POST) {
            //判断是商务助理还是外销助理，商务助理45 外销助理 67
            $admin = getAdminUser();
            if (!in_array($admin['uid'], array(45, 67))) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'只有外销助理和商务助理可添加合同票据'));
            }
            //判断是合同还是票据
            $category    = intval(I('post.category'));
            $startNumber = intval(trim(I('post.start')));
            $endNumber   = intval(trim(I('post.end')));

            if (empty($startNumber) || empty($endNumber)) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'开始编号数字和结束编号数字不能为空！'));
            }

            //商务助理
            if (45 == $admin['uid']) {
                //合同
                if (1 == $category) {
                    //商务制式
                    $type = '1';
                //票据
                } else {
                    //商务制式
                    $type = '5';
                }
            //外销助理
            } else if (67 == $admin['uid']) {
                //合同
                if (1 == $category) {
                    //外销制式
                    $type = '3';
                //票据
                } else {
                    //外销制式
                    $type = '6';
                }
            }

            //转为整数
            if ($startNumber > $endNumber) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'开始编号必须小于结束编号'));
            }
            if ($endNumber > 9999999) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'结束编号必须小于7位数'));
            }
            if ($endNumber - $startNumber > 500) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'入库申请一次性只能添加不超过500条'));
            }

            //合同票据分类
            $typeArray = $this->typeArray;

            //添加申请批次
            $code = $typeArray[$type]['code'] . str_repeat('0', 7 - strlen($startNumber)) . $startNumber . '-' . $typeArray[$type]['code'] . str_repeat('0', 7 - strlen($endNumber)) . $endNumber;


            //添加合同票据
            $save = $contractIdArray = array();
            for ($i=$startNumber; $i <= $endNumber; $i++) {
                $contractId = $typeArray[$type]['code'] . str_repeat('0', 7 - strlen($i)) . $i;
                $contractIdArray[] = $contractId;
                $save[] = array(
                    'type' => $type,
                    'contractid' => $contractId,
                    'status' => 1,
                    'special' => 1,
                    'batch' => $batch,
                    'isused' => 1,
                    'isdel' => 1,
                    'isexamine' => 1
                );
                $contractid_Arr[] = $contractId;
            }
            if (empty($contractIdArray)) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'输入的编号范围为空'));
            }

            //添加前判断是否已存在
            $count = M('piaoju_contract')->where(array('type'=>$type, 'contractid'=>array('IN', $contractIdArray)))->count();
            if ($count > 0) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'提交失败！' . $count . '条数据重复，请检查！'));
            }
            //添加
            $result = M('piaoju_contract')->addAll($save);
            //如果添加失败，则删除申请批次
            if (!$result) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'提交失败！请检查编码是否正确'));
            }else{
                $save_branch = array(
                    'code' => $code,
                    'type' => $type,
                    'examineid' => getAdminUser('id'),
                    'isexamine' => 1,
                    'time' => time()
                );
                $batch = M('piaoju_batch')->add($save_branch);
                //将本次添加的合同/票据更新批次ID
                $new_batch_data['batch'] = $batch;
                $new_batch = M('piaoju_contract')->where(array('contractid'=>array('IN',$contractid_Arr)))->save($new_batch_data);
            }
            //查询出添加的合同票据，添加入库记录
            $temp = M('piaoju_contract')->field('id')->where(array('type'=>$type, 'contractid'=>array('IN', $contractIdArray)))->select();
            $listInfo = array();
            foreach ($temp as $key => $value) {
                $listInfo[] = array(
                    'main_id' => $value['id'],
                    'status' => '1',
                    'time' => time()
                );
            }
            if (!empty($listInfo)) {
                M('piaoju_manage_list')->addAll($listInfo);
            }
            $this->ajaxReturn(array('status'=>1, 'info'=>'提交成功', 'data' => $batch));
        }
        $this->error();
    }

    /**
     * 入库申请记录
     */
    public function stockApplyRecord()
    {
        $examineid = $_SESSION['uc_userinfo']['id'];

        $limit = intval(I('get.limit'));
        if (empty($limit)) {
            $limit = 100;
        }
        $limit = ($limit > 0) ? $limit : 3;

        $count = M('piaoju_batch')->alias('b')
                                  ->field('b.*, a.name AS adminuser_name')
                                  ->join('qz_adminuser AS a ON a.id = b.examineid')
                                  ->where(array('b.examineid' => $examineid))
                                  ->count();

        $result = M('piaoju_batch')->alias('b')
                                   ->field('b.*, a.name AS adminuser_name')
                                   ->join('qz_adminuser AS a ON a.id = b.examineid')
                                   ->where(array('b.examineid' => $examineid))
                                   ->order('b.isexamine, id DESC')
                                   ->limit($limit)
                                   ->select();

        $html = '';
        foreach ($result as $key => $value) {
            $html = $html  . '<tr><td class="text-align-left">'.$value['code'].'</td><td>'.$value['adminuser_name'].'</td><td>'.($value['isexamine'] == 1 ? '<span class="no_start">未审核</span>' : '<span class="has_end">已审核</span>').'</td>
                    <td>'.date('Y-m-d', $value['time']).'</td></tr>';
        }

        $this->ajaxReturn(array('status'=>1, 'data'=>array('count' => $count, 'html' => $html, 'id'=>$examineid)));
    }

    /**
     * 库存删除
     */
    public function stockDelete()
    {
        //判断是合同还是票据，1：合同 2：票据
        $category = intval(trim(I('post.category')));
        $uid = getAdminUser('uid');
        if ('39' == $uid) {
            //商务经理
            $type = ($category == 1) ? '1' : '5';
        } else if ('59' == $uid) {
            //外销经理
            $type = ($category == 1) ? '3' : '6';
        } else {
            $this->ajaxReturn(array('status'=>0, 'info'=>'只有商务经理和外销经理才可以删除库存！'));
        }
        $startCode = strtoupper(trim(I('post.startCode')));
        $endCode = strtoupper(trim(I('post.endCode')));
        $explicitCode = strtoupper(trim(I('post.explicitCode')));
        $code = array();
        if (!empty($explicitCode)) {
            $code[] = $explicitCode;
            $successInfo = '删除单个成功~';
        } else {
            if (empty($startCode) || empty($endCode)) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'请填写完整编号区间或编号！'));
            }
            //判断输入长度
            if ((mb_strlen($endCode,'utf-8') < 8) || (mb_strlen($endCode,'utf-8') < 8)) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'编号区间输入错误！'));
            }
            //判断编号前缀是否一致
            $startPrefix = mb_substr($startCode,0,(mb_strlen($startCode,'utf-8') - 7),'utf-8');
            $endPrefix = mb_substr($endCode,0,(mb_strlen($endCode,'utf-8') - 7),'utf-8');
            if ($startPrefix != $endPrefix) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'编号区间前缀输入不一致！'));
            }
            $prefix = $startPrefix;
            $start = intval(mb_substr($startCode,-7,7,'utf-8'));
            $end = intval(mb_substr($endCode,-7,7,'utf-8'));

            //判断结束是否大于开始
            if ($start > $end) {
                $this->ajaxReturn(array('status'=>0, 'info'=>'开始编号必须小于结束编号！'));
            }

            for ($i=$start; $i <= $end; $i++) {
                $code[] = $prefix . str_repeat('0', 7 - strlen($i)) . $i;
            }
            $successInfo = '删除区间成功~';
        }

        if (empty($code)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'编号输入错误！'));
        }

        //删除编号
        M('piaoju_contract')->where(array('type' => $type, 'contractid' => array('IN', $code)))->delete();
        $this->ajaxReturn(array('status'=>1, 'info'=>$successInfo));
    }

    /**
     * 入库审批
     */
    public function stockApproval()
    {
        $isexamine = I('post.isexamine');
        $ids = I('post.ids');
        if (!in_array($isexamine, array('2', '3'))) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'非法请求'));
        }
        if (empty($ids)) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'请选择入库审批编号'));
        }

        //修改条件，主要角色限制，暂未填写
        $map = array(
            'id' => array('IN', $ids),
            'isexamine' => array('EQ', 1)
        );

        //此处根据商务外销角色查找,商务经理处理商务的,外销经理处理外销的
        $uid = getAdminUser('uid');
        if ('39' == $uid) {
            //商务经理
            $map['type'] = array('IN', array('1', '2', '5'));
        } else if ('59' == $uid) {
            //外销经理
            $map['type'] = array('IN', array('3', '4', '6'));
        } else if ('1' != $uid) {
            //非管理员
            $map['id'] = array('LT', 0);
        }
        //$batch_data['examineid'] = $_SESSION['uc_userinfo']['id'];
        $batch_data['isexamine'] = 2;
        $batch_data['time'] = time();
        //1,修改批次表审核状态
        $result = M('piaoju_batch')->where($map)->save($batch_data);
        // //2,修改要审核的合同、票据
        $contract_map['batch'] = $map['id'];
        if($map['type']){
            $contract_map['type'] = $map['type'];
        }
        $contract_map['isexamine'] = $map['isexamine'];
        $contract_Arr = M('piaoju_contract')->where($contract_map)->field('id,contractid')->select();
        $contract_data['isexamine'] = 2;
        $contract_data['status'] = 2;
        $contract_data['examine_time'] = time();
        $contract_result = M('piaoju_contract')->where($contract_map)->save($contract_data);
        if($contract_result){
            foreach ($contract_Arr as $key => $value) {
                //记录表
                $listInfo[] = array(
                    'main_id' => $value['id'],
                    'status' => 2,
                    'time' => time()
                );
            }
            //增加记录表
            M('piaoju_manage_list')->addAll($listInfo);
        }

        // $result = M('piaoju_contract')->field('id, batch')->where($map)->select();
        // if (empty($result)) {
        //     $this->ajaxReturn(array('status'=>0, 'info'=>'您输入的编号已被审核，请刷新'));
        // }
        // $save = $listInfo = array();
        // foreach ($result as $key => $value) {
        //     $save[$value['batch']][] = $value['id'];
        //     //记录表
        //     $listInfo[] = array(
        //         'main_id' => $value['id'],
        //         'status' => 2,
        //         'time' => time()
        //     );
        // }

        // //分批次审核
        // foreach ($save as $key => $ids) {
        //     $map = array(
        //         'id' => array('IN', $ids),
        //         'isexamine' => array('EQ', 1)
        //     );
        //     M('piaoju_contract')->where($map)->save(array('isexamine' => $isexamine, 'status' => '2'));
        //     M('piaoju_batch')->where(array('id' => $key))->save(array('isexamine' => 2));
        // }


        $this->ajaxReturn(array('status'=>1, 'info'=>'入库审批操作成功','map'=>$contract_Arr));
    }

    /**
     * 获取等待审核合同与票据列表
     * @return [type] [description]
     */
    public function getWaitApprovalPiaojuContractList()
    {
        //查询条件
        $map = array(
            'isexamine' => 1
        );
        //此处根据商务外销角色查找,商务经理看商务的,外销经理看外销的
        $uid = getAdminUser('uid');
        if ('39' == $uid) {
            //商务经理
            $map['type'] = array('IN', array('1', '2', '5'));
        } else if ('59' == $uid) {
            //外销经理
            $map['type'] = array('IN', array('3', '4', '6'));
        } else if ('1' != $uid) {
            //非管理员
            $map['id'] = array('LT', 0);
        }
        //$result = M('piaoju_contract')->where($map)->select();
        //查询部门下属的待审核合同/票据
        $batch = M('piaoju_batch')->where($map)->select();
        $contractHtml = $piaojuHtml = '';
        $contractCount = $piaojuCount = 0;
        foreach ($batch as $key => $value) {
            if (in_array($value['type'], array('1', '2', '3', '4'))) {
                $contractHtml = $contractHtml . '<option value='.$value['id'].'>'.$value['code'].'</option>';
                $contractCount++;
            } else {
                $piaojuHtml = $piaojuHtml . '<option value='.$value['id'].'>'.$value['code'].'</option>';
                $piaojuCount++;
            }
        }
        $data = array(
            'contractHtml'=>$contractHtml,
            'piaojuHtml'=>$piaojuHtml,
            'contractCount'=>$contractCount,
            'piaojuCount'=>$piaojuCount,
        );
        return $this->ajaxReturn(array("data"=>$data,"status"=>1));
    }

    /**
     * 票据合同API
     */
    public function piaojuContractApi()
    {
        if (IS_POST) {
            $uid = getAdminUser('uid');
            $category = I('post.category');
            if ('39' == $uid) {
                //商务经理
                $type = ($category == 1) ? '1' : '5';
            } else if ('59' == $uid) {
                //外销经理
                $type = ($category == 1) ? '3' : '6';
            } else {
                $this->ajaxReturn(array('data' => array(), 'status' => 1));
            }
            $query = I("post.query");
            $result = M('piaoju_contract')->where(array('type' => $type, 'contractid' => array('LIKE', '%' . $query . '%')))
                                          ->limit(10)
                                          ->select();
            return $this->ajaxReturn(array("data"=>$result,"status"=>1));
        }
        $this->error();
    }

    /**
     * 合同票据文件上传
     */
    public function uploadDocument()
    {

        $admin = getAdminUser();
        //获取合同，票据类型
        //商务部
        if ('6' == $admin['department_id']) {
            $contractType = '1';
            $piaojuType = '5';
        //外销部
        } else if ('5' == $admin['department_id']) {
            $contractType = '3';
            $piaojuType = '6';
        } else {
            $this->ajaxReturn(array("status"=>0, "info"=>'只有商务部或外销部可进行此操作'));
        }
        //获取合同
        $contractCode = intval(I('post.contractCode'));
        if (empty($contractCode)) {
            return $this->ajaxReturn(array("status"=>0, "info"=>"您输入的合同编号或类型为空"));
        }
        $contractCode = 'QZ' . str_repeat('0', 7 - strlen($contractCode)) . $contractCode;

        $map = array(
            'type' => $contractType,
            'contractid' => $contractCode
        );

        $contract = M('piaoju_contract')->where($map)->find();
        if (empty($contract)) {
            return $this->ajaxReturn(array("status"=>0, "info"=>"查询不到您输入的合同"));
        }
        $mainCid = $contract['id'];

        //获取票据
        $piaojuCode = intval(I('post.piaojuCode'));
        if (!empty($piaojuCode)) {
            $piaojuCode = 'SJYWT' . str_repeat('0', 7 - strlen($piaojuCode)) . $piaojuCode;
            $map = array(
                'type' => $piaojuType,
                'contractid' => $piaojuCode
            );
            $piaoju = M('piaoju_contract')->where($map)->find();
            if (empty($piaoju)) {
                return $this->ajaxReturn(array("status"=>0, "info"=>"查询不到您输入的票据"));
            }
            $mainVid = $piaoju['id'];
        }

        $contractImage = I('post.contractImage');
        $piaojuImage = I('post.piaojuImage');
        //验证该合同和票据是否输入同一个记录
        $map = array(
            'main_cid' => $mainCid
        );
        $info = M('piaoju_saler_contract')->where($map)->find();
        if (empty($info)) {
            return $this->ajaxReturn(array("status"=>0, "info"=>"该合同未被获取"));
        }
        //判断该合同票据是否是当前用户
        if ($info['saler'] != $admin['id']) {
            return $this->ajaxReturn(array("status"=>0, "info"=>"只有该合同或票据的归属人才有操作权限"));
        }

        //判断票据是否被别的合同使用了
        if (!empty($mainVid)) {
            $map = array(
                'main_vid' => $mainVid,
                'main_cid' => array('NEQ', $mainCid)
            );
            $temp = M('piaoju_saler_contract')->where($map)->find();
            if (!empty($temp)) {
                return $this->ajaxReturn(array("status"=>0, "info"=>"该票据已被别的合同使用"));
            }
        }

        //删除原有的合同票据图片
        if (!empty($info['main_vid'])) {
            $map = array(
                'main_id' => array('IN', array($info['main_cid'], $info['main_vid']))
            );
        } else {
            $map = array(
                'main_id' => $info['main_cid']
            );
        }
        M('piaoju_imgs')->where($map)->save(array('status' => 2));

        //插入新图片
        $save = array();
        foreach ($contractImage as $key => $value) {
            $save[] = array(
                'main_id' => $mainCid,
                'imgurl' => $value,
                'time' => time(),
                'status' => 1
            );
        }
        if (!empty($mainVid)) {
            foreach ($piaojuImage as $key => $value) {
                $save[] = array(
                    'main_id' => $mainVid,
                    'imgurl' => $value,
                    'time' => time(),
                    'status' => 1
                );
            }
        }
        M('piaoju_imgs')->addAll($save);

        //绑定合同票据
        if (!empty($mainVid)) {
            $save = array(
                'main_vid' => intval($mainVid)
            );
            M('piaoju_saler_contract')->where(array('main_cid' => $info['main_cid']))->save($save);
        }

        return $this->ajaxReturn(array("status"=>1, "info"=>"操作成功"));
    }

    public function uploadDocumentSearch()
    {
        $contractId = intval(I('get.contractId'));

        $admin = getAdminUser();
        //商务部
        if ('6' == $admin['department_id']) {
            $contractType = '1';
            $prefix = 'QZ';
        //外销部
        } else if ('5' == $admin['department_id']) {
            $contractType = '3';
            $prefix = 'QZ';
        } else {
            $this->ajaxReturn(array("status"=>0, "info"=>'只有商务部或外销部可进行此操作'));
        }

        if (empty($contractId)) {
            return $this->ajaxReturn(array("status"=>0, "info"=>"您填写的合同编号为空"));
        }

        $contractId = $prefix . str_repeat('0', 7 - strlen($contractId)) . $contractId;

        $map = array(
            'c.contractid' => $contractId,
            'c.type' => $contractType,
            's.saler' => $admin['id']
        );

        $result = M('piaoju_contract')->alias('c')
                                      ->where($map)
                                      ->field('c.id AS contract_id, c.contractid AS contract_code, c.type AS contract_type, z.id AS voucher_id, z.contractid AS voucher_code, z.type AS voucher_type')
                                      ->join('INNER JOIN qz_piaoju_saler_contract AS s ON s.main_cid = c.id')
                                      ->join('LEFT JOIN qz_piaoju_contract AS z ON z.id = s.main_vid')
                                      ->find();

        if (empty($result)) {
            return $this->ajaxReturn(array("status"=>0, "info"=>"您输入的编号未查询到相关记录"));
        }
        $data = array(
            'contract_id'   => $result['contract_id'],
            'contract_type' => $result['contract_type'],
            'contract_code' => $result['contract_code'],
            'voucher_id'    => $result['voucher_id'],
            'voucher_type'  => $result['voucher_type'],
            'voucher_code'  => $result['voucher_code'],
            'image'         => array()
        );

        //搜索图片
        if (empty($result['voucher_code'])) {
            $map = array(
                'main_id' => $result['contract_id']
            );
        } else {
            $map = array(
                'main_id' => array('IN', array($result['contract_id'], $result['voucher_id']))
            );
        }
        $map['status'] = 1;
        $temp = M('piaoju_imgs')->where($map)->select();
        if (!empty($temp)) {
            //生成带有有效期的下载链接
            $auth = new Auth(OP('QINIU_AK'), OP('QINIU_CK'));
            foreach ($temp as $key => $value) {
                $src = 'http://' . C('QINIU_PRIVATE_DOMAIN') . '/' . $value['imgurl'];
                $src = $auth->privateDownloadUrl($src);
                $temp[$key]['src'] = $src;
            }
            $data['image'] = $temp;
        }

        return $this->ajaxReturn(array("status"=>1, "data"=>$data));
    }

    /**
     * 查询票据/合同上传的图片
     * @return [type] [description]
     */
    public function findcontractimg()
    {
        if ($_POST) {
            $id = I("post.id");
            $list = D("Contractmanage")->findContractImg($id);

            if (count($list) > 0) {
               //生成带有有效期的下载链接
                $auth = new Auth(OP('QINIU_AK'), OP('QINIU_CK'));
                foreach ($list as $key => $value) {
                    $url = 'http://' . C('QINIU_PRIVATE_DOMAIN') . '/' . $value['imgurl'];
                    $url = $auth->privateDownloadUrl($url);
                    $list[$key]["imgurl"] = $url;
                }

                $this->assign("list",$list);
                $tmp = $this->fetch("contractimgtpl");
            }
            return $this->ajaxReturn(array("status"=>1, "data"=>$tmp));
        }
    }


    public function findcontracthistory()
    {
        if ($_POST) {
            $id = I("post.id");
            $info = D("Contractmanage")->findcontracthistory($id);

            if (count($info) > 0) {
                $this->assign("info",$info[0]);
                $tmp = $this->fetch("contracthistorytpl");
            }
            return $this->ajaxReturn(array("status"=>1, "data"=>$tmp));
        }
    }
}