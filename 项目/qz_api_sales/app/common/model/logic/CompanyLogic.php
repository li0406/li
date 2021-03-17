<?php

namespace app\common\model\logic;

use think\Db;
use think\Collection;

use app\index\enum\PnpCodeEnum;
use app\index\enum\OrderCodeEnum;
use app\index\enum\CompanyTagCodeEnum;

use app\common\model\db\SubTag;
use app\common\model\db\UserVip;
use app\common\model\db\OrderInfo;
use app\common\model\db\SubTagCompany;
use app\common\model\db\UserCompanyAccount;
use app\common\model\db\UserCompanySignback;
use app\common\model\db\UserCompanyRoundOrderAmount;

use app\common\model\db\User;
use app\common\model\db\Area;
use app\common\model\db\Company;
use app\common\model\db\Contacts;
use app\common\model\db\UserCompany;
use app\common\model\db\ReportVisit;
use app\common\model\db\ReportCompany;
use app\common\model\db\UserCompanyDeliverArea;
use app\common\model\service\SmsService;

use app\index\enum\CompanyCodeEnum;

class CompanyLogic
{

    public function getCompanys($company)
    {
        $comDb = new ReportCompany();
        $map[] = ['is_del', 'eq', 1];
        if (ctype_digit((string)$company)) {
            $map[] = ['id', 'eq', $company];
        } else {
            $map[] = ['company_name', 'like', '%' . $company . '%'];
        }
        $company_list = $comDb->getList($map, [], 0, 99999999);
        return array_map(function ($value) {
            return [
                'company_id' => $value['id'],
                'company_name' => $value['company_name'],
                'created_time' => $value['created_at'],
            ];
        }, $company_list->toArray());
    }

    public function getAllCompanys($info)
    {
        //设置查询条件
        $map = function ($query) use ($info) {
            $query->where('b.is_del', '=', 1);
            if (!empty($info['company'])) {
                if (ctype_digit((string)$info['company'])) {
                    $query->where('b.id', '=', $info['company']);
                } else {
                    $query->where('b.company_name', 'like', '%' . $info['company'] . '%');
                }
            }
            if (!empty($info['cs'])) {
                $query->where('b.cs', '=', $info['cs']);
            }
            if (!empty($info['level'])) {
                $query->where('b.intention', '=', $info['level']);
            }
            $uids = AdminuserLogic::getAuthUserids();
            if (!empty($uids)) {
                $query->where('a.uid', 'in', $uids);
            }
            if (!empty($info['company_style'])) {
                $query->where('b.is_new', '=', $info['company_style']);
            }
        };
        $map1 = function ($query) use ($info) {
            if (!empty($info['op_uid'])) {
                $query->where('d.uid', '=', $info['op_uid']);
            }
            if (!empty($info['is_sign'])) {
                $query->where('d.status', '=', $info['is_sign']);
            }
            if (!empty($info['visit_start'])) {
                $query->where('d.start_time', '>=', strtotime($info['visit_start']));
            }
            if (!empty($info['visit_end'])) {
                $query->where('d.end_time', '<=', strtotime($info['visit_end']) + 86399);
            }
            if (!empty($info['enter_start']) && !empty($info['enter_end'])) {
                $query->where('d.created_at', 'between', [strtotime($info['enter_start']), strtotime($info['enter_end']) + 86399]);
            }
            if (!empty($info['next_start'])) {
                $query->where('d.next_time', '>=', strtotime($info['next_start']));
            }
            if (!empty($info['next_end'])) {
                $query->where('d.next_time', '<=', strtotime($info['next_end']) + 86399);
            }
            if (!empty($info['visit_style'])) {
                $query->where('d.style', '=', $info['visit_style']);
            }
        };

        //设置排序
        $order = !empty($info['order']) ? $info['order'] : 1;
        switch ($order) {
            case 1:
                $order = [
                    'order1' => 'd.updated_at desc,qz_report_user.updated_at desc',
                    'order2' => 'd.updated_at desc',
                ];
                break;
            case 2:
                $order = [
                    'order1' => 'd.updated_at desc,qz_report_user.updated_at desc',
                    'order2' => 'd.next_time desc',
                ];
                break;
        }
        $company_list = $this->getAllList($map, $map1, isset($info['p']) ? $info['p'] : 1, isset($info['psize']) ? $info['psize'] : 20, $order);
        return $company_list;
    }

    public function getOldCompanys($company_name)
    {
        $comDb = new User();
        $map[] = ['classid', 'eq', 3];
        $map[] = ['qc', 'like', '%' . $company_name . '%'];
        $field = 'id,name,register_time,cs,qx,qc,jc';
        $company_list = $comDb->getList($field, $map, ['city', 'area'], 0, 20);
        return array_map(function ($value) {
            return [
                'company_id' => $value['id'],
                'company_name' => $value['qc'],
                'company_jc' => $value['jc'],
                'value' => $value['qc'],
                'cs' => $value['cs'],
                'qx' => $value['qx'],
                'cname' => isset($value['city']['cname']) ? $value['city']['cname'] : '',
                'aname' => isset($value['area']['aname']) ? $value['area']['aname'] : '',
                'created_time' => $value['register_time'],
            ];
        }, Collection::make($company_list)->toArray());
    }

