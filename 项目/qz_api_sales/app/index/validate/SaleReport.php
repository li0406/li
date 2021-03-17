<?php
// +----------------------------------------------------------------------
// | Passport 报备装修公司/erp公司验证
// +----------------------------------------------------------------------
namespace app\index\validate;

use think\Validate;

class SaleReport extends Validate
{
    protected $rule = [
        'company_name' => 'require|length:1,30',
        'cooperation_mode' => 'require|in:1,2',
        'cs' => 'require|number',
        'city_name' => 'require|length:1,30',
        'is_new' => 'require|in:1,2',
        'company_contact' => 'require|length:1,20',
        'company_contact_position' => 'length:1,30',
        'company_contact_phone' => ['require','regex' => '/^(13|14|15|16|17|18|19)[0-9]{9}$/'],
        'company_contact_weixin' => 'length:1,50',
        'contract_start' => 'requireIf:cooperation_mode,1',
        'contract_end' => 'requireIf:cooperation_mode,1',
        'current_contract_start' => 'requireIf:cooperation_type,1|requireIf:cooperation_type,2|requireIf:cooperation_type,3|requireIf:cooperation_type,7',
        'current_contract_end' => 'requireIf:cooperation_type,1|requireIf:cooperation_type,2|requireIf:cooperation_type,3|requireIf:cooperation_type,7|check_current_contract',
        'current_payment_time' => 'require',
        'total_contract_fixed_amount' => 'require|integer|egt:0|max:2147483648',
        'total_contract_amount' => 'require|integer|egt:0|max:2147483648',
        'total_contract_bond' => 'requireIf:cooperation_type,6',
        'current_contract_bond' => 'requireIf:cooperation_type,6',
        'current_contract_fixed_amount' => 'require|integer|egt:0|max:2147483648',
        'current_contract_amount' => 'require|integer|egt:0|max:2147483648|check_contract_amount',
        'lave_amount' => 'integer|egt:-1|max:2147483648',
        'logo_address' => 'length:1,255',
        'passage_position' => 'in:1,2,3',
        'contract_handsel_passage' => 'require|integer|egt:0|max:2147483648',
        'contract_handsel_banner' => 'require|integer|egt:0|max:2147483648',
        'lxs' => 'require|in:1,2,3,4',
        'promised_orders_fen' => 'require',
        'promised_orders_zeng' => 'require',
        'company_id' => 'require|length:1,30',
        'status' => 'require|in:1,2,3,4,5,6,7,8',
        'open_city_chat_remark' => 'require',
        'viptype' => 'requireIf:cooperation_type,1|requireIf:cooperation_type,2|requireIf:cooperation_type,3|requireIf:cooperation_type,7',
        'back_ratio' => 'requireIf:cooperation_type,6',
        'total_contract_round_total_amount' => 'requireIf:cooperation_type,6',
        'total_contract_round_amount' => 'requireIf:cooperation_type,1|requireIf:cooperation_type,2|requireIf:cooperation_type,3|requireIf:cooperation_type,7|requireIf:cooperation_type,6',
        'current_contract_round_total_amount' => 'requireIf:cooperation_type,6',
        'current_contract_round_amount' => 'requireIf:cooperation_type,1|requireIf:cooperation_type,2|requireIf:cooperation_type,3|requireIf:cooperation_type,7|requireIf:cooperation_type,6',
        'total_contract_gift_amount' => 'requireIf:cooperation_type,6',
        'current_contract_gift_amount' => 'requireIf:cooperation_type,6',
    ];

