<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*   百度账户城市管理
*/
class CitybaiduController extends HomeBaseController {

    public function _initialize() {
        parent::_initialize();
    }

    //首页
    public function index (){
        $account = I('get.account');
        $city = I('get.city');

        if(!empty($account)){
            $map['baidu_account'] = $account;
        }

        if(!empty($city)){
            $map['cid'] = $city;
        }

        $accounts = D('Citybaidu')->getAccounts();
        $allCity = D('Citybaidu')->getQuyu();

        //取所有城市
        $quyu = D('Citybaidu')->getQuyu($map);

        foreach ($quyu as $k => $v) {
            if(empty($v['baidu_account'])){
                $v['baidu_account'] = '#空账户';
            }
           
            $list[$v['baidu_account']][$v['abc']][] = $v;

            //记录帐号组
            if(empty($accountList[$v['baidu_account']])){
                $accountList[$v['baidu_account']] = $v['baidu_account'];
            }
        }

        import('Library.Org.Util.App');
        $app = new \App();

        //排序
        foreach ($accountList as $key => $value) {
            //增加首字母大写
            $abc = $app->getFirstCharter($value);
            if($value != '#空账户'){
                if(empty($abc)){
                    $abc = substr($value,0,1);
                }
                $accountSort[] = array(
                    'sort' => $abc,'name' => $key,
                );
            }            
            ksort($list[$key]);
        }

        $accountSort = multi_array_sort($accountSort,'sort');

        foreach ($accountSort as $key => $value) {
            $newList[$value['name']] = $list[$value['name']];
        }

        $newList['#空账户'] = $list['#空账户'];

        $this->assign('list',$newList);
        $this->assign('accounts',$accounts);
        $this->assign('allCity',$allCity);
        $this->assign('info',$info);
        $this->display();       
    }

    //新增城市百度帐号
    public function add(){

        if(IS_POST){
            $account = I('post.account');
            if(empty($account)){
                $this->ajaxReturn(array("info"=>"请填写账户名称","status"=>0));
            }
            $city = I('post.city');
            if(empty($city)){
                $this->ajaxReturn(array("info"=>"请选择城市","status"=>0));
            }

            //查询是否有此帐号
            $isHave = D('Citybaidu')->getAccount($account);
            if(!empty($isHave)){
                $this->ajaxReturn(array("info"=>"已有此帐号，不可重复添加","status"=>0));
            }

            $citys = implode($city,',');            
            $data['baidu_account'] = $account;

            D("Citybaidu")->editAccount($data,$city);

            $this->ajaxReturn(array("info"=>"操作成功","status"=>1));   
        }

        //取所有城市
        $quyu = D('Citybaidu')->getQuyu();

        foreach ($quyu as $k => $v) {
            //管理类别 0商务 1外销 2商务外销
            $dept = $v['manager'] == '0' ? 'in' : 'out';
            //城市级别 ABC
            switch ($v['little']) {
                case '0':
                    //a类城市
                    $ABC = 'a';
                    break;
                case '1':
                    //b类城市
                    $ABC = 'b';
                    break;
                case '2':
                    //c类城市
                    $ABC = 'c';
                    break;
            }            
            if($v['baidu_account'] == ''){  
                $list[$dept][$ABC][] = $v;
            }
        }

        $this->assign('list',$list);
        $this->assign('info',$info);
        $this->display();
    }

    //编辑城市百度帐号
    public function edit(){
        $id = I('get.id');

        if(IS_POST){
            $account = I('post.account');
            if(empty($account)){
                $this->ajaxReturn(array("info"=>"请填写账户名称","status"=>0));
            }
            $city = I('post.city');
            if(empty($city)){
                $this->ajaxReturn(array("info"=>"请选择城市","status"=>0));
            }

            $citys = implode($city,',');            
            $data['baidu_account'] = $account;

            //先清空原有设置
            D("Citybaidu")->clearAccount($account);
            
            //设新值
            D("Citybaidu")->editAccount($data,$city);

            $this->ajaxReturn(array("info"=>"操作成功","status"=>1));   
        }

        //取所有城市
        $map['baidu_account'] = array('EQ','');
        $quyu = D('Citybaidu')->getQuyu();

        foreach ($quyu as $k => $v) {
            //管理类别 0商务 1外销 2商务外销
            $dept = $v['manager'] == '0' ? 'in' : 'out';
            //城市级别 ABC
            switch ($v['little']) {
                case '0':
                    //a类城市
                    $ABC = 'a';
                    break;
                case '1':
                    //b类城市
                    $ABC = 'b';
                    break;
                case '2':
                    //c类城市
                    $ABC = 'c';
                    break;
            }

            if($v['baidu_account'] == '' || $v['baidu_account'] == $id){
                if($v['baidu_account'] == $id){
                    $v['selected'] = 'selected';
                }                
                $list[$dept][$ABC][] = $v;
            }                        
        }

        $info['account'] = $id;

        $this->assign('list',$list);
        $this->assign('info',$info);
        $this->display();
    }

    //删除
    public function remove(){

        if(IS_POST){

            $account = I('post.account');

            if(empty($account)){
                $this->ajaxReturn(array("info"=>"请选择账户！","status"=>0));
            }

            //清空帐户
            D("Citybaidu")->clearAccount($account);

            $this->ajaxReturn(array("info"=>"操作成功","status"=>1));
        }

        $this->ajaxReturn(array("info"=>"错误的操作！","status"=>0));
    }

}