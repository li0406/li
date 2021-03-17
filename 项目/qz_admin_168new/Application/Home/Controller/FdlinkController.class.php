<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class FdlinkController extends HomeBaseController{

    /**
     * [index 友情链接首页]
     * @return [type] [description]
     */
    public function index(){
        //获取所有城市信息
        $cs = D("Area")->getOpenCtiys(false);
        //将相同首字母的城市合并
        foreach ($cs as $key => $value) {
            if(!array_key_exists($value["key"], $cs)){
                $cs[$value["key"]] = array();
                $cs[$value["key"]]["key"] =$value["key"];
            }
            $cs[$value["key"]]["child"][] = $value;
            unset($cs[$key]);
        }
        //添加总站
        $www = array(
                "key"=>"",
                "child"=>array(
                       array(
                        "cname"=>"总站",
                        "cid"=>"000001"
                            )
                        )
                    );
        array_unshift($cs,$www);
        $all = array(
                "key"=>"",
                "child"=>array(
                       array(
                        "cname"=>"全部",
                        "cid"=>""
                            )
                        )
                   );

        array_unshift($cs,$all);
        $this->assign("cs",$cs);
        $city = "";
        $status = '';
        if(isset($_GET["city"]) && $_GET["city"] !==""){
            $this->assign("city",$_GET["city"]);
            $city = $_GET["city"];
        }

        if(!empty($_GET["type"])){
            $type = $_GET["type"];
            $this->assign("type",$_GET["type"]);
        }

        if(!empty($_GET["url"])){
            $url = $_GET["url"];
        }

        if($_GET["status"] != ''){
            $status = intval($_GET["status"]);
        }


        if($_GET["linkpage"] != ''){
            $linkpage = trim($_GET["linkpage"]);
        }

        $pageIndex = 1;
        $pageCount = 10;

        if(!empty($_GET["count"])){
            $pageCount = intval($_GET["count"]);
        }

        //导出exel
        $export = I('get.export');
        if ($export == 1) {
            ini_set('memory_limit','256M');
            $allLinks = $this->getAllLinks($city,$type,$linkpage,$status,$url);
            if (empty($allLinks)) {
                $this->error('当前的导出无数据!');
            }
            $title = array();
            $title['cname'] = '城市名称';
            $title['link_page_name'] = '链接页面';
            $title['show_class'] = '推荐类型';
            $title['link_name'] = '链接名称';
            $title['link_url'] = '链接地址';
            $title['show_on'] = '推荐状态';
            $title['addtime'] = '添加时间';
            $title['city_url'] = '城市地址';
            array_unshift($allLinks, $title);
            //dump($allLinks);
            $dbeAction = A("Home/Downloadbigexcel");
            $dbeAction->downloadBigExcel($allLinks, 'Sheet1', "友情链接".date('Ymd'));
            die();
        }

        $links = $this->getLinks($city,$type,$pageIndex,$pageCount,$status,$url,$linkpage);
        $this->assign("links",$links);

        $linktype = $this->getLinkCategory();
        $this->assign("linktype", $linktype);
        $this->display();
    }

    /**
     * 友情链接可用性监测
     * @return void
     */
    public function monitor()
    {
        $cs             = I('get.cs');
        $link_page      = I('get.link_page');
        $monitor_status = I('get.monitor_status');
        $show_class     = 1;
        $pageIndex      = 1;
        $pageCount      = 50;
        $page           = I('get.page');
        if(!empty($page)){
            $pageIndex      = $page;
            $pageCount      = 1000;
            $main['info'] = $this->getLinksForDl($cs, $show_class, $pageIndex, $pageCount, '', '', $link_page, $monitor_status);
            $this->downExcel($main['info']);
            die;
        }
        $main['info'] = $this->getLinks($cs, $show_class, $pageIndex, $pageCount, '', '', $link_page, $monitor_status);
        $this->assign("category", $this->getLinkCategory());
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 编辑友情链接
     * @return void
     */
    public function operate()
    {
        $data = I('post.');
        if (!empty($data)) {
            $id = intval($data['id']);
            //新增
            if (empty($id)) {
                //查询该城市是设置了，类型是2 热门城市
                if($data["show_class"] == 2){
                    $res = D("Friendlink")->getLinksInfoByCity($data["cs"],$data["show_class"]);
                    if(count($res) > 0){
                        $this->ajaxReturn(array("info"=>"该城市推荐已添加,请勿重复添加！","status"=>0));
                    }
                }elseif($data["show_class"] == 4){
                    $res = D("Friendlink")->getLinksInfoByCity($data["cs"],$data["show_class"]);
                    if(count($res) > 0){
                        $this->ajaxReturn(array("info"=>"该城市附近城市已添加,请勿重复添加，可选择编辑功能~","status"=>0));
                    }
                }elseif($data["show_class"] == 1){
                    //分站装修公司列表页，可以添加友情链接
                    if($data["cs"] != "000001" && !empty($data["link_page"]) && ($data["link_page"] != 'company-list') && ($data["link_page"] != 'xgt')){
                        $this->ajaxReturn(array("info"=>"分站不支持该类型友情链接！","status"=>0));
                    }
                }elseif($data["show_class"] == 3){
                    //只有主站可以添加
                    if($data["cs"] != "000001" || empty($data["link_page"])){
                        $this->ajaxReturn(array("info"=>"分站不支持该类型链接！","status"=>0));
                    }
                }else{
                    $this->ajaxReturn(array("info"=>"未知的分类类型！","status"=>0));
                }
                $save = array(
                    "cs"         =>$data["cs"],
                    "link_name"  =>$data["link_name"],
                    "link_url"   =>$data["link_url"],
                    "show_cs"    =>$data['show_cs'],
                    "show_class" =>$data["show_class"],
                    "link_page"  =>$data["link_page"],
                    "show_on"    =>1,
                    "addtime"    =>date("Y-m-d H:i:s")
                );
                $res = D("Friendlink")->addLink($save);
                if($res !== false){
                    $this->ajaxReturn(array("status"=>1));
                }
                $this->ajaxReturn(array("info"=>"添加友情链接失败", "status"=>0));
            } else {
                $save = array(
                    "link_name" =>$data["link_name"],
                    "link_url"  =>$data["link_url"],
                    "show_cs"   =>$data['show_cs'],
                    "link_page" =>$data["link_page"],
                    "show_on"   =>1
                );
                $res = D("Friendlink")->editLink($id,$save);
                if($res !== false){
                    $this->ajaxReturn(array("status"=>1));
                }
                $this->ajaxReturn(array("info"=>"编辑友情链接失败", "status"=>0));
            }
            $this->ajaxReturn(array("info"=>"未知错误", "status"=>0));
        }
        $id = I('get.id');
        $main['info'] = D("Friendlink")->getLinksInfoById($id);
        $this->assign("main",$main);
        $this->assign("category", $this->getLinkCategory());
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->display();
    }

    /**
     * 获取链接分类
     * @return array 链接分类
     */
    public function getLinkCategory()
    {
        $category = S('Friendlink:Category:getLinkCategory');
        if(empty($category)){
            $categorys = M('friend_link_category')->select();
            foreach ($categorys as $key => $value) {
                $category[$value['link_page']] = $value['link_page_name'];
            }
            S('Friendlink:Category:getLinkCategory',$category,900);
        }
        return $category;
    }

    /**
     * 获取所有友情链接数据
     * @param  string $city 城市id
     * @param  int    $type 链接类型
     * @param  string $linkpage 链接页面
     * @param  int    $status 推荐状态
     * @param  string $url 链接地址
     * @return array
     */
    private function getAllLinks($city='',$type='',$linkpage='',$status='',$url=''){
       return  D("Friendlink")->getAllLinks($city,$type,$linkpage,$status,$url);
    }

    /**
     * 获取热门链接
     * @return [type] [description]
     */
    public function getLinks($city,$type,$pageIndex,$pageCount,$status,$url="",$linkpage,$monitor_status){
        $count =  D("Friendlink")->getLinksListCount($city,$type,$status,$url,$linkpage,$monitor_status);
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $page = $p->show();
            $list = D("Friendlink")->getLinksList($city,$type,$p->firstRow,$p->listRows,$status,$url,$linkpage,$monitor_status);
            return array("list"=>$list,"page"=>$page);
        }
        return null;
    }

    /**
     * 获取热门链接(下载时使用，分页和页码不一样)
     * @return [type] [description]
     */
    public function getLinksForDl($city,$type,$pageIndex,$pageCount,$status,$url="",$linkpage,$monitor_status){
        $count =  D("Friendlink")->getLinksListCount($city,$type,$status,$url,$linkpage,$monitor_status);
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $page = $p->show();
            $list = D("Friendlink")->getLinksList($city,$type,($pageIndex-1)*$pageCount,$pageCount,$status,$url,$linkpage,$monitor_status);
            return array("list"=>$list,"page"=>$page);
        }
        return null;
    }

    /**
     * 禁用链接
     * @return [type] [description]
     */
    public function delete(){
        if($_POST){
            $id = $_POST["id"];
            $data = array(
                "show_on"=>0
            );
            $res = D("Friendlink")->editLink($id,$data);
            if($res !== false){
                $this->ajaxReturn(array("data"=>'',"info"=>"操作成功","status"=>1));
            }
            $this->ajaxReturn(array("data"=>'',"info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    /**
     * 禁用链接
     * @return [type] [description]
     */
    public function shownow(){
        if($_POST){
            $id = $_POST["id"];
            $data = array(
                    "show_on"=>1
                          );
            $res = D("Friendlink")->editLink($id,$data);
            if($res !== false){
                $this->ajaxReturn(array("data"=>'',"info"=>"操作成功","status"=>1));
            }
            $this->ajaxReturn(array("data"=>'',"info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    /**
     * 删除链接
     * @return [type] [description]
     */
    public function remove(){
        $id = I('get.id');
        if(empty($id) || !is_numeric($id)){
            $this->ajaxReturn(array("info"=>"数据错误！","status"=>0));
        }
        if (D("Friendlink")->remove($id)){
            $this->ajaxReturn(array("info"=>"删除成功！","status"=>1));
        }else{
            $this->ajaxReturn(array("info"=>"删除失败！","status"=>0));
        }
    }

    /**
     * 禁用链接
     * @return [type] [description]
     */
    public function deleteAll(){
        $ids = trim($_POST['ids']);
        $map['link_id'] = array('IN',$ids);
        $result = M("friend_link")->where($map)->save(array('show_on' => '0'));
        if($result){
            $this->ajaxReturn(array("data"=>'',"info"=>"禁用成功！","status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>'',"info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    /**
     * 删除链接
     * @return [type] [description]
     */
    public function removeAll(){
        $ids = trim($_POST['ids']);
        $map['link_id'] = array('IN',$ids);
        $result = M("friend_link")->where($map)->delete();
        if($result){
            $this->ajaxReturn(array("data"=>'',"info"=>"删除成功！","status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>'',"info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    /**
     * 查询城市信息
     * @return [type] [description]
     */
    public function findCityInfo(){
        if($_POST){
            $name = trim($_POST["query"]);
            $limit = $_POST["matchCount"];
            $citys = D("Area")->getCityInfoByName($name,$limit);
            if(count($citys)>0){
                $this->ajaxReturn(array("data"=>$citys,"info"=>"","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>'',"info"=>"","status"=>0));
            }
        }
    }

    /**
     * 添加友情链接
     */
    public function add(){
        if($_POST){
            $city ="";
            foreach ($_POST["citys"] as $key => $value) {
               $city .=$value.",";
            }

            switch ($_POST["type"]){
                case '1':
                    //分站装修公司列表页，可以添加友情链接
                    if($_POST["cs"] != "000001" && !empty($_POST["page"]) && ($_POST["page"] != 'company-list') && ($_POST["page"] != 'xgt')){
                        $this->ajaxReturn(array("data"=>'',"info"=>"分站不支持该类型友情链接！","status"=>0));
                    }
                    //检查链接地址是否为空
                    if (empty($_POST["url"])) {
                        $this->ajaxReturn(array("info"=>"链接地址为空", "status"=>0));
                    }
                    //检查友情链接地址的唯一性
                    $temp = D('Friendlink')->getFriendLinkByLinkUrl($_POST["url"]);
                    if (!empty($temp)) {
                        $this->ajaxReturn(array("info"=>"链接地址“".$_POST["url"]."”已存在", "status"=>0));
                    }
                    break;
                case '2':
                    $res = D("Friendlink")->getLinksInfoByCity($_POST["cs"],$_POST["type"]);
                    if(count($res) > 0){
                        $this->ajaxReturn(array("data"=>'',"info"=>"该城市推荐已添加,请勿重复添加！","status"=>0));
                    }
                    break;
                case '3':
                    //只有主站可以添加
                    if($_POST["cs"] != "000001" || empty($_POST["page"])){
                        $this->ajaxReturn(array("data"=>'',"info"=>"分站不支持该类型链接！","status"=>0));
                    }
                    break;
                case '4':
                    $res = D("Friendlink")->getLinksInfoByCity($_POST["cs"],$_POST["type"]);
                    if(count($res) > 0){
                        $this->ajaxReturn(array("data"=>'',"info"=>"该城市附近城市已添加,请勿重复添加，可选择编辑功能~","status"=>0));
                    }
                    break;
                case '5':
                    //检查友情链接名称的唯一性
                    $temp = D('Friendlink')->getFriendLinkByLinkName($_POST["name"],5);
                    if (!empty($temp)) {
                        $this->ajaxReturn(array("info"=>"链接名称“".$_POST["name"]."”已存在", "status"=>0));
                    }
                    break;
                default :
                    $this->ajaxReturn(array("data"=>'',"info"=>"未知的分类类型！","status"=>0));
            }

            $data = array(
                "cs"=>$_POST["cs"],
                "link_name"=>$_POST["name"],
                "link_url"=>$_POST["url"],
                "show_cs"=>$city,
                "show_class"=>$_POST["type"],
                "show_on"=>1,
                "link_page"=>$_POST["page"],
                "adminuser_id" => getAdminUser('id'),
                "addtime"=>date("Y-m-d H:i:s")
            );
            $res = D("Friendlink")->addLink($data);
            if($res !== false){
                $this->ajaxReturn(array("data"=>'',"info"=>"","status"=>1));
                die();
            }
            $this->ajaxReturn(array("data"=>'',"info"=>"添加友情链接失败","status"=>0));
        }else{
            //页面数组
            $linktype = S('Friendlink:Category:Array');
            if(empty($result)){
                $categorys = M('friend_link_category')->select();
                foreach ($categorys as $key => $value) {
                    $linktype[] = array('name' => $value['link_page_name'],'page' => $value['link_page']);
                }
                S('Friendlink:Category:Array',$result,36000);
            }
            $this->assign("pages",$linktype);
            //获取推荐城市
            $citys = D("Area")->getOpenCtiys();
            $this->assign("citys",$citys);
            $tmp = $this->fetch("link");
            $this->ajaxReturn(array("data"=>array("tmp"=>$tmp,"title"=>"添加链接"),"info"=>"添加链接","status"=>1));
        }
    }

    /**
     * 编辑链接
     * @return [type] [description]
     */
    public function edit(){
        if($_POST){
            $id = $_POST["id"];
            $city ="";
            foreach ($_POST["citys"] as $key => $value) {
               $city .=$value.",";
            }
            $data = array(
                "link_name"=>$_POST["name"],
                "link_url"=>$_POST["url"],
                "show_cs"=>$city,
                "link_page"=>$_POST["page"],
                "adminuser_id" => getAdminUser('id'),
                "show_on"=>1
            );

            $info = D("Friendlink")->getLinksInfoById($id);
            if ($info['show_class'] == 1) {
                //检查友情链接地址的唯一性
                $temp = D('Friendlink')->getFriendLinkByLinkUrl($_POST["url"]);
                if ((count($temp) > 2) || (!empty($temp) && ($temp[0]['link_id'] != $id) && ($data['link_url'] == $temp[0]['link_url']))) {
                    $this->ajaxReturn(array("info"=>"链接地址“".$_POST["url"]."”已存在","status"=>0));
                }
            }

            $res = D("Friendlink")->editLink($id,$data);
            if($res !== false){
                $this->ajaxReturn(array("data"=>'',"info"=>"操作成功","status"=>1));
            }
            $this->ajaxReturn(array("data"=>'',"info"=>"操作失败,请联系技术部门！","status"=>0));
        }else{
             //页面数组
            $linktype = S('Friendlink:Category:Array');
            if(empty($result)){
                $categorys = M('friend_link_category')->select();
                foreach ($categorys as $key => $value) {
                    $linktype[] = array('name' => $value['link_page_name'],'page' => $value['link_page']);
                }
                S('Friendlink:Category:Array',$result,36000);
            }
            $this->assign("pages",$linktype);
            $id = $_GET["id"];
            $link = D("Friendlink")->getLinksInfoById($id);
            $this->assign("link",$link);
            //获取推荐城市
            $citys = D("Area")->getOpenCtiys();
            $this->assign("citys",$citys);
            $tmp = $this->fetch("link");
            $this->ajaxReturn(array("data"=>array("tmp"=>$tmp,"title"=>"编辑链接"),"info"=>"编辑链接","status"=>1));
        }
    }


    /**
     * 批量添加
     * @return [type] [description]
     */
    public function batch(){
        if($_POST){
            $data = $_POST["data"];
            //如果是字符串，进行json解析
            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            if (!empty($data)) {

                $systemUrl = $systemDomain = $addUrl = $addDomain = array();

                //获取链接唯一性数组和域名-城市二维数组
                $temp = M("friend_link")->field('link_name, link_url, cs')->where(array('show_class' => 1))->select();
                $urlArray = $domainArray = array();
                foreach ($temp as $key => $value) {
                    $urlArray[trim($value['link_url'])] = 1;
                    $domainArray[get_domain($value['link_url'])][$value['cs']] = 1;
                }
                unset($temp);

                //遍历要插入的数据
                foreach ($data as $key => $value) {
                    //获取域名
                    $domain = get_domain($value["link_url"]);

                    //检查对比系统友情链接地址的唯一性
                    if (!empty($urlArray[$value['link_url']])) {
                        $systemUrl[trim($value['link_url'])][] = $key;
                    }
                    //检查对比系统单个分站和主站下域名唯一性
                    if (!in_array($domain, array('qizuang.com'))) {
                        if (!empty($domainArray[$domain][$value['cs']])) {
                            $systemDomain[$domain][$value['cs']][] = $key;
                        }
                    }

                    //记录新增的链接中相同的
                    $addUrl[trim($value['link_url'])][] = $key;
                    //记录新增的链接地址同一个站下的域名重复的
                    if (!in_array($domain, array('qizuang.com'))) {
                        $addDomain[$domain][$value['cs']][] = $key;
                    }

                    //添加操作人ID
                    $data[$key]['adminuser_id'] = getAdminUser('id');
                    //为后续插入数据删除城市名字段
                    unset($data[$key]["cname"]);
                }

                $checkMessage = '';
                //验证对比系统链接唯一
                if (!empty($systemUrl)) {
                    $checkMessage = $checkMessage . '系统中已存在与数据';
                    foreach ($systemUrl as $key => $value) {
                        $checkMessage = $checkMessage . implode(',', $value) . ',';
                    }
                    $checkMessage = rtrim($checkMessage, ',') . '重复的链接地址。<br><br>';
                }

                //验证新增链接中链接地址唯一性
                if (!empty($addUrl)) {
                    $addUrlMessage = '';
                    foreach ($addUrl as $key => $value) {
                        if (count($value) > 1) {
                            $addUrlMessage = $addUrlMessage . '[' . implode(',', $value) . ']' . ' ';
                        }
                    }
                    if (!empty($addUrlMessage)) {
                        $checkMessage = $checkMessage . '数据' . rtrim($addUrlMessage) . '存在重复的链接地址。<br><br>';
                    }
                }

                //验证不通过则返回
                if (!empty($checkMessage)) {
                    $this->ajaxReturn(array("info"=> $checkMessage, "status"=>0));
                }

                $i = D("Friendlink")->addAllLink($data);
                if($i !== false){
                    $this->ajaxReturn(array("data"=>'',"info"=>"操作成功","status"=>1));
                }
            }
            $this->ajaxReturn(array("data"=>'',"info"=>"批量导入失败！","status"=>0));
        }else{
            $linktype = S('Friendlink:Category:Array');
            if(empty($result)){
                $categorys = M('friend_link_category')->select();
                foreach ($categorys as $key => $value) {
                    $linktype[] = array('name' => $value['link_page_name'],'page' => $value['link_page']);
                }
                S('Friendlink:Category:Array',$result,36000);
            }
            $this->assign("linktypes",$linktype);
            $this->display();
        }
    }


    /**
     * 预览
     * @return [type] [description]
     */
    public function preview(){
        if($_POST){
            $fileType = explode(".", $_FILES["file_data"]["name"]);
            $ext = $fileType[1];
            $filePath =  dirname(dirname(dirname(dirname(__FILE__))))."/upload/";
            if(!is_dir($filePath)){
                mkdir($filePath,0777);
            }
            $path = $_FILES["file_data"]["tmp_name"];
            $filePath = $filePath.time().".".$ext;
            move_uploaded_file($path, $filePath);
            $action = A("Export");
            $data = $action->loadFile($filePath,$ext);
            $result = array();
            foreach ($data as $key => $value) {
                //如果上传的四个参数都为空，则过滤掉
                if (empty($value['link_name']) && empty($value['link_url']) && empty($value['link_page']) && empty($value['cname'])) {
                    continue;
                }
                $result[] = $value;
            }
            $this->ajaxReturn(array("data"=>$result,"info"=>"操作成功","status"=>1));
        }
    }

    //下载Excel
    public function downExcel($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头
        $title = array(
            '编号',
            '添加时间',
            '站点',
            '链接页面',
            '锚文本',
            '链接',
            '对方锚文本',
            '对方链接',
            '当前状态',
            '添加用户',
            '刷新时间',
            '总计',
            count($list['list']).'条'
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        //设置表内容
        $j = 1;
        foreach ($list['list'] as $k => $v) {
            //初始化$i
            $i = 0;
            $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $phpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            $phpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $phpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $phpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $phpExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $phpExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            //编号
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['link_id']);
            //添加时间
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['addtime']);
            //站点
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cname']);
            //链接页面
            $categorys  = $this->getLinkCategory();
            $category   = $categorys[$v['link_page']];
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$category);
            //锚文本
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['link_name']);
            //链接
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['link_url']);
            //对方锚文本
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['monitor_text']);
            //对方链接
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['monitor_href']);
            //当前状态
            if($v['monitor_status'] == 1){
                $status = '请求出错';
            }elseif($v['monitor_status'] == 2){
                $status = '对方链接正常';
            }elseif($v['monitor_status'] == 3){
                $status = '对方无链接';
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$status);
            //对方链接
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['adminuser_name']);
            //刷新时间
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['monitor_time']);
            $j++;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="友情链接可用性监测.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }



}