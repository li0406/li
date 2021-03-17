<?php
/*  设计师操作 */
namespace Home\Model\Logic;


use Home\Model\LogAdminDesignModel;
use Home\Model\TeamModel;
use Home\Model\UserModel;

class DesignLogicModel
{
    /**
     * @param string $city          城市id
     * @param string $area          区域id
     * @param string $sjsname       设计师姓名/ID
     * @param string $comname       公司名称
     * @param string $truevip       真/假会员
     * @param string $avatar        是否有头像
     * @param string $show          是否首页显示
     *
     **/
    public function getDesignList($city='',$area='',$sjsname='',$comname='',$truevip='',$show=''){
        import('Library.Org.Util.Page');
        $model = new UserModel();
            $map['u.classid'] = 2;  //表示是设计师
        if($city){
            $map['u.cs'] = $city;
        }
        if($area){
            $map['u.qx'] = $area;
        }

        if($comname){       //公司名称
            //先用公司名称模糊搜索出所有满足条件的公司id  然后team表里面comid  in 这些id组成的数组
            $comlist = $model->getCompanyListByName($comname);
            $comidlist = array();
            foreach ($comlist as $key => $val){
                $comidlist[$key] = $val['id'];
            }
            if(!empty($comidlist)){
                $map['u2.id'] = array('in',$comidlist);
            }else{
                $map['u2.id'] = 0;
            }
        }
        if($truevip){   //真/假会员  1表示真会员， 2表示假会员。  3表示非会员
            if($truevip == 1){
                $map['u2.on'] = 2;
                $map['c.fake'] = 0;   //真会员
            }
            if($truevip == 2){
                $map['u2.on'] = 2;
                $map['c.fake'] = 1;   //假会员
            }
            if($truevip == 3){      //非会员
                $map['u2.on'] = array('neq',2);  //表示自然注册用户
            }
        }
        if($show){  //是否首页显示   1为显示，2为不显示
            if($show == 1){
                $map['t.xs'] = 1;
            }else{
                $map['t.xs'] = array('neq',1);
            }
        }
        //
        if($sjsname){
            $map2['u.name'] = array('like','%'.$sjsname.'%');
            if(is_numeric($sjsname)){
                $map2['u.id'] = array('like','%'.$sjsname.'%');
                $map2['_logic'] = 'or';
            }
            $map['_complex'] = $map2;
        }
        //分页
        $pageCount = 20;
        $count = $model->getDesignListCount($map);
        $page = new \Page($count,$pageCount);
        $pageTmp =  $page->show();
        $list = $model->getDesignList($map,$page->firstRow, $page->listRows);
        foreach ($list as $key => $val){
            if($val['on'] == 2 && $val['fake'] == 1){   //表示假会
                $list[$key]['truevip'] = 2;
            }else{
                $list[$key]['truevip'] = 1;
            }
        }
        $return['list'] = $list ? $list : array();
        $return['page'] = $pageTmp;
        return $return;
    }

    //根据设计师id获取设计师信息
    public function getDesignInfoById($id){
        $model = new UserModel();
        $map = [];
        $map['u.id'] = $id;
        $info = $model->getDesignInfoById($map);
        //默认都是可编辑
        $truevip = 2;       //2表示假会员/非会员  可自由编辑

        if($info['comid']){
            //有签约装修公司  查询装修公司是真会员还是假会员
            $getinfo = $model->getCompanyVipInfo($info['comid']);
            if($getinfo['on'] == 2 && $getinfo['fake'] == 0){
                $truevip = 1;
            }
            $info['comname'] = $getinfo['jc'];
        }
        $info['truevip'] = $truevip;
//        dump($info);die;
        return $info;
    }

