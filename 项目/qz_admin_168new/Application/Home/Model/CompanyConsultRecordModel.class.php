<?php
/**
 * 公司入驻咨询信息表
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 16:07
 */

namespace Home\Model;

Use Think\Model;

class CompanyConsultRecordModel extends Model
{

    public function insertRecord($data)
    {
        return M("company_consult_record")->add($data);
    }

    public function selectRecord($where, $order = ['a.id' => 'asc'])
    {
        return M("company_consult_record")->alias("a")
            ->field('a.*,b.name dept_name')
            ->join("left join qz_department as b on b.id = a.dept")
            ->where($where)
            ->order($order)
            ->select();
    }

}