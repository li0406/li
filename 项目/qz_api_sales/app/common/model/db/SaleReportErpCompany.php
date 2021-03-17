<?php
// +----------------------------------------------------------------------
// | SaleReportZxCompany 销售报备装修公司表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class SaleReportErpCompany extends Model
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
        'is_new' => 'integer',
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
    public function setContractStartAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }
    public function setContractEndAttr($value)
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
    public function setCurrentPaymentTimeAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }
    public function setNextPaymentTimeAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    /**
     * 关联订单城市
     *
     * @return \think\model\relation\HasOne
     */
    public function city()
    {
        return $this->hasOne('Quyu', 'cid', 'cs')->field('cid,cname');
    }

    public function saveOne($data, $map = [])
    {
        return $this->save($data, $map);
    }

    public function delOne($map)
    {
        return $this->where(new Where($map))->delete();
    }

    public function getErpReportDetail($where, $with = [], $field = '*')
    {
        return $this->field($field)->where(new Where($where))->with($with)->find();
    }

    public function getSalerId($report_id){
        return $this->where("id", $report_id)->value("saler_id");
    }
}