<?php


namespace app\index\controller;


use think\Controller;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;

class Base extends Controller
{
    public function __construct()
    {
        parent::__construct();;
        $this->assign('QZ_HOST',config('QZ_HOST'));
        $this->assign('ACCOUNT_HOST',config('ACCOUNT_HOST'));
        $this->assign('SPACE_HOST',config('SPACE_HOST'));
        $this->assign('API_HOST',config('API_HOST'));
        $this->assign('U_HOST',config('U_HOST'));

        $user = Request::param('user');
        //获取城市
        $cityId = Cache::get('Citycache:CityId:'.$user['uuid']);
        if($cityId){
            $this->assign('cityid',$cityId);
        }else{
            $this->assign('cityid',0);
        }
    }
}