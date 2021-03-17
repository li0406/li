<?php


namespace Home\Model\Logic;


use Home\Model\Db\MallAddressModel;
use Home\Model\Db\OrdersModel;
use Home\Model\Db\UcenterBlockModel;
use Home\Model\Db\UcenterPlatformModel;
use Home\Model\Db\UcenterUserModel;
use Home\Model\Db\UmengUserRecordModel;
use Home\Model\Db\ZxsActionNoticeModel;
use Think\Db;

class UcenterUserLogicModel
{
    protected $ucenterUserModel;
    protected $ucenterPlatformModel;
    protected $ucenterBlockModel;

    public function __construct()
    {
        $this->ucenterUserModel = new UcenterUserModel();
        $this->ucenterPlatformModel = new UcenterPlatformModel();
        $this->ucenterBlockModel = new UcenterBlockModel();
    }

    public function getAllUserList($param)
    {
        $map = $this->setmap($param);
        $sort = 'u.uuid desc';

        //排序   zctime  注册时间排序   1：倒序 2：正序
        if (isset($param['zctime']) && intval($param['zctime']) == 1) {
            $sort = 'registered_at desc,u.uuid desc';
        } elseif (isset($param['zctime']) && intval($param['zctime']) == 2) {
            $sort = 'registered_at asc,u.uuid asc';
        }

        //lttime   最后活跃时间排序    1：倒序  2：正序
        if (isset($param['lttime']) && intval($param['lttime']) == 1) {
            $sort = 'last_actived_at desc,u.uuid desc';
        } elseif (isset($param['lttime']) && intval($param['lttime']) == 2) {
            $sort = 'last_actived_at asc,u.uuid asc';
        }


        $count = $this->ucenterUserModel->getAllUserByMapCount($map);
        $pageCount = 20;
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();
            $list = $this->ucenterUserModel->getAllUserByMap($map, intval($p->nowPage - 1) * $pageCount, $p->listRows, $sort);
            $phonelist = [];
            $ids = [];
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    //默认头像？
                    $list[$key]['avatar'] = $val['avatar'] ? changeImgUrl($val['avatar'], 2) : 'https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB';
                    if ($val['phone']) {
                        $phonelist[] = tel_encrypt($val['phone']);
                    }
                    $ids[] = $val['uuid'];
                }
                if (!empty($phonelist)) {
                    $date = strtotime("-1 month");
                    $ordermodel = new OrdersModel();
                    $result = $ordermodel->getLastOrder($phonelist, $date);
                    if (count($result) > 0) {
                        foreach ($list as $key => $val) {
                            foreach ($result as $k => $v) {
                                if (tel_encrypt($val['phone']) == $v['tel_encrypt']) {
                                    $list[$key]['is_ordered'] = 1;
                                    break;
                                }
                            }
                        }

                    }
                }

