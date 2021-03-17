<?php
/**
 *  个人电话
 */


namespace Home\Controller;

import('Library.Org.ChinaunicomCloudTel.ChinaunicomCloudTel',"",".php");

use Home\Common\Controller\HomeBaseController;

use Cuct\ChinaunicomCloudTel;


class MytelController extends HomeBaseController
{
    //构造
    public function _initialize(){


        // 联通云总机
        // 本代码耦合冗余,但是也暂不做重构
        //主帐号
        $accountSid = OP('CUCT_ACCOUNTSID');
        //主帐号Token
        $accountToken = OP('CUCT_ACCOUNTTOKEN');
        //应用Id
        $appId = OP('CUCT_APPID_QZ');
        //请求地址
        $serverIP = OP('CUCT_SERVERIP');
        //请求端口
        $serverPort = OP('CUCT_SERVERPORT');
        //REST版本号
        $softVersion = OP('CUCT_SOFTVERSION');
        //子帐号
        $subAccountSid = OP('CUCT_SUBACCOUNTSID');
        //子帐号Token
        $subAccountToken =  OP('CUCT_SUBACCOUNTTOKEN');
        //VoIP帐号
        $voIPAccount = $userInfo['voipaccount'];
        //VoIP密码
        $voIPPassword = $userInfo['voippwd'];

        //如果是开发环境 把请求的服务器地址换成测试地址
        $APP_ENV = C('APP_ENV');
        if ($APP_ENV =='dev') {
            //主帐号
            $accountSid = OP('CUCT_ACCOUNTSID_DEV');
            //主帐号Token
            $accountToken = OP('CUCT_ACCOUNTTOKEN_DEV');
            //应用Id
            $appId = OP('CUCT_APPID_QZ_DEV');
            //请求地址
            $serverIP = OP('CUCT_SERVERIP_DEV');
            //请求端口
            $serverPort = OP('CUCT_SERVERPORT_DEV');
            //REST版本号
            $softVersion = OP('CUCT_SOFTVERSION_DEV');
            //子帐号
            $subAccountSid = OP('CUCT_SUBACCOUNTSID_DEV');
            //子帐号Token
            $subAccountToken =  OP('CUCT_SUBACCOUNTTOKEN_DEV');
            //VoIP帐号
            $voIPAccount = $userInfo['voipaccount'];
            //VoIP密码
            $voIPPassword = $userInfo['voippwd'];
        }

        $this->Cuct = new ChinaunicomCloudTel($accountSid, $accountToken, $appId, $serverIP, $serverPort, $softVersion, $subAccountSid, $subAccountToken, $voIPAccount, $voIPPassword);



    }

    public function  index()
    {

    }


