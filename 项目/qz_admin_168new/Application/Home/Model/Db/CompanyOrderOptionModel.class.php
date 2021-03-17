<?php

namespace Home\Model\Db;

use Think\Model;

class CompanyOrderOptionModel extends Model
{
    public function getUserOptionCount($condition = '', $cs = '', $start = '', $end = '')
    {
        $where = [];
        if ($condition) {
            $where["_complex"] = array(
                "u.id" => ["eq", $condition],
                "u.jc" => ["like", '%' . $condition . '%'],
                "_logic" => "OR"
            );
        }
        if ($start && $end) {
            $where['co.start_time'] = ['egt', $start];
            $where['co.end_time'] = ['elt', $end];
        }
        if ($cs) {
            $where['u.cs'] = ['eq', $cs];
        }
        return M("company_order_option")->alias('co')
            ->field('co.id')
            ->join('qz_user u on u.id = co.company_id')
            ->where($where)
            ->count();
    }

    public function getUserOptionList($condition = '', $cs = '', $start = '', $end = '', $pageIndex = 0, $page = 20)
    {
        $where = [];
        if ($condition) {
            $where["_complex"] = array(
                "u.id" => ["eq", $condition],
                "u.jc" => ["like", '%' . $condition . '%'],
                "_logic" => "OR"
            );
        }
        if ($start && $end) {
            $where['co.start_time'] = ['egt', $start];
            $where['co.end_time'] = ['elt', $end];
        }
        if ($cs) {
            $where['u.cs'] = ['eq', $cs];
        }
        return M("company_order_option")->alias('co')
            ->field('co.*,u.jc,u.cs,q.cname')
            ->join('qz_user u on u.id = co.company_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($where)
            ->order('co.id desc')
            ->limit($pageIndex, $page)
            ->select();
    }

    public function getCompanyOrderOptionInfo($id)
    {
        return M('company_order_option')->alias('co')
            ->field('co.*,FROM_UNIXTIME(co.start_time,"%Y-%m-%d") start,FROM_UNIXTIME(co.end_time,"%Y-%m-%d") end,u.jc,u.cs,q.cname')
            ->join('qz_user u on u.id = co.company_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where(['co.id' => ['eq', $id]])
            ->find();
    }

    public function getOptionByCompany($company_id)
    {
        return M('company_order_option')->alias('co')
            ->field('co.id')
            ->where(['co.company_id' => ['eq', $company_id]])
            ->find();
    }

    public function editOptionInfo($id, $data)
    {
        return M('company_order_option')->where(['id' => ['eq', $id]])->save($data);
    }

    public function addInfo($post)
    {
        return M('company_order_option')->add($post);
    }

    public function delInfo($id)
    {
        return M('company_order_option')->where(['id' => ['eq', $id]])->delete();
    }
}