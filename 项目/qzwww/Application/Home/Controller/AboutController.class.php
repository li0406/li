<?php
namespace Home\Controller;
use Common\Enums\ApiConfig;
use Common\Model\Logic\MediaReportLogicModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Home\Common\Controller\HomeBaseController;
class AboutController extends HomeBaseController
{
    public function _initialize(){
        parent::_initialize();

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');

    }
 
    //公司简介
    public function about(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",0);
        $this->display();
    }

    //联系我们
    public function contactus(){
        //按照排序大小排序 排序越大 排的越前面
        $field="substation_name,city,tel,contact_name,address";//要查询的字段 分站名称 所属城市 联系电话 联系人 联系地址
        //查询列表
        $sub_station_list=M('substation_info')->field($field)->where(array('stat'=>1))->order('sort_num desc')->select();
        $tel1 = '"'.OP("QZ_CONTACT_TEL400").'"';
        $this->assign("tel1",$tel1);
        $this->assign('list',$sub_station_list);//分站办事处列表
        $this->assign("tabIndex",0);
        $this->assign("navIndex",7);
        $this->display();
    }

    //企业文化
    public function culture(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",9);
        $this->display();
    }

    // 领袖风采
    public function leadership(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",10);
        $this->display();
    }

    //员工风采
    public function team(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",11);
        $this->display();
    }

    //企业风采
    public function fengcai(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",12);
        $this->display();
    }

    //业主口碑
    public function koubei(){
        $assign = MediaReportLogicModel::getMediaReports();
        $this->assign($assign);
        $this->assign("tabIndex",0);
        $this->assign("navIndex",2);
        $this->display();
    }

    //公司历程
    public function gslc(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",1);
        $this->display();
    }

    //媒体报道
    public function media(){
        $assign = MediaReportLogicModel::getMediaReports();
        $this->assign($assign);
        $this->assign("tabIndex",0);
        $this->assign("navIndex",3);
        $this->display();
    }

    //法律申明
    public function legal(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",4);
        $this->display();
    }

    /**
     * 战略合作
     * @return [type] [description]
     */
    public function liansuo(){
        $this->assign("navIndex",13);
        $this->display();
    }

    //免责声明
    public function disclamer(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",5);
        $this->display();
    }

    //权益保护
    public function quanyi(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",6);
        $this->display();
    }

    //商务合作
    public function joinus(){
        $this->assign("tabIndex",0);
        $this->assign("navIndex",'');
        $this->display();
    }

    //家具合作
    public function jiaju(){
        $this->assign("us_type",2);
        $this->assign("tabIndex",0);
        $this->assign("navIndex",'');
        $this->display();
    }

    //家具合作
    public function jiancai(){
        $this->assign("us_type",3);
        $this->assign("tabIndex",0);
        $this->assign("navIndex",'');
        $this->display();
    }

    //智能家居
    public function znju(){
        $this->assign("us_type",4);
        $this->assign("tabIndex",0);
        $this->assign("navIndex",'');
        $this->display();
    }
   
    

    public function linkUs()
    {
        //历史问题，不方便在框架中开启过滤
        $param = I('post.', '', 'trim,htmlspecialchars');
        $vaildate = $this->_consultLinkUs($param);
        if ($vaildate['result'] === false) {
            $this->ajaxReturn(['status' => ApiConfig::PARAMETER_ILLEGAL, 'info' => $vaildate['mes']]);
        }
        if (!check_verify($param['verify'])) {
            $this->ajaxReturn(['status' => ApiConfig::VERIFY_CODE_ERROR, 'info' => '验证码错误']);
        }
        $ip_info_id = D('CompanyIPInfo', 'Logic')->getIPInfo(1, $param);
        $company_consult_logic = D('CompanyConsult', 'Logic');
        if (!$company_consult_logic->zhanLve($param, $ip_info_id)) {
            $this->ajaxReturn(['status' => ApiConfig::REQUEST_FAILL, 'info' => '录入信息失败']);
        }
        $this->ajaxReturn(['status' => ApiConfig::REQUEST_SUCCESS, 'info' => '操作成功']);
    }

    
    /**
     * 验证码路由
     */
    public function verify()
    {
        getVerify("", 4, 130, 40);
    }


    

   

    

