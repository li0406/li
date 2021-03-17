<?php
namespace Home\Model\Logic;
use Home\Model\Db\CompanyPackageModel;
use Home\Model\Db\UmengRecordModel;
use Home\Model\Db\UserCompanyAccountLogModel;
use Home\Model\Db\UserCompanyRankModel;
use Home\Model\OrdersrankModel;
use Home\Model\Db\UserCompanyRoundOrderAmountModel;
use Home\Model\Db\UserCompanyRoundOrderModel;
use Home\Model\Db\UserModel;
use Home\Model\Db\YgjNoticeModel;

/**
 *
 */
class UserLogicModel
{
    public function getQianDanUserList($city_id,$user_type)
    {
        $model = new UserModel();
        $comLogic = new CompanyLogicModel();
        $result = $model->getQianDanUserList($city_id,$user_type);
       
        $list = [];
        foreach ($result as $value) {
            //包的使用状态为不可用
            $value["qiang"] = 0;
            $list[$value["id"]] = $value;
            $ids[] = $value["id"];

            //给公司数据添加设置接单区域
            // 此处处理质检人员，需要查看的装修公司备注信息
            $moreText[] = $list[$value["id"]]['lx'] = $value['lx'] == 1 ? '家装' : ($value['lx'] == 2 ? '公装' : '公装/家装');
            $moreText[] = $list[$value["id"]]['lxs'] = $value['lxs'] == 1 ? '新房' : ($value['lxs'] == 2 ? '旧房' : '新房/旧房');
            $moreText[] = $list[$value["id"]]['contract'] = $value['contract'] == 1 ? '半包' : ($value['contract'] == 2 ? '全包' : '半包/全包');
            $list[$value["id"]]['fen_mianji'] = empty($value['fen_mianji']) ? '' : $moreText[] = '>=' . $value['fen_mianji'] . 'm²';
            $inverseCompnay = $comLogic->trueUserNameByIds($value['other_id']);
            $list[$value["id"]]['inverse'] = $inverseCompnay;
            !empty($inverseCompnay) ? ($moreText[] = $inverseCompnay) : null;
            $notDeliverArea = $comLogic->getNotDeliverArea($value['cs'], $value['deliver_area']);
            $list[$value["id"]]['not_area'] = $notDeliverArea ? ($moreText[] = '不接{'.implode(',',array_column($notDeliverArea,'area')).'}单') : '';
            !empty($value['fen_special_needs']) ? $moreText[] = $value['fen_special_needs'] : null;
            $list[$value["id"]]['more_extra'] = implode('、',$moreText);
            unset($moreText);

        }

        if (count($ids) > 0) {
            //获取分单信息
            $result = $model->getUserOrderPackageInfo($ids,1);
            foreach ($result as $item) {
                $list[$item["company_id"]]["package_status"] = $item["package_status"];
                $list[$item["company_id"]]["use_fen"] = $item["use_status"];
                $list[$item["company_id"]]["fen"] = $item["send_number"];

                if ( $item["package_status"] != 2) {
                    $list[$item["company_id"]]["fen"] = 0;
                }
            }

            //获取赠单信息
            $result = $model->getUserOrderPackageInfo($ids,2);
            foreach ($result as $item) {
                $list[$item["company_id"]]["package_status"] = $item["package_status"];
                $list[$item["company_id"]]["use_zen"] = $item["use_status"];
                $list[$item["company_id"]]["zen"] = $item["send_number"];
                if ( $item["package_status"] != 2) {
                    $list[$item["company_id"]]["zen"] = 0;
                }
            }
        }

        return $list;
    }

    public function getUserOrderPackageInfo($company_id,$package_type)
    {
        $model = new UserModel();
        return $model->getUserOrderPackageInfo($company_id,$package_type);
    }

    public function getNextPackageInfo($company_id,$package_id,$package_type)
    {
        $model = new UserModel();
        return $model->getNextPackageInfo($company_id,$package_id,$package_type);
    }

    public function editPackage($id,$data)
    {
        $model = new UserModel();
        return $model->editPackage($id,$data);
    }

    public function editPackageInfo($id,$data)
    {
        $model = new UserModel();
        return $model->editPackageInfo($id,$data);
    }

