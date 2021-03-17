<?php
namespace Home\Model\Logic;
use Common\Enums\ApiConfig;
use Home\Model\CasesModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\SubTagModel;
use Home\Model\Db\SubthematicModel;
use Home\Model\QuyuModel;

class SubthematicLogicModel
{

    /**
     * 根据参数获取分站-专题页列表
     * @param $data
     */
    public function getSubthematicListByMap($data,$pageIndex, $pageCount = 10)
    {
        $model = new SubthematicModel();
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 10 : intval($pageCount);

        $map = [];
        if($data['theme-name']){        //专题名称
            $map['tags.name'] = array('like','%'.$data['theme-name'].'%');
        }

        if($data['theme-url']){         //专题URL
            $map['subthematic.url'] = array('like','%'.$data['theme-url'].'%');
        }

        if($data['theme-zd']){         //专题站点
            $map['subthematic.cs'] = $data['theme-zd'];
        }

        if($data['theme-tuijian']){         //是否推荐至分站首页   1表示推荐  2表示不推荐
            $map['subthematic.is_top'] = $data['theme-tuijian'];
        }

        if($data['start'] && !$data['end']){        //只有开始时间
            $map['subthematic.created_at'] = [
                ['EGT', strtotime($data['start'] . ' 00:00:00')],
            ];
        }elseif(!$data['start'] && $data['end']){   //只有结束时间
            $map['subthematic.created_at'] = [
                ['EGT', strtotime($data['end'] . ' 00:00:00')],
                ['ELT', strtotime($data['end'] . ' 23:59:59')],
            ];
        }elseif($data['start'] && $data['end']){    //有开始时间也有结束时间
            $map['subthematic.created_at'] = [
                ['EGT', strtotime($data['start'] . ' 00:00:00')],
                ['ELT', strtotime($data['end'] . ' 23:59:59')],
            ];
        }

        $count = $model->getSubthematicListByMapCountV2($map);

        import('Library.Org.Util.Page');
        $p = new \Page($count,$pageCount);
        $show = $p->show();
        $list = $model->getSubthematicListByMapV2($map,($pageIndex - 1) * $pageCount, $pageCount);
        return ['list' => $list, 'page' => $show];
    }


    //新增专题
    public function addThematic($data){
        $model = new SubthematicModel();
        $quyumodel = new QuyuModel();
        $userinfo = session('uc_userinfo');
        $savedata = [];
        //所属站点
        if(!empty($data['add_zhandian'])){
            $savedata['cs'] = $data['add_zhandian'];
            //根据cid获取bm
            $quyumap = [];
            $quyumap['cid'] = $data['add_zhandian'];
            $quyuinfo = $quyumodel->getQuyu($quyumap);
            $savedata['bm'] = $quyuinfo[0]['bm'];
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '所属站点不能为空';
            return $return;
        }

                //专题名
        if(!empty($data['add_name'])){
//            $savedata['tagid'] = $data['add_name'];
            $savedata['sub_tagid'] = $data['add_name'];
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '专题名不能为空';
            return $return;
        }

        //此处需要加一个判断   如果tageid 和cs在数据库中有一样的了，则需要提示无法重复添加
        $map1 = $savedata;
        unset($map1['bm']);
        $hadinfo = $model->getSubThematicInfoByMap($map1);
        if($hadinfo){
            $return = [];
            $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE;
            $return['error_msg'] = '该站点已存在该专题，无法重复添加';
            return $return;
        }

        //URL
        if(!empty($data['add_url'])){
            $savedata['url'] = str_replace("/","",$data['add_url']);
            //如果该站点下的url已存在，则无法重复添加
            $map2['cs'] = $savedata['cs'];
            $map2['url'] = $savedata['url'];
            $hadinfo = $model->getSubThematicInfoByMap($map2);
            if($hadinfo){
                $return = [];
                $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE;
                $return['error_msg'] = '该URL已存在';
                return $return;
            }
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = 'URL不能为空';
            return $return;
        }

        //标题Title
        if(!empty($data['add_title'])){
            $savedata['title'] = $data['add_title'];
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '标题Title不能为空';
            return $return;
        }

        //标题关键字
        if(!empty($data['add_keywords'])){
            $savedata['keywords'] = $data['add_keywords'];
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '标题关键字不能为空';
            return $return;
        }

        //标题描述
        if(!empty($data['add_description'])){
            $savedata['description'] = $data['add_description'];
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '标题页描述不能为空';
            return $return;
        }
        //推荐选项必选
        if(!empty($data['biaoqian'])){
            $savedata['is_top'] = $data['biaoqian'];
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::PARAMETER_MISSING;
            $return['error_msg'] = '推荐选项必选';
            return $return;
        }

        $savedata['create_id'] = $userinfo['id'];
        $savedata['created_at'] = time();
        $savedata['is_subtag'] = 1;
        //添加操作
        M()->startTrans(); // 开启事务
        try{
            $add = $model->addThematic($savedata);
            M()->commit(); // 事务提交
        } catch (\Exception $e) {
                // 回滚事务
            M()->rollback(); // 事务回滚
            $add = false;
        }
        if($add === false){
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = '保存失败';
            return $return;
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '保存成功';
            $return['data'] = $add;
            return $return;
        }

    }

