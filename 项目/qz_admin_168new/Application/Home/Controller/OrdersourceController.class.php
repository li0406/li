<?php

//渠道来源标识管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderSourceInLogicModel;
use Home\Model\Logic\OrderSourceLogicModel;
use Home\Model\Logic\OrderSourceAccountLogicModel;

class OrdersourceController extends HomeBaseController{
    private $dept = array(
        "总裁办" => "zcb",
        "推广二部" => "tg2",
        "推广一部" => "tg1",
        "渠道部" => "qd",
        "公装部" => "gz",
        "新媒体部" => "media",
        "线下广告" => "online",
        "品牌部" => 'brand'
    );

    public function _initialize(){
        parent::_initialize();
    }

    //首页
    public function index(){
        $uid = session("uc_userinfo.uid");
        //非管理员查看全部
        if ($uid != 1) {
            $id = session("uc_userinfo.id");
            //查询自己所对应的部门下的渠道
            $result =  D("DepartmentIdentify")->findMyDept($id);

            foreach ($result as $key => $value) {
                if (!empty($value["id"])) {
                     $ids[] = $value["id"];
                }
                $depts[] = $value["dept"];
                if (!array_key_exists($value["dept_belong"], $department)) {
                    $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
                }

                $tag = $this->dept[$value["dept_belong"]];
                if (!array_key_exists('all', $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$tag] = array(
                        "id" => $tag,
                        "name" => $value["dept_belong"].'全部'
                    );
                }

                if (!array_key_exists($value["dept"], $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$value["dept"]] = array(
                        "id" => $value["dept"],
                        "name" => $value["deptname"]
                    );
                    $dept[] = $value["dept"];
                }
            }
        } else {
            $result = D("DepartmentIdentify")->findAllDept();
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["dept_belong"], $department)) {
                    $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
                }
                $tag = $this->dept[$value["dept_belong"]];
                if (!array_key_exists($tag, $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$tag] = array(
                        "id" => $tag,
                        "name" => $value["dept_belong"].'全部'
                    );
                }

