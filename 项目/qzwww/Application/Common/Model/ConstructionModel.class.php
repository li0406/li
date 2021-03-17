<?php
/**
 *  装修施工
 */
namespace Common\Model;
use Think\Model;
class ConstructionModel extends Model{
    protected $tableName = 'construction_location';
    var $location = null;//装修位置信息
    var $details = null;//装修详细信息
    var $constructionprice = null;// 装修价格信息
    var $materials = null;//装修建材
    public function _initialize(){
        //获取装修位置
        $this->location = S("Cache:Constructionlocation");
        if(!$this->location){
            $result =  M("construction_location")->field("id,name,jc,orders")->order("orders")->select();
            if(count($result)>0){
                $this->location =  $result;
                S("Cache:Constructionlocation",$result,3600*24);
            }
        }
        //获取装修施工明细
        $this->details = S("Cache:Constructiondetails");
        if(!$this->details){
            $result =  M("construction_details")->field("id,name,location,range,units,fangshi,parentid,remarks")->select();
            if(count($result)>0){
                $this->details =  $result;
                S("Cache:Constructiondetails",$result,3600*24);
            }
        }

        //获取建材信息
        $this->materials = S("Cache:Constructionmaterials");
        if(!$this->materials){
            $result =  M("construction_materials")->select();
            if(count($result)>0){
                $this->materials =  $result;
                S("Cache:Constructionmaterials",$result,3600*24);
            }
        }
    }

    /**
    * 获取施工位置信息
    * @return [type] [description]
    */
    public function getLocation(){
        $location = $this->location;
        return $location;
    }

    /**
     * 获取施工项目明细
     * @return [type] [description]
     */
    public function getDetails(){
        $details = $this->details;
        return $details;
    }

     /**
     * 获取施工价格明细
     * @return [type] [description]
     */
    public function getPrices(){
        $prices = $this->constructionprice;
        return $prices;
    }

    /**
     * 添加到装修报价列表     *
     * @param [type] $id [订单ID]
     * @param [type] $data [装修报价数据]
     * result  true 成功  false  失败
     */
    public function setConstructionpriceList($orderid,$data){
        $m = M("construction_price_list");
        //查询订单记录是否存在
        $map = array(
                    "orderid"=>$orderid
                     );
        $count =  $m->where($map)->count();
        $m->startTrans();
        if($count > 0){
            //订单记录存在,删除原有记录
            $m->where($map)->delete();
        }

        $index = 0;
        foreach ($data as $key => $value) {
            $subdata = array(
                    "orderid"     =>trim($orderid),
                    "width"   =>$value["width"],
                    "length"   =>$value["length"],
                    "xm"       =>$value["xm"],
                    "zxdc"     =>$value["zxdc"],
                    "fengge"   =>$value['fengge'],
                    "location" =>$value["location"],
                    "time"     =>time(),
                    "total"    =>$value["total"],
                    "range"    =>$value["range"]
                );
            $n =  $m->add($subdata);
            if($n != -1){
                $index ++;
            }
        }

        if($index == count($data)){
            $m->commit();
            return true;
        }else{
            $m->rollback();
        }
        return false;
    }

    /**
     * 根据简称获取位置信息
     * @param  [type] $str [简称]
     * @return [type]      [description]
     */
    public function getIDByJc($str){
        $location =  $this->location;
        foreach ($location as $key => $value) {
            if($str ==$value["jc"]){
                return $value;
            }
        }
        return null;
    }

    /**
     * 获取建材信息
     * @return [type] [description]
     */
    public function getMaterials(){
        return $this->materials;
    }

