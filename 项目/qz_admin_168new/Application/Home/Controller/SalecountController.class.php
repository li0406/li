<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class SalecountController extends HomeBaseController{

    //构造
    public function _initialize(){
        parent::_initialize();
    }
    //手工处理排序问题，不对外开放的操作
    public function initCityOrder(){
        //查询所有的城市，把城市ID写入qz_sales_city_paixu，城市在各模块的默认排序都为0
        $citys = D('Salecount')->setCityOrder();

    }


    /*
     * 新销售：城市会员合作时长
     * 
    */
    public function cityVips()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Salecount')->getAdminsByPosition();
        //$adminArr = D('Salecount')->getAdminsByPosition();
        //var_dump($brand);

        foreach ($brand as $k => $v) {
            foreach ($v as $key => $val) {
                $managers[$k][$val['id']]['id'] = $val['id']; 
                $managers[$k][$val['id']]['name'] = $val['name']; 
                $managers[$k][$val['id']]['uid'] = $val['uid']; 
            }
        }
        //搜索关键词 
        $keyword['city']            = I("get.city");
        $keyword['department']      = I("get.department");
        $keyword['time']            = I("get.time");
        $keyword['tshizhang']       = I("get.tshizhang");
        $keyword['ttuanzhang']      = I("get.ttuanzhang");
        $keyword['csjl']            = I("get.csjl");
        $keyword['pshizhang']       = I("get.pshizhang");
        $keyword['ptuanzhang']      = I("get.ptuanzhang");
        $keyword['pinpai']          = I("get.pinpai");

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
        }

        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 20;
        $time_now = date("Y-m",time());
        if($time_now != $keyword['time'] && $keyword['time'] != ''){
            //查询qz_sale_cityvips  type = 1
            //强制数字整数
            $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
            $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
            $list['list'] = D('Salecount')->getHistoryCityVip(($pageIndex-1)*$pageCount,$pageCount, $keyword, 1);
            foreach ($list['list'] as $k => $v) {
                if($v['dept'] == 1){
                    $list['list'][$k]['department'] = '商务';
                }else{
                    $list['list'][$k]['department'] = '外销';
                }
                foreach ($brand['brand_division'] as $key => $value) {
                    if($value['id'] == $v['brand_division']){
                        $list['list'][$k]['brand_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_regiment'] as $key => $value) {
                    if($value['id'] == $v['brand_regiment']){
                        $list['list'][$k]['brand_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_manage'] as $key => $value) {
                    if($value['id'] == $v['brand_manage']){
                        $list['list'][$k]['brand_manage_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_division'] as $key => $value) {
                    if($value['id'] == $v['dev_division']){
                        $list['list'][$k]['dev_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_regiment'] as $key => $value) {
                    if($value['id'] == $v['dev_regiment']){
                        $list['list'][$k]['dev_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_manage'] as $key => $value) {
                    if($value['id'] == $v['dev_manage']){
                        $list['list'][$k]['dev_manage_name'] = $value['name'];
                    }
                }
                //占比
                $list['list'][$k]['gtyear_percent'] = (number_format($v['gtyear_percent'],1,'.','')).'%';
                $list['list'][$k]['gthalfyear_percent'] = (number_format($v['gthalfyear_percent'],1,'.','')).'%';
                $list['list'][$k]['lthalfyear_percent'] = (number_format($v['lthalfyear_percent'],1,'.','')).'%';
                $list['list'][$k]['ltmonth_percent'] = (number_format($v['ltmonth_percent'],1,'.','')).'%';
            }
            $count = D('Salecount')->getHistoryVipNum($keyword, 1);
            if($count > $pagecount){
                import('Library.Org.Util.Page');
                $page = new \Page($count,$pageCount);
                $list['page'] =  $page->show();
            }
            $list['totalnum'] = $count;
            //查询所有,导出用
            $all = D('Salecount')->getHistoryVipAll($keyword, 1);
            foreach ($all as $k => $v) {
                if($v['dept'] == 1){
                    $all[$k]['department'] = '商务';
                }else{
                    $all[$k]['department'] = '外销';
                }
                foreach ($brand['brand_division'] as $key => $value) {
                    if($value['id'] == $v['brand_division']){
                        $all[$k]['brand_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_regiment'] as $key => $value) {
                    if($value['id'] == $v['brand_regiment']){
                        $all[$k]['brand_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_manage'] as $key => $value) {
                    if($value['id'] == $v['brand_manage']){
                        $all[$k]['brand_manage_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_division'] as $key => $value) {
                    if($value['id'] == $v['dev_division']){
                        $all[$k]['dev_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_regiment'] as $key => $value) {
                    if($value['id'] == $v['dev_regiment']){
                        $all[$k]['dev_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_manage'] as $key => $value) {
                    if($value['id'] == $v['dev_manage']){
                        $all[$k]['dev_manage_name'] = $value['name'];
                    }
                }
            }
            S("Cache:AllCityVipConn",$all,15*60);
            
        }else{
            //当月则查询实时
            $list = $this->getCityVipConn($pageIndex, $pageCount, $sort, $keyword);
            //查询所有,导出用
            $all = D('Salecount')->getCityVipConn($keyword,'b.px2 AS px');
            S("Cache:AllCityVipConn",$all,15*60);
        }
        $keyword['city'] = I("get.city");
        //添加合计、总计
        foreach ($all as $k => $v) {
            $total['department'] = '本项合计';// 部门  
            $total['city'] = '-';// 城市  
            $total['brand_division_name'] = '-';// 品师长     
            $total['brand_regiment_name'] = '-';// 品团长     
            $total['brand_manage_name'] = '-';// 品牌师     
            $total['dev_division_name'] = '-';// 拓师长     
            $total['dev_regiment_name'] = '-';// 拓团长     
            $total['dev_manage_name'] = '-';// 城市经理    
            $total['realvipnum'] += $v['realvipnum'];// 实际总会员数  
            $total['gtyear'] += $v['gtyear'];// 会员数≥1年
            $total['gthalfyear'] += $v['gthalfyear'];// 1年＞会员数≥0.5年 
            $total['lthalfyear'] += $v['lthalfyear'];// 0.5年＞会员数≥0.25年 
            $total['ltmonth'] += $v['ltmonth'];// 会员数＜0.25年
        }
        $total['gtyear_percent'] = ($total['gtyear']/$total['realvipnum'])*100;//≥1年占比 
        $total['gtyear_percent'] = (number_format($total['gtyear_percent'],1,'.','')).'%';
        $total['gthalfyear_percent'] = ($total['gthalfyear']/$total['realvipnum'])*100;// 1年＞会员数≥0.5年占比
        $total['gthalfyear_percent'] = (number_format($total['gthalfyear_percent'],1,'.','')).'%';
        $total['lthalfyear_percent'] = ($total['lthalfyear']/$total['realvipnum'])*100;//0.5年＞会员数≥0.25年占比
        $total['lthalfyear_percent'] = (number_format($total['lthalfyear_percent'],1,'.','')).'%';
        $total['ltmonth_percent'] = ($total['ltmonth']/$total['realvipnum'])*100;//＜0.25年占比
        $total['ltmonth_percent'] = (number_format($total['ltmonth_percent'],1,'.','')).'%';
        foreach ($list['list'] as $k => $v) {
            $heji['department'] = '本页合计';// 部门  
            $heji['city'] = '-';// 城市  
            $heji['brand_division_name'] = '-';// 品师长     
            $heji['brand_regiment_name'] = '-';// 品团长     
            $heji['brand_manage_name'] = '-';// 品牌师     
            $heji['dev_division_name'] = '-';// 拓师长     
            $heji['dev_regiment_name'] = '-';// 拓团长     
            $heji['dev_manage_name'] = '-';// 城市经理    
            $heji['realvipnum'] += $v['realvipnum'];// 实际总会员数  
            $heji['gtyear'] += $v['gtyear'];// 会员数≥1年
            $heji['gthalfyear'] += $v['gthalfyear'];// 1年＞会员数≥0.5年 
            $heji['lthalfyear'] += $v['lthalfyear'];// 0.5年＞会员数≥0.25年 
            $heji['ltmonth'] += $v['ltmonth'];// 会员数＜0.25年
        }
        $heji['gtyear_percent'] = ($heji['gtyear']/$heji['realvipnum'])*100;//≥1年占比 
        $heji['gtyear_percent'] = (number_format($heji['gtyear_percent'],1,'.','')).'%';
        $heji['gthalfyear_percent'] = ($heji['gthalfyear']/$heji['realvipnum'])*100;// 1年＞会员数≥0.5年占比
        $heji['gthalfyear_percent'] = (number_format($heji['gthalfyear_percent'],1,'.','')).'%';
        $heji['lthalfyear_percent'] = ($heji['lthalfyear']/$heji['realvipnum'])*100;//0.5年＞会员数≥0.25年占比
        $heji['lthalfyear_percent'] = (number_format($heji['lthalfyear_percent'],1,'.','')).'%';
        $heji['ltmonth_percent'] = ($heji['ltmonth']/$heji['realvipnum'])*100;//＜0.25年占比
        $heji['ltmonth_percent'] = (number_format($heji['ltmonth_percent'],1,'.','')).'%';
        $this->assign('total',$total);
        $this->assign('heji',$heji);
        $userdept = getUserDepartment();
        $this->assign('department',$userdept);
        $this->assign('manager',$managers);
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$managers);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * 查询会员合作详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [会员合作详情数组]
     */
    private function getCityVipConn($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        /*if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }*/
        $count = D('Salecount')->getCityVipCount($map);
        $list = D('Salecount')->getCityVipConn($map, 'b.px2 AS px',($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        
        $result['list'] = $list;
        $result['totalnum'] = $count;
        /*//查询所有,导出用
        $all = S("Cache:AllCityVipConn");
        if(empty($all)){
            $all = D('Salecount')->getCityVipAll($map, 'm.id ASC','',$condition);
            S("Cache:AllCityVipConn",$all,15*60);
        }*/
        return $result;
    }

    //城市新签合作时长newsigning
    public function cityNewSigningVip()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Salecount')->getAdminsByPosition();
        
        foreach ($brand as $k => $v) {
            foreach ($v as $key => $val) {
                $managers[$k][$val['id']]['id'] = $val['id']; 
                $managers[$k][$val['id']]['name'] = $val['name']; 
                $managers[$k][$val['id']]['uid'] = $val['uid']; 
            }
        }
        //搜索关键词 
        $keyword['city']            = I("get.city");
        $keyword['department']      = I("get.department");
        $keyword['time']            = I("get.time");
        $keyword['tshizhang']       = I("get.tshizhang");
        $keyword['ttuanzhang']      = I("get.ttuanzhang");
        $keyword['csjl']            = I("get.csjl");
        $keyword['pshizhang']       = I("get.pshizhang");
        $keyword['ptuanzhang']      = I("get.ptuanzhang");
        $keyword['pinpai']          = I("get.pinpai");

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
        }

        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        $time_now = date("Y-m",time());
        if($time_now != $keyword['time'] && $keyword['time'] != ''){
            //查询qz_sale_cityvips  type = 1
            //强制数字整数
            $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
            $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
            $list['list'] = D('Salecount')->getHistoryCityVip(($pageIndex-1)*$pageCount,$pageCount, $keyword, 2);
            foreach ($list['list'] as $k => $v) {
                if($v['dept'] == 1){
                    $list['list'][$k]['department'] = '商务';
                }else{
                    $list['list'][$k]['department'] = '外销';
                }
                foreach ($brand['brand_division'] as $key => $value) {
                    if($value['id'] == $v['brand_division']){
                        $list['list'][$k]['brand_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_regiment'] as $key => $value) {
                    if($value['id'] == $v['brand_regiment']){
                        $list['list'][$k]['brand_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_manage'] as $key => $value) {
                    if($value['id'] == $v['brand_manage']){
                        $list['list'][$k]['brand_manage_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_division'] as $key => $value) {
                    if($value['id'] == $v['dev_division']){
                        $list['list'][$k]['dev_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_regiment'] as $key => $value) {
                    if($value['id'] == $v['dev_regiment']){
                        $list['list'][$k]['dev_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_manage'] as $key => $value) {
                    if($value['id'] == $v['dev_manage']){
                        $list['list'][$k]['dev_manage_name'] = $value['name'];
                    }
                }

                //占比
                $list['list'][$k]['gtyear_percent'] = (number_format($v['gtyear_percent'],1,'.','')).'%';
                $list['list'][$k]['gthalfyear_percent'] = (number_format($v['gthalfyear_percent'],1,'.','')).'%';
                $list['list'][$k]['lthalfyear_percent'] = (number_format($v['lthalfyear_percent'],1,'.','')).'%';
                $list['list'][$k]['ltmonth_percent'] = (number_format($v['ltmonth_percent'],1,'.','')).'%';
            }
            $count = D('Salecount')->getHistoryVipNum($keyword, 2);
            if($count > $pagecount){
                import('Library.Org.Util.Page');
                $page = new \Page($count,$pageCount);
                $list['page'] =  $page->show();
            }
            $list['totalnum'] = $count;
            //查询所有,导出用
            $all = D('Salecount')->getHistoryVipAll($keyword, 2);
            foreach ($all as $k => $v) {
                if($v['dept'] == 1){
                    $all[$k]['department'] = '商务';
                }else{
                    $all[$k]['department'] = '外销';
                }
                foreach ($brand['brand_division'] as $key => $value) {
                    if($value['id'] == $v['brand_division']){
                        $all[$k]['brand_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_regiment'] as $key => $value) {
                    if($value['id'] == $v['brand_regiment']){
                        $all[$k]['brand_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_manage'] as $key => $value) {
                    if($value['id'] == $v['brand_manage']){
                        $all[$k]['brand_manage_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_division'] as $key => $value) {
                    if($value['id'] == $v['dev_division']){
                        $all[$k]['dev_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_regiment'] as $key => $value) {
                    if($value['id'] == $v['dev_regiment']){
                        $all[$k]['dev_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_manage'] as $key => $value) {
                    if($value['id'] == $v['dev_manage']){
                        $all[$k]['dev_manage_name'] = $value['name'];
                    }
                }
            }
            S("Cache:AllCityNewSigningVipConn",$all,15*60);
        }else{
            //当月则查询实时
            $list = $this->getCityNewSigningVip($pageIndex, $pageCount, $sort, $keyword);
            //查询所有,导出用
            $all = D('Salecount')->getCityVipConn($keyword,'b.px3 AS px','','',1);
            S("Cache:AllCityNewSigningVipConn",$all,15*60);
        }

        foreach ($all as $k => $v) {
            $total['department'] = '本项合计';// 部门  
            $total['city'] = '-';// 城市  
            $total['brand_division_name'] = '-';// 品师长     
            $total['brand_regiment_name'] = '-';// 品团长     
            $total['brand_manage_name'] = '-';// 品牌师     
            $total['dev_division_name'] = '-';// 拓师长     
            $total['dev_regiment_name'] = '-';// 拓团长     
            $total['dev_manage_name'] = '-';// 城市经理    
            $total['realvipnum'] += $v['realvipnum'];// 实际总会员数  
            $total['gtyear'] += $v['gtyear'];// 会员数≥1年
            $total['gthalfyear'] += $v['gthalfyear'];// 1年＞会员数≥0.5年 
            $total['lthalfyear'] += $v['lthalfyear'];// 0.5年＞会员数≥0.25年 
            $total['ltmonth'] += $v['ltmonth'];// 会员数＜0.25年
        }
        $total['gtyear_percent'] = ($total['gtyear']/$total['realvipnum'])*100;//≥1年占比 
        $total['gtyear_percent'] = (number_format($total['gtyear_percent'],1,'.','')).'%';
        $total['gthalfyear_percent'] = ($total['gthalfyear']/$total['realvipnum'])*100;// 1年＞会员数≥0.5年占比
        $total['gthalfyear_percent'] = (number_format($total['gthalfyear_percent'],1,'.','')).'%';
        $total['lthalfyear_percent'] = ($total['lthalfyear']/$total['realvipnum'])*100;//0.5年＞会员数≥0.25年占比
        $total['lthalfyear_percent'] = (number_format($total['lthalfyear_percent'],1,'.','')).'%';
        $total['ltmonth_percent'] = ($total['ltmonth']/$total['realvipnum'])*100;//＜0.25年占比
        $total['ltmonth_percent'] = (number_format($total['ltmonth_percent'],1,'.','')).'%';
        foreach ($list['list'] as $k => $v) {
            $heji['department'] = '本页合计';// 部门  
            $heji['city'] = '-';// 城市  
            $heji['brand_division_name'] = '-';// 品师长     
            $heji['brand_regiment_name'] = '-';// 品团长     
            $heji['brand_manage_name'] = '-';// 品牌师     
            $heji['dev_division_name'] = '-';// 拓师长     
            $heji['dev_regiment_name'] = '-';// 拓团长     
            $heji['dev_manage_name'] = '-';// 城市经理    
            $heji['realvipnum'] += $v['realvipnum'];// 实际总会员数  
            $heji['gtyear'] += $v['gtyear'];// 会员数≥1年
            $heji['gthalfyear'] += $v['gthalfyear'];// 1年＞会员数≥0.5年 
            $heji['lthalfyear'] += $v['lthalfyear'];// 0.5年＞会员数≥0.25年 
            $heji['ltmonth'] += $v['ltmonth'];// 会员数＜0.25年
        }
        $heji['gtyear_percent'] = ($heji['gtyear']/$heji['realvipnum'])*100;//≥1年占比 
        $heji['gtyear_percent'] = (number_format($heji['gtyear_percent'],1,'.','')).'%';
        $heji['gthalfyear_percent'] = ($heji['gthalfyear']/$heji['realvipnum'])*100;// 1年＞会员数≥0.5年占比
        $heji['gthalfyear_percent'] = (number_format($heji['gthalfyear_percent'],1,'.','')).'%';
        $heji['lthalfyear_percent'] = ($heji['lthalfyear']/$heji['realvipnum'])*100;//0.5年＞会员数≥0.25年占比
        $heji['lthalfyear_percent'] = (number_format($heji['lthalfyear_percent'],1,'.','')).'%';
        $heji['ltmonth_percent'] = ($heji['ltmonth']/$heji['realvipnum'])*100;//＜0.25年占比
        $heji['ltmonth_percent'] = (number_format($heji['ltmonth_percent'],1,'.','')).'%';
        $this->assign('total',$total);
        $this->assign('heji',$heji);
        $keyword['city']            = I("get.city");
        $userdept = getUserDepartment();
        $this->assign('department',$userdept);
        $this->assign('manager',$managers);
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$managers);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * 查询新签会员合作详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [新签会员详情数组]
     */
    private function getCityNewSigningVip($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        /*if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }*/
        $count = D('Salecount')->getCityVipCount($map);
        $list = D('Salecount')->getCityVipConn($map,'b.px3 AS px',($pageIndex-1)*$pageCount,$pageCount,1);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list;
        $result['totalnum'] = $count;
        /*//查询所有,导出用
        $all = S("Cache:AllCityNewSigningVipConn");
        if(empty($all)){
            $all = D('Salecount')->getCityVipAll($map, 'm.id ASC',1,$condition);
            S("Cache:AllCityNewSigningVipConn",$all,15*60);
        }*/
        return $result;
    }

    //城市续费合作时长renew
    public function cityRenewVip()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Salecount')->getAdminsByPosition();
        
        foreach ($brand as $k => $v) {
            foreach ($v as $key => $val) {
                $managers[$k][$val['id']]['id'] = $val['id']; 
                $managers[$k][$val['id']]['name'] = $val['name']; 
                $managers[$k][$val['id']]['uid'] = $val['uid']; 
            }
        }
        //搜索关键词 
        $keyword['city']            = I("get.city");
        $keyword['department']      = I("get.department");
        $keyword['time']            = I("get.time");
        $keyword['tshizhang']       = I("get.tshizhang");
        $keyword['ttuanzhang']      = I("get.ttuanzhang");
        $keyword['csjl']            = I("get.csjl");
        $keyword['pshizhang']       = I("get.pshizhang");
        $keyword['ptuanzhang']      = I("get.ptuanzhang");
        $keyword['pinpai']          = I("get.pinpai");

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
        }

        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 50;
        $time_now = date("Y-m",time());
        if($time_now != $keyword['time'] && $keyword['time'] != ''){
            //查询qz_sale_cityvips  type = 1
            //强制数字整数
            $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
            $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
            $list['list'] = D('Salecount')->getHistoryCityVip(($pageIndex-1)*$pageCount,$pageCount, $keyword, 3);
            foreach ($list['list'] as $k => $v) {
                if($v['dept'] == 1){
                    $list['list'][$k]['department'] = '商务';
                }else{
                    $list['list'][$k]['department'] = '外销';
                }
                foreach ($brand['brand_division'] as $key => $value) {
                    if($value['id'] == $v['brand_division']){
                        $list['list'][$k]['brand_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_regiment'] as $key => $value) {
                    if($value['id'] == $v['brand_regiment']){
                        $list['list'][$k]['brand_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_manage'] as $key => $value) {
                    if($value['id'] == $v['brand_manage']){
                        $list['list'][$k]['brand_manage_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_division'] as $key => $value) {
                    if($value['id'] == $v['dev_division']){
                        $list['list'][$k]['dev_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_regiment'] as $key => $value) {
                    if($value['id'] == $v['dev_regiment']){
                        $list['list'][$k]['dev_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_manage'] as $key => $value) {
                    if($value['id'] == $v['dev_manage']){
                        $list['list'][$k]['dev_manage_name'] = $value['name'];
                    }
                }
                //占比
                $list['list'][$k]['gtyear_percent'] = (number_format($v['gtyear_percent'],1,'.','')).'%';
                $list['list'][$k]['gthalfyear_percent'] = (number_format($v['gthalfyear_percent'],1,'.','')).'%';
                $list['list'][$k]['lthalfyear_percent'] = (number_format($v['lthalfyear_percent'],1,'.','')).'%';
                $list['list'][$k]['ltmonth_percent'] = (number_format($v['ltmonth_percent'],1,'.','')).'%';
            }
            $count = D('Salecount')->getHistoryVipNum($keyword, 3);
            if($count > $pagecount){
                import('Library.Org.Util.Page');
                $page = new \Page($count,$pageCount);
                $list['page'] =  $page->show();
            }
            $list['totalnum'] = $count;
            //查询所有,导出用
            $all = D('Salecount')->getHistoryVipAll($keyword, 3);
            foreach ($all as $k => $v) {
                if($v['dept'] == 1){
                    $all[$k]['department'] = '商务';
                }else{
                    $all[$k]['department'] = '外销';
                }
                foreach ($brand['brand_division'] as $key => $value) {
                    if($value['id'] == $v['brand_division']){
                        $all[$k]['brand_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_regiment'] as $key => $value) {
                    if($value['id'] == $v['brand_regiment']){
                        $all[$k]['brand_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['brand_manage'] as $key => $value) {
                    if($value['id'] == $v['brand_manage']){
                        $all[$k]['brand_manage_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_division'] as $key => $value) {
                    if($value['id'] == $v['dev_division']){
                        $all[$k]['dev_division_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_regiment'] as $key => $value) {
                    if($value['id'] == $v['dev_regiment']){
                        $all[$k]['dev_regiment_name'] = $value['name'];
                    }
                }
                foreach ($brand['dev_manage'] as $key => $value) {
                    if($value['id'] == $v['dev_manage']){
                        $all[$k]['dev_manage_name'] = $value['name'];
                    }
                }
            }
            S("Cache:AllCityRenewVipConn",$all,15*60);
        }else{
            //当月则查询实时
            $list = $this->getCityRenewVip($pageIndex, $pageCount, $sort, $keyword);
            $all = D('Salecount')->getCityVipConn($keyword,'b.px4 AS px','','',2);
            S("Cache:AllCityRenewVipConn",$all,15*60);
        }
        foreach ($all as $k => $v) {
            $total['department'] = '本项合计';// 部门  
            $total['city'] = '-';// 城市  
            $total['brand_division_name'] = '-';// 品师长     
            $total['brand_regiment_name'] = '-';// 品团长     
            $total['brand_manage_name'] = '-';// 品牌师     
            $total['dev_division_name'] = '-';// 拓师长     
            $total['dev_regiment_name'] = '-';// 拓团长     
            $total['dev_manage_name'] = '-';// 城市经理    
            $total['realvipnum'] += $v['realvipnum'];// 实际总会员数  
            $total['gtyear'] += $v['gtyear'];// 会员数≥1年
            $total['gthalfyear'] += $v['gthalfyear'];// 1年＞会员数≥0.5年 
            $total['lthalfyear'] += $v['lthalfyear'];// 0.5年＞会员数≥0.25年 
            $total['ltmonth'] += $v['ltmonth'];// 会员数＜0.25年
        }
        $total['gtyear_percent'] = ($total['gtyear']/$total['realvipnum'])*100;//≥1年占比 
        $total['gtyear_percent'] = (number_format($total['gtyear_percent'],1,'.','')).'%';
        $total['gthalfyear_percent'] = ($total['gthalfyear']/$total['realvipnum'])*100;// 1年＞会员数≥0.5年占比
        $total['gthalfyear_percent'] = (number_format($total['gthalfyear_percent'],1,'.','')).'%';
        $total['lthalfyear_percent'] = ($total['lthalfyear']/$total['realvipnum'])*100;//0.5年＞会员数≥0.25年占比
        $total['lthalfyear_percent'] = (number_format($total['lthalfyear_percent'],1,'.','')).'%';
        $total['ltmonth_percent'] = ($total['ltmonth']/$total['realvipnum'])*100;//＜0.25年占比
        $total['ltmonth_percent'] = (number_format($total['ltmonth_percent'],1,'.','')).'%';
        foreach ($list['list'] as $k => $v) {
            $heji['department'] = '本页合计';// 部门  
            $heji['city'] = '-';// 城市  
            $heji['brand_division_name'] = '-';// 品师长     
            $heji['brand_regiment_name'] = '-';// 品团长     
            $heji['brand_manage_name'] = '-';// 品牌师     
            $heji['dev_division_name'] = '-';// 拓师长     
            $heji['dev_regiment_name'] = '-';// 拓团长     
            $heji['dev_manage_name'] = '-';// 城市经理    
            $heji['realvipnum'] += $v['realvipnum'];// 实际总会员数  
            $heji['gtyear'] += $v['gtyear'];// 会员数≥1年
            $heji['gthalfyear'] += $v['gthalfyear'];// 1年＞会员数≥0.5年 
            $heji['lthalfyear'] += $v['lthalfyear'];// 0.5年＞会员数≥0.25年 
            $heji['ltmonth'] += $v['ltmonth'];// 会员数＜0.25年
        }
        $heji['gtyear_percent'] = ($heji['gtyear']/$heji['realvipnum'])*100;//≥1年占比 
        $heji['gtyear_percent'] = (number_format($heji['gtyear_percent'],1,'.','')).'%';
        $heji['gthalfyear_percent'] = ($heji['gthalfyear']/$heji['realvipnum'])*100;// 1年＞会员数≥0.5年占比
        $heji['gthalfyear_percent'] = (number_format($heji['gthalfyear_percent'],1,'.','')).'%';
        $heji['lthalfyear_percent'] = ($heji['lthalfyear']/$heji['realvipnum'])*100;//0.5年＞会员数≥0.25年占比
        $heji['lthalfyear_percent'] = (number_format($heji['lthalfyear_percent'],1,'.','')).'%';
        $heji['ltmonth_percent'] = ($heji['ltmonth']/$heji['realvipnum'])*100;//＜0.25年占比
        $heji['ltmonth_percent'] = (number_format($heji['ltmonth_percent'],1,'.','')).'%';
        $this->assign('total',$total);
        $this->assign('heji',$heji);
        $keyword['city']            = I("get.city");
        $userdept = getUserDepartment();
        $this->assign('department',$userdept);
        $this->assign('manager',$managers);
        $this->assign('totalnum',$list['totalnum']);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$managers);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * 查询续费会员合作详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [续费会员详情数组]
     */
    private function getCityRenewVip($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        /*if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }*/
        $count = D('Salecount')->getCityVipCount($map);
        $list = D('Salecount')->getCityVipConn($map,'b.px4 AS px',($pageIndex-1)*$pageCount,$pageCount,2);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        
        $result['list'] = $list;
        $result['totalnum'] = $count;
        /*//查询所有,导出用
        $all = S("Cache:AllCityRenewVipConn");
        if(empty($all)){
            $all = D('Salecount')->getCityVipAll($map, 'm.id ASC',2,$condition);
            S("Cache:AllCityRenewVipConn",$all,15*60);
        }*/
        return $result;
    }

    /*
     * 统计全瞰-会员合作时长
     * 
    */
    public function cityVipTable()
    {
        $keyword['department'] = I('get.department');
        $keyword['time'] = I('get.date');
        $year = date('Y',time());
        $month = intval(date('m',time()));
        $natural_month = date('m',time());


        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            $idstr = implode(',', $ids);
            $condition['city'] = array('IN',$idstr); 
            //根绝城市权限查询对应的数据
            if(empty($keyword['time']) || $keyword['time'] == $year){
                $natural_month = $year.'-'.$natural_month;
                if($month == 1){
                    $year = $year - 1;
                }
                //如果没有选择财年，或者选择当年，则查询当年已有数据，没有数据的月份留空
                //可能的条件：1，没有选择财年，且不是1月  2，选择了今年，且不是1月
                //在此生成财年月份
                $nextyear = $year+1;
                $title = [
                    $year.'-02',
                    $year.'-03',
                    $year.'-04',
                    $year.'-05',
                    $year.'-06',
                    $year.'-07',
                    $year.'-08',
                    $year.'-09',
                    $year.'-10',
                    $year.'-11',
                    $year.'-12',
                    $nextyear.'-01'
                ];
                foreach ($title as $key => $value) {
                    if($key >= ($month-1) && $month != 1){
                        unset($title[$key]);
                    }
                }
                foreach ($title as $k => $v) {
                    $list[$k]['month'] = $v;
                    if($v != $natural_month){
                        //查询sale_cityvips
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],1,$condition);
                        $list[$k] = $data;
                    }else{
                        //实时查询今天数据为本月
                        $all = D('Salecount')->getCityVipAll($map, 'm.id ASC','',$condition);
                        foreach ($all as $key => $value) {
                            if($keyword['department'] == 0){
                                //$list[$k]['realvipnum'] += $value['realvipnum'];
                                $list[$k]['gtyear'] += $value['gtyear'];
                                
                                $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                $list[$k]['ltmonth'] += $value['ltmonth'];
                            }elseif($keyword['department'] == 1){
                                if($value['dept'] == 1){
                                    //$list[$k]['realvipnum'] += $value['realvipnum'];
                                    $list[$k]['gtyear'] += $value['gtyear'];
                                    
                                    $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                    $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                    $list[$k]['ltmonth'] += $value['ltmonth'];
                                }
                            }elseif($keyword['department'] == 2){
                                if($value['dept'] == 2){
                                    //$list[$k]['realvipnum'] += $value['realvipnum'];
                                    $list[$k]['gtyear'] += $value['gtyear'];
                                    
                                    $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                    $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                    $list[$k]['ltmonth'] += $value['ltmonth'];
                                }
                            }     
                        }
                        $list[$k]['realnewsigningvip'] = ($list[$k]['gtyear'] + $list[$k]['gthalfyear'] + $list[$k]['lthalfyear'] + $list[$k]['ltmonth']);
                        //var_dump($list);
                        $list[$k]['gtyear_percent'] = ($list[$k]['gtyear']/$list[$k]['realnewsigningvip'])*100;
                        $list[$k]['gtyear_percent'] = (number_format($list[$k]['gtyear_percent'],1,'.','')).'%';

                        $list[$k]['gthalfyear_percent'] = ($list[$k]['gthalfyear']/$list[$k]['realnewsigningvip'])*100;
                            $list[$k]['gthalfyear_percent'] = (number_format($list[$k]['gthalfyear_percent'],1,'.','')).'%';

                        $list[$k]['lthalfyear_percent'] = ($list[$k]['lthalfyear']/$list[$k]['realnewsigningvip'])*100;
                        $list[$k]['lthalfyear_percent'] = (number_format($list[$k]['lthalfyear_percent'],1,'.','')).'%';

                        $list[$k]['ltmonth_percent'] = ($list[$k]['ltmonth']/$list[$k]['realnewsigningvip'])*100;
                            $list[$k]['ltmonth_percent'] = (number_format($list[$k]['ltmonth_percent'],1,'.','')).'%';
                    }
                }
                //生成图表数组，需要gtyear  gthalfyear  lthalfyear ltmonth  4组数据
                foreach ($list as $k => $v) {
                    if($v['gtyear'] > 0){
                        $table_data['gtyear'][] = intval($v['gtyear']);
                    }else{
                        $table_data['gtyear'][] = 0;
                    }
                    if($v['gthalfyear'] > 0){
                        $table_data['gthalfyear'][] = intval($v['gthalfyear']);
                    }else{
                        $table_data['gthalfyear'][] = 0;
                    }
                    if($v['lthalfyear'] > 0){
                        $table_data['lthalfyear'][] = intval($v['lthalfyear']);
                    }else{
                        $table_data['lthalfyear'][] = 0;
                    }
                    if($v['ltmonth'] > 0){
                        $table_data['ltmonth'][] = intval($v['ltmonth']);
                    }else{
                        $table_data['ltmonth'][] = 0;
                    }
                }
                foreach ($table_data as $k => $v) {
                    $table_data[$k] = implode(',', $v);
                }
                $table_data['year'] = $year;
                //查询去年的数据
                $last_year = $year - 1;
                $last_title = [
                    $last_year.'-02',
                    $last_year.'-03',
                    $last_year.'-04',
                    $last_year.'-05',
                    $last_year.'-06',
                    $last_year.'-07',
                    $last_year.'-08',
                    $last_year.'-09',
                    $last_year.'-10',
                    $last_year.'-11',
                    $last_year.'-12',
                    $year.'-01'
                ];
                foreach ($last_title as $k => $v) {
                    $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],1,$condition);
                    $last_list[] = $data;
                }
                foreach ($last_list as $k => $v) {
                    if($v['gtyear'] > 0){
                        $table_last['gtyear'][] = intval($v['gtyear']);
                    }else{
                        $table_last['gtyear'][] = 0;
                    }
                    if($v['gthalfyear'] > 0){
                        $table_last['gthalfyear'][] = intval($v['gthalfyear']);
                    }else{
                        $table_last['gthalfyear'][] = 0;
                    }
                    if($v['lthalfyear'] > 0){
                        $table_last['lthalfyear'][] = intval($v['lthalfyear']);
                    }else{
                        $table_last['lthalfyear'][] = 0;
                    }
                    if($v['ltmonth'] > 0){
                        $table_last['ltmonth'][] = intval($v['ltmonth']);
                    }else{
                        $table_last['ltmonth'][] = 0;
                    }
                }
                foreach ($table_last as $k => $v) {
                    $table_last[$k] = implode(',', $v);
                }
                $table_last['year'] = $last_year;
            }else{
                //查询数据并整合，没有数据的年份，统一显示空数据
                //在此生成财年月份
                if($keyword['time'] < $year){
                    $nextyear = $keyword['time']+1;
                    $title = [
                        $keyword['time'].'-02',
                        $keyword['time'].'-03',
                        $keyword['time'].'-04',
                        $keyword['time'].'-05',
                        $keyword['time'].'-06',
                        $keyword['time'].'-07',
                        $keyword['time'].'-08',
                        $keyword['time'].'-09',
                        $keyword['time'].'-10',
                        $keyword['time'].'-11',
                        $keyword['time'].'-12',
                        $nextyear.'-01'
                    ];
                    foreach ($title as $k => $v) {
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],1,$condition);
                        $list[$k] = $data;
                    }
                    //生成图表数组，需要gtyear  gthalfyear  lthalfyear ltmonth  4组数据
                    foreach ($list as $k => $v) {
                        if($v['gtyear'] > 0){
                            $table_data['gtyear'][] = intval($v['gtyear']);
                        }else{
                            $table_data['gtyear'][] = 0;
                        }
                        if($v['gthalfyear'] > 0){
                            $table_data['gthalfyear'][] = intval($v['gthalfyear']);
                        }else{
                            $table_data['gthalfyear'][] = 0;
                        }
                        if($v['lthalfyear'] > 0){
                            $table_data['lthalfyear'][] = intval($v['lthalfyear']);
                        }else{
                            $table_data['lthalfyear'][] = 0;
                        }
                        if($v['ltmonth'] > 0){
                            $table_data['ltmonth'][] = intval($v['ltmonth']);
                        }else{
                            $table_data['ltmonth'][] = 0;
                        }
                    }
                    foreach ($table_data as $k => $v) {
                        $table_data[$k] = implode(',', $v);
                    }
                    $table_data['year'] = $keyword['time'];
                    //查询去年的数据
                    $last_year = $keyword['time'] - 1;
                    $last_title = [
                        $last_year.'-02',
                        $last_year.'-03',
                        $last_year.'-04',
                        $last_year.'-05',
                        $last_year.'-06',
                        $last_year.'-07',
                        $last_year.'-08',
                        $last_year.'-09',
                        $last_year.'-10',
                        $last_year.'-11',
                        $last_year.'-12',
                        $keyword['time'].'-01'
                    ];
                    foreach ($last_title as $k => $v) {
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],1,$condition);
                        $last_list[] = $data;
                    }
                    foreach ($last_list as $k => $v) {
                        if($v['gtyear'] > 0){
                            $table_last['gtyear'][] = intval($v['gtyear']);
                        }else{
                            $table_last['gtyear'][] = 0;
                        }
                        if($v['gthalfyear'] > 0){
                            $table_last['gthalfyear'][] = intval($v['gthalfyear']);
                        }else{
                            $table_last['gthalfyear'][] = 0;
                        }
                        if($v['lthalfyear'] > 0){
                            $table_last['lthalfyear'][] = intval($v['lthalfyear']);
                        }else{
                            $table_last['lthalfyear'][] = 0;
                        }
                        if($v['ltmonth'] > 0){
                            $table_last['ltmonth'][] = intval($v['ltmonth']);
                        }else{
                            $table_last['ltmonth'][] = 0;
                        }
                    }
                    foreach ($table_last as $k => $v) {
                        $table_last[$k] = implode(',', $v);
                    }
                    $table_last['year'] = $last_year;
                }  
            }
        }
        $dept = getUserDepartment();
        $this->assign('department',$dept);
        $this->assign('table_last',$table_last);//去年数据
        $this->assign('table_data',$table_data);//今年数据
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    /*
     * 统计全瞰-新签合作时长
     * 
    */
    public function cityNewSigningTable()
    {
        $keyword['department'] = I('get.department');
        $keyword['time'] = I('get.date');
        $year = date('Y',time());
        $month = intval(date('m',time()));
        $natural_month = date('m',time());
        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            $idstr = implode(',', $ids);
            $condition['city'] = array('IN',$idstr); 
            //
            if(empty($keyword['time']) || $keyword['time'] == $year){
                $natural_month = $year.'-'.$natural_month;
                if($month == 1){
                    $year = $year - 1;
                }
                //如果没有选择财年，或者选择当年，则查询当年已有数据，没有数据的月份留空
                //可能的条件：1，没有选择财年，且不是1月  2，选择了今年，且不是1月
                //在此生成财年月份
                $nextyear = $year+1;
                $title = [
                    $year.'-02',
                    $year.'-03',
                    $year.'-04',
                    $year.'-05',
                    $year.'-06',
                    $year.'-07',
                    $year.'-08',
                    $year.'-09',
                    $year.'-10',
                    $year.'-11',
                    $year.'-12',
                    $nextyear.'-01'
                ];
                foreach ($title as $key => $value) {
                    if($key >= ($month-1) && $month != 1){
                        unset($title[$key]);
                    }
                }
                foreach ($title as $k => $v) {
                    $list[$k]['month'] = $v;
                    if($v != $natural_month){
                        //查询sale_cityvips
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],2,$condition);
                        $list[$k] = $data;
                    }else{
                        //实时查询今天数据为本月
                        $all = D('Salecount')->getCityVipAll($map, 'm.id ASC',1,$condition);
                        foreach ($all as $key => $value) {
                            if($keyword['department'] == 0){
                                $list[$k]['realvipnum'] += $value['realvipnum'];
                                $list[$k]['gtyear'] += $value['gtyear'];
                                
                                $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                $list[$k]['ltmonth'] += $value['ltmonth'];
                            }elseif($keyword['department'] == 1){
                                if($value['dept'] == 1){
                                    $list[$k]['realvipnum'] += $value['realvipnum'];
                                    $list[$k]['gtyear'] += $value['gtyear'];
                                    
                                    $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                    $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                    $list[$k]['ltmonth'] += $value['ltmonth'];
                                }
                            }elseif($keyword['department'] == 2){
                                if($value['dept'] == 2){
                                    $list[$k]['realvipnum'] += $value['realvipnum'];
                                    $list[$k]['gtyear'] += $value['gtyear'];
                                    
                                    $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                    $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                    $list[$k]['ltmonth'] += $value['ltmonth'];
                                }
                            }
                            $list[$k]['realnewsigningvip'] = ($list[$k]['gtyear'] + $list[$k]['gthalfyear'] + $list[$k]['lthalfyear'] + $list[$k]['ltmonth']);     
                        }
                        $list[$k]['gtyear_percent'] = ($list[$k]['gtyear']/$list[$k]['realvipnum'])*100;
                        $list[$k]['gtyear_percent'] = (number_format($list[$k]['gtyear_percent'],1,'.','')).'%';

                        $list[$k]['gthalfyear_percent'] = ($list[$k]['gthalfyear']/$list[$k]['realvipnum'])*100;
                            $list[$k]['gthalfyear_percent'] = (number_format($list[$k]['gthalfyear_percent'],1,'.','')).'%';

                        $list[$k]['lthalfyear_percent'] = ($list[$k]['lthalfyear']/$list[$k]['realvipnum'])*100;
                        $list[$k]['lthalfyear_percent'] = (number_format($list[$k]['lthalfyear_percent'],1,'.','')).'%';

                        $list[$k]['ltmonth_percent'] = ($list[$k]['ltmonth']/$list[$k]['realvipnum'])*100;
                            $list[$k]['ltmonth_percent'] = (number_format($list[$k]['ltmonth_percent'],1,'.','')).'%';
                    }
                }
                //生成图表数组，需要gtyear  gthalfyear  lthalfyear ltmonth  4组数据
                foreach ($list as $k => $v) {
                    if($v['gtyear'] > 0){
                        $table_data['gtyear'][] = intval($v['gtyear']);
                    }else{
                        $table_data['gtyear'][] = 0;
                    }
                    if($v['gthalfyear'] > 0){
                        $table_data['gthalfyear'][] = intval($v['gthalfyear']);
                    }else{
                        $table_data['gthalfyear'][] = 0;
                    }
                    if($v['lthalfyear'] > 0){
                        $table_data['lthalfyear'][] = intval($v['lthalfyear']);
                    }else{
                        $table_data['lthalfyear'][] = 0;
                    }
                    if($v['ltmonth'] > 0){
                        $table_data['ltmonth'][] = intval($v['ltmonth']);
                    }else{
                        $table_data['ltmonth'][] = 0;
                    }
                }
                foreach ($table_data as $k => $v) {
                    $table_data[$k] = implode(',', $v);
                }
                $table_data['year'] = $year;
                //查询去年的数据
                $last_year = $year - 1;
                $last_title = [
                    $last_year.'-02',
                    $last_year.'-03',
                    $last_year.'-04',
                    $last_year.'-05',
                    $last_year.'-06',
                    $last_year.'-07',
                    $last_year.'-08',
                    $last_year.'-09',
                    $last_year.'-10',
                    $last_year.'-11',
                    $last_year.'-12',
                    $year.'-01'
                ];
                foreach ($last_title as $k => $v) {
                    $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],2,$condition);
                    $last_list[] = $data;
                }
                foreach ($last_list as $k => $v) {
                    if($v['gtyear'] > 0){
                        $table_last['gtyear'][] = intval($v['gtyear']);
                    }else{
                        $table_last['gtyear'][] = 0;
                    }
                    if($v['gthalfyear'] > 0){
                        $table_last['gthalfyear'][] = intval($v['gthalfyear']);
                    }else{
                        $table_last['gthalfyear'][] = 0;
                    }
                    if($v['lthalfyear'] > 0){
                        $table_last['lthalfyear'][] = intval($v['lthalfyear']);
                    }else{
                        $table_last['lthalfyear'][] = 0;
                    }
                    if($v['ltmonth'] > 0){
                        $table_last['ltmonth'][] = intval($v['ltmonth']);
                    }else{
                        $table_last['ltmonth'][] = 0;
                    }
                }
                foreach ($table_last as $k => $v) {
                    $table_last[$k] = implode(',', $v);
                }
                $table_last['year'] = $last_year;
            }else{
                //查询数据并整合，没有数据的年份，统一显示空数据
                //在此生成财年月份
                if($keyword['time'] < $year){
                    $nextyear = $keyword['time']+1;
                    $title = [
                        $keyword['time'].'-02',
                        $keyword['time'].'-03',
                        $keyword['time'].'-04',
                        $keyword['time'].'-05',
                        $keyword['time'].'-06',
                        $keyword['time'].'-07',
                        $keyword['time'].'-08',
                        $keyword['time'].'-09',
                        $keyword['time'].'-10',
                        $keyword['time'].'-11',
                        $keyword['time'].'-12',
                        $nextyear.'-01'
                    ];
                    foreach ($title as $k => $v) {
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],2,$condition);
                        $list[$k] = $data;
                    }
                    //生成图表数组，需要gtyear  gthalfyear  lthalfyear ltmonth  4组数据
                    foreach ($list as $k => $v) {
                        if($v['gtyear'] > 0){
                            $table_data['gtyear'][] = intval($v['gtyear']);
                        }else{
                            $table_data['gtyear'][] = 0;
                        }
                        if($v['gthalfyear'] > 0){
                            $table_data['gthalfyear'][] = intval($v['gthalfyear']);
                        }else{
                            $table_data['gthalfyear'][] = 0;
                        }
                        if($v['lthalfyear'] > 0){
                            $table_data['lthalfyear'][] = intval($v['lthalfyear']);
                        }else{
                            $table_data['lthalfyear'][] = 0;
                        }
                        if($v['ltmonth'] > 0){
                            $table_data['ltmonth'][] = intval($v['ltmonth']);
                        }else{
                            $table_data['ltmonth'][] = 0;
                        }
                    }
                    foreach ($table_data as $k => $v) {
                        $table_data[$k] = implode(',', $v);
                    }
                    $table_data['year'] = $keyword['time'];
                    //查询去年的数据
                    $last_year = $keyword['time'] - 1;
                    $last_title = [
                        $last_year.'-02',
                        $last_year.'-03',
                        $last_year.'-04',
                        $last_year.'-05',
                        $last_year.'-06',
                        $last_year.'-07',
                        $last_year.'-08',
                        $last_year.'-09',
                        $last_year.'-10',
                        $last_year.'-11',
                        $last_year.'-12',
                        $keyword['time'].'-01'
                    ];
                    foreach ($last_title as $k => $v) {
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],2,$condition);
                        $last_list[] = $data;
                    }
                    foreach ($last_list as $k => $v) {
                        if($v['gtyear'] > 0){
                            $table_last['gtyear'][] = intval($v['gtyear']);
                        }else{
                            $table_last['gtyear'][] = 0;
                        }
                        if($v['gthalfyear'] > 0){
                            $table_last['gthalfyear'][] = intval($v['gthalfyear']);
                        }else{
                            $table_last['gthalfyear'][] = 0;
                        }
                        if($v['lthalfyear'] > 0){
                            $table_last['lthalfyear'][] = intval($v['lthalfyear']);
                        }else{
                            $table_last['lthalfyear'][] = 0;
                        }
                        if($v['ltmonth'] > 0){
                            $table_last['ltmonth'][] = intval($v['ltmonth']);
                        }else{
                            $table_last['ltmonth'][] = 0;
                        }
                    }
                    foreach ($table_last as $k => $v) {
                        $table_last[$k] = implode(',', $v);
                    }
                    $table_last['year'] = $last_year;
                }  
            }
        }

        
        $dept = getUserDepartment();
        $this->assign('department',$dept);
        $this->assign('table_last',$table_last);//去年数据
        $this->assign('table_data',$table_data);//今年数据
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    /*
     * 统计全瞰-续费合作时长
     * 
    */
    public function cityRenewTable()
    {
        $keyword['department'] = I('get.department');
        $keyword['time'] = I('get.date');
        $year = date('Y',time());
        $month = intval(date('m',time()));
        $natural_month = date('m',time());

        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            $idstr = implode(',', $ids);
            $condition['city'] = array('IN',$idstr); 

            if(empty($keyword['time']) || $keyword['time'] == $year){
                $natural_month = $year.'-'.$natural_month;
                if($month == 1){
                    $year = $year - 1;
                }
                //如果没有选择财年，或者选择当年，则查询当年已有数据，没有数据的月份留空
                //可能的条件：1，没有选择财年，且不是1月  2，选择了今年，且不是1月
                //在此生成财年月份
                $nextyear = $year+1;
                $title = [
                    $year.'-02',
                    $year.'-03',
                    $year.'-04',
                    $year.'-05',
                    $year.'-06',
                    $year.'-07',
                    $year.'-08',
                    $year.'-09',
                    $year.'-10',
                    $year.'-11',
                    $year.'-12',
                    $nextyear.'-01'
                ];
                foreach ($title as $key => $value) {
                    if($key >= ($month-1) && $month != 1){
                        unset($title[$key]);
                    }
                }
                foreach ($title as $k => $v) {
                    $list[$k]['month'] = $v;
                    if($v != $natural_month){
                        //查询sale_cityvips
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],3,$condition);
                        $list[$k] = $data;
                    }else{
                        //实时查询今天数据为本月
                        $all = D('Salecount')->getCityVipAll($map, 'm.id ASC',2,$condition);
                        foreach ($all as $key => $value) {
                            if($keyword['department'] == 0){
                                $list[$k]['realvipnum'] += $value['realvipnum'];
                                $list[$k]['gtyear'] += $value['gtyear'];
                                
                                $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                $list[$k]['ltmonth'] += $value['ltmonth'];
                            }elseif($keyword['department'] == 1){
                                if($value['dept'] == 1){
                                    $list[$k]['realvipnum'] += $value['realvipnum'];
                                    $list[$k]['gtyear'] += $value['gtyear'];
                                    
                                    $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                    $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                    $list[$k]['ltmonth'] += $value['ltmonth'];
                                }
                            }elseif($keyword['department'] == 2){
                                if($value['dept'] == 2){
                                    $list[$k]['realvipnum'] += $value['realvipnum'];
                                    $list[$k]['gtyear'] += $value['gtyear'];
                                    
                                    $list[$k]['gthalfyear'] += $value['gthalfyear'];
                                    $list[$k]['lthalfyear'] += $value['lthalfyear'];
                                    $list[$k]['ltmonth'] += $value['ltmonth'];
                                }
                            }
                            $list[$k]['realnewsigningvip'] = ($list[$k]['gtyear'] + $list[$k]['gthalfyear'] + $list[$k]['lthalfyear'] + $list[$k]['ltmonth']);     
                        }
                        $list[$k]['gtyear_percent'] = ($list[$k]['gtyear']/$list[$k]['realvipnum'])*100;
                        $list[$k]['gtyear_percent'] = (number_format($list[$k]['gtyear_percent'],1,'.','')).'%';

                        $list[$k]['gthalfyear_percent'] = ($list[$k]['gthalfyear']/$list[$k]['realvipnum'])*100;
                            $list[$k]['gthalfyear_percent'] = (number_format($list[$k]['gthalfyear_percent'],1,'.','')).'%';

                        $list[$k]['lthalfyear_percent'] = ($list[$k]['lthalfyear']/$list[$k]['realvipnum'])*100;
                        $list[$k]['lthalfyear_percent'] = (number_format($list[$k]['lthalfyear_percent'],1,'.','')).'%';

                        $list[$k]['ltmonth_percent'] = ($list[$k]['ltmonth']/$list[$k]['realvipnum'])*100;
                            $list[$k]['ltmonth_percent'] = (number_format($list[$k]['ltmonth_percent'],1,'.','')).'%';
                    }
                }
                //生成图表数组，需要gtyear  gthalfyear  lthalfyear ltmonth  4组数据
                foreach ($list as $k => $v) {
                    if($v['gtyear'] > 0){
                        $table_data['gtyear'][] = intval($v['gtyear']);
                    }else{
                        $table_data['gtyear'][] = 0;
                    }
                    if($v['gthalfyear'] > 0){
                        $table_data['gthalfyear'][] = intval($v['gthalfyear']);
                    }else{
                        $table_data['gthalfyear'][] = 0;
                    }
                    if($v['lthalfyear'] > 0){
                        $table_data['lthalfyear'][] = intval($v['lthalfyear']);
                    }else{
                        $table_data['lthalfyear'][] = 0;
                    }
                    if($v['ltmonth'] > 0){
                        $table_data['ltmonth'][] = intval($v['ltmonth']);
                    }else{
                        $table_data['ltmonth'][] = 0;
                    }
                }
                foreach ($table_data as $k => $v) {
                    $table_data[$k] = implode(',', $v);
                }
                $table_data['year'] = $year;
                //查询去年的数据
                $last_year = $year - 1;
                $last_title = [
                    $last_year.'-02',
                    $last_year.'-03',
                    $last_year.'-04',
                    $last_year.'-05',
                    $last_year.'-06',
                    $last_year.'-07',
                    $last_year.'-08',
                    $last_year.'-09',
                    $last_year.'-10',
                    $last_year.'-11',
                    $last_year.'-12',
                    $year.'-01'
                ];
                foreach ($last_title as $k => $v) {
                    $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],3,$condition);
                    $last_list[] = $data;
                }
                foreach ($last_list as $k => $v) {
                    if($v['gtyear'] > 0){
                        $table_last['gtyear'][] = intval($v['gtyear']);
                    }else{
                        $table_last['gtyear'][] = 0;
                    }
                    if($v['gthalfyear'] > 0){
                        $table_last['gthalfyear'][] = intval($v['gthalfyear']);
                    }else{
                        $table_last['gthalfyear'][] = 0;
                    }
                    if($v['lthalfyear'] > 0){
                        $table_last['lthalfyear'][] = intval($v['lthalfyear']);
                    }else{
                        $table_last['lthalfyear'][] = 0;
                    }
                    if($v['ltmonth'] > 0){
                        $table_last['ltmonth'][] = intval($v['ltmonth']);
                    }else{
                        $table_last['ltmonth'][] = 0;
                    }
                }
                foreach ($table_last as $k => $v) {
                    $table_last[$k] = implode(',', $v);
                }
                $table_last['year'] = $last_year;
            }else{
                //查询数据并整合，没有数据的年份，统一显示空数据
                //在此生成财年月份
                if($keyword['time'] < $year){
                    $nextyear = $keyword['time']+1;
                    $title = [
                        $keyword['time'].'-02',
                        $keyword['time'].'-03',
                        $keyword['time'].'-04',
                        $keyword['time'].'-05',
                        $keyword['time'].'-06',
                        $keyword['time'].'-07',
                        $keyword['time'].'-08',
                        $keyword['time'].'-09',
                        $keyword['time'].'-10',
                        $keyword['time'].'-11',
                        $keyword['time'].'-12',
                        $nextyear.'-01'
                    ];
                    foreach ($title as $k => $v) {
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],3,$condition);
                        $list[$k] = $data;
                    }
                    //生成图表数组，需要gtyear  gthalfyear  lthalfyear ltmonth  4组数据
                    foreach ($list as $k => $v) {
                        if($v['gtyear'] > 0){
                            $table_data['gtyear'][] = intval($v['gtyear']);
                        }else{
                            $table_data['gtyear'][] = 0;
                        }
                        if($v['gthalfyear'] > 0){
                            $table_data['gthalfyear'][] = intval($v['gthalfyear']);
                        }else{
                            $table_data['gthalfyear'][] = 0;
                        }
                        if($v['lthalfyear'] > 0){
                            $table_data['lthalfyear'][] = intval($v['lthalfyear']);
                        }else{
                            $table_data['lthalfyear'][] = 0;
                        }
                        if($v['ltmonth'] > 0){
                            $table_data['ltmonth'][] = intval($v['ltmonth']);
                        }else{
                            $table_data['ltmonth'][] = 0;
                        }
                    }
                    foreach ($table_data as $k => $v) {
                        $table_data[$k] = implode(',', $v);
                    }
                    $table_data['year'] = $keyword['time'];
                    //查询去年的数据
                    $last_year = $keyword['time'] - 1;
                    $last_title = [
                        $last_year.'-02',
                        $last_year.'-03',
                        $last_year.'-04',
                        $last_year.'-05',
                        $last_year.'-06',
                        $last_year.'-07',
                        $last_year.'-08',
                        $last_year.'-09',
                        $last_year.'-10',
                        $last_year.'-11',
                        $last_year.'-12',
                        $keyword['time'].'-01'
                    ];
                    foreach ($last_title as $k => $v) {
                        $data = D('Salecount')->getVipTableInfo($v,$keyword['department'],3,$condition);
                        $last_list[] = $data;
                    }
                    foreach ($last_list as $k => $v) {
                        if($v['gtyear'] > 0){
                            $table_last['gtyear'][] = intval($v['gtyear']);
                        }else{
                            $table_last['gtyear'][] = 0;
                        }
                        if($v['gthalfyear'] > 0){
                            $table_last['gthalfyear'][] = intval($v['gthalfyear']);
                        }else{
                            $table_last['gthalfyear'][] = 0;
                        }
                        if($v['lthalfyear'] > 0){
                            $table_last['lthalfyear'][] = intval($v['lthalfyear']);
                        }else{
                            $table_last['lthalfyear'][] = 0;
                        }
                        if($v['ltmonth'] > 0){
                            $table_last['ltmonth'][] = intval($v['ltmonth']);
                        }else{
                            $table_last['ltmonth'][] = 0;
                        }
                    }
                    foreach ($table_last as $k => $v) {
                        $table_last[$k] = implode(',', $v);
                    }
                    $table_last['year'] = $last_year;
                }  
            }
        }
        
        //var_dump($year);
        $dept = getUserDepartment();
        $this->assign('department',$dept);
        $this->assign('table_last',$table_last);//去年数据
        $this->assign('table_data',$table_data);//今年数据
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    /*
     * 城市续费率
     * 
    */
    public function cityReNewPercent()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Salecount')->getAdminsByPosition();

        foreach ($brand as $k => $v) {
            foreach ($v as $key => $val) {
                $managers[$k][$val['id']]['id'] = $val['id']; 
                $managers[$k][$val['id']]['name'] = $val['name']; 
                $managers[$k][$val['id']]['uid'] = $val['uid']; 
            }
        }
        //搜索关键词 
        $keyword['city']            = I("get.city");
        $keyword['department']      = I("get.department");
        $keyword['time']            = I("get.time");
        $keyword['tshizhang']       = I("get.tshizhang");
        $keyword['ttuanzhang']      = I("get.ttuanzhang");
        $keyword['csjl']            = I("get.csjl");
        $keyword['pshizhang']       = I("get.pshizhang");
        $keyword['ptuanzhang']      = I("get.ptuanzhang");
        $keyword['pinpai']          = I("get.pinpai");
        if(empty($keyword['time'])){
            $keyword['time'] = date('Y-m',time());
        }   

        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
        }

        //获取
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 20;
        $time_now = date("Y-m",time());
        if($time_now != $keyword['time'] && $keyword['time'] != ''){
            //查询历史数据
            $list = $this->getCityReNewConn($pageIndex, $pageCount, $sort, $keyword);
        }else{
            //查询本月实时数据
            $list = $this->setCityReNewCounts($pageIndex, $pageCount, $sort, $keyword);
        }
        $keyword['city']            = I("get.city");
        foreach ($list['list'] as $k => $v) {
            if($v['dept'] == 1){
                $list['list'][$k]['department'] = '商务';
            }else{
                $list['list'][$k]['department'] = '外销';
            }
        }
        $dept = getUserDepartment();
        $this->assign('total',$list['total']);
        $this->assign('heji',$list['heji']);
        $this->assign('department',$dept);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$managers);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * 查询历史城市续费详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [会员合作详情数组]
     */
    private function getCityReNewConn($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }
        $count = D('Salecount')->getCityReNewCount($map);
        $list = D('Salecount')->getCityReNewConn($map, 'p.px5 DESC,m.ratio desc,m.id asc',($pageIndex-1)*$pageCount,$pageCount);
        foreach ($list as $k => $v) {
            $list[$k]['over_vip'] = round($v['over_vip'],1);
            $list[$k]['renew_percent'] = number_format($v['renew_percent'],1,'.','').'%';
            $list[$k]['renew_point'] = number_format($v['renew_point'],1,'.','').'%';

            $list[$k]['renew_compass'] = number_format($v['renew_compass'],1,'.','').'%';
            $list[$k]['year_renew_num'] = number_format($v['year_renew_num'],1,'.','').'%';
            //$list[$k]['renew_rare'] = number_format($v['renew_rare'],1,'.','').'%';
            $list[$k]['renew_max'] = number_format($v['renew_max'],1,'.','').'%';
            //系数后续费率
            if($v['dept'] == 2){
                //外销   系数后续费率=实际续费数/（到期数*本月续费率指标）*61.5%
                $list[$k]['renew_rare'] = ($v['realnum']/($v['daoqi']*($v['renew_point']/100)))*(0.615);

            }else if($v['dept'] == 1){
                //商务  系数后续费率=实际续费数/（到期数*本月续费率指标）*59%
                $list[$k]['renew_rare'] = ($v['realnum']/($v['daoqi']*($v['renew_point']/100)))*(0.59);
            }  
            $list[$k]['renew_rare'] = (number_format($list[$k]['renew_rare']*100,1,'.','')).'%';  
        }
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        //查询所有,导出用
        $all = D('Salecount')->getCityReNewAll($map, 'm.id ASC');
        S("Cache:AllCityReNewConn",$all,15*60);
        
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //添加合计、总计
        $list_num = count($list);
        foreach ($list as $k => $v) {
            $heji['city'] = '本页合计';// 城市  
            $heji['ratio'] = '-';// 城市重点系数  
            $heji['department'] = '-';// 部门  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['daoqi'] += $v['daoqi'];// 到期数     
            $heji['realnum'] += $v['realnum'];// 实际续费数   
            $heji['renew_percent'] += (float)$v['renew_percent'];// 续费率     
            $heji['renew_point'] += (float)$v['renew_point'];// 续费率指标  
            $heji['renew_compass'] += (float)$v['renew_compass'];// 续费率达成   
            $heji['year_renew_num'] += (float)$v['year_renew_num'];// 全年续费率均值  
            $heji['renew_rare'] += $v['renew_rare'];// 系数后续费率  
            $heji['renew_max'] += (float)$v['renew_max'];// 续费率封顶值     
            $heji['over_vip'] += $v['over_vip'];// 超出会员数
        }
        $heji['renew_percent'] = number_format($heji['renew_percent']/$list_num,1,'.','');
        $heji['renew_percent'] = $heji['renew_percent'].'%';
        $heji['renew_point'] = number_format($heji['renew_point']/$list_num,1,'.','');// 续费率指标 
        $heji['renew_point'] = $heji['renew_point'].'%'; 
        $heji['renew_compass'] = number_format($heji['renew_compass']/$list_num,1,'.','');// 续费率达成  
        $heji['renew_compass'] = $heji['renew_compass'].'%';  
        $heji['year_renew_num'] = number_format($heji['year_renew_num']/$list_num,1,'.','');// 全年续费率均值
        $heji['year_renew_num'] = $heji['year_renew_num'].'%';   
        $heji['renew_rare'] = number_format($heji['renew_rare']/$list_num,1,'.','');// 系数后续费率
        $heji['renew_rare'] = $heji['renew_rare'].'%'; 
        $heji['renew_max'] = number_format($heji['renew_max']/$list_num,1);// 续费率封顶值
        $heji['renew_max'] = number_format($heji['renew_max'],1,'.','').'%'; 
        $heji['over_vip'] = round($heji['over_vip'],1);
        $all_num = count($all);
        foreach ($all as $k => $v) {
            $total['city'] = '本项合计';// 城市  
            $total['ratio'] = '-';// 城市重点系数  
            $total['department'] = '-';// 部门  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['daoqi'] += $v['daoqi'];// 到期数     
            $total['realnum'] += $v['realnum'];// 实际续费数   
            $total['renew_percent'] += (float)$v['renew_percent'];// 续费率     
            $total['renew_point'] += (float)$v['renew_point'];// 续费率指标  
            $total['renew_compass'] += (float)$v['renew_compass'];// 续费率达成   
            $total['year_renew_num'] += (float)$v['year_renew_num'];// 全年续费率均值  
            $total['renew_rare'] += $v['renew_rare'];// 系数后续费率  
            $total['renew_max'] += (float)$v['renew_max'];// 续费率封顶值     
            $total['over_vip'] += $v['over_vip'];// 超出会员数
        }
        $total['renew_percent'] = number_format($total['renew_percent']/$all_num,1,'.','');
        $total['renew_percent'] = $total['renew_percent'].'%';
        $total['renew_point'] = number_format($total['renew_point']/$all_num,1,'.','');// 续费率指标 
        $total['renew_point'] = $total['renew_point'].'%'; 
        $total['renew_compass'] = number_format($total['renew_compass']/$all_num,1,'.','');// 续费率达成  
        $total['renew_compass'] = $total['renew_compass'].'%';  
        $total['year_renew_num'] = number_format($total['year_renew_num']/$all_num,1,'.','');// 全年续费率均值
        $total['year_renew_num'] = $total['year_renew_num'].'%';   
        $total['renew_rare'] = number_format($total['renew_rare']/$all_num,1,'.','');// 系数后续费率
        $total['renew_rare'] = $total['renew_rare'].'%';
        $total['renew_max'] = number_format($total['renew_max']/$all_num,1,'.','');// 系数后续费率
        $total['renew_max'] = number_format($total['renew_max'],1,'.','').'%';
        $total['over_vip'] = round($total['over_vip'],1);
        $result['total'] = $total;
        $result['heji'] = $heji;
        
        return $result;
    }

    /**
     * 查询本月城市续费详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [会员合作详情数组]
     */
    private function setCityReNewCounts($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D('Salecount')->getThisMonthCityReNewCount($map);
        $list = D('Salecount')->setCityReNewCounts(($pageIndex-1)*$pageCount,$pageCount, 'p.px5 DESC,m.ratio desc,m.id asc',$map);
        foreach ($list as $k => $v) {
            //系数后续费率
            if($v['dept'] == 2){
                //外销   系数后续费率=实际续费数/（到期数*本月续费率指标）*61.5%
                $list[$k]['renew_rare'] = ($v['realnum']/($v['daoqi']*($v['renew_point']/100)))*(0.615);

            }else if($v['dept'] == 1){
                //商务  系数后续费率=实际续费数/（到期数*本月续费率指标）*59%
                $list[$k]['renew_rare'] = ($v['realnum']/($v['daoqi']*($v['renew_point']/100)))*(0.59);
            }  
            $list[$k]['renew_rare'] = (number_format($list[$k]['renew_rare']*100,1,'.','')).'%';
        }
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        //查询所有,导出用
        $all = D('Salecount')->setCityReNewCountsAll($map, 'p.px5 DESC,m.ratio desc,m.id asc');
        S("Cache:AllCityReNewConn",$all,15*60);
        
        $result['list'] = $list;
        $result['totalnum'] = $count;
        //添加合计、总计
        $list_num = count($list);
        foreach ($list as $k => $v) {
            $heji['city'] = '本页合计';// 城市  
            $heji['ratio'] = '-';// 城市重点系数  
            $heji['department'] = '-';// 部门  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['daoqi'] += $v['daoqi'];// 到期数     
            $heji['realnum'] += $v['realnum'];// 实际续费数   
            $heji['renew_percent'] += (float)$v['renew_percent'];// 续费率     
            $heji['renew_point'] += (float)$v['renew_point'];// 续费率指标  
            $heji['renew_compass'] += (float)$v['renew_compass'];// 续费率达成   
            $heji['year_renew_num'] += (float)$v['year_renew_num'];// 全年续费率均值  
            $heji['renew_rare'] += $v['renew_rare'];// 系数后续费率  
            $heji['renew_max'] += (float)$v['renew_max'];// 续费率封顶值     
            $heji['over_vip'] += $v['over_vip'];// 超出会员数
        }
        $heji['renew_percent'] = number_format(($heji['renew_percent']/$list_num),1,'.','');
        $heji['renew_percent'] = $heji['renew_percent'].'%';
        $heji['renew_point'] = number_format($heji['renew_point']/$list_num,1,'.','');// 续费率指标 
        $heji['renew_point'] = $heji['renew_point'].'%'; 
        $heji['renew_compass'] = number_format($heji['renew_compass']/$list_num,1,'.','');// 续费率达成  
        $heji['renew_compass'] = $heji['renew_compass'].'%';  
        $heji['year_renew_num'] = number_format($heji['year_renew_num']/$list_num,1,'.','');// 全年续费率均值
        $heji['year_renew_num'] = $heji['year_renew_num'].'%';   
        $heji['renew_rare'] = number_format($heji['renew_rare']/$list_num,1,'.','');// 系数后续费率
        $heji['renew_rare'] = $heji['renew_rare'].'%'; 
        $heji['renew_max'] = number_format($heji['renew_max']/$list_num,1,'.','');// 续费率封顶值
        $heji['renew_max'] = number_format($heji['renew_max'],1,'.','').'%'; 
        $heji['over_vip'] = round($heji['over_vip'],1);
        $all_num = count($all);
        foreach ($all as $k => $v) {
            $total['city'] = '本项合计';// 城市  
            $total['ratio'] = '-';// 城市重点系数  
            $total['department'] = '-';// 部门  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['daoqi'] += $v['daoqi'];// 到期数     
            $total['realnum'] += $v['realnum'];// 实际续费数   
            $total['renew_percent'] += (float)$v['renew_percent'];// 续费率     
            $total['renew_point'] += (float)$v['renew_point'];// 续费率指标  
            $total['renew_compass'] += (float)$v['renew_compass'];// 续费率达成   
            $total['year_renew_num'] += (float)$v['year_renew_num'];// 全年续费率均值  
            $total['renew_rare'] += $v['renew_rare'];// 系数后续费率  
            $total['renew_max'] += (float)$v['renew_max'];// 续费率封顶值     
            $total['over_vip'] += $v['over_vip'];// 超出会员数
        }
        $total['renew_percent'] = number_format($total['renew_percent']/$all_num,1,'.','');
        $total['renew_percent'] = $total['renew_percent'].'%';
        $total['renew_point'] = number_format($total['renew_point']/$all_num,1,'.','');// 续费率指标 
        $total['renew_point'] = $total['renew_point'].'%'; 
        $total['renew_compass'] = number_format($total['renew_compass']/$all_num,1,'.','');// 续费率达成  
        $total['renew_compass'] = $total['renew_compass'].'%';  
        $total['year_renew_num'] = number_format($total['year_renew_num']/$all_num,1,'.','');// 全年续费率均值
        $total['year_renew_num'] = $total['year_renew_num'].'%';   
        $total['renew_rare'] = number_format($total['renew_rare']/$all_num,1,'.','');// 系数后续费率
        $total['renew_rare'] = $total['renew_rare'].'%';
        $total['renew_max'] = number_format($total['renew_max']/$all_num,1,'.','');// 系数后续费率
        $total['renew_max'] = number_format($total['renew_max'],1,'.','').'%'; 
        $total['over_vip'] = round($total['over_vip'],1);
        $result['total'] = $total;
        $result['heji'] = $heji;

        return $result;
    }


    /*
     * 城市续费月数完成率
     * 
    */
    public function cityReNewMonthPercent()
    {
        //获取自己的管辖城市
        //$citys = getUserCitys();
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Salecount')->getAdminsByPosition();

        foreach ($brand as $k => $v) {
            foreach ($v as $key => $val) {
                $managers[$k][$val['id']]['id'] = $val['id']; 
                $managers[$k][$val['id']]['name'] = $val['name']; 
                $managers[$k][$val['id']]['uid'] = $val['uid']; 
            }
        }
        //搜索关键词 
        $keyword['city']            = I("get.city");
        $keyword['department']      = I("get.department");
        $keyword['time']            = I("get.time");
        $keyword['pshizhang']       = I("get.pshizhang");
        $keyword['ptuanzhang']      = I("get.ptuanzhang");
        $keyword['pinpai']          = I("get.pinpai");
        $keyword['tshizhang']       = I("get.tshizhang");
        $keyword['ttuanzhang']      = I("get.ttuanzhang");
        $keyword['csjl']            = I("get.csjl");


        //获取所有城市列表
        $citys = getUserSaleCitys();
        $ids = '';
        foreach ($citys as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            if(!empty($keyword['city']) && in_array($keyword['city'], $ids)){
                $keyword['city']            = I("get.city");
            }else{
                $idstr = implode(',', $ids);
                $keyword['city'] = array('IN',$idstr); 
            }
        }

        //获取
        //查询已有会员指标
        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 20;
        $time_now = date("Y-m",time());

        if($time_now != $keyword['time'] && $keyword['time'] != ''){
            //查询历史数据
            $list = $this->getCityReNewMonthConn($pageIndex, $pageCount, $sort, $keyword);
        }else{
            //查询本月实时数据
            $list = $this->getThisMonthCityReNewMonthConn($pageIndex, $pageCount, '', $keyword);
        }

        
        foreach ($list['list'] as $k => $v) {
            if($v['dept'] == 1){
                $list['list'][$k]['department'] = '商务';
            }else{
                $list['list'][$k]['department'] = '外销';
            }
        }
        //var_dump($list);
        $keyword['city']            = I("get.city");
        $dept = getUserDepartment();

        $this->assign('total',$list['total']);
        $this->assign('heji',$list['heji']);
        $this->assign('department',$dept);
        $this->assign('page',$list['page']);
        $this->assign('list',$list['list']);
        $this->assign('keyword',$keyword);
        $this->assign('brand',$managers);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * 查询城市续费月数完成率详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [会员合作详情数组]
     */
    private function getCityReNewMonthConn($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }
        $count = D('Salecount')->getCityReNewMonthCount($map);
        $list = D('Salecount')->getCityReNewMonthConn($map, 'p.px6 DESC,m.id asc' ,($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        
        
        //查询所有,导出用
        $all = D('Salecount')->getCityReNewMonthAll($map, 'm.id ASC');
        S("Cache:AllCityReNewMonthConn",$all,15*60);

        //添加总计、合计
        $list_num = count($list);
        foreach ($list as $k => $v) {
            $list[$k]['renew_month_point'] = number_format($v['renew_month_point'],1,'.','');
            $list[$k]['renew_month_rate'] = number_format($v['renew_month_rate'],1,'.','').'%';
            $list[$k]['renew_month_rate_point'] = number_format($v['renew_month_rate_point'],1,'.','').'%';
            $list[$k]['renew_month_rate_complete'] = number_format($v['renew_month_rate_complete'],1,'.','').'%';

            $list[$k]['renew_year_value'] = number_format($v['renew_year_value'],1,'.','').'%';
            

            //$list[$k]['renew_monthly_rate'] = number_format($v['renew_monthly_rate'],1,'.','').'%';

            if($v['dept'] == 2){
                //外销   系数后续费月数完成率=实际续费月数/（续费月数指标*本月续费月数率指标）*92.3%
                $list[$k]['renew_monthly_rate'] = ($v['renew_month']/($v['renew_month_point']*($v['renew_month_rate_point']/100)))*(0.923);
            }else if($v['dept'] == 1){
                //商务  系数后续费月数完成率=实际续费月数/（续费月数指标*本月续费月数率指标）*88.8%
                $list[$k]['renew_monthly_rate'] = ($v['renew_month']/($v['renew_month_point']*($v['renew_month_rate_point']/100)))*(0.888);
            }  
            $list[$k]['renew_monthly_rate'] = (number_format($list[$k]['renew_monthly_rate']*100,1,'.','')).'%';

            $list[$k]['renew_max'] = number_format($v['renew_max'],1,'.','').'%';

            
            $heji['city'] = '本页合计';// 城市  
            $heji['department'] = '-';// 部门  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['daoqi'] += $v['daoqi'];// 到期数     
            $heji['renew_month_point'] += $v['renew_month_point'];// 续费月数指标  
            $heji['renew_month'] += $v['renew_month'];// 实际续费月数
            $heji['renew_month_rate'] += (float)$v['renew_month_rate'];// 续费月数完成率     
            $heji['renew_month_rate_point'] += (float)$v['renew_month_rate_point'];// 续费月数完成率指标  
            $heji['renew_month_rate_complete'] += (float)$v['renew_month_rate_complete'];// 续费月数完成率达成   
            $heji['renew_year_value'] += (float)$v['renew_year_value'];// 全年续费月数完成率均值   
            $heji['renew_monthly_rate'] += (float)$v['renew_monthly_rate'];// 系数后续费月数完成率     
            $heji['renew_max'] += (float)$v['renew_max'];// 续费月数完成率封顶值     
            $heji['over_month'] += $v['over_month'];// 超出月数
        }
        $heji['renew_month_rate'] = number_format($heji['renew_month_rate']/$list_num,1,'.','');// 续费月数完成率     
        $heji['renew_month_rate_point'] = number_format($heji['renew_month_rate_point']/$list_num,1,'.','');// 续费月数完成率指标  
        $heji['renew_month_rate_complete'] = number_format($heji['renew_month_rate_complete']/$list_num,1,'.','');// 续费月数完成率达成   
        $heji['renew_year_value'] = number_format($heji['renew_year_value']/$list_num,1,'.','');// 全年续费月数完成率均值   
        $heji['renew_monthly_rate'] = number_format($heji['renew_monthly_rate']/$list_num,1,'.','');// 系数后续费月数完成率     
        $heji['renew_max'] = number_format($heji['renew_max']/$list_num,1,'.','');// 续费月数完成率封顶值

        $all_num = count($all);
        foreach ($all as $k => $v) {
            $total['city'] = '本项合计';// 城市  
            $total['department'] = '-';// 部门  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['daoqi'] += $v['daoqi'];// 到期数     
            $total['renew_month_point'] += $v['renew_month_point'];// 续费月数指标  
            $total['renew_month'] += $v['renew_month'];// 实际续费月数
            $total['renew_month_rate'] += (float)$v['renew_month_rate'];// 续费月数完成率     
            $total['renew_month_rate_point'] += (float)$v['renew_month_rate_point'];// 续费月数完成率指标  
            $total['renew_month_rate_complete'] += (float)$v['renew_month_rate_complete'];// 续费月数完成率达成   
            $total['renew_year_value'] += (float)$v['renew_year_value'];// 全年续费月数完成率均值   
            $total['renew_monthly_rate'] += (float)$v['renew_monthly_rate'];// 系数后续费月数完成率     
            $total['renew_max'] += (float)$v['renew_max'];// 续费月数完成率封顶值     
            $total['over_month'] += $v['over_month'];// 超出月数
        }
        $total['renew_month_rate'] = number_format($total['renew_month_rate']/$all_num,1,'.','');// 续费月数完成率     
        $total['renew_month_rate_point'] = number_format($total['renew_month_rate_point']/$all_num,1,'.','');// 续费月数完成率指标  
        $total['renew_month_rate_complete'] = number_format($total['renew_month_rate_complete']/$all_num,1,'.','');// 续费月数完成率达成   
        $total['renew_year_value'] = number_format($total['renew_year_value']/$all_num,1,'.','');// 全年续费月数完成率均值   
        $total['renew_monthly_rate'] = number_format($total['renew_monthly_rate']/$all_num,1,'.','');// 系数后续费月数完成率     
        $total['renew_max'] = number_format($total['renew_max']/$all_num,1,'.','');// 续费月数完成率封顶值 
        $result['heji'] = $heji;
        $result['total'] = $total;
        $result['list'] = $list;
        $result['totalnum'] = $count;
        return $result;
    }

    /**
     * 查询本月城市续费月数完成率详情
     * @param  [string] $pageIndex      [页码]
     * @param  [string] $pageCount      [分页量]
     * @param  [string] $sort           [排序]
     * @param  [array]  $map            [搜索条件]
     * @return [array]  $result         [会员合作详情数组]
     */
    private function getThisMonthCityReNewMonthConn($pageIndex, $pageCount, $sort, $map)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        /*if(!empty($map['time'])){
            $map['time'] = strtotime($map['time'].'-01');//转化为时间戳
        }*/
        $count = D('Salecount')->getThisMonthCityReNewCount($map);
        $list = D('Salecount')->getThisMonthCityReNewMonthConn($map, 'p.px6 DESC,m.ratio desc,m.id asc' ,($pageIndex-1)*$pageCount,$pageCount);
        foreach ($list as $k => $v) {
            if($v['dept'] == 2){
                //外销   系数后续费月数完成率=实际续费月数/（续费月数指标*本月续费月数率指标）*92.3%
                $list[$k]['renew_monthly_rate'] = ($v['renew_month']/($v['renew_month_point']*($v['renew_month_rate_point']/100)))*(0.923);
            }else if($v['dept'] == 1){
                //商务  系数后续费月数完成率=实际续费月数/（续费月数指标*本月续费月数率指标）*88.8%
                $list[$k]['renew_monthly_rate'] = ($v['renew_month']/($v['renew_month_point']*($v['renew_month_rate_point']/100)))*(0.888);
            }  
            $list[$k]['renew_monthly_rate'] = (number_format($list[$k]['renew_monthly_rate']*100,1,'.','')).'%';
        }
        if($count > $pagecount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        //查询所有,导出用
        $all = D('Salecount')->getThisMonthCityReNewMonthConn($map, 'p.px6 DESC,m.ratio desc,m.id asc');
        S("Cache:AllCityReNewMonthConn",$all,15*60);
        //添加总计、合计
        $list_num = count($list);
        foreach ($list as $k => $v) {
            $heji['city'] = '本页合计';// 城市  
            $heji['department'] = '-';// 部门  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['daoqi'] += $v['daoqi'];// 到期数     
            $heji['renew_month_point'] += $v['renew_month_point'];// 续费月数指标  
            $heji['renew_month'] += $v['renew_month'];// 实际续费月数
            $heji['renew_month_rate'] += (float)$v['renew_month_rate'];// 续费月数完成率     
            $heji['renew_month_rate_point'] += (float)$v['renew_month_rate_point'];// 续费月数完成率指标  
            $heji['renew_month_rate_complete'] += (float)$v['renew_month_rate_complete'];// 续费月数完成率达成   
            $heji['renew_year_value'] += (float)$v['renew_year_value'];// 全年续费月数完成率均值   
            $heji['renew_monthly_rate'] += (float)$v['renew_monthly_rate'];// 系数后续费月数完成率     
            $heji['renew_max'] += (float)$v['renew_max'];// 续费月数完成率封顶值     
            $heji['over_month'] += $v['over_month'];// 超出月数
        }
        $heji['renew_month_rate'] = number_format($heji['renew_month_rate']/$list_num,1,'.','');// 续费月数完成率     
        $heji['renew_month_rate_point'] = number_format($heji['renew_month_rate_point']/$list_num,1,'.','');// 续费月数完成率指标  
        $heji['renew_month_rate_complete'] = number_format($heji['renew_month_rate_complete']/$list_num,1,'.','');// 续费月数完成率达成   
        $heji['renew_year_value'] = number_format($heji['renew_year_value']/$list_num,1,'.','');// 全年续费月数完成率均值   
        $heji['renew_monthly_rate'] = (number_format($heji['renew_monthly_rate']/$list_num,1,'.','')).'%';// 系数后续费月数完成率     
        $heji['renew_max'] = number_format($heji['renew_max']/$list_num,1,'.','');// 续费月数完成率封顶值

        $all_num = count($all);
        foreach ($all as $k => $v) {
            $total['city'] = '本项合计';// 城市  
            $total['department'] = '-';// 部门  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['daoqi'] += $v['daoqi'];// 到期数     
            $total['renew_month_point'] += $v['renew_month_point'];// 续费月数指标  
            $total['renew_month'] += $v['renew_month'];// 实际续费月数
            $total['renew_month_rate'] += (float)$v['renew_month_rate'];// 续费月数完成率     
            $total['renew_month_rate_point'] += (float)$v['renew_month_rate_point'];// 续费月数完成率指标  
            $total['renew_month_rate_complete'] += (float)$v['renew_month_rate_complete'];// 续费月数完成率达成   
            $total['renew_year_value'] += (float)$v['renew_year_value'];// 全年续费月数完成率均值   
            $total['renew_monthly_rate'] += (float)$v['renew_monthly_rate'];// 系数后续费月数完成率     
            $total['renew_max'] += (float)$v['renew_max'];// 续费月数完成率封顶值     
            $total['over_month'] += $v['over_month'];// 超出月数
        }
        $total['renew_month_rate'] = number_format($total['renew_month_rate']/$all_num,1,'.','');// 续费月数完成率     
        $total['renew_month_rate_point'] = number_format($total['renew_month_rate_point']/$all_num,1,'.','');// 续费月数完成率指标  
        $total['renew_month_rate_complete'] = number_format($total['renew_month_rate_complete']/$all_num,1,'.','');// 续费月数完成率达成   
        $total['renew_year_value'] = number_format($total['renew_year_value']/$all_num,1,'.','');// 全年续费月数完成率均值   
        $total['renew_monthly_rate'] = (number_format($total['renew_monthly_rate']/$all_num,1,'.','')).'%';// 系数后续费月数完成率     
        $total['renew_max'] = number_format($total['renew_max']/$all_num,1,'.','');// 续费月数完成率封顶值 
        $result['heji'] = $heji;
        $result['total'] = $total;

        $result['list'] = $list;
        $result['totalnum'] = $count;
        return $result;
    }

    /*
     * 统计全瞰-续费率
     * 
    */
    public function renewPercentTable()
    {
        $keyword['department'] = I('get.department');
        $keyword['time'] = I('get.date');
        //如果没有department，则默认为0（营销中心）:系数由中心续费月度系数提供、数据来自qz_sale_cityrenew
        if(empty($keyword['department'])){
            $keyword['department'] = 0;
            //部门
            if($_SESSION['uc_userinfo']['department_id'] == 5){
                //外销
                $keyword['department'] = 2;
            }elseif($_SESSION['uc_userinfo']['department_id'] == 6){
                //商务
                $keyword['department'] = 1;
            }
        }
        $year = date('Y',time());
        $month = intval(date('m',time()));
        $natural_month = date('m',time());

        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            $idstr = implode(',', $ids);
            $condition['city'] = array('IN',$idstr); 

            if(empty($keyword['time']) || $keyword['time'] == $year){
                $natural_month = $year.'-'.$natural_month;
                if($month == 1){
                    $year = $year - 1;
                }
                //如果没有选择财年，或者选择当年，则查询当年已有数据，没有数据的月份留空
                //可能的条件：1，没有选择财年，且不是1月  2，选择了今年，且不是1月
                //在此生成财年月份
                $nextyear = $year+1;
                $title = [
                    $year.'-02',
                    $year.'-03',
                    $year.'-04',
                    $year.'-05',
                    $year.'-06',
                    $year.'-07',
                    $year.'-08',
                    $year.'-09',
                    $year.'-10',
                    $year.'-11',
                    $year.'-12',
                    $nextyear.'-01'
                ];
                foreach ($title as $key => $value) {
                    if($key >= ($month-1) && $month != 1){
                        unset($title[$key]);
                    }
                }
                foreach ($title as $k => $v) {
                    $list[$k]['month'] = $v;
                    if($v == $natural_month){
                        //本月实时查询
                        $data = D('Salecount')->getThisMonthRenewPercentTableInfo($keyword,'','',$condition);
                        $list[$k] = $data;
                    }else{
                        //查询sale_cityvips
                        $data = D('Salecount')->getRenewPercentTableInfo($v,$keyword['department'],$condition);
                        $list[$k] = $data;
                    }
                    
                }
                //var_dump($list);
                //生成图表数组，需要续费率指标  实际续费率  全年续费率均值 续费率封顶值 到期数 5组数据
                foreach ($list as $k => $v) {
                    //续费率指标
                    if($v['xflzb'] > 0){
                        $table_data['xflzb'][] = floatval($v['xflzb']);
                    }else{
                        $table_data['xflzb'][] = 0;
                    }
                    //实际续费率
                    if($v['xufeilv'] > 0){
                        $table_data['xufeilv'][] = floatval($v['xufeilv']);
                    }else{
                        $table_data['xufeilv'][] = 0;
                    }
                    //全年续费率均值
                    if($v['qnxfjz'] > 0){
                        $table_data['qnxfjz'][] = floatval($v['qnxfjz']);
                    }else{
                        $table_data['qnxfjz'][] = 0;
                    }
                    //续费率封顶值
                    if($v['xflzg'] > 0){
                        $table_data['xflzg'][] = floatval($v['xflzg']);
                    }else{
                        $table_data['xflzg'][] = 0;
                    }
                    //到期数
                    if($v['total_daoqi'] > 0){
                        $table_data['total_daoqi'][] = floatval($v['total_daoqi']);
                    }else{
                        $table_data['total_daoqi'][] = 0;
                    }
                }
                foreach ($table_data as $k => $v) {
                    $table_data[$k] = implode(',', $v);
                }
                $table_data['year'] = $year;
                //查询去年的数据
                $last_year = $year - 1;
                $last_title = [
                    $last_year.'-02',
                    $last_year.'-03',
                    $last_year.'-04',
                    $last_year.'-05',
                    $last_year.'-06',
                    $last_year.'-07',
                    $last_year.'-08',
                    $last_year.'-09',
                    $last_year.'-10',
                    $last_year.'-11',
                    $last_year.'-12',
                    $year.'-01'
                ];
                foreach ($last_title as $k => $v) {
                    $data = D('Salecount')->getRenewPercentTableInfo($v,$keyword['department'],$condition);
                    $last_list[] = $data;
                }
                foreach ($last_list as $k => $v) {
                    //实际续费率
                    if($v['xufeilv'] > 0){
                        $table_last['xufeilv'][] = floatval($v['xufeilv']);
                    }else{
                        $table_last['xufeilv'][] = 0;
                    }
                    //全年续费率均值
                    if($v['qnxfjz'] > 0){
                        $table_last['qnxfjz'][] = floatval($v['qnxfjz']);
                    }else{
                        $table_last['qnxfjz'][] = 0;
                    }
                    //到期数
                    if($v['total_daoqi'] > 0){
                        $table_last['total_daoqi'][] = floatval($v['total_daoqi']);
                    }else{
                        $table_last['total_daoqi'][] = 0;
                    }
                }
                foreach ($table_last as $k => $v) {
                    $table_last[$k] = implode(',', $v);
                }
                $table_last['year'] = $last_year;
            }else{
                //查询数据并整合，没有数据的年份，统一显示空数据
                //在此生成财年月份
                if($keyword['time'] < $year){
                    $nextyear = $keyword['time']+1;
                    $title = [
                        $keyword['time'].'-02',
                        $keyword['time'].'-03',
                        $keyword['time'].'-04',
                        $keyword['time'].'-05',
                        $keyword['time'].'-06',
                        $keyword['time'].'-07',
                        $keyword['time'].'-08',
                        $keyword['time'].'-09',
                        $keyword['time'].'-10',
                        $keyword['time'].'-11',
                        $keyword['time'].'-12',
                        $nextyear.'-01'
                    ];
                    foreach ($title as $k => $v) {
                        $list[$k]['month'] = $v;
                        //查询sale_cityvips
                        $data = D('Salecount')->getRenewPercentTableInfo($v,$keyword['department'],$condition);
                            $list[$k] = $data;
                    }
                    //生成图表数组，需要续费率指标  实际续费率  全年续费率均值 续费率封顶值 到期数 5组数据
                    foreach ($list as $k => $v) {
                        //续费率指标
                        if($v['xflzb'] > 0){
                            $table_data['xflzb'][] = floatval($v['xflzb']);
                        }else{
                            $table_data['xflzb'][] = 0;
                        }
                        //实际续费率
                        if($v['xufeilv'] > 0){
                            $table_data['xufeilv'][] = floatval($v['xufeilv']);
                        }else{
                            $table_data['xufeilv'][] = 0;
                        }
                        //全年续费率均值
                        if($v['qnxfjz'] > 0){
                            $table_data['qnxfjz'][] = floatval($v['qnxfjz']);
                        }else{
                            $table_data['qnxfjz'][] = 0;
                        }
                        //续费率封顶值
                        if($v['xflzg'] > 0){
                            $table_data['xflzg'][] = floatval($v['xflzg']);
                        }else{
                            $table_data['xflzg'][] = 0;
                        }
                        //到期数
                        if($v['total_daoqi'] > 0){
                            $table_data['total_daoqi'][] = floatval($v['total_daoqi']);
                        }else{
                            $table_data['total_daoqi'][] = 0;
                        }
                    }
                    foreach ($table_data as $k => $v) {
                        $table_data[$k] = implode(',', $v);
                    }
                    $table_data['year'] = $keyword['time'];
                    //查询去年的数据
                    $last_year = $keyword['time'] - 1;
                    $last_title = [
                        $last_year.'-02',
                        $last_year.'-03',
                        $last_year.'-04',
                        $last_year.'-05',
                        $last_year.'-06',
                        $last_year.'-07',
                        $last_year.'-08',
                        $last_year.'-09',
                        $last_year.'-10',
                        $last_year.'-11',
                        $last_year.'-12',
                        $keyword['time'].'-01'
                    ];
                    foreach ($last_title as $k => $v) {
                        $data = D('Salecount')->getRenewPercentTableInfo($v,$keyword['department'],$condition);
                        $last_list[] = $data;
                    }
                    foreach ($last_list as $k => $v) {
                        //实际续费率
                        if($v['xufeilv'] > 0){
                            $table_last['xufeilv'][] = floatval($v['xufeilv']);
                        }else{
                            $table_last['xufeilv'][] = 0;
                        }
                        //全年续费率均值
                        if($v['qnxfjz'] > 0){
                            $table_last['qnxfjz'][] = floatval($v['qnxfjz']);
                        }else{
                            $table_last['qnxfjz'][] = 0;
                        }
                        //到期数
                        if($v['total_daoqi'] > 0){
                            $table_last['total_daoqi'][] = floatval($v['total_daoqi']);
                        }else{
                            $table_last['total_daoqi'][] = 0;
                        }
                    }
                    foreach ($table_last as $k => $v) {
                        $table_last[$k] = implode(',', $v);
                    }
                    $table_last['year'] = $last_year;
                    
                }  
            }
        }
        //var_dump($condition);
        
        $dept = getUserDepartment();
        $this->assign('department',$dept);
        $this->assign('table_last',$table_last);//去年数据
        $this->assign('table_data',$table_data);//今年数据
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    /*
     * 统计全瞰-续费月数完成率
     * 
    */
    public function renewMonthRateTable()
    {
        $keyword['department'] = I('get.department');
        $keyword['time'] = I('get.date');
        //如果没有department，则默认为0（营销中心）:系数由中心续费月度系数提供、数据来自qz_sale_cityrenew
        if(empty($keyword['department'])){
            $keyword['department'] = 0;
            //部门
            if($_SESSION['uc_userinfo']['department_id'] == 5){
                //外销
                $keyword['department'] = 2;
            }elseif($_SESSION['uc_userinfo']['department_id'] == 6){
                //商务
                $keyword['department'] = 1;
            }
        }
        $year = date('Y',time());
        $month = intval(date('m',time()));
        $natural_month = date('m',time());

        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['city']; 
        }
        if($ids !== ''){
            $idstr = implode(',', $ids);
            $condition['city'] = array('IN',$idstr); 

            if(empty($keyword['time']) || $keyword['time'] == $year){
                $natural_month = $year.'-'.$natural_month;
                if($month == 1){
                    $year = $year - 1;
                }
                //如果没有选择财年，或者选择当年，则查询当年已有数据，没有数据的月份留空
                //可能的条件：1，没有选择财年，且不是1月  2，选择了今年，且不是1月
                //在此生成财年月份
                $nextyear = $year+1;
                $title = [
                    $year.'-02',
                    $year.'-03',
                    $year.'-04',
                    $year.'-05',
                    $year.'-06',
                    $year.'-07',
                    $year.'-08',
                    $year.'-09',
                    $year.'-10',
                    $year.'-11',
                    $year.'-12',
                    $nextyear.'-01'
                ];
                foreach ($title as $key => $value) {
                    if($key >= ($month-1) && $month != 1){
                        unset($title[$key]);
                    }
                }
                foreach ($title as $k => $v) {
                    $list[$k]['month'] = $v;
                    if($v == $natural_month){
                        //本月实时查询
                        $data = D('Salecount')->getRenewMonthTableInfo($keyword,'',$condition);
                        $list[$k] = $data;
                    }else{
                        //查询sale_cityvips
                        $data = D('Salecount')->getRenewMonthRateInfo($v,$keyword['department'],$condition);
                        $list[$k] = $data;
                    }
                }
                //生成图表数组，需要续费月数完成率指标  续费月数完成率  全年续费月数完成率率均值 续费月数完成率率封顶值 4组数据
                foreach ($list as $k => $v) {
                    //续费月数完成率指标
                    if($v['xfyswczb'] > 0){
                        $table_data['xfyswczb'][] = floatval($v['xfyswczb']);
                    }else{
                        $table_data['xfyswczb'][] = 0;
                    }
                    //续费月数完成率
                    if($v['xufeilyuewanchenglv'] > 0){
                        $table_data['xufeilyuewanchenglv'][] = floatval($v['xufeilyuewanchenglv']);
                    }else{
                        $table_data['xufeilyuewanchenglv'][] = 0;
                    }
                    //全年续费月数完成率率均值
                    if($v['qnxfyswcljz'] > 0){
                        $table_data['qnxfyswcljz'][] = floatval($v['qnxfyswcljz']);
                    }else{
                        $table_data['qnxfyswcljz'][] = 0;
                    }
                    //续费月数完成率率封顶值
                    if($v['xflyswczg'] > 0){
                        $table_data['xflyswczg'][] = floatval($v['xflyswczg']);
                    }else{
                        $table_data['xflyswczg'][] = 0;
                    }
                }
                foreach ($table_data as $k => $v) {
                    $table_data[$k] = implode(',', $v);
                }
                $table_data['year'] = $year;
                //查询去年的数据
                $last_year = $year - 1;
                $last_title = [
                    $last_year.'-02',
                    $last_year.'-03',
                    $last_year.'-04',
                    $last_year.'-05',
                    $last_year.'-06',
                    $last_year.'-07',
                    $last_year.'-08',
                    $last_year.'-09',
                    $last_year.'-10',
                    $last_year.'-11',
                    $last_year.'-12',
                    $year.'-01'
                ];
                foreach ($last_title as $k => $v) {
                    $data = D('Salecount')->getRenewMonthRateInfo($v,$keyword['department'],$condition);
                    $last_list[] = $data;
                }
                foreach ($last_list as $k => $v) {
                    //续费月数完成率
                    if($v['xufeilyuewanchenglv'] > 0){
                        $table_last['xufeilyuewanchenglv'][] = floatval($v['xufeilyuewanchenglv']);
                    }else{
                        $table_last['xufeilyuewanchenglv'][] = 0;
                    }
                    //全年续费月数完成率率均值
                    if($v['qnxfyswcljz'] > 0){
                        $table_last['qnxfyswcljz'][] = floatval($v['qnxfyswcljz']);
                    }else{
                        $table_last['qnxfyswcljz'][] = 0;
                    }
                }
                foreach ($table_last as $k => $v) {
                    $table_last[$k] = implode(',', $v);
                }
                $table_last['year'] = $last_year;
            }else{
                //查询数据并整合，没有数据的年份，统一显示空数据
                //在此生成财年月份
                if($keyword['time'] < $year){
                    $nextyear = $keyword['time']+1;
                    $title = [
                        $keyword['time'].'-02',
                        $keyword['time'].'-03',
                        $keyword['time'].'-04',
                        $keyword['time'].'-05',
                        $keyword['time'].'-06',
                        $keyword['time'].'-07',
                        $keyword['time'].'-08',
                        $keyword['time'].'-09',
                        $keyword['time'].'-10',
                        $keyword['time'].'-11',
                        $keyword['time'].'-12',
                        $nextyear.'-01'
                    ];
                    foreach ($title as $k => $v) {
                        $list[$k]['month'] = $v;
                        //查询sale_cityvips
                        $data = D('Salecount')->getRenewMonthRateInfo($v,$keyword['department'],$condition);
                            $list[$k] = $data;
                    }
                    //生成图表数组，需要续费月数完成率指标  续费月数完成率  全年续费月数完成率率均值 续费月数完成率率封顶值 4组数据
                    foreach ($list as $k => $v) {
                        //续费月数完成率指标
                        if($v['xfyswczb'] > 0){
                            $table_data['xfyswczb'][] = floatval($v['xfyswczb']);
                        }else{
                            $table_data['xfyswczb'][] = 0;
                        }
                        //续费月数完成率
                        if($v['xufeilyuewanchenglv'] > 0){
                            $table_data['xufeilyuewanchenglv'][] = floatval($v['xufeilyuewanchenglv']);
                        }else{
                            $table_data['xufeilyuewanchenglv'][] = 0;
                        }
                        //全年续费月数完成率率均值
                        if($v['qnxfyswcljz'] > 0){
                            $table_data['qnxfyswcljz'][] = floatval($v['qnxfyswcljz']);
                        }else{
                            $table_data['qnxfyswcljz'][] = 0;
                        }
                        //续费月数完成率率封顶值
                        if($v['xflyswczg'] > 0){
                            $table_data['xflyswczg'][] = floatval($v['xflyswczg']);
                        }else{
                            $table_data['xflyswczg'][] = 0;
                        }
                    }
                    foreach ($table_data as $k => $v) {
                        $table_data[$k] = implode(',', $v);
                    }
                    $table_data['year'] = $keyword['time'];
                    //查询去年的数据
                    $last_year = $keyword['time'] - 1;
                    $last_title = [
                        $last_year.'-02',
                        $last_year.'-03',
                        $last_year.'-04',
                        $last_year.'-05',
                        $last_year.'-06',
                        $last_year.'-07',
                        $last_year.'-08',
                        $last_year.'-09',
                        $last_year.'-10',
                        $last_year.'-11',
                        $last_year.'-12',
                        $keyword['time'].'-01'
                    ];
                    foreach ($last_title as $k => $v) {
                        $data = D('Salecount')->getRenewMonthRateInfo($v,$keyword['department'],$condition);
                        $last_list[] = $data;
                    }
                    foreach ($last_list as $k => $v) {
                        //续费月数完成率
                        if($v['xufeilyuewanchenglv'] > 0){
                            $table_last['xufeilyuewanchenglv'][] = floatval($v['xufeilyuewanchenglv']);
                        }else{
                            $table_last['xufeilyuewanchenglv'][] = 0;
                        }
                        //全年续费月数完成率率均值
                        if($v['qnxfyswcljz'] > 0){
                            $table_last['qnxfyswcljz'][] = floatval($v['qnxfyswcljz']);
                        }else{
                            $table_last['qnxfyswcljz'][] = 0;
                        }
                    }
                    foreach ($table_last as $k => $v) {
                        $table_last[$k] = implode(',', $v);
                    }
                    $table_last['year'] = $last_year;
                    
                }  
            }
        }
        $dept = getUserDepartment();
        $this->assign('department',$dept);
        $this->assign('table_last',$table_last);//去年数据
        $this->assign('table_data',$table_data);//今年数据
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    //------------------需要执行计划任务的内容--------------------------------------
    //更新公司合同信息，计划任务每天执行
    /*public function getcompanycontracts()
    {
        $data = D('Salecount')->getCompanyContracts();
        echo 'everything done!';
        //var_dump($data);
    }

    //更新公司会员合作时长信息，计划任务每月第一天凌晨0:10执行
    public function setcityvips()
    {
        $all['all'] = D('Salecount')->getCityVipAll($map, 'm.id ASC');
        $all['xq']  = D('Salecount')->getCityVipAll($map, 'm.id ASC',1);//1新签
        $all['xf']  = D('Salecount')->getCityVipAll($map, 'm.id ASC',2);//2续费
        $data = D('Salecount')->setCityVips($all);
        echo 'everything done!';
    }

    

    //更新城市续费月数完成率信息，计划任务每月10号执行
    public function setCityReNewMonthConn()
    {
        $data = D('Salecount')->setCityReNewMonthCounts();
        //var_dump($data);
        //统计数据写入数据库 qz_sale_cityrenew_months
        $result = D('Salecount')->writeCityReNewMonthIn($data);
        echo 'everything done!';
    }*/

    //导出
    public function exportSaleDatas()
    {
        $time = $_GET['time'];
        $data = D('Salecount')->getCityCompanys($time);

        //导出excel
        //var_dump($data);

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');

        $writer = new \XLSXWriter();
        //标题
        $herder = array(
            '部门',
            '城市',
            '分单量',
            '赠单量'
        );
        $wArr = array_values($herder);
        $writer->writeSheetRow('Sheet1', $herder);

        //数据
        foreach ($data as $k => $v) {
            if($v['dept'] == 1){
                $department = '商务';
            }elseif($v['dept'] == 2){
                $department = '外销';
            }
            $value = array(
                $department,
                $v['city'],
                $v['fendan'],
                $v['zengdan']
            );
            $wArr = array_values($value);
            $writer->writeSheetRow('Sheet1', $value);
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="城市分单量.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer->writeToStdOut("php://output");
    }

}