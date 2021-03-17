<?php

//装修公司活动管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\QuanWuRecordLogicModel;

class ActivityController extends HomeBaseController{
    private $status = array(
        "2" => "未开始",
        "1" => "进行中",
        "0" => "已失效",
    );

    private $level = array(
        "1" => "一等奖",
        "2" => "二等奖",
        "3" => "三等奖",
        "4" => "四等奖",
        "5" => "五等奖",
        "6" => "六等奖",
        "7" => "七等奖",
        "8" => "八等奖",
        "9" => "九等奖",
        "99" => "安慰奖"
    );

    private $type = array(
        "1" => "礼品",
        "2" => "门店优惠券",
        "3" => "电商优惠券",
        "4" => "微信口令红包"
    );

    private $model = array(
        "1" => "线下兑奖",
        "2" => "公众号兑奖",
        "3" => "网页兑奖"
    );

    //首页
    public function index(){
        //获取活动列表
        $result = $this->getActivityList(I("get.name"),I("get.start"),I("get.end"),I("get.status"));

        foreach ($result["list"] as $key => $item) {
            if ($item['prize_start'] == "0000-00-00 00:00:00") {
                $result["list"][$key]['prize_start'] = "";
            }

            if ($item['prize_end'] == "0000-00-00 00:00:00") {
                $result["list"][$key]['prize_end'] = "";
            }
        }

        $this->assign("status",$this->status);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    //活动设置
    public function setActivity(){
        if (IS_POST) {
            $id = I('post.id', 0, 'trim,intval');

            $acyivity_name = I('post.name', '', 'trim');
            if (empty($acyivity_name)) {
                $this->ajaxReturn(['info' => '请先输入活动名称', 'status' => 0]);
            }

            $start = I('post.start', '', 'trim');
            $end = I('post.end', '', 'trim');
            if (empty($start)) {
                $this->ajaxReturn(['info' => '请先填写活动开始时间', 'status' => 0]);
            } else if(empty($end)) {
                $this->ajaxReturn(['info' => '请先填写活动结束时间', 'status' => 0]);
            } else if ($start >= $end) {
                $this->ajaxReturn(['info' => '活动开始时间不能大于活动结束时间', 'status' => 0]);
            }

            $prize_start = I('post.prize_start', '', 'trim');
            $prize_end = I('post.prize_end', '', 'trim');
            if ($prize_start && $prize_end && $prize_start >= $prize_end) {
                $this->ajaxReturn(['info' => '兑奖开始时间不能大于兑奖结束时间', 'status' => 0]);
            }

            if (I("post.activity_location") == "") {
                $this->ajaxReturn(['info' => '请先选择活动端口', 'status' => 0]);
            }

            $max_participation = I('post.max_participation', 0, 'trim,intval');
            $default_participation = I('post.default_participation', 0, 'trim,intval');
            if ($max_participation != 0 && $default_participation > $max_participation) {
                $this->ajaxReturn(['info' => '参与默认次数不能大于参与最大次数', 'status' => 0]);
            }

            if ($default_participation < 0) {
                $this->ajaxReturn(['info' => '参与默认次数不能小于0', 'status' => 0]);
            }

            // 默认活动未开始
            $activity_isbegin = false;
            if (!empty($id)) {
                $map = array("id" => $id);
                $activity = M('activity')->where($map)->find();
                if (empty($activity)) {
                    $this->ajaxReturn(['info' => '活动不存在', 'status' => 0]);
                }

                // 活动是否开始
                $activity_isbegin = time() >= strtotime($activity['start']);
                if ($activity_isbegin && strtotime($end) < strtotime($activity['end'])) {
                    $this->ajaxReturn(['info' => '活动开始后只能延长活动结束时间', 'status' => 0]);
                }
            }

            $data = array(
                'name' => $acyivity_name,
                "mobile_url" => I("post.mobile_url"),
                "mobile_cover_url" => I("post.mobile_cover_url"),
                "cover" => I("post.cover"),
                'end' => $end,
                'prize_start' => $prize_start,
                'prize_end' => $prize_end,
                "enrollment" => I("post.enrollment"),
                "src" => str_replace('，', ',', trim(I("post.src"))),
                "source_id" => implode(',', I('post.source', [], 'trim')),
                "activity_location" => I("post.activity_location"),
                'time' => time(),
                'max_participation' => $max_participation,
                'default_participation' => $default_participation
            );

            if ($activity_isbegin == false) {
                $data["start"] = $start;
            }

            $item = htmlspecialchars_decode(I("post.item"));
            $item = json_decode($item,true);

            $subData = [];
            foreach ($item as $key => $value) {
                foreach ($value as $k => $val) {
                    if ($val["name"] == "prize_rate") {
                        if (  !is_numeric($val["value"]) ) {
                            $val["value"] = 0;
                        }
                    }
                    $subData[$key][$val["name"]] = $val["value"];
                }
            }

            // 奖项必填项验证
            foreach ($subData as $key => $value) {
                if (!$value['prize_level'] || !$value['prize_type'] || !$value['prize_name']) {
                    $this->ajaxReturn(array("info"=>"请先完善活动奖项信息","status"=>0));
                }

                if ($value["prize_count"] < 0) {
                    $this->ajaxReturn(array("info"=>"活动奖项奖品数量不合法","status"=>0));
                }
            }

            M()->startTrans(); // 开启事务

            if (!empty($id)) {
                $i =  D("Activity")->eidtActivity($id,$data);
            } else {
               //添加活动
                $id = $i =  D("Activity")->addActivity($data);
            }

            if ($i != false) {
                // 查询活动原有奖项ID
                $prizeAll = D("activity")->getPrizeAll($id, 'id,prize_receive_count');
                if (!empty($prizeAll) && $activity_isbegin == false) {
                    $prize_ids = array_column($prizeAll, 'id');
                    $sub_ids = array_filter(array_column($subData, 'id')); // 从最新奖项中拿到原有奖项ID
                    $del_ids = array_diff($prize_ids, $sub_ids); // 需要删除的奖项ID

                    // 删除相应奖项
                    if (!empty($del_ids)) {
                        D("Activity")->delPrizes($del_ids);
                    }
                }

                $prizeAll = array_column($prizeAll, null, 'id');

                $prize_info = [];
                foreach ($subData as $key => $value) {
                    $prize_count = $value["prize_count"];

                    $info = array(
                        "activity_id" => $id,
                        "prize_rate" => $value["prize_rate"],
                        "prize_tips" => $value["prize_tips"],
                        "prize_mode" => $value["prize_model"],
                        "prize_address" => $value["prize_address"],
                        "prize_day_count" => $value["prize_day_count"],
                        "updated_at" => time()
                    );

                    if (!empty($value["id"])) {
                        // 奖项剩余数量（暂时不允许修改）
                        // $prize_receive_count = $prizeAll[$prize_id]["prize_receive_count"];
                        // if ($prize_count >= $prize_receive_count) {
                        //     $prize_residue = $prize_count - $prize_receive_count;

                        //     // 只有修改后的奖项数量大于等于已领取数量时才允许修改
                        //     $info["prize_count"] = $prize_count;
                        // }

                        $prize_id = $value["id"];
                        $i = D("Activity")->editPrize($prize_id, $info);

                        // 修改之后把奖项ID追加进数据组用于生成记录
                        $info['id'] = $prize_id;
                        $prize_info[] = $info;
                    } else if ($activity_isbegin == false) { // 活动未开始才能新增奖项
                        // 奖项剩余数量默认为奖项数量
                        $prize_residue = $prize_count;

                        $info["prize_type"] = $value["prize_type"];
                        $info["prize_name"] = $value["prize_name"];
                        $info["prize_level"] = $value["prize_level"];
                        $info["prize_count"] = $prize_count;
                        $info["created_at"] = time();
                        $i = $prize_id = D("Activity")->addPrize($info);

                        $prize_info[] = $info;
                    } else {
                        $i = true;
                    }

                    if ($i == false) {
                        break;
                    }

                    // 奖项剩余数量变动记录
                    if (isset($prize_residue)) {
                        $logData = array(
                            "prize_id" => $prize_id,
                            "prize_count" => $prize_count,
                            "prize_residue" => $prize_residue,
                            "date" => date("Y-m-d"),
                            "created_at" => time(),
                            "updated_at" => time()
                        );

                        $logResult = M("activity_prize_count_log")->add($logData);
                    }
                }

                // 活动修改操作记录
                $logAdmin = array(
                    "action_id" => $id,
                    "remark" => "活动信息修改",
                    "logtype" => "activity-change",
                    "info" => array(
                        "activity" => $data,
                        "prize_list" => $prize_info
                    )
                );

                $logAdminRes = D("logAdmin")->addLog($logAdmin);
            }

            if ($i !== false) {
                M()->commit(); // 事务提交
                $this->ajaxReturn(array("status"=>1));
            }

            M()->rollback(); // 事务回滚
            $this->ajaxReturn(array("info"=>"操作失败,请是刷新重试！","status"=>0));
        } else {
            $id = I('get.id', 0, 'trim,intval');
            if (!empty($id)) {
                $info =  $this->getActivity($id);
                $info["itemCount"] = count($info["child"]);
                if ($info["prize_start"] == "0000-00-00 00:00:00") {
                    $info["prize_start"] = "";
                }

                if ($info["prize_end"] == "0000-00-00 00:00:00") {
                    $info["prize_end"] = "";
                }

                $this->assign("info",$info);
                if (!empty($info['cover'])) {
                    $this->assign("coverPreview","'".'<img style="width:100%;" src="//'.C('QINIU_DOMAIN').'/'.$info['cover'].'">'."'");
                }
                if (!empty($info['mobile_cover_url'])) {
                    $this->assign("mobileCoverPreview","'".'<img style="width:100%;" src="http://'.C('QINIU_DOMAIN').'/'.$info['mobile_cover_url'].'">'."'");
                }

                $activity_isbegin = intval(time() >= strtotime($info['start']));
                $this->assign("activity_isbegin", $activity_isbegin);
            }

            //获取发单位置标识
            $result = D("OrderSource")->getSourceList(2);
            $this->assign("source",$result);
            $this->assign("level",$this->level);
            $this->assign("type",$this->type);
            $this->assign("model",$this->model);
            $this->display("setActivity");
        }
    }

   //活动详情
    public function activityDetail(){
        $id = I("get.id");
        $info =  $this->getActivity($id);

        if ($info['prize_start'] == "0000-00-00 00:00:00") {
            $info['prize_start'] = "";
        }

        if ($info['prize_end'] == "0000-00-00 00:00:00") {
            $info['prize_end'] = "";
        }

        //获取发单位置标识
        $result = D("OrderSource")->getSourceList(2);
        $this->assign("source",$result);

        $result = $this->getPrizeUserList($info["id"]);

        //获取中奖信息人员名单
        // if (count($info["source_id"]) > 0) {
        //     $result = $this->getPrizeUserList($info["id"]);
        // } else {
        //     $result = $this->getNoSourcePrizeUserList($info["name"]);
        // }

        $this->assign("userList",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("info",$info);
        $this->assign("level",$this->level);
        $this->assign("type",$this->type);
        $this->assign("model",$this->model);
        $this->display("activityDetail");
    }

   /**
    * 编辑奖品发放
    * @return [type] [description]
    */
    public function editPrizeUser()
    {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "prize_status" => 1
            );
            $i = D("Activity")->editPrizeUser($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败,请是刷新重试！","status"=>0));
        }
    }

   /**
    * 活动列表
    * @param  string $name   [活动名称]
    * @param  datetime $begin  [活动开始时间]
    * @param  datetime $end    [活动结束时间]
    * @param  int $status [活动状态]
    * @return array
    */
    private function getActivityList($name,$begin,$end,$status)
    {
        $count = D("Activity")->getActivityListCount($name,$begin,$end,$status);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $result = D("Activity")->getActivityList($name,htmlspecialchars_decode($begin),htmlspecialchars_decode($end),$status,$p->firstRow, $p->listRows);
            //通过活动时间来判断当前活动状态
            foreach ($result as $k => $v) {
                $now = date('Y-m-d H:i:s', time());
                if ($v['start'] < $now && $v['end'] < $now) {
                    $result[$k]['status'] = 2;
                } elseif ($v['start'] <= $now && $v['end'] >= $now) {
                    $result[$k]['status'] = 1;
                } elseif ($v['start'] > $now && $v['end'] > $now) {
                    $result[$k]['status'] = 0;
                }
            }
        }
        return array("list"=>$result,"page"=>$show);
    }


