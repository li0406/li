<?php

namespace app\common\model\logic;

use app\common\model\db\User;
use app\common\model\db\UserCompanyAccount;
use app\common\model\db\UserVip;
use app\common\model\db\Company;

/**
 *
 */
class UserLogic
{
    public function getQianDanUserList($city_id,$user_type)
    {
        $model = new User();
        $result = $model->getQianDanUserList($city_id,$user_type);

        $ids = $list = [];
        foreach ($result as $value) {
            //包的使用状态为不可用
            $value["qiang"] = 0;
            $list[$value["id"]] = $value;
            $ids[] = $value["id"];
        }

        if (count($ids) > 0) {
            //获取分单信息
            $result = $this->getUserOrderPackageInfo($ids,1);
            foreach ($result as $item) {
                $list[$item["company_id"]]["package_status"] = $item["package_status"];
                $list[$item["company_id"]]["use_fen"] = $item["use_status"];
                $list[$item["company_id"]]["fen"] = $item["send_number"];

                if ( $item["package_status"] == 3) {
                    $list[$item["company_id"]]["fen"] = 0;
                }
            }

            //获取赠单信息
            $result = $this->getUserOrderPackageInfo($ids,2);
            foreach ($result as $item) {
                $list[$item["company_id"]]["package_status"] = $item["package_status"];
                $list[$item["company_id"]]["use_zen"] = $item["use_status"];
                $list[$item["company_id"]]["zen"] = $item["send_number"];
                if ( $item["package_status"] == 3) {
                    $list[$item["company_id"]]["zen"] = 0;
                }
            }
        }

        return $list;
    }

    public function qianDanCompanyOrder($qianCompany,$type_fw,$order_id)
    {
        $ids = array_column($qianCompany,"id");
        if (count($ids) > 0) {
            //最近包的状态信息
            $result = $this->getUserOrderPackageInfo($ids,$type_fw);

            foreach ($result as $value) {
                $qianCompany[$value["company_id"]]["id"] = $value["id"];
                $qianCompany[$value["company_id"]]["package_status"] = $value["package_status"];
                $qianCompany[$value["company_id"]]["send_number"] = $value["send_number"];
                $qianCompany[$value["company_id"]]["total_number"] = $value["total_number"];
                $qianCompany[$value["company_id"]]["package_info_id"] = $value["package_info_id"];
                $qianCompany[$value["company_id"]]["use_status"] = $value["use_status"];
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
                        $subData["use_status"] = 3;
                        $data["use_status"] = 3;
                        $this->editPackage($value["id"],$data);
                        //查询下是否有一个订单包
                        $nextPackage = $this->getNextPackageInfo($key,$value["id"],$type_fw);

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

    public function getUserOrderPackageInfo($company_id,$package_type)
    {
        $model = new User();
        return $model->getUserOrderPackageInfo($company_id,$package_type);
    }

    public function editPackageInfo($id,$data)
    {
        $model = new User();
        return $model->editPackageInfo($id,$data);
    }

    public function getNextPackageInfo($company_id,$package_id,$package_type)
    {
        $model = new User();
        return $model->getNextPackageInfo($company_id,$package_id,$package_type);
    }

    public function editPackage($id,$data)
    {
        $model = new User();
        return $model->editPackage($id,$data);
    }

    public function addPackageOrderRelation($package_id,$order_id,$company_id)
    {
        $model = new User();
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
        $model = new User();
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

    public function getMemberCountByDay($begin,$end)
    {
        $model = new UserVip();
        return $model->getMemberCountByDate($begin,$end);
    }

    public function getUserInfo($user_id){
        $companyModel = new Company();
        return $companyModel->findUserInfo($user_id);
    }
}