    //设置我的电话
    public function mytelset()
    {

        // 获取配置信息
        $Doption        = D('Options');
        $TelCenter_Channel      = $Doption->getMyTelCenter_ChannelByid(session("uc_userinfo.id")); //获取当前帐号的电话提供商信息
        $TelCenter_ChannelInfo = $Doption->getTelCenter_ChannelInfoByChannel($TelCenter_Channel); //通过提供商标识 获取完整的信息

        $userInfo['TelCenter_Channel']      = $TelCenter_ChannelInfo['TelCenter_Channel']; //提供商
        $userInfo['TelCenter_Channel_name'] = $TelCenter_ChannelInfo['TelCenter_Channel_name']; //提供商名称
        $userInfo['solutions']              = $TelCenter_ChannelInfo['solutions']; //提供商名称2 兼容老系统

        // 获取voip/坐席绑定信息
        $userInfo['user'] = D('Adminuser')->findUserInfoById(session("uc_userinfo.id"), $userInfo['solutions']);

        $displayTplName = 'mytelset'; // 模板名称

        if ($userInfo['solutions'] == 'holly') {
            /************合力忆捷专用逻辑**************/
            $displayTplName = 'mytelset_holly'; // 模板名称
        }


        if ($userInfo['solutions'] == 'cuct') {
            /************联通云总机专用逻辑**************/
            //dump($userInfo);
            //$userInfo['cuct_displayName'] = session("uc_userinfo.name"); 注释掉  直接取 user
            if (empty($userInfo['cuct_displayName'])){
                $userInfo['cuct_displayName'] = session("uc_userinfo.user");
            }
            //联通云总机 总机号码从配置表中取出
            $CUCT_Masternumber = D('Options')->getOptionNameCC('CUCT_Masternumber');
            $userInfo['CUCT_Masternumber'] = $CUCT_Masternumber['option_value'];
            $userInfo['user']['mynumber'] =  str_replace('_00021090', '', $userInfo['user']['voipaccount']);
            //dump($userInfo['user']);
        }



        // 提交保存逻辑
        if ($_POST) {
            $TelCenter_Channel =  I("post.TelCenter_Channel");
            switch ($TelCenter_Channel) {
                case 'ytx':
                    $data = '';
                    $info = '请在老后台中去设置';
                    $status = 0;
                    break;
                case 'holly':
                    $channel       = 'holly';
                    $actiontype    =  I("post.actiontype");
                    $holly_number  =  I("post.holly_number");
                    $holly_number  = trim($holly_number);
                    if (!is_numeric($holly_number)) {
                        // 处理兼容 8002@qizuang 只取坐席号
                        $holly_numberArr = explode('@',$holly_number);
                        $holly_number = $holly_numberArr[0];
                    }

                    // 绑定逻辑
                    if ($actiontype == 'bind') {

                        //检查是否重复绑定
                        if (!empty($userInfo['user']['voipaccount'])) {
                            //dump($userInfo['user']['voipaccount']);
                            $data            = '';
                            $info            = '您已经绑定过了!要重新绑定请先解绑再绑定!';
                            $status          = -1;
                            //这里逻辑要结束掉,所以要返回
                            $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                        }

                        //检查坐席号是否是空闲的,没被绑定过的
                        $holly_numberBindInfo = self::getVoipInfoByvoipaccount($holly_number, $channel);
                        //var_dump(M()->getLastSql());
                        if(!empty($holly_numberBindInfo)) {
                            // 已经被占用
                            if ($holly_numberBindInfo['use_id'] > 0 && $holly_numberBindInfo['use_on'] == 1) {
                                $data    = '';
                                $info    = '失败:'.$holly_number .'已经占用了, 后台账号ID:'. $holly_numberBindInfo['use_id'] . '用户:'.$holly_numberBindInfo['use_name'];
                                $status  = -1;
                                $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                            }
                        } else {
                            // 未定义的坐席
                            $data    = '';
                            $info    = '失败:'.$holly_number .'未定义坐席,请确认输入正确,或者坐席已经开通';
                            $status  = -1;
                            $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                        }

                        self::BindVoipAccountAll($holly_number,$channel);

                        $data            = '';
                        $info            = '绑定成功!';
                        $status          = 0;
                        $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                    }

                    //解绑逻辑
                    if ($actiontype == 'unbind') {
                        self::UnbindVoipAccountAll($userInfo['user']['voipaccount'],$channel);
                        $data = '';
                        $info = '解绑成功!';
                        $status = 0;
                        $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                    }

                    $data = '';
                    $info = '错误:你是否做了非标准操作!';
                    $status = -1;
                    $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                    break;
                case 'cuct':
                    $actiontype        = I("post.actiontype");
                    $cuct_tel          = I("post.cuct_tel");
                    $cuct_number       = I("post.cuct_number");
                    $cuct_displayName  = I("post.cuct_displayName");
                    $cuct_directNumber = I("post.cuct_directNumber");

                    //禁止绑定总机号码 2018年12月27日 17:31:25
                    $MasterNumberList = ['051286865110','051281881460'];
                    if(in_array($cuct_directNumber,$MasterNumberList)) {
                        $data            = '';
                        $info            = '错误!对外显号的号码不能是总机号码!';
                        $status          = 0;
                        $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                    }

                    //如果 绑定提交的 直通号码 对外显号 和 总机号码相同则设为空
                    /*if ($CUCT_Masternumber['option_value'] == $cuct_directNumber) {
                        $cuct_directNumber = '';
                    }*/

                    //检查是否重复绑定
                    if ($actiontype != 'unbind') {
                        //非解绑逻辑才进行

                        //判断已经绑定过了
                        if (!empty($userInfo['user']['voipaccount'])) {
                            //dump($userInfo['user']['voipaccount']);
                            $data            = '';
                            $info            = '您已经绑定过了!要重新绑定请先解绑再绑定!';
                            $status          = 0;
                            //这里逻辑要结束掉,所以要返回
                            $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                        }
                    }


                    if (!empty($cuct_tel)) {
                        //解绑逻辑
                        if ($actiontype == 'unbind') {
                            //处理联通云总机
                            $result = $this->dropUser($userInfo['user']['tel_work1']);
                            if ($result['resp']['respCode'] == '0') {
                                $data            = $result['resp'];
                                $info            = '我的电话解绑成功!';
                                $status          = 1;

                                //我的电话解绑
                                $this->unbindVoipAccount($userInfo['user']['voipaccount']);

                            } else {
                                $status = 0;
                                $data = $result['resp'];
                                $info = $result['msg'];
                            }

                            //这里逻辑要结束掉,所以要返回
                            $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                        }

                        //创建并绑定逻辑
                       $result = $this->createUser(trim($cuct_tel), trim($cuct_displayName),  trim($cuct_directNumber));
                       if ($result['resp']['respCode'] == '0') {

                           //处理 直通号码 对外显号 ,未绑定直通号码则处理为总机号码
                           if (empty($cuct_directNumber)){
                               $cuct_directNumber = $CUCT_Masternumber['option_value'];
                           }

                           //把联通云总机生成的坐席号码 补上 固定后缀用于sip注册使用
                           $voipnumber = $result['resp']['createUser']['number'];
                           $voipnumber .= '_00021090';

                           //{"data":{"respCode":0,"createUser":{"number":"1000"}},"info":null,"status":1}
                           $data['number']            = $voipnumber;
                           $data['cuct_directNumber'] = $cuct_directNumber;
                           $info                      = '我的电话绑定成功!';
                           $status                    = 1;

                           //我的账户绑定手机号码 绑定 VOIP电话(用户分机号)
                           $this->bindVoipAccount($voipnumber, $cuct_tel, $cuct_directNumber);

                       } else {
                           $status = 0;
                           $data = $result['resp'];
                           $info = $result['msg'];
                       }

                    }
                    $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
                    break;
                default :
                    $data = '';
                    $info = '未匹配的电话呼叫系统提供商';
                    $status = 0;
                    break;
            }
            $this->ajaxReturn(array("data"=>$data,"info"=>$info,"status"=>$status));
        }

        $this->assign("navIndex",'mytel');
        $this->assign("userInfo", $userInfo);
        $this->display($displayTplName);
    }