    //编辑专题
    public function editThematic($data)
    {
        $model = new SubthematicModel();
        $quyumodel = new QuyuModel();
        $savedata = [];
        $savedata['id'] = $data['id'];

        $oldinfo = $model->getSubThematicInfoByMap($savedata);
        //所属站点
        if(!empty($data['add_zhandian'])){
            $savedata['cs'] = $data['add_zhandian'];
            //根据cid获取bm
            $quyumap = [];
            $quyumap['cid'] = $data['add_zhandian'];
            $quyuinfo = $quyumodel->getQuyu($quyumap);
            $savedata['bm'] = $quyuinfo[0]['bm'];
        }else{
            $savedata['cs'] = $oldinfo['cs'];
        }

        //专题名
        if(!empty($data['add_name'])){
//            $savedata['tagid'] = $data['add_name'];
            $savedata['sub_tagid'] = $data['add_name'];
        }
//        $savedata['tagid'] = 30807;  //测试数据，需要删除该行

        //此处需要加一个判断   如果tageid 和cs在数据库中有一样的了，则需要提示无法重复添加
        if($data['add_zhandian']){
            $map1 = $savedata;
            unset($map1['bm']);
            unset($map1['id']);
            $hadinfo = $model->getSubThematicInfoByMap($map1);
            if($hadinfo && $hadinfo['id'] != $data['id']){
                $return = [];
                $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE;
                $return['error_msg'] = '该站点已有该专题';
                return $return;
            }
        }

        //URL
        if(!empty($data['add_url'])){
//            $savedata['url'] = $data['add_url'];
            $savedata['url'] = str_replace("/","",$data['add_url']);
            //如果该站点下的url已存在，则无法重复添加
            if($savedata['url'] != $oldinfo['url']){
                $map2 = [];
                $map2['cs'] = $savedata['cs'];
                $map2['url'] = $savedata['url'];
                $hadinfo = $model->getSubThematicInfoByMap($map2);
                if($hadinfo){
                    $return = [];
                    $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE;
                    $return['error_msg'] = '该URL已存在';
                    return $return;
                }
            }

        }

        //标题Title
        if(!empty($data['add_title'])){
            $savedata['title'] = $data['add_title'];
        }

        //标题关键字
        if(!empty($data['add_keywords'])){
            $savedata['keywords'] = $data['add_keywords'];
        }

        //标题描述
        if(!empty($data['add_description'])){
            $savedata['description'] = $data['add_description'];
        }
        //推荐选项
        if(!empty($data['biaoqian'])){
            $savedata['is_top'] = $data['biaoqian'];
        }

        //专题页简介
        if(!empty($data['synopsis'])){
            $savedata['synopsis'] = $data['synopsis'];
        }
        //编辑操作

        M()->startTrans(); // 开启事务
        try{
            $add = $model->editThematic($savedata);
            M()->commit(); // 事务提交
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            $add = false;
        }
        if($add === false){
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = '保存失败';
            return $return;
        }else{
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '保存成功';
            return $return;
        }

    }

