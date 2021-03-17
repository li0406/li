<?php
namespace app\index\enum;
/**
 * 
 */
class InvoiceEnum
{
    // 发票类型
    const INVOICE_TYPE_SPECIAL = 1;
    const INVOICE_TYPE_ORDINARY = 2;
    const INVOICE_TYPE_ELE_ORDINARY = 3;

    // 发票状态
    const INVOICE_STATUS_PRE = 1;
    const INVOICE_STATUS_SUBMIT = 2;
    const INVOICE_STATUS_ONGOING = 3;
    const INVOICE_STATUS_NORMAL = 4;
    const INVOICE_STATUS_REJECT = 5;
    const INVOICE_STATUS_DELETE = -1; //删除
    const INVOICE_STATUS_RECALL = -2; //撤回

    public static $type = [
        SELF::INVOICE_TYPE_SPECIAL => '增值税专用发票',
        SELF::INVOICE_TYPE_ORDINARY => '增值税普通发票',
        SELF::INVOICE_TYPE_ELE_ORDINARY => '增值税电子普通发票'
    ];

    public static $status = [
        SELF::INVOICE_STATUS_PRE => '待提交',
        SELF::INVOICE_STATUS_SUBMIT => '待审核',
        SELF::INVOICE_STATUS_ONGOING => '待开票', //alias:开票中
        SELF::INVOICE_STATUS_NORMAL => '已开票',
        SELF::INVOICE_STATUS_REJECT => '已驳回',
    ];

    public static $billing_company = [
        1 => '苏州云网通信息科技有限公司',
        2 => '苏州齐装网络技术有限公司',
    ];

    public static $is_in_account = [
        1 => '是',
        2 => '否',
    ];

    // 获取状态列表
    public static function getList($module){
        $key = strtolower($module);

        $list = [];
        if (isset(static::$$key)) {
            foreach (static::$$key as $k => $val) {
                $list[] = [
                    "id" => $k,
                    "name" => $val
                ];
            }
        }

        return $list;
    }

    /**
     * 获取状态值
     * @Author   liuyupeng
     * @DateTime 2019-05-20T15:28:28+0800
     */
    public static function getItem($module, $code, $default = ''){
        $key = strtolower($module);
        $code = strtolower($code);

        if (isset(static::$$key)) {
            $modules = static::$$key;
            if (array_key_exists($code, $modules)) {
                return $modules[$code];
            }
        }

        return $default;
    }
    
    /*
    * 获取所有审核状态
    */
    public static function getCheckStatus(){
        return [
            static::INVOICE_STATUS_ONGOING,
            static::INVOICE_STATUS_NORMAL,
            static::INVOICE_STATUS_REJECT
        ];
    }
}