    public function getInfoById($company_id)
    {
        $conDb = new User();
        $map[] = ['id', 'eq', $company_id];
        $contacts = $conDb->getInfo($map);
        return [
            'id' => isset($contacts['id']) ? $contacts['id'] : '',
            'cs' => isset($contacts['cs']) ? $contacts['cs'] : '',
            'area' => isset($contacts['qx']) ? $contacts['qx'] : ''
        ];
    }

    /**
     * 验证会员公司是否绑定
     * @param $user_company_id [会员公司id]
     * @param string $visit_company_id [日报公司id]
     * @return array
     */
    public function getCompanyRepeat($user_company_id, $visit_company_id = '')
    {
        if (empty($user_company_id)) {
            return [
                'is_repeat' => 0,
            ];
        }
        $comDb = new ReportCompany();
        //如果事编辑日报公司 , 则先验证 会员公司id和日报公司是否已经绑定
        if (!empty($visit_company_id)) {
            $info = $comDb->getInfo(['id' => $visit_company_id]);
            if ($info['user_id'] == $user_company_id) {
                return [
                    'is_repeat' => 0,
                ];
            }
        }
        $map[] = ['is_del', 'eq', 1];
        $map[] = ['user_id', 'eq', $user_company_id];
        $company_count = $comDb->getCount($map, []);
        if ($company_count > 0) {
            return [
                'is_repeat' => 1,
            ];
        } else {
            return [
                'is_repeat' => 0,
            ];
        }
    }

    /**
     * 新增会员
     * @param $info
     * @param $user
     * @return array
     */
    public function insertCompany($info, $user)
    {
        $save = [
            'company_name' => $info['company_name'],
            'user_id' => isset($info['user_id']) ? $info['user_id'] : '',
            'cs' => $info['cs'],
            'qx' => $info['qx'],
            'address' => $info['address'],
            'is_new' => $info['is_new'],
            'intention' => $info['intention'],
            'customer_source' => $info['customer_source'],
            'updated_at' => time()
        ];

        $comDb = new ReportCompany();
        $save['created_at'] = time();
        $save['op_uid'] = $user['user_id'];
        $insertId = $comDb->insertReportCompany($save);
        return $insertId;
    }

    public function getAllList($map, $map1, $page = 1, $limit = 20, $order)
    {
        $comDb = new ReportVisit();

        $count = $comDb->getAllListCount($map, $map1);
        if ($count > 0) {
            $field = 'e.updated_at,e.id,e.customer_source,e.user_id,e.company_name,e.address,e.intention,e.is_new,
            qz_quyu.cname,qz_area.qz_area,qz_report_user.name as uname,qz_report_user.tel,
            d.id visit_id,d.start_time,d.end_time,d.next_time,d.status,d.retainage_time,d.created_at,d.style,
            qz_adminuser.name as admin_name,qz_adminuser.id as admin_id';
            $list = $comDb->getAllList($field, $map, $map1, $page, $limit, $order);
            $listTemp = array_map(function ($value) {
                return [
                    'id' => $value['id'],
                    'company_name' => $value['company_name'],
                    'cname' => $value['cname'],
                    'area' => $value['qz_area'],
                    'name' => $value['uname'],
                    'addr' => $value['address'],
                    'tel' => $value['tel'],
                    'is_new' => $value['is_new'],
                    'intention_level' => $value['intention'],
                    'sign_status' => $value['status'],
                    //'contact_count' => $value['register_time'],
                    'visit_start_time' => $value['start_time'],
                    'visit_end_time' => $value['end_time'],
                    'visit_start_time_num' => strtotime($value['start_time']),
                    'visit_end_time_num' => strtotime($value['end_time']),
                    'visit_time' => $value['created_at'],
                    'next_time' => $value['next_time'],
                    'retainage_time' => $value['retainage_time'],
                    'admin_id' => $value['admin_id'],
                    'admin_name' => $value['admin_name'],
                    'contact_num' => $value['contact_num'],
                    'style' => $value['style'],
                    'start' => isset($value['user']['start']) ? $value['user']['start'] : '',
                    'end' => isset($value['user']['end']) ? $value['user']['end'] : '',
                    'allstart' => isset($value['hetong']['allstart']) ? $value['hetong']['allstart'] : '',
                    'allend' => isset($value['hetong']['allend']) ? $value['hetong']['allend'] : '',
                ];
            }, $list->toArray());
            return sys_response(0, '', [
                'list' => $listTemp,
                'page' => sys_paginate($count, $limit, $page) #分页信息
            ]);
        }
        return sys_response(4000001);
    }

