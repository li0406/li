<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
/**
 * 用户统计相关
 */
class UserstatisticsController extends HomeBaseController {
    /**
     * [details 会员详情统计]
     * @return [type] [description]
     */
    public function memberDetails(){
        $data = I('get.');
        //设置默认值
        $id = empty($data['id']) ? '' : intval($data['id']);
        $name = empty($data['name']) ? '' : trim($data['name']);
        $start_time = empty($data['start_time']) ? '' : trim($data['start_time']);
        $end_time = empty($data['end_time']) ? '' : trim($data['end_time']);
        $city = empty($data['city']) ? '' : intval($data['city']);
        $department = empty($data['department']) ? '' : intval($data['department']);
        if (!empty($department)) {
            $roleCids = $this->getRoleCitys($department);
            if (!empty($city)) {
                $city = array_intersect(array($city),$roleCids);
            } else {
                //设置为9，此处应该搜索不到
                if (!empty($roleCids)) {
                    $city = $roleCids;
                } else {
                    $city = false;
                }
            }
        }

        //获取列表的初步信息
        $tempResult = $this->getMemberDetailList($id, $name, $start_time, $end_time, $city);
        //获取列表的其他信息
        $tempResult['list'] = $this->getUserVipDetailOtherInfo($tempResult['list']);

        $info['info'] = $tempResult;
        //获取管辖城市信息
        $info['city'] = D('Quyu')->getQuyuListWithFirstChar();

        //获取部门筛选列表
        $map['type'] = '1';
        $tree = D('SaleSetting')->getCategory($map);
        $tree = getSaleCategory($tree);
        $info['tree'] = $tree;


        $this->assign('info',$info);
        $this->display();
    }

    public function downloadMemberDetails(){
        $data = I('get.');
        //设置默认值
        $id = empty($data['id']) ? '' : intval($data['id']);
        $name = empty($data['name']) ? '' : trim($data['name']);
        $start_time = empty($data['start_time']) ? '' : trim($data['start_time']);
        $end_time = empty($data['end_time']) ? '' : trim($data['end_time']);
        $city = empty($data['city']) ? '' : intval($data['city']);
        $department = empty($data['department']) ? '' : intval($data['department']);
        $tempResult = D('UserVip')->getUserVipDetailList($id, $name, $start_time, $end_time, $city, $department);
        $tempResult = $this->getUserVipDetailOtherInfo($tempResult);

        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");

        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );

        $phpExcel = new \PHPExcel();
        //设置表头
        $title = array(
            '部门',
            '会员ID',
            '会员名称',
            '城市',
            '系数',
            '倍数',
            '超出数',
            '本次合同开始时间',
            '本次合同结束时间',
            '总合同开始时间',
            '总合同结束时间',
            '上月分单差额',
            '月分单需求数',
            '进度分单值',
            '当前累计分单',
            '分单均衡值',
            '拓展师长',
            '品牌师长',
            '拓展团长',
            '品牌团长',
            '城市经理',
            '品牌师',
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        //设置表内容
        $j = 1;
        foreach ($tempResult as $key => $value) {
            //初始化$i
            $i = 0;

            //部门
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['department']);

            //会员ID
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['company_id']);