    //根据专题id获取选择的装修公司列表
    public function getCompanyListByThematicId($id){
        $model = new SubthematicModel();
        $companymodel = new CompanyModel();
        $companylist = $model->getCompanyListByThematicId($id);
        $fenggelist = $companymodel->getFuwuLeixing('leixing');
        if($companylist){
            foreach ($companylist as $key =>$val){
                if($val['fuwu']){
                    $fuwu = '';
                    $myfuwu = explode(',',$val['fuwu']);
                    foreach ($myfuwu as $k => $v){
                        foreach ($fenggelist as $k2 => $v2){
                            if($v == $v2['id']){
                                 if(!$fuwu){
                                     $fuwu = $v2['name'];
                                 }else{
                                     $fuwu = $fuwu . '，'. $v2['name'];
                                 }
                            }
                        }
                    }
                    $companylist[$key]['fuwu'] = $fuwu;

                }else{
                    $companylist[$key]['fuwu'] = '';
                }
                //会员状态
                if($val['on'] == 2 && $val['fake'] == 1){
                    $companylist[$key]['vipstatus'] = '假会员';
                }elseif($val['on'] == 2 && $val['fake'] == 0){
                    $companylist[$key]['vipstatus'] = '真会员';
                }elseif($val['on'] == -1){
                    $companylist[$key]['vipstatus'] = '过期会员';
                }elseif($val['on'] == 0){
                    $companylist[$key]['vipstatus'] = '注册会员';
                }else{
                    $companylist[$key]['vipstatus'] = '';
                }

            }
            return $companylist;
        }else{
            return array();
        }

    }

    //删除选择的装修公司
    public function deleteChooseCompany($id){
        $model = new SubthematicModel();
        if($id){
            return $model->deleteChooseCompany($id);
        }else{
            return false;
        }
    }

    //删除选择的案例
    public function deleteChooseCases($id){
        $model = new SubthematicModel();
        if($id){
            return $model->deleteChooseCases($id);
        }else{
            return false;
        }
    }

    //根据城市id和专题id获取该城市下所有的装修公司（专题id用来判断是否选择）
    public function getCompanyListByCsidAndZtid($cs,$ztid,$getdata)
    {
        $companymodel = new CompanyModel();
        $model = new SubthematicModel();
        $chooselist = $model->getCompanyListByThematicId($ztid);


        $count = $companymodel->getCompanyListByCsCount($cs,intval($ztid),$getdata);
        if($count > 0){
            import('Library.Org.Util.Page');
            $page = new \Page($count,10);       //每页10条数据
            $result = $companymodel->getCompanyListByCs($cs,intval($ztid),$getdata,$page->firstRow, $page->listRows);
            $fenggelist = $companymodel->getFuwuLeixing('leixing');

            foreach ($result as $key =>$val){
                if($val['fuwu']){
                    $fuwu = '';
                    $myfuwu = explode(',',$val['fuwu']);
                    foreach ($myfuwu as $k => $v){
                        foreach ($fenggelist as $k2 => $v2){
                            if($v == $v2['id']){
                                if(!$fuwu){
                                    $fuwu = $v2['name'];
                                }else{
                                    $fuwu = $fuwu . '，'. $v2['name'];
                                }
                            }
                        }
                    }
                    $result[$key]['fuwu'] = $fuwu;

                }else{
                    $result[$key]['fuwu'] = '';
                }
                //会员状态
                if($val['on'] == 2 && $val['fake'] == 1){
                    $result[$key]['vipstatus'] = '假会员';
                }elseif($val['on'] == 2 && $val['fake'] == 0){
                    $result[$key]['vipstatus'] = '真会员';
                }elseif($val['on'] == -1){
                    $result[$key]['vipstatus'] = '过期会员';
                }elseif($val['on'] == 0){
                    $result[$key]['vipstatus'] = '注册会员';
                }else{
                    $result[$key]['vipstatus'] = '';
                }

                $result[$key]['is_choose'] = 0;     //0表示未选择
                //判断是否以选择
                foreach ($chooselist as $k => $v){
                    if($val['company_id'] == $v['company_id']){
                        $result[$key]['is_choose'] = 1;         //1表示以选择
                    }
                }
            }

            $show = $page->show();
        }else{
            $result = [];
            $show = "";
        }
        return array("list" => $result, "page" => $show);

    }

