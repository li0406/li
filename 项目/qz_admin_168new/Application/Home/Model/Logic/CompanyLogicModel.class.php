<?php

namespace Home\Model\Logic;

use Home\Model\Db\AdminquyuModel;
use Home\Model\Db\CompanyDeliverAreaMode;
use Home\Model\Db\CompanyDeliverAreaModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\UserCompanyOpInfoLogModel;
use Home\Model\Db\UserModel;
use Home\Model\Db\UserVipModel;
use Home\Model\Db\OrdersModel;
use Home\Model\UserCompanyModel;
use Home\Model\Db\SaleReportCityQuoteModel;

class CompanyLogicModel
{
    public $cooperationType = array(
        "1" => "常规",
        "2" => "独",
        "3" => "垄"
    );

    public $bannerStatus = [
        0 => '待审核',
        1 => '审核通过',
        2 => '审核未通过'
    ];

    public $memberStatus = [
        -4 => '会员暂停',
        -3 => '预约会员',
        -1 => '过期会员',
        0 => '注册会员',
        1 => '认证会员',
        2 => '正常会员',
    ];

    /**
     * 获取查询参数
     *
     * @param $get
     * @return mixed
     */
    public function getListMap($get)
    {
        $map['a.classid'] = array('IN',array(3,6));
        if (!empty($get['company'])) {
            $map['_complex'] = array(
                'a.qc' => array('LIKE', '%' . $get['company'] . '%'),
                'a.id' => array('LIKE', '%' . $get['company'] . '%'),
                '_logic' => 'OR'
            );
        }
        if (!empty($get['city'])) {
            $map['a.cs'] = $get['city'];
        }
        if (!empty($get['state'])) {
            $map['c.modify_state'] = $get['state'];
        }
        if (!empty($get['op'])) {
            $map['c.op_info_id'] = $get['op'];
        }
        if (!empty($get['member'])) {
            if ($get['member'] == 1) {
                $map['c.fake'] = array('EQ', 1);
                $map['a.on'] = array('IN', array(-1, 2));
            } elseif ($get['member'] == 2) {
                $map['a.on'] = array('EQ', '0');
            }
        } else {
            $map['_string'] = ' (c.fake =1 and a.on in (-1,2))  OR ( a.on = 0) ';
        }
        if (!empty($get['start']) && !empty($get['end'])) {
            $map['a.register_time'] = ['BETWEEN', [strtotime($get['start']), strtotime($get['end'] . ' 23:59:59')]];
        } elseif (!empty($get['start]'])) {
            $map['a.register_time'] = ['EGT', strtotime($get['start'])];
        } elseif (!empty($get['end'])) {
            $map['a.register_time'] = ['ELT', strtotime($get['end'] . ' 23:59:59')];
        }

        if (!empty($get['upstart']) && !empty($get['upend'])) {
            $map['c.op_info_last_time'] = ['BETWEEN', [strtotime($get['upstart']), strtotime($get['upend'] . ' 23:59:59')]];
        } elseif (!empty($get['upstart'])) {
            $map['c.op_info_last_time'] = ['EGT', strtotime($get['upstart'])];
        } elseif (!empty($get['upend'])) {
            $map['c.op_info_last_time'] = ['ELT', strtotime($get['upend'] . ' 23:59:59')];
        }
        return $map;
    }

