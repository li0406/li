<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
* 站内信管理
*/
class AdminusersystemnoticeController extends HomeBaseController {
    //用户类别
    private $classType = array(
                array("value"=>"","name"=>"全部"),
                array("value"=>"1","name"=>"注册用户"),
                array("value"=>"2","name"=>"设计师"),
                array("value"=>"3","name"=>"装修公司"),
                );
    //发送类型
    private $sendType = array(
                array("value"=>"2","name"=>"站内信")
                );

    public function index()
    {
        //获取公告列表
        $pageIndex = 1;
        $pageCount = 20;
        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }

        if (I("get.title") != "") {
            $title = I("get.title");
            $this->assign("title",$title);
        }

        if (I("get.begin") != "" && I("get.end") != "") {
            $begin = I("get.begin");
            $end = I("get.end");
            $this->assign("begin",$begin);
            $this->assign("end",$end);
        }

        $result = $this->getNoticeList($pageIndex,$pageCount,$title,$begin,$end);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 编辑/新增公告
     * @return [type] [description]
     */
    public function noticeup()
    {
        $model = D("UserSystemNotice");
        if($_POST){
            $redis  = new \Redis();
            $flag = $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'), 1);

            if (!$flag) {
               $this->ajaxReturn(array("info"=>"redis未开启,请联系技术部！", "status"=>0));
            }

            $data = array(
                "title" => I("post.title"),
                "type" => I("post.type"),
                "text" => htmlspecialchars_decode(I("post.content")),
                "classid" => I("post.uid")
                          );
            $status = 0;

            if($model->create($data,1)){
                //新增操作
                $data["userid"] = $this->User["id"];
                $data["username"] = $this->User["name"];
                $data["time"] = time();
                $id = $model->addNotice($data);
                if ($id !== false) {
                    $status = 1;
                    $errMsg = "";
                    $classId = I("post.uid");

                    if (I("post.ids") !== "") {
                        $ids = json_decode(htmlspecialchars_decode(I("post.ids")),true);
                    }

                    if (I("post.group") == 2) {
                        $fake = 1;
                    }

                    //查询城市的用户信息
                    $list = D("User")->getUserInfoListByCity($ids,$classId,$fake);

                    $n = 1;
                    foreach ($list as $key => $value) {
                        $sub = array(
                                "noticle_id" => $id,
                                "uid" =>$value["id"]
                        );
                        $subData[$n][] = $sub;
                        if ($key%3000 == 0) {
                            $n ++;
                        }
                    }
                    $redis->delete('usermail');
                    foreach ($subData as $key => $value) {
                       $redis->lpush('usermail',json_encode($value));
                    }
                    $max = $redis->llen("usermail");
                    $redis->close();

                }
            }else{
                $errMsg = $model->getError();
            }
            $this->ajaxReturn(array("info"=>$errMsg, "status"=>$status,"data"=>$max));

        }else{
            $id = I("get.id");
            if(!empty($id)){
                //查询公告信息
                $notice = $model->getNoticeInfo($id);
                //获取公告的对应用户
                $result = D("UserNoticeRelated")->getNoticeRelated($id);
                foreach ($result as $key => $value) {
                    if (!array_key_exists($value["cid"],$cs)) {
                        $cs[$value["cid"]] = $value["cid"];
                    }

                    if($value["classid"] == 3){
                        $name = $value['jc'];
                        if($value["on"] == 2){
                            $name .= " vip";
                        }
                    }else{
                        $name = $value['name'];
                    }
                    $user[$value['uid']] = $name;
                }
                $this->assign("cs",$cs);
                $this->assign("user",$user);
                $this->assign("notice",$notice);
            }

            //获取热门城市
            $hot = D("Quyu")->getHotCity();
            $this->assign("hotCity",$hot);
            $result = D("Quyu")->getProvinceAndCity();
            $n = 1;
            foreach ($result as $key => $value) {
                if ($value["cid"] != "000001") {
                    if (!array_key_exists($value["uid"],$cityList)) {
                        $cityList[$value["uid"]]["name"] = $value["qz_province"];
                        if($n%5 == 0){
                            $cityList[$value["uid"]]["mark"] = 1;
                        }
                        $n ++;
                    }
                    $cityList[$value["uid"]]["child"][] = array(
                                                    "cid" => $value["cid"],
                                                    "cname" => $value["cname"]
                                                                );
                }
            }

            $this->assign("cityList",$cityList);

            $this->assign("sendType",$this->sendType);
            $this->assign("classtype",$this->classType);
            $this->display();
        }
    }

    /**
     * 获取城市的VIP用户信息
     * @return [type] [description]
     */
    public function getuserinfo(){
        if ($_POST) {
            $status = 0;
            $errMsg = "";
            $classid = I("post.uid");
            $cityIds = htmlspecialchars_decode(I("post.ids"));
            $cityIds = array_unique(json_decode($cityIds,true));
            if(count($cityIds) > 0){
                //获取用户信息
                $userList = D("User")->getVipUserList($cityIds,$classid);
                $status = 1;
            }
            $this->ajaxReturn(array("data"=>$userList, "info"=>$errMsg, "status"=>$status));
        }
    }

    /**
     * 进度显示
     * @return [type] [description]
     */
    public function step() {
        $redis  = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'), 1);
        if (I("get.max") != "") {
            $max = I("get.max");
            $data = json_decode($redis->lPop("usermail"),true);
            $len = $redis->llen("usermail");
            if ($max == $len) {
                $n = 1;
            }
            $offset = round(($max-$len)/$max*100,2);
            //添加数据
            D("UserNoticeRelated")->addAllRelated($data);
            if ($len <= 0) {
                $redis->delete("usermail");
                $this->ajaxReturn(array("status"=>2));
            }
            $redis->close();
            $this->ajaxReturn(array("status"=>1,"data"=>$offset));
        }

    }

    /**
     * 删除公告
     * @return [type] [description]
     */
    public function delnotice()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("UserSystemNotice")->delNotice($id);
            if ($i !== false) {
                //删除关联的信息
                D("UserNoticeRelated")->delRelated($id);
            }
            $this->ajaxReturn(array("status"=>1));
        }
    }

    /**
     * 站内信预览
     * @return [type] [description]
     */
    public function preview() {
        if (I("get.id") !== "") {
            $noticeInfo = D("UserSystemNotice")->getNoticeInfo(I("get.id"));
            if (count($noticeInfo) > 0) {
                $this->assign("notice",$noticeInfo);
            }
            $this->display();
        }
    }

    /**
     * 获取公告列表
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $title     [标题]
     * @param  [type] $begin     [开始时间]
     * @param  [type] $end       [结束时间]
     * @return [type]            [description]
     */
    private function getNoticeList($pageIndex,$pageCount,$title,$begin,$end)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("UserSystemNotice")->getNoticeListCount($title,$begin,$end);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $list = D("UserSystemNotice")->getNoticeList($p->firstRow,$p->listRows,$title,$begin,$end);
            return array("page"=>$show,"list"=>$list);
        }

    }
}