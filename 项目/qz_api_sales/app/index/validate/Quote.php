<?php
// +----------------------------------------------------------------------
// | Quote 报价管理验证
// +----------------------------------------------------------------------
namespace app\index\validate;


use think\Validate;

class Quote extends Validate
{
    protected $rule = [
        'city_name' => 'require|length:1,20',
        'parent_city_name' => 'require|length:1,20',
        'little' => 'require|in:0,1,2,3,4,5',
        'quarter_quote' => 'require|integer|max:2147483648',
        'half_year_quote' => 'require|integer|max:2147483648',
        'year_quote' => 'require|integer|max:2147483648',
        'month_promise_order' => 'require|length:1,255',
        'year_member_time' => 'require|length:1,255',
        'is_consist' => 'require|in:1,2',
        'consist_price' => 'requireIf:is_consist,1|integer|max:2147483648',
        'consist_fen' => 'requireIf:is_consist,1|integer|max:2147483648',
        'consist_zeng' => 'requireIf:is_consist,1|integer|max:2147483648',
        'is_exclusive' => 'require|in:1,2',
        'exclusive_price' => 'requireIf:is_exclusive,1|integer|max:2147483648',
        'exclusive_fen_max' => 'requireIf:is_exclusive,1|integer|max:2147483648',
        'exclusive_fen_min' => 'requireIf:is_exclusive,1|integer|max:2147483648',
        'exclusive_zeng' => 'requireIf:is_exclusive,1|integer|max:2147483648',
        'cooperation_name' => 'require|length:1,255',
        'quote_type' => 'require|length:1,255',
        'cooperation_price' => 'require|length:1,255',

    ];

    protected $message = [
        'city_name.require' => '请输入城市',
        'city_name.length' => '城市输入过长',
        'parent_city_name.require' => '请输入所属地级市',
        'parent_city_name.length' => '所属地级市输入过长',
        'little.require' => '请选择报价对应城市等级',
        'little.in' => '请选择报价对应城市等级',
        'quarter_quote.require' => '请输入季度报价',
        'quarter_quote.integer' => '请核实季度报价',
        'quarter_quote.max' => '请核实季度报价',
        'half_year_quote.require' => '请输入半年报价',
        'half_year_quote.integer' => '请核实半年报价',
        'half_year_quote.max' => '请核实半年报价',
        'year_quote.require' => '请输入年度报价',
        'year_quote.integer' => '请核实年度报价',
        'year_quote.max' => '请核实年度报价',
        'month_promise_order.length' => '月承诺订单内容过长',
        'year_member_time.require' => '请输入年度会员时间',
        'year_member_time.length' => '年度会员时间内容过长',
        'is_consist.require' => '请选择是否包干',
        'is_consist.in' => '请选择是否包干',
        'consist_price.requireIf' => '请输入包干报价',
        'consist_price.integer' => '请核实包干报价',
        'consist_price.max' => '请核实包干报价',
        'consist_fen.requireIf' => '请输入包干最低承诺分单数',
        'consist_fen.integer' => '请核实包干最低承诺分单数',
        'consist_fen.max' => '请核实包干最低承诺分单数',
        'consist_zeng.requireIf' => '请输入包干赠单承诺',
        'consist_zeng.integer' => '请核实包干赠单承诺',
        'consist_zeng.max' => '请核实包干赠单承诺',
        'is_exclusive.require' => '请选择是否独家',
        'is_exclusive.in' => '请选择是否独家',
        'exclusive_price.requireIf' => '请输入独家报价',
        'exclusive_price.integer' => '请核实独家报价',
        'exclusive_price.max' => '请核实独家报价',
        'exclusive_fen_max.requireIf' => '请输入独家最高分单数',
        'exclusive_fen_max.integer' => '请核实独家最高分单数',
        'exclusive_fen_max.max' => '请核实独家最高分单数',
        'exclusive_fen_min.requireIf' => '请输入独家最低分单数',
        'exclusive_fen_min.integer' => '请核实独家最低分单数',
        'exclusive_fen_min.max' => '请核实独家最低分单数',
        'exclusive_zeng.requireIf' => '请输入独家赠单承诺',
        'exclusive_zeng.integer' => '请核实独家赠单承诺',
        'exclusive_zeng.max' => '请核实独家赠单承诺',
        'cooperation_name.require' => '请输入合作类型',
        'cooperation_name.length' => '合作类型内容过长',
        'quote_type.require' => '请输入报价类型',
        'quote_type.length' => '报价类型内容过长',
        'cooperation_price.require' => '请输入合作报价',
        'cooperation_price.length' => '合作报价内容过长',
    ];

    protected $scene = [
        'addcityquote' => ['city_name', 'parent_city_name', 'little', 'quarter_quote', 'half_year_quote', 'year_quote', 'month_promise_order', 'year_member_time', 'is_consist', 'consist_price', 'consist_fen', 'consist_zeng', 'is_exclusive', 'exclusive_price', 'exclusive_fen_max', 'exclusive_fen_min', 'exclusive_zeng'],
        'addotherquote' => ['cooperation_name', 'quote_type', 'cooperation_price'],
    ];
}