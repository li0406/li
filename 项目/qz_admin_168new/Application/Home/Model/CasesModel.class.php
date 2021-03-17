<?php
namespace Home\Model;

Use Think\Model;

class CasesModel extends Model{

    /**
     * 获取案例数量
     * @param  integer $cases_id         案例ID
     * @param  integer $cs               城市
     * @param  string  $title            案例标题
     * @param  string  $cases_time_start 案例发布开始时间
     * @param  string  $cases_time_end   案例发布结束时间
     * @return integer                   案例数量
     */
    public function getCasesCount($cases_id = 0, $cs = 0, $cases_time_start = '',$cases_time_end = '', $title, $on){

        //增加美图专题筛选功能 $cases_id 有可能是多个
        $is_choice = I('get.is_choice');
        if($is_choice == '1' && !empty($cases_id)){
            $map['id'] = array('IN',$cases_id);
        }elseif($is_choice == '2' && !empty($cases_id)){
            $map['id'] = array('NOT IN',$cases_id);
        }elseif (!empty($cases_id)) {
            $map['id'] = $cases_id;
        }

        if (!empty($cs)) {
            $map['cs'] = intval($cs);
        }

       /* if (!empty($designer_id)) {
            $map['userid'] = intval($designer_id);;
        }

        if (!empty($company_id)) {
            $map['uid'] = intval($company_id);;
        }*/

        if(!empty($title)){
            $map['title'] = array("like", "%" . $title . "%");
        }

        if (!empty($cases_time_start)) {
            $map['time'][] = array('EGT', $cases_time_start);
        } else {
            $map['time'][] = array('EGT', strtotime("-3 month"));
        }

        if (!empty($cases_time_end)) {
            $map['time'][] = array('ELT', $cases_time_end);
        }

        if(!empty($on)){
            $map['on'][] = array('EQ', $on);
        }

        $build = M('cases')->field('id,`on`,cs,uid,userid,fengge,huxing')->where($map)->order('id DESC')->buildSql();
        $build = M()->table($build)->alias('c')
            ->join('INNER JOIN qz_case_img as b on c.id = b.caseid ')
            ->field('c.*,count(c.id) as case_count,count(IF(b.`status` = 2, 1, NULL)) AS img_count,b.img_path,b.img_host,b.img')
            ->group('c.id')
            ->buildSql();
        return M()->table($build)->alias('c')
            ->where('c.case_count = img_count')
            ->count();
    }

    /**
     * 获取案例列表
     * @param  integer $cases_id         案例ID
     * @param  integer $cs               城市
     * @param  string  $title            案例标题
     * @param  string  $cases_time_start 案例发布开始时间
     * @param  string  $cases_time_end   案例发布结束时间
     * @param  integer $start            查询开始位置
     * @param  integer $end              查询结束位置
     * @return array                     案例列表
     */
    public function getCasesList($cases_id = 0, $cs = 0, $cases_time_start = '',$cases_time_end = '', $title, $start = 0, $end = 10,$on)
    {


        //增加美图专题筛选功能 $cases_id 有可能是多个
        $is_choice = I('get.is_choice');
        if($is_choice == '1' && !empty($cases_id)){
            $map['id'] = array('IN',$cases_id);
        }elseif($is_choice == '2' && !empty($cases_id)){
            $map['id'] = array('NOT IN',$cases_id);
        }elseif (!empty($cases_id)) {
            $map['id'] = $cases_id;
        }

        if (!empty($cs)) {
            $map['cs'] = intval($cs);
        }

       /* if (!empty($designer_id)) {
            $map['userid'] = intval($designer_id);;
        }

        if (!empty($company_id)) {
            $map['uid'] = intval($company_id);;
        }*/

        if(!empty($title)){
            $map['title'] = array("like", "%" . $title . "%");
        }

        if (!empty($cases_time_start)) {
            $map['time'][] = array('EGT', $cases_time_start);
        } else {
            $map['time'][] = array('EGT', strtotime("-3 month"));
        }

        if (!empty($cases_time_end)) {
            $map['time'][] = array('ELT', $cases_time_end);
        }

        if(!empty($on)){
            $map['on'][] = array('EQ', $on);
        }

        $build = M('cases')->field('id,`on`,cs,uid,userid,fengge,huxing,title,time')->where($map)->order('id DESC')->buildSql();
        $build = M()->table($build)->alias('c')
            ->join('INNER JOIN qz_case_img as b on c.id = b.caseid ')
            ->field('c.*,count(c.id) as case_count,count(IF(b.`status` = 2, 1, NULL)) AS img_count,b.img_path,b.img_host,b.img')
            ->group('c.id')
            ->buildSql();
        $build = M()->table($build)->alias('c')
            ->where('c.case_count = img_count')
            ->limit($start,$end)
            ->buildSql();
        return M()->table($build)->alias('c')
                                 ->field('c.*, c.on AS cases_on, u.qc AS company_name, u.id AS company_id, u.on, y.fake, z.id AS designer_id, z.name AS design_name, q.cname, q.bm,g.name as fengge,h.name AS huxing')
                                 ->join('LEFT JOIN qz_quyu AS q ON q.cid = c.cs')
                                 ->join('LEFT JOIN qz_user AS u ON u.id = c.uid')
                                 ->join('LEFT JOIN qz_user_company AS y ON y.userid = u.id')
                                 ->join('LEFT JOIN qz_user AS z ON z.id = c.userid')
                                 ->join("LEFT JOIN qz_fengge as g on g.id = c.fengge")
                                 ->join("LEFT JOIN qz_huxing as h on h.id = c.huxing")
                                 ->select();
    }

