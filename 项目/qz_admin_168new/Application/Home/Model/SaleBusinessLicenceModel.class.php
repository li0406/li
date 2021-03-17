<?php

namespace Home\Model;
Use Think\Model;

class SaleBusinessLicenceModel extends Model
{
    /**
     * 获取营业执照列表数量
     * @param  array  $state [description]
     * @return [type]        [description]
     */
    public function getBusinessLicenceListCount($state = array(1),$city,$id,$audit,$begin,$end,$dev_manage,$brand_division,$dev_regiment,$brand_regiment,$cs,$type)
    {
        $map = array(
            "a.state" => array("IN",$state),
            "a.type" => array("NEQ",4)
        );

        if (count($cs) > 0) {
            $map["u.cs"] = array("IN",$cs);
        }

        if (!empty($city)) {
            $map["q.cname"] = array("EQ",$city);
        }

        if (!empty($id)) {
            $map["_complex"] = array(
                "a.company_id" => array("EQ",$id),
                "u.qc" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($audit)) {
            $map["a.state"] = array("EQ",$audit);
        }

        if (!empty($begin) && !empty($end)) {
            $map["a.audit_time"] = array(
                array("EGT",$begin),
                array("ELT",$end)
            );
        }

        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $map["u.on"] = array("EQ",2);
                    break;
                case 2:
                    $map["u.on"] = array("NEQ",2);
                    break;
            }
        }

        $buildSql = M("sale_business_licence")->where($map)->alias("a")
                                 ->join("join qz_user u on u.id = a.company_id")
                                 ->join("join qz_user_company b on b.userid = a.company_id and b.fake = 0")
                                 ->join("join qz_quyu as q on q.cid = u.cs")
                                 ->field("a.*,u.qc,q.cname")
                                 ->order("a.time desc,a.company_id")
                                 ->buildSql();

        if (!empty($dev_manage)) {
            $map1["u.name"] = array("EQ",$dev_manage);
        }

        if (!empty($brand_division)) {
            $map1["u1.name"] = array("EQ",$brand_division);
        }

        if (!empty($dev_regiment)) {
            $map1["u2.name"] = array("EQ",$dev_regiment);
        }

        if (!empty($brand_regiment)) {
            $map1["u3.name"] = array("EQ",$brand_regiment);
        }

        $buildSql = M("sale_business_licence")->table($buildSql)->alias("t1")->where($map1)
                                         ->join("left join qz_sales_city_manage c on c.city = t1.cname")
                                         ->join("left join qz_adminuser u on u.id = c.dev_manage")
                                         ->join("left join qz_adminuser u1 on u1.id = c.brand_manage")
                                         ->join("left join qz_adminuser u3 on u3.id = c.brand_regiment")
                                         ->join("left join qz_adminuser u2 on u2.id = c.dev_regiment")
                                         ->field("t1.*,u.name as dev_manage,u1.name as brand_division,u3.name as brand_regiment,u2.name as dev_regiment")
                                         ->buildSql();