    /**
     * 绑定voip 通用
     *
     * @param $voipnumber string 绑定的voip电话  必选
     * @param $solutions string  voip提供商 必选
     *
     * @return mixed
     */
    private function BindVoipAccountAll($voipnumber, $solutions) {
        //坐席号码 和  当前后台用户绑定
        $bin_data['use_on']   = 1;
        $bin_data['use_id']   = session("uc_userinfo.id");
        $bin_data['use_name'] = session("uc_userinfo.user");
        $bin_data['use_time'] = time();
        if ($bin_data['use_id'] == "") {
            return false;
        }
        if (!empty($voipnumber) && !empty($solutions)) {
           return D('Admin_voip_tels')->bindVoipInfoByvoipaccount($voipnumber, $solutions, $bin_data);
        }
        return false;

    }

    /**
     *  解绑voip 通用
     * @param $voipnumber string 绑定的voip电话  必选
     * @param $solutions string  voip提供商 必选
     */
    private function UnbindVoipAccountAll($voipnumber, $solutions)
    {
        //voip表解绑
        if (!empty($voipnumber) && !empty($solutions)) {
            D('Admin_voip_tels')->unbindVoipInfoByvoipaccount($voipnumber, $solutions);
        }

    }


    /**
     * 通过voip账号查询本条voip信息 通用
     * @param   $voipaccount  string voip账号
     * @param   $solutions string voip提供商
     * @return  mixed  查询结果
     */
    private function getVoipInfoByvoipaccount($voipaccount, $solutions)
    {

        if (!empty($voipaccount) && !empty($solutions)) {
            return D('Admin_voip_tels')->getVoipInfoByvoipaccount($voipaccount, $solutions);
        }
        return false;
    }


    // 联通云总机直线号码获取 专用
    public function autocompletDirectNumber() {
        $status = '0'; //状态
        $msg    = '';  //消息
        $data   = '';  //数据
        $text   =  I('get.text');
        if (empty($text) && '0' != $text) {
            $status = '0'; //状态
            $msg    = '查询数据不能为空!';  //消息
            $data   = '';  //数据
            $this->ajaxReturn(array("data"=>$data,"info"=>$msg,"status"=>$status));
        }
        $result = $this->Cuct->freeNumbers(10);

        if ($result['resp']['respCode'] == '0') {
            $data   = array();  //数据
            $numbers = $result['resp']['freeNumbers']['numbers'];
            //dump($result['resp']['freeNumbers']['numbers']);
            foreach ($numbers as $key => $value) {
                $data[$key]['id'] = $key;
                $data[$key]['tel'] = $value;
            }
            $status = '1'; //状态
            $msg    = '返回未使用的直通号码';  //消息

        } else {
            $status = 0;
            $data = $result['resp'];
            $info = $result['msg'];

        }
        $this->ajaxReturn(array("data"=>$data,"info"=>$msg,"status"=>$status));
    }


