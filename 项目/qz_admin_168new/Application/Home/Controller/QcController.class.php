<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\QcInfoLogicModel;

/**
 *   质检模块
 */
class QcController extends HomeBaseController
{
    //录音质量
    public $video_state = array(
        "2" => "一般",
        "1" => "较差",
        "3" => "良好"
    );

    //质检类型
    public $qc_info_type = array(
        '1' => '听单',
        '2' => '讨论组听单'
    );

    //选择状态
    public $state = array(
        "1" => "优秀录音",
        "2" => "问题录音"
    );

    // 话术引导
    public $yd = array(
        "1" => "拒绝",
        "2" => "预算",
        "3" => "量房",
        "4" => "开工"
    );
    //信息核实
    public $hs = array(
        "1" => "完整",
        "2" => "不完整"
    );
    //服务态度
    public $td = array(
        "1" => "良好",
        "2" => "欠佳"
    );
    //后台备注
    public $bz = array(
        "1" => "一致",
        "2" => "不严谨",
        "3" => "错误"
    );
    //操作审核
    public $sh = array(
        "1" => "符合要求",
        "2" => "未按要求"
    );

    //订单来源
    public $order_source = array(
        "1" => "53客服",
        "2" => "400电话",
        "3" => "QQ咨询","99" => "非53,400,QQ"
    );

    //所属部门
    private $dept = array(
        "1" => "推广一部",
        "2" => "推广二部"
    );

    public function index()
    {

        //城市
        //获取全部城市
        $temp = D("Quyu")->getQuyuList();
        foreach ($temp as $key => $value) {
            $city[$value["cid"]] = $value;
        }
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        //客服列表
        $kfList = D("Adminuser")->getKfList();

        //查询活动发单位置
        $result = D("Activity")->getActivityIds();
        $ids = array();
        foreach ($result as $key => $value) {
            if ($value["source_id"] != 0) {
                $source = array_filter(explode(",",$value["source_id"]));
                $ids = array_merge($ids,$source);
            }
        }
        $ids = array_filter($ids);
        //src信息查询

        if (I("get.src") !== "") {
            $result = D("OrderSource")->getBySrc(I("get.src"));
            $src[] = array(
                "id" => $result["alias"],
                "text" => $result["alias"]
            );
        }


        //获取质检列表
        $list = $this->getQcList(I("get.begin"),I("get.end"),I("get.id"),I("get.type"),I("get.cs"),I("get.manager"),I("get.group"),I("get.user"),I("get.time_start"),I("get.time_end"),I("get.status"),I("get.source"),I("get.chk_start"),I("get.chk_end"),I("get.src") ,$ids);

        //显号
        $logic = new QcInfoLogicModel();
        $list = $logic->getOrderApplyTelList($list);

        $this->assign("src",$src);
        $this->assign("order_source",$this->order_source);
        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->assign("kfList",$kfList);
        $this->assign("manager",$users["manager"]);
        $this->assign("groups",$users["groups"]);
        $this->assign("city",$city);
        $this->display();
    }

    /**
     * [telcenter 录音库列表]
     * @return [type] [description]
     */
    public function telcenter()
    {
        //获取查询参数
        $order_id = I('get.order_id');
        $time     = I('get.time');
        $op_uid   = I('get.op_uid');
        $kf_id    = I('get.kf_id');
        $sub_yd   = I('get.sub_yd');
        $sub_hs   = I('get.sub_hs');
        $sub_td   = I('get.sub_td');
        $sub_bz   = I('get.sub_bz');
        $sub_sh   = I('get.sub_sh');

        if (!empty($time)) {
            $time_start = strtotime($time);
            $time_end = strtotime($time . ' + 1 day');
        }

        //获取结果列表
        $main['info'] = $this->getQcTelcenterList($order_id, $time_start, $time_end, $op_uid, $kf_id, $sub_yd, $sub_hs, $sub_td, $sub_bz, $sub_sh);

        //质检类型
        $main['qc_info_type'] = $this->qc_info_type;
        //录音状态
        $main['qc_telcenter_type'] = $this->state;
        //话术引导
        $main['qc_telcenter_sub_yd'] = $this->yd;
        //信息核实
        $main['qc_telcenter_sub_hs'] = $this->hs;
        //服务态度
        $main['qc_telcenter_sub_td'] = $this->td;
        //后台备注
        $main['qc_telcenter_sub_bz'] = $this->bz;
        //操作审核
        $main['qc_telcenter_sub_sh'] = $this->sh;
        //获取客服列表
        $main['ke_fu'] = D('Adminuser')->getKfList(true);
        //获取质检人员列表
        $main['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(23);

        $this->assign('main', $main);
        $this->display();
    }

    /**
     * [getQcTelcenterList description]
     * @param  [type]  $order_id   [订单ID]
     * @param  [type]  $time_start [开始时间]
     * @param  [type]  $time_end   [结束时间]
     * @param  [type]  $op_uid     [抽检人]
     * @param  [type]  $kf_id      [客服ID]
     * @param  [type]  $sub_yd     [客服引导状态]
     * @param  [type]  $sub_hs     [客服核实状态]
     * @param  [type]  $sub_td     [客服态度]
     * @param  [type]  $sub_bz     [客服备注]
     * @param  [type]  $sub_sh     [客服审核]
     * @param  integer $each       [每页显示]
     * @return [type]              [description]
     */
    public function getQcTelcenterList($order_id, $time_start, $time_end, $op_uid, $kf_id, $sub_yd, $sub_hs, $sub_td, $sub_bz, $sub_sh, $each = 10)
    {
        import('Library.Org.Util.Page');
        $count = D('QcInfo')->getQcInfoCount($order_id, $time_start, $time_end, $op_uid, $kf_id, $sub_yd, $sub_hs, $sub_td, $sub_bz, $sub_sh);
        $Page  = new \Page($count,$each);
        if ($count > $each) {
            $result['page'] = $Page->show();
        }
        $result['list'] = D('QcInfo')->getQcInfoList($order_id, $time_start, $time_end, $op_uid, $kf_id, $sub_yd, $sub_hs, $sub_td, $sub_bz, $sub_sh, $Page->firstRow,$Page->listRows);

        return $result;
    }

    /**
     * 质检页面
     * @return [type] [description]
     */
    public function qcinfo()
    {
        $id = I("get.id");
        //获取订单信息模版
        $ordrTmp = $this->getQcOrderInfoTmp($id);
        //获取质检模版
        $qcTmp = $this->getQcInfoTmp($id);
        //获取质检抽检模版
        $samplingQcTmp = $this->getSamplingQcTmp($id);

        $this->assign("samplingQcTmp",$samplingQcTmp);
        $this->assign("qcTmp",$qcTmp);
        $this->assign("orderTmp",$ordrTmp);
        $this->display();
    }

    /**
     * 查看订单
     * @return [type] [description]
     */
    public function viewOrder()
    {
        $id = I("get.id");

        //获取订单信息模版
        $ordrTmp = $this->getQcOrderInfoTmp($id);
        //获取质检模版
        $qcTmp = $this->getQcInfoTmp($id,false);
        //获取质检抽检模版
        $samplingQcTmp = $this->getSamplingQcTmp($id);

        $this->assign("samplingQcTmp",$samplingQcTmp);
        $this->assign("qcTmp",$qcTmp);
        $this->assign("orderTmp",$ordrTmp);
        $this->display("qcinfo");
    }

    /**
     * 抽检页面
     * @return [type] [description]
     */
    public function samplinginfo()
    {
        $id = I("get.id");
        //获取订单信息模版
        $ordrTmp = $this->getQcOrderInfoTmp($id);
        //获取质检模版
        $qcTmp = $this->getQcInfoTmp($id,false);
        //获取质检抽检模版
        $samplingQcTmp = $this->getSamplingQcTmp($id,true);

        $this->assign("samplingQcTmp",$samplingQcTmp);
        $this->assign("qcTmp",$qcTmp);
        $this->assign("orderTmp",$ordrTmp);
        $this->display("qcinfo");
    }

    /**
     * 质检抽检列表
     * @return [type] [description]
     */
    public function sampling()
    {
        //获取全部城市
        $temp = D("Quyu")->getQuyuList();
        foreach ($temp as $key => $value) {
            $city[$value["cid"]] = $value;
        }
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        //客服列表
        $kfList = D("Adminuser")->getKfList();
        //获取质检人员列表
        $qcList = D('Adminuser')->getAdminuserListByUid(array(23,99));

        //查询活动发单位置
        $result = D("Activity")->getActivityIds();
        $ids = array();
        foreach ($result as $key => $value) {
            if ($value["source_id"] != 0) {
                $source = array_filter(explode(",",$value["source_id"]));
                $ids = array_merge($ids,$source);
            }
        }
        $ids = array_filter($ids);
        //筛选条件
        $orders_id              = I("get.orders_id");
        $orders_cs              = I("get.orders_cs");
        $orders_type            = I("get.orders_type");
        $orders_time_real_start = I("get.orders_time_real_start");
        if (!empty($orders_time_real_start)) {
            $orders_time_real_start = strtotime($orders_time_real_start);
        } else {
            $orders_time_real_start = strtotime('-90 days');
        }
        $orders_time_real_end   = I("get.orders_time_real_end");
        if (!empty($orders_time_real_end)) {
            $orders_time_real_end = strtotime("+1 day", strtotime($orders_time_real_end));
        } else {
            $orders_time_real_end = time();
        }
        $kf_id                 = I("get.kf_id");
        $kf_group              = I("get.kf_group");
        $kf_manager            = I("get.kf_manager");
        $status                = I("get.status");
        $op_uid                = I("get.op_uid");
        $time_start            = I("get.time_start");
        $time_start            = empty($time_start) ? '' : strtotime($time_start);
        $time_end              = I("get.time_end");
        $time_end              = empty($time_end) ? '' : strtotime("+1 day", strtotime($time_end));
        $sampling_start        = I("get.sampling_start");
        $sampling_start        = empty($sampling_start) ? '' : strtotime($sampling_start);
        $sampling_end          = I("get.sampling_end");
        $sampling_end          = empty($sampling_end) ? '' : strtotime("+1 day", strtotime($sampling_end));

        //获取质检的订单信息
        $count = D('QcInfo')->getSamplingListCount($orders_id, $orders_cs, $orders_type, $orders_time_real_start, $orders_time_real_end, $kf_id, $kf_group, $kf_manager, $op_uid, $status, $time_start, $time_end, $sampling_start, $sampling_end ,$ids);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $list["show"]    = $p->show();
            $list["list"] = D('QcInfo')->getSamplingList($orders_id, $orders_cs, $orders_type, $orders_time_real_start, $orders_time_real_end, $kf_id, $kf_group, $kf_manager, $op_uid, $status, $time_start, $time_end, $sampling_start, $sampling_end,$p->firstRow,$p->listRows, $ids);

            foreach ($list["list"] as $key => $value) {
                $list["list"][$key]["sampling_time"] = empty($value["sampling_time"])?"-":date("Y-m-d H:i:s",$list[$key]["sampling_time"]);
            }
        }

        //显号
        $logic = new QcInfoLogicModel();
        $list = $logic->getOrderApplyTelList($list);

        $this->assign("qcList",$qcList);
        $this->assign("list",$list["list"]);
        $this->assign("show",$list["show"]);
        $this->assign("kfList",$kfList);
        $this->assign("manager",$users["manager"]);
        $this->assign("groups",$users["groups"]);
        $this->assign("city",$city);
        $this->display();
    }



