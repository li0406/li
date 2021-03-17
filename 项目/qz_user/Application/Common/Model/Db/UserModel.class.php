<?php
/**
 * 用户表 user
 */

namespace Common\Model\Db;

use Think\Model;

class UserModel extends Model
{
    protected $tableName = "user";

    /**
     * @param [array]$map 查询条件
     * 获取家具商用户所有信息
     */
    public function getJiajuUserInfo($map)
    {
        $field = 't1.id,t1.on,t1.classid,t1.user,t1.tel_safe,t1.tel_safe_chk,t1.mail_safe,t1.mail_safe_chk,t1.wx_unionid,t1.wx_openid,t1.name,t1.sex,t1.tel,t1.mail,t1.sf,t1.cs,t1.qx,t1.qc,t1.jc,t1.dz,t1.cals,t1.cal,t1.qq,t1.logo,t1.login_time,t1.register_time,t1.blocked,t1.remark,t1.check_time,t1.check_score,';

        $field .= 'c.lng,c.lat,c.address,c.contact_user,c.contact_phone,cs.cname as city,
        c.furniture_brand,
        c.sale_range,
        group_concat(r1.type_id) sale_type,
        group_concat(r2.style_id) furniture_style,
        group_concat(r3.level_id) furniture_level,
        group_concat(r4.class_id) furniture_category,';
        $field .= 's.qz_province as province,x.qz_area as area';
        //1.获取用户的基本信息
        $buildSql = $this->where($map)->buildSql();
        //2.查询用户钱包信息和额外信息
        return $this->table($buildSql)->alias('t1')
            ->join('left join qz_jiaju_user_company as c on t1.id = c.company_id')
            ->join('left join qz_jiaju_quyu as cs on t1.cs = cs.cid')
            ->join('left join qz_province as s on t1.sf = s.qz_provinceid')
            ->join('left join qz_area as x on t1.qx = x.qz_areaid')
            ->join('left join qz_jiaju_company_type_relation as r1 on t1.id = r1.company_id')
            ->join('left join qz_jiaju_company_style_relation as r2 on t1.id = r2.company_id')
            ->join('left join qz_jiaju_company_level_relation as r3 on t1.id = r3.company_id')
            ->join('left join qz_jiaju_company_goodsclass_relation r4 on t1.id = r4.company_id')
            ->field($field)
            ->group('t1.id')
            ->find();
    }
}