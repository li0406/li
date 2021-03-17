<?php

namespace app\common\model\db;

use app\index\enum\OrderCodeEnum;
use think\db\Where;
use think\Model;

class Company extends Model
{
    protected $table = 'qz_user';

    public function getCompanyCount($map)
    {
        if (empty($map)) {
            return 0;
        }
        $company = !empty($map['company']) ? $map['company'] : '';
        unset($map['company']);
        return $this->alias('u')
            ->join('qz_user_company c','c.userid = u.id')
            ->where(new Where($map))
            ->where(function ($q) use ($company) {
                if(!empty($company)){
                    $where = [
                        ['u.qc', 'like', '%' . $company . '%'],
                        ['u.id', '=', $company]
                    ];
                    $q->whereOr($where);
                }
            })
            ->count(1);
    }

    public function getCompanyList($map,$page = 0,$pageCount = 6)
    {
        if (empty($map)) {
            return 0;
        }
        $company = !empty($map['company']) ? $map['company'] : '';
        unset($map['company']);
        $buildSql = $this->alias('u')
            ->field('u.id,u.qc company_name,u.cs,floor((UNIX_TIMESTAMP(`u`.`end`)-UNIX_TIMESTAMP(now()))/(60 *60* 24) + 1) surplus_day,floor((UNIX_TIMESTAMP(`u`.`end`)-UNIX_TIMESTAMP(`u`.`start`))/(60 *60* 24) + 1) vip_day,u.start,u.end')
            ->join('qz_user_company c','c.userid = u.id')
            ->where(new Where($map))
            ->where(function ($q) use ($company) {
                if(!empty($company)){
                    $where = [
                        ['u.qc', 'like', '%' . $company . '%'],
                        ['u.id', '=', $company]
                    ];
                    $q->whereOr($where);
                }
            })
            ->order('surplus_day,id')
            ->limit($page,$pageCount)
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->field('t.*,q.cname')
            ->join('qz_quyu q','q.cid = t.cs')
            ->select();
    }

    /**
     * 获取城市最近30天过期公司
     * @param $cs
     * @param $end
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPastCompany($cs, $end = '')
    {
        $begin = date("Y-m-d", strtotime("-30 day", strtotime($end)));
        $map = array(
            "a.cs" => array("EQ", $cs),
            "a.end" => array(
                array("EGT", $begin),
                array("ELT", $end)
            ),
            "a.on" => array("EQ", -1)
        );
        return $this->where(new Where($map))->alias("a")
            ->join("qz_user_company b ", " a.id = b.userid and b.fake = 0")
            ->field("a.jc,a.id,a.end")
            ->select();
    }

    /**
     * 获取装修公司详细信息
     * @return [type] [description]
     */
    public function getCompanyDetailsList($cs, $on = 2)
    {
        $map = array(
            "a.classid" => array("EQ", 3)
        );

        if (!empty($cs)) {
            $map['a.cs'] = array('IN', $cs);
        }

        if (!empty($on)) {
            $map["a.on"] = array("EQ", $on);
        }
        return $this->where(new Where($map))->alias("a")
            ->join("qz_user_company b ", " a.id = b.userid and b.fake = 0")
            ->join("qz_quyu q ", " q.cid = a.cs")
            ->join("qz_area d ", " d.qz_areaid = a.qx")
            ->field("a.id,a.classid,a.jc,a.start,a.end,a.cs,a.qx,q.cname,d.qz_area,b.viptype,b.get_gift_order,b.lx,b.other_id,b.lng,b.lat,b.mianji,b.lxs,b.cooperation_type")
            ->group("a.id")
            ->select();
    }

    public function getUserInfo($id, $field = '*', $with = [])
    {
        if (is_array($id)) {
            $where[] = ['id', 'in', $id];
        } else {
            $where[] = ['id', '=', $id];
        }
        return $this->field($field)->where($where)->with($with)->select();
    }

    public function findUserInfo($id)
    {
        return $this->where("id", $id)->find();
    }

    /**
     * 根据条件获取用户信息
     * @param string $map
     * @param string $field
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserInfoByMap($map = "", $field = "*")
    {
        return $this->where($map)->field($field)->find();

    }


    public function addUserInfo($adddata)
    {
        return $this->insertGetId($adddata);
    }


}
