<?php
// +----------------------------------------------------------------------
// | Orders 订单表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;
use think\db\Where;
use think\db\Query;

class UserVip extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_user_vip';

    /**
     * 获取装修公司暂停信息
     */
    public function getParseContractList($company_id)
    {
        $map = array(
            "type" => array("EQ",4),
            "company_id" => array("IN",$company_id)
        );

        return $this->where(new Where($map))->field("company_id,end_time")->select();
    }

    public function getMemberCountByDate($begin,$end)
    {
        $map[] = ["time","EGT",$begin];
        $map[] = ["time","ELT",$end];
        $map[] = ["type","IN",[2,8]];

        return $this->where($map)->count();
    }

    // 获取装修公司当前合同/历史合同信息
    public function getCompanyContractList($company_id, $date){
        $map = new Query();
        $map->where("a.company_id", $company_id);
        $map->where("a.start_time", "<=", $date);
        $map->where("a.type", "in", [2, 8]);

        return $this->alias("a")->where($map)
            ->field([
                "a.id", "a.start_time", "a.end_time", "a.viptype", 
                "a.contract_type", "a.cooperate_mode", "a.time",
                "a.saler_id", "a.saler_name", "a.op_uid", "a.op_uname"
            ])
            ->order("a.start_time desc")
            ->select();
    }

    // 获取城市会员数
    public function getCityVipCountList($input){
        // $query = new Query();

        $pauseMap = [
            ['start_time', '<=', $input['end_date']],
            ['end_time', '>=', $input['start_date']],
            ['type', '=', 4],
        ];

        $map = [
            ['a.type', 'IN', [2, 8]],
            ['a.start_time', '<=', $input['end_date']],
            ['a.end_time', '>=', $input['start_date']],
            ['u.classid', 'IN', [3, 6]],
            ['c.fake', '=', 0],
            ['a.company_id', 'NOT IN', function($query) use($pauseMap){
                $query->table('qz_user_vip')->where($pauseMap)->field('company_id');
            }]
        ];

        $subSqlChild1 = $this->table('qz_user_company_round_order_amount')->orderRaw('field(`type`, 4, 1, 3, 2)')->where('price', '>', 0)->buildSql();
        $subSqlChild = $this->table($subSqlChild1)->alias('t1')->group('t1.company_id')->buildSql();

        $subSql = $this->alias('a')->where($map)
            ->join('qz_user u', 'u.id = a.company_id')
            ->join('qz_user_company c', 'c.userid = u.id')
            ->join('qz_user_company_account acc', 'acc.user_id = u.id', 'LEFT')
            ->join("$subSqlChild rd", 'rd.company_id = u.id', 'LEFT')
            ->field([
                    'a.company_id', 'u.cs as city_id', 'c.cooperate_mode', 'c.viptype',
                    'acc.account_amount', 'acc.round_order_number', 'u.classid','rd.price as calc_price'
                ])
            ->group('a.company_id')
            ->buildSql();

        return $this->table($subSql)->alias('t')
            ->field([
                't.city_id',
                'q.manager',
                'q.cname',
                'COUNT(IF(t.cooperate_mode = 1, 1, null)) AS vipone_num',
                'SUM(IF(t.cooperate_mode = 1, t.viptype, 0)) AS vipone_multiple_count',
                'SUM(IF(t.cooperate_mode = 2 , 1, 0)) AS viptwo_multiple_count',
                'COUNT(IF(t.classid = 6, 1, NULL)) AS old_vip_count',
                'COUNT(IF(t.cooperate_mode = 3, 1, null)) AS vipthree_num',
                'COUNT(IF(t.cooperate_mode = 4, 1, null)) AS vipfour_num',
            ])
            ->join('qz_quyu q', 't.city_id=q.cid')
            ->where('t.cooperate_mode', 'in', [1,3])
            ->whereOr(function($query){
                $query->where('t.cooperate_mode', 'IN', [2,4]);
                $query->where(function($query){
                    $query->where('t.round_order_number', '>=', 1);
                    $query->whereOr(function($query){
                        $query->whereColumn('t.account_amount', '>=', 't.calc_price');
                        $query->where('t.calc_price', 'NOT NULL');
                    });
                });
            })
            ->group('t.city_id')
            ->select();
    }

    public function getCompanystanrdContractInfo($company_id = 0, $mode = 0)
    {
        $map[] = ["company_id","EQ",$company_id];
        $map[] = ["cooperate_mode","EQ",$mode];
        $map[] = ["start_time","ELT",date("Y-m-d")];
        $map[] = ["type","IN",[2,8]];

        return $this->where($map)->field("start_time,end_time")->order("id desc")->find();

    }
}