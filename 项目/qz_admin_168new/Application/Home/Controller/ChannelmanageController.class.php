<?php
// +----------------------------------------------------------------------
// | ChannelmanageController   渠道JS代码控制器
// +----------------------------------------------------------------------
// | Author: 2851986856@qq.com
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class ChannelmanageController extends HomeBaseController
{

    /**
     * PC落地页模板
     * @var array
     */
    protected static $landing_tpl_pc = array(
        "baojia"    => "baojia",
        "zhaobiao"  => "zhaobiao",
        "sheji"     => "sheji",
    );


    /**
     * Mobile落地页模板
     * @var array
     */
    protected static $landing_tpl_mobile = array(
        "baojia"    => "baojia",
        "zhaobiao"  => "zhaobiao",
        "sheji"     => "sheji",
        "liangfang" => "liangfang",
        "newbaojia" => "newbaojia",
        "baojia1"   => "baojia1",
        "sheji-jzrk"   => "sheji-jzrk",
        "baojia-zst"   => "baojia-zst",
        "sheji-dyqd"   => "sheji-dyqd",
        "baojia-jzrk"   => "baojia-jzrk",
        "sheji-dyqd-2"   => "sheji-dyqd-2",
        "baojia1-jzrk"   => "baojia1-jzrk",
        "jjqwdzqd"   => "jjqwdzqd",
        "jjqwsj"   => "jjqwsj",
        "qwdzbj"   => "qwdzbj",
        "jjqwbj"   => "jjqwbj",
        "baojia2"   => "baojia2",
        "baojia3"   => "baojia3",
        "zhaoshang" => "zhaoshang",
        "gzsjbj" => 'gzsjbj',
        "sheji-xcx"   => "sheji-xcx",
        "qdsj1" => 'qdsj1',
        "qdsj1-xcx" => 'qdsj1-xcx',
        "baojia4"   => "baojia4",
        "mjsj"   => "mjsj",
        "mjsj1"   => "mjsj1",
        "baojiazk" => "baojiazk",
        "qzjzj" => "qzjzj",
        "mthd" => "mthd",
        "baojia-ywwx" => "baojia-ywwx",
        "lyzxhd" => "lyzxhd",
        "sheji-jzrk-shxs" => "sheji-jzrk-shxs",
        "xhxsj" => "xhxsj",
        "zxbj" => "zxbj",
        "baojia10"    => "baojia10",
        "baojia11"    => "baojia11",
        "baojia13"    => "baojia13",
        "baojia14"    => "baojia14",
        "baojia15"    => "baojia15",
        "zxbaojia1"    => "zxbaojia1",
        "zxbaojia2"    => "zxbaojia2",
        "zxbaojia3"    => "zxbaojia3",
        "baojia16"    => "baojia16",
        "xbaojia5"    => "xbaojia5",
        "xbaojia6"    => "xbaojia6",
        "xbaojia4"    => "xbaojia4",
        "sheji-xy1"    => "sheji-xy1",
        "baojiawqz"    => "baojiawqz",
        "shuang11"    => "shuang11",
        "baojia17"    => "baojia17",
        "honor" => "honor"
    );

    /**
     * Mobile淘装落地页模板
     * @var array
     */
    protected static $landing_tpl_m_tz = array(
        "tbaojia"    => "tbaojia",
        "tbaojia1"    => "tbaojia1",
        "tbaojia2"    => "tbaojia2",
        "tbaojia3"    => "tbaojia3",
        "tbaojia4"    => "tbaojia4",
        "tbaojia51"    => "tbaojia51",
        "baojiax"    => "baojiax",
        "baojiay"    => "baojiay"
    );

    /**
     * 获取
     * @param $type 类型可输入 pc mobile
     * @param $mode 模式 data为数组 html为select option html
     * @return array|string
     */
    public static function getAllLdTPL($type='pc',$mode='data'){
        if($type == 'pc') {
            $landing_tpl_pc = self::$landing_tpl_pc;
            if($mode == 'data') {
                return $landing_tpl_pc;
            } else {
                $landing_tpl_pc_html = '';
                foreach($landing_tpl_pc as $key => $value) {
                    $landing_tpl_pc_html .= '<option value="' . $key .'">' . $value . '</option>';
                }
                unset($value);
                return $landing_tpl_pc_html;
            }
        }
        if($type == 'mobile') {
            $landing_tpl_mobile = self::$landing_tpl_mobile;
            if($mode == 'data') {
                return $landing_tpl_mobile;
            } else {
                $landing_tpl_mobile_html = '';
                foreach($landing_tpl_mobile as $key => $value) {
                    $landing_tpl_mobile_html .= '<option value="' . $key .'">' . $value . '</option>';
                }
                unset($value);
                return $landing_tpl_mobile_html;
            }
        }
        if($type == 'm_tz') {
            $landing_tpl_m_tz = self::$landing_tpl_m_tz;
            if($mode == 'data') {
                return $landing_tpl_m_tz;
            } else {
                $landing_tpl_m_tz_html = '';
                foreach($landing_tpl_m_tz as $key => $value) {
                    $landing_tpl_m_tz_html .= '<option value="' . $key .'">' . $value . '</option>';
                }
                unset($value);
                return $landing_tpl_m_tz_html;
            }
        }
        return '';
    }

    /**
     * 渠道JS代码管理
     * @return mixed
     */
    public function js()
    {
        $pageIndex = intval(I('get.p','1'))=== 0 ? 1:intval(I('get.p','1'));
        $pageCount = 20;

        $map = [];
        $status = intval(I('get.status',0));
        if (!empty($status)){
            $map['a.status'] = ['eq', $status];
        }

        $type = intval(I('get.type',0));
        if (!empty($type)){
            $map['a.type'] = ['eq', $type];
        }

        $group = intval(I('get.group',0));
        if (!empty($group)) {
            $map['a.groupid'] = ['eq', $group];
        }

        $result = $this->getList($map, $pageIndex, $pageCount,'create_time desc,id desc');
        //来源组
        $group = D("OrderSource")->getAllGroup(1);

        $this->assign("group", $group);
        $this->assign("list", $result['list']);
        $this->assign('page', $result['page']);
        $this->display();
    }

    /**
     * 添加页面
     */
    public function add()
    {
        if (IS_POST){
            $data = I('post.');

            foreach ($data as $key => $value){
                $data[$key] = trim($value);
            }
            $checkMessage = $this->checkForm($data);
            if ($checkMessage !== true){
                $this->error($checkMessage['info']);die();
            }
            $this->checkUnique($data['type'], $data['templete'], $data['path']);

            $result = D("OrderSourceManage")->saveData($data);

            if ($result!==false){
                $this->success('保存成功','/channelmanage/js');die();
            }else{
                $this->error('保存失败');die();
            }
        }else{
            //来源组
            $group1 = D("OrderSource")->getAllGroup(1,1);
            $group2 = D("OrderSource")->getAllGroup(1,2);
            $group3 = D("OrderSource")->getAllGroup(1,3);
            $this->assign("group1", $group1);
            $this->assign("group2", $group2);
            $this->assign("group3", $group3);
            $this->assign("v_tpl_pc_html", self::getAllLdTPL('pc','html'));
            $this->assign("v_tpl_mobile_html", self::getAllLdTPL('mobile','html'));
            $this->assign("v_tpl_m_tz_html", self::getAllLdTPL('m_tz','html'));
            $this->display();
        }
    }

    /**
     * 编辑页面
     */
    public function edit()
    {
        if (IS_POST){
            $data = I('post.');
            foreach ($data as $key => $value){
                $data[$key] = trim($value);
            }
            $checkMessage = $this->checkForm($data,2);
            if ($checkMessage !== true){
                $this->error($checkMessage['info']);die();
            }
            $this->checkUnique($data['type'], $data['templete'], $data['path'], $data['id']);

            $result = D("OrderSourceManage")->saveData($data);

            if ($result!==false){
                $this->success('保存成功','/channelmanage/js');die();
            }else{
                $this->error('保存失败');die();
            }
        }else{
            $id = I('get.id',0);
            if (empty($id)){
                $this->error('非法请求，将返回上次访问页面');
            }
            //来源组
            $group1 = D("OrderSource")->getAllGroup(1,1);
            $group2 = D("OrderSource")->getAllGroup(1,2);
            $group3 = D("OrderSource")->getAllGroup(1,3);
            $this->assign("group1", $group1);
            $this->assign("group2", $group2);
            $this->assign("group3", $group3);
            $info = D("OrderSourceManage")->getInfoById($id);
            $this->assign("data", $info);

            $this->assign("v_tpl_pc_html", self::getAllLdTPL('pc','html'));
            $this->assign("v_tpl_mobile_html", self::getAllLdTPL('mobile','html'));
            $this->assign("v_tpl_m_tz_html", self::getAllLdTPL('m_tz','html'));
            $this->display();
        }
    }

    /**
     * 根据groupid获取src符号
     */
    public function getSrcByGroup()
    {
        $group_id = intval(I('get.id',''));

        if (empty($group_id)) {
            $this->ajaxReturn(['status'=>0 , 'info'=>'数据为空','data'=>[]]);
        }
        $src = D("OrderSourceManage")->getSrcByGroup($group_id);

        if (empty($src)){
            $this->ajaxReturn(['status'=>0 , 'info'=>'数据为空','data'=>[]]);
        }else{
            $this->ajaxReturn(['status'=>1 , 'info'=>'获取成功','data'=>$src]);
        }
    }

    //获取列表并分页
    private function getList($map, $pageIndex = 1, $pageCount = 10,$orderby)
    {
        $result = D("OrderSourceManage")->getList($map,$pageIndex, $pageCount,$orderby);
        $count = $result['count'];
        $list = $result['list'];
        import('Library.Org.Util.Page');
        $p = new \Page($count, $pageCount);
        $p->setConfig('header', '个申请');
        $p->setConfig('prev', "上一页");
        $p->setConfig('next', '下一页');
        $p->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $pageTmp = $p->show();
        foreach ($list as $k=>$v){
            switch ($v['type']){
                case 1:
                    $list[$k]['domain'] = C('QZ_YUMINGWWW');
                    break;
                case 2:
                    $list[$k]['domain'] = C('MOBILE_DONAMES');
                    break;
                case 3:
                    $list[$k]['domain'] = C('MOBILE_TAOZUANG');
                    break;
            }
        }
        return array("list" => $list, "page" => $pageTmp);
    }


    /**
     * 数据验证方法
     * @param $data
     * @param int $action 1新增 非1编辑
     * @return array|bool
     */
    public function checkForm($data,$action = 1)
    {
        header('Content-type:text/html;charset=utf-8');
        if ($action!==1 && empty($data['id']) &&intval($data['id']) == 0){
            return ['status'=>0 , 'info'=>'参数错误,ID不存在'];
        }
        if (empty($data['type'])||!in_array($data['type'],[1,2,3])){
            return ['status'=>0 , 'info'=>'终端类型选择错误'];
        }
        if (empty($data['templete'])){
            return ['status'=>0 , 'info'=>'模板未选择'];
        }
        if (empty($data['path'])){
            return ['status'=>0 , 'info'=>'URL路径未填写'];
        }
        if (!preg_match("/^([a-z]+)$/", $data['path'])){
            return ['status'=>0 , 'info'=>'URL路径必须为小写英文字母'];
        }
        if (empty($data['groupid'])){
            return ['status'=>0 , 'info'=>'渠道来源组未选择'];
        }
        if (empty($data['src'])){
            return ['status'=>0 , 'info'=>'标记代号未选择'];
        }
        if (!preg_match("/^([a-z|\-|\d+|,]+)$/", $data['src'])){
            return ['status'=>0 , 'info'=>'标记代号必须为小写英文字母,下划线,数字组合'];
        }
        if (empty($data['base_code'])){
            return ['status'=>0 , 'info'=>'基础代码未填写'];
        }
        if (empty($data['js_code'])){
            return ['status'=>0 , 'info'=>'JS代码未填写'];
        }
        return true;
    }

    /**
     *
     * 检查模板和路径的组合是否重复
     *
     * @param $type
     * @param $templete
     * @param $path
     * @param $id ID号
     *
     * @retrun mixed
     */
    public function checkUnique($type, $templete, $path, $id=0) {
        $map['type'] = $type; //类型 1为PC 2为M端 3为淘装m端
        $map['templete'] = $templete; //模板
        $map['path'] = $path; //路径
        $result = D("OrderSourceManage")->getInfoByMap($map);
        if ($result['id'] == $id) {
            // 如果是修改那么需要传入id排除本身
            return true;
        }
        if (count($result)>0) {
            $this->error('模板和路径已经存在,所以重复了,请检查后重新增加!');die();
        }
    }

    public function delete()
    {
        $id = I('get.id',0);
        if (empty($id)){
            $this->ajaxReturn(['status'=>0,'info'=>'参数错误']);
        }
        $info = D("OrderSourceManage")->delInfoById($id);
        if ($info!==false){
            $this->ajaxReturn(['status'=>1,'info'=>'删除成功']);
        }else{
            $this->ajaxReturn(['status'=>0,'info'=>'删除失败']);
        }
    }
}
