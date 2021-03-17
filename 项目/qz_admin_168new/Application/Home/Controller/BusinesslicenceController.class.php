<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class BusinesslicenceController extends HomeBaseController
{

    public function index()
    {
        //获取待审核列表
        $result = $this->getList(array(1),I("get.city"),I("get.id"),"","","",I("get.dev_manage"),I("get.brand_division"),I("get.dev_regiment"),I("get.brand_regiment"),I("get.type"));
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 审核确认
     * @return [type] [description]
     */
    public function auditconfirmation()
    {
        $result = $this->getList(array(2,3),I("get.city"),I("get.id"),$audit,"","","","","","",I("get.type"));
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("query",$result["query"]);
        $this->display();
    }

    /**
     * 审核记录
     * @return [type] [description]
     */
    public function auditrecord()
    {
        if (I("get.audit") !== "") {
            if (I("get.audit") == 4 || I("get.audit") == 5) {
                $audit = I("get.audit");
            }
        }
        //获取复审核列表
        $result = $this->getList(array(4,5),I("get.city"),I("get.id"),$audit,I("get.begin"),I("get.end"),"","","","");
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    public function audit()
    {
        if ($_POST) {
            $type = I("post.type");
            switch ($type) {
                case '1':
                    //初审
                    //查看工商局查询是否上传
                    $sub_type = I("post.sub_type");
                    if ($sub_type == 1) {
                        $info = D("SaleBusinessLicence")->findBusinessTypeInfo(I("post.id"),4);
                        if (count($info) == 0) {
                            $this->ajaxReturn(array("info"=>"尚未获取到工商总局查询图片,请先上传！","status"=>0));
                        }
                    }

                    $remark = str_replace(array('\'','\"'), '\'', I("post.remark"));
                    $data = array(
                        "remark" => $remark,
                        "audit_time" => time(),
                        "uid" => session("uc_userinfo.id"),
                        "uname" => session("uc_userinfo.name")
                    );

                    if (I("post.state") == 1) {
                        $data["state"] = 3;
                    } else {
                        $data["state"] = 2;
                    }
                    $i = D("SaleBusinessLicence")->editBusinessLicence(I("post.id"),$sub_type,$data);
                    break;
                case '2':
                    //复审
                    $sub_type = I("post.sub_type");
                    $info = D("SaleBusinessLicence")->findBusinessTypeInfo(I("post.id"),$sub_type);

                    if ($info["state"] < 2) {
                        $this->ajaxReturn(array("info"=>"营业执照图片请先初审！","status"=>0));
                    }

                    if ($info["state"] == 3) {
                        $data = array(
                            "state" => 4
                        );
                        $i = D("SaleBusinessLicence")->editBusinessLicence(I("post.id"),$sub_type,$data);
                        $data = array(
                            "state" => 4
                        );
                        $i = D("SaleBusinessLicence")->editBusinessLicence(I("post.id"),4,$data);
                        switch ( $sub_type) {
                            case '1':
                                $msg = "您的营业执照审核已通过！";
                                $title = "营业执照";
                                break;

                            case '3':
                                $msg = "您的装修资质审核已通过！";
                                $title = "装修资质";
                                break;
                        }

                    } elseif ($info["state"] == 2) {
                        $data = array(
                            "state" => 5
                        );
                        $i = D("SaleBusinessLicence")->editBusinessLicence(I("post.id"),$sub_type,$data);
                         switch ( $sub_type) {
                            case '1':
                                $msg = "您的营业执照审核未通过，原因是:".$info["remark"];
                                $title = "营业执照";
                                break;

                            case '3':
                                $msg = "您的装修资质审核未通过，原因是:".$info["remark"];
                                $title = "装修资质";
                                break;
                        }
                    }
                    //审核补通过发送通知邮件
                    R("Api/sendMessage",array($title."审核通知",$msg,3,I("post.id")));
                    break;
                case '3':
                    $data = array(
                        "state" => 1,
                        "audit_time" => 0
                    );
                    $sub_type = I("post.sub_type");
                    $i = D("SaleBusinessLicence")->editBusinessLicence(I("post.id"),$sub_type,$data);
                break;
            }

            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => '营业执照审核',
                    'logtype' => 'businesslicence',
                    'action_id' => I("post.id"),
                    'info' => ""
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"网络传输异常,请稍后再试！","status"=>0));
        }
    }

    /**
     * 上传营业执照
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function upfile()
    {
        if ($_POST) {
            $type = I("post.type");
            switch ($type) {
                case '2':
                    if (I("post.img") == "" && I("post.img2") == "") {
                        $this->ajaxReturn(array("info"=>"请上传营业执照或装修资质","status"=>0));
                    }

                    //删除原来的图片
                    if (I("post.img") !== "") {
                        D("SaleBusinessLicence")->delBusinessLicence(I("post.id"),1);
                        $data[] = array(
                            "company_id" => I("post.id"),
                            "img1" => I("post.img"),
                            "img_host" => "qiniu",
                            "state" => 1,
                            "time" => time(),
                            "type" => 1
                        );
                    }

                    if (I("post.img2") !== "") {
                        D("SaleBusinessLicence")->delBusinessLicence(I("post.id"),3);
                        $data[] = array(
                            "company_id" => I("post.id"),
                            "img1" => I("post.img2"),
                            "img_host" => "qiniu",
                            "state" => 1,
                            "time" => time(),
                            "type" => 3
                        );
                    }

                    $i = D("SaleBusinessLicence")->addAllBusinessLicence($data);
                    break;
                default:
                    if (I("post.img") === "") {
                        $this->ajaxReturn(array("info"=>"请先上传工商总局查询截图！","status"=>0));
                    }
                    $data = array(
                        "company_id" => I("post.id"),
                        "img1" => I("post.img"),
                        "img_host" => "qiniu",
                        "state" => 3,
                        "time" => time(),
                        "type" => 4
                    );
                    //删除原来的图片
                    D("SaleBusinessLicence")->delBusinessLicence(I("post.id"),4);
                    $i = D("SaleBusinessLicence")->addBusinessLicence($data);
                    break;
            }


            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"网络传输异常,请稍后再试！","status"=>0));
        }
    }

    /**
     * 清空上传资料
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function clear()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("SaleBusinessLicence")->delBusinessLicence($id);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"网络传输异常,请稍后再试！","status"=>0));
        }
    }

    /**
     * 上传营业执照
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function upload()
    {
        if (I("get.id") != "") {
           $list = $this->saleCityList(I("get.id"));
           $this->assign("list",$list);
        }

        $this->display();
    }


    public function businessLicenceStat()
    {
        $list = $this->getBusinessLicenceStatList(I("get.city"),I("get.id"),I("get.yyzz"),I("get.zxzz"));
        $this->assign("list",$list);
        $this->display();
    }

    private function saleCityList($user_id)
    {
        if (session("uc_userinfo.uid") != 1) {
            $citys = getUserSaleCitys();
            foreach ($citys as $key => $value) {
                $cs[] = $value["cid"];
            }
            $cs = array_filter($cs);
            if (count($cs) == 0) {
                $this->error('您还没有管辖城市！');
            }
        }
        $result = D("SaleBusinessLicence")->getSaleCityCompanyList($cs,$user_id);
        foreach ($result as $key => $value) {
            if ($value["type"] != 4) {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]] = array(
                        "qc" => $value["qc"],
                        "cname" => $value["cname"],
                        "dev_manage" => $value["dev_manage"],
                        "brand_manage" => $value["brand_manage"],
                        "brand_regiment" => $value["brand_regiment"],
                        "dev_regiment" => $value["dev_regiment"],
                        "id" => $value["id"],
                        "uname" => $value["qc"]
                    );
                }
                if (!empty($value["type"])) {
                    $list[$value["id"]]["child"][$value["type"]] = $value;
                }

                if (count($list[$value["id"]]["child"]) == 2) {
                    $list[$value["id"]]["isSave"] = 1;
                }
            }
        }
        return $list;
    }

    private function getList($state,$city,$id,$audit,$begin,$end,$dev_manage,$brand_division,$dev_regiment,$brand_regiment,$type)
    {
        if (session("uc_userinfo.uid") != 1) {
            $citys = getUserSaleCitys();
            foreach ($citys as $key => $value) {
                $cs[] = $value["cid"];
            }
            $cs = array_filter($cs);
            if (count($cs) == 0) {
                $this->error('您还没有管辖城市！');
            }
        }

        if (!empty($begin) && !empty($end)) {
            $mothBegin = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $count = D("SaleBusinessLicence")->getBusinessLicenceListCount($state,$city,$id,$audit,$mothBegin,$monthEnd,$dev_manage,$brand_division,$dev_regiment,$brand_regiment,$cs,$type);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $page =  $p->show();
            $result  = D("SaleBusinessLicence")->getBusinessLicenceList($state,$city,$id,$audit,$mothBegin,$monthEnd,$dev_manage,$brand_division,$dev_regiment,$brand_regiment,$cs,$type,$p->firstRow,$p->listRows);

            foreach ($result as $key => $value) {
                $sub = array(
                    "qc" => $value["qc"],
                    "cname" => $value["cname"],
                    "dev_manage" => $value["dev_manage"],
                    "brand_manage" => $value["brand_manage"],
                    "brand_regiment" => $value["brand_regiment"],
                    "dev_regiment" => $value["dev_regiment"],
                    "id" => $value["company_id"],
                    "state" => $value["state"],
                    "audit_time" => $value["audit_time"],
                    "uname" => $value["uname"],
                    "remark" => $value["remark"],
                    "type" => $value["type"],
                    "child" => array(
                        "img1" => $value["img1"],
                        "img2" => $value["img2"],
                        "img3" => $value["img3"],
                        "img4" => $value["img4"]
                    )
                );
                if (!empty($value["gszj_img"])) {
                     $sub["gszj"] = array(
                        "img1" => $value['gszj_img'],
                        "img2" => "",
                        "img3" => "",
                        "img4" => ""
                    );
                }
                $list[] = $sub;
            }
        }

        return array("list"=>$list,"page"=>$page,"query" => $query);
    }

    /**
     * 获取营业执照列表
     * @param  [type] $city_id [城市ID]
     * @param  [type] $user_id [用户ID]
     * @param  [type] $yyzz    [营业执照状态]
     * @param  [type] $zxzz    [装修资质状态]
     * @return [type]          [description]
     */
    private function getBusinessLicenceStatList($city_id,$user_id,$yyzz,$zxzz)
    {
        if (session("uc_userinfo.uid") != 1) {
            $citys = getUserSaleCitys();
            foreach ($citys as $key => $value) {
                $cs[] = $value["cid"];
            }
            $cs = array_filter($cs);
            if (count($cs) == 0) {
                $this->error('您还没有管辖城市！');
            }
        }

        $count = D("SaleBusinessLicence")->getSaleCityCompanyListCount($cs,$user_id,2,$city_id,$yyzz,$zxzz);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();

            $result = D("SaleBusinessLicence")->getSaleCityCompanyList($cs,$user_id,2,$city_id,$yyzz,$zxzz,$p->firstRow, $p->listRows);

            foreach ($result as $key => $value) {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]] = array(
                        "qc" => $value["qc"],
                        "cname" => $value["cname"],
                        "dev_manage" => $value["dev_manage"],
                        "brand_manage" => $value["brand_manage"],
                        "brand_regiment" => $value["brand_regiment"],
                        "dev_regiment" => $value["dev_regiment"],
                        "id" => $value["id"],
                        "uname" => $value["qc"]
                    );
                }

                if (!empty($value["type"])) {
                    $list[$value["id"]]["child"][$value["type"]] = $value;
                }
            }
        }

        return array("list"=> $list,"page"=>$show);
    }
}
