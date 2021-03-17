<?php
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class WwwController extends ApiBaseController {
    public function _initialize(){
        //检测请求的域名是否合法
        //合法的域名数组
        $register_url = C("REGISTER_URL");
        $referer= $_SERVER['HTTP_ORIGIN'];

        if(in_array($referer,$register_url) || preg_match('/([A-za-z])+(.[A-za-z]+)?.'.APP_HTTP_DOMAIN.'/i', $referer)){
            header("Access-Control-Allow-Credentials:true");
            header('Access-Control-Allow-Origin:'.$referer);
        }
    }
    // 取得我所在的城市
    public function getMyCity(){
        //默认给苏州
        $normal = array(
                    "cid"     => "000001",
                    "bm"      => "www",
                    "oldName" => "全国"
                        );
        //如果传入了城市
        if($cs = $_GET['cs']) {
            $frag = mb_substr($cs, 0, 2, 'utf-8');
            $city = D('Common/Quyu')->getCityIdByName($frag);
            if(!empty($city)){
                return  $this->ajaxReturn(array("data"=>$city,"info"=>'OK',"status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>$normal, "info"=>"OK","status"=>1));
    }
}