    /**
     * 获取会员公司合同
     * @Author   liuyupeng
     * @DateTime 2019-05-20T14:27:07+0800
     */
    public function getContract($user_id)
    {
        $userModel = new User();
        $contract = $userModel->getContract($user_id);
        if (empty($contract)) {
            return sys_response(4000001, '未找到该会员公司');
        }

        $reportVisit = new ReportVisit();
        $visitInfo = $reportVisit->getNewInfo($user_id);

        if (!empty($visitInfo)) {
            $contract['retainage_time'] = date('Y-m-d', $visitInfo['retainage_time']);
        } else {
            $contract['retainage_time'] = '';
        }

        return sys_response(0, '', $contract);
    }


    /**
     * 获取用户具体信息
     *
     * @param int $id 用户ID
     * @param int $pageSize 获取数量
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getListUser($id, $pageSize = 10)
    {
        $map[0] = ['classid', 'EQ', 3];  //只查询装修公司
        if (!empty($id)) {
            if (ctype_digit((string)$id)) {
                $map[1] = ['id', 'EQ', intval($id)];
            } else {
                $map[1] = ['qc|jc', 'like', '%' . $id . '%'];
            }
        }
        
        $userModel = new User();
        $list = $userModel->getList('id,jc,jc value,qc,cs', $map, ['city', 'account'], 1, $pageSize);

        $list = array_map(function ($val) {
            $val['cname'] = $val['city']['cname'];
            $val['value'] = isset($val['city']['cname']) ? $val['city']['cname'] . '：' . $val['value'] : $val['value'];
            $val['deposit_money'] = $val['account'] ? floatval($val['account']['deposit_money']) : 0;
            unset($val['city'], $val['account']);
            return $val;
        }, Collection::make($list)->toArray());

        return $list;
    }

    /**
     * 会员到期提醒PC端
     *
     * @param $param
     * @return array|bool
     */
    public function getVipUserPc($param, $p, $psize)
    {
        $map = [];
        if (!empty($param['end'])) {
            $map['end'] = $param['end'];
        }

        if (!empty($param['start'])) {
            $map['start'] = $param['start'];
        }

        if (!empty($param['company'])) {
            $map['company'] = $param['company'];
        }
        if (!empty($param['type'])) {
            $map['type'] = $param['type'];
        }
        if (!empty($param['lastday'])) {
            $map['lastday'] = $param['lastday'];
        }

        if (!empty($param['classid'])) {
            $map['classid'] = $param['classid'] == 1?3:6;
        }

        // 非超级管理员获取自己和下级管辖城市
        $citysids = AdminuserLogic::getCityIds();
        if (empty($citysids)) {
            return sys_response(4000002, '暂无权限查看数据', []);
        } else {
            $map['cs'] = ['in', $citysids];
            if (!empty($param['cs'])) {
                if (in_array($param['cs'], $citysids)) {
                    $map['cs'] = $param['cs'];
                } else {
                    return sys_response(4000002, '暂无权限查看数据', []);
                }
            }
        }

        $userModel = new User();
        $list = $userModel->getVipUser($map);

        if (isset($param['down']) && $param['down'] == 1) {
            $responseData = [
                'list' => $list,
            ];
        } else {
            $responseData = [
                'list' => array_slice(Collection::make($list)->toArray(), ($p - 1) * $psize, $psize),
                'page' => sys_paginate(count($list), $psize, $p) #分页信息
            ];
        }
        return sys_response(0, '', $responseData);
    }

    public function getVipCompany($data, $citys)
    {
        if (empty($citys)) {
            return [];
        }
        $page = !empty($data['page']) ? (int)$data['page'] : 1;
        $pageCount = !empty($data['page_count']) ? (int)$data['page_count'] : 6;
        $map = [
            'u.on' => ['eq', 2],
            'u.classid' => ['eq', 3],
            'c.fake' => ['eq', 0],
        ];
        if (!empty($data['cs'])) {
            if (is_array($data['cs'])) {
                $map['u.cs'] = ['in', $data['cs']];
            } else {
                $map['u.cs'] = ['eq', $data['cs']];
            }
        }
        if (!empty($data['start']) && !empty($data['end'])) {
            $map['u.end'] = ['between', [$data['start'], $data['end']]];
        }
        if (!empty($data['company'])) {
            $map['company'] = $data['company'];
        }
        $list = [];
        $comDb = new Company();
        $count = $comDb->getCompanyCount($map);
        if ($count > 0) {
            $list = $comDb->getCompanyList($map, ($page - 1) * $pageCount, $pageCount);
        }
        return sys_response(0, '', [
            'list' => $list,
            'page' => sys_paginate($count, $pageCount, $page) #分页信息
        ]);
    }

