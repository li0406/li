<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class TuiController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 推广页面动态生成
     * 根据数据库配置OrderSourceManage表中动态读取参数，拼接成完整页面
     */
    public function index()
    {
        $templeteArray = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
        if (empty($templeteArray)) {
            $this->_empty();
        }
        if (empty($templeteArray[0]) || empty($templeteArray[1])) {
            $this->_empty();
        }
        $templete = $templeteArray[0];
        if (stripos($templeteArray[1], '?') === false) {
            $path = $templeteArray[1];
        } else {
            $path = explode('?', $templeteArray[1])[0];
        }

        $src = I('get.src', '');
        $map['type'] = 1; //类型 1为PC 2为M端
        $map['templete'] = $templete; //模板
        $map['path'] = $path; //路径
        //$map['src'] = $src;
        $map['status'] = 1; //选择已经启用的
        $sourceInfoCK = 'C:PC:tui:dt:'.md5(json_encode($map));
        $sourceInfo = S($sourceInfoCK);
        if (empty($sourceInfo)) {
            $sourceInfo = D('OrderSourceManage')->getInfoByMap($map);
            S($sourceInfoCK, $sourceInfo, 60 * 5);
        }

        if (empty($sourceInfo)) {
            $this->_empty();
        }
        
        $this->assign('base_code', $sourceInfo['base_code']);
        $this->assign('js_code', $sourceInfo['js_code']);
        $this->assign("src", $src);
        if(!empty($src)){
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if(empty($weixinResult)){
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $this->assign("img", $weixinResult['img']);

        switch ($templete) {
            case 'sheji':  //设计页需要加载的数据
                //添加选中效果
                $this->assign('choose_menu', 'sheji');
                //导航栏标识
                $this->assign("tabIndex", 2);
                break;
            case 'baojia': //装修报价页需要加载的数据
                //判断是否是搜索引擎蜘蛛
                $robotIsTrue = B("Common\Behavior\RobotCheck");
                if (true === $robotIsTrue) {
                    $this->assign('robot', 1);
                }
                if (empty($this->cityInfo["bm"])) {
                    $t = T("Home@Index:header");
                } else {
                    if (!$robotIsTrue) {
                        $t = T("Sub@Index:header");
                    }
                    //显示头部导航栏效果
                    $this->assign("nav_show", true);
                }
                //导航栏标识
                $this->assign("tabIndex",6);
                //添加选中效果
                $this->assign('choose_menu', 'zxbj');
                $headerTmp = $this->fetch($t);
                $this->assign("headerTmp", $headerTmp);
                //检查客户端设备类型 移动版本跳转到
                B("Home\Behavior\MobileBrowserCheck");
                $info = S('Zxbj:Index:Telnum');
                if (empty($info)) {
                    import('Library.Org.Util.App');
                    $app = new \App();
                    $head = array('135', '136', '137', '138', '139', '150', '151', '152', '159', '130', '131', '132', '155', '133', '153', '189');
                    for ($i = 0; $i < 10; $i++) {
                        $xing = $app->getRandXing();
                        $sex_array = array("先生", "女士");
                        $sex = $sex_array[rand(0, 1)];
                        $sub["name"] = $xing . $sex;
                        $sub["time"] = rand(1, 6);
                        $sub["tel"] = $head[rand(0, count($head) - 1)] . '****' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                        $info[] = $sub;
                    }
                    S('Zxbj:Index:Telnum', $info, 300);
                }

                if (isset($_COOKIE["w_qizuang_n"])) {
                    $orderid = $_COOKIE["w_qizuang_n"];
                    $order = D("Orders")->getOrderInfoById($orderid);
                    $result = $this->calculatePrice($order["mianji"], $order["cs"]);
                    $result["cs"] = $order["cs"];
                    $result["qx"] = $order["qx"];
                    $result["mianji"] = $order["mianji"];
                    $result["xiaoqu"] = $order["xiaoqu"];
                    $result["tel8"] = $order["tel8"];

                    $this->assign("price", $result);
                }
                $source = '30';
                if (I('get.source')) {
                    $source = remove_xss(trim(I('get.source')));
                }
                $this->assign("source", $source);
                $this->assign("info", $info);
                break;
            case 'zhaobiao':  //招标页面需要加载的数据
                $zbInfo = S('Cache:Zhaobiao');
                if (!$zbInfo) {
                    //获取申请装修服务列表
                    $zbInfo["orders"] = $this->getOrderList();
                    //获取最新业主点评
                    $zbInfo["comments"] = $this->getComment();
                    S("Cache:Zhaobiao", $zbInfo, 3600);
                }
                $info["orders"] = $zbInfo["orders"];
                //随机取6条评论信息
                $info["comment"] = array_slice($zbInfo["comments"], mt_rand(1, count($zbInfo["comments"]) - 6), 6);
                //导航栏标识
                $this->assign("tabIndex", 1);
                $this->assign("zbInfo", $info);
                break;
            default:
                $this->_empty();
                break;
        }
        $this->display($templete);
    }

    private function getOrderList()
    {
        // 获取风格
        $fengge = D("Fengge")->getfg();
        import('Library.Org.Util.App');
        $app = new \App();
        $halfCount = 0;
        for ($i = 0; $i < 50; $i++) {
            $xing = $app->getRandXing();
            $sex_array = array("先生", "女士");
            $sex = $sex_array[rand(0, 1)];
            $sub["name"] = $xing . $sex;
            $mianji = rand(80, 120);
            $sub["mianji"] = $mianji;
            $leixing_array = array("半包", "全包");
            $seed = rand(0, 1);
            $halfCount = $seed == 0 ? $halfCount + 1 : $halfCount;
            if ($halfCount > 10) {
                $seed = 1;
            }
            $jiage = $seed == 0 ? $mianji * 368 : $mianji * 688;
            $sub["leixing"] = $leixing_array[$seed];
            $sub["jiage"] = round(($jiage / 10000), 1) . "万元";
            $sub["fengge"] = $fengge[rand(0, count($fengge) - 1)]["name"];
            $show_time = mt_rand(10, 600);
            if ($show_time >= 60) {
                $sub["time"] = floor($show_time / 60) . '分钟前';
            } else {
                $sub["time"] = $show_time . '秒前';
            }
            $data[] = $sub;
        }
        return $data;
    }

    /**
     * 获取评论
     * @return mixed
     */
    private function getComment()
    {
        $comment = D("Comment")->getNewComment(50);
        foreach ($comment as $key => $value) {
            $rand = rand(1, 10);
            $comment[$key]["uptime"] = $rand . "分钟前";
        }
        shuffle($comment);
        return $comment;
    }

    /**
     * 计算价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    public function calculatePrice($mianji, $cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积

        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);
        if (empty($price)) {
            $price = 300;
        }

        $total = $price * 1.2 * $mianji;
        $result['kt'] = $total * 0.25;
        $result['zw'] = $total * 0.18;
        $result['wsj'] = $total * 0.16;
        $result['cf'] = $total * 0.08;
        $result['sd'] = $total * 0.25;
        $result['other'] = $total * 0.08;
        $result['total'] = $total;
        return $result;
    }
}