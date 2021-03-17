<?php

namespace Home\Controller;

use Common\Enums\StatusCodeEnum;
use Home\Model\Logic\OrderReviewLogicModel;
use Think\Db;
use Think\Log;

use Home\Common\Controller\HomeBaseController;

use Home\Model\Db\OrderRobPoolModel;
use Home\Model\OrderCsosNewModel;
use Home\Model\OrderDockingModel;
use Home\Model\OrderInfoModel;
use Home\Model\OrderPoolModel;
use Home\Model\OrdersModel;
use Home\Model\AreaModel;
use Home\Model\UserModel;

use Home\Model\Logic\CompanyLogicModel;
use Home\Model\Logic\CompanyRoundOrderAmountLogicModel;
use Home\Model\Logic\JiajuOrdersLogicModel;
use Home\Model\Logic\LogOrderRouteLogicModel;
use Home\Model\Logic\NewReviewLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\OrderCompanyReviewLogicModel;
use Home\Model\Logic\RoundOrderApplyLogicModel;
use Home\Model\Logic\OrderSourceInLogicModel;
use Home\Model\Logic\UserLogicModel;
use Home\Model\Logic\WorkSiteLiveLogicModel;
use Home\Model\Logic\OrdersGiveCompanyLogicModel;
use Home\Model\Logic\RobOrdersLogicModel;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\LogSmsSendLogicModel;
use Home\Model\Logic\OrderQueueLogicModel;

use Home\Model\Service\PnpServiceModel;
use Home\Model\Service\SmsServiceModel;
use Home\Model\Service\MsgServiceModel;
use Home\Model\Service\QzorderServiceModel;
use Home\Model\Service\CronapiServiceModel;
use Home\Model\Service\ElasticsearchServiceModel;

use Common\Enums\MsgTemplateCodeEnum;

/**
*
*/
class OrdersController extends HomeBaseController
{
	public $source_in = array(
        "0" => "-请选择-",
        "1" => "53客服",
        //"1" => "在线客服",
        "2" => "400电话",
        "3" => "QQ咨询",
        //"4" => "业主发布",
        //"5" => "赠送单生成",
        //"10" => "微信咨询",
        "11" => "推广部",
        "12" => "微信已审核",
        "13" => "微信未审核",
        "14" => "回访客服",
        "16" => "公众号已审",
        "17" => "公众号未审"
        //"100" => "非业主发布"
    );

	public $src_from = array(
	    "kffd" => "客服发单",
        "qz-dh-a2" => "APP电话咨询",
        "gzh-zxsw" => "公众号已审",
        "gzh-wsw" => "公众号未审"
    );

    public $lf_time = array(
        "随时,下班,今明" => "随时,下班,今明",
        "1周内,本周末" => "1周内,本周末",
        "2周内,下周末" => "2周内,下周末",
        "3周内" => "3周内",
        "4周内" => "4周内",
        "1个月以上" => "1个月以上",
        "其他" => "其他"
    );

	public $start_time = array(
        "1个月内开工" => "1个月内开工",
        "2个月内开工" => "2个月内开工",
        "3个月内开工" => "3个月内开工",
        "4个月内开工" => "4个月内开工",
        "5个月内开工" => "5个月内开工",
        "6个月内开工" => "6个月内开工",
        "6个月以上开工" => "6个月以上开工",
        "方案满意开工" => "方案满意开工",
        "满意拿房后开工" => "满意拿房后开工",
        "面议" => "面议"
    );

	public $keys = array(
        "1" => "有钥匙",
        "0" => "无钥匙",
        "3" => "其他"
    );

	public $lxs = array(
        "1" => "新房装修",
        "2" => "整体翻新",
        "3" => "局部改造"
    );

	public $status = array(
        array("name"=>"状态未变","id"=>"-99","child"=>array(
                "无人接听",
                "未接通",
                "通话中",
                "空号",
                "装修公司",
                "拒绝服务",
                "开场挂",
                "核实一半挂机",
                "拒接",
                "只是看看",
                "寻求其他服务",
                "重复订单",
                "已开站无真会员",
                "地级市50公里以外面积200平以下",
                "关机",
                "停机",
                "假订单",
                "测试单",
                "否认发单",
                "已回访",
                "其他"
            )
        ),
        array("name"=>"次新单","id"=>"1","child"=>array(
                "无人接听",
                "未接通",
                "通话中",
                "空号",
                "装修公司",
                "拒绝服务",
                "开场挂",
                "核实一半挂机",
                "拒接",
                "只是看看",
                "寻求其他服务",
                "重复订单",
                "已开站无真会员",
                "地级市50公里以外面积200平以下",
                "关机",
                "停机",
                "假订单",
                "测试单",
                "否认发单",
                "过段时间联系",
                "等会联系",
                "已回访",
                "其他"
            )
        ),
        array("name"=>"待定单","id"=>"2","child"=>array(
                "无人接听",
                "未接通",
                "通话中",
                "空号",
                "装修公司",
                "拒绝服务",
                "开场挂",
                "核实一半挂机",
                "拒接",
                "只是看看",
                "寻求其他服务",
                "重复订单",
                "已开站无真会员",
                "地级市50公里以外面积200平以下",
                "关机",
                "停机",
                "假订单",
                "测试单",
                "否认发单",
                "过段时间联系",
                "已回访",
                "其他"
            )
        ),
        array("name"=>"分单","id"=>"4","child"=>array(
                "A级分配",
                "S级分配"
        )),
        array("name"=>"赠单","id"=>"6","child"=>array(
                "距离远",
                "预算低",
                "面积小",
                "交房时间长",
                "开工时间长",
                "城市未开",
                "需要垫资",
                "不能量房",
                "改动项目少",
                "与装修相关",
                "只要设计",
                "意向不强"
            )
        ),
        array("name"=>"无效","id"=>"8","child"=>array(
                "空号",
                "无人接听",
                "未接通",
                "装修公司",
                "已转家具单",
                "拒绝服务",
                "开场挂",
                "核实一半挂机",
                "拒接",
                "只是看看",
                "寻求其他服务",
                "重复订单",
                "已开站无真会员",
                "地级市50公里以外面积200平以下",
                "关机",
                "停机",
                "假订单",
                "测试单",
                "否认发单",
                "同行网站",
                "精装房",
                "已回访",
                "其他"
            )
        ),
        array("name"=>"暂时无效","id"=>"98","child"=>array(
                "地级市50公里以外面积200平以下",
                "已开站无真会员"
        )),
        array("name"=>"压单","id"=>"97","child"=>array(
            "没交房","人在外地","过段时间联系","不确定量房时间","房子出租中","其他"
        ))
    );

	public $docking_status = array(
        array("name"=>"分没人跟","id"=>"0","child"=>array()),
        array("name"=>"赠没人跟","id"=>"1","child"=>array()),
        array("name"=>"撤回","id"=>"2","child"=>array(
                    "更改订单编辑内容",
                    "需要再次电话确认",
                    "订单分赠性质更改",
                    "上下备注不一致",
                    "转到下属城市"
        )),
        array("name"=>"待分配分单","id" => "3","child"=>array()),
        array("name"=>"待分配赠单","id" => "4","child"=>array()),
        array("name"=>"分单","id"=>"5","child"=>array(
                "A级分配",
                "S级分配"
        ))
    );

	public $new_docking_status = [
        ['name' => '推送至抢分池', 'id' => '6', "child" => []],
        ['name' => '推送至抢赠池', 'id' => '8', "child" => []],
        ['name' => '直接赠送', 'id' => '7', "child" => []],
    ];

	// feature-972 更换位置
    public $new_docking_status2 = [
        ['name' => '直接赠送', 'id' => '7', "child" => []],
        ['name' => '推送至抢分池', 'id' => '6', "child" => []],
        ['name' => '推送至抢赠池', 'id' => '8', "child" => []],
    ];

    // public $direct_create_jiaju_city = [
    //     440600, //佛山
    //     441900, //东莞
    //     520100, //贵阳
    //     440700, //江门
    //     520500, //毕节
    //     420100, //武汉
    //     330500, //湖州
    //     370200, //青岛
    // ];

	public $shi = array(
        0=>"-",
        1=>1,
        2=>2,
        3=>3,
        4=>4,
        5=>5,
        6=>6,
        7=>7,
        8=>8,
        9=>9,
        10=>10
    );

	public $other = array(
            "装修公司做过同样小区" ,
            "要求装修公司规模大，有品牌" ,
            "施工过程中监控" ,
            "需要提供齐装网对装修公司的监管内容" ,
            "要看样板房或案例图片" ,
            "有公装装修经验" ,
            "只接本地电话号码，或提供装修公司号码业主自己联系" ,
            "只需要1-2家装修公司" ,
            "装修公司在小区有样板房" ,
            "设计师要有经验" ,
            "报价与实际内容相符" ,
            "年前装修好" ,
            "联系前先发短信"
    );

	public $timediff = array(
        1=>"≤15分钟",
        2=>"＞15分钟"
    );

	public $details_count = [
        1 => ['name' => '当前新订单总数', 'logo' => 'ordercountbrief_new'],
        2 => ['name' => '当前已抢未拨打新单', 'logo' => 'ordercountbrief_uncalled'],
        3 => ['name' => '当前发单量总数', 'logo' => 'ordercountbrief_publish']
    ];

	public $applystatus = array(
        1=>"待审核",
        2=>"通过",
        3=>"不通过"
    );

	public function __construct()
    {
        parent::__construct();
        // $token = session('uc_userinfo.sales_token');
        // if (empty($token)) {
        //     $this->error('请重新登陆后再试! ');
        // }
    }

    public function index()
    {
        $admin = getAdminUser();

        //获取查询条件
        $param = I('get.', '', 'trim');

        //初始化定义查询条件，目的是规范输入
        $id = 0;
        $cs = 0;
        $xiaoqu = '';
        $ip = '';
        $tel_encrypt = '';
        $isactivity =  $param["isactivity"];
        $source_in = $param["source"];
        $lxs = $param["lxs"];

        //修改后的订单发布时间筛选
        $time_start = empty($param['time_start']) ? '' : strtotime($param['time_start']);
        $time_end = empty($param['time_end']) ? '' : strtotime($param['time_end']) + 86400;

        /*S-真实发布订单时间限制*/
        //订单保鲜度，3分钟内发布的订单不能编辑查看，因为用户发布订单是按照步骤来的，订单信息会一步步补充完整
        $currentTime = time();
        if (empty($param['time_real_start']) && empty($param['time_real_end'])) {
            $time_real_start = strtotime("-90 day");
            $time_real_end = $currentTime - 180;
        } elseif (!empty($param['time_real_start']) && empty($param['time_real_end'])) {
            $time_real_start = strtotime($param['time_real_start']);
            $time_real_end = $currentTime - 180;
        } elseif (empty($param['time_real_start']) && !empty($param['time_real_end'])) {
            $time_real_end = strtotime($param['time_real_end']);
            if ($time_real_end > $currentTime - 180) {
                $time_real_end = $currentTime - 180;
            }
            //开始时间为结束时间减去90天的时间 90 * 24 * 3600;
            $time_real_start = $time_real_end - 7776000;
        } else {
            $time_real_start = strtotime($param['time_real_start']);
            $time_real_end = strtotime($param['time_real_end']);
            if ($time_real_end > $currentTime - 180) {
                $time_real_end = $currentTime - 180;
            }
        }
        /*S-真实发布订单时间限制*/
        //拿房时间限制
        $nf_time_start = empty($param['nf_time_start']) ? '' : $param['nf_time_start'];
        $nf_time_end = empty($param['nf_time_end']) ? '' : $param['nf_time_end'];
        $on = false;
        $on_sub = false;
        $type_fw = false;
        $remarks = empty($param['remarks']) ? '' : $param['remarks'];
        $openeye_st = false;
        $op_uid = false;
        $fen_customer = false;

        //订单状态
        $main['status'] = D('Orders')->getOrderStatusDescription(true,2);
        //获取订单备注
        $main['remarks'] = $this->status2Arr($this->status);

        //获取订单操作人：次新单，扫单，待定单，撤回单时候获取帮打人的订单
        if (in_array($param['status'], array(12,13,14,22,24,25))) {
            $operaters = D('Adminuser')->getKfManagerListByIdAndUid($admin['id'], $admin['uid'], $admin['cs_help_user']);
        } else {
            $operaters = D('Adminuser')->getKfManagerListByIdAndUid($admin['id'], $admin['uid']);
        }

        $main['operaters'] = $operaters['list'];

        //如果是客服，客服组长，客服主管，则限制搜索人为管辖的人的id
        if (in_array($admin['uid'], array(2,31,30))) {
            if (empty($param['op_uid'])) {
                $op_uid = $operaters['ids'];
            } else {
                $op_uid = in_array($param['op_uid'], $operaters['ids']) ? intval($param['op_uid']) : $operaters['ids'];
            }
        } elseif (!empty($param['op_uid'])) {
            $op_uid = intval($param['op_uid']);
        }

        //订单号，手机号，IP，小区名字查询处理
        if (!empty($param['condition'])) {
            $condition = trim($param['condition']);
            if (is_numeric($condition)) {
                if (strlen($condition) > 15) {
                    //订单号的长度都大于15位
                    $id = addslashes($condition);
                } else {
                    //手机号码
                    $tel_encrypt = D('Orders')->getOrdersTelEncrypt($condition);
                }
            } elseif (preg_match('/[J|j]\d+$/', $condition)){
                //家具订单
                $logLogic = new LogOrderRouteLogicModel();
                $route = $logLogic->getOrderLog($condition);
                if($route){
                    $id = addslashes($route['order_id']);
                }
            }else {
                //小区名字
                $xiaoqu = addslashes($condition);
            }
        }

        //处理城市选择查询
        if (!empty($param['city']) && $param['city'] != 'null') {
            $cs = intval($param['city']) == 1 ? '000001' : intval($param['city']);
        }

        $area = !empty($param['area'])?$param['area']:'';
        //处理订单状态筛选，使用isset引用上面定义的数组条件
        if (!empty($param['status'])) {
            $status = $param['status'];
            if (isset($main['status'][$status]['param']['on'])) {
                $on = $main['status'][$status]['param']['on'];
            }
            if (isset($main['status'][$status]['param']['on_sub'])) {
                $on_sub = $main['status'][$status]['param']['on_sub'];
            }
            if (isset($main['status'][$status]['param']['type_fw'])) {
                $type_fw = $main['status'][$status]['param']['type_fw'];
            }
            //如果选择未分配订单 , 则添加条件
            if($param['status'] == 27){
                $fen_customer = 0;
            }
        }

        //处理订单显号
        if (!empty($param['displaynumber'])) {
            switch ($param['displaynumber']) {
                case '1':
                    $openeye_st = 'null';
                    break;
                case '2':
                    $openeye_st = 1;
                    break;
                case '3':
                    $openeye_st = 2;
                    break;
                case '4':
                    $openeye_st = 3;
                    break;
                default:
                    break;
            }
        }

        //处理排序规则，没有排序规则使用默认排序，否则按照不同订单状态进行不同的排序
        $order = 'IF(o.remarks="已回访",0,1),priority DESC,on_sub DESC,`on` ASC,visitime ASC,case when on_sub = 9 then o.time end ASC ,case when on_sub <>9 then o.time end DESC,o.time DESC, o.id DESC';

        if (!empty($main['status'][$status]['order'])) {
            $order = $main['status'][$status]['order'];
        }

        //是否安装完整度排序
        if ('1' == $param['wzd_orrder']) {
            $order = 'o.wzd ASC, ' . $order;
        } else if ('2' == $param['wzd_orrder']) {
            $order = 'o.wzd DESC, ' . $order;
        }

        //获取订单列表和分页信息
        if (I('get.details')) {
            //如果是页面点击 总数跳转则从新获取页面数据
            $main['info'] = $this->getCountOrdersList($this->details_count[I('get.details')]);
        } else {
            $main['info'] = $this->getOrdersList($id, $cs, $xiaoqu, $ip, $tel_encrypt, $time_start, $time_end, $time_real_start, $time_real_end, $nf_time_start, $nf_time_end, $on, $on_sub, $type_fw, $remarks, $openeye_st, $op_uid, $order, 10, $isactivity,$source_in,$area,$fen_customer,$lxs);
        }

        $list = $main['info']['list'];
        //获取订单ID数组
        $ids = array_column($list, 'id');

        //获取每个订单的电话号码的归属地
        $result = D('Home/Db/Phonelocation')->getOrderTelLocationByOrderIds($ids);
        foreach ($result as $key => $value) {
            $phoneLocation[$value['id']] = $value['cname'];
        }

        //获取每个订单的电话号码的重复次数
        $result = D('Orders')->getTelnumberRepaetCountByIds($ids);
        foreach ($result as $key => $value) {
            $phoneRepeats[$value['id']] = $value['repeat_count'];
        }

        //获取每个订单的通话记录
        $result = \Home\Model\LogTelcenterOrdercallModel::getOrderCallCountAndLastTimeByOrderIds($ids);

        $callRepeats = array_column($result,'call_repeats','orderid');
        $callLasttime = array_column($result,'time_add','orderid');

        //获取每个订单最近的订单备注添加时间
        $result = D('LogOrderRemarkTime')->getLastLogOrderRemarkTimeByOrderIds($ids);
        foreach ($result as $key => $value) {
            $remarkTime[$value['order_id']] = $value['remark_time'];
        }

        //处理每条记录
        $dateArray=array('年'=>31536000,'月'=>2592000,'周'=>604800,'天'=>86400,'时'=>3600,'分'=>60,'秒'=>1);
        foreach ($list as $key => $value) {
            //电话号码位处理成星号
            $list[$key]['tel'] = substr($value['tel'], 0,3) . "*****" . substr($value['tel'], -3);
            //重复订单处理
            $list[$key]['phone_repeat_count'] = $phoneRepeats[$value['id']];
            $list[$key]['call_repeat_count'] = $callRepeats[$value['id']];
            // $list[$key]['ip_repeat_count'] = $ipRepeats[$value['id']];

            if ($callLasttime[$value['id']]) {
                $list[$key]['call_lasttime'] = date("Y-m-d H:i:s", strtotime($callLasttime[$value['id']]));
            } else {
                $list[$key]['call_lasttime'] = '';
            }

            //电话号码归属地处理，如果和发单填写的城市不同则显示电话号码归属地
            if (!empty($phoneLocation[$value['id']])) {
                if(false === strpos($phoneLocation[$value['id']], $value['city']) && false === strpos($value['city'], $phoneLocation[$value['id']]) && $value['city'] != '总站'){
                    $list[$key]['phone_location'] = $phoneLocation[$value['id']];
                }
            }

            //处理订单显号申请人 $apply_tel = array('申请人ID' => '审核状态')
            $apply_tel_admin = explode(',', $value['apply_tel_admin']);
            $apply_tel_status = explode(',', $value['apply_tel_status']);
            foreach ($apply_tel_admin as $k => $v) {
                $list[$key]['apply_tel'][$v] = $apply_tel_status[$k];
            }

            //处理订单发布时间记录
            $list[$key]['remark_time'] = $remarkTime[$value['id']];

            //当前时间和订单发布时间差
            $time_diff = time() -  $list[$key]['time'];
            $list[$key]['timex_ori'] =   $time_diff;//记录原时间戳时间差
            foreach ($dateArray as $k => $v) {
                if ($time_diff>=$v) {
                    $num=intval($time_diff/$v);
                    $time_diff-=$num*$v;
                    $real_diff.=$num.$k;
                }
            }
            $list[$key]['timex'] =   $real_diff;
            unset($time_diff);
            unset($real_diff);

            //订单及时度
            $time_diff = $list[$key]['callfast_time'];
            foreach ($dateArray as $k => $v) {
                if ($time_diff>=$v) {
                    $num=intval($time_diff/$v);
                    $time_diff-=$num*$v;
                    $real_diff.=$num.$k;
                }
            }
            $list[$key]['timef'] =   $real_diff;
            unset($time_diff);
            unset($real_diff);

            //最长呼叫
            $time_diff = $list[$key]['calllong_time'];
            foreach ($dateArray as $k => $v) {
                if ($time_diff>=$v) {
                    $num=intval($time_diff/$v);
                    $time_diff-=$num*$v;
                    $real_diff.=$num.$k;
                }
            }
            $list[$key]['timel'] =   $real_diff;
            unset($time_diff);
            unset($real_diff);
        }
        $main['info']['list'] = $list;



        //获取城市信息
        $main['city'] = D('Quyu')->getQuyuList();
        if($param['city']){
            $area = new AreaModel();
            $main['area'] = $area->getChildByCity($param['city']);
        }

        //判断是否有查看呼叫记录的权限
        if (check_user_menu_auth('/voip/voiprecord/')) {
            $main['auth']['checkcall'] = '1';
        }


        $this->assign('new_order_interval', OP('new_order_interval'));
        //登录人信息
        $main['admin'] = $admin;

        //默认操作人
        $main['defaultOperater'] = in_array($admin['id'], $operaters['ids']) ? $admin['id'] : '';

        //获取订单标识
        $inLogic = new OrderSourceInLogicModel();
        $orderType = $inLogic->getSourceInSelect();
        $this->assign('orderType',$orderType);
        $this->assign('userInfo',$admin);
        $this->assign('main', $main);
        $this->display();
    }
    
    public function pauseOrderList()
    {
        $admin = getAdminUser();
        //获取查询条件
        $param = I('get.', '', 'trim');

        //判断是否有查看呼叫记录的权限
        if (check_user_menu_auth('/voip/voiprecord/')) {
            $main['auth']['checkcall'] = '1';
        }
        //获取订单备注
        $main['remarks'] = $this->status2Arr($this->status);

        //获取订单标识
        $inLogic = new OrderSourceInLogicModel();
        $orderType = $inLogic->getSourceInSelect();
        $this->assign('orderType',$orderType);

        //获取城市信息
        $main['city'] = D('Quyu')->getQuyuList();
        if($param['city']){
            $area = new AreaModel();
            $main['area'] = $area->getChildByCity($param['city']);
        }

        $operaters = D('Adminuser')->getKfManagerListByIdAndUid($admin['id'], $admin['uid'],'');
        $main['operaters'] = $operaters['list'];
        $ids = array_column($operaters['list'],"id");
        $ids[] = session("uc_userinfo.id");
        //获取列表
        $logic = new OrdersLogicModel();
        $main['info'] = $logic->getPauseOrderPoolList($ids,$param["condition"],$param["time_start"],$param["time_end"],$param["time_real_start"],$param["time_real_end"],$param["city"],$param["area"],$param["remarks"],$param["op_uid"],$param["source"],$param["orderlxs"]);

        $this->assign('main', $main);
        $this->display();
    }

    public function getCountOrdersList($detail){
        //判断是否可获取订单数量简单信息
        if (check_menu_auth('/orders/getordercountbrief/') == true) {
            return $this->getOrderCountBrief($detail);
        }
    }