        return M("sale_business_licence")->table($buildSql)->alias("t2")->count();
    }

    /**
     * 获取营业执照列表
     * @param  array  $state [description]
     * @return [type]        [description]
     */
    public function getBusinessLicenceList($state = array(1),$city,$id,$audit,$begin,$end,$dev_manage,$brand_division,$dev_regiment,$brand_regiment,$cs,$type,$pageIndex,$pageCount)
    {
        $map = array(
            "a.state" => array("IN",$state),
            "a.type" => array("NEQ",4)
        );

        if (count($cs) > 0) {
            $map["u.cs"] = array("IN",$cs);
        }

        if (!empty($city)) {
            $map["q.cname"] = array("EQ",$city);
        }

        if (!empty($id)) {
            $map["_complex"] = array(
                "a.company_id" => array("EQ",$id),
                "u.qc" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($audit)) {
            $map["a.state"] = array("EQ",$audit);
        }

        if (!empty($begin) && !empty($end)) {
            $map["a.audit_time"] = array(
                array("EGT",$begin),
                array("ELT",$end)
            );
        }

        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $map["u.on"] = array("EQ",2);
                    break;
                case 2:
                    $map["u.on"] = array("NEQ",2);
                    break;
            }
        }

        $buildSql = M("sale_business_licence")->where($map)->alias("a")
                                         ->join("join qz_user u on u.id = a.company_id")
                                         ->join("join qz_user_company b on b.userid = a.company_id and b.fake = 0")
                                         ->join("join qz_quyu as q on q.cid = u.cs")
                                         ->join("left join qz_sale_business_licence l on l.company_id = a.company_id and l.type = 4 and a.type = 1")
                                         ->field("a.*,u.qc,q.cname,l.img1 as gszj_img")
                                         ->order("u.on desc,a.time desc,u.id")
                                         ->limit($pageIndex.",".$pageCount)
                                         ->buildSql();
        if (!empty($dev_manage)) {
            $map1["u.name"] = array("EQ",$dev_manage);
        }

        if (!empty($brand_division)) {
            $map1["u1.name"] = array("EQ",$brand_division);
        }

        if (!empty($dev_regiment)) {
            $map1["u2.name"] = array("EQ",$dev_regiment);
        }

        if (!empty($brand_regiment)) {
            $map1["u3.name"] = array("EQ",$brand_regiment);
        }

        return  M("sale_business_licence")->table($buildSql)->alias("t")->where($map1)
                                         ->join("left join qz_sales_city_manage c on c.city = t.cname")
                                         ->join("left join qz_adminuser u on u.id = c.dev_manage")
                                         ->join("left join qz_adminuser u1 on u1.id = c.brand_manage")
                                         ->join("left join qz_adminuser u3 on u3.id = c.brand_regiment")
                                         ->join("left join qz_adminuser u2 on u2.id = c.dev_regiment")
                                         ->field("t.*,u.name as dev_manage,u1.name as brand_manage,u3.name as brand_regiment,u2.name as dev_regiment")
                                         ->select();
    }

    /**
     * 删除营业执照上传资料
     * @param  [type] $type [类型]
     * @return [type]       [description]
     */
    public function delBusinessLicence($id,$type)
    {
        $map = array(
            "company_id" => array("EQ",$id)
        );
        if (!empty($type)) {
            if (is_array($type)) {
                $map["type"] = array("IN",$type);
            } else {
                $map["type"] = array("EQ",$type);
            }

        }
        return M("sale_business_licence")->where($map)->delete();
    }

    /**
     * 添加资料
     * @param [type] $data [description]
     */
    public function addBusinessLicence($data)
    {
        return M("sale_business_licence")->add($data);
    }

    public function addAllBusinessLicence($data)
    {
         return M("sale_business_licence")->addAll($data);
    }

    /**
     * 分类别获取资料
     */
    public function findBusinessTypeInfo($id,$type)
    {
        $map["company_id"] = array("EQ",$id);
        $map["type"] = array("EQ",$type);
        return M("sale_business_licence")->where($map)->find();
    }

    /**
     * 编辑资料
     * @param  [type] $id   [装修公司ID]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editBusinessLicence($id,$type,$data)
    {
        $map["company_id"] = array("EQ",$id);
        if (!empty($type)) {
             $map["type"] = array("EQ",$type);
        }
        return M("sale_business_licence")->where($map)->save($data);
    }

    public function getSaleCityCompanyListCount($cs,$user_id,$on,$city_id,$yyzz,$zxzz)
    {
        $map = array(
            "a.classid" => array("EQ",3)
        );

        if (!empty($user_id)) {
            $map["_complex"] = array(
                "a.id" => array("EQ",$user_id),
                "a.qc" => array("LIKE","%$user_id%"),
                "_logic" => "OR"
            );
        }

        if (is_array($cs)) {
            $map["a.cs"] = array(
                array("IN",$cs)
            );
        }

        if (!empty($city_id)) {
            $map["s.city"] = array("EQ",$city_id);
        }

        if (!empty($on)) {
            $map["b.fake"] = array("EQ",0);
        }

        $buildSql = M("user")->where($map)->alias("a")
                 ->join("join qz_user_company b on b.userid = a.id")
                 ->join("join qz_quyu q on q.cid = a.cs")
                 ->join("left join qz_sales_city_manage s on s.city = q.cname")
                 ->field("a.id,a.qc,q.cname,s.dev_manage,s.brand_manage,s.brand_regiment,s.dev_regiment")
                 ->buildSql();

        if (!empty($yyzz)) {
            $sub = array(
                "type" => array("EQ",1)
            );

            switch ($yyzz) {
                case '1':
                    $sub["type"] = array("exp","is null");
                   break;
                case '2':
                    $sub["state"] = array("IN",array(1,2,3));
                   break;
                case '4':
                     $sub["state"] = array("IN",array(4));
                    break;
                case '5':
                     $sub["state"] = array("IN",array(5));
                    break;
            }
            $where["_complex"][] =  $sub;
        }

        if (!empty($zxzz)) {
            $sub = array(
                "type" => array("EQ",3)
            );

            switch ($zxzz) {
                case '1':
                    $sub["type"] = array("exp","is null");
                   break;
                case '2':
                    $sub["state"] = array("IN",array(1,2,3));
                   break;
                case '4':
                    $sub["state"] = array("IN",array(4));
                    break;
                case '5':
                    $sub["state"] = array("IN",array(5));
                    break;
            }
            $where["_complex"][] =  $sub;
        }

        if (count( $where["_complex"]) > 1) {
             $where["_complex"]["_logic"] ="OR";
        }

        $buildSql = M("user")->table($buildSql)->alias("t1")->where($where)
                        ->join("left join qz_sale_business_licence l on l.company_id = t1.id and l.type <> 4")
                        ->field("t1.*,l.type,l.state,l.img1,l.img2,l.img3,l.img4")
                        ->buildSql();
        return  M("user")->table($buildSql)->alias("t")->count();

    }

    /**
     * 查询城市会员数据
     * @param  [type] $cs      [description]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getSaleCityCompanyList($cs,$user_id,$on,$city_id,$yyzz,$zxzz,$pageIndex,$pageCount)
    {
        $map = array(
            "a.classid" => array("EQ",3)
        );

        if (!empty($user_id)) {
            $map["_complex"] = array(
                "a.id" => array("EQ",$user_id),
                "a.qc" => array("LIKE","%$user_id%"),
                "_logic" => "OR"
            );
        }

        if (is_array($cs)) {
            $map["a.cs"] = array(
                array("IN",$cs)
            );
        }

        if (!empty($city_id)) {
            $map["s.city"] = array("EQ",$city_id);
        }

        if (!empty($on)) {
            $map["b.fake"] = array("EQ",0);
        }

        $buildSql = M("user")->where($map)->alias("a")
                 ->join("join qz_user_company b on b.userid = a.id")
                 ->join("join qz_quyu q on q.cid = a.cs")
                 ->join("left join qz_sales_city_manage s on s.city = q.cname")
                 ->field("a.id,a.qc,q.cname,s.dev_manage,s.brand_manage,s.brand_regiment,s.dev_regiment")
                 ->limit($pageIndex.",".$pageCount)
                 ->order("a.on desc,b.fake,a.id")
                 ->buildSql();

        $buildSql = M("user")->table($buildSql)->alias("t")
                         ->join("left join qz_adminuser u on u.id = t.dev_manage")
                         ->join("left join qz_adminuser u1 on u1.id = t.brand_manage")
                         ->join("left join qz_adminuser u3 on u3.id = t.brand_regiment")
                         ->join("left join qz_adminuser u2 on u2.id = t.dev_regiment")
                         ->field("t.id,t.qc,t.cname,u.name as dev_manage,u1.name as brand_manage,u3.name as brand_regiment,u2.name as dev_regiment")->buildSql();

        if (!empty($yyzz)) {
            $sub = array(
                "type" => array("EQ",1)
            );

            switch ($yyzz) {
                case '1':
                    $sub["type"] = array("exp","is null");
                   break;
                case '2':
                    $sub["state"] = array("IN",array(1,2,3));
                   break;
                case '4':
                     $sub["state"] = array("IN",array(4));
                    break;
                case '5':
                     $sub["state"] = array("IN",array(5));
                    break;
            }
            $where["_complex"][] =  $sub;
        }

        if (!empty($zxzz)) {
            $sub = array(
                "type" => array("EQ",3)
            );

            switch ($zxzz) {
                case '1':
                    $sub["type"] = array("exp","is null");
                   break;
                case '2':
                    $sub["state"] = array("IN",array(1,2,3));
                   break;
                case '4':
                    $sub["state"] = array("IN",array(4));
                    break;
                case '5':
                    $sub["state"] = array("IN",array(5));
                    break;
            }
            $where["_complex"][] =  $sub;
        }

        if (count( $where["_complex"]) > 1) {
             $where["_complex"]["_logic"] ="OR";
        }

        return M("user")->table($buildSql)->alias("t1")->where($where)
                        ->join("left join qz_sale_business_licence l on l.company_id = t1.id and l.type <> 4")
                        ->field("t1.*,l.type,l.state,l.img1,l.img2,l.img3,l.img4")
                        ->select();

    }
}