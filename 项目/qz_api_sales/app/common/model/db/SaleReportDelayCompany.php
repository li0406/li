<?php
// +----------------------------------------------------------------------
// | SaleReportDelayCompany 销售报备会员延期报备表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;
use think\db\Where;
use think\db\Query;

class SaleReportDelayCompany extends Model
{
    /**
     * 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
     * @var bool|string
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段 false表示关闭
     * @var false|string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段 false表示关闭
     * @var false|string
     */
    protected $updateTime = 'update_time';

    protected $type = [
        'status' => 'integer',
    ];
    
    public function setKfFinishTimeAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setKfCheckTimeAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setCheckTimeAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setSubmitTimeAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setCurrentContractStartAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setCurrentContractEndAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setCurrentVipStartAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setCurrentVipEndAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setDelayContractStartAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function setDelayContractEndAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    public function getById($id, $field = "*"){
        return $this->where("id", $id)->field($field)->find();
    }

    public function saveOne($data, $map = [])
    {
        return $this->save($data, $map);
    }

    public function delOne($map)
    {
        return $this->where(new Where($map))->delete();
    }

    public function getDelayReportDetail($where, $with = [], $field = '*')
    {
        return $this->field($field)->where(new Where($where))->with($with)->find();
    }

    public function getSalerId($report_id){
        return $this->where("id", $report_id)->value("saler_id");
    }

    // 装修公司的大报备次数
    public function getCompanyReportDelayNumber($company_id){
        $map = new Query();
        $map->where("a.status", 8);
        $map->where("a.company_id", $company_id);

        return $this->alias("a")->where($map)->count("a.id");
    }


    // 会员公司大报备列表
    public function getCompanyReportDelayList($company_id){
        $map = new Query();
        $map->where("a.status", 8);
        $map->where("a.company_id", $company_id);

        return $this->alias("a")->where($map)
            ->field([
                    "a.id", "a.cooperation_type", "a.company_name", "a.company_mode", "a.cs as city_id", "a.city_name",
                    "a.viptype", "a.back_ratio", "a.current_contract_start", "a.current_contract_end",
                    "a.delay_contract_start", "a.delay_contract_end", "a.delay_promise_orders",
                    "a.create_time", "a.submit_time", "a.saler_id", "a.saler as saler_name", "a.check_time", "a.status",
                ])
            ->order("a.create_time desc")
            ->select();
    }
}