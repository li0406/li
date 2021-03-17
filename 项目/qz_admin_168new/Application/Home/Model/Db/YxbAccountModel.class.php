<?php
namespace Home\Model\Db;
Use Think\Model;

class YxbAccountModel extends Model
{
    /**
     * 公司是真会员
     * @param $userid
     * @return mixed
     */
    public function getUserInfo($userid){
        $where['a.id'] = array('EQ',$userid);
        $where['a.classid'] = array('IN',array(3,6));
        $where['b.fake'] = array('EQ',0);
        return M('user')->alias('a')
            ->field('a.id,a.jc,a.qc,a.cs,a.dz')
            ->join('qz_user_company b on b.userid=a.id')
            ->where($where)
            ->select();
    }

    /**
     *获取公司信息
     * @param $userid
     * @return mixed
     */
    public function getErpInfo($userid){
        $where['t.id'] = array('EQ',$userid);
        $where['t.classid'] = array('IN',array(3,6));
        $where['a.fake'] = array('EQ',0);
        return M('user')->alias('t')
            ->field('t.id,t.jc,t.qc,t.cs,t.dz,b.contact_name,b.account,b.contact_name,b.contact_tel,b.contact_wx,c.type,c.start_time,c.end_time,c.id as time_id,q.cname,p.qz_province')
            ->join('qz_user_company as a on a.userid = t.id')
            ->join('qz_quyu q on q.cid = t.cs')
            ->join('qz_province p ON q.uid = p.qz_provinceid')
            ->join('LEFT JOIN qz_yxb_account as b on b.company_id = t.id and b.class_type = 1')
            ->join('LEFT JOIN qz_yxb_account_time c on c.company_id = t.id')
            ->where($where)
            ->order('c.id desc')
            ->select();
    }


    public function getExistCompany($where){
        return M('user')->where($where)->count();
    }

    public function getExistErp($where){
        $where['class_type'] = 1;
        return M('yxb_account')->where($where)->count();
    }

    /**
     * 添加公司信息
     * @param $data
     * @return mixed
     */
    public function addUserCompany($data){
        return M("user_company")->add($data);
    }

    /**
     * 添加公司账号信息
     * @param $data
     * @return mixed
     */
    public function addUser($data){
        return M("user")->add($data);
    }

    /**
     * ERP账号表添加数据
     * @param $data
     * @return mixed
     */
    public function addUserErp($data){
        $data['create_time'] = time();
        return M("yxb_account")->add($data);
    }

    /**
     * ERP账号添加默认 工种 数据
     * @param $data
     * @return mixed
     */
    public function addUserErpWorktype($data){
        return M("yxb_worktype")->addAll($data);
    }

    /**
     * ERP账号添加默认 岗位 数据
     * @param $data
     * @return mixed
     */
    public function addUserErpStation($data){
        return M("yxb_station")->addAll($data);
    }

    /**
     * ERP账号添加默认 岗位 数据
     * @param $data
     * @return mixed
     */
    public function addUserErpStationRule($data){
        return M("yxb_station_rule")->addAll($data);
    }

    /**
     * ERP账号添加默认 订单来源 数据
     * @param $data
     * @return mixed
     */
    public function addUserErpOrdersource($data){
        return M("yxb_order_source")->addAll($data);
    }

    /**
     * ERP账号添加订单修改默认 订单来源 数据
     * @param $data
     * @return mixed
     */
    public function updateUserErpOrdersource($company_id,$source){
        $data['source'] = $source;
        $where['company_id'] = $company_id;
        return M("yxb_orders")->where($where)->save($data);;
    }

    /**
     * ERP操作日志表添加数据
     * @param $data
     * @return mixed
     */
    public function addUserErpLog($data){
        import('Library.Org.Util.App');
        $app = new \App();
        $data['ip'] = $app->get_client_ip();
        return M("yxb_account_log")->add($data);
    }

    /**
     * ERP续费表添加数据
     * @param $data
     * @return mixed
     */
    public function addUserErpTime($data){
        $data['uid'] =  session("uc_userinfo.id");
        $data['add_time'] = time();
        return M("yxb_account_time")->add($data);
    }

    /**
     * ERP操作日志表添加数据
     * @param $data
     * @return mixed
     */
    public function editUserErpTime($where,$data){
        return M("yxb_account_time")->where($where)->save($data);
    }

