<?php
//ERP相关模块
namespace Home\Model\Logic;

class AuthLogicModel
{
    var $citys = null;
    var $quyu  = null;

    public function getCityAndAreaInfo(){
        $this->citys = S("Cache:168new:Area");
        if(!$this->citys){
            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $list = D('Home/Db/Auth')->getAllCityInfo();
            if(count($list)>0){
                $citys = array();
                foreach ($list as $key => $value) {
                    if(!array_key_exists($value['cid'], $citys)){
                        $citys[$value['cid']] = array();
                        $citys[$value['cid']]["cid"] = $value["cid"];
                        //会员数量
                        $citys[$value['cid']]["usercount"] = $value["usercount"];
                        //增加首字母大写
                        $str =  substr( ucfirst($value["px_abc"]) , 0, 1);
                        if (empty($str)) {
                            $str = $app->getFirstCharter($value["cname"]);
                        }

                        $citys[$value['cid']]["key"]          = $str;
                        $citys[$value['cid']]["bm"]           = $value["bm"];
                        $citys[$value['cid']]["uid"]          = $value["uid"];
                        $citys[$value['cid']]["cname"]        = $str." ".$value["cname"];
                        $citys[$value['cid']]["oldName"]      = $value["cname"];
                        $citys[$value['cid']]["province"]     = $value["qz_province"];
                        $citys[$value['cid']]["bigpart"]      = $value["qz_bigpart"];
                        $citys[$value['cid']]["bigpart_name"] = $value["qz_bigpart_name"];
                        $citys[$value['cid']]["px"]           = $value["px"];
                        $citys[$value['cid']]["type"]         = $value["type"];
                        $citys[$value['cid']]["parent_city"]  = $value["parent_city"];
                        $citys[$value['cid']]["parent_city1"] = $value["parent_city1"];
                        $citys[$value['cid']]["parent_city2"] = $value["parent_city2"];
                        $citys[$value['cid']]["parent_city3"] = $value["parent_city3"];
                        $citys[$value['cid']]["parent_city4"] = $value["parent_city4"];
                        $citys[$value['cid']]["lng"] = $value["lng"];
                        $citys[$value['cid']]["lat"] = $value["lat"];
                        $citys[$value['cid']]["child"]        = array();
                    }

                    $str = $app->getFirstCharter($value["qz_area"]);
                    $quyu = array(
                        "key"=>$str,
                        "oldName" =>$value["qz_area"],
                        "qz_areaid"=>$value["qz_areaid"],
                        "qz_area" =>$str." ".$value["qz_area"],
                        "orders" => $value["orders"],
                    );
                    $citys[$value['cid']]["child"][]= $quyu;
                }
                $edition = array();
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition[] = $value["key"];
                }
                array_multisort($edition, SORT_ASC,SORT_STRING,$citys);
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition = array();
                    foreach ($value["child"] as $k => $v) {
                        $edition[] = $v["key"];
                    }
                    array_multisort($edition, SORT_ASC, $citys[$key]["child"]);
                }
                //因为排序,重新替换数组的键
                foreach ($citys as $key => $value) {
                    $citys[$value["cid"]] = $value;
                    unset($citys[$key]);
                }
                $this->citys = $citys;
                S("Cache:168new:Area",$citys,7200);
            }
        }
    }
    public function getCityAndAreaInfoByAdminId($admin_id){
        if(!$this->citys){
            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $list = D('Home/Db/Auth')->getAllCityInfo($admin_id);
            if(count($list)>0){
                $citys = array();
                foreach ($list as $key => $value) {
                    if(!array_key_exists($value['cid'], $citys)){
                        $citys[$value['cid']] = array();
                        $citys[$value['cid']]["cid"] = $value["cid"];
                        //会员数量
                        $citys[$value['cid']]["usercount"] = $value["usercount"];
                        //增加首字母大写
                        $str =  substr( ucfirst($value["px_abc"]) , 0, 1);
                        if (empty($str)) {
                            $str = $app->getFirstCharter($value["cname"]);
                        }

                        $citys[$value['cid']]["key"]          = $str;
                        $citys[$value['cid']]["bm"]           = $value["bm"];
                        $citys[$value['cid']]["uid"]          = $value["uid"];
                        $citys[$value['cid']]["cname"]        = $str." ".$value["cname"];
                        $citys[$value['cid']]["oldName"]      = $value["cname"];
                        $citys[$value['cid']]["province"]     = $value["qz_province"];
                        $citys[$value['cid']]["bigpart"]      = $value["qz_bigpart"];
                        $citys[$value['cid']]["bigpart_name"] = $value["qz_bigpart_name"];
                        $citys[$value['cid']]["px"]           = $value["px"];
                        $citys[$value['cid']]["type"]         = $value["type"];
                        $citys[$value['cid']]["parent_city"]  = $value["parent_city"];
                        $citys[$value['cid']]["parent_city1"] = $value["parent_city1"];
                        $citys[$value['cid']]["parent_city2"] = $value["parent_city2"];
                        $citys[$value['cid']]["parent_city3"] = $value["parent_city3"];
                        $citys[$value['cid']]["parent_city4"] = $value["parent_city4"];
                        $citys[$value['cid']]["lng"] = $value["lng"];
                        $citys[$value['cid']]["lat"] = $value["lat"];
                        $citys[$value['cid']]["child"]        = array();
                    }

                    $str = $app->getFirstCharter($value["qz_area"]);
                    $quyu = array(
                        "key"=>$str,
                        "oldName" =>$value["qz_area"],
                        "qz_areaid"=>$value["qz_areaid"],
                        "qz_area" =>$str." ".$value["qz_area"],
                        "orders" => $value["orders"],
                    );
                    $citys[$value['cid']]["child"][]= $quyu;
                }
                $edition = array();
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition[] = $value["key"];
                }
                array_multisort($edition, SORT_ASC,SORT_STRING,$citys);
                foreach ($citys as $key => $value) {
                    // 准备要排序的数组
                    $edition = array();
                    foreach ($value["child"] as $k => $v) {
                        $edition[] = $v["key"];
                    }
                    array_multisort($edition, SORT_ASC, $citys[$key]["child"]);
                }
                //因为排序,重新替换数组的键
                foreach ($citys as $key => $value) {
                    $citys[$value["cid"]] = $value;
                    unset($citys[$key]);
                }
                $this->citys = $citys;
            }
        }
    }

    /**
     * 获取数组形式的城市信息
     * @param  string $cs 城市编号
     * @param  string $all 是否选择全部城市
     * @param  string $filter 是否过滤会员是0的城市
     * @return mixed
     */
    public function getCityArray($cs, $all = true, $admin_id = '')
    {
        if (!empty($admin_id)) {
            $this->getCityAndAreaInfoByAdminId($admin_id);
        } else {
            $this->getCityAndAreaInfo();
        }
        $citys = array();
        //如果有城市编号
        if (!empty($cs)) {
            $allCity[] = $this->citys[$cs];
        } else {
            $allCity = $this->citys;
        }

        foreach ($allCity as $key => $value) {
            if ($value['type'] == 1) {
                $shen = array(
                    'id' => $value['cid'],
                    'oldname' => $value['oldName'],
                    'cname' => $value['cname']
                );
                $citys['shen'][] = $shen;
            } else {
                if ($all) {
                    $shen = array(
                        'id' => $value['cid'],
                        'oldname' => $value['oldName'],
                        'cname' => $value['cname']
                    );
                    $citys['shen'][] = $shen;
                }
            }

            // 准备要排序的数组
            $edition = array();
            foreach ($value['child'] as $k => $v) {
                $edition[] = $v['orders'];
            }
            array_multisort($edition, SORT_ASC, $value['child']);
            $citys['shi'][$value['cid']] = $value['child'];
        }

        return json_encode($citys);
    }

    public function getCitysByAminId($admin_id){
        if (empty($admin_id)) {
            return false;
        }
        $map_kf = array(
            "id"=>array("EQ",$admin_id)
        );
        $result_kf = M("adminuser")->where($map_kf)->find();
        if (!empty($result_kf['cs'])) {
            $cs = explode(',',trim($result_kf['cs'], ','));
            return $cs;
        }else{
            return [];
        }

    }
    public function getCommunityWuyeType(){
        $result = D('Home/Db/Auth')->getCommunityWuyeType();
        foreach($result as $val){
            $wuye_type[$val["id"]] = $val["name"];
        }
        return $wuye_type;
    }

    public function getCommunity($where, $count)
    {

        //强制数字整数
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D('Home/Db/Auth')->getCommunity($where,$p->firstRow, $p->listRows);

            $list =  $this->getWuyeTypeInfo($list);
            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    public function getCommunityNew($where)
    {
        $list = D('Home/Db/Auth')->getCommunityNew($where);
        $list =  $this->getWuyeTypeInfo($list);
        //处理续费数据
        return $list;
    }


    public function getWuyeTypeInfo($list){

        //全部物业类型
        $wuye_type = $this->getCommunityWuyeType();

        if(isset($list)&&!empty($list)){
            foreach($list as $key=>$val){

                if(mb_strlen($val['info'],'utf-8')>10){
                    $list[$key]["info_new"] = mb_substr($val['info'],0,10,'utf-8').'...';
                }else{
                    $list[$key]["info_new"] = $val['info'];
                }

                if(mb_strlen($val['address'],'utf-8')>10){
                    $list[$key]["address_new"] =   mb_substr($val['address'],0,10,'utf-8').'...';
                }else{
                    $list[$key]["address_new"] =  $val['address'];
                }

                if(mb_strlen($val['management'],'utf-8')>10){
                    $list[$key]["management_new"] =  mb_substr($val['management'],0,10,'utf-8').'...';
                }else{
                    $list[$key]["management_new"] =  $val['management'];
                }

                if(mb_strlen($val['school'],'utf-8')>10){
                    $list[$key]["school_new"] =  mb_substr($val['school'],0,10,'utf-8').'...';
                }else{
                    $list[$key]["school_new"] =  $val['school'];
                }

                if(mb_strlen($val['producer'],'utf-8')>10){
                    $list[$key]["producer_new"] = mb_substr($val['producer'],0,10,'utf-8').'...';
                }else{
                    $list[$key]["producer_new"] = $val['producer'];
                }

                if(mb_strlen($val['name'],'utf-8')>10){
                    $list[$key]["name_new"] = mb_substr($val['name'],0,10,'utf-8').'...';
                }else{
                    $list[$key]["name_new"] = $val['name'];
                }

                if(!empty($val['wuye_type'])&&isset($val['wuye_type'])){
                    $wuye[$key] = explode(',',$val['wuye_type']);
                    foreach($wuye[$key] as $val){
                        $wuyename[$key][] = $wuye_type[$val];
                    }
                    $list[$key]['wuyename'] = implode(',',$wuyename[$key]);
                }

            }
            return $list;
        }

    }

    public function getCommunityCount($where)
    {
        return D('Home/Db/Auth')->getCommunityCount($where);
    }


    public function selectCommunity($xiaoqu,$city,$id){
        return D('Home/Db/Auth')->selectCommunity($xiaoqu,$city,$id);
    }


    public function addCommunity($data){
        return D('Home/Db/Auth')->addCommunity($data);
    }

    public function editCommunity($data,$id){
        return D('Home/Db/Auth')->editCommunity($data,$id);
    }

    public function editRead($ids){
        return D('Home/Db/Auth')->editRead($ids);
    }

    public function getOneArea($id){
        $info = D('Home/Db/Auth')->getOneArea($id);
        //全部物业类型
        $wuye_type = $this->getCommunityWuyeType();
        if(!empty($info['wuye_type'])&&isset($info['wuye_type'])){
            $wuye = explode(',',$info['wuye_type']);
            foreach($wuye as $val){
                $wuyename[] = $wuye_type[$val];
            }
            $info['wuyename'] = implode(',',$wuyename);
        }

        $info['houses'] = empty($info['houses'])?'':$info['houses'];
        $info['year'] = empty($info['year'])?'':$info['year'];
        $info['parking'] = empty($info['parking'])?'':$info['parking'];

        $info['listr'] = $this->getLiStr($wuye_type,$info['wuye_type']);
        return $info;
    }

    /**
     * 获取多选框赋值
     * @param $info
     * @param $type
     */
    public function getLiStr($wuye_type,$type){
        $type = explode(',',$type);
        $html = '';
        foreach($wuye_type as $key=>$val){
            if(in_array($key,$type)){
                $html .= '<li><input class="wuyetype" checked type="checkbox"  value="'.$val.'" data-id="'.$key.'"/>'.$val.'</li>';
            }else{
                $html .= '<li><input class="wuyetype" type="checkbox"  value="'.$val.'" data-id="'.$key.'"/>'.$val.'</li>';
            }
        }
        return $html;
    }

    /**
     * 通过城市获取小区
     * @param $areaid
     */
    public function getcommunitybycity($areaid,$xiaoqu=''){
        $info = D('Home/Db/Auth')->getcommunitybycity($areaid,$xiaoqu);
       $html = '';
        foreach($info as $val){
            $str = '';
            if(!empty(floatval($val["longitude"]))&&!empty(floatval($val["latitude"]))){
                $str = $val["longitude"].",".$val["latitude"];
            }

            $html .=  "<li data-type='".$val["type"]."' data-jw='".$str."' >".$val["name"]."</li>";
        }
        return $html;
    }

    public function getExistXiaoqu($qx,$xiaoqu){
        return D('Home/Db/Auth')-> selectCommunity($xiaoqu,$qx);
    }

    public function addCommunityfromOrder($city,$area,$xiaoqu, $lng,$lat,$xiaoqutype){
        $data["city"] = $city;
        $data["area"] = $area;
        $data["name"] = $xiaoqu;
        $data["longitude"] = $lng;
        $data["latitude"] = $lat;
        $data["add_time"] = time();
        $data["type"] = $xiaoqutype;
        return D('Home/Db/Auth')->addCommunity($data);
    }

    public function deleteArea($id){
        return D('Home/Db/Auth')->deleteArea($id);
    }

}
