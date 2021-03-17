<?php
namespace Api\Controller;
use Think\Controller;

/**
* API接口
*/
class V0Controller extends Controller
{
	public function hm()
	{
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();

		$state = 0;
        $engine = "";
        if (I("get.ref") !== "") {
            //判断是否是外链
            $result = preg_match('/qizuang.com/',I("get.ref"));
            if (!$result) {
               //不是本站点击的都算是外链
               $state = 2;
            }

            //查看是否是搜索引擎访问的
            //主流搜索引擎域名
            $engineArray = array("baidu.com"=>"百度","sogou.com"=>"搜狗","so.com"=>"360","youdao.com"=>"有道","soso.com"=>"搜搜","cn.bing.com"=>"必应中国","sm.cn"=>"神马");

            foreach ($engineArray as $key => $value) {
                $result = preg_match('/'.$key.'/',I("get.ref"));
                if ($result) {
                    $engine = $value;
                    break;
                }
            }

            if (!empty($engine)) {
                $state = 1;
                cookie('QZENGINE', $engine, array('expire'=> 3600 ,'domain' => '.'.APP_HTTP_DOMAIN));
            }

            if ($state != 0) {
               cookie('QZSTATE', $state, array('expire'=> 3600 ,'domain' => '.'.APP_HTTP_DOMAIN));
            }

        }

        $host = I("get.host") != ""? I("get.host"):cookie("QZHOST");
        // cookie('QZHOST',$host, array('expire'=> 3600,'path'=>'/','domain' => '.'.APP_HTTP_DOMAIN));
        $engine = cookie('QZENGINE') == null?$engine:cookie('QZENGINE');
        $state = cookie('QZSTATE') == null?$state:cookie('QZSTATE');

        $uuid = I("get.uuid");
        if (empty($uuid)) {
            $tag = cookie("QZUUID") == null?"":cookie("QZUUID");
        }

        $tag =  I("get.tag");
        if (empty($tag)) {
            $tag = cookie("QZSRC") == null?"":cookie("QZSRC");
        }
        $ip = $app->get_client_ip();
        //获取友链信息
        $data = array(
            "user_agent" => $_SERVER["HTTP_USER_AGENT"],
            "ip" => $ip,
            "host" => $host,
            "time_in" => I("get.timeIn"),
            "time_out" => I("get.timeOut"),
            "visit_time" => I("get.visit_time"),
            "ck" => I("get.ck"),
            "referer" => I("get.ref"),
            "tag" => $tag,
            "ua" => md5($ip.$_SERVER["HTTP_USER_AGENT"]),
            "uuid" => $uuid,
            "engine" => $engine,
            "state" => $state,
            "time" => time(),
            "date" => date("Y-m-d")
        );
        M("market_acquisition")->add($data);

        header('HTTP/1.1 200 OK');
        header ('Content-Type: image/gif');
        header('Cache-Control:no-cache');
        $im = imagecreate(1, 1);
        $bg_color = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, 1, 1, $bg_color);
        echo imagegif($im);
        imagedestroy($im);
    }
}