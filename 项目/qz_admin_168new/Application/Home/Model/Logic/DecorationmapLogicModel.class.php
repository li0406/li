<?php

namespace Home\Model\Logic;

use Home\Model\Db\DecorationMapModel;
use Home\Model\Service\BaiduServiceModel;

class DecorationmapLogicModel
{
    protected $mapDb;
    protected $user;

    public function __construct($user = [])
    {
        $this->mapDb = new DecorationMapModel();
        $this->user = $user;
    }

    public function getDecorationMapInfo($id)
    {
        return $this->mapDb->getMapInfo($id);
    }

    public function getDecorationMapCount($input)
    {
        return $this->mapDb->getMapCount($input);
    }

    public function getDecorationMapList($input, $offset = 0, $limit = 10)
    {
        return $this->mapDb->getMapList($input, $offset, $limit);
    }

    public function delDecorationMap($id)
    {
        if (empty($id)) {
            return false;
        }
        return $this->mapDb->delDecorationMap($id);
    }

    public function updateDecorationMapInfo($data)
    {
        if (empty($data['id'])) {
            return ['error_code' => 400, 'error_mas' => '缺少数据 , 请刷新后再试!'];
        }
        if (empty($data['company_name'])) {
            return ['error_code' => 400, 'error_mas' => '请填写装修公司'];
        }
        if (empty($data['address'])) {
            return ['error_code' => 400, 'error_mas' => '请填写地址'];
        }
        if (empty($data['city'])) {
            return ['error_code' => 400, 'error_mas' => '请选择所属城市'];
        }
        $coordinate = [];
        if (!empty($data['coordinate'])) {
            $coordinate = explode(',', str_replace('，',',',$data['coordinate']));
        }
        $save = [
            'company_name' => $data['company_name'],
            'address' => $data['address'],
            'city' => $data['city'],
            'haoping_lv' => !empty($data['haoping_lv']) ? round($data['haoping_lv'] / 100, 2) : 0,
            'case_num' => !empty($data['case_num']) ? $data['case_num'] : 0,
            'designer_num' => !empty($data['designer_num']) ? $data['designer_num'] : 0,
            'site_num' => !empty($data['site_num']) ? $data['site_num'] : 0,
            'logo' => !empty($data['logo']) ? $this->changeUploadLogo($data['logo']) : '',
            'intro' => !empty($data['intro']) ? $data['intro'] : '',
            'lng' => $coordinate[0],
            'lat' => $coordinate[1],
            'op_uid' => $this->user['id'],
            'updated_at' => time(),
        ];

        return $this->mapDb->editDecorationMap($data['id'], $save);
    }