    //专题选取装修公司
    public function chooseCompanyAction($param)
    {
        $model = new SubthematicModel();
        //检查是否已存在
        $map = [];
        $map['subthematic_id'] = $param['subthematic_id'];
        $map['company_id'] = $param['company_id'];
        $hadcompany = $model->getSubThematicChooseCompanyByMap($map);
        if($hadcompany){
            $return = [];
            $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE;
            $return['error_msg'] = '已存在无法重复选择';
            return $return;
        }else{
            //不存在 则可以选择添加
            $count = $model->getCompanyListByThematicId($param['subthematic_id']);
            $count = count($count);
            if($count >= 8){
                $return = [];
                $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE_MAX;
                $return['error_msg'] = '装修公司已选择8条，无法继续选择';
                return $return;
            }else{
                //可以添加
                $data = [];
                $data['subthematic_id'] = $param['subthematic_id'];
                $data['company_id'] = $param['company_id'];

                //添加操作
                M()->startTrans(); // 开启事务
                try{
                    $result = $model->addThematicCompany($data);
                    M()->commit(); // 事务提交
                } catch (\Exception $e) {
                    // 回滚事务
                    M()->rollback(); // 事务回滚
                    $result = false;
                }
                if($result === false){
                    $return = [];
                    $return['error_code'] = ApiConfig::REQUEST_FAILL;
                    $return['error_msg'] = '保存失败';
                    return $return;
                }else{
                    $return = [];
                    $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                    $return['error_msg'] = '保存成功';
                    $return['data'] = $result;
                    return $return;
                }
            }
        }

    }



    //根据城市id和专题id获取该城市下所有的装修公司（专题id用来判断是否选择）
    public function getCaseListByCsidAndZtid($cs,$ztid,$getdata)
    {
        $casemodel = new CasesModel();
        $model = new SubthematicModel();
        $chooselist = $model->getCasesListByThematicId($ztid);        //获取以选择的案例列表


        $count = $casemodel->getCasesListByCsCount($cs,intval($ztid),$getdata);   //获取案例总数量
        if($count > 0){
            import('Library.Org.Util.Page');
            $page = new \Page($count,10);       //每页10条数据
            $result = $casemodel->getCasesListByCs($cs,intval($ztid),$getdata,$page->firstRow, $page->listRows);

            foreach ($result as $key =>$val){

                //会员状态
                if($val['on'] == 2 && $val['fake'] == 1){
                    $result[$key]['vipstatus'] = '假会员';
                }elseif($val['on'] == 2 && $val['fake'] == 0){
                    $result[$key]['vipstatus'] = '真会员';
                }elseif($val['on'] == -1){
                    $result[$key]['vipstatus'] = '过期会员';
                }elseif($val['on'] == 0){
                    $result[$key]['vipstatus'] = '注册会员';
                }else{
                    $result[$key]['vipstatus'] = '';
                }

                $result[$key]['is_choose'] = 0;     //0表示未选择
                //判断是否以选择
                foreach ($chooselist as $k => $v){
                    if($val['cases_id'] == $v['cases_id']){
                        $result[$key]['is_choose'] = 1;         //1表示以选择
                    }
                }
            }

            $show = $page->show();
        }else{
            $result = [];
            $show = "";
        }
//        dump($result);die;
        return array("list" => $result, "page" => $show);

    }


    //专题选取案例
    public function chooseCaseAction($param)
    {
        $model = new SubthematicModel();
        //检查是否已存在
        $map = [];
        $map['subthematic_id'] = $param['subthematic_id'];
        $map['case_id'] = $param['case_id'];
        $hadcompany = $model->getSubThematicChooseCaseByMap($map);
        if($hadcompany){
            $return = [];
            $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE;
            $return['error_msg'] = '已存在无法重复选择';
            return $return;
        }else{
            //不存在 则可以选择添加
            $count = $model->getCasesListByThematicId($param['subthematic_id']);
            $count = count($count);
            if($count >= 8){
                $return = [];
                $return['error_code'] = ApiConfig::DATA_HAD_IN_TABLE_MAX;
                $return['error_msg'] = '案例已选择8条，无法继续选择';
                return $return;
            }else{
                //可以添加
                $data = [];
                $data['subthematic_id'] = $param['subthematic_id'];
                $data['case_id'] = $param['case_id'];

                //添加操作
                M()->startTrans(); // 开启事务
                try{
                    $result = $model->addThematicCase($data);
                    M()->commit(); // 事务提交
                } catch (\Exception $e) {
                    // 回滚事务
                    M()->rollback(); // 事务回滚
                    $result = false;
                }
                if($result === false){
                    $return = [];
                    $return['error_code'] = ApiConfig::REQUEST_FAILL;
                    $return['error_msg'] = '保存失败';
                    return $return;
                }else{
                    $return = [];
                    $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                    $return['error_msg'] = '保存成功';
                    $return['data'] = $result;
                    return $return;
                }
            }
        }

    }


