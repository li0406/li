<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Db;

use Think\Model;

class CompanyModel extends Model
{
    protected $tableName = "user";

    public function selectCompany($company_ids)
    {
		if (empty($company_ids)) {
			return false;
		}
		$map = array(
			'u.id' => ['IN',$company_ids],
			'u.classid' => array("IN",array(3,6))
		);
		//注意此处连表qz_user_company的ID问题
		return M('user')->alias('u')
			->field('u.*')
			->where($map)
			->select();
    }

	public function getCompanyListCount($where)
    {
		if(!empty($where['jc'])){
			$map["_complex"] = array(
				"a.jc" =>  array("LIKE","%".$where['jc']."%"),
				"a.id" =>  array("EQ",$where['jc']),
				"_logic" => "OR"
			);
		}
		if(!empty($where["city"])){
			$map["a.cs"] = $where["city"];
		}
        if(!empty($where["css"])){
            $map["q.cid"] = array("IN",$where["css"]);
        }
		if(!empty($where["area"])){
			$map["a.qx"] = $where["area"];
		}
		if(!empty($where["zuobiao"])){
			if($where["zuobiao"] == 1){
				$map["c.lng"]  = array("EQ",'');
				$map["c.lat"]  = array("EQ",'');
			}else{
				$map["c.lng"]  = array("NEQ",'');
				$map["c.lat"]  = array("NEQ",'');
			}
		}
		if($where['bao'] != ""){
			$map["c.contract"]  = array("EQ",$where['bao']);
		}
		if($where['zhuang'] != ""){
			$map["c.lx"]  = array("EQ",$where['zhuang']);
		}

        if(!empty($where['get_gift_order'])){
            $map["c.get_gift_order"]  = array("EQ",$where['get_gift_order']);
        }
		$map['c.fake'] = 0;
		$map['a.on'] = 2;

		if ($where['a.id']) {
            $map['a.id'] = $where['a.id'];
        }

		return M('user')->where($map)->alias('a')
			->field("a.id")
			->join("join qz_user_company c on a.id = c.userid")
			->join("join qz_quyu q on q.cid = a.cs")
			->count();
	}

    public function getCompanyList($where, $pageIndex, $pageCount)
    {
		if(!empty($where['jc'])){
			$map["_complex"] = array(
				"a.jc" =>  array("LIKE","%".$where['jc']."%"),
				"a.id" =>  array("EQ",$where['jc']),
				"_logic" => "OR"
			);
		}
		if(!empty($where["city"])){
			$map["a.cs"] = $where["city"];
		}
        if(!empty($where["css"])){
            $map["q.cid"] = array("IN",$where["css"]);
        }
		if(!empty($where["area"])){
			$map["a.qx"] = $where["area"];
		}
		if(!empty($where["zuobiao"])){
			if($where["zuobiao"] == 1){
				$map["c.lng"]  = array("EQ",'');
				$map["c.lat"]  = array("EQ",'');
			}else{
				$map["c.lng"]  = array("NEQ",'');
				$map["c.lat"]  = array("NEQ",'');
			}
		}
        if($where['bao'] != ""){
            $map["c.contract"]  = array("EQ",$where['bao']);
        }
        if($where['zhuang'] != ""){
            $map["c.lx"]  = array("EQ",$where['zhuang']);
        }
		if(!empty($where['get_gift_order'])){
			$map["c.get_gift_order"]  = array("EQ",$where['get_gift_order']);
		}

		$map["c.fake"] = 0;
		$map["a.on"] = 2;
		return M('user')->where($map)->alias("a")
			->field("a.id,a.jc,a.cs,q.cname,a.dz,c.lng,c.lat,c.contract,c.lx,c.other_id,c.saler,x.qz_area,c.get_gift_order,c.lxs,c.mianji")
			->join("join qz_user_company c on a.id = c.userid")
			->join("join qz_quyu q on q.cid = a.cs")
			->join("left join qz_area x on x.qz_areaid = a.qx")
			->order("a.register_time desc")
            ->limit($pageIndex, $pageCount)
			->select();
	}