                //获取是否有收货地址
                if (!empty($ids)) {
                    $malladdressmodel = new MallAddressModel();
                    $addresslist = $malladdressmodel->checkHadAddress($ids);
                    $countaddress = count($addresslist);
                    foreach ($list as $key => $val) {
                        $list[$key]['hadaddress'] = 0;
                        if ($countaddress > 0) {
                            foreach ($addresslist as $k => $v) {
                                if ($val['uuid'] == $v['uuid']) {
                                    $list[$key]['hadaddress'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

            }


        }
        $return = [];
        $return['page'] = $show ? $show : "";
        $return["list"] = $list ? $list : [];
        return $return;

    }


    public function getPlatformUserList($param)
    {
        $map = $this->setmap($param);

        $sort = 'u.uuid desc';
        $sort2 = 't.uuid desc';

        //fttime   开始活跃时间排序    1：倒序  2：正序
        if (isset($param['fttime']) && intval($param['fttime']) == 1) {
            $sort = 'first_actived_at desc,u.uuid desc';
            $sort2 = 'first_actived_at desc,t.uuid desc';
        } elseif (isset($param['fttime']) && intval($param['fttime']) == 2) {
            $sort = 'first_actived_at asc,u.uuid asc';
            $sort2 = 'first_actived_at asc,t.uuid asc';
        }

        //lttime   最后活跃时间排序    1：倒序  2：正序
        if (isset($param['lttime']) && intval($param['lttime']) == 1) {
            $sort = 'last_actived_at desc,u.uuid desc';
            $sort2 = 'last_actived_at desc,t.uuid desc';
        } elseif (isset($param['lttime']) && intval($param['lttime']) == 2) {
            $sort = 'last_actived_at asc,u.uuid asc';
            $sort2 = 'last_actived_at asc,t.uuid asc';
        }

        $count = $this->ucenterUserModel->getPlatformUserListCount($map, $param['source2']);
        $pageCount = 20;
        $show = '';
        $list = [];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();
            $list = $this->ucenterUserModel->getPlatformUserList($map, intval($p->nowPage - 1) * $pageCount, $p->listRows, $sort, $sort2, $param['source2']);
            $newlist = [];
            foreach ($list as $key => $value) {
                if (empty($newlist[$value['id']])) {
                    $newlist[$value['id']] = $value;
                } else {
                    unset($newlist[$value['id']]['oauth_typelist']);
                }
                //默认头像？ 
                $newlist[$value['id']]['avatar'] = $value['avatar'] ? changeImgUrl($value['avatar'], 2) : 'https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB';
                $oauthlist = explode(',', $value['oauth_typelist']);
                //微信
                if (in_array(1, $oauthlist)) {
                    $newlist[$value['id']]['wx_oauth'] = 1;
                } else {
                    $newlist[$value['id']]['wx_oauth'] = 0;
                }
                //微博
                if (in_array(2, $oauthlist)) {
                    $newlist[$value['id']]['wb_oauth'] = 1;
                } else {
                    $newlist[$value['id']]['wb_oauth'] = 0;
                }
                //qq
                if (in_array(3, $oauthlist)) {
                    $newlist[$value['id']]['qq_oauth'] = 1;
                } else {
                    $newlist[$value['id']]['qq_oauth'] = 0;
                }

            }
        }
        $return = [];
        $return['page'] = $show ? $show : "";
        $return["list"] = $newlist ? $newlist : [];
        return $return;

    }


    //设置map
    private function setmap($param)
    {
        $map = [];
        //注册时间
        if ($param['zhuce_start'] && $param["zhuce_end"]) {       //CCONTROLLER做处理，如果没有值就都没有值，如果一个有值就都有值，

            $map["b.registered_at"] = array("between", array($param['zhuce_start'], $param["zhuce_end"]));
        }
        //最近活跃时间
        if ($param['huoyue_start'] && $param["huoyue_end"]) {
            $map["a.last_actived_at"] = array("between", array($param['huoyue_start'], $param["huoyue_end"]));
        }
        //首次活跃时间
        if ($param['first_huoyue_start'] && $param["first_huoyue_end"]) {
            $map["a.first_actived_at"] = array("between", array($param['first_huoyue_start'], $param["first_huoyue_end"]));
        }
        //城市
        if ($param['city']) {
            $map["b.city"] = array("eq", intval($param["city"]));
        }

        //类型
        if ($param['type']) {
            $map['u.type'] = array("eq", intval($param['type']));
        }

        //来源
        if ($param['source']) {
            $map["u.platform"] = array("eq", $param["source"]);
        }
        //昵称
        if ($param["nickname"] || $param["nickname"] != "") {
            $map["b.nickname"] = array("like", "%" . $param["nickname"] . "%");
        }
        //状态
        if ($param["status"]) {
            $map["u.status"] = array("eq", $param["status"]);
        }

        return $map;

    }

    //获取用户数量
    public function getUcenterUserCount($platform = "")
    {
        $map = [];
        if ($platform) {      //有条件的
            $count = $this->ucenterUserModel->getPlatformUserCount($platform);
        } else {              //所有用户数量
            $map['u.platform'] = array('neq', '');
            $count = $this->ucenterUserModel->getAllUserByMapCount($map);
        }

        return $count ? $count : 0;
    }


    /**
     * 根据id获取用户基本信息并处理
     * @param $id
     * @return mixed
     */
    public function getUserBasicInfoById($id)
    {
        $basicinfo = $this->ucenterUserModel->getUserBasicInfoById($id);
        if ($basicinfo['phone']) {
            $date = strtotime("-1 month");

            $ordermodel = new OrdersModel();
            $result = $ordermodel->getLastOrder(tel_encrypt($basicinfo['phone']), $date);
            if (count($result) > 0) {
                $basicinfo['is_ordered'] = 1;
            }


            $basicinfo['phone'] = substr_replace($basicinfo['phone'], "*****", 3, 5); //做一个中间为星号的号码
        }
        if ($basicinfo['email']) {
            $wz = strrpos($basicinfo['email'], "@");
            $basicinfo['email'] = substr_replace($basicinfo['email'], "*****", 2, ($wz - 2)); //做一个中间为星号的号码
        }

        $basicinfo['avatar'] = $basicinfo['avatar'] ? changeImgUrl($basicinfo['avatar'], 2) : 'https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB';
        return $basicinfo;
    }


    public function getPlatformUserinfo($uuid)
    {
        //产品线
        $newlist = [];
        $list = $this->ucenterPlatformModel->getList();
        foreach ($list as $key => $value) {
            $result = $this->ucenterUserModel->getPlatformUserinfo($uuid, $value['slot']);
            if (!$result) {
                continue;
            }
            $newlist[$value['slot']]['first_actived_at'] = $result['first_actived_at'] ? $result['first_actived_at'] : '';
            $newlist[$value['slot']]['last_actived_at'] = $result['last_actived_at'] ? $result['last_actived_at'] : '';
            $newlist[$value['slot']]['platform'] = $value['name'];
            if ($result['oauth_typelist']) {
                $newlist[$value['slot']]['no_oauth'] = 0;
                $oauthlist = explode(',', $result['oauth_typelist']);
                //微信
                if (in_array(1, $oauthlist)) {
                    $newlist[$value['slot']]['wx_oauth'] = 1;
                } else {
                    $newlist[$value['slot']]['wx_oauth'] = 0;
                }
                //微博
                if (in_array(2, $oauthlist)) {
                    $newlist[$value['slot']]['wb_oauth'] = 1;
                } else {
                    $newlist[$value['slot']]['wb_oauth'] = 0;
                }
                //QQ
                if (in_array(3, $oauthlist)) {
                    $newlist[$value['slot']]['qq_oauth'] = 1;
                } else {
                    $newlist[$value['slot']]['qq_oauth'] = 0;
                }
            } else {
                $newlist[$value['slot']]['no_oauth'] = 1;
            }
        }
        return $newlist ? $newlist : [];

    }


    // 获取用户的状态 拉黑？ 还是未拉黑
    public function getUcenterUserstatusById($id)
    {
        return $this->ucenterUserModel->getUcenterUserstatusById($id);
    }

    //拉黑action
    public function pullBlackAction($uuid, $remark)
    {
        $map = [];
        $map['uuid'] = array('eq', $uuid);
        $data = [];
        $data['status'] = -1;
        $data['updated_at'] = time();

        M()->startTrans(); // 开启事务
        try {
            $result = $this->ucenterUserModel->changeUcenterUser($map, $data);
            if ($result) {
                $newdata = [];
                $newdata['uuid'] = $uuid;
                $newdata['reason'] = $remark;
                $newdata['operator_id'] = session("uc_userinfo.id");
                $newdata['created_at'] = time();
                $newdata['updated_at'] = time();
                $this->ucenterBlockModel->addblock($newdata);

                $result = true;
                M()->commit(); // 事务提交
            } else {
                M()->rollback(); // 事务回滚
                $result = false;
            }
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            $result = false;
        }
        return $result;

    }

    //取消拉黑
    public function cancelPullBlack($uuid)
    {
        $map = [];
        $map['uuid'] = array('eq', $uuid);
        $data = [];
        $data['status'] = 1;
        $data['updated_at'] = time();
        $result = $this->ucenterUserModel->changeUcenterUser($map, $data);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    //更改用户类型
    public function changeUserType($uuid, $type)
    {
        $map = [];
        $map['uuid'] = array('eq', $uuid);
        $data = [];
        $data['type'] = $type;
        $data['updated_at'] = time();

        M()->startTrans(); // 开启事务
        try {
            $result = $this->ucenterUserModel->changeUcenterUser($map, $data);
            if ($result) {
                $result = true;
                M()->commit(); // 事务提交
            } else {
                M()->rollback(); // 事务回滚
                $result = false;
            }
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            $result = false;
        }
        return $result;

    }

    /**
     * 根据用户id获取手机号
     */
    public function getUserPhoneById($id)
    {
        $result = $this->ucenterUserModel->getUserPhoneById($id);
        return $result['phone'];
    }

    //下线所有用户
    public function loginOutAll($uuid)
    {
        //解析token,过期 清除session,否则拿redis值
        import('Library.Org.Passport.JwtToken', '', '.php');
        $jwt = new \JwtToken();
        $result = $jwt->loginOutAllV2($uuid);
        if ($result === true) {
            return true;
        } else {
            return false;
        }

    }

    public function getUserDeviceTokenById($uuid)
    {
        $model = new UmengUserRecordModel();
        return $model->getUserDeviceTokenById($uuid);
    }

    public function sendUserNotice($user,$action_id)
    {
        $all = [];
        foreach ($user as $item) {
            $all[] = [
                "action_id" => $action_id,
                "user_id" => $item,
                "type" => 1,
                "time" => time(),
                "action_type" => 6
            ];
        }
        $model = new ZxsActionNoticeModel();
        return $model->addAllInfo($all);
    }
}