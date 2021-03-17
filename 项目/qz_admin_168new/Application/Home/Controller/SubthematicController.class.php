<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\CasesModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\SubTagModel;
use Home\Model\Db\SubthematicModel;
use Home\Model\Logic\SubthematicLogicModel;
use Home\Model\QuyuModel;
use Home\Model\TagsModel;
use Common\Enums\ApiConfig;

class SubthematicController extends HomeBaseController
{

    //分站-专题页管理
    public function index()
    {
        $logic = new SubthematicLogicModel();
        $data = I('get.');
        $info = $logic->getSubthematicListByMap($data,$data['p']);

        //站点城市
        $city = D('Quyu')->getQuyuList();
        $this->assign('city',$city);

        //模板下载地址
        $downtemplate = "http://".C('QINIU_DOMAIN')."/custom/20190723/FrYIXGWY7_gYKG_c5BO63-LcqgU4.xlsx";

        $this->assign('list',$info['list'] ? $info['list'] :[]);
        $this->assign('page',$info['page']);
        $this->assign('getdata',$data);
        $this->assign('downtemplate',$downtemplate);
        $this->display();
    }


    //新增专题-基础信息设置
    public function addThematicBasicinfo(){
        $tagsmodel = new TagsModel();
        $model = new SubthematicModel();

        $getdata = I('get.');
        if($getdata['ztid']){       //编辑
            $info = $model->getSubThematicInfoById($getdata['ztid']);
            $this->assign('info',$info);
        }else{
            $info = [];
            $this->assign('info',$info);
        }

        //站点城市
        $city = D('Quyu')->getQuyuList();
        $this->assign('city',$city);
//        $tags = $tagsmodel->getAllTagsbySubarticle_count(6);      //5表示分站标签
//        $this->assign('tags',$tags);
        $sub_tag = (new SubTagModel())->getSubTag('',$info['sub_tagid'],10);

        $this->assign('sub_tag',$sub_tag);
       
        $this->assign('getdata',$getdata);

        $this->display();
    }

    public function getSubTag(){
        $sub_tag = (new SubTagModel())->getSubTag(I('get.name'),I('get.id'),10);
        return $this->ajaxReturn(['error_code'=>0,'data'=>$sub_tag]);
    }


    //新增专题
    public function addThematicAction(){
        $logic = new SubthematicLogicModel();
        $data = I('post.');
        $return = $logic->addThematic($data);
        if(!empty($data['add_name']) && $return['error_code'] == 1){
            //若专题页未添加过公司,根据关联的分站标识以ID倒序自动调取该标识关联的8条装修公司数据
            //根据关联的分站标识以时间倒序自动调取该标识关联的8条装修案例
            $logic->addDefaultSubTagInfo($data['add_name'],$return['data']);
        }
        $this->ajaxReturn($return);
    }

    //新增专题-页面内容设置
    public function addThematicPageinfo(){
        $model = new SubthematicModel();
        $logic = new SubthematicLogicModel();
        $getdata = I('get.');
        $id = $getdata['id'];
        if(!$id){           //如果没有id则跳转到添加页面
            header('Location: ' . '/subthematic/addThematicBasicinfo');
        }

        //专题页的基本信息
        $info = $model->getSubThematicInfoById($id);
        $this->assign('info',$info);
        
        //专题选择装修公司
        $choosecompanylist = $logic->getCompanyListByThematicId($id);
        $this->assign('choosecompanylist',$choosecompanylist);

        //装修选择装修案例
        $choosecaselist = $model->getCasesListByThematicId($id);
        $this->assign('choosecaselist',$choosecaselist);

        $this->assign('getdata',$getdata);      //get获取的参数
        $this->display();
    }

