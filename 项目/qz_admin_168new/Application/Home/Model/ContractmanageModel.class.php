<?php
namespace Home\Model;

Use Think\Model;

class ContractmanageModel extends Model{
    protected $autoCheckFields = false;

    /**
     * 查询外销编号（合同+票据）是否存在
     * @param  array $ids 合同/订单编号
     * @param  string $from 操作类别
     * @return array $result
     */
    public function checkContractExist($ids,$from){
        $map['type'] = array('IN','3,4,6');
        if($from == 6){
            $map['id'] = array('IN',$ids);
            //寄回公司的status必须为4,5
            $map['status'] = array('EQ','9');
        }else{
            $map['contractid'] = array('IN',$ids);
            //助理寄出的status必须为2
            $map['status'] = 2;
        }

        $map['isdel'] = 1;
        $map['isexamine'] = 2;
        if($from == 2){
            $map['status'] = 1;
            $map['isused'] = 1;
        }elseif($from == 4){
            $map['status'] = 3;
            $map['isused'] = 2;
        }


        $result = M('piaoju_contract')->where($map)->select();

        return $result;
    }

    /**
     * 更新合同/外销编号当前状态
     * @param  array $ids 合同/订单ID
     * @param  int   $status 要修改的状态
     * @return array $result
     */
    public function reFreshContract($ids,$status)
    {
        $data['status'] = $status;//修改状态为寄出
        $data['isused'] = 2;
        $map['id'] = array('IN',$ids);
        $result = M('piaoju_contract')->where($map)->save($data);

        return $result;
    }

    /**
     * 更新合同/外销编号当前状态
     * @param  array $ids 合同/订单ID
     * @param  int   $status 要修改的状态
     * @return array $result
     */
    public function assisantFreshContract($ids,$status,$special,$new_beaccpet)
    {
        $data['status'] = $status;//修改状态为寄出
        $data['special'] = $special;
        $data['isused'] = 2;
        $data['beaccept'] = $new_beaccpet;
        $map['id'] = array('IN',$ids);
        $result = M('piaoju_contract')->where($map)->save($data);

        return $result;
    }

    /**
     * 查询待收快递列表
     * @param  array $ids 合同/订单编号
     * @return array $result
     */
    public function getExpressList($status)
    {
        if($status != 6){
            $map['l.signfor'] = $_SESSION['uc_userinfo']['id'];
            $map['c.special'] = 1;
            $map['l.status'] = $status;
            $map['l.isreceived'] = 1;//未签收状态
        }else{
            $map['c.status'] = $status;
            $map['l.isreceived'] = 1;//未签收状态
            $map['l.status'] = $status;
            if($_SESSION['uc_userinfo']['department_id'] == 5){
                $map['c.type'] = array('IN',[3,4,6]);
            }
        }

        $order = 'l.sendtime desc';
        $result = M('piaoju_manage_list')->alias('l')
                                ->join('qz_piaoju_contract as c on c.id = l.main_id')
                                ->join('qz_adminuser as a on a.id = l.sendout')
                                ->field('l.id,c.contractid,c.type,c.special,a.name,l.status,l.signtime,l.express,l.expressid')
                                ->where($map)
                                ->order($order)
                                ->select();
        $re = array_val_chunk($result);//快速将查询结果按照发送批次分组
        $status_arr = [
                    1=>'入库',
                    2=>'审核',
                    3=>'寄出',
                    4=>'销售获取',
                    5=>'签约',
                    6=>'寄回',
                    7=>'助理确认收货',
                    8=>'归档',
                    1=>'待助理审核',
                ];
        foreach ($re as $k => $v) {
            foreach ($v as $key => $value) {
                $contract['name'] = $value['name'];
                $contract['express'] = $value['express'];
                $contract['expressid'] = $value['expressid'];
                $contract['contract'][$key]['contractid'] = $value['contractid'];
                $contract['contract'][$key]['status'] = $status_arr[$value['status']];
                $contract['contract'][$key]['special'] = $value['special'];
                $contract['contract'][$key]['id'] = $value['id'];
            }
            if($contract['express'] == '2'){
                $contract['expressid'] = '其它送达方式';
            }

            $all_result[] = $contract;
            unset($contract);
        }
        return $all_result;
    }