    /**
     * 处理导入数据
     * @param int $type 类型: 1.真会员 2.假会员 3.三方公司
     * @param array $data 导入的数据
     * @param array $cityInfo 城市信息
     * @param array $companyInfo 装修公司信息(仅非真会员有数据)
     * @return array
     */
    public function disposeData($type, $data, $cityInfo, $companyInfo = [])
    {
        if (count($data) == 0) {
            return [];
        }

        $repetitionData = []; //重复数据
        $successData = []; //成功数据
        $failedData = []; //失败数据
        $saveData = []; //新增数据
        //获取已存在数据
        $mapData = $this->mapDb->getDecorationMapByCompanyName(array_column($data, 'company_name'));
        $mapData = array_column($mapData, null, 'company_name');
        foreach ($data as $k => $v) {
            //过滤重复数据
            if (isset($mapData[$v['company_name']]) && isset($cityInfo[$v['city']]) && $cityInfo[$v['city']]['cid'] == $mapData[$v['company_name']]['city']) {
                unset($data[$k]);
                $repetitionData[] = ++$k;
                continue;
            }
            //过滤真会员
            if (!isset($companyInfo[$v['company_id']]) && $type != 3) {
                unset($data[$k]);
                $failedData[] = ++$k;
            }
        }
        //更新操作
        if (count($data) > 0) {
            $baiduServer = new BaiduServiceModel();
            //获取百度坐标
            $coordinatesData = $baiduServer->getCoordinatesByAddress($data);
            foreach ($data as $k => $v) {
                //有百度返回数据和城市数据 才做保存
                if (isset($coordinatesData[$k]) && $coordinatesData[$k]['status'] == 0 && isset($cityInfo[$v['city']])) {
                    $saveData[] = [
                        'type' => $type,
                        'company_name' => $v['company_name'],
                        'company_id' => $v['company_id'] ?: 0,
                        'city' => $cityInfo[$v['city']]['cid'],
                        'address' => $v['address'],
                        'lng' => $coordinatesData[$k]['result']['location']['lng'],
                        'lat' => $coordinatesData[$k]['result']['location']['lat'],
                        'haoping_lv' => !empty($companyInfo[$v['company_id']]['ping']) && !empty($companyInfo[$v['company_id']]['haoping']) ? round($companyInfo[$v['company_id']]['haoping'] / $companyInfo[$v['company_id']]['ping'], 2) : 0,
                        'case_num' => !empty($companyInfo[$v['company_id']]['casesnum']) ? $companyInfo[$v['company_id']]['casesnum'] : 0,
                        'designer_num' => !empty($companyInfo[$v['company_id']]['designer_num']) ? $companyInfo[$v['company_id']]['designer_num'] : 0,
                        'site_num' => !empty($companyInfo[$v['company_id']]['site_num']) ? $companyInfo[$v['company_id']]['site_num'] : 0,
                        'intro' => !empty($companyInfo[$v['company_id']]['jianjie']) ? $companyInfo[$v['company_id']]['jianjie'] : '',
                        'logo' => !empty($companyInfo[$v['company_id']]['logo']) ? changeImgUrl2($companyInfo[$v['company_id']]['logo']) : 'https://' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png',
                        'op_uid' => $this->user['id'],
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    $successData[] = ++$k;
                } else {
                    $failedData[] = ++$k;
                }
            }
            $status = $this->mapDb->addDecorationMap($saveData);
            if (!$status) {
                $successData = [];
                $failedData = array_merge($successData, $failedData);
            }
        }
        return ['success' => $successData, 'false' => $failedData, 'repetition' => $repetitionData];
    }

    /**
     * 处理导入地图数据
     * @param $ex
     * @return array
     */
    public function importFile($ex)
    {
        if (0 == $ex['size']) {
            return ['error_code' => 400, 'error_msg' => '失败，请选择要上传的excel文件！'];
        }

        //处理文件
        $filename = TEMP_PATH . time() . substr($ex['name'], stripos($ex['name'], '.'));
        move_uploaded_file($ex['tmp_name'], $filename);
        $excelData = import_excel($filename);
        unlink($filename);
        if (count($excelData) == 1) {
            return ['error_code' => 400, 'error_msg' => '数据格式错误'];
        }
        unset($excelData[0]); //删除第一行标题
        //处理数据
        $fakeData = []; //非真会员
        $tripartiteData = []; //三方数据
        $checkData = []; //验证数据
        $repetitionData = []; //重复数据
        $returnData = [];
        foreach ($excelData as $k => $v) {
            //获取数据并验证数据
            if (!empty($v[0]) || !empty($v[1]) || !empty($v[2])) {
                if (empty($v[0]) || empty($v[1]) || empty($v[2])) {
                    return ['error_code' => 400, 'error_msg' => '数据格式错误'];
                }
                $data = [
                    'company_name' => $v[0],
                    'city' => $v[1],
                    'address' => $v[2],
                    'company_id' => $v[3] ?: 0,
                ];
                $returnData[$k] = $data;
                //验证导入名称重复
                if (isset($checkData[$data['company_name']]) && $checkData[$data['company_name']]['city'] == $data['city']) {
                    $repetitionData[] = ++$k;
                } else {
                    $checkData[$data['company_name']] = $data;
                }
            }
        }
        if (count($repetitionData) > 0) {
            $msg = '';
            foreach ($repetitionData as $v) {
                $msg .= $v . ' ';
                return ['error_code' => 400, 'error_msg' => '重复数据！（行号: ' . $msg . '）'];
            }
        }

        //验证每次最多导入50条
        if (count($returnData) > 50) {
            return ['error_code' => 400, 'error_msg' => '每次最多导入50条数据！'];
        }

        //区分三方/非真会员
        foreach ($returnData as $k => $v) {
            if (!empty($v['company_id'])) {
                $fakeData[$k] = $v;
            } else {
                $tripartiteData[$k] = $v;
            }
        }
        $city = array_column($returnData, 'city');
        unset($returnData);
        return ['error_code' => 0, 'data' => ['fake' => $fakeData, 'tripartite' => $tripartiteData, 'city' => $city]];
//        //用于处理相同文件的判断
//        $excelData = array_filter($excelData);
//        $excelDataHash = md5(json_encode($excelData)); //计算本次上传的数据hash
//
//        //处理同一个文件重复上传
//        $cache_key = 'Cache:168new:Home:DecorationMap:import' . $user['id'];
//        $lastexcelDataHash = S($cache_key);
//        if ($lastexcelDataHash == $excelDataHash) {
//            //本次上传的excel data数据hash和上一次相同，就返回失败
//            $redata['code'] = 2;
//            $redata['data'] = [];
//            $redata['msg'] = '失败，同一个excel文件重复上传啊亲！';
//            return $redata;
//        }
//        S($cache_key, $excelDataHash, 60 * 60 * 8);
    }

    public function changeUploadLogo($url)
    {
        $preg = "/^http(s)?:\\/\\/.+/";
        if (preg_match($preg, $url)) {
            return $url;
        } else {
            return 'https://' . C('QINIU_DOMAIN') . '/' . $url;
        }
    }
}
