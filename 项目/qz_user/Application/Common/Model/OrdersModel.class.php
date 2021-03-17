<?php
/**
 *订单表 orders
 */
namespace Common\Model;
use Think\Model;
class OrdersModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 获取最新的订单信息
     * @param  [type] $city  [城市ID]
     * @param  [type] $on    [订单状态]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getNewOrders($limit=10,$on = '',$cs = ''){
        $date=date('Y-m-d',time());//今天 零点
        $time=strtotime($date)-24*3600;//24小时前的时间
        $map = array(
                "name" =>array("NEQ","null"),
                'time'   => array('LT',$time),                       //时间小于过去24小时
                'mianji' => array('EGT', 100),                       // 面积 >= 100
                'yusuan' => array('EXP', 'BETWEEN 17 AND 42'),
                     );
        if(!empty($on)){
          $map["on"] = array("EQ",$on);
        }

        if(!empty($cs)){
          $map["cs"] = array("EQ",$cs);
        }

        //生成子查询语句
        $buildSql = M("orders")->where($map)->order("time desc")->limit($limit)->buildSql();
        return M("orders")->table($buildSql."as a")
               ->join("left join qz_quyu as c on a.cs  = c.cid")
               ->join("left join qz_huxing as b on a.huxing  = b.id")
               ->join("left join qz_fangshi as d on a.fangshi  = d.id")
               ->join("left join qz_jiage as e on e.id  = a.yusuan")
               ->field("a.id,a.userid,a.on,a.fengge,a.type,a.name,a.sex,a.qx,a.sf,a.xiaoqu,a.time_real,a.time,a.lx,a.dz,a.yt,a.huxing,a.fangshi,a.yusuan,a.mianji,a.shi,a.ting,a.wei,a.cs,a.lxs,a.keys,a.yusuans,a.type_fw,a.on_sub,a.on_sub_wuxiao,b.name as hxname,c.cname as cname,d.name as fname,e.name as yusuan")->select();

    }

    /**
     * [order_chk_history 如果24小时内，该ip，该城市发布过订单，且订单为未审核状态，
     * 就直接跳转到填写详细。]
     * @param  [type] $tel [电话号码]
     * @return [type] $re array  [返回数组 status 状态 1;info 提示信息]
     */
    public function order_chk_history($tel,$city) {
        import('Library.Org.Util.App');
        $app         = new \App();

        $tel_encrypt = $app->order_tel_encrypt($tel); //生成加密电话

        //2017-07-03修改  城市不再作为重复订单的判断标志
        /*if (!empty($city)) {
            $oldonmap['o.cs']   = array('eq',$city); //城市
        }*/

        $oldonmap['o.tel_encrypt'] = array('eq',$tel_encrypt); //加密电话号码
        $oldonmap['o.time'] = array('gt',time() - 86400); //过去24小时
        $oldonmap['o.on']   = array('eq',0); //未审核
        $oldonorder         = M('orders')->
                              alias('o')->
                              field('o.id')->
                              where($oldonmap)->
                              limit(1)->
                              select();
        if(count($oldonorder)>0){
            return $oldonorder[0]['id'];
        }
        return null;
    }


    /**
     * [checkFenHistory 如果24小时内，该ip，该城市发布过订单，且订单为审核状态，
     * 就直接返回提示，不要重复发单。]
     * @param  [type] $tel [电话号码]
     * @return [type] $re array  [返回数组 status 状态 1;info 提示信息]
     */
    public function checkFenHistory($tel) {
        import('Library.Org.Util.App');
        $app         = new \App();
        $tel_encrypt = $app->order_tel_encrypt($tel); //生成加密电话
        $oldonmap['o.tel_encrypt'] = array('eq',$tel_encrypt); //加密电话号码
        $oldonmap['o.time'] = array('gt',time() - 86400); //过去24小时
        $oldonmap['o.on']   = array('gt',0); //审核过的订单
        $oldonorder         = M('orders')->
                              alias('o')->
                              field('o.id')->
                              where($oldonmap)->
                              limit(1)->
                              select();
        if(count($oldonorder)>0){
            return $oldonorder[0]['id'];
        }
        return null;
    }

    /**
     * [orderpublish 发布订单]
     * @param  [array] $data   [传入订单参数]
     * @param  [str]   $method [方法. insert 为新增. update为修改]
     * @return [str]   [成功返回 单号  失败返回 false]
     */
    public  function orderpublish($data,$method) {
        if (empty($data) || empty($method)) {
            return false;
        }

        import('Library.Org.Util.App');
        $app = new \App();
        $data['ip']        = $app->get_client_ip();
        $data['time']      = time();
        $data['time_real'] = time();
        $data['userid']    = I("session.u_userInfo")['id'];

        //构建存入扩展表的数据
        $data_et                   = array();

        //?src=video 推广渠道来源跟踪,逻辑
        $srcMark = cookie('src_mark');
        if(!empty($srcMark)){
            $promoteSrc = D('Common/TrackingOrderSource')->getPromoteSrc($srcMark);
            if (!empty($promoteSrc)) {
                //渠道标记代号src
                $data['source_src'] = $promoteSrc['src'];
                //渠道标记代号src的id
                $data['source_src_id'] = $promoteSrc['id'];
            }
        }

        if(empty($data['source_src_id'])){
            //如果是微信中访问 发单的 就记下来
            if( false !== strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") ) {
                $data['source_src'] = 'lzwx';
                $data['source_src_id'] = '93';
            }
        }

        $data_et['orderid']        = $data['id']; // '订单号和 orders表中的id对应',
        $data_et['source']         = (int) $data['source']; // '来源网址 和orders表中的source应该一致',
        $data_et['source_src']  = empty($data['source_src']) ? '' : $data['source_src']; //渠道标记代号src
        $data_et['source_src_id']  = (int) $data['source_src_id']; //渠道标记代号src的id

        if (!empty($data['source_in'])) {
            $data_et['source_in']      = $data['source_in']; // '来源入口 默认0  比如微信10 和orders表中的source_in应该一致',
        }
        // $data_et['source_type'] = $data['source_type']; // '1 51客服, 2 400电话, 3 QQ咨询, 4 业主发布 和orders表中的source_type应该一致',
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $data_et['referer']        = $_SERVER['HTTP_REFERER']; // ' 发布页的HTTP Referer',
        }

        $data_et['select_desid']   = (int)$data['select_desid']; // '选择的设计师',
        $data_et['select_comid']   = (int)$data['select_comid']; // '选择的装修公司',
        $data_et['display_type']   = (int)$data['display_type']; // '显示类型 默认0  1为开放给公司人员查看',
        $data_et['addtime']        = $data['time']; // '增加时间',

        if ($method == "insert") {
            //新增订单
            $data['id']     = date('Ymd'). sprintf("%05d%03d", time()%86400, mt_rand(1,1000)); // 生成订单号

            if(empty($data['cs'])) {
                $data['cs'] = I("session.cityId");
            }
            // 电话号码安全入库
            $tel8   = $data['tel'];
            M()->table('safe_order_tel8')->add(array(
                            'orderid'   => $data['id'],
                            'tel8'      => $tel8,
                        ));

            //安全入库后. 处理入库orders表的为 星号
            //如果是手机号码11位
            if (11 == strlen($tel8)) {
                $data['tel']   = substr($tel8,0,3) .'*****'. substr($tel8,-3);
            }else{
                $data['tel']   = substr($tel8,0,-3) . '***';
            }

            //存放一个加密电话号码用于订单号码搜索用途
            $data['tel_encrypt'] = $app->order_tel_encrypt($tel8);

            //计算订单完整度
            $data['wzd']  = self::calculateComplete($data);

            //新增加订单
            $result = M("orders")->add($data);

            //添加市场发单跟踪统计数据
            if (cookie("QZUUID") != null) {
                $marketData["uuid"] = cookie("QZUUID");
            }

            if (cookie("QZSRC") != null) {
                $marketData['tag'] = cookie("QZSRC");
            }

            if (cookie("QZHOST") != null) {
               $marketData['host'] = cookie("QZHOST");
            }

            $marketData["order_id"] = $data['id'];
            $marketData["time"] = time();
            M("market_orders")->add($marketData);

            //新增加订单的来源的扩展表
            $data_et['orderid'] = $data['id']; // '订单号和 orders表中的id对应', 新订单,订单id需要重新赋值
            $redata_et = self::orderFieldHaveon($data); //订单项有填写标记
            if (!empty($redata_et)) {
                $data_et  = array_merge($data_et, $redata_et);
            }
            M('orders_source')->add($data_et);

            //增加订单的初始状态扩展表
            $data_sc_et             = array();
            $data_sc_et['orderid']  = $data['id']; //订单id
            $data_sc_et['on']       = 0; //新单0
            $data_sc_et['on_sub']   = 10; //on=0子状态默认10
            if (empty($data['cs'])) {
                $data_sc_et['cs']  = '000001'; //默认
            } else {
                $data_sc_et['cs']  = (int) $data['cs']; //订单发布城市
            }
            $data_sc_et['type_fw']  = 0; // 分 赠送 默认 0
            $data_sc_et['time_add'] = $data['time']; //订单发布时间
            M('orders_status_change')->add($data_sc_et);

            if($result != false){
               return  $data['id'];
            }
            return false;
        } else if ($method == "update") {
            //第二步完善订单
            $orderid  = $data['orderid'];
            unset($data['orderid']); //干掉订单号
            unset($data_et['orderid']); //干掉订单号
            $com_list = $data['com_list'];
            unset($data['com_list']);

            //检查该订单状态 如果 不为 0，不可编辑
            $mapon['id'] = $orderid;
            $orderon     = M('orders')->where($mapon)->select();
            if ($orderon[0]['on'] != 0) {
                return FALSE;
            }

            unset($data['time']);
            unset($data['time_real']);
            unset($data['tel']);

            //计算订单完整度
              //合并 订单历史数据,和update更新数据,再来计算订单完整度
            $datawzd      = array_merge($orderon[0], $data);
            $data['wzd']  = self::calculateComplete($datawzd);

            $onmap['id']    = $orderid;
            //不要更新来源
            unset($data['source_src']);
            unset($data['source_src_id']);

            //城市信息：②最后一次若城市已填，区域未填，区域归为其他。
            if(!empty($data["cs"])){
               if(empty($data['qx'])){
                    //区域未填，获取城市的‘其他’区域
                    $map['fatherid'] = $data["cs"];
                    $map['qz_area'] = '其他';
                    $qx = M('area')->where($map)->field('qz_areaid')->find();
                    $data['qx'] = $qx['qz_areaid'];
                }
            }else{
                unset($data['qx']);
            }

            //更新订单信息
            $saveorder      = M("orders")->where($onmap)->save($data);

            //存订单的来源的扩展表
            unset($data_et['source']);
            unset($data_et['source_in']);
            unset($data_et['source_type']);
            unset($data_et['source_src_id']);
            unset($data_et['referer']);
            unset($data_et['addtime']);
            //不要更新来源
            unset($data_et['source_src']);
            unset($data_et['source_src_id']);
            $onmap_et['orderid'] = $orderid;
            $redata_et = self::orderFieldHaveon($data); //订单项有填写标记
            if (!empty($redata_et)) {
                $data_et  = array_merge($data_et, $redata_et);
            }
            M('orders_source')->where($onmap_et)->save($data_et);



            return true;
            // if ($saveorder) {
            //     //如果自助选择了 喜欢的装修公司
            //     // if($data['type']==1 || !empty($com_list)) {
            //     //     $order_info = M('order_infofb');
            //     //     $fbmap['orderid'] = array('eq',$orderid);
            //     //     $order_info->where($fbmap)->delete();
            //     //     $a = rtrim($com_list,',');
            //     //     $b = explode(',',$a);
            //     //     for($i=0;$i<count($b);$i++){
            //     //         $data3['comid']   = $b[$i];
            //     //         $data3['orderid'] = $orderid;
            //     //         $data3['addtime'] = time();
            //     //         $order_info->data($data3)->add();
            //     //     }

            //     // }

            //     return $orderid; //返回订单号码
            // } else {
            //     return FALSE;
            // }
        } else {
             //未定义的 $method
            return FALSE;
        }
    }

    /**
     * [order_chk_ip 单ip每天发布不得超过3条]
     * @param  [type] $ip [ip]
     * @return [type] $re array  [返回数组 status 状态 1;info 提示信息]
     */
    public function order_chk_ip($ip) {
        import('Library.Org.Util.App');
        $app                 = new \App();
        $cipmap['ip']        = $app->get_client_ip();
        $cipmap['time_real'] = array('gt',time() - 86400);
        $ordereds            = M("orders")->where($cipmap)->count();
        $ipiswhite           = in_array($cipmap['ip'], C('IP_WHITE_LIST'));
        //测试用白名单：判断如果IP是192.168.8.*段 重置 $ipiswhite = TRUE
        /*$startip = ip2long("192.168.8.0");
        $endip = ip2long("192.168.8.255");
        $nowip = ip2long($cipmap['ip']);
        if($nowip > $startip && $nowip < $endip){
            $ipiswhite = TRUE;
        }*/
        //测试环境、开发环境直接通过检查
        if (in_array(C('APP_ENV'),array('dev','test'))) {
            $ipiswhite = true;
        }
        if ($ordereds > 3 && $ipiswhite == false) {
            $re['status'] = 0;
            $re['info']   = 'IP检查,发布招标超限！0x01';
            return $re;
        }else{
            $re['status'] = 1;
            //$re['info']   =  "IP:" . $cipmap['ip'] . "今天还可以发布". (3 - $ordereds) ."条0x02";
            $re['info']   =  "IP检查,通过! 0x02";
            return $re;
        }
    }

     /**
     * [order_chk_tel 简单判断电话号码 允许包含数组和短杠- 位数为7-18位]
     * @param  [type] $tel [电话号码]
     * @return [type] $re array  [返回数组 status 状态 1;info 提示信息]
     */
    public function order_chk_tel($tel) {
        $istel = preg_match('/^[0-9-\-]{7,18}$/', $tel);
        if (!$istel) {
            $re['status'] = 0;
            $re['info']   = '电话格式不对,您可以留你的手机号码:-)';
            return $re;
        }else{
            $re['status'] = 1;
            $re['info']   = '电话号码验证成功!';
            return $re;
        }
    }

    /**
     * 获取装修公司的最新订单
     * @param  string $id    [公司编号]
     * @param  string $limit [显示数量]
     * @return [type]        [description]
     */
    public function getOrdersByComId($id='',$limit=10){
        $map = array(
                  "a.com"=>array("EQ",$id)
                       );
        $buildMap = array(
                    "order_on"=>array("EQ",1),
                    "order_company_id"=>array("EQ",$id),
                    "deleted"=>array("EQ",0)
                          );
        $buildSql = M("orders_company_report")->where($buildMap)
                                              ->field("UNIX_TIMESTAMP(time_qd) as addtime,name,mianji,lx,huxing,yusuan,xiaoqu")
                                              ->buildSql();
        $nextbuildSql = M("order_info")->where($map)->alias("a")
                                   ->join("INNER JOIN qz_orders as b on a.`order` = b.id")
                                   ->field("a.addtime,b.name,b.mianji,b.lx,b.huxing,b.yusuan,b.xiaoqu")
                                   ->buildSql();
        $buildSql = M("order_info")->table($nextbuildSql)->alias("t")
                                    ->union($buildSql,true)
                                    ->buildSql();

        return M("order_info")->table($buildSql)->alias("t1")
                                   ->join("LEFT JOIN qz_huxing as c on c.id = t1.huxing")
                                   ->join("LEFT JOIN qz_jiage as d on d.id = t1.yusuan")
                                   ->field("t1.*,c.name as huxing,d.name as yusuan")
                                   ->order("addtime desc")
                                   ->limit($limit)
                                   ->select();
    }

    /**
     * 根据订单编号获取订单信息
     * @return [type] [description]
     */
    public function getOrderInfoById($id){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        return M("orders")->where($map)->alias("a")
                          ->join("LEFT JOIN qz_quyu as q on q.cid = a.cs")
                          ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
                          ->join("LEFT JOIN qz_jiage as c on c.id = a.yusuan")
                          ->join("LEFT JOIN qz_huxing as d on d.id  = a.huxing")
                          ->join("LEFT JOIN qz_fengge as f on f.id  = a.fengge")
                          ->join("LEFT JOIN qz_fangshi as g on g.id  = a.fangshi")
                          ->join("LEFT JOIN safe_order_tel8 as h on h.orderid  = a.id")
                          ->field("a.id,a.time,a.lasttime,a.lat,a.lng,a.lftime,a.mianji,a.name,a.nf_time,a.remarks,a.xiaoqu,a.text,a.fengge as fg,a.lx,a.lxs,a.start,a.sex,a.visitime,a.wx,a.standby_user,a.is_wx,a.other_contact,
                          a.qiandan_companyid,a.qiandan_status,
                          a.order2com_allread,
                          q.cname,
                          b.qz_area,
                          c.name as yusuan,
                          d.name as hxname,
                          f.name as fengge,
                          g.name as fangshi,
                          h.tel8 as tel8
                          ")
                          ->find();
    }
    /**
     * 根据订单编号获取二次订单信息
     * @return [type] [description]
     */
    public function getBackOrderInfoById($id){
        $map = array(
            "a.id"=>array("EQ",$id)
        );
        return M("orders")->where($map)->alias("a")
            ->join("LEFT JOIN qz_quyu as q on q.cid = a.cs")
            ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
            ->join("LEFT JOIN qz_jiage as c on c.id = a.yusuan")
            ->join("LEFT JOIN qz_huxing as d on d.id  = a.huxing")
            ->join("LEFT JOIN qz_fengge as f on f.id  = a.fengge")
            ->join("LEFT JOIN qz_fangshi as g on g.id  = a.fangshi")
            ->join("LEFT JOIN safe_order_tel8 as h on h.orderid  = a.id")
            ->join("LEFT JOIN qz_company_liangfang as k on k.order_id  = a.id")
            ->field("a.*,a.fengge as fg, b.qz_area,c.name as yusuan,d.name as hxname,f.name as fengge,g.name as fangshi,h.tel8 as tel8,q.cname,k.remark")
            ->find();
    }

    /**
     * 根据装修公司编号查询分配的订单信息数量
     * @param  [type] $comid [description]
     * @param  [type] $text  [description]
     * @return [type]        [description]
     */
    public function getOrderListByComidCount($comid, $text, $isread, $state = 0, $begin = "", $end = ""){
        $map = array(
                "t.com"=>array("EQ",$comid),
                "b.`on`"=>array("EQ",4)
                     );
        import('Library.Org.Util.App');
        $app = new \App();

        if(!empty($text)){
            $map["_complex"] = array(
              "b.xiaoqu"=>array("LIKE","%$text%"),
              "b.tel_encrypt"=>array("EQ",$app->order_tel_encrypt($text)),
              "b.id"=>array("LIKE","%$text%"),
              "_logic"=>"OR"
            );
        }

        if($isread !== "" && $isread !== null){
            $map["t.isread"] = array("EQ",$isread);
        }

        if (!empty($state)) {
          $map["e.`status`"] = array("EQ", $state);
        }

        if (!empty($begin)) {
          $map[]["b.time"] = array("EGT", strtotime($begin));
        }

        if (!empty($end)) {
          $map[]["b.time"] = array("ELT", strtotime($end) + 86399);
        }

        return  M("order_info")->alias("t")->where($map)
                  ->join("inner join qz_orders as b on t.order = b.id")
                  ->join("left join qz_order_company_review as e on e.orderid = t.order and e.comid = t.com")
                  ->count();
    }

    /**
     * 根据装修公司编号查询分配的订单信息
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getOrderListByComid($comid, $text, $isread, $state, $begin = "", $end = "", $pageIndex, $pageCount, $export = false)
    {
        if ($export == false) {
          //过滤
          //强制数字整数
          $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
          $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        }

        $map = array(
          "t.com"=>array("EQ",$comid),
          "b.`on`"=>array("EQ",4)
        );
        if(!empty($text)){
            import('Library.Org.Util.App');
            $app = new \App();
            $map["_complex"] = array(
                                "b.xiaoqu"=>array("LIKE","%$text%"),
                                "b.tel_encrypt"=>array("EQ",$app->order_tel_encrypt($text)),
                                "b.id"=>array("LIKE","%$text%"),
                                "_logic"=>"OR"
                                    );
        }

        if($isread !== "" && $isread !== null){
            $map["t.isread"] = array("EQ",$isread);
        }

        if (!empty($state)) {
          $map["e.`status`"] = array("EQ", $state);
        }

        if (!empty($begin)) {
          $map[]["b.time"] = array("EGT", strtotime($begin));
        }

        if (!empty($end)) {
          $map[]["b.time"] = array("ELT", strtotime($end) + 86399);
        }

        $buildSql = M("order_info")->alias("t")->where($map)
                                   ->join("inner join qz_orders as b on t.order = b.id")
                                   ->join("left join qz_order_company_review as e on e.orderid = t.order and e.comid = t.com")
                                   ->limit($pageIndex.",".$pageCount)
                                   ->order("t.isread asc,b.time desc")
                                   ->field("t.*,b.qiandan_status,b.sex,b.qx,b.yusuan,b.qiandan_companyid,b.time as ordertime,b.`name` as ordername,b.xiaoqu,b.mianji,e.`status`,b.text")
                                   ->buildSql();
        return M("order_info")->table($buildSql)->alias("a")
                             ->join("left join qz_area as c on c.qz_areaid = a.qx")
                             ->join("left join qz_jiage as d on d.id = a.yusuan")
                             ->order("a.isread asc,a.ordertime desc")
                             ->field("a.*,c.qz_area as qx,d.`name` as jiage")
                             ->select();
    }

    /**
     * 编辑订单信息
     * @param  [type] $map  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editOrder($id,$data){
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return M("orders")->where($map)->save($data);
    }

    /**
     * 获取装修公司的订单分配信息
     * @return [type] [description]
     */
    public function getAllocationOrder($id,$comid){
        $map = array(
            "order"=>array("EQ",$id),
            "com"=>array("EQ",$comid)
        );
        return M("order_info")->where($map)
                              ->find();
    }

    /**
     * 获取订单分配的装修公司的微信openid
     * @param  integer $order_id 订单ID
     * @return array|mix         查询信息
     */
    public function getOrdersWechatOpenId($order_id = 0)
    {
        if (empty($order_id)) {
            return false;
        }

        $map = array(
            'i.order' => $order_id
        );

        return M('order_info')->alias('i')
                              ->field('w.wx_openid')
                              ->join('LEFT JOIN qz_order_wechat AS w ON (w.comid = i.com AND w.is_delete = 0)')
                              ->where($map)
                              ->group('i.com')
                              ->select();
    }

    /**
     * 获取分单公司的信息
     * @param  [type] $orderid [订单编号]
     * @return [type]     [description]
     */
    public function getOrdersDistributionCompany($orderid){
        $map = array(
                "a.order"=>array("EQ",$orderid)
                     );
        return M("order_info")->where($map)->alias("a")
                              ->join("INNER JOIN qz_user as b on b.id = a.com")
                              ->field("b.jc,b.logo,b.id,a.type_fw")
                              ->order('b.id')
                              ->select();
    }

    /**
     * 获取是否读过订单
     * @return [type] [description]
     */
    public function getOrderReadCount($orderid,$comid){
         $map = array(
                "order"=>array("EQ",$orderid),
                "com"=>array("EQ",$comid),
                "isread"=>array("EQ",1)
                     );
         return M("order_info")->where($map)->count();
    }

    /**
     *  根据业主编号或者手机号码 获取发布的订单信息的数量
     * @param  [type]  $id   [用户编号]
     * @param  [type]  $tel  [安全手机]
     * @param  boolean $safe [安全状态]
     * @return [type]        [description]
     */
    public function getOrdersByIdCount($id,$tel,$safe = false){
        if (empty($id) && empty($tel)) {
            return false;
        }

        //如果手机验证了,则包含其手机关联的订单
        if($safe && !empty($tel)){
            $maptel = array();
            $maptel['a.tel_encrypt'] = array("EQ",md5($tel.C('QZ_YUMING')));
            $unionSql = M("orders")->alias("a")
                                   ->field("a.id,a.qx,time,xiaoqu,a.yusuan,a.fengge,a.qiandan_companyid")
                                   ->where($maptel)
                                   ->buildSql();

        }

        //1.查询用户的后台发布 orders中 userid关联的订单信息
        $map = array();
        $map['a.userid'] = array("EQ",$id);
        $buildSql = M("orders")->where($map)->alias("a")
                              ->field("a.id,a.qx,time,xiaoqu,a.yusuan,a.fengge,a.qiandan_companyid")
                              ->buildSql();

        //2.合并2表的数据
        $buildSql = M("orders")->table($buildSql)->alias("t")
                           ->union($unionSql,true)
                           ->buildSql();

        $buildSql = M("orders")->table($buildSql)->alias("t2")
                               ->group("t2.id")
                               ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                          ->count();

    }

    /**
     * 根据业主编号或者手机号码 获取发布的订单信息
     * @param  [type] $id   [用户编号]
     * @param  [type] $tel   [用户电话]
     * @param  [type] $safe [是否手机验证]
     * @return [type]       [description]
     */
    public function getOrdersById($id,$tel,$safe = false,$pageIndex =0 ,$pageCount =10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if (empty($id) && empty($tel)) {
            return false;
        }
        //如果手机验证了,则包含其手机关联的订单
        if($safe && !empty($tel)){
            $maptel = array();
            $maptel['a.tel_encrypt'] = array("EQ",md5($tel.C('QZ_YUMING')));
            $unionSql = M("orders")->alias("a")
                                   ->field("a.id,a.qx,time,xiaoqu,a.yusuan,a.fengge,a.qiandan_companyid,a.on")
                                   ->where($maptel)
                                   ->buildSql();

        }

        //1.查询用户的后台发布 orders中 userid关联的订单信息
        $map = array();
        $map['a.userid'] = array("EQ",$id);
        $buildSql = M("orders")->where($map)->alias("a")
                              ->field("a.id,a.qx,time,xiaoqu,a.yusuan,a.fengge,a.qiandan_companyid,a.on")
                              ->buildSql();
        //2.合并2表的数据
        $buildSql = M("orders")->table($buildSql)->alias("t")
                         ->union($unionSql,true)
                         ->buildSql();

        $buildSql = M("orders")->table($buildSql)->alias("t1")
                          ->join("LEFT JOIN qz_area as c on c.qz_areaid = t1.qx")
                          ->join("LEFT JOIN qz_fengge as d on d.id = t1.fengge")
                          ->join("LEFT JOIN qz_jiage as e on e.id = t1.yusuan")
                          ->order("t1.time desc")
                          ->group("t1.id")
                          ->field("t1.id,t1.on,t1.time,t1.xiaoqu,c.qz_area as qx,d.name as fengge,e.name as yusuan")
                          ->buildSql();

        $buildSql = M("orders")->table($buildSql)->alias("t2")
                               ->limit($pageIndex.",".$pageCount)
                               ->buildSql();
        //3.获取分单信息
        return M("orders")->table($buildSql)->alias("t1")
                          ->join("LEFT JOIN qz_order_info as q on q.order = t1.id")
                          ->join("LEFT JOIN qz_user as u on u.id = q.com")
                          ->join("LEFT JOIN qz_quyu as f on f.cid = u.cs")
                          ->group("t1.id")
                          ->order("t1.time desc,t1.id")
                          ->field("t1.*,sum(if(q.isread,1,0)) as count,q.isread,u.jc,GROUP_CONCAT(u.id) as comid ,GROUP_CONCAT(u.logo) as logos,GROUP_CONCAT(f.bm) as bm")
                          ->select();
    }

    /**
     *  修改订单阅读状态
     * @param  [type] $id    [订单编号]
     * @param  [type] $comid [公司编号]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function editOrderRead($id,$comid,$data){
        $map = array(
                "order"=>array("EQ",$id),
                "com"=>array("EQ",$comid)
                     );
        return M("order_info")->where($map)->save($data);
    }

    /**
     * 根据用户编号和手机获取最新的订单信息
     * @param  [type] $id       [用户编号]
     * @param  [type] $tel      [用户手机]
     * @param  [type] $safe [是否手机验证]
     * @return [type]           [description]
     */
    public function getLastOrderInfoById($id,$tel,$safe){
        if (empty($id) && empty($tel)) {
            return false;
        }

        //如果手机验证了,则包含其手机关联的订单
        if($safe && !empty($tel)){
            $maptel = array();
            $maptel['a.tel_encrypt'] = array("EQ",md5($tel.C('QZ_YUMING')));
            $unionSql = M("orders")->alias("a")
                                   ->field("a.id,a.qx,time,xiaoqu,a.yusuan,a.fengge,a.qiandan_companyid,a.on")
                                   ->where($maptel)
                                   ->buildSql();
        }

        //1.查询用户关联的订单信息
        $map = array(
                "a.userid"=>array("EQ",$id)
                     );
        $buildSql = M("orders")->where($map)->alias("a")
                              ->field("a.id,a.qx,time,xiaoqu,a.yusuan,a.fengge,a.qiandan_companyid,a.on")
                              ->buildSql();

        //2.合并2表的数据
        $buildSql = M("orders")->table($buildSql)->alias("t")
                         ->union($unionSql,true)
                         ->buildSql();
        return M("orders")->table($buildSql)->alias("t1")
                          ->order("t1.time desc")
                          ->find();
    }

    /**
    * [getOrderFenpeiAllIsRead 已分配公司的订单是否都已读]
    * @param  [int] $orderid [订单号]
    * @return [bool]         []
    */
    public function getOrderFenpeiAllIsRead($orderid){
        $map['order'] = array('EQ',$orderid);
        $list = M('order_info')->where($map)->select();
        $flag = true;
        foreach ($list as $key => $value) {
            if (0 == $value['isread']){
                $flag = false;
                break;
            }
        }
        return $flag;
    }

    /**
     *order2com_allread
     */
    public function setOrderAllIsRead(){

    }

    /**
     * calculateComplete 计算订单信息完整度
     * @param  array $data  评估项,数组传入,和orders中字段对应, 一般情况下直接传入一条订单的完整数据即可
     * @return float 完整度
     */
    /**
    * 订单完整度
    * 订单完整度记录在 表orders  字段wzd float
    *
    *发单订单完整度
    *计算元素和权重
    *
    *1.联系人：王先生  20%
    *2.性别：男   10%
    *3.城市：苏州   10%
    *4.区域：姑苏    10%
    *5.小区名称：小花园   20%
    *6.面积：120 10%
    *7.预算：10w 10%
    *8.开工时间：三个月后 10%
    *
    *
    *完整度 = （已填的项的权重和) / 100  * 100%
    *
    *举例
    *发单填写为:
    *1.联系人：王大哥  20%
    *2.性别：男   10%
    *3.城市：苏州   10%
    *4.区域：姑苏    10%
    *5.小区名称：小花园   20%
    *
    *完整度为:  (20+10+10+10+20) / 100  * 100% =  70%
    *
    *
    */
    public function calculateComplete($data)
    {
        //定义需要评估的项
        $info = array(
                      'name'    => trim($data['name']),     //1.联系人 20%
                      'sex'     => trim($data['sex']),      //2.性别 10%
                      'cs'      => trim($data['cs']),       //3.城市 10%
                      'qx'      => trim($data['qx']),       //4.区域  10%
                      'xiaoqu'  => trim($data['xiaoqu']),   //5.小区名称 20%
                      'mianji'  => trim($data['mianji']),   //6.面积 10%
                      'yusuan'  => trim($data['yusuan']),   //7.预算 10%
                      'start'   => trim($data['start']),    //8.开工时间 10%
                      );
        $info      = array_filter($info); //过滤评估项中的空项
        $sumweight = 0; //定义初始权重
        foreach ($info as $key => $value) {
            if ('name' == $key || 'xiaoqu' == $key) {
                $sumweight += 20;
            } else {
                $sumweight += 10;
            }
        }
        $complete = $sumweight / 100 * 100;
        return $complete;
    }

    /**
     * 发布订单 订单项填写标记
     * @param  array $data 订单项目
     * @return array 标记
     */
    public function orderFieldHaveon($data)
    {
        if (!empty($data)) {
            $data   = array_filter($data); //过滤空
            $redata = array(); //标记返回数据
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'name':
                        $redata['have_name'] = 1;
                        break;
                    case 'sex':
                        $redata['have_sex'] = 1;
                        break;
                    case 'cs':
                        $redata['have_cs'] = 1;
                        break;
                    case 'qx':
                        $redata['have_qx'] = 1;
                        break;
                    case 'xiaoqu':
                        $redata['have_xiaoqu'] = 1;
                        break;
                    case 'mianji':
                        $redata['have_mianji'] = 1;
                        break;
                    case 'yusuan':
                        $redata['have_yusuan'] = 1;
                        break;
                    case 'start':
                        $redata['have_start'] = 1;
                        break;
                    default:
                        # code...
                        break;
                }
            }
            return $redata;
        }
        return false;
    }

    /**
     * 查询24小时的订单历史数量
     * @param string $value [description]
     */
    public function getHistoryOrderCount($tel)
    {
        $map = array(
            "tel_encrypt" => array("EQ",$tel),
            "time_real" => array("EGT",strtotime("-1 day",time()))
        );
        return M("orders")->where($map)->count();
    }

    /**
     * 移动端国内公司订单统计
     * @param  [type] $company_id 公司id
     * @param  [type] $isread     已读未读
     * @return [type]             [description]
     */
    public function getMobileOrderListCount($company_id,$isread){
        $map = array(
            "t.com" => array("EQ",$company_id),
            "t.isread" => array("EQ",$isread)
        );
        return  M("order_info")->alias("t")->join('JOIN qz_orders as o on o.id = t.order')->where($map)->count();
    }

    /**
     * 移动版获取装修公司订单列表
     * @param  [type] $company_id [description]
     * @param  [type] $isread     [description]
     * @return [type]             [description]
     */
    public function getMobileOrderList($company_id, $isread, $pageIndex, $pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
            "com" => array("EQ", $company_id),
            "isread" => array("EQ", $isread)
        );
        $buildSql = M("order_info")->where($map)->order("addtime desc")
            ->limit($pageIndex . "," . $pageCount)
            ->field("order,isread,type_fw,allot_source")
            ->buildSql();
        $buildSql = M("order_info")->table($buildSql)->alias("t")
            ->join("qz_orders as o on o.id = t.order")
            ->field("t.isread,t.type_fw,t.allot_source,o.id,o.qx,o.xiaoqu,o.yusuan,o.time,o.time_real")
            ->buildSql();
        return M("order_info")->table($buildSql)->alias("t1")
            ->join("LEFT JOIN qz_area as area on area.qz_areaid = t1.qx")
            ->join("LEFT JOIN qz_jiage as j on j.id = t1.yusuan")
            ->field("t1.isread,t1.type_fw,t1.id,area.qz_area as qx,j.name as yusuan,t1.time,t1.time_real,t1.xiaoqu,t1.allot_source")
            ->select();
    }

    /**
     * 获取新(分/赠)订单数量
     * @return [type] [description]
     */
    public function getNewNumbersByComid($comid, $start_time, $end_time){
      $map = array(
        "b.on" => array("EQ",4),
        "t.com" => array("EQ", $comid),
        "t.type_fw" => array("IN", "1,2"),
        "t.allot_source" => array("NEQ", 3),//手动抢单的不要提示
        "t.addtime" => array("BETWEEN", array($start_time, $end_time))
      );

      return  M("order_info")->alias("t")->where($map)
       ->join("inner join qz_orders as b on t.order = b.id")
       ->count("t.id");
    }
}
