<?php
// +----------------------------------------------------------------------
// | QuoteLogic  报备报价
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\SaleReportCityQuote;
use app\common\model\db\SaleReportQuote;
use app\common\model\db\SaleReportQuoteLog;
use app\index\enum\QuoteCodeEnum;
use fengqi\Hanzi\Hanzi;
use think\facade\Log;

class QuoteLogic
{
    public function __construct()
    {
        $this->cityQuoteModel = new SaleReportCityQuote();
        $this->quoteModel = new SaleReportQuote();
        $this->quoteLogModel = new SaleReportQuoteLog();
    }

    /**
     * 修改报备记录状态
     *
     * @param $post
     * @return array
     */
    public function city_quote_list($param, $page, $page_size)
    {
        $map = ['is_delete' => 1];
        if (!empty($param['cname'])) {
            $map['city_name'] = $param['cname'];
        }
        if (!empty($param['parent_cname'])) {
            $map['parent_city_name'] = $param['parent_cname'];
        }
        if (!empty($param['level'])) {
            $map['little'] = $param['level'] - 1;
        }
        $count = $this->cityQuoteModel->getCount($map);
        $list = [];
        if (!empty($count)) {
            $list = $this->cityQuoteModel->getList($map, $page, $page_size, '*', 'parent_city_px,parent_city_name,city_px,city_name');
            $list = $list->toArray();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = empty($v['create_time']) ? '' : date('Y/m/d H:i', $v['create_time']);
                $list[$k]['update_time'] = empty($v['update_time']) ? '' : date('Y/m/d H:i', $v['update_time']);
            }
        }
        return sys_response(0, '', [
            'list' => $list,
            'page' => sys_paginate($count, $page_size, $page),
        ]);
    }

    /**
     * 修改报备记录状态
     *
     * @param $post
     * @return array
     */
    public function swjerp_quote_list()
    {
        $map = [];
        $count = $this->quoteModel->getCount($map);
        $list = [];
        if (!empty($count)) {
            $list = $this->quoteModel->getList($map,null,null,'*','cooperation_name asc,quote_type asc,create_time desc');
            $list = $list->toArray();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = empty($v['create_time']) ? '' : date('Y/m/d H:i', $v['create_time']);
                $list[$k]['update_time'] = empty($v['update_time']) ? '' : date('Y/m/d H:i', $v['update_time']);
            }
        }

        return sys_response(0, '', [
            'list' => $list,
        ]);
    }

    /**
     * 保存城市报价
     *
     * @param $post
     * @param $user
     * @return array
     */
    public function save_city_quote($post, $user)
    {
        $map = [];
        if (!empty($post['id'])) {
            $map['id'] = $post['id'];
            $this->cityQuoteModel->id = $post['id'];
            $detail = $this->cityQuoteModel->getOne($map);
        }
        unset($post['id']);
        $cityPinYin = Hanzi::pinyin($post['city_name']);
        $parentCityPinYin = Hanzi::pinyin($post['parent_city_name']);
        $post['city_px'] = strtoupper(substr($cityPinYin['py'],0,1));
        $post['parent_city_px'] = strtoupper(substr($parentCityPinYin['py'],0,1));

        $this->cityQuoteModel->startTrans();
        try {
            $flag = $this->cityQuoteModel->saveOne($post, $map);
            if (!empty($detail)) {
                $detail = $detail->toArray();
                unset($detail['id'], $detail['update_time'], $detail['create_time']);
                if ($detail != $post) {
                    // 添加操作日志
                    $this->quoteLogModel->addOne([
                        'quote_id' => $this->cityQuoteModel->id,
                        'op_admin_id' => $user['user_id'],
                        'op_admin_name' => $user['user_name'],
                        'action' => '修改',
                        'quote_type' => 1,
                        'content' => array_merge($post, ['id' => $this->cityQuoteModel->id]),
                    ]);
                }
            } else {
                // 添加操作日志
                $this->quoteLogModel->addOne([
                    'quote_id' => $this->cityQuoteModel->id,
                    'op_admin_id' => $user['user_id'],
                    'op_admin_name' => $user['user_name'],
                    'action' => '新增',
                    'quote_type' => 1,
                    'content' => array_merge($post, ['id' => $this->cityQuoteModel->id]),
                ]);
            }
            $this->cityQuoteModel->commit();
            if ($flag !== false) {
                return sys_response(0, '', []);
            } else {
                return sys_response(5000002, '', []);
            }
        } catch (\Exception $e) {
            $this->cityQuoteModel->rollback();
            return sys_response(5000002, '', []);
        }
    }

    /**
     * 保存三维家/erp报价
     *
     * @param $post
     * @param $user
     * @return array
     */
    public function save_swjerp_quote($post, $user)
    {
        $map = [];
        if (!empty($post['id'])) {
            $map['id'] = $post['id'];
            $this->quoteModel->id = $post['id'];
            $detail = $this->quoteModel->getOne($map);
        }
        unset($post['id']);
        $this->quoteModel->startTrans();
        try {
            $flag = $this->quoteModel->saveOne($post, $map);
            if (!empty($detail)) {
                $detail = $detail->toArray();
                unset($detail['id'], $detail['update_time'], $detail['create_time']);
                if ($detail != $post) {
                    // 添加操作日志
                    $this->quoteLogModel->addOne([
                        'quote_id' => $this->quoteModel->id,
                        'op_admin_id' => $user['user_id'],
                        'op_admin_name' => $user['user_name'],
                        'action' => '修改',
                        'quote_type' => 2,
                        'content' => array_merge($post, ['id' => $this->quoteModel->id]),
                    ]);
                }
            } else {
                // 添加操作日志
                $this->quoteLogModel->addOne([
                    'quote_id' => $this->quoteModel->id,
                    'op_admin_id' => $user['user_id'],
                    'op_admin_name' => $user['user_name'],
                    'action' => '新增',
                    'quote_type' => 2,
                    'content' => array_merge($post, ['id' => $this->quoteModel->id]),
                ]);
            }

            $this->quoteModel->commit();
            if ($flag !== false) {
                return sys_response(0, '', []);
            } else {
                return sys_response(5000002, '', []);
            }
        } catch (\Exception $e) {
            $this->quoteModel->rollback();
            return sys_response(5000002, '', []);
        }
    }

    /**
     * 获取城市报价详情
     * @param $id 城市id/城市名称
     * @param bool $cll 是否其他地方调用
     * @return array|\PDOStatement|string|\think\Model|null
     */
    public function get_city_quote($id,$cll = false)
    {
        if (is_int($id)) {
            $map['id'] = $id;
        } else {
            $map['city_name'] = $id;
        }
        $detail = $this->cityQuoteModel->getOne($map);

        if (is_object($detail)) {
            $detail = $detail->toArray();
            $detail['create_at'] = $detail['create_time'];
            $detail['update_at'] = $detail['update_time'];
            $detail['round_order_money'] = floatval($detail['round_order_money']);
            $detail['part_reform_price'] = floatval($detail['part_reform_price']);
            
            $detail['create_time'] = empty($detail['create_time']) ? '' : date('Y/m/d H:i:s', $detail['create_time']);
            $detail['update_time'] = empty($detail['update_time']) ? '' : date('Y/m/d H:i:s', $detail['update_time']);

            // 季度报价
            $detail["quarter_quote_last"] = "";
            $detail["quarter_quote_trend"] = "";

            // 半年报价
            $detail["half_year_quote_last"] = "";
            $detail["half_year_quote_trend"] = "";

            // 年度报价
            $detail["year_quote_last"] = "";
            $detail["year_quote_trend"] = "";

            // 独家报价
            $detail["exclusive_price_last"] = "";
            $detail["exclusive_price_trend"] = "";

            // 包干报价
            $detail["consist_price_last"] = "";
            $detail["consist_price_trend"] = "";

            $lastInfo = $this->cityQuoteModel->getLastOne($detail["id"], $detail["city_name"]);
            if (!empty($lastInfo)) {
                $get_trend_func = function($value, $last_value){
                    $trend = "eq";
                    if ($value > $last_value) {
                        $trend = "up";
                    } else if ($value < $last_value) {
                        $trend = "down";
                    }

                    return $trend;
                };

                // 季度报价
                $detail["quarter_quote_last"] = $lastInfo["quarter_quote"];
                $detail["quarter_quote_trend"] = $get_trend_func($detail["quarter_quote"], $lastInfo["quarter_quote"]);

                // 半年报价
                $detail["half_year_quote_last"] = $lastInfo["half_year_quote"];
                $detail["half_year_quote_trend"] = $get_trend_func($detail["half_year_quote"], $lastInfo["half_year_quote"]);

                // 年度报价
                $detail["year_quote_last"] = $lastInfo["year_quote"];
                $detail["year_quote_trend"] = $get_trend_func($detail["year_quote"], $lastInfo["year_quote"]);

                // 独家报价
                if ($detail["is_exclusive"] == 1 && $lastInfo["is_exclusive"] == 1) {
                    $detail["exclusive_price_last"] = $lastInfo["exclusive_price"];
                    $detail["exclusive_price_trend"] = $get_trend_func($detail["exclusive_price"], $lastInfo["exclusive_price"]);
                }

                // 包干报价
                if ($detail["is_consist"] == 1 && $lastInfo["is_consist"] == 1) {
                    $detail["consist_price_last"] = $lastInfo["consist_price"];
                    $detail["consist_price_trend"] = $get_trend_func($detail["consist_price"], $lastInfo["consist_price"]);
                }
            }

        }

        if($cll){
            return $detail;
        }

        return sys_response(0, '', [
            'detail' => $detail
        ]);
    }

    /**
     * 获取三维家/erp报价详情
     *
     * @param $id
     * @return array
     */
    public function get_swjerp_quote($id)
    {
        $map['id'] = $id;
        $detail = $this->quoteModel->getOne($map);
        if (is_object($detail)) {
            $detail = $detail->toArray();
            $detail['create_time'] = empty($detail['create_time']) ? '' : date('Y/m/d H:i:s', $detail['create_time']);
            $detail['update_time'] = empty($detail['update_time']) ? '' : date('Y/m/d H:i:s', $detail['update_time']);
        }
        return sys_response(0, '', [
            'detail' => $detail
        ]);
    }


    public function del_city_quote($id)
    {
        $map['id'] = $id;
        $flag = $this->cityQuoteModel->save(['is_delete' => 2, 'update_time' => time()], $map);
//        $this->quoteLogModel->delLog(['quote_id' => $id, 'quote_type' => 1]);
        if ($flag !== false) {
            return sys_response(0, '', []);
        } else {
            return sys_response(5000002, '', []);
        }
    }

    public function del_swjerp_quote($id)
    {
        $map['id'] = $id;
        $flag = $this->quoteModel->save(['is_delete' => 2, 'update_time' => time()], $map);
//        $this->quoteLogModel->delLog(['quote_id' => $id, 'quote_type' => 2]);
        if ($flag !== false) {
            return sys_response(0, '', []);
        } else {
            return sys_response(5000002, '', []);
        }
    }

    /**
     * 获取日志
     *
     * @param $param
     * @param $page
     * @param $page_size
     * @return array
     */
    public function get_quote_log($param, $page, $page_size)
    {
        if (empty($param['quote_id']) || empty($param['quote_type']) || !in_array($param['quote_type'], [1, 2])) {
            return sys_response(4000002, '', []);
        }
        //通过当前报价记录ID,获取当前记录的城市其他记录的所有数据
        $quote_list = $this->getOtherQuoteByQuote($param['quote_id']);
        if(count($quote_list) == 0){
            return sys_response(4000001);
        }
        $map['quote_type'] = $param['quote_type'];
        $map['quote_id'] = ['in',array_column($quote_list,'id')];
        if (!empty($param['start']) && !empty($param['end'])) {
            $map['create_time'] = ['between', [strtotime($param['start']), strtotime($param['end'] . ' 23:59:59')]];
        }
        $count = $this->quoteLogModel->getCount($map);
        $list = [];
        if (!empty($count)) {
            $list = $this->quoteLogModel->getList($map, $page, $page_size);
            $list = $list->toArray();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = empty($v['create_time']) ? '' : date('Y/m/d H:i:s', $v['create_time']);

                if ($v["content"]) {
                    // 处理历史数据档位报价不存在数据补充
                    $list[$k]["content"]["grade_a_price"] = $v["content"]["grade_a_price"] ?? 0;
                    $list[$k]["content"]["grade_a_order"] = $v["content"]["grade_a_order"] ?? "";
                    $list[$k]["content"]["grade_b_price"] = $v["content"]["grade_b_price"] ?? 0;
                    $list[$k]["content"]["grade_b_order"] = $v["content"]["grade_b_order"] ?? "";
                    $list[$k]["content"]["grade_c_price"] = $v["content"]["grade_c_price"] ?? 0;
                    $list[$k]["content"]["grade_c_order"] = $v["content"]["grade_c_order"] ?? "";
                    $list[$k]["content"]["grade_d_price"] = $v["content"]["grade_d_price"] ?? 0;
                    $list[$k]["content"]["grade_d_order"] = $v["content"]["grade_d_order"] ?? "";
                    $list[$k]["content"]["round_order_money"] = $v["content"]["round_order_money"] ?? 0;
                    $list[$k]["content"]["user_order_limit"] = $v["content"]["user_order_limit"] ?? 0;
                    $list[$k]["content"]["part_reform_price"] = $v["content"]["part_reform_price"] ?? 0;
                }
            }
        }
        return sys_response(0, '', [
            'list' => $list,
            'page' => sys_paginate($count, $page_size, $page),
        ]);
    }

    /**
     * @param $data
     * @return array
     */
    public function import_city_quote($data, $user)
    {
        $now = time();
        $list = [];
        $fail_num = $successful_num = 0;
        $string = "导入失败记录\n";
        foreach ($data as $k => $v) {
            $new_value = [];

            $new_value['city_mode'] = $v['C'] == "主做1.0模式"?1:( $v['C'] == "主做2.0模式" ?2 :0);
            $new_value['is_standard'] = $v['D'] == "是"?1:($v['D'] == "否"?2:0);
            // 城市等级
            $new_value['little'] = QuoteCodeEnum::getLittle($v['E']);
            $new_value['round_order_money'] = floatval($v['F']);
            $new_value['part_reform_price'] = floatval($v['G']);
            $new_value['rebates_ratio'] = $this->disposeRebatesRatio($v['H']);
            $new_value['quarter_quote'] = intval($v['I']);
            $new_value['half_year_quote'] = intval($v['J']);
            $new_value['year_quote'] = intval($v['K']);
            $new_value['month_promise_order'] = ($v['L'] == '/' || empty($v['L'])) ? '' : $v['L'];
            $new_value['user_order_limit'] = intval($v['M']);
            $new_value['year_member_time'] = ($v['N'] == '/' || empty($v['N'])) ? '' : $v['N'];
            $new_value['is_consist'] = ($v['O'] == '不包干' || $v['O'] == '/' || $v['O'] === '') ? 2 : 1;
            $new_value['consist_price'] = intval($v['O']);//
            $new_value['consist_fen'] = intval($v['P']);
            $new_value['consist_zeng'] = intval($v['Q']);
            $new_value['is_exclusive'] = ($v['R'] == '不独家' || $v['R'] == '/' || $v['R'] === '') ? 2 : 1;
            $new_value['exclusive_price'] = intval($v['R']);//
            $new_value['exclusive_fen_min'] = intval($v['S']);
            $new_value['exclusive_fen_max'] = intval($v['T']);
            $new_value['exclusive_zeng'] = intval($v['U']);
            $new_value['grade_a_price'] = intval($v['V']);
            $new_value['grade_a_order'] = $v['W'];
            $new_value['grade_b_price'] = intval($v['X']);
            $new_value['grade_b_order'] = $v['Y'];
            $new_value['grade_c_price'] = intval($v['Z']);
            $new_value['grade_c_order'] = $v['AA'];
            $new_value['grade_d_price'] = intval($v['AB']);
            $new_value['grade_d_order'] = $v['AC'];
            $new_value['update_time'] = $now;
            $new_value['create_time'] = $now;

            // 城市存在推送到保存记录数组中
            if (!empty($v['A']) && !empty($v['B'])) {
                // 城市名称
                $new_value['city_name'] = $v['A'];
                // 地级城市名称
                $new_value['parent_city_name'] = $v['B'];

                $cityPinYin = Hanzi::pinyin($v['A']);
                $parentCityPinYin = Hanzi::pinyin($v['B']);
                $new_value['city_px'] = strtoupper(substr($cityPinYin['py'],0,1));
                $new_value['parent_city_px'] = strtoupper(substr($parentCityPinYin['py'],0,1));

                array_push($list, $new_value);
                $successful_num++;
            } else {
                $fail_num++;
                $string .= ($k + 1).'行：'.implode($v, ' ，')."\n";
            }
        }

        // 如果有失败记录，则记录失败记录
        if ($fail_num) {
            Log::write($string,'alert');
        }
        $flag =  $this->cityQuoteModel->saveAll($list);
        if ($flag) {
            $quoteLogList = $this->quoteLogModel->getNoneLogCityQuote(['a.create_time' => $now]);
            $log_list = [];
            foreach ($quoteLogList as $content) {
                $log_list[] = [
                    'quote_id' => $content['id'],
                    'op_admin_id' => $user['user_id'],
                    'op_admin_name' => $user['user_name'],
                    'action' => '新增',
                    'quote_type' => 1,
                    'content' => $content->toArray(),
                    'update_time' => $now,
                    'create_time' => $now
                ];
            }
            $this->quoteLogModel->saveAll($log_list);
        }
        return ['flag' => $flag, 'fail_num' => $fail_num, 'successful_num' => $successful_num];
    }

    public function getOtherQuoteByQuote($quote_id){
        $cityInfo = $this->cityQuoteModel->getOne(['id'=>['eq',$quote_id]],'id,city_name');
        if(empty($cityInfo)){
            return  [];
        }
        $where = [
            'city_name'=>['eq',$cityInfo['city_name']],
            'is_delete'=>['eq',1],
        ];
        $list = $this->cityQuoteModel->getList($where,null,null,'id','id')->toArray();
        return $list;
    }

    /**
     * 获取日志
     *
     * @param $param
     * @param $page
     * @param $page_size
     * @return array
     */
    public function get_history_quote_log($param, $page, $page_size)
    {
        if (empty($param['city_name'])) {
            return sys_response(4000002, '', []);
        }
        //通过当前报价记录ID,获取当前记录的城市其他记录的所有数据
        $where = [
            'city_name' => ['eq', $param['city_name']],
            'is_delete' => ['eq', 1],
        ];
        $quote_list = $this->cityQuoteModel->getList($where, null, null, 'id', 'id')->toArray();
        if (count($quote_list) == 0) {
            return sys_response(4000001);
        }
        $map['quote_type'] = 1;
        $map['quote_id'] = ['in', array_column($quote_list, 'id')];
        $list = $this->quoteLogModel->getList($map, $page, $page_size);
        $list = $list->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['create_time'] = empty($v['create_time']) ? '' : date('Y/m/d H:i:s', $v['create_time']);

            if ($v["content"]) {
                // 处理历史数据档位报价不存在数据补充
                $list[$k]["content"]["grade_a_price"] = $v["content"]["grade_a_price"] ?? 0;
                $list[$k]["content"]["grade_a_order"] = $v["content"]["grade_a_order"] ?? "";
                $list[$k]["content"]["grade_b_price"] = $v["content"]["grade_b_price"] ?? 0;
                $list[$k]["content"]["grade_b_order"] = $v["content"]["grade_b_order"] ?? "";
                $list[$k]["content"]["grade_c_price"] = $v["content"]["grade_c_price"] ?? 0;
                $list[$k]["content"]["grade_c_order"] = $v["content"]["grade_c_order"] ?? "";
                $list[$k]["content"]["grade_d_price"] = $v["content"]["grade_d_price"] ?? 0;
                $list[$k]["content"]["grade_d_order"] = $v["content"]["grade_d_order"] ?? "";
                $list[$k]["content"]["round_order_money"] = floatval($v["content"]["round_order_money"] ?? 0);
                $list[$k]["content"]["user_order_limit"] = $v["content"]["user_order_limit"] ?? 0;
                $list[$k]["content"]["part_reform_price"] = $v["content"]["part_reform_price"] ?? 0;
            }
        }
        return sys_response(0, '', [
            'list' => $list
        ]);
    }

    /**
     * 处理城市返点比例 百分比被转换问题
     * @param $val
     * @return string
     */
    private function disposeRebatesRatio($val)
    {
        if (!empty($val)) {
            if (strpos($val, '.') !== false && ((string)($val * 100 / 100) == $val)) {
                return $val * 100 . '%';
            } else {
                return $val;
            }
        } else {
            return '';
        }
    }
}