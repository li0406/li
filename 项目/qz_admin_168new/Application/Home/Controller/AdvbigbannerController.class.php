<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class AdvbigbannerController extends HomeBaseController{
    public function _initialize(){
        parent::_initialize();
        $this->assign("sidebarid",1);
        $this->assign("side_sid",1);
    }

    public function index(){
        //获取通栏的列表
        $query = I("get.query");
        $this->assign("query",$query);
        $cs = I("get.cs");
        $this->assign("cs",I("get.cs"));

        if($cs == ""){
            $cs = $this->city;
        }elseif ($cs == 0) {
            $cs = 0;
        } else{
            $cs = array(I("get.cs"));
        }

        $position = I("get.position");
        if(count($this->city) > 0){
            $citys = getUserCitys();
            //将城市排序
            $result = $this->getBigBannerList($query,$cs,$position);
            $this->assign('list',$result["list"]);
            $this->assign('page',$result["page"]);
        }
        $this->assign("citys",$citys);

        $this->assign("position",$position);

        $this->assign('list',$result["list"]);
        $this->assign('page',$result["page"]);
        $this->display();
    }

    /**
     * 添加/编辑通栏广告
     * @return [type] [description]
     */
    public function bigBannerUp(){
        if($_POST){
            $admin = getAdminUser();

            $data = array(
                'start_time'=> strtotime(I("post.start_time")),
                'end_time'=> strtotime(I("post.end_time")),
                'img_url'=> I("post.img"),
                'img_host'=> 'qiniu',
                'url'=> urldecode(I("post.url")),
                'description'=> I("post.description"),
                'op_time'=> time(),
                'op_uid'=> $admin['id'],
                'op_uname'=> $admin['name'],
                'sort'=> I("post.sort"),
                'status'=> I("post.status"),
                'company_id' => I("post.company_id"),
                'company_name' => I("post.company_name"),
                "city_id" => I("post.city_id")
            );

            //判断是否有全站的权限
            if (empty($data['city_id'])) {
                if(!in_array(session("uc_userinfo.uid"),getAllCityManager())){
                    $this->ajaxReturn(array("info"=>'您没有全站的管辖权限，请选择城市',"status"=>0));
                }
            }

            $model = D("Advbanner");
            $status = 0;
            if($model->create($data,1)){
                $position = I("post.position");
                if(strtolower($position) == "a"){
                    //通栏A
                    $data["module"] = "home_bigbanner_a";
                    $banner = "bigbanner_a";
                }elseif (strtolower($position) == "b"){
                    //通栏B
                    $data["module"] = "home_bigbanner_b";
                    $banner = "bigbanner_b";
                }elseif (strtolower($position) == "c") {
                    //通栏B
                    $data["module"] = "home_bigbanner_c";
                    $banner = "bigbanner_c";
                }

                $id = I("post.id");
                if(!empty($id)){
                    //编辑状态
                    $i = D("Advbanner")->editBanner($id,$data);
                    $action = "编辑";
                }else{
                    //新增状态
                    //查询通栏记录，如果超过3条提示错误
                    $count = D("Advbanner")->getBigBnnerPositionCount($data["module"],$data["city_id"]);
                    if($count <= 3){
                        $id = $i = D("Advbanner")->addBanner($data);
                        $action = "新增";
                    }else{
                        $i = false;
                        $errMsg = "通栏".$position."超过了规定的3条数量,请使用编辑操作！";
                    }
                }

                if($i !== false){
                    $status = 1;
                    //添加操作日志
                    $logData = array(
                        "remark"=>$action ."通栏广告【".$id."】",
                        "action_id"=>I("post.company_id"),
                        "info"=>$_POST,
                        "logtype"=>"editbigbanner"
                    );
                    D('LogAdmin')->addLog($logData);
                }
            }else{
                $errMsg = $model->getError();
            }
            $this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
        }else{
            $id = I("get.id");
            if(!empty($id)){
                $adv = D("Advbanner")->getBannerById($id,'home_bigbanner');
                if(count($adv) == 0){
                    $this->_error("您查询的信息不存在");
                }
                $adv["img"] = $adv["img_url"];
                if($adv["img_host"] == "qiniu"){
                    $imgs = array('<img src="//'.OP("QINIU_DOMAIN").'/'.$adv["img_url"].'"/>');
                    $json = json_encode($imgs);
                }
                $adv["img_url"] = $json;

                $this->assign("adv",$adv);
                $advData = array("id"=>$adv["company_id"],"text"=>$adv["company_name"],"cs"=>$adv["city_id"]);
                $this->assign("advData",json_encode($advData));
            }

            //获取管辖城市信息
            $citys = getUserCitys();
            $this->assign("citys",$citys);
            $this->display();
        }
    }

    /**
     * [setStatus 更改广告的状态是否启用]
     */
    public function setStatus(){
        if($_POST){
            $id = I("post.id");
            $data= array("status"=>I("post.status"));
            $i = D("Advbanner")->editBanner($id,$data);
            $status =0 ;
            if($i!== false){
                $status = 1;
            }else{
                $errMsg ="操作失败,请联系技术部门！";
            }

            if(1 == I("post.status")){
                $action = '使用';
            }else{
                $action = '禁用';
            }

            //添加操作日志
            $logData = array(
                            "remark"=>"修改了通栏广告【".$id."】的状态为：".$action,
                            "action_id"=>I("post.company_id"),
                            "info"=>$_POST,
                            "logtype"=>"editbigbanner"
                             );
            D('LogAdmin')->addLog($logData);
            $this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
        }
    }

    public function sortbigbanner(){
        if($_POST){
            $data = I("post.data");
            foreach ($data as $key => $value) {
                $saveData = array(
                    "sort"=>$value["value"]
                               );
                D("Advbanner")->editBanner($value["id"],$saveData);
            }

            $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
        }
    }

    /**
     * 获取通栏列表
     * @return [type] [description]
     */
    private function getBigBannerList($query,$cs,$position){
        $map["module"] = array("like","%home_bigbanner_%");
        if(!empty($query)){
            $map["_complex"] = array(
                "company_id"=>array("LIKE","%$query%"),
                "company_name"=>array("LIKE","%$query%"),
                "_logic"=>"OR"
                         );
        }

        if($cs !== "" ){
            if ($cs == 0) {
                $map["city_id"] = array(
                    array("EQ",0),
                    array("EQ",''),
                    "OR"
                );
            } else{
                $map["city_id"] = array(
                    array("IN",$cs)
                );
            }
        }

        if(!empty($position)){
            if(strtolower($position) == "a"){
                $map["module"] = array("EQ","home_bigbanner_a");
            }else if(strtolower($position) == "b"){
                $map["module"] = array("EQ","home_bigbanner_b");
            }else if(strtolower($position) == "c"){
                $map["module"] = array("EQ","home_bigbanner_c");
            }
        }

        $count = D("Advbanner")->getBigBannerListCount($map);

        if(count($count) > 0){
            $order = "id desc";
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $list = D("Advbanner")->getBigBannerList($p->firstRow,$p->listRows,$map,$order);

            $now  =  strtotime(date("Y-m-d"));
            foreach ($list as $key => $value) {
                $list[$key]["end_day"] = 0;
                if($value["end_time"] >= $now){
                    $list[$key]["end_day"] = ($value["end_time"] - $now)/86400+1;
                }
            }
            return array("page"=>$show,"list"=>$list);
        }
    }

    public function delbigbanner(){

    }
}
