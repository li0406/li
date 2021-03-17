<?php
// +----------------------------------------------------------------------
// | OrderBlackLogicModel
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

use Home\Model\Db\OrderBlackModel;

class OrderBlackLogicModel
{
    protected $orderBlackModel;

    public function __construct()
    {
        $this->orderBlackModel = new OrderBlackModel();
    }

    public function importData($data)
    {
        $telFind = $importData = [];
        $opId = session('uc_userinfo.id');
        $opTime = time();
        $fail_line = false;
        $phonelocationModel = new \Home\Model\Db\PhonelocationModel();
        $userModel = new \Home\Model\UserModel();
        foreach ($data as $k => $v) {
            $new_value = [];
            if (!empty($v[0]) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $v[0])) {
                $find = $this->orderBlackModel->getOne(['tel' => $v[0]], 'id');
                if (empty($find) && array_search($v[0], $telFind) === false) {
                    $new_value['tel'] = $v[0];
                    $new_value['tel_encrypt'] = order_tel_encrypt($v[0]);
                    $new_value['company_id'] = 0;
                    $new_value['opid'] = $opId;
                    $new_value['optime'] = $opTime;
                    $new_value['location_province'] = '';
                    $new_value['location_city'] = '';
                    $new_value['isp'] = '';
                    $phoneLocationInfo = $phonelocationModel->getOne(['phone' => substr($v[0], 0, 7)], 'p,c,isp');
                    if (!empty($phoneLocationInfo)) {
                        $new_value['location_province'] = $phoneLocationInfo['p'];
                        $new_value['location_city'] = $phoneLocationInfo['c'];
                        $new_value['isp'] = $phoneLocationInfo['isp'];
                    }
                    if (!empty($v[1])) {
                        $findCompanyId = $userModel->getCompanyByJc($v[1]);
                        if ($findCompanyId) {
                            $new_value['company_id'] = $findCompanyId['id'];
                        }
                    }
                    $importData[] = $new_value;
                    $telFind[] = $v[0];
                }
            } else {
                $fail_line = $k + 1;
                break;
            }
        }
        $importData = array_unique($importData,SORT_REGULAR);