    //删除专题
    public function deleteSubthematicById($id)
    {
        $model = new SubthematicModel();
        if(!$id){
            return false;
        }
        M()->startTrans(); // 开启事务
        try{
            $map = [];
            $map['id'] = $id;
            $result1 = $model->deleteSubThematic($map);          //删除专题

            $map2 = [];
            $map2['subthematic_id'] = $id;
            $result2 = $model->deleteSubThematicCompany($map2);   //删除专题选择的装修公司

            $map3=[];
            $map3['subthematic_id'] = $id;
            $result3 = $model->deleteSubThematicCases($map3);     //删除专题选择的案例
            M()->commit(); // 事务提交
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            return false;
        }

    }

    //批量删除案例
    public function deleteSubthematicByIds($ids=[])
    {
        $model = new SubthematicModel();
        if(empty($ids)){
            return false;
        }
        M()->startTrans(); // 开启事务
        try{
            $map = [];
            $map['id'] = array('in',$ids);
            $result1 = $model->deleteSubThematic($map);          //删除专题

            $map2 = [];
            $map2['subthematic_id'] = array('in',$ids);
            $result2 = $model->deleteSubThematicCompany($map2);   //删除专题选择的装修公司

            $map3=[];
            $map3['subthematic_id'] = array('in',$ids);
            $result3 = $model->deleteSubThematicCases($map3);     //删除专题选择的案例
            M()->commit(); // 事务提交
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            return false;
        }

    }



    /**
     * 根据专题id和装修公司id删除选择的装修公司
     * 调用前请验证确保两个参数都有值
     * @param $subthematicid    专题id
     * @param $companyid        装修公司id
     */
    public function deletechooseByThematicidAndCompanyId($subthematicid,$companyid)
    {
        $model = new SubthematicModel();
        if(!$subthematicid || !$companyid) {
            return false;
        }
        $map = [];
        $map['subthematic_id'] = $subthematicid;
        $map['company_id'] = $companyid;
        $result2 = $model->deleteSubThematicCompany($map);   //删除专题选择的装修公司
        if($result2){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 根据专题id和案例id删除选择的案例
     * 调用前请验证确保两个参数都有值
     * @param $subthematicid    专题id
     * @param $caseid        案例id
     */
    public function deletechooseByThematicidAndCaseId($subthematicid,$caseid)
    {
        $model = new SubthematicModel();
        if(!$subthematicid || !$caseid) {
            return false;
        }
        $map = [];
        $map['subthematic_id'] = $subthematicid;
        $map['case_id'] = $caseid;
        $result = $model->deleteSubThematicCases($map);   //删除专题选择的装修公司
        if($result){
            return true;
        }else{
            return false;
        }
    }


    public function addDefaultSubTagInfo($subtagid,$id){
        //若专题页未添加过公司,根据关联的分站标识以ID倒序自动调取该标识关联的8条装修公司数据
        //根据关联的分站标识以时间倒序自动调取该标识关联的8条装修案例
        $submodel = new SubTagModel();
        $company  = $submodel->getCompanyByTag($subtagid,8);
        $subthematicmodel =  new SubthematicModel();
        if(!empty($company)){
            $companyData = [];
            foreach($company as $key=>$val){
                $companyData[$key]['subthematic_id'] = $id;
                $companyData[$key]['company_id'] = $val['company_id'];
            }
            $subthematicmodel->addThematicCompanyList($companyData);
        }

        $case  = $submodel->getCaseByTag($subtagid,8);

        if(!empty($case)){
            $caseData = [];
            foreach($case as $key=>$val){
                $caseData[$key]['subthematic_id'] = $id;
                $caseData[$key]['case_id'] = $val['case_id'];
            }

            $subthematicmodel->addThematicCasesList($caseData);
        }
    }

}