<?php
namespace Home\Common\Controller;
use Common\Controller\BaseController;
class HomeBaseController extends BaseController {
    public function _initialize(){
        // 站点多域名访问支持 动态化httphost
        $schemeAndHost = getSchemeAndHost();
        //$SCHEME_HOST   = $schemeAndHost;
        /** 'isSsl' => boolean false
         * 'scheme' => string 'http'
         * 'scheme_full' => string 'http://'
         * 'host' => string 'm.qizuang.com'
         * 'domain' => string 'qizuang.com'
         * 'scheme_host' => string 'http://m.qizuang.com'
         */
        $this->assign('SCHEME_HOST',$schemeAndHost);

        // 程序运行环境dev prod
        $this->assign('APP_ENV',getAppEnv());

        // 自适应 ws wss
        $schemeWebSocket = 'ws://';
        if ($schemeAndHost['isSsl']) {
            $schemeWebSocket = 'wss://';
        }
        $this->assign('SCHEME_WEBSOCKET',$schemeWebSocket);

        parent::_initialize();
        $this->User = session('uc_userinfo');
        $this->city = array_filter(getMyCityIds());

        if(empty($this->User)){
            if(IS_AJAX){
                $this->ajaxReturn(array("data"=>"","info"=>"您的登陆超时了,请重新登陆！","status"=>0));
            }else{
                $url = C("UC_URL");
                redirect($url);
            }
        }
        //加载菜单
        $this->initMenu();
        //检查菜单权限
        $this->check_auth();

        $this->assign("USER_TOKEN", $this->User["sales_token"]);
    }

    /**
     * 获取管辖的用户列表
     * @return [type] [description]
     */
    public function getUserList(){
        if($this->User['uid'] == 1){
            $users = D("Adminuser")->getAllUserList();
        }else{
            if(!empty($this->city)){
                //管辖城市
                $users = D("Adminuser")->getMyUserList($this->User["groups"],$this->city,$this->User["id"]);
            }
        }

        //添加名称首字母
        import('Library.Org.Util.App');
        $app = new \app();
        $edition = array();
        foreach ($users as $key => $value) {
            $str = $app->getFirstCharter($value["name"]);
            $users[$key]["char_name"] = $str.$value["name"];
            $edition[] = $str;
        }
        array_multisort($edition, SORT_ASC,SORT_STRING,$users);
        return $users;
    }

    /**
     * 获取用户的角色管辖范围
     * @return [type] [description]
     */
    public function getRoleList(){
        if($this->User['uid'] == 1){
            $roles = D("RbacRole")->getAllRoles();
        }else{
            if(!empty($this->User["groups"])){
                $roles = D("RbacRole")->getMyRoleList($this->User["groups"]);
            }
        }
        return $roles;
    }


    /**
     * 获取菜单列表
     * @return [type] [description]
     */
    public function getChildMenuList($parentid = null,$data = null,$node_list = null){
        $arr = array();
        foreach ($data as $key => $value) {
            if($value["parentid"] == $parentid){
                $child = $this->getChildMenuList($value["nodeid"],$data,$node_list);
                if(!empty($child)){
                    if(!empty($child[$value["nodeid"]])){
                        $value["child"] = $child[$value["nodeid"]];
                    }
                }
                $arr[$value["parentid"]][] = $value;
            }
        }
        return $arr;
    }

    /**
     * 初始化动态菜单
     * @return [type] [description]
     */
    private function initMenu(){
        //获取所有的菜单
        $menus = getMenuList(false);

        //获取自己的权限菜单
        //如果权限菜单不存在
        if(!session("?uc_userinfo.auth_menu")){
            //非管理员加载自己的菜单
            if(session("uc_userinfo.uid") != 1){
                $roleList = D("RbacNodeRole")->getUserRoleNode(session("uc_userinfo.uid"));
                foreach ($roleList as $key => $value) {
                    $nodeList[$value["node_id"]] = $value["node_id"];
                }
            }else{
                //管理员加载全部菜单
                foreach ($menus as $key => $value) {
                    $nodeList[$value["nodeid"]] = $value["nodeid"];
                }
            }
            session("uc_userinfo.auth_menu",$nodeList);
        }

        $auth_menu = session("uc_userinfo.auth_menu");
        $url =  $_SERVER["REQUEST_URI"];
        $path = getUrlPath($url);
        $node = cookie("QZ_ADMIN_NODE");
        //加载自己的菜单
        foreach ($menus as $value) {
            foreach ($auth_menu as $val) {
                if ((string)$value["nodeid"] === (string)$val && $value["version"] == 2) {
                   if(!empty($path) && !empty($node) && $node == $value["nodeid"]){
                        $value["active"] = 1;
                        $parentid = $value["parentid"];
                    }
                    $myMenu[] = $value;
                    break;
                }
            }
        }


        if(!empty($parentid)){
            foreach ($myMenu as $key => $value) {
                if($parentid == $value["nodeid"]){
                    $myMenu[$key]["active"] = 1;
                }
            }
        }

        //合并为树形菜单
        foreach ($myMenu as $key => $value) {
            if($value["level"] == 1){
                $list[$key] = $value;
                $arr = $this->getChildMenuList($value["nodeid"],$myMenu);
                if(count($arr) > 0){
                    $value["child"] = $arr[$value["nodeid"]];
                    $list[$key] = $value;
                }
            }
        }

        $this->assign("base_tree_menu",$list);
    }

    /**
     * 检查菜单权限
     * @return [type] [description]
     */
    public function check_auth($url = ""){
        $path = $_SERVER["REQUEST_URI"];
        if ($path == "/") {
            return true;
        }
        $result = check_user_menu_auth($path);
        if (!$result) {
            if(IS_AJAX){
                $this->ajaxReturn(array("data"=>"","info"=>"您无权访问该页面！","status"=>0));
            }else{
                $this->_error('您无权访问该页面！');
            }
        }
    }

    public function pageError(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->error('您访问的页面被外星人抓走了  _(:3 」∠)_');
    }
}