    /**
     * 保存自主建材
     */
    public function setMaterials($id,$data){
        $m = M("construction_materials_list");
        //查询订单记录是否存在
        $map = array(
                    "orderid"=>$id
                     );
        $count =  $m->where($map)->count();

        $m->startTrans();
        if($count > 0){
            //订单记录存在,删除原有记录
            $m->where($map)->delete();
        }
        // echo $m->where($map)->count();
        $index = 0;
        foreach ($data as $key => $value) {
            $subdata = array(
                    'orderid'=>$value["orderid"],
                    'location'=>$value["location"],
                    'mid'=>$value["id"],
                    'total'=>$value["total"],
                    'count'=>$value["count"],
                    "time"=>time(),
                    "width"=>$value["width"],
                    "length"=>$value["len"]
                             );
           $n =  $m->add($subdata);
           if($n != -1){
                $index ++;
           }
        }

        if($index == count($data)){
            $m->commit();
            return true;
        }else{
            $m->rollback();
        }
        return false;
    }

    /**
     * 获取订单装修施工清单
     * @param  [type] $orderid [订单编号]
     * @return [type]          [description]
     */
    public function getconstructionpricelist($orderid){
        $m = M("construction_price_list");
        $map = array(
                "orderid"=>$orderid
                     );
        $order = $m->where($map)->select();
        if(count($order)>0){
            return $order;
        }
        return array();
    }

    /**
     * 根据编号返回详细信息
     * @return [type] [description]
     */
    public function getDetailsInfoByid($id){
        $details = array();
        foreach ($this->details as $key => $value) {
            if($value["id"] == $id){
                $details = $value;
            }
        }
        return $details;
    }

    /**
     * 获取自购主材清单
     * @return [type] [description]
     */
    public function getMaterialsInfo($orderid){
        $m = M("construction_materials_list");
        $map = array(
                "orderid"=>$orderid
                     );
        $materials = $m->where($map)->select();
        if(count($materials)>0){
            return $materials;
        }
        return array();
    }

    /**
     * 保存订单报价列表
     */
    public function addAllPriceList($data){
        return M("construction_price_list")->addAll($data);
    }

    /**
     * 获取报价列表
     * @param  [type] $orderid [订单编号]
     * @return [type]          [description]
     */
    public function getOrderPriceListCount($orderid){
        $map = array(
                "orderid"=>array("EQ",$orderid)
                     );
        return M("construction_price_list")->where($map)->count();
    }

   /**
    * 获取订单报价列表
    * @param  [type] $orderid [订单编号]
    * @return [type]          [description]
    */
    public function getOrderPriceList($orderid){
        $map = array(
                "orderid"=>array("EQ",$orderid)
                     );
        return M("construction_price_list")->where($map)->order("`range` asc")->select();
    }



    /**
     * 删除报价列表
     */
    public function DeltetOrderPriceList($orderid){
        $map = array(
                "orderid"=>array("EQ",$orderid)
                     );
        return M("construction_price_list")->where($map)->delete();
    }

    /**
     * 根据城市的编号获取当前城市的装修报价价格
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getConstructionPriceGroupByCs($cs){
        $map = array(
            "cid"=>array("EQ",$cs)
                     );
        return M("construction_city_price")->where($map)->find();
    }

    /**
     * 根据群组名称查询价格组的详细信息
     * @param  [type] $group [description]
     * @return [type]        [description]
     */
    public function getConstructionPriceByGroup($group){
        if(empty($group)){
            $group = "other";
        }
        //获取装修施工项目价格
        $constructionprice = S("Cache:Constructionprice:".$group);
        if(!$constructionprice){
            $map = array(
                "group"=>array("EQ",$group),
                "istop"=>array("EQ",1),
                "_logic"=>"OR"
                     );

            $result =  M("construction_price")->where($map)->field("`id`, `zxdc`,`group`, `location`, `xm`, `length`, `width`, `count`, `price`")->select();
            if(count($result)>0){
                foreach ($result as $key => $value) {
                    if(!empty($value["location"])){
                        $constructionprice[$value["location"]][$value["xm"]] = $value;
                    }else{
                        $constructionprice["other"][$value["xm"]] = $value;
                    }
                }
                S("Cache:Constructionprice:".$group,$constructionprice,3600*24);
            }
        }
        return  $constructionprice;
    }
}