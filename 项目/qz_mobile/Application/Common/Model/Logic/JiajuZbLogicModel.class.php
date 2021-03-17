<?php

namespace Common\Model\Logic;
use Think\Log;

class JiajuZbLogicModel
{
    const YIGUIPRICE = 3000;//衣柜价格
    const CHUANGPRICE = 2000;//床价格
    const SHAFAPRICE= 4000;//沙发价格
    const CHAJIPRICE = 1000;//茶几价格
    const DIANSHIGUIPRICE = 1500;//电视柜价格
    const XIEGUIPRICE = 1200;//鞋柜价格
    const CANZHUOPRICE = 2500;//餐桌价格
    const CHUGUIPRICE = 6000;//橱柜价格

    /**
     * 报价计算器
     * @param  [type] $mianji [面积]
     * @param  [type] $huxing [户型]
     * @return [type] $info  [description]
     */
    public function calculatePrice($mianji,$huxing)
    {
        $tempmianji = $mianji/100;
        //计算卧室价格
        $temphuxing = array_values($huxing);

        $info['yigui'] =  round((int)$temphuxing[2]*self::YIGUIPRICE*$tempmianji);
        $info['chuang'] =  round((int)$temphuxing[2]*self::CHUANGPRICE*$tempmianji);
        //计算客厅价格
        $info['shafa'] =  round((int)$temphuxing[0]*self::SHAFAPRICE*$tempmianji);
        $info['chaji'] =  round((int)$temphuxing[0]*self::CHAJIPRICE*$tempmianji);
        $info['dianshigui'] =  round((int)$temphuxing[0]*self::DIANSHIGUIPRICE*$tempmianji);
        $info['xiegui'] =  round((int)$temphuxing[0]*self::XIEGUIPRICE*$tempmianji);
        //计算餐厅价格
        $info['canzhuo'] =  round((int)$temphuxing[1]*self::CANZHUOPRICE*$tempmianji);
        //计算厨房价格
        $info['chugui'] =  round((int)$temphuxing[3]*self::CHUGUIPRICE*$tempmianji);
        //计算总价
        $info['total'] = $info['yigui']+$info['chuang']+$info['shafa']+$info['chaji']+$info['dianshigui']+$info['xiegui']+$info['canzhuo']+$info['chugui'];
        $info['total_str'] =str_split($info['total']);
        return $info;
    }


    /**
     * 订单发布
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function publish_order($data)
    {
        $data['id'] = self::getOrderNewId();
        $data['time'] = time();
        $data['time_real'] = time();
        $data['ip'] = get_client_ip();
        if (empty($data['tel_encrypt'])) {
            $data['tel_encrypt'] = tel_encrypt($data['tel']);
        }
        $data['tel'] = substr($data['tel'],0,3) .'*****'. substr($data['tel'],-3);

        if (empty($data['cs']) ) {
            $data['cs'] = '000001';
        }

        if (empty($data['qx'])) {
            $data['qx'] = '';
        }
        $i =  D('Common/Db/JiajuOrder')->addOrder($data);
        if ($i !== false) {
            return $data['id'];
        }
        return false;
    }

    /**
     * 获取当前电话其他信息
     * @param  [type] $tel [description]
     * @return [black_mark]      [黑名单标识 1.有 0 没有]
     * @return [last_order_id]      [最后发单的订单ID]
     */
    public function getHistoryInfo($tel)
    {
        $order_id = "";
        //查询是否是黑名单
        $count =  self::getOrderTelBlackCount($tel);

        if ($count > 0) {
            //黑名单的订单进去黑名单订单列表
            Log::record(date("Y-m-d H:i:s").' black_order '.json_encode($data));
        }
        //查询最后一次发单是在24小时之内
        //1，24小时内有发单且未审核，再次发单如果电话未变，即为更新订单(不再考虑城市问题，传入的城市直接更新)
        //2，24小时内有发单且已经审核，再次发单提示（您今日已发过订单，请明日再来。）
        $tel_encrypt = tel_encrypt($tel);
        $info = self::getLastPublishOrderInfo($tel_encrypt);

        if (count($info) > 0) {
            //24小时内有发单且已经审核,不是新单状态
            if ( ($info["time_real"] + 86400) >= time()) {
                if ( $info["on"] == "0" && $info["on_sub"] == "10" ) {
                    $order_id = $info["id"];
                }
            }
        }
        return ["black_mark" => $count,"last_order_id" => $order_id];
    }