    /**
     * 获取用户信息
     *
     * @param $id
     * @return mixed
     */
	public function getvipcompany($id)
    {
		$map['a.id'] =  array("EQ",$id);
		return M('user')->where($map)->alias("a")
			->field("a.id,a.jc,q.cname,a.cs,a.dz,c.lng,c.lat,c.contract,c.lx,c.other_id,c.saler,x.qz_area,a.qx,c.get_gift_order,c.mianji,c.lxs,CONCAT_WS(',',q.parent_city,q.parent_city1,q.parent_city2,q.parent_city3,q.parent_city4) as adjacent_cid")
			->join("join qz_user_company c on a.id = c.userid")
			->join("join qz_quyu q on q.cid = a.cs")
			->join("left join qz_area x on x.qz_areaid = a.qx")
			->find();
	}

	public function saveUser($user,$id)
    {
		$map["id"] = array("EQ",$id);
		return M('user')->where($map)->save($user);
	}

    public function addUser($user)
    {
        return M('user')->add($user);
    }

	public function saveCompany($user,$id)
    {
        $user['deliver_area'] = '';
		$map["userid"] = array("EQ",$id);
		return M('user_company')->where($map)->save($user);
	}

	/**
	 * 通过公司名模糊搜索公司id
	 */
	public function searchidbyname($name,$limit)
    {
		$map['u.qc'] = array('like','%'.$name.'%');
		$map['u.on'] = array('EQ',2);
		$map['u.classid'] = array('IN',array(3,6));
		$map['c.fake'] = array('EQ',0);
		return M('user')->where($map)->alias('u')
			->field('u.qc,u.id')
			->join('qz_user_company c on c.userid = u.id')
			->limit($limit)
			->select();
	}

	/**
	 * 判断公司是否是真会员
	 */
	public function searchid($id){
		$map['u.id'] = array('EQ',$id);
		$map['u.on'] = array('EQ',2);
		$map['u.classid'] = array('IN',array(3,6));
		$map['c.fake'] = array('EQ',0);
		return M('user')->where($map)->alias('u')
			->field('u.id')
			->join('qz_user_company c on c.userid = u.id')
			->find();
	}

	public function getCompany(){
		$map['b.fake'] = array("EQ",0);
		$map['a.on'] = array("EQ",2);
		return  M("user")
            ->alias("a")
            ->where($map)
			->join("join qz_user_company b on a.id = b.userid")
			->field("a.id")->select();
	}

    /**
     * 获取非会员装修公司数量
     *
     * @param $map
     * @return mixed
     */
    public function getAllCompanyListCount($map)
    {
        return M('user')
            ->where($map)
            ->alias('a')
            ->field('a.id')
            ->join('join qz_user_company c on a.id = c.userid')
            ->join('join qz_quyu q on q.cid = a.cs')
            ->count();
    }

    /**
     * 获取非会员装修公司列表
     *
     * @param $map
     * @return mixed
     */
    public function getAllCompanyList($map, $pageIndex, $pageCount)
    {
        return M('user')
            ->alias('a')
            ->where($map)
            ->field('c.modify_state as state,a.logo,a.id,a.jc,a.qc,q.cname,a.dz,c.lng,c.lat,a.on,c.fake,c.lx,c.other_id,c.saler,x.qz_area,a.register_time,d.name as rname,c.op_info_name,c.op_info_last_time')
            ->join('join qz_user_company c on a.id = c.userid')
            ->join('join qz_quyu q on q.cid = a.cs')
            ->join('left join qz_area x on x.qz_areaid = a.qx')
            ->join('left join qz_adminuser d on d.id = a.register_admin_id')
            ->order('a.register_time desc')
            ->limit($pageIndex . ',' . $pageCount)
            ->select();
    }

