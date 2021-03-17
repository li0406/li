<?php
/**
 * 订单相关模块
 */

namespace Home\Model\Logic;

use Home\Model\Db\OrderSourceInRelationModel;
use Home\Model\Db\YyOrderInfoModel;
use Home\Model\OrderSourceModel;
use Home\Model\OrderSourceStatsModel;
use Home\Model\DepartmentIdentifyModel;
use Home\Model\QcInfoModel;

class OrderSourceLogicModel
{
    private $dept = array(
        "总裁办" => "zcb",
        "推广二部" => "tg2",
        "推广一部" => "tg1",
        "渠道部" => "qd",
        "公装部" => "gz",
        "新媒体部" => "media",
        "线下广告" => "online",
        "品牌部" => 'brand'
    );

    /**
     * 获取渠道组
     * @return array
     */
    public function getSourceGroupList(){
        //来源组
        $result = D('OrderSource')->getSourceGroup('1','');
        $info["group"] = array(
            array(
                'id' => "",
                'name' => '请选择渠道组',
                'children' => array(
                    array(
                        'id' => "",
                        'name' => '请选择'
                    )
                )
            )
        );
        $group = [];
        $groupChild = [];//子级
        foreach ($result as $key => $value) {
            if (!empty($value["id"])) {
                if (!array_key_exists($value["parentid"],$group)) {
                    $group[$value["parentid"]]["id"] = $value["parentid"];
                    $group[$value["parentid"]]["name"] = $value["parent_name"];
                    $group[$value["parentid"]]["children"][] = array(
                        "id" => "",
                        "name" => "请选择"
                    );
                }
                $group[$value["parentid"]]["children"][] = array(
                    "id" => $value["id"],
                    "name" => $value["name"]
                );
                //因为页面只有一层显示,原本父级和子级联动 , 现在把子级直接取出 , 和父级放一起(原逻辑不变 , 在第一层添加子级数据)
                $groupChild[$value["id"]]['id'] = $value["id"];
                $groupChild[$value["id"]]['name'] = $value["name"];
                $groupChild[$value["id"]]['parentid'] = $value["parentid"];
            } else {
                if (!array_key_exists($value["parentid"],$info["group"])) {
                    if (!empty($value["parent_name"])) {
                        $group[$value["parentid"]]["id"] = $value["parentid"];
                        $group[$value["parentid"]]["name"] = $value["parent_name"];
                        $group[$value["parentid"]]["children"][] = array(
                            "id" => "",
                            "name" => "请选择"
                        );
                    }
                }
            }
        }
        $group = array_merge($info["group"],$group,$groupChild);
        return $group;
    }

    /**
     * 获取当前渠道组的所有渠道
     * @param $get
     * @return mixed
     */
    public function getSrcByGroup($get){
        if(!$get['group']){
            return [];
        }
        //获取当前渠道组的子渠道(可能没有)
        $groups = D("OrderSource")->getSourceGroupChild($get['group']);
        //再将自身和子级 合并
        $groups = array_merge(array_column($groups,'id'),[$get['group']]);
        //取出所有数据
        $list = D("OrderSource")->getAllSourceList($groups);
        return $list;
    }

    public function getOrderSourcesList($get,$depts){
        $map = [];
        if($get['start'] && $get['end']){
            $map['time'] = [['EGT', strtotime($get['start'] . ' 00:00:00')], ['ELT', strtotime($get['end'] . ' 23:59:59')], 'AND'];
            $StartMonth = $get['start'].' 00:00:00'; //开始日期
            $EndMonth = $get['end'].' 23:59:59'; //结束日期
        } else {
            $map['time'] =[['EGT', strtotime(date('Y-m').'-01 00:00:00')],['ELT', time()], 'AND'];
            $StartMonth   = date('Y-m').'-01 00:00:00'; //开始日期
            $EndMonth     = date('Y-m-d').' 23:59:59'; //结束日期
        }
        if(count($depts)>0){
            $map['dept'] = ['in',$depts];
        }
        if($get['group']){
            $map['group'] = ['eq',$get['group']];
        }
        if($get['src']){
            $map['src'] = ['eq',$get['src']];
        }
        $orderSourceModel = new OrderSourceModel();
        //1.查询出的数据
        $list = $orderSourceModel->getOrderSourcesList($map);
        $dayData = [];
        //1.1将时间作为索引
        foreach ($list as $k=>$v){
            $dayData[$v['t']] = $v;
            unset($list[$k]);
        }
        //2.循环查询的月份的每一天,并将数据放入
        $ToStartMonth = strtotime($StartMonth); //转换一下
        $ToEndMonth = strtotime($EndMonth); //一样转换一下
        $returnData = [];

        while ($ToStartMonth < $ToEndMonth) {
            $NewMonth = trim(date('Y-m-d',$ToStartMonth),' ');
            $ToStartMonth += strtotime('+1 day',$ToStartMonth) - $ToStartMonth;
            //2.1将对应数据放入
            if($dayData[$NewMonth]){
                $returnData[$NewMonth] = $dayData[$NewMonth];
                unset($dayData[$NewMonth]);
            }else{
                $returnData[$NewMonth]['t'] = $NewMonth;
            }
        }

        return $returnData;
    }