    /**
     * 根据案例ID获取案例
     * @param  integer $case_id 案例ID
     * @return array            案例数组
     */
    public function getCaseById($case_id = 0)
    {
        if (empty($case_id)) {
            return false;
        }
        return M('cases')->where(array('id' => intval($case_id)))->find();
    }

    /**
     * 根据案例ID删除案例
     * @param  integer $case_id 案例ID
     * @return bool             是否删除
     */
    public function deleteCaseById($case_id = 0)
    {
        if (empty($case_id)) {
            return false;
        }
        $result = M('cases')->where(array('id' => intval($case_id)))->delete();
        if (!$result) {
            return false;
        }

        M('case_img')->where(array('caseid' => intval($case_id)))->delete();
        return true;
    }

    /**
     * 获取案例图片
     * @param  integer $case_id 案例ID
     * @return array            案例图片数组
     */
    public function getCaseImagesByCaseId($case_id = 0)
    {
        if (empty($case_id)) {
            return false;
        }
        return M('case_img')->where(array('caseid' => intval($case_id)))->select();
    }

    /**
     * 删除案例图片
     * @param  array  $case_img_ids 案例图片ID数组
     * @return bool                 是否删除
     */
    public function deleteCaseImagesByCaseImagesIds($case_img_ids = array())
    {
        if (empty($case_img_ids)) {
            return false;
        }

        if (!is_array($case_img_ids)) {
            $case_img_ids = array($case_img_ids);
        }

        $map['id'] = array('IN', $case_img_ids);

        return M('case_img')->where($map)->delete();
    }

    /**
     * 修改案例审核状态
     * @param integer $id 案例ID
     * @param integer $on 修改的状态
     */
    public function setCasesOn($id = 0, $on = 0)
    {
        if (empty($id) || empty($on) || !in_array($on, array(1, 2, 3))) {
            return false;
        }

        return M('cases')->where(array('id' => $id))->save(array('on' => $on));
    }


    public function getCaseCount($id){
        $map = [
            'uid' => ['eq', $id],
            'on' => ['in', [1, 2]],
        ];
        return M('Cases')->where($map)->count();
    }

    //修改3D效果图审核状态
    public function setThreedStatus($id = 0,$status = 0){
        if(empty($id) && empty($status)){
            return false;
        }
        $res = M('cases')->where(array('id' => $id))->save(array('status' => $status));
        if($res === false){
            return array('status'=>0, 'info'=>'操作失败');
        }else{
            M('case_img')->where(array('caseid'=>$id))->save(array('status' => $status));
            return array('status'=> 1,'info'=>'操作成功');
        }
    }