    /**
     * 获取装修公司基本信息
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getCompanyInfoByAdmin($comid)
    {
        $map = array(
            "a.id" => array("EQ", $comid)
        );
        //1.获取用户的基本信息
        $buildSql = M("user")->where($map)->alias("a")
            ->buildSql();
        //2.查询用户的未读订单信息
        $buildSql = M("user")->table($buildSql)->alias("t")
            ->join("LEFT JOIN qz_order_info as b on b.com = t.id and b.isread = 0 and b.type_fw not in(11,22)")
            ->join("INNER JOIN qz_quyu as q on q.cid = t.cs")
            ->group("b.com")
            ->field("t.*,count(b.id) as unreadcount,q.bm")
            ->buildSql();
        //3.查询用户未读系统信息信息
        return M("user")
            ->table($buildSql)
            ->alias("t1")
            ->join("LEFT JOIN qz_user_company as c on t1.id = c.userid")
            ->join("LEFT JOIN qz_team as f on f.comid = t1.id and f.zt = 2")
            ->field("t1.*,c.nickname,c.nickname1,c.team_num,c.quyu,c.fuwu,c.fengge,c.baozhang,c.jiawei,c.chengli,c.guimo,c.luxian,c.text as about_jianjie,c.hengfu,c.jd_tel_1,c.jd_tel_2,count(f.id) as dcount")
            ->find();
    }

    /**
     * 判断公司是否是假会员
     */
    public function isRealCompany($id){
        $map['u.id'] = array('EQ',$id);
        $map['u.classid'] = array('IN',array(3,6));
        return M('user')->where($map)->alias('u')
            ->field('u.on,c.fake')
            ->join('qz_user_company c on c.userid = u.id')
            ->find();
    }

    /**
     * 判断公司是否是注册会员
     */
    public function isRegCompany($id){
        $map['u.id'] = array('EQ',$id);
        $map['u.on'] = array('EQ',0);
        $map['u.classid'] = array('IN',array(3,6));
        return M('user')->where($map)->alias('u')
            ->field('u.id')
            ->join('qz_user_company c on c.userid = u.id')
            ->find();
    }


    public function deleteCompany($map){
        return M('user')->where($map)->delete();
    }

    /**
     * 获取装修公司基本图片
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getCompanyImgs($comid){
        //取个人图片
        return M('company_img')->field("id,img as img_path,img_host,0 as img_on,remark")->where(array('userid'=>$comid))->select();
    }

    /**
     * 获取装修公司基本活动
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getCompanyActivity($comid){
        //基本活动
        return M('company_activity')->field("id,title,text,start,end,types,state")->where(array('cid'=>$comid))->select();
    }

    // 获取类型列表
    public function getlx(){
        $map['type'] = array('eq','leixing');
        $list = M("leixing")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }

    // 获取风格列表
    public function getfg(){
        $map['type'] = array('eq','fengge');
        $list = M("fengge")->where($map)->order('px')->field('id,name')->select();
        return $list;
    }

    /**
     * 更新装修公司基本活动
     * @return [type]        [description]
     */
    public function saveCompanyActivity($data){
        //更新基本活动
        return M('company_activity')->save($data);
    }

    //修改公司状态
    public function changeCompanyState($map,$data)
    {
        return M('user_company')->where($map)->save($data);
    }

    /**
     * 只获取用户user_company
     *
     * @return mixed
     */
    public function getOnlyCompany($map, $field = '*')
    {
        if ((is_array($field) && count($field) > 1) || strpos($field, ',')) {
            return M('user_company')->field($field)->where($map)->getField($field, true);
        } elseif ($field !== '*') {
            return M('user_company')->field($field)->where($map)->getField($field);
        } else {
            return M('user_company')->field($field)->where($map)->find();
        }
    }

    /**
     * 保存用户派单区域
     *
     * @param $areaArray
     * @return bool|string
     */
    public function saveCompanyArea($areaArray)
    {
        return M('user_company_deliver_area')->addAll($areaArray);
    }

    /**
     * 删除用户派单区域
     *
     * @param $map
     * @return mixed
     */
    public function delCompanyArea($map)
    {
        return M('user_company_deliver_area')->where($map)->delete();
    }
    /**
     * 获取装修公司派单区域总数量
     *
     * @param $map
     * @return mixed
     */
    public function getCompanyAreaCount($map)
    {
        return M('user_company_deliver_area')->where($map)->count(1);
    }

