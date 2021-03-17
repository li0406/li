<?php

class Mywechat {
    private $weObj = null;
    /**
     * 获取微信token
     * @return [type] [description]
     */
    public function getWechatToken(){
        if (C('APP_ENV') == 'dev' || C('APP_ENV') == 'test') {
            //开发模式用
//            $appid = OP('WX_QZ_FW_APPID_DEV'); //填写高级调用功能的app id
//            $secret = OP('WX_QZ_FW_APPSECRET_DEV'); //填写高级调用功能的密钥
            $appid = 'wx6ac56878778c987a'; //填写高级调用功能的app id
            $secret = 'a5f6a314af2b43779d8682307d7bbcb4'; //填写高级调用功能的密钥
        } else {
            $appid = OP("WX_QZ_FW_APPID");
            $secret = OP("WX_QZ_FW_APPSECRET");
        }
        //查询当前的token是否有效
        $Wechat = D("WechatToken")->getLastToken($appid);
        $time = time();
        $token = $Wechat["token"];
        //有效时间小于当前时间,重新申请token
        if ($Wechat["expires_in"] < $time) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            //0不包含头部信息，非零包含头部
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            //连接超时10秒
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            //超时设置10秒
            curl_setopt($ch, CURLOPT_TIMEOUT,10);
            //以变量存储，不要直接输出
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $hander = curl_exec($ch);
            if ($hander) {
                $json = json_decode($hander, true);
                if (isset($json["access_token"]) && $json["expires_in"]) {
                    $token = isset($json["access_token"]) ? $json["access_token"] : '';
                    $expire = isset($json["expires_in"]) ? $time + ($json['expires_in'] ? intval($json['expires_in']) - 100 : 3600) : 0;
                    //将数据保存到数据库中
                    $data = array(
                        "appid" => $appid,
                        "token" => $token,
                        "expires_in" => $expire
                    );
                    D("WechatToken")->addToken($data);
                }
            }
            curl_close($ch);
        }
        return $token;
    }

    /**
     * 获取微信用户的链接地址
     * @return [type] [description]
     */
    public function getWecharUserUrl($wx_unionid)
    {
        $urls = [];
        $token = $this->getWechatToken();
        if (empty($token)){
            throw new Exception('Token获取失败');
        }
        foreach ($wx_unionid as $key => $value) {
            $url["url"] = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$value&lang=zh_CN";
            $url["wx_unionid"] = $value;
            $urls[] = $url;
        }
        return $urls;
    }

    /**
     * 获取用户的基本信息
     * $wx_unionid 微信ID数组
     * @return [type] [description]
     */
    public function getWecharUserInfo($wx_unionid)
    {
        $wx_unionid = array_filter($wx_unionid);
        if (empty($wx_unionid)) {
            return "";
        }
        $urls = $this->getWecharUserUrl($wx_unionid);
        $mh = curl_multi_init();
        // 初始化
        foreach ($urls as $i => $val) {
            $conn[$i] = curl_init();
            curl_setopt($conn[$i], CURLOPT_URL, $val["url"]);
            curl_setopt($conn[$i], CURLOPT_HEADER, 0);
            curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($conn[$i], CURLOPT_TIMEOUT, 30);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_multi_add_handle($mh, $conn[$i]);
        }
        $active = null;

        // 执行批处理句柄
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            //if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            //}
        }

        foreach ($urls as $i => $url) {
            $hander = curl_multi_getcontent($conn[$i]); // 获得爬取的代码字符串
            $json = json_decode($hander, true);
            if (!isset($json["errcode"])) {
                $result[] = array(
                    "img" => $json["headimgurl"],
                    "nickname" => $json["nickname"],
                    "sex" => $json["sex"] == 1 ? "先生" : "女士",
                    "city" => $json["city"],
                    "wx_unionid" => $url["wx_unionid"]
                );
            } elseif ($json["subscribe"] == 0) {
                $data = array(
                    "content" => "获取绑定名单: 用户未关注，URL:【" . $url['url'] . "】 公司编号:【" . $_SESSION["u_userInfo"]["id"] . "】",
                    "time" => time()
                );
                M("log_debug")->add($data);
            } else {
                //
                $data = array(
                    "content" => "获取绑定名单:" . $json["errcode"] . ":" . $json["errmsg"] . " URL:【" . $url['url'] . "】 公司编号:【" . $_SESSION["u_userInfo"]["id"] . "】",
                    "time" => time()
                );
                M("log_debug")->add($data);
            }
        }
        // 结束清理
        foreach ($urls as $i => $url) {
            curl_multi_remove_handle($mh, $conn[$i]);
            curl_close($conn[$i]);
        }
        curl_multi_close($mh);
        return $result;
    }

    /**创建二维码ticket
    * @param int|string $scene_id 自定义追踪id,临时二维码只能用数值型
    * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)；2:永久二维码(此时expire参数无效)
    * @param int $expire 临时二维码有效期，最大为1800秒
    * @http://mp.weixin.qq.com/wiki/18/28fc21e7ed87bec960651f0ce873ef8a.html
    */
    public function getQRCode($options = null, $scene_id = 91, $type = 0, $expire = 1800)
    {
        if ($this->weObj == null) {
            import('Library.Org.wechat.wechat');
            $this->weObj = new \TPWechat($options);
        }
        //创建二维码ticket
        $result = $this->weObj->getQRCode($scene_id, $type, $expire);
        if (!$result) {
            $data = array(
                "content" => $this->weObj->errMsg,
                "time" => time()
            );
            M("log_debug")->add($data);
            return false;
        }
        return $result;
    }

    //通过二维码ticket,生成二维码图片
    public function getQRUrl($scene_id = 91, $type = 0, $expire = 1800)
    {
        if (C('APP_ENV') == 'dev' || C('APP_ENV') == 'test') {
            $this->wxoptions = array(
                //开发模式用
                'token' => OP('WX_QZ_FW_TOKEN_DEV'), //填写你设定的key
                //'encodingaeskey' => OP('WX_QZ_FW_ENCODINGAESKEY'), //    填写加密用的EncodingAESKey
                'appid' => OP('WX_QZ_FW_APPID_DEV'), //填写高级调用功能的app id
                'appsecret' => OP('WX_QZ_FW_APPSECRET_DEV') //填写高级调用功能的密钥
            );
        } else {
            $this->wxoptions = array(
                'token' => OP('WX_QZ_FW_TOKEN'), //填写你设定的key
                'encodingaeskey' => OP('WX_QZ_FW_ENCODINGAESKEY'), //填写加密用的EncodingAESKey
                'appid' => OP('WX_QZ_FW_APPID'), //填写高级调用功能的app id
                'appsecret' => OP('WX_QZ_FW_APPSECRET') //填写高级调用功能的密钥
            );
        }
        import('Library.Org.wechat.wechat');
        $this->weObj = new \TPWechat($this->wxoptions);
        $ticket = $this->getQRCode(null, $scene_id, $type, $expire);
        if ($ticket !== false) {
            $result = $this->weObj->getQRUrl($ticket["ticket"]);  //创建二维码图片
            return array("ticket" => $ticket["ticket"], "img" => $result, "scene_id" => $scene_id);
        }
        return false;
    }

    //电脑端微信扫描ajax轮询监控
    public function wxscanstatus() {
        //通过ticket查询微信回调接口情况
        if ($_SESSION['u_wx_ticket']) {
            //通过ticket查询数据库微信callback日志
            $callbackst = D("Logwechatcallbak")->checkCallbackTicket($_SESSION['u_wx_ticket']);

            //检查是否已经扫描过了(code:0x02),现在等待用户确认中
            if (isset($_SESSION['u_wx_ticket_need_user_ok'])) {
                //根据用户的openid查询是否已经绑定,如果绑定成功，提示信息
                $wechar = D("Orderwechar")->getAccountByOpenId($callbackst["fromusername"],$_SESSION["u_userInfo"]["id"],0);
                $wechar = array_filter($wechar);
                if(count($wechar) > 0){
                    unset($_SESSION['u_wx_ticket']); //干掉票据
                    unset($_SESSION['u_wx_ticket_need_user_ok']); //干掉等待用户确认
                    return array("status"=>3,"info"=>"绑定成功");
                }
            }

            if (count($callbackst) > 0) { //查到了回调数据
                //设置session u_wx_ticket_need_user_ok 标识, 等待用户确认
                $_SESSION['u_wx_ticket_need_user_ok'] = 1;
                //给接口返回扫描成功
                return array("status"=>2,"info"=>"扫描成功,请在你微信上确认!");
            }else{
                return array("status"=>1,"info"=>"等待用户扫描......");
            }
        }else{
            return array("status"=>0,"info"=>"错误当前状态没有生成微信二维码!");
        }
    }


    //电脑端微信扫描ajax轮询监控
    public function wxscanstatusforlogin()
    {
        $wxloginTicket = $_SESSION['u_wx_login_ticket'];
        //通过ticket查询微信回调接口情况
        if (!empty($wxloginTicket)) {
            $logCallback = D('Logwechatcallbak')->checkCallbackTicketByKey($wxloginTicket);
            if (empty($logCallback)) {
                return array('status' => 1, 'info' => '正在等待扫描');
            }
            //根据用户的openid查询是否已经绑定,如果绑定成功，提示信息
            $wechar = D('Orderwechar')->getAccountByOpenId($logCallback['fromusername'], $_SESSION['u_userInfo']['id'], 0);
            if (!empty($wechar)) {
                unset($_SESSION['u_wx_login_ticket']);
                return array('status' => 2, 'info' => '扫码成功');
            } else {
                return array('status' => 1, 'info' => '用户微信未绑定');
            }
        } else {
            return array('status' => 0, 'info' => '二维码已过期');
        }
    }

    /**
     * 获取微信登录二维码
     * @return [type] [description]
     */
    public function getloginQr(){
        import('Library.Org.OAuth.WechatOAuth2');
        $auth = new \Library\Org\OAuth\WechatOAuth2(OP("open_wechat_appid"),OP("open_wechat_secret"));
        //生成随机验证码
        import('Library.Org.Util.App');
        $app = new \App();
        $code  = authcode(session_id(),"");
        $_SESSION["oauth_safecode"] = urlencode($code);
        //授权参数
        $options = array(
                "redirect_uri"=>trim(OP("open_wechat_redirect_url")),
                "state"=> urlencode($code)
                         );
        $url = $auth->getAuthorizeURL( $options);
        return $url;
    }

    /**
     * 获取开放平台授权的token
     * code 授权的code
     * @return [type] [description]
     */
    public function getOpenToken($code){
        $appid = trim(OP("open_wechat_appid"));
        $secret = trim(OP("open_wechat_secret"));
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        import('Library.Org.OAuth.WechatOAuth2');
        $auth = new \Library\Org\OAuth\WechatOAuth2(OP("open_wechat_appid"),OP("open_wechat_secret"));
        $result = $auth->http($url,"GET");
        $json = json_decode($result,true);
        if(!empty($json)){
            if(!isset($json["errcode"])){
                return $json;
            }
        }
        return null;
    }

    /**
     * 根据token和unionid查询授权用户信息
     * @param  [type] $token   [description]
     * @param  [type] $unionid [description]
     * @return [type]          [description]
     */
    public function getUserInfoByToken($token,$unionid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$token&openid=$unionid";
        import('Library.Org.OAuth.WechatOAuth2');
        $auth = new \Library\Org\OAuth\WechatOAuth2(OP("open_wechat_appid"),OP("open_wechat_secret"));
        $result = $auth->http($url,"GET");
        $json = json_decode($result,true);
        if(!empty($json)){
            if(!isset($json["errcode"])){
                return $json;
            }
        }
        return null;
    }
}