    public function qianDanCompanyOrder($qianCompany,$order_id)
    {
        if (count($qianCompany) > 0) {

            foreach ($qianCompany as $value) {
                $result = $this->getUserOrderPackageInfo([$value["company_id"]],$value["type_fw"]);
                if (count($result) > 0) {
                    $qianCompany[$value["company_id"]]["id"] = $result[0]["id"];
                    $qianCompany[$value["company_id"]]["package_status"] = $result[0]["package_status"];
                    $qianCompany[$value["company_id"]]["send_number"] = $result[0]["send_number"];
                    $qianCompany[$value["company_id"]]["total_number"] = $result[0]["total_number"];
                    $qianCompany[$value["company_id"]]["package_info_id"] = $result[0]["package_info_id"];
                    $qianCompany[$value["company_id"]]["use_status"] = $result[0]["use_status"];
                }
            }

            foreach ($qianCompany as $key => $value) {
                if ($value["return_order_count"] == -1) {
                    //扣除订单
                    $send_number = $value["send_number"]+1;
                    $subData= [
                        "send_number" => $send_number
                    ];

                    //订单扣满
                    $offset = $value["total_number"] - $send_number;
                    if ($offset == 0) {
                        //查询当前包的另外一个分包的使用状态
                        $type_fw = $value["type_fw"] == 1?2:1;
                        $nowPackage = $this->getNowPackageInfo($key,$value["id"],$type_fw);
                        //如果已使用，则改变订单包使用状态
                        if ($nowPackage["package_info_status"] == 3) {
                            $data["use_status"] = 3;
                        }
                        $this->editPackage($value["id"],$data);
                        $subData["use_status"] = 3;
                        //查询下是否有一个订单包
                        $nextPackage = $this->getNextPackageInfo($key,$value["id"],$value["type_fw"]);

                        if (count($nextPackage) > 0) {
                            //开启下一个包的使用状态
                            $data = [
                                "use_status" => 2
                            ];
                            $this->editPackage($nextPackage["package_id"],$data);

                            $data = [
                                "use_status" => 2
                            ];
                            $this->editPackageInfo($nextPackage["package_info_id"],$data);
                        }
                    }

                    //订单包如果未使用，开启使用状态
                    if ($value["package_status"] == 1) {
                        $data = [
                            "use_status" => 2
                        ];
                        $this->editPackage($value["id"],$data);
                        //分包的使用状态
                        $subData["use_status"] = 2;
                    }
                    $this->editPackageInfo($value["package_info_id"],$subData);

                    //添加订单和订单包的关联
                    $this->addPackageOrderRelation($value["id"], $order_id,$key);
                } else {
                    //返回已扣除的订单
                    //当前订单包是否可以使用
                    if ($value["package_status"] == 2) {
                        //有剩余包量的情况下，扣去已给的包数量
                        $total_number = $value["total_number"]+1;
                        $subData= [
                            "total_number" => $total_number
                        ];
                        $this->editPackageInfo($value["package_info_id"],$subData);
                    } else {

                        //无可使用订单包
                        //添加总量 +1
                        $subData = [
                            "total_number" => $value["total_number"]+1,
                            "use_status" => 2
                        ];
                        $this->editPackageInfo($value["package_info_id"],$subData);

                        $data = [
                            "use_status" => 2
                        ];
                        $this->editPackage($value["id"],$data);
                    }
                    //删除订单和订单包的关联
                    $this->editPackageOrderRelation($order_id,$key);
                }
            }
        }
    }

    public function addPackageOrderRelation($package_id,$order_id,$company_id)
    {
        $model = new CompanyPackageModel();
        $data = [
            "order_id" => $order_id,
            "company_id" => $company_id,
            "package_id" => $package_id,
            "created_at" => time(),
            "updated_at" => time()
        ];

        return  $model->addPackageOrderRelation($data);
    }

    public function editPackageOrderRelation($order_id,$company_id)
    {
        $model = new CompanyPackageModel();
        $where = [
            "order_id" => ["EQ", $order_id],
            "company_id" => ["EQ",$company_id],
        ];

        $data = [
            "updated_at" => time(),
            "deleted" => 2
        ];

        return  $model->editPackageOrderRelation($where,$data);
    }

    public function getNowPackageInfo($company_id,$package_id,$package_type)
    {
        $model = new UserModel();
        return $model->getNowPackageInfo($company_id,$package_id,$package_type);
    }