                if (!array_key_exists($value["id"], $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$value["id"]] = array(
                        "id" => $value["id"],
                        "name" => $value["name"]
                    );
                }
            }
        }

        if (I("get.group_name") !== "") {
            $depts = array();
            if (!is_numeric(I("get.group_name"))) {
                foreach ($this->dept as $key => $value) {
                    if (I("get.group_name") == $value) {
                        $belong = $key;
                        break;
                    }
                }
                foreach ($department[$belong]["child"] as $key => $value) {
                   $depts[] = $value["id"];
                }
                unset($dept[0]);
            } else {
                $depts[] = I("get.group_name");
            }
        }

        $depts = array_unique($depts);

        $pageIndex = 1;
        $pageCount = 20;
        if (!empty($_GET["p"])) {
            $pageIndex = $_GET["p"];
        }

        $status = I('get.status');
        $condition['a.visible'] = '0';
        $info['status'] = $status;

        if ($status == '2') {
            unset($condition['a.visible']);
        }

        $condition['a.type'] = array('EQ', '1');

        if (count($ids) > 0) {
            $condition['a.id'] = ['IN',$ids];
        }

        if(I('get.src')){
            $condition['a.src'] = ['eq',trim(remove_xss(I('get.src')))];
        }
        if(I('get.source')){
            $condition['a.name'] = ['like',"%".trim(remove_xss(I('get.source')))."%"];
        }

        if (I('get.groupid') !== "" && I('get.sub_groupid') !== "") {
            $condition['a.groupid'] = array("EQ", I('get.sub_groupid'));
        } elseif (I('get.groupid') !== "" && I('get.sub_groupid') == "") {
            $condition['_complex'] =  array(
                "g.parentid" => array("EQ", I('get.groupid')),
                "a.groupid" => array("EQ",I('get.groupid')),
                "_logic" => "OR"
            );
        }

        if(I('get.charge')){
            $condition['a.charge'] = ['eq',trim(remove_xss(I('get.charge')))];
        }

        if(I('get.show') !== ""){
            $condition['a.isshow'] = ['eq',trim(remove_xss(I('get.show')))];
        }

        if (I("get.group_name") !== "") {
            $condition['a.dept'] = ['IN',$depts];
        }

        if (I("get.alias") !== "") {
            $condition['a.alias'] = ['EQ',I("get.alias")];
            $this->assign("alias", array(array("id"=>I("get.alias"),"text"=>I("get.alias"))));
        }

        $source_in = I("get.source_in");
        if (!empty($source_in) || $source_in == '0') {
            if ($source_in == '0') {
                $condition['r.id'] = ['exp', 'is null'];
            } else {
                $condition['r.source_in'] = ['eq', $source_in];
            }
        }

        if(I('get.account_id')){
            $condition['a.account_id'] = ['eq', trim(remove_xss(I('get.account_id')))];
        }

        $result = $this->getList($condition, $pageIndex, $pageCount);
        $info["list"] = $result['list'];
        $info["page"] = $result['page'];
        //来源组
        $result = D('OrderSource')->getSourceGroup('1', $depts, '', true);
        $info["group"] = array(
            array(
                'id' => "",
                'name' => '请选择',
                'children' => array(
                    array(
                        'id' => "",
                        'name' => '请选择'
                    )
                )
            )
        );
        foreach ($result as $key => $value) {
            if (!empty($value["id"])) {
                if (!array_key_exists($value["parentid"],$group)) {
                    $group[$value["parentid"]]["id"] = $value["parentid"];
                    $group[$value["parentid"]]["name"] = $value["parent_name"];
                    $group[$value["parentid"]]["children"][] = array(
                        "id" => "",
                        "name" => "请选择"
                    );
                }
                $group[$value["parentid"]]["children"][] = array(
                    "id" => $value["id"],
                    "name" => $value["name"],
                    "category" => $value["category"],
                );
            } else {
                if (!array_key_exists($value["parentid"],$info["group"])) {
                    if (!empty($value["parent_name"])) {
                        $group[$value["parentid"]]["id"] = $value["parentid"];
                        $group[$value["parentid"]]["name"] = $value["parent_name"];
                        $group[$value["parentid"]]["category"] = $value["category"];
                        $group[$value["parentid"]]["children"][] = array(
                            "id" => "",
                            "name" => "请选择"
                        );
                    }
                }
            }
        }
        $info["group"] = array_merge($info["group"],$group);
        $info["group"] = json_encode($info["group"]);

        // 获取订单标识
        $inLogic = new OrderSourceInLogicModel();
        $sourceIn = $inLogic->getSourceInSelect();
        $this->assign("source_in", $sourceIn);

        // 查询渠道账户
        $orderSourceAccountLogic = new OrderSourceAccountLogicModel();
        $accountList = $orderSourceAccountLogic->getSourceAccountAll();
        $this->assign("account_list", $accountList);

        $this->assign("source", $source);
        $this->assign("department", $department);
        $this->assign('info', $info);
        $this->display();
    }

    //增加
    public function add(){
        if(IS_POST){
            $post = I('post.');
            $data['src'] = trim($post['src']);
            $data['name'] = $post['name'];
            $data['groupid'] = $post['groupid'];
            $data['dept'] = $post['dept'];
            $data['description'] = $post['description'];
            $data['type'] = '1';
            $data['charge'] = $post['charge'];
            $data['redirect'] = $post['redirect'];
            $data['addtime'] = time();
            $data['isshow'] =  $post['isshow'];

            //随机6位数字渠道别名
            $data['alias'] = "QD".unique_rand();

            if (!empty($post["sub_groupid"])) {
                $data['groupid'] = $post["sub_groupid"];
            }

            if (empty($data['groupid'])) {
                $this->_error('请添加所属来源组 :)');
            }

            //添加渠道和订单标识关联
            if (!empty($post["source_in"]) || $post["source_in"] == 0) {
                $sourceLogic = new OrderSourceLogicModel();
                $sourceLogic->saveOrderSourceInRelation($data['src'],$post["source_in"]);
            }
            if (M('order_source')->add($data)){
                //添加操作日志
                $logData = array(
                    "remark"=>$action ."订单渠道来源标识【添加】",
                    "action_id"=> "",
                    "info"=>I("post."),
                    "logtype"=>"ordersource"
                );
                D('LogAdmin')->addLog($logData);

                $this->success('增加渠道来源成功 :)','/ordersource');
            }else{
                $this->_error('增加渠道来源失败 :)');
            }
        }else{
            $info["group"] = array(
                array(
                    'id' => "",
                    'name' => '请选择',
                    'children' => array(
                        array(
                            'id' => "",
                            'name' => '请选择'
                        )
                    )
                )
            );

            $result = D('OrderSource')->getSourceGroup('1', null);

            foreach ($result as $key => $value) {
                if (!empty($value["id"])) {
                    if (!array_key_exists($value["parentid"],$group)) {
                        $group[$value["parentid"]]["id"] = $value["parentid"];
                        $group[$value["parentid"]]["name"] = $value["parent_name"];
                        $group[$value["parentid"]]["children"][] = array(
                            "id" => "",
                            "name" => "请选择"
                        );
                    }
                    $group[$value["parentid"]]["children"][] = array(
                        "id" => $value["id"],
                        "name" => $value["name"]
                    );
                } else {
                    if (!array_key_exists($value["parentid"],$info["group"])) {
                        if (!empty($value["parent_name"])) {
                            $group[$value["parentid"]]["id"] = $value["parentid"];
                            $group[$value["parentid"]]["name"] = $value["parent_name"];
                            $group[$value["parentid"]]["children"][] = array(
                                "id" => "",
                                "name" => "请选择"
                            );
                        }
                    }
                }
            }

            $info["group"] = array_merge($info["group"],$group);
            $info["group"] = json_encode($info["group"]);
            $info['dept'] = D('OrderSource')->getDept($depts);
            //获取订单标识
            $inLogic = new OrderSourceInLogicModel();
            $sourceIn = $inLogic->getSourceInSelect();
            $this->assign("source_in", $sourceIn);

            $this->assign('info',$info);
            $this->display();
        }
    }

    /**
     * 获取对应渠道分类的渠道数据
     */
    public function getSourceGroupByCategory()
    {
        if (IS_POST) {
            $category = (!empty(I('post.category'))) ? I('post.category') : 1;
            $group = [];
            $info["group"] = array(
                array(
                    'id' => "",
                    'name' => '请选择',
                    'children' => array(
                        array(
                            'id' => "",
                            'name' => '请选择'
                        )
                    )
                )
            );

            $result = D('OrderSource')->getSourceGroup('', '', $category);

            foreach ($result as $key => $value) {
                if (!empty($value["id"])) {
                    if (!array_key_exists($value["parentid"], $group)) {
                        $group[$value["parentid"]]["id"] = $value["parentid"];
                        $group[$value["parentid"]]["name"] = $value["parent_name"];
                        $group[$value["parentid"]]["children"][] = array(
                            "id" => "",
                            "name" => "请选择"
                        );
                    }
                    $group[$value["parentid"]]["children"][] = array(
                        "id" => $value["id"],
                        "name" => $value["name"]
                    );
                } else {
                    if (!array_key_exists($value["parentid"], $info["group"])) {
                        if (!empty($value["parent_name"])) {
                            $group[$value["parentid"]]["id"] = $value["parentid"];
                            $group[$value["parentid"]]["name"] = $value["parent_name"];
                            $group[$value["parentid"]]["children"][] = array(
                                "id" => "",
                                "name" => "请选择"
                            );
                        }
                    }
                }
            }

            $group = array_merge($info["group"], $group);
            $this->ajaxReturn(['error_code' => 0, 'info' => $group]);
        }
    }

    //修改
    public function edit(){
        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('不是正确的来源标识 :(');
        }
        $Db = D('OrderSource');
        $info = $Db->getById($id);
        if (empty($info["parentid"])) {
            $info["parentid"] = $info["groupid"];
            unset($info["groupid"]);
        }
        if(empty($info)){
            $this->_error('不是正确的来源标识 :(');
        }
        //设置能修改渠道来源标识的名称 权限
        $editStyle = false;
        if (in_array($this->User['uid'], ['51', '37', '4', '1'])) {
            $editStyle = true;
        }
        if(IS_POST){
            $post = I('post.');

            $data['name'] = $post['name'];
            $data['groupid'] = $post['groupid'];
            $data['dept'] = $post['dept'];
            $data['charge'] = $post['charge'];
            $data['account_id'] = $post['account_id'];
            $data['description'] = $post['description'];
            $data['redirect'] = $post['redirect'];
            $data['isshow'] =  $post['isshow'];

            if (!empty($post["sub_groupid"])) {
                $data['groupid'] = $post["sub_groupid"];
            }

            if (empty($data['groupid'])) {
                $this->_error('请添加所属来源组 :)');
            }

            $status = $Db->edit($id, $data);
            if ($status || $status == 0) {
                //添加渠道和订单标识关联
                if (!empty($post["source_in"]) || $post["source_in"] == 0) {
                    $sourceLogic = new OrderSourceLogicModel();
                    $sourceLogic->saveOrderSourceInRelation($info['src'], $post["source_in"]);
                }
                $logData = array(
                    "remark" => $action . "订单渠道来源标识【编辑】",
                    "action_id" => $id,
                    "info" => I("post."),
                    "logtype" => "ordersource"
                );
                D('LogAdmin')->addLog($logData);
                $this->success('修改来源标识成功 :)', '/ordersource');
            } else {
                $this->_error('修改来源标识失败 :)');
            }
        }else{
            $info["group"] = array(
                array(
                    'id' => 0,
                    'name' => '请选择',
                    'children' => array(
                        array(
                            'id' => 0,
                            'name' => '请选择'
                        )
                    )
                )
            );
            //获取当前src的渠道分类
            $result = D('OrderSource')->getSourceGroup('1',$depts,$info['category']);

            foreach ($result as $key => $value) {
                if (!empty($value["id"])) {
                    if (!array_key_exists($value["parentid"],$group)) {
                        $group[$value["parentid"]]["id"] = $value["parentid"];
                        $group[$value["parentid"]]["name"] = $value["parent_name"];
                        $group[$value["parentid"]]["children"][] = array(
                            "id" => "",
                            "name" => "请选择"
                        );
                    }
                    $group[$value["parentid"]]["children"][] = array(
                        "id" => $value["id"],
                        "name" => $value["name"]
                    );
                } else {
                    if (!array_key_exists($value["parentid"],$info["group"])) {
                        if (!empty($value["parent_name"])) {
                            $group[$value["parentid"]]["id"] = $value["parentid"];
                            $group[$value["parentid"]]["name"] = $value["parent_name"];
                            $group[$value["parentid"]]["children"][] = array(
                                "id" => "",
                                "name" => "请选择"
                            );
                        }
                    }
                }
            }
            $info["group"] = array_merge($info["group"],$group);
            $info["group"] = json_encode($info["group"]);

            $info['deptList'] = D('OrderSource')->getDept($depts);

            // 获取订单标识
            $inLogic = new OrderSourceInLogicModel();
            $sourceIn = $inLogic->getSourceInSelect();
            $this->assign("source_in", $sourceIn);

            // 获取渠道账户
            $orderSourceAccountLogic = new OrderSourceAccountLogicModel();
            $accountList = $orderSourceAccountLogic->getSourceAccountAll();
            $this->assign("account_list", $accountList);

            $this->assign("editStyle",$editStyle);
            $this->assign("info",$info);
            $this->display();
        }
    }

    public function editSourceAlias()
    {
        $model = D("OrderSource");
        $all =  $model->getAllSource(1);
        foreach ($all as $key => $value) {
            $data = array(
                "alias" => "QD".unique_rand()
            );
            $model->editSource($value["id"],$data);
        }
    }


    //修改状态
    public function setstatus(){
        $id = I('get.id');
        if(empty($id) || !is_numeric($id)){
            $this->_error('不是正确的来源标识 :(');
        }
        $Db = D('OrderSource');
        $info = $Db->getById($id);
        if(empty($info)){
            $this->_error('不是正确的来源标识 :(');
        }

        $data['visible'] = I('get.type') == '1' ? '0' : '1';

        if ($Db->edit($id,$data)){
            $this->success('修改来源标识状态成功 :)');
        }else{
            $this->_error('修改来源标识状态失败 :)');
        }
    }

    public function delsourcegroup()
    {
        if ($_POST) {
            $id = I("post.id");

            //查询是否有子模块
            $count =  D('OrderSource')->getSourceGroupChildCount($id);
            if ($count > 0) {
                $this->ajaxReturn(array("status"=>0 ,"info" => "该来源组还有二级来源组，请先删除二级来源组！"));
            }

            //查询该模块下是否有发单位置
            $count = D('OrderSource')->findSourceByGroupIdCount($id);
            if ($count > 0) {
                $this->ajaxReturn(array("status"=>0 ,"info" => "该模块下还有发单位置标识,请先删除发单位置标识！"));
            }

            $i = D('OrderSource')->delsourcegroup($id);

            if ($i !== false) {
                $this->ajaxReturn(array("status"=> 1 ,"info" => "删除成功！"));
            }
            $this->ajaxReturn(array("status"=>0 ,"info" => "删除失败！"));
        }
    }

    public function delOrderSource()
    {
        if ($_POST) {
            $id = I("post.id");
            //查询该模块下是否有发单位置
            $data = D('OrderSource')->getById($id);

            if (!$data) {
                $this->ajaxReturn(array("status"=>0 ,"info" => "未找到该渠道来源标识！"));
            }
            $save['visible'] = 1;
            $i = D('OrderSource')->edit($id, $save);

            if ($i !== false) {
                $this->ajaxReturn(array("status"=> 1 ,"info" => "删除成功！"));
            }
            $this->ajaxReturn(array("status"=>0 ,"info" => "抱歉删除失败！"));
        }
    }

    //验证标记代号 食肉已经存在
    public function checkSrc()
    {
        $src = I('get.src');
        $i = D('OrderSource')->getInfoBySrc($src);
        if($i){
            $this->ajaxReturn(array("status"=> 0 ,"info" => "标记代号数据库中已存在，请更换标记代号。"));
        }else{
            $this->ajaxReturn(array("status"=> 1 ,"info" => ""));
        }
    }

    //发单位置首页
    public function location(){
        //自定义
        $substart = date("m/d/Y",strtotime("01/01/2015"));
        $subend = date("m/d/Y");
        $this->assign("substart",$substart);
        $this->assign("subend",$subend);

        if (I("get.start") !== "" && I("get.end") !== "") {
            $substart = I("get.start");
            $subend = I("get.end");
        } else {
            $substart = "";
            $subend = "";
        }

        //获取所有的发单位置
        $result = D("OrderSource")->getLocationSource();
        $group[] = array(
            "id" =>"",
            "name" => "全部",
            "child" => [
                [
                    "id" => "",
                    "name" => "全部位置"
                ]
            ]
        );
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["second_id"],$group)) {
                $group[$value["second_id"]] = array(
                    "id" => $value["second_id"],
                    "name" => $value["second_name"]
                );

                $group[$value["second_id"]]["child"][] = array(
                    "id" => $value["second_id"]."-all",
                    "name" => $value["second_name"]."全部"
                );
            }

            $group[$value["second_id"]]["child"][] = array(
                "id" => $value["three_id"],
                "name" => $value["three_name"]
            );
        }

        $result = $this->getLocationList(I("get.src"),I("get.source"),I("get.group"),I("get.visible"), $substart,$subend,I("get.p"));

        $this->assign("source",$group);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    public function sourceGroup()
    {
        //获取模版信息
        $result  = D("OrderSource")->getAllGroup(2);
        $group = [];
        foreach ($result as $key => $value) {
            if ($value["parentid"] == 0) {
                if (!array_key_exists($value["id"],$group)) {
                    $group[$value["id"]] = $value;
                }
            } else {
                $group[$value["parentid"]]["child"][] = $value;
            }
        }
        $this->assign("group",$group);
        $this->display();
    }

    public function srcGroup()
    {
        //获取渠道组数据
        $list = $this->getSrcGroupList('',true);
        $this->assign("list",$list);
        $this->display();
    }

    public function getSrcGroup()
    {
        $category = I('post.category');
        //获取渠道组数据
        $list = $this->getSrcGroupList($category);
        if(count($list)>0){
            $this->ajaxReturn(['error_code' => 0, 'info' => $list]);
        }else{
            $this->ajaxReturn(['error_code' => 400, 'info' => '未查找到家具渠道组数据!']);
        }
    }

    public function srcgroupup()
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "parentid" => I("post.group"),
                "category" => I("post.category"),
                "name" => trim(I("post.name")),
                "type" => 1
            );

            if (empty($data["name"])) {
                $this->ajaxReturn(array("status"=> 0 ,"info" => "来源组名称不能为空！"));
            }

            if (!empty($id)) {
                if ($id == $data["parentid"]) {
                    $this->ajaxReturn(array("status"=> 0 ,"info" => "来源组不能添加自己!"));
                }

                $i = D("OrderSource")->editGroup($id,$data);
            } else {
                $i = D("OrderSource")->addGroup($data);
            }

            if ($i !== false) {
                $logData = array(
                    "remark"=>$action ."渠道来源组管理",
                    "action_id"=> $id,
                    "info"=>I("post."),
                    "logtype"=>"srcgroup"
                );
                D('LogAdmin')->addLog($logData);
                $this->ajaxReturn(array("status"=> 1 ,"info" => "保存成功！"));
            }
            $this->ajaxReturn(array("status"=> 0 ,"info" => "添加失败，请重新添加！"));
        }
    }

    public function getOrderSourceGroupTmp()
    {
        if (I("get.id") !== "") {
            //查询组信息
            $info = D("OrderSource")->findGroupInfo(I("get.id"));

            $this->assign("info",$info);
        }
        //获取一级分类
        $group =  D("OrderSource")->getAllGroup(2,1,false,0);

        $this->assign("group",$group);

        $tmp = $this->fetch("ordersourcegrouptmp");
        $this->ajaxReturn(array("data"=>$tmp));
    }

    public function groupUp()
    {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "name" => I("post.name"),
                "type" => I("post.type"),
                "parentid" => I("post.parentid")
            );

            if (empty($data["name"])) {
                $this->ajaxReturn(array("info"=>"请填写发单位置组名称","status" => 0));
            }

            if (!empty($id)) {
                $i = D("OrderSource")->editGroup($id,$data);
            } else {
                $data["addtime"] = time();
                $i = D("OrderSource")->addGroup($data);
            }
            if ($i !== false) {
                //添加操作日志
                $logData = array(
                        "remark"=>"操作发单位置组",
                        "action_id"=>$id,
                        "info"=>json_encode($data),
                        "logtype"=>"sourcegroupup"
                );
                D('LogAdmin')->addLog($logData);
                $this->ajaxReturn(array("status" => 1));
            }
            $this->ajaxReturn(array("info"=>"操作失败！","status" => 0));
        }
    }

    //增加
    public function locationup(){
        if(IS_POST){
            $post = I('post.');
            $data['name'] = $post['name'];
            $data['groupid'] = $post['groupid'];
            $data['thumb'] = $post['img_url'];
            $data['description'] = $post['description'];
            $data['type'] = '2';
            $data['addtime'] = empty(I("post.time"))?"":strtotime(I("post.time"));
            $id = I("post.id");
            if (!empty($id)) {
                $i = D('OrderSource')->editSource($id,$data);
            } else {
                //年月日+随机两位数 17093001
                $data['src'] = date("ymds");
                $id = $i = D('OrderSource')->addSource($data);
            }

            if ($i !== false){
                //添加操作日志
                $logData = array(
                        "remark"=>"操作发单位标识",
                        "action_id"=>$id,
                        "info"=>json_encode($data),
                        "logtype"=>"sourceup"
                );
                D('LogAdmin')->addLog($logData);
                $this->ajaxReturn(array("info"=>"增加渠道来源标识成功！","status" => 1));
            }
            $this->ajaxReturn(array("info"=>"增加渠道来源标识失败！","status" => 0));
        }else{
            $id = I("get.id");
            if (!empty($id)) {
                $info["info"] = D('OrderSource')->getById($id);
                $info["info"]['thumb_json'] = [];
                if (!empty($info["info"]['thumb'])) {
                    $src = "http://".C("QINIU_DOMAIN")."/".$info["info"]['thumb'];
                    $info["info"]['thumb_json'] = json_encode([
                        '<img src="'.$src.'" class="file-preview-image" >'
                    ]);
                }
            }
            $result  = D("OrderSource")->getAllGroup(2);
            $group = [];
            foreach ($result as $key => $value) {
                if ($value["parentid"] == 0) {
                    if (!array_key_exists($value["id"],$group)) {
                        $group[$value["id"]] = $value;
                    }
                } else {
                    $group[$value["parentid"]]["child"][] = $value;
                }
            }
            $info['sourceGroup'] = $group;
            $this->assign("info",$info);
            $this->display();
        }
    }

    //增加来源组
    public function addSourceGroup(){
        if(!IS_POST){
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
        }
        $post = I('post.');

        $data['addtime'] = time();

        //推广来源
        if($post['type'] == 'tg'){
            $data['type'] = '1';
        }

        //发单位置
        if($post['type'] == 'wz'){
            $data['type'] = '2';
        }

        $data['name'] = $post['name'];
        if (M('order_source_group')->add($data)){
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
        }
    }

    /**
     * 查询渠道信息 by alias 渠道代号
     * @return [type] [description]
     */
    public function findsrcinfo()
    {
        if ($_POST) {
            $src = I("post.q");
            $result = D("OrderSource")->findSrcList($src);
            $this->ajaxReturn(array('data'=>$result,'info'=>'操作成功','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'查询失败！','status'=>0));
    }

    public function tendency(){
        $search = I('get.');
        if($search['start'] || $search['end']){
            if(empty($search['start'])){
                $this->error('缺少开始时间!');
            }
            if(empty($search['end'])){
                $this->error('缺少结束时间!');
            }
            if((strtotime($search['end'])-strtotime($search['start']))>31*24*60*60){
                $this->error('查询时间不能超过1个月!');
            }
        }
        $orderSourceLogic = new OrderSourceLogicModel();
        //渠道来源组
        $group = $orderSourceLogic->getSourceGroupList();
        //渠道来源
        $src = $orderSourceLogic->getSrcByGroup($search);
        //部门
        $department = $orderSourceLogic->getDepartmentList();
        $depts = [];
        if ($search['dept']) {
            if (!is_numeric($search['dept'])) {
                foreach ($this->dept as $key => $value) {
                    if ($search['dept'] == $value) {
                        $belong = $key;
                        break;
                    }
                }
                foreach ($department[$belong]["child"] as $key => $value) {
                    $depts[] = $value["id"];
                }
                unset($depts[0]);
            } else {
                $depts[] = $search['dept'];
            }
        }
        $list = $orderSourceLogic->getOrderSourcesList($search,$depts);
        $this->assign('department',$department);
        $this->assign('group',$group);
        $this->assign('src',$src);
        $this->assign('list',$list);
        $this->display();
    }

    //根据渠道组取渠道
    public function ajaxSrcList()
    {
        $orderSourceLogic = new OrderSourceLogicModel();
        $list =$orderSourceLogic->getSrcByGroup(I('get.'));
        $this->ajaxReturn(['status' => 1, 'info' => $list]);
    }

    // 根据来源名称获取列表
    public function getSourceListByName(){
        $name = I('get.name');
        if(empty($name)){
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '获取失败!']);
        }
        if (IS_AJAX) {
            $sourceLogic = new OrderSourceLogicModel();
            $list = $sourceLogic->getSourceBySourceName($name);
            $this->ajaxReturn(['error_code' => 0, 'list' => $list]);
        }
    }

    //获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10){
        ini_set('memory_limit', '512M');
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $result = D("OrderSource")->getList($condition,($pageIndex-1) * $pageCount,$pageCount);

        $count = $result['count'];
        $list = $result['result'];
        import('Library.Org.Util.Page');
        $p = new \Page($count,$pageCount);
        $p->setConfig('header','个申请');
        $p->setConfig('prev', "上一页");
        $p->setConfig('next', '下一页');
        $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $pageTmp = $p->show();
        return array("list"=>$list,"page"=>$pageTmp);
    }

    /**
     * 渠道来源标识
     * @param  [type] $src       [位置标识]
     * @param  [type] $source    [位置名称]
     * @param  [type] $group     [所属模块]
     * @param  [type] $visible   [启用状态]
     * @param  [type] $start     [开始时间]
     * @param  [type] $end       [结束时间]
     * @param  [type] $pageIndex [description]
     * @return [type]            [description]
     */
    private function getLocationList($src,$source,$group,$visible,$start,$end,$pageIndex)
    {
        if (!empty($start) && !empty($end)) {
            $start = strtotime($start);
            $end = strtotime($end)+86400;
        }

        if (strpos($source,"-all") > 0) {
            $group = str_replace("-all","",$source);
            unset($source);
        }

        $count = D("OrderSource")->getLocationListCount($src,$source,$group,$visible,$start,$end);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $list = D("OrderSource")->getLocationList($src,$source,$group,$visible,$start,$end,$p->firstRow,$p->listRows);
        }

        return array("list"=>$list,"page"=>$show);
    }

    /**
     * 获取渠道组数据
     * @return [type] [description]
     */

    /**
     * 获取渠道组数据
     * @param int $category 渠道分类 1.装修2.家具
     * @return mixed
     */
    private function getSrcGroupList($category = 1,$is_all = '')
    {
        $result = D("OrderSource")->getAllGroup(1,$category,$is_all);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["id"],$list) && $value["parentid"] == 0) {
                $list[$value["id"]] = array(
                    "id" => $value["id"],
                    "name" => $value["name"],
                    "category" => $value["category"],
                    "child" => array()
                );
            } else {
                if(isset($list[$value["parentid"]]["child"])){
                    $list[$value["parentid"]]["child"][] = array(
                        "id" => $value["id"],
                        "name" => $value["name"],
                        "category" => $value["category"],
                        "parentid" => $value["parentid"]
                    );
                }
            }
        }
        return $list;
    }


    // 渠道账户管理
    public function accountlist(){
        $input = array(
            "account_name" => I("get.account_name"),
            "enabled" => I("get.enabled"),
        );

        $orderSourceAccountLogic = new OrderSourceAccountLogicModel();
        $data = $orderSourceAccountLogic->getSourceAccountPageList($input);

        $this->assign("input", $input);
        $this->assign("data", $data);
        $this->display();
    }

    // 渠道账户管理
    public function accountsave(){
        $id = I("post.id");
        $account_name = I("post.account_name");
        $enabled = I("post.enabled", 1);

        if (empty($account_name)){
            $this->ajaxReturn([
                "error_code" => 500,
                "error_msg" => "请先输入渠道账户名称"
            ]);
        }

        $operator = session("uc_userinfo.id");

        $orderSourceAccountLogic = new OrderSourceAccountLogicModel();
        $ret = $orderSourceAccountLogic->setSourceAccount($account_name, $enabled, $operator, $id);

        $this->ajaxReturn([
            "error_code" => $ret["errcode"],
            "error_msg" => $ret["errmsg"]
        ]);
    }

    // 匹配标识
    public function accountrelated(){
        if (IS_POST) {
            $account_id = I("post.account_id");
            $order_source_ids = I("post.order_source_ids");

            if (is_string($order_source_ids)) {
                $order_source_ids = explode(",", $order_source_ids);
            }

            $order_source_ids = array_filter(array_unique($order_source_ids));

            if (count($order_source_ids) == 0){
                $this->ajaxReturn([
                    "error_code" => 0,
                    "error_msg" => "请先选择渠道标识",
                ]);
            }

            $orderSourceLogic = new OrderSourceLogicModel();
            $ret = $orderSourceLogic->setSourceRelatedAccount($account_id, $order_source_ids);
            if ($ret == false){
                $this->ajaxReturn([
                    "error_code" => 500,
                    "error_msg" => "操作失败",
                ]);
            }

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "请求成功",
            ]);
        }

        $account_id = I("get.account_id");
        $account_name = I("get.account_name");
        $this->assign("account_id", $account_id);
        $this->assign("account_name", $account_name);
        $template = $this->fetch("accountrelated");

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    // 搜索渠道标识（不包括已关联的标识）
    public function search_account_related_src(){
        $keyword = I("post.keyword");

        $list = [];
        if (!empty($keyword)) {
            $orderSourceLogic = new OrderSourceLogicModel();
            $list = $orderSourceLogic->getRelatedSourceByName($keyword);
        }

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "items" => $list
            ]
        ]);
    }
}