    /**
     * Gets the previous case.
     *
     * @param      <type>  $id     The identifier
     *
     * @return     <type>  The previous case.
     */
    public function getPrevCase($id,$cid,$cs,$des_id,$com_id,$time_start,$time_end,$classid,$status){
        if (!empty($cs)) {
            $map['a.cs'] = intval($cs);
        }
        if (!empty($des_id)) {
            $map['a.userid'] = intval($des_id);
        }
        if (!empty($com_id)) {
            $map['a.uid'] = intval($com_id);
        }

        if (!empty($status)) {
            $map['a.status'] = intval($status);
        }
        if (!empty($time_start)) {
            $map['a.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['a.time'][] = array('ELT', $time_end);
        }

        $map['a.id'] = array('LT',$id);
        // $map['a.status'] = array('EQ',$status);
        $map['a.classid'] = array('EQ',4);

        return M('cases')->alias('a')
                        ->field('a.id')
                        ->where($map)
                        ->order('a.id DESC')
                        ->find();
    }


    /**
     *
     */
    public function getNextCase($id,$cid,$cs,$des_id,$com_id,$time_start,$time_end,$classid,$status){
        if (!empty($cs)) {
            $map['a.cs'] = intval($cs);
        }
        if (!empty($des_id)) {
            $map['a.userid'] = intval($des_id);;
        }
        if (!empty($com_id)) {
            $map['a.uid'] = intval($com_id);;
        }
        if (!empty($status)) {
            $map['a.status'] = intval($status);;
        }
        if (!empty($time_start)) {
            $map['a.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['a.time'][] = array('ELT', $time_end);
        }
        $map['a.id'] = array('GT',$id);
        $map['a.classid'] = array('EQ',4);

        return M('cases')->alias('a')
                        ->field('a.id')
                        ->where($map)
                        ->order('a.id')
                        ->find();
    }


    public function getReviewByCasesId($id){
        return M('case_review')->field('*')->where(array('caseid' => array('EQ',$id)))->find();
    }

    public function changeThreedFacePic($array){
        if(!empty($array) && isset($array)){
            foreach ($array as $key => $value) {
                $newdata = array();
                $newdata = M('case_img')->where(array('caseid' => array('EQ',$value['caseid']),'img_path'=>array('like','%/bg%')))->order('id desc')->find();
                $array[$key]['src'] = $newdata['img_path'];
                $array[$key]['src_img'] = $newdata['img'];
                $array[$key]['imgid'] = $newdata['id'];
            }
            return $array;
        }else{
            return $array;
        }
    }

    public function changeThreedShowPic($array){
        if(!empty($array) && isset($array)){
            foreach ($array as $key => $value) {
                $newdata = array();
                $newdata = M('case_img')->where(array('caseid' => array('EQ',$value['id']),'img_path'=>array('like','%/bg%')))->order('id desc')->find();
                $array[$key]['img_path'] = $newdata['img_path'];
                $array[$key]['img'] = $newdata['img'];
                $array[$key]['imgid'] = $newdata['id'];
                $array[$key]['img_status'] = $newdata['status'];
            }
            return $array;
        }else{
            return $array;
        }
    }

    public function saveData($id, $data){
        $map = array(
            "id" => array("EQ", $id)
        );

        return M("cases")->where($map)->save($data);
    }

    /**************************p.2.17.20 （pc端+m端）分站专题页V1.0.0 *********************************************/

    //根据城市id获取该城市下所有的装修公司数量
    public function getCasesListByCsCount($cs,$subthematicid,$getdata)
    {
        $map = [];
        $map['cases.cs'] = $cs;
        $map['cases.on'] = 1;       //1表示审核
        $map['cases.isdelete'] = 1;       //1表示未删

        if(isset($getdata['case_id']) && $getdata['case_id']){        //案例id
            $map['cases.id'] = $getdata['case_id'];
        }

        if(isset($getdata['case_name']) && $getdata['case_name']){        //公司名称
            $map['cases.title'] = array('like','%'.$getdata['case_name'].'%');
        }

        if(isset($getdata['status']) && $getdata['status'] == 1){           //选取状态为是
            $map['choose.subthematic_id'] = $subthematicid;
            return M('cases')->alias('cases')
                ->where($map)
                ->join('qz_subthematic_cases choose on choose.case_id  = cases.id')
                ->join('qz_user users on users.id = cases.uid')
                ->join('qz_user_company companyinfo on companyinfo.userid  = users.id')
                ->count();
        }elseif(isset($getdata['status']) && $getdata['status'] == 2){      //选取状态为否

            return M('cases')->alias('cases')
                ->where($map)
                ->join('qz_user users on users.id = cases.uid')
                ->join('qz_user_company companyinfo on companyinfo.userid  = users.id')
                ->join('LEFT JOIN qz_subthematic_cases choose on choose.case_id  = cases.id and choose.subthematic_id ='.$subthematicid)
                ->where('choose.id is null')
                ->count();
        }else{                                                              //全部
            if($map) {
                return M('cases')->alias('cases')
                    ->where($map)
                    ->join('qz_user users on users.id = cases.uid')
                    ->join('qz_user_company companyinfo on companyinfo.userid  = users.id')
                    ->count();
            }else{
                return 0;
            }
        }

    }


    //根据城市id获取该城市下所有的装修公司（获取的参数为）
    public function getCasesListByCs($cs,$subthematicid,$getdata,$pageIndex,$pageCount)
    {
        $map = [];
        $map['cases.cs'] = $cs;
        $map['cases.on'] = 1;       //1表示审核
        $map['cases.isdelete'] = 1;       //1表示未删

        if(isset($getdata['case_id']) && $getdata['case_id']){        //案例id
            $map['cases.id'] = $getdata['case_id'];
        }

        if(isset($getdata['case_name']) && $getdata['case_name']){        //公司名称
            $map['cases.title'] = array('like','%'.$getdata['case_name'].'%');
        }

        if(isset($getdata['status']) && $getdata['status'] == 1){           //选取状态为是
            $map['choose.subthematic_id'] = $subthematicid;
            return M('cases')->alias('cases')
                ->where($map)
                ->field('cases.id cases_id,cases.title cases_title,users.on,users.id company_id,users.jc company_jc,companyinfo.fake,quyu.bm')
                ->join('qz_subthematic_cases choose on choose.case_id  = cases.id')
                ->join('qz_user users on users.id = cases.uid')
                ->join('qz_quyu quyu on quyu.cid = users.cs')
                ->join('qz_user_company companyinfo on companyinfo.userid  = users.id')
//                ->group('cases.id')
                ->limit($pageIndex,$pageCount)
                ->order('cases.time desc')
                ->select();

        }elseif(isset($getdata['status']) && $getdata['status'] == 2){      //选取状态为否

            return M('cases')->alias('cases')
                ->where($map)
                ->field('cases.id cases_id,cases.title cases_title,users.on,users.id company_id,users.jc company_jc,companyinfo.fake,quyu.bm')
                ->join('qz_user users on users.id = cases.uid')
                ->join('qz_quyu quyu on quyu.cid = users.cs')
                ->join('qz_user_company companyinfo on companyinfo.userid  = users.id')
                ->join('LEFT JOIN qz_subthematic_cases choose on choose.case_id  = cases.id and choose.subthematic_id ='.$subthematicid)
                ->where('choose.id is null')
//                ->group('cases.id')
                ->limit($pageIndex,$pageCount)
                ->order('cases.time desc')
                ->select();

        }else{                                                              //全部
            if($map) {

                return M('cases')->alias('cases')
                    ->where($map)
                    ->field('cases.id cases_id,cases.title cases_title,users.on,users.id company_id,users.jc company_jc,companyinfo.fake,quyu.bm')
                    ->join('qz_user users on users.id = cases.uid')
                    ->join('qz_quyu quyu on quyu.cid = users.cs')
                    ->join('qz_user_company companyinfo on companyinfo.userid  = users.id')
//                    ->group('cases.id')
                    ->limit($pageIndex,$pageCount)
                    ->order('cases.time desc')
                    ->select();

            }else{
                return array();
            }
        }

    }

    //
    public function selectCasesbyids($casesids)
    {
        if (empty($casesids)) {
            return false;
        }
        $map = array(
            'c.id' => ['IN',$casesids],
            'c.on' => 1,
            'c.classid'=>['IN',array(1,2,3)],
        );
        //注意此处连表qz_user_company的ID问题
        return M('cases')->alias('c')
            ->field('c.id')
            ->where($map)
            ->select();
    }

}