    /**
     * 获取装修公司派单区域总数量
     *
     * @param $map
     * @return mixed
     */
    public function getCompanyAreaList($map)
    {
        return M('user_company_deliver_area')->alias('a')
            ->join('qz_area b on b.qz_areaid  = a.area_id')
            ->field('b.qz_areaid area_id,b.qz_area area')
            ->where($map)
            ->order('b.orders asc')
            ->select();
    }

    // 获取所有的服务类型
    public function getFuwuLeixing($type)
    {
        $map = [];
        $map['type'] = $type;
        return M('leixing')->select();
    }

    //根据城市id获取该城市下所有的装修公司数量
    public function getCompanyListByCsCount($cs,$subthematicid,$getdata)
    {
        $map = [];
        $map['user.cs'] = $cs;
        $map['user.classid'] = array('IN',array(3,6));       //3表示装修公司

        if(isset($getdata['company_id']) && $getdata['company_id']){        //公司id
            $map['user.id'] = $getdata['company_id'];
        }

        if(isset($getdata['company_name']) && $getdata['company_name']){        //公司名称
            $map['user.jc'] = array('like','%'.$getdata['company_name'].'%');
        }

        if(isset($getdata['status']) && $getdata['status'] == 1){           //选取状态为是
            $map['choose.subthematic_id'] = $subthematicid;
            return M('user')->alias('user')
                ->where($map)
                ->join('qz_user_company companyinfo on companyinfo.userid  = user.id')
                ->join('qz_subthematic_company choose on choose.company_id  = user.id')
                ->count();
        }elseif(isset($getdata['status']) && $getdata['status'] == 2){      //选取状态为否

            return M('user')->alias('user')
                ->where($map)
                ->join('qz_user_company companyinfo on companyinfo.userid  = user.id')
                ->join('LEFT JOIN qz_subthematic_company choose on choose.company_id  = user.id and choose.subthematic_id ='.$subthematicid)
                ->where('choose.id is null')
                ->count();
        }else{                                                              //全部
            if($map) {
                return M('user')->alias('user')
                    ->where($map)
                    ->join('qz_user_company companyinfo on companyinfo.userid  = user.id')
                    ->count();
            }else{
                return 0;
            }
        }

    }


    //根据城市id获取该城市下所有的装修公司（获取的参数为）
    public function getCompanyListByCs($cs,$subthematicid,$getdata,$pageIndex,$pageCount)
    {
        $map = [];
        $map['user.cs'] = $cs;
        $map['user.classid'] = array('IN',array(3,6));       //3表示装修公司

        if(isset($getdata['company_id']) && $getdata['company_id']){        //公司id
            $map['user.id'] = $getdata['company_id'];
        }

        if(isset($getdata['company_name']) && $getdata['company_name']){        //公司名称
            $map['user.jc'] = array('like','%'.$getdata['company_name'].'%');
        }

        if(isset($getdata['status']) && $getdata['status'] == 1){           //选取状态为是
            $map['choose.subthematic_id'] = $subthematicid;
            return M('user')->alias('user')
                ->where($map)
                ->field('user.id company_id,user.on,user.jc company_jc,quyu.cname company_cityname,area.qz_area company_areaname,companyinfo.fuwu,companyinfo.fake')
                ->join('qz_user_company companyinfo on companyinfo.userid  = user.id')
                ->join('qz_subthematic_company choose on choose.company_id  = user.id')
                ->join('LEFT JOIN qz_quyu quyu on quyu.cid = user.cs')
                ->join('LEFT JOIN qz_area area on area.qz_areaid = user.qx')
                ->group('user.id')
                ->limit($pageIndex,$pageCount)
                ->order('user.register_time desc')
                ->select();
        }elseif(isset($getdata['status']) && $getdata['status'] == 2){      //选取状态为否

            return M('user')->alias('user')
                ->where($map)
                ->field('user.id company_id,user.on,user.jc company_jc,quyu.cname company_cityname,area.qz_area company_areaname,companyinfo.fuwu,companyinfo.fake')
                ->join('qz_user_company companyinfo on companyinfo.userid  = user.id')
                ->join('LEFT JOIN qz_quyu quyu on quyu.cid = user.cs')
                ->join('LEFT JOIN qz_area area on area.qz_areaid = user.qx')
                ->join('LEFT JOIN qz_subthematic_company choose on choose.company_id  = user.id and choose.subthematic_id ='.$subthematicid)
                ->where('choose.id is null')
                ->group('user.id')
                ->limit($pageIndex,$pageCount)
                ->order('user.register_time desc')
                ->select();
        }else{                                                              //全部
            if($map) {
                return M('user')->alias('user')
                    ->where($map)
                    ->field('user.id company_id,user.on,user.jc company_jc,quyu.cname company_cityname,area.qz_area company_areaname,companyinfo.fuwu,companyinfo.fake')
                    ->join('qz_user_company companyinfo on companyinfo.userid  = user.id')
                    ->join('LEFT JOIN qz_subthematic_company choose on choose.company_id = user.id and choose.subthematic_id = '.$subthematicid)
                    ->join('LEFT JOIN qz_quyu quyu on quyu.cid = user.cs')
                    ->join('LEFT JOIN qz_area area on area.qz_areaid = user.qx')
                    ->group('user.id')
                    ->limit($pageIndex,$pageCount)
                    ->order('user.register_time desc')
                    ->select();
            }else{
                return array();
            }
        }

    }