    /**
     * 编辑/添加质检信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function qcup()
    {
        if ($_POST) {
            $id = I("post.orderid");
            //获取质检信息
            $info = $this->getQcInfo($id);
            $data = array(
                "order_id" => $id,
                "op_uid" => session("uc_userinfo.id"),
                "op_name" => session("uc_userinfo.name"),
                "type" => I("post.type"),
                "status" => 3,
                "remark" => I("post.remark"),
                "video" => I("post.video"),
                "money" => I("post.money"),
                "score" => I("post.score"),
                "conform_regulation" => intval(I('post.conform_regulation')),
                "conform_regulation_remark" => trim(I('post.conform_regulation_remark')),
                "remark2" => I("post.remark2")
            );

            //已质检状态下删除已保存状态
            if ($info["status"] == 1) {
                unset($data["status"]);
            }

            //计算质检单操作时间
            $offset =  time() -  I("post.startTime");
            if (!empty($info["push_time"])) {
                $data["push_time"] = $info["push_time"] + $offset;
            } else {
                $data["push_time"] = $offset;
            }

            //撤回单不更新质检时间
            if ($info["status"] == 99) {
                unset($data["time"]);
                $data["status"] = 2;
            }

            //查询对接/审核客服信息 +评分对象信息
            $ids[] = I("post.fen_id");
            $ids[] = I("post.chk_id");

            $ids = array_filter($ids);
            if (count($ids) > 0) {
                $users = D("Adminuser")->getUserInfoByIds($ids);
                foreach ($users as $key => $value) {
                    $value["kfmanager"] = str_replace(",","",$value["kfmanager"]);
                    if ($value["id"] == I("post.chk_id")) {
                        $data["kf_id"] = $value["id"];
                        $data["kf_name"] = $value["name"];
                        $data["kf_group"] = $value["kfgroup"];
                        $data["kf_manager"] = $value["kfmanager"];
                    }

                    if ($value["id"] == I("post.fen_id")) {
                        $data["docking_id"] = $value["id"];
                        $data["docking_name"] = $value["name"];
                        $data["docking_group"] = $value["kfgroup"];
                        $data["docking_manager"] = $value["kfmanager"];
                    }
                }
            }

            $i = false;
            if (count($info) > 0) {
                $start = strtotime(date("Y-m-d"));
                $end = $start+86400-1;
                if ($info["status"] == 99 || $info["time"] == 0 || $info["time"] >= $start && $info["time"] <= $end) {
                    $i = D("QcInfo")->eidtQc($id,$data);
                }
            }

            if ($i !== false) {
                //添加抽检操作日志
                $log = array(
                    'remark' => '保存订单',
                    'logtype' => 'qcinfo',
                    'action_id' => $id,
                    'action' => __CONTROLLER__."/".__ACTION__,
                    "info" => json_encode($info)
                );
                D('LogAdmin')->addLog($log);

                //获取以抽检项目
                $result = D("QcInfo")->getQcItemByType($id,1);

                foreach ($result as $key => $value) {
                    $ids[] = $value["id"];
                }

                if (count($ids) > 0) {
                    //删除以前抽检的错误项目
                    D("QcInfo")->delQcItemByIds($ids,$id);
                }

                //添加质检项信息
                $item = I("post.item");
                $money =  array_filter(explode(",",I("post.item_money")));
                foreach ($item as $key => $value) {
                    $all[] = array(
                        "qc_item_id" => $value,
                        "order_id" => I("post.orderid"),
                        "money" => $money[$key]
                    );
                }

                // 1）质检窗口中勾选10元的错误，将10*次数计入合计。
                // 2）质检窗口中勾选20元的错误，根据错误次数不同有不同的算法：
                //     a：当次数≤2，扣20元/次。即将20*次数计入合计中；
                //     b：当3≤次数≤5，扣40元/次。即将40*次数计入合计中；
                //     c：当次数≥6，扣60元/次。即将60*次数计入合计中；

                //查询对接客服讨论组截图与备注不一致错误次数
                $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
                $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
                $count = D("QcInfo")->findQcItemCount(16,$data["docking_id"],$monthStart, $monthEnd);

                foreach ($all as $key => $value) {
                    if ($value["qc_item_id"] == 16 && $value["money"] == 20) {
                        $count += 1;
                        if ($count >= 3 && $count <= 5) {
                            $all[$key]["money"] = 40;
                        } elseif ($count >= 6) {
                            $all[$key]["money"] = 60;
                        }
                    }
                }

                if (count($all) > 0) {
                    D("QcInfo")->addQcItem($all);
                }

                if (I("post.video_type") !== "") {
                    //删除以前推荐的录音
                    D("QcInfo")->delQcVideo(I("post.orderid"));
                    //添加录音
                    $data = array(
                        "order_id" => I("post.orderid"),
                        "type" => I("post.video_type"),
                        "sub_yd" => I("post.video_yd"),
                        "sub_hs" => I("post.video_hs"),
                        "sub_td" => I("post.video_td"),
                        "sub_bz" => I("post.video_bz"),
                        "sub_sh" => I("post.video_sh"),
                        "sub_tj" => I("post.video_tj"),
                        "state" => 1
                    );
                    D("QcInfo")->addQcVideo($data);
                }

                //添加打分项
                //删除之前的打分项
                $ids = D("Home/Logic/QcItemsScoreLogic")->getOrderScoreItem($id,1);

                if (count($ids) > 0) {
                    //删除以前抽检的错误项目
                    D("Home/Logic/QcItemsScoreLogic")->delQcScoreItemByIds($id,$ids);
                }

                //保存打分项目
                $item = I("post.qcservice");
                $all = [];
                foreach ($item as $key => $value) {
                    $all[] = array(
                        "qc_item_id" => $value,
                        "order_id" => $id,
                    );
                }

                if (count($all) > 0) {
                    D("Home/Logic/QcItemsScoreLogic")->addQcScoreItem($all);
                }
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 质检发布
     * @return [type] [description]
     */
    public function qcpush()
    {
        if ($_POST) {
            $id = I("post.id");

            //获取质检信息
            $info = $this->getQcInfo($id);

            if ($info["status"] != 3) {
                $this->ajaxReturn(array("status"=>0,"info"=>"请先保存后再发布！"));
            }


            $data = array(
                "status" => 1,
                "time" => time()
            );

            //计算质检单操作时间
            $offset =  time() -  I("post.startTime");
            if (!empty($info["push_time"])) {
                $data["push_time"] = $info["push_time"] + $offset;
            } else {
                $data["push_time"] = $offset;
            }

            $i = D("QcInfo")->eidtQc($id,$data);

            $start = strtotime(date("Y-m-d"));
            $end = $start+86400-1;
            if ($info["status"] == 99 || $info["time"] == 0 || $info["time"] >= $start && $info["time"] <= $end) {
                $i = D("QcInfo")->eidtQc($id,$data);
            }

            if ($i !== false) {
                //添加抽检操作日志
                $log = array(
                    'remark' => '推送订单',
                    'logtype' => 'qcinfo',
                    'action_id' => $id,
                    'action' => __CONTROLLER__."/".__ACTION__,
                    "info" => json_encode($info)
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 抽检信息
     * @return [type] [description]
     */
    public function samplingup()
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "status" => 2,
                "sampling_id"  => session("uc_userinfo.id"),
                "sampling_name"  => session("uc_userinfo.name"),
                "sampling_status"  => I("post.sampling_status"),
                "sampling_remark"  => I("post.remark"),
                "sampling_time"  => time()
            );

            $i = D("QcInfo")->eidtQc($id,$data);

            if ($i !== false) {
                //获取以抽检项目
                $result = D("QcInfo")->getQcItemByType($id,2);

                foreach ($result as $key => $value) {
                    $ids[] = $value["id"];
                }

                if (count($ids) > 0) {
                    //删除以前抽检的错误项目
                    D("QcInfo")->delQcItemByIds($ids,$id);
                }

                $item = I("post.sampling_item");

                if (count($item) > 0) {
                    foreach ($item as $key => $value) {
                        $all[] = array(
                            "qc_item_id" => $value,
                            "order_id" => $id
                        );
                    }
                    if (count($all) > 0) {
                        D("QcInfo")->addQcItem($all);
                    }
                }

                if (I("post.videoid") !== "") {
                    //如果有推荐录音
                    if (I("post.video_status") !== "") {
                        $data = array(
                            "status" => I("post.video_status")
                        );
                        D("QcInfo")->editQcVideo(I("post.videoid"),$data);
                    }
                }

                if (I("post.qcid") !== "") {
                    //添加抽检操作日志
                    $log = array(
                        'remark' => '质检订单抽检',
                        'logtype' => 'samplingqcinfo',
                        'action_id' => $id,
                        'action' => __CONTROLLER__."/".__ACTION__,
                        "info" => json_encode($data)
                    );
                    D('LogAdmin')->addLog($log);

                    $data = array(
                        "type" => 1,
                        "adminuser_id" => I("post.qcid"),
                        "data" => array(
                            "order_id" => $id,
                            "sampling_status" => I("post.sampling_status")
                        )
                    );
                    D("AdminuserMessageQueue")->addAdminuserMessageQueue($data);
                }
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 撤销质检订单
     */
    public function qcReset()
    {
        if ($_POST) {
            $id = I("post.id");
            $data = array(
                "status" => 99,
                "sampling_id"  => session("uc_userinfo.id"),
                "sampling_name"  => session("uc_userinfo.name"),
                "sampling_status"  => I("post.sampling_status"),
                "sampling_remark"  => I("post.remark"),
                "sampling_time"  => time()
            );
            $i = D("QcInfo")->eidtQc($id,$data);
            if ($i !== false) {
                //获取以抽检项目
                $result = D("QcInfo")->getQcItemByType($id,2);

                foreach ($result as $key => $value) {
                    $ids[] = $value["id"];
                }

                if (count($ids) > 0) {
                    //删除以前抽检的错误项目
                    D("QcInfo")->delQcItemByIds($ids);
                }

                $item = I("post.sampling_item");
                if (count($item) > 0) {
                    foreach ($item as $key => $value) {
                        $all[] = array(
                            "qc_item_id" => $value,
                            "order_id" => $id
                        );
                    }
                    if (count($all) > 0) {
                        D("QcInfo")->addQcItem($all);
                    }
                }

                $data = array(
                    "type" => 2,
                    "adminuser_id" => I("post.qcid"),
                    "data" => array(
                        "order_id" => $id
                    )
                );
                D("AdminuserMessageQueue")->addAdminuserMessageQueue($data);

                //添加撤销操作日志
                $log = array(
                    'remark' => '质检订单撤销',
                    'logtype' => 'resetqcinfo',
                    'action_id' => $id,
                    'action' => __CONTROLLER__."/".__ACTION__
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array("status"=>1,"info"=>"撤销质检订单完成"));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));$this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 订单推送及时度
     * @return [type] [description]
     */
    public function orderPush()
    {
        //获取对接客服管
        $group = $this->getKfGroupInfo(104);
        //获取对接客服
        $kf = D('Adminuser')->getDockingKfList();
        $list = $this->getOrderPushList(I("get.id"),I("get.group"),I("get.kf"),I("get.status"),I("get.time"),I("get.begin"),I("get.end"));
        $this->assign("kf",$kf);
        $this->assign("group",$group);
        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }

    /**
     * 400客服质检
     * @return [type] [description]
     */
    public function qc400()
    {
        //400客服列表
        $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
        $kf = D("User")->getAdminNamesById($ids['option_value']);
        //获取质检人员列表
        $qckf = D('Adminuser')->getQualityCheckingList(13);
        $api = A("Home/Api");
        $pageCount = I("get.count") ? I("get.count") : 50;
        $item = $this->getQcItem(3);
        //获取400质检列表
        $list = $this->getQcList400(I("get.begin"),I("get.end"),$pageCount);
        //远程中心人员查看时,隐藏质检项目
        //获取部门下所有的角色ID
        $result = D("RbacRole")->getRoleListByDept(22);
        foreach ($result as $key => $value) {
            $ids[] = $value["role_id"];
        }
        if(!in_array(session("uc_userinfo.uid"),$ids)){
            $zjshow = 1;
        }else{
            $zjshow = 2;
        }
        $this->assign('zjshow', $zjshow);

        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->assign("citys",$citys);
        $this->assign("kf",$kf);
        $this->assign("item",$item);
        $this->assign("qckf",$qckf);
        $this->display();
    }

    /**
     * 400客服质检操作
     * @return [type] [description]
     */
    public function qc400up()
    {
        if ($_POST) {
            $data = json_decode(htmlspecialchars_decode(I("post.data")),true);
            $id = $data[0][11];
            unset($data[0][11]);

            $item = $this->getQcItem(3);
            $errcode = 1;
            $errmsg = '操作成功！';
            $index = 0;
            foreach ($data as $key => $value) {
                $sub = array(
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name")
                );
                foreach ($value as $k => $val) {
                    if (in_array($k,array(0,1,2,10))) {
                        if (empty($val) ) {
                            switch ($k) {
                                case '0':
                                    $errmsg = "日期数据填写有误,请仔细核对！";
                                    break;
                                case '1':
                                    $errmsg = "在线客服数据填写有误,请仔细核对！";
                                    break;
                                case '2':
                                    $errmsg = "城市数据填写有误,请仔细核对！";
                                    break;
                                case '10':
                                    $errmsg = "质检员数据填写有误,请仔细核对！";
                                    break;
                            }
                            $errcode = -1;
                            break;
                        }
                    }
                    if (!in_array($k,array(6,7,8,9))) {
                        switch ($k) {
                            case '0':
                                $sub['time'] = strtotime($val);
                                break;
                            case '1':
                                $sub['kf_id'] =  $val;
                                break;
                            case '2':
                                $sub['city_id'] =  trim($val);
                                break;
                            case '3':
                                $sub['tel'] =  trim($val);
                                break;
                            case '4':
                                $sub['order_id'] =  trim($val);
                                break;
                            case '5':
                                $sub['remark'] =  trim($val);
                                break;
                            case '10':
                                $sub['qc_uid'] =  trim($val);
                                break;
                        }

                    } else {
                        switch ($k) {
                            case '6':
                                $qc_item_id = $item[0]["id"];
                                $money = $val;
                                break;
                            case '7':
                                $qc_item_id = $item[1]["id"];
                                $money = $val;
                                break;
                            case '8':
                                $qc_item_id = $item[2]["id"];
                                $money = $val;
                                break;
                            case '9':
                                $qc_item_id = $item[3]["id"];
                                $money = $val;
                                break;
                        }
                        $itemAll[$index][] = array(
                            "money" => $money,
                            "qc_item_id" => $qc_item_id
                        );
                    }

                }
                $all[] = $sub;
                $index ++;
            }

            if ($errcode == 1) {
                if (!empty($id)) {
                    D("QcInfo")->editInfoBy400($id,$all[0]);
                } else {
                    $id = D("QcInfo")->addAllInfoBy400($all);

                }

                $all = array();
                if ($id != false) {
                    //删除原来的质检项目
                    D("QcInfo")->delQcItemOther($id,1);
                    foreach ($itemAll as $key => $value) {
                        foreach ($value as $k => $val) {
                            $val["order_id"] = $id;
                            $all[] = $val;
                        }
                        $id ++;
                    }

                    //添加质检项目
                    D("QcInfo")->addQcItemOther($all);

                    //添加操作日志
                    $log = array(
                        'remark' => '在线客服质检操作【'.$id.'】',
                        'logtype' => '400qcchange',
                        'action' => __CONTROLLER__."/".__ACTION__,
                        'info' => json_encode($all)
                    );
                    D('LogAdmin')->addLog($log);
                }
            } else {
                $errcode = -1;
                $errMsg = "数据添加失败！";
            }

            $this->ajaxReturn(array("status" => $errcode,"info"=> $errmsg));
        }
    }

    /**
     * 重复ip列表
     */
    public function repeatIp(){
        //获取全部城市
        $temp = D("Quyu")->getQuyuList();
        foreach ($temp as $key => $value) {
            $city[$value["cid"]] = $value;
        }

        //查询活动发单位置
        $result = D("Activity")->getActivityIds();
        $ids = array();
        foreach ($result as $key => $value) {
            if ($value["source_id"] != 0) {
                $source = array_filter(explode(",",$value["source_id"]));
                $ids = array_merge($ids,$source);
            }
        }
        //获取质检列表
        $list = $this->getRepeatIp(I("get.begin"),I("get.end"),I("get.id"),I("get.type"),I("get.cs"),I("get.manager"),I("get.group"),I("get.user"),I("get.time_start"),I("get.time_end"),I("get.status"),I("get.sampling_status"), $ids,1);
        $this->assign("order_source",$this->order_source);

        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->assign("city",$city);
        $this->display('repeatip');
    }

    /**
     * 质检400统计
     * @return [type] [description]
     */
    public function qc53()
    {
        //400客服列表
        $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
        $kf = D("User")->getAdminNamesById($ids['option_value']);

        //获取质检人员列表
        $qckf = D('Adminuser')->getQualityCheckingList(13);
        //获取400质检列表
        $pageCount = I("get.count") ? I("get.count") : 50;
        $list = $this->getQcList53(I("get.begin"),I("get.end"),$pageCount);
        $item = $this->getQcItem(4);
        //远程中心人员查看时,隐藏质检项目
        //获取部门下所有的角色ID
        $result = D("RbacRole")->getRoleListByDept(22);
        foreach ($result as $key => $value) {
            $ids[] = $value["role_id"];
        }
        if(!in_array(session("uc_userinfo.uid"),$ids)){
            $zjshow = 1;
        }else{
            $zjshow = 2;
        }
        $this->assign('zjshow', $zjshow);

        $this->assign("qckf",$qckf);
        $this->assign("item",$item[4]);
        $this->assign("kf",$kf);
        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }

    public function qc53up()
    {
        $data = json_decode(htmlspecialchars_decode(I("post.data")),true);
        if (isset($data[0][12])) {
            $id = $data[0][12];
            unset($data[0][12]);
        }

        $item = $this->getQcItem(4);
        $item = $item[4];
        $errcode = 1;
        $errmsg = '操作成功！';
        $index = 0;
        foreach ($data as $key => $value) {
            $sub = array(
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name")
            );

            foreach ($value as $k => $val) {
                if (in_array($k,array(0,1,3,11))) {
                    if (empty($val) ) {
                        switch ($k) {
                            case '0':
                                $errmsg = "日期填写有误,请仔细核对！";
                                break;
                            case '1':
                                $errmsg = "监察方式填写有误,请仔细核对！";
                                break;
                            case '3':
                                $errmsg = "客服填写有误,请仔细核对！";
                                break;
                            case '11':
                                $errmsg = "质检员填写有误,请仔细核对！";
                                break;
                        }
                        $errcode = -1;
                        break;
                    }
                }

                if (!in_array($k,array(6,7,8,9,10))) {
                    switch ($k) {
                        case '0':
                            $sub['time'] = strtotime($val);
                            break;
                        case '1':
                            $sub['type'] =  $val;
                            break;
                        case '2':
                            $sub['visit_name'] =  $val;
                            break;
                        case '3':
                            $sub['kf_id'] =  $val;
                            break;
                        case '4':
                            $sub['advantage'] =  $val;
                            break;
                        case '5':
                            $sub['problem'] =  $val;
                            break;
                        case '11':
                            $sub['qc_uid'] =  $val;
                            break;
                    }
                } else {
                    switch ($k) {
                        case '6':
                            $qc_item_id = $item[42]["child"][0]["id"];
                            $money = $val;
                            break;
                        case '7':
                            $qc_item_id = $item[43]["child"][0]["id"];
                            $money = $val;
                            break;
                        case '8':
                            $qc_item_id = $item[44]["child"][0]["id"];
                            $money = $val;
                            break;
                        case '9':
                            $qc_item_id = $item[44]["child"][1]["id"];
                            $money = $val;
                            break;
                        case '10':
                            $qc_item_id = $item[45]["child"][0]["id"];
                            $money = $val;
                            break;
                    }
                    $itemAll[$index][] = array(
                        "money" => $money,
                        "qc_item_id" => $qc_item_id
                    );
                }

            }
            $all[] = $sub;
            $index++;
        }

        if ($errcode == 1) {
            if (!empty($id)) {
                D("QcInfo")->editInfoBy53($id,$all[0]);

            } else {
                $id = D("QcInfo")->addAllInfoBy53($all);
            }


            if ($id != false) {
                //删除53质检项目
                D("QcInfo")->delQcItemOther($id,2);

                $all = array();
                foreach ($itemAll as $key => $value) {
                    foreach ($value as $k => $val) {
                        $val["order_id"] = $id;
                        $all[] = $val;
                    }
                    $id ++;
                }

                //添加质检项目
                D("QcInfo")->addQcItemOther($all);

                //添加操作日志
                $log = array(
                    'remark' => '在线客服质检操作【'.$id.'】',
                    'logtype' => '53qcchange',
                    'action' => __CONTROLLER__."/".__ACTION__,
                    'info' => json_encode($all)
                );
                D('LogAdmin')->addLog($log);
            }
        } else {
            $errcode = -1;
            $errMsg = "数据添加失败！";
        }

        $this->ajaxReturn(array("status" => $errcode,"info"=> $errmsg));

    }

    public function del400()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("QcInfo")->delInfoBy400($id);

            if ($i !== false) {
                //删除质检项目
                D("QcInfo")->delQcItemOther($id,1);
                $this->ajaxReturn(array("status" =>1));
            }
            $this->ajaxReturn(array("status" =>0,"info" => "操作失败！"));
        }
    }

    public function del53()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("QcInfo")->delInfoBy53($id);

            if ($i !== false) {
                //删除质检项目
                D("QcInfo")->delQcItemOther($id,2);
                $this->ajaxReturn(array("status" =>1));
            }
            $this->ajaxReturn(array("status" =>0,"info" => "操作失败！"));
        }
    }

    /**
     * 获取客服组信息
     * @return [type] [description]
     */
    private function getKfGroupInfo($uid = 31)
    {
        $edition = array();
        $result = D("Adminuser")->getKfGroupInfo($uid);
        foreach ($result as $key => $value) {
            $list["groups"][$value["id"]] = $value;
            $edition[] = $value["kfgroup"];
            if (!array_key_exists($value["manager_id"],$list["manager"])) {
                $list["manager"][$value["manager_id"]] = array(
                    "id" => $value["manager_id"],
                    "name" => $value["manager"]
                );
            }
        }
        return $list;
    }

    /**
     * 获取质检列表
     * @param  [type] $begin      [实际发布时间]
     * @param  [type] $end        [实际发布时间]
     * @param  [type] $id         [订单ID等]
     * @param  [type] $type       [订单类型]
     * @param  [type] $cs         [订单城市]
     * @param  [type] $manager    [客服时]
     * @param  [type] $group      [客服组]
     * @param  [type] $user       [客服]
     * @param  [type] $time_start [客服对接时间]
     * @param  [type] $time_end   [客服对接时间]
     * @param  [type] $status     [质检状态]
     * @param  [type] $source     [订单来源]
     * @param  [type] $chk_start     [质检开始时间]
     * @param  [type] $chk_end     [质检结束时间]
     * @return [type]             [description]
     */
    private function getQcList($begin,$end,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$source,$chk_start,$chk_end,$src,$open_type_list)
    {
        $monthEnd = strtotime(date("Y-m-d"))+86400-1;
        $monthStart = strtotime("-7 day",strtotime(date("Y-m-d")));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end))-1;
        }

        $time_start = strtotime($time_start);
        $time_end = strtotime($time_end)+86400-1;

        //获取质检的订单信息
        $count = D("Orders")->getQcListCount(session("uc_userinfo.id"),$monthStart,$monthEnd,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$source,$chk_start,$chk_end,$src,$open_type_list);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show    = $p->show();
            $result = D("Orders")->getQcList(session("uc_userinfo.id"),$monthStart,$monthEnd,$p->firstRow,$p->listRows,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$source,$chk_start,$chk_end,$src,$open_type_list);
        }
        return array("list"=>$result,"page"=>$show);
    }

    /**
     * 获取订单信息模版
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getQcOrderInfoTmp($id)
    {
        //查询订单信息
        $order = D('Home/Orders');
        $logic = new QcInfoLogicModel();
        $info = $order->getQcOrderInfo($id);

        if ($info["nf_time"] == "0000-00-00") {
            $info["nf_time"] = "";
        }

        if ($info["lx"] == 1) {
            $info["lx"] = "家装";
        } else if ($info["lx"] == 2) {
            $info["lx"] = "公装";
        }
        $lxs = A("Orders");
        $info["lxs"] = $lxs->lxs[$info["lxs"]];

        //第一次通话结束--第三次通话开始的时间差
        $results = $this->getVoipRecordList(I("post.id"));
        foreach($results as $result){
            if($result['action'] =="开始"){
                $kaishi[] = $result;
            }elseif($result['action'] == "挂断"){
                $jieshu[] = $result;
            }
        }
        $countkaishi = count($kaishi) ;

        //新逻辑:拨号满两次且（当前时间-第一次结束通话）≥30分钟，才能正常申请显号，否则通过紧急入口
        if($countkaishi >= 2){
            $endtime = $jieshu[0]['time_add'];
            $time = time()-strtotime($endtime);
            $timemin =$time/60;
            if($timemin >= 30){
                $jinji = 0;
            }else{
                $jinji = 1;
            }
        }else{
            $jinji = 1;
        }

        //获取审核显号
        $admin = getAdminUser();

        //显号
        $applyInfo = $logic->getLastOrderApplyTelInfo($id);
        $time  = strtotime("+3 day",$applyInfo["apply_time"]);
        if ($applyInfo["status"] == 2 && $time >= time() ) {
            //获取订单真实号码
            $orderLogic = new OrdersLogicModel();
            $telInfo = $orderLogic->getOrderRealTel($id);
            $info["tel"] = $telInfo["tel8"];
        }

        $info["lasttime"] = empty($info["lasttime"])?"":$info["lasttime"];

        //获取订单分配信息
        $company = D("OrderInfo")->getOrderComapny($id);
        $comStr = "";
        foreach ($company as $key => $value) {
            $comStr .= $value["jc"].",";
        }

        //获取订单IP是否重复
        $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

        if ($ipCount[0]["repeat_count"] > 1) {
            $this->assign("ipMark",$ipCount[0]["repeat_count"]-1);
        }

        //获取订单对接时间和微信发送时间
        $result = D("Orders")->getOrderPushInfo($id);
        $info["date_diff"] = timediff($result["diff_date"]);

        //页面显示微信
        if(!empty($info['wx'])){
            if(strlen($info['wx']) > 6){
                $info['wx_show'] =  substr_cut($info['wx'],3,3);
            }else{
                $info['wx_show'] = '****';
            }
        }

        //获取电话是否重复
        $phoneCount = D("Orders")->getTelnumberRepaetCountByIds(array($info["id"]));
        if ($phoneCount[0]["repeat_count"] > 1) {
            $this->assign("phoneMark",$phoneCount[0]["repeat_count"]-1);
        }


        $this->assign("info",$info);
        $this->assign("jinji",$jinji);
        $this->assign("compnays",$comStr);

        $tmp = $this->fetch("qcOrderTmp");
        return $tmp;
    }

    private function getQcItem($type = 1)
    {
        $results = D("QcInfo")->getNewQcItem($type);

        foreach($results as $key=>$value){
            $result[$value['id']] = $value;
        }
        foreach ($results as $key => $value) {
            if ($value["parentid"] != 0) {
                $item[$value["group"]][$value["parentid"]]["name"] = $result[$value["parentid"]]['name'];
                $item[$value["group"]][$value["parentid"]]["child"][] = $value;
            } else {
                if ($value["group"] == 3) {
                    $item[] = $value;
                }

                if ($value["group"] == 4) {
                    $item[$value["parentid"]]["child"][] = $value;
                }
            }
        }

        return $item;
    }

    /**
     * 获取质检信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getQcInfo($id)
    {
        $result = D("QcInfo")->getQcInfoById($id);

        foreach ($result as $key => $value) {
            if ($key == 0) {
                $list["op_name"] = $value["op_name"];
                $list["type"] = $value["type"];
                $list["status"] = $value["status"];
                $list["time"] = $value["time"];
                $list["push_time"] = $value["push_time"];
                $list["remark"] = $value["remark"];
                $list["remark2"] = $value["remark2"];
                $list["video"] = $value["video"];
                $list["op_name"] = $value["op_name"];
                $list["money"] = $value["money"];
                $list["num"] = $value["score"];
                $list["qcid"] = $value["op_uid"];
                $list["conform_regulation"] = $value["conform_regulation"];
                $list["conform_regulation_remark"] = $value["conform_regulation_remark"];
                $list["sampling_id"] = $value["sampling_id"];
                $list["sampling_name"] = $value["sampling_name"];
                $list["sampling_status"] = $value["sampling_status"];
                $list["sampling_remark"] = $value["sampling_remark"];
                $list["sampling_time"] =  empty($value["sampling_time"])?"":$value["sampling_time"];
                $list["record_id"] = $value["record_id"];
            }
            $list["item"][] = $value["qc_item_id"];
            $list["item_money"][$value["qc_item_id"]] = $value["money"];
        }

        //获取打分项
        $result = D("Home/Logic/QcItemsScoreLogic")->getQcInfoById($id);

        foreach ($result as $key => $value) {
            $list["item_score"][] = $value["qc_item_id"];
            $list["score"][$value["qc_item_id"]] = $value["score"];
        }

        return $list;
    }


    private function getQcInfoTmp($id,$isEditor = true)
    {
        $model = D("QcInfo");
        //获取质检评分项
        $item = $this->getQcItem();

        //获取质检打分项
        $scoreItem = D("Home/Logic/QcItemsScoreLogic")->getQcItem();

        //获取最新的温馨提示
        $kindInfo = D("QcKindlyRemind")->getNewKindlyRemindInfo();

        //获取质检信息
        $qcInfo = $this->getQcInfo($id);

        //质检单子以第一个打开的人为准
        if (empty($qcInfo["qcid"])) {
            $data = array(
                "order_id" => $id,
                "op_uid" => session("uc_userinfo.id"),
                "op_name" => session("uc_userinfo.name")
            );
            $model->addQc($data);
        }

        //打开订单的时间
        $qcInfo["startTime"] = time();

        //获取推荐录音信息
        $videoInfo = $model->getQcVideoInfo($id);
        $isOpen = false;
        //如果质检时间在一天之外的不能修改
        if (empty($qcInfo["time"]) ||  $qcInfo["time"] >= strtotime(date("Y-m-d")) && $qcInfo["time"] <= mktime(23,59,59,date("m"),date("d"),date("Y")) || $qcInfo["status"] == 99) {
            $isOpen = true;
        }

        //已抽检的订单和默认不显示按钮
        if (!$isOpen || $qcInfo['status'] == 2 || (!empty($qcInfo["qcid"]) && $qcInfo["qcid"] != session("uc_userinfo.id")) || !$isEditor) {
            $isOpen = false;
        }

        $url_parse  =  parse_url($_SERVER["HTTP_REFERER"]);

        if (!empty($url_parse["path"]) && $url_parse["path"] == "/qcstat/qcconclusionstat/") {
            $isOpen = false;
        }
        //获取部门下所有的角色ID
        $result = D("RbacRole")->getRoleListByDept(22);
        foreach ($result as $key => $value) {
            $ids[] = $value["role_id"];
        }

        //获取部门下所有人员
        if (count($ids) > 0) {
            //获取客服人员
            $users = D("Adminuser")->getUserListByUid($ids);
            $this->assign("kfList",$users);
        }
        //远程中心人员查看时,隐藏质检项目
        //获取部门下所有的角色ID
        $results = D("RbacRole")->getRoleListByDept(22);
        foreach ($results as $key => $value) {
            $ids[] = $value["role_id"];
        }
        if(!in_array(session("uc_userinfo.uid"),$ids)){
            $zjshow = 1;
        }else{
            $zjshow = 2;
        }
        $this->assign('zjshow', $zjshow);

        $this->assign("scoreItem",$scoreItem);
        $this->assign("isOpen",$isOpen);
        $this->assign("kindInfo",$kindInfo);
        $this->assign("qcInfo",$qcInfo);
        $this->assign("videoInfo",$videoInfo);
        $this->assign("state",$this->state);
        $this->assign("video_state",$this->video_state);
        $this->assign("yd",$this->yd);
        $this->assign("hs",$this->hs);
        $this->assign("td",$this->td);
        $this->assign("bz",$this->bz);
        $this->assign("sh",$this->sh);
        $this->assign("qcItem",$item);
        $tmp = $this->fetch("qcInfoTmp");

        return $tmp;
    }

    /**
     * 获取质检抽检模版
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getSamplingQcTmp($id,$isSampling = false)
    {
        $model = D("QcInfo");
        //获取抽检平分项目
        $sampling_item = $this->getQcItem(2);

        //获取推荐录音信息
        $videoInfo = $model->getQcVideoInfo($id);
        //获取质检信息
        $qcInfo = $this->getQcInfo($id);

        // if ($qcInfo["status"] == 2) {
        //    $isSampling = false;
        // }

        if ($isSampling) {
            $this->assign("isSampling",$isSampling);
        }

        $this->assign("qcInfo",$qcInfo);
        $this->assign("videoInfo",$videoInfo);
        $this->assign("sampling_item",$sampling_item);
        $tmp = $this->fetch("qcSamplingQcTmp");
        return $tmp;
    }

    /**
     * 订单推送及时度
     * @param  [type] $id        [订单编号]
     * @param  [type] $group     [对接客服组]
     * @param  [type] $kf        [对接客服]
     * @param  [type] $status    [推送状态]
     * @param  [type] $time      [耗时]
     * @param  [type] $begin     [分单开始时间]
     * @param  [type] $end       [分单结束时间]
     * @return mix
     */
    private function getOrderPushList($id,$group,$kf,$status,$time,$begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $count = D("QcInfo")->getOrderPushListCount($id, $group, $kf, $status, $time, $monthStart, $monthEnd);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,50);
            // $p->setConfig('prev', "上一页");
            // $p->setConfig('next', '下一页');
            // $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %HEADER%');
            $show    = $p->show();
            $result = D("QcInfo")->getOrderPushList($id, $group, $kf, $status, $time, $monthStart, $monthEnd,$p->firstRow,$p->listRows);

            foreach ($result as $key => $value) {
                $result[$key]["diff_date"] = timediff2($value["diff_date"]);
            }
        }

        return array("list"=>$result,"page"=>$show);
    }

    /**
     * 获取400客服质检列表
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcList400($begin,$end,$pageCount)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $count = D("QcInfo")->getQcList400Count($monthStart, $monthEnd);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $show = $p->show();
            $result = D("QcInfo")->getQcList400($monthStart, $monthEnd,$p->firstRow,$p->listRows);

            foreach ($result as $key => $value) {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]] = $value;
                }
                $list[$value["id"]]["child"][] = array(
                    "money" =>$value["money"]
                );
            }
        }

        return array("list"=>$list,"page"=>$show);
    }


    /**
     * 获取53客服质检列表
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getQcList53($begin,$end,$pageCount)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $count = D("QcInfo")->getQcList53Count($monthStart, $monthEnd);

        $list = [];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $show = $p->show();
            $result = D("QcInfo")->getQcList53($monthStart, $monthEnd,$p->firstRow,$p->listRows);

            foreach ($result as $key => $value) {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]] = $value;
                }
                $list[$value["id"]]["child"][] = array(
                    "money" =>$value["money"]
                );
            }
        }

        return array("list"=>$list,"page"=>$show);
    }

    private function getRepeatIp($begin,$end,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$sampling_status,$open_type_list,$having){
        $monthEnd = strtotime(date("Y-m-d")) + 86400 - 1;
        $monthStart = strtotime("-30 day", strtotime(date("Y-m-d")));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end)) - 1;
        }

        $count = D("Orders")->getRepeatIpListCount(session("uc_userinfo.id"), $monthStart, $monthEnd, $id, $type, $cs, $manager, $group, $user, $time_start, $time_end, $status,$sampling_status, $open_type_list, $having);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $result = D("Orders")->getRepeatIpList(session("uc_userinfo.id"), $monthStart, $monthEnd, $p->firstRow, $p->listRows, $id, $type, $cs, $manager, $group, $user, $time_start, $time_end, $status,$sampling_status,$open_type_list, $having);
        }
        return array("list" => $result, "page" => $show);
    }

    /**
     * 渠道查询(质检)
     */
    public function getQcSrc(){
        //开始时间和结束时间
        $start_time = I('get.start_time');
        if(!empty($start_time)){
            $date = strtotime($start_time);
            $start_time = mktime(0,0,0,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $start_time = mktime(0,0,0,date("m"),date("1"),date("Y"));
        }
        $end_time = I('get.end_time');
        if(!empty($end_time)){
            $date = strtotime($end_time);
            $end_time   = mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $end_time   = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }
        $info['start_time'] = date('Y-m-d',$start_time);
        $info['end_time'] = date('Y-m-d',$end_time);
        //限制天数
        $timeDiff = $end_time - $start_time;
        if($timeDiff > 1 && $timeDiff <= 86400){
            $timeNum = '1';
        }else{
            $timeNum = floor($timeDiff/86400);
        }

        if($timeNum > 31){
            $this->error('时间不能大于一个月');
        }

        //每页20条数据
        //获取列表
        if(I('get.alias')){
            $alias = I('get.alias');
        }
        if(I('get.type')){
            $type = intval(I('get.type'));
        }

        if (I("get.alias") !== "") {
            $result = D("OrderSource")->getBySrc(I("get.alias"));
            $src[] = array(
                "id" => $result["alias"],
                "text" => $result["alias"]
            );
        }

        $pageCount = 20;
        $result = $this->getQcAliasList($start_time,$end_time,$pageCount,null,$alias,$type);
        $this->assign('srcList',$src);
        $this->assign('info',$info);
        $this->assign('listPageIndex',$result['pageIndex']);
        $this->assign('page',$result["page"]);
        $this->assign('list',$result["list"]);
        $this->assign('sum',$result["sum"]);
        $this->display('qcsrc');
    }

    /**
     * 获取有订单的渠道
     * @param $begin
     * @param $end
     * @param $depts 部门集合
     * @param $type 排序方式
     * @param $pageCount
     * @return array
     */
    public function getQcAliasList($begin,$end,$pageCount,$depts,$alias,$type)
    {
        $count = D("Orders")->getQcOrderAliasCount($begin,$end,$depts,$alias);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $show = $p->show();
            $result = D("Orders")->getQcOrderAliasList($begin, $end,$depts,$alias,$type,$p->firstRow,$p->listRows);
            $sum = D("Orders")->getQcOrderAliasAll($begin, $end,$depts,$alias);
        }
        return array("sum"=>$sum,"list"=>$result,"page"=>$show,"pageIndex"=>$p->firstRow);
    }

    /**
     * 错误项
     */
    public function errorItem(){
        if ($_POST) {
            $id = I('post.id');
            $status = I('post.status');
            if ($status == 1) {
                $data['status'] = 2;
            } else {
                $data['status'] = 1;
            }
            $result = D('QcInfo')->editItem($data,$id);
            if ($result) {
                $this->ajaxReturn(array('status' => 1));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '修改失败，请重试！'));
            }
        }

        if(I('get.type') == 1){
            $search['name'] = trim(I('get.groupname'));
            //所属组
            $result = D('QcInfo')->errorGroups($search);
            foreach($result as $val){
                $parent[$val['id']] = $val;
            }
            $this->assign('group',$parent);

            $this->display('errorgroup');
        }else{
            $search['name'] = trim( I('get.itemname'));
            $search['group'] = trim( I('get.itemgroup'));
            $search['parentid'] = trim( I('get.itemparentid'));
            //获取错误项
            $count = D('QcInfo')->errorItemsCount($search);
            if($count>0){
                import('Library.Org.Util.Page');
                $Page  = new \Page($count,20);
                $list['page'] = $Page->show();
                $list['list'] = D('QcInfo')->errorItems($search,$Page->firstRow,$Page->listRows);
            }
            //所属组
            $result = D('QcInfo')->errorGroups();
            foreach($result as $val){
                $parent[$val['id']] = $val;
            }
            $this->assign('group',$parent);

            $this->assign('row',$Page->firstRow);
            $this->assign('list',$list);
            $this->display();
        }



    }


    /**
     * 错误项添加/编辑
     */
    public function adderroritem(){
        if ($_POST) {
            $data['name'] = trim(I('post.name'));
            $data['money'] = trim(I('post.money'));
            $data['money2'] = trim(I('post.money2'));
            $data['money3'] = trim(I('post.money3'));
            $data['group'] = trim(I('post.group'));
            $data['parentid'] = trim(I('post.parent'));
            $data['welfare'] = trim(I('post.welfare'));
            $data['type'] = 1;
            $data['addtime'] = time();
            if(I("post.id")){
                //编辑
                $row =  D('QcInfo')->editItem($data,I("post.id"));
            }else{
                //新增
                $row =  D('QcInfo')->addItem($data);
            }
            if($row>0){
                $this->ajaxReturn(array("status" =>1,"info" =>"保存成功"));
            }else{
                $this->ajaxReturn(array("status" =>0,"info" => "添加失败，请重新添加！"));
            }
        }

        if(I('get.id')){
            $row = D('QcInfo')->getErrorItemById(I('get.id'));
            $this->assign('row',$row);
        }

        //所属组
        $parent = D('QcInfo')->errorGroups();
        $this->assign('parent',$parent);
        $this->display();
    }


    /**
     * 错误组添加/编辑
     */
    public function adderrorgroup(){
        if ($_POST) {
            $data['name'] = trim(I('post.name'));
            $data['type'] = 1; // 客服错误项
            $data['parentid'] = 0;
            $data['addtime'] = time();
            if(I("post.id")){
                //编辑
                $row =  D('QcInfo')->editItem($data,I("post.id"));
            }else{
                //新增
                $row =  D('QcInfo')->addItem($data);
            }

            if($row>0){
                $this->ajaxReturn(array("status" =>1,"info" => "保存成功！"));
            }else{
                $this->ajaxReturn(array("status" =>0,"info" => "添加失败，请重新添加！"));
            }
        }

        if(I('get.id')){
            $row = D('QcInfo')->getErrorItemById(I('get.id'));
            $this->assign('row',$row);
        }
        $this->display();
    }


    /**
     * [getRepeatOrderListByPhone 手机号重复订单]
     * @return [type] [description]
     */
    public function getRepeatOrderListByPhone()
    {
        $id = I('post.id');
        //检查
        if (empty($id)) {
            $this->ajaxReturn(array('data' => '', 'info' => '缺少参数！', 'status' => 0));
        }
        $order = D('Orders')->getOrdersById($id);
        if (!empty($order)) {
            $result = D('Orders')->getOrdersByTelEncrypt($order['tel_encrypt']);
            if (!empty($result)) {
                $this->assign('list', $result);
                $html = $this->fetch("order_repeat_modal");
                $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '重复订单列表为空！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' => 0));
    }


    public function getVoipRecordList($orderid)
    {
        $db = D('LogTelcenterOrdercall');
        $result = $db->getOrderCallListByOrderId($orderid);
        $result = $db->callistHuman($result, 1); //数据再加工
        foreach ($result as $key => &$value) {
            $value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
            //呼叫动作
            switch ($value['action']) {
                case 'CallAuth':
                    $value['action'] = "开始";
                    break;
                case 'CallEstablish':
                    $value['action'] = "接通";
                    break;
                case 'Hangup':
                    $value['action'] = "挂断";
                    break;
                case 'CallEstablish_Sub':
                    $value['action'] = "主叫接通";
                    break;
                case 'Hangup_Sub':
                    $value['action'] = "主叫挂断";
                    break;
                default:
                    # code...
                    break;
            }
            //拨打方式
            switch ($value['calltype']) {
                case 'callBack':
                    $value['calltype'] = "回拨拨打";
                    break;
                default:
                    break;
            }
            //挂断方式
            if ($value['TelCenter_Channel'] == 'cuct') {
                //对联通云总机的挂机代码处理
                //state：状态：0-呼叫挂机（默认值）；1-通话失败。
                switch ($value['byetype']) {
                    case '0':
                        $value['byetypewz'] = '呼叫挂机';
                        break;
                    case '1':
                        $value['byetypewz'] = '通话失败';
                        break;
                    default:
                        $value['byetypewz'] = '-';
                        break;
                }
            }else{
                // 默认是 云通讯的挂机代码
                $value['byetypewz'] = $db->getByeTypeInfo($value['byetype']);
            }
        }

        return $result;
    }

    public function applytel()
    {
        if ($_POST) {
            $id = I("post.id");
            $reason = I("post.reason");
            $logic = new QcInfoLogicModel();
            $info = $logic->getLastOrderApplyTelInfo($id);
            $time = strtotime("+3 day",$info["apply_time"]);

            //如果申请状态在申请中
            if ($info["status"] == 2 && $time > time()) {
                $this->ajaxReturn(array('info' => '该订单已通过显号审核，不能重复申请', 'status' => 0));
            }

            //如果订单超过3天未审核或者显号超过3天，自动关闭订单
            if ($info["status"] == 1 &&  $time < time()) {
                $data = [
                    "status" => 4
                ];
                $logic->editOrderApplyTelInfo($id,$data);
            } elseif ($info["status"] == 1  &&  $time > time()) {
                $this->ajaxReturn(array('info' => '该订单正在申请显号，不能重复申请', 'status' => 0));
            }
            //添加申请数据
            $i = $logic->addOrderApplyTelInfo($id,$reason);

            if ($i !== false) {
                //默认申请通过
                $data = [
                    "status" => 2,
                    "passer_id" => session("uc_userinfo.id"),
                    "pass_time" => time()
                ];
                $logic->editApplyTelInfo($i,$data);
                $this->ajaxReturn(array('info' => '申请成功', 'status' => 1));
            }
            $this->ajaxReturn(array('info' => '申请失败', 'status' => 0));
        }
    }

    public function getapplylist()
    {
        if ($_POST) {
            $id = I("post.id");
            $logic = new QcInfoLogicModel();
            $list = $logic->getApplyList($id);
            $this->ajaxReturn(array('data' => $list, 'status' => 1));
        }
        $this->ajaxReturn(array('info' => '申请失败', 'status' => 0));
    }

    public function editapplytel()
    {
        if ($_POST) {
            $id = I("post.id");
            $val = I("post.val");
            $logic = new QcInfoLogicModel();
            $data = [
                "status" => $val == 1?2:3,
                "passer_id" => session("uc_userinfo.id"),
                "pass_time" => time()
            ];
            $list = $logic->editApplyTelInfo($id,$data);
            $this->ajaxReturn(array('data' => $list, 'status' => 1));
        }
        $this->ajaxReturn(array('info' => '申请失败', 'status' => 0));
    }
}