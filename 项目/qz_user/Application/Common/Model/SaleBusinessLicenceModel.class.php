<?php

namespace Common\Model;
use Think\Model;

class SaleBusinessLicenceModel extends Model
{
    /**
     * 查询装修公司营业执照是否已上传
     * @param  [type] $id [装修公司ID]
     * @return [type]     [description]
     */
    public function findCompanyBusinessLicenceCount($id,$state = array(1,2,3,4))
    {
        $map = array(
            "state" => array("IN",$state),
            "company_id" => array("EQ",$id),
            "type" => array("EQ",1)
        );
        return M("sale_business_licence")->where($map)->count();
    }


    /**
     * 查询装修公司营业执照是否已上传
     * @param  [type] $id [装修公司ID]
     * @return [type]     [description]
     */
    public function findCompanyBusinessLicenceInfo($id)
    {
        $map = array(
            "company_id" => array("EQ",$id),
            "type" => array("NEQ",4)
        );
        return M("sale_business_licence")->where($map)->select();
    }

    /**
     * 删除审核图片
     * @param  [type] $id [装修公司ID]
     * @return [type]     [description]
     */
    public function delBusinessLicence($id,$type)
    {
        $map = array(
            "company_id" => array("EQ",$id)
        );
        if (!empty($type)) {
            $map["type"] = array("EQ",$type);
        }
        return M("sale_business_licence")->where($map)->delete();
    }

    /**
     * 添加审核图片
     * @param  [type] $id [装修公司ID]
     * @return [type]     [description]
     */
    public function addBusinessLicence($data)
    {
         return M("sale_business_licence")->addAll($data);
    }
}