            //会员名称
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['company_name']);

            //城市
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['cname']);

            //系数
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['cityImportantCoefficient']);

            //倍数
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['viptype']);

            //超出数
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['viptype'] - 1));

            //本次合同开始时间
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['start_time']);

            //本次合同结束时间
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['end_time']);

            //总合同开始时间
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['total_start_time']);

            //总合同结束时间
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['total_end_time']);

            //上月分单差额
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastMonthOrderDiff']);

            //月分单需求数
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['salesOrderPoints']);

            //进度分单值
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['scheduleOrder']);

            //当前累计分单
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['vipThisMonthOrderCount']);

            //分单均衡值
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['orderBalance']);

            //拓展师长
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tzsz']);

            //品牌师长
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ppsz']);

            //拓展团长
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tztz']);

            //品牌团长
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['pptz']);

            //城市经理
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['tzzy']);

            //品牌师
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['ppzy']);


            $j++;
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="会员详情统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    /**
     * [getMemberDetailsList description]
     * @param  [type] $id         [装修公司ID]
     * @param  [type] $name       [装修公司名字]
     * @param  [type] $start_time [本次合同开始时间]
     * @param  [type] $end_time   [本次合同结束时间]
     * @param  [type] $city       [会员城市]
     * @param  [type] $department [部门]
     * @return [type]             [description]
     */
    private function getMemberDetailList($id, $name, $start_time, $end_time, $city, $each = '15'){
        import('Library.Org.Util.Page');
        $count = D('UserVip')->getUserVipDetailCount($id, $name, $start_time, $end_time, $city);
        $Page = new \Page($count,$each);
        $result['page'] = $Page->show();
        $result['list'] = D('UserVip')->getUserVipDetailList($id, $name, $start_time, $end_time, $city, $Page->firstRow, $Page->listRows);
        return $result;
    }

    /**
     * [getUserVipDetailOtherInfo 获取会员详情的其它信息]
     * @param  [type] $userVipDetail [会员详情]
     * @return [type]                [description]
     */
    private function getUserVipDetailOtherInfo($userVipDetail){
        $cityIds = array();
        $companyIds = array();
        foreach ($userVipDetail as $key => $value) {
            $cityIds[] = $value['cid'];
            $companyIds[] = $value['company_id'];
        }
        $cityIds = array_unique($cityIds);
        $companyIds = array_unique($companyIds);

        //获取城市重点系数
        $result = D('SaleSetting')->getCityImportantCoefficient($cityIds);
        foreach ($result as $key => $value) {
            $cityImportantCoefficient[$value['cid']] = $value['number'];
        }
        unset($result);

        //获取该城市当月的会员总数(包含掉的会员)
        $result = D('UserVip')->getCityVipCountByCityIds($cityIds);
        foreach ($result as $key => $value) {
            $cityVipCount[$value['cid']] = $value['number'];
        }
        unset($result);

        //获取该城市当月的所有会员(包含掉的会员)实际分单(一个订单可能分给多个装修公司，这里是多次计算的)累计数
        $result = D('UserVip')->getCityOrderCountByCityIds($cityIds);
        foreach ($result as $key => $value) {
            $cityOrderCount[$value['cid']] = $value['number'];
        }
        unset($result);

        //获取每个会员公司的月分单需求数
        $result = D('SalesOrderPoints')->getSalesOrderPointsByCompanyIds($companyIds);
        foreach ($result as $key => $value) {
            $salesOrderPoints[$value['userid']] = $value['point'];
        }
        unset($result);

        //获取每个会员公司的上月实际分单
        $endTime = strtotime(date('Y-m-01 00:00:00'));
        $startTime = strtotime(date('Y-m-01 00:00:00',($endTime - 10)));
        $result = D('OrderInfo')->getVipOrderCountByCompanyIds($companyIds,$startTime,$endTime);
        foreach ($result as $key => $value) {
            $vipLastMonthOrderCount[$value['company_id']] = $value['number'];
        }
        unset($result);

        //获取每个会员当月的累计实际分单总数
        $result = D('OrderInfo')->getVipOrderCountByCompanyIds($companyIds);
        foreach ($result as $key => $value) {
            $vipThisMonthOrderCount[$value['company_id']] = $value['number'];
        }
        unset($result);

        //遍历赋值
        foreach ($userVipDetail as $key => $value) {
            $userVipDetail[$key]['cityImportantCoefficient'] = $cityImportantCoefficient[$value['cid']];
            $userVipDetail[$key]['cityVipCount']             = $cityVipCount[$value['cid']];
            $userVipDetail[$key]['cityOrderCount']           = $cityOrderCount[$value['cid']];
            $userVipDetail[$key]['salesOrderPoints']         = $salesOrderPoints[$value['company_id']];
            $userVipDetail[$key]['vipLastMonthOrderCount']   = $vipLastMonthOrderCount[$value['company_id']];
            $userVipDetail[$key]['vipThisMonthOrderCount']   = $vipThisMonthOrderCount[$value['company_id']];
            //上月分单差额 = 上月实际分单-月分单需求数
            $userVipDetail[$key]['lastMonthOrderDiff'] = $vipLastMonthOrderCount[$value['company_id']] - $salesOrderPoints[$value['company_id']];
            //进度分单值 = 月分单需求数/本月天数*当前天数
            $userVipDetail[$key]['scheduleOrder'] = number_format($salesOrderPoints[$value['company_id']] / (date('t') * (date('m'))));
            //分单均衡值=该城市所有会员实际分单累计数/会员总数
            $userVipDetail[$key]['orderBalance'] = number_format($cityOrderCount[$value['cid']]/$cityVipCount[$value['cid']]);

            //获取职能管辖相关
            $roleInfos = $this->getCityRoles($value['cid']);
            $userVipDetail[$key]['department'] = $roleInfos['department'];
            $userVipDetail[$key]['tzsz'] = $roleInfos['tzsz'];
            $userVipDetail[$key]['tztz'] = $roleInfos['tztz'];
            $userVipDetail[$key]['tzzy'] = $roleInfos['tzzy'];
            $userVipDetail[$key]['ppsz'] = $roleInfos['ppsz'];
            $userVipDetail[$key]['pptz'] = $roleInfos['pptz'];
            $userVipDetail[$key]['ppzy'] = $roleInfos['ppzy'];
        }
        return $userVipDetail;
    }

    /**
     * [getCityRoles 获取城市管理者]
     * @return [type] [description]
     */
    public function getCityRoles($cityId = ''){
        if (empty($cityId)) {
            return false;
        }
        //$result = S('UserStatistics:CityRoles');
        if (empty($result)) {
            $map = array(
                'y.type' => 1
            );
            $temp = M('sales_setting_value')->alias('z')
                                            ->field('
                                                z.cid,
                                                y.info AS one,
                                                x.info AS two,
                                                CONCAT(IF(w.name is null,"",w.name),"-",IF(w.info is null,"",w.info)) AS three,
                                                v.name AS department
                                            ')
                                            ->join('qz_sales_category AS y ON y.id = z.pid')
                                            ->join('qz_sales_category AS x ON x.id = y.pid')
                                            ->join('qz_sales_category AS w ON w.id = x.pid')
                                            ->join('qz_sales_category AS v ON v.id = w.pid')
                                            ->where($map)
                                            ->select();
            $result = array();
            foreach ($temp as $key => $value) {
                $result[$value['cid']]['department'] = $value['department'];
                if ('商务' == $value['department']) {
                    if (strpos($value['three'], '品牌') === false) {
                        $result[$value['cid']]['tzsz'] = explode('-', $value['three'])[1];
                        $result[$value['cid']]['tztz'] = $value['two'];
                        $result[$value['cid']]['tzzy'] = $value['one'];
                    } else {
                        $result[$value['cid']]['ppsz'] = explode('-', $value['three'])[1];
                        $result[$value['cid']]['pptz'] = $value['two'];
                        $result[$value['cid']]['ppzy'] = $value['one'];
                    }
                } else {
                    $result[$value['cid']]['wxsz'] = explode('-', $value['three'])[1];
                    $result[$value['cid']]['wxtz'] = $value['two'];
                    $result[$value['cid']]['wxzy'] = $value['one'];
                }
            }
            S('UserStatistics:CityRoles',$result,600);
        }

        return $result[$cityId];
    }

    /**
     * [getRoleCitys 获取角色管辖的城市]
     * @param  string $roleId [description]
     * @return [type]         [description]
     */
    public function getRoleCitys($roleId = ''){

        if (empty($roleId)) {
            return [];
        }
        //$result = S('UserStatistics:RoleCitys');

        if (empty($result)) {
            $map = array(
                'y.type' => 1
            );
            $temp = M('sales_setting_value')->alias('z')
                                            ->field('
                                                z.cid,
                                                y.id AS one,
                                                x.id AS two,
                                                w.id AS three,
                                                v.id AS four
                                            ')
                                            ->join('qz_sales_category AS y ON y.id = z.pid')
                                            ->join('qz_sales_category AS x ON x.id = y.pid')
                                            ->join('qz_sales_category AS w ON w.id = x.pid')
                                            ->join('qz_sales_category AS v ON v.id = w.pid')
                                            ->where($map)
                                            ->select();
            $result = array();
            foreach ($temp as $key => $value) {
                $result[$value['one']][] = $value['cid'];
                $result[$value['two']][] = $value['cid'];
                $result[$value['three']][] = $value['cid'];
                $result[$value['four']][] = $value['cid'];
            }
            S('UserStatistics:RoleCitys',$result,600);
        }
        return $result[$roleId];
    }
}