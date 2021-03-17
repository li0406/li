<?php
/**
 * ImageVerify Model.
 */

namespace Home\Model;
Use Think\Model;

class ImageVerifyModel extends Model {

    protected $autoCheckFields = false;

    /**
     * Gets the cases count.
     *
     * @param  <type>  $cid         The case id
     * @param  <type>  $cs          The city
     * @param  <type>  $des_id      The designer identifier
     * @param  <type>  $com_id      The company identifier
     * @param  <type>  $status      The status
     * @param  <type>  $time_start  The time start
     * @param  <type>  $time_end    The time end
     *
     * @return <type>  The cases count.
     */
    public function getCasesCount($cid,$cs,$des_id,$com_id,$status,$time_start,$time_end)
    {
        $map = [];

        if (!empty($cid)) {
            $map['a.id'] = $cid;
        }
        if (!empty($cs)) {
            $map['a.cs'] = intval($cs);
        }
        if (!empty($des_id)) {
            $map['a.userid'] = intval($des_id);;
        }
        if (!empty($com_id)) {
            $map['a.uid'] = intval($com_id);;
        }
        if (!empty($time_start)) {
            $map['a.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['a.time'][] = array('ELT', $time_end);
        }

        //如果其它条件为空，数据缓存5分钟
        if(empty($map)){
            if(!empty($status)){
                $map['status'] = array('EQ', $status);
            }
            $count = S('Cache:Case:imgCount:status:'.$status);
            if(!$count){
                $count = M('case_img')->where($map)->count(1);
                S("Cache:Case:imgCount:status:".$status,$count,300);
            }
            return $count;
        }else{
            if (!empty($status)) {
                $map['b.status'] = array('EQ', $status);
            }
        }

        return M('cases')->alias('a')
            ->join('INNER JOIN qz_case_img as b on a.id = b.caseid')
            ->where($map)
            ->count(1);
    }

    /**
     * Gets the cases list.
     *
     * @param      <type>   $cases_id    The cases identifier
     * @param      <type>   $cs          The city
     * @param      <type>   $des_id      The designer identifier
     * @param      <type>   $com_id      The company identifier
     * @param      <type>   $status      The status
     * @param      <type>   $time_start  The time start
     * @param      <type>   $time_end    The time end
     * @param      integer $start The start
     * @param      integer $end The end
     *
     * @return     <type>   The cases list.
     */
    public function getCasesList($cid, $cs, $des_id, $com_id, $status, $time_start, $time_end, $start = 0, $end = 10)
    {
        $map = [];

        if (!empty($cid)) {
            $map['a.id'] = $cid;
        }
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
            $map['b.status'] = intval($status);;
        }
        if (!empty($time_start)) {
            $map['a.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['a.time'][] = array('ELT', $time_end);
        }
        $build = M('cases')->alias('a')
            ->field('a.id as caseid,a.on as cases_on,a.uid,a.title,a.cs,a.userid,a.time,a.status as case_status,b.id as imgid,b.status,b.img_path as src,b.img as src_img,b.img_host')
            ->join('INNER JOIN qz_case_img as b on a.id = b.caseid ')
            ->where($map)
            ->limit($start, $end)
            ->order('a.id DESC')
            ->buildSql();

        return M('cases')->table($build)->alias('c')
            ->field('c.*,u.qc AS company_name,u.id AS company_id,d.name AS design_name,q.cname,q.bm')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = c.cs')
            ->join('LEFT JOIN qz_user AS u ON u.id = c.uid')
            ->join('LEFT JOIN qz_user AS d ON d.id = c.userid')
            ->select();
    }


    /**
     * Gets the case information by identifier.
     * @param      string $id The identifier
     * @return     <type>  The case information by identifier.
     */
    public function getCaseInfoById($caseid, $status)
    {
        if (empty($caseid)) {
            return false;
        }
        $map['c.id'] = array("EQ", $caseid);
        $buildSql = M('cases')->alias('c')->where($map)
            ->field('c.id,c.on as caseon,c.uid,c.title,c.time,c.userid,c.cs,u.qc,u.on,u2.name as designer,q.cname,q.bm')
            ->join('LEFT JOIN qz_user as u on u.id = c.uid')
            ->join('LEFT JOIN qz_user as u2 on u2.id = c.userid')
            ->join('LEFT JOIN qz_quyu as q on q.cid = c.cs')
            ->buildSql();
        $myon = '';
        if (!empty($status)) {
            $myon = " AND d.status = '$status' ";
        }
        return M("cases")->table($buildSql)->alias("t1")
            ->join("INNER JOIN qz_case_img d on t1.id = d.caseid $myon")
            ->field("t1.*,d.img,d.img_host,d.img_path,d.img_on,d.id as imgid,d.status as img_status")
            ->order("d.id asc")
            ->select();

    }

    public function getThreedInfoById($id='',$status){

        $map['c.id'] = array("EQ",$id);
        $map['c.classid'] = array("EQ",4);
        if(!empty($status)){
            $maps = " AND d.status = '$status' ";
        }

        $buildSql = M('cases')->alias('c')->where($map)
            ->field('c.id,c.on as caseon,c.uid,c.title,c.time,c.userid,c.cs,u.qc,u.on,u2.name as designer,q.cname,q.bm')
            ->join('LEFT JOIN qz_user as u on u.id = c.uid')
            ->join('LEFT JOIN qz_user as u2 on u2.id = c.userid')
            ->join('LEFT JOIN qz_quyu as q on q.cid = c.cs')
            ->buildSql();

        return M("cases")->table($buildSql)->alias("t1")
            ->join("INNER JOIN qz_case_img d on t1.id = d.caseid $maps")
            ->field("t1.*,d.img,d.img_host,d.img_path,d.img_on,d.id as imgid,d.status as img_status")
            ->order("d.id asc")
            ->group('d.caseid')
            ->select();

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

    /**
     * Gets the case by identifier.
     * @param   <type>   $id The identifier
     * @return  boolean  The case by identifier.
     */
    public function getCaseById($case_id)
    {
        if (empty($case_id)) {
            return false;
        }
        return M('cases')->where(array('id' => intval($case_id)))->find();
    }

    /**
     * 获取装修公司未审核和审核通过的装修总数
     * @param $uid int 装修公司ID
     * @return mixed
     */
    public function getCaseCount($id)
    {
        $map = [
            'uid' => ['eq', $id],
            'on' => ['in', [1, 2]],
        ];
        return M('Cases')->where($map)->count(1);
    }

    /**
     * Gets the previous case.
     * @param  <type>  $id The identifier
     * @return <type>  The previous case.
     */
    public function getPrevCase($nowid, $cs, $des_id, $com_id, $status, $time_start, $time_end)
    {
        $map['a.id'] = array('LT', $nowid);

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
            $map['b.status'] = intval($status);;
        }
        if (!empty($time_start)) {
            $map['a.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['a.time'][] = array('ELT', $time_end);
        }
        if (!empty($status)) {
            $map['b.status'] = array('EQ', $status);
        }
        return M('cases')->alias('a')
            ->field('a.id')
            ->join('INNER JOIN qz_case_img as b on a.id = b.caseid')
            ->where($map)
            ->order('a.id DESC')
            ->find();
    }

    /**
     * Gets the next case.
     * @param  <type>  $id The identifier
     * @return <type>  The next case.
     */
    public function getNextCase($nowid, $cs, $des_id, $com_id, $status, $time_start, $time_end)
    {
        $map['a.id'] = array('GT', $nowid);

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
            $map['b.status'] = intval($status);;
        }
        if (!empty($time_start)) {
            $map['a.time'][] = array('EGT', $time_start);
        }
        if (!empty($time_end)) {
            $map['a.time'][] = array('ELT', $time_end);
        }
        if (!empty($status)) {
            $map['b.status'] = array('EQ', $status);
        }

        return M('cases')->alias('a')->field('a.id')
            ->join('INNER JOIN qz_case_img as b on a.id = b.caseid')
            ->where($map)
            ->order('a.id')
            ->find();
    }

    /**
     * Gets the case image review.
     * @param  <type>  $id     The identifier
     * @return <type>  The case image review.
     */
    public function getCaseImgReview($id){
        return M('case_review')->field('*')->where(array('imgid' => array('EQ',$id)))->find();
    }

    /**
     * Sets the case image review.
     * @param  integer  $id      The identifier
     * @param  integer  $status  The status
     * @return boolean
     */
    public function setCaseImgReview($id,$data){
        if (empty($id) || empty($data)) {
            return false;
        }
        return M('case_review')->where(array('imgid' => $id))->save($data);
    }

    /**
     * Gets the review by case identifier.
     * @param  <type>  $id     The identifier
     * @return <type>  The review by case identifier.
     */
    public function getReviewByImgId($id)
    {
        return M('case_review')->field('*')->where(array('imgid' => array('EQ', $id)))->find();
    }

    public function getReviewBycaseId($id)
    {
        return M('case_review')->field('*')->where(array('caseid' => array('EQ', $id)))->find();
    }

    /**
     * Gets the case by identifier.
     * @param  int   $id     The identifier
     * @return array The case info.
     */
    public function getCaseImgById($id)
    {
        return M('cases')->alias('a')
            ->join('INNER JOIN qz_case_img as b on a.id = b.caseid')
            ->field('a.id')
            ->where(array('a.id' => array('EQ', $id)))
            ->find();
    }

    /**
     * Sets the case image status.
     *
     * @param      integer  $id      The identifier
     * @param      integer  $status  The status
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public function setCaseImgStatus($id = 0, $status = 0){
        if (empty($id) || empty($status)) {
            return false;
        }
        return M('case_img')->where(array('id' => $id))->save(array('status' => $status));
    }

    /**
     * Sets the case image status by case identifier.
     *
     * @param      integer  $cid     The cid
     * @param      integer  $status  The status
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public function setCaseImgStatusByCaseId($cid = 0, $status = 0){
        if (empty($cid) || empty($status)) {
            return false;
        }
        return M('case_img')->where(array('caseid' => $cid))->save(array('status' => $status));
    }

    /**
     * 设置案例状态.
     *
     * @param  integer  $id      The identifier
     * @param  integer  $status  The status
     * @return boolean  ( description_of_the_return_value )
     */
    public function setCaseStatus($id = 0, $status = 0){
        if (empty($id) || empty($status)) {
            return false;
        }
        return M('cases')->where(array('id' => $id))->save(array('status' => $status));
    }

    /**
     * 设置案例是否审核.
     *
     * @param  <type>   $id     The identifier
     * @param  <type>   $on     status
     * @return boolean
     */
    public function setCaseOn($id, $on)
    {
        if (empty($id) || empty($on)) {
            return false;
        }
        return M('cases')->where(array('id' => $id))->save(array('on' => $on));
    }

    /**
     * 设置案例分数.
     *
     * @param  integer  $id     The identifier
     * @param  integer  $grade  The grade
     * @return boolean
     */
    public function setCaseGrade($id = 0, $grade = 0){
        if (empty($id) || empty($grade)) {
            return false;
        }
        return M('cases')->where(array('id' => $id))->save(array('grade' => $grade));
    }

    /**
     * Gets the case image.
     * @param <type>  $imgid  The image id
     */
    public function getCaseImg($imgid)
    {
        return M('case_img')->field('*')->where(array('id' => array('EQ', $imgid)))->find();
    }

    /**
     * 获取对应条件的图片条数
     * @param $map
     * @return mixed
     */
    public function getCaseInfoCount($map){
        return M('case_img')->field('*')->where($map)->count();
    }

    /**
     * 获取对应条件的图片条数
     */
    public function getThreedCount($cid, $cs, $des_id, $com_id, $status, $time_start)
    {
        $map['a.classid'] = 4;
        if (!empty($cid)) {
            $map['a.id'] = $cid;
        }
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
        return M('cases')->alias('a')->where($map)->count();
    }

    /**
     * Gets the cases list.
     *
     * @param      <type>   $cases_id    The cases identifier
     * @param      <type>   $cs          The city
     * @param      <type>   $des_id      The designer identifier
     * @param      <type>   $com_id      The company identifier
     * @param      <type>   $status      The status
     * @param      <type>   $time_start  The time start
     * @param      <type>   $time_end    The time end
     * @param      integer  $start       The start
     * @param      integer  $end         The end
     *
     * @return     <type>   The cases list.
     */
    public function getThreedList($cid, $cs, $des_id, $com_id, $time_start, $time_end, $status, $start = 0, $end = 10)
    {
        $map['a.classid'] = 4;

        if (!empty($cid)) {
            $map['a.id'] = $cid;
        }
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

        $build = M('cases')->alias('a')
            ->field('a.id as caseid,a.on as cases_on,a.uid,a.classid,a.title,a.cs,a.userid,a.time,a.status as case_status,b.id as imgid,b.status,b.img_path as src,b.img as src_img,b.img_host')
            ->join('INNER JOIN qz_case_img as b on a.id = b.caseid ')
            ->where($map)
            ->limit($start, $end)
            ->group('b.caseid')
            ->order('b.id DESC')
            ->buildSql();

        return M('cases')->table($build)->alias('c')
            ->field('c.*,u.qc AS company_name,u.id AS company_id,d.name AS design_name,q.cname,q.bm')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = c.cs')
            ->join('LEFT JOIN qz_user AS u ON u.id = c.uid')
            ->join('LEFT JOIN qz_user AS d ON d.id = c.userid')
            ->order('c.imgid desc')
            ->select();
    }

    /**
     * getThreedStatusAndChange
     * 查询3D效果图的案例状态， 如果不对应，则以qz_cases表的状态为准
     * @param $id
     */
    public function getThreedStatusAndChange($id){
        $casestatus =M('cases')->where(array('id'=>$id))->getField('status');
        $where['caseid'] = $id;
        $data['status'] = $casestatus;
        $num = M('case_img')->where($where)->save($data);
        return $num;

    }


}