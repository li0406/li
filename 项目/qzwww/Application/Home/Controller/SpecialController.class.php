<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class SpecialController extends HomeBaseController{

    public function _initialize(){

        parent::_initialize();
        $headerTmp = "";
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
             //导航栏标识
            $this->assign("tabIndex",6);
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
             //导航栏标识
            $this->assign("tabIndex",6);
        }
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //保障默认为选中状态
        $this->assign('choose_more', 'baozhang');
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    //强制更新 City Array 数据
    //此处不应有前台入口
    //说明：此段代码应该写在后台，但后台没有操作前台缓存的方法，并且要取最新的CityArray数组，
    //为了避免把 Area 的 Model 和 Coutroller 方法复制到后台重复了，所以在前台操作。
    public function updateCityData(){
        //把这两个缓存清空
        S("Cache:Area",null);
        //获取数组形式的城市信息
        $citys = D("Common/Quyu")->getCityArray();
        $citys = json_encode($citys);
        $content =  "var citys = JSON.parse('".$citys."');";
        $filename = date('YmdHi').'.js';
        $result = $this->uploadContentToQiNiu('common/js/allcity'.$filename,$content);
        $filename = $result['key'];
        //清空OP缓存
        S('C:OP:N:ALL_CITY_JSON',null);
        die($filename);
    }

    public function children(){
        //获取城市信息
        //$citys = getCityArray();
        //$info["citys"] =  json_encode($citys);
        //$this->assign("info",$info);
        $this->display();
    }

    //六大保障
    public function security(){


        $info['count'] = releaseCount('fbzrs');
        $this->assign("info",$info);
        $this->display();
    }

    //懒人大法
    public function lanren(){


        $diary = $this->getHotDiary(3);

        $this->assign("diary",$diary);

        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 购物节专题
     * @return [type] [description]
     */
    public function dazhuanpan(){
        if(empty($_COOKIE["w_qizuang_first"])){
            //如果首次登陆，显示填写信息的信息框
            $this->assign("isFirst",1);
        }

        //获取中奖用户信息
        //假名单
        $prize_list = array(
                array("tel"=>"135*****565","prize"=>"智能扫地机器人"),
                array("tel"=>"189*****874","prize"=>"智能蓝牙耳机"),
                array("tel"=>"132*****314","prize"=>"精美餐具"),
                array("tel"=>"134*****234","prize"=>"音响机器人"),
                array("tel"=>"181*****786","prize"=>"精美餐具"),
                array("tel"=>"139*****125","prize"=>"智能蓝牙耳机"),
                array("tel"=>"156*****822","prize"=>"精美餐具"),
                array("tel"=>"189*****845","prize"=>"音响机器人"),
                array("tel"=>"137*****651","prize"=>"精美餐具"),
                array("tel"=>"139*****118","prize"=>"精美餐具")
        );
        //获取真名单
        $prize = D("Logactivity")->getPrizeUserList("dazhuanpan");
        $count = count($prize);
        if($count < count($prize_list)){
            //真名单数量小于家名单时
            $offset =  count($prize_list) - $count;
            shuffle($prize_list);
            $prize_list = array_slice($prize_list,0,$offset);
        }else{
            $prize_list = array();
            foreach ($prize as $key => $value) {
                $prize_list[] = array(
                            "tel"=>substr_replace($value["tel"],"*****",3,5),
                            "prize"=>$value["prize"]
                                      );
            }
        }
        $this->assign("prize_list",$prize_list);
        //判断移动端，加载不同的模板
        if(ismobile()){
            $this->display("Home@Special/m_dazhuanpan");
        }else{
            $this->display("Home@Special/dazhuanpan");
        }
    }

    /**
     * 注册活动用户
     * @return [type] [description]
     */
    public function addspecialuser(){
        if($_POST){
            $code = I("post.code");
            if(!empty($_SESSION["isverify"])){
                //删除认证标示
                unset($_SESSION["isverify"]);
                $data = array(
                    "name"=>I("post.name"),
                    "tel"=>I("post.tel"),
                    "address"=>I("post.address"),
                    "source"=>I("post.source"),
                    "time"=>time()
                            );
                $i = D("Activityuserinfo")->addUserInfo($data);
                if($i !== false){
                    //设置COOKIE，防止重复显示注册信息框
                    $cookie_data = serialize(array(
                            "name"=>I("post.name"),
                            "tel"=>I("post.tel")
                                         ));
                    setcookie("w_qizuang_first",$cookie_data,time()+3600*24*365,'/', '.'.APP_HTTP_DOMAIN);
                    $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                }
                $this->ajaxReturn(array("data"=>"","info"=>"活动发生了异常,请稍后再试！","status"=>0));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"验证码错误!","status"=>0));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"非法提交","status"=>0));
    }

    /**
     * 获取奖品
     * @return [type] [description]
     */
    public function prize(){
        if($_POST){
            $type = I("post.type");
            //是否已经注册
            if(empty($_COOKIE["w_qizuang_first"])){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>4));
            }
            //查询该活动的活动信息
            $config = D("Activityconfig")->getConfig($type);
            //判断活动是否开启
            if(!$config["enabled"]){
                $this->ajaxReturn(array("data"=>"","info"=>"活动尚未开启,请耐心等待噢！","status"=>0));
            }



            //判断活动时间是否过期
            if(count($config) == 0){
                $this->ajaxReturn(array("data"=>"","info"=>"活动配置项错误,请稍后再试！","status"=>0));
            }
            //判断后动是否结束
            $date = strtotime(date("Y-m-d"));
            if($date > $config["end"]){
                $this->ajaxReturn(array("data"=>"","info"=>"活动已经结束，欢迎下次参与！","status"=>0));
            }

            //判断后动是否开始
            if($date < $config["start"]){
                $this->ajaxReturn(array("data"=>"","info"=>"活动尚未开始,请耐心等待噢！","status"=>0));
            }

            //查询该号码是否参与过活动
            $userInfo = unserialize($_COOKIE["w_qizuang_first"]);
            $count = D("Logactivity")->getActivityNowCount($userInfo["tel"]);
            if($count > 0){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>5));
            }

            //解析COOKIE
            $userInfo = unserialize($_COOKIE["w_qizuang_first"]);

            //导入扩展文件
            import('Library.Org.Util.Prizecalculation');


            //查询活动的奖品信息
            $prizeList = D("Activityprize")->getPrizeList($type);
            if(count($prizeList) > 0){
                foreach ($prizeList as $key => $value) {
                    $data[$key] = array(
                            "prizeid"=>$key,
                            "angle"=>$value["angle"],
                            "prize"=>$value["prize"],
                            "fixed"=>$value["fixed"],
                            "v"=>$value["rate"],
                            "id"=>$value["id"]
                                    );
                    if($value["fixed"] == 1){
                        $fixed = $data[$key];
                    }
                }

                $prize = new \Prizecalculation($data);
                $result = $prize->culation($type);

                //记录抽奖日志
                $logData = array(
                        "user"=> $userInfo["name"],
                        "tel"=>$userInfo["tel"],
                        "level"=>$result["id"],
                        "status"=>0,
                        "time"=>time(),
                        "type"=>$type
                                 );
                if($result["fixed"] == 0){
                    //中奖状态
                    $logData["prize"] = $result["prize"];
                    $logData["status"] = 1;
                    //如果奖品已经发送完毕，则显示未中奖,否则修改该奖品的库存数
                    $total = $prizeList[$result["prizeid"]]["total"];
                    $use_count = $prizeList[$result["prizeid"]]["use_count"];

                    if($total == $use_count){
                        $result = $fixed;
                    }else{
                        D("Activityprize")->setPrizeCount($result["id"]);
                    }
                }
                D("Logactivity")->addLog($logData);
                $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
        }
    }


    //获取装修日记
    private function getHotDiary($num){
        $result = S('Cache:Special:lanrenDiary');

        if(empty($result)){
            $map['a.parent_id'] = array('NEQ','0');//是后续日记
            $map['a.stat'] = 1;//仍然在使用的日记
            $list = M('diary_info')->where($map)->alias("a")
                                   ->join("INNER JOIN qz_user as u on a.user_id = u.id")
                                   ->order("a.diary_time DESC")
                                   ->limit('0,'.$num)
                                   ->field("a.id,a.user_id,a.title,a.img_logo,a.add_time,u.name,u.logo")
                                   ->select();
            foreach ($list as $k => $v) {
                $sMap['diary_id'] = $v['id'];
                $imgList = M('diary_img')->field("img_path,img_host")->where($sMap)->limit('0,4')->select();
                if(!empty($imgList)){
                    $v['imgList'] = $imgList;
                }else{
                    $v['imgList'] = array(
                        '0' => array(
                              'img_path'=> $v['img_logo'],
                        ),
                    );
                }
                $result[] = $v;
            }
            S('Cache:Special:lanrenDiary',$result,1800);
        }
        return $result;
    }


    //上传内容到七牛
    private function uploadContentToQiNiu($filename,$content){
        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);
        $putPolicy->SaveKey = $filename;
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_Put($upToken, null, $content, $putExtra);
        if($err == null){
            $result = array(
                    "hash"=>$ret["hash"],
                    "key"=> $ret["key"]
                            );
            return $result;
        }
        return $err;
    }

    public function qixi(){
        $this->display();
    }

    public function guoqing(){
        $this->display();
    }

    public function huanxinjia(){
        if (IS_POST) {
            $activity_name = '11月焕新家';
            //保存用户数据
            $state = $this->saveUserAdressInfo(I('post.'),$activity_name);
            if($state){
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'info' => '地址添加失败！']);
            }
        }
        $this->display();
    }

    public function zxj(){
        if (IS_POST) {
            $activity_name = '2018装修季';
            //保存用户数据
            $state = $this->saveUserAdressInfo(I('post.'),$activity_name);
            if($state){
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'info' => '地址添加失败！']);
            }
        }
        $this->display();
    }

    public function zxj_zhengshi(){
        $this->display();
    }
    public function zxj_end(){
        $this->display();
    }

