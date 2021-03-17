<?php

//专题页
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;

class SpecialController extends MobileBaseController{

    public function index(){
        $this->_error();
    }

    //微信专页
    public function about(){
        //$this->assign("info",$info);
        $this->display();
    }


    public function qixi(){
        //获取最新发布订单信息
        //关键字、描述、标题
        $basic["head"]["title"] = "七夕，装修点亮生活-齐装网";
        $basic["head"]["keywords"] = "七夕情人节装修活动，齐装网";
        $basic["head"]["description"] = "齐装网在七夕情节人来临之际，特为广大业主提供七夕，装修点亮生活活动，欢迎大家积极参与。";
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('source',407);//设置发单入口标识
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->display();
    }

    public function guoqing(){
        $headInfo = [
            'title'=>'迎中秋庆国庆，好礼嗨不停-齐装网',
            'description'=>'齐装网在国庆中秋来临之际，特为广大业主提供双节旅游活动，欢迎大家积极参与。',
            'keywords'=>'国庆中秋双节装修活动，齐装网',
        ];
        $this->assign('headInfo',$headInfo);
        $this->display();
    }

    public function zhuli(){
        //获取用户信息
        $sendUrl = $this->getUserOpenid(0);
        $this->assign('url', $sendUrl);
        $this->display();
    }

    public function zhulijin($openid){
        //初始值
        $info['zlws'] = 1; //资料完善
        $info['is_first'] = 1; //是否第一次打开
        $info['is_winning'] = 0; //是否获奖
        $info['is_end'] = 0; //是否已经奖品全部被领取
        if(!$openid){
            header("Location:/act/zhuli");
        }
        //获取活动id
        $activityInfo = M('activity')->where(['name' => ['eq', '齐装网双节活动-助力抢双人游']])->field('id,name')->find();
        //判断数据库是否存在
        $info = D('WeixinActivity')->getInfoByOpenid($openid,$activityInfo['id']);
        if (!$info) {
            $data['openid'] = $openid;
            $saveId =  D('WeixinActivity')->saveActivityInfo($data);
            $info = M('weixin_activity')->find($saveId);
            $info['is_first'] = 1;
        }else{
            $info['is_first'] = 0;
        }
        //如果积分到3680 则提示过奖,改数据库
        if($info['integral_amount'] >= 3680){
            //如果已经有9个人 达到3680,则更换提示
            $where['activity_sign'] = ['eq','齐装网双节活动-助力抢双人游'];
            $where['is_winning'] = ['eq',1];
            $winningCount = D('WeixinActivity')->getWinningCount($where);
            if($winningCount >=9 ){
                $save['integral_amount'] = 3680;
                D('WeixinActivity')->updateActivityInfo($save,$info['id']);
                $info = M('weixin_activity')->find($info['id']);
                $info['is_end'] = 1;
            }else{
                $save['is_winning'] = 1;
                $save['integral_amount'] = 3680;
                D('WeixinActivity')->updateActivityInfo($save,$info['id']);
                $info = M('weixin_activity')->find($info['id']);
                //判断是否完善过资料
                if($info['tel'] && $info['name']){
                    $info['zlws'] = 1;
                }else{
                    $info['zlws'] = 0;
                }
            }
        }
        //获取助力奖金
        $info['zhuli'] = $info['integral_amount']-1000;

        //分享操作
        $saveConfig = $this->fenxiang();
        $this->assign('saveConfig', $saveConfig);
        $this->assign('info', $info);
        $this->display('zhulijin');
    }