    //删除
    public function deleteDesignById($id,$action){
        if(empty($id)){
            $return['error_code'] = 400601; //为获取到必要参数
            return $return;
        }
        $model = new UserModel();
        $teammodel = new TeamModel();
        $logmodel = new LogAdminDesignModel();
        //先检查是否可以删除
        //第一步检查该设计师是否有绑定的装修公司， 如果没有，则无法删除，  如果有，则继续下一步
        $hadteam = $teammodel->searchTeamInfoByUserId($id);
        if($hadteam['comid']){
            //表示有绑定装修， 第二步判断所属装修公司是否是假会员
            $cominfo = $model->getCompanyVipInfo($hadteam['comid']);
            if($cominfo['on'] == 2 && $cominfo['fake'] == 1){
                //假会员，进行下一步删除操作
                //查询是否在user_des表存在数据
                $haduserdesinfo = $model->searchUserDesInfo($id);
                //删除user_des表的数据
                $userdes = $model->deleteUserdesInfo($id);
                if($userdes || empty($haduserdesinfo)){
                    //删除user表数据
                    $deleteinfo = $model->delCompany($id);
                    //删除qz_team表数据
                    $teammodel->deleteTeamInfoById($id);
                    if($deleteinfo){
                        //记录日志
                        //导入扩展文件
                        import('Library.Org.Util.App');
                        $app = new \App();
                        $logdata = array(
                            "username" => $_SESSION["uc_userinfo"]["name"],
                            "userid" => $_SESSION["uc_userinfo"]["id"],
                            "ip" => $app->get_client_ip(),
                            "designid" => $id,
                            "type"=>3,
                            "info" => '用户删除设计师【'.$hadteam['name'].'】成功',
                            "addtime" => time(),
                            "action" => $action
                        );
                        $logmodel->addData($logdata);
                        $return['error_code'] = 1; //删除成功
                        return $return;
                    }else{
                        $return['error_code'] = 400602; //删除数据失败
                        return $return;
                    }
                }else{
                    $return['error_code'] = 400602; //删除数据失败
                    return $return;
                }
            }else{
                $return['error_code'] = 400600;     //无法删除
                return $return;
            }
        }else{
            $return['error_code'] = 400600;     //无法删除
            return $return;
        }
    }


    //批量删除设计师
    public function batchDeleteDesign($idlist,$action){
        if(empty($idlist)){
            $return['error_code'] = 400601; //未获取到必要参数
            return $return;
        }
        $model = new UserModel();
        $teammodel = new TeamModel();
        $logmodel = new LogAdminDesignModel();
        //先检查是否可以删除
        foreach($idlist as $key => $val){
            //第一步检查该设计师是否有绑定的装修公司， 如果没有，则无法删除，  如果有，则继续下一步
            $hadteam = $teammodel->searchTeamInfoByUserId($val);
            if($hadteam['comid']){
                //表示有绑定装修， 第二步判断所属装修公司是否是假会员
                $cominfo = $model->getCompanyVipInfo($hadteam['comid']);
                if($cominfo['on'] == 2 && $cominfo['fake'] == 1){
                    //假会员，进行下一步删除操作
                    //查询是否在user_des表存在数据
                    $haduserdesinfo = $model->searchUserDesInfo($val);
                    //删除user_des表的数据
                    $userdes = $model->deleteUserdesInfo($val);
                    if($userdes || empty($haduserdesinfo)){
                        //删除user表数据
                        $deleteinfo = $model->delCompany($val);
                        //删除qz_team表数据
                        $teammodel->deleteTeamInfoById($val);
                        if($deleteinfo){
                            //记录日志
                            //导入扩展文件
                            import('Library.Org.Util.App');
                            $app = new \App();
                            $logdata = array(
                                "username" => $_SESSION["uc_userinfo"]["name"],
                                "userid" => $_SESSION["uc_userinfo"]["id"],
                                "ip" => $app->get_client_ip(),
                                "designid" => $val,
                                "type"=>3,
                                "info" => '用户删除设计师【'.$hadteam['name'].'】成功',
                                "addtime" => time(),
                                "action" => $action
                            );
                            $logmodel->addData($logdata);
                        }else{
                            continue;
                        }
                    }else{
                        continue;
                    }
                }else{
                    continue;
                }
            }else{
                continue;
            }
        }
        $return['error_code'] = 1; //删除成功
        return $return;
    }


    //根据id获取日志
    public function getDesignLogListById($id){
        $model = new LogAdminDesignModel();
        $list = $model->getDesignLogListById($id);
        return $list;
    }