    public function getUserAccountList($new_qian)
    {
        $model = new UserModel();
        $result = $model->getUserAccountList($new_qian);
        //获取多轮单单价
        $amountDb = new UserCompanyRoundOrderAmountModel();
        $amountData = $amountDb->getAmountByCompany($new_qian);
        $amountData = array_column($amountData, null, 'company_id');

        //获取装修公司最高单价
        $res = $amountDb->getMaxAmountByCompany($new_qian);
        $maxAccount = array_column($res, "price", 'company_id');

        $list = [];
        foreach ($result as $item) {
            //审核颜色
            $item["shen_new_qian_color"] = 1;
            //2.0会员显示的颜色 默认 1绿色 2.灰色
            $item["new_qian_color"] = 1;
            // 补轮单数量为0继续验证其它逻辑
            if ($item["round_order_number"] <= 0) {
                //轮单费未设置
                //如果没有设置多轮单单价
                if(empty($amountData[$item['user_id']]['gz_price']) && empty($amountData[$item['user_id']]['jg_price']) && empty($amountData[$item['user_id']]['mjmin_price']) && empty($amountData[$item['user_id']]['mjmax_price'])){
                    $item["un_new_qian_change"] = 1;
                    //如果没有轮单费 显示灰色
                    if ($item["account_amount"] <= 0) {
                        $item["new_qian_color"] = 2;
                    }
                }

                //账户余额小于0
                if ($item["account_amount"] <= 0) {
                    $item["un_new_qian_change"] = 1;
                    $item["new_qian_color"] = 2;
                }
            }
            //将账户余额添加至多轮单数据中(用于验证账户余额是否过低)
            $amountData[$item['user_id']]['account_amount'] = $item["account_amount"];
            $amountData[$item['user_id']]['jc'] = $item["jc"];
            $amountData[$item['user_id']]['round_order_number'] = $item["round_order_number"];

            //如果账户余额小于了最大单价
            if ($item["account_amount"] == 0 || $item["account_amount"] < $maxAccount[$item['user_id']]) {
                $item["shen_new_qian_color"] = 2;
            }

            $list[$item["user_id"]] = $item;
            $list[$item["user_id"]]['round_order_amount'] = $amountData[$item['user_id']]?:[];
        }

        return $list;
    }

    public function getNewQianCompanyAccountInfo($company,$order_info)
    {
        $info = [
            "error_code" => 0,
            "error_msg" => ""
        ];

        //查询用户账户状态
        $model = new UserModel();
        $result = $model->getUserAccountList($company);
        //获取多轮单单价
        $amountDb = new UserCompanyRoundOrderAmountModel();
        $amountData = $amountDb->getAmountByCompany($company);
        $amountData = array_column($amountData, null, 'company_id');

        //有新签单会员优先验证订单面试是否是纯数字
        if (empty($order_info['mianji']) || !is_numeric($order_info['mianji'])) {
            return ['error_code' => 1, 'error_msg' => "请正确填写家装面积"];
        }

        $amountLogic = new CompanyRoundOrderAmountLogicModel();
        foreach ($result as $item) {
            // 补轮单数量为0继续验证其它逻辑
            if ($item["round_order_number"] <= 0) {
                //账户余额小于0
                if ($item["account_amount"] <= 0) {
                    $info["error_msg"] .= $item["jc"]."账户余额不足,余额:".$item["account_amount"]."\r\n";
                    continue;
                }

                //将账户余额添加至多轮单数据中(用于验证账户余额是否过低)
                $amountData[$item['user_id']]['account_amount'] = $item["account_amount"];
                $amountData[$item['user_id']]['jc'] = $item["jc"];
                $amountData[$item['user_id']]['round_order_number'] = $item["round_order_number"];
;
                $status = $amountLogic->checkCompanyAmountOrderInfo($order_info,$amountData[$item['user_id']]);
                if($status['error_code'] != 0){
                    $info["error_msg"] .= $status['error_msg'];
                    continue;
                }
            }
        }

        if (!empty($info["error_msg"])) {
            $info["error_code"] = 1;
        }
        $info["data"] = array_column($result,null,"user_id");
        return $info;
    }

    public function setCompanyRefund($new_qian_refund,$order_id)
    {
        //查询用户账户状态
        $logic = new CompanyAccountLogicModel();
        $roundOrderModel = new UserCompanyRoundOrderModel();
        $result = $roundOrderModel->getCompanyRoundOrderList($new_qian_refund, $order_id);
        foreach ($result as $item) {
            if ($item["allot_type"] == 2) {
                $logic->setAccountRoundOrderNumberInc($item["user_id"], $order_id, "补轮次数返还", true);
            } else if ($item["allot_type"] == 1) {
                $logic->setAccountRoundOrderBack($item["user_id"], $item["round_money"], $order_id, "轮单退费");
            }
        }
    }

    public function setCompanyDeduction($new_qiandan_deduction,$orderInfo)
    {
        //查询用户账户状态
        $model = new UserModel();
        $logic = new CompanyAccountLogicModel();
        $result = $model->getUserAccountList($new_qiandan_deduction);
        //获取多轮单单价
        $amountDb = new UserCompanyRoundOrderAmountModel();
        $amountData = $amountDb->getAmountByCompany($new_qiandan_deduction);
        $amountData = array_column($amountData, null, 'company_id');

        $amountLogic = new CompanyRoundOrderAmountLogicModel();
        // 扣了补轮次数的公司
        $round_number_companys = [];
        foreach ($result as $item) {
            // 如果有补轮数量优先扣除补轮数
            if ($item["round_order_number"] > 0) {
                $round_number_companys[] = $item["user_id"];
                $logic->setAccountRoundOrderNumberDec($item["user_id"], $orderInfo['id']);
            } else {
                //轮单单价
                $roundInfo = $amountLogic->getCompanyAmountOrderInfo($orderInfo,$amountData[$item["user_id"]]);
                $logic->setAccountRoundOrderDeduction($item["user_id"],$roundInfo['price'],$orderInfo,"轮单扣费",$item["back_ratio"],$roundInfo);
            }
        }

        return ["round_number_companys" => $round_number_companys];
    }