    /**
     * 外销/助理签收快递
     * @param  array $ids 合同/订单编号操作记录ID数组
     * @param  string $from 签收人类别 3为助理签收  6为外销签收
     * @return array $result
     */
    public function receiveExpress($ids,$from)
    {
        $map['id'] = array('IN',$ids);
        $data['isreceived'] = 2;
        $data['receivetime'] = time();
        $up = M('piaoju_manage_list')->where($map)->field('main_id')->select();
        if($from == 3){
            $status = 7;
        }elseif($from == 6){
            $status = 4;
        }
        foreach ($up as $k => $v) {
            $saveAll[] = [
                'main_id'=>$v['main_id'],
                'status'=>$status,
                'sendout'=>$_SESSION['uc_userinfo']['id'],
                'sendtime'=>time(),
                'time'=>time()
            ];
            $upIds[] = $v['main_id'];
        }
        //写入记录
        $result = M('piaoju_manage_list')->addAll($saveAll);
        //同步更新piaoju_contract
        if($result){
            $c_data['status'] = $status;
            $c_map['id'] = array('IN',$upIds);
            M('piaoju_contract')->where($c_map)->save($c_data);
            M('piaoju_manage_list')->where($map)->save($data);
        }
        return $result;
    }

    /**
     * 外销/助理签收快递
     * @param  array $ids 合同/订单编号操作记录ID数组
     * @param  string $from 签收人类别 3为助理签收  6为外销签收
     * @return array $result
     */
    public function checkLostExpress($ids,$from)
    {
        $map['id'] = array('IN',$ids);
        $data['isreceived'] = 2;
        $data['receivetime'] = time();
        $up = M('piaoju_manage_list')->where($map)->field('main_id,sendout')->select();

        foreach ($up as $k => $v) {
            $saveAll[] = [
                'main_id'=>$v['main_id'],
                'status'=>4,
                'special'=>1,
                'sendout'=>$v['sendout'],
                'sendtime'=>time(),
                'time'=>time()
            ];
            $upIds[] = $v['main_id'];
        }

        //删除签收之后的操作记录
        $map = array(
            "main_id" => array("IN",$upIds),
            "status" => array("EGT",4)
        );
        M('piaoju_manage_list')->where($map)->delete();

        //写入记录
        $result = M('piaoju_manage_list')->addAll($saveAll);
        //同步更新piaoju_contract
        if($result){
            $c_data['status'] = 4;
            $c_data['special'] = 1;
            $c_map['id'] = array('IN',$upIds);
            M('piaoju_contract')->where($c_map)->save($c_data);
        }
        return $saveAll;
    }

    /**
     * 检查外销编号（合同+票据）状态是否正常
     * @param  array $ids 合同/订单编号
     * @param  array $type 合同/订单类别
     * @return array $result
     */
    public function checkContractRightStatus($ids,$type)
    {
        //1,  qz_piaoju_contract  type=$type  status=2 special=1 isdel=2  isused=2 isexamine=2
        //2,  qz_piaoju_manage_list  signfor=UID   isreceived=2  status=2
        $map['c.type'] = $type;
        //$map['c.status'] = 4;
        //$map['c.special'] = 1;
        $map['c.isdel'] = 1;
        $map['c.isused'] = 2;
        //$map['l.signfor'] = $_SESSION['uc_userinfo']['id'];
        //$map['l.isreceived'] = 2;
        //$map['l.status'] = 3;
        $map['c.contractid'] = array('IN',$ids);

        $result = M('piaoju_contract')->alias('c')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special')
                                ->select();
        return $result;
    }

    /**
     * 作废时检查编号（合同+票据）状态是否正常
     * @param  array $ids 合同/订单编号
     * @param  array $type 合同/订单类别
     * @return array $result
     */
    public function checkContractAllStatus($ids,$type)
    {
        //1,  qz_piaoju_contract  type=$type  status=2 special=1 isdel=2  isused=2 isexamine=2
        //2,  qz_piaoju_manage_list  signfor=UID   isreceived=2  status=2
        $map['c.type'] = $type;
        //$map['c.status'] = 2;//作废时合同可能是所有状态
        $map['c.contractid'] = array('IN',$ids);
        $map['c.isdel'] = 1;
        $map['c.isused'] = 2;

        $result = M('piaoju_contract')->alias('c')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special')
                                ->select();
        return $result;
    }

    /**
     * 找回时检查编号（合同+票据）状态是否正常
     * @param  array $ids 合同/订单编号
     * @param  array $type 合同/订单类别
     * @return array $result
     */
    public function checkContractFindBack($ids,$type)
    {
        //1,  qz_piaoju_contract  type=$type  status=2 special=1 isdel=2  isused=2 isexamine=2
        //2,  qz_piaoju_manage_list  signfor=UID   isreceived=2  status=2
        $map['c.type'] = $type;
        //$map['c.status'] = 2;//作废时合同可能是所有状态
        $map['c.contractid'] = array('IN',$ids);
        $map['c.isdel'] = 1;
        $map['c.isused'] = 2;
        //$map['c.special'] = 3;

        $result = M('piaoju_contract')->alias('c')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special')
                                ->select();
        return $result;
    }

