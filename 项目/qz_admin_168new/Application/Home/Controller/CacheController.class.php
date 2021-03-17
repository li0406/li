<?php

//缓存管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class CacheController extends HomeBaseController
{

    public function _initialize(){
        parent::_initialize();
    }

    //首页
    public function index(){

        /*
        redis_key_get
        redis_get
        redis_del
        */

        $group = 'nosql1';
        $result = $this->redis_get($group,'Cache:Groups');
        $result = json_decode($result,true);

        $all = $this->redis_key_get($group,'*');
        dump($all);

        //判断是否开启统计
        $isSwitch = S('Cache:IsSwitch');
        $isSwitch = $this->getStatSwitch($isSwitch);

        $id = I('get.key');
        if(!empty($id)){
            $cacheList = $this->redis_get($group,'Cache:Lists');
            $cacheList = json_decode($cacheList,true);

            //取值
            $item['content'] = $this->redis_get($group,$id);
            if(empty($item['content'])){
                $item['content'] = '值已过期或未设定';
            }
        }


        $group = I('get.group');
        $keyword = I('get.keyword');

        foreach ($result as $k => $v) {
            if(!empty($id) && $k == $id){
                $item['name'] = $k;
                $item['function'] = $v['F'];
                $item['model'] = $v['C'];
                $item['count'] = empty($cacheList[$id]) ? 0 : $cacheList[$id];
            }

            $k = rtrim($k,':');
            $keyArray = explode(':',$k);
            unset($keyArray['0']);
            //dump($keyArray);

            $groupMenu[] = $keyArray['1'];

            //按分组搜索
            if(!empty($group) && $keyArray['1'] != $group){
                continue;
            }

            if(!empty($keyword) && !strripos($k,$keyword)){
                continue;
            }

            $count = count($keyArray);

            $v['N'] = $k;

            if($count <= 1){
                $tree[$keyArray['1']] = $v;
            }elseif($count <= 2){
                $tree[$keyArray['1']][$keyArray['2']] = $v;
            }elseif($count <= 3){
                $tree[$keyArray['1']][$keyArray['2']][$keyArray['3']] = $v;
            }
        }
        ksort($tree);
        $tree = $this->outMenu($tree);

        $groupMenu = array_unique($groupMenu);

        $this->assign("isSwitch",$isSwitch);
        $this->assign('group',$group);
        $this->assign("groupMenu",$groupMenu);
        $this->assign("item",$item);
        $this->assign("tree",$tree);
        $this->display();
    }

    public function remove(){
        $group = 'nosql1';
        $key = I('get.key');
        if(!empty($key)){
            $result = $this->redis_del($group,$key);
            $this->success('删除成功 :)');
            die;
        }
        $this->_error('删除失败 :)');
    }

    public function setStatStatus(){
        $type = I('get.type');
        $status = I('get.status');
        $newStatus = $status == 'off' ? 'on' : 'off';
        $isSwitch = S('Cache:IsSwitch');
        $isSwitch[$type] = $newStatus;
        S('Cache:IsSwitch',$isSwitch,86400 * 30);
        $this->success('修改状态成功 :)');
        die;
    }

    //返回格式化后开启按钮状态
    public function getStatSwitch($status){
        $on = '<button type="button" class="btn btn-danger switch">';
        $off = '<button type="button" class="btn btn-default switch">';

       if($status['mobile'] == 'on'){
            $nstatus['mobile'] = $on.'<i class="fa fa-toggle-on" data-id="on" type-id="mobile"></i>&nbsp;开启移动版</button>';
       }else{
            $nstatus['mobile'] = $off.'<i class="fa fa-toggle-off" data-id="off" type-id="mobile"></i>&nbsp;关闭移动版</button>';
       }

       if($status['old'] == 'on'){
            $nstatus['old'] = $on.'<i class="fa fa-toggle-on" data-id="on" type-id="old"></i>&nbsp;开启老站</button>';
       }else{
            $nstatus['old'] = $off.'<i class="fa fa-toggle-off" data-id="off" type-id="old"></i>&nbsp;关闭老站</button>';
       }

       if($status['new'] == 'on'){
            $nstatus['new'] = $on.'<i class="fa fa-toggle-on" data-id="on" type-id="new"></i>&nbsp;开启新站</button>';
       }else{
            $nstatus['new'] = $off.'<i class="fa fa-toggle-off" data-id="off" type-id="new"></i>&nbsp;关闭新站</button>';
       }

       return $nstatus;
    }

    //输出 菜单
    public function outMenu($tree){
        $html = '';
        foreach ($tree as $k => $v) {
            //dump($v);
            if(is_array($v) && empty($v['F'])){
                $html .= '<li><a href="javascript:;">'.$k.'</a></li><ul>';
                $html .= $this->outMenu($v);
                $html  = $html.'</ul></li>';
            }else{
                $html .= '<li><a href="javascript:;" data-id="'.$v['N'].'" title="'.$v['N'].'" >'.$v['N'].'</a></li>';
            }
        }
        return $html;
    }








    /**
     * redis 获取建列表
     * @param  str $group 分组 是那一组redis
     * @param  str $key   支持的方式 "key" 一个 "key*"" 开始匹配   "*"" 所有
     * @return array
     */
    public function redis_key_get($group, $key){
        if (empty($group)||empty($key)) {
            return '必要参数不能为空!';
        }
        $Redis = D("Redis");
        $Redis->connect($group);
        return $Redis->key_get($key);
    }

    /**
     * redis 获取一个key的值
     * @param  str $group 分组 是那一组redis
     * @param  str $key
     * @return array
     */
    public function redis_get($group, $key){
        if (empty($group)||empty($key)) {
            return '必要参数不能为空!';
        }
        $Redis = D("Redis");
        $Redis->connect($group);
        return $Redis->get($key);
    }

    /**
     * redis 删除key
     * @param  str $group 分组 是那一组redis
     * @param  str $key
     * @return array
     */
    public function redis_del($group, $key){
        if (empty($group)||empty($key)) {
            return '必要参数不能为空!';
        }
        $Redis = D("Redis");
        $Redis->connect($group);
        return $Redis->del($key);
    }

    /**
     * redis 设置值
     * @param  str $group 分组 是那一组redis
     * @param  str $key
     * @return array
     */
    public function redis_set($group,$key,$value,$expire = null){
        if (empty($group)||empty($key)) {
            return '必要参数不能为空!';
        }
        $Redis = D("Redis");
        $Redis->connect($group);
        return $Redis->set($key,$value,$expire);
    }
}