    public function getErpCompanyCount($where){
        $map['t.classid'] = array('IN',array(3,6));
        $map['a.fake'] = array('EQ',0); //真会员

        if($where['city']){
            $where['city'] = trim($where['city']);
            $mwhere['t2.cs'] = array('EQ',$where['city']);
            $having[] = 't2.cs='.$where['city'];
        }

        if($where['company']){
            $where['company'] = trim($where['company']);
            $having[] = "(t2.jc like '%".$where['company']."%' OR t2.id='".$where['company']."')";
        }
        if($where['status']){
            if($where['status'] == 5){
                $having[] = 'status in (5,6)';
            }else{
                $having[] = 'status='.$where['status'];
            }

        }

        if($where['account']){
            $where['account'] = trim($where['account']);
            $having[] = "t2.account='".$where['account']."'";
        }

        if($where['start_time_range']){
            $start_time_range = explode('~',$where['start_time_range']);
            $start_time_one = strtotime($start_time_range[0]);
            $start_time_two = strtotime($start_time_range[1]);
            $having[] = 't2.start_time between '.$start_time_one.' and '.$start_time_two;
        }

        if($where['end_time_range']){
            $end_time_range = explode('~',$where['end_time_range']);
            $end_time_one = strtotime($end_time_range[0])+86399;
            $end_time_two = strtotime($end_time_range[1])+86399;

            $having[] = 't2.end_time between '.$end_time_one.' and '.$end_time_two;
        }

        $havings = implode(' AND ',$having);


        $build =  M('user')->alias('t')
            ->field('
            t.id,t.jc,t.cs,b.account,q.cname,b.company_id,
            case 
            when c.start_time <= UNIX_TIMESTAMP() and c.end_time >= UNIX_TIMESTAMP() and c.type = 2 then 1
			when start_time > UNIX_TIMESTAMP() and c.type = 2 then 2
			else 3	end as `mark`,
			c.start_time as start_w_time,c.end_time as end_w_time
            ')
            ->join('qz_user_company as a on a.userid = t.id')
            ->join('qz_quyu q on q.cid = t.cs')
            ->join('LEFT JOIN qz_yxb_account as b on b.company_id = t.id and b.class_type=1')
            ->join('LEFT JOIN qz_yxb_account_time c on c.company_id = t.id')
            ->where($map)
            ->buildSql();



        $buildSql = M()->table($build)->alias('t11')
            ->field('t11.*,
            max(if(t11.mark = 1,t11.start_w_time,null)) as yx_start_time,
			max(if(t11.mark = 1,t11.end_w_time,null)) as yx_end_time,
			min(if(t11.mark = 2,t11.start_w_time,null)) as wsx_start_time,
			min(if(t11.mark = 2,t11.end_w_time,null)) as wsx_end_time,
			max(if(t11.mark = 3,t11.start_w_time,null)) as wx_start_time,
			max(if(t11.mark = 3,t11.end_w_time,null)) as wx_end_time
            ')
            ->group('t11.id')
            ->buildSql();


        $buildSql11 = M()->table($buildSql)->alias('t12')
            ->field('t12.*,
            IFNULL(t12.yx_start_time,IFNULL(t12.wsx_start_time,t12.wx_start_time)) as start_time,
            IFNULL(t12.yx_end_time,IFNULL(t12.wsx_end_time,t12.wx_end_time)) as end_time
            ')
            ->buildSql('t12.id');


        $buildSql2 = M()->table($buildSql11)->alias('t1')
            ->field("
                 t1.*,
                 c.uid ,
                 c.type,
                 c.id as time_id,
                 if(c.start_time=0,'',FROM_UNIXTIME(c.start_time,'%Y-%m-%d')) as start_tt,
                 if(c.end_time=0,'',FROM_UNIXTIME(c.end_time,'%Y-%m-%d')) as end_tt,
                 if(c.start_time is NULL,0,c.start_time) as start_tm,
                 if(c.end_time is NULL,0,c.end_time) as end_tm,
                 case
                 when unix_timestamp()>=c.start_time and unix_timestamp()<=c.end_time then 1
                 when unix_timestamp() < c.start_time then 2
                 when unix_timestamp() > c.end_time then 3 else 4 end time_type
            ")
            ->join('left join qz_yxb_account_time as c on c.company_id= t1.company_id and c.start_time = t1.start_time')
            ->group('t1.id')
            ->buildSql();

        $buildSql3 = M()->table($buildSql2)->alias('t2')
            ->field("*,
	            case when type=2 and time_type=1 then 12
		        when type=2 and time_type=2 then 11
			    when type=2 and time_type=3 then 13
			    when type is NULL then 6
			    else t2.type end status,
                if(t2.start_time is NULL,0,floor((t2.end_time+1- t2.start_time)/86400 )) as sum_day,
                if(type=2 and time_type=1,floor((t2.end_time-unix_timestamp())/86400+1),0) as remain_day
            ")
            ->group('t2.id')
            ->having($havings)
            ->buildSql();
        $result = M()->table($buildSql3)->alias('t3')->count();
        return $result;
    }

    public function getErpCompanyList($where,$order,$page, $pageCount)
    {
        $map['t.classid'] = array('IN',array(3,6));
        $map['a.fake'] = array('EQ',0); //真会员

        if($where['city']){
            $where['city'] = trim($where['city']);
            $mwhere['t2.cs'] = array('EQ',$where['city']);
            $having[] = 't2.cs='.$where['city'];
        }

        if($where['company']){
            $where['company'] = trim($where['company']);
            $having[] = "(t2.jc like '%".$where['company']."%' OR t2.id='".$where['company']."')";
        }
        if($where['status']){
            if($where['status'] == 5){
                $having[] = 'status in (5,6)';
            }else{
                $having[] = 'status='.$where['status'];
            }

        }

        if($where['account']){
            $where['account'] = trim($where['account']);
            $having[] = "t2.account='".$where['account']."'";
        }

        if($where['start_time_range']){
            $start_time_range = explode('~',$where['start_time_range']);
            $start_time_one = strtotime($start_time_range[0]);
            $start_time_two = strtotime($start_time_range[1]);
            $having[] = 't2.start_time between '.$start_time_one.' and '.$start_time_two;
        }

        if($where['end_time_range']){
            $end_time_range = explode('~',$where['end_time_range']);
            $end_time_one = strtotime($end_time_range[0])+86399;
            $end_time_two = strtotime($end_time_range[1])+86399;

            $having[] = 't2.end_time between '.$end_time_one.' and '.$end_time_two;
        }

       $havings = implode(' AND ',$having);


       if(empty($order)){
           // 默认按有效日期-结束倒序、公司ID倒序的顺序排序。 remain_day,
           $order = 't2.end_tt desc,t2.id desc';
       }

        $build =  M('user')->alias('t')
            ->field('
            t.id,t.jc,t.cs,b.account,q.cname,b.company_id,
            case 
            when c.start_time <= UNIX_TIMESTAMP() and c.end_time >= UNIX_TIMESTAMP() and c.type = 2 then 1
			when start_time > UNIX_TIMESTAMP() and c.type = 2 then 2
			else 3	end as `mark`,
			c.start_time as start_w_time,c.end_time as end_w_time
            ')
            ->join('qz_user_company as a on a.userid = t.id')
            ->join('qz_quyu q on q.cid = t.cs')
            ->join('LEFT JOIN qz_yxb_account as b on b.company_id = t.id and b.class_type=1')
            ->join('LEFT JOIN qz_yxb_account_time c on c.company_id = t.id')
            ->where($map)
            ->buildSql();



        $buildSql = M()->table($build)->alias('t11')
            ->field('t11.*,
            max(if(t11.mark = 1,t11.start_w_time,null)) as yx_start_time,
			max(if(t11.mark = 1,t11.end_w_time,null)) as yx_end_time,
			min(if(t11.mark = 2,t11.start_w_time,null)) as wsx_start_time,
			min(if(t11.mark = 2,t11.end_w_time,null)) as wsx_end_time,
			max(if(t11.mark = 3,t11.start_w_time,null)) as wx_start_time,
			max(if(t11.mark = 3,t11.end_w_time,null)) as wx_end_time
            ')
            ->group('t11.id')
            ->buildSql();


        $buildSql11 = M()->table($buildSql)->alias('t12')
            ->field('t12.*,
            IFNULL(t12.yx_start_time,IFNULL(t12.wsx_start_time,t12.wx_start_time)) as start_time,
            IFNULL(t12.yx_end_time,IFNULL(t12.wsx_end_time,t12.wx_end_time)) as end_time
            ')
            ->buildSql('t12.id');


        $buildSql2 = M()->table($buildSql11)->alias('t1')
            ->field("
                 t1.*,
                 c.uid ,
                 c.type,
                 c.id as time_id,
                 if(c.start_time=0,'',FROM_UNIXTIME(c.start_time,'%Y-%m-%d')) as start_tt,
                 if(c.end_time=0,'',FROM_UNIXTIME(c.end_time,'%Y-%m-%d')) as end_tt,
                 if(c.start_time is NULL,0,c.start_time) as start_tm,
                 if(c.end_time is NULL,0,c.end_time) as end_tm,
                 case
                 when unix_timestamp()>=c.start_time and unix_timestamp()<=c.end_time then 1
                 when unix_timestamp() < c.start_time then 2
                 when unix_timestamp() > c.end_time then 3 else 4 end time_type
            ")
            ->join('left join qz_yxb_account_time as c on c.company_id= t1.company_id and c.start_time = t1.start_time and c.end_time = t1.end_time')
            ->group('t1.id')
            ->buildSql();


        $result = M()->table($buildSql2)->alias('t2')
            ->field("*,
	            case when type=2 and time_type=1 then 12
		        when type=2 and time_type=2 then 11
			    when type=2 and time_type=3 then 13
			    when type is NULL then 6
			    else t2.type end status,
                if(t2.start_time is NULL,0,floor((t2.end_time+1- t2.start_time)/86400 )) as sum_day,
                if(type=2 and time_type=1,floor((t2.end_time-unix_timestamp())/86400+1),0) as remain_day,
                count(o.order_no) as order_number
            ")
            ->join('LEFT JOIN qz_yxb_orders as o on o.company_id = t2.id ')
            ->group('t2.id')
            ->having($havings)
            ->order($order)
            ->limit($page,$pageCount)
            ->select();

        return $result;
    }

    public function getErpCompanyTimeCount($where){
        $map['t.classid'] = array('IN',array(3,6));
        $map['a.fake'] = array('EQ',0); //真会员

        if($where['city']){
            $where['city'] = trim($where['city']);
            $mwhere['t2.cs'] = array('EQ',$where['city']);
            $having[] = 't2.cs='.$where['city'];
        }

        if($where['company']){
            $where['company'] = trim($where['company']);
            $having[] = "(t2.jc like '%".$where['company']."%' OR t2.id='".$where['company']."')";
        }
        if($where['status']){
            $having[] = 't2.status='.$where['status'];

        }

        if($where['account']){
            $where['account'] = trim($where['account']);
            $having[] = "t2.account='".$where['account']."'";
        }

        if($where['start_time_range']){
            $start_time_range = explode('~',$where['start_time_range']);
            $start_time_one = strtotime($start_time_range[0]);
            $start_time_two = strtotime($start_time_range[1]);
            $having[] = 't2.start_time between '.$start_time_one.' and '.$start_time_two;
        }

        if($where['end_time_range']){
            $end_time_range = explode('~',$where['end_time_range']);
            $end_time_one = strtotime($end_time_range[0])+86399;
            $end_time_two = strtotime($end_time_range[1])+86399;

            $having[] = 't2.end_time between '.$end_time_one.' and '.$end_time_two;
        }

        $havings = implode(' AND ',$having);


        if(empty($order)){
            // 默认按剩余天数正序、有效日期-结束倒序、公司ID倒序的顺序排序。
            $order = 'add_time desc';
        }

        $buildSql = M('yxb_account_time')->alias('c')
            ->field("
                 c.type,
                case when c.type=1 then 1
		        when c.type=5 then 4
			    when c.type=3 then 3
			    else 2 end  status,
                 c.start_time,c.end_time,c.uid,
	             if(c.start_time=0,'',FROM_UNIXTIME(c.start_time,'%Y-%m-%d')) as start_tt,
	             if(c.end_time=0,'',FROM_UNIXTIME(c.end_time,'%Y-%m-%d')) as end_tt,
	             if(c.start_time is NULL,0,c.start_time) as start_tm,
	             if(c.end_time is NULL,0,c.end_time) as end_tm,
	             c.id AS time_id,
	             b.account,
	             if(c.start_time is NULL,0,floor((c.end_time+1- c.start_time)/86400 )) as sum_day,
                t.id,t.jc,t.cs,q.cname,u.name as uname,
                if(c.add_time=0,'',FROM_UNIXTIME(c.add_time,'%Y-%m-%d')) as add_time
            ")
            ->join('qz_yxb_account as b on b.company_id = c.company_id and b.class_type=1')
            ->join('qz_user as t on b.company_id = t.id')
            ->join('qz_user_company as a on a.userid = t.id')
            ->join('qz_quyu q on q.cid = t.cs')
            ->join('left join qz_user as u on u.id = c.uid')
            ->where($map)
            ->buildSql();
        $result = M()->table($buildSql)->alias('t2')
            ->where($havings)
            ->count();
        return $result;
    }

    /**
     * 审核页
     * @param $where
     * @param $order
     * @param $page
     * @param $pageCount
     * @return mixed
     */
    public function getErpCompanyTimeList($where,$order,$page, $pageCount)
    {
        $map['t.classid'] = array('IN',array(3,6));
        $map['a.fake'] = array('EQ',0); //真会员

        if($where['city']){
            $where['city'] = trim($where['city']);
            $mwhere['t2.cs'] = array('EQ',$where['city']);
            $having[] = 't2.cs='.$where['city'];
        }

        if($where['company']){
            $where['company'] = trim($where['company']);
            $having[] = "(t2.jc like '%".$where['company']."%' OR t2.id='".$where['company']."')";
        }

        if($where['status']){
            $having[] = "status=".$where['status'];
        }

        if($where['account']){
            $where['account'] = trim($where['account']);
            $having[] = "t2.account='".$where['account']."'";
        }

        if($where['start_time_range']){
            $start_time_range = explode('~',$where['start_time_range']);
            $start_time_one = strtotime($start_time_range[0]);
            $start_time_two = strtotime($start_time_range[1]);
            $having[] = 't2.start_time between '.$start_time_one.' and '.$start_time_two;
        }

        if($where['end_time_range']){
            $end_time_range = explode('~',$where['end_time_range']);
            $end_time_one = strtotime($end_time_range[0])+86399;
            $end_time_two = strtotime($end_time_range[1])+86399;

            $having[] = 't2.end_time between '.$end_time_one.' and '.$end_time_two;
        }

        $havings = implode(' AND ',$having);


        if(empty($order)){
            // 默认按剩余天数正序、有效日期-结束倒序、公司ID倒序的顺序排序。
            $order = 'create_time desc';
        }

        $buildSql = M('yxb_account_time')->alias('c')
            ->field("
                 c.type,
                case when c.type=1 then 1
		        when c.type=5 then 4
			    when c.type=3 then 3
			    else 2 end  status,
                 c.start_time,c.end_time,c.uid,
	             if(c.start_time=0,'',FROM_UNIXTIME(c.start_time,'%Y-%m-%d')) as start_tt,
	             if(c.end_time=0,'',FROM_UNIXTIME(c.end_time,'%Y-%m-%d')) as end_tt,
	             if(c.start_time is NULL,0,c.start_time) as start_tm,
	             if(c.end_time is NULL,0,c.end_time) as end_tm,
	             c.id AS time_id,
	             b.account,
	             if(c.start_time is NULL,0,floor((c.end_time+1- c.start_time)/86400 )) as sum_day,
                t.id,t.jc,t.cs,q.cname,u.name as uname,
                c.add_time as create_time,
                if(c.add_time=0,'',FROM_UNIXTIME(c.add_time,'%Y-%m-%d')) as add_time
            ")
            ->join('qz_yxb_account as b on b.company_id = c.company_id and b.class_type=1')
            ->join('qz_user as t on b.company_id = t.id')
            ->join('qz_user_company as a on a.userid = t.id')
            ->join('qz_quyu q on q.cid = t.cs')
            ->join('left join qz_adminuser as u on u.id = c.uid')
            ->where($map)
            ->buildSql();
        $result = M()->table($buildSql)->alias('t2')
            ->field('t2.*')
            ->where($havings)
            ->order($order)
            ->limit($page,$pageCount)
            ->select();

        return $result;
    }


    /**
     * 编辑续费表
     * @param $save
     * @param $where
     */
    public function editErpType($save,$where){
        return M("yxb_account_time")->where($where)->save($save);
    }

    /**
     * 编辑账号表
     * @param $save
     * @param $where
     */
    public function editErp($save,$where){
        $where['class_type'] = 1;
        return M("yxb_account")->where($where)->save($save);
    }

    public function getNewErpTime($where){
        return M("yxb_account_time")->where($where)->order('id desc')->limit(1)->select();
    }

    public function getNewErpTimeList($where){
        //状态不为驳回的erp信息
        $where['type'] = array('NEQ',3);
        return M("yxb_account_time")->where($where)->select();
    }

    /**
     * 获取日志表信息
     * @param $where
     */
    public function getErpLogInfo($where){
        return M('yxb_account_log')->where($where)->order('id desc')->select();
    }

    /**
     * 获取公司对应日志
     */
    public function getErpLogAndCompanyInfo($where){
        $map['a.end_type'] = $where['end_type'];
        $map['a.company_id'] = $where['company_id'];
        return M('yxb_account_log')->alias('a')
            ->field('a.*,b.start_time,b.end_time')
            ->join('qz_yxb_account_time b on b.id = a.time_id')
            ->where($map)
            ->order('a.id desc')
            ->select();
    }

    /**
     * 订单列表页
     * @param $where
     * @param $order
     * @param $firstRow
     * @param $listRows
     * @return mixed
     */
    public function getOrdersList($where,$order,$firstRow, $listRows){
        //城市
        if(!empty($where["city"])&&isset($where["city"])){
            $whereone['o.city'] = ['EQ',$where['city']];
        }
        //齐装网订单
        if(!empty($where["qz_order_id"])&&isset($where["qz_order_id"])){
            $whereone['o.qz_order'] = ['EQ',trim($where["qz_order_id"])];
        }
        //ERP订单
        if(!empty($where["order_id"])&&isset($where["order_id"])){
            $whereone['o.order_no'] = ['EQ',trim($where["order_id"])];
        }
        //订单来源
        if(!empty($where["ordersource"])&&isset($where["ordersource"])){
            $whereone['o.source'] = ['EQ',$where["ordersource"]];
        }
        //订单状态
        if(!empty($where["orderstatus"])&&isset($where["orderstatus"])){
            $whereone['m.state'] = ['EQ',$where["orderstatus"]];
        }
        //施工状态
        if(!empty($where["shigongstatus"])&&isset($where["shigongstatus"])){
            $whereone['m.build_state'] = ['EQ',$where["shigongstatus"]];
        }
        //发单开始时间 发单结束时间
        if(!empty($where["fa-start-time"])&&!empty($where["fa-end-time"])){
            $whereone['o.add_time'] = array('between',[strtotime($where["fa-start-time"]),strtotime($where["fa-end-time"])]);
        }else if(!empty($where["fa-start-time"])){
            $whereone['o.add_time'] = array('EGT',strtotime($where["fa-start-time"]));
        }else if(!empty($where["fa-end-time"])){
            $whereone['o.add_time'] = array('ELT',strtotime($where["fa-end-time"]));
        }else{
            $timethree = date('Y-m-d H:i:s',strtotime('-3 month'));
            $whereone['o.add_time'] = array('between',[strtotime($timethree),time()]);
        }
        //接单开始时间 结束时间
        if(!empty($where["jie-start-time"])&&!empty($where["jie-end-time"])){
            $whereone['m.reception_time'] = ['between',[strtotime($where["jie-start-time"]),strtotime($where["jie-end-time"])]];
        }else if(!empty($where["jie-start-time"])){
            $whereone['m.reception_time'] = ['EGT',strtotime($where["jie-start-time"])];
        }else if(!empty($where["jie-end-time"])){
            $whereone['m.reception_time'] = ['ELT',strtotime($where["jie-end-time"])];
        }

        if(empty($order)){
            $order = "t3.order_no desc";
        }
        $buildSql = M('yxb_orders')->alias('o')
            ->field('o.*,m.build_state,state,u.user as company,m.reception_time')
            ->join("qz_user u on u.id = o.company_id")
            ->join("left join qz_yxb_orders_manage m on m.order_no = o.order_no")
            ->where($whereone)
            ->buildSql();

        $buildSql3 = M()->table($buildSql)->alias('t2')
            ->field('t2.*,f.add_time as follow_time')
            ->join("left join qz_yxb_follow_order f on f.order_no = t2.order_no and f.state=3")
            ->order("f.add_time desc")
            ->buildSql();

        $result = M()->table($buildSql3)->alias('t3')
            ->field('t3.* , q.cname , u.qc as company_name')
            ->join("join qz_quyu q ON q.cid = t3.city")
            ->join("LEFT JOIN qz_user u on u.id = t3.company_id")
            ->group('t3.order_no')
            ->order($order)
            ->limit($firstRow, $listRows)
            ->select();

        return $result;

    }

    /**
     * 订单列表数量
     * @param $where
     * @return mixed
     */
    public function getOrdersCount($where){

        //城市
        if(!empty($where["city"])&&isset($where["city"])){
            $whereone['o.city'] = ['EQ',$where['city']];
        }
        //齐装网订单
        if(!empty($where["qz_order_id"])&&isset($where["qz_order_id"])){
            $whereone['o.qz_order'] = ['EQ',trim($where["qz_order_id"])];
        }
        //ERP订单
        if(!empty($where["order_id"])&&isset($where["order_id"])){
            $whereone['o.order_no'] = ['EQ',trim($where["order_id"])];
        }
        //订单来源
        if(!empty($where["ordersource"])&&isset($where["ordersource"])){
            $whereone['o.source'] = ['EQ',$where["ordersource"]];
        }
        //订单状态
        if(!empty($where["orderstatus"])&&isset($where["orderstatus"])){
              $whereone['m.state'] = ['EQ',$where["orderstatus"]];
        }
        //施工状态
        if(!empty($where["shigongstatus"])&&isset($where["shigongstatus"])){
              $whereone['m.build_state'] = ['EQ',$where["shigongstatus"]];
        }
        //发单开始时间 发单结束时间
        if(!empty($where["fa-start-time"])&&!empty($where["fa-end-time"])){
            $whereone['o.add_time'] = array('between',[strtotime($where["fa-start-time"]),strtotime($where["fa-end-time"])]);
        }else if(!empty($where["fa-start-time"])){
            $whereone['o.add_time'] = array('EGT',strtotime($where["fa-start-time"]));
        }else if(!empty($where["fa-end-time"])){
            $whereone['o.add_time'] = array('ELT',strtotime($where["fa-end-time"]));
        }else{
            $timethree = date('Y-m-d H:i:s',strtotime('-3 month'));
            $whereone['o.add_time'] = array('between',[strtotime($timethree),time()]);
        }
        //接单开始时间 结束时间
        if(!empty($where["jie-start-time"])&&!empty($where["jie-end-time"])){
            $whereone['m.reception_time'] = ['between',[strtotime($where["jie-start-time"]),strtotime($where["jie-end-time"])]];
        }else if(!empty($where["jie-start-time"])){
            $whereone['m.reception_time'] = ['EGT',strtotime($where["jie-start-time"])];
        }else if(!empty($where["jie-end-time"])){
            $whereone['m.reception_time'] = ['ELT',strtotime($where["jie-end-time"])];
        }

        $buildSql = M('yxb_orders')->alias('o')
            ->force('list_search')
            ->field('o.company_id,o.order_no,m.reception_time,o.city')
            ->join("qz_user u on u.id = o.company_id")
            ->join("left join qz_yxb_orders_manage m on m.order_no = o.order_no")
            ->where($whereone)
            ->buildSql();

        $buildSql3 = M()->table($buildSql)->alias('t2')
            ->field('t2.*')
            ->join("join qz_quyu q ON q.cid = t2.city")
            ->group('t2.order_no')
            ->buildSql();

        $result = M()->table($buildSql3)->alias('t3')->count();

        return $result;
    }

    /**
     * 获取erp订单信息
     * @param $id
     * @return array
     */
    public function getOrderErpInfo($id,$company_id){
        //获取账号信息
        if(!empty($id)&&isset($id)){
            $map["o.order_no"] = array('EQ',$id);
            $map["o.company_id"] = array('EQ',$company_id);
        }
        $buildSql = M('yxb_orders')->alias("o")
            ->field('
            o.*,m.build_state,state,q.cname,p.qz_province as pname,u.qc as company_name,h.name as huxing_name,r.add_time as reception_time,
            u1.contact_name as receptionname,
            u2.contact_name as designername,
            u.dz as company_address,
            m.reception_time as jiedan_time,
            a.qz_area as aname
            ')
            ->join('left join qz_yxb_orders_manage m on m.order_no = o.order_no')
            ->join('left join qz_user u on u.id = o.company_id')
            ->join('left join qz_quyu as q on q.cid = o.city')
            ->join('left join qz_province p on q.uid = p.qz_provinceid')
            ->join('left join qz_area a on o.area = a.qz_areaid')
            ->join('left join qz_huxing as h on o.house_type = h.id')
            ->join("left join qz_yxb_reception r on r.order_no = o.order_no")
            ->join("left join qz_yxb_account u1 on u1.id = m.reception_id")
            ->join("left join qz_yxb_account u2 on u2.id = m.designer_id")
            ->order("r.add_time")
            ->where($map)
            ->buildSql();
        $result = M()->table($buildSql)->alias('t1')
            ->group('t1.order_no')
            ->select();

        return $result[0];
    }

    /**
     * 获取接单日志
     * @param $order_no
     */
    public function getJieDanLog($order_no){
        $map["order_no"] = array("EQ",$order_no);
       return M('yxb_reception')->where($map)->order('add_time desc')->select();
    }

    /**
     * 获取跟单日志
     * @param $order_no
     */
    public function getGengDanLog($order_no){
        $map["order_no"] = array("EQ",$order_no);
        return M('yxb_follow_order')->where($map)->order('add_time desc')->select();
    }

    /**
     * 获取签单日志
     * @param $order_no
     */
    public function getQianDanLog($order_no){
        $map["order_no"] = array("EQ",$order_no);
        return M('yxb_sign_order')->where($map)->order('add_time desc')->select();
    }

    /**
     * 获取施工信息
     * @param $order_no
     */
    public function getShigongLog($order_no){
        $map["a.order_no"] = array("EQ",$order_no);
        $result =  M('yxb_build')->alias('a')
            ->field('a.*,b.img,b.title')
            ->join("left join qz_yxb_build_design b on b.build_id = a.id")
            ->where($map)->order('add_time desc')->select();
        return $result;
    }

    /**
     * 获取施工log信息
     * @param $order_no
     */
    public function getShigongLogTwo($order_no){
        $map["order_no"] = array("EQ",$order_no);
        return M('yxb_build_log')->where($map)->order('add_time desc')->limit(1)->select();
    }


    /**
     * 获取收尾信息
     * @param $order_no
     */
    public function getShouweiLog($order_no){
        $map["order_no"] = array("EQ",$order_no);
        return M('yxb_end')->where($map)->order('add_time desc')->select();
    }

    /**
     * 获取完成信息
     * @param $order_no
     */
    public function getFinishLog($order_no){
        $map["order_no"] = array("EQ",$order_no);
        return M('yxb_finish')->where($map)->order('add_time desc')->select();
    }

    /**
     * 订单列表页
     * @param $where
     * @param $order
     * @param $firstRow
     * @param $listRows
     * @return mixed
     */
    public function getOrdersAccountList($where,$order,$firstRow, $listRows){
        if(!empty($where['company_id'])&&isset($where['company_id'])){
            $map['o.company_id'] = $where['company_id'];
        }else{
            return false;
        }

        if(!empty($where['state'])&&isset($where['state'])){
            if(is_array($where['state'])){
                $map['m.state'] = array('in',$where['state']);
            }else{
                $map['m.state'] = array('eq',$where['state']);
            }
        }

        //订单来源
        if(!empty($where['dd_type'])&&isset($where['dd_type'])){
            if(is_array($where['dd_type'])){
                $map['o.source'] = array('in',$where['dd_type']);
            }else{
                $map['o.source'] = array('eq',$where['dd_type']);
            }
        }

        //订单状态
        if(!empty($where['dd_status'])&&isset($where['dd_status'])){
            if(is_array($where['dd_status'])){
                $map['m.state'] = array('in',$where['dd_status']);
            }else{
                $map['m.state'] = array('eq',$where['dd_status']);
            }

        }
        //施工状态
        if(!empty($where['sg_status'])&&isset($where['sg_status'])){
            if(is_array($where['sg_status'])){
                $map['m.build_state'] = array('in',$where['sg_status']);
            }else{
                $map['m.build_state'] = array('eq',$where['sg_status']);
            }

        }


        if(empty($order)){
            $order = "t3.order_no desc";
        }
        $buildSql = M('yxb_orders')->alias('o')
            ->field('o.*,m.build_state,state,m.reception_time')
            ->join("left join qz_yxb_orders_manage m on m.order_no = o.order_no")
            ->where($map)
            ->buildSql();

        $buildSql3 = M()->table($buildSql)->alias('t2')
            ->field('t2.*,f.add_time as follow_time')
            ->join("left join qz_yxb_follow_order f on f.order_no = t2.order_no and f.state=3")
            ->order("f.add_time desc")
            ->buildSql();

        $result = M()->table($buildSql3)->alias('t3')
            ->field('t3.*')
            ->group('t3.order_no')
            ->order($order)
            ->limit($firstRow, $listRows)
            ->select();

        return $result;

    }

    /**
     * 订单列表数量
     * @param $where
     * @return mixed
     */
    public function getOrdersAccountCount($where){
        if(!empty($where['company_id'])&&isset($where['company_id'])){
            $map['o.company_id'] = array('EQ',$where['company_id']);
        }else{
            return false;
        }
        //订单状态 (用于tab切换查询)
        if(!empty($where['state'])&&isset($where['state'])){
            if(is_array($where['state'])){
                $map['m.state'] = array('in',$where['state']);
            }else{
                $map['m.state'] = array('eq',$where['state']);
            }
        }

        //订单来源
        if(!empty($where['dd_type'])&&isset($where['dd_type'])){
            if(is_array($where['dd_type'])){
                $map['o.source'] = array('in',$where['dd_type']);
            }else{
                $map['o.source'] = array('eq',$where['dd_type']);
            }
        }

        //订单状态
        if(!empty($where['dd_status'])&&isset($where['dd_status'])){
            if(is_array($where['dd_status'])){
                $map['m.state'] = array('in',$where['dd_status']);
            }else{
                $map['m.state'] = array('eq',$where['dd_status']);
            }

        }
        //施工状态
        if(!empty($where['sg_status'])&&isset($where['sg_status'])){
            if(is_array($where['sg_status'])){
                $map['m.build_state'] = array('in',$where['sg_status']);
            }else{
                $map['m.build_state'] = array('eq',$where['sg_status']);
            }

        }


        $buildSql = M('yxb_orders')->alias('o')
            ->field('o.*,m.build_state,state,u.user as company,m.reception_time')
            ->join("qz_user u on u.id = o.company_id")
            ->join("left join qz_yxb_orders_manage m on m.order_no = o.order_no")
            ->where($map)
            ->buildSql();

        $buildSql3 = M()->table($buildSql)->alias('t2')
            ->field('t2.*,f.add_time as follow_time')
            ->join("left join qz_yxb_follow_order f on f.order_no = t2.order_no and f.state=3")
            ->order("f.add_time desc")
            ->buildSql();

        $buildSql4 = M()->table($buildSql3)->alias('t3')
            ->field('t3.*')
            ->group('t3.order_no')
            ->buildSql();

        $result = M()->table($buildSql4)->alias('t4')->count();

        return $result;
    }

     /**
     * 获取erp装修公司员工总数据
     * @param $where
     * @return mixed
     */
    public function getAccountListCount($where)
    {
        return M('yxb_account')->alias('a')
            ->field('a.id,a.company_id,a.account,a.status,a.contact_name,a.contact_tel,a.contact_wx,a.image,s.`name` as station_name,d.dept_name')
            ->join('qz_yxb_account_info i on i.account_id = a.id')
            ->join('qz_yxb_station s on s.id = i.station_id')
            ->join('qz_yxb_department d ON d.id = i.dept_id')
            ->where($where)
            ->count();
    }

    /**
     * 获取erp装修公司员工数据
     * @param $where
     * @param $page
     * @param $pageCount
     * @return mixed
     */
    public function getAccountList($where, $page, $pageCount)
    {
        return M('yxb_account')->alias('a')
            ->field('a.id,a.company_id,a.account,a.status,a.contact_name,a.contact_tel,a.contact_wx,a.image,s.`name` as station_name,d.dept_name')
            ->join('qz_yxb_account_info i on i.account_id = a.id')
            ->join('qz_yxb_station s on s.id = i.station_id')
            ->join('qz_yxb_department d ON d.id = i.dept_id')
            ->where($where)
            ->order('CONVERT(a.`contact_name` USING gbk) COLLATE gbk_chinese_ci asc')
            ->limit($page, $pageCount)
            ->select();
    }

    /**
     * 获取erp装修公司员工数据
     * @param $where
     * @param $page
     * @param $pageCount
     * @return mixed
     */
    public function getErpGroupList($where)
    {
        return M('yxb_workergroup')->alias('wg')
            ->field('wg.id as gid,wg.is_del,wg.group_name,wt1.`name` as manager_worktype,a.account,a.`status`,a.image,a.contact_name,a.contact_tel,a.contact_wx,w.`name` as woker_name,w.tel as woker_tel,w.wx_num as woker_wx,wt.`name` as woker_worktype')
            ->join('qz_yxb_worker w ON w.group_id = wg.id')
            ->join('qz_yxb_worktype wt ON wt.id = w.worktype_id')
            ->join('left join qz_yxb_worktype wt1 ON wt1.id = wg.manager_worktype_id')
            ->join('qz_yxb_account a ON a.id = wg.manager_id')
            ->where($where)
            ->select();
    }

    //获取满足条件的意见反馈总数
    public function getSuggestListCount($param,$ordertype,$order){
        //城市
        if(!empty($param['city'])&&isset($param['city'])){
            $map['g.cs'] = array('eq',$param["city"]);
        }
        if(!empty($param['account'])&&isset($param['account'])){  //登陆账号查询
            $map['u.account'] = array('eq',$param["account"]);
        }
        if(!empty($param['date'])&&isset($param['date'])){  //反馈时间查询
            $datalist = explode("~",$param['date']);
            $timestart = strtotime($datalist[0]);
            $timeend = strtotime($datalist[1]);
            if($timestart == $timeend){
                $timeend = $timeend + 86399;
            }
            $map['a.addtime'] = array('BETWEEN',array($timestart,$timeend));
        }
        if(!empty($param['company'])&&isset($param['company'])){  //公司名称查询
            $pattern = '/^\d+(\.\d+)?$/';
            if(preg_match($pattern,$param['company'])){
                $map['g.id'] = array('eq',$param["company"]);
            }else{
                $map['g.user'] = array("LIKE",'%'.$param["company"].'%');
            }
        }

        if(!empty($param['chuliren'])&&isset($param['chuliren'])){  //处理人查询
            $map['a.handler_name'] = array("LIKE",'%'.$param["chuliren"].'%');
        }
        if(!empty($param['handlestatus'])&&isset($param['handlestatus'])){
            $map['a.handle_status'] = array('eq',$param["handlestatus"]);
        }

        if(!empty($param['qudao'])&&isset($param['qudao'])){
            $map['a.feedback_channel_id'] = array('eq',$param["qudao"]);
        }

        return  M('yxb_feedback a')
                ->where($map)
                ->join("join qz_user g on g.id = a.company_id")
                ->join("left join qz_yxb_station w on a.station_id = w.id")
                ->join("join qz_yxb_feedback_channel q on q.id = a.feedback_channel_id")
                ->join("join qz_yxb_account u on u.id = a.account_id")
                ->join('qz_quyu x on x.cid = g.cs')
                ->field('a.*,q.channel_name feedback_channel,g.user companyname,w.name position,u.account')
                ->count();

    }
    //获取意见反馈列表
    public function getSuggestList($param,$ordertype,$order,$pageIndex,$pageCount){
        //城市
        if(!empty($param['city'])&&isset($param['city'])){
            $map['g.cs'] = array('eq',$param["city"]);
        }
        if(!empty($param['account'])&&isset($param['account'])){  //登陆账号查询
            $map['u.account'] = array('eq',$param["account"]);
        }

        if(!empty($param['date'])&&isset($param['date'])){  //反馈时间查询
            $datalist = explode("~",$param['date']);
            $timestart = strtotime($datalist[0]);
            $timeend = strtotime($datalist[1]);
            if($timestart == $timeend){
                $timeend = $timeend + 86399;
            }
            $map['a.addtime'] = array('BETWEEN',array($timestart,$timeend));
        }
        if(!empty($param['company'])&&isset($param['company'])){  //公司名称查询
            $pattern = '/^\d+(\.\d+)?$/';
            if(preg_match($pattern,$param['company'])){
                $map['g.id'] = array('eq',$param["company"]);
            }else{
                $map['g.user'] = array("LIKE",'%'.$param["company"].'%');
            }
        }

        if(!empty($param['chuliren'])&&isset($param['chuliren'])){  //处理人查询
            $map['a.handler_name'] = array("LIKE",'%'.$param["chuliren"].'%');
        }

        if(!empty($param['handlestatus'])&&isset($param['handlestatus'])){
            $map['a.handle_status'] = array('eq',$param["handlestatus"]);
        }

        if(!empty($param['qudao'])&&isset($param['qudao'])){
            $map['a.feedback_channel_id'] = array('eq',$param["qudao"]);
        }

        //$ordertype [排序类型] 1为公司ID排序， 2为反馈时间排序，3为最新处理时间排序
        //$order     [排序顺序] 1表示降序，2表示升序。 默认为1
        if($ordertype == 1 && $order == 1){
            $kk = 'a.company_id desc';
        }elseif($ordertype == 1 && $order == 2){
            $kk = 'a.company_id asc';
        }elseif($ordertype == 2 && $order == 1){
            $kk = 'a.addtime desc';
        }elseif($ordertype == 2 && $order == 2){
            $kk = 'a.addtime asc';
        }elseif($ordertype == 3 && $order == 1){
            $kk = 'a.updata_time desc';
        }elseif($ordertype == 3 && $order == 2){
            $kk = 'a.updata_time asc';
        }elseif($ordertype == 0){
            $kk = 'a.addtime asc';
        }else{
            $kk = 'a.addtime asc';
        }
        return  M('yxb_feedback a')
                ->where($map)
                ->join("join qz_user g on g.id = a.company_id")
                ->join("left join qz_yxb_station w on a.station_id = w.id")
                ->join("join qz_yxb_feedback_channel q on q.id = a.feedback_channel_id")
                ->join("join qz_yxb_account u on u.id = a.account_id")
                ->join('qz_quyu x on x.cid = g.cs')
                ->field('a.*,q.channel_name feedback_channel,g.jc companyname,w.name position,u.account,x.cname cityname')
                ->order($kk)
                ->limit($pageIndex.",".$pageCount)->select();
    }

    //获取单条意见反馈信息
    public function getFeedbackById($param){
        if(!empty($param['id'])&&isset($param['id'])){
            $map['a.id'] = $param['id'];
        }
        return M('yxb_feedback a')
               ->where($map)
               ->join("join qz_user g on g.id = a.company_id")
               ->join("left join qz_yxb_station w on a.station_id = w.id")
               ->join("join qz_yxb_feedback_channel q on q.id = a.feedback_channel_id")
               ->join("join qz_yxb_account u on u.id = a.account_id")
               ->join('qz_quyu x on x.cid = g.cs')
               ->join('qz_province s on s.qz_provinceid = x.uid')
               ->field('a.*,q.channel_name feedback_channel,g.jc companyname,w.name position,u.account,x.cname cityname,s.qz_province')
               ->find();
    }

    /**
     * 获取处理记录
     *@param  $param | 数组  $paeam['id']表示feedbackid
     */
    public function getFeedbackHandle($param){
        $where['feedback_id'] = $param['id'];
        $list = M('yxb_feedback_log')
                ->where($where)
                ->order('addtime desc')
                ->select();
        return $list;
    }

    /**
     * 保存意见反馈提交记录
     */
    public function saveFeedbackHandle($data){
        if(!empty($data['feedbackid'])&&isset($data['feedbackid'])){
            $map['feedback_id'] = $data['feedbackid'];
            $savefeedback['id'] = $data['feedbackid'];
        }
        if(!empty($data['handlerid'])&&isset($data['handlerid'])){
            $map['handler_id'] = $data['handlerid'];
            $savefeedback['handler_id'] = $data['handlerid'];
        }
        if(!empty($data['handlername'])&&isset($data['handlername'])){
            $map['handler_name'] = $data['handlername'];
            $savefeedback['handler_name'] = $data['handlername'];
        }
        if(!empty($data['handlestatus'])&&isset($data['handlestatus'])){
            $map['handle_status'] = $data['handlestatus'];
            $savefeedback['handle_status'] = $data['handlestatus'];
        }
        if(!empty($data['remarkcontent'])&&isset($data['remarkcontent'])){
            $map['remark_content'] = $data['remarkcontent'];
        }
        $map['addtime'] = time();
        $logReturn = M('yxb_feedback_log')->add($map); //保存意见反馈提交记录

        $savefeedback['updata_time'] = $map['addtime'];
        $feedbackRequest = M('yxb_feedback')->save($savefeedback); //feedback表更新记录
        if($logReturn === false && $feedbackRequest === false){
            return false;
        }else{
            return true;
        }
    }

    //获取反馈渠道列表
    public function getFeedbackchannel(){
        return M('yxb_feedback_channel')->select();
    }

    //获取岗位对应id
    public function getStationIdByname($where){
        return M('yxb_station')->field('id,name')->where($where)->select();
    }

}