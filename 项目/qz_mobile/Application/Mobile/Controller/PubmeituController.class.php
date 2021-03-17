<?php
/**
 * 移动版 - 美图
 */
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class PubmeituController extends MobileBaseController{

    public function _initialize(){
        parent::_initialize();
        if (IS_GET) {
            $uri = $_SERVER['REQUEST_URI'];
            preg_match('/html$/', $uri, $m);
            if (count($m) == 0) {
                preg_match('/\/$/', $uri, $m);
                $parse = parse_url($uri);
                if (count($m) == 0 && empty($parse["query"])) {
                    header("HTTP/1.1 301 Moved Permanently");
                    if (isSsl()) {
                        $http = "https://";
                    } else {
                        $http = "http://";
                    }
                    header("Location: " . $http . $_SERVER["HTTP_HOST"] . $uri . "/");
                    die();
                }
            }
        }
    }

    //美图列表
    public function pubMeituList(){

        /*S-动态参数分页跳转到静态参数分页*/
        if (isset($_GET['a1'])) {
            $arr = array(
                'l' => 'a1',
                'f' => 'a2',
                'm' => 'a3'
            );
            //拼接静态URL
            $url = $this->SCHEME_HOST['scheme_host'].'/meitu/gongzhuang-';
            foreach ($arr as $key => $value) {
                $url = $url . $key . intval($_GET[$value]);
            }
            if (intval($_GET['p']) > 1) {
                $url = $url . 'p' . intval($_GET['p']);
            }
            $url = $url . '?';
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('a1', 'a2', 'a3', 'p', 'q'))) {
                    $url = $url . $key . '=' . $_GET[$key] . '&';
                }
            }
            $url = rtrim($url, '&');
            $url = rtrim($url, '?');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }

        /*E-动态参数分页跳转到静态参数分页*/

        //获取请求参数
        import('Library.Org.Page.PPage');
        $page = new \PPage();
        //解析参数
        $params = $page->analyticalAddress();
        //如果没有传参就跳转添加默认参数
        if (!$params['params']) {
            $url = $this->SCHEME_HOST['scheme_full'] . $this->SCHEME_HOST['host'] . (substr($_SERVER["REQUEST_URI"],0,-1) ). '-l0f0m0/';

            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
        }

        //获取美图列表
        $pageIndex = 1;
        $pageCount = 40;
        $meitu = $this->getMeiTuList($pageIndex,$pageCount,'',$params);
        $info["meitu"] = $meitu["list"];
        $info["count"] = $meitu["count"];
        $info["page"] = $meitu["page"];
        foreach ($meitu["params"] as $key => $value) {
            if($value > 0){
                $count ++;
                $params[]= $key;
            }
        }
        $params_count = count($params);
        //如果是1个参数
        if(count($params) == 1){
            $type = $params[0];
        }

        //获取推荐局部信息
        $result = D("Pubmeitu")->getPubMeituAttr('1',$limit,$isTop);
        $info["wz"] = $this->getStaticNavUrl($result, 'l', $meitu["url"]);
        //获取风格
        $result = D("Pubmeitu")->getPubMeituAttr('2',$limit,$isTop);
        $info["fg"] = $this->getStaticNavUrl($result, 'f', $meitu["url"]);
        //获取户型
        $result = D("Pubmeitu")->getPubMeituAttr('3',$limit,$isTop);
        $info["mj"] = $this->getStaticNavUrl($result, 'm', $meitu["url"]);

        //第一次没有筛选设置默认值
        if (empty($meitu["params"])) {
            $meitu["params"] = [
                'location' => '0',
                'fengge' => '0',
                'mianji' => '0'
            ];
        }
        //动态生成绑定参数
        foreach ($meitu["params"] as $key => $value) {
            switch ($key) {
                case 'location':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["wz"]);
                    break;
                case 'fengge':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["fg"]);
                    break;
                case 'mianji':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["mj"]);
                    break;
            }
            $info["params"][$key] = $sub;
            $info["navParams"][$key] = $value;
        }

        //判断是否为 ajax 请求
        if(IS_AJAX){
            foreach ($info['meitu'] as $k => $v) {
                $str .= '<div class="article" id=""><a href="/meitu/g'.$v['id'].'.html"><img src="'.$this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN').'/'.$v['img_path'].'-w300.jpg" alt=""/></a><p><a href="/meitu/g'.$v['id'].'.html">'.$v['title'].'</a></p></div>';
            }
            if(empty($str)){
                $this->ajaxReturn(['data' => '', 'info' => '该分类下已没有更多图片了~', 'status' => 0]);
            }else{
                $this->ajaxReturn(['data' => $str, 'info' => '', 'status' => 1]);
            }
        }

        //seo 标题/描述/关键字
        $mianji = isset($info['params']['mianji']['name'])?$info['params']['mianji']['name']:'';
        $fengge = isset($info['params']['fengge']['name'])?$info['params']['fengge']['name']:'';
        $location = isset($info['params']['location']['name'])?$info['params']['location']['name']:'';
        $all_condition_name = $mianji . $fengge .$location;

        $basic["head"]["title"] = $all_condition_name . '装修效果图_' . $all_condition_name . '设计-齐装网装修效果图';
        $basic["head"]["keywords"] = $all_condition_name . '装修效果图,' . $all_condition_name . '装修图片,' . $all_condition_name . '公装图片';
        $basic["head"]["description"] = '齐装网' . $all_condition_name . '装修效果图专区，提供国内外新流行的' . $all_condition_name . '公装设计效果图，更多' . $all_condition_name . '装修效果图片尽在齐装网装修效果图频道';

        $basic["body"]["title"] = '公装美图';
        $info["params"] = array_filter($info["params"]);
        $info['pageid'] = empty($info['navParams']['p']) ? $pageIndex : $info['navParams']['p'];
        //不限按钮链接
        $info['select']['location'] = '/meitu/gongzhuang-l0f'.$info['navParams']['fengge'].'m'.$info['navParams']['mianji'].'/';
        $info['select']['fengge'] = '/meitu/gongzhuang-l'.$info['navParams']['location'].'f0m'.$info['navParams']['mianji'].'/';
        $info['select']['mianji'] = '/meitu/gongzhuang-l'.$info['navParams']['location'].'f'.$info['navParams']['fengge'].'m0/';

        //处理当前选中项
        if(!empty($meitu["params"]['location'])){
            foreach ($info["wz"] as $key => $value) {
                if($meitu["params"]['location'] == $value['id']){
                    $info['nav']['wz'] = $value['name'];
                }
            }
        }
        //风格
        if(!empty($meitu["params"]['fengge'])){
            foreach ($info["fg"] as $key => $value) {
                if($meitu["params"]['fengge'] == $value['id']){
                    $info['nav']['fg'] = $value['name'];
                }
            }
        }
        //户型
        if(!empty($meitu["params"]['mianji'])){
            foreach ($info["mj"] as $key => $value) {
                if($meitu["params"]['mianji'] == $value['id']){
                    $info['nav']['mj'] = $value['name'];
                }
            }
        }

        /*由于此处没有分页，该属性可以简单如下这样处理，生成canonical标签属性值*/
        if(!isset($_GET['a1'])){
            $position = strpos($_SERVER['REQUEST_URI'], '?');
            if($position > 0){
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.'.C('QZ_YUMING').substr($_SERVER['REQUEST_URI'], 0, $position);
            }else{
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.'.C('QZ_YUMING').$_SERVER['REQUEST_URI'];
            }

            $info['canonical'] = str_replace('/meitu/','/',$info['canonical']);
        }
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info",$info);
        $this->assign("basic",$basic);
        $this->assign("realurl",'/meitu/gongzhuang-l'.$info['navParams']['location'].'f'.$info['navParams']['fengge'].'m'.$info['navParams']['mianji']);
        $this->display("list");
    }

    /**
     * 获取导航链接
     * @param  array  $datas 参数数组
     * @param  string $type  短参数
     * @param  array  $urls  url地址
     */
    public function getStaticNavUrl($datas = array(), $type = '', $urls = array())
    {
        if ('meitu/gongzhuang' == trim($urls['short_url'], '/')) {
            $urls['short_url'] = '/meitu/gongzhuang-l0f0m0/';
        }
        //参数替换
        $pattern = '/'.$type.'\d+/i';
        foreach ($datas as $key => $value) {
            $datas[$key]["link"] = preg_replace($pattern, $type.$value["id"],$urls['short_url']);
            $datas[$key]["nofollow"] = 'follow';
        }
        return $datas;
    }


    //美图详情页
    public function show(){

        $p = intval(I("get.id"));

        //判断来源
        $referer = parse_url($_SERVER['HTTP_REFERER'])['path'];
        $params = array();
        if (!empty($referer)) {
            $match = array();
            if (1 === preg_match('/meitu\/gongzhuang-(l([\d+]+)f([\d+]+)m([\d+]+)(p[\d+]+)?+(q[\d+]+)?)$/', $referer, $match)) {
                $params = array(
                    'location' => intval($match['2']),
                    'fengge' => intval($match['3']),
                    'mianji' => intval($match['4'])
                );
            } else if (1 === preg_match('/meitu\/g(\d+)\.html/', $referer, $match)) {
                $temp = json_decode(cookie('pubmeitu_terminal_params_mobile'));
                if (array_sum($temp) > 0) {
                    $params = array(
                        'location' => intval($temp['0']),
                        'fengge' => intval($temp['1']),
                        'mianji' => intval($temp['2'])
                    );
                }
            }
        }

        cookie('pubmeitu_terminal_params_mobile', null);

        //美图缓存
        $cacheKey = $p . md5(serialize($params));
        $info = S("Cache:Mobile:PubMeitu:show:info:". $cacheKey);
        if(empty($info)){
            //查询美图信息
            $map = array(
                'id' => intval($p)
            );
            $info['now'] = $this->getPubMeituInfoByMap($map, $params);
            if (empty($info['now'])) {
                $this->_error();
            }
            //获取上一个美图
            $map = array(
                'id' => array('GT', $p)
            );
            $info['prv'] = $this->getPubMeituInfoByMap($map, $params, 'asc');
            if (empty($info['prv'])) {
                $map = array(
                    'id' => array('GT', 0)
                );
                $info['prv'] = $this->getPubMeituInfoByMap($map, $params, 'asc');
            }
            //获取下一个美图
            $map = array(
                'id' => array('LT', $p)
            );
            $info['next'] = $this->getPubMeituInfoByMap($map, $params, 'desc');
            if (empty($info['next'])) {
                $map = array(
                    'id' => array('GT', 0)
                );
                $info['next'] = $this->getPubMeituInfoByMap($map, $params, 'desc');
            }
            S("Cache:Mobile:PubMeitu:show:info:". $cacheKey, $info, 300);
        }

        //seo
        $basic["head"]["title"] = $info["now"]["title"]."-齐装网装修效果图";
        $basic["head"]["keywords"] = $info["now"]["keyword"];
        $basic["head"]["description"] = $info["now"]["description"];
        $basic["body"]["title"] = '装修美图';
        $this->assign("info",$info);
        $this->assign('basic',$basic);
        $this->assign("params", json_encode(array_values($params)));
        $this->display('show');
    }

    /**
     * 通过条件获取单个美图
     * @param  array  $map    条件
     * @param  array  $params 参数
     * @param  string $order  排序
     * @return
     */
    private function getPubMeituInfoByMap($map, $params, $order = 'asc'){
        $temp = D("Pubmeitu")->getPubMeituInfoByMap($map, $params, $order);
        $result = array();
        foreach ($temp as $key => $value) {
            if (!isset($result['id'])) {
                $result = $value;
            }
            $result["child"][] = $value;
            $result["count"] ++;
        }
        return $result;
    }

    private function getMeituInfo($id){
        $meitu = D("Pubmeitu")->getPubMeituInfo($id);
        foreach ($meitu as $key => $value) {
            if(!array_key_exists($value["action"], $meitu)){
                $meitu[$value["action"]] = $value;
            }
            $meitu[$value["action"]]["child"][] = $value;
            $meitu[$value["action"]]["count"] ++;
            unset($meitu[$key]);
        }
        return $meitu;
    }



    private function getParams($type,$value,$count,$url,$data){
        foreach ($data as $k => $val) {
            if($value == $val["id"]){
                $sub = array(
                        "name" =>$val["name"]
                             );
                if($count == 1){
                    $sub["link"] = "/meitu/gongzhuang/";
                }else{
                    switch ($type) {
                        case 'location':
                           //替换当前的参数
                            $reg = '/a1=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a1=0",$url["url"]);
                            break;
                         case 'fengge':
                           //替换当前的参数
                            $reg = '/a2=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a2=0",$url["url"]);
                            break;
                         case 'mianji':
                           //替换当前的参数
                            $reg = '/a3=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a3=0",$url["url"]);
                            break;

                    }
                    $sub["link"] = $link;
                }
                break;
            }
        }
        return $sub;
    }

    private function getLocation($limit,$isTop,$type,$params_count,$url){

        $result = D("Pubmeitu")->getPubMeituAttr('1',$limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                 $link ="/meitu/gongzhuang-l".$value["id"]."f0m0";
                 $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "location"){
                    //替换当前的参数
                    $reg = '/l\d+/i';
                    $link =preg_replace($reg, "l".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a1=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a1=".$value["id"],$url["url"]);
                }
            }else{
                 //替换当前的参数
                $reg = '/a1=\d+/i';
                $link = preg_replace($reg, "a1=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
        return $result;
    }

    private function getFengge($limit,$isTop,$type,$params_count,$url){
        $result = D("Pubmeitu")->getPubMeituAttr('2',$limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                $link ="/meitu/gongzhuang-l0f".$value["id"]."m0";
                $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "fengge"){
                    //替换当前的参数
                    $reg = '/f\d+/i';
                    $link =preg_replace($reg, "f".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a2=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a2=".$value["id"],$url["url"]);
                }
            }else{
                //替换当前的参数
                $reg = '/a2=\d+/i';
                $link = preg_replace($reg, "a2=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
         return $result;
    }

    private function getMianji($limit,$isTop,$type,$params_count,$url){
        $result = D("Pubmeitu")->getPubMeituAttr('3',$limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                 $link ="/meitu/gongzhuang-l0f0m".$value["id"];
                 $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "huxing"){
                    //替换当前的参数
                    $reg = '/m\d+/i';
                    $link =preg_replace($reg, "m".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a3=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a3=".$value["id"],$url["url"]);
                }
            }else{
                 //替换当前的参数
                $reg = '/a3=\d+/i';
                $link = preg_replace($reg, "a3=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
         return $result;
    }

    private function getMeiTuList($pageIndex,$pageCount,$keyword,$params = null){
        //判断是否有分页
        $pageIndex = !empty($params['params']['p'])?$params['params']['p']:$pageIndex;
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Pubmeitu")->getPubMeiTuListCount($params['params']["location"],$params['params']["fengge"],$params['params']["mianji"],'','');
        //分页
        import('Library.Org.Page.Page');
        //自定义配置项
        $config  = array("prev","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $list = D("Pubmeitu")->getPubMeiTuList(($page->pageIndex-1)*$pageCount,$pageCount,$params['params']["location"],$params['params']["fengge"],$params['params']["mianji"],'','',1);
        foreach ($list as $key => $value) {
            //取每个分类的第一个元素
            $exp =array_filter(explode(",", $value["location"]));
            $list[$key]["location"] = $exp[0];

            $exp =array_filter(explode(",", $value["fengge"]));
            $list[$key]["fengge"] = $exp[0];

            $exp =array_filter(explode(",", $value["mianji"]));
            $list[$key]["mianji"] = $exp[0];
        }

        $pageList = $page->showMeitu();
        return array("list"=>$list,"params"=>$params["params"],"url"=>$params["url"],'page'=>$pageList);
    }
}
