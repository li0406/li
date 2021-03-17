<?php
// +----------------------------------------------------------------------
// | WorksiteLiveLogic 工地直播逻辑
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\WorkSiteInfo;
use app\common\model\db\WorkSiteLive;
use app\index\enum\WorkSiteLiveStepCodeEnum;
use think\facade\Request;

class WorkSiteLiveLogic
{
    /**
     * 添加签单施工编号
     * @param null $order_id
     * @param null $qiandna_company
     * @param int $order_type 订单类型 1.普通订单 2.主动资讯订单
     * @return array
     */
    public function addOrderWorkSite($order_id = null, $qiandna_company = null, $order_type = 1)
    {
        if (empty($order_id) || empty($qiandna_company)) {
            return [];
        }
        $db = new WorkSiteLive();
        //验证当前订单是否已经生成过编号
        $where = [
            'order_id' => ['eq', $order_id],
            'order_type' => ['eq', $order_type],
        ];
        $info = $db->getWorkSiteInfo($where, 'id');
        if (count($info) > 0) {
            return [];
        }
        $code = randomkeys();
        $save = [
            'code' => $code,
            'order_id' => $order_id,
            'order_type' => $order_type,
            'company_id' => $qiandna_company,
            'state' => 1,
            'endtime' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ];
        return $db->saveWorkSite($save);
    }

    public function checkOrderWithdraw($order_id,$order_type = 1){
        $where = [
            'l.order_id'=>['eq',$order_id],
            'l.order_type'=>['eq',$order_type],
        ];
        $db = new WorkSiteLive();
        $work_site = $db->getWorkInfo($where)->toArray();
        return $work_site;
    }

    public function delWorkSite($order_id, $order_type = 1)
    {
        //查询是否有施工信息
        $where = [
            'l.order_id' => ['eq', $order_id],
            'l.order_type' => ['eq', $order_type],
        ];
        $db = new WorkSiteLive();

        //获取当前订单的直播信息
        $live_info = $db->getWorkSiteInfo(['order_id' => ['eq', $order_id], 'order_type' => ['eq', $order_type]], 'id');
        if (count($live_info) == 0) {
            return true;
        }

        $work_info = $db->getWorkInfo($where)->toArray();
        if (count($work_info) > 0) {
            //有施工信息
            $infoIds = array_column($work_info, 'id');
            //删除多媒体表
            $db->delWorkSiteMedia([['info_id', 'in', $infoIds]]);
            //删除施工信息表
            $db->delWorkSiteInfo([['id', 'in', $infoIds]]);
        }
        //删除直播用户关联表
        $db->delWorkSiteUserRelate([['live_id', '=', $live_info['id']]]);
        //删除直播表
        $db->delWorkSiteLive([['id', '=', $live_info['id']]]);
        return true;
    }

    /**
     * 工地直播列表
     * @param $input
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getLiveList($input, $page = 1, $limit = 10){
        $userLogic = new AdminuserLogic();
        $city = $userLogic::getAndValidateSaleCity($input['cs']);
        if($city == false){
            return sys_response(3000001,'当前城市无权限查看!');
        }
        $input['cs'] = $city;
        $user = Request::instance()->user;
        $input["company_id"] = $user["user_id"];

        $worksiteLive = new WorkSiteLive();
        $count = $worksiteLive->getPageCount($input);

        $list = [];
        if ($count > 0) {
            $list = $worksiteLive->getPageList($input, ($page - 1) * $limit, $limit);
            //获取最新施工信息
            $infoDb = new WorkSiteInfo();
            $info = $infoDb->getNewestWorkSiteInfo(array_column($list, 'id'))->toArray();
            $info = array_column($info, null, 'live_id');
            foreach ($list as $key => $item) {
                $list[$key]["qiandan_date"] = date("Y-m-d H:i:s", $item["datetime"]);
                $list[$key]["type_name"] = "主动咨询单";
                if ($item["order_type"] == 1) {
                    $list[$key]["type_name"] = $item["type_fw"] == 1 ? "分单" : "赠单";
                }
                $list[$key]["huxing_other_name"] = sprintf("%s室 %s厅 %s卫", $item["shi"], $item["ting"], $item["wei"]);
                if (isset($info[$item['id']])) {
                    $list[$key]['step_name'] = WorkSiteLiveStepCodeEnum::getItem('step',$info[$item['id']]['step']);
                }else{
                    $list[$key]['step_name'] = '';
                }
            }
        }

        return ["count" => $count, "list" => $list];
    }

    /**
     * 获取工地直播详情数据(PC端)
     * @param $id
     * @return array|string|\think\Model|null
     */
    public function getDetail($id){
        $worksiteLive = new WorkSiteLive();
        $detail = $worksiteLive->getWorkSiteInfo(['id' => ['eq', $id]]);

        $info = [];
        if (!empty($detail)) {
            if ($detail->order_type == 1) {
                $orderinfo = $worksiteLive->getOrderInfo($detail->order_id);
            } else {
                $orderinfo = $worksiteLive->getOrderReportInfo($detail->order_id);
            }

            if (!empty($orderinfo)) {
                // 数组合并前删除订单ID防止冲突
                unset($orderinfo["id"]);
                $info = array_merge($detail->toArray(), $orderinfo);
                $info["qiandan_date"] = date("Y-m-d H:i:s", $info["datetime"]);

                $info["type_name"] = "主动咨询单";
                if ($info["order_type"] == 1) {
                    $info["type_name"] = $info["type_fw"] == 1 ? "分单" : "赠单";
                }

                $info["huxing_other_name"] = sprintf("%s室 %s厅 %s卫", $info["shi"], $info["ting"], $info["wei"]);
            }
        }

        return $info;
    }
}