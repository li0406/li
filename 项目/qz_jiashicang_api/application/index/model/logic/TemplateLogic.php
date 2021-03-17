<?php
/**
 * @des:上传城市数据业务处理层
 * @author:tengyu
 * @date:2020-11-13
 */

namespace app\index\model\logic;


use app\common\enum\BaseStatusCodeEnum;
use app\index\model\adb\CityExceptUser;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\index\model\db\CityExceptUser as CityExceptUserDb;
use think\Exception;
use think\Controller;

class TemplateLogic
{


    /**
     *  @des:获取上传的城市预计会员数,读取上传数据文件
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function uploadCityData()
    {
        try {
            $tmp = $_FILES['file']['tmp_name'];
            $sheetData = $this->readerTemplate($tmp);
            $formatSheet = current($sheetData);
            if ($formatSheet['A'] != '城市' || $formatSheet['B'] != '期望会员数') {
                throw new Exception('预期会员数模板头部与模板不一致!', 4000002);
            }

            //获取所有开通的城市名
            $cityLogic = new CityLogic();
            $cityData = $cityLogic->getAllOpenCity();
            $cityCombine = array_column($cityData, 'city_id', 'city_name');
            unset($cityData);

            //获取本月存在的预期城市会员数
            $dbCityExcept = new CityExceptUserDb();
            $start_time = strtotime(date('Y-m-01'));
            $end_time = strtotime(date('Y-m-01',strtotime('+1 months'))) -1;
            $existCityInfo = $dbCityExcept->getCityExceptUserInfoNew($start_time,$end_time);
            $existCityInfoCombine = array_column($existCityInfo, null, 'cname');

            $insert = $update = array();
            foreach ($sheetData as $k => $v) {
                if (!isset($cityCombine[$v['A']])) { //不存在的或未开通的城市跳过
                    continue;
                }
                if (!isset($existCityInfoCombine[$v['A']]) && !empty($v['B'])) {
                    $insert[] = [
                        'cname' => $v['A'],
                        'cs' => $cityCombine[$v['A']],
                        'except_user' => (float)$v['B'],
                        'datetime' => strtotime(date('Y-m-01')),
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                }
                //城市创建了，预期会员数不一致走更新，其他忽略
                if (isset($existCityInfoCombine[$v['A']]) && (float)$existCityInfoCombine[$v['A']]['except_user'] != (float)$v['B']) {
                    $update[] = [
                        'id' => $existCityInfoCombine[$v['A']],
                        'except_user' => (float)$v['B'],
                        'datetime' => strtotime(date('Y-m-01')),
                        'updated_at' => time(),
                    ];
                }
            }
            unset($cityCombine,$existCityInfo);
            //数据库插入更新操作
            if (!empty($insert)) {
                //执行创建城市期望数据
                $insertData = array_chunk($insert,1000);
                foreach ($insertData as $v) {
                    $dbCityExcept->batchInsertCityExcept($v);
                }
            }
            if (!empty($update)) {
                //执行更新城市期望数据
                $dbCityExcept->batchUpdateCityExcept($update);
            }
            $return = [
                'error_code' => 0,
                'error_msg' => '上传成功!',
                'data' => 'success'
            ];
        } catch (Exception $exception) {
            $return =[
                'error_code' => $exception->getCode() ?? 1000001,
                'error_msg' => '读取上传文件发生错误:'.$exception->getMessage(),
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }

    /**
     * @des:读取模板文件
     * @param $file
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function readerTemplate($file)
    {
        $inputFileType = IOFactory::identify($file);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        return $sheetData;
    }
}