    /**
     * 幸运抢活动
     */
    public function xinyunqiang($data = ''){
        $info['is_join'] = 0;//是否参与过
        $info['is_time'] = 0;//是否在时间段中
        $info['is_wanshan'] = 0;//是否完善过资料
        $info['is_winning'] = 0;//是否获奖
        $info['is_back'] = '';//是否回调页面
        $info['edit_id'] = '';//编辑信息id
        //判断是否是回调回来的 第一次打开页面则直接跳转
        if ($data) {
            //获取活动id
            $activityInfo = M('activity')->where(['name' => ['eq', '齐装网双节活动-幸运抢']])->field('id,name')->find();
            //判断是否能获奖/是否在时间段
            $status = $this->getWinningInfo();
            //搜索是否已经参加活动
            $userinfo = D('WeixinActivity')->getInfoByOpenid($data['openid'], $activityInfo['id']);
            //1.是否在时间段中
            if ($status['is_time'] == 1) {
                $info['is_time'] = 1;
                //2.判断是否参与过
                if ($userinfo) {
                    $info['edit_id'] = $userinfo['id'];
                    $info['is_join'] = 1;
                    //2.1是否已经获奖
                    if ($userinfo['is_winning'] == 1) {
                        $info['is_winning'] = 1;
                        //2.1.1 是否完善过资料
                        if ($userinfo['name'] && $userinfo['tel']) {
                            $info['is_wanshan'] = 1;
                        }
                    } else {
                        //2.2 没中过奖(相当于新用户)
                        if ($status['is_winning']) {
                            //中奖则保存数据
                            $save = [
                                'is_winning' => 1,
                                'winning_time' => time(),
                            ];
                            $jieguo = D('WeixinActivity')->updateActivityInfo($save, $userinfo['id']);
                            //更新失败 则提示未获奖
                            if ($jieguo) {
                                $info['is_winning'] = 1;
                            }
                        }
                    }
                } else {
                    //3. 没参与过 没中过奖
                    if ($status['is_winning']) {
                        //中奖则保存数据
                        $save = [
                            'activity_sign' => $activityInfo['name'],
                            'activity_id' => $activityInfo['id'],
                            'openid' => $data['openid'],
                            'integral_amount' => 0,
                            'addtime' => time(),
                            'is_winning' => 1,
                            'winning_time' => time(),
                        ];
                        $jieguo = D('WeixinActivity')->saveUserInfo($save);
                        //更新失败 则提示未获奖
                        if ($jieguo) {
                            $info['edit_id'] = $jieguo;
                            $info['is_winning'] = 1;
                        } else {
                            $info['is_winning'] = 0;
                        }
                    } else {
                        $save = [
                            'activity_sign' => $activityInfo['name'],
                            'activity_id' => $activityInfo['id'],
                            'openid' => $data['openid'],
                            'integral_amount' => 0,
                            'addtime' => time(),
                            'is_winning' => 0,
                        ];
                        D('WeixinActivity')->saveUserInfo($save);
                        $info['is_winning'] = 0;
                    }
                }
            }
            $info['is_back'] = 1;
        } else {
            $url = urlencode(C('WX_URL') . 'act/xinyunqiangFuncBack');
            $appid = C('WX_APPID');//微信标识
            $sendUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_base&state=xinyunqiang#wechat_redirect';
            $this->assign('url', $sendUrl);
        }
        $this->assign('info', $info);
        $this->display('xinyunqiang');
    }

    /**
     * 幸运抢回调
     */
    public function xinyunqiangFuncBack(){
        $code = I('get.code');
        $state = I('get.state');
        $appid = C('WX_APPID');
        $appsecret = C('WX_APPSECRET');
        //获取用户信息
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
        $data = $this->sendGetRequest($url);
        //判断重复刷新页面则跳转
        if($data['errcode'] == 40163){
            $url = urlencode(C('WX_URL').'act/xinyunqiangFuncBack');
            $appid = C('WX_APPID');//微信标识
            $sendUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_base&state=xinyunqiang#wechat_redirect';
            header('Location:'.$sendUrl);
            die();
        }
        $this->xinyunqiang($data);
    }

    /**
     * @param string $back 被助力编辑id
     * @param string $openid  帮助人openid
     */
    public function friend($back = '',$openid = ''){
        if(!$back){
            $get = I('get.');
            //获取活动id
            $activityInfo = M('activity')->where(['name' => ['eq', '齐装网双节活动-助力抢双人游']])->field('id,name')->find();
            $info = D('WeixinActivity')->getInfoByOpenid($get['openid'],$activityInfo['id']);
            $sendUrl = $this->getUserOpenid($info['id']);
            //查看我的助力
            $myUrl = $this->getUserOpenid(0);
            $this->assign('url', $myUrl);
            $this->assign('sendUrl', $sendUrl);
        }else{
            //添加数据里
            $data = $this->updateMoney($back,$openid);
            //查看我的助力
            $myUrl = $this->getUserOpenid(0);
            $this->assign('url', $myUrl);
            $this->assign('integral_amount', $data['integral_amount']);
            $this->assign('status', $data['status']);
            $this->assign('info', $data['info']);
        }
        $this->display('friend');
    }

    public function rules(){
        $this->display();
    }