    /**
     * 获取公司列表
     *
     * @param $where
     * @param int $is_export
     * @return array
     */
    public function getAllCompanyList($where, $is_export = 0)
    {
        $companyModel = new CompanyModel();
        $map = $this->getListMap($where);
        $count = $companyModel->getAllCompanyListCount($map);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            if ($is_export == 1) {
                $list = $companyModel->getAllCompanyList($map, null, null);
            } else {
                $list = $companyModel->getAllCompanyList($map, $p->firstRow, $p->listRows);
            }
        }
        return ['list' => isset($list) ? $list : [], 'page' => isset($show) ? $show : ''];
    }

    /**
     * 获取公司真会员列表
     *
     * @param $where
     * @return array
     */
    public function getTrueCompany($where)
    {
        $companyModel = new CompanyModel();
        $count = $companyModel->getCompanyListCount($where);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = $companyModel->getCompanyList($where, $p->firstRow, $p->listRows);
            $list = array_map(function ($val) use ($companyModel) {
                $val['deliver_area_count'] = $companyModel->getCompanyAreaCount(['company_id' => $val['id']]);
                return $val;
            }, $list);
            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 获取VIP公司具体信息
     *
     * @param $id
     * @return mixed
     */
    public function getvipcompany($id)
    {
        $companyModel = new CompanyModel();
        $result = $companyModel->getvipcompany($id);
        if (!empty(floatval($result["lng"])) && !empty(floatval($result["lat"]))) {
            $result["zuobiao"] = $result["lng"] . "," . $result["lat"];
        }
        $areaArray = $companyModel->getCompanyAreaList(['a.company_id' => $id]);
        $result['deliver_area'] = $areaArray ? implode(',', array_column($areaArray, 'area_id')) : '';
        return $result;
    }

    //获取公司信息
    public function getCompanyInfoByAdmin($id)
    {
        $result = D('Home/Db/Company')->getCompanyInfoByAdmin($id);
        return $result;
    }

    /**
     * 保存会员信息
     *
     * @param array $user 用户信息
     * @param array $company 用户信息
     * @param string $id 用户ID
     * @return bool
     */
    public function savevipcompany($user, $company, $id)
    {
        $user["time"] = time();
        $company["time"] = time();
        $companyModel = new CompanyModel();
        $userResult = $companyModel->saveUser($user, $id);
        $companyResult = $companyModel->saveCompany($company, $id);

        //删除原有接单区域
        $companyModel->delCompanyArea(['company_id' => $id]);
        //判断是否需要保存派单区域
        if (!empty($company['deliver_area'])) {
            $areaArray = array_map(function ($val) use ($user, $id) {
                return [
                    'company_id' => $id,
                    'area_id' => $val,
                    'create_time' => $user['time'],
                ];
            }, explode(',', $company['deliver_area']));
            $companyModel->saveCompanyArea($areaArray);
        }

        if ($userResult && $companyResult) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 下载Excel
     *
     * @param array $title The title
     * @param array $column The column
     * @param array $list The list
     * @param string $filename The filename
     *
     * @return     mixed
     */
    public function downExcel($title, $filename)
    {
        import('Library.Org.Phpexcel.PHPExcel', "", ".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', "", ".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array('cacheTime' => 300);
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $phpExcel = new \PHPExcel();
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$value);
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $filename . '.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    //判断是否是真会员
    public function getRealCompany($id)
    {

        $result = D('Home/Db/Company')->isRealCompany($id);
        //加会员，注册会员
        if ($result["fake"] == 1 || $result["on"] == 0 && $result["fake"] == 0 ) {
            return true;
        }
        return false;
    }

    public function deleteCompany($id)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id,
        );
        return D('Home/Db/Company')->deleteCompany($map);
    }

    //获取公司img
    public function getCompanyImgs($id)
    {
        $img_list = D('Home/Db/Company')->getCompanyImgs($id);
        foreach ($img_list as $key => $value) {
            //如果是七牛的图片 就把 //staticqn.qizuang.com/ 改为空字符串 统一保证无staticqn.qizuang.com域名
            if ($value['img_host'] == "qiniu") {
                $img_list[$key]['img_path'] = "https://" . C("QINIU_DOMAIN") . "/" . $value['img_path'];
            } else {
                $img_list[$key]['img_path'] = "upload/company/m_" . $value['img_path'];
            }
        }

        return $img_list;
    }

    //获取公司活动
    public function getCompanyActivity($id)
    {
        $act_list = D('Home/Db/Company')->getCompanyActivity($id);
        return $act_list;
    }

    //更新公司活动
    public function saveCompanyActivity($data)
    {
        $act_result = D('Home/Db/Company')->saveCompanyActivity($data);
        return $act_result;
    }

    //获取公司服务类型
    public function getlx()
    {
        $lx_list = D('Home/Db/Company')->getlx();
        return $lx_list;
    }

    //获取公司风格
    public function getfg()
    {
        $lx_list = D('Home/Db/Company')->getfg();
        return $lx_list;
    }

    /**
     * 修改公司状态
     *
     * @param $id
     * @param $state
     * @return bool
     */
    public function changeCompanyState($id, $state)
    {
        if (empty($id)) {
            return false;
        }
        $companyModel = new CompanyModel();
        $data['modify_state'] = $state;
        if (empty($data['op_info_id'])) {
            $data['op_info_id'] = session('uc_userinfo.id');
        }
        if (empty($data['op_info_name'])) {
            $data['op_info_name'] = session('uc_userinfo.name');
        }
        if (empty($data['op_info_last_time'])) {
            $data['op_info_last_time'] = time();
        }
        return $companyModel->changeCompanyState(['userid' => $id], $data);
    }

    /**
     * 记录用户操作日志
     *
     * @param $data
     * @return mixed
     */
    public function saveOpInfoLog($data)
    {
        if (empty($data['admin_id'])) {
            $data['admin_id'] = session('uc_userinfo.id');
        }
        if (empty($data['admin_name'])) {
            $data['admin_name'] = session('uc_userinfo.name');
        }
        if (empty($data['create_time'])) {
            $data['create_time'] = time();
        }
        $logModel = new UserCompanyOpInfoLogModel();
        return $logModel->addLog($data);
    }

    /**
     * 获取VIP客户数量
     *
     * @param $id
     * @return mixed
     */
    public function getvipcompanycount($id)
    {
        $map['a.id'] = array("in", $id);
        $companyModel = new CompanyModel();
        return $companyModel->getCompanyListCount($map);
    }

    /**
     * 根据区县获取公司列表
     * @param array $qx
     * @return array|mixed
     */
    public function getCompanyListByQx($qx = [])
    {
        if (empty($qx)) {
            return [];
        }
        $companyDb = new UserCompanyModel();
        $companys = $companyDb->getCompanyDeliverArea($qx);
        $companys = array_column($companys, 'company_id');
        return $companys;
    }

    /**
     * 给公司添加 订单接收区域 属性
     * @param $companys
     * @return mixed
     */
    public function setCompanyDeliverArea($companys)
    {
        $companyDb = new UserCompanyModel();
        $areas = $companyDb->getDeliverAreaByCompany(array_column($companys, 'id'));
        if (count($areas) > 0) {
            foreach ($areas as $k => $v) {
                $areas[$v['company_id']] .= $v['area_id'] . ',';
                unset($areas[$k]);
            }
        }
        foreach ($companys as $k => $v) {
            $companys[$k]['deliver_area'] = $areas[$v['id']];
        }
        return $companys;
    }


    /**
     * 删除前台公司显示
     *
     * @param $id
     * @param $state
     * @return bool
     */
    public function changeCompanyShow($id, $show = 2)
    {
        if (empty($id)) {
            return false;
        }
        $companyModel = new CompanyModel();
        $data['is_show'] = $show;
        if (empty($data['op_info_id'])) {
            $data['op_info_id'] = session('uc_userinfo.id');
        }
        if (empty($data['op_info_name'])) {
            $data['op_info_name'] = session('uc_userinfo.name');
        }
        if (empty($data['op_info_last_time'])) {
            $data['op_info_last_time'] = time();
        }
        return $companyModel->changeCompanyState(['userid' => $id], $data);
    }

    //是否是有效会员
    public function checkCompanyOn($id)
    {
        $userModel = new UserModel();
        $userInfo = $userModel->getUserCompanyInfo($id);

        $result = false;
        if ($userInfo["on"] == 2 && $userInfo["fake"] == 0) {
            $result = true;
        }

        return $result;
    }

    /**
     * 根据对立公司ID获取名称字符串
     *
     * @param $ids
     * @return string
     */
    public function trueUserNameByIds($ids)
    {
        if (!$ids) {
            return '';
        }
        $map['c.fake'] = ['EQ', 0];
        $map['a.on'] = ['EQ', 2];
        $map['a.id'] = ['IN', $ids];
        $model = new UserModel();
        $list = $model->getUserList($map, 'a.jc');
        return implode(',', array_column($list, 'jc'));
    }

    /**
     * 获取非派单地区
     *
     * @param $cids
     * @return mixed
     */
    public function getNotDeliverArea($cids, $area_ids)
    {
        if (empty($cids) || empty($area_ids)) {
            return [];
        }
        $map['fatherid'] = ['IN', $cids];
        $map['qz_areaid'] = ['NOT IN', $area_ids];
        $quyuModel = new AdminquyuModel();
        return $quyuModel->getAreaList($map);
    }

    /**
     * 获取当前城市中 , 勾选了订单所在区域的装修公司
     * @param $order_info [订单信息] (主要需要 城市/区县)
     * @return mixed
     */
    public function getGiveCompanyListByOrder($order_info)
    {
        $deliver = new CompanyDeliverAreaModel();
        $where = [
            'd.area_id' => ['eq', $order_info['qx']],
            'a.cs' => ['eq', $order_info['cs']],
            'a.on' => ['eq', 2],
            'b.cooperate_mode' => ["EQ",1]
        ];
        unset($order_info);
        $field = 'a.id,a.on,a.jc,a.start,a.end,a.cs,a.qx,b.viptype,b.get_gift_order,b.lng,b.lat,b.cooperation_type,b.lx,b.lxs,b.mianji,b.contract,b.fen_mianji,b.fen_special_needs,b.other_id';
        $company = $deliver->getCompanyListByArea($where, $field);
        //给公司数据添加设置接单区域
        $comLogic = new CompanyLogicModel();
        $company = $comLogic->setCompanyDeliverArea($company);
        return $company;

    }


    //公司注册查询
    public function companyRegisterList($param, $citys)
    {
        $showcity = [];
        foreach ($citys as $key => $val) {
            if (intval($val['cid']) > 0) {
                $showcity[] = intval($val['cid']);
            }
        }
        //条件整理
        $map = [];
        $map['u.classid'] = array('in', array(3,6));    // 3,6
        //城市
        if (isset($param['city']) && intval($param['city']) > 0) {
            $map['u.cs'] = array('eq', intval($param['city']));
        } else {
            $map['u.cs'] = array('in', $showcity);
        }

        //状态
        if (isset($param['status']) && !empty($param['status'])) {
            switch (intval($param['status'])) {
                case 1 :        // 注册公司
                    $map['u.on'] = array('eq', 0);
                    $map['c.fake'] = array('neq', 1);
                    break;
                case 2:         // 合作会员（包括合作中的会员，暂停会员）
                    $map['u.on'] = array('in', array(2, -4));       //-4表示暂停会员
                    $map['c.fake'] = array('eq', 0);
                    break;
                case 3:         // 过期会员
                    $map['u.on'] = array('eq', -1);
                    $map['c.fake'] = array('eq', 0);
                    break;
                case 4:         // 假会员
                    $map['u.on'] = array('eq', 2);
                    $map['c.fake'] = array('eq', 1);
                    break;
                case 5:         // 所有会员（合作会员+过期会员+假会员）
                    $map['u.on'] = array('in', array(2, -1, -4));       //-4表示暂停会员  -1过期会员
                    break;
            }
        }

        //合同时间    contract_start / contract_end  是时间格式:   Y-m-d
        if (!empty($param['contract_start']) && !empty($param['contract_end'])) {
            $map['u.start'] = array('egt', $param['contract_start']);
            $map['u.end'] = array('elt', $param['contract_end']);
        } elseif (!empty($param['contract_start']) && empty($param['contract_end'])) {
            $map['u.start'] = array('egt', $param['contract_start']);

        } elseif (empty($param['contract_start']) && !empty($param['contract_end'])) {
            $map['u.end'] = array('elt', $param['contract_end']);
        }

        //公司简称、ID
        if (isset($param['company_jc']) && !empty($param['company_jc'])) {
            if (intval($param['company_jc']) > 0) {
                $map['u.id'] = array('eq', intval($param['company_jc']));
            } else {
                $map['u.jc'] = array('like', '%' . $param['company_jc'] . '%');
            }
        }
        $usermodel = new UserModel();
        $count = $usermodel->companyRegisterListCount($map);
        $list = [];
        $show = '';
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 10);
            $show = $p->show();
            $list = $usermodel->companyRegisterList($map, $p->firstRow, $p->listRows);
            foreach ($list as $key => $val) {
                $list[$key]['logo'] = changeImgUrl($val['logo']);
                $list[$key]['area_name'] = $val['area_name'] ? $val['area_name'] : '';
                $list[$key]['city_name'] = $val['city_name'] ? $val['city_name'] : '';
                $list[$key]['ip'] = $val['ip'] ? $val['ip'] : '';
                $list[$key]['name'] = $val['name'] ? $val['name'] : '';
                $list[$key]['contract_start'] = $val['contract_start'] ? $val['contract_start'] : '';
                $list[$key]['contract_end'] = $val['contract_end'] ? $val['contract_end'] : '';
                $list[$key]['count_ip'] = intval($val['count_ip']);
                //会员状态
                if($val['on'] == 0 && $val['fake'] != 1){
                    $list[$key]['status'] = 1;  // 注册公司
                }elseif($val['on'] == -1 && $val['fake'] == 0) {
                    $list[$key]['status'] = 3;  //过期会员
                }elseif($val['on'] == 2 && $val['fake'] == 1) {
                    $list[$key]['status'] = 4;  //假会员
                }else{
                    $list[$key]['status'] = 2;  //VIP
                }
                //是否显示“新”标识
                if(date('Y-m',time()) == date('Y-m',$val['register_time'])){
                    $list[$key]['new'] = 1;
                }else{
                    $list[$key]['new'] = -1;
                }

            }
        }
        $return = [];
        $return['list'] = $list;
        $return['page'] = $show;
        return $return;

    }

    /**
     * 根据公司id获取公司详情
     * @param $id
     * @return mixed
     */
    public function getCompanyDetailById($id)
    {
        $usermodel = new UserModel();
        $info = $usermodel->getCompanyDetailById($id);
        if ($info) {
            $info = $info[0];
            $info['logo'] = $info['logo'] ? changeImgUrl($info['logo']) : changeImgUrl("/Public/default/images/default_logo.png");
            $info['area_name'] = $info['area_name'] ? $info['area_name'] : '';
            $info['city_name'] = $info['city_name'] ? $info['city_name'] : '';
            $info['cals'] = $info['cals'] ? $info['cals'] : '';
            $info['contacts_name'] = $info['contacts_name'] ? $info['contacts_name'] : '';
            $info['dz'] = $info['dz'] ? $info['dz'] : '';
            $info['ip'] = $info['ip'] ? $info['ip'] : '';
            $info['jc'] = $info['jc'] ? $info['jc'] : '';
            $info['qc'] = $info['qc'] ? $info['qc'] : '';
            $info['qq'] = $info['qq'] ? $info['qq'] : '';
            $info['qq1'] = $info['qq1'] ? $info['qq1'] : '';
            $info['tel'] = $info['tel'] ? $info['tel'] : '';
            $info['register_time'] = $info['register_time'] ? date("Y-m-d H:i:s",$info['register_time']) : '';
        }
        return $info;
    }


    public function editCompanyInfo($param)
    {
        $usermodel = new UserModel();

        $map = [];
        $map['id'] = array('eq', $param['id']);
        $savedata = [];
        if($param['jc']){
            $savedata['jc'] = $param['jc'];
        }
        if($param['qc']){
            $savedata['qc'] = $param['qc'];
        }
        if($param['dz']){
            $savedata['dz'] = $param['dz'];
        }
        if($param['logo']){
            $savedata['logo'] = $param['logo'];
        }

        M()->startTrans(); // 开启事务
        try{
            $usermodel->saveUserInfo($map,$savedata);

            M()->commit(); // 事务提交
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            return false;
        }

    }

    /**
     * 获取当前公司的下份合同
     * @param $companyInfo
     * @param string $day
     * @return array
     */
    public function getNextContractByCompany($companyInfo, $day = '')
    {
        $company_id = array_column($companyInfo,'id');
        $companyInfo = array_column($companyInfo, null, 'id');
        if (count($company_id) == 0) {
            return [];
        }
        $where = [
            'company_id' => ['in', $company_id],
            'type' => ['neq', 1],
            'start_time' => ['gt', date('Y-m-d')],
        ];
        $vipDb = new UserVipModel();
        $companyContract = $vipDb->getCompanyNewestContract($where);
        if (!empty($day)) {
            //todo .. 需要再优化
            foreach ($companyContract as $k => $v) {
                //获取当前合同距离下次合同的时间
                $offset = (strtotime($v['start']) - $companyInfo[$v['company_id']]['contract_end']) / 86400 + 1;
                if ($offset < $day) {
                    unset($companyContract[$k]);
                }
            }
        }
        return array_column($companyContract,null,'company_id');
    }

    public function getSelectedCitys()
    {
        $model = new \Home\Model\Db\OrdersModel();
        $citys = $model->getAllOpenCitys();
        return $citys;
    }

    public function getCompanyBannerList($input, $limit = 20)
    {
        $model = new CompanyModel();
        $count = $model->getCompanyBannersCount($input);

        if(!$count) return ['list' => '', 'count' => 0, 'pageshow' => null];

        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, $limit);
        $pageshow = $pageClass->show();

        $list = $model->getCompanyBanners($input, $pageClass->firstRow, $pageClass->listRows);
        foreach ($list as $k => &$v) {
            $v['img_path'] = changeImgUrl2($v['img_path']);
            $v['status_desc'] = $this->bannerStatus[(int)$v['status']];
            // $v['type'] = $this->memberStatus[(int)$v['on']];
        }

        return ['list' => $list, 'count' => $count, 'pageshow' => $pageshow];
    }

    public function getCompanyBannerInfo($id){
        $companyModel = new CompanyModel();
        return $companyModel->getCompanyBannerInfo($id);
    }

    public function updateBannerStatus($post) {
        $banner_id = $post['banner_id'];
        $status = (int)$post['mark'];

        $res = false;
        if (!empty($banner_id)) {
            if(in_array($status, array_keys($this->bannerStatus))){
                $companyModel = new CompanyModel();
                $res = $companyModel->updateBannerStatus($banner_id, $status);
            }
        }

        return $res;
    }

    // 装修公司月订单达标状态
    public function setCompanyMonthOrderReachStatus($result, $city_id, $city_name){
        // 查询当前城市订单上限
        $cityQuoteModel = new SaleReportCityQuoteModel();
        $quoteInfo = $cityQuoteModel->getCityUserOrderLimit($city_name);
        $user_order_limit = check_variable($quoteInfo["user_order_limit"], 0);

        $companyIds = []; // 所有1.0会员ID
        $vip_count = 0; // 当前城市会员数量

        // 遍历城市装修公司
        // 1. 记录城市1.0订单上限
        // 2. 计算城市会员总数（包括2.0会员）
        // 3. 拿到所有的1.0会员ID
        
        if (!empty($result[0])) {
            foreach ($result[0] as $key => $city) {
                $result[0][$key]["user_order_limit"] = $user_order_limit;
                foreach ($city["child"] as $k => $user) {
                    // 是否是不可用2.0会员
                    if (!isset($user["un_new_qian_change"])) {
                        $vip_count++;
                    }

                    // 1.0会员
                    if ($user["cooperate_mode"] == 1) {
                        $companyIds[] = $user["id"];
                    }
                }
            }
        }

        if (!empty($result[1])) {
            foreach ($result[1] as $key => $city) {
                $ret = $cityQuoteModel->getCityUserOrderLimit($city["cname"]);
                $result[1][$key]["user_order_limit"] = check_variable($ret["user_order_limit"], 0);

                $result[1][$key]["vip_count"] = 0;
                foreach ($city["child"] as $k => $user) {
                    // 是否是不可用2.0会员
                    if (!isset($user["un_new_qian_change"])) {
                        $result[1][$key]["vip_count"]++;
                    }

                    // 1.0会员
                    if ($user["cooperate_mode"] == 1) {
                        $companyIds[] = $user["id"];
                    }
                }
            }
        }

        // 查询1.0会员公司当月分单量
        if (count($companyIds) > 0) {
            $ordersModel = new OrdersModel();
            $companyOrdersList = $ordersModel->getCompanyFenOrders($companyIds, date("Y-m-01"), date("Y-m-t"));
            $companyOrders = array_column($companyOrdersList, "order_count", "com");
        }

        // 城市基础会员数
        $city_base_users = 6;

        if (!empty($result[0]) && $user_order_limit > 0 && $vip_count >= $city_base_users) {
            foreach ($result[0] as $key => $city) {
                foreach ($city["child"] as $k => $user) {
                    // 1.0会员
                    if ($user["cooperate_mode"] == 1) {
                        $com_order_count = check_variable($companyOrders[$user["id"]], 0);
                        $result[0][$key]["child"][$k]["order_count"] = $com_order_count;

                        if ($com_order_count >= $user["viptype"] * $user_order_limit) {
                            $result[0][$key]["child"][$k]["user_order_limit_state"] = 1;
                        }
                    }
                }
            }
        }

        if (!empty($result[1])) {
            foreach ($result[1] as $key => $city) {
                if ($city["vip_count"] >= $city_base_users && $city["user_order_limit"] > 0) {
                    foreach ($city["child"] as $k => $user) {
                        // 1.0会员
                        if ($user["cooperate_mode"] == 1) {
                            $com_order_count = check_variable($companyOrders[$user["id"]], 0);
                            $result[1][$key]["child"][$k]["order_count"] = $com_order_count;

                            if ($com_order_count >= $user["viptype"] * $city["user_order_limit"]) {
                                $result[1][$key]["child"][$k]["user_order_limit_state"] = 1;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
