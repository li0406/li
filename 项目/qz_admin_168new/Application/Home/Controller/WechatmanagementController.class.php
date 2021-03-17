<?php

//微信公众号管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class WechatmanagementController extends HomeBaseController{
    /**
     * 微信渠道关联设置
     */
    public function index(){

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

        if(I('get.src')){
            $condition['a.src'] = ['eq',trim(remove_xss(I('get.src')))];
        }

        if(I('get.source_group')){
            $condition['a.groupid'] = ['eq',trim(remove_xss(I('get.source_group')))];
        }

        if(I('get.group_name')){
            $condition['a.dept'] = ['eq',trim(remove_xss(I('get.group_name')))];
        }

        if(I('get.charge')){
            $condition['a.charge'] = ['eq',trim(remove_xss(I('get.charge')))];
        }

        $result = $this->getList($condition, $pageIndex, $pageCount);

        //来源组
        $group  = D("OrderSource")->getAllGroup();

        //部门
        $department = D("DepartmentIdentify")->getAllDepartment();

        //判断当前记录的微信名称是否设置
        foreach($result['list'] as $key => $value){
            $record = D("YySrcWeixin")->getOneBySourceid($value['id']);
            if($record){
                $result['list'][$key]['wxname'] = $record['name'];
                $result['list'][$key]['source_id'] = $record['source_id'];
            }else{
                $result['list'][$key]['wxname'] = '';
                $result['list'][$key]['source_id'] = '';
            }
        }

        $this->assign("group", $group);
        $this->assign("department", $department);
        $this->assign("list", $result['list']);
        $this->assign('page', $result['page']);
        $this->display();
    }



    /**
     *  设置公众号信息
     */
    public function setwechat(){

        if(!empty($_GET['id'])){
            $id = $_GET['id'];
        }

        //获取ordersource表中的一行记录
        $record = D("OrderSource")->getOne($id);
        $wxresult = D("YySrcWeixin")->getOneBySourceid($id);

        $src = $record['src'];
        $groupname = D("OrderSource")->getGroup($record['groupid']);
        $dept = D("DepartmentIdentify")->getDepartment($record['dept']);

        //有记录则显示当前记录,没有则显示默认
        if(empty($wxresult)){
            /*$defaultData = D("YySrcWeixin")->getDefault();
            $desc = str_replace([",", "，", "\r\n", "\r", "\n"], ',', $defaultData['desc']);
            $desc = explode(',', $desc);*/
            $defaultData['name'] = '';
            $defaultData['img'] = '';
            $defaultData['title'] = '';
            $desc = '';
        }else{
            $defaultData['title'] = $wxresult['title'];
            $defaultData['name'] = $wxresult['name'];
            $defaultData['img'] = $wxresult['img'];
            $desc = str_replace([",", "，", "\r\n", "\r", "\n"], ',', $wxresult['desc']);
            $desc = explode(',', $desc);
        }

        $this->assign('title', $defaultData['title']);
        $this->assign('wxname', $defaultData['name']);
        $this->assign("imgurl", $defaultData['img']);
        $this->assign("desc", $desc);
        $this->assign('src', $src);
        $this->assign('groupname', $groupname['name']);
        $this->assign('deptid', $record['dept']);
        $this->assign("dept", $dept['name']);
        $this->assign('sourceid', $id);
        $this->display();
    }

    /**
     *  添加公众号信息
     */
    public function addWeChat(){
        //获取默认值
        $defaultData = D("YySrcWeixin")->getDefaultData();

        if(IS_POST){
            $desc = $_POST['desc'];
            $desc = str_replace(["\r\n", "\r", "\n"], "", $desc);
            if(!empty($desc) || $desc != ''){
                $desc = I("post.desc");
                $desc = trim(str_replace(["\r\n", "\r", "\n"], ',', $desc), ',');
                $data['desc'] = $desc;
            }else{
                $desc = trim(str_replace(["\r\n", "\r", "\n"], ',', $defaultData['desc']), ',');
                $data['desc'] = $desc;
            }
            if(!empty($_POST['wxname'])){
                $data['name'] = I("post.wxname");
            }else{
                $data['name'] = $defaultData['name'];

            }
            if(!empty($_POST['title'])){
                $data['title'] = I("post.title");
            }else{
                $data['title'] = $defaultData['title'];
            }
            if(!empty($_POST['imgurl'])){
                $data['img'] = I("post.imgurl");
            }else{
                $data['img'] = $defaultData['img'];
            }
            if(!empty($_POST['source_id'])){
                $data['source_id'] = I("post.source_id");
            }
        }
        $data['add_time'] = time();
        $data['uid'] = session("uc_userinfo.id");

        //根据sourceid查询是否有记录,有则修改数据,没有则增加数据
        $result = D("YySrcWeixin")->getOneBySourceid($data['source_id']);

        //添加操作日志
        $log = [
            'remark' => '添加公众号信息',
            'logtype' => 'Wechatmanagement',
            'action_id' => $data['source_id'],
            'info' => json_encode($data),
        ];
        D('LogAdmin')->addLog($log);

        if($result){
            $del = D("YySrcWeixin")->deleteData($result['source_id']);
            if(!$del){
                $this->error("保存失败，请检查后重新添加");
            }
        }else{
            //修改数据
            /*$map['wxaccounts_id'] = $result['wxaccounts_id'];

            if(D("YySrcWeixin")->saveData($map, $data)){
                $this->redirect("/Wechatmanagement/index/");
            }else{
                $this->error("保存失败，请检查后重新添加");
            }*/

        }

        //增加数据
        if(D("YySrcWeixin")->addData($data)){
            $this->redirect("/Wechatmanagement/index/");
        }else{
            $this->error("保存失败，请检查后重新添加");
        }
    }


    /**
     *  批量设置微信公众号信息
     */
    public function setbatchwechat(){
        if(IS_POST){
            $ids = $_POST['ids'];
        }

        $this->assign("ids", $ids);
        $this->display();
    }

    //批量添加数据
    public function setBatchData(){
        if(IS_POST){
            $ids = $_POST['ids'];
            $ids = explode(",", $ids);
            foreach($ids as $key => $value){
                if(D("YySrcWeixin")->getOneBySourceid($value)){
                    $ids_exist[] = $value;
//                    unset($ids[$key]);
                }
            }
//            $ids = array_values($ids);
        }
        //已经存在的数据删除后重新添加
        foreach($ids_exist as $key => $value){
            $result = D("YySrcWeixin")->deleteData($value);
            if(!$result){
                $this->error("保存失败，请检查后重新添加");
            }
        }

        $desc = trim(str_replace(["\r\n", "\r", "\n"], ',', $_POST['desc']), ',');

        //获取默认值
        $defaultData = D("YySrcWeixin")->getDefaultData();

        //批量添加数据
        if(!empty($ids)){
            foreach($ids as $key => $value){
                if(!empty($_POST['wxname'])){
                    $data[$key]['name'] = I("post.wxname");
                }else{
                    $data[$key]['name'] = $defaultData['name'];
                }
                if(!empty($_POST['title'])){
                    $data[$key]['title'] = I("post.title");
                }else{
                    $data[$key]['title'] = $defaultData['title'];
                }
                if(!empty($desc)){
                    $data[$key]['desc'] = $desc;
                }else{
                    $data[$key]['desc'] = $defaultData['desc'];
                }
                if(!empty($_POST['imgurl'])){
                    $data[$key]['img'] = I("post.imgurl");
                }else{
                    $data[$key]['img'] = $defaultData['img'];
                }
                $data[$key]['source_id'] = $value;
                $data[$key]['uid'] = session("uc_userinfo.id");
                $data[$key]['add_time'] = time();
            }

            //添加操作日志
            $log = [
                'remark' => '批量添加公众号信息',
                'logtype' => 'Wechatmanagement',
                'action_id' => json_encode($ids),
                'info' => json_encode($data),
            ];
            D('LogAdmin')->addLog($log);
            $addAllResult = D("YySrcWeixin")->addAllData($data);
            if($addAllResult){
                $this->redirect("/Wechatmanagement/index/");
            }else{
                $this->error("保存失败，请检查后重新添加");
            }
        }
    }

    /**
     *  设置默认公众号信息
     */
    public function setdefaultwechat(){
        $result = D("YySrcWeixin")->getDefaultData();
        $name = $title = $desc = $imgurl = '';

        if($result){
            $name = $result['name'];
            $title = $result['title'];
            $imgurl = $result['img'];
            $desc = explode(",", $result['desc']);
        }

        $this->assign("name", $name);
        $this->assign("title", $title);
        $this->assign('imgurl', $imgurl);
        $this->assign("desc", $desc);
        $this->display();
    }

    //设置默认公众号信息
    public function setDefaultData(){
        if(IS_POST){
            if(!empty($_POST['wxname'])){
                $data['name'] = I("post.wxname");
            }else{
                $this->error("请填写微信公众号名称");
            }
            if(!empty($_POST['title'])){
                $data['title'] = I("post.title");
            }else{
                $this->error("请填写微信公众号简介标题");
            }
            if(!empty($_POST['imgurl'])){
                $data['img'] = I("post.imgurl");
            }else{
                $this->error("请添加微信公众号二维码");
            }
            if(!empty($_POST['desc'])){
                $desc = trim(str_replace(["\r\n", "\r", "\n"], ',', $_POST['desc']), ',');
                $data['desc'] = $desc;
            }else{
                $this->error("请填写微信公众号简介详情");
            }
        }

        $data['is_default'] = '1';

        $result = D("YySrcWeixin")->getDefaultData();

        //添加操作日志
        $log = [
            'remark' => '添加默认公众号信息',
            'logtype' => 'Wechatmanagement',
            'action_id' => $data['is_default'],
            'info' => json_encode($data),
        ];
        D('LogAdmin')->addLog($log);

        if(!$result){
            //新增数据
            $data['uid'] = session("uc_userinfo.id");
            $data['add_time'] = time();
        }else{
            //修改数据
            $map['is_default'] = '1';
            if(D("YySrcWeixin")->saveData($map, $data)){
                $this->redirect("/Wechatmanagement/index/");
            }else{
                $this->error("保存失败，请检查后重新添加");
            }
        }

        if(D("YySrcWeixin")->addData($data)){
            $this->redirect("/Wechatmanagement/index/");
        }else{
            $this->error("保存失败，请检查后重新保存");
        }
    }


    //获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10){
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


}