    public function selectCompanybyids($company_ids)
    {
        if (empty($company_ids)) {
            return false;
        }
        $map = array(
            'u.id' => ['IN',$company_ids],
            'u.classid' => array('IN',array(3,6))
        );
        //注意此处连表qz_user_company的ID问题
        return M('user')->alias('u')
            ->field('u.id,u.name')
            ->where($map)
            ->select();
    }

    public function saveUserCompany($data,$companyid)
    {
        $map = [];
        $map['userid'] = $companyid;
        return M('user_company')->where($map)->save($data);
    }

    public function bannerMap($input)
    {
        $map = [];
        if ($input['company_id']) {
            $map['u.id'] = ['eq', $input['company_id']];
        }
        if($input['company']){
            $map['u.jc'] = ['LIKE', '%'.$input['company'].'%'];
        }

        if($input['city_id']){
            $map['u.cs'] = $input['city_id'];
        }

        if(strlen($input['memberType']) > 0){
            $map['u.on'] = (int)$input['memberType'];
        }

        if(strlen($input['status']) > 0){
            $map['b.status'] = (int)$input['status'];
        }

        return $map;
    }

    public function getCompanyBannersCount($input)
    {
        $map = $this->bannerMap($input);
        $count = M('company_banners')->alias('b')->join('JOIN qz_user u ON b.userid=u.id')->where($map)->count();

        return $count ?: 0;
    }

    public function getCompanyBanners($input, $start=0, $limit=20)
    {
        $map = $this->bannerMap($input);
        $result = M('company_banners')->alias('b')
            ->field('b.id,b.img_path,b.submit_date,b.status,u.jc as company_name,u.`on`,q.cname city_name')
            ->join('JOIN qz_user u ON b.userid=u.id')
            ->join('LEFT JOIN qz_quyu q ON q.cid=u.cs')
            ->where($map)
            ->order('b.submit_date DESC,b.id DESC')
            ->limit($start, $limit)
            ->select();

        return $result;
    }

    public function getCompanyBannerInfo($id){
        $map = [
            "id" => array("EQ", $id)
        ];

        return M('company_banners')->where($map)->find();
    }

    public function updateBannerById($id, $data)
    {
        $map = [
            'id' => $id
        ];

        return M('company_banners')->where($map)->save($data);
    }

    public function updateBannerStatus($id, $status)
    {
        $map = [
            'id' => $id
        ];
        return M('company_banners')->where($map)->save(['status' => (int)$status]);
    }
}