    private function getActivity($id)
    {
        $result = D("Activity")->getActivity($id);

        foreach ($result as $key => $value) {
            if (!isset($info)) {
                $info["id"] = $value["id"];
                $info["name"] = $value["name"];
                $info["cover"] = $value["cover"];
                $info["mobile_url"] = $value["mobile_url"];
                $info["mobile_cover_url"] = $value["mobile_cover_url"];
                $info["start"] = $value["start"];
                $info["end"] = $value["end"];
                $info["prize_start"] = $value["prize_start"];
                $info["prize_end"] = $value["prize_end"];
                $info["enrollment"] = $value["enrollment"];
                $info["src"] = $value["src"];
                $info["source_id"] = array_flip(array_filter(explode(",", $value["source_id"])));
                $info["status"] = $value["status"];
                $info["activity_location"] = $value["activity_location"];
                $info['default_participation'] = $value['default_participation'];
                $info['max_participation'] = $value['max_participation'];
            }

            // 当前奖品剩余数量
            $prize_residue = '-';
            if ($value["prize_count"] > 0) {
                $prize_residue = 0;
                if ($value["prize_count"] > $value["prize_receive_count"]) {
                    $prize_residue = $value["prize_count"] - $value["prize_receive_count"];
                }
            }

            $info["child"][] = array(
                "id" => $value["prize_id"],
                "activity_id" => $value["activity_id"],
                "prize_name"  =>  $value["prize_name"],
                "prize_level" => $value["prize_level"],
                "prize_type"  =>  $value["prize_type"],
                "prize_count" => $value["prize_count"],
                "prize_rate"  =>  $value["prize_rate"],
                "prize_address"=> $value["prize_address"],
                "prize_tips"  =>  $value["prize_tips"],
                "prize_mode"  =>  $value["prize_mode"],
                "prize_day_count"  => $value["prize_day_count"],
                "prize_receive_count"  => $value["prize_receive_count"],
                "prize_residue"  => $prize_residue,
                "level" => $this->level[$value["prize_level"]],
                "type" => $this->level[$value["prize_type"]]
            );
        }

        return $info;
    }

