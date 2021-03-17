<?php
namespace app\index\validate;

use think\Validate;
use app\index\enum\InvoiceEnum;
/**
 * 
 */
class Invoice extends Validate
{
    
    // 验证规则
    protected $rule = [
        'type' => 'require|checkInvoiceType',
        'billing_company' => 'requireInvoice:billing_company|checkInvoiceCompany',
        'is_in_account' => 'requireInvoice:is_in_account|checkIsOrNot:is_in_account',
        'invoice_price' => 'requireInvoice:invoice_price|float|checkVaildPrice:invoice_price',
        'invoice_title' => 'requireInvoice:invoice_title',
        'taxpayer_identification_number' => 'requireInvoice:taxpayer_identification_number|checkIdenNum',
        'company_address' => 'requireInvoice:company_address',
        'company_phone' => 'requireInvoice:company_phone|checkPhone',
        'company_bank' => 'requireInvoice:company_bank',
        'company_bank_account' => 'requireInvoice:company_bank_account|number',
        'apply_email' => 'requireInvoice:apply_email|email',
        'receiver_name' => 'requireInvoice:receiver_name',
        'receiver_phone' => 'requireInvoice:receiver_phone|checkPhone:1',
        'receiver_address' => 'requireInvoice:receiver_address',
        'license_imgs' => 'requireInvoice:license_imgs',
        // 'other_imgs' => '',
        // 'other_remarks' => '',
        'is_submit' => 'require|checkIsOrNot:is_submit'
    ];

    // 错误信息
    protected $message = [
        'type.require' => '请选择开票类型',
        'billing_company.requireInvoice' => '请选择开票公司',
        'is_in_account.requireInvoice' => '请选择是否到账',
        'invoice_price.requireInvoice' => '请填写开票金额',
        'invoice_price.float' => '请填写有效的开票金额',
        'invoice_title.requireInvoice' => '请填写发票抬头',
        'taxpayer_identification_number.requireInvoice' => '请填写纳税人识别号',
        'company_address.requireInvoice' => '请填写地址',
        'company_phone.requireInvoice' => '请填写电话',
        'company_phone.checkPhone' => '请填写正确的公司电话（座机或手机号）',
        'company_bank.requireInvoice' => '请填写开户行',
        'company_bank_account.requireInvoice' => '请填写银行账号',
        'company_bank_account.number' => '请填写正确的银行账号',
        'apply_email.requireInvoice' => '请填写申请人邮箱',
        'apply_email.email' => '请填写正确的邮箱',
        'receiver_name.requireInvoice' => '请填写收件人',
        'receiver_phone.requireInvoice' => '请填写收件人电话',
        'receiver_phone.checkPhone' => '请填写正确的收件人电话（手机号）',
        'receiver_address.requireInvoice' => '请填写收件人地址',
        'license_imgs.requireInvoice' => '请上传回传合同和营业执照',
        'license_imgs.array'=> '请上传回传合同和营业执照',
        'other_imgs.array'=> '其他说明图片格式错误'
    ];

    //验证场景
    protected $scene = [];

    protected function checkInvoiceType($value, $rule, $data=[])
    {
        $types = array_keys(InvoiceEnum::$type);
        if(!in_array($value, $types)){
            return '开票类型错误';
        }

        return true;
    }

    protected function checkInvoiceCompany($value, $rule, $data=[]){
        $company = array_keys(InvoiceEnum::$billing_company);
        if(!in_array($value, $company)){
            return '开票公司错误';
        }

        return true;
    }

    /*
    * 验证是否
    */
    protected function checkIsOrNot($value, $rule, $data=[]){
        if(!in_array($value, [1, 2])){
            switch ($rule) {
                case 'is_in_account':
                    $msg = '是否到账传值不合法';
                    break;
                case 'is_submit':
                    $msg = '是否提交传值不合法';
                    break;
                default:
                    $msg = $rule.'传值不合法';
                    break;
            }
            return $msg;
        }

        return true;
    }

    /*
    * 验证金额
    */
    protected function checkVaildPrice($value, $rule, $data=[]){
        if($value < 0){
            switch ($rule) {
                case 'invoice_price';
                    $msg = '开票金额必须大于0';
                    break;
                default:
                    $msg = '金额必须大于0';
                    break;
            }
            return $msg;
        }

        return true;
    }

    protected function checkIdenNum($value, $rule, $data=[]){
        $len = strlen($value);
        if($len != 15 && $len != 18 && $len != 20){
            return '请填写有效的纳税人识别号';
        }

        return true;
    }

    /**
    * 验证座机和手机号
    * @param $rule 1 仅验证通过手机号 2 仅验证通过座机 0 都验证通过
    */
    protected function checkPhone($value, $rule, $data=[]){
        $res = true;

        $mobilePreg = '/^1[3-9][0-9]\d{8}$/';
        $isMobile = preg_match($mobilePreg, $value);

        $telPreg = '/^([0-9]{3,4}-)?[0-9]{7,8}$/';
        $isTel = preg_match($telPreg, $value);

        switch ($rule) {
            case '1':
                !$isMobile && $res = false;
                break;
            case '2':
                !$isTel && $res = false;
                break;
            default:
                (!$isMobile && !$isTel) && $res = false;
                break;
        }

        return $res;
    }

    protected function requireInvoice($value, $rule, $data=[]){
        if(empty($rule)){
            return '规则错误';
        }

        if(in_array($rule, $this->checkRequireField($data['type']))){
            if(is_array($value)){
                if(count($value) == 0 || is_null($value[0]) || '' === $value[0]){
                    return false;
                }
            }else{
                if(is_null($value) || '' === $value){
                    return false;
                }    
            }
            
        }

        return true;
    }

    private function checkRequireField($type){
        return [
            InvoiceEnum::INVOICE_TYPE_SPECIAL => [
                'billing_company',
                'is_in_account',
                'invoice_price',
                'invoice_title',
                'taxpayer_identification_number',
                'company_address',
                'company_phone',
                'company_bank_account',
                'company_bank',
                'receiver_name',
                'receiver_phone',
                'receiver_address',
                'license_imgs',
            ],
            InvoiceEnum::INVOICE_TYPE_ORDINARY => [
                'billing_company',
                'is_in_account',
                'invoice_price',
                'invoice_title',
                'taxpayer_identification_number',
                'receiver_name',
                'receiver_phone',
                'receiver_address',
                'license_imgs',
            ],
            InvoiceEnum::INVOICE_TYPE_ELE_ORDINARY => [
                'billing_company',
                'is_in_account',
                'invoice_price',
                'invoice_title',
                'taxpayer_identification_number',
                'apply_email',
                'license_imgs',
            ]
        ][$type] ?? [];
    }
}