    /**
     *  联通云总机 创建企业用户
     * @param phone 用户绑定电话号码:
    要求号码长度至少为 10 位，
    可以是手机号（以 1 开头），
    可以是固话号码（加区号，如 02566687765），
    也可以是400 和 800 开始的号码  必选
     * @param displayName 用户显示名称，不输入时用用户绑定号码代替  可选
     * @param directNumber  用户显示名称，不输入时用用户绑定号码代替   可选
     * @param callTime  用户呼叫时间限制（分钟）     可选
     */
    private function createUser($phone, $displayName='', $directNumber='', $callTime='')
    {
        $result = $this->Cuct->createUser($phone, $displayName, $directNumber, $callTime);
        return $result;
    }

    /**
     *  联通云总机 删除企业用户
     * @param phone 用户绑定电话号码  必选
     * @param
     * @param
     * @param
     */
    private function dropUser($phone)
    {
        $result = $this->Cuct->dropUser($phone);
        return $result;
    }

    /**
     * 联通云总机 更新企业用户信息
     * @param phone 用户绑定电话号码  必选
     * @param displayName 用户名称  可选
     * @param directNumber 用户直线号码：
    该参数为字符串‘0’时表示释放用户绑定的直线号码；
    不带该参数（或该参数为空字符串），表示保持不变  可选
     * @param callTime 用户呼叫时间限制（分钟） 可选
     */
    private function updateUser($phone, $displayName='', $directNumber='', $callTime='')
    {
        $result = $this->Cuct->updateUser($phone, $displayName, $directNumber, $callTime);
        return $result;
    }

    /**
     * 联通云总机 获取 空闲的直线号码
     * @param  integer $cacheTime 设置缓存时间单位秒,0为不缓存每次都请求接口
     * @return
     */
    private function freeNumbers($cacheTime=0) {
        $freeNumbers = S('C:CUCT:freeNumbers');
        if (empty($freeNumbers) || $cacheTime==0) {
            //如果缓存为空取接口 或者 设置的缓存时间为0也取接口
            $result = $this->Cuct->freeNumbers();
            $freeNumbers = $result['resp'];
            S('C:CUCT:freeNumbers', $freeNumbers, (int)$cacheTime);
        }
        return $freeNumbers;
    }


    /**
     *  我的电话绑定
     * @param voipnumber 绑定的voip电话  必选
     * @param $cuct_tel   绑定手机号
     * @param $cuct_directNumber 直通号码 对外显号  可选
     */
    private function bindVoipAccount($voipnumber, $cuct_tel, $cuct_directNumber)
    {

        //把联通云总机的坐席号码 和  当前后台用户绑定
        $bin_data['use_on']   = 1;
        $bin_data['use_id']   = session("uc_userinfo.id");
        $bin_data['use_name'] = session("uc_userinfo.user");
        $bin_data['use_time'] = time();
        if (!empty($voipnumber)) {
            D('Admin_voip_tels')->bindVoipInfoByvoipaccount($voipnumber, 'cuct', $bin_data);
        }
        //dump(M()->getlastsql());

        //把联通云总机的直通号码存入 adminuser表中的 tel_work1字段
        $bin_data1['tel_work1']            = $cuct_tel;
        $bin_data1['tel_customer_ser_num'] = $cuct_directNumber;
        if (!empty($bin_data['use_id'])) {
            D('Adminuser')->editUserInfo($bin_data['use_id'], $bin_data1);
        }

    }

    /**
     *  我的电话解绑
     * @param voipnumber 绑定的voip电话  必选
     * @param $cuct_tel   绑定手机号
     */
    private function unbindVoipAccount($voipnumber)
    {

        //voip表解绑
        if (!empty($voipnumber)) {
            D('Admin_voip_tels')->unbindVoipInfoByvoipaccount($voipnumber, 'cuct');
        }
        //dump(M()->getlastsql());

        // adminuser表解绑   tel_work1字段
        $myid = session("uc_userinfo.id");
        $bin_data1['tel_work1']            = '';
        $bin_data1['tel_customer_ser_num'] = '';
        if (!empty($myid)) {
            D('Adminuser')->editUserInfo($myid, $bin_data1);
        }

    }



}