   /**
    * 参与人员名单
    * @param  [type] $id [description]
    * @return [type]     [description]
    */
    private function getPrizeUserList($id)
    {
        $count = D("Activity")->getPrizeUserListCount($id);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 50);
            $show = $p->show();
            $result = D("Activity")->getPrizeUserList($id,$p->firstRow, $p->listRows);
        }

        return array("list"=>$result,"page"=>$show);
    }

   /**
    * 获取非发单活动的中奖人员名单
    * @param  [type] $id [description]
    * @return [type]     [description]
    */
    private function getNoSourcePrizeUserList($id)
    {
        $count = D("Activity")->getNoSourcePrizeUserListCount($id);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 50);
            $show = $p->show();
            $result = D("Activity")->getNoSourcePrizeUserList($id,$p->firstRow, $p->listRows);
        }

        return array("list"=>$result,"page"=>$show);
    }

    /**
     * 活动修改记录查看
     * @return [type] [description]
     */
    public function activityLog(){
        $offset = I("offset", 0, "intval");
        $limit = I("limit", 20, "intval");

        $map = array(
            "l.logtype" => "activity-change"
        );

        if ($userid = I("userid")) {
            $map["l.userid"] = array("EQ", $userid);
        }

        if ($activity_id = I("activity_id")) {
            $map["l.action_id"] = array("EQ", $activity_id);
        }

        if ($start = I("start")) {
            $map["l.time"] = array("EGT", $start);
        }

        if ($end = I("end")) {
            $map["l.time"] = array("ELT", $end . " 23:59:59");
        }

        $list = D("logAdmin")->alias("l")
            ->where($map)
            ->join("left join qz_activity as a on a.id=l.action_id")
            ->field("l.*,a.name activity_name")
            ->order("l.id desc")
            ->limit($offset, $limit)
            ->select();

        foreach ($list as $key => $item) {
            $list[$key]["info"] = json_decode($item["info"], true);
        }

        $this->assign("list", $list);
        $this->display();
    }


    /**
     * 全屋定制落地页后台管理
     */
    public function qwdzldy(){
        $logic = new QuanWuRecordLogicModel();
        $data = I('get.');
        //站点城市
        $city = D('Quyu')->getQuyuList();
        $list = $logic->getquanwuOrderList($data,I('get.p'));

        $this->assign('city',$city);
        $this->assign('getdata',$data);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display();
    }

    //导出报表
    public function exportQuanwuOrder()
    {
        $logic = new QuanWuRecordLogicModel();
        $data = I('get.');

        ini_set('memory_limit','512M');
        ini_set('max_execution_time',  60 * 3);
        $excelData = [];
        $excelData['header'] =   [
            '发布时间' => 'string',
            '城市区县' => 'string',
            '姓名' => 'string',
            '联系方式' => 'string',
            '来源页面' => 'string',
        ];
        $excelData['sheet'] = 'sheet1';
        $excelData['row'] = [];
        $result = $logic->getAllquanwuOrderListByData($data);
        $rowAll = [];
        $i = 0;
        foreach ($result as $key => $value) {
            $rowAll[$i][0] = $value['created_at'];
            $rowAll[$i][1] = $value['cname'].$value['quxian'];
            $rowAll[$i][2] = $value['name'];
            $rowAll[$i][3] = $value['tel'];
            if($value['source'] == 1){
                $rowAll[$i][4] = 'PC';
            }elseif($value['source'] == 2){
                $rowAll[$i][4] = 'M';
            }else{
                $rowAll[$i][4] = '';
            }
            $i++;
        }

        $excelData['row'] = $rowAll;
        $excelData['filename'] = '全屋定制订单.xlsx';
        export_excel_download($excelData);
    }

}
