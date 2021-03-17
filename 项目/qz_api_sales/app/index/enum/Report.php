<?php

namespace app\index\enum;
/**
 * 
 */
class Report 
{
    public static function lxsType($key)
    {
        return [
            1 => '只接家装',
            2 => '只接公装',
            3 => '家装公装都接',
            4 => '其他'
        ][$key] ?? '';
    }

    public static function getCompanyMode($key)
    {
        return [
            1 => '常规',
            2 => '新签返',
            3 => '老签返'
        ][$key] ?? '';
    }

    public static function passagePosition($key)
    {
        return [
            1 => 'A通栏',
            2 => 'B通栏',
            3 => 'A+B通栏'
        ][$key] ?? '';
    }

    public static function reportFieldName($key){
        return [
            'id' => '报备号',
            'company_name' => '公司名称',
            'cooperation_type' => '合作类型',
            'cooperation_mode' => '合作模式',
            'section' => '部门名称',
            'account' => '账号',
            'city_name' => '城市名称',
            'back_ratio' => '返点比例',
            'viptype' => '单倍/几倍',
            'is_new' => '新/老会员',
            'company_contact' => '联系人',
            'company_contact_position' => '联系人职务',
            'company_contact_phone' => '联系人电话',
            'company_contact_weixin' => '联系人微信',
            'contract_start' => '总合同开始时间',
            'contract_end' => '总合同结束时间',
            'current_contract_start' => '当前合同开始时间',
            'current_contract_end' => '当前合同结束时间',
            'current_payment_time' => '本次款项到账时间',
            'total_contract_amount' => '总合同金额',
            'next_payment_time' => '下次款项到账时间',
            'current_contract_amount' => '本次到账金额',
            'company_rolers' => '报备记录：角色',
            'company_tag' => '标签',
            'lave_amount' => '余款金额',
            'logo_address' => 'logo位置',
            'passage_position' => '通栏位置',
            'contract_handsel_passage' => '总合同广告赠送',
            'contract_handsel_banner' => '总合同广告赠送',
            'current_adver_content' => '本次上广告情况',
            'cureent_adver_content' => '本次上广告情况', // 历史错误
            'amount_and_area' => '接单金额及区域',
            'lxs' => '家装/公装是否都接',
            'lxs_remark' => '是否家装公装都接其它',
            'promised_orders_fen' => '总合同承诺分单数',
            'promised_orders_zeng' => '总合同承诺赠单数',
            'current_promised_orders_fen' => '本次合同承诺分单数',
            'current_promised_orders_zeng' => '本次合同承诺赠单数',
            'year_month_one' => '过年月（一）',
            'year_month_two' => '过年月（二）',
            'current_year_month_one' => '本次合同过年月1',
            'current_year_month_two' => '本次合同过年月2',
            'company_id' => '网店代码',
            'remarke' => '备注',
            'imgs' => '上传凭证',
            'contract_remark' => '当前合同备注',
            'total_contract_remark' => '总合同备注',
            'is_kf_voucher' => '是否客服传凭',
            'open_city_chat_remark' => '开站讨论组备注',
            'cashdeposit_handler' => '保证金预约时间',
            'total_contract_fixed_amount' => '总合同固定费用',
            'total_contract_bond' => '总合同保证金',
            'total_contract_round_amount' => '总合同轮单费',
            'total_contract_round_total_amount' => '总合同总轮单费',
            'total_contract_gift_amount' => '总合同返现金额',
            'current_contract_fixed_amount' => '本次合同固定费用',
            'current_contract_bond' => '本次合同保证金',
            'current_contract_round_amount' => '本次合同轮单费',
            'current_contract_round_total_amount' => '本次合同总轮单费',
            'current_contract_gift_amount' => '本次合同返现金额',
            'round_order_amount' => '轮单费单价',
            'current_vip_start' => '本次会员开始时间',
            'current_vip_end' => '本次会员结束时间',
            'company_mode' => '合作模式',
            'current_fen_orders' => '本次分单量',
            'promise_orders' => '订单承诺量',
            'delay_promise_orders' => '延期承诺单量',
            'delay_days' => '应延期天数',
            'delay_real_days' => '实际延期天数',
            'delay_contract_start' => '延期合同开始时间',
            'delay_contract_end' => '延期合同结束时间',
            'report_cooperation_type' => '报备合同合作类型',
            'total_contract_amount_guaranteed' => '总合同保产值金额',
            'current_contract_amount_guaranteed' => '本次到账保产值金额',
            'current_platform_money'=>'本次合同平台使用费',
            'total_platform_money'=>'总合同平台使用费',
            'total_platform_money_start_date'=>'总合同平台使用费有效期起始日期',
            'total_platform_money_end_date'=>'总合同平台使用费有效期终止日期',
            'current_platform_money_start_date'=>'本次合同平台使用费有效期起始日期',
            'current_platform_money_end_date'=>'本次合同平台使用费有效期终止日期'
        ][$key] ?? '';
    }
}