    public function getDepartmentList(){
        $department = [];
        $result = D("DepartmentIdentify")->findAllDept();
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["dept_belong"], $department)) {
                $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
            }
            $tag = $this->dept[$value["dept_belong"]];
            if (!array_key_exists($tag, $department[$value["dept_belong"]]["child"])) {
                $department[$value["dept_belong"]]["child"][$tag] = array(
                    "id" => $tag,
                    "name" => $value["dept_belong"].'全部'
                );
            }

            if (!array_key_exists($value["id"], $department[$value["dept_belong"]]["child"])) {
                $department[$value["dept_belong"]]["child"][$value["id"]] = array(
                    "id" => $value["id"],
                    "name" => $value["name"]
                );
            }
        }
        return $department;
    }

    /**
     * 获取自己管辖的渠道
     * @return mixed
     */
    public function getSourceDepartmentList()
    {
        if (session("uc_userinfo.uid") != 1) {
            $result = D("DepartmentIdentify")->findMyDept(session("uc_userinfo.id"));
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["dept_belong"], $department)) {
                    $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
                }
                $tag = $this->dept[$value["dept_belong"]];
                if (!array_key_exists('all', $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$tag] = array(
                        "id" => $tag,
                        "name" => $value["dept_belong"].'全部'
                    );
                }

                if (!array_key_exists($value["dept"], $department[$value["dept_belong"]]["child"])) {
                    if ($value["dept"] != 15) {
                        $department[$value["dept_belong"]]["child"][$value["dept"]] = array(
                            "id" => $value["dept"],
                            "name" => $value["deptname"]
                        );
                    }
                    $dept[] = $value["dept"];
                }
            }
        } else {
            $result = D("DepartmentIdentify")->findAllDept();
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["dept_belong"], $department)) {
                    $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
                }
                $tag = $this->dept[$value["dept_belong"]];
                if (!array_key_exists($tag, $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$tag] = array(
                        "id" => $tag,
                        "name" => $value["dept_belong"].'全部'
                    );
                }

                if (!array_key_exists($value["id"], $department[$value["dept_belong"]]["child"])) {
                    if ($value["id"] != 15) {
                        $department[$value["dept_belong"]]["child"][$value["id"]] = array(
                            "id" => $value["id"],
                            "name" => $value["name"]
                        );
                    }
                }
            }
        }

        //如果只有一个总裁办全部，删除这个全部项
        if (count( $department["总裁办"]["child"]) == 1) {
            unset($department["总裁办"]);
        }

        return $department;
    }

    /**
     * 家具转装修统计
     */
    public function getConvertToNormalList($begin,$end,$department,$charge)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }

        $model = new OrderSourceStatsModel();
        $result = $model->getConvertToNormalList($monthStart,$monthEnd,$department,$charge);

        //实际分单量、实际分单率
        $realOrder = $model->getConvertToNormalListWithReal($monthStart,$monthEnd,$department,$charge);
        return $this->summaryConvertData($result,$realOrder);
    }

    public function getDepartmentListWithBelong($belong)
    {
        $model = new DepartmentIdentifyModel();
        $result = $model->getDepartmentListWithBelong($belong);
        foreach ($result as $key  => $value) {
            $list[] = $value["id"];
        }
        return $list;
    }

    public function getMyChildSrcList($category,$level,$group_id)
    {
        $model = new OrderSourceModel();
        $result = $model->getMyChildSrcList($category,$level,$group_id);

        foreach ($result as $key => $value) {
            if (!empty($value["src"])) {
                $list[] = $value["src"];
            }
        }
        return $list;
    }

    public function getSourceDetailsWithConvert($begin,$end,$department,$charge,$srcList)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }

        $model = new OrderSourceStatsModel();
        $order = $model->getConvertToNormalList($monthStart,$monthEnd,$department,$charge,$srcList);

        //实际分单量、实际分单率
        $realOrder = $model->getConvertToNormalListWithReal($monthStart,$monthEnd,$department,$charge,$srcList);

        return $this->summaryConvertDataWithDetails($order,$realOrder);

    }

    public function getNormalSourceDetailsWithConvert($begin,$end,$department,$charge,$srcList)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }

        $model = new OrderSourceStatsModel();
        //分单
        $model = new OrderSourceStatsModel();
        $order = $model->getConvertToJiajuList($monthStart,$monthEnd,$department,$charge,$srcList);

        //实际分单
        $realOrder = $model->getConvertToJiajuListWithReal($monthStart,$monthEnd,$department,$charge,$srcList);

        return $this->summaryConvertDataWithDetails($order,$realOrder);
    }
    public function getConvertToJiajuList($begin,$end,$department,$charge)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }

        //分单
        $model = new OrderSourceStatsModel();
        $order = $model->getConvertToJiajuList($monthStart,$monthEnd,$department,$charge);
        //实际分单
        $realOrder = $model->getConvertToJiajuListWithReal($monthStart,$monthEnd,$department,$charge);

        $list = $this->summaryConvertData($order,$realOrder);
        return $list;
    }

    public function getTtransfertoNormalList($begin,$end,$department,$charge)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }

        $model = new OrderSourceStatsModel();
        $order = $model->getTtransfertoNormalList($monthStart,$monthEnd,$department,$charge);
        $realOrder = $model->getTtransfertoNormalListWithReal($monthStart,$monthEnd,$department,$charge);
        $list = $this->summaryConvertData($order,$realOrder);
        return $list;
    }

    public function getTransfertoNormalDetailsWithConvert($begin,$end,$department,$charge,$srcList)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }

        $model = new OrderSourceStatsModel();
        $order = $model->getTtransfertoNormalList($monthStart,$monthEnd,$department,$charge,$srcList);

        $realOrder = $model->getTtransfertoNormalListWithReal($monthStart,$monthEnd,$department,$charge,$srcList);

        return $this->summaryConvertDataWithDetails($order,$realOrder);
    }

    public function getCconvertOrderDetailsWithNormal($src,$begin,$end,$status)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }
        $model = new OrderSourceStatsModel();
        $count = $model->getCconvertOrderDetailsWithNormalCount($src,$monthStart,$monthEnd,$status);

        
        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,100);
            $show    = $p->show();
            $list = $model->getCconvertOrderDetailsWithNormal($p->firstRow,$p->listRows, $src,$monthStart,$monthEnd,$status);
        }
        return array("page"=>$show,"list"=>$list);
    }

    public function getCconvertOrderDetailsWithJiaju($src,$begin,$end,$status)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }
        $model = new OrderSourceStatsModel();
        $count = $model->getCconvertOrderDetailsWithJiajuCount($src,$monthStart,$monthEnd,$status);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,100);
            $show    = $p->show();
            $list = $model->getCconvertOrderDetailsWithJiaju($p->firstRow,$p->listRows, $src,$monthStart,$monthEnd,$status);
        }
        return array("page"=>$show,"list"=>$list);
    }

    public function getTransferOrderDetailsWithNormal($src,$begin,$end,$status)
    {
        $monthStart = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $monthEnd = mktime(23,59,0,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 -1 ;
        }
        $model = new OrderSourceStatsModel();
        $count = $model->getTransferOrderDetailsWithNormalCount($src,$monthStart,$monthEnd,$status);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,100);
            $show    = $p->show();
            $list = $model->getTransferOrderDetailsWithNormal($p->firstRow,$p->listRows, $src,$monthStart,$monthEnd,$status);
        }
        return array("page"=>$show,"list"=>$list);
    }
    /**
     * 合并转化数据
     */
    private function summaryConvertData($data,$realData)
    {
        $all = $list = [];
        foreach ($data as $key => $value) {
            if (!empty($value["parent_id"])) {
                if (!array_key_exists($value["parent_id"],$list)) {
                    $list[$value["parent_id"]]["name"] = $value["parent_name"];
                    $list[$value["parent_id"]]["id"] = $value["parent_id"];
                }
                $parentid = $value["parent_id"];
            } elseif(empty($value["parent_id"])){
                if (!array_key_exists($value["group_id"],$list)) {
                    $list[$value["group_id"]]["name"] = $value["group_name"];
                    $list[$value["group_id"]]["id"] = $value["group_id"];
                }
                $parentid = $value["group_id"];
            }

            //发单量
            $list[$parentid]["all_count"] ++;

            $all["all_count"] ++;

            if ($value["tel_mark"] == 1) {
                //已拨打量
                $list[$parentid]["tel_count"] ++;
                $all["tel_count"] ++;
            } elseif($value["tel_mark"] == 0){
                //未拨打量
                $list[$parentid]["un_tel_count"] ++;
                $all["un_tel_count"] ++;
            }

            if ($value["on"] == 4) {
                //有效单量
                $list[$parentid]["yx_count"] ++;
                $all["yx_count"] ++;
            }

            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                //分单量
                $list[$parentid]["fen_count"] ++;
                $all["fen_count"] ++;
            }
            //分单率
            $list[$parentid]["fen_rate"] = setInfNanToN(round(($list[$parentid]["fen_count"]/$list[$parentid]["all_count"])*100,2));
            $all["fen_rate"] = setInfNanToN(round(($all["fen_count"]/$all["all_count"])*100,2));
        }

        foreach ($realData as $key => $value) {
            if (!empty($value["parent_id"])) {
                $parentid = $value["parent_id"];
                $parent_name = $value["parent_name"];
            } elseif (empty($value["parent_id"])) {
                $parentid = $value["group_id"];
                $parent_name = $value["group_name"];

            }

            if (!array_key_exists($parentid, $list["list"])) {
                $list[$parentid]["name"] = $parent_name;
                $list[$parentid]["id"] = $parentid;
            }
            //实际分单量
            $list[$parentid]["real_fen_count"]++;
            $all["real_fen_count"] ++;

            if ($value["on"] == 4) {
                $list[$parentid]["real_yx_count"]++;
                $all["real_yx_count"] ++;
            }
            //分单率
            if ($list[$parentid]["all_count"] > 0) {
                $list[$parentid]["real_fen_rate"] = setInfNanToN(round(($list[$parentid]["real_fen_count"]/$list[$parentid]["all_count"])*100,2));
            } else {
                $list[$parentid]["real_fen_rate"] = 0;
            }
            $all["real_fen_rate"] = setInfNanToN(round(($all["real_fen_count"]/$all["all_count"])*100,2));
        }

        return array("list" => $list,"all" => $all);
    }

    private function summaryConvertDataWithDetails($data,$realData)
    {
        $src = $list = [];
        foreach ($data as $key => $value) {
            if ( !array_key_exists($value["group_id"],$list)) {
                $list[$value["group_id"]]["name"] = $value["group_name"];
                $list[$value["group_id"]]["id"] = $value["group_id"];
                $parentid = $value["group_id"];
            }

            if (!array_key_exists($value["src"],$src)) {
                $src[$value["src"]]["name"] = $value["src_name"];
            }

            //发单量
            $list[$parentid]["all_count"] ++;
            $src[$value["src"]]["all_count"] ++;

            if ($value["tel_mark"] == 1) {
                //已拨打量
                $list[$parentid]["tel_count"] ++;
                $src[$value["src"]]["tel_count"] ++;
            } elseif($value["tel_mark"] == 0){
                //未拨打量
                $list[$parentid]["un_tel_count"] ++;
                $src[$value["src"]]["un_tel_count"] ++;
            }

            if ($value["on"] == 4) {
                //有效单量
                $list[$parentid]["yx_count"] ++;
                $src[$value["src"]]["yx_count"] ++;
            }

            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                //分单量
                $list[$parentid]["fen_count"] ++;
                $src[$value["src"]]["fen_count"] ++;
            }
            //分单率
            $list[$parentid]["fen_rate"] = setInfNanToN(round(($list[$parentid]["fen_count"]/$list[$parentid]["all_count"])*100,2));
            $src[$value["src"]]["fen_rate"] = setInfNanToN(round(($src[$value["src"]]["fen_count"]/$src[$value["src"]]["all_count"])*100,2));
        }

        foreach ($realData as $key => $value) {
            $parentid = $value["group_id"];
            $parent_name = $value["group_name"];

            if (!array_key_exists($parentid, $list)) {
                $list[$parentid]["name"] = $parent_name;
                $list[$parentid]["id"] = $parentid;
            }

            if (!array_key_exists($value["src"], $src)) {
                $src[$value["src"]]["name"] = $value["src_name"];
            }

            //实际分单量
            $list[$parentid]["real_fen_count"]++;
            $src[$value["src"]]["real_fen_count"]++;

            if ($value["on"] == 4) {
                $list[$parentid]["real_yx_count"]++;
                $src[$value["src"]]["real_yx_count"]++;
            }
            //分单率
            if ($list[$parentid]["all_count"] > 0) {
                $list[$parentid]["real_fen_rate"] = setInfNanToN(round(($list[$parentid]["real_fen_count"]/$list[$parentid]["all_count"])*100,2));
            } else {
                $list[$parentid]["real_fen_rate"] = 0;
            }

            if ($src[$value["src"]]["all_count"] > 0) {
                $src[$value["src"]]["real_fen_rate"] = setInfNanToN(round(($src[$value["src"]]["real_fen_count"]/$src[$value["src"]]["all_count"])*100,2));
            } else {
                $src[$value["src"]]["real_fen_rate"] = 0;
            }
        }

        return array("list" =>$list,"src" => $src);
    }

    /**
     * 渠道来源查询
     *
     * @param string $order 订单编号
     * @param string $tel 手机号码
     * @return mixed
     */
    public function srcsearch($order, $tel)
    {
        $map = [];
        if (!empty($tel)) {
            $tel_encrypt = order_tel_encrypt($tel);
            $map['o.tel_encrypt'] = $tel_encrypt;
        }
        if (!empty($order)) {
            $map['a.oid'] = $order;
        }

        if (stripos($order,'j') === 0) {
            $yyOrderInfoModel = new YyOrderInfoModel();
            return $yyOrderInfoModel->getJiajuSrcYyOrderInfo($map);
        } else {
            $yyOrderInfoModel = new YyOrderInfoModel();
            $result = $yyOrderInfoModel->getZxSrcYyOrderInfo($map);

            foreach ($result as $item) {
                $list[$item["id"]] = $item;
                $ids[] = $item["id"];
            }
            //添加订单质检人员信息
            if (count($ids) > 0) {
                $model = new QcInfoModel();
                $result = $model->getQcInfoByIds($ids);
                foreach ($result as $item) {
                    if (array_key_exists($item["order_id"],$list)) {
                        $list[$item["order_id"]]["qc_name"] = $item["op_name"];
                    }
                }
            }
            return $list;
        }
    }

    /**
     * 添加渠道/订单类型关联
     * @param $src
     * @param $source_in
     * @return array
     */
    public function saveOrderSourceInRelation($src, $source_in)
    {
        if (empty($src) || (empty($source_in) && $source_in != 0)) {
            return [];
        }
        //删除原有关联
        $relationDb = new OrderSourceInRelationModel();
        $relationDb->delSourceInRelation(['src' => ['eq', $src]]);
        if($source_in != 0){
            $save = [
                'src' => $src,
                'source_in' => $source_in,
            ];
            $relationDb->addSourceInRelation($save);
        }
    }

    public function getSourceBySourceName($name)
    {
        $sourceDb = new OrderSourceModel();
        return $sourceDb->getSourceSrcByName($name);
    }

    public function getRelatedSourceByName($name, $limit = 100) {
        $orderSourceModel = new OrderSourceModel();
        return $orderSourceModel->getRelatedSourceByName($name, $limit);
    }

    public function setSourceRelatedAccount($account_id, $source_ids){
        $result = false;
        if (count($source_ids) > 0) {
            $data = [
                "account_id" => $account_id,
            ];

            $orderSourceModel = new OrderSourceModel();
            $result = $orderSourceModel->updateAll($source_ids, $data);
        }

        return $result;
    }
}