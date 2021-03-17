<?php
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class ZbfbController extends ApiBaseController {
    private $data = null;
    public function _initialize(){
        //检测请求的域名是否合法
        //合法的域名数组
        //因为兼容IE跨域提交,所以没有使用http_referer
        $register_url = C("REGISTER_URL");
        $referer= trim($_SERVER['HTTP_ORIGIN']);
        if(in_array($referer,$register_url) || preg_match('/([A-za-z])+(.[A-za-z]+)?.'.APP_HTTP_DOMAIN.'/i', $referer)){
            header("Access-Control-Allow-Credentials:true");
            header('Access-Control-Allow-Origin:'.$referer);
            if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
                $exp = explode("&",$GLOBALS['HTTP_RAW_POST_DATA']);
                $exp = array_filter($exp);
                $data = array();
                foreach ($exp as $key => $value) {
                   $e = explode("=", $value);
                   $data[$e[0]] = $e[1];
                }
                $this->data = $data;
                $ssid = urldecode($data["ssid"]);
                if(isset($data["action"])){
                    $action = $data["action"];
                }
                if(isset($data["cs"])){
                    $cs = $data["cs"];
                }
                if(isset($data["des"])){
                    $des = $data["des"];
                }
            }else{
                $ssid = $_POST["ssid"];
                $action = $_POST["action"];
                $cs = $_POST["cs"];
                $des = $_POST["des"];
            }

            if(!empty($ssid)){
                $ssid = authcode($ssid);
                session_id($ssid);
            }
            session_start();
            if($action =="load"){
                //安全验证码
                $safe = getSafeCode();
                $this->assign("safecode",$safe["safecode"]);
                $this->assign("safekey",$safe["safekey"]);
                //session_id
                $this->assign("ssid",$safe["ssid"]);
                $this->assign("des",$des);
                // $this->assign("ssid",authcode(session_id(),""));
                $this->assign("cityid",$cs);
            }
         }else{
            echo json_encode(array('data'=>"","info"=>"不合法的请求,请刷新页面！","status"=>0));
            die();
         }
    }
    /**
     * 订单发布订单
     * @return [type] [description]
     */
    public function fb_order(){
        if(!empty($this->data)){
            $source = $this->data;
        }elseif ($_POST) {
            $source = $_POST;
        }else{
            die();
        }
        $this->saveOrder($source);
    }



    /**
     * 带验证码的订单发布
     * @return [type] [description]
     */
    public function fb_order_safe(){
        if(!empty($this->data)){
            $source = $this->data;
        }elseif ($_POST) {
            $source = $_POST;
        }else{
            die();
        }
        /*if(!check_verify($source["code"])){
            //重新设置安全码
            //安全验证码
            $safe =getSafeCode();
            $arr["safecode"] = $safe["safecode"];
            $arr["safekey"] = $safe["safekey"];
            $arr["ssid"] = $safe["ssid"];
            $this->ajaxReturn(array("data"=>$arr,"info"=>"验证码输入错误,请重新输入","status"=>0));
            die();
        }*/
        $this->saveOrder($source);
    }

    /**
     * 保存订单
     * @return [type] [description]
     */
    public function saveOrder($source){
        //重新设置安全码
        //安全验证码
        $safe =getSafeCode();
        $arr["safecode"] = $safe["safecode"];
        $arr["safekey"] = $safe["safekey"];
        $arr["ssid"] = $safe["ssid"];

        //涉及到跨域提交,需验证安全码
        if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
            //IE需要将提交的数据URL解析下
            $safecode = urldecode($source["safecode"]);
            $safekey = authcode(urldecode($source["safekey"]));
            //如果是IE6,7,8的订单，URLDECODING
            foreach ($source as $key => $value) {
               $source[$key] = urldecode($value);
            }
        }else{
            $safecode = $source["safecode"];
            $safekey = authcode($source["safekey"]);
        }
        $code = $_SESSION[$safekey];
        if($code == "" || authcode($safecode,'DECODE') != $code){
            echo  json_encode(array("data"=>$arr,"info"=>"您没有通过验证,请重新提交！","status"=>0));
            die();
        }

        //如果提交的时候有验证码需要验证码验证
        if(isset($source["verifycode"])){
            if(!check_verify($source["verifycode"])){
                $this->ajaxReturn(array("data"=>$arr,"info"=>"验证码错误","status"=>0));
                die();
            }
        }


        //如果登录状态下,装修公司/设计师不允许发单
        if(isset($_SESSION["u_userInfo"])){
            if($_SESSION["u_userInfo"]["classid"] != 1){
                $this->ajaxReturn(array("data"=>"" ,"info"=>"您无法提交订单！","status"=>0));
                die();
            }
        }

        $data   = array(
                'time'      => time(),
        );

        //如果是微信中访问 发单的 就记下来
        if( false !== strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") ) {
            $data['source_in'] = 10; //标记微信10
        }

        import('Library.Org.Util.App');

        $app = new \App();
        $data['ip'] = $app->get_client_ip();

        if(!empty($source["cs"])){
            $data['cs'] = remove_xss($source["cs"]);
        }

        if(!empty($source["qx"])) {
            $data['qx'] =remove_xss($source["qx"]);
        }

        if(!empty($source["name"])) {
            $data['name'] = remove_xss($source["name"]);
        }

        if(!empty($source["sex"])) {
            $data['sex'] = remove_xss($source["sex"]);
        }

        if(!empty($source["tel"])) {
            $data['tel'] = remove_xss($source["tel"]);
        }

        if(!empty($source["mianji"])) {
            $data['mianji'] = remove_xss($source["mianji"]);
        }

        if(!empty($source["text"])) {
            $data['text'] = remove_xss($source["text"]);
        }

        if(!empty($source["lx"])) {
            $data['lx'] = remove_xss($source["lx"]);
        }

        if(!empty($source["lxs"])) {
            $data['lxs'] = remove_xss($source["lxs"]);
        }

        if(!empty($source["huxing"])) {
            $data['huxing'] = remove_xss($source["huxing"]);
        }

        if(!empty($source["fengge"])) {
            $data['fengge'] = remove_xss($source["fengge"]);
        }

        if(!empty($source["xiaoqu"])) {
            $data['xiaoqu'] = remove_xss($source["xiaoqu"]);
        }

        if(!empty($source["fangshi"])) {
            $data['fangshi']  = remove_xss($source["fangshi"]);
        }

        if(!empty($source["order_id"])) {
            $data['order_id'] = remove_xss($source["order_id"]);
        }

        if(!empty($source["yusuan"])) {
            $data['yusuan']   = remove_xss($source["yusuan"]);
        }

        if(!empty($source["zxdc"])){
            $data['zxdc'] = remove_xss($source["zxdc"]);
        }

        if(!empty($source["shi"])){
            $data['shi'] = remove_xss($source["shi"]);
        }

        if(!empty($source["ting"])){
            $data['ting'] = remove_xss($source["ting"]);
        }

        if(!empty($source["wei"])){
            $data['wei'] = remove_xss($source["wei"]);
        }

        if(!empty($source["chu"])){
            $data['chu'] = remove_xss($source["chu"]);
        }

        if(!empty($source["yangtai"])){
            $data['yangtai'] = remove_xss($source["yangtai"]);
        }

        if(!empty($source["yusuan"])){
            $data['yusuan'] = remove_xss($source["yusuan"]);
        }

        if(!empty($source["source"])){
            $data['source'] = remove_xss($source["source"]);
        }

        if(!empty($source["des"])){
            $data['des'] = remove_xss($source["des"]);
        }

        if(!empty($source["lftime"])){
            $data['lftime'] = remove_xss($source["lftime"]);
        }

        if(!empty($source["lftime"])){
            $data['lftime'] = remove_xss($source["lftime"]);
        }

        if(!empty($source["start"])){
            $data['start'] = remove_xss($source["start"]);
        }

        if(!empty($source["userid"])){
            $data['userid'] = remove_xss($source["userid"]);
        }


        $oid = authcode($source["orderid"]);
        //如果带有
        if(!empty($oid)){
            $orderid = $data["orderid"] = $oid;
            $ordersave  = D('Common/Orders')->orderpublish($data,"update"); //传入去修改完善订单
        }else{
            //单ip每天发布不得超过10条
            $chkip = D('Common/Orders')->order_chk_ip($source["ip"]);
            if ($chkip['status'] == 0) {
                $this->ajaxReturn(array('data'=>"","info"=>$chkip['info'],"status"=>0));
                exit();
            }

            //判断 网络蜘蛛
            if ($app->GetRobot()) {
                $this->ajaxReturn(array('data'=>"","info"=>'robot not access！',"status"=>0));
                exit();
            }

            //简单判断电话号码 允许数字和短杠的组合  7-18位
            $chktel = D('Common/Orders')->order_chk_tel($data['tel']);
            if ($chktel['status'] == 0) {
                $this->ajaxReturn(array('data'=>"","info"=>$chktel['info'],"status"=>0));
                exit();
            }
            //如果24小时内，该ip，该城市发布过订单，且订单为未审核状态
            //返回这个发布号码的单号
            $chkhistory = D('Common/Orders')->order_chk_history($data['tel'], $data['cs']);

            if($chkhistory != null){
                //如果本手机24小时内发布过单子, 完善订单
                $orderid = $data['orderid'] = $chkhistory; //历史订单id号
                $ordersave  = D('Common/Orders')->orderpublish($data,"update"); //传入去修改完善订单

            }else{
                //单子入库 新增插入
                $orderid = D('Common/Orders')->orderpublish($data,"insert"); //传入插入新订单
                if($orderid !== false){
                    $ordersave = true;
                    //新单子 发送通知业主短信
                    //发送订单申请成功 短信
                    $sms_tel = $data['tel'];
                    if (11 == strlen($sms_tel)) {
                        //如果是11位号码
                        //导入扩展文件
                        import('Library.Org.Util.App');
                        $app = new \App();
                        $phonesms  = str_replace('{{tel}}', substr($sms_tel,0,3) .'*****'
                                                 .  substr($sms_tel,-3), OP('SMS_ORDERFB1'));
                        $app->SmsSend($sms_tel,$phonesms);
                    }
                }
            }
        }
        unset($_SESSION[$safekey]);

        //如果操作成功 返回true
        if($ordersave){
            //设置订单的cookie,有效期15分钟
            $orderid =$orderid;
            $time = time();
            setcookie("w_qizuang_n",$orderid,$time+1800,'/', '.'.APP_HTTP_DOMAIN);
            switch ($source["step"]) {
                case '1':
                    //获取发单成功页面第一步
                    //获取预算列表
                    $jiage = D("Jiage")->getJiage();
                    $step["jiage"] = $jiage;
                    //获取类型列表
                    $hx = D("Huxing")->gethx();
                    $step["hx"] = $hx;
                    //获取方式列表
                    $fs = D("Fangshi")->getfs();
                    $step["fs"] = $fs;
                    $step["safecode"] = $safe["safecode"];
                    $step["safekey"] = $safe["safekey"];
                    $step["ssid"] = $safe["ssid"];
                    $step["orderid"] = $orderid;
                    $step["mianji"] = $data["mianji"];
                    $this->assign("step",$step);

                    $t = T("setp1");
                    $fetch = $this->fetch($t);
                    $arr["tmp"] = $fetch;
                    break;
                case '2':
                    //获取发单成功页面第二步
                    $t = T("step2");
                    $step["ssid"] = $safe["ssid"];
                    $this->assign("step",$step);
                    $fetch = $this->fetch($t);
                    $arr["tmp"] = $fetch;
                    break;
            }
            $this->ajaxReturn(array("data"=>$arr,"info"=>"","status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>$arr,"info"=>"发布失败！","status"=>0));
        }
    }
    /**
     * 设置获取报价弹层显示
     * @return [type] [description]
     */
    public function setwindowswitch(){
        //设置cookie,时间24小时
        setcookie("w_index",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
        $this->ajaxReturn(array("aaa"));
    }

    /**
     * 刷新验证码
     * @return [type] [description]
     */
    public function refresh(){
        $safe =getSafeCode();
        $this->ajaxReturn(array("data"=>$safe,"info"=>"","status"=>1));
    }



    public function dispatcher(){
        if(empty($this->data)){
            $type = $_POST["type"];
            $cid = $_POST["cid"];
        }else{
            $type = $this->data["type"];
            $cid = $this->data["cid"];
        }
        //如果公司编号cid不为空,则添加公司的咨询量
        if(!empty($cid)){
            D("Common/User")->editUvAndPv($cid,"uv");
        }

        if(!empty($type)){
            switch ($type) {
                case 'sj':
                    $tmp = $this->zbsj();
                break;
                case 'bj':
                    $tmp = $this->zxbj();
                break;
                case 'zx':
                    $tmp = $this->zbzx();
                break;
                case 'ys':
                    $tmp = $this->zbys();
                break;
                case 'lf':
                    $tmp = $this->zblf();
                break;
                case 'fb':
                    $tmp = $this->zbfb();
                break;
                case 'sms':
                    if(!empty($this->data)){
                        $data = $this->data;
                    }elseif ($_POST) {
                        $data = $_POST;
                    }
                    $tmp = $this->sms($data["savedata"]);
                break;
                case 'zxfb':
                    if(!empty($this->data)){
                        $data = $this->data;
                    }elseif ($_POST) {
                        $data = $_POST;
                    }
                    $tmp = $this->zxfb($data["savedata"]);
                break;
            }
            if(!empty($tmp)){
                $this->ajaxReturn(array("data"=>$tmp,"info"=>"abc","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"非法提交!","status"=>0));
    }

    /**
     * 订单结果反馈信息
     * @return [type] [description]
     */
    public function feedback(){
        if($_POST){
            if(!empty($_COOKIE["w_qizuang_n"])){
                $orderid = $_COOKIE["w_qizuang_n"];
                // if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                //     $orderid = urldecode($_COOKIE["w_qizuang_n"]);
                // }
                $orderid = authcode($orderid,"DECODE");
                $data = array(
                    "feedback"=>I("post.feedback"),
                    "orderid"=>$orderid,
                    "time"=>time()
                              );
                D("Orderfeedback")->setOrderFeed($data);
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
        }
    }

    /**
     * 获取订单报价
     * @return [type] [description]
     */
    public function getZbPrice(){
        if(!empty($_COOKIE["w_qizuang_n"])){
            $orderid = $_COOKIE["w_qizuang_n"];
            // if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
            //     $orderid = urldecode($_COOKIE["w_qizuang_n"]);
            // }
            $orderid = authcode($orderid,"DECODE");
            //查询订单信息
            $order = D("Orders")->getOrderInfoById($orderid);
            unset($order['tel8']);
            if(count($order) > 0){
                //计算订单报价
                $mianji = $order["mianji"];
                $zxdc = $order["zxdc"];
                //根据面积计算房屋类型
                $house = array(
                            "shi"=>1,
                            "ting"=>1,
                            "wei"=>1,
                            "yangtai"=>1,
                            "chu"=>1,
                            "huxing"=>"38"
                                   );
                if($mianji >=60 && $mianji <90){
                    $house["shi"]=2;
                    $house["huxing"]="39";
                }elseif($mianji >=90 && $mianji <100){
                    $house["shi"]=3;
                    $house["huxing"]="46";
                }elseif($mianji >=100 && $mianji <120){
                    $house["shi"]=3;
                    $house["ting"]=2;
                    $house["huxing"]="47";
                }elseif($mianji >=120 && $mianji <130){
                    $house["shi"]=3;
                    $house["ting"]=2;
                    $house["wei"]=2;
                    $house["huxing"]="43";
                }elseif($mianji >=130 && $mianji <150){
                    $house["shi"]=4;
                    $house["ting"]=2;
                    $house["wei"]=2;
                    $house["yangtai"]=2;
                    $house["huxing"]="48";
                }elseif($mianji >=150 && $mianji <200){
                    $house["shi"]=5;
                    $house["ting"]=2;
                    $house["wei"]=2;
                    $house["yangtai"]=3;
                    $house["huxing"]="49";
                }else{
                    $house["shi"]=6;
                    $house["ting"]=2;
                    $house["wei"]=3;
                    $house["yangtai"]=3;
                    $house["huxing"]="50";
                }
                //更新订单的室厅卫厨阳台
                $result = D("Orders")->editOrder($orderid,$house);
                if($result !== false){
                    unset($house["huxing"]);
                    //获取价格
                    $data = $this->getZxInfo($house,$mianji,$zxdc,$orderid);

                    $this->ajaxReturn(array("data"=>$data,"info"=>"","status"=>1));
                }
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"获取订单报价失败,请刷新后再试！","status"=>0));
    }

    /**
     * 获取装修报价
     * @param  [type] $house [房屋信息]
     * @param  [type] $zxdc  [装修档次]
     * @return [type]        [description]
     */
    private function getZxInfo($house,$mianji,$zxdc,$orderid){
        //计算出房屋具体的装修位置
        $data = array();
        //计算出所有的房间
        for ($i = 1; $i <= $house["shi"]; $i++) {
            if($i == 1){
                array_push($data, "zw");
            }elseif($i == 2){
                array_push($data, "cw");
            }elseif($i == 3){
                array_push($data, "sf");
            }elseif($i == 4){
                array_push($data, "kw");
            }elseif($i == 5){
                array_push($data, "etws");
            }elseif($i == 6){
                array_push($data, "zwf");
            }
        }
        //计算出所有的厅
        for ($i = 1; $i <= $house["ting"] ; $i++) {
            if($i == 1){
                array_push($data, "kt");
            }elseif($i == 2){
                array_push($data, "ct");
            }
        }

        //计算卫生间
        for ($i = 1; $i <= $house["wei"] ; $i++) {
            if($i == 1){
                array_push($data, "wsj");
            }elseif($i == 2){
                array_push($data, "zwwsj");
            }else{
                array_push($data, "kwwsj");
            }
        }

        //计算厨房
        if($house["chu"] > 0){
            array_push($data, "cf");
        }
        //计算阳台
        for ($i = 1; $i <= $house["yangtai"] ; $i++) {
            switch ($i) {
                case '2':
                    array_push($data, "cyt");
                    break;
                default:
                     array_push($data, "yt");
                    break;
            }
        }
        //房屋对应的基础装修信息
        $zxwz = array(
            "kt"=>array(
                "id"=>"1",
                "name"=>"客厅",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"14",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"14",
                        "wd"=>"21"
                               )
                )
            ),
            "zw"=>array(
                "id"=>"3",
                "name"=>"主卧",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"13",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"13",
                        "wd"=>"21"
                               )
                )
            ),
            "cw"=>array(
                "id"=>"9",
                "name"=>"次卧",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"13",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"13",
                        "wd"=>"21"
                               )
                )
            ),
            "sf"=>array(
                "id"=>"11",
                "name"=>"书房",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"9",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"9",
                        "wd"=>"21"
                    )
                )
            ),
            "kw"=>array(
                "id"=>"5",
                "name"=>"客卧",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"9",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"9",
                        "wd"=>"21"
                               )
                )
            ),
            "etws"=>array(
                "id"=>"14",
                "name"=>"儿童房",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"9",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"9",
                        "wd"=>"21"
                               )
                )
            ),
            "zwf"=>array(
                "id"=>"15",
                "name"=>"杂物房",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"9",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"9",
                        "wd"=>"21"
                               )
                )
            ),
            "ct"=>array(
                "id"=>"2",
                "name"=>"餐厅",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"1",
                        "qm"=>"11",
                        "wd"=>"15"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"5",
                        "qm"=>"9",
                        "wd"=>"18"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"6",
                        "qm"=>"9",
                        "wd"=>"21"
                               )
                )
            ),
            "cf"=>array(
                "id"=>"7",
                "name"=>"厨房",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"27",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"31",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"31",
                        "qm"=>"37",
                        "wd"=>"41"
                               )
                )
            ),
            "wsj"=>array(
                "id"=>"4",
                "name"=>"卫生间",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"27",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"31",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"31",
                        "qm"=>"37",
                        "wd"=>"41"
                               )
                )
            ),
            "zwwsj"=>array(
                "id"=>"12",
                "name"=>"主卧卫生间",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"27",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"31",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"31",
                        "qm"=>"37",
                        "wd"=>"41"
                               )
                )
            ),
            "kwwsj"=>array(
                "id"=>"13",
                "name"=>"客卧卫生间",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"27",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"31",
                        "qm"=>"34",
                        "wd"=>"40"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"31",
                        "qm"=>"37",
                        "wd"=>"41"
                               )
                )
            ),
            "yt"=>array(
                "id"=>"8",
                "name"=>"阳台",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"43",
                        "qm"=>"53",
                        "wd"=>"56"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"45",
                        "qm"=>"49",
                        "wd"=>"56"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"47",
                        "qm"=>"51",
                        "wd"=>"55"
                               )
                )
            ),
            "cyt"=>array(
                "id"=>"16",
                "name"=>"次阳台",
                "child"=>array(
                    //简装
                    "1"=>array(
                        "dm"=>"43",
                        "qm"=>"53",
                        "wd"=>"56"
                               ),
                    //精装
                    "2"=>array(
                        "dm"=>"45",
                        "qm"=>"49",
                        "wd"=>"56"
                               ),
                    //豪装
                    "3"=>array(
                        "dm"=>"47",
                        "qm"=>"51",
                        "wd"=>"55"
                    )
                )
            ),

        );
        //获取房屋装修的对应装修信息
        foreach ($data as $key => $value) {
            foreach ($zxwz as $k => $val) {
                if($value == $k){
                    $zxInfo[$k]["id"] = $val["id"];
                    $zxInfo[$k]["name"] = $val["name"];
                    $zxInfo[$k]["child"] = $val["child"][$zxdc];
                }
            }
        }

        //全部的施工详细位置
        $details = D("Construction")->getDetails();
        //获取当前详细的装修位置
        $nowdetails = array();
        foreach ($zxInfo as $key => $value) {
            $nowdetails[$key]["id"] = $value["id"];
            $nowdetails[$key]["name"] = $value["name"];
            foreach ($details as $k => $val) {
                if($value["child"]["dm"] == $val["parentid"]){
                    $nowdetails[$key]["child"][] = $val;
                }
                if($value["child"]["qm"] == $val["parentid"] ){
                    $nowdetails[$key]["child"][] = $val;
                }
                if($value["child"]["wd"] == $val["parentid"] ){
                    $nowdetails[$key]["child"][] = $val;
                }
            }

        }

        //水电安装及其他项目清单信息
        foreach ($details as $key => $value) {
            if(empty($value["location"]) && $value["range"] == 0 && $value["parentid"] != 0){
                // $nowdetails[$value["parentid"]]["child"][] = $value;
                $nowdetails[$value["parentid"]]["id"] = $value["parentid"];
                $nowdetails[$value["parentid"]]["child"][] = $value;
            }
        }


        //施工价格信息
        $prices = D("Construction")->getPrices();
        //获取当前装修档次、风格的装修价格
        $nowprice = array();
        foreach ($prices as $key => $value) {
            //默认现代简约风格
            if($value["zxdc"] == $zxdc && 1 == $value["fengge"]){
                $nowprice[] = $value;
            }
        }

        $halfTotal = 0;
        //获取当前的施工项目的价格信息
        foreach ($nowdetails as $key => $value) {
            foreach ($value["child"] as $k => $val) {
                $result = $this->getDetailsPrice($nowprice,$val["id"],$value["width"],$value["length"],$val["fangshi"],$mianji);
                $nowdetails[$key]["child"][$k]["price"] = $result["price"];
                $nowdetails[$key]["child"][$k]["count"] = $result["count"];
                $nowdetails[$key]["child"][$k]["total"] = $result["total"];
                $nowdetails[$key]["total"] += $result["total"];
                $halfTotal += $result["total"];
            }
        }

        //自购主材
        //获取所有建材表
        $allTotal = 0;
        $materialsList = D("Construction")->getMaterials();
        foreach ($zxInfo as $key => $value) {
            foreach ($materialsList as $k => $val) {
                $locations = explode(',',$val["location"]);
                $locations = array_filter($locations);
                if(in_array($value["id"], $locations) && $val["group"] == 0){
                    $nowmaterials[$value["id"]]["total"] = 0;
                }

                if(in_array($value["id"], $locations) && $val["group"] != 0){
                    $nowmaterials[$value["id"]]["child"][] = $val;
                }
            }
        }

        //计算建材的价格明细
        foreach ($nowmaterials as $key => $value) {
            foreach ($value["child"] as $k => $val) {
                $result = $this->getMaterialsPrice($val["width"],$val["length"],$val["fangshi"],$val["price"]);
                $nowmaterials[$key]["child"][$k]["count"] = $result["count"];
                $nowmaterials[$key]["child"][$k]["total"] = $result["total"];
                $nowmaterials[$key]["total"] += $result["total"];
                $allTotal += $result["total"];
            }
        }


        //将订单产生的报价存入数据库中
        foreach ($nowdetails as $key => $value) {
             foreach ($value["child"] as $k => $val) {
                if(!array_key_exists($value["id"].$val["range"], $subData)){
                    $subData[$value["id"].$val["range"]] = array(
                            "orderid"=>$orderid,
                            "location"=>$value["id"],
                            "range"=>$val["range"],
                            "xm"=>$val["parentid"],
                            "zxdc"=>$zxdc,
                            "fengge"=>1,
                            "name"=>$value["name"],
                            "time"=>time()
                    );
                }

             }
        }

        foreach ($subData as $key => $value) {
            $saveData[] = $value;
        }
        // //查询该订单的报价是否存在
        // $count = D("Construction")->getOrderPriceListCount($orderid);
        // if($count > 0){
        //     //删除原有的报价
        //     D("Construction")->DeltetOrderPriceList($orderid);
        // }
        // $i = D("Construction")->addAllPriceList($saveData);
        if($i !== false){
             return array("halfTotal"=>$halfTotal,"allTotal"=>$allTotal);
        }
        return false;
    }

    //建材计算
     private function getMaterialsPrice($width,$length,$fangshi,$price){
        // 计算方式  1.长*宽  2. （长+宽）*2  3.(长+宽)*2*2.8  4房屋面积*1
        // 5.1厨房+1卫生间 6. 等于5 7. 等于 4  8. 等于3  9 等于 6  默认 0  表示 1
        //获取计算方式
        $result = array();
        $total = 0;
        $count = 0;
        switch ($fangshi) {
            case 0:
                $count = 1;
                $total = sprintf('%.2f', 1*$price);
                break;
            case 1:
                $count = sprintf('%.2f', ($width*$length));
                $total = sprintf('%.2f', ($width*$length)*$price);
                break;
            case 2:
                $count = sprintf('%.2f', ($width+$length)*2);
                $total = sprintf('%.2f', ($width+$length)*2*$price);
                break;
            case 3:
                $count = sprintf('%.2f', ($width+$length)*2*2.8);
                $total =  sprintf('%.2f', ($width+$length)*2*2.8*$price);
                break;
            case 4:
                $count = $mianji;
                $total =  sprintf('%.2f', $mianji*$price);
                break;
            case 5:
                $count = "按实际计算";
                $total = 0;
                break;
            case 6:
                $count = 5;
                $total = sprintf('%.2f', 5*$price);
                break;
            case 7:
                $count = 4;
                $total = sprintf('%.2f', 4*$price);
                break;
            case 8:
                $count = 3;
                $total = sprintf('%.2f', 3*$price);
                break;
            case 9:
                $count = 6;
                $total = sprintf('%.2f', 6*$price);
                break;
        }
        $result["count"] = $count;
        $result["total"] = $total;
        return $result;
    }

     //获取详细的价格明细
    private function getDetailsPrice($nowprice,$id,$width,$length,$fangshi=0,$mianji=0){
        // 计算方式  1.长*宽  2. （长+宽）*2  3.(长+宽)*2*2.8  4房屋面积*1
        // 5.1厨房+1卫生间 6. 等于5 7. 等于 4  8. 等于3  9 等于 6
        // 10. 等于 1+1  默认 0  表示 1
        $result = array();
        foreach ($nowprice as $price) {
            if($price["xm"] == $id){
                $result["price"] = $price["price"];
                //获取长和宽
                $width = empty($width) == true?$price["width"]:$width;
                $length = empty($length) == true?$price["length"]:$length;
                //获取计算方式
                $total = 0;
                $count = 0;
                switch ($fangshi) {
                    case 0:
                        $count = 1;
                        $total = sprintf('%.2f',1*$price["price"]);
                        break;
                    case 1:
                        $count = sprintf('%.2f', ($width*$length));
                        $total = sprintf('%.2f', ($width*$length)*$price["price"]);
                        break;
                    case 2:
                        $count = sprintf('%.2f', ($width+$length)*2);
                        $total = sprintf('%.2f', ($width+$length)*2*$price["price"]);
                        break;
                    case 3:
                        $count = sprintf('%.2f', ($width+$length)*2*2.8);
                        $total = sprintf('%.2f',($width+$length)*2*2.8*$price["price"]);
                        break;
                    case 4:
                        $count = $mianji;
                        $total = sprintf('%.2f',$mianji*$price["price"]);
                        break;
                    case 5:
                        $count = "按实际计算";
                        $total = 0;
                        break;
                    case 6:
                        $count = 5;
                        $total = sprintf('%.2f',5*$price["price"]);
                        break;
                    case 7:
                        $count = 4;
                        $total = sprintf('%.2f',4*$price["price"]);
                        break;
                    case 8:
                        $count = 3;
                        $total = sprintf('%.2f',3*$price["price"]);
                        break;
                    case 9:
                        $count = 6;
                        $total = sprintf('%.2f',6*$price["price"]);
                        break;
                    case 10:
                        $count = "1+1";
                        $total = sprintf('%.2f',2*$price["price"]);
                        break;
                }
                $result["width"] = $width;
                $result["length"] = $length;
                $result["count"] = $count;
                $result["total"] = $total;
            }
        }
        return $result;
    }

    private function sms($data){
        $isSave = 0;
        if(!empty($data)){
            $isSave = 1;
            $this->assign("source",json_encode($data));
        }
        $this->assign("isSave",1);
        $t = T("sms");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 获取发布的订单的弹窗
     * @return [type] [description]
     */
    private function zxfb($data){
        if(!empty($data)){
            $this->assign("source",json_encode($data));
        }
        $t = T("zxfb");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标发布
     * @return [type] [description]
     */
    private function zbfb(){
        $t = T("zbfb");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标设计
     * @return [type] [description]
     */
    private function zbsj(){
        $zb_box_object=array(
                             "show_box_div"         =>"zb_box_sj",
                             "show_box_banner_div"  =>"zb_box_hd_sj",
                             "zb_box_info"          =>'合理的采光色调及空间布局、理想的风水规划,专业人办专业事儿！',//招标弹窗头部提示
                             "zb_box_tip"           =>'4套设计全面PK,让你的装修绝不后悔!',//招标弹窗温馨提示
                             "zb_box_btn"           =>'获取4套设计方案',//招标弹窗button文字
                             );
        $this->assign("zb_box_object",$zb_box_object);//赋值招标弹窗对象
        $t = T("zbtmp");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标量房
     * @return [type] [description]
     */
    private function zblf(){
        $zb_box_object=array(
                             "show_box_div"         =>"zb_box_lf",
                             "show_box_banner_div"  =>"zb_box_hd_lf",
                             "zb_box_info"          =>'拒绝开发商用各项理由"大事化小",不放过自己的每一项权利!',//招标弹窗头部提示
                             "zb_box_tip"           =>'漏水、裂缝、气泡等质量问题一网打尽!',//招标弹窗温馨提示
                             "zb_box_btn"           =>'一键免费申请',//招标弹窗button文字
                             );
        $this->assign("zb_box_object",$zb_box_object);//赋值招标弹窗对象
        $t = T("zbtmp");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标预算
     * @return [type] [description]
     */
    private function zbys(){
        $zb_box_object=array(
                             "show_box_div"         =>"zb_box_ys",
                             "show_box_banner_div"  =>"zb_box_hd_ys",
                             "zb_box_info"          =>'主材辅料费用,运输及人工成本等一目了然,您千万不要当"冤大头"!',//招标弹窗头部提示
                             "zb_box_tip"           =>'全照国家标准,0漏项,0增项,远离被"蒙"!',//招标弹窗温馨提示
                             "zb_box_btn"           =>'获取详细预算清单',//招标弹窗button文字
                             );
        $this->assign("zb_box_object",$zb_box_object);//赋值招标弹窗对象
        $t = T("zbtmp");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标咨询
     * @return [type] [description]
     */
    private function zbzx(){
        $zb_box_object=array(
                             "show_box_div"         =>"zb_box_zx",
                             "show_box_banner_div"  =>"zb_box_hd_bj",
                             "zb_box_info"          =>'详细预算清单,4份不同报价挤干水份,花1分钟可省万元！',//招标弹窗头部提示
                             "zb_box_tip"           =>'环保材料,低报价,土豪也能省！',//招标弹窗温馨提示
                             "zb_box_btn"           =>'马上获取预算报价',//招标弹窗button文字
                             );
        $this->assign("zb_box_object",$zb_box_object);//赋值招标弹窗对象
        $t = T("zbtmp");
        $tmp = $this->fetch($t);
        return $tmp;
    }

     /**
     * 装修报价
     * @return [type] [description]
     */
    private function zxbj(){
        //获取户型列表
        $hx = D("Common/Huxing")->gethx();
        //获取装修风格列表
        $fg = D("Common/Fengge")->getfg();
        $this->assign("hx",$hx);
        $this->assign("fengge",$fg);
        $t = T("zxbj");
        $tmp = $this->fetch($t);
        return $tmp;
    }
}