    protected $message = [
        'company_name.require' => '请输入公司名称',
        'company_name.length' => '公司名称过长',
        'cooperation_mode.require' => '请输入合作模式',
        'cooperation_mode.in' => '请选择合作模式一年/终身',
        'cs.require' => '请选择城市',
        'cs.number' => '请选择城市',
        'city_name.require' => '请选择城市',
        'city_name.length' => '城市过长',
        'is_new.require' => '请选择新/老会员',
        'is_new.in' => '请选择新/老会员',
        'company_contact.require' => '请输入联系人',
        'company_contact.length' => '联系人名称过长',
        'company_contact_phone.require' => '请输入电话',
        'company_contact_phone.regex' => '请输入正确的手机号',
        'company_contact_position.length' => '联系人职务过长',
        'company_contact_weixin.length' => '联系人微信过长',
        'contract_start.requireIf' => '请选择总合同时间',
        'contract_end.requireIf' => '请选择总合同时间',
        'current_contract_start.require' => '请选择本次会员时间',
        'current_contract_start.requireIf' => '请选择本次会员时间',
        'current_contract_end.require' => '请选择本次会员时间',
        'current_payment_time.require' => '请选择本次款项到账时间',
        'next_payment_time.require' => '请选择下次到账时间',
        'total_contract_bond.requireIf' => '请输入总合同保证金',
        'total_contract_fixed_amount.require' => '请输入总合同服务费',
        'total_contract_fixed_amount.integer' => '总合同服务费错误',
        'total_contract_fixed_amount.egt' => '总合同服务费错误',
        'total_contract_fixed_amount.max' => '总合同服务费错误',
        'total_contract_amount.require' => '请输入总合同金额',
        'total_contract_amount.integer' => '总合同金额错误',
        'total_contract_amount.egt' => '总合同金额错误',
        'total_contract_amount.max' => '总合同金额错误',
        'current_contract_bond.requireIf' => '请输入本次到账保证金',
        'current_contract_fixed_amount.require' => '请输入本次到账服务费',
        'current_contract_fixed_amount.integer' => '本次到账服务费错误',
        'current_contract_fixed_amount.egt' => '本次到账服务费错误',
        'current_contract_fixed_amount.max' => '本次到账服务费错误',
        'current_contract_amount.require' => '请输入本次到账金额',
        'current_contract_amount.integer' => '本次到账金额错误',
        'current_contract_amount.egt' => '本次到账金额错误',
        'current_contract_amount.max' => '本次到账金额错误',
        'lave_amount.integer' => '余款多少错误',
        'lave_amount.egt' => '余款多少错误',
        'lave_amount.max' => '余款多少错误',
        'lxs.require' => '请选择家装/公装是否都接',
        'lxs.in' => '请选择家装/公装是否都接',
//        'promised_orders_fen.require' => '请输入订单承诺量',
//        'promised_orders_zeng.require' => '请输入订单承诺量',
//        'current_promised_orders_fen.require' => '请输入订单承诺量',
//        'current_promised_orders_zeng.require' => '请输入订单承诺量',
        'logo_address.length' => 'logo位置输入过长',
        'passage_position.in' => '请选择通栏位置',
        'contract_handsel_passage.require'=> '请输入总合同广告赠送（通栏）天数',
        'contract_handsel_passage.integer' => '请核实总合同广告赠送（通栏）天数',
        'contract_handsel_passage.egt' => '请核实总合同广告赠送（通栏）天数',
        'contract_handsel_passage.max' => '请核实总合同广告赠送（通栏）天数',
        'contract_handsel_banner.require' => '请输入总合同广告赠送（轮显）天数',
        'contract_handsel_banner.integer' => '请核实总合同广告赠送（轮显）天数',
        'contract_handsel_banner.egt' => '请核实总合同广告赠送（轮显）天数',
        'contract_handsel_banner.max' => '请核实总合同广告赠送（轮显）天数',
        'company_id.require' => '请输入网店代码',
        'company_id.length' => '网店代码长度过长',
        'status.require' => '审核状态错误',
        'status.in' => '审核状态错误',
        'open_city_chat_remark.require' => '请输入开站讨论组备注',
        'viptype.requireIf' => '请选择单倍/几倍',
        'back_ratio.requireIf' => '请选择返点比例',
        'total_contract_round_total_amount.requireIf' => '请输入总合同总轮单费',
        'total_contract_round_amount.requireIf' => '请输入总合同轮单费',
        'current_contract_round_total_amount.requireIf' => '请输入本次总轮单费',
        'current_contract_round_amount.requireIf' => '请输入本次轮单费',
        'total_contract_gift_amount.requireIf' => '请输入总合同赠送金额',
        'current_contract_gift_amount.requireIf' => '请输入本次合同赠送金额',
    ];