    public function huanxinjia(){
        if (IS_POST) {
            $post = I('post.');
            if ($post['address']) {
                $post['address'] = remove_xss($post['address']);
            }
            //查询用户是否已经参加活动
            $join = D("Activityuserinfo")->findUserInfo($post['order_id']);
            if($join){
                $this->ajaxReturn(['status' => 0, 'info' => '抱歉！您已参加活动！']);
            }
            //活动信息
            $activity_name = '11月焕新家-移动';
            $activityInfo = M('activity')->where(['name' => ['eq', $activity_name]])->field('id,name')->find();
            if (!$activityInfo) {
                $this->ajaxReturn(['status' => 0, 'info' => '未知活动！']);
            }
            $post['activity_id'] = $activityInfo['id'];
            //活动奖品信息
            $prizeInfo = D("Activityprize")->getPrizeInfoByActivity($activityInfo['id']);
            if (!$prizeInfo) {
                $this->ajaxReturn(['status' => 0, 'info' => '未知活动奖品！']);
            }
            $post['prize_id'] = $prizeInfo['id'];
            $post['time'] = time();
            $dd = D("Activityuserinfo")->addUserInfo($post);
            if($dd){
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'info' => '地址添加失败！']);
            }
        }
        $this->display();
    }

    public function travel(){
        if (IS_POST) {
            $post = I('post.');
            if ($post['address']) {
                $post['address'] = remove_xss($post['address']);
            }
            //查询用户是否已经参加活动
            $join = D("Activityuserinfo")->findUserInfo($post['order_id']);
            if($join){
                $this->ajaxReturn(['status' => 0, 'info' => '抱歉！您已参加活动！']);
            }
            //活动信息
            $activity_name = '装修送港澳豪华游';
            $activityInfo = M('activity')->where(['name' => ['eq', $activity_name]])->field('id,name')->find();
            if (!$activityInfo) {
                $this->ajaxReturn(['status' => 0, 'info' => '未知活动！']);
            }
            $post['activity_id'] = $activityInfo['id'];
            //活动奖品信息
            $prizeInfo = D("Activityprize")->getPrizeInfoByActivity($activityInfo['id']);
            if (!$prizeInfo) {
                $this->ajaxReturn(['status' => 0, 'info' => '未知活动奖品！']);
            }
            $post['prize_id'] = $prizeInfo['id'];
            $post['time'] = time();
            $dd = D("Activityuserinfo")->addUserInfo($post);
            if($dd){
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'info' => '地址添加失败！']);
            }
        }
        $header = [
            'title'=>'装修送旅游，12月专修享5天4晚港澳游-齐装网',
            'description'=>'12月装修领旅游卡，齐装网',
            'keywords'=>'齐装网在年终12月来临之际，特为广大业主提供装修送旅游福利，欢迎大家积极参与。',
        ];
        $this->assign('headInfo',$header);
        $this->display();
    }

    public function suning(){
        $cityInfo = $this->cityInfo;
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $bm = I('get.bm');
        $bms = [
            'cd' => ['name' => '成都', 'source' => 17122233, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=8270171612&storeType=2&storeCode=10006701'],
            'nc' => ['name' => '南昌', 'source' => 17122343, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=4225046456&storeType=0&storeCode=7960'],
            'wh' => ['name' => '武汉', 'source' => 17122249, 'back_url' => 'https://res.m.suning.com/project/zhaoji/activiteDetails_1.html?activityCode=1099210084&storeType=2&storeCode=10004711'],
            'xa' => ['name' => '西安', 'source' => 17122247, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=6236030148&storeType=2&storeCode=10007701'],
            'ty' => ['name' => '太原', 'source' => 17122256, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=1338190737&storeType=0&storeCode=7281'],
            'sjz' => ['name' => '石家庄', 'source' => 17122207, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=3153579802&storeType=2&storeCode=10001106'],
            'nn' => ['name' => '南宁', 'source' => 17122220, 'back_url' => 'https://t.suning.cn/9dw6lg'],
            'gy' => ['name' => '贵阳', 'source' => 17122231, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=7365805150&storeType=2&storeCode=10007304'],
            'sz' => ['name' => '苏州', 'source' => 17122237, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=2237331032&storeType=2&storeCode=10028764'],
            'tj' => ['name' => '天津', 'source' => 17122230, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=0190513696&storeType=2&storeCode=10000887'],
            'sh' => ['name' => '上海', 'source' => 17122219, 'back_url' => 'https://res.m.suning.com/project/zhaoji/activiteDetails_1.html?activityCode=8751807464&storeType=0&storeCode=7068'],
            'zz' => ['name' => '郑州', 'source' => 17122227, 'back_url' => 'https://mois.suning.com/signUpTemplet/sendRedirect.do?activityCode=7725990677&storeType=2&storeCode=10004911'],
            'bj' => ['name' => '北京', 'source' => 17122225, 'back_url' => 'https://apup.cnsuning.com/apup-admin-web/company-detail'],
        ];
        //设置默认城市
        if (!in_array($bm, array_keys($bms))) {
            $bm = 'sz';
        }
        $keys = [
            'title' => '跨年狂欢，苏宁易购特惠家电助力齐装网装修盛宴-' . $bms[$bm]['name'],
            'description' => '跨年狂欢，享苏宁易购特惠家电，优惠不容错过。',
            'keywords' => '跨年狂欢，报名齐装网即享苏宁易购全场优惠。特惠家电大折扣，全新优惠，精彩不容错过。',
        ];
        $this->assign("info",$info);
        $this->assign('cityInfo',$cityInfo);
        $this->assign('bms', $bms);
        $this->assign('bm', $bm);
        $this->assign('keys', $keys);
        $this->display();
    }

    //更新用户信息
    public function updateUserInfo(){
        $post = I('post.');
        $save['name'] = remove_xss($post['name']);
        $save['tel'] = remove_xss($post['tel']);
        //获取地址
        $save['address'] = remove_xss($this->getAdressInfo($post));
        $save['winning_time'] = time();
        //是否放弃
        if($post['del']){
            $save = [];
            $save['winning_time'] = 0;
            $save['is_winning'] = 0;
        }
        $status = D('WeixinActivity')->updateActivityInfo($save,$post['edit_id']);
        if($status){
            $this->ajaxReturn(['info'=>'','status'=>1]);
        }else{
            $this->ajaxReturn(['info' => '完善信息失败!', 'status' => 0]);
        }
    }

    //大礼包免费领
    public function coupon(){
        $head = [
            'title' => '装修享好礼，享20000元新优惠券福利-齐装网',
            'keywords' => '装修享好礼，建材家具家居优惠券，价值20000元，优惠不容错过。',
            'description' => '装修享好礼，报名赢取20000元优惠券。装修建材家具家，全新优惠，精彩不容错过。',
        ];
        $this->assign('head',$head);
        $this->display();
    }

    public function qudao()
    {
        $id = I("get.id");
        switch ($id) {
            case 'coupon-1':
                $head = [
                    'title'=>'年终狂欢趴，享20000元新装修优惠券福利-齐装网',
                    'keywords'=>'年终装修大促，报名赢取20000元装修优惠券。装修建材家具家通通，全新优惠，精彩不容错过。',
                    'description'=>'年终装修大优惠，建材家具家居优惠券，价值20000元，优惠不容错过。',
                ];
                break;
            case 'coupon-2':
                $head = [
                    'title'=>'装修报价',
                    'keywords'=>'装修公司,装修网,齐装网',
                    'description'=>'齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！',
                ];
                break;
        }
        $this->assign('head',$head);
        $this->display("Mobile@Special/Qudao/".$id);
    }
    // a.19.08.03 复制生成腾讯新闻媒体落地页
    public function zk()
    {
        $head = [
        'title'=>'装修报价',
        'keywords'=>'装修公司,装修网,齐装网',
        'description'=>'齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！',
        ];
        $this->assign('head',$head);
        $this->display();
    }
    //12月活动
    public function zxj(){
        if (IS_POST) {
            $activity_name = '2018齐装装修季';
            //保存用户数据
            $state = $this->saveUserAdressInfo(I('post.'),$activity_name);
            if($state){
                $this->ajaxReturn(['status' => 1, 'info' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'info' => '地址添加失败！']);
            }
        }
        $head = [
            'title'=>'齐装网福利！分享集赞瓜分200G流量',
            'keywords'=>'年终装修大促，报名赢取20000元装修优惠券。装修建材家具家通通，全新优惠，精彩不容错过。',
            'description'=>'年终装修大优惠，建材家具家居优惠券，价值20000元，优惠不容错过。',
        ];
        $this->assign('headInfo',$head);
        $this->display();
    }

    //12月活动(正式)
    public function zxj_zhengshi(){
        $head = [
            'title'=>'2018装修季，享20000元装修大礼包等三重优惠--齐装网',
            'keywords'=>'2018装修季，享三重装修好礼，优惠不容错过。',
            'description'=>'2018装修季，报名赢取三重好礼。装修建材家具家电折扣，全新优惠，精彩不容错过。',
        ];
        $this->assign('headInfo',$head);
        $this->display();
    }

    //判断幸运抢是否获奖
    private function getWinningInfo()
    {
        //获取活动id
        $activityInfo = M('activity')->where(['name'=>['eq','齐装网双节活动-幸运抢']])->field('id,name,start,end')->find();
        //初始化数据
        $val = [
            'is_winning' => 0, //是否能获奖
            'is_time' => 0, //是否在活动时间
        ];
        //判断当前时间是否在活动时间
        $activity_s1 = strtotime($activityInfo['start']); //第一次时间 10.1 10点-22点
        $activity_e1 = '1506866400';
        $activity_s2 = '1507082400'; //第一次时间 10.4 10点-22点
        $activity_e2 = strtotime($activityInfo['end']);
//        $activity_s1 = '1506668400';
//        $activity_e1 = '1506774020';
//        $activity_s2 = '1507082400';
//        $activity_e2 = '1507125600';
        $now = time();
        if ($now >= $activity_s1 && $now <= $activity_e1) {
            //第一时间段中
            $where['activity_id'] = ['eq',$activityInfo['id']];
            $where['winning_time'] = ['EGT',$activity_s1];
            $where['winning_time'] = ['ELT',$activity_e1];
            $where['is_winning'] = ['eq', 1];
            $winningCount = D('WeixinActivity')->getWinningCount($where); //查询获奖个数
            if ($winningCount >= 4) {
                $val = [
                    'is_winning' => 0,
                    'is_time' => 1,
                ];
                return $val;
            } else {
                $val = [
                    'is_winning' => 1,
                    'is_time' => 1,
                ];
                return $val;
            }
        } elseif ($now >= $activity_s2 && $now <= $activity_e2) {
            //第二时间段中
            $where['activity_id'] = ['eq',$activityInfo['id']];
            $where['winning_time'] = ['EGT',$activity_s2];
            $where['winning_time'] = ['ELT',$activity_e2];
            $where['is_winning'] = ['eq', 1];
            $winningCount = D('WeixinActivity')->getWinningCount($where); //查询获奖个数
            if ($winningCount >= 4) {
                $val = [
                    'is_winning' => 0,
                    'is_time' => 1,
                ];
                return $val;
            } else {
                $val = [
                    'is_winning' => 1,
                    'is_time' => 1,
                ];
                return $val;
            }
        } else {
            return $val;
        }
    }

    //获取省市区
    private function getAdressInfo($data){
        $province = M('province')->field('qz_provinceid,qz_province')->where(['qz_provinceid' => ['eq', $data['province']]])->find();
        $city = M('quyu')->field('cid,cname')->where(['cid' => ['eq', $data['city']]])->find();
        $area = M('area')->field('qz_areaid,qz_area')->where(['qz_areaid' => ['eq', $data['area']]])->find();
        return $province['qz_province'] . ' ' . $city['cname'] . ' ' . $area['qz_area'];
    }

    //获取openid
    private function getUserOpenid($state)
    {
        $url = urlencode(C('WX_URL').'act/funcBack');
        $appid = C('WX_APPID');//微信标识
        $sendUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';
//        $this->sendGetRequest($sendUrl);
        return $sendUrl;
    }

    /**
     * 获取当前用户的唯一标识 (回调函数)
     * @param $code
     * @return mixed
     */
    public function funcBack(){
        $code = I('get.code');
        $state = I('get.state');
        $appid = C('WX_APPID');
        $appsecret = C('WX_APPSECRET');
        //获取用户信息
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
        $data = $this->sendGetRequest($url);
        //判断重复刷新页面则跳转助力页面
        if($data['errcode'] == 40163){
            $myUrl = $this->getUserOpenid(0);
            header('Location:'.$myUrl);
            die();
        }
        if($state == 0){
            //获取助力金所需数据
            $this->zhulijin($data['openid']);
        }else{
            $this->friend($state,$data['openid']);;
        }

    }

    //获取分享操作信息

    private function fenxiang()
    {
        $appid = C('WX_APPID');
        $appsecret = C('WX_APPSECRET');
        $timestamp = time();
        $jsapi_ticket = $this->make_ticket($appid, $appsecret);
        $nonceStr = $this->make_nonceStr();
        $url = $this->SCHEME_HOST['scheme_full'] . $this->SCHEME_HOST['host'] . $_SERVER['REQUEST_URI'];
        $signature = $this->make_signature($nonceStr, $timestamp, $jsapi_ticket, $url);
        $saveConfig = [
            'appid' => $appid,
            'timestamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
        ];
        return $saveConfig;
    }

    /**
     * 更新金钱
     * @param $edit_id 被帮助人信息
     * @param $openid  帮助人openid
     * @return array
     */
    public function updateMoney($edit_id,$openid){
        //查询被帮助者信息
        $data = D('WeixinActivity')->getInfoById($edit_id);
        if(!$data){
            return ['info' => '未找到助力用户!', 'status' => 0];
        }
        //查询是否帮助过别人
        $where['user_openid'] = $data['openid'];
        $where['help_openid'] = $openid;
        $guanxi = M('weixin_activity_relation')->where($where)->find();
        if($guanxi){
            return ['info' => '已经帮助过好友助力!','integral_amount'=>$guanxi['integral_amount'], 'status' => 0];
        }
        $integral_amount = mt_rand(70,100);
        $save['integral_amount'] = $data['integral_amount'] + $integral_amount;
        $status = D('WeixinActivity')->updateActivityInfo($save,$edit_id);
        if($status){
            //保存活动关系表
            $relation = [
                'activity_sign' => '齐装网双节活动-助力抢双人游',
                'user_openid' => $data['openid'],
                'help_openid' => $openid,
                'integral_amount' => $integral_amount,
                'addtime' => time(),
            ];
            $dd = M('weixin_activity_relation')->add($relation);
            if($dd){
                return ['status'=>1,'integral_amount'=>$integral_amount];
            }
        }
        return ['info' => '助力失败!', 'status' => 0];
    }

    /**
     * 获取当前用户的唯一标识
     * @param $code
     * @return mixed
     */
//    private function getUserInfo($code){
//        $appid = C('WX_APPID');
//        $appsecret = C('WX_APPSECRET');
//        //获取用户信息
//        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
//        return $this->sendGetRequest($url);
//    }

    private function make_nonceStr()
    {
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i<16; $i++) {
            $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
        }
        $nonceStr = implode($codes);
        return $nonceStr;
    }

    private function make_signature($nonceStr,$timestamp,$jsapi_ticket,$url)
    {
        $tmpArr = array(
            'noncestr' => $nonceStr,
            'timestamp' => $timestamp,
            'jsapi_ticket' => $jsapi_ticket,
            'url' => $url
        );
//        var_dump($tmpArr);exit;
        ksort($tmpArr, SORT_STRING);
        $string1 = http_build_query( $tmpArr );
        $string1 = urldecode( $string1 );
        $signature = sha1( $string1 );
        return $signature;
    }

    private function make_ticket($appId,$appsecret)
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticket = S('weixin:ticket');
        if(!$ticket){
            $TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appsecret;
            $result =  $this->sendGetRequest($TOKEN_URL);
            $access_token = $result['access_token'];
            $ticket_URL="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
            $result =  $this->sendGetRequest($ticket_URL);
            $ticket = $result['ticket'];
            S('weixin:ticket',$ticket,7000);
        }
        return $ticket;
    }

    private function sendGetRequest($url){
        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        return json_decode($output,true);
    }

    /**
     * 保存用户地址
     * @param $post 用户数据
     * @param $activity_name 活动名称
     * @return mixed
     */
    private function saveUserAdressInfo($post,$activity_name)
    {
        if (!$post['address']) {
            $this->ajaxReturn(['status' => 0, 'info' => '请输入收货地址！']);
        } else {
            $post['address'] = remove_xss($post['address']);
        }
        if (!$post['order_id']) {
            $this->ajaxReturn(['status' => 0, 'info' => '缺少订单号！']);
        }
        if (!$post['source']) {
            $this->ajaxReturn(['status' => 0, 'info' => '缺少发单位置标识！']);
        }
        //查询用户是否已经参加活动
        $join = D("Activityuserinfo")->findUserInfo($post['order_id']);
        if ($join) {
            $this->ajaxReturn(['status' => 0, 'info' => '抱歉！您已参加活动！']);
        }
        //活动信息
        $activityInfo = M('activity')->where(['name' => ['eq', $activity_name]])->field('id,name')->find();
        if (!$activityInfo) {
            $this->ajaxReturn(['status' => 0, 'info' => '未知活动！']);
        }
        $post['activity_id'] = $activityInfo['id'];
        //活动奖品信息
        $prizeInfo = D("Activityprize")->getPrizeInfoByActivity($activityInfo['id']);
        if (!$prizeInfo) {
            $this->ajaxReturn(['status' => 0, 'info' => '未知活动奖品！']);
        }
        $post['prize_id'] = $prizeInfo['id'];
        $post['time'] = time();
        return D("Activityuserinfo")->addUserInfo($post);
    }

    /**
     *  12月活动-2万元装修大礼包
     */
    public function zxlb(){
        $head = [
            'title' => '装修享好礼，享20000元新优惠券福利-齐装网',
            'description' => '装修享好礼，建材家具家居优惠券，价值20000元，优惠不容错过。',
            'keywords' => '装修享好礼，报名赢取20000元优惠券。装修建材家具家，全新优惠，精彩不容错过。',
        ];
        $this->assign("head", $head);
        $this->display("zxlb_a18049");
    }
    /**
     *  12月活动-2万元装修大礼包: 落地页增加效果优化JS
     */
    public function zxlb2(){
        $head = [
            'title' => '装修享好礼，享20000元新优惠券福利-齐装网',
            'description' => '装修享好礼，建材家具家居优惠券，价值20000元，优惠不容错过。',
            'keywords' => '装修享好礼，报名赢取20000元优惠券。装修建材家具家，全新优惠，精彩不容错过。',
        ];
        $this->assign("head", $head);
        $this->display();
    }

    /**
     *  12月活动-2万元装修大礼包: 落地页增加效果优化JS
     */
    public function zxlb4(){
        $head = [
            'title' => '装修享好礼，享20000元新优惠券福利-齐装网',
            'description' => '装修享好礼，建材家具家居优惠券，价值20000元，优惠不容错过。',
            'keywords' => '装修享好礼，报名赢取20000元优惠券。装修建材家具家，全新优惠，精彩不容错过。',
        ];
        $this->assign("head", $head);
        $this->display();
    }

    public function zxlb5(){
        $head = [
            'title' => '装修享好礼，享20000元新优惠券福利-齐装网',
            'description' => '装修享好礼，建材家具家居优惠券，价值20000元，优惠不容错过。',
            'keywords' => '装修享好礼，报名赢取20000元优惠券。装修建材家具家，全新优惠，精彩不容错过。',
        ];
        $this->assign("head", $head);
        $this->display("zxlb5_a18049");
    }

    /*
     *大礼包复制
     */
    public function zxlb6(){
        $head = [
            'title' => '装修享好礼，享20000元新优惠券福利-齐装网',
            'description' => '装修享好礼，建材家具家居优惠券，价值20000元，优惠不容错过。',
            'keywords' => '装修享好礼，报名赢取20000元优惠券。装修建材家具家，全新优惠，精彩不容错过。',
        ];
        $this->assign("head", $head);
        $this->display();
    }


    public function successResult(){
        $this->display("success");
    }

    public function juBuLuoDiYe()
    {
        $today = date('Y-m-d');
        $today_key = 'm:ldy:baojianum:'.$today;
        $randPeople =  S($today_key);
        //随机生成获取设计与报价的人数并且生成缓存数据
        if(empty($randPeople)){
            $randPeople = rand(1000, 2000);
            S($today_key,$randPeople);
            //删除昨日缓存，防止垃圾数据堆积
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $yesterday_key = 'm:ldy:baojianum:'.$yesterday;
            S($yesterday_key,null);
        }
        if(!empty($_GET['key'])){
            //ajax传入的数据写入缓存
            S('m:ldy:baojianum:'.$today,$_GET['key']);
            $this->ajaxReturn(array("status"=> "1", "info"=>"success", "data"=>$_GET['key']));
        }
        $this->assign('randPeople',$randPeople);

        //发单设置
        $laiyuan = $_GET['fi'];
        if(empty($laiyuan)){
            $source = 18050425;
        }else{
            $source = $laiyuan;
        }
        $this->assign('source',$source);

        //SEO标题关键字描述
        $basic["head"]["title"] = '局部装修_局部装修效果图_局部装修装饰设计案例-齐装网';
        $basic["head"]["keywords"] = '局部装修,局部装修效果图,局部装饰,局部装修案例';
        $basic["head"]["description"] = '齐装网拥有全新局部装修案例,包括厨房,卫生间,餐厅,客厅及卧室等装修设计案例,精选2018全新局部装修装修效果图，每个局部多张实景装饰效果。全网口碑装修平台为您装修省时,省力,省心,最重要的是省钱，90%的业主选择齐装网。';
        $this->assign('basic',$basic);

        //获取风格
        $fengge = D("Fengge")->getfg();
        $this->assign("fengge", $fengge);

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo',session('m_cityInfo'));

        $src = $_GET['src'];
        $result =[];
        if($src){
            $orderSourceResult = D("OrderSource")->getOne($src);
            //根据sourceid获取微信管理信息
            $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);
        }
        if(!$result){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['desc'])){
            $this->assign("img", $result['img']);
        }
        $this->assign("src", $src);


        $this->display('jubu');
    }

    public function wxGuide(){
        $this->display();
    }

    /**
     * 会销落地页
     * @return \Think\View|void
     */
    public function zhanxiao()
    {
        if ($_POST) {
            $name = I("post.name");
            $tel = I("post.tel");
            $name = trim($name);
            $tel  = trim($tel);

            if (empty($name)) {
                $this->ajaxReturn(array("error_code" => 4000002,"error_msg" => "姓名格式不正确,请重新输入"));
            }

            if (empty($tel)) {
                $this->ajaxReturn(array("error_code" => 4000002,"error_msg" => "手机号码不正确,请重新输入"));
            }

            $reg ='/(13|14|15|16|17|18|19)[0-9]{9}/';
            preg_match($reg, $tel,$m);
            if (count($m) == 0) {
                $this->ajaxReturn(array("error_code" => 4000002,"error_msg" => "手机号码不正确,请重新输入"));
            }

            $data = [
                "name" => remove_xss($name),
                "tel"  => $tel,
                "time" => time()
            ];

            //查重
            $findHistroy =  D("Common/Logic/ActivityZhanhuiLogic")->findByTel($tel);
            if (count($findHistroy)>0) {
                $this->ajaxReturn(array("error_code" => 1,"error_msg" => "已报名成功，无需再次提交"));
            }

            //数据入库
            $result =  D("Common/Logic/ActivityZhanhuiLogic")->addInfo($data);

            if ($result !== false) {
               $this->ajaxReturn(array("error_code" => 0,"error_msg" => "提交成功"));
            }
            $this->ajaxReturn(array("error_code" => 5000002,"error_msg" => "服务器连接异常"));
        } else {
            $this->display();
        }
    }

    public function fx1()
    {
        $this->display();
    }

    /**
     * 装修订单短信推送落地页
     * @return \Think\View|void
     */
    public function yuding()
    {
        $this->display();
    }

    /**
     * 装修订单短信推送落地页提交接口
     * @return string|mixed|void
     */
    public function addyuding()
    {
        if (IS_POST) {
            $order_id = I('post.order_id');
            $url = I('post.url');
            $status = I('post.status');
            if (intval($status) === 1) {
                //查询推送记录
                $pushLogic = new \Common\Model\Logic\SmsPushLogicModel();
                $pushLog =$pushLogic->findSmsPushLog($order_id,$url);
                if (empty($pushLog)) {
                    $this->ajaxReturn(['error_code' => 0,'error_msg' => '保存成功']);
                }
                //查询是否有已经转成家具订单
                $jiajuZbLogic = new \Common\Model\Logic\JiajuZbLogicModel();
                $jiajuOrderData = $jiajuZbLogic->getJiajuOrderByZxid($order_id);
                if (!empty($jiajuOrderData)){
                    $this->ajaxReturn(['error_code' => 0,'error_msg' => '保存成功']);
                }
                //写入家具订单信息
                $orderModel = new \Common\Model\OrdersModel();
                $zxOrderInfo = $orderModel->getOrderInfoById($order_id);
                switch (intval($pushLog['type'])) {
                    case 1:
                        $ordertype = 7;
                        break;
                    case 2:
                        $ordertype = 8;
                        break;
                    case 3:
                        $ordertype = 9;
                        break;
                    case 4:
                        $ordertype = 10;
                        break;
                    default:
                        $ordertype = 0;
                }
                $jiajuData = [
                    'tel' => $zxOrderInfo['tel8'],
                    'order_id' => $zxOrderInfo['id'],
                    'type' => $ordertype,
                    'on' => 0,
                    'on_sub' => 10,
                    'name' => empty($zxOrderInfo['name']) ? '':$zxOrderInfo['name'],
                    'sex' => empty($zxOrderInfo['sex']) ? '':$zxOrderInfo['sex'],
                    'cs' => empty($zxOrderInfo['cs']) ? '':$zxOrderInfo['cs'],
                    'qx' => empty($zxOrderInfo['qx']) ? '':$zxOrderInfo['qx'],
                    'dz' => empty($zxOrderInfo['dz']) ? '':$zxOrderInfo['dz'],
                    'xiaoqu' => empty($zxOrderInfo['xiaoqu']) ? '':$zxOrderInfo['xiaoqu'],
                    'other_contact' => empty($zxOrderInfo['other_contact']) ? '':$zxOrderInfo['other_contact'],
                    'mianji' => empty($zxOrderInfo['mianji']) ? '':$zxOrderInfo['mianji'],
                    'special_remarks' => '',
                    'zhuanfaren' => empty($zxOrderInfo['zhuanfaren']) ? '':$zxOrderInfo['zhuanfaren'],
                    'yt' => empty($zxOrderInfo['yt']) ? '':$zxOrderInfo['yt'],
                    'lng' => empty($zxOrderInfo['lng']) ? '':$zxOrderInfo['lng'],
                    'lat' => empty($zxOrderInfo['lat']) ? '':$zxOrderInfo['lat'],

                    'huxing' => empty($zxOrderInfo['huxing']) ? 0:$zxOrderInfo['huxing'],
                    'source_type' => empty($zxOrderInfo['source_type']) ? 0:$zxOrderInfo['source_type'],
                    'source' => empty($zxOrderInfo['source']) ? 0 :$zxOrderInfo['source'],
                    'source_in' => empty($zxOrderInfo['source_in']) ? 0:$zxOrderInfo['source_in'],
                    'fengge' => empty($zxOrderInfo['fengge']) ? 0:$zxOrderInfo['fengge'],
                    'lx' => empty($zxOrderInfo['lx']) ? 0:$zxOrderInfo['lx'],
                    'xiaoqu_type' => empty($zxOrderInfo['xiaoqu_type']) ? '':$zxOrderInfo['xiaoqu_type'],
                ];
                $jiaju_order_id = $jiajuZbLogic->publish_order($jiajuData);
                if ($jiaju_order_id !== false) {
                    //增加日志标志信息
                    $pushLogic->setSmsPushLogStatus($pushLog['id']);
                    //添加订单安全表
                    $jiajuZbLogic->public_tel_safe($jiaju_order_id, $zxOrderInfo['tel8']);
                    //添加渠道数据
                    $jiajuZbLogic->public_yy_order($jiaju_order_id);

                    $this->ajaxReturn(array('error_code' => 0,'error_msg' => '保存成功'));
                } else {
                    $this->ajaxReturn(array('error_code' => 5000002,'error_msg' => '保存失败'));
                }
            }
            $this->ajaxReturn(['error_code' => 0,'error_msg' => '保存成功']);
        } else {
            $this->ajaxReturn(array('error_code' => 5000002,'error_msg' => '无效的请求信息'));
        }
    }
    //283 渠道落地页上线美团合作落地页
    public function mthd(){
        $this->display();
    }

    public function lyzxhd(){
        $this->display();
    }
    // 387 落地页 齐装家装季
    public function qzjzj() {
        $head = [
            'title' => '齐装家装季7800元限量红包',
            'description' => '齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display();
    }

     // 549 复制落地页并删除部分内容
     public function qzjzj_zfb() {
        $head = [
            'title' => '齐装家装季7800元限量红包',
            'description' => '齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display();
    }

    // 455复制落地页，并进行数据上报  2019/10/26
    public function qzjzj_zswx() {
        $head = [
            'title' => '齐装家装季7800元限量红包',
            'description' => '齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display('qzjzj-zswx');
    }

    // 427  复制落地页，在新落地页上添加手机验证码功能
    public function qzjzj_shxs() {
        $head = [
            'title' => '齐装家装季7800元限量红包',
            'description' => '齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display('qzjzj-shxs');
    }
    // 387 落地页 齐装家装季 详情页
    public function qzjzj_detail() {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzjzj/");
        $this->display();
    }

    // 549 复制落地页并删除部分内容
    public function qzjzj_zfbdetail() {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzjzj_zfb/");
        $this->display();
    }

    // 777 M端复制落地页去除定位授权
    public function qzjzj_zfbzj() {
        $head = [
            'title' => '齐装家装季7800元限量红包',
            'description' => '齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display('qzjzj-zfbzj');
    }

     /**
     * 计算价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    private function calculatePrice($mianji,$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积
        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);

        if (empty($price)) {
            $price = 300;
        }

        $total = $price*1.2*$mianji;
        $info["child"]['kt'] = $total*0.25 ;
        $info["child"]['zw'] = $total*0.18;
        $info["child"]['wsj'] = $total*0.16;
        $info["child"]['cf'] = $total*0.08;
        $info["child"]['sd'] = $total*0.25;
        $info["child"]['other'] = $total*0.08;
        $info['total'] = $total;

        return $info;
    }
    // 387 落地页 超值家装季
    public function czjzj() {
        $head = [
            'title' => '超值家装季7800元限量红包',
            'description' => '超值装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display();
    }

    // 525 复制落地页并修改部分内容
    public function jzdlb() {
        $head = [
            'title' => '超值家装季7800元限量红包',
            'description' => '超值装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display();
    }
    public function jzdlb_detail() {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/jzdlb/");
        $this->display();
    }

    // 464 移动端相关报价页面去除部分文字及部分页面复制
    public function czjzj_yw() {
        $head = [
            'title' => '超值家装季7800元限量红包',
            'description' => '超值装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display('czjzj-yw');
    }

    public function czjzj_detail() {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/czjzj/");
        $this->display();
    }
    // 1296 复制页面&修改落地页面
    public function qzjzj1() {
        $head = [
            'title' => '齐装双11钜惠装限量红包活动',
            'description' => '齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。',
            'keywords' => '装修报价，量房设计，设计师1V1',
        ];
        $this->assign("head", $head);
        $this->display();
    }
    // 1296 复制页面&修改落地页面 发单成功页
    public function qzjzj1_details() {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzjzj1/");
        $this->display();
    }
}