    /**
     * 获取会员公司分页列表
     * @return [type] [description]
     */
    public function getPageList($options, $page = 1, $page_size = 20)
    {
        // 管理员能够看到的所有城市
        $cityIds = AdminuserLogic::getCityIds();

        // 城市检索
        if (!empty($options["cs"])) {
            if (in_array($options["cs"], $cityIds)) {
                $cityIds = $options["cs"];
            } else {
                $cityIds = "";
            }
        }

        $options["citys"] = $cityIds;
        if (empty($options["citys"])) {
            return ["count" => 0, "list" => []];
        }

        $userModel = new User();
        $count = $userModel->getUserCompanyList("count", $options);

        $list = [];
        if ($count > 0) {
            $page = intval($page) <= 0 ? 1 : intval($page);
            $page_size = intval($page_size) <= 0 ? 20 : intval($page_size);
            $list = $userModel->getUserCompanyList("list", $options, ($page - 1) * $page_size, $page_size);

            $list = array_map(function ($item) {
                $item["latlng"] = "";
                if ($item["lng"] && $item["lat"]) {
                    $item["latlng"] = $item["lng"] . "," . $item["lat"];
                }

                $item["lxs_name"] = CompanyCodeEnum::getItem("lxs", $item["lxs"], "未填写");
                $item["leixing_name"] = CompanyCodeEnum::getItem("lx", $item["lx"], "未填写");
                $item["contract_name"] = CompanyCodeEnum::getItem("contract", $item["contract"], "未填写");
                $item["user_on_name"] = CompanyCodeEnum::getItem("user_on", $item["user_on"]);
                $item["cooperate_mode_name"] = CompanyCodeEnum::getItem("cooperate_mode", $item["cooperate_mode"]);
                $item["pnp_on_name"] = $item["pnp_on"] == 1 ? '已开通' : '未开通';
                unset($item["lat"], $item["lng"]);
                return $item;
            }, $list->toArray());
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 获取详情
     * @return [type] [description]
     */
    public function getCompanyDetail($id)
    {
        $userModel = new User();
        $info = $userModel->getUserCompanyDetail($id);

        if (!empty($info)) {
            $info = $info->toArray();

            $info["back_ratio"] = floatval($info["back_ratio"]);
//            $info["order_round_amount"] = floatval($info["order_round_amount"]);

            // 相邻城市
            $deliver_city = array(
                $info["cs"],
                $info["parent_city"],
                $info["parent_city1"],
                $info["parent_city2"],
                $info["parent_city3"],
                $info["parent_city4"]
            );

            $info["deliver_city"] = array_filter(array_unique($deliver_city));
            $info["latlng"] = "";
            if ($info["lng"] && $info["lat"]) {
                $info["latlng"] = $info["lng"] . "," . $info["lat"];
            }
            unset($info["lat"], $info["lng"]);
            unset($info["parent_city"], $info["parent_city1"], $info["parent_city2"], $info["parent_city3"], $info["parent_city4"]);

            //获取多轮单单价
            $AmountDb = new UserCompanyRoundOrderAmount();
            $info['round_order_amount'] = $AmountDb->getRoundOrderAmountInfo($info['id']);
            if (!empty($info['round_order_amount'])) {
                $info['round_order_amount']['gz_price'] = floatval($info['round_order_amount']['gz_price']);
                $info['round_order_amount']['jg_price'] = floatval($info['round_order_amount']['jg_price']);
                $info['round_order_amount']['mjmin_price'] = floatval($info['round_order_amount']['mjmin_price']);
                $info['round_order_amount']['mjmax_price'] = floatval($info['round_order_amount']['mjmax_price']);
            }

            // PNP配置默认值处理
            if (empty($info["pnp_days"]) || empty($info["pnp_tel_num"])) {
                $pnpConfigLogic = new PnpConfigLogic();
                $pnpconfig = $pnpConfigLogic->getAxbConfig();

                $info["pnp_days"] = check_variate($pnpconfig["pnp_axb_expire"], PnpCodeEnum::PNP_AXB_EXPIRE);
                $info["pnp_tel_num"] = check_variate($pnpconfig["pnp_axb_com_tel_num"], PnpCodeEnum::PNP_AXB_COM_TEL_NUM);
            }
        }

        return $info;
    }

    /**
     * 获取会员公司相邻区域
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCompanyDeliverArea($id, $deliver_city)
    {
        $cityList = model("db.quyu")->getCitysByCids($deliver_city)->toArray();
        $areaList = model("db.area")->getDeliverArea($id, $deliver_city)->toArray();

        foreach ($cityList as $key => $city) {
            $cityList[$key]["count"] = 0;
            $cityList[$key]["children"] = [];
            foreach ($areaList as $k => $area) {
                if ($area["fatherid"] == $city["cid"]) {
                    $cityList[$key]["children"][] = $area;
                    if ($area["checked"] == 1) {
                        $cityList[$key]["count"]++;
                    }
                }
            }
        }

        return $cityList;
    }

    /**
     * 编辑会员城市信息
     * @return [type] [description]
     */
    public function editUserCompanyInfo($id, $data, $userinfo)
    {
        Db::startTrans();

        try {
            // user 数据
            $userData = array(
                "cs" => $data["cs"],
                "qx" => $data["qx"],
                "dz" => $data["dz"],
                "time" => time()
            );

            // 会员公司数据
            $companyData = array(
                "contract" => check_variate($data["contract"], 0),
                "lx" => check_variate($data["lx"], 0),
                "lxs" => check_variate($data["lxs"], 0),
                "other_id" => check_variate($data["other_id"]),
                "mianji" => check_variate($data["mianji"], 0),
                "fen_mianji" => check_variate($data["fen_mianji"], 0),
                "get_gift_order" => check_variate($data["get_gift_order"], 2),
                "fen_special_needs" => check_variate($data["fen_special_needs"]),
                "pnp_tel_num" => check_variate($data["pnp_tel_num"], 0),
                "pnp_days" => check_variate($data["pnp_days"], 0),
                "pnp_on" => check_variate($data["pnp_on"], 2),
                "lat" => "",
                "lng" => "",
                "time" => time()
            );

            // 坐标（经纬度）
            if (!empty($data["latlng"])) {
                $latlng = explode(",", $data["latlng"]);
                $companyData["lng"] = $latlng[0];
                $companyData["lat"] = $latlng[1];
            }

            // 保存会员和会员公司信息
            $userResult = User::where("id", $id)->data($userData)->update();
            $ucResult = UserCompany::where("userid", $id)->data($companyData)->update();
            if (!$userResult || !$ucResult) {
                throw new \Exception("会员公司信息保存失败");
            }

            // 删除会员公司接单区域信息
            $deliverAreaModel = new UserCompanyDeliverArea();
            $result = $deliverAreaModel->deleteByCompanyId($id);

            // 保存新的会员公司接单区域信息
            if (!empty($data["deliver_area"])) {
                $deliver_area = explode(",", $data["deliver_area"]);
                $deliver_area = array_filter(array_unique($deliver_area));

                // 根据接单区域ID查询区域
                $areaModel = new Area();
                $areaList = $areaModel->getList(["qz_areaid" => array("in", $deliver_area)]);

                // 接单区域ID不合法
                if (empty($areaList)) {
                    throw new \Exception("接单区域ID不合法");
                }

                // 组织接单区域数据
                $rows = array_map(function ($item) use ($id) {
                    return [
                        "company_id" => $id,
                        "area_id" => $item["area_id"],
                        "create_time" => time()
                    ];
                }, $areaList->toArray());

                // 添加接单区域数据
                $result = $deliverAreaModel->addRecords($rows);
                if (empty($result)) {
                    throw new \Exception("接单区域保存失败");
                }
            }

            $amountData = [];
            // 保存轮单单价
            if (in_array($userinfo["cooperate_mode"],[2,4]) ) {
                //编辑多轮单单价
                $amountLogic = new UserCompanyRoundOrderAmountLogic();
                $amountData = $amountLogic->createSaveData($data);
                if(count($amountData) == 0){
                    throw new \Exception("请填写轮单单价");
                }
                //删除原有数据
                $amountLogic->delAmount($data['id']);
                //添加数据
                $status = $amountLogic->saveAmount($amountData);
                if(!$status){
                    throw new \Exception("轮单单价添加失败!");
                }
            }

            // 添加操作日志
            $logInfo = [
                "user" => $userData,
                "company" => $companyData,
                "deliver_area" => $data["deliver_area"],
                "order_round_amount" => $amountData
            ];
            $res = LogAdminLogic::addLog("editvipcompany", $id, "编辑会员公司信息", $logInfo);
            
            Db::commit();
            $result = true;
        } catch (\Exception $e) {
            Db::rollback();
            $result = $e->getMessage();
        }

        return $result;
    }

    //获取指定日期前30过期会员
    public function getPastCompany($cs, $day = '')
    {
        $day = !empty($day) ? $day : date('Y-m-d');
        $companyDb = new Company();
        $lostCompany = $companyDb->getPastCompany($cs, $day);
        if (count($lostCompany) > 0) {
            foreach ($lostCompany as $key => $value) {
                $offset = (strtotime($day) - strtotime($value["end"])) / 86400 + 1;
                $lostCompany[$key]["day"] = $offset;
            }
        }
        return $lostCompany;
    }

    public function getCompanyListByCity($cs, $id, $lng, $lat,$type_fw)
    {
        //获取公司分/抢/赠 数
        //获取上次分配的公司
        $fenpei_company = $this->getOrderLastFenCompany($id, $cs);
        //已分配的公司
        $fen_company = (new OrderInfo())->getOrderCompany($id,[]);
        $fen_company = array_column($fen_company->toArray(), null, 'com');

        //1.获取当前城市的公司
        $list = $this->getCompanyDetailsList($cs);
        $other = $now = $now_fen_company = [];
        foreach ($list as $key => $value) {
            if (!array_key_exists($value["qx"], $now)) {
                $now[$value["qx"]]['area_id'] = $value["qx"];
                $now[$value["qx"]]['area'] = $value["qz_area"];
            }
            if ((int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0 && (int)$value["lng"] != 0) {
                $value["distance"] = get_distance(array($lng, $lat), array($value["lat"], $value["lng"]), true, 0);
            } else {
                $value["distance"] = "无";
            }
            if (!empty($fen_company[$value['id']])) {
                $value["is_choose"] = 1;//选中
                //除了销售补单的 , 其他都不能修改
                if ($fen_company[$value['id']]['allot_source'] == 4) {
                    $value["is_change"] = 2; //能否更改 1.不能 2.能
                } else {
                    $value["is_change"] = 1;
                }
            } else {
                $value["is_choose"] = 0;
                $value["is_change"] = 2;
            }
            $now[$value["qx"]]["company_list"][] = $value;
            //上次分配公司
            foreach ($fenpei_company as $k => $val) {
                if ($val["cs"] == $cs) {
                    $now_fen_company[] = $val;
                    unset($fenpei_company[$k]);
                }
            }
        }

        //获取当前城市的签单返点会员
        $userLogic = new UserLogic();
        $qiandanList = $userLogic->getQianDanUserList($cs,2);

        foreach ($qiandanList as $value) {
            if (!empty($fen_company[$value['id']])) {
                $value["is_choose"] = 1;//选中
                //除了销售补单的 , 其他都不能修改
                if ($fen_company[$value['id']]['allot_source'] == 4) {
                    $value["is_change"] = 2; //能否更改 1.不能 2.能
                } else {
                    $value["is_change"] = 1;
                }
            } else {
                //默认是不选中可以选择
                $value["is_choose"] = 0;
                $value["is_change"] = 2;

                //签返装修公司不可选中状态
                if ( $value["classid"] == 6 && isset($value["package_status"]) && $value["package_status"] == 3) {
                    $value["is_choose"] = 0;
                    $value["is_change"] = 1;;
                } elseif($value["classid"] == 6 && isset($value["package_status"]) && $value["package_status"] == 2 && in_array($type_fw,array(1,3)) ){
                    //订单包可用并且是分、分没人跟状态
                    if ($value["use_fen"] == 3) {
                        $value["is_choose"] = 0;
                        $value["is_change"] = 1;
                    }
                } elseif($value["classid"] == 6 && isset($value["package_status"]) && $value["package_status"] == 2 && in_array($type_fw,array(2,4)) ){
                    //订单包可用并且是赠、赠没人跟状态
                    if ($value["use_zen"] == 3) {
                        $value["is_choose"] = 0;
                        $value["is_change"] = 1;;
                    }
                } elseif(!isset($value["package_status"]) && $value["classid"] == 6){
                    $value["is_choose"] = 0;
                    $value["is_change"] = 1;
                }
            }


            if (!array_key_exists($value["qx"],$now)) {
                $now[$value["qx"]]['area_id'] = $value["qx"];
                $now[$value["qx"]]['area'] = $value["qz_area"];
            }

            $now[$value["qx"]]["company_list"][] = $value;
        }

        //2.获取相邻城市的公司
        $cityRelaLogic = new OrderCityRelation();
        $other_city = $cityRelaLogic->getRelationCity($cs);
        if (count($other_city) > 0) {
            //相邻城市会员
            $result = $this->getCompanyDetailsList(array_column(is_object($other_city) ? $other_city->toArray() : $other_city, 'cid'), 2);

            foreach ($result as $key => $value) {
                if (!array_key_exists($value["cs"], $other)) {
                    $other[$value["cs"]]["cid"] = $value["cs"];
                    $other[$value["cs"]]["cname"] = $value["cname"];
                }

                if ((int)$lat != 0 && (int)$lng != 0 && (int)$value["lat"] != 0 && (int)$value["lng"] != 0) {
                    $value["distance"] = get_distance(array($lng, $lat), array($value["lat"], $value["lng"]), true, 0);
                } else {
                    $value["distance"] = "无";
                }

                if (!empty($fen_company[$value['id']])) {
                    $value["is_choose"] = 1;//选中
                    //除了销售补单的 , 其他都不能修改
                    if ($fen_company[$value['id']]['allot_source'] == 4) {
                        $value["is_change"] = 2; //能否更改 1.不能 2.能
                    } else {
                        $value["is_change"] = 1;
                    }
                } else {
                    //默认是不选中可以选择
                    $value["is_choose"] = 0;
                    $value["is_change"] = 2;

                    //签返装修公司不可选中状态
                    if ( $value["classid"] == 6 && isset($value["package_status"]) && $value["package_status"] == 3) {
                        $value["is_choose"] = 0;
                        $value["is_change"] = 1;;
                    } elseif($value["classid"] == 6 && isset($value["package_status"]) && $value["package_status"] == 2 && in_array($type_fw,array(1,3)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($value["use_fen"] == 3) {
                            $value["is_choose"] = 0;
                            $value["is_change"] = 1;
                        }
                    } elseif($value["classid"] == 6 && isset($value["package_status"]) && $value["package_status"] == 2 && in_array($type_fw,array(2,4)) ){
                        //订单包可用并且是赠、赠没人跟状态
                        if ($value["use_zen"] == 3) {
                            $value["is_choose"] = 0;
                            $value["is_change"] = 1;;
                        }
                    } elseif(!isset($value["package_status"]) && $value["classid"] == 6){
                        $value["is_choose"] = 0;
                        $value["is_change"] = 1;
                    }
                }

                $other[$value["cs"]]["company_list"][] = $value;
                $other[$value["cs"]]["last_fen_company"] = [];
                //上次分配公司
                foreach ($fenpei_company as $k => $val) {
                    if ($val["cs"] == $value["cs"]) {
                        $other[$value["cs"]]["last_fen_company"][] = $val;
                        unset($fenpei_company[$k]);
                    }
                }
            }

            //获取相邻城市的签单返点会员
            $userLogic = new UserLogic();
            $qiandanList = $userLogic->getQianDanUserList(array_column(is_object($other_city) ? $other_city->toArray() : $other_city, 'cid'),2);

            foreach ($qiandanList as $value) {
                if (!empty($fen_company[$value['id']])) {
                    $value["is_choose"] = 1;//选中
                    //除了销售补单的 , 其他都不能修改
                    if ($fen_company[$value['id']]['allot_source'] == 4) {
                        $value["is_change"] = 2; //能否更改 1.不能 2.能
                    } else {
                        $value["is_change"] = 1;
                    }
                } else {
                    $value["is_choose"] = 0;
                    $value["is_change"] = 2;
                }
                if (!array_key_exists($value["cs"],$other)) {
                    $other[$value["cs"]]["cid"] = $value["cs"];
                    $other[$value["cs"]]["cname"] = $value["cname"];
                }
                $value["qianMark"] = 1;
                $other[$value["cs"]]["company_list"][] = $value;
            }
        }
        return [
            'now_company' => $now,
            'now_fen_company' => $now_fen_company,
            'other_company' => $other,
        ];
    }

    public function getOrderLastFenCompany($id, $cs)
    {
        //查询上次分配装修公司
        $orderInfoDb = new OrderInfo();
        return $orderInfoDb->getLastTypeFw($id, $cs);
    }

    public function getCompanyTagList($options, $page = 1, $page_size = 20){
        // 管理员能够看到的所有城市
        $cityIds = AdminuserLogic::getCityIds();

        // 城市检索
        if (!empty($options["cs"])) {
            if (in_array($options["cs"], $cityIds)) {
                $cityIds = $options["cs"];
            } else {
                $cityIds = "";
            }
        }

        $options["citys"] = $cityIds;
        if (empty($options["citys"])) {
            return ["count" => 0, "list" => []];
        }

        $userModel = new User();
        $count = $userModel->getUserCompanyTagList('count',$options);

        $list = [];
        if ($count > 0) {
            $page = intval($page) <= 0 ? 1 : intval($page);
            $page_size = intval($page_size) <= 0 ? 20 : intval($page_size);
            $list = $userModel->getUserCompanyTagList("list", $options, ($page - 1) * $page_size, $page_size);

            $list = array_map(function ($item) {
                $item["lxs_name"] = CompanyCodeEnum::getItem("lxs", $item["lxs"], "未填写");
                $item["leixing_name"] = CompanyCodeEnum::getItem("lx", $item["lx"], "未填写");
                $item["contract_name"] = CompanyCodeEnum::getItem("contract", $item["contract"], "未填写");
                return $item;
            }, $list->toArray());
        }

        return ["count" => $count, "list" => $list];
    }

    private function getCompanyDetailsList($cs, $on = 2)
    {
        $userDb = new Company();
        //获取符合条件的公司列表
        $companys = $userDb->getCompanyDetailsList($cs, $on);
        if (count($companys) > 0) {
            //获取公司分/抢/赠 数
            $orderInfoDb = new OrderInfo();
            $company_order_num = $orderInfoDb->getCompanyOrderNum(array_column($companys->toArray(), 'id'));
            $company_order_num = array_column($company_order_num->toArray(), null, 'id');
            //塞数据
            foreach ($companys as $k => $v) {
                if (isset($company_order_num[$v['id']])) {
                    $companys[$k]['fen'] = $company_order_num[$v['id']]['fen'];
                    $companys[$k]['qiang'] = $company_order_num[$v['id']]['qiang'];
                    $companys[$k]['zen'] = $company_order_num[$v['id']]['zen'];
                } else {
                    $companys[$k]['fen'] = 0;
                    $companys[$k]['qiang'] = 0;
                    $companys[$k]['zen'] = 0;
                }
            }
            $companys[$k]['company_id'] = $v['id'];
        }
        foreach ($companys as $key => $value) {
            //计算到期时间
            $offset = (strtotime($value["end"]) - strtotime(date("Y-m-d"))) / 86400 + 1;

            if ($offset <= 15 && empty($value["start_time"])) {
                $companys[$key]["end_time"] = $offset;
            }

            //合同开始时间如果大于本月1号显示新
            if ($value["start"] >= date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")))) {
                $companys[$key]["is_new"] = 1;
            } else {
                $companys[$key]["is_new"] = 2;
            }

            //合作类型
            if ($value["cooperation_type"] != 1) {
                $companys[$key]["cooperation_type"] = OrderCodeEnum::getCooperationType($value["cooperation_type"]);
            } else {
                $companys[$key]["cooperation_type"] = '';
            }
        }

        return $companys;
    }

    public function getCompanyFixationTag()
    {
        $tag = CompanyTagCodeEnum::getCompanyFixationTag();
        return $tag;
    }

    public function saveCompanyTag($company_id, $tag)
    {
        if (count($tag) == 0) {
            return sys_response(4000002);
        }
        $tagDb = new SubTag();
        $relDb = new SubTagCompany();
        //删除现有公司标识
        $relDb->delCompanyTag($company_id);
        //重新添加公司标识
        $save = [];
        foreach ($tag as $k => $v) {
            $save[] = [
                'tag_id' => $v['id'],
                'company_id' => $company_id,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        $status = $relDb->addCompanyTag($save);
        if ($status) {
            return sys_response(0);
        } else {
            return sys_response(5000002);
        }
    }


    /**
     * 验证用户名是否可使用， 可以则返回true,已被使用返回false
     * @param $user_name
     * @return bool
     */
    public function check_user($user_name)
    {
        $companymodel = new Company();
        if($user_name){
            $map = [];
            $map[] = ['user','=',$user_name];
            $result = $companymodel->getUserInfoByMap($map,"id,user,name");
            if(!empty($result)){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }

    public function registerCompany($resigerdata)
    {
        $companymodel = new Company();
        $usercompanymodel = new UserCompany();
        Db::startTrans(); // 开启事务
        try {
            //注册
            $register_result = $companymodel->addUserInfo($resigerdata);

            if(!empty($register_result)){
                // qz_user_company表添加数据
                $companyinfo = [];
                $companyinfo['userid'] = $register_result;
                $companyinfo['text'] = "该公司很懒,什么都没留下！";
                $company_result = $usercompanymodel->addUserCompanyInfo($companyinfo);

                // 添加公司账户信息
                $companyAccountModel = new UserCompanyAccount();
                $companyAccountModel->addAccountInfo(["user_id" => $register_result]);

                // 添加签返扩展信息
                $companySignbackModel = new UserCompanySignback();
                $companySignbackModel->addSignbackInfo(["user_id" => $register_result]);

                //日志
            }else{
                Db::rollback();
                return false;
            }
            // 提交事务
            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }

    }

    /**
     * @des:获取装修公司详情的一条数据
     * @param $userid
     */
    public function getUserCompanyInfoByUserId($userid)
    {
        $userCompanyModel = new UserCompany();
        return $userCompanyModel->getUserCompanyInfoByUserId($userid);
    }

    // 城市会员公司PNP号码保护批量开启
    public function setCityCompanyPnpOn($city_id){
        $pnpConfigLogic = new PnpConfigLogic();
        $config = $pnpConfigLogic->getAxbConfig();

        $userCompanyModel = new UserCompany();
        $companyList = $userCompanyModel->getCityPnpOffCompany($city_id);

        $ret = true;
        if (count($companyList) > 0) {
            $data = [
                "pnp_on" => PnpCodeEnum::PNP_ON,
                "pnp_days" => check_variate($config["pnp_axb_expire"], PnpCodeEnum::PNP_AXB_EXPIRE),
                "pnp_tel_num" => check_variate($config["pnp_axb_com_tel_num"], PnpCodeEnum::PNP_AXB_COM_TEL_NUM),
                "time" => time()
            ];

            $ret = $userCompanyModel->setCityCompanyPnpSwitch($city_id, PnpCodeEnum::PNP_OFF, $data);
        }

        // 如果执行成功发送短信通知
        if ($ret == true) {
            $telList = array_column($companyList->toArray(), "tel_safe");
            $telList = array_values(array_filter(array_unique($telList)));
            if (count($telList) > 0) {
                $smsService = new SmsService();
                $telChunkList = array_chunk($telList, 50);
                foreach ($telChunkList as $tels) {
                    $tels = implode(",", $tels);
                    $smsService->sendSms("202012181055", $tels);
                }
            }
        }

        return $ret;
    }

    // 城市会员公司PNP号码保护批量关闭
    public function setCityCompanyPnpOff($city_id){
        $data = [
            "pnp_on" => PnpCodeEnum::PNP_OFF,
            "time" => time()
        ];

        $userCompanyModel = new UserCompany();
        $ret = $userCompanyModel->setCityCompanyPnpSwitch($city_id, PnpCodeEnum::PNP_ON, $data);
        return $ret;
    }

}