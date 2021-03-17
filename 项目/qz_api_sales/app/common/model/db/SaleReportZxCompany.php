<?php
// +----------------------------------------------------------------------
// | SaleReportZxCompany 销售报备装修公司表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use app\index\enum\SalesReportCodeEnum;
use think\Db;
use think\Model;
use think\db\Where;
use think\db\Query;

class SaleReportZxCompany extends Model
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

    public function getById($id, $field = "*"){
        return $this->where("id", $id)->field($field)->find();
    }

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
    public function setDenyPromisedStartAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }
    public function setDenyPromisedEndAttr($value)
    {
        if (empty($value)) {
            return 0;
        }elseif (ctype_digit((string)$value)) {
            return $value;
        } else {
            return strtotime($value) ?: 0;
        }
    }

    protected function getUnionAllBuildSql()
    {
        return $this->table('qz_sale_report_zx_company')
            ->field([
                    'id', 'company_name', 'cooperation_type', 'cs', 'city_name','viptype','back_ratio', 'is_new', 'company_contact', 'company_contact_phone', 'company_contact_weixin', '"" account', 'contract_start', 'contract_end', 'current_contract_start', 'current_contract_end', 'current_payment_time', 'total_contract_amount', 'current_contract_amount', 'saler_id', 'saler', 'status', 'create_time', 'update_time', 'submit_time', 'check_time', 'kf_check_time', 'kf_finish_time', 'company_id', '0 company_mode', '0 delay_contract_start', '0 delay_contract_end', '0 delay_real_days'
            ])->unionAll(function ($query) {
                $query->field([
                    'id', 'company_name', 'cooperation_type', '"" cs','"" city_name', '"" viptype','"" back_ratio', '"" is_new', 'company_contact', 'company_contact_phone', '"" company_contact_weixin', 'account', '"" contract_start', '"" contract_end', 'current_contract_start', 'current_contract_end', '"" current_payment_time', '0 total_contract_amount', 'current_contract_amount', 'saler_id', 'saler', 'status', 'create_time', 'update_time', 'submit_time', 'check_time', 'kf_check_time', 'kf_finish_time', '"" company_id', '0 company_mode', '0 delay_contract_start', '0 delay_contract_end', '0 delay_real_days'
                ])->table('qz_sale_report_swj_company');
            })
            ->unionAll(function ($query) {
                $query->field([
                    'id', 'company_name', 'cooperation_type', 'cs', 'city_name', 'viptype', '"" back_ratio', 'is_new', 'company_contact', 'company_contact_phone', 'company_contact_weixin', 'account', 'contract_start', 'contract_end', 'current_contract_start', 'current_contract_end', 'current_payment_time', 'total_contract_amount', 'current_contract_amount', 'saler_id', 'saler', 'status', 'create_time', 'update_time', 'submit_time', 'check_time', 'kf_check_time', 'kf_finish_time', 'company_id', '0 company_mode', '0 delay_contract_start', '0 delay_contract_end', '0 delay_real_days'
                ])->table('qz_sale_report_erp_company');
            })
            ->unionAll(function ($query) {
                $query->field([
                    'id', 'company_name', 'cooperation_type', 'cs', 'city_name', 'viptype', 'back_ratio', '2 as is_new', '"" as company_contact', '"" as company_contact_phone', '"" as company_contact_weixin', '"" as account', '"" as contract_start', ' "" as contract_end', 'delay_contract_start as current_contract_start', 'delay_contract_end as current_contract_end', '"" as current_payment_time', '"" as total_contract_amount', '"" as current_contract_amount', 'saler_id', 'saler', 'status', 'create_time', 'update_time', 'submit_time', 'check_time', 'kf_check_time', 'kf_finish_time', 'company_id', 'company_mode',  'delay_contract_start', 'delay_contract_end', 'delay_real_days'
                ])->table('qz_sale_report_delay_company');
            })
            ->buildSql();
    }

    /**
     * 获取聚合列表
     *
     * @param $map
     * @param null $page
     * @param null $page_size
     * @param array $with
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getUnionAllList($map, $page = null, $page_size = null, $with = [], $orderby = 'create_time desc', $condition = [])
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $page_size * ($page - 1);
        }
        return $this->table($this->getUnionAllBuildSql())->alias('t')
            ->where(new Where($map))
            ->where(function ($q) use ($condition) {
                if (!empty($condition)) {
                    $map1[] = ["t.company_name", "LIKE", "%$condition%"];
                    $map1[] = ["t.saler", "=", $condition];
                    $map1[] = ["t.company_contact_phone", "=", $condition];
                    $q->whereOr($map1);
                }
            })
            ->limit($offset, $page_size)
            ->with($with)
            ->orderRaw($orderby)
            ->select();
    }

    /**
     * 获取聚合数量
     *
     * @param $map
     * @param null $page
     * @param null $page_size
     * @param array $with
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getUnionAllCount($map, $condition = '')
    {
        return $this->table($this->getUnionAllBuildSql())->alias('t')
            ->where(new Where($map))
            ->where(function ($q) use ($condition) {
                if (!empty($condition)) {
                    $map1[] = ["t.company_name", "LIKE", "%$condition%"];
                    $map1[] = ["t.saler", "=", $condition];
                    $map1[] = ["t.company_contact_phone", "=", $condition];
                    $q->whereOr($map1);
                }
            })
            ->count();
    }

    public function saveOne($data, $map = [])
    {
        return $this->save($data, $map);
    }

    public function delOne($map)
    {
        return $this->where(new Where($map))->delete();
    }

    public function getZxReportDetail($where, $with = [], $field = '*')
    {
        return $this->field($field)->where(new Where($where))->with($with)->find();
    }
    
    public function getSalerId($report_id){
        return $this->where("id", $report_id)->value("saler_id");
    }

    private function getSelectPageMap($input){
        $map = new Query();
        $map->where("a.status", "in", $input["status"]);

        // 查询城市
        if (!empty($input["city_id"]) && !empty($input["city_name"])) {
            $map->where(function($query) use ($input) {
                $query->where("a.cs", $input["city_id"]);
                $query->whereOr("a.city_name", $input["city_name"]);
            });
        } else if (!empty($input["city_id"]) ) {
            $map->where("a.cs", $input["city_id"]);
        } else if (!empty($input["city_name"])) {
            $map->where("a.city_name", $input["city_name"]);
        }

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
    public function getSelectPageCount($input){
        $map = $this->getSelectPageMap($input);

        return $this->alias("a")->where($map)
            ->count("a.id");
    }

    // 获取小报备关联选择大报备列表
    public function getSelectPageList($input, $offset = 0, $limit = 0){
        $map = $this->getSelectPageMap($input);

        return $this->alias("a")->where($map)
            ->field([
                    "a.id", "a.company_name", "a.cooperation_type", "a.cs as city_id", "a.city_name",
                    "a.viptype", "a.back_ratio", "a.current_contract_start", "a.current_contract_end", "a.is_new",
                    "a.company_contact", "a.company_contact_phone", "a.company_contact_weixin", "a.create_time", "a.submit_time",
                    "a.saler_id", "a.saler as saler_name", "a.current_contract_amount", "a.check_time", "a.status"
                ])
            ->order("a.create_time desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 装修公司的大报备次数
    public function getCompanyReportNumber($company_id){
        $map = new Query();
        $map->where("a.status", 8);
        $map->where("a.company_id", $company_id);

        return $this->alias("a")->where($map)->count("a.id");
    }

    // 会员公司大报备列表
    public function getCompanyReportList($company_id){
        $map = new Query();
        $map->where("a.status", 8);
        $map->where("a.company_id", $company_id);

        return $this->alias("a")->where($map)
            ->field([
                    "a.id", "a.cooperation_type", "a.company_name", "a.cs as city_id", "a.city_name",
                    "a.viptype", "a.back_ratio", "a.current_contract_start", "a.current_contract_end", "a.is_new",
                    "a.company_contact", "a.company_contact_phone", "a.company_contact_weixin", "a.create_time", "a.submit_time",
                    "a.saler_id", "a.saler as saler_name", "a.current_contract_amount", "a.check_time", "a.status", "a.lave_amount",
                    "a.promised_orders_fen","a.promised_orders_zeng","a.current_promised_orders_fen","a.current_promised_orders_zeng",
                    "a.contract_start", "a.contract_end", "a.round_order_amount", "a.total_contract_amount"
                ])
            ->order("a.create_time desc")
            ->select();
    }

    public function getReportCountByDay($begin,$end)
    {
        $map[] = ["kf_check_time","EGT",$begin];
        $map[] = ["kf_check_time","ELT",$end];
        $map[] = ["status","EQ",8];

        $first_sql = Db::name("sale_report_erp_company")->where($map)->field("count(id) as count,count(if(is_new = 1,1,null)) as new_count,count(if(is_new = 2,1,null)) as un_new_count")->buildSql();
        $second_sql = Db::name("sale_report_swj_company")->where($map)->field("count(id) as count,0 as new_count,0 as un_new_count")->buildSql();

        $buildSql = $this->where($map)->field("count(id) as count,count(if(is_new = 1,1,null)) as new_count,count(if(is_new = 2,1,null)) as un_new_count")
                         ->union($first_sql)
                         ->union($second_sql)
                         ->buildSql();
       return Db::table($buildSql)->alias("a")->field("sum(count) as all_count,sum(new_count) as all_new_count,sum(un_new_count) as all_un_new_count")->find();
    }

    public function getMemberChart($begin,$end)
    {
        $map[] = ["a.kf_check_time","EGT",$begin];
        $map[] = ["a.kf_check_time","ELT",$end];
        $map[] = ["a.status","EQ",8];

        return $this->where($map)->alias("a")
             ->field("cooperation_type,count(id) as count")
            ->group("a.cooperation_type")->select();

    }

    /**
     * 查询最近重复装修公司
     * @param $map
     * @param string $orderby
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllCount($company_id)
    {
        return $this->table($this->getCompanyUnionBuildSql($company_id))->alias('t')
            ->field('count(t.id2) count_num,t.id')
            ->group('t.id')
            ->select();
    }

    protected function getCompanyUnionBuildSql($company_id,$days = 3)
    {
        return $this->table('qz_sale_report_zx_company')->alias('zx')
            ->field([
                'zx.id','zx1.id id2', 'zx.company_name'
            ])
            ->join('qz_sale_report_zx_company zx1', 'zx1.company_id = zx.company_id')
            ->unionAll(function ($query) use ($company_id,$days) {
                $query->field(
                    ['e.id','e1.id id2', 'e.company_name']
                )->table('qz_sale_report_erp_company')->alias('e')
                    ->join('qz_sale_report_erp_company e1', 'e1.company_id = e.company_id')
                    ->where('e1.status','in',SalesReportCodeEnum::getCheckReportStatus())
                    ->where('e.company_id','in',$company_id)
                    ->where('e1.submit_time <= e.submit_time')
                    ->where("e1.submit_time > e.submit_time - " . 86400 * $days);
            })
            ->where('zx1.status','in',SalesReportCodeEnum::getCheckReportStatus())
            ->where('zx.company_id','in',$company_id)
            ->where('zx1.submit_time <= zx.submit_time')
            ->where("zx1.submit_time > zx.submit_time - " . 86400 * $days)
            ->buildSql();
    }

    public function getCityMembersCount($map)
    {
        $where = [
            'u.on' => 2,
            'c.fake' => 0
        ];

        $map = array_merge($map, $where);
        
        return $this->table('qz_user')->alias('u')->leftjoin('qz_user_company c', ' u.id=c.userid')->where($map)->count();
    }
}