    //验证账号是否存在
    public function checkHadAccount($user){
        $model = new UserModel();
        $return = $model->findCompanyAccountByName($user);
        if($return){
            return true;
        }else{
            return false;
        }

    }

    //新增设计师
    public function addNewDesign($userinfo,$desinfo,$teaminfo,$action){
        $usermodel = new UserModel();
        $logmodel = new LogAdminDesignModel();
        $userid = $usermodel->addCompany($userinfo);
        if($userid){
            $desinfo['userid'] = $userid;
            $desid = $usermodel->addUserdesInfo($desinfo);
            if(!empty($teaminfo)){
                $teaminfo['userid'] = $userid;
                $usermodel->addTeamInfo($teaminfo);
            }
            if($desid){
                //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                $logdata = array(
                    "username" => $_SESSION["uc_userinfo"]["name"],
                    "userid" => $_SESSION["uc_userinfo"]["id"],
                    "ip" => $app->get_client_ip(),
                    "designid" => $userid,
                    "type"=>2,
                    "info" => '用户新增设计师【'.$userinfo['name'].'】成功',
                    "addtime" => time(),
                    "action" => $action
                );
                $logmodel->addData($logdata);
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }

    //编辑设计师信息
    public function editDesigninfo($designid,$userinfo,$desinfo,$teaminfo,$action){
        $model = new UserModel();
        $logmodel = new LogAdminDesignModel();
        $userinfo['id'] = $designid;
        $change = $model->editUserInfo($userinfo);
        if($change !== false){
            //查询user_des表是否有数据，无数据则添加，有数据则修改
            $hadusedesinfo = $model->searchUserDesInfo($designid);
            if($hadusedesinfo){ //有数据
                $change2 = $model->editUserdesInfo($designid,$desinfo);
            }else{
                $desinfo['userid'] = $designid;
                $change2 = $model->addUserdesInfo($desinfo);
            }
            if($teaminfo){
                //查询是否存在已入驻的信息
                $map = [];
                $map['userid'] = $designid;
                $map['zt'] = 2;
                $hadinfo = $model->getTeamInfoByMap($map);
                if($hadinfo){       //有入驻信息， 修改
                    $teaminfo['id'] = $hadinfo['id'];
                    $model->editTeamInfo($teaminfo);

                    //是否存在多余的重复数据，有则删除
                    $map=[];
                    $map['userid'] = $designid;
                    $map['comid'] = $teaminfo['comid'];
                    $map['zt'] = array('neq',2);
                    $hadinfo = $model->getTeamInfoByMap($map);
                    if($hadinfo){
                        $model->deleteTeamInfo($hadinfo);
                    }
                }else{              //无入驻信息，添加入驻信息， 并修改改设计师其他team信息的zt为拒绝
                    //检查是否有对应的 userid 和 comid 的数据
                    $map=[];
                    $map['userid'] = $designid;
                    $map['comid'] = $teaminfo['comid'];
                    $hadinfo = $model->getTeamInfoByMap($map);
                    if($hadinfo){
                        $teaminfo['id'] = $hadinfo['id'];
                        $teaminfo['zt'] = 2;
                        $model->editTeamInfo($teaminfo);
                    }else{
                        $teaminfo['zt'] = 2;
                        $teaminfo['userid'] = $designid;
                        $model->addTeamInfo($teaminfo);
                        //修改userid为designid的所有zt不为2的数据的zt为1
                        $model->changeTeamInfoToRefuse($designid);
                    }
                }

            }
            if($change2 === false){
                return false;
            }else{
                //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                $logdata = array(
                    "username" => $_SESSION["uc_userinfo"]["name"],
                    "userid" => $_SESSION["uc_userinfo"]["id"],
                    "ip" => $app->get_client_ip(),
                    "designid" => $designid,
                    "type"=>1,
                    "info" => '用户编辑设计师【'.$userinfo['name'].'】成功',
                    "addtime" => time(),
                    "action" => $action
                );
                $logmodel->addData($logdata);
                return true;
            }
        }else{
            return false;
        }
    }

