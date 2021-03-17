<?php
// +----------------------------------------------------------------------
// | RemedyLogic 补单逻辑
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\Adminuser;
use app\common\model\db\Company;
use app\common\model\db\Fangshi;
use app\common\model\db\Fengge;
use app\common\model\db\Huxing;
use app\common\model\db\Jiage;
use app\common\model\db\LogEditorders;
use app\common\model\db\LogOrderThirdparty;
use app\common\model\db\OrderCompanyReview;
use app\common\model\db\OrderInfo;
use app\common\model\db\OrderPool;
use app\common\model\db\OrderRobPool;
use app\common\model\db\Orders;
use app\common\model\db\OrderUnremedy;
use app\common\model\db\User;
use app\common\model\db\YxbOrders;
use app\common\model\db\YxbOrdersManage;
use app\common\model\db\YxbOrderSource;
use app\common\model\db\YxbReception;
use app\index\enum\OrderCodeEnum;
use Common\Enums\OrderSource;

use app\common\model\service\MsgService;
use app\common\enum\MsgTemplateCodeEnum;
use Util\Page;

class RemedyOrderLogic
{

    // 获取销售补单列表
    public function getRemedyOrderList($input, $page = 1, $limit = 20){
        $ordersModel = new Orders();
        $count = $ordersModel->getRemedyOrderCount($input);
        $pageobj = new Page($page, $limit, $count);

        $list = [];
        if ($count > 0) {
            $list = $ordersModel->getRemedyOrderList($input, $pageobj->firstrow, $limit);

            foreach ($list as $key => &$item) {
                $item["lftime"] = OrderCodeEnum::getLfTime($item["lftime"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageobj->show()
        ];
    }

   
    /**
     * 订单基本信息
     * @param $order_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderInfo($order_id)
    {
        $order = new Orders();
        //基础数据
        $info = $order
            ->field('id,lasttime last_time,wzd,time_real,cs,qx,ip,tel,wx,is_wx,other_contact,standby_user,dz,name,sex,xiaoqu_type,xiaoqu,lx,lxs,huxing,shi,mianji,fengge,yusuan,fangshi,nf_time
            ,keys,lftime,text,huifan,visitime,customer,chk_customer,fen_customer,type_fw fen_status,lng,lat,type_fw,start')
            ->with(['city', 'area', 'allotNum', 'OrderCityInfo'])
            ->where([['id', '=', $order_id]])
            ->find()
            ->withAttr('xiaoqu_type', function ($value) {
                return OrderCodeEnum::getXiaoquType($value);
            })
            ->withAttr('lx', function ($value) {
                return OrderCodeEnum::getLx($value);
            })
            ->withAttr('lxs', function ($value) {
                return OrderCodeEnum::getLxs($value);
            })
            ->withAttr('huxing', function ($value) {
                return Huxing::getHuxing($value, 'name')['name'];
            })
            ->withAttr('fengge', function ($value) {
                return Fengge::getFengge($value, 'name')['name'];
            })
            ->withAttr('yusuan', function ($value) {
                return Jiage::getYusuan($value, 'name')['name'];
            })
            ->withAttr('fangshi', function ($value) {
                return Fangshi::getFangshi($value, 'name')['name'];
            })
            ->withAttr('keys', function ($value) {
                return OrderCodeEnum::getKeys($value);
            })
            ->withAttr('lftime', function ($value) {
                return !empty($value) ? OrderCodeEnum::getLfTime($value) : '';
            })
            ->withAttr('customer', function ($value) {
                return Adminuser::findInfoById($value)['name'];
            })
            ->withAttr('chk_customer', function ($value) {
                return Adminuser::findInfoById($value)['name'];
            })
            ->withAttr('fen_customer', function ($value) {
                return Adminuser::findInfoById($value)['name'];
            })
            ->withAttr('type_fw', function ($value) {
                return OrderCodeEnum::getOrderStatus(4,0,$value);
            });
        //ip城市
        $info['ip_city'] = !empty($info['city']) ? $info['city']['cname'] : '';
        if (!empty($info['ip'])) {
            $city = get_city_by_ip($info['ip']);
            if (!empty($city[2]) && $city[2] != '') {
                $info['ip_city'] = $city[2];
            }
        }
        //页面显示微信
        if (!empty($info['wx'])) {
            if (strlen($info['wx']) > 6) {
                $info['wx'] = substr_cut($info['wx'], 3, 3);
            } else {
                $info['wx'] = '****';
            }
        }
        //取参数
        if (!empty($info['city'])) {
            $info['cname'] = $info['city']['cname'];
            unset($info['city']);
        }
        if (!empty($info['area'])) {
            $area = $info['area']['qz_area'];
            unset($info['area']);
            $info['area'] = $area;
            unset($area);
        }
        if (!empty($info['allot_num'])) {
            $allot_num = $info['allot_num']['allot_num'];
            unset($info['allot_num']);
            $info['allot_num'] = $allot_num;
            unset($allot_num);
        }
        if (!empty($info['order_city_info'])) {
            $info['banbao'] = $info['order_city_info']['half_price_min'] . '-' . $info['order_city_info']['half_price_max'];
            $info['quanbao'] = $info['order_city_info']['price_min'] . '-' . $info['order_city_info']['price_max'];
            $info['description'] = $info['order_city_info']['description1'];
        } else {
            $info['banbao'] = null;
            $info['quanbao'] = null;
            $info['description'] = null;
        }
        unset($info['order_city_info']);
        return sys_response(0, '', [
            'info' => $info
        ]);
    }

    /**
     * 获取订单关联的会员公司
     * @param $order_id
     * @return array
     */
    public function getOrderCompany($order_id)
    {
        $order = new Orders();
        $order_detail = $order->getOrderInfoById($order_id, 'cs');
        if (empty($order_detail)) {
            return sys_response(4000001);
        }
        $info = new OrderInfo();
        //分配的公司
        $fen_company = $info->getOrderCompany($order_id);
        $returnData = [];
        if (count($fen_company) > 0) {
            foreach ($fen_company as $k => $v) {
                $returnData['fen_company'][$k]['jc'] = $v['jc'];
                $returnData['fen_company'][$k]['company_id'] = $v['com'];
                $returnData['fen_company'][$k]['isread'] = $v['isread'];
            }
        }
        //最近30天过期会员
        $companyDb = new CompanyLogic();
        $returnData['past_company'] = $companyDb->getPastCompany($order_detail['cs']);
        return sys_response(0, '', ['info' => $returnData]);
    }

    public function changeOrder($info)
    {
        $orderDb = new Orders();
        $userDb = new User();
        $order_info = $orderDb->getOrderInfoById($info->order_id, '*', ['allotNum', 'orderCompanyInfo','OrderTel','city','area']);
        if (empty($order_info)) {
            return sys_response(4000001);
        }

        //选择的装修公司
        $companys = $info->company;


        //验证订单状态
        if ($order_info['on'] != 4) {
            return sys_response(4000700, '订单尚未审核有效,审核后再分配！');
        }
        //验证当前订单可分配数
        $noRemedyNum = 0;
        foreach ($order_info['order_company_info'] as $k => $v) {
            if ($v['allot_source'] != 4) {
                $noRemedyNum++;
            }
        }
        if (($noRemedyNum + count($companys)) > $order_info['allot_num']['allot_num']) {
            return sys_response(4000700, '订单最多分配' . $order_info['allot_num']['allot_num'] . '家');
        }
        //验证当前订单是否在抢单池中
        $poolDb = new OrderRobPool();
        $pool = $poolDb->getOrderPoolInfo(['order_id'=>['eq',$order_info['id']]],'order_id,is_rob');

        if($pool['is_rob'] == 1){
            return sys_response(4000800, '订单尚在抢单池中 , 请稍后再试！');
        }

        //获取销售补单的 , 用作后面更新 新的公司
        $old_company = $this->addOrderInfo($order_info['id'], $companys, $info->user);

        //添加量房信息
        $this->orderReviewOption($order_info['id'], $companys,$old_company);
        //同步订单到erp系统
        $this->addYxbOrder($order_info, $companys,$old_company);
        //添加订单操作日志
        $this->addEditOrderLog($order_info['id'],$companys,$info->user);
        //添加三方订单操作日志
        $this->addThirdPartyOrderLog($order_info['id'],$companys,$info->user);

        // 更新订单已读状态
        $this->updateOrderComAllread($order_info["id"], $order_info["order2com_allread"]);

        //签返公司的订单包处理逻辑
        $qianCompany = [];
        $all = array_intersect($companys, $old_company);//保留
        $old_company = array_diff($old_company, $all);
        $companys = array_diff($companys, $all);

        if (count($old_company) > 0) {
            $result = $userDb->getUsersInfo($old_company);
            $result = $result->toArray();
            foreach ($result as $value) {
                if ($value["classid"] == 6) {
                    //签返公司需要增加一个订单
                    $value["return_order_count"] = 1;
                    $qianCompany[$value["id"]] = $value;
                }
            }
        }

        $result = $userDb->getUsersInfo($companys);
        if (count($result) > 0) {
            $result = $result->toArray();
            //签到单返点会员
            foreach ($result as $value) {
                if ($value["classid"] == 6) {
                    //签返公司需要扣除一个订单
                    $value["return_order_count"] = -1;
                    $qianCompany[$value["id"]] = $value;
                }
            }
        }

        if (count($qianCompany) > 0) {
            $userLogic = new UserLogic();
            $userLogic->qianDanCompanyOrder($qianCompany,$order_info["type_fw"],$order_info["id"]);
        }

        // 806 添加新回访数据,如果当前订单城市在回访城市中,需要直接添加新回访
        $reviewLogic = new NewOrderReviewLogic();
        $reviewCs = $reviewLogic->getReviewCityByCity($order_info['cs']);
        if (!empty($reviewCs)) {
            //新增新回访
            if(!empty($info->review_time)){
                $reviewLogic->addReviewInfo($info->order_id, $info->review_time);
            }
        } else {
            //删除新回访数据
            $reviewLogic->delReviewInfo($order_info["id"]);
        }

        // QZMSG 装修公司新订单提醒
        if (count($companys) > 0) {
            $msgService = new MsgService();
            $linkParam = "?order_id=" . $info->order_id;
            $msgService->sendCompanyMsg(MsgTemplateCodeEnum::ORDER_NEW, $companys, $linkParam, $info->order_id, "销售补单");
        }

        // 1336 销售中心驾驶舱-基础数据处理 加入订单处理队列
        OrderQueueLogic::addQueueInfo($info->order_id, $companys);

        return sys_response(0);
    }

    public function saveUnRemedyOrder($orders, $user)
    {
        $save = [];
        //获取当前订单的城市
        $where[] = ['id', 'in', $orders];
        $orders = (new Orders())->getOrdersInfo($where, 'id,cs');
        foreach ($orders as $k => $v) {
            $save[] = [
                'order_id' => $v['id'],
                "op_name" => $user['user_name'],
                "op_uid" => $user['user_id'],
                "cs" => $v['cs'],
                "add_time" => time()
            ];
        }
        $status = (new OrderUnremedy())->addUnRemedy($save);
        if ($status) {
            return sys_response(0);
        } else {
            return sys_response(5000002);
        }
    }

    private function addOrderInfo($order_id,$companys,$user){
        $infoDb = new OrderInfo();
        $where = ['order' => ['eq', $order_id], 'allot_source' => ['eq', 4]];
        $orderInfo = $infoDb->getOrderCompanyInfo($where,"*",["company"])->toArray();

        $saveData = [];
        if (count($orderInfo) > 0) {
            foreach ($orderInfo as $k => $v) {
                foreach ($companys as $kk => $vv) {
                    if ($v['com'] == $vv) {
                        unset($orderInfo[$k]['id']);
                        unset($orderInfo[$k]['opid']);
                        unset($orderInfo[$k]['opname']);
                        $orderInfo[$k]['opid'] = $user['user_id'];
                        $orderInfo[$k]['opname'] = $user['user_name'];
                        $saveData[] = $orderInfo[$k];
                        unset($companys[$kk]);
                    }
                }
            }
        }

        //如果有添加 , 则继续追加
        if (count($companys) > 0) {
            foreach ($companys as $v) {
                $saveData[] = [
                    'com' => $v,
                    'order' => $order_id,
                    'type_fw' => 2,
                    'addtime' => time(),
                    'opid' => $user['user_id'],
                    'opname' => $user['user_name'],
                    'allot_source' => 4,
                ];
            }
        }
        //先删除原有的 , 再添加
        $infoDb->delOrderCompany($where);
        $infoDb->saveAll($saveData);
        //将修改前的 销售操作过的公司返回 , 用作量房表的删除
        return array_column($orderInfo,'com');
    }

    private function orderReviewOption($order_id,$companys,$old_company){
        //对比数据 筛选出要删的/保留/新增的
        $all = array_intersect($companys,$old_company);//保留
        $del = array_diff($old_company,$all);//要删除的
        $add = array_diff($companys,$all);//要新增的
        //量房表操作
        $reviewDb = new OrderCompanyReview();
        $saveData = [];
        //删除
        if(count($del) > 0){
            $where = [
                'comid'=>['in',$del],
                'orderid'=>['eq',$order_id],
            ];
            $reviewDb->delOrderCompanyReview($where);
        }
        //添加
        if (count($add) > 0) {
            foreach ($add as $v) {
                $saveData[] = [
                    "orderid" => $order_id,
                    "comid" => $v,
                    "status" => 0,
                    "time" => time(),
                    "reason" => "",
                    "remark" => "",
                    "lianxi" => 2,
                    "liangfang" => 2,
                    "lf_time" => 0,
                ];
            }
            $reviewDb->saveAll($saveData);
        }
    }

    private function addYxbOrder($order,$companys,$old_company){

        //对比数据 筛选出要删的/保留/新增的
        $all = array_intersect($companys,$old_company);//保留
        $add = array_diff($companys,$all);//要新增的
        $erp_orders_insert = $erp_orders_manage_insert = $reception_insert = [];
        if(count($add) > 0){
            //获取每条装修公司的订单来源
            $sourceDb = new YxbOrderSource();
            $source = $sourceDb->getOrderSource($add)->toArray();
            if(count($source) > 0){
                $source = array_column($source,null,'company_id');
            }
            $add = (new Company())->getUserInfo($add,'id,qc')->toArray();
            foreach ($add as $k => $vv) {
                $order_no = getOrderNo();
                $erp_orders_insert[$k]['order_no'] = $order_no;
                $erp_orders_insert[$k]['company_id'] = $vv['id'];
                $erp_orders_insert[$k]['source'] = !empty($source[$vv['id']])?$source[$vv['id']]['id']:0;
                $erp_orders_insert[$k]['consumer_name'] = $order['name'] . $order['sex'];
                $erp_orders_insert[$k]['consumer_tel'] = $order['tel8'];
                $erp_orders_insert[$k]['house_type'] = !empty($order['huxing']) ? $order['huxing'] : 0;
                $erp_orders_insert[$k]['build_address'] = $order['city']['cname'].$order['area']['qz_area'].$order['xiaoqu'];
                $erp_orders_insert[$k]['qz_order'] = $order['id'];
                $erp_orders_insert[$k]['add_time'] = time();
                $erp_orders_insert[$k]['publish_time'] = strtotime($order['time_real']);
                $erp_orders_insert[$k]['house_area'] = empty($order['mianji'])?'':$order['mianji'];
                //预留
                if (!empty($order['cs'])) {
                    $erp_orders_insert[$k]['city'] = $order['cs'];
                }
                if (!empty($order['area'])) {
                    $erp_orders_insert[$k]['area'] = $order['qx'];
                }
                if(in_array($order['type_fw'],[1,2])){
                    $erp_orders_insert[$k]['type_fw'] = $order['type_fw'];
                }
                if(!empty($order['xiaoqu'])){
                    $erp_orders_insert[$k]['xiaoqu'] = $order['xiaoqu'];
                }
                $erp_orders_manage_insert[$k]['order_no'] = $order_no;
                $erp_orders_manage_insert[$k]['company_id'] = $vv['id'];
                //接单日志
                $reception_insert[$k]['add_time'] =  time();
                $reception_insert[$k]['order_no'] =  $order_no;
                $reception_insert[$k]['contact_name'] =  $vv['qc'];
                $reception_insert[$k]['company_id'] =  $vv['id'];
            }
            //erp订单信息
            (new YxbOrders())->addOrders($erp_orders_insert);
            (new YxbOrdersManage())->addOrders($erp_orders_manage_insert);
            (new YxbReception())->addReceptionLog($reception_insert);
        }

    }

    private function addEditOrderLog($order_id, $data, $user)
    {
        //添加操作日志
        $source = array(
            "username" => $user['user_name'],
            "admin_id" => $user['user_id'],
            "orderid" => $order_id,
            "type" => 2,
            "postdata" => json_encode($data),
            "addtime" => time()
        );
        (new LogEditorders())->addLog($source);
    }

    private function addThirdPartyOrderLog($order_id, $companys, $user)
    {
        $save = [];
        foreach ($companys as $k => $v) {
            $save[] = [
                'order_id' => $order_id,
                'company_id' => $v,
                'type_fw' => 2,
                'op_uid' => $user['user_id'],
                'op_name' => $user['user_name'],
                'op_type' => 1,
                'add_time' => time(),
            ];
        }
        (new LogOrderThirdparty())->addLog($save);
    }

    /**
     * 更新订单已读状态
     * @param $order_id
     * @param $old_status
     */
    private function updateOrderComAllread($order_id, $old_status){
        $order2com_allread = 0;
        $orderInfoModel = new OrderInfo();
        $total = $orderInfoModel->getOrderReadStatistic($order_id);
        if ($total["allot_num"] > 0 && $total["allot_num"] == $total["read_num"]) {
            $order2com_allread = 1;
        }

        if ($order2com_allread != $old_status) {
            $ordersModel = new Orders();
            $ordersModel->updateComAllread($order_id, $order2com_allread);
        }
    }
}