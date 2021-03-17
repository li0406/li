<?php
/**
 * 公用的调用数据接口
 */
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class ApiController extends HomeBaseController{
    public function _initialize(){

    }

    public function index(){
        die();
    }

    /**
     * 获取装修公司信息
     * @return [type] [description]
     */
    public function getCompanyInfo(){
        $query = I("get.query");
        $cityids = getMyCityIds();
        if(!empty($cityids)){
            $result = D("User")->getUserInfoList($query,$cityids,10);
        }
        return $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
    }

    /**
     *根据关键字获取非会员和假会员装修公司
     */
    public function getNotVipCompanyList(){
        $query = I("get.query");
        $result = D("User")->getNotVipUserList($query,10);
        return $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
    }


    public function getDesignerInfo(){
        $query = I("get.query");
        $result = D("User")->getDesignerInfoList($query, 10);
        return $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
    }

    //取案例的公司自动提示
    public function getCaseCompany(){
        $keyword = $_GET['condition'];
        if(!empty($keyword)){
            if(!is_numeric($keyword)){
                $map['u.jc'] = array('like','%'.$keyword.'%');
            }else{
                $map['u.id'] = array('like','%'.$keyword.'%');
            }

            //判断是否有城市信息
            $cityId = I('get.cityid');
            if(empty($cityId)){
                $map['u.cs'] = array('IN',implode(',',getMyCityIds()));
            }else{
                $map['u.cs'] = $cityId;
            }
            $map['u.classid'] = array('EQ','3');

            $users = M('user')->alias('u')->field('u.id,u.user,u.jc,u.qc,u.cs,q.cname')
                                ->join('LEFT JOIN qz_quyu AS q ON q.cid = u.cs')
                                ->limit('0,8')->where($map)->select();

            if(!empty($users)){
                $status = '1';
                $msg    = '查询成功!';
                $data = $users;
            }
        }
        if(empty($data)){
            $status = '0';
            $msg    = '没有找到,请重新查询!';
            $data   = '';
        }
        $this->ajaxReturn(array("data"=>$data,"info"=>$msg,"status"=>$status));
        die();
    }

    /**
     * 获取全部城市（包含区县信息）
     * @return [type] [description]
     */
    public function getAllCity(){
        $city = S("C:AdminAllCity");
        if(!$city){
            //获取全部城市的编号
            $city = D("Quyu")->getAllCity();
            S("C:AdminAllCity",$city,600);
        }
        return $city;
    }

    /**
     * 获取全部城市（不包含区县信息）
     * @return [type] [description]
     */
    public function getAllCityOnly(){
        $city = S("C:A:AllCityOnly");
        if(!$city){
            //获取全部城市的编号
            $city = D("Quyu")->getAllQuyuOnly();
            S("C:A:AllCityOnly",$city,600);
        }
        return $city;
    }

    /**
     * 获取所有的城市信息（不包含区县信息）
     * @param  [type] $cityArray [城市ID的数据]
     * @return [type] [description]
     */
    public function getAllCityInfo($cityArray){
        $allCity = $this->getAllCityOnly();
        //dump(count($allCity));
        $cityArray = array_flip($cityArray);
        $city = [];
        foreach ($allCity as $key => $value) {
            if (array_key_exists($value["cid"],$cityArray) || empty($cityArray)) {
                $value['key']          = $value['first_abc'];
                $value["oldname"]      = $value["cname"];
                $value["cname"]        = $value['first_abc']." ".$value["cname"];
                $city[] = $value;
            }
        }
        unset($value);
        //dump(count($city));
        return $city;
    }

    /**
     * 获取城市信息(包含区域信息)
     * @param  [type] $cityArray [城市ID的数据]
     * @return [type]     [description]
     */
    public function getCityInfo($cityArray){
        $allCity = $this->getAllCity();
        $cityArray = array_flip($cityArray);
        import('Library.Org.Util.App');
        $app = new \App();
        foreach ($allCity as $key => $value) {
            if(array_key_exists($value["cid"],$cityArray)) {
                if(!array_key_exists($value["cid"], $city)) {
                    $city[$value["cid"]]["cid"]          = $value["cid"];
                    //增加首字母大写
                    $str = $app->getFirstCharter($value["cname"]);
                    $city[$value["cid"]]["key"]          = $str;
                    $city[$value["cid"]]["bm"]           = $value["bm"];
                    $city[$value["cid"]]["uid"]          = $value["uid"];
                    $city[$value["cid"]]["cname"]        = $str." ".$value["cname"];
                    $city[$value["cid"]]["oldname"]      = $value["cname"];
                    $city[$value["cid"]]["type"]         = $value["type"];
                    $city[$value["cid"]]["is_open_city"] = $value["is_open_city"];
                    $city[$value["cid"]]["mark_red"]     = $value["mark_red"];
                    $city[$value["cid"]]["px"]           = $value["px"];
                    $city[$value["cid"]]["px_abc"]       = $value["px_abc"];
                    $city[$value["cid"]]["parent_city"]  = $value["parent_city"];
                    $city[$value["cid"]]["parent_city1"] = $value["parent_city1"];
                    $city[$value["cid"]]["parent_city2"] = $value["parent_city2"];
                    $city[$value["cid"]]["parent_city3"] = $value["parent_city3"];
                    $city[$value["cid"]]["parent_city4"] = $value["parent_city4"];
                    $city[$value["cid"]]["other_city"]   = $value["other_city"];
                    $city[$value["cid"]]["qz_province"]     = $value["qz_province"];
                    $city[$value["cid"]]["qz_provinceid"]     = $value["qz_provinceid"];
                }
                //增加首字母大写
                $str = $app->getFirstCharter($value["qz_area"]);
                $quyu = array(
                    "key"=>$str,
                    "qz_areaid"=>$value["qz_areaid"],
                    "qz_area" =>$str." ".$value["qz_area"],
                    "orders" => $value["orders"],
                    "oldname" =>$value["qz_area"]
                    );
                $city[$value["cid"]]["child"][]= $quyu;
            }
        }
        return $city;
    }

    public function getMeituList(){
        $keyword = I('get.condition');
        if(!empty($keyword)){
            if(!is_numeric($keyword)){
                $map['m.title'] = array('like','%'.$keyword.'%');
            }else{
                $map['m.id'] = array('like','%'.$keyword.'%');
            }
            $result = M('meitu')->alias('m')->field('m.id,m.title')->limit('0,15')->where($map)->select();
            if(!empty($result)){
                $status = '1';
                $msg    = '查询成功!';
                $data = $result;
            }
        }
        if(empty($data)){
            $status = '0';
            $msg    = '没有找到,请重新查询!';
            $data   = '';
        }
        $this->ajaxReturn(array("data"=>$data,"info"=>$msg,"status"=>$status));
        die();
    }

    /**
     * 城市按字母排序
     * @return [type] [description]
     */
    public function citySort($citys){
        $edition = array();
        foreach ($citys as $k => $v) {
            $edition[] = $v["key"];
        }
        array_multisort($edition, SORT_ASC, $citys);
        return $citys;
    }


    public function getLogsListById(){
        $id = intval($_GET['id']);
        $limit = intval($_GET['limit']);
        if(empty($limit) || $limit > 20 || $limit < 0){
            $limit = 20;
        }
        $logtype = $_GET['logtype'];
        $result = D('LogAdmin')->getLogListsById($id,$logtype,$limit);
        $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
    }

    /**
     * 发送系统站内信
     * @param  [type]  $title   [站内信标题]
     * @param  [type]  $message [站内行内容]
     * @param  integer $classid [用户类别]
     * @param  integer $company_id [装修公司ID]
     * @return [type]           [description]
     */
    public function sendMessage($title, $message , $classid = 3,$company_id){
        $data = array(
            "title" => $title,
            "text" => $message,
            "classid" =>$classid,
            "time" =>time(),
            "userid" => "0",
            "username" => '系统'
                      );
        $i = D("UserSystemNotice")->addNotice($data);
        if ($i !== false) {
            if (!empty($company_id)) {
                $data = array(
                    "noticle_id" => $i,
                    "uid" => $company_id
                              );
                D("UserNoticeRelated")->addRelated($data);
            }
            return true;
        }
        return false;
    }

    /**
     * [switchMenuShow 切换菜单是否显示]
     * @return [type] [description]
     */
    public function switchMenuShow()
    {
        $showmenu = I('get.showmenu') == 'true' ? true : false;
        session('uc_userinfo.showmenu', $showmenu);
        $this->ajaxReturn(array("status"=>1));
    }

    /**
     * 短信发送接口
     * @return [type] [description]
     */
    public function sms()
    {
        $token = I("get.token");
        $auth = getToken(I("get.sign"),I("get.timestamp"));
        if ($auth == $token) {
            $data = I("post.");

            foreach ($data as $key => $value) {
                $tels = array_filter(explode(",",$value));
                foreach ($tels as $val) {
                    //查询模版信息
                    $data = array(
                       "tpl" => OP($key),
                       "tel" => $val,
                       "type" => "nil",
                       "sms_channel" => "yunrongyx"
                    );
                    sendSmsQz($data);
                }
            }
        }
    }

    /**
     * 查询用户API
     */
    public function getUserByIdAndClassid()
    {
        $id = I('get.id');
        $classid = I('get.classid');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '传入的ID为空'));
        }
        $result = D('User')->getUserByIdAndClassid($id, $classid);
        if (empty($result)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '查询数据为空'));
        }
        $this->ajaxReturn(array('status' => 1, 'data' => $result));
    }
}