<?php
namespace Common\Controller;
use Think\Controller;
class BaseController extends Controller{
    public function _initialize(){
        if(!session("?uc_userinfo")){
            if(IS_AJAX){
                if(!strstr($_SERVER["HTTP_ACCEPT"],"text/html")){
                    $this->ajaxReturn(array("data"=>"","info"=>"您的登陆超时了,请重新登录","status"=>0));
                }else{
                    echo "您的登录超时了,请重新登录";
                }
            }else{
                header("LOCATION:".C("UC_URL"));
            }
            die();
        }

        // 站点多域名访问支持 动态化httphost
        $schemeAndHost = getSchemeAndHost();
        //$SCHEME_HOST   = $schemeAndHost;
        /** 'isSsl' => boolean false
         * 'scheme' => string 'http'
         * 'scheme_full' => string 'http://'
         * 'host' => string 'm.qizuang.com'
         * 'domain' => string 'qizuang.com'
         * 'scheme_host' => string 'http://m.qizuang.com'
         */
        $this->assign('SCHEME_HOST',$schemeAndHost);
    }

    //空操作
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Public:404');
        die();
    }

    /**
     * [_error description]
     * @return [type] [description]
     */
    public function _error($data){
        $this->assign("errMsg",$data["errMsg"]);
        $this->assign("jumpUrl",$data["jumpUrl"]);
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Public:error');
        die();
    }


}
