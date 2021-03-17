<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 统一接口返回格式
if(!function_exists('sys_response')) {
    function sys_response($error_code = 0, $error_msg = '', $data = null){
        // 如果$error_msg为空则自动获取
        if (empty($error_msg)) {
            $key = 'CODE_' . $error_code;
            $reflectionClass = new ReflectionClass('\app\common\enum\BaseStatusCodeEnum');
            if ($reflectionClass->hasConstant($key)) {
                $error_msg = $reflectionClass->getConstant($key);
            }
        }

        $response = [
            'error_code' => $error_code,
            'error_msg' => $error_msg,
            'data' => $data
        ];

        if ($data === false) {
            unset($response['data']);
        }
        
        return $response;
    }
}

// 分页参数格式化
if (!function_exists('sys_page_format')){
    function sys_page_format($page, $default = 1){
        return intval($page) > 0 ? intval($page) : $default;
    }
}

// 计算分页偏移量
if (!function_exists('sys_page_offset')){
    function sys_page_offset($page = 1, $limit = 10){
        $page = sys_page_format($page, 1);
        $limit = sys_page_format($limit, 10);
        return ($page - 1) * $limit;
    }
}


if (!function_exists('curl')){
    /**
     * curl请求函数封装
     * @param $curllink
     * @param null $data
     * @param int $type
     * @return mixed
     */
    function curl($curllink, $data = [], $headers = null)
    {
        //初始化一个curl对象
        $ch = curl_init();
        //设置需要抓取的url
        curl_setopt($ch, CURLOPT_URL, $curllink);
        //跳过SSL证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //连接超时15秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        //超时设置60秒
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if (is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定是否显示头信息
        $output = curl_exec($ch);//执行
        curl_close($ch);//关闭
        //判断能否解析成json
        return isJson($output) !== false ? isJson($output,true) : $output;
    }
}


if (!function_exists('isJson')) {
    /**
     * 判断字符串是否为Json格式
     * @param  string $data Json 字符串
     * @param  bool $assoc 是否返回关联数组。默认返回对象
     * @return bool|array 成功返回转换后的对象或数组，失败返回 false
     */
    function isJson($jsonString = '', $assoc = true)
    {
        $data = json_decode($jsonString, $assoc);
        if (is_array($data)) {
            return $data;
        }
        return false;
    }
}

/**
 * 图片地址拼接判断 如果有完整地址了则不拼接
 */
if(!function_exists('changeImgUrl')) {
    function changeImgUrl($url, $type = 1)
    {
        if(empty($url)){
            return '';
        }

        $preg = "/^http(s)?:\/\/.+/";
        if (preg_match($preg, $url)) {
            return $url;
        }

        if (strpos($url, "//") === 0) {
            return "https:". $url;
        }

        if($type == 1){
            return config('app.QINIU.DOMAIN') . '/' .$url;      // zxs七牛 staticqn.qizuang.com
        }

        return $url;
    }
}

// 二维数组排序
if(!function_exists('sys_array_multisort')) {
    function sys_array_multisort($list, $field, $rule = SORT_ASC) {
        $fieldList = array_column($list, $field);
        array_multisort($fieldList, $rule, $list);
        return $list;
    }
}

// 检查变量
if(!function_exists('sys_check_variable')) {
    function sys_check_variable(&$variable, $default = "") {
        if (!isset($variable) || empty($variable)) {
            return $default;
        }

        return $variable;
    }
}

// 除法
if(!function_exists('sys_devision')) {
    function sys_devision($numerator, $denominator, $decimal = 2) {
        $ret = 0;
        if ($numerator != 0 && $denominator != 0) {
            $ret = round($numerator / $denominator, $decimal);
        }

        return $ret;
    }
}

// 除法（分子大于0分母为0时值位1）
if(!function_exists('sys_devision_ratio')) {
    function sys_devision_ratio($numerator, $denominator, $decimal = 2) {
        $ret = 0;
        if ($numerator != 0 && $denominator != 0) {
            $ret = round($numerator / $denominator, $decimal);
        } else if ($numerator > 0 && $denominator == 0) {
            $ret = 1;
        }

        return $ret;
    }
}

// 数组求合
if(!function_exists('sys_array_sum')) {
    function sys_array_sum($list, $field, $decimal = 0) {
        $fieldList = array_column($list, $field);
        $result = round(array_sum($fieldList), $decimal);
        return $result;
    }
}

/**
 * 同比，环比转化
 * @param $now
 * @param $month
 * @param $year
 */
if(!function_exists('conversion_data')) {
    function conversion_data($now = 0, $month = 0, $year = 0)
    {
        //环比
        $month_percent = 0;
        $month_symbol = '';
        //同比
        $year_percent = 0;
        $year_symbol = '';

        if ($now > 0) {
            //环比
            if ($month != 0) {
                //（当月总量-上月总量）/上月总量*100%
                $month_percent = abs(round(($now - $month) / $month,4) * 100 );
            } else {
                $month_percent = 100;
            }

            if ($now > $month) {
                $month_symbol = 'plus';
            } else if($now < $month) {
                $month_symbol = 'reduce';
            }

            //同比
            if ($year != 0) {
                //当月总量-去年同月总量）/去年同月总量*100%
                $year_percent = abs(round(($now - $year) /$year,4) * 100);
            } else {
                $year_percent = 100;
            }

            if ($now > $year) {
                $year_symbol = 'plus';
            } else if($now < $year) {
                $year_symbol = 'reduce';
            }
        } else {
            if ($year != 0 ) {
                $month_percent = 100;
                $month_symbol = 'reduce';
            }

            if ($year != 0) {
                $year_percent = 100;
                $year_symbol = 'reduce';
            }
        }

        $data = [
            "count" => $now,
            "month_percent" => [
                "percent" => $month_percent,
                "symbol" => $month_symbol,
            ],
            "year_percent" => [
                "percent" => $year_percent,
                "symbol" => $year_symbol,
            ],
        ];

        return $data;
    }
}


// 读取Excel文件
if(!function_exists('sys_read_excel')) {
    function sys_read_excel($filename, $sheet = 0, $column = 0, $row_begin = 1, &$options = []){
        $filename = iconv("utf-8", "gb2312", $filename);
        if (empty($filename) OR !file_exists($filename)) {
            throw new \think\Exception("请选择导入文件");
        }

        $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filename);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType);

        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $currSheet = $spreadsheet->getSheet($sheet);

        $rowMax = $currSheet->getHighestRow();
        $columnH = $currSheet->getHighestColumn();
        $columnMax = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnH);

        if ($column > 0 && $columnMax != $column) {
            throw new \think\Exception("导入文件与模板格式不一致");
        }

        $data = [];
        for ($row_idx = $row_begin; $row_idx <= $rowMax; $row_idx++) {
            $isNull = true;
            for ($col_idx = 1; $col_idx <= $columnMax; $col_idx++) {
                $cellName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx);
                $cellId = $cellName . $row_idx;

                $cell = $currSheet->getCell($cellId);

                if (isset($options['format'])) {
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode(); // 获取格式
                    $options['format'][$row_idx][$cellName] = $format; // 记录格式
                }

                if (isset($options['formula'])) {
                    $formula = $currSheet->getCell($cellId)->getValue(); // 获取公式，公式均为=号开头数据
                    if (0 === strpos($formula, '=')) {
                        $options['formula'][$cellId] = $formula;
                    }
                }

                if (isset($format) && 'm/d/yyyy' == $format) {
                    $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd'); // 日期格式翻转处理
                }

                $data[$row_idx][$cellName] = trim($cell->getFormattedValue());
                if (!empty($data[$row_idx][$cellName])) {
                    $isNull = false;
                }
            }

            // 判断是否整行数据为空，是的话删除该行数据
            if ($isNull == true) {
                unset($data[$row_idx]);
            }
        }

        return $data;
    }
}

