<?php
// +----------------------------------------------------------------------
// | SaleReportZxCompany 销售报备装修公司表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;
use think\db\Where;
use think\db\Query;

class SaleReportSwjCompany extends Model
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

    public function getSwjReportDetail($where, $with = [], $field = '*')
    {
        return $this->field($field)->where(new Where($where))->with($with)->find();
    }

    public function getSalerId($report_id){
        return $this->where("id", $report_id)->value("saler_id");
    }

    private function getSelectSwjPageMap($input){
        $map = new Query();
        $map->where("a.status", "in", $input["status"]);

        // 销售人员
        if (!empty($input["saler_ids"])) {
            $map->where("a.saler_id", "in", $input["saler_ids"]);
        }

        // 查询装修公司
        if (!empty($input["company_name"])) {
            $map->where("a.company_name", "like", "%" .$input["company_name"]. "%");
        }

        // 报备人
        if (!empty($input["saler_name"])) {
            $map->where("a.saler", "like", "%" .$input["saler_name"]. "%");
        }

        // 关键词
        if (!empty($input["keyword"])) {
            $map->where(function($query) use ($input) {
                $query->where("a.company_contact_phone", $input["keyword"]);
                $query->whereOr("a.company_name", $input["keyword"]);
                $query->whereOr("a.saler", $input["keyword"]);
            });
        }

        return $map;
    }

    // 获取小报备关联选择大报备列表数量
    public function getSelectSwjPageCount($input){
        $map = $this->getSelectSwjPageMap($input);

        return $this->alias("a")->where($map)
            ->count("a.id");
    }

    // 获取小报备关联选择大报备列表
    public function getSelectSwjPageList($input, $offset = 0, $limit = 0){
        $map = $this->getSelectSwjPageMap($input);

        return $this->alias("a")->where($map)
            ->field([
                    "a.id", "a.company_name", "a.cooperation_type",
                    "a.current_contract_start", "a.current_contract_end", "a.section", "a.account",
                    "a.company_contact", "a.company_contact_phone", "a.create_time", "a.submit_time",
                    "a.saler_id", "a.saler as saler_name", "a.current_contract_amount", "a.check_time", "a.status"
                ])
            ->order("a.create_time desc")
            ->limit($offset, $limit)
            ->select();
    }
}