    /**
     * 查询黑名单
     * @param  [type] $tel [description]
     * @return [type]      [description]
     */
    public function getOrderTelBlackCount($tel)
    {
        return D("Common/Db/JiajuOrderBlacklist")->getOrderTelBlackCount($tel);
    }

    /**
     * 获取最后发单信息
     * @param  [type] $tel_encrypt [description]
     * @return [type]              [description]
     */
    public function getLastPublishOrderInfo($tel_encrypt)
    {
       return D("Common/Db/JiajuOrder")->getLastPublishOrderInfo($tel_encrypt);
    }

    /**
     * 获取IP发单数量
     * @param  [type] $ip [description]
     * @return [type]     [description]
     */
    public function getOrderCountByIp($ip)
    {
        $begin = strtotime(date("Y-m-d"));
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
        if (C("IP_WHITE_LIST") !== "") {
            $whiteIp = C("IP_WHITE_LIST");
        } else {
            $whiteIp = "'222.92.114.186','223.112.69.58','127.0.0.1'";
        }
        return D("Common/Db/JiajuOrder")->getOrderCountByIp($ip,$begin,$end,$whiteIp);
    }

    public function getOrderNewId()
    {
        return  "J".date('Ymd'). sprintf("%05d%03d", microtime()*1000000, rand(10,99));
    }

    /**
     * 添加订单采集数据
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function public_yy_order($order_id)
    {
        $data = [
            "oid" => $order_id,
            "urlid" => 0,
            "src" => "",
            "ref" => "",
            "time" => time(),
            "vi" => ""
        ];

        $src = cookie('QZSRC');
        if (!empty($src)) {
            $data["src"] = $src;
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $parseUrl = parse_url($_SERVER['HTTP_REFERER']);
            $ref = preg_replace('/[http|https]\:\/\//', '', $parseUrl["host"].$parseUrl["path"]);//去掉协议
            //过滤http、https协议
            $pattern = array("http://","https://");
            $ref = str_replace( $pattern,"",$ref);

            //是否以html结尾
            $pattern = '/.*?\.html$/';
            preg_match($pattern,$ref,$m);

            if (count($m) > 0) {
                $data["ref"] = $m[0];
            } else {
                //过滤掉多余的反斜杠
                $reg = '/\/+/';
                $ref = preg_replace($reg, "/",  $ref);

                //过滤最后一个反斜杠
                $reg = '/\/$/';
                $ref = preg_replace($reg, "", $ref);
            }
            $data["ref"] = $ref;
            //查询url的ID
            if (!empty($ref)) {
                $url = $ref."/";
                $urlInfo = D('Common/Db/YySiteUrl')->getUrlInfo($url);
                if (count($urlInfo) > 0) {
                    $data["urlid"] = $urlInfo["id"];
                }
            }
        }

        //如果是微信发单
        if( false !== strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") ) {
            $data['vi'] = 'servicewechat';
        } else {
            $data['vi'] = cookie('vi');
            if(empty($yy_data['vi'])){
                $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                for ( $i = 0; $i < 16; $i++ )
                {
                    $data['vi'] .= $chars[ mt_rand(0, strlen($chars) - 1) ];
                }
                cookie('vi',$data['vi'],['expire'=>3600]);
            }
        }

        //添加渠道数据
        return D('Common/Db/JiajuYyOrderInfo')->addInfo($data);
    }

    /**
     * 编辑订单
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function update_order($id,$data)
    {
        return D('Common/Db/JiajuOrder')->editInfo($id,$data);
    }

    public function public_tel_safe($order_id,$tel)
    {
        $data = [
            "orderid" => $order_id,
            "tel8" => $tel
        ];
        return D("Common/Db/JiajuOrder")->addTelSafe($data);
    }

    /**
     * 根据装修订单编号查询家具订单信息
     * @param $zxOrderId
     * @return array|mixed
     */
    public function getJiajuOrderByZxid($zxOrderId)
    {
        $map['order_id'] = $zxOrderId;
        return D("Common/Db/JiajuOrder")->getOrderByMap($map);
    }
}