    /**
     * 合同票据库存
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getContractStockStat($begin,$end,$dep)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            // "b.special" => array("IN",array(1,4,5)),
            "b.type" => array("IN",$dep)
        );

        $buildSql = M("piaoju_manage_list")->where($map)->alias(" a FORCE INDEX(idx_time_id)")
                                            ->join("join qz_piaoju_contract b on a.main_id = b.id")
                                            ->field('a.id,main_id,b.status,FROM_UNIXTIME(time,"%Y-%m-%d") as time,FROM_UNIXTIME(time,"%Y-%m") as `month`,b.type')
                                            ->order("time desc")
                                            ->buildSql();

        $buildSql = M("piaoju_manage_list")->table($buildSql)->alias("t")
                                           ->field("id,main_id,status,
                                                    CASE
                                                        when `status` in (3,6) and t.type in (1,2,3,4)  then 'ht_zt'
                                                        WHEN `status` in (4,5,9) and t.type in (1,2,3,4) then 'ht_sx'
                                                        WHEN `status` in (7,8) and t.type in (1,2,3,4) then 'ht_gs'
                                                        when `status` in (3,6) and t.type in (5,6) then 'pj_zt'
                                                        WHEN `status` in (4,5,9) and t.type in (5,6) then 'pj_sx'
                                                        WHEN `status` in (7,8) and t.type in (5,6) then 'pj_gs'
                                                    end state,
                                                    time,`month`")
                                           ->group("main_id")
                                           ->buildSql();

        return  M("piaoju_manage_list")->table($buildSql)->alias("t1")
                                       ->field("`month`,time,state, count(main_id) as count")
                                       ->group("time,state")
                                       ->order("time")->select();
    }

    /*
     * 作废时检查编号（合同+票据）状态是否正常
     * @param  array $ids 合同/订单编号
     * @param  array $type 合同/订单类别
     * @return array $result
     */
    public function checkCommerceStatus($ids,$type)
    {
        //1,  qz_piaoju_contract  type=$type  status=2 special=1 isdel=2  isused=2 isexamine=2
        //2,  qz_piaoju_manage_list  signfor=UID   isreceived=2  status=2
        $map['c.type'] = $type;
        $map['c.contractid'] = array('IN',$ids);
        $map['c.isdel'] = 1;
        $map['c.isused'] = 2;
        //$map['l.signfor'] = $_SESSION['uc_userinfo']['id'];
        //$map['l.isreceived'] = 2;
        //$map['l.status'] = 2;
        //$map['c.special'] = 1;

        $result = M('piaoju_contract')->alias('c')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special')
                                ->select();
        return $result;
    }

    /**
     * 查询合同/票据进度
     * @param  string   $code   合同/订单编号
     * @return array $result
     */
    public function getContratProgress($code)
    {
        if(!empty($code)){
            $code = 'QZ'.trim($code);
            $where['contractid'] = $code;
            $where['type'] = array('IN','3,4');
            $result = M('piaoju_contract')->where($where)->find();
        }
        return $result;
    }