     //公司招聘
    public function zhaopin(){
        /*$this->assign("tabIndex",0);
        $this->assign("navIndex",2);
        $this->display();*/

        $this->assign("is_top","0");
        $this->display('Job/index');
    }

    

    

    

    

    /**
     * 加盟
     * @return [type] [description]
     */
    public function join(){
        if($_POST){
            $code = I("post.verify");
            if(check_verify($code)){
                //检测是否有申请过
                $count = D("Joinus")->findUsInfo(I("post.name"),I("post.tel"));
                if($count > 0){
                    $this->ajaxReturn(array("data"=>"","info"=>"您的申请正在审核中,请勿重复申请！","status"=>0));
                }
                $data = array(
                    "qc"=>I("post.qc"),
                    "name"=>I("post.name"),
                    "tel"=>I("post.tel"),
                    "advantage"=>I("post.advantage"),
                    "addtime"=>time(),
                    "state"=>0,
                    "type"=>I("post.type")
                              );
                $i = D("Joinus")->addUs($data);
                if($i !== false){
                    $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                }
                $this->ajaxReturn(array("data"=>"","info"=>"加盟失败,请稍后再试！","status"=>0));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"验证码填写错误,请重新填写","status"=>0));
        }
    }

    public function sitemap(){
        $cityInfo = S('Cache:Home:sitemap');
        if(!$cityInfo){
            $cityInfo = $this->getAllProvinceAndCitys();
            S("Cache:Home:sitemap",$cityInfo,15 * 60);
        }

        //获取分类别表 1公装 2家装
        //美图公装
        $tuCategoryLogic = new TuCategoryLogicModel();
        $meitugz = S('Cache:getList-1::categorylist');         //分类缓存
        if(!$meitugz){
            $meitugz = $tuCategoryLogic->getMeituLv2CategoryByType(1);
            S('Cache:getList-1::categorylist', $meitugz, 60 * 60 * 12);
        }
        //美图家装
        $meitujz = S('Cache:getList-2::categorylist');         //分类缓存
        if(!$meitujz){
            $meitujz = $tuCategoryLogic->getMeituLv2CategoryByType(2);
            S('Cache:getList-2::categorylist', $meitujz, 60 * 60 * 12);
        }

        $this->assign('meitugz',$meitugz);
        $this->assign('meitujz',$meitujz);
        $this->assign('city',$cityInfo);
        $this->display();
    }

    /**
     * 获取所有省份及城市
     * @return [type] [description]
     */
    private function getAllProvinceAndCitys($flag = false){
        $citys = D("Common/Quyu")->getAllProvinceAndCitys($flag);
        return $citys;
    }

    private function _consultLinkUs($data)
    {
        //公司名称
        if (!empty($data['name']) && mb_strlen($data['name'], 'utf-8') > 50) {
            return ['result' => false, 'mes' => '装修公司数据有误'];
        }
        //联系人
        if (!empty($data['linkman']) && mb_strlen($data['linkman'], 'utf-8') > 50) {
            return ['result' => false, 'mes' => '联系人格式不正确'];
        }
        //联系电话
        if (empty($data['tel']) || !preg_match('/^1\d{10}$/', $data['tel'])) {
            return ['result' => false, 'mes' => '手机号格式不正确'];
        }
        //合作方向
        if (!empty($data['remark']) && mb_strlen($data['remark'], 'utf-8') > 200) {
            return ['result' => false, 'mes' => '合作方向数据异常'];
        }
        return ['result' => true, 'mes' => '验证通过'];
    }


}