    public function delNewQianOrderMap($new_qian_refund,$order_id)
    {
        $model = new UserCompanyRoundOrderModel();
        $company_ids = array_column($new_qian_refund,"company_id");
        return $model->delInfoByUserId($order_id,$company_ids);
    }

    public function addNewQianOrderMap($new_qiandan_deduction,$order_info)
    {
        $all = [];
        $company_ids = array_column($new_qiandan_deduction,"company_id");
        $new_qiandan_deduction = array_column($new_qiandan_deduction, null, "company_id");

        $model = new UserModel();
        $result = $model->getUserAccountList($company_ids);
        //获取多轮单单价
        $amountDb = new UserCompanyRoundOrderAmountModel();
        $amountData = $amountDb->getAmountByCompany($company_ids);
        $amountData = array_column($amountData, null, 'company_id');

        $amountLogic = new CompanyRoundOrderAmountLogicModel();

        foreach ($result as $item) {
            $user_id = $item["user_id"];
            
            $round_money = 0; // 该单扣了多少轮单费（赠单不扣费）
            if ($new_qiandan_deduction[$user_id]["type_fw"] == 1) {
                //验证通过 查询当前分配时符合的轮单费
                $company_amount = $amountLogic->getCompanyAmountOrderInfo($order_info, $amountData[$item['user_id']]);
                $round_money = $company_amount['price'];
            }

            $allot_type = 1; // 分配方式（使用了轮单数为补轮单）
            if ($new_qiandan_deduction[$user_id]["use_number"] == 1) {
                $round_money = 0; // 补轮单不扣费
                $allot_type = 2;
            }

            $all[] = [
                "user_id" => $item["user_id"],
                "order_id" => $order_info['id'],
                "created_at" => time(),
                "updated_at" => time(),
                "back_ratio" => empty($item["back_ratio"])?0:$item["back_ratio"],
                "round_money" => $round_money,
                "allot_type" => $allot_type
            ];
        }
        if (count($all) > 0) {
            $model = new UserCompanyRoundOrderModel();
            return $model->addAllInfo($all);
        }
        return false;
    }

    public function editNewQianOrderMap($company = [],$order_id = 0,$data = [])
    {
        $model = new UserCompanyRoundOrderModel();
        return $model->editNewQianOrderMap($company,$order_id,$data);
    }

    /**
     * 获取装修公司信息
     * @param int $fake 0.真会员 1.非真会员(假会员,过期...)
     * @param array $company_id
     * @return mixed
     */
    public function getUserCompany($fake = 0, $company_id = [])
    {
        $userDb = new UserModel();
        $info = $userDb->getFakeCompanyList($company_id);
        if (count($info) > 0) {
            //获取rank数据
            $rankDb = new UserCompanyRankModel();
            //获取用户rank信息
            $rackList = $rankDb->getRankList($company_id);
            $rackList = array_column($rackList, null, 'comid');
            foreach ($info as $k=>$v){
                if ($v['on'] == 2 && $v['fake'] == 0) {
                    unset($info[$k]);
                }
                $info[$k]['ping'] = !empty($rackList[$v['id']])?$rackList[$v['id']]['ping']:0;
                $info[$k]['casesnum'] = !empty($rackList[$v['id']])?$rackList[$v['id']]['casesnum']:0;
                $info[$k]['haoping'] = !empty($rackList[$v['id']])?$rackList[$v['id']]['haoping']:0;
            }
        }
        return $info;
    }
    
    public function getCompanyDeviceTokenById($company_id)
    {
        $model = new UmengRecordModel();
        $result = $model->getCompanyDeviceTokenById($company_id);
        $deviceTokens = [];
        foreach ($result as $item) {
            $deviceTokens[] = $item["device_token"];
        }
        return implode(",",$deviceTokens);
    }

    public function sendComapnyNotice($company,$action_id)
    {
        $all = [];
        foreach ($company as $item) {
            $all[] = [
                "action_id" => $action_id,
                "user_id" => $item,
                "action_type" => 1,     // 来源类型：1:齐装保申请
                "created_at" => time(),
                "updated_at" => time()
            ];
        }
        $model = new YgjNoticeModel();
        return $model->addAllInfo($all);
    }

    public function getCompanyTags($city_id)
    {
        $model  = new UserModel();
        $result = $model->getCompanyTags($city_id);
        $list = array_column($result,"tags","id");
        return $list;
    }
}