public function suning()
    {
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $bm = I('get.bm');
        $bms = [
            'cd' => ['name' => '成都', 'source' => 17122233],
            'nc' => ['name' => '南昌', 'source' => 17122343],
            'wh' => ['name' => '武汉', 'source' => 17122249],
            'xa' => ['name' => '西安', 'source' => 17122247],
            'ty' => ['name' => '太原', 'source' => 17122256],
            'sjz' => ['name' => '石家庄', 'source' => 17122207],
            'nn' => ['name' => '南宁', 'source' => 17122220],
            'gy' => ['name' => '贵阳', 'source' => 17122231],
            'sz' => ['name' => '苏州', 'source' => 17122237],
            'tj' => ['name' => '天津', 'source' => 17122230],
            'sh' => ['name' => '上海', 'source' => 17122219],
            'zz' => ['name' => '郑州', 'source' => 17122227],
            'bj' => ['name' => '北京', 'source' => 17122225],
        ];
        //设置默认城市
        if (!in_array($bm, array_keys($bms))) {
            $bm = 'sz';
        }
        $keys = [
            'title' => '跨年狂欢，苏宁易购特惠家电助力齐装网装修盛宴-' . $bms[$bm]['name'],
            'description' => '跨年狂欢，享苏宁易购特惠家电，优惠不容错过。',
            'keywords' => '跨年狂欢，报名齐装网即享苏宁易购全场优惠。特惠家电大折扣，全新优惠，精彩不容错过。',
        ];
        $city =  D("Common/Quyu")->getCityInfoByBm($bm);
        $this->assign('cityInfo',$city);
        $this->assign('bms', $bms);
        $this->assign('bm', $bm);
        $this->assign('keys', $keys);
        $this->display();
    }

    public function voucher_hgj(){
        $this->display();
    }
    /**
     * 保存用户地址
     * @param $post 用户数据
     * @param $activity_name 活动名称
     * @return mixed
     */
    private function saveUserAdressInfo($post,$activity_name)
    {
        if (!$post['address']) {
            $this->ajaxReturn(['status' => 0, 'info' => '请输入收货地址！']);
        } else {
            $post['address'] = remove_xss($post['address']);
        }
        if (!$post['order_id']) {
            $this->ajaxReturn(['status' => 0, 'info' => '缺少订单号！']);
        }
        if (!$post['source']) {
            $this->ajaxReturn(['status' => 0, 'info' => '缺少发单位置标识！']);
        }
        //查询用户是否已经参加活动
        $join = D("Activityuserinfo")->findUserInfo($post['order_id']);
        if ($join) {
            $this->ajaxReturn(['status' => 0, 'info' => '抱歉！您已参加活动！']);
        }
        //活动信息
        $activityInfo = M('activity')->where(['name' => ['eq', $activity_name]])->field('id,name')->find();
        if (!$activityInfo) {
            $this->ajaxReturn(['status' => 0, 'info' => '未知活动！']);
        }
        $post['activity_id'] = $activityInfo['id'];
        //活动奖品信息
        $prizeInfo = D("Activityprize")->getPrizeInfoByActivity($activityInfo['id']);
        if (!$prizeInfo) {
            $this->ajaxReturn(['status' => 0, 'info' => '未知活动奖品！']);
        }
        $post['prize_id'] = $prizeInfo['id'];
        $post['time'] = time();
        return D("Activityuserinfo")->addUserInfo($post);
    }

    /**
     *  会销落地页
     */
    public function zhanxiao(){
        if ($_POST) {
            $name = I("post.name");
            $tel = I("post.tel");
            $name = trim($name);
            $tel  = trim($tel);

            if (empty($name)) {
                $this->ajaxReturn(array("error_code" => 4000002,"error_msg" => "姓名格式不正确,请重新输入"));
            }

            if (empty($tel)) {
                $this->ajaxReturn(array("error_code" => 4000002,"error_msg" => "手机号码不正确,请重新输入"));
            }

            $reg ='/(13|14|15|16|17|18|19)[0-9]{9}/';
            preg_match($reg, $tel,$m);
            if (count($m) == 0) {
                $this->ajaxReturn(array("error_code" => 4000002,"error_msg" => "手机号码不正确,请重新输入"));
            }

            $data = [
                "name" => remove_xss($name),
                "tel"  => $tel,
                "time" => time()
            ];

            //查重
            $findHistroy =  D("Common/Logic/ActivityZhanhuiLogic")->findByTel($tel);
            if (count($findHistroy)>0) {
                $this->ajaxReturn(array("error_code" => 1,"error_msg" => "已报名成功，无需再次提交"));
            }

            //数据入库
            $result =  D("Common/Logic/ActivityZhanhuiLogic")->addInfo($data);

            if ($result !== false) {
               $this->ajaxReturn(array("error_code" => 0,"error_msg" => "提交成功"));
            }
            $this->ajaxReturn(array("error_code" => 5000002,"error_msg" => "服务器连接异常"));
        } else {
            $this->display();
        }
    }

    /**
     * 美图首页活动弹层
     * 【大转盘 ...】
     */
    public function tuTopLayer()
    {
        //导入扩展文件
        import('Library.Org.Util.Prizecalculation');
        $data = [
            1 => array('prizeid'=>1,'min'=>1,'max'=>29,'prize'=>'云南双人游',"img" => '/assets/common/img/lotteryLy.png','v'=>0),
            2 => array('prizeid'=>2,'min'=>302,'max'=>328,'prize'=>'上门量房',"img" => '/assets/common/img/lotteryLF.png','v'=>76),
            3 => array('prizeid'=>3,'min'=>242,'max'=>268,'prize'=>'四套设计方案',"img" => '/assets/common/img/lotterySheJi.png','v'=>24)
        ];

        $prize = new \Prizecalculation($data);
        $result = $prize->culation();
        $data = [
            "prizeid" => $result["prizeid"],
            "prize" => $result["prize"],
            "img" => $result["img"]
        ];
        echo json_encode(array("error_code" => 0,"data" => $data),320);
        die();
    }
}

