    /**
     * 添加 装修公司报备
     * @return $this
     */
    public function sceneAddzx()
    {
        return $this->only(
            [
                'company_name', 'cs', 'city_name', 'is_new', 'viptype', 'back_ratio',
                'company_contact', 'company_contact_position', 'company_contact_phone', 'company_contact_weixin',
                'contract_start', 'contract_end', 'current_contract_start', 'current_contract_end',
                'current_payment_time', 'next_payment_time', 'total_contract_amount', 'current_contract_amount',
                'lave_amount', 'company_id', 'status', 'logo_address', 'passage_position', 'contract_handsel_passage',
                'contract_handsel_banner', 'current_adver_content', 'amount_and_area', 'lxs',
//                'promised_orders_fen', 'promised_orders_zeng', 'current_promised_orders_fen', 'current_promised_orders_zeng',
                'total_contract_round_amount', 'total_contract_round_total_amount',
                'current_contract_round_amount', 'current_contract_round_total_amount',
                'total_contract_gift_amount', 'current_contract_gift_amount',
                'open_city_chat_remark', 'check_round_amount', 'check_gift_amount','total_platform_money', 'current_platform_money'
            ])
            ->append('check_round_amount', function($value, $data){
                if(in_array($data['cooperation_type'], [4,5,8])){
                    return true;
                }

                if (!is_numeric($data['total_contract_round_amount'])) {
                    return '总合同轮单费非法';
                } else if (!is_numeric($data['current_contract_round_amount'])) {
                    return '本次合同轮单费非法';
                }

                if ($data['cooperation_type'] == 6) {
                    if (!is_numeric($data['total_contract_round_total_amount'])) {
                        return '总合同总轮单费非法';
                    } else if (!is_numeric($data['current_contract_round_total_amount'])) {
                        return '本次合同总轮单费非法';
                    }

                    if ($data['total_contract_round_amount'] > $data['total_contract_round_total_amount']) {
                        return '轮单费不得大于总轮单费';
                    } else if ($data['current_contract_round_amount'] > $data['current_contract_round_total_amount']) {
                        return '轮单费不得大于总轮单费';
                    }else if ($data['current_contract_round_total_amount'] > $data['total_contract_round_total_amount']) {
                        return '本次总轮单费不得大于总合同总轮单费';
                    }

                    // 总轮单费=轮单费+赠送金额
                    $data["total_contract_gift_amount"] = $data["total_contract_gift_amount"] ?? 0;
                    $data["current_contract_gift_amount"] = $data["current_contract_gift_amount"] ?? 0;
                    $total_contract_round_total_amount = $data['total_contract_round_amount'] + $data["total_contract_gift_amount"];
                    $current_contract_round_total_amount = $data['current_contract_round_amount'] + $data["current_contract_gift_amount"];
                    if ($data['total_contract_round_total_amount'] != $total_contract_round_total_amount) {
                        return "总轮单费与轮单费，返现金额的合计不一致";
                    } else if ($data['current_contract_round_total_amount'] != $current_contract_round_total_amount) {
                        return "总轮单费与轮单费，返现金额的合计不一致";
                    }

                    // 总合同金额 = 服务费+轮单费+保证金
                    $total_contract_round_amount = $data["total_contract_round_amount"] ?? 0;
                    $total_contract_fixed_amount = $data["total_contract_fixed_amount"] ?? 0;
                    $total_contract_bond = $data["total_contract_bond"] ?? 0;
                    $total_platform_money = $data['total_platform_money'] ?? 0;
                    $total_contract_amount = $total_contract_fixed_amount + $total_contract_round_amount + $total_contract_bond + $total_platform_money;
                    if ($data["total_contract_amount"] != $total_contract_amount) {
                        return "总合同金额与轮单费、保证金、平台使用费合计不一致";
                    }

                    // 本次到账金额 = 服务费+轮单费+保证金
                    $current_contract_round_amount = $data["current_contract_round_amount"] ?? 0;
                    $current_contract_fixed_amount = $data["current_contract_fixed_amount"] ?? 0;
                    $current_contract_bond = $data["current_contract_bond"] ?? 0;
                    $current_platform_money = $data['current_platform_money'] ?? 0;
                    $current_contract_amount = $current_contract_fixed_amount + $current_contract_round_amount + $current_contract_bond + $current_platform_money;
                    if ($data["current_contract_amount"] != $current_contract_amount) {
                        return "本次到账金额与轮单费、保证金、平台使用费合计不一致";
                    }

                    //验证轮单单价
                    if(empty($data['round_order_amount'])){
                        return "请输入轮单费单价";
                    }
                }

                if(in_array($data['cooperation_type'], [1,2,3,7])){
                    if($data['total_contract_round_amount'] < $data['current_contract_round_amount']){
                        return '本次会员费不得大于总合同会员费';
                    }

                    $total_contract_round_amount = $data["total_contract_round_amount"] ?? 0;
                    $total_platform_money = $data['total_platform_money'] ?? 0;
                    $total_contract_amount = $total_contract_round_amount + $total_platform_money;
                    if ($data["total_contract_amount"] != $total_contract_amount) {
                        return "总合同金额与会员费、平台使用费合计不一致";
                    }

                    $current_contract_round_amount = $data["current_contract_round_amount"] ?? 0;
                    $current_platform_money = $data['current_platform_money'] ?? 0;
                    $current_contract_amount = $current_contract_round_amount + $current_platform_money;
                    if ($data["current_contract_amount"] != $current_contract_amount) {
                        return "本次到账金额与会员费、平台使用费合计不一致";
                    }
                }

                return true;
            })
            ->append('total_platform_money', function($value, $data){
                if($value > 0 ){
                    if(empty($data['total_platform_money_start_date'])){
                        return '请选择开始日期';
                    }
                    if(empty($data['total_platform_money_end_date'])){
                        return '请选择终止日期';
                    }
                    if(strtotime($data['total_platform_money_start_date']) > strtotime($data['total_platform_money_end_date'])){
                        return '终止日期不得小于开始日期';   
                    }
                }
                return true;
            })
            ->append('current_platform_money', function($value, $data){
                if($value > 0 ){
                    if(empty($data['current_platform_money_start_date'])){
                        return '请选择开始日期';
                    }
                    if(empty($data['current_platform_money_end_date'])){
                        return '请选择终止日期';
                    }
                    if(strtotime($data['current_platform_money_start_date']) > strtotime($data['current_platform_money_end_date'])){
                        return '终止日期不得小于开始日期';   
                    }

                    if(strtotime($data['total_platform_money_start_date']) > strtotime($data['current_platform_money_start_date'])){
                        return '本次合同平台使用费开始日期不得早于总合同使用费开始日期';
                    }

                    if(strtotime($data['total_platform_money_end_date']) < strtotime($data['current_platform_money_end_date'])){
                        return '本次合同平台使用费终止日期不得晚于总合同使用费终止日期';
                    }

                    if(strtotime($data['total_platform_money_end_date']) < strtotime($data['current_platform_money_start_date'])){
                        return '本次平台使用费开始日期不得早于总平台使用费结束日期';
                    }
                }
                return true;
            })
            ->append('check_gift_amount', function($value, $data){
                if ($data['cooperation_type'] == 6) {
                    if (!is_numeric($data['total_contract_gift_amount'])) {
                        return '总合同赠送金额非法';
                    } else if (!is_numeric($data['current_contract_gift_amount'])) {
                        return '本次合同赠送金额非法';
                    }

                    if ($data['current_contract_gift_amount'] > $data['total_contract_gift_amount']) {
                        return '本次赠送金额不得大于总合同赠送金额';
                    }
                }

                return true;
            });
    }

