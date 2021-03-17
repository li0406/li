<?php
// +----------------------------------------------------------------------
// | AuditOrderModel 质检订单模型
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class AuditCitysModel extends Model
{
    protected $autoCheckFields  =   false;

    /**
     * 获取质检部门所有城市
     *
     * @param bool $with_cname
     * @return mixed
     */
    public function zhijianCitys($with_cname = false, $with_vip_num = false, $having = false)
    {
        $buildSql = M('sales_quyu')->field('cs')->where(['to' => ['eq', 'jihebu']])->buildSql();
        $result =  M('quyu')->alias('a')
            ->where('a.is_open_city = 1 and a.cid not in '.$buildSql);

        if ($with_vip_num === true) {
            $userBuildSql = M('user')
                ->alias('u')
                ->field('u.*,c.fake')
                ->join('join qz_user_company c on c.userid = u.id and c.fake = 0 and u.`on` = 2 and u.classid = 3')
                ->buildSql();
            $result = $result->join('left join '.$userBuildSql.' u on u.cs = a.cid')
                ->group('a.cid');
            if ($having) {
                $result = $result->having($having);
            }
            return $result->field('a.cid,upper(left(a.px_abc,1)) px_abc,a.cname,count(u.id) vip_num')->order('px_abc asc,vip_num desc')->select();
        }
        if ($with_cname === true) {
            return $result->field('a.cid,upper(left(a.px_abc,1)) px_abc,a.cname')->order('a.px_abc asc,a.cid asc')->select();
        } else {
            return $result->getField('cs', true);
        }
    }

    /**
     * 获取质检人员每个人管辖城市
     *
     * @param $map
     * @return mixed
     */
    public function getZjRolerCitys($map)
    {
        return M('admin_audit_citys')->alias('a')
            ->join('inner join qz_quyu q on q.cid = a.cid')
            ->where($map)
            ->field('a.admin_id,q.cid,q.cname')
            ->select();
    }

    public function delAuditCitys($map)
    {
        return M('admin_audit_citys')->where($map)->delete();
    }

    public function addMuchAuditCitys($datas)
    {
        return M('admin_audit_citys')->addAll($datas);
    }
}