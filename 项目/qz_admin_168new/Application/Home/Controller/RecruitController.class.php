<?php
//人事部门统计
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class RecruitController extends HomeBaseController
{
    //人事部门ID
    public static $PERSONNAL_DEP_ID  =  11;

    public  $today;
    public function _initialize()
    {
        parent::_initialize();
        header('Content-Type: text/html; charset=utf-8');
        $this->today = date('Y-m-d');
        $this->assign('now',$this->today);
    }

    /**
     * 人事电话统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function index()
    {
        //获取人事列表
        $personaleList = D("Personal")->getPersonalList(self::$PERSONNAL_DEP_ID,true);
        $personaleIds = implode(',',array_column($personaleList,'id'));
        $id = I('get.pid',0,'intval');
        $begin = I("get.begin");
        $end = I("get.end");
        if ($id === 0){
            $id = $personaleIds;
        }
        if (!empty($begin) && !empty($end)){
            if (strtotime($begin) === FALSE || strtotime($end) === FALSE ){
                $this->error('时间格式错误，请正确选择');
            }
            if (ceil((strtotime($end) - strtotime($begin))/86400) > 31){
                $this->error('查询时间不能大于31天');
            }

            $begin =  date('Y-m-d',strtotime($begin)).' 00:00:00';
            $end = date('Y-m-d',strtotime($end)).' 23:59:59';
        }else{
            $begin = $this->today.' 00:00:00';
            $end = $this->today.' 23:59:59';
        }
        //统计数据
        $list = $this->getTelStat($id, $begin, $end,'all');
        $this->assign("personaleList", $personaleList);
        $this->assign("id", $id);
        $this->assign("list", $list);
        $this->assign("begin", date('Y-m-d',strtotime($begin)));
        $this->assign("end", date('Y-m-d',strtotime($end)));
        $this->display();
        die(0);
    }

    /**
     * 人事电话量统计
     * @param  int $id    [人事ID]
     * @param  string $begin [开始时间]
     * @param  string $end   [结束时间]
     * @return array  [结果]
     */
    private function getTelStat($id, $begin, $end,$action = '')
    {
        $monthStart = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $monthEnd = date("Y-m-d", mktime(0, 0, 0, date("m"), date("t"), date("Y")) + 86400);
        if (!empty($begin) && !empty($end)) {
            $monthStart = $begin;
            $monthEnd = $end;
        }
        switch ($action)
        {
            case 'all':
                $group = 'uid';
                break;
            default:
                $group = 'uid,days';
        }
        $result = D("Personal")->getTelStat($id, $monthStart, $monthEnd,$group);
        foreach ($result as $key => $value) {
            $value["avg_time"] = timediff(ceil($value["sum_time"] / $value["tel_count"]));
            $value["sum_time"] = timediff($value["sum_time"]);
            $list[$key] = $value;
        }
        return $list;
    }

    /**
     * 每天具体信息查看
     * @param [int]id [人事员工id]
     * @param [dateformat]begin [开始时间]
     * @param [dateformat]end [结束时间]
     * return view
     */
    public function days()
    {
        $id = I('get.id',0,'intval');
        $begin = I("get.begin");
        $end = I("get.end");
        if ($id === 0){
            $this->error('需要查找的员工不存在');
        }
        $user = M('adminuser')->field('id,user,name')->where(['stat'=>1,'id'=>$id])->find();
        if (empty($user)){
            $this->error('需要查找的员工不存在');
        }
        if (empty($begin)&&empty($end)){
            $this->error('请先选择时间段');
        }
        if (strtotime($begin) === FALSE || strtotime($end) === FALSE ){
            $this->error('请检查选择的时间段');
        }

        if (ceil((strtotime($end) - strtotime($begin))/86400) > 31){
            $this->error('查询时间不能大于31天');
        }
        $begin =  date('Y-m-d',strtotime($begin)).' 00:00:00';
        $end = date('Y-m-d',strtotime($end)).' 23:59:59';

        //统计数据
        $list = $this->getTelStat($id, $begin, $end);
        $this->assign("list", $list);
        $this->assign("user", $user);
        $this->display();
        exit();
    }

    /**
     * 根据天数查看每天具体明细[AJAX]
     * @param [int]id [人事员工id]
     * @param [dateformat]day [需要查看的具体日期]
     * @param [int]pageNum [页码]
     * @param [int]pageSize [每页数据量]
     * return json
     */
    public function items()
    {
        $id = I('get.id',0,'intval');
        $day = I("get.day");
        $pageNum = I('get.pageNum',1,'intval');     //页码
        $pageSize = I('get.pageSize',15,'intval');  //每页数据量
        if ($id === 0){
            $this->ajaxReturn(array('data' => '', 'info' => '需要查找的员工不存在', 'status' =>0));
        }
        if (empty($day) || strtotime($day) === FALSE){
            $this->ajaxReturn(array('data' => '', 'info' => '请求错误，请刷新！', 'status' =>0));
        }
        //请求具体数据
        $list = D("Personal")->getItemList($id, date('Y-m-d',strtotime($day)),$pageNum,$pageSize);
        $this->ajaxReturn(array('data' => $list, 'info' => '数据获取成功', 'status' =>1));
    }

    /**
     * 人事通话录音库
     */
    public function voice()
    {
        //获取人事列表
        $personaleList = D("Personal")->getPersonalList(self::$PERSONNAL_DEP_ID,true);
        $personaleIds = implode(',',array_column($personaleList,'id'));
        $id = I('get.pid',0,'intval');
        $begin = I("get.begin");
        $end = I("get.end");
        $time = I("get.time",1,'intval');
        $pageNum = max(1, I('get.p'));
        if ($id === 0){
            $id = $personaleIds;
        }
        if (!empty($begin) && !empty($end)){
            if (strtotime($begin) === FALSE || strtotime($end) === FALSE ){
                $this->error('时间格式错误，请正确选择');
            }
            if (ceil((strtotime($end) - strtotime($begin))/86400) > 31){
                $this->error('查询时间不能大于31天');
            }

            $begin =  date('Y-m-d',strtotime($begin)).' 00:00:00';
            $end = date('Y-m-d',strtotime($end)).' 23:59:59';
        }else{
            $begin = $this->today.' 00:00:00';
            $end = $this->today.' 23:59:59';
        }

        //请求具体数据
        $data = $this->getVoiceListByPage($id, [$begin,$end],$time, $pageNum, 50);

        $this->assign("personaleList", $personaleList);
        $this->assign("id", $id);
        $this->assign("data", $data);
        $this->assign("begin", date('Y-m-d',strtotime($begin)));
        $this->assign("end", date('Y-m-d',strtotime($end)));
        $this->display();
        exit();
    }

    private function getVoiceListByPage($id, $day, $time,$pageNum, $pageSize)
    {
        $count = D("Personal")->getVoiceCount($id,$day,$time);
        if ($count > 0) {
            import('Library.Org.Page.Page');
            $config = array("prev", "first", "last", "next");
            $page = new \Page($pageNum, $pageSize, $count, $config);
            $pageTmp = $page->show();
            $list = D("Personal")->getVoiceList($id,$day,$time, $pageNum, $pageSize);
            return ['page' => $pageTmp, 'list' => $list];
        }
        return ['page'=>"",'list'=>[]];
    }
}