    //编辑专题信息
    public function editThematicAction()
    {
        $logic = new SubthematicLogicModel();
        $data = I('post.');
        if(!$data['id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }
        $return = $logic->editThematic($data);
        $this->ajaxReturn($return);
    }

    //删除选择的装修公司
    public function deleteChooseCompany(){
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if($param['id']){
            $del = $logic->deleteChooseCompany($param['id']);
            if($del === false){
                $return = [];
                $return['error_code'] = ApiConfig::DELETE_FALE;
                $return['error_msg'] = '删除数据失败';
                $this->ajaxReturn($return);
            }else{
                $return = [];
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = '删除成功';
                $this->ajaxReturn($return);
            }

        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到需要删除的数据id';
            $this->ajaxReturn($return);
        }
    }


    //删除选择的案例
    public function deleteChooseCases(){
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if($param['id']){
            $del = $logic->deleteChooseCases($param['id']);
            if($del === false){
                $return = [];
                $return['error_code'] = ApiConfig::DELETE_FALE;
                $return['error_msg'] = '删除数据失败';
                $this->ajaxReturn($return);
            }else{
                $return = [];
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = '删除成功';
                $this->ajaxReturn($return);
            }

        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到需要删除的数据id';
            $this->ajaxReturn($return);
        }
    }




    //选取装修公司
    public function chooseCompanys(){
        $logic = new SubthematicLogicModel();
        $model = new SubthematicModel();

        $getdata = I('get.');
        $subthematicid = $getdata['ztid'];
        if(!$subthematicid){           //如果没有id则跳转到添加页面
            header('Location: ' . '/subthematic');
        }

        //专题页的基本信息
        $info = $model->getSubThematicInfoById($subthematicid);
        $this->assign('info',$info);

        $csid = $info['cs'];        //城市id

        //根据城市id和专题id获取该城市下所有的装修公司（专题id用来判断是否选择）
        $list = $logic->getCompanyListByCsidAndZtid($csid,$subthematicid,$getdata);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('getdata',$getdata);
        $this->display();
    }

    //专题选取装修公司
    public function chooseCompanyAction()
    {
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if(!$param['subthematic_id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }

        if(!$param['company_id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到装修公司id';
            $this->ajaxReturn($return);
        }

        $return = $logic->chooseCompanyAction($param);
        $this->ajaxReturn($return);
    }


    //选取装修案例页面
    public function chooseCases(){
        $logic = new SubthematicLogicModel();
        $model = new SubthematicModel();

        $getdata = I('get.');
        $subthematicid = $getdata['ztid'];
        if(!$subthematicid){           //如果没有id则跳转到添加页面
            header('Location: ' . '/subthematic');
        }

        //专题页的基本信息
        $info = $model->getSubThematicInfoById($subthematicid);
        $this->assign('info',$info);

        $csid = $info['cs'];        //城市id

        //根据城市id和专题id获取该城市下所有的装修公司（专题id用来判断是否选择）
        $list = $logic->getCaseListByCsidAndZtid($csid,$subthematicid,$getdata);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('getdata',$getdata);


        $this->display();
    }

    //专题选取案例
    public function chooseCasesAction()
    {
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if(!$param['subthematic_id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }

        if(!$param['case_id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到案例id';
            $this->ajaxReturn($return);
        }

        $return = $logic->chooseCaseAction($param);
        $this->ajaxReturn($return);
    }

    //删除案例
    public function deleteSubthematicById()
    {
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if(!$param['subthematicid']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }

        $del = $logic->deleteSubthematicById($param['subthematicid']);
        if($del === false){
            $return = [];
            $return['error_code'] = ApiConfig::DELETE_FALE;
            $return['error_msg'] = '删除数据失败';
            $this->ajaxReturn($return);
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '删除成功';
            $this->ajaxReturn($return);
        }

    }

    //根据专题id和装修公司id删除选择的装修公司
    public function deletechooseByThematicidAndCompanyId()
    {
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if(!$param['subthematicid'])
        {
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }
        if(!$param['company_id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到装修公司id';
            $this->ajaxReturn($return);
        }
        $del = $logic->deletechooseByThematicidAndCompanyId($param['subthematicid'],$param['company_id']);      //根据专题id和装修公司id删除选择的装修公司
        if($del === false){
            $return = [];
            $return['error_code'] = ApiConfig::DELETE_FALE;
            $return['error_msg'] = '删除数据失败';
            $this->ajaxReturn($return);
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '删除成功';
            $this->ajaxReturn($return);
        }

    }

    //根据专题id和案例id删除选择的案例
    public function deletechooseByThematicidAndCaseId()
    {
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if(!$param['subthematic_id'])
        {
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }
        if(!$param['case_id']){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到案例id';
            $this->ajaxReturn($return);
        }
        $del = $logic->deletechooseByThematicidAndCaseId($param['subthematic_id'],$param['case_id']);      //根据专题id和案例id删除选择的案例
        if($del === false){
            $return = [];
            $return['error_code'] = ApiConfig::DELETE_FALE;
            $return['error_msg'] = '删除数据失败';
            $this->ajaxReturn($return);
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '删除成功';
            $this->ajaxReturn($return);
        }

    }



    //批量删除专题
    public function deleteSubthematicByIdList()
    {
        $logic = new SubthematicLogicModel();
        $param = I('post.');
        if(empty($param['ids'])){
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '未获取到专题id';
            $this->ajaxReturn($return);
        }

        $del = $logic->deleteSubthematicByIds($param['ids']);
        if($del === false){
            $return = [];
            $return['error_code'] = ApiConfig::DELETE_FALE;
            $return['error_msg'] = '删除数据失败';
            $this->ajaxReturn($return);
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '删除成功';
            $this->ajaxReturn($return);
        }

    }


    //批量导入页面
    public function BatchImport(){

        $this->display();
    }


    //导入操作
    public function companyUploadExcel(){
        //分析Excel内容
        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.'/'.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = importExcel($filename);
        if(count($excel['0'])  != 10 ){
            $this->ajaxReturn(array('data' => '','error_msg' => '数据格式不正确','error_code' => ApiConfig::EXCEL_DATA_ERROR));
        }
        unset($excel['0']);
        $errordata = [];        //错误信息
        $err = 0;
        //逐行导入数据
        $nowtime = time();
        $userinfo = session('uc_userinfo');
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            if(strpos($v[0],'填写须知') !== false){
                break;
            }
            $savedata = [];     //导入的数据
            //查询数据的完整性， 如果红色星号的数据为空，则记录错误信息并 continue
            if(!trim($v[0])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '专题名不能为空';
                $err++;
                continue;
            }
            if(!trim($v[1])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = 'URL不能为空';
                $err++;
                continue;
            }
            if(!trim($v[2])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '所属站点不能为空';
                $err++;
                continue;
            }
            if(!trim($v[3])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '页面标题不能为空';
                $err++;
                continue;
            }
            if(!trim($v[4])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '页面关键词不能为空';
                $err++;
                continue;
            }
            if(!trim($v[5])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '页面描述不能为空';
                $err++;
                continue;
            }
            if(!trim($v[7])){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '专题页简介不能为空';
                $err++;
                continue;
            }
            //所属站点查询是否已开站  返回城市信息  csid,bm      有错误则记录错误信息并continue
            $quyumodel = new QuyuModel();
            $searchcity = [];
            $v[2] = str_replace('市','',$v[2]);
            $searchcity['cname'] = $v[2];
            $csinfo = $quyumodel->getQuyu($searchcity,'');
            if($csinfo){
                $cityinfo = $csinfo[0];                                                           //城市信息 后面会用到。
            }else{
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '该站点未开站:'.$v[2];
                $err++;
                continue;
            }

            //专题名去查询是否存在   返回专题信息                有错误则记录错误信息并continue
            $tagmodel = new TagsModel();
            $searchcity = [];
            $searchcity[0] = $v[0];
            $tagslist = $tagmodel->findTagsByName($searchcity);
            $taginfo = [];
            if($tagslist){
                $taginfo = $tagslist[0];                                                          //标签信息， 后面会用到
                if($taginfo['subarticle_count'] < 6){                                               //文章量大于等于6才算
                    $errordata[$err]['line'] = $k + 1;
                    $errordata[$err]['error'] = '未获取到该专题：'.$v[0];
                    $err++;
                    continue;
                }
            }else{
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '未获取到该专题：'.$v[0];
                $err++;
                continue;
            }
            //查询该城市的该专题是否已存在
            $subthematicmodel = new SubthematicModel();
            $searchzhuanti = [];
            $searchzhuanti['cs'] = $cityinfo['cid'];
            $searchzhuanti['tagid'] = $taginfo['id'];
            $had = $subthematicmodel->getSubThematicInfoByMap($searchzhuanti);
            if($had){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '该站点下已有该专题：('.$v[2].'-'.$v[0].')';
                $err++;
                continue;
            }

            //查询该城市的该域名是否已存在
            $searchzhuanti = [];
            $searchzhuanti['cs'] = $cityinfo['cid'];
            $v[1] = str_replace('/','',$v[1]);
            $v[1] = preg_replace('# #','',$v[1]);       //正则去除url所有空格
            if(strlen($v[1]) > 60){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = 'URL不能超过60个字符';
                $err++;
                continue;
            }
            $searchzhuanti['url'] = $v[1];
            $had = $subthematicmodel->getSubThematicInfoByMap($searchzhuanti);
            if($had){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '该站点下已有该URL：('.$v[2].'-'.$v[1].')';
                $err++;
                continue;
            }

            //装修公司处理
            $companylist = [];
            if($v[8]){
                $companylist = explode(',',preg_replace('# #','',$v[8]));
            }
            if(count($companylist) > 8){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '装修公司最多选择8个';
                $err++;
                continue;
            }

            $companymodel = new CompanyModel();
            if($companylist){
                $companycount = $companymodel->selectCompanybyids($companylist);
                if($companycount){
                    $checkcompanylist = [];
                    foreach ($companycount as $kk1 => $vv1){
                        $checkcompanylist[$kk1] = $vv1['id'];
                    }
                    $checdata = 1;
                    foreach ($companylist as $kk1 => $vv1){
                        if(!in_array($vv1,$checkcompanylist)){
                            $checdata = 2;
                            break;
                        }
                    }
                    if($checdata == 2){
                        $errordata[$err]['line'] = $k + 1;
                        $errordata[$err]['error'] = '装修公司id有误：'.$vv1;
                        $err++;
                        continue;
                    }
                }else{
                    $errordata[$err]['line'] = $k + 1;
                    $errordata[$err]['error'] = '装修公司id错误';
                    $err++;
                    continue;
                }
            }

            //装修案例处理
            $caseslist = [];
            if($v[9]){
                $caseslist = explode(',',preg_replace('# #','',$v[9]));
            }
            if(count($caseslist) > 8){
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '装修案例最多选择8个';
                $err++;
                continue;
            }

            $casesmodel = new CasesModel();
            if($caseslist){
                $searchcaselist = $casesmodel->selectCasesbyids($caseslist);
                if($searchcaselist){
                    $checkcaseslist = [];
                    foreach ($searchcaselist as $kk1 => $vv1){
                        $checkcaseslist[$kk1] = $vv1['id'];
                    }
                    $checdata = 1;
                    foreach ($caseslist as $kk1 => $vv1){
                        if(!in_array($vv1,$checkcaseslist)){
                            $checdata = 2;
                            break;
                        }
                    }
                    if($checdata == 2){
                        $errordata[$err]['line'] = $k + 1;
                        $errordata[$err]['error'] = '装修案例id有误：'.$vv1;
                        $err++;
                        continue;
                    }
                }else{
                    $errordata[$err]['line'] = $k + 1;
                    $errordata[$err]['error'] = '装修案例id错误';
                    $err++;
                    continue;
                }
            }


            //是否推荐到首页处理
            if($v[6] == '是'){
                $v[6] = 1;
            }elseif($v[6] == '否'){
                $v[6] = 2;
            }else{
                $v[6] = 1;
            }

            //记录保存数据
            $savedata = [];
            $savedata['tagid'] = $taginfo['id'];  //标签id
            $savedata['cs'] = $cityinfo['cid'];  //城市id
            $savedata['bm'] = $cityinfo['bm'];  //城市bm
            $savedata['url'] = $v[1];  //url
            $savedata['title'] = $v[3];  //页面标签
            $savedata['description'] = $v[5];  //页面标签
            $savedata['keywords'] = $v[4];  //页面标签
            $savedata['synopsis'] = $v[7];//专题简介
            $savedata['is_top'] = $v[6];//是否推荐到首页 1表示是 2表示否
            $savedata['create_id'] = $userinfo['id'];//创建人  ： 当前用户id
            $savedata['created_at'] = $nowtime;//创建时间

            //添加专题
            $addid = $subthematicmodel->addThematic($savedata);
            if($addid){
                //添加选择的公司
                if($companylist){
                    $savecompanys = [];
                    foreach ($companylist as $key2 => $val2){
                        $savecompanys[$key2]['subthematic_id'] = $addid;
                        $savecompanys[$key2]['company_id'] = $val2;
                    }
                    $subthematicmodel->addThematicCompanyList($savecompanys);
                }
                //添加选择的案例
                if($caseslist){
                    $savecases = [];
                    foreach ($caseslist as $key2 => $val2){
                        $savecases[$key2]['subthematic_id'] = $addid;
                        $savecases[$key2]['case_id'] = $val2;
                    }
                    $subthematicmodel->addThematicCasesList($savecases);
                }
            }else{
                $errordata[$err]['line'] = $k + 1;
                $errordata[$err]['error'] = '专题添加失败：('.$v[2].'-'.$v[0].')';
                $err++;
                continue;
            }

        }
        $return = [];
        $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
        $return['error_msg'] = '操作成功';
        $return['data']['error_data_count'] = count($errordata);
        $return['data']['error_list'] = $errordata;
        $this->ajaxReturn($return);
    }



}