    /**
     * 刷新订单状态请求
     * @return mixed
     */
    public function refreshOrderCount()
    {
        $admin = getAdminUser();
        if (!empty($admin)) {
            $result = $this->getRefreshOrderCount($admin['id']);
            $this->ajaxReturn(array('data'=>$result,'info'=>'获取成功！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
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

    /**
     * 订单数量简单信息
     * @param string $detail 是否查询列表信息
     * @return mixed
     */
    public function getOrderCountBrief($detail = '')
    {
        $db = D('OrderPool');
        //当前新订单总数：订单池的新订单数量，没有认领人的
        $cityIds = $this->getAllowScrambleOrderCityIds();
        $result['new'] = $db->getNoOwnerNewOrderCount($cityIds);

        //当前已抢未拨打新订单总数
        $result['uncalled'] = $db->getUnCalledNewOrderCount($cityIds);

        if (time() >= strtotime(date('Y-m-d 17:30:00'))) {
            $start = strtotime(date('Y-m-d 17:30:00'));
            $end = strtotime(date('Y-m-d 17:30:00') . '+1 days');
        } else {
            $start = strtotime(date('Y-m-d 17:30:00') . '-1 days');
            $end = strtotime(date('Y-m-d 17:30:00'));
        }
        //获取当天发单量
        $result['publish'] = $db->getCanGetNewOrderCount($cityIds, $start, $end);
        $result['page'] = '';
        $result['list'] = [];

        //根据页面点击的总个数来查询 对应的数据
        if($detail){
            import('Library.Org.Util.Page');
            $fields = 'o.id,o.source_in,o.time_real,o.time,o.cs,o.qx,o.source_type,o.tel,o.tel_encrypt,
                        o.nf_time,o.mianji,o.visitime,o.ON,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,
                        o.from_old_orderid,o.remarks,o.lasttime,o.calllong_time,o.callfast_time,o.wzd,q.cname AS city,a.qz_area AS area,
                        o.wzd,GROUP_CONCAT(t. STATUS) AS apply_tel_status,GROUP_CONCAT(t.apply_id) AS apply_tel_admin,p.op_name,o.source';
            switch ($detail['logo']) {
                case 'ordercountbrief_new':
                    $p = new \Page($result['new'], 20);
                    $list = $db->getNoOwnerNewOrderList($cityIds,$p->listRows,$fields,'',$p->firstRow,'o.id');
                    break;
                case 'ordercountbrief_uncalled':
                    $p = new \Page($result['uncalled'], 20);
                    $list = $db->getUnCalledNewOrderList($cityIds, $fields, 'o.id', $p->firstRow, $p->listRows);
                    break;
                case 'ordercountbrief_publish':
                    $p = new \Page($result['publish'], 20);
                    $list = $db->getCanGetNewOrderList($cityIds, $start, $end,$fields,$p->firstRow, $p->listRows,'o.id');
                    break;
            }
            $result['page'] = isset($p) ? $p->show() : '';
            $result['list'] = isset($list) ? $list : [];
        }
        return $result;
    }

    /**
     * 获取订单刷新数量
     * @param  [int] $adminId [用户ID]
     * @return mixed
     */
    public function getRefreshOrderCount($adminId)
    {
        if (!empty($adminId)) {
            $unfinished_order_count = OP('unfinished_order_count');
            $db = D('OrderPool');
            //当前新订单总数：订单池的新订单数量，没有认领人的
            $cityIds = $this->getAllowScrambleOrderCityIds();
            $result['new'] = $db->getNoOwnerNewOrderCount($cityIds);

            //当前未完成订单数 = 新单+次新单+扫单+待定单+被撤订单*系数+当天无效单*系数-当天有效单*系数（去除被撤订单）
            $result['unfinished'] = $db->getUnfinishedOrderCountByUid($adminId);
            //可获取新订单总数 = 个人未完成订单数峰值 - 当前未完成订单数
            $result['obtain'] = $unfinished_order_count - $result['unfinished'];
            //被撤回订单数量
            $result['retracted'] = $db->getRevokedOrderCount($adminId);
            //当天每人人均应该获取的订单量，如果当前时间大于等于5点半，则重新计算
            if (time() >= strtotime(date('Y-m-d 17:30:00'))) {
                $start = strtotime(date('Y-m-d 17:30:00'));
                $end = strtotime(date('Y-m-d 17:30:00') . '+1 days');
            } else {
                $start = strtotime(date('Y-m-d 17:30:00') . '-1 days');
                $end = strtotime(date('Y-m-d 17:30:00'));
            }
            $canGetNewOrderCount = $db->getCanGetNewOrderCount($cityIds, $start, $end);
            //获取在订单池中客服数量
            $acitveAdminUserCount = D('Home/Logic/OrderPondServiceLogic')->getKfNum();
            if ($acitveAdminUserCount == 0) {
                $result['average'] = 0;
            } else {
                $result['average'] = ceil($canGetNewOrderCount / $acitveAdminUserCount);
            }
            return $result;
        }
        return false;
    }

    /**
     * [scrambleOrder 抢单，获取订单| scramble：争抢]
     * @return [type] [description]
     */
    public function scrambleOrder()
    {
        $admin = getAdminUser();
        //只有客服，客服组长能获取新订单
        if (!in_array($admin['uid'], array(2,31))) {
            $this->ajaxReturn(array('data'=>'','info'=>'当前角色不支持获取新订单！','status'=>0));
        }
        //导入RedisLibrary类库用于锁定用户操作和订单操作
        import('Library.Org.RedisLibrary.RedisLibrary');
        $redis = new \RedisLibrary();
        if ($redis != true) {
            $this->ajaxReturn(array('data'=>'','info'=>'缓存中间件连接失败，请联系技术部门！','status'=>0));
        }
        //获取新订单时间间隔，除非获取成功，其他返回结果前均要释放该锁
        $adminKey = 'ScrambleOrder:' . $admin['id'];
        $lock = $redis->lock($adminKey, OP('new_order_interval'));
        if ($lock == false) {
            $this->ajaxReturn(array('data'=>'','info'=>'请勿多次点击！','status'=>0));
        }
        //获取未完成订单量限制开关，switch = 1，开启开关
        $switch = OP('unfinished_order_switch');
        if ($switch == 1) {
            $unfinishedStart = OP('unfinished_order_start');
            $unfinishedEnd   = OP('unfinished_order_end');
            $start           = strtotime(date('Y-m-d') . ' ' . $unfinishedStart);
            $end             = strtotime(date('Y-m-d') . ' ' . $unfinishedEnd);
            $time            = time();
            //如果当前时间不在开关起作用的时间范围内，同样需要判断未完成订单量是否超出限制
            if (!(($time >= $start) && ($time <= $end))) {
                //判断当前未完成订单量是否超出限制
                $unfinishedAllow = OP('unfinished_order_count');
                $unfinishedCount = D('OrderPool')->getUnfinishedOrderCountByUid($admin['id']);
                //可获取新订单总数 = 个人未完成订单数峰值 - 当前未完成订单数
                if ($unfinishedCount >= $unfinishedAllow) {
                    $redis->unlock($adminKey);
                    $this->ajaxReturn(array('data'=>'','info'=>'未完成订单量达到峰值：' . $unfinishedAllow,'status'=>0));
                }
            }
        } else {
            //判断当前未完成订单量是否超出限制
            $unfinishedAllow = OP('unfinished_order_count');
            $unfinishedCount = D('OrderPool')->getUnfinishedOrderCountByUid($admin['id']);
            //可获取新订单总数 = 个人未完成订单数峰值 - 当前未完成订单数
            if ($unfinishedCount >= $unfinishedAllow) {
                $redis->unlock($adminKey);
                $this->ajaxReturn(array('data'=>'','info'=>'未完成订单量达到峰值：' . $unfinishedAllow,'status'=>0));
            }
        }

        //获取允许的未处理新订单个数
        $newOrderCountAllow = OP('new_order_count');
        //获取操作人的未完成新单数量
        $unfinishedNewCount = D('OrderPool')->getUnfinishedNewOrderCountByUid($admin['id']);
        if ($unfinishedNewCount >= $newOrderCountAllow) {
            //如果未完成新单量大于等于设置的新单量上限，则不允许抢单
            $redis->unlock($adminKey);
            $this->ajaxReturn(array('data'=>'','info'=>'未处理新订单数量达到峰值：' . $newOrderCountAllow,'status'=>0));
        } else {
            //所有能抢单的城市
            $cityIds = $this->getAllowScrambleOrderCityIds();
            //查询客服是否在订单池中
            $isInPond = D('Home/Logic/OrderPondLogic')->findKfInPond($admin['id']);
            //未分配客服，只能抢主池订单
            if ($isInPond === false) {
                $setCity = D('Home/Logic/OrderPondLogic')->getPondCityByKf($admin['id'],true);
                $allowCity = array_intersect($setCity['city_ids'],$cityIds);
                //查询总订单池的订单
                $result = D('OrderPool')->getNoOwnerNewOrderList($allowCity, 1, 'p.orderid', 'o.zhuanfaren DESC, o.time_real DESC')[0];
            } else {
                //查询此客服设置的城市
                $setCity = D('Home/Logic/OrderPondLogic')->getPondCityByKf($admin['id']);
                $allowCity = array_intersect($setCity['city_ids'],$cityIds);
                if (in_array(1,$setCity['id'])) {
                    //查询总订单池的订单
                    $result = D('OrderPool')->getNoOwnerNewOrderList($allowCity, 1, 'p.orderid', 'o.zhuanfaren DESC, o.time_real DESC')[0];
                } else {
                    //可获取城市为空，直接查询主池的订单
                    if (empty($allowCity)){
                        $setCity = D('Home/Logic/OrderPondLogic')->getPondCityByKf($admin['id'],true);
                        $allowCity = array_intersect($setCity['city_ids'],$cityIds);
                        $result = D('OrderPool')->getNoOwnerNewOrderList($allowCity, 1, 'p.orderid', 'o.zhuanfaren DESC, o.time_real DESC')[0];
                    } else {
                        //查询子池订单
                        $result = D('OrderPool')->getNoOwnerNewOrderList($allowCity, 1, 'p.orderid', 'o.zhuanfaren DESC, o.time_real DESC')[0];
                        if (empty($result)) {
                            //子池没有再去查询总订单池的订单
                            $setCity = D('Home/Logic/OrderPondLogic')->getPondCityByKf($admin['id'],true);
                            $allowCity = array_intersect($setCity['city_ids'],$cityIds);
                            $result = D('OrderPool')->getNoOwnerNewOrderList($allowCity, 1, 'p.orderid', 'o.zhuanfaren DESC, o.time_real DESC')[0];
                        }
                    }
                }
            }
            if (empty($result)) {
                $redis->unlock($adminKey);
                $this->ajaxReturn(array('data'=>'','info'=>'暂无新定单，请稍后再试','status'=>0));
            } else {
                $orderKey = 'OrderPool:' . $result['orderid'];
                $lock = $redis->lock($orderKey, 10);
                if ($lock == false) {
                    $redis->unlock($adminKey);
                    $this->ajaxReturn(array('data'=>'','info'=>'该订单已被抢，请再次点击获取！','status'=>0));
                } else {
                    $save['op_uid']  = $admin['id'];
                    $save['op_name'] = $admin['name'];
                    $save['addtime'] = time();
                    $save['status']  = '0';
                    $map = array(
                        'orderid' => $result['orderid'],
                        'op_uid' => 0
                    );
                    $flag = M('order_pool')->where($map)->save($save);
                    $redis->unlock($orderKey);
                    if ($flag != false) {
                        $this->ajaxReturn(array('data'=>'','info'=>'订单获取成功！','status'=>1));
                    } else {
                        $redis->unlock($adminKey);
                        $this->ajaxReturn(array('data'=>'','info'=>'订单获取失败，请重新获取！','status'=>0));
                    }
                }
            }
        }
    }

    /**
     * 获取允许抢单的城市ID
     * @return mixed
     */
    private function getAllowScrambleOrderCityIds()
    {
        //获取开放的城市ID
        $cids = D('User')->getOpenCityHaveRealVipCids();
        //获取客服系统订单设置,开放城市
        $allowCids = array_filter(explode(',', OP('open_order_city')));
        if (!empty($allowCids)) {
            //取以上城市ID的交集
            $cityIds = array_intersect($allowCids, $cids);
        } else {
            $cityIds = $cids;
        }
        return $cityIds;
    }

    /**
     * 获取显号审核列表
     * @return mixed
     */
    public function getApplyTelList()
    {
        $id = I('post.id');
        //检查
        if (empty($id)) {
            $this->ajaxReturn(array('data' => '', 'info' => '缺少参数！', 'status' => 0));
        }

        $result = D('OrdersApplyTel')->getApplyTelListByOrdersId($id);

        if (!empty($result)) {
            $this->assign('list', $result);
            $html = $this->fetch("orders_apply_tel_modal");
            $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
        } else {
            $this->ajaxReturn(array('data' => '', 'info' => '重复订单列表为空！', 'status' => 0));
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' => 0));
    }

    /**
     * 申请显示电话号码审核操作
     * @return mixed
     */
    public function displayNumberCheck()
    {
        $admin = getAdminUser();
        $id = I('post.id');
        //检查参数
        if (empty($id)) {
            $this->ajaxReturn(array('data'=>'','info'=>'非法请求，缺少参数，请联系技术部门！','status'=>0));
        }
        $save['status'] = I('post.status');
        $save['passer_id'] = $admin['id'];
        $save['pass_time'] = time();
        $result = D('OrdersApplyTel')->saveOrdersApplyTel($id, $save);
        if ($result) {
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'审核失败！','status'=>0));
    }

    /**
     * 手机号重复订单
     * @return mixed
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

    /**
     * [getRepeatOrderListByIp IP重复订单]
     * @return [type] [description]
     */
    public function getRepeatOrderListByIp()
    {
        $id = I('post.id');
        //检查
        if (empty($id)) {
            $this->ajaxReturn(array('data' => '', 'info' => '缺少参数！', 'status' => 0));
        }
        $order = D('Orders')->getOrdersById($id);
        if (!empty($order)) {
            $result = D('Orders')->getOrdersByIp($order['ip']);
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

    /**
     * [getRecentOperateLog 获取最近操作记录]
     * @return void
     */
    public function getRecentOperateLog()
    {
        $id = getAdminUser('id');
        if (!empty($id)) {
            $result = D('LogOrderCsos')->getRecentOperateLogByUserId($id);
            if (!empty($result)) {
                $this->assign('list', $result);
                $html = $this->fetch("recent_operate_log");
                $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '最近操作订单列表为空！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' => 0));
    }

    /**
     * 重复检测
     * @return void
     */
    public function repeatCheck(){
        if ($_POST) {
            //查询订单信息
            $info = $this->getOrderInfo(I("post.id"));
            //生成手机访问的短网址
            $orderdwz  = url_getdwz($info['id']);
            $info['dwz'] = $orderdwz;

            $this->assign("info",$info);
            $this->assign("source_in",$this->source_in);
            $this->assign("zhuanfaren",$this->zhuanfaren);
            //获取订单城市和区县
            $city = D("Quyu")->getCityInfoById($info["cs"]);
            $this->assign("city",$city);
            //户型
            $huxing = D("Huxing")->gethx();
            //预算
            $yusuan = D("Jiage")->getJiage();
            //装修方式
            $fangshi  = D("Fangshi")->getfs();
            //风格
            $fengge  = D("Fengge")->getfg();
            //获取最后审核人信息
            $csos_new = D("OrderCsosNew")->getCsosInfo(I("post.id"));

            //获取订单分配信息
            $company = D("OrderInfo")->getOrderComapny($info["id"]);

            //获取该订单所在城市的的会员公司
            $result = $this->getCompanyList($info["cs"],$info["lng"],$info["lat"]);

            //如果是已分配公司,默认选中
            foreach ($company as $key => $value) {
                $compnay_id[$value["id"]] = $value["id"];
            }

            foreach ($result[0] as $key => $value) {
                foreach ($value["child"] as $k => $val) {
                    if (array_key_exists($val["id"],$compnay_id)) {
                        $result[0][$key]["child"][$k]["ischeck"] = 1;
                    }
                }
            }

            foreach ($result[1] as $key => $value) {
                foreach ($value["child"] as $k => $val) {
                    if (array_key_exists($val["id"],$compnay_id)) {
                        $result[1][$key]["child"][$k]["ischeck"] = 1;
                    }
                }
            }

            //查询上次分配装修公司
            $fenpei_company = D("OrderInfo")->getLastTypeFw($info["id"],$info["cs"]);

            //本地装修公司
            foreach ($fenpei_company as $k => $val) {
                if ($val["cs"] == $info["cs"]) {
                    $fenpei_now_company[] = $val;
                    unset($fenpei_company[$k]);
                }
            }

            //其他城市
            foreach ($result[1] as $key => $value) {
                foreach ($fenpei_company as $k => $val) {
                    if ($val["cs"] == $key) {
                        $result[1][$key]["fen_company"][] = $val;
                        unset($fenpei_company[$k]);
                    }
                }
            }

            //获取最近30过期的会员信息
            $lostCompany = $this->getLastExpireCompanyList($info["cs"],date("Y-m-d"));

            //获取订单IP是否重复
            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

            if ($ipCount[0]["repeat_count"] > 0) {
                $this->assign("ipMark",$ipCount[0]["repeat_count"]);
            }
            $this->assign("lostCompany",$lostCompany);
            $this->assign("company",$company);
            $this->assign("fenpei_now_company",$fenpei_now_company);
            $this->assign("nowCompanys",$result[0]);
            $this->assign("otherCompanys",$result[1]);
            $this->assign("logCount",$logCount);
            $this->assign("csos_new",$csos_new);
            $this->assign("status",$this->status);
            $this->assign("keys",$this->keys);
            $this->assign("lf_time",$this->lf_time);
            $this->assign("start_time",$this->start_time);
            $this->assign("shi",$this->shi);
            $this->assign("lxs",$this->lxs);
            $this->assign("fengge",$fengge);
            $this->assign("fangshi",$fangshi);
            $this->assign("yusuan",$yusuan);
            $this->assign("huxing",$huxing);
            $tmp = $this->fetch("repeat_check");
            $this->ajaxReturn(array("code"=>200,"data"=>$tmp,'info'=>$info));
        }
    }

    /**
     * 订单详细页面
     * @return void
     */
    public function operate()
    {
        //第一次通话结束--第三次通话开始的时间差
        if ($_POST) {
            $this->assign('is_show',I('post.is_show'));
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
            //查询订单信息
            $info = $this->getOrderInfo(I("post.id"));

            //过滤不规则字符串
            $reg = '/[\`~!@#$%^&*\(\)+<>?"{},\/;\'\"\s]/';
            $info["xiaoqu"] = preg_replace($reg,"",$info["xiaoqu"]);

            //查询是否是活动订单
            if (!empty($info["source"])) {
                $info["activity"] = D("Home/Logic/ActivityLogic")->getActivityInfo($info["source"]);
            }

            //后台转发人数组
            $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
            $names = D("User")->getAdminNamesById($ids['option_value']);
            foreach ($names as $k => $v) {
                $zhuanfaren[] = $v['name'];
            }
            $this->assign("zhuanfaren",$zhuanfaren);
            //获取订单城市和区县
            $city = D("Quyu")->getCityInfoById($info["cs"]);
            $this->assign("city",$city);
            //户型
            $huxing = D("Huxing")->gethx();
            //预算
            $yusuan = D("Jiage")->getJiage();
            //装修方式
            $fangshi  = D("Fangshi")->getfs();
            //风格
            $fengge  = D("Fengge")->getfg();
            //获取最后审核人信息
            $csos_new = D("OrderCsosNew")->getCsosInfo(I("post.id"));
            //获取 未接通电话短信通知 短信记录
            $logCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_WJTDH);

            //获取 通知业主分配的装修公司 短信记录
            $smsCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_FPGS);

            //获取 拒绝服务 短信记录
            $jjfwCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_JJFW);

            // 打电话前通知
            $preFsCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_BEFORECALL);

            //获取 企业微信 短信记录
            $wxCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_QYWX);

            //获取订单分配信息
            $company = D("OrderInfo")->getOrderComapny($info["id"]);

            //有分配订单的情况下，查询微信是否发送
            if (count($company) > 0) {
                //获取订单微信发送记录
                $wx = D("LogWxOrdersend")->getWeixinLog($info["id"]);
                if (count($wx) > 0) {
                    $this->assign("wxMark",1);
                }
            }

            //获取城市信息模板
            $cityTmp = $this->getCityInfoTmp($info["cs"]);

            //获取城市信息
            $quyu = D('Quyu')->getQuyuList();
            $this->assign('quyu', $quyu);

            //获取该订单所在城市的的会员公司
            $companyList = $this->getCompanyList($info["cs"],$info["lng"],$info["lat"], $info);

            // 装修公司月订单达标状态
            $companyLogic = new CompanyLogicModel();
            $companyList = $companyLogic->setCompanyMonthOrderReachStatus($companyList, $info["cs"], $info["cname"]);

            $this->assign('companyList',$companyList);
            $nowCompanys = $this->fetch("nowCompanys");
            $otherCompanys = $this->fetch("otherCompanys");

            //获取最近30天过期的会员信息
            $lostCompany = $this->getLastExpireCompanyList($info["cs"],date("Y-m-d"));

            //获取订单IP是否重复
            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

            if ($ipCount[0]["repeat_count"] > 1) {
                $this->assign("ipMark",$ipCount[0]["repeat_count"]-1);
            }

            //发单位置19102332，显示关联的装修公司
            if ($info["source"] ==  '19102332') {
                $logic = new OrdersLogicModel();
                $result = $logic->getZixunCompanyInfo($info['id']);
                $info["huifan"] = $result."\r\n".$info["huifan"];
            }

            // 渠道来源
            $source_src = D("Orders")->getOrderSourceById(I("post.id"));

            //获取发单标识
            $inLogic = new OrderSourceInLogicModel();
            $orderType = $inLogic->getSourceInSelect();
            $this->assign('orderType',$orderType);

            $this->assign("wxCount",$wxCount);
            $this->assign("info",$info);
            $this->assign("source_in",$this->source_in);
            $this->assign("src_from", $this->src_from);
            $this->assign("source_src", $source_src['source_src']);
            $this->assign("jinji",$jinji);
            $this->assign("timemin",$timemin);
            $this->assign("lostCompany",$lostCompany);
            $this->assign("company",$company);
            $this->assign("smsCount",$smsCount);
            $this->assign("nowCompanys",$nowCompanys);
            $this->assign("otherCompanys",$otherCompanys);
            $this->assign("logCount",$logCount);
            $this->assign("jjfwCount",$jjfwCount);
            $this->assign("preFsCount",$preFsCount);
            $this->assign("csos_new",$csos_new);
            $this->assign("status",$this->status);
            $this->assign("keys",$this->keys);
            $this->assign("lf_time",$this->lf_time);
            $this->assign("start_time",$this->start_time);
            $this->assign("shi",$this->shi);
            $this->assign("lxs",$this->lxs);
            $this->assign("fengge",$fengge);
            $this->assign("fangshi",$fangshi);
            $this->assign("yusuan",$yusuan);
            $this->assign("huxing",$huxing);
            $this->assign("cityTmp",$cityTmp);
        }else{
            //家具转装修详情页
            //查询订单信息
            $info = $this->getOrderInfo(I("get.isjiaju"),true);
            if(count($info) == 0){
                $this->error('未查找到订单信息! ');
            }
            //过滤不规则字符串
            $reg = '/[\`~!@#$%^&*\(\)+<>?"{},\/;\'\"\s]/';
            $info["xiaoqu"] = preg_replace($reg,"",$info["xiaoqu"]);

            $this->assign("info",$info);
            $this->assign("source_in",$this->source_in);
            //后台转发人数组
            $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
            $names = D("User")->getAdminNamesById($ids['option_value']);
            foreach ($names as $k => $v) {
                $zhuanfaren[] = $v['name'];
            }
            $this->assign("zhuanfaren",$zhuanfaren);
            //获取订单城市和区县
            $city = D("Quyu")->getCityInfoById($info["cs"]);
            $this->assign("city",$city);
            //户型
            $huxing = D("Huxing")->gethx();
            //预算
            $yusuan = D("Jiage")->getJiage();
            //装修方式
            $fangshi  = D("Fangshi")->getfs();
            //风格
            $fengge  = D("Fengge")->getfg();
            //获取最后审核人信息
            $csos_new = D("OrderCsosNew")->getCsosInfo($info["id"]);

            //获取城市信息模板
            $cityTmp = $this->getCityInfoTmp($info["cs"]);

            //获取城市信息
            $quyu = D('Quyu')->getQuyuList();
            $this->assign('quyu', $quyu);

            //获取该订单所在城市的的会员公司
            $companyList = $this->getCompanyList($info["cs"],$info["lng"],$info["lat"]);
            $this->assign('companyList',$companyList);
            $nowCompanys = $this->fetch("nowCompanys");
            $otherCompanys = $this->fetch("otherCompanys");

            //获取最近30天过期的会员信息
            $lostCompany = $this->getLastExpireCompanyList($info["cs"],date("Y-m-d"));

            //获取订单IP是否重复
            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

            if ($ipCount[0]["repeat_count"] > 1) {
                $this->assign("ipMark",$ipCount[0]["repeat_count"]-1);
            }
            $status = $this->status;
            $statusjiaju[] = $status[3];
            $statusjiaju[] = $status[4];
            $this->assign("status", $statusjiaju);
            $this->assign("lostCompany",$lostCompany);
            $this->assign("nowCompanys",$nowCompanys);
            $this->assign("otherCompanys",$otherCompanys);
            $this->assign("csos_new",$csos_new);
            $this->assign("keys",$this->keys);
            $this->assign("lf_time",$this->lf_time);
            $this->assign("start_time",$this->start_time);
            $this->assign("shi",$this->shi);
            $this->assign("lxs",$this->lxs);
            $this->assign("fengge",$fengge);
            $this->assign("fangshi",$fangshi);
            $this->assign("yusuan",$yusuan);
            $this->assign("huxing",$huxing);
            $this->assign("cityTmp",$cityTmp);
            $this->assign("isJiaju",1);
        }
        $tmp = $this->fetch("operate");
        $this->ajaxReturn(array('data'=>$tmp,'info'=>$info,'status'=>1));
    }

    /**
     * 根据地址获取经纬度
     * @return void
     */
    public function getAddressLatlong()
    {
        $url = "http://api.map.baidu.com/geocoder/v2/?address=%E5%8C%97%E4%BA%AC%E5%B8%82%E6%B5%B7%E6%B7%80%E5%8C%BA%E4%B8%8A%E5%9C%B0%E5%8D%81%E8%A1%9710%E5%8F%B7&output=json&ak=q23cg2dVNxDL71KLzs64mujph1fE6zX4";
        get_content_by_curl($url);
    }

    public function getAreaByCid()
    {
        $cid = I('get.cid');
        $order_id = I("get.order_id");
        if (empty($cid)) {
            $this->ajaxReturn(array('data'=>'','info'=>'获取失败，城市ID为空！','status'=>0));
        }
        $city = D("Quyu")->getCityInfoById($cid);
        if (empty($city)) {
            $this->ajaxReturn(array('data'=>'','info'=>'区域为空！','status'=>0));
        }
        //生成模板
        $areaHtml = '<option value="">-请选择-</option>';
        foreach ($city as $key => $value) {
            if (!empty($value['qz_areaid'])) {
                $areaHtml = $areaHtml . '<option value="' . $value['qz_areaid'] . '">' . $value['qz_area'] . '</option>';
            }
        }

        $order_info = $this->getOrderInfo($order_id);

        //获取该城市的会员公司
        $companyList = $this->getCompanyList($cid, $order_info['lng'], $order_info['lat'], $order_info);

        // 装修公司月订单达标状态
        $companyLogic = new CompanyLogicModel();
        $companyList = $companyLogic->setCompanyMonthOrderReachStatus($companyList, $id, $city[0]["cname"]);

        $this->assign('companyList',$companyList);
        $nowCompanys = $this->fetch("nowCompanys");
        $otherCompanys = $this->fetch("otherCompanys");

        //获取城市信息
        $cityTmp = $this->getCityInfoTmp($cid);

        $this->ajaxReturn(array(
            'data'=>array('area' => $areaHtml, 'nowCompanys'=>$nowCompanys,'otherCompanys'=>$otherCompanys,'cityTmp'=>$cityTmp),
            'info'=>'',
            'status'=>1
        ));
    }

    /**
     * 查询IP真人概率
     * @param  string $value [description]
     * @return void
     */
    public function searchrtbasia()
    {
        if($_GET['ipstr']){
            $ipstr    = $_GET['ipstr'];
            $info['ipstr'] = $ipstr;

            $apikey = OP('APISTORE_BAIDU');

            /*S-获取真人概率信息*/
            $ch = curl_init();
            $url = 'http://apis.baidu.com/rtbasia/non_human_traffic_screening/nht_query?ip='.$ipstr;
            $header = array(
                "apikey: $apikey",
            );
            // 添加apikey到header
            curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //设置超时只需要设置一个秒的数量就可以
            curl_setopt($ch, CURLOPT_TIMEOUT,3);
            // 执行HTTP请求
            curl_setopt($ch , CURLOPT_URL , $url);
            $res = curl_exec($ch);
            $result = json_decode($res,true);
            if('Success' == $result['msg']){
                $istrue = ($result['data']['score'] * 10).'%';
            }else{
                $istrue = '真人概率未知!';
            }
            $info['istrue'] = $istrue;
            /*E-获取真人概率信息*/

            /*S-获取IP地区信息*/
            $ch = curl_init();
            $url = 'http://apis.baidu.com/apistore/iplookupservice/iplookup?ip='.$ipstr;
            $header = array(
                "apikey: $apikey",
            );
            // 添加apikey到header
            curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //设置超时只需要设置一个秒的数量就可以
            curl_setopt($ch, CURLOPT_TIMEOUT,3);
            // 执行HTTP请求
            curl_setopt($ch , CURLOPT_URL , $url);
            $res = curl_exec($ch);
            $result = json_decode($res,true);
            if('success' == $result['errMsg']){
                if('None' == $result['retData']['province']){
                    $info['position'] = '未知地区';
                }else{
                    $info['position'] = $result['retData']['province'].$result['retData']['city'].$result['retData']['district'];
                }
                $info['type'] = $result['retData']['carrier'];
            }else{
                $info['position'] = '未知地区';
                $info['type'] = '未知网络类型';
            }
            /*S-获取IP地区信息*/

            $this->assign('info',$info);
            $this->display();
        }
    }

    /**
     * 安全百度搜索号码
     * @return void
     */
    public function tel_baidusearch()
    {
        //获取订单号码
        $model = D("Home/Orders");
        $result = $model->findOrderInfoAndTel(I("get.id"));

        if (empty($result["tel8"])) {
            $this->_error("该订单发布电话不存在");
        }

        $baidu = GetPhoneBaiduPageInfoNoNum($result["tel8"]);

        $tdk = [];
        $tdk['title'] = '百度搜索索引';
        $this->assign('tdk',$tdk);

        $this->assign('list',$baidu);
        $this->assign('type','baidu');
        $this->display("tel_search");
    }

    /**
     * 360号码搜索
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function tel_360search()
    {
        //获取订单号码
        $model = D("Home/Orders");
        $result = $model->findOrderInfoAndTel(I("get.id"));

        if (empty($result["tel8"])) {
            $this->_error("该订单发布电话不存在");
        }

        $search360 = GetPhone360searchPageInfoNoNum($result["tel8"]);

        $tdk = [];
        $tdk['title'] = '360搜索索引';
        $this->assign('tdk',$tdk);

        $this->assign('list',$search360);
        $this->assign('type','360');
        $this->display("tel_search");
    }


    /**
     * 搜狗搜索
     * pc端
     * https://www.sogou.com/web?query=11
     * @param  string $_GET['id'] 订单号
     * @return mixed 经过保密处理的搜索结果
     */
    public function tel_sogousearch()
    {
        //获取订单号码
        $model = D("Home/Orders");
        $result = $model->findOrderInfoAndTel(I("get.id"));

        if (empty($result["tel8"])) {
            $this->_error("该订单发布电话不存在");
        }

        $sogouSearch = GetPhoneSogouSearchPageInfoNoNum($result["tel8"]);

        $tdk = [];
        $tdk['title'] = '搜狗搜索索引';
        $this->assign('tdk',$tdk);

        $this->assign('list',$sogouSearch);
        $this->assign('type','sogou');
        $this->display("tel_search");
    }


    /**
     * 神马搜索搜索
     * //https://m.sm.cn/
     * 只支持移动端
     * @param  string $_GET['id'] 订单号
     * @return mixed 经过保密处理的搜索结果
     */
    public function tel_smsearch()
    {
        //获取订单号码
        $model = D("Home/Orders");
        $result = $model->findOrderInfoAndTel(I("get.id"));

        if (empty($result["tel8"])) {
            $this->_error("该订单发布电话不存在");
        }

        $smSearch = GetPhoneSmSearchPageInfoNoNum($result["tel8"]);

        $tdk = [];
        $tdk['title'] = '神马搜索索引';
        $this->assign('tdk',$tdk);

        $this->assign('list',$smSearch);
        $this->assign('type','sm');
        $this->display("tel_search");
    }


    /**
     * 申请显号
     * @return [type] [description]
     */
    public function tel_openeye()
    {
        if ($_POST) {
            if(!$_POST['text']){
                $this->ajaxReturn(array('errmsg'=> "请填写申请理由",'code'=> 100));
            }else{
                $result = $this->findOrderEyeInfo(I("post."));
                $this->ajaxReturn(array('errmsg'=> $result["errmsg"],'code'=> $result["code"]));
            }

        }
    }

    /**
     * 申请显号
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function findOrderEyeInfo($data)
    {
        $admin = getAdminUser();

        //1.判断该订单是否被该人审核显号
        //  1.已审核，已通过，直接返回
        //  2.已审核，未通过，更新申请时间和申请理由
        //2.未申请
        //  1.直接添加记录
        $apply = D('OrdersApplyTel')->getApplyTelByOrdersIdAndApplyId($data["id"], $admin['id']);

        if (!empty($apply)) {
            if ($apply['status'] == 2) {
                return array("code"=>"404","errmsg"=>"该订单已申请显号成功,请勿重新申请");
            }

            $save = array(
                'apply_reason' => $data["text"],
                'entrance' => $data['input'],
                'apply_time'   => time(),
                'status'       => 1
            );

            $result = D('OrdersApplyTel')->saveOrdersApplyTel($apply['id'], $save);
        } else {
            $save = array(
                'entrance' => $data['input'],
                'orders_id'    => $data["id"],
                'apply_id'     => $admin['id'],
                'apply_reason' => $data["text"],
                'apply_time'   => time(),
                'status' => 1
            );
            $result = D('OrdersApplyTel')->addOrdersApplyTel($save);
        }

        if ($result !== false) {
            return array("code"=>"200");
        }

        return array("code"=>"404","errmsg"=>"操作失败,请重新申请");
    }

    /**
     * 保存订单
     * @param  string $value [description]
     * @return void
     */
    public function orderup()
    {
        if ($_POST) {
            $this->editOrder(I("post.", [], 'trim'));
        }
        $this->ajaxReturn(['errmsg' => '请求错误', 'code' => 400]);
    }

    /**
     * 修(申请修改记录)
     */
    public function showapplyedit(){
        if ($_POST) {
            $result =  D("Orders")->getOrderApplyEdit(I('post.id'),2);
            if(!empty($result)&&isset($result)){
                $result['apply_time'] = empty( $result['apply_time'])?'': date('Y-m-d H:i:s',$result['apply_time']);
                $result['pass_time'] = empty( $result['pass_time'])?'': date('Y-m-d H:i:s',$result['pass_time']);
                $result['applystatus'] = $this->applystatus[$result['status']];
                $result['passname'] = isset($result['passname'])?$result['passname']:'';
                if(check_user_menu_auth('http://168new.qizuang.com/orders/changeapplyedit/')){
                    $check = 1;
                }else{
                    $check = 2;
                }
                $this->ajaxReturn(array('info'=> $result,'code'=> 200,'data'=>$check));
            }else{
                $this->ajaxReturn(array('errmsg'=> '获取失败','code'=> 1));
            }
        }
    }

    /**
     * 是否通过
     */
    public function changeapplyedit(){
        if ($_POST) {
            $id  = I('post.id');
            $status = I('post.status');
            $data["status"] =$status;
            $data["pass_time"] = time();
            $data["pass_id"] = session("uc_userinfo.id");
            $result =  D("Orders")->changeApplyEdit($id,$data);

            if($result>0){
                //添加操作日志
                $logData = array(
                    "remark" => session("uc_userinfo.name")."申请修改订单".$id."信息状态",
                    "action_id" =>  $id,
                    "logtype" => "changeapplyedit",
                    "info"=>$data
                );
                D('LogAdmin')->addLog($logData);

                $this->ajaxReturn(array('errmsg'=> "操作成功",'code'=> 200));
            }else{
                $this->ajaxReturn(array('errmsg'=> '操作失败','code'=> 1));
            }
        }
    }



    private function editOrder($data)
    {
        $model = D("Orders");

        // 验证
        if (isset($data['mianji']) && !empty($data['mianji'])) {
            if (!is_numeric($data['mianji'])) {
                $this->ajaxReturn(array("errmsg" => "面积请填写数字", "code" => 400));
            }
            if ($data['mianji'] < 1) {
                $this->ajaxReturn(array("errmsg" => "面积不得小于1", "code" => 400));
            }
            $data['mianji'] = floatval($data['mianji']);
        }

        if ($data["lx"] == 1 && empty($data['mianji'])) {
            $this->ajaxReturn(array("errmsg" => "家装面积不得为空", "code" => 400));
        }

        $id = $data["id"];
        //状态为分单或赠单情况不可保存订单信息
        $orderInfo = $model->getOrdersById($id);
        $isJiaju = false;
        if(!$orderInfo){
            $isJiaju = true;
        }
        $data['pagetype'] = isset($data['pagetype'])?$data['pagetype']:1;
        // 若请求来自对接页面不走该验证
        /*if($data['pagetype'] == 1 && $isJiaju == false){
            if (($orderInfo['on'] == 4) && ($orderInfo['type_fw'] == 1 || $orderInfo['type_fw'] == 2)) {
                return array("code"=>400,"errmsg"=>'分单或赠单情况下不可保存订单信息!');
            }
        }*/

        $data["lng"] = $data["lat"] = '';
        //2018-10-20小区落库需求新增start
        if(!empty($data["qx"])&&!empty($data["xiaoqu"])&&!empty($data["zuobiao"])){
            $jingwei = explode(',',$data["zuobiao"]);
            $data["lng"] = $jingwei[0];
            $data["lat"] = $jingwei[1];

            if(count($jingwei)>2){
                $this->ajaxReturn(array("errmsg" => "坐标填写错误", "code" => 400));
            }
            $lng = '/^(\-|\+)?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180)$/';
            $lan = '/^(\-|\+)?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/';
            if(!preg_match($lng,$jingwei[0])||!preg_match($lan,$jingwei[1])){
                $this->ajaxReturn(array("info" => "坐标填写错误!", "status" => 400));
            }

            $existXiaoqu = D("Home/Logic/AuthLogic")->getExistXiaoqu($data["cs"],$data["xiaoqu"]);
            //若区域内不存在该小区,对小区落库做新增操作
            if(!$existXiaoqu){
                D("Home/Logic/AuthLogic")->addCommunityfromOrder($data['cs'],$data["qx"],$data["xiaoqu"], $data["lng"],$data["lat"],empty($data["xiaoqutype"])?1:$data["xiaoqutype"]);
            }
        }

        /*if(empty($data["lx"]) || empty($data["lxs"])){
            $this->ajaxReturn(array("info"=>"请选择装修类型!", "status"=>1));
        }
        if(empty($data["mianji"])){
            $this->ajaxReturn(array("info"=>"请填写面积!", "status"=>1));
        }*/

        unset($data["zuobiao"]);
        $data["xiaoqu_type"] = $data["xiaoqutype"];
		unset($data["xiaoqutype"]);
        //2018-10-20小区落库需求新增end

        unset($data["id"]);
        if (!empty($data["lftime_other"])) {
            $data["lftime"] = $data["lftime_other"];
            unset($data["lftime_other"]);
        }

        if (!empty($data["start_other"])) {
            $data["start"] = $data["start_other"];
            unset($data["start_other"]);
        }
        $data["lasttime"] = time();
        $data["customer"] = session("uc_userinfo.id");



        if (empty($data["nf_time"])) {
            unset($data['nf_time']);
        }
        if (empty($data["lng"])) {
            unset($data['lng']);
        }
        if (empty($data["lat"])) {
            unset($data['lat']);
        }

        //编辑订单 微信/备用联系人
        if (isset($data['same_tel']) && $data['same_tel'] == 1) {
            //将订单手机号添加到微信字段
            $oDb = new OrdersModel();
            $order_info = $oDb->findOrderInfoAndTel($id);
            $data['wx'] = $order_info['tel8'];
            unset($data['same_tel']);
            unset($order_info);
        } else {
            //验证微信是否更改过
            if (strpos($data['contact_wx'], '*') === false) {
                $data['wx'] = empty($data['contact_wx']) ? '' : $data['contact_wx'];
                unset($data['contact_wx']);
            }
        }
        if (!isset($data['is_wx']) || $data['is_wx'] != 1) {
            $data['is_wx'] = 2;
        }

        // 添加回访时间
        if (!empty($data['review_time'])) {
            $data['review_time'] = strtotime($data['review_time']);
        }

        //保存订单信息
        if ($isJiaju) {
            //判断是否已经 家具转过装修
            $logLogic = new LogOrderRouteLogicModel();
            $route = $logLogic->getOrderLog($id);
            if ($route) {
                $this->ajaxReturn(array("errmsg" => "已将生成过装修订单!", "code" => 0));
            }
            //创建家具订单
            $new_id = date('Ymd') . sprintf("%05d%03d", time() % 86400, mt_rand(1, 1000)); // 生成订单号
            $data['id'] = $new_id;
            $i = $model->addOrder($data);
        } else {
            $i = $model->editOrder($id, $data);
        }

        if(!empty($data['src_from'])){
            $srcS = $model->getSource($data['src_from']);
            $model->updateSrc($id, ['source_src_id' => $srcS['id'], 'source_src' => $srcS['src']]);
        }

        if ($i !== false) {
            //家具订单转为装修订单 , 添加订单信息
            if($isJiaju){
                //添加订单池表
                $poolSave = [
                    'orderid' => $data['id'],
                    'time' => time(),
                    'type' => 2,
                    'status' => 0,
                    'op_uid' => session("uc_userinfo.id"),
                    'op_name' => session("uc_userinfo.user"),
                    'addtime' => time(),
                ];
                $poolDb = new OrderPoolModel();
                $poolDb->addOrder($poolSave);
                //添加转换信息表
                $logLogic = new LogOrderRouteLogicModel();
                $logLogic->saveOrderLog($data['id'], $id);
                //将家具订单手机号转到装修订单中
                D('Orders')->saveOrderTelByJiaju($data['id'], $id);

                //将后面的id转换成 生成的id
                $id = $data['id'];
            }
            //字段统计
            //查询是否记录统计
            $result = D("Home/Logic/OrdersLogic")->getOrderFieldStat($id);

            if (count($result) > 0) {
                foreach ($result as $key => $value) {
                    $fieldStat[$value["field"]] = $value["before_value"];
                }

                //修改统计记录
                $field = ["name","xiaoqu","cs","qx","mianji","yusuan"];
                foreach ($field as $key => $value) {
                    if ($fieldStat[$value] != $data[$value]) {
                        $saveField = [
                            "after" => $data[$value],
                            "op_uid" => session("uc_userinfo.id"),
                            "op_name" => session("uc_userinfo.name"),
                            "state" => 2,
                            "time" => time()
                        ];

                        if (!empty($fieldStat[$value])) {
                            $saveField["state"] = 3;
                        }

                        D("Home/Logic/OrdersLogic")->updateFieldStat($id,$value,$saveField);
                    }
                }
            }

            /**计算小区与城市距离start**/

            /**计算小区与城市距离end**/

            //若来自客服对接页面
            if($data['pagetype'] == 2){
                $data["save_state"] = 2;
                $data["save_time"] = time();
                D("Orders")->changeApplyEdit($id,$data);
            }

            //添加订单状态操作日志
            $this->orderStatusChange($id);
            //添加操作日志
            $source = array(
                "username" => session("uc_userinfo.name"),
                "admin_id" => session("uc_userinfo.id"),
                "orderid"  => $id,
                "type"     => 0,
                "postdata" => json_encode($data),
                "addtime"  => time()
            );
            D("LogEditorders")->addLog($source);
            $this->ajaxReturn([
                "code" => 200,
                "errmsg" => '保存成功',
                'success_id' => $data['id']
            ]);
        } else {
            $this->ajaxReturn([
                "code" => 0,
                "errmsg" => '保存失败',
                'success_id' => $data['id']
            ]);
        }
    }

    /**
     * 生成家具订单
     * @return void
     */
    public function jjorderup()
    {
        if ($_POST) {
            $id = I("post.id");
            $cpaModel = D("Home/Logic/CpaOrdersLogic");
            //查询是否已经生成过订单
            $count = $cpaModel->findOrderCount($id);
            if ($count > 0) {
                $this->ajaxReturn(array('info'=> "该订单已生成家具订单",'status'=> 0));
            }

            //获取订单信息
            $info = $this->getOrderInfo($id);

            if (empty($info["chk_customer"])) {
                $this->ajaxReturn(array('info'=> "请先编辑订单后再生成家具订单！",'status'=> 0));
            }

            if ($info["lng"] <= 0  || $info["lat"] <= 0) {
                $this->ajaxReturn(array('info'=> "家具订单需要地理坐标,请先编辑订单！",'status'=> 0));
            }

            $cpaId = $cpaModel->getOrderId();
            $data = array(
                "id" => $cpaId,
                "order_id" => $id,
                "on" => 1,
                "time_real" => time(),
                "time" =>  strtotime($info["time_real"]),
                "name" => $info["name"],
                "sex" => $info["sex"],
                "tel" => $info["tel"],
                "tel_encrypt" => $info["tel_encrypt"],
                "cs" => $info["cs"],
                "qx" => $info["qx"],
                "xiaoqu" => $info["xiaoqu"],
                "ip" => $info["ip"],
                "lng" => $info["lng"],
                "lat" => $info["lat"],
                "huxing" => $info["huxing"],
                "mianji" => $info["mianji"],
                "fengge" => $info["fengge"],
                "lx" => $info["lx"],
                "lxs" => $info["lxs"],
                "yusuan" => $info["yusuan"],
                "step" => I("post.step"),
                "recommend" => I("post.recbusiness"),
                "csos_time" => time(),
                "special_remarks" => $info["text"]
            );

            //计算订单和会员之间的距离
            $result = $cpaModel->setOrderDistance($cpaId,$info["cs"],$info["lng"],$info["lat"]);

            if (count($result) == 0) {
                $data["on"] = 3;
            }

            $i = $cpaModel->addOrder($data);
            if ($i !== false) {
                //添加订单电话进电话表
                $data = array(
                    "orderid" => $cpaId,
                    "tel8" => $info["tel8"]
                );
                $cpaModel->addSafeTel($data);

                //添加进日志表
                $logdata = array(
                    "order_id"=>$cpaId,
                    "add_time"=>time()
                );
                D('Home/Orders')->addJiaJuOrderLog($logdata);

                $this->ajaxReturn(array('info'=> "家具订单生成成功！",'status'=> 1));
            }
            $this->ajaxReturn(array('info'=> "家具订单生成失败！",'status'=> 0));
        }
    }

    /**
     * 定单审核状态
     * @return void
     */
    public function orderstatus()
    {
        if ($_POST) {
            $orders = D("Home/Orders");
            $csosModel = D("OrderCsosNew");

            $status = str_replace('remark_','',I('post.status',''));

            $time = time();
            switch ($status) {
                case 1:
                    //次新单
                    $data["on"] = 0;
                    $data["on_sub"] = 9;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case 2:
                    //待定
                    $data["on"] = 2;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case 3:
                    //有效未分配
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case 4:
                    //分单
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 1;
                    break;
                case 6:
                    //赠单
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 2;
                    break;
                case 8:
                    //无效单
                    $data["on"] = 5;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case 97:
                    //压单
                    $data["on"] = 97;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case 98:
                    //暂时无效
                    $data["on"] = 98;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
            }

            $data["remarks"] = I("post.sub_status");
            //查询订单信息
            $info = $this->getOrderInfo(I('post.id',''));

            //审核的时候先验证 , 数据是否填写(之前仅页面上js验证)
            $ordersLogic = new OrdersLogicModel();
            $check = $ordersLogic->checkOrdersInfo(I('post.'),$info);
            if ($check['code'] != 200) {
                $this->ajaxReturn(['code' => 400, 'errmsg' => $check['errmsg'], 'err_field' => $check['err_field']]);
            }

            $orderLogic = new OrdersLogicModel();

            //进行订单信息验证
            if (($message = $orderLogic->valiOrder($info, $status)) !== false) {
                $this->ajaxReturn(['errmsg' => $orderLogic->valiOrder($info, $status), 'code' => 404]);
            }

            if ($status == 97 && empty($info["visitime"])) {
                $this->ajaxReturn(['errmsg' => '压单状态必须填写待定时间！', 'code' => 404]);
            }

            //扫单审核为有效单 发布订单时间 字段time 修改为当前审核有效时间
            if ($info["on"] == 0 && $info["on_sub"] == 8 && in_array($status, [3, 4, 5, 6, 7])) {
                $data["time"] = $time;
            }

            //无效审核为有效单 发布订单时间 字段time 修改为当前审核有效时间
            if ($info["on"] == 5 && in_array($status, [3, 4, 5, 6, 7])) {
                $data["time"] = $time;
            }

            //没有下次联系时间的 待定单 审核为 有效 修改time订单发布时间为当前审核时间
            if (empty($info["visitime"]) && $info["on"] == 2 && in_array($status, [3, 4, 5, 6, 7])) {
                $data["time"] = $time;
            }

            //添加分配上限
            $allotStatus = $orderLogic->saveOrderAllotNum(I("post.id"),I("post.allot_num"));
            //获取最新的订单可分配数
            if($allotStatus){
                $info['allot_num'] = I("post.allot_num");
            }

            $data['lasttime'] = $time;
            $data['chk_customer'] = session('uc_userinfo.id');

            $result = $orders->editOrder(I("post.id"),$data);
            if ($result !== false) {
                //获取客服信息
                $kfInfo = D("Adminuser")->findKfInfo(session("uc_userinfo.id"));
                //记录操作统计表
                $csosData = array(
                    "order_id" => I("post.id"),
                    "order_on" => $data["on"],
                    "order_on_sub" => $data["on_sub"],
                    "order_on_sub_wuxiao" => $data["on_sub_wuxiao"],
                    "order_new_type" => $data["on"] == 2?2:1,
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "kftype" => $kfInfo["kftype"],
                    "kfgroup" =>  $kfInfo["kfgroup"],
                    "user_name" => session("uc_userinfo.name"),
                    "lasttime" => $time
                );

                //记录操作统计表
                $csos_new = $csosModel->getCsosInfo(I("post.id"));

                if (count($csos_new) > 0) {
                    //订单已审有效，但未分配
                    //已审核分配的订单
                    //依照谁审核有效算谁的原则
                    if (($info["on"] == 4 && $info["type_fw"] == 0 && in_array($status,array(3,4,5,6,7))) || ($info["on"] == 4 && in_array($info["type_fw"],array(1,2))) || $info["on"] == 99) {
                        unset($csosData['user_id'], $csosData['user_uid'], $csosData['kftype'], $csosData['kfgroup'], $csosData['user_name'], $csosData['lasttime']);
                        if (in_array($status,array(5,7))) {
                            //删除已分配装修公司
                            D("OrderInfo")->delOrderInfo(I("post.id"));
                        }
                    }elseif($csos_new["order_on"] == 4 && $info["type_fw"] != 0 && $status == 8) {
                        //以审有效已分配，审核为无效
                        //删除已分配装修公司
                        D("OrderInfo")->delOrderInfo(I("post.id"));
                    }elseif ($info["on"] == 97 && $data["on"] != 4  &&  ($csos_new["order_on"] == 4 )) {
                        //压单变成非有效单
                        unset($csosData['user_id'], $csosData['user_uid'], $csosData['kftype'], $csosData['kfgroup'], $csosData['user_name']);
                    }
                    $csosModel->editCsos(I("post.id"),$csosData);
                } else {
                    if ($status == 97) {
                        //压单情况下，csos默认订单为有效
                        $csosData["order_on"] = 4;
                    }

                    //添加新记录
                    $csosData["addtime"] = $time;
                    $csosModel->addCsos($csosData);
                }

                $this->orderStatusChange($info["id"],$data["on"],$data["on_sub"],$data["on_sub_wuxiao"]);

                //添加审单日志
                $logData = array(
                    "orderid" => $info["id"],
                    "old_on" => $info["on"],
                    "new_on" => $data["on"],
                    "old_on_sub" => $info["on_sub"],
                    "new_on_sub" => $data["on_sub"],
                    "old_on_sub_wuxiao" => $info["on_sub_wuxiao"],
                    "new_on_sub_wuxiao" => $data["on_sub_wuxiao"],
                    "old_type_fw" => $info["type_fw"],
                    "new_type_fw" => $data["type_fw"],
                    "old_type_zs_sub" => $info["type_zs_sub"],
                    "new_type_zs_sub" => $data["type_zs_sub"],
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "old_name" => $csos_new["user_name"],
                    "name" => session("uc_userinfo.name"),
                    "time" => $time,
                );
                $csosModel->addLog($logData);
                if ((I("post.sub_status") !== "") && ($info["remarks"] != I("post.sub_status"))) {
                    $save = array(
                        'order_id' => $info["id"],
                        'remark_time' => date('Y-m-d H:i:s', time())
                    );
                    D('LogOrderRemarkTime')->addLogOrderRemarkTime($save);
                }

                // 如果审核为分单则保存到redis，计划任务执行订单提醒
                if ($status == 4) {
                    import('Library.Org.RedisLibrary.RedisLibrary');
                    $redis = new \RedisLibrary();
                    if (!empty($redis)) {
                        $redis->rpush(C("ZHIJIAN_ORDER_NOTICE_CACHE_KEY"), I("post.id"));
                        $redis->expire(C("ZHIJIAN_ORDER_NOTICE_CACHE_KEY"), 7200);
                        $redis->close();
                    }
                }

                if ($status == 97) {
                    //压单情况下将压单推送到压单池
                    $logic = new OrdersLogicModel();
                    $logic->pushPauseOrderPool($info);
                }

                $this->ajaxReturn(array('errmsg'=> "订单审核成功！",'code'=> 200,'info' => $status));
            }

            $this->ajaxReturn(array('errmsg'=> "订单审核失败！",'code'=> 404));
        }
    }

    /**
     * 获取订单距离数据
     */
    public function getOrderDistance()
    {
        if ($_POST) {
            $cs = I("post.cs");
            $zuobiao = I("post.zuobiao");

            if (empty($zuobiao)) {
                $this->ajaxReturn(array('errmsg'=> "请填写业主坐标",'code'=> 404));
            }

            $zuobiao = array_filter(explode(",", $zuobiao));
            $lng = $zuobiao[0];
            $lat = $zuobiao[1];
            $companyList = [];

            //获取该订单所在城市的的会员公司
            $result = $this->getCompanyList($cs,$lng,$lat);

            foreach ($result[0] as $value) {
                foreach ($value["child"] as $v) {
                    $companyList[$v["id"]] = $v["distance"];
                }
            }

            foreach ($result[1] as $value) {
                foreach ($value["child"] as $v) {
                    $companyList[$v["id"]] = $v["distance"];
                }
            }

            $this->ajaxReturn(array('errmsg'=> "操作成功！",'code'=> 0,'data' => $companyList));
        }
    }

    /**
     * [getOrdersList 获取订单列表]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @param  string $order            [排序]
     * @param  string $each             [每页查询]
     * @return [type]                   [description]
     */
    private function getOrdersList($id = 0,$cs = 0, $xiaoqu = '', $ip = '', $tel_encrypt = '', $time_start = '', $time_end = '',  $time_real_start = '', $time_real_end = '', $nf_time_start = '', $nf_time_end = '', $on = false, $on_sub = false, $type_fw = false, $remarks = false, $openeye_st = false, $op_uid = false, $order = 'time_real DESC', $each = '10',$isactivity,$source_in,$area,$fen_customer = false,$lxs){
        import('Library.Org.Util.Page');
        $db = D('Orders');
        //查询活动发单位置
        $list = D("Activity")->getActivityIds();
        $ids = array();
        foreach ($list as $key => $value) {
            if ($value["source_id"] != 0) {
                $source = array_filter(explode(",",$value["source_id"]));
                $ids = array_merge($ids,$source);
                foreach ($source as $k => $val) {
                    $active[$val] = $value["name"];
                }
            }
        }

        $count = $db->getOrdersListCountJoinOrderPool($id, $cs, $xiaoqu, $ip, $tel_encrypt, $time_start, $time_end,  $time_real_start, $time_real_end, $nf_time_start, $nf_time_end, $on, $on_sub, $type_fw, $remarks, $openeye_st, $op_uid,$isactivity,$ids,$source_in,$area,$fen_customer,$lxs);

        $Page       = new \Page($count,$each);
        $result['page'] = $Page->show();
        $result['list'] = $db->getOrdersListJoinOrderPool($id, $cs, $xiaoqu, $ip, $tel_encrypt, $time_start, $time_end,  $time_real_start, $time_real_end, $nf_time_start, $nf_time_end, $on, $on_sub, $type_fw, $remarks, $openeye_st, $op_uid, $order, $Page->firstRow,$Page->listRows,$isactivity,$ids,$source_in,$area,$fen_customer,$lxs);


        foreach ($result['list'] as $key => $value) {
            if (in_array($value["source"],$ids)) {
                $result['list'][$key]['sourceMark'] = 1;
                $result['list'][$key]['source_remark'] = $active[$value["source"]];
            }
        }


        return $result;
    }

    /**
     * 保存订单
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function applyedit()
    {
        if ($_POST) {
            $model = D("Orders");
            $post  = I("post.");
            $data['orders_id'] = $post["orders"];
            $data['apply_reason'] = $post["reason"];
            $data["apply_time"] = time();
            $data["apply_id"] = session("uc_userinfo.id");
            if ($model->addOrdersApplyEdit($data)) {
                //添加操作日志
                $logData = array(
                    "remark" => session("uc_userinfo.name")."申请修改订单".$post["orders"]."信息",
                    "action_id" =>  $post["orders"],
                    "logtype" => "applyedit",
                    "info"=>$data
                );
                D('LogAdmin')->addLog($logData);

                $code = 200;
            } else {
                $msg = $model->getError();
            }
            $this->ajaxReturn(array('errmsg'=> $msg,'code'=> $code));
        }

    }
    // +----------------------------------------------------------------------
    // |------------------------------分割线-----------------------------------
    // +----------------------------------------------------------------------
    /**
     * 参数设置
     * @return void
     */
    public function config()
    {
        //查询订单设置的配置信息
        $result = D("Options")->getOptionByGroup("order");
        $options = [];
        foreach ($result as $key => $value) {
            $options[$value["option_name"]] = $value["option_value"];
            /*if ($value["option_name"] == "open_order_customer") {
                $options[$value["option_name"]] = array_flip(array_filter(explode(",", $value["option_value"])));
            }*/

            if ($value["option_name"] == "time_step") {
                $options[$value["option_name"]] = json_decode($value["option_value"],true);
            }
        }

        //获取当前会员城市历史订单
        $logic = new OrdersLogicModel();
        $count = $logic->getOrderHistoryCount();
        $this->assign("count",$count);
        $this->assign("options",$options);
        $this->display();
    }

    /**
     * 排版管理
     * @return void
     */
    public function scheduling()
    {
        //获取客服角色列表
        $result = D("Adminuser")->getDepartmentUidById(array(22));
        if (in_array(session("uc_userinfo.uid"),array(30,31,104))) {
            //客服师长,客服团长查看获取自己的管辖人员ID
            $roles = D("RbacNodeGroup")->getRoleGroupInfoByRoleId(session("uc_userinfo.uid"));
            $roles = array_filter(explode(",",$roles["role_id"]));
            if (count($roles) == 0) {
                 $this->_error("没有相关管辖数据,请在角色组管理中设置");
            }
            //获取客服列表
            if (session("uc_userinfo.uid") == 30) {
                //师长
                $kfmanager = session("uc_userinfo.id");
            } elseif (session("uc_userinfo.uid") == 31) {
                //团长
                $userInfo = D("Adminuser")->findUserInfoById(session("uc_userinfo.id"));
                $kfgroup = $userInfo["kfgroup"];
            }
            $res = D("Adminuser")->getKFListByUid($roles,$kfmanager,$kfgroup);

            foreach ($res as $key => $value) {
                $ids[] = $value["id"];
            }

            $ids[] = session("uc_userinfo.id");
        }

        //获取客服排班
        $list = $this->getSchedulingList($result["roles"],I("get.date"),$ids);
        //获取最近一星期的操作日志
        $end = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $start = strtotime("-1 week",$end);
        $log = D("LogAdmin")->getLogListByLogType("scheduling",date("Y-m-d H:i:s",$start),date("Y-m-d 23:59:59",$end));
        $this->assign("log", $log);
        $this->assign("list",$list);
        $this->display();
    }

    public function schedulingUp()
    {
        if ($_POST) {
            $id = I("post.id");
            $name = I("post.name");
            $scheduling = I("post.data");
            $month = I("post.date");
            if (empty($month)) {
                $month = date("Y-m");
            }

            $firstDay = strtotime(date("Y-m-01",strtotime($month)));
            $lastDay = mktime(23,59,59,date("m",$firstDay),date("t",$firstDay),date("Y",$firstDay));

            //删除原纪录
            D("UserScheduling")->delSchedulingInfo($id,date("Y-m-d",$firstDay),date("Y-m-d",$lastDay));

            //获取当月日历
            $dayCount = date("t",strtotime($month));

            for ($i = 0; $i < $dayCount ; $i++) {
                $date[] = date("Y-m-d",strtotime("+$i day",$firstDay));
            }

            foreach ($scheduling as $key => $value) {
                $data[] = array(
                    "user_id" => $id,
                    "status" => $value,
                    "time" => time(),
                    "date" => $date[$key]
                );
            }

            if (count($data) > 0) {
                $i = D("UserScheduling")->addALLScheduling($data);
                if ($i !== false) {
                     //添加操作日志
                    $logData = array(
                        "remark" => session("uc_userinfo.name")."编辑了".$month." ".$name."的排班",
                        "action_id" => $id,
                        "logtype" => "scheduling"
                    );
                    D('LogAdmin')->addLog($logData);
                    $this->ajaxReturn(array("status"=>1));
                }
            }
            $this->ajaxReturn(array("status"=>0,"errmsg"=>"设置失败！"));
        }
    }

    /**
     * 订单设置
     * @return void
     */
    public function orderconfigup()
    {
        if ($_POST) {
            $CronapiService  =  new  CronapiServiceModel();

            $model = D("Options");
            $group = "order";
            //查询订单设置的配置信息
            $result = D("Options")->getOptionByGroup($group);
            $data = I("post.");
            //验证数据
            $reg = '/^\d+$/';
            if (!preg_match($reg, $data["new_order_interval"])) {
                $this->ajaxReturn(array("code"=>404,"errmsg"=>"新订单时间间隔设置错误！"));
            }
            if (!preg_match($reg, $data["new_order_count"])) {
                $this->ajaxReturn(array("code"=>404,"errmsg"=>"个人待处理新订单峰值！"));
            }
            if (!preg_match($reg, $data["unfinished_order_count"])) {
                $this->ajaxReturn(array("code"=>404,"errmsg"=>"个人未完成订单量峰值设置错误！"));
            }

            if (!preg_match($reg, $data["intval_time_up"]) || !preg_match($reg, $data["intval_time_down"])) {
                $this->ajaxReturn(array("code"=>404,"errmsg"=>"自动派单间隔时间设置错误！"));
            }

            if ($data["intval_time_down"] >= 60 || $data["intval_time_up"] >= 60 ) {
                $this->ajaxReturn(array("code"=>404,"errmsg"=>"自动派单间隔时间不能超过60分钟！"));
            }
            if ($data["auto_order_limit"] < 1 && $data["auto_order_limit_switch"] == 1 ) {
                $this->ajaxReturn(array("code"=>404,"errmsg"=>"当天个人最高派单量不能少于1个！"));
            }

            $description = array(
                "new_order_interval" => "获取新订单时间间隔",
                "new_order_count" => "个人待处理新订单峰值",
                "unfinished_order_count" => "个人未完成订单量峰值",
                "unfinished_order_start" => "个人未完成订单量峰值开关开始时间",
                "unfinished_order_end" => "个人未完成订单量峰值开关结束时间",
                "unfinished_order_switch" => "个人未完成订单量峰值开关，0：关，1：开",
                "revoke_rete" => "被撤回订单数计算系数",
                "invalid_rate" => "当天无效单计算系数",
                "effective_rate" => "当天有效单计算系数",
                //"open_order_customer" => "派单客服",
                "auto_order_limit" => "当天个人最高派单量",
                "auto_order_limit_switch" => "当天个人最高派单量开关，0：关，1：开",
                "auto_order_start" => "自动派单开始时间",
                "auto_order_end" => "自动派单结束时间",
                "auto_order_switch" => "自动派单开关",
                "auto_intval_time" => "自动派单间隔时间开关",
                "intval_time_up" => "自动派单上午间隔时间",
                "intval_time_down" => "自动派单下午间隔时间",
                "kf_num" => "自动派单客服数量",
                "new_kf_num" => "新客服数量",
                "time_step" => "客服个人每小时派单量",
                "auto_order_start_down" => "下午客服派单开始时间",
                "auto_order_end_down" => "下午客服派单结束时间",
                "auto_order_limit_up" => "上午客服最高派单"
            );

            foreach ($result as $key => $value) {
                $options[$value["option_name"]] = $value;
            }

            //查询是否有新增项目
            foreach ($data as $key => $value) {
                if ($key == "time_step") {
                    $data[$key] = json_encode($value);
                }
                if (!array_key_exists($key,$options)) {
                    $other[$key] = $data[$key];
                    unset($data[$key]);
                }
            }

            if (count($data) > 0) {
                //修改配置
                foreach ($data as $key => $value) {
                    $i = $model->setOption($key,$value);
                }
            }

            if (count($other) > 0) {
                foreach ($other as $key => $value) {
                    $sub = array(
                        "option_name" => $key,
                        "option_value" => $value,
                        "option_group" => $group,
                        "option_remark" => '客服系统订单设置,'.$description[$key],
                        "autoload" => 'yes'
                    );
                    $all[] = $sub;
                }
                //添加配置项
                $i = $model->addAllOption($all);
            }

            if ($i !== false) {
                //清除OP缓存
                S('Cache:Optionslist',null);
                $json = [];
                $json["err_msg"] = "计划任务修改失败，没有修改项";
                $json["err_code"] = -1;

                //如果设置的间隔时间不一致，更新计划任务
                if (array_key_exists("intval_time",$other) || $options["intval_time_up"]["option_value"] != I("post.intval_time_up") ||  $options["intval_time_down"]["option_value"] != I("post.intval_time_down") || $options["auto_intval_time"]["option_value"] != I("post.auto_intval_time")) {
                    $timeUp = I("post.intval_time_up")*60;
                    $timeDown = I("post.intval_time_down")*60;
                    //计算分小时

                    $minUp = floor($timeUp%86400%3600/60);
                    $minUp =  $minUp == 0?"*":"*/".$minUp;

                    $minDown = floor($timeDown%86400%3600/60);
                    $minDown =  $minDown == 0?"*":"*/".$minDown;

                    $placeholder = "#";
                    if (I("post.auto_intval_time") == 1) {
                        $placeholder = "";
                    }

                    $params = [
                        "name"=>"order/zxdeliveryordersync",
                        "timer"=>"$placeholder$minUp 8-12 * * *|$placeholder$minDown 13-18 * * *",
                        "whoami"=>"root"
                    ];

                    $json["err_msg"] = "服务调用失败";
                    $json["err_code"] = -1;
                    $reJosn = $CronapiService->Cron($params);
                    if($reJosn) {
                      $json =  $reJosn;
                    }

                }
                $this->ajaxReturn(array("code"=>200,"err_msg"=>$json["err_msg"],"err_code"=>$json["err_code"]));
            }
            $this->ajaxReturn(array("code"=>404,"errmsg"=>"设置失败！"));
        }
    }

    /**
     * 城市信息展示页
     * @return void
     */
    public function cityinfo()
    {
        //获取城市信息列表
        $list = D("OrderCityInfo")->getCityInfoList();
        $this->assign("list",$list);
        $this->display();
    }

    public function docking()
    {
        $arr = OP("open_type_list");
        $arr = array_filter(explode(",",$arr));
        //查新订单类型
        $type = 0;
        $status = array(
            "1" => "分单",
            "2" => "赠单"
        );

        $getBegin = I('get.begin', '', 'trim');
        $getEnd = I('get.end', '', 'trim');
        $id = I('get.id', '', 'trim');
        $cs = I('get.cs', '', 'trim');

        $getParamType = I('get.type', '', 'trim');
        if ($getParamType == 2) {
            $type = 1;
            $status[3] = "分没人跟";
            $status[4] = "赠没人跟";

            //如果是已经分配的列表,默认限制只看最近3个月的数据
            if (empty($getBegin) && empty($getEnd)) {
                $getBegin = date('Y-m-d H:i:s', strtotime("-3 month"));
                $getEnd = date('Y-m-d H:i:s', time());
            }
        }
        if($getParamType == 3){
            $type = 2;
            $status = array(
                "5" => "待分配分单",
                "6" => "待分配赠单"
            );
        }
        if($getParamType == 4){
            $type = 4;
        }

        $citys = getUserCitys();

        //获取对接客服列表信息
        $result = D("Adminuser")->getDockingKfList();
        foreach ($result as $key => $value) {
            $kfList[] = array(
                "name" => $value["name"],
                "id" => $value["id"]
            );
        }

        if(!$getParamType){
            $gettype = 1;
        }else{
            $gettype = $getParamType;
        }

        //如果有选择城市,则查出所对应的的区县
        if(I('get.cs')){
            $area = new AreaModel();
            $qxs = $area->getChildByCity(I('get.cs'));
        }

        //获取发单标识
        $inLogic = new OrderSourceInLogicModel();
        $orderType = $inLogic->getSourceInSelect();
        $this->assign('orderType',$orderType);

        $this->assign("gettype",$gettype);
        $this->assign("timediff",$this->timediff);
        $this->assign("citys",$citys);
        $this->assign("qxs",$qxs);
        $this->assign("status",$status);
        $this->assign("kflist",$kfList);
        $this->assign("day_time",strtotime('-30 days'));

        $qx = !empty(I('get.qx'))?I('get.qx'):'';
        //获取对接的列表
        $list = $this->getDockingList($this->city,$type,$id,$cs,$getBegin,$getEnd,I("get.status"),I("get.kf"),I("get.timediff"),$arr,I("get.isactivity"),I("get.source"),I("get.rob_type"),$qx);
        $this->assign("showList",$arr);
        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }

    /**
     * 对接编辑页面
     * @return void
     */
    public function editDocking()
    {
        if ($_POST) {
            //查询订单信息
            $info = $this->getOrderInfo(I("post.id"));
            //过滤不规则字符串
            // $reg = '/[\`~!@#$%^&*\(\)+<>?"{},.\/;\'\"\s]/';
            $reg = '/[\`~!@#$%^&*\(\)+<>?"{},\/;\'\"\s]/';
            $info["xiaoqu"] = preg_replace($reg,"",$info["xiaoqu"]);

            $this->assign("info",$info);
            $this->assign("source_in",$this->source_in);

            //后台转发人数组
            $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
            $names = D("User")->getAdminNamesById($ids['option_value']);
            foreach ($names as $k => $v) {
                $zhuanfaren[] = $v['name'];
            }
            $this->assign("zhuanfaren",$zhuanfaren);
            /*$this->assign("zhuanfaren",$this->zhuanfaren);*/
            //获取订单城市和区县
            $city = D("Quyu")->getCityInfoById($info["cs"]);
            $this->assign("city",$city);
            //户型
            $huxing = D("Huxing")->gethx();
            //预算
            $yusuan = D("Jiage")->getJiage();
            //装修方式
            $fangshi  = D("Fangshi")->getfs();
            //风格
            $fengge  = D("Fengge")->getfg();
            //获取最后审核人信息
            $csos_new = D("OrderCsosNew")->getCsosInfo(I("post.id"));

            //获取 未接通电话短信通知 短信记录
            $logCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_WJTDH);

            //获取 通知业主分配的装修公司 短信记录
            $smsCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_FPGS);

            //获取订单分配信息
            $company = D("OrderInfo")->getOrderComapny($info["id"]);
            $lostFenCompany = [];
            //有分配订单的情况下，查询微信是否发送
            if (count($company) > 0) {
                foreach ($company as $item) {
                    if ($item["on"] == "-1") {
                        $lostFenCompany[] = $item;
                    }
                }

                //获取订单微信发送记录
                $wx = D("LogWxOrdersend")->getWeixinLog($info["id"]);
                if (count($wx) > 0) {
                    $this->assign("wxMark",1);
                }
            }

            // 获取所有的会员公司
            $bold_company_ids = [];

            //获取该订单所在城市的的会员公司
            $result = $this->getCompanyList($info["cs"],$info["lng"],$info["lat"],$info);

            //如果是已分配公司,默认选中
            foreach ($company as $key => $value) {
                $bold_company_ids[] = $value["id"];
                $compnay_id[$value["id"]] = $value["id"];
            }
            $choose_company = [];
            foreach ($company as $key => $value) {
                $choose_company[$value["id"]]['company_id'] = $value["id"];
                $choose_company[$value["id"]]['allot_source'] = $value["allot_source"];
            }

            foreach ($result[0] as $key => $value) {
                foreach ($value["child"] as $k => $val) {
                    $bold_company_ids[] = $val["id"];
                    if (array_key_exists($val["id"],$compnay_id)) {
                        $result[0][$key]["child"][$k]["ischeck"] = 1;
                        if($choose_company[$val["id"]]['allot_source'] == 3){
                            $result[0][$key]["child"][$k]["no_change"] = 1;
                        }else{
                            $result[0][$key]["child"][$k]["no_change"] = 0;
                        }
                    }

                    //签返装修公司不可选中状态
                    if ( $val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 3) {
                        $result[0][$key]["child"][$k]["un_package_change"] = 1;
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(1,3)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_fen"] == 3) {
                            $result[0][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(2,4)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_zen"] == 3) {
                            $result[0][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    } elseif(!isset($val["package_status"]) && $val["classid"] == 6) {
                        $result[0][$key]["child"][$k]["un_package_change"] = 1;
                    }
                }
            }

            foreach ($result[1] as $key => $value) {
                foreach ($value["child"] as $k => $val) {
                    $bold_company_ids[] = $val["id"];
                    if (array_key_exists($val["id"],$compnay_id)) {
                        $result[1][$key]["child"][$k]["ischeck"] = 1;
                    }
                    if($choose_company[$val["id"]]['allot_source'] == 3){
                        $result[1][$key]["child"][$k]["no_change"] = 1;
                    }else{
                        $result[1][$key]["child"][$k]["no_change"] = 0;
                    }

                    //签返装修公司不可选中状态
                    if ($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 3) {
                        $result[1][$key]["child"][$k]["un_package_change"] = 1;
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(1,3)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_fen"] == 3) {
                            $result[1][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(2,4)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_zen"] == 3) {
                            $result[1][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    }  elseif(!isset($val["package_status"]) && $val["classid"] == 6) {
                        $result[1][$key]["child"][$k]["un_package_change"] = 1;
                    }
                }
            }
            //查询上次分配装修公司
            $fenpei_company = D("OrderInfo")->getLastTypeFw($info["id"],$info["cs"]);

            //本地装修公司
            foreach ($fenpei_company as $k => $val) {
                $bold_company_ids[] = $val["id"];
                if ($val["cs"] == $info["cs"]) {
                    $fenpei_now_company[] = $val;
                    unset($fenpei_company[$k]);
                }
            }

            //其他城市
            foreach ($result[1] as $key => $value) {
                foreach ($fenpei_company as $k => $val) {
                    if ($val["cs"] == $key) {
                        $result[1][$key]["fen_company"][] = $val;
                        unset($fenpei_company[$k]);
                    }
                }
            }

            //获取最近30过期的会员信息
            $lostCompany = $this->getLastExpireCompanyList($info["cs"],date("Y-m-d"));

            //将2.0灰色的会员添加到30天过期内
            if (count($result[2]) > 0) {
                foreach ($result[2] as $item) {
                    $lostCompany[] = [
                        "jc" => $item["jc"],
                        "cooperate_mode" => 2
                    ];
                }
            }

            // 装修公司月订单达标状态
            $companyLogic = new CompanyLogicModel();
            $result = $companyLogic->setCompanyMonthOrderReachStatus($result, $info["cs"], $info["cname"]);

            //获取群公布模版信息
            $notice = D("OrderNoticeTemplate")->getCityTemplate($info["cs"]);

            //获取城市信息模版
            $tmp = $this->getCityInfoTmp($info["cs"],true);

            //获取申请记录
            $apply = D("Orders")->getOrderApplyEdit($info["id"],1);

            // 需要加粗的会员公司ID
            $bold_company_ids = array_unique($bold_company_ids);
            $bold_company_ids = $this->getBoldCompanyIds($bold_company_ids);
            $this->assign("boldIds", $bold_company_ids);


            $this->assign("lostFenCompany",$lostFenCompany);
            $this->assign("apply",$apply);
            $this->assign("notice",$notice);
            $this->assign("tmp",$tmp);
            $this->assign("lostCompany",$lostCompany);
            $this->assign("company",$company);
            $this->assign("smsCount",$smsCount);
            $this->assign("fenpei_now_company",$fenpei_now_company);
            $this->assign("nowCompanys",$result[0]);
            $this->assign("otherCompanys",$result[1]);
            $this->assign("logCount",$logCount);
            $this->assign("csos_new",$csos_new);
            //未分配页面 订单出现的时间超过5分钟后，才能现在待分配
            if(I('post.type') != 1){
                unset($this->docking_status[3]);
                unset($this->docking_status[4]);
            }

            //940 删除对接撤回操作
            unset($this->docking_status[2]);

            //如果不是是赠单,删除分单选项
            if (!($info["on"] == 4 && $info["type_fw"] == 2) && !($info["on"] == 4 && $info["type_fw"] == 6)) {
                unset($this->docking_status[5]);
            }
            //如果是赠单 , 审核状态修改(k1.7.0),如果是抢单撤回管理页面,订单状态审核只能撤回
            if(($info["on"] == 4 && $info["type_fw"] == 2) || (I('post.type') == 4)){
                unset($this->docking_status[0]);
                unset($this->docking_status[1]);
                unset($this->docking_status[3]);
                //赠单情况下 , 先不删除 [分单] 操作
                if(!($info["on"] == 4 && $info["type_fw"] == 2)){
                    unset($this->docking_status[5]);
                }

                //如果已经分配结束 , 就不能直接赠送/推送至抢单池
                if((count($company) != $info['allot_num']) && I('post.type') != 4){
                    // feature-972 赠单更换位置
                    $this->docking_status = array_merge($this->docking_status, $this->new_docking_status2);
                }
            }

            //如果是待分配页面的分单 , 添加推送至抢分池(k.1.6.13)
            if($info["on"] == 4 && ($info["type_fw"] == 1 && (I('post.type') == 1)) || ($info["type_fw"] == 5 && (I('post.type') == 3))){
                //如果已经分配结束 , 就不能直接赠送/推送至抢单池,只保留抢分池
                if((count($company) != $info['allot_num'])){
                    $this->docking_status = array_merge($this->new_docking_status,$this->docking_status);
                    unset($this->docking_status[1]);
                    unset($this->docking_status[2]);
                    //未分配列表 去除分没人跟,赠没人跟
                    unset($this->docking_status[3]);
                    unset($this->docking_status[4]);
                }else{
                    //因为添加了新的
                    unset($this->docking_status[0]);
                    unset($this->docking_status[1]);
                }
            }

            //如果是待分配赠单 , 审核状态修改(k1.7.0)
            if($info["on"] == 4 && $info["type_fw"] == 6){
                unset($this->docking_status[0]);
                unset($this->docking_status[1]);
                unset($this->docking_status[3]);
                //如果已经分配结束 , 就不能直接赠送/推送至抢单池
                if(count($company) != $info['allot_num']){
                    $this->docking_status = array_merge($this->new_docking_status,$this->docking_status);
                }
            }

            //如果是抢单撤回页面 , 则只保留 撤回操作
            if(I('post.type') == 4){
                unset($this->docking_status[5]);
            }
            //获取发单标识
            $inLogic = new OrderSourceInLogicModel();
            $orderType = $inLogic->getSourceInSelect();
            $this->assign('orderType',$orderType);

            $this->assign("gettype",I('post.type'));
            $this->assign("status",$this->docking_status);
            $this->assign("keys",$this->keys);
            $this->assign("lf_time",$this->lf_time);
            $this->assign("start_time",$this->start_time);
            $this->assign("shi",$this->shi);
            $this->assign("lxs",$this->lxs);
            $this->assign("fengge",$fengge);
            $this->assign("fangshi",$fangshi);
            $this->assign("yusuan",$yusuan);
            $this->assign("huxing",$huxing);
            $this->assign("applyname",session("uc_userinfo.name"));
            $tmp = $this->fetch("editDocking");
            $this->ajaxReturn(array("code"=>200,"data"=>$tmp,'info'=>$info));
        }
    }

     /**
     * 定单审核状态
     * @return void
     */
    public function orderdockingstatus()
    {
        if ($_POST) {
            $orders = D("Home/Orders");
            $csosModel = D("OrderCsosNew");

            $status = I("post.status");
            $status = str_replace("remark_","",$status);
            $time = time();
            switch ($status) {
                case '0':
                    //分没人跟
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 3;
                    break;
                case '1':
                    //赠没人跟
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 4;
                    break;
                case '2':
                    //撤回
                    $data["on"] = 99;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    $data["fen_customer"] = 0;
                    break;
                case '3':
                    //待分配分单
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 5;
                    break;
                case '4':
                    //待分配赠单
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 6;
                    break;
                case '5':
                    //分单
                    $data["on"] = 4;
                    $data["type_fw"] = 1;
                    break;
                case '6':
                case '8':
                    //推送至抢单池 (6.抢分 8.抢赠)
                    $data["on"] = 4;
                    break;
                case '7':
                    //直接赠送
                    $data["on"] = 4;
                    break;
            }

            if (I("post.sub_status") !== "") {
                $data["remarks"] = I("post.sub_status");
            }

            //查询订单信息
            $info = $this->getOrderInfo(I("post.id"));

            if (empty($info["fen_customer"]) && $status != 2) {
                $data["fen_customer"] = session("uc_userinfo.id");
            }

            if (count($info) == 0 ) {
                $this->ajaxReturn(array('errmsg'=> "该订单不存在！",'code'=> 404));
            }

            //审核的时候先验证 , 数据是否填写(之前仅页面上js验证)
            $ordersLogic = new OrdersLogicModel();
            $check = $ordersLogic->checkDockingOrdersInfo(I('post.'),$info);
            if ($check['code'] != 200) {
                $this->ajaxReturn(['code' => 400, 'errmsg' => $check['errmsg'], 'err_field' => $check['err_field']]);
            }

            $data["lasttime"] = $time;
            $result = $orders->editOrder(I("post.id"),$data);

            if ($result !== false) {
                //删除装修公司反馈信息
                D("Home/Logic/OrderCompanyReviewLogic")->delReviewInfoByOrderId(I("post.id"));

                //获取客服信息
//                $kfInfo = D("Adminuser")->findKfInfo(session("uc_userinfo.id"));
                $this->orderStatusChange($info["id"],$data["on"],$data["on_sub"],$data["on_sub_wuxiao"]);

                //撤回单的时候调整订单状态
                if ($data["on"] == 99) {
                    //记录操作统计表
                    $csosData = array(
                        "order_on" => $data["on"],
                        "order_on_sub" => $data["on_sub"],
                        "order_on_sub_wuxiao" => $data["on_sub_wuxiao"]
                    );
                    $csosModel->editCsos(I("post.id"),$csosData);

                    //删除对接信息
                    D("OrderDocking")->delDocking($info["id"]);
                    //删除抢单池数据
                    $robDb = new OrderRobPoolModel();
                    $robDb->delRobInfo($info["id"]);

                    //删除所有直播信息
                    (new WorkSiteLiveLogicModel())->delWorkSite($info['id']);

                    //删除新回访数据
                    $reviewLogic = new NewReviewLogicModel();
                    $reviewLogic->delReviewInfo($info["id"]);

                    //解除虚号订单绑定
                    $service = new PnpServiceModel();
                    $service->unBindTel($info["id"]);
                }
                $gettype =   I("post.gettype"); //来自待分配页面
                if ($status != 2 && $gettype != 3) {

                    //查询是否有对接信息
                    $count = D("OrderDocking")->getDockingInfoCount($info["id"]);
                    if ($count == 0) {
                        $dockingdata = array(
                            "order_id" => $info["id"],
                            "op_uid" => session("uc_userinfo.id"),
                            "op_uname" => session("uc_userinfo.name"),
                            "time" => time()
                        );
                        D("OrderDocking")->addDocking($dockingdata);
                    }
                }

                //添加审单日志
                $logData = array(
                    "orderid" => $info["id"],
                    "old_on" => $info["on"],
                    "new_on" => $data["on"],
                    "old_on_sub" => $info["on_sub"],
                    "new_on_sub" => $data["on_sub"],
                    "old_on_sub_wuxiao" => $info["on_sub_wuxiao"],
                    "new_on_sub_wuxiao" => $data["on_sub_wuxiao"],
                    "old_type_fw" => $info["type_fw"],
                    "new_type_fw" => $data["type_fw"],
                    "new_type_zs_sub" => $data["type_zs_sub"],
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "name" => session("uc_userinfo.name"),
                    "time" => $time,
                );

                $csosModel->addLog($logData);
                if ((I("post.sub_status") !== "") && ($info["remarks"] != I("post.sub_status"))) {
                    $save = array(
                        'order_id' => $info["id"],
                        'remark_time' => date('Y-m-d H:i:s', time())
                    );
                    D('LogOrderRemarkTime')->addLogOrderRemarkTime($save);
                }
                //勾选了推送抢单池
                if($status == 6 || $status == 8){
                    $robLogic = new RobOrdersLogicModel();
                    $company = $this->getCompanyDetailsList(array($info['cs']),2);
                    $status = $robLogic->addRobPool($info,$company,$status);
                    //添加订单绑定虚号
                    $service = new PnpServiceModel();
                    $service->BindOrderTelX($info["id"]);
                    if($status['code'] != 200){
                        $this->ajaxReturn(array('errmsg'=> $status['errmsg'],'code'=> $status['code']));
                    }
                }else{
                    //删除已分配装修公司
                    D("OrderInfo")->delOrderInfo(I("post.id"));

                }

                //勾选了直接赠送
                if ($status == 7) {
                    //获取可以分配的公司(只接收赠单)
                    $companyLogic = new CompanyLogicModel();
                    $list = $companyLogic->getGiveCompanyListByOrder($info);
                    if(count($list) > 0){
                        $giveLogic = new OrdersGiveCompanyLogicModel();
                        $giveStatus = $giveLogic->giveCompany($info,$list);
                        //添加订单绑定虚号
                        $service = new PnpServiceModel();
                        $service->BindOrderTelX($info["id"]);
                        if ($giveStatus['code'] == 404) {
                            $this->ajaxReturn(array('errmsg'=> $giveStatus['errmsg'],'code'=> 404));
                        }
                    }
                }
                $this->ajaxReturn(array('errmsg'=> "订单审核成功！",'code'=> 200));
            }

            $this->ajaxReturn(array('errmsg'=> "订单审核失败！",'code'=> 404));
        }
    }

    /**
     * 订单分单操作
     * @param  string $value [description]
     * @return void
     */
    public function orderchange()
    {
        if ($_POST) {
            $oldComids = $oldComids = $order_info = [];

            try {
                M()->startTrans();
                //装修公司验证
                $logic = new OrdersLogicModel();
                $reviewLogic = new OrderReviewLogicModel();
                $newReviewLogic = new NewReviewLogicModel();
                $companys = I("post.companys");
                $type = I("post.type");
                $type_fw = $logic->checkFenCompanyInfo($companys);

                //订单信息验证
                //订单信息
                $order_info = $logic->checkFenOrderInfo(I("post.id"),$type_fw);

                //获取常规公司和老签返公司和新签返公司
                $result = $logic->getComapnyClassified($companys);
                $normal = $result["normal"]; //常规
                $old_qian = $result["old_qian"]; //老签返
                $new_qian = $result["new_qian"]; //新签返
                $all_company = $result["all"]; //全部

                //获取订单分配信息
                $order_fen_info = $logic->getOrderFenComapny(I("post.id"));

                //已分配的公司ID
                $oldComids = array_column($order_fen_info, "id");
                //新分配的公司ID
                $nowComids = array_column($companys, "company_id");

                //常规会员业务逻辑处理（1.0 1.0标杆）
                $logic->handlingNormalBusiness($normal,$order_info,$order_fen_info);

                //新签返会员业务逻辑处理（2.0 2.0标杆）
                $logic->handlingNewQianBusiness($new_qian,$order_info,$order_fen_info);

                //老签返业务逻辑处理
                $logic->handlingOldQianBusiness($old_qian,$order_info,$order_fen_info);

                //订单量房处理
                $logic->setOrderCompanyReview($all_company,$order_info["id"]);

                //回访业务处理
                //订单转入回访池(未分配和待分配的分单)
                if(($type == 3 || $type == 1) && ($type_fw == 1)) {
                    $reviewLogic->insertToReview(I("post.id"),I("post.lftime"));
                }

                $reviewCs = $newReviewLogic->getReviewCityByCity($order_info['cs']);
                if (!empty($reviewCs)) {
                    //新增新回访
                    if(!empty($order_info['review_time'])){
                        $reviewCs["mianji_min"] = floatval($reviewCs["mianji_min"]);
                        $reviewCs["mianji_max"] = floatval($reviewCs["mianji_max"]);

                        $mianji_condition = 1;
                        if (is_numeric($order_info["mianji"])) {
                            // 最小面积限制
                            if ($order_info["mianji"] < $reviewCs["mianji_min"]) {
                                $mianji_condition = 2;
                            }

                            // 最大面积限制
                            if ($reviewCs["mianji_max"] > 0 && $order_info["mianji"] > $reviewCs["mianji_max"]) {
                                $mianji_condition = 2;
                            }
                        }

                        if ($mianji_condition == 1) {
                            $newReviewLogic->addReviewInfo($order_info["id"], $order_info['review_time']);
                        }
                    }
                } else {
                    //删除新回访数据
                    $newReviewLogic->delReviewInfo($order_info["id"]);
                }

                //订单逻辑处理
                //更新订单状态，分/赠单算分单
                $data = array(
                    "on" => 4,
                    "type_fw" => $type_fw,
                    "lasttime" => time(),
                    "fen_customer" => empty($order_info["fen_customer"])?session("uc_userinfo.id"):$order_info["fen_customer_id"]
                );

                //如果分配的时候是分单，添加结算标识为有效
                if ($order_info["on"] == 4 && $type_fw == 1) {
                    $data["is_settlement"] = 1;
                }

                (new OrdersModel())->editOrder($order_info["id"],$data);
                // 修改订单已读状态
                $logic->updateOrderComReadAll($order_info["id"], $order_info["order2com_allread"]);

                //对接业务处理
                //查询是否有对接信息
                $count = D("OrderDocking")->getDockingInfoCount($order_info["id"]);
                if ($count == 0) {
                    $data = array(
                        "order_id" => $order_info["id"],
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    D("OrderDocking")->addDocking($data);
                }

                //添加操作日志
                $source = array(
                    "username" => session("uc_userinfo.name"),
                    "admin_id" => session("uc_userinfo.id"),
                    "orderid"  => $order_info["id"],
                    "type"     => $type_fw,
                    "postdata" => json_encode($data),
                    "addtime"  => time()
                );
                D("LogEditorders")->addLog($source);

                $csosModel = new OrderCsosNewModel();
                //添加审单日志
                $logData = array(
                    "orderid" => $order_info["id"],
                    "old_on" => $order_info["on"],
                    "new_on" => $order_info["on"],
                    "old_on_sub" => $order_info["on_sub"],
                    "new_on_sub" => $order_info["on_sub"],
                    "old_on_sub_wuxiao" => $order_info["on_sub_wuxiao"],
                    "new_on_sub_wuxiao" => $order_info["on_sub_wuxiao"],
                    "old_type_fw" => $order_info["type_fw"],
                    "new_type_fw" => $type_fw,
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "name" => session("uc_userinfo.name"),
                    "time" => time(),
                );
                $csosModel->addLog($logData);

                M()->commit();
            } catch (\Exception $e) {
                M()->rollback();
                $this->ajaxReturn(["error_msg" => $e->getMessage() ,"error_code" => $e->getCode()]);
            }

            try {
                //其他辅助功能
                //消息推送处理
                //获取 通知业主分配的装修公司 短信记录
                $sms_status = 2;
                $smsCount = D("LogSmsSend")->getOrderSendLogCount($order_info["id"],1);
                if ($smsCount  == 0) {
                    //自动发送短信
                    $result = $this->sendOrderSms($order_info["id"]);
                    if ($result) {
                        $sms_status = 1;
                    }
                }

                // QZMSG 装修公司新订单通知
                $newComids = array_diff($nowComids, $oldComids);
                if (count($newComids) > 0) {
                    $msgService = new MsgServiceModel();
                    $linkparam = "?order_id=" . $order_info["id"];
                    $msgService->sendCompanyMsg(MsgTemplateCodeEnum::ORDER_NEW, $newComids, $linkparam, $order_info["id"], "订单分配");
                }
                // 1336 销售中心驾驶舱-基础数据处理 加入订单处理队列
                OrderQueueLogicModel::addQueueInfo($order_info["id"], $newComids);

                //友盟推送处理
                //获取device_token
                $devidetokens = $logic->getDeviceToken($newComids);
                if(!empty($devidetokens)){
                    $logic->sendUmeng($devidetokens,I("post.id"),'1','您有新的装修订单，立即查看~',$newComids);
                }

                //添加订单绑定虚号
                $service = new PnpServiceModel();
                $service->BindOrderTelX($order_info["id"]);

                //获取订单分配信息
                $company = D("OrderInfo")->getOrderComapny($order_info["id"]);

                $this->ajaxReturn(array('error_code'=> 0,"data"=>$company,"info"=> $sms_status ,"error_msg"=>"订单分配成功！ "));

            } catch (\Exception $e) {
                $this->ajaxReturn(["error_msg" => '分配业务已完成，推送业务异常，请重新推送' ,"error_code" => $e->getCode()]);
            }
        }
    }

    /**
     * 订单状态改变
     * @param  [type] $orderid       [订单ID]
     * @param  [type] $on            [订单状态]
     * @param  [type] $on_sub        [订单子状态]
     * @param  [type] $on_sub_wuxiao [订单无效子状态]
     * @return void
    */
    public function orderStatusChange($orderid,$on,$on_sub,$on_sub_wuxiao)
    {
        if (empty($orderid)) {
            die();
        }
        $orders = D("Home/Orders");

        //修改订单的状态
        $data = array(
            "chk_customer" => session("uc_userinfo.id")
        );

        if (!empty($on)) {
            $data["on"] = $on;
        }

        if (!empty($on_sub_wuxiao)) {
            $data["on_sub_wuxiao"] = $on_sub_wuxiao;
        }

        if (!empty($on_sub)) {
            $data["on_sub"] = $on_sub;
        }

        $orders->editOrder($orderid,$data);

        //获取订单信息
        $orderInfo = $orders->findOrderInfo($orderid);
        //获取订单状态数据
        $orderStatusChange = D("OrdersStatusChange");
        $orderStatusInfo = $orderStatusChange->findOrderStatus($orderid,$orderInfo["on"],$orderInfo["on_sub"],$orderInfo["on_sub_wuxiao"]);

        if (count($orderStatusInfo) > 0) {
            //添加orders_status_change
            $data = array(
                "user_id" => session("uc_userinfo.id"),
                "user_user" =>  session("uc_userinfo.name"),
                "time_add" =>time()
            );

            $orderStatusChange->editOrderStatus($orderStatusInfo["orderid"],$data);
        } else {
            //添加orders_status_change
            $data = array(
                "orderid" => $orderInfo["id"] ,
                "on" => $orderInfo["on"],
                "on_sub" =>$orderInfo["on_sub"],
                "user_id" => session("uc_userinfo.id"),
                "user_user" =>  session("uc_userinfo.name"),
                "cs" => $orderInfo["cs"],
                "time_add" =>time()
            );
            $orderStatusChange->addOrderStatus($data);
        }

        //获取上一次订单的日志信息
        $log = D("Home/LogOrderSwitchstatus");
        $lastLog = $log->getLastOrderLog($orderInfo["id"]);

        //订单状态改变记录表
        $data = array(
            "orderid" => $orderInfo["id"],
            "last_on" => $lastLog["on"],
            "last_on_sub" => $lastLog["on_sub"],
            "last_on_sub_wuxiao" => $lastLog["on_sub_wuxiao"],
            "on" => $orderInfo["on"],
            "on_sub" => $orderInfo["on_sub"],
            "on_sub_wuxiao" => $orderInfo["on_sub_wuxiao"],
            "last_user_id" => $lastLog["user_id"],
            "user_id" => session("uc_userinfo.id"),
            "last_name" =>  $lastLog["name"],
            "name" => session("uc_userinfo.name"),
            "addtime" => time()
        );
        $log->addLog($data);
    }

    /**
     * 赠单不能生成新单
     * @return void
     */
    public function ordertonewchange()
    {
        if ($_POST) {
            if (I("post.text") == "") {
                $this->ajaxReturn(array('errmsg'=> "请填写原因",'code'=> 404));
            }
            $order = D("Orders");
            $data = array(
                "order_to_new_remak" => I("post.text"),
                "order_to_new" => 2
            );
            $i = $order->editOrder(I("post.id"),$data);
            if ($i !== false) {
                $this->ajaxReturn(array('code'=> 200));
            }
            $this->ajaxReturn(array('errmsg'=> "操作失败！",'code'=> 404));
        }
    }

    /**
     * 赠单生成新单
     * @return void
     */
    public function ordernftoneworder()
    {
        if ($_POST) {
            //查询订单信息
            $info = $this->getOrderInfo(I("post.id"));
            if ('4' != $info['on'] || !in_array($info['type_fw'],array('2','4')) ) {
                $this->ajaxReturn(array('errmsg'=> "只有赠单才允许生成新单!",'code'=> 404));
            }

            if (!empty($info['from_old_orderid'])) {
                $this->ajaxReturn(array('errmsg'=> "本订单已经是生成的订单!",'code'=> 404));
                  }

                  if ( '0' != ($info['order_to_new'])) {
                    $this->ajaxReturn(array('errmsg'=> "本订单已经被处理过了,不能够生成新订单!",'code'=> 404));
                  }

            //处理新订单的基本数据，过滤掉无关的数据
            $newInfo = $info;
            unset($newInfo["id"]);
            unset($newInfo['source']);
            unset($newInfo['tel8']);
            $newInfo["tel_encrypt"] = order_tel_encrypt($info["tel8"]);
            $newInfo["customer"] = session("uc_userinfo.id");
            $newInfo["on"] = 0;
            $newInfo['type_fw'] = 0;
            $newInfo['time_real'] = time();
            $newInfo['on_sub'] = 10;
            $newInfo['time'] = time();
            $newInfo['remarks'] = "赠送单再访生成";
            $newInfo['source_type'] = 5;
            $newInfo['lasttime'] = time();
            $newInfo['from_old_orderid'] = $info["id"];
            $newInfo["id"] =  date('Ymd'). sprintf("%05d%03d", time()%86400, mt_rand(1,1000));
            $newInfo['tel'] = $info["tel_sensitive"];
            // var_dump($newInfo["id"] );die();
            $orders = D("Orders");
            $i = $orders->addOrder($newInfo);

            if ($i !== false) {
                //更新原订单标识
                $data = array(
                        "order_to_new" => 1
                );
                $orders->editOrder($info["id"],$data);

                //订单电话表插入数据
                $data = array(
                        "orderid" =>$newInfo["id"] ,
                        "tel8" => $info["tel"]
                );
                $orders->addTelEncrypt($data);

                //添加订单状态改变日志logorderStatusChange
                $this->orderStatusChange($newInfo["id"],0,10);

                //添加原订单的电话数据给新订单
                //获取最近一天的原订单电话记录
                $logTel = D("LogTelcenterOrdercall");
                $date = date("Y-m-d", strtotime("-1 day",strtotime(date("Y-m-d"))));
                $logs = $logTel->getOrderLastLogOneDay($info["id"],$date);

                if (count($logs) > 0) {
                    foreach ($logs as $key => $value) {
                        unset($value["id"]);
                        $value["orderid"] = $newInfo["id"];
                        $all[] = $value;
                    }
                    //添加电话记录
                    $logTel->addAllLog($all);
                }
                $this->ajaxReturn(array('errmsg'=> "新订单生成,订单号码： ".$newInfo["id"] ,'code'=> 200));
            }
            $this->ajaxReturn(array('errmsg'=> "生成新单失败!",'code'=> 404));
        }
    }

    /**
     * 发送微信
     * @return void
     */
    public function sendwx()
    {
        //查询订单信息
        $info = $this->getOrderInfo(I("post.id"));
        if ($info["on"] != 4 && !in_array($info["type_fw"],array(1,2) )) {
            $this->ajaxReturn(array("code"=>404,"errmsg"=>"订单尚未分配,请审核后通知装修公司"));
        }

        $wechat_compnay = array_filter(explode(",",I("post.companys")));
        //发送装修公司
        if (count($wechat_compnay) > 0) {
            $weixin = A("Home/Orderweixin");
            $result = $weixin->send_order_to_compnay($wechat_compnay,$info["id"], 2);
            $this->ajaxReturn(array('errmsg'=> empty($result)?"微信推送成功": $result,'code'=> 200));
        }
        $this->ajaxReturn(array('errmsg'=> '请选择装修公司','code'=> 404));
    }

    /**
     * 发送短信
     * @param  string $value [description]
     * @return void
     */
    public function sendsms()
    {
        $result = $this->sendOrderSms(I("post.id"));
        if ($result) {
            $this->ajaxReturn(array('code'=> 200));
        }
        $this->ajaxReturn(array('errmsg'=> "发送失败",'code'=> 404));
    }

    /**
     * 群公布模板管理
     * @return void
     */
    public function template()
    {
        $list = D("OrderNoticeTemplate")->getTemplateList();
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 群公布模板新增/编辑
     * @return void
     */
    public function templateup()
    {
        if ($_POST) {
            $id = I("post.id");
            $city = implode(",", I("post.city"));
            $data = array(
                'title' => I("post.title"),
                'content' => I("post.content"),
                'city' => empty($city)?"":$city
            );
            if (!empty($id)) {
                $i = D("OrderNoticeTemplate")->editTemplate($id,$data);
            } else {
                $i = D("OrderNoticeTemplate")->addTemplate($data);
            }
            if ($i !== false) {
                $this->ajaxReturn(array('code'=> 200));
            }
            $this->ajaxReturn(array('errmsg'=> "操作失败",'code'=> 404));
        } else {
            $id = I("get.id");
            if (!empty($id)) {
                $info = D("OrderNoticeTemplate")->getTemplateInfo($id);
                $info["city"] = array_filter(explode(",",$info["city"]));
                $this->assign("info",$info);
            }
            //获取城市信息
            $api = A("Home/Api");
            $citys = $api->getAllCityInfo();
            $this->assign("citys",$citys);
            $this->display();
        }
    }

    /**
     * 复制模版信息
     * @return void
     */
    public function copytemplate()
    {
        if ($_POST) {
            //获取订单信息
            $info = $this->getOrderInfo(I("post.id"));

            //获取订单分配信息
            $company = D("OrderInfo")->getOrderComapny($info["id"]);
            if (count($company) > 0) {
                $comStr = "";
                foreach ($company as $key => $value) {
                    $comStr .= $value["jc"]." ";
                }
            }

            //获取销售信息
            $result = D("Adminuser")->findSellsInfoByCity($info['cs']);

            foreach ($result as $key => $value) {
                if ($value["type"] == 1 && !empty($value["first_name"])) {
                    $sellInfo = $value['first_name'];
                    $sellTel = $value['first_tel'];
                    $qq =  $value["first_qq"];
                    break;
                } elseif ($value["type"] == 2 && !empty($value["first_name"])) {
                    $sellInfo = $value['first_name'];
                    $sellTel = $value['first_tel'];
                    $qq =  $value["first_qq"];
                    break;
                } elseif ($value["type"] == 3 && !empty($value["first_name"])) {
                    $sellInfo = $value['first_name'];
                    $sellTel = $value['first_tel'];
                    $qq =  $value["first_qq"];
                    break;
                } elseif ($value["type"] == 4 && !empty($value["first_name"])) {
                    $sellInfo = $value['first_name'];
                    $sellTel = $value['first_tel'];
                    $qq =  $value["first_qq"];
                    break;
                }
            }

            //公装不显示小区
            if ($info["lx"] == 2) {
                unset($info["xiaoqu"]);
            }

            $site = "http://".$info["bm"].".".C('QZ_YUMING')."/";

            if (!empty($info["lx"])) {
                $lx = $info["lx"] == 1?"家装":"公装";
            }
            // "{订单简介}" => $info['cname']." ".$info["qz_area"]." ".$info["xiaoqu"]." ".$lx,
            //获取模版信息
            $tmpid = I("post.tmpid");
            $tmpInfo = D("OrderNoticeTemplate")->getTemplateById($tmpid);
            $replace = array(
                "{区县}" => $info['cname']." ".$info["qz_area"],
                "{小区}" => $info["xiaoqu"],
                "{装修类型}" => $lx,
                "{公布会员}" => $comStr,
                "{业主要求}" => $info['text'],
                "{联系人}" => mb_substr($sellInfo,0,1,"utf-8"),
                "{联系方式}" => $sellTel,
                "{网址}" => $site,
                "{QQ}" => $qq,
                "{城市}" => $info["cname"],
                "{验证码}" => mt_rand(1,10)."+".mt_rand(1,10)
            );

            foreach ($replace as $key => $value) {
                $reg = '/'.$key.'/';
                $tmpInfo['content'] = preg_replace($reg,$value,$tmpInfo['content']);
            }

            $this->ajaxReturn(array('data'=> $tmpInfo['content'],'code'=> 200));
        }
    }

    /**
     * 删除模版
     * @return void
     */
    public function deltemplate()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("OrderNoticeTemplate")->delTemplate($id);
            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => '群公布模版删除',
                    'logtype' => 'ordernoticetemplate',
                    'action_id' => $id,
                    'action' => __CONTROLLER__."/".__ACTION__
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('code'=> 200));
            }
            $this->ajaxReturn(array('errmsg'=> "操作失败",'code'=> 404));
        }
    }

    /**
     * 签单审核
     * @return void
     */
    public function qiandan()
    {
        //如果是客服，查看全部订单
        $result =$result = D("RbacRole")->getRoleListByDept(22);
        $roles = array_column($result,"role_id");
        $roles = array_flip($roles);

        if (!array_key_exists(session("uc_userinfo.uid"),$roles) && session("uc_userinfo.uid") != 1) {
            //获取管辖城市
            if(count($this->city) > 0){
                $citys = getUserCitys(false);
                $this->assign("citys",$citys);
            } else {
                $this->_error("尚未获取城市权限！");
            }
        }

        //获取签单审核列表
        $result = D("Home/Logic/OrdersLogic")->getQianDanList($this->city,I("get.id"),I("get.begin"),I("get.end"),I("get.status"),I("get.state"),I("get.city"),I("get.company"),I("get.qian"));
        $this->assign("info",$result);
        $this->display();
    }

    /**
     * 签单审核和弹窗界面
     * @return void
     */
    public function qiandanUp()
    {
        if (IS_POST) {
            $post['id'] = I('post.id','trim');
            $post['qiandan_status'] = I('post.status', '', 'intval');
            $post['reason'] = I('post.reason', '');
            $post['qiandan_addtime'] = I('post.addtime', '');
            $post['xiaoqu'] = I('post.xiaoqu', '');
            $post['cname'] = I('post.cname', '');
            $post['qz_area'] = I('post.qz_area', '');
            $post['jine'] = I('post.money', '');
            $post['company'] = I('post.company', '');
            $post['chk_user'] = session("uc_userinfo.name");

            $header = ['Content-type:application/json', 'token:' . session('uc_userinfo.sales_token')];
            $response = curl(C('SALES_API').'/v1/orders/qdup', json_encode($post), $header);
            $this->ajaxReturn($response);
        } else {
            $id = I('get.id');
            //获取订单信息
            $info = $this->getOrderInfo($id);
            if (empty($info)){
                $this->ajaxReturn(['error_code' => 4000600, 'error_msg' => '订单不存在/信息不完整']);
            }

            //获取订单分单信息
            $info['companys'] = D('Home/Logic/OrdersLogic')->getOrderComapny($id);

            //获取城市信息
            $quyu = D('Quyu')->getQuyuList();
            //获取订单城市和区县
            $city = D('Quyu')->getCityInfoById($info['cs']);

            //户型
            $huxing = D('Huxing')->gethx();
            //预算
            $yusuan = D('Jiage')->getJiage();
            //装修方式
            $fangshi = D('Fangshi')->getfs();
            //风格
            $fengge = D('Fengge')->getfg();

            $this->assign('lxs', $this->lxs);
            $this->assign('huxing', $huxing);
            $this->assign('yusuan', $yusuan);
            $this->assign('fangshi', $fangshi);
            $this->assign('fengge', $fengge);
            $this->assign('quyu', $quyu);
            $this->assign('city', $city);
            $this->assign('info', $info);
            //点击编辑弹窗
            if(!empty(I('get.detail'))){
                $tmp = $this->fetch('qiandan_detail');
            }else{
                $tmp = $this->fetch('qiandantmp');
            }
            $this->ajaxReturn(['error_code' => 0, 'error_msg' => '成功','data' => $tmp]);
        }
    }

    public function qiandanFail()
    {
        $id = I("get.id");
        $pageIndex = I("get.p");
        $pageCount = 10;
        $logic = new OrdersLogicModel();
        $list = $logic->getQiandanFailList($id,$pageIndex,$pageCount);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * @return void
     */
    public function getOrderFieldStat()
    {
        if ($_POST) {
            $id = I("post.id");
            //获取字段修改日志
            $list = D("Home/Logic/OrdersLogic")->getOrderFieldStat($id);

            if (count($list) > 0) {
                $this->assign("list",$list);
                $tmp = $this->fetch("orderFieldTmp");

                $this->ajaxReturn(array("status" => 1,"data" => $tmp));
            }
            $this->ajaxReturn(array("status" => 0,"info" => "暂未查到修改信息"));
        }
        $this->ajaxReturn(array("status" => 0,"info" => "参数错误"));
    }

    /**
     * 获取对接的订单列表
     * @param  [type] $cs           [管辖城市]
     * @param  [type] $fen_customer [分单人ID]
     * @param  [type] $id           [订单ID]
     * @param  [type] $other_cs     [城市ID]
     * @param  [type] $begin        [开始时间]
     * @param  [type] $end          [结束时间]
     * @param  [type] $status       [订单状态]
     * @param  [type] $kf           [对接客服ID]
     * @return [type]               [description]
     */
    private function getDockingList($cs,$fen_customer,$id,$other_cs,$begin,$end,$status,$kf,$time_diff,$optn_type_list,$isactivity,$source_in,$rob_type = null,$qx)
    {
        if (in_array(session("uc_userinfo.uid"), $optn_type_list)) {
            //查询活动发单位置
            $result = D("Activity")->getActivityIds();
            $ids = array();
            foreach ($result as $key => $value) {
                if ($value["source_id"] != 0) {
                    $source = array_filter(explode(",", $value["source_id"]));
                    $ids = array_merge($ids, $source);
                }
            }
        }

        $model = D("Orders");
        import('Library.Org.Util.Page');
        switch ($fen_customer) {
            //已分配订单
            case 1:
                $count = $model->getOrderDockingListCount($kf, $id, $other_cs, $status, $begin, $end, $time_diff, $isactivity, $ids,$source_in,$qx);
                if ($count > 0) {
                    $p = new \Page($count, 10);
                    $p->setConfig('prev', "上一页");
                    $p->setConfig('next', '下一页');
                    $show = $p->show();
                    $list = $model->getOrderDockingList($kf, $id, $other_cs, $status, $begin, $end, $time_diff, $p->firstRow, $p->listRows, $isactivity, $ids,$source_in,$qx);
                    foreach ($list as $key => $value) {
                        if ($value["time_diff"] >= 0) {
                            $list[$key]["date_diff"] = timediff($value["time_diff"]);
                        }
                        if (in_array($value["source"], $ids)) {
                            $list[$key]['sourceMark'] = 1;
                        }
                    }
                }
                break;
            case 4:
                //如果选择的是抢单tab列表(k1.7.0)
                $count = $model->getOrderRobListCount($kf, $id, $other_cs, $status, $begin, $end, $isactivity, $ids,$source_in,$rob_type,$qx);
                if ($count > 0) {
                    $p = new \Page($count, 10);
                    $p->setConfig('prev', "上一页");
                    $p->setConfig('next', '下一页');
                    $show = $p->show();
                    $list = $model->getOrderRobList($kf, $id, $other_cs, $status, $begin, $end, $p->firstRow, $p->listRows, $isactivity, $ids,$source_in,$rob_type,$qx);
                    foreach ($list as $key => $value) {
                        if (in_array($value["source"], $ids)) {
                            $list[$key]['sourceMark'] = 1;
                        }
                    }
                }
                break;
            default:

                // k.1.7.5 三方对接流程优化V1.0.0 需求
                // 未分配订单城市会员的判断：订单状态为分单时,只有城市会员>4家||城市会员<=4家&&所属城市不接受质检介入的订单才显示到该页面
                // @author by liuyupeng
                if ($fen_customer == 0) {
                    $citys = D("Quyu")->getKefuCitys();
                    $fen_citys = array_column($citys, "cid");
                }

                //未分配订单/待分配订单
                $result = $model->getDockingList($cs, $fen_customer, $id, $other_cs, $p->firstRow, $p->listRows, $begin, $end, $status, null, null, $isactivity, $ids,$source_in,$qx, $fen_citys);

                foreach ($result as $key => $value) {
                    if (in_array($value["source"], $ids)) {
                        $result[$key]['sourceMark'] = 1;
                    }
                }

                //a.17.06.15 客服后台-客服订单对接管理-未分配订单列表新增数据条数展示
                import('Library.Org.Util.Page');
                $p = new \Page(count($result), count($result));
                $p->setConfig('prev', "上一页");
                $p->setConfig('next', '下一页');
                $show = $p->show();

                //按照城市归类
                foreach ($result as $key => $value) {
                    $city[$value["cs"]]["child"][] = $value;
                }

                //抽取每个城市的最早的一个订单时间
                foreach ($city as $key => $value) {
                    foreach ($value["child"] as $k => $val) {
                        if (empty($lasttime)) {
                            $lasttime = $val["csos_time"];
                        } else {
                            if ($val['csos_time'] < $lasttime) {
                                $lasttime = $val["csos_time"];
                            }
                        }
                    }
                    $city[$key]['lasttime'] = $lasttime;
                }

                //重新排序
                $edition = array();
                foreach ($city as $key => $value) {
                    $edition[] = $value["lasttime"];
                }
                array_multisort($edition, SORT_ASC, $city);

                $list = array();
                foreach ($city as $key => $value) {
                    $list = array_merge($list, $value["child"]);
                }
        }

        if (count($list) > 0) {
            //查询电话重复
            foreach ($list as $key => $value) {
                $ids[] = $value["id"];
            }

            //获取每个订单的电话号码的重复次数
            $result = D('Orders')->getTelnumberRepaetCountByIds($ids);
            foreach ($result as $key => $value) {
                $phoneRepeats[$value['id']] = $value['repeat_count'];
            }

            //获取每个订单的IP的重复次数
            $result = D('Orders')->getIpRepaetCountByIds($ids);
            foreach ($result as $key => $value) {
                $ipRepeats[$value['id']] = $value['repeat_count'];
            }

            //是否显示"修"
            $applyresult = D('Orders')->getOrderApplyEditList($ids);
            foreach ($applyresult as $key => $val) {
                $applyList[$val['orders_id']] = $val['status'];
            }

            foreach ($list as $key => $value) {
                $list[$key]['phone_repeat_count'] = $phoneRepeats[$value['id']];
                $list[$key]['ip_repeat_count'] = $ipRepeats[$value['id']];
                $list[$key]['applystatus'] = $applyList[$value['id']];
            }
        }
        return array("page" => $show, "list" => $list);
    }

    /**
     * 获取订单信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getOrderInfo($id, $is_jiaju = false)
    {
        //查询订单信息
        if($is_jiaju){
            //如果是家具订单 , 则查询家具数据
            $jiajuLogic = new JiajuOrdersLogicModel();
            $info = $jiajuLogic->getJiajuOrdersInfo($id);
            if(count($info) > 0){
                $info = $info[0];
                $info['time_real'] = date("Y-m-d H:i:s",$info['time_real']);
                $info['time'] = date("Y-m-d H:i:s",$info['time']);
                $info['lasttime'] = date("Y-m-d H:i:s",$info['lasttime']);
            }
        }else{
            $order = D('Home/Orders');
            $info = $order->findOrderInfo($id);
        }
        //如果经度纬度存在
        if(!empty(floatval($info["lng"]))&&!empty(floatval($info["lat"]))){
            $info["jingwei"] = $info["lng"].",".$info["lat"];
        }

        if (count($info) == 0) {
            return false;
        }

        if ($info["nf_time"] == "0000-00-00") {
            $info["nf_time"] = "";
        }

        //页面显示微信
        if(!empty($info['wx'])){
            if(strlen($info['wx']) > 6){
                $info['wx_show'] =  substr_cut($info['wx'],3,3);
            }else{
                $info['wx_show'] = '****';
            }
        }
        //计算订单状态
        if($is_jiaju){
            //家具转装修 , 订单状态就是新单
            $info["on"] = 0;
        }else{
            if ($info['on'] == 0 && $info['on_sub'] == 9) {
                $info["orderstatus"] = 1;
            }
            elseif ( $info['on'] == 2){
                $info["orderstatus"] = 2;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 0){
                $info["orderstatus"] = 3;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 1){
                $info["orderstatus"] = 4;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 2){
                $info["orderstatus"] = 6;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 3){
                $info["orderstatus"] = 5;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 4){
                $info["orderstatus"] = 7;
            }
            elseif ( $info['on'] == 5){
                $info["orderstatus"] = 8;
            }elseif ( $info['on'] == 98){
                $info["orderstatus"] = 98;
            }
        }

        if ($info["lng"] > 0 && $info["lat"] > 0) {
            $info["coordinate"] = $info["lng"].",".$info["lat"];
        }

        $exp = array_filter(explode("；",$info["text"]));
        $info["text_array"] = $exp;

        $info["tel_sensitive"] = $info["tel"];
        //获取审核显号
        $admin = getAdminUser();
        $info['apply_tel'] = D('OrdersApplyTel')->getApplyTelByOrdersIdAndApplyId($info['id'], $admin['id']);

        if ($info["apply_tel"]['status'] == 2) {
            $info["tel"] = $info["tel8"];
        }

        $info["lasttime"] = empty($info["lasttime"])?"":$info["lasttime"];

        //获取家居订单
        $logLogic = new LogOrderRouteLogicModel();
        $route = $logLogic->getOrderLog('',$id);
        $info["jiaju_order_id"] = '';
        if($route){
            $info["jiaju_order_id"] = $route['jiaju_order_id'];
        }
        $info['cname_ip'] = $info['cname'];
        //查询ip城市 , 如果没有 , 就用订单城市
        if($info['ip']){
            $city = get_city_by_ip($info['ip']);
            if($city[2]){
                $info['cname_ip'] = $city[2];
            }
        }
        return $info;
    }

    /**
     * 获取当前城市装修公司和相邻的装修公司
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    private function getCompanyList($cs,$lng,$lat,$order_info = [])
    {
        //获取订单城市会员列表
        $list = $this->getCompanyDetailsList(array($cs),2);
        $new_qian = $other = $now =[];

        foreach ($list as $key => $value) {
            if (!array_key_exists($value["qx"], $now)) {
                $now[$value["qx"]]['cid'] = $value["cs"];
                $now[$value["qx"]]['cname'] = $value["cname"];
                $now[$value["qx"]]['area_id'] = $value["qx"];
                $now[$value["qx"]]['qz_area'] = $value["qz_area"];
            }

            if ( (int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0  && (int)$value["lng"] != 0) {
                $value["distance"] = get_distance(array($lng,$lat),array($value["lat"],$value["lng"]),true,0);
            } else {
                $value["distance"] = "无";
            }

			//兼容签单返点会员数据
            // $value['lx'] = $value['lx'] == 1 ? '家装' : ($value['lx'] == 2 ? '公装' : '公装/家装');

            if (in_array($value["cooperate_mode"],[2,4])) {
                //新签返会员 2.0标杆
                $new_qian[] = $value["id"];
            }

            $now[$value["qx"]]["child"][] = $value;
            $now_ids[] = $value["id"];
        }

        $userLogic = new UserLogicModel();

        //获取当前城市的签单返点会员
        $qiandanList = $userLogic->getQianDanUserList($cs,2);

        foreach ($qiandanList as $value) {
            if (!array_key_exists($value["qx"],$now)) {
                $now[$value["qx"]]['area_id'] = $value["qx"];
                $now[$value["qx"]]['qz_area'] = $value["qz_area"];
            }
            $value["qianMark"] = 1;

            if ( (int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0  && (int)$value["lng"] != 0) {
                $value["distance"] = get_distance(array($lng,$lat),array($value["lat"],$value["lng"]),true,0);
            } else {
                $value["distance"] = "无";
            }
            $now[$value["qx"]]["child"][] = $value;
        }

        $amountLogic = new CompanyRoundOrderAmountLogicModel();
        //新签返会员账户信息
        if (count($new_qian) > 0) {
            $result = $userLogic->getUserAccountList($new_qian);

            foreach ($now as $key => $item) {
                foreach ($item["child"] as $k => $value) {
                    $now[$key]["child"][$k]["round_order_number"] = 0;
                    if (array_key_exists($value["id"],$result)) {
                        $now[$key]["child"][$k]["round_order_number"] = $result[$value["id"]]["round_order_number"];
                        $now[$key]["child"][$k]["new_qian_color"] = $result[$value["id"]]["new_qian_color"];
                        $now[$key]["child"][$k]["shen_new_qian_color"] = $result[$value["id"]]["shen_new_qian_color"];
                        //判断是否可以勾选
                        if (isset($result[$value["id"]]["un_new_qian_change"])) {
                            $now[$key]["child"][$k]["un_new_qian_change"] = $result[$value["id"]]["un_new_qian_change"];
                        } else {
                            //再去验证 装修公司多轮单单价配置是否符合 订单要求

                            $status = $amountLogic->checkCompanyAmountOrderInfo($order_info, $result[$value["id"]]['round_order_amount']);

                            if($status['error_code'] != 0){
                                $now[$key]["child"][$k]["un_new_qian_change"] = 1;
                            }
                        }

                        if ($order_info["on"] == 4) {
                            //验证显示的颜色
                            $now[$key]["child"][$k]["new_qian_color"] = $amountLogic->checkCompanyAmountColor($now[$key]["child"][$k],$order_info, $result[$value["id"]]['round_order_amount']);
                            //如果不可勾选并且显示灰色，显示到30天过期会员内
                            if ($result[$value["id"]]['round_order_amount']["round_order_number"] == 0 && $now[$key]["child"][$k]["new_qian_color"] == 2 && $now[$key]["child"][$k]["un_new_qian_change"] == 1 ) {
                                $lost_company[] = $now[$key]["child"][$k];
                                unset($now[$key]["child"][$k]);
                            }
                        }

                    }
                }

            }
        }

        //获取相邻城市
        $result = D("OrderCityRelation")->getRelationCity($cs);
        if (count($result) > 0) {
            $new_qian = [];
            foreach ($result as $key => $value) {
                if ($cs != $value["cid"]) {
                    $adjacentCity[] = $value["cid"];
                }
            }

            if (count($adjacentCity) > 0) {
                //相邻城市会员
                $result = $this->getCompanyDetailsList($adjacentCity,2);

                foreach ($result as $key => $value) {
                    if (!array_key_exists($value["cs"],$other)) {
                        $other[$value["cs"]]["cid"] = $value["cs"];
                        $other[$value["cs"]]["cname"] = $value["cname"];
                    }

                    if ( (int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0  && (int)$value["lng"] != 0) {
                        $value["distance"] = get_distance(array($lng,$lat),array($value["lat"],$value["lng"]),true,0);
                    } else {
                        $value["distance"] = "无";
                    }


                    if ($value["cooperate_mode"] == 2) {
                        //新签返会员
                        $new_qian[] = $value["id"];
                    }

                    //兼容签单饭店会员数据
                    $value['lx'] = $value['lx'] == 1 ? '家装' : ($value['lx'] == 2 ? '公装' : '公装/家装');

                    $other[$value["cs"]]["child"][] = $value;
                }

                //获取相邻城市的签单返点会员
                $userLogic = new UserLogicModel();
                $qiandanList = $userLogic->getQianDanUserList($adjacentCity,2);

                foreach ($qiandanList as $value) {
                    if (!array_key_exists($value["cs"],$other)) {
                        $other[$value["cs"]]["cid"] = $value["cs"];
                        $other[$value["cs"]]["cname"] = $value["cname"];
                    }
                    $value["qianMark"] = 1;
                    if ( (int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0  && (int)$value["lng"] != 0) {
                        $value["distance"] = get_distance(array($lng,$lat),array($value["lat"],$value["lng"]),true,0);
                    } else {
                        $value["distance"] = "无";
                    }
                    $other[$value["cs"]]["child"][] = $value;
                }
            }

            //新签返会员账户信息
            if (count($new_qian) > 0) {
                $result = $userLogic->getUserAccountList($new_qian);

                foreach ($other as $key => $item) {
                    foreach ($item["child"] as $k => $value) {
                        $other[$key]["child"][$k]["round_order_number"] = 0;
                        if (array_key_exists($value["id"],$result)) {
                            $other[$key]["child"][$k]["round_order_number"] = $result[$value["id"]]["round_order_number"];
                            $other[$key]["child"][$k]["shen_new_qian_color"] = $result[$value["id"]]["shen_new_qian_color"];
                            //判断是否可以勾选
                            if (isset($result[$value["id"]]["un_new_qian_change"])) {
                                $other[$key]["child"][$k]["un_new_qian_change"] = $result[$value["id"]]["un_new_qian_change"];
                            } else {
                                //再去验证 装修公司多轮单单价配置是否符合 订单要求
                                $status = $amountLogic->checkCompanyAmountOrderInfo($order_info, $result[$value["id"]]['round_order_amount']);
                                if($status['error_code'] != 0){
                                    $other[$key]["child"][$k]["un_new_qian_change"] = 1;
                                }
                            }

                            //验证显示的颜色
                            $other[$key]["child"][$k]["new_qian_color"] = $amountLogic->checkCompanyAmountColor($other[$key]["child"][$k],$order_info, $result[$value["id"]]['round_order_amount']);
                            //如果不可勾选并且显示灰色，显示到30天过期会员内
                            if ($result[$value["id"]]['round_order_amount']["round_order_number"] == 0 && $now[$key]["child"][$k]["new_qian_color"] == 2 && $now[$key]["child"][$k]["un_new_qian_change"] == 1 ) {
                               unset($now[$key]["child"][$k]);
                            }
                        }
                    }
                }
            }
        }

        //添加装修公司接单要求


        return array($now,$other,$lost_company);
    }

    /**
     * 签单小区历史
     * @return [type] [description]
     */
    public function history()
    {
        //查询小区历史签单公司
        $history = $this->orderHistory(I("get.xiaoqu"),I("get.cs"));
        $this->assign("list",$history);
        $this->display();
    }

    /**
     * 获取装修公司详细信息
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    private function getCompanyDetailsList($cs,$on,$companys = [],$getGiftOrder = ''){
        $companys = D("User")->getCompanyDetailsList($cs,$on,$companys,$getGiftOrder);

        //给公司数据添加设置接单区域
        $comLogic = new CompanyLogicModel();
        $companys = $comLogic->setCompanyDeliverArea($companys);

        //获取公司下一份合同
        $companyContract = $comLogic->getNextContractByCompany($companys);
        foreach ($companys as $key => $value) {
            //计算到期时间
            $offset = (strtotime($value["end"]) - strtotime(date("Y-m-d")))/86400+1;

            //如果没有下一份合同,则要显示到期提醒(788)
            if ($offset <= 15 && empty($value["start_time"]) && !isset($companyContract[$value['id']])) {
                $companys[$key]["end_time"] = $offset;
            }

            //合同开始时间如果大于本月1号显示新
            if ($value["start"] >= date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")))) {
                $companys[$key]["newMark"] = 1;
            }

            if ($value["cooperation_type"] != 1) {
                $companys[$key]["cooperation_name"] = $comLogic->cooperationType[$value["cooperation_type"]];
            }
            $ids[] = $value["id"];

            // 此处处理质检人员，需要查看的装修公司备注信息
            $moreText[] = $companys[$key]['lx'] = $value['lx'] == 1 ? '家装' : ($value['lx'] == 2 ? '公装' : '公装/家装');
            $moreText[] = $companys[$key]['lxs'] = $value['lxs'] == 1 ? '新房' : ($value['lxs'] == 2 ? '旧房' : '新房/旧房');
            $moreText[] = $companys[$key]['contract'] = $value['contract'] == 1 ? '半包' : ($value['contract'] == 2 ? '全包' : '半包/全包');
            $companys[$key]['fen_mianji'] = empty($value['fen_mianji']) ? '' : $moreText[] = '>=' . $value['fen_mianji'] . 'm²';
            $inverseCompnay = $comLogic->trueUserNameByIds($value['other_id']);
            $companys[$key]['inverse'] = $inverseCompnay;
            !empty($inverseCompnay) ? ($moreText[] = $inverseCompnay) : null;
            $notDeliverArea = $comLogic->getNotDeliverArea($value['cs'], $value['deliver_area']);
            $companys[$key]['not_area'] = $notDeliverArea ? ($moreText[] = '不接{'.implode(',',array_column($notDeliverArea,'area')).'}单') : '';
            !empty($value['fen_special_needs']) ? $moreText[] = $value['fen_special_needs'] : null;
            $companys[$key]['more_extra'] = implode('、',$moreText);
            unset($moreText);
        }

        //获取装修公司暂停信息
        if (count($ids) > 0) {
            $result = D("UserVip")->getParseContractList($ids);
            foreach ($result as $key => $value) {
                //计算到期时间
                $offset = (strtotime(date("Y-m-d")) - strtotime($value["end_time"]))/86400+1;
                if ($offset <= 15 && empty($value["start_time"])) {
                    $parseList[$value["company_id"]]["parseMark"] = 1;
                }
            }

            foreach ($companys as $key => $value) {
                if (array_key_exists($value["id"],$parseList)) {
                    $companys[$key]["parseMark"] = $parseList[$value["id"]];
                }
            }
        }
        return $companys;
    }

    /**
     * 推送订单至抢单池
     */
    public function pushOrderRobPool(){
        $info = $this->getOrderInfo(I("post.id"));
        $robLogic = new RobOrdersLogicModel();
//        $company = $this->getCompanyDetailsList(array($info['cs']),2);
        //获取当前城市中 , 勾选了订单所在区域的装修公司
        $companyLogic = new CompanyLogicModel();
        $company = $companyLogic->getGiveCompanyListByOrder($info);
        $order_status = 6;
        //$order_status 去抢单池时的状态 , 与编辑页面审核订单状态相同
        if(I("post.type") == 2){
            $order_status = 8;
        }
        $poolStatus = $robLogic->addRobPool($info,$company,$order_status);

        if ($poolStatus["code"] == 200) {
            //添加订单绑定虚号
            $service = new PnpServiceModel();
            $service->BindOrderTelX($info["id"]);
        }

        $this->ajaxReturn($poolStatus);
    }

    /**
     * 获取历史签单小区信息
     * @param  [type] $xiaoqu [description]
     * @return [type]         [description]
     */
    private function orderHistory($xiaoqu,$cs)
    {
        if (!empty($xiaoqu)) {
            //获取分词
            $server = new ElasticsearchServiceModel();
            $result = $server->analyzeWord($xiaoqu);
            $fxList[] = $xiaoqu;
            if (count($result) > 0) {
                $fxList = array_merge($fxList,$result);
            }

            //查询小区签单历史
            $result = D("Orders")->getQianDanHistory($fxList,$cs);
            if (count($result) > 0) {
               return count($result);
            }
        }
        return 0;
    }

    private function getLastExpireCompanyList($cs,$date)
    {
        $model = new UserModel();
        $lostCompany = $model->getLastExpireCompanyList($cs,$date);

        foreach ($lostCompany as $key => $value) {
            $offset = (strtotime($date) - strtotime($value["end"]))/86400+1;
            $lostCompany[$key]["day"] = $offset;
        }

        //2.0过期会员
        $result =  $model->getQianLastExpireCompanyList($cs,$date);
        if (count($result) > 0) {
            foreach($result as $item) {
                $offset = ceil((strtotime($date) - $item["end"])/86400)+1;
                $sub = [
                    "jc" => $item["jc"],
                    "id" => $item["id"],
                    "day" => $offset,
                    "end" => $item["end"],
                    "cooperate_mode" => $item["cooperate_mode"],
                ];
                $lostCompany[] = $sub;
            }
        }
        return $lostCompany;
    }

    /**
     * 发送订单分配后通知业主的短信
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function sendOrderSms($id)
    {
        //查询订单信息
        $info = $this->getOrderInfo($id);
        if ($info["on"] != 4 && !in_array($info["type_fw"],array(1,2) )) {
            $this->ajaxReturn(array("code"=>404,"errmsg"=>"订单尚未分配,请审核后通知业主"));
        }
        $orderModel = D("Orders");
        //获取业主电话信息
        $tel = $orderModel->findOrderInfoAndTel($info["id"]);

        if (empty($tel["tel8"])) {
            $this->ajaxReturn(array('errmsg'=> "业主联系电话未知，发送失败",'code'=> 404));
        }

        //获取分单装修公司
        $company = D("OrderInfo")->getOrderComapny($info["id"]);
        foreach ($company as $key => $value) {
            $ids[] = $value["id"];
            $companyJc[] = $value['jc'];
        }

        //查询装修公司接单报备信息,装修公司网店填写的电话优先
        $jdbbList = D("User")->getJdbbList($ids);
        $str = "";
        foreach ($jdbbList as $key => $value) {
            if (empty($value["tel"]) && empty($value["cal"]) &&  empty($value["receive_order_tel1"])) {
                $this->ajaxReturn(array('errmsg'=> "装修公司【 ".$value["jc"]." (".$value["comid"].") 】 未设置接单联系方式,请设置后重新发送！",'code'=> 404));
            }

            $jc = $value["jc"] ? : '装修公司'; //装修公司简称;
            $receive_order_tel1_remark = str_replace(array('总','经理','老板'),'',$value['receive_order_tel1_remark']);
            if (!empty($value["tel"])) {
                $receive_order_tel1 = $value["tel"];
            } elseif (!empty($value["cal"])){
                $receive_order_tel1 = $value["cal"].$value["cals"];
            } else {
                $receive_order_tel1 = $value["receive_order_tel1"];
            }
            $str .= sprintf(" %s;联系人:%s;电话:%s",$jc,$receive_order_tel1_remark,$receive_order_tel1);
        }

        //添加分配装修公司短网址短网址
        $short_url = shortUrl("https://h5.qizuang.com/smscompany?order_id=".$info["id"]);

        $str .= ";网店地址:".$short_url;

        $params[] = $str;
        $smsService = new SmsServiceModel();
        //指定发送短信内容 26/27:装小七-报价
        if (in_array($info['source_in'], [26, 27])) {
            $result = $smsService->sendSms("202007281045",$tel["tel8"],$params);
        }else{
            // $result = $smsService->sendSms("201908211007",$tel["tel8"],$params);
            $result["error_code"] = 0;
            foreach ($companyJc as $v) {
                $sendRes = $smsService->sendSms(MsgTemplateCodeEnum::MSG_ORDER_HAS_CHECK, $tel["tel8"], [$v]);
                if ($sendRes["error_code"] != 0) {
                    $result["error_code"] = 1;
                    break;
                }
            }
        }

        if ($result["error_code"] != 0) {
            return $result["error_msg"];
        }

        //添加后台日志记录
        $orderLogic = new OrdersLogicModel();
        $orderLogic->setOrderSmsLog($id,1,$tel["tel8"]);

        return true;
    }

    /**
     * 获取订单城市信息页模版
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    private function getCityInfoTmp($cs,$flag = false)
    {
        //获取订单的城市信息
        $info = D("OrderCityInfo")->findCityInfo($cs);
        if (count($info) > 0) {
            if ($flag) {
                $this->assign("isDocking",1);
            }
            $this->assign("cityInfo",$info);
        }
        return $this->fetch("cityTmp");
    }

    /**
     * 获取客服中心人员列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSchedulingList($uid,$month,$ids)
    {
        $uid = array_filter(explode(",",$uid));
        if (count($uid) > 0) {
            $result =  D("Adminuser")->getUserListByUid($uid,1);
            foreach ($result as $key => $value) {
               //非离职客服
               if ($value["uid"] != 84) {
                    $list[$value["id"]]["id"] = $value["id"];
                    $list[$value["id"]]["uid"] = $value["uid"];
                    $list[$value["id"]]["role_name"] = $value["role_name"];
                    $list[$value["id"]]["name"] = $value["name"];
                    $list[$value["id"]]["scheduling"] = array();
                    $list[$value["id"]]["workCount"] = 0;
                    $list[$value["id"]]["resetCount"] = 0;
                    $list[$value["id"]]["isedit"] = 1;
                    if (count($ids) > 0 && !in_array($value["id"],$ids)) {
                        $list[$value["id"]]["isedit"] = 0;
                    }
                }
            }

            if (empty($month)) {
                $month = date("Y-m");
            }
            $firstDay = strtotime(date("Y-m-01",strtotime($month)));
            $lastDay = mktime(23,59,59,date("m",$firstDay),date("t",$firstDay),date("Y",$firstDay));
            //获取当月日历
            $dayCount = date("t",strtotime($month));

            for ($i = 0; $i < $dayCount ; $i++) {
                $day = date("Y-m-d",strtotime("+$i day", $firstDay));
                $calendar[$day]["en"] = date("d",strtotime($day));
                $offset = date("w",strtotime($day));
                switch ($offset) {
                    case '0':
                        $calendar[$day]["cn"] = "日";
                        break;
                    case '1':
                        $calendar[$day]["cn"] = "一";
                        break;
                    case '2':
                        $calendar[$day]["cn"] = "二";
                        break;
                    case '3':
                        $calendar[$day]["cn"] = "三";
                        break;
                    case '4':
                        $calendar[$day]["cn"] = "四";
                        break;
                    case '5':
                        $calendar[$day]["cn"] = "五";
                        break;
                    case '6':
                        $calendar[$day]["cn"] = "六";
                        break;
                }
            }

            //获取当月排班数据
            $result =  D("UserScheduling")->getSchedulingList(date("Y-m-d",$firstDay),date("Y-m-d",$lastDay));

            if (count($result)) {
                foreach ($result as $key => $value) {
                    switch ($value["status"]) {
                        case '2':
                            $scheduling[$value["user_id"]]['workCount'] ++;
                            if ($value["uid"] == "2") {
                                $kfList[$value["date"]] ++;
                            }
                            break;
                        case '3':
                            $scheduling[$value["user_id"]]['workCount'] ++;
                            break;
                        case '1':
                            $scheduling[$value["user_id"]]['resetCount'] ++;
                            break;
                    }
                    $scheduling[$value["user_id"]]["date"][$value["date"]] = $value["status"];
                }


                foreach ($list as $key => $value) {
                    if (array_key_exists($value["id"],$scheduling)) {
                        $list[$key]["workCount"] = $scheduling[$value["id"]]["workCount"];
                        $list[$key]["resetCount"] = $scheduling[$value["id"]]["resetCount"];
                        $list[$key]["scheduling"] = $scheduling[$value["id"]]["date"];
                    } else {
                        foreach ($calendar as $k => $val) {
                            $list[$key]["scheduling"][$k] = "";
                        }
                    }
                }
            } else {
                foreach ($list as $key => $value) {
                    foreach ($calendar as $k => $val) {
                        $list[$key]["scheduling"][$k] = "";
                    }
                }
            }

            foreach ($calendar as $k => $val) {
                if (!array_key_exists($k,$kfList)) {
                   $kfList[$k] = 0;
                }
            }

            //重新排序
            $edition = array();
            foreach ($kfList as $key => $value) {
                 $edition[] = $key;
            }
            array_multisort($edition, SORT_ASC, $kfList);
        }

        return array("list" => $list,"calendar" => $calendar,"kfList"=>$kfList);
    }


    /**
     *
     * 把定义的 订单状态 对应的备注文字 转换为一个一维数组
     *
     * @param $statusArr
     * @return array
     */
    private function status2Arr($statusArr) {
        if (!is_array($statusArr)) {
            return [];
        }
        $reArr = [];
        foreach ($statusArr as $key => $value) {
            if (isset($value['child'])) {
                $reArr = array_merge($reArr, $value['child']);
            }
        }
        $reArr = array_unique($reArr);
       return $reArr;
    }

    /**
     * 通过城市id获取小区信息
     */
    public function getcommunitybycity(){
        $city = I('get.cs');
        $xiaoqu = I('get.xiaoqu');
        $xiaoquList =  D('Home/Logic/AuthLogic')->getcommunitybycity($city,$xiaoqu);

        $this->ajaxReturn(array('data'=>$xiaoquList, 'status'=>0));
    }

    /**
     *  如果是无效单，且备注不为“已转家具单”则实时生成家具新单
     */
    private function addJiaJuOrder($orderid){
        //查询是否已导入过，
        $hadlog = D('Home/Orders')->searchDdcLog($orderid);
        if($hadlog){
            return false;
        }
        //获取订单信息
        $orderinfo = D('Home/Orders')->showOrderInfoById($orderid);
        if($orderinfo){
            //导入到家具订单池
            $orderinfo['id'] = 'J'.$orderinfo['order_id'];
            $orderinfo['time_real'] =time();
            $orderinfo['time'] = time();
            $orderinfo['type'] = 2; //审核实时转单
            D('Home/Orders')->addtoJiaJuOrder($orderinfo);
            //添加日志
            $logdata = [];
            $logdata['order_id'] = $orderinfo['order_id'];
            $logdata['add_time'] = time();
            D('Home/Orders')->addJiaJuOrderLog($logdata);
            //添加手机号
            $teldata = [];
            $teldata['orderid'] = $orderinfo['id'];
            $teldata['tel8'] = $orderinfo['tel8']?$orderinfo['tel8']:18022222222;
            D('Home/Orders')->addJiaJuOrderTelInfo($teldata);

            //添加到jiaju_order_pool表数据
            $pooldata = [];
            $pooldata['orderid'] = 'J'.$orderinfo['order_id'];
            $pooldata['time'] = time();
            D('Home/Orders')->addJiaJuOrderPoolInfo($pooldata);

            return true;
        }else{
            return false;
        }

    }

    /**
     * 会员分单统计
     *
     * @return void
     */
    public function orderfp()
    {
        $logicmodel = new OrdersLogicModel();
        $usermodel = new UserModel();

        $cityid = I('get.cs');
        $startime = I('get.start','','strtotime');     //开始时间
        $endtime = I('get.end','','strtotime');        //结束时间
        $comid = I('comid');

        //初始化
        $show = '';              //分页
        $getcomlist = array();  //获取的公司列表
        $countfen = 0;          //分单数量
        $countfenqiang = 0;     //抢单数量
        $countzeng = 0;         //赠单数量
        $countqian = 0;         //签单数量
        $countqianjine = 0;     //签单金额


        if (empty($startime)) {
            $startime = strtotime(date('Y-m-01', time()));
        }
        if (empty($endtime)) {
            $endtime = strtotime(date('Y-m-d', time()));
        }

        //会员状态  -1已过期会员 0代表注册用户 1代表认证状态 2是会员状态 -3是预约会员状态 -4会员暂停状态
        $map = array(
            'on' => array('IN', array(-1, 2, -3, -4)),
            'classid' => 3,
        );

        if($comid){
            $map['_complex'] = array(
                'id' => $comid,
                'jc' => $comid,
                '_logic' => 'or'
            );
        }
        if($cityid){
            $map['cs'] = $cityid;
        }

        //不可按照时间段单独查询，必须与其他项进行组合查询
        if($comid || $cityid){
            $getcomlist = $usermodel->getComidList($map);
        }

        if ($getcomlist) {
            $map = [
                'i.com' => ['IN', array_column($getcomlist, 'id')],
                'i.addtime' => ['BETWEEN', [$startime, $endtime + 86400 - 1]],
            ];

            $count = $logicmodel->getOrdersListCount($map);
            if ($count) {
                //分页
                import('Library.Org.Util.Page');
                $p = new \Page($count, 20);
                $show = $p->show();
                $odr = $logicmodel->getOrdersList($map, $p->firstRow, $p->listRows);
                foreach ($odr as $key => $val) {
                    $info = $logicmodel->getQianDanInfoByComid($val['com'], $startime, $endtime);
                    $odr[$key]['qiandancount'] = $info['qiandancount'];
                    $odr[$key]['qiandanjine'] = $info['qiandanjine'];
                    $countqian = $countqian + $odr[$key]['qiandancount'];
                    $countqianjine = $countqianjine + $odr[$key]['qiandanjine'];
                }

                $countfen = array_sum(array_column($odr,'cntf'));
                $countzeng = array_sum(array_column($odr,'cntw'));
                $countfenqiang = array_sum(array_column($odr,'cntqf'));
                $countqian = array_sum(array_column($odr,'qiandancount'));
                $countqianjine = array_sum(array_column($odr,'qiandanjine'));
            }
        }
        if (isset($odr)) {
            foreach ($odr as &$row) {
                foreach ($getcomlist as $c) {
                    if ($c['id'] == $row['com']) {
                        $row['jc'] = $c['jc'];
                        $row['on'] = $c['on'];
                        break;
                    }
                }
            }
            unset($row);
            // 排序和统计
            $sort = [[], [], []];
            $stat = array(
                'vip' => array('f' => 0, 'w' => 0, 'c' => 0),
                'out' => array('f' => 0, 'w' => 0, 'c' => 0),       // 过期会员
            );

            foreach ($odr as &$row) {
                $sort[0][] = +$row['on'];
                $sort[1][] = +$row['cntf'];
                $sort[2][] = +$row['cntw'];

                $type = +$row['on'] == 2 ? 'vip' : 'out';
                $stat[$type]['c']++;
                $stat[$type]['f'] += $row['cntf'];
                $stat[$type]['w'] += $row['cntw'];
            }
            array_multisort($sort[0], SORT_DESC, $sort[1], SORT_DESC, $sort[2], SORT_DESC, $odr);
        }

        $this->assign("citys", getUserCitys(false));
        //数据信息
        $this->assign('page', $show);
        $this->assign('list', isset($odr) ? $odr : []);
        $this->assign('stat', isset($stat) ? $stat : []);
        $this->assign('countvip', isset($odr) ? count($odr) : 0);
        $this->assign('countfen', $countfen);
        $this->assign('countzeng', $countzeng);
        $this->assign('countfenqiang', $countfenqiang);
        $this->assign('countqian', $countqian);
        $this->assign('countqianjine', $countqianjine);

        $this->display();
    }

    /**
     * 分单详情页
     *
     * @return void
     */
    public function com_order()
    {
        $admin = getAdminUser();

        $comid = I('get.comid','');                    //公司id
        $startime = I('get.start','','strtotime');     //开始时间
        $endtime = I('get.end','','strtotime');        //结束时间
        $orderid = I('get.orderid','');                //订单id
        $fz_type = I('fz_type','','intval');           //订单状态 0 全部 1 分单，2 赠单，3 分单（抢）
        $xiaoqu = I('xq','');                          //小区
        $lx = I('lx','');                              //装修类型
        $fs = I('fs','');                              //装修方式
        $cs = I('cs','');                              //管辖城市
        $lf_status = I('lf_status','');                //量房状态
        $kefu_id = I('kefu_id','');                    //审核客服
        $order_lftime = I('order_lftime', 1);          //按量房时间排序

        if (empty($startime)) {
            $startime = strtotime(date('Y-m', time()));
        }

        if (empty($endtime)) {
            $endtime = strtotime(date('Y-m-d', time()));
        }

        $logic = new OrdersLogicModel();
        $list = $logic->getFenOrZengOrdersList($comid, $startime, $endtime, $orderid, $fz_type, $xiaoqu, $fs , $lx, $cs, $lf_status, $kefu_id, $order_lftime);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);

        $operaters = D('Adminuser')->getKfManagerListByIdAndUid($admin['id'], $admin['uid']);
        $this->assign('operaters', $operaters["list"]);

        $this->assign('fangshi', D('Fangshi')->getfs());
        $this->assign('citys', getUserCitys(false));
        $this->assign('order_lftime', $order_lftime);

        $this->display();
    }

    /**
     * 根据id获取订单信息
     *
     * @return void
     */
    public function orderinfojd()
    {
        $logic = new OrdersLogicModel();
        $orderid = I('post.orderid');
        if (empty($orderid)) {
            $return['error_code'] = 0;
            $return['error_msg'] = '未获取到订单id';
            $this->ajaxReturn($return);
        }
        $info = $logic->getOrderInfoById($orderid);
        if (empty($info)) {
            $return['error_code'] = 0;
            $return['error_msg'] = '未获取到订单id';
        } else {
            $return['error_code'] = 1;
            $return['error_msg'] = '获取成功';
            $return['data'] = $info;
        }
        $this->ajaxReturn($return);
    }


    //会员分单统计导出功能
    public function exportOrderTongji()
    {
        $logicmodel = new OrdersLogicModel();
        $usermodel = new UserModel();

        $cityid = I('get.city');
        $startime = I('get.startime') ? strtotime(I('get.startime')) : '';
        $endtime = I('get.endtime') ? strtotime(I('get.endtime')) : '';
        $comid = I('comid');

        //初始化
        $getcomlist = array();  //获取的公司列表
        $comidlist = array();   //整理后的公司id列表
        $countfen = 0;          //分单数量
        $countzeng = 0;         //赠单数量
        $countqian = 0;         //签单数量
        $countqianjine = 0;     //签单金额


        if(empty($startime)){
            $startime = strtotime(date('Y-m',time()));
        }
        if(empty($endtime)){
            $endtime = strtotime(date('Y-m-d',time()));
        }

        //会员状态  -1已过期会员 0代表注册用户 1代表认证状态 2是会员状态 -3是预约会员状态 -4会员暂停状态
        $map = array(
            'on'    => array('IN', array(-1,2,-3,-4)),
            'classid'=>3,
        );

        if($comid){
            $map['_complex'] = array(
                'id' => $comid,
                'jc' => $comid,
                '_logic' => 'or'
            );
        }
        if($cityid){
            $map['cs'] = $cityid;

        }

        $getcomlist = $usermodel->getComidList($map);

        if($getcomlist){
            foreach ($getcomlist as $key => $val){
                $comidlist[$key] = $val['id'];
            }
            $map    = array(
                'i.com'     => array('IN',  $comidlist),
                'i.addtime' =>array('BETWEEN', array($startime, $endtime +86400-1)),
            );

            //分页
            import('Library.Org.Util.Page');
            $count = $logicmodel->getOrdersListCount($map);
            $odr = $logicmodel->getOrdersList($map,0,$count);

            foreach($odr as $key => $val){
                $info = $logicmodel->getQianDanInfoByComid($val['com'],$startime,$endtime);
                $odr[$key]['qiandancount'] = $info['qiandancount'];
                $odr[$key]['qiandanjine'] = $info['qiandanjine'];
                $countfen = $countfen + $val['cntf'];
                $countzeng = $countzeng + $val['cntw'];
                $countqian = $countqian + $odr[$key]['qiandancount'];
                $countqianjine = $countqianjine + $odr[$key]['qiandanjine'];
            }
        }else{
            $odr = array();
        }
        if ($odr) {
            foreach ($odr as &$row){
                foreach ($getcomlist as $c){
                    if ($c['id'] == $row['com']) {
                        $row['jc']  = $c['jc'];
                        $row['on']  = $c['on'];
                        break;
                    }
                }
            }
            unset($row);
            // 排序和统计
            $sort = array( array(), array(), array() );
            $stat = array(
                'vip'   => array('f' => 0, 'w' => 0, 'c' => 0),
                'out'   => array('f' => 0, 'w' => 0, 'c' => 0),       // 过期会员
            );

            foreach ($odr as &$row) {
                $sort[0][]  = +$row['on'];
                $sort[1][]  = +$row['cntf'];
                $sort[2][]  = +$row['cntw'];

                $type   = +$row['on'] == 2 ? 'vip' : 'out';
                $stat[$type]['c']++;
                $stat[$type]['f'] += $row['cntf'];
                $stat[$type]['w'] += $row['cntw'];
            }
            array_multisort(
                $sort[0], SORT_DESC,
                $sort[1], SORT_DESC,
                $sort[2], SORT_DESC,
                $odr);
        }

        $logicmodel->exportOrderTongji($odr);

    }

    /**
     * 会员分单明细导出功能
     *
     * @throws \Exception
     */
    public function exportOrderMingxi()
    {
        $comid = I('get.comid','');                    //公司id
        $startime = I('get.start','','strtotime');     //开始时间
        $endtime = I('get.end','','strtotime');        //结束时间
        $orderid = I('get.orderid','');                //订单id
        $fz_type = I('fz_type','','intval');           //订单状态 0 全部 1 分单，2 赠单，3 分单（抢）
        $xiaoqu = I('xq','');                          //小区
        $lx = I('lx','');                              //装修类型
        $fs = I('fs','');                              //装修方式
        $cs = I('cs','');                              //管辖城市
        $kefu_id = I('kefu_id','');                    //客服人员
        $lf_status = I('lf_status','');                //量房状态
        $order_lftime = I('order_lftime','1');         //第一次量房时间排序

        if (empty($startime)) {
            $startime = strtotime(date('Y-m', time()));

        }
        if (empty($endtime)) {
            $endtime = strtotime(date('Y-m-d', time()));
        }

        $map['i.addtime'] = array('BETWEEN', array($startime, $endtime + 86400 - 1));
        if ($comid) {
            $map['i.com'] = $comid;
        }
        if ($orderid) {
            $map['i.order'] = $orderid;
        }
        if ($fz_type) {
            if ($fz_type == 3) {
                $map['i.type_fw'] = 1;
                $map['i.allot_source'] = 3;
            } else if ($fz_type == 1) {
                $map['i.type_fw'] = 1;
                $map['i.allot_source'] = ['neq', 3];
            } else {
                $map['i.type_fw'] = $fz_type;
            }
        }
        if ($xiaoqu) {
            $map['o.xiaoqu'] = ['like','%'.$xiaoqu.'%'];
        }
        if ($lx) {
            $map['o.lx'] = $lx;
        }
        if ($fs) {
            $map['o.fangshi'] = $fs;
        }

        // 管辖城市
        if (!in_array(session('uc_userinfo.uid'),getAllCityManager())) {
            $citys = getMyCityIds();
            if (empty($cs)) {
                if (empty($citys)) {
                    $map['o.cs'] = "000000";
                } else {
                    $map['o.cs'] = array("in", $citys);
                }
            } else if (in_array($cs, $citys)) {
                $map['o.cs'] = $cs;
            } else {
                $map['o.cs'] = "000000";
            }
        } else if ($cs) {
            $map['o.cs'] = $cs;
        }

        // 客服(订单归属人)
        if (!in_array(session('uc_userinfo.uid'), [1, 33, 63])) {
            $adminuserLogic = new AdminuserLogicModel();
            $userIds = $adminuserLogic->getChildrenIds(session('uc_userinfo.uid'));
            $userIds[] = session('uc_userinfo.id');

            if (empty($kefu_id)) {
                $map['p.op_uid'] = array("in", $userIds);
            } else if (in_array($kefu_id, $userIds)) {
                $map['p.op_uid'] = $kefu_id;
            } else {
                $map['p.op_uid'] = "-1";
            }
        } else if ($kefu_id) {
            $map["p.op_uid"] = array("EQ", $kefu_id);
        }

        $map2 = [];
        if ($lf_status) {
            $lf_status = $lf_status == 3 ? 0 : $lf_status;
            $map2["t.liangfang_status"] = array("EQ", $lf_status);
        }

        $logic = new OrdersLogicModel();
        $model  = new OrderInfoModel();
        $odr = $model->getFenOrZengOrdersList($map, $map2, 0, 0, $order_lftime);
        $logic->exportOrderMingxi($odr);
    }

    /**
     * 未接通订单
     *
     * @return void
     */
    public function uncall()
    {
        $logic = new OrdersLogicModel();

        //列表数据和分页
        $list = $logic->getOrderUncallList(I('get.cs', ''), I('get.user', ''), I('get.start', ''), I('get.end', ''));

        //获取城市列表
        $citys = A('Home/Api')->getAllCityInfo();

        //获取客服名单列表
        $adminModel = new \Home\Model\AdminuserModel();
        $kflist = $adminModel->getKfList();

        $this->assign(compact('list','citys','kflist'));

        $parse =  parse_url($_SERVER['REQUEST_URI']);
        $this->assign('query',isset($parse['query']) ? $parse['query'] : '');

        $this->display();
    }


    /**
     * 未接通订单导出
     *
     * @return void
     */
    public function exportOrderUncall()
    {
        ini_set('memory_limit','512M');
        try {
            ob_start();

            $herder = ['发布日期', '订单号', '最后修改时间', '城市区县', '手机号', '订单状态', '订单归属人'];
            $fileName = '未接通订单列表';
            $logic = new OrdersLogicModel();

            //列表数据和分页
            $list = $logic->getOrderUncallListNoPage(I('get.cs', ''), I('get.user', ''), I('get.start', ''), I('get.end', ''));
            foreach ($list as $key => $value) {
                $list[$key] = [
                  'time' => $value['time'],
                  'id' => $value['id'],
                  'lasttime' => $value['lasttime'],
                  'cname' => $value['cname'].$value['area'],
                  'tel' => $value['tel'],
                  'status' => getOrderStatus($value['on'], $value['on_sub'], $value['type_fw']),
                  'op_name' => $value['op_name'],
                ];
            }
            $list = array_merge([$herder], $list);

            import('Library.Org.Phpexcel.PHPExcel', '', '.php');
            import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', '', '.php');
            // 设置缓存方式，减少对内存的占用
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $cacheSettings = array('cacheTime' => 300);
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $phpExcel = new \PHPExcel();
            $j = 1;
            foreach ($list as $key => $value) {
                $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
                $n = 65;
                $p = 65;

                foreach ($value as $k => $val) {
                    $char = strtoupper(chr($i));
                    if ($char > 'Z') {
                        $char = strtoupper(chr($n)) . strtoupper(chr($p));
                        if ($char > 'Z') {
                            $n++;
                        }
                        $p++;
                    }
                    //每行的单元格
                    $num = $char . $j;
                    //设置单元格宽度
                    $phpExcel->getActiveSheet()->getColumnDimension($char)->setWidth(20);

                    if (stripos($val, 'http') === 0) {
                        //如果是超链接，则进行超链接设置
                        $phpExcel->getActiveSheet()->setCellValue($num, '点击查看');
                        $phpExcel->getActiveSheet()->getCell($num)->getHyperlink()->setUrl($val);
                        $phpExcel->getActiveSheet()->getStyle($num)->getFont()->setUnderline(\PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        $phpExcel->getActiveSheet()->getStyle($num)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_BLUE);
                    } elseif (stripos($k, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                        //判断是否是时间戳，如果是转换成具体时间
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num, date('Y-m-d H:i:s', $val));
                    } elseif (strlen($val) > 15 && is_numeric($val)) {
                        //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num, $val);
                    } else {
                        $phpExcel->getActiveSheet()->setCellValue($num, $val);
                    }
                    $phpExcel->getActiveSheet()->getStyle($num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $i++;
                }
                $j++;
            }

            ob_end_clean();
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control:must-revalidate, post-check=0, pre-check=0');
            header('Content-Type:application/force-download');
            header('Content-Type:application/vnd.ms-execl');
            header('Content-Type:application/octet-stream');
            header('Content-Type:application/download');;
            header('Content-Disposition:attachment;filename="'.$fileName.'.xlsx"');
            header('Content-Transfer-Encoding:binary');
            $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
            $writer->save('php://output');
        }catch (\Exception $e){
            if($_SESSION['uc_userinfo']['uid'] == 1){
                echo $e->getMessage();
            }
        }
        exit();
    }

    /**
     * 需要加粗的会员公司ID
     * @param  [type] $company_ids [description]
     * @return [type]              [description]
     */
    private function getBoldCompanyIds($company_ids){
        $companyIds = [];
        if (!empty($company_ids)) {
            $list = D("Orders")->getOrderMonthReachCompany($company_ids);
            if (!empty($list)) {
                $companyIds = array_column($list, "id");
            }
        }

        return $companyIds;
    }

    public function directCreateJiajuOrder()
    {
        $id = I('post.id');
        $logic = new JiajuOrdersLogicModel();
        //验证是否创建过家具订单
        $status = $logic->checkZxOrder($id);
        if ($status['error_code'] != 0) {
            $this->ajaxReturn(['status' => 0, 'info' => $status['error_msg']]);
        }
        $status = (new JiajuOrdersLogicModel())->directCreateJiajuOrderByZx($id);
        if ($status['error_code'] != 0) {
            $this->ajaxReturn(['status' => 0, 'info' => $status['error_msg']]);
        } else {
            $this->ajaxReturn(['status' => 1, 'info' => $status['error_msg']]);
        }
    }

    // 订单状态同步
    public function syncOrderComReadStatus(){
        $_token = I("_token");
        $order_id = I("order_id");
        if ($_token == date("YmdHi") && !empty($order_id)) {
            $order2com_allread = 0;
            $orderInfoModel = new OrderInfoModel();
            $total = $orderInfoModel->getOrderReadStatistic();
            if ($total["allot_num"] > 0 && $total["allot_num"] == $total["read_num"]) {
                $order2com_allread = 1;
            }

            $ordersModel = new OrdersModel();
            $res = $ordersModel->editOrder($order_id, ["order2com_allread" => $order2com_allread]);
            exit("执行结束.");
        }
        exit("access denied.");
    }

    public function createJiajuCustomOrder()
    {
        if ($_POST) {
            $id = I("post.id");
            $logic = new JiajuOrdersLogicModel();
            //验证是否创建过家具订单
            $status = $logic->checkZxOrder($id);
            if ($status['error_code'] != 0) {
                $this->ajaxReturn(['status' => 0, 'info' => $status['error_msg']]);
            }
            $logic->createJiajuCustomOrder($id);

        }
    }

    /**
     * 订单撤回操作(公共接口)
     */
    public function orderBackOption(){
        $data = I('post.');
        $order_id = $data['id'];

        //查询订单信息
        $info = $this->getOrderInfo($order_id);
        if (count($info) == 0 ) {
            $this->ajaxReturn(array('errmsg' => "该订单不存在！", 'code' => 404));
        }
        
        //请求服务操作
        $orderServer = new QzorderServiceModel();
        $status = $orderServer->orderRecall($order_id, $this->User['id']);
        if ($status && $status['error_code'] == 0) {
            //如果是分单撤回，渠道订单结算标识改成2 不结算
            if ($info["on"] == 4 && $info["type_fw"] == 1) {
                $data["is_settlement"] = 2;
                (new OrdersModel())->editOrder($order_id,$data);
            }

            //添加撤回原因
            $ordersLogic = new OrdersLogicModel();
            $ordersLogic->addOrderBackRecord($data, $this->User);

            //删除装修公司反馈信息
            $reviewLogic = new OrderCompanyReviewLogicModel();
            $reviewLogic->delReviewInfoByOrderId($order_id);
            //记录操作统计表
            $csosData = [
                "order_on" => 99,
                "order_on_sub" => 0,
                "order_on_sub_wuxiao" => 0
            ];
            $ordersLogic->editOrderCsosNew($order_id, $csosData);
            //删除对接信息
            $ordersLogic->delOrderDocking($order_id);
            //删除抢单池数据
            $ordersLogic->delOrderRob($order_id);

            //删除所有直播信息
            $worksiteLogic = new WorkSiteLiveLogicModel();
            $worksiteLogic->delWorkSite($order_id);

            //删除新回访数据
            $newReviewLogic = new NewReviewLogicModel();
            $newReviewLogic->delReviewInfo($order_id);

            // 删除订单分配落档信息
            $ordersLogic->deleteOrderAllotInfo($order_id);

            //添加审单日志
            $logData = [
                "orderid" => $info["id"],
                "old_on" => $info["on"],
                "new_on" => 99,
                "old_on_sub" => $info["on_sub"],
                "new_on_sub" => 0,
                "old_on_sub_wuxiao" => $info["on_sub_wuxiao"],
                "new_on_sub_wuxiao" => 0,
                "old_type_fw" => $info["type_fw"],
                "new_type_fw" => 0,
                "new_type_zs_sub" => 0,
                "user_id" => session("uc_userinfo.id"),
                "user_uid" => session("uc_userinfo.uid"),
                "old_name" => 0,
                "name" => session("uc_userinfo.name"),
                "time" => time(),
            ];
            $ordersLogic->addCsosNewLog($logData);
            $this->ajaxReturn(['error_code' => 0]);
        } else {
            if (empty($status)) {
                $status = [
                    "error_code" => 1000001,
                    "error_msg" => "网络链接失败"
                ];
            }

            Log::write("CURL请求失败：" . json_encode($status), "ERR");
            $this->ajaxReturn($status);
        }
    }

    /**
     * 获取撤回单撤回原因
     */
    public function getOrderBackReason()
    {
        $order_id = I('post.id');

        $ordersLogic = new OrdersLogicModel();
        $info = $ordersLogic->getOrderBackRecord($order_id);
        if (!empty($info)) {
            $this->ajaxReturn(['error_code' => 0, 'info' => $info]);
        }
        $this->ajaxReturn(['error_code' => 400, 'error_msg' => '未查询到撤销原因']);
    }

    public function pauseorderup()
    {
        if ($_POST) {
            $order_id = I("post.order_id");
            //获取订单的最近一次记录
            $logic = new OrdersLogicModel();
            $info = $logic->getOrderPauseInfo($order_id);

            //获取最后一次计划的信息
            $max = $logic->getOrderMaxPauseInfo($order_id);

            if (count($info) > 0) {
                //修改该记录的处理状态
                $data = [
                    "status" => 1,
                    "updated_at" => time(),
                    "actuala_time" => date("Y-m-d"),
                ];

                $logic->pauseOrderUp($info["id"],$data);
                $msg = '轮拨操作成功!';
                if ($max["step"] == $info["step"]) {
                    $msg = "本次操作为最后一次轮拨操作，请及时修改订单状态或待定时间";
                }

                $this->ajaxReturn(['error_code' => 200, 'error_msg' => $msg]);
            }
        }
        $this->ajaxReturn(['error_code' => 400, 'error_msg' => '该订单还未到压单回访时间']);
    }

    public function xiaoquHistory()
    {
        $xiaoqu = I("get.xiaoqu");
        $cs = I("get.city");

        //查询小区历史签单公司
        $count = $this->orderHistory($xiaoqu,$cs);

        $this->ajaxReturn(['error_code' => 0, 'error_msg' => '',"data" => $count]);
    }

    public function getOrderCount()
    {
        $admin = getAdminUser();
        $type = I("get.type");
        $data = [];
        switch ($type) {
            case 1:
                //当前新订单总数 当前已抢未拨打新单 当前发单量总数 当前被撤回订单数
                //判断是否可获取订单数量简单信息
                if (check_menu_auth('/orders/getordercountbrief/') == true) {
                    $data = $this->getOrderCountBrief();
                    $orderoOgic = new OrdersLogicModel();
                    $data['unfen'] = $orderoOgic->getUnfenCountByUser($admin);
                }
                break;
            case 2:
                //当前未分配订单量 当前新订单总数 可获取新订单总数 当前被撤回订单数 当前人均发单量
                //显示抢单部分需满足以下条件
                //1.查看的是新单，并且是点击tab栏查看的
                //2.有‘查看订单状态指标数字’的权限
                //备注：如果还需要有抢单的需求，还要加上抢单的权限
                if (cookie('order_status_forbidden') == 1 && I('get.status') == 11 && check_menu_auth('/orders/refreshordercount/')) {
                    $data = $this->getRefreshOrderCount($admin['id']);
                }
                break;
        }

        $this->ajaxReturn(['error_code' => 0, 'error_msg' => '',"data" => $data]);
    }
}
