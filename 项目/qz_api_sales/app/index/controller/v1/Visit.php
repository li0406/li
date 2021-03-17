<?php

namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\CompanyLogic;
use app\common\model\logic\ContactsLogic as ConLogic;
use app\common\model\logic\QuyuLogic;
use app\common\model\logic\VisitLogic;
use app\index\validate\Contacts;
use \app\index\validate\Visit as VisitValidate;
use app\index\validate\ReportCompany as ReportCompanyValidate;
use think\Controller;
use think\Request;
use think\Db;

Class Visit extends Controller
{
    public function add_contacts(Request $request, ConLogic $contactsLogic)
    {
        $validate = new Contacts();
        if ($request->operate == 'edit') {
            if (!$validate->scene('EditContacts')->check($request->post())) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002 . '：' . $validate->getError());
            }
            $info = $contactsLogic->updateUser($request->post(), $request->user);
        } else {
            if (!$validate->scene('Contacts')->check($request->post())) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002 . '：' . $validate->getError());
            }
            $info = $contactsLogic->saveUser($request->post(), $request->user);
        }
        return ['error_code' => $info['error_code'], 'error_msg' => $info['error_msg']];
    }

    public function list_contacts(Request $request, ConLogic $contactsLogic)
    {
        if (empty($request->company_id)) {
            return sys_response(4000002);
        }
        $info = $contactsLogic->getContacts($request->company_id);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }

    public function add_visit_record(Request $request,VisitLogic $visitLogic){
        $validate = new VisitValidate();
        if (!$validate->scene('Visit')->check($request->post())) {
            return ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：' . $validate->getError()];
        }
        $info = $visitLogic->saveVisit($request->post(),$request->user);
        return ['error_code' => $info['error_code'], 'error_msg' => $info['error_msg']];
    }

    public function list_visit_record(Request $request,VisitLogic $visitLogic){
        if (empty($request->company_id)) {
            return sys_response(4000002);
        }

        $page = $request->param('page', 1, 'intval,abs');
        $page_count = $request->param('page_count', 20, 'intval,abs');
        return $visitLogic->getVisits($request->get(), $page, $page_count);
    }

    /**
     * 首次添加日志
     *
     * @param Request $request
     * @return array
     */
    public function add_first_visit(Request $request, CompanyLogic $companyLogic,ConLogic $contactsLogic,VisitLogic $visitLogic)
    {
        $validate = new Contacts();
        $validateCompany = new ReportCompanyValidate();
        $validateVisit = new VisitValidate();
        if (!$validateCompany->scene('FirstReportCompany')->check($request->post())) {
            return ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：' . $validateCompany->getError()];
        }
        if (!$validate->scene('FirstContacts')->check($request->post())) {
            return ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：' . $validate->getError()];
        }
        if (!$validateVisit->scene('FirstVisit')->check($request->post())) {
            return ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：' . $validateVisit->getError()];
        }


        $data = $request->post();
        if(!empty($data['user_id'])){
            $info = $companyLogic->getCompanyRepeat($data['user_id']);
            if ($info['is_repeat'] > 0) {
                return ['error_code' => 4000003, 'error_msg' => BaseStatusCodeEnum::CODE_4000003];
            }
        }
        Db::startTrans();
        if(empty($data['company_id'])){
            $info = $companyLogic->insertCompany($data, $request->user);
            if(!$info){
                // 回滚事务
                Db::rollback();
                return ['error_code' => 5000002, 'error_msg' => BaseStatusCodeEnum::CODE_5000002];
            }
            $data['company_id'] = $info;
        }
        $infoContacts = $contactsLogic->saveUser($data, $request->user);
        if(!$infoContacts){
            // 回滚事务
            Db::rollback();
            return ['error_code' => $infoContacts['error_code'], 'error_msg' => $infoContacts['error_msg']];
        }
        $infoVisit = $visitLogic->saveVisit($data,$request->user);
        if(!$infoVisit){
            // 回滚事务
            Db::rollback();
            return ['error_code' => $infoVisit['error_code'], 'error_msg' => $infoVisit['error_msg']];
        }
        Db::commit();
        return ['error_code' => $infoVisit['error_code'], 'error_msg' => $infoVisit['error_msg']];

    }

    /**
     * 根据用户ID获取合同详情
     * @param int   $user_id    [必填]用户ID
     * @Author   liuyupeng
     * @DateTime 2019-05-20T14:24:02+0800
     */
    public function get_contract(Request $request){
        $user_id = $request->param('user_id');
        if (empty($user_id)) {
            return json(sys_response(4000002));
        }

        $companyLogic = new CompanyLogic();
        $response = $companyLogic->getContract($user_id);
        return json($response);
    }

    /**
     * 批量导入日志
     *
     * @param Request $request
     * @return array
     */
    public function company_import(Request $request, CompanyLogic $companyLogic,ConLogic $contactsLogic,VisitLogic $visitLogic,QuyuLogic $QuyuLogic)
    {
        //保存数据
        $datatemp = input('post.data');
        if(empty($datatemp)){
            return ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002];
        }
        $i = 0;
        $returnValidate['error_code'] = 0;
        $datatemp1 = [];
        foreach ($datatemp as $data){
            $i++;
            $validate = new Contacts();
            $validateCompany = new ReportCompanyValidate();
            $validateVisit = new VisitValidate();
            if (!$validateCompany->scene('FirstReportCompany')->check($data)) {
                $returnValidate =  ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：第'.$i .'行'. $validateCompany->getError()];
                break;
            }
            if (!$validate->scene('FirstContacts')->check($data)) {
                $returnValidate =  ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：第'.$i .'行'.  $validate->getError()];
                break;
            }
            if (!$validateVisit->scene('FirstVisit')->check($data)) {
                $returnValidate =  ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：第'.$i .'行'.  $validateVisit->getError()];
                break;
            }
            $infoQuyu = $QuyuLogic->getQuyuTrue($data['cs'],$data['qx']);
            if ($infoQuyu['error_code'] > 0) {
                $returnValidate =  ['error_code' => $infoQuyu['error_code'], 'error_msg' => '：第'.$i .'行'. $infoQuyu['error_msg']];
                break;
            }else{
                $data['cs'] = $infoQuyu['data']['fatherid'];
                $data['qx'] = $infoQuyu['data']['qz_areaid'];
                $datatemp1[]= $data;
            }
        }
        if($returnValidate['error_code'] != 0){
            return $returnValidate;
        }
        Db::startTrans();
        foreach ($datatemp1 as $data) {
            if(!empty($data['user_id'])){
                $info = $companyLogic->getCompanyRepeat($data['user_id']);
                if ($info['is_repeat'] > 0) {
                    $return = ['error_code' => 4000003, 'error_msg' => BaseStatusCodeEnum::CODE_4000003];
                    break;
                }
            }
            if(empty($data['company_id'])){
                $info = $companyLogic->insertCompany($data, $request->user);
                if(!$info){
                    // 回滚事务
                    Db::rollback();
                    $return = ['error_code' => 5000002, 'error_msg' => BaseStatusCodeEnum::CODE_5000002];
                    break;
                }
                $data['company_id'] = $info;
            }
            $infoContacts = $contactsLogic->saveUser($data, $request->user);
            if(!$infoContacts){
                // 回滚事务
                Db::rollback();
                $return = ['error_code' => $infoContacts['error_code'], 'error_msg' => $infoContacts['error_msg']];
                break;
            }
            $infoVisit = $visitLogic->saveVisit($data,$request->user);
            if(!$infoVisit){
                // 回滚事务
                Db::rollback();
                $return = ['error_code' => $infoVisit['error_code'], 'error_msg' => $infoVisit['error_msg']];
                break;
            }else{
                $return = ['error_code' => $infoVisit['error_code'], 'error_msg' => $infoVisit['error_msg']];
            }
        }
        if($return['error_code'] == 0){
            Db::commit();
            return ['error_code' => $infoVisit['error_code'], 'error_msg' => $infoVisit['error_msg']];
        }
        return $return;
    }
}