    /**
     * 添加 erp公司报备
     * @return $this
     */
    public function sceneAdderp()
    {
        return $this->only(
            ['company_name', 'cooperation_mode', 'cs', 'city_name', 'is_new', 'company_contact', 'company_contact_position', 'company_contact_phone', 'company_contact_weixin', 'contract_start', 'contract_end', 'current_payment_time', 'next_payment_time', 'total_contract_amount', 'current_contract_amount', 'lave_amount', 'company_id', 'status', 'open_city_chat_remark']);
    }

    /**
     * 自定义检查合同时间
     *
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function check_current_contract($value, $rule, $data)
    {
        $data['contract_start'] = $data['contract_start'] ?? "";
        $data['contract_end'] = $data['contract_end'] ?? "";
        $data['current_contract_start'] = $data['current_contract_start'] ?? "";
        $data['current_contract_end'] = $data['current_contract_end'] ?? "";

        $contractStart = strtotime($data['contract_start']);
        $contractEnd = strtotime($data['contract_end']);
        $currentContractStart = strtotime($data['current_contract_start']);
        $currentContractEnd = strtotime($data['current_contract_end']);

        if ($contractStart === false || $contractEnd === false) {
            return '合同时间格式错误';
        }
        if ($currentContractStart === false || $currentContractEnd === false) {
            return '本次会员时间格式错误';
        }
        if ($currentContractStart < $contractStart || $currentContractEnd > $contractEnd){
            return '本次会员时间必须在合同时间范围内';
        }
        return true;
    }

    // 验证本次到账金额不得大于总合同金额
    public function check_contract_amount($value, $rule, $data){
        if ($value > $data['total_contract_amount']) {
            return '本次到账金额不得大于总合同金额';
        }
        return true;
    }
}