    /**
     * 获取合同详细列表
     * @param  [type] $dep  [查询分类]
     * @param  [type] $type [类别]
     * @return [type]       [description]
     */
    public function getContractDetailsStat($code,$state,$age,$addr,$sale,$dep,$type = 1,$ids,$isaudit)
    {
        $map = array(
            "b.type" => array("IN",$dep)
        );
        $model = M('piaoju_manage_list');
        $buildSql = $model->alias("a")->where($map)
                                       ->join("join qz_piaoju_contract b on b.id = a.main_id")
                                       ->field('b.id,b.contractid, b.`status`,b.special,b.reason,time,if(a.status in (4,5),signtime,null) as qx_time')
                                       ->order("a.time desc")->buildSql();

        $model->table($buildSql)->alias("t");
        if ($type == 1) {
            //合同
            $model->join("left join qz_piaoju_saler_contract c on c.main_cid = t.id");
        } else {
            //票据
            $model->join("left join qz_piaoju_saler_contract c on c.main_vid = t.id");
        }

        $buildSql = $model->join("left join qz_adminuser u on u.id = c.saler")->group("t.id")->field("t.id,t.time, t.contractid, t.`status`,t.special , max(t.qx_time) as qx_time,u.`name`,t.reason,u.id as uid")->buildSql();
        $map = array();
        if (!empty($code)) {
            $map["contractid"] = array("EQ",$code);
        }

        if (!empty($state)) {
            switch ($state) {
                case '1':
                    //已签约
                    $map["status"] = array("EQ",5);
                    break;
                case '2':
                    //归档
                    $map["status"] = array("EQ",8);
                    break;
                case '3':
                    //作废
                    $map["special"] = array("EQ",2);
                    break;
                case '4':
                    //使用中
                    $map["status"] = array("EQ",4);
                    break;
                case '5':
                    //在途
                    $map["status"] = array("IN",array(3,6));
                    break;
                case '6':
                    //遗失
                    $map["special"] = array("EQ",3);
                    break;
            }
        }

        if (!empty($age)) {
            $lasttime = strtotime("-90 day",mktime(0,0,0,date("m"),date("d"),date("Y")));
            if ($age == 2) {
                $map["qx_time"] = array("EGT",$age);
            } else {
                $map["qx_time"] = array("ELT",$age);
            }
        }

        if (!empty($addr)) {
            if (empty($state)) {
                switch ($addr) {
                    case '1':
                        $map["status"] = array("IN",array(4,5));
                        break;
                    case '2':
                        $map["status"] = array("IN",array(7,8));
                        break;
                    case '3':
                        $map["status"] = array("IN",array(3,6));
                        break;
                }
            } else {
                $map["special"] = array("EQ",1);
                if ($state  == 1) {
                    if ($addr == 1) {
                        $map["status"] = array("EQ",5);
                    }
                } elseif ($state  == 2) {
                    if ($addr == 2) {
                        $map["status"] = array("EQ",8);
                    }
                } elseif ($state  == 4) {
                    if ($addr == 1) {
                        $map["status"] = array("EQ",4);
                    }
                } elseif ($state  == 5) {
                    $map["status"] = array("IN",array(3,6));
                } elseif ($state  == 6) {
                    $map["special"] = array("EQ",3);
                    if ($addr == 1) {
                       $map["status"] = array("IN",array(4,5));
                    } elseif ($addr == 2) {
                       $map["status"] = array("IN",array(7,8));
                    } elseif ($addr == 3) {
                       $map["status"] = array("IN",array(3,6));
                    }
                } elseif ($state  == 3) {
                    $map["special"] = array("EQ",2);
                    if ($addr == 1) {
                       $map["status"] = array("IN",array(4,5));
                    } elseif ($addr == 2) {
                       $map["status"] = array("IN",array(7,8));
                    } elseif ($addr == 3) {
                       $map["status"] = array("IN",array(3,6));
                    }
                }

            }
        }

        if (count($ids) > 0) {
            if ($isaudit) {
                $map["_complex"] = array(
                    "uid" => array("IN",$ids),
                    "status" => array("EQ",2),
                    "_logic" => "or"
                );
            } else {
                $map["uid"] = array("IN",$ids);
            }
        }

        if (!empty($sale)) {
           $map["uid"] = array("EQ",$sale);
        }

        //在途，使用中，遗失，作废，已签约，归档
        $buildSql =  $model->table($buildSql)->where($map)->alias("t2")
                           ->field("t2.*,
                                    case
                                        when t2.status in (3,6) and special = 1 then 1
                                        when t2.status in (5,7) and special = 1 then 5
                                        when t2.status = 4 and special = 1 then 2
                                        when t2.status = 8 and special = 1 then 6
                                        when special = 3 then 3
                                        when special = 2 then 4
                                        when t2.status = 2 and special = 1 then 7
                                    end as type")
                           ->buildSql();
        return  $model->table($buildSql)->alias("t3")->order("type,contractid")->limit(50)->select();

    }

    /**
     * 查询票据/合同上传的图片
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findContractImg($id)
    {
        $map = array(
            "main_id" => array("EQ",$id),
            "status" => array("EQ",1)
        );
        return M("piaoju_imgs")->where($map)->field("imgurl")->select();
    }

    /**
     * 查询票据历史记录
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findContractHistory($id)
    {
        $sql = "SELECT
                t2.contractid,FROM_UNIXTIME(t2.sq_time,'%Y-%m-%d') as sq_time,u.name as out_name,t2.name,
                u1.name as in_name,FROM_UNIXTIME(t2.out_time,'%Y-%m-%d') as out_time,
                FROM_UNIXTIME(t2.in_time,'%Y-%m-%d') as in_time,FROM_UNIXTIME(t2.zf_time,'%Y-%m-%d') as zf_time,
                FROM_UNIXTIME(t2.ys_time,'%Y-%m-%d') as ys_time,FROM_UNIXTIME(t2.gd_time,'%Y-%m-%d') as gd_time,
                FROM_UNIXTIME(t2.qy_time,'%Y-%m-%d') as qy_time
                from (
                    select
                    t1.contractid,
                    t1.name,t1.sq_time,
                    max(out_id) as out_id,
                    max(out_time) as out_time,
                    max(in_id) as in_id,
                    max(in_time) as in_time,
                    max(zf_time) as zf_time,
                    max(ys_time) as ys_time,
                    max(gd_time) as gd_time,
                    max(qy_time) as qy_time
                    from (
                            select
                            t.id,t.contractid,t.name,t.sq_time,c.status,c.sendtime,c.time,c.special,c.sendout,if(c.`status` = 3,c.sendout,null) as out_id,if(c.`status` = 3,c.sendtime,null) as out_time,
                            if(c.`status` = 6,c.sendout,null) as in_id,if(c.`status` = 6,c.sendtime,null) as in_time,if(c.special = 2,c.time,null) as zf_time,if(c.special = 3,c.time,null) as ys_time,
                            if(c.status = 8,c.time,null) as gd_time,
                            if(c.status = 5,c.time,null) as qy_time
                            from (
                                select a.id, a.contractid,u.`name`,b.time as 'sq_time' FROM qz_piaoju_contract a
                                join qz_piaoju_batch b on a.batch = b.id
                                join qz_adminuser u on u.id = b.examineid
                                where a.id = '$id'
                            ) t join  qz_piaoju_manage_list c on c.main_id = t.id
                            order by c.time desc
                    ) t1
                    group by t1.id
                ) t2
                left join qz_adminuser u on u.id = t2.in_id
                left join qz_adminuser u1 on u1.id = t2.out_id";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    public function getContractStateStat($begin,$end,$dep)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "b.type" => array("IN",$dep),
            "b.status" => array("GT",2)
        );

        $buildSql = M("piaoju_manage_list")->where($map)->alias("a")
                                           ->join("join qz_piaoju_contract b on a.main_id = b.id")
                                           ->field('b.id,b.type,b.`status`,b.special,FROM_UNIXTIME( a.time,"%Y-%m-%d") as time,FROM_UNIXTIME(a.time,"%Y-%m") as date,
                                                CASE
                                                    when b.`status` = 5 and b.type in (1,2,3,4)  then "ht_qy"
                                                    WHEN b.`status`= 8 and b.type in (1,2,3,4) then "ht_gd"
                                                    WHEN b.`special` = 2 and b.type in (1,2,3,4) then "ht_zf"
                                                    when b.`special` = 3 and b.type in (1,2,3,4) then "ht_ys"
                                                    when b.`status` in(4,7) and b.type in (1,2,3,4) then "ht_syz"
                                                    when b.`status` in (3,6) and b.type in (1,2,3,4) then "ht_zt"
                                                    when b.`status` = 9 and b.type in (1,2,3,4) then "ht_dd"
                                                    when b.`status` = 5 and b.type in (5,6)  then "pj_qy"
                                                    WHEN b.`status`= 8 and b.type in (5,6) then "pj_gd"
                                                    WHEN b.`special` = 2 and b.type in (5,6) then "pj_zf"
                                                    when b.`special` = 3 and b.type in (5,6) then "pj_ys"
                                                    when b.`status`in(4,7) and b.type in (5,6) then "pj_syz"
                                                    when b.`status` = 9 and b.type in (5,6) then "pj_dd"
                                                    when b.`status` in (3,6) and b.type in (5,6) then "pj_zt"
                                                end state')
                                           ->order("a.time desc")
                                           ->buildSql();
         $buildSql = M("piaoju_manage_list")->table($buildSql)->alias("t")
                                            ->group("id")->buildSql();

        return M("piaoju_manage_list")->table($buildSql)->alias("t")
                                      ->field("t.time,t.date,count(t.time) as count,t.state")
                                      ->group("t.time,t.state")
                                      ->order("t.time")->select();
    }

    /**
     * [findUsers 获取adminuser用户]
     * @param  [string]  $name  [姓名]
     * @param  integer $limit   [获取条数]
     * @param  integer $limit   [获取条数]
     * @return [array] $result  [标签数组]
     */
    public function findUsers($name,$exact=0,$limit = 15){
        $map['uid'] = array('IN','1,7,56,61,62,36,39,40,41,42,43,45,72,77,71,58,59,65,67,3,46,73,60,37');
        $map['name'] =  array('EQ',$name);
        $map['stat'] =  array('EQ',1);
        $role = $_SESSION['uc_userinfo']['role_name'];
        if($role != '外销助理'){
            $map['uid'] = '67';//如果时候非外销助理角色寄出，只能邮寄给外销助理
        }
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('adminuser')->where($map)->find();
        if (!empty($complete)) {
            $limit = $limit - 1;
            $map['id'] = ['NEQ',$complete['id']];
        }
        if($exact != 1){
            //重新定义名字查询条件
            $map['name'] =  array('like','%'.$name.'%');

            $result = M('adminuser')->where($map)->limit($limit)->select();
            if (!empty($complete)) {
                array_unshift($result, $complete);
            }
        }else{
            $result = $complete;
        }

        return $result;
    }

    /**
     * [getExaminedContract根据助理账号，按部门查询待审核的合同列表]
     * @param  [void]
     * @param  void
     * @return [array] $result  [标签数组]
     */
    public function getExaminedContract($type,$special,$uid=null)
    {
        //查询待审核的合同  status=9 speciel=4签约待审核 | status=9 speciel=5作废待审核
        if($_SESSION['uc_userinfo']['department_id'] == 5){
            //外销
            $contract = [3,4,6];
        }else{
            //商务
            $contract= [1,2,5];
        }

        if(!empty($uid)){
            $map = [
                'c.type'=>array('IN',$contract),
                'c.status'=>array('EQ',$type),
                'c.special' => array('EQ',$special),
                array(
                    'b.saler'=>array('eq',$uid),
                    'c.contractid'=> array(
                        array('eq','QZ'.$uid),
                        array('eq','SJYWT'.$uid),
                        "or"
                    ),
                    '_logic'=>'or'
                ),
            ];
        }else{
            $map = [
                'c.type'=>array('IN',$contract),
                'c.status'=>array('EQ',$type),
                'c.special' => array('EQ',$special)
            ];
        }

        return M('piaoju_contract')->alias('c')
                                ->join('join qz_piaoju_saler_contract b on b.main_cid = c.id')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special,b.saler as sendout,c.reason')
                                ->group('c.id')
                                ->select();
    }

    /**
     * [getExaminedContract根据助理账号，按部门查询待审核的合同列表]
     * @param  [void]
     * @param  void
     * @return [array] $result  [标签数组]
     */
    public function getNeedCheckList($type,$special,$beaccept)
    {
        //查询待审核的合同  status=7 speciel=4 beaccept=2 签约待审核 | status=7 speciel=5 beaccept=2作废待审核 | status=9  special=7 beaccept=2遗失待审核
        if($_SESSION['uc_userinfo']['department_id'] == 5){
            //外销
            $contract = [3,4,6];
        }else{
            //商务
            $contract= [1,2,5];
        }

        $map = [
            'c.type'=>array('IN',$contract),
            'c.status'=>array('EQ',$type),
            'c.special' => array('EQ',$special),
            'c.beaccept' => array('EQ',$beaccept)
        ];


        $result = M('piaoju_contract')->alias('c')
                                ->join("join qz_piaoju_saler_contract b on b.main_cid = c.id")
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special,c.beaccept,c.reason,b.saler as sendout')
                                ->group('c.id')
                                ->select();

        return $result;
    }

    /**
     * [getSendBackList 获取外销寄回合同列表]
     * @param  [void]
     * @param  void
     * @return [array] $result  [标签数组]
     */
    public function getSendBackList()
    {
        //查询待寄回公司列表  status=5 special=1签约 status=4 special=2作废
        //且记录表中收货人列表为当前登录用户ID

        $map = [
            'l.sendout' => array('eq',$_SESSION['uc_userinfo']['id']),
            array(
                array(
                    'c.status'=>array('eq',9),
                    'l.status'=>array('eq',9),
                    'l.special'=>array('eq',4),
                    'c.beaccept'=>array('eq',2),
                    '_logic'=>'AND'
                ),
                array(
                    'c.status'=>array('eq',9),
                    'l.status'=>array('eq',9),
                    'l.special'=>array('eq',5),
                    'c.beaccept'=>array('eq',2),
                    '_logic'=>'AND'
                ),
                '_logic'=>'OR'
            ),
            '_logic'=>'and'
        ];

        $result = M('piaoju_contract')->alias('c')
                                ->join('qz_piaoju_manage_list as l on c.id = l.main_id')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special')
                                ->group('c.id')
                                ->select();
        return $result;
    }

    /**
     * [getBeSignedUpList 获取外销带签约合同列表]
     * @param  string   $type
     * @param  string   $special
     * @param  string   $contractid  默认为空，搜索的时候传入编号
     * @return [array] $result  [待签约数组]
     */
    public function getBeSignedUpList($type,$special,$contractid=null)
    {
        //查询待签约列表  status=4 special=1 sendout=UID

        $map = [
            'l.sendout' => array('eq',$_SESSION['uc_userinfo']['id']),
            'c.status'=>array('eq',$type),
            'c.special'=>array('eq',$special),
            'l.status'=>array('eq',$type),
            'l.special'=>array('eq',$special),
        ];
        if(!empty($contractid)){
            $map['c.contractid'] = $contractid;
        }

        $result = M('piaoju_contract')->alias('c')
                                ->join('qz_piaoju_manage_list as l on c.id = l.main_id')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special')
                                ->group('c.id')
                                ->select();
        return $result;
    }

    /**
     * [getHasCheckedList 获取已审核列表列表]
     * @param  void
     * @return [array] $result  [待签约数组]
     */
    public function getHasCheckedList($contractid=null)
    {
        //查询待签约列表  status=4 special=1 sendout=UID
        ////审核通过列表
        //签约 status=5 special=1 beaccept=3
        //作废 status=4 special=2 beaccept=4

        $map = [
            'l.sendout' => array('eq',$_SESSION['uc_userinfo']['id']),
            //'l.status' => ['EQ',6],
            [
                array(
                    'c.status' => ['EQ',8],
                    'c.special' => ['EQ',1],
                    'c.beaccept' => ['EQ',3],
                    'l.status' => ['EQ',5],
                    'l.special' => ['EQ',1]
                ),
                array(
                    'c.status' => ['EQ',4],
                    'c.special' => ['EQ',2],
                    'c.beaccept' => ['EQ',4],
                    'l.status' => ['EQ',4],
                    'l.special' => ['EQ',2]
                ),
                array(
                    'c.status' => ['EQ',4],
                    'c.special' => ['EQ',3],
                    'c.beaccept' => ['EQ',5],
                    'l.status' => ['EQ',4],
                    'l.special' => ['EQ',3]
                ),
                '_logic'=>'OR'
            ]
        ];
        if(!empty($contractid)){
            $map['c.contractid'] = $contractid;
        }
        $order = 'l.sendtime desc';
        $result = M('piaoju_contract')->alias('c')
                                ->join('qz_piaoju_manage_list as l on c.id = l.main_id')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special,c.beaccept,l.sendout')
                                ->group('c.id')
                                ->order($order)
                                ->select();
        //var_dump(M()->getLastSql());
        return $result;
    }

    /**
     * [getTotalCount 获取合同票据数量统计总览]
     * @param  void
     * @return [array] $result  [待签约数组]
     */
    public function getTotalCount()
    {
        //总库存量（助理导入的所有合同编号且军长审批通过） status>1 isexamine=2
        //未使用数量（军长审批后所有状态自动变为未使用）status=2 isexamine=2
        //已使用(外销助理寄出或商务销售点击获取编号) status>2 isexamine=2
        $map['status'] = array('GT',1);
        $map['isexamine'] = 2;
        $map['isdel'] = 1;
        $result = M('piaoju_contract')->where($map)->field("count(id) as total,count(IF (status=2, id, NULL)) AS notused,count(IF (status>2, id, NULL)) AS used")->select();
        return $result;
    }

    /**
     * [getKuCunAreaData 获取合同票据库存使用占比]
     * @param  void
     * @return [array] $result
     */
    public function getKuCunAreaData()
    {
        //公司 status = 2,7,8
        //在途 status = 3,6
        //销售处  status = 4,5,9
        $map['status'] = array('GT',1);
        $map['isexamine'] = 2;
        $map['isdel'] = 1;
        $result = M('piaoju_contract')->where($map)->field("count(id) as total,count(IF (status=2 or status=7 or status=8, id, NULL)) AS gongsi,count(IF (status=3 or status=6, id, NULL)) AS zaitu,count(IF (status=4 or status=5 or status=9, id, NULL)) AS xiaoshou")->select();
        if($result[0]['gongsi'] == 0){
            $kucun_area['gongsi'] = 0;
        }else{
            $kucun_area['gongsi'] = round($result[0]['gongsi']/$result[0]['total']*100);
        }
        if($result[0]['zaitu'] == 0){
            $kucun_area['zaitu'] = 0;
        }else{
            $kucun_area['zaitu'] = round($result[0]['zaitu']/$result[0]['total']*100);
        }
        if($result[0]['xiaoshou'] == 0){
            $kucun_area['xiaoshou'] = 0;
        }else{
            $kucun_area['xiaoshou'] = round($result[0]['xiaoshou']/$result[0]['total']*100);
        }
        return $kucun_area;
    }

    /**
     * [getKuCunStateData 获取合同票据合同状态占比]
     * @param  void
     * @return [array] $result
     */
    public function getKuCunStateData()
    {
        //待签约 status=4 special=1
        //签约 status=5 special=1
        //作废 status=4 special=2
        //遗失 status=4 special=3
        //对比已使用
        $map['status'] = array('GT',1);
        $map['isexamine'] = 2;
        $map['isdel'] = 1;
        $result = M('piaoju_contract')->where($map)->field("count(IF (status=4 and special=1, id, NULL)) AS daiqianyue,count(IF (status=5 and special=1, id, NULL)) AS qianyue,count(IF (special=2, id, NULL)) AS zuofei,count(IF (special=3, id, NULL)) AS yishi")->select();
        $total = $result[0]['daiqianyue'] + $result[0]['qianyue'] + $result[0]['zuofei'] +$result[0]['yishi'];
        if($result[0]['daiqianyue'] == 0){
            $kucun_state['daiqianyue'] = 0;
        }else{
            $kucun_state['daiqianyue'] = round($result[0]['daiqianyue']/$total*100,2);
        }
        if($result[0]['qianyue'] == 0){
            $kucun_state['qianyue'] = 0;
        }else{
            $kucun_state['qianyue'] = round($result[0]['qianyue']/$total*100,2);
        }
        if($result[0]['zuofei'] == 0){
            $kucun_state['zuofei'] = 0;
        }else{
            $kucun_state['zuofei'] = round($result[0]['zuofei']/$total*100,2);
        }
        if($result[0]['yishi'] == 0){
            $kucun_state['yishi'] = 0;
        }else{
            $kucun_state['yishi'] = round($result[0]['yishi']/$total*100,2);
        }

        return $kucun_state;
    }

    /**
     * [getAssistantCheckedList 获取助理审核列表]
     * @param  void
     * @return [array] $result  [待签约数组]
     */
    public function getAssistantCheckedList($uid=null)
    {
        //查询待签约列表  status=4 special=1 sendout=UID


        if(!empty($uid)){
            $map = [
                'l.assistant' => array('eq',$_SESSION['uc_userinfo']['id']),
                'c.beaccept'=>array('GT',2),
                array(
                    'c.contractid'=>array('eq','QZ'.$uid),
                    'l.sendout'=>array('eq',$uid),
                    '_logic'=>'or'
                ),
            ];
        }else{
            $map = [
                'l.assistant' => array('eq',$_SESSION['uc_userinfo']['id']),
                'c.beaccept'=>array('GT',2)
            ];
        }

        $order = 'l.time desc';
        $result = M('piaoju_contract')->alias('c')
                                ->join('qz_piaoju_manage_list as l on c.id = l.main_id')
                                ->where($map)
                                ->field('c.id,c.type,c.contractid,c.status,c.special,c.beaccept,l.sendout')
                                ->order($order)
                                ->group('c.id')
                                ->select();
        return $result;
    }

    /**
     * [findContractLast 查找合同的申请销售ID]
     * @param  [type] $contractid [合同ID]
     * @return [type]             [description]
     */
    public function findContractLast($contractid)
    {
        //查询寄出的销售人员
        //status=6   main_id=$contractid
        if($contractid){
            $where['main_id'] = $contractid;
            $where['status'] = 4;
            $result = M('piaoju_manage_list')->where($where)->field('sendout')->order('id desc')->find();
            return $result;
        }
    }

    /**
     * [checkLastStatus 助理审核后更新为归档]
     * @param  [array] $contractid [要更改的合同ID数组]
     * @return [type]             [description]
     */
    public function checkLastStatus($contractid)
    {
        if(!empty($contractid)){
            $where['id'] = ['in',$contractid];
            $data['status'] = 8;//status=8归档
            $result = M('piaoju_contract')->where($where)->save($data);
        }
    }

    public function getDeptCityList($depid)
    {
        if (!empty($depid)) {
            $map = array(
                "dept" => array("EQ",$depid)
            );
        }
        return M("sales_city_manage")->where($map)->select();
    }

    /**
     * 获取全部合同数据
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getContractAll($type)
    {
        $map = array(
            "type" => array("IN",$type),
            "isexamine" => array("EQ",2),
            "isdel" => array("EQ",1)
        );
        return M("piaoju_contract")->where($map)
                                   ->field("count(id) as `all`, count(if(status = 8 or special in (2,3),1,null)) as audit_count")
                                   ->find();

    }

    public function getUserContractDetails($ids)
    {
        $map = array(
            "a.isexamine" => array("EQ",2),
            "a.type" => array("IN",array(1,2,3,4))
        );
        if (is_array($ids)) {
            $map["b.saler"] = array("IN",$ids);
        }

        $buildSql = M("piaoju_contract")->where($map)->alias("a")
                                        ->join("join qz_piaoju_saler_contract as b on a.id = b.main_cid")
                                        ->join("join qz_adminuser u on u.id = b.saler")
                                        ->join("join qz_piaoju_manage_list as c on c.main_id = a.id")
                                        ->group("a.id")->field(" a.*,b.saler, u.name,MAX(IF(c.`status` = 3 and c.special = 1,signtime,null)) as out_signtime")
                                        ->buildSql();
        return M("piaoju_contract")->table($buildSql)->alias("t")
                                   ->group("t.saler")
                                   ->field("t.name,
                                            count(if(t.status in (4,5,6,9) and t.special in(1,4,5,7),1,null)) as 'all',
                                            count(if(t.status = 8 and t.special = 1,1,null)) as 'gd',
                                            count(if(t.special = 2 ,1,null)) as 'zf',
                                            count(if(t.special = 3 ,1,null)) as 'ys',
                                            count(if(t.status = 3 ,1,null)) as 'out_zt',
                                            count(if(t.status = 6 ,1,null)) as 'in_zt',
                                            count(if(t.status <> 8 and TIMESTAMPDIFF(DAY,CURRENT_DATE(),FROM_UNIXTIME(out_signtime)) > 90 ,1,null)) as 'time_out_day'")
                                   ->select();

    }
}