<?php
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class OAuthController extends ApiBaseController{
    protected  $localhrefurl;
    public function _initialize(){
        session_start();
        $this->localhrefurl = !empty($_SESSION['refer_url'])?$_SESSION['refer_url']:'https://u.qizuang.com/home/';
    }

    public function weixin_login(){
        if(I("get.code") !== ""){
            //匹配加密的state参数，防止攻击
            if(I("get.state") == urldecode($_SESSION["oauth_safecode"])){
                //unset($_SESSION["oauth_safecode"]);
                //获取token
                import('Library.Org.Util.Mywechat');
                $Mywechat = new \Mywechat();
                $result = $Mywechat->getOpenToken(I("get.code"));
                if(!empty($result)){
                    $token = $result["access_token"];
                    $unionid =  $result["unionid"];
                    //查询用户是否绑定帐号
                    $user = D("User")->getBangAccountInfo("weixin",$unionid);
                    if(count($user) > 0){
                        //已绑定了帐号
                        $this->login($user);
                        //如果是不用户后台登录的，则跳转到当前登录页面
                        header("location:$this->localhrefurl");
                    }else{
                        //查询用户的信息
                        $result = $Mywechat->getUserInfoByToken($token,$unionid);
                        if(!empty($result)){
                            //认证的用户ID
                            $_SESSION["oauth_account"] = array(
                                "account_name"=>"wx_unionid",
                                "uid"=>$unionid,
                                "screen_name"=>$result["nickname"],
                                "logo"=>$result["headimgurl"]
                            );
                            //没有查询到信息，则标识未有绑定帐号,跳转到绑定帐号页面
                            $userInfo = array(
                                "img"=> $result["headimgurl"],
                                "screen_name"=>$result["nickname"],
                                "tip"=>"微信",
                                "icon"=>"tblogin"
                            );
                            $this->assign("user",$userInfo);
                            $this->display("oauth_login");
                        }
                    }

                }
            }else{
                echo "Account authorization failed!";
            }
        }else{
            //获取微信登录二维码
            import('Library.Org.Util.Mywechat');
            $Mywechat = new \Mywechat();
            $authorizeURL = $Mywechat->getloginQr();
            header("location:".$authorizeURL);
        }
    }

    /**
     * QQ授权登录
     */
    public function qq_login(){
        import('Library.Org.OAuth.QqOAuth2');
        $auth = new \Library\Org\OAuth\QqOAuth2(OP("qq_appid"),OP("qq_secret"));
        //如果code不等于空,表示已经授权
        if(I("get.code") !== ""){
            //匹配加密的state参数，防止攻击
            $state = urldecode(I("get.state"));
            $state =  authcode($state,"DECODE");
            if(!empty($state)){
                //通过验证后，注销验证的cookie
                //获取token
                $code = I("get.code");
                $options = array(
                    "redirect_uri"=>OP("qq_redirect_url"),
                    "code"=>$code
                );
                $result = $auth->getAccessToken("code",$options);
                //  if(!empty($result["access_token"])){
                $accessToken = $result["access_token"];
                //获取用户的openid
                $json = $auth->getOpenid($accessToken);
                if(!empty($json["openid"])){
                    $openid = $json["openid"];
                    //查询该用户是否已绑定帐号
                    $user = D("User")->getBangAccountInfo("qq",$openid);

                    if(count($user)>0){
                        //已绑定了用户帐号
                        $this->login($user);
                        header("location:$this->localhrefurl");
                    }else{
                        //未绑定帐号
                        $options = array(
                            "openid"=>$openid,
                            "access_token"=>$accessToken,
                            "oauth_consumer_key"=>OP("qq_appid")
                        );
                        //获取授权用户信息
                        $url ="https://graph.qq.com/user/get_user_info"."?".http_build_query($options);
                        $result = $auth->getUserInfo($url,"GET");
                        if(count($result) > 0){
                            //认证的用户ID
                            $_SESSION["oauth_account"] = array(
                                "account_name"=>"qq_account",
                                "uid"=>$openid,
                                "screen_name"=>$result["nickname"],
                                "logo"=>$result["figureurl_qq_2"]
                            );
                            //没有查询到信息，则标识未有绑定帐号,跳转到绑定帐号页面
                            $userInfo = array(
                                "img"=> $result["figureurl_qq_2"],
                                "screen_name"=>$result["nickname"],
                                "tip"=>"QQ",
                                "icon"=>"login_icon_qq"
                            );
                            $this->assign("user",$userInfo);
                            $this->display("oauth_login");
                        }
                    }
                }else{
                    echo "Account verification failed!";
                    die();
                }
                //  }
            }
        }else{
            //生成随机验证码
            import('Library.Org.Util.App');
            $app = new \App();
            //获取6位数字
            $code = $app->getSafeCode(6,"NUMBER");
            $state =authcode($code,"");
            //授权参数
            $options = array(
                "redirect_uri"=>OP("qq_redirect_url"),
                "state"=> urlencode($state)
            );

            $authorizeURL = $auth->getAuthorizeURL($options);
            header("location:".$authorizeURL);
        }
    }

    /**
     * 新浪授权登录
     * @return [type] [description]
     */
    public function sina_login(){
        import('Library.Org.OAuth.WeiboOAuth2');
        $auth = new \Library\Org\OAuth\WeiboOAuth2(OP("sina_appid"),OP("sina_secret"));
        //如果code不等于空,表示已经授权
        if(I("get.code") !== ""){
            //匹配加密的state参数，防止攻击
            // $state = urldecode(I("get.state"));
            // $state =  authcode($state,"DECODE");
            //    if(!empty($state)){
            //通过验证后，注销验证的cookie
            $code = I("get.code");
            //获取token,token使用一次后就失效
            //授权参数
            $options = array(
                "redirect_uri"=>OP("sina_redirect_url"),
                "code"=>$code
            );

            $result = $auth->getAccessToken("code",$options);

            if(!empty($result["access_token"])){
                $accessToken = $result["access_token"];
                $uid = $result["uid"];
                $expires_in = $result["expires_in"];//有效时间
                //获取新浪微博用户的详细信息
                $options = array(
                    "access_token"=>$accessToken,
                    "uid"=>$uid
                );
                $url = "https://api.weibo.com/2/users/show.json"."?".http_build_query($options);
                $result = $auth->getUserShow($url,"GET");
                if(count($result) > 0){
                    //查询该用户是否已绑定用户帐号
                    $user = D("User")->getBangAccountInfo("weibo",$uid);
                    if(count($user) > 0){
                        //如果有信息，则标识该帐号已经绑定了用户帐号,直接登录，跳转到用户后台
                        $this->login($user);
                        //如果是不用户后台登录的，则跳转到当前登录页面
                        $referer = $_SERVER["HTTP_REFERER"];
                        if(!empty($referer)){
                            $parse_url = parse_url($referer);
                            $bm = strstr($parse_url["host"],".",true);
                            if($bm != "u"){
                                header("location:".$referer);
                                die();
                            }
                        }
                        header("location: $this->localhrefurl");
                    }else{

                        //认证的用户ID
                        $_SESSION["oauth_account"] = array(
                            "account_name"=>"weibo_account",
                            "uid"=>$uid,
                            "screen_name"=>$result["screen_name"],
                            "logo"=>$result["profile_image_url"]
                        );
                        //没有查询到信息，则标识未有绑定帐号,跳转到绑定帐号页面
                        $userInfo = array(
                            "screen_name"=>$result["screen_name"],
                            "tip"=>"新浪微博",
                            "icon"=>"login_icon_sina",
                            "img"=>$result["profile_image_url"]
                        );
                        $this->assign("user",$userInfo);
                        $this->display("oauth_login");
                    }
                }
            }else{
                echo "Account verification failed!";
                die();
            }
            // }
        }else{
            //生成随机验证码
            import('Library.Org.Util.App');
            $app = new \App();
            //获取6位数字
            $code = $app->getSafeCode(6,"NUMBER");
            $state =authcode($code,"");
            //授权参数
            $options = array(
                    "redirect_uri"=> OP("sina_redirect_url"),
                    "state"=>urlencode($state)
                             );
            $authorizeURL = $auth->getAuthorizeURL($options);
            header("location:".$authorizeURL);
        }

    }

    /**
     * 百度授权登录
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function baidu_login()
    {
        import('Library.Org.OAuth.BaiduOAuth2');
        $auth = new \Library\Org\OAuth\BaiduOAuth2("i0rmlcMsuL4X5Lmf6gG2VAwbrqjKaZwv","GSHWnKAQlvPnonV4CUa8mnCGo07FietL");

        if(I("get.code") !== ""){
            $code = I("get.code");
            $origin_url = urldecode(I("get.state"));
            //授权参数
            $options = array(
                    "redirect_uri"=> "https://oauthtmp.qizuang.com/loginfrombaidu",
                    "code"=>$code
                            );
            $result = $auth->getAccessToken("code",$options);

            if (empty($result["error"])) {
                $url = "https://openapi.baidu.com/rest/2.0/cambrian/sns/userinfo";
                $accessToken = $result["access_token"];
                $openid = $result["openid"];

                //获取百度用户的详细信息
                $options = array(
                        "access_token"=>$accessToken,
                        "openid"=>$openid
                                 );
                $url = "https://openapi.baidu.com/rest/2.0/cambrian/sns/userinfo"."?".http_build_query($options);
                $result = $auth->getUserShow($url,"GET");
                if (empty($result["error_code"])) {
                    header("location:".$origin_url);
                    die();
                }
            }
            echo $result["error"].":".$result["error_description"];
            die();
        }else {
            import('Library.Org.Util.App');
            $app = new \App();
            //获取6位数字
            $code = $app->getSafeCode(6,"NUMBER");
            $state =authcode($code,"");
            //授权参数
            $options = array(
                    "redirect_uri"=> "https://oauthtmp.qizuang.com/loginfrombaidu",
                    "state"=>urlencode($_SERVER["HTTP_REFERER"])
                             );
            $authorizeURL = $auth->getAuthorizeURL($options);
            header("location:".$authorizeURL);
            die();
        }
    }

    /**
     * 验证用户帐号
     * @return [type] [description]
     */
    public function validateaccount(){
        if(!isset($_SESSION["oauth_account"])){
            $this->ajaxReturn(array("data"=>"","info"=>"非法提交","status"=>0));
            die();
        }

        if($_POST){
            $model = D("User");
            $data = array(
                "user"=>strtolower(trim(I("post.user")))
            );
            if($model->create($data,8)){
                $user = $model->findUserInfoByUser($data["user"]);
                if(count($user) > 0 ){
                    if(md5(I("post.pwd")) == $user["pass"]){
                        //如果该帐号未绑定该类型帐号
                        if(empty($user[$_SESSION["oauth_account"]["account_name"]])){
                            //验证通过,绑定帐号
                            $data = array(
                                $_SESSION["oauth_account"]["account_name"] => $_SESSION["oauth_account"]["uid"]
                            );
                            $i = $model->edtiUserInfo($user["id"],$data);
                            if($i !== false){
                                //写入登录的session
                                $this->login($user);
                                //绑定成功,登录后台
                                $this->ajaxReturn(array("data"=>$this->localhrefurl,"info"=>"","status"=>1));
                            }
                            $this->ajaxReturn(array("data"=>"","info"=>"绑定失败,请刷新重试！","status"=>0));
                        }
                        $this->ajaxReturn(array("data"=>"","info"=>"该帐号已绑定,绑定失败！","status"=>0));
                    }
                }
                $this->ajaxReturn(array("data"=>"","info"=>"用户名/密码错误！","status"=>0));
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>$model->getError(),"status"=>0));
            }
        }
    }

    /**
     * 跳过此步
     * @return [type] [description]
     */
    public function jump(){
        $this->display();
    }

    /**
     * 注册用户
     * @return [type] [description]
     */
    public function register(){
        if($_POST){
            if(isset($_SESSION["oauth_account"])){
                $type = I("post.type");
                if($type == 1 || $type == 2 || $type == 3){
                    import('Library.Org.Util.App');
                    $app = new \App();
                    $code = $app->getSafeCode(8,"NUMBER");
                    //注册一个新帐号
                    //用户名格式 u_uid
                    $user = "u_".$_SESSION["oauth_account"]["uid"];
                    $data = array(
                        "classid"=>$type,
                        "user"=>$user,
                        "pass"=>md5($code),
                        "name"=>$_SESSION["oauth_account"]["screen_name"],
                        $_SESSION["oauth_account"]["account_name"]=>$_SESSION["oauth_account"]["uid"],
                        "logo"=>empty($_SESSION["oauth_account"]["logo"])?C("QINIU_SCHEME")."://".C("QINIU_DOMAIN")."/".OP("DEFAULT_LOGO"):$_SESSION["oauth_account"]["logo"],

                        "register_time"=>time()
                    );
                    $model = D("User");
                    if($model->create($data,9)){
                        //验证通过,注册帐号
                        $i = $model->addUser($data);
                        if($i !== false){
                            if($type == 2){
                                //如果是设计师,添加设计师详细数据
                                $saveData = array(
                                    "userid"=>$i
                                );
                                D("Userdes")->addDes($saveData);
                            }else if($type == 3){
                                //如果是装修公司,添加装修公司详细数据
                                $saveData = array(
                                    "userid"=>$i,
                                    "lunxian"=>""
                                );
                                D("Usercompany")->AddCompanyDetails($saveData);
                            }
                            $data["id"] = $i;
                            $this->login($data);
                            $this->ajaxReturn(array("data"=>"$this->localhrefurl","info"=>"","status"=>1));
                        }
                        $this->ajaxReturn(array("data"=>"","info"=>"注册失败,请重新绑定！","status"=>0));
                    }else{
                        $this->ajaxReturn(array("data"=>"","info"=>$user->getError(),"status"=>0));
                    }
                }
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"非法提交","status"=>0));

    }

    //短网址地址
    public function short_url()
    {
        import('Library.Org.OAuth.WeiboOAuth2');
        $auth = new \Library\Org\OAuth\WeiboOAuth2('620994561','1c2808fe019fa6e55616b18d4b610abd');
        $param = array(
            'source' => $auth->client_id
        );

        $url = "http://api.t.sina.com.cn/short_url/shorten.json" ."?". http_build_query($param).'&'.$_SERVER['QUERY_STRING'];
        $result = $auth->getShortUrl($url,"GET");
        if (!isset($result["error_code"])) {
            $this->ajaxReturn(array("data" => $result,"info"=> "请求成功！","status"=>1));
            die();
        }
        $this->ajaxReturn(array("info"=> $result["error"],"status"=>0));
        die();
    }


    /**
     * 写入用户后台session
     */
    private function login($user){
        //根据BM查询城市信息
        $city = D("Quyu")->getCityById($user["cs"]);
        $_SESSION["u_userInfo"] = array(
            "id"=>$user["id"],
            "name"=>$user["name"],
            "user"=>$user["user"],
            "cs"=>$user["cs"],
            "qx"=>$user["qx"],
            "logo"=>$user["logo"],
            "classid"=>$user["classid"],
            "bm"=>$user["bm"],
            "cname"=>$city["cname"]
        );
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        //记录日志
        $data = array(
            "username"=>$user["name"],
            "userid"=>$user["id"],
            "ip"=>$app->get_client_ip(),
            "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
            "info"=>"用户登录成功",
            "time"=>date("Y-m-d H:i:s"),
            "action"=>CONTROLLER_NAME."/".ACTION_NAME
        );
        D("Loguser")->addLog($data);
        unset($_SESSION["oauth_account"]);
    }
}