        $flag = $this->orderBlackModel->saveAll($importData);
        return ['flag' => $flag, 'fail_line' => $fail_line];
    }

    /**
     * 获取列表
     *
     * @param $param
     * @param int $pageSize
     * @return array
     */
    public function getList($param, $pageSize = 20)
    {
        $map['a.status'] = 1;
        if (!empty($param['tel'])) {
            $map['a.tel'] = $param['tel'];
        }
        if (!empty($param['start']) && !empty($param['end'])) {
            $map['a.optime'] = ['between', [strtotime($param['start']), strtotime($param['end'].' 23:59:59')]];
        }
        if (!empty($param['cs'])) {
            $map['a.location_city'] = $param['cs'];
        }
        if (!empty($param['status'])) {
            if ($param['status'] == 1) {
                $map['u.fake'] = 0;
                $map['u.`on`'] = ['in', [2, -4]];
            } else {
                $map['u.`on`'] = ['in', [0, -1]];
            }
        }
        if (!empty($param['export'])) {
            $list = $this->orderBlackModel->getList($map, null, null);
            $list = $this->dealList($list);
            $this->exportOrderBlack($list);
        }

        $count = $this->orderBlackModel->getCount($map);
        $result = ['page' => '', 'list' => []];
        if (!empty($count)) {
            //分页
            import('Library.Org.Util.Page');
            $page = new \Page($count, $pageSize);
            $result['page'] = $page->show();
            $result['list'] = $this->orderBlackModel->getList($map, $page->firstRow, $page->listRows);
            $result['list'] = $this->dealList($result['list']);
        }
        return $result;
    }

    /**
     * 黑名单导出
     * @param  array $list
     * @return mixed
     */
    public function exportOrderBlack($list)
    {
        import('Library.Org.Phpexcel.PHPExcel', "", ".php");
        import('Library.Org.Phpexcel.PHPExcel.IOFactory', "", ".php");
        import('Library.Org.Phpexcel.PHPExcel.Style.Alignment', "", ".php");

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet(0);

        $activeSheet->getColumnDimension('A')->setWidth(40);
        $activeSheet->getColumnDimension('B')->setWidth(15);
        $activeSheet->getColumnDimension('C')->setWidth(15);
        $activeSheet->getColumnDimension('D')->setWidth(15);
        $activeSheet->getColumnDimension('E')->setWidth(15);
        $activeSheet->getColumnDimension('F')->setWidth(15);
        $activeSheet->getColumnDimension('G')->setWidth(15);
        $activeSheet->getColumnDimension('H')->setWidth(15);

        $VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
        $HORIZONTAL_LEFT = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $activeSheet->getStyle('A:H')->getAlignment()->setHorizontal($HORIZONTAL_CENTER)->setVertical($VERTICAL_CENTER);

        // 对齐方式
        $activeSheet->getStyle('A')->getAlignment()->setHorizontal($HORIZONTAL_LEFT);

        // 表头
        $activeSheet->setCellValue('A1', '序号')
            ->setCellValue('B1', '手机号码')
            ->setCellValue('C1', '手机归属地')
            ->setCellValue('D1', '所属装修公司')
            ->setCellValue('E1', '是否合作')
            ->setCellValue('F1', '封禁时间')
            ->setCellValue('G1', '操作人');

        $activeSheet->freezePaneByColumnAndRow(0, 2);
        $activeSheet->getRowDimension(1)->setRowHeight(20);

        // 表头加粗
        $activeSheet->getStyle('A1:G1')->applyFromArray(array(
            'font' => array(
                'name' => 'Microsoft Yahei',
                'bold' => true
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'argb' => 'E7E6E6E6'
                )
            )
        ));


        $indexXls = 2;
        foreach ($list as $key => $li) {
            $activeSheet->setCellValue('A' . $indexXls, $key + 1);
            $activeSheet->setCellValue('B' . $indexXls, $li['tel']);
            $activeSheet->setCellValue('C' . $indexXls, $li['location_city']);
            $activeSheet->setCellValue('D' . $indexXls, $li['jc'] ?: '--');
            $activeSheet->setCellValue('E' . $indexXls, !empty($li['company_id']) ? ((in_array($li['on'], [2, -4]) && $li['fake'] == 0) ? '是' : '否') : '');
            $activeSheet->setCellValue('F' . $indexXls, date('Y-m-d H:i:s', $li["optime"]));
            $activeSheet->setCellValue('G' . $indexXls, $li['opadmin']['name']);
            $activeSheet->getRowDimension($indexXls)->setRowHeight(20);
            $indexXls++;
        }

        ob_end_clean();//清除缓冲区,避免乱码

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="发单黑名单 - 导出记录.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 循环处理黑名单数据
     * @param $list
     * @return mixed
     */
    protected function dealList($list)
    {
        // 处理手机号码定位数据
        $phonelocationModel = new \Home\Model\Db\PhonelocationModel();
        foreach ($list as $k => $v) {
            if (empty($v['location_province']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $v['tel'])) {
                $phoneLocationInfo = $phonelocationModel->getOne(['phone' => substr($v['tel'], 0, 7)], 'p,c,isp');
                if (!empty($phoneLocationInfo)) {
                    $list[$k]['location_province'] = $phoneLocationInfo['p'];
                    $list[$k]['location_city'] = $phoneLocationInfo['c'];
                    $list[$k]['isp'] = $phoneLocationInfo['isp'];
                    $new_value = [
                        'location_province' => $phoneLocationInfo['p'],
                        'location_city' => $phoneLocationInfo['c'],
                        'isp' => $phoneLocationInfo['isp'],
                    ];
                    $this->orderBlackModel->saveOne($new_value, ['id' => $v['id']]);
                }
            }
        }

        // 获取对应操作人员信息
        $op_ids = array_unique(array_filter(array_column($list, 'opid')));
        if (!empty($op_ids)) {
            $adminModel = new \Home\Model\Db\AdminuserModel();
            $companys = $adminModel->getAdminUserInfoByIds($op_ids, 'id,name');
            foreach ($list as $k => $v) {
                $list[$k]['opadmin'] = [];
                foreach ($companys as $k2 => $v2) {
                    if ($v['opid'] == $v2['id']) {
                        $list[$k]['opadmin'] = $v2;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 保存黑名单数据
     *
     * @param $post
     * @return bool|mixed
     */
    public function saveBlack($post)
    {
        $data = [];
        if (!empty($post['tel'])) {
            $data['tel'] = $post['tel'];
            $data['tel_encrypt'] = order_tel_encrypt($post['tel']);
            $phonelocationModel = new \Home\Model\Db\PhonelocationModel();
            $phoneLocationInfo = $phonelocationModel->getOne(['phone' => substr($data['tel'], 0, 7)], 'p,c,isp');
            if (!empty($phoneLocationInfo)) {
                $data['location_province'] = $phoneLocationInfo['p'];
                $data['location_city'] = $phoneLocationInfo['c'];
                $data['isp'] = $phoneLocationInfo['isp'];
            }
        }
        if (!empty($post['jc'])) {
            $userModel = new \Home\Model\UserModel();
            $findCompanyId = $userModel->getCompanyByJc($post['jc']);
            if ($findCompanyId) {
                $data['company_id'] = $findCompanyId['id'];
            }
        }

        $data['opid'] = session('uc_userinfo.id');
        $data['optime'] = time();

        return $this->orderBlackModel->saveOne($data);
    }

    public function delOneBlack($id)
    {
        return $this->orderBlackModel->delOne(['id' => $id]);
    }

    public function getOneBlack($tel)
    {
        return $this->orderBlackModel->getOne(['tel' => $tel]);
    }

    public function synchronizeData()
    {
        $opId = session('uc_userinfo.id');
        $opTime = time();
        $phonelocationModel = new \Home\Model\Db\PhonelocationModel();
        $userModel = new \Home\Model\UserModel();
        $importNumber = 0;
        $telFind = [];
        //批量循环处理，每次处理1000条记录，处理3000次
        for ($x = 1; $x <= 3000; $x++) {
            $subTelList = $telList = $importData = $findTelNow = [];
            $list = $userModel->getCompanyListByPage($x, 1000);
            //如果数据为空了 跳出当前for循环
            if (empty($list)) {
                break;
            }
            foreach ($list as $key => $val) {
                if (!empty($val['tel_safe']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val['tel_safe'])) {
                    if ($val['tel_safe_chk'] == 1 && array_search($val['tel_safe'], $telFind) === false) {
                        $telList[] = [
                            'id' => $val['id'],
                            'tel' => $val['tel_safe'],
                        ];
                        $findTelNow[] = $telFind[] = $val['tel_safe'];
                        $subTelList[] = substr($val['tel_safe'],0, 7);
                    }
                }
                if (!empty($val['tel']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val['tel'])) {
                    if (array_search($val['tel'], $telFind) === false) {
                        $telList[] = [
                            'id' => $val['id'],
                            'tel' => $val['tel'],
                        ];
                        $findTelNow[] = $telFind[] = $val['tel'];
                        $subTelList[] = substr($val['tel'],0, 7);
                    }
                }
                if (!empty($val['jd_tel_1']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val['jd_tel_1'])) {
                    if (array_search($val['jd_tel_1'], $telFind) === false) {
                        $telList[] = [
                            'id' => $val['id'],
                            'tel' => $val['jd_tel_1'],
                        ];
                        $findTelNow[] = $telFind[] = $val['jd_tel_1'];
                        $subTelList[] = substr($val['jd_tel_1'],0, 7);
                    }
                }
                if (!empty($val['jd_tel_2']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val['jd_tel_2'])) {
                    if (array_search($val['jd_tel_2'], $telFind) === false) {
                        $telList[] = [
                            'id' => $val['id'],
                            'tel' => $val['jd_tel_2'],
                        ];
                        $findTelNow[] = $telFind[] = $val['jd_tel_2'];
                        $subTelList[] = substr($val['jd_tel_2'],0, 7);
                    }
                }
                if (!empty($val['jd_tel_3']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val['jd_tel_3'])) {
                    if (array_search($val['jd_tel_3'], $telFind) === false) {
                        $telList[] = [
                            'id' => $val['id'],
                            'tel' => $val['jd_tel_3'],
                        ];
                        $findTelNow[] = $telFind[] = $val['jd_tel_3'];
                        $subTelList[] = substr($val['jd_tel_3'],0, 7);
                    }
                }
                if (!empty($val['jd_tel_4']) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val['jd_tel_4'])) {
                    if (array_search($val['jd_tel_4'], $telFind) === false) {
                        $telList[] = [
                            'id' => $val['id'],
                            'tel' => $val['jd_tel_4'],
                        ];
                        $findTelNow[] = $telFind[] = $val['jd_tel_4'];
                        $subTelList[] = substr($val['jd_tel_4'],0, 7);
                    }
                }
            }
            $telList = array_unique($telList,SORT_REGULAR);
            $subTelList = array_unique($subTelList);
            if (empty($subTelList)) {
                $subTelList = '';
            }
            $telLocation = $phonelocationModel->getList(['phone' => ['in', $subTelList]], null,null,'phone,p,c,isp');
            $telLocation = array_column($telLocation, NULL, 'phone');

            if (empty($findTelNow)) {
                $findTelNow = '';
            }
            $isBlacks = $this->orderBlackModel->getOnlySelfList(['tel' => ['in', $findTelNow]], null, null, 'id,tel');
            $isBlacks = array_column($isBlacks, NULL, 'tel');
            foreach ($telList as $key => $val) {
                $new_value = [];
                if (empty($isBlacks[$val['tel']])) {
                    $new_value['tel'] = $val['tel'];
                    $new_value['tel_encrypt'] = order_tel_encrypt($val['tel']);
                    $new_value['company_id'] = $val['id'];
                    $new_value['opid'] = $opId;
                    $new_value['optime'] = $opTime;
                    $new_value['location_province'] = '';
                    $new_value['location_city'] = '';
                    $new_value['isp'] = '';
                    $subTel = substr($val['tel'],0, 7);
                    if (!empty($telLocation[$subTel])) {
                        $new_value['location_province'] = $telLocation[$subTel]['p'];
                        $new_value['location_city'] = $telLocation[$subTel]['c'];
                        $new_value['isp'] = $telLocation[$subTel]['isp'];
                    }
                    $importData[] = $new_value;
                    $importNumber++;
                }
            }
            unset($telList,$isBlacks,$telLocation,$list);
            $importData = array_unique($importData,SORT_REGULAR);
            // 写入黑名单表
            $this->orderBlackModel->saveAll($importData);
            sleep(1);
        }


        // 查询
        $telCountList = M('company_consult')->field('tel')->group('tel')->select();
        $telCountInStr = $telCountList = array_unique(array_filter(array_column($telCountList,'tel')));
        if (empty($telCountInStr)) {
            $telCountInStr = '';
        }
        $isBlackTwo = $this->orderBlackModel->getOnlySelfList(['tel' => ['in', $telCountInStr]], null, null, 'id,tel');
        $isBlackTwo = array_column($isBlackTwo, NULL, 'tel');
        $listData = [];
        foreach ($telCountList as $key => $val) {
            $new_value = [];
            if (!empty($val) && preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $val)) {
                if (empty($isBlackTwo[$val])) {
                    $new_value['tel'] = $val;
                    $new_value['tel_encrypt'] = order_tel_encrypt($val);
                    $new_value['company_id'] = 0;
                    $new_value['opid'] = $opId;
                    $new_value['optime'] = $opTime;
                    $new_value['location_province'] = '';
                    $new_value['location_city'] = '';
                    $new_value['isp'] = '';
                    $phoneLocationInfo = $phonelocationModel->getOne(['phone' => substr($val, 0, 7)], 'p,c,isp');
                    if (!empty($phoneLocationInfo)) {
                        $new_value['location_province'] = $phoneLocationInfo['p'];
                        $new_value['location_city'] = $phoneLocationInfo['c'];
                        $new_value['isp'] = $phoneLocationInfo['isp'];
                    }
                    $listData[] = $new_value;
                    $importNumber++;
                }
            }
        }
        unset($telCountList);
        echo '共导入'.$importNumber.'条记录<br>';
        $this->orderBlackModel->saveAll($listData);
    }
}