    //获取满足条件的所有设计师
    public function getDesignListAll($city='',$area='',$sjsname='',$comname='',$truevip='',$show=''){
        ini_set('memory_limit', '512M');
        import('Library.Org.Util.Page');
        $model = new UserModel();
        $map['u.classid'] = 2;  //表示是设计师
        if($city){
            $map['u.cs'] = $city;
        }
        if($area){
            $map['u.qx'] = $area;
        }

        if($comname){       //公司名称
            //先用公司名称模糊搜索出所有满足条件的公司id  然后team表里面comid  in 这些id组成的数组
            $comlist = $model->getCompanyListByName($comname);
            $comidlist = array();
            foreach ($comlist as $key => $val){
                $comidlist[$key] = $val['id'];
            }
            if(!empty($comidlist)){
                $map['u2.id'] = array('in',$comidlist);
            }
        }
        if($truevip){   //真/假会员  1表示真会员， 2表示假会员。  3表示非会员
            if($truevip == 1){
                $map['u2.on'] = 2;
                $map['c.fake'] = 0;   //真会员
            }
            if($truevip == 2){
                $map['u2.on'] = 2;
                $map['c.fake'] = 1;   //假会员
            }
            if($truevip == 3){
                $map['u2.on'] = 0;  //表示自然注册用户
            }
        }
        if($show){  //是否首页显示   1为显示，2为不显示
            if($show == 1){
                $map['t.xs'] = 1;
            }else{
                $map['t.xs'] = array('neq',1);
            }
        }
        //
        if($sjsname){
            $map2['u.name'] = array('like','%'.$sjsname.'%');
            if(is_numeric($sjsname)){
                $map2['u.id'] = array('like','%'.$sjsname.'%');
                $map2['_logic'] = 'or';
            }
            $map['_complex'] = $map2;
        }

        //分页
        $pageCount = 10000;
        $count = $model->getDesignListCount($map);
        $times =  ceil($count/$pageCount);
        $tt = 0;
        $list = array();
        while($tt < $times){
            $first = $tt * $pageCount;
            $listrows = $pageCount;
            $getlist = $model->getDesignList($map,$first, $listrows);
            $list = array_merge($list,$getlist);
            $tt ++ ;
            if(empty($getlist)){
                break;
            }
        }
        foreach ($list as $key => $val){
            if($val['on'] == 2 && $val['fake'] == 1){   //表示假会
                $list[$key]['truevip'] = 2;
            }else{
                $list[$key]['truevip'] = 1;
            }
        }
        $return['list'] = $list ? $list : array();
        return $return;
    }

    //批量导出
    /**
     * 下载Excel
     *
     * @param      array  $title     The title
     * @param      array  $column    The column
     * @param      array  $list      The list
     * @param      string  $filename  The filename
     *
     * @return     mixed
     */
    public function downExcel($title,$filename,$list){


        import('Library.Org.PHP_XLSXWriter.xlsxwriter');

        try {
            $writer = new \XLSXWriter();
            //写入日期
            $writer->writeSheetRow('Sheet1', array('日期：' . date('Y-m-d H:i',time())));
            //标题
            $herder = array(
                '序号',
                '设计师ID',
                '头像',
                '姓名',
                '公司名称',
                '区域',
                '状态',
                '首页显示',
                '添加时间',
                '添加人'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $jsq = 1;
            foreach ($list as $key => $value) {
                if($value['zt'] == 2){
                    $value['zt'] = '入驻';
                }else{
                    $value['zt'] = '';
                }
                if($value['xs'] == 1){
                    $value['xs'] = '是';
                }else{
                    $value['xs'] = '';
                }
                $value['register_time'] = date('Y-m-d',$value['register_time']);
                $v = array(
                    $jsq,
                    $value['id'],
                    $value['logo'],
                    $value['name'],
                    $value['jc'],
                    $value['cname'].'-'.$value['qz_area'],
                    $value['zt'],
                    $value['xs'],
                    $value['register_time'],
                    $value['addname'],
                );
                $writer->writeSheetRow('Sheet1', $v);
                $jsq++;
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="设计师管理.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        } catch (Exception $e) {
            echo 'something error';
        }
    }

}