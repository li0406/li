<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Db;

use Think\Model;

class CardModel extends Model
{
    protected $tableName = "card";
    //编辑礼券
    public function editCommon($data,$id){
        $map['id'] = array("EQ",$id);
        $data['update_time'] = time();
        return M('card')->where($map)->save($data);
    }

    //添加礼券
    public function addCommon($data){
        $data['add_time'] = time();
        return M('card')->add($data);
    }

    //添加公司礼券记录
    public function addCardComRecord($data){
        $data['apply_time'] = time();
        return M('card_com_record')->add($data);
    }

    //添加公司礼券信息
    public function addCardCom($data){
        $data['add_time'] = time();
        return M('card_com')->add($data);
    }

    //更新公司礼券记录
    public function updateCardComRecord($data,$id){
        $map['id'] = array("EQ",$id);
        return M('card_com_record')->where($map)->save($data);
    }

    /**
     * 获取专用券条数
     * @param $where
     * @param $page
     * @param $count
     */
    public function getSpecialCount($where){
        $map['c.type'] = array('EQ',2);
        $map['r.check'] = array('NEQ',4);
        //礼券名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }

        //公司名称/id
        if(!empty($where['card_id'])){
            $map['_complex'] =array(
                "u.qc" =>array("LIKE","%".$where['card_id']."%"),
                "u.id"    =>array("LIKE","%".$where['card_id']."%"),
                "_logic"  =>"OR"
            );
        }

        //城市
        if(!empty($where['cs'])){
            $map['u.cs'] = array('EQ',$where['cs']);
        }

        //上传时间
        if(!empty($where['apply_time'])){
            $time = explode('~',$where['apply_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.apply_time'] = array('between',[$start,$end]);
        }

        //审核状态
        if(!empty($where['check'])){
            $map['r.check'] = array('EQ',$where['check']);
        }

        //有效开始时间
        if(!empty($where['start'])){
            $time = explode('~',$where['start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.start'] = array('between',[$start,$end]);
        }
        //有效结束时间
        if(!empty($where['end'])){
            $time = explode('~',$where['end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.end'] = array('between',[$start,$end]);
        }

        //活动开始时间
        if(!empty($where['activity_start'])){
            $time = explode('~',$where['activity_start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_start'] = array('between',[$start,$end]);
        }
        //活动结束时间
        if(!empty($where['activity_end'])){
            $time = explode('~',$where['activity_end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_end'] = array('between',[$start,$end]);
        }

       return M('card_com_record')->alias('r')
            ->join('qz_card_com com on r.card_com_id=com.id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($map)
            ->count();
    }

    /**
     * 获取专用券列表
     * @param $where
     * @param $page
     * @param $count
     */
    public function getSpecialList($where,$page,$count){
        $map['c.type'] = array('EQ',2);
        $map['r.check'] = array('NEQ',4);
        //礼券名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }

        //公司名称/id
        if(!empty($where['card_id'])){
            $map['_complex'] =array(
                "u.qc" =>array("LIKE","%".$where['card_id']."%"),
                "u.id"    =>array("LIKE","%".$where['card_id']."%"),
                "_logic"  =>"OR"
            );
        }

        //城市
        if(!empty($where['cs'])){
            $map['u.cs'] = array('EQ',$where['cs']);
        }

        //上传时间
        if(!empty($where['apply_time'])){
            $time = explode('~',$where['apply_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.apply_time'] = array('between',[$start,$end]);
        }

        //审核状态
        if(!empty($where['check'])){
            $map['r.check'] = array('EQ',$where['check']);
        }

        //有效开始时间
        if(!empty($where['start'])){
            $time = explode('~',$where['start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.start'] = array('between',[$start,$end]);
        }
        //有效结束时间
        if(!empty($where['end'])){
            $time = explode('~',$where['end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.end'] = array('between',[$start,$end]);
        }

        //活动开始时间
        if(!empty($where['activity_start'])){
            $time = explode('~',$where['activity_start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_start'] = array('between',[$start,$end]);
        }
        //活动结束时间
        if(!empty($where['activity_end'])){
            $time = explode('~',$where['activity_end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_end'] = array('between',[$start,$end]);
        }

        $buildSql = M('card_com_record')->alias('r')
            ->join('qz_card_com com on r.card_com_id=com.id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->join('left join qz_card_user cu on cu.record_id = r.id')
            ->where($map)
            ->field('r.id,c.name,com.com_id,u.qc,u.cs,q.cname,r.apply_time,r.activity_start,r.activity_end,r.start,r.end,r.check,r.check_username,count(cu.id) as get_num,r.amount')
            ->group('r.id')
            ->buildSql();

        return M('card')->table($buildSql)->alias('t')->field('t.*')->order('t.apply_time desc')->limit($page,$count)->select();

    }

    /**
     * 获取礼券信息
     * @param $id
     */
    public function getSpecialInfo($id,$type){
        $map['r.id'] = array('EQ',$id);
        $map['c.type'] = array('EQ',2);
        if($type == 1){
            $map['r.check'] = array('EQ',1);
        }
        return M('card_com_record')->alias('r')
            ->join('qz_card_com com on r.card_com_id=com.id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->field('r.id,c.name,com.com_id,u.qc,q.cname,r.apply_time,r.activity_start,r.activity_end,r.start,r.end,r.amount,c.money1,c.money2,c.money3,c.gift,c.active_type,c.rule,c.module,u.cs,r.check_reason')
            ->where($map)
            ->find();
    }

    public function getCommonInfo($id){
        $map['r.id'] = array('EQ',$id);
        $map['c.type'] = array('EQ',1);
        return M('card_com_record')->alias('r')
            ->join('qz_card_com com on r.card_com_id=com.id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->field('r.id,c.name,com.com_id,u.qc,q.cname,r.start,r.end,r.amount,c.money1,c.money2,c.rule,u.cs,r.check_reason,r.amount')
            ->where($map)
            ->group('r.id')
            ->find();
    }


    /**
     * 通用券数量
     * @param $where
     */
    public function getCommonCount($where){
        $map['c.type'] = array('EQ',1);
        //名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }
        //创建时间
        if(!empty($where['add_time'])){
            $time = explode('~',$where['add_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['c.add_time'] = array('between',[$start,$end]);
        }
        //状态
        if(!empty($where['enable'])){
            if($where['enable'] == 1){
                $map["_complex"] = array(
                    "c.enable" => 1,
                    "_complex" => array("c.disable_time" =>  array("GT",time()),
                        "c.enable"=>array("EQ",2),
                        "_logic" => "AND"
                    ),
                    "_logic" => "OR"
                );
            }else{
                $map["_complex"] = array(
                    "c.enable" =>  array("EQ","2"),
                    "c.disable_time" =>  array("LT",time())
                );
            }

        }

        return M('card')->alias('c')->where($map)->count();
    }

    /**
     * 通用券列表
     * @param $where
     * @param $page
     * @param $count
     * @return mixed
     */
    public function getCommonList($where,$page,$count){
        $map['c.type'] = array('EQ',1);
        //名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }
        //创建时间
        if(!empty($where['add_time'])){
            $time = explode('~',$where['add_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['c.add_time'] = array('between',[$start,$end]);
        }
        //状态
        if(!empty($where['enable'])){
            if($where['enable'] == 1){
                $map["_complex"] = array(
                    "c.enable" => 1,
                    "_complex" => array("c.disable_time" =>  array("GT",time()),
                        "c.enable"=>array("EQ",2),
                        "_logic" => "AND"
                    ),
                    "_logic" => "OR"
                );
            }else{
                $map["_complex"] = array(
                    "c.enable" =>  array("EQ","2"),
                    "c.disable_time" =>  array("LT",time())
                );
            }

        }

        //获取礼券信息和在用商家数量
        $buildSql =  M('card')->alias('c')
            ->join('left join qz_card_com com on com.card_id=c.id')
            ->field('c.id,c.name,c.money1,c.money2,c.add_time,c.enable,c.disable_time,count(com.id) as com_count')
            ->where($map)
            ->group('c.id')
            ->buildSql();

        //获取业主领用数量
        $buildSql2 =  M('card')->table($buildSql)->alias('t')
            ->join('left join qz_card_com com on com.card_id = t.id')
            ->join('left join qz_card_com_record r on com.id = r.card_com_id')
            ->join('left join qz_card_user cu on cu.record_id = r.id')
            ->field('t.*,count(cu.id) as user_count')
            ->group('t.id')
            ->buildSql();

        return M('card')->table($buildSql2)->alias('t1')
            ->order('t1.add_time desc')
            ->limit($page,$count)
            ->select();

    }

    /**
     * 商家领用数量
     * @param $where
     */
    public function getSellerCount($where){
        $map['c.type'] = array('EQ',1);
        $map['r.check'] = array('NEQ',4);
        //礼券名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }

        //公司名称/id
        if(!empty($where['card_id'])){
            $map['_complex'] =array(
                "u.qc" =>array("LIKE","%".$where['card_id']."%"),
                "u.id"    =>array("LIKE","%".$where['card_id']."%"),
                "_logic"  =>"OR"
            );
        }

        //城市
        if(!empty($where['cs'])){
            $map['u.cs'] = array('EQ',$where['cs']);
        }

        //商家申请时间
        if(!empty($where['apply_time'])){
            $time = explode('~',$where['apply_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.apply_time'] = array('between',[$start,$end]);
        }


        //有效开始时间
        if(!empty($where['start'])){
            $time = explode('~',$where['start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.start'] = array('between',[$start,$end]);
        }
        //有效结束时间
        if(!empty($where['end'])){
            $time = explode('~',$where['end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.end'] = array('between',[$start,$end]);
        }

        //活动开始时间
        if(!empty($where['activity_start'])){
            $time = explode('~',$where['activity_start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_start'] = array('between',[$start,$end]);
        }
        //活动结束时间
        if(!empty($where['activity_end'])){
            $time = explode('~',$where['activity_end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_end'] = array('between',[$start,$end]);
        }

        $buildSql = M('card_com_record')->alias('r')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($map)
            ->field('
                c.name,com.com_id,u.qc,q.cname,r.apply_time,r.activity_start,r.activity_end,r.start,r.end,r.check,r.check_username,c.disable_time,c.enable,
                case 
                when c.enable = 2 and unix_timestamp() >= c.disable_time then 7
                when r.CHECK=1 then 1 
                when r.CHECK=3 then 3 
                when r.CHECK=2 and unix_timestamp() < r.start then 4
                when r.CHECK=2 and unix_timestamp()>=r.start and unix_timestamp()<=r.end then 5
                when r.CHECK=2 and unix_timestamp() > r.end then 6
                end status 
            ')
            ->group('r.id')
            ->buildSql();
        // 状态
        if(!empty($where['status'])){
            $map2['t.status'] = array('EQ',$where['status']);
        }
        return M('card')->table($buildSql)->alias('t')->where($map2)->count();
    }

    /**
     * 商家领用列表
     * @param $where
     * @param $page
     * @param $count
     * @return mixed
     */
    public function getSellerList($where,$page,$count){
        $map['c.type'] = array('EQ',1);
        $map['r.check'] = array('NEQ',4);
        //礼券名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }

        //公司名称/id
        if(!empty($where['card_id'])){
            $map['_complex'] =array(
                "u.qc" =>array("LIKE","%".$where['card_id']."%"),
                "u.id"    =>array("LIKE","%".$where['card_id']."%"),
                "_logic"  =>"OR"
            );
        }

        //城市
        if(!empty($where['cs'])){
            $map['u.cs'] = array('EQ',$where['cs']);
        }

        //商家申请时间
        if(!empty($where['apply_time'])){
            $time = explode('~',$where['apply_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.apply_time'] = array('between',[$start,$end]);
        }


        //有效开始时间
        if(!empty($where['start'])){
            $time = explode('~',$where['start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.start'] = array('between',[$start,$end]);
        }
        //有效结束时间
        if(!empty($where['end'])){
            $time = explode('~',$where['end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.end'] = array('between',[$start,$end]);
        }

        //活动开始时间
        if(!empty($where['activity_start'])){
            $time = explode('~',$where['activity_start']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_start'] = array('between',[$start,$end]);
        }
        //活动结束时间
        if(!empty($where['activity_end'])){
            $time = explode('~',$where['activity_end']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['r.activity_end'] = array('between',[$start,$end]);
        }

        $buildSql = M('card_com_record')->alias('r')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->join('left join qz_card_user cu on cu.record_id = r.id')
            ->where($map)
            ->field('
                c.name,com.com_id,u.qc,u.cs,q.cname,r.apply_time,r.activity_start,r.activity_end,r.start,r.end,r.check,r.check_username,c.disable_time,c.enable,count(cu.id) as get_count,r.amount,r.id,
                case 
                when c.enable = 2 and unix_timestamp() >= c.disable_time then 7
                when r.CHECK=1 then 1 
                when r.CHECK=3 then 3 
                when r.CHECK=2 and unix_timestamp() < r.start then 4
                when r.CHECK=2 and unix_timestamp()>=r.start and unix_timestamp()<=r.end then 5
                when r.CHECK=2 and unix_timestamp() > r.end then 6
                end status 
            ')
            ->group('r.id')
            ->buildSql();
        // 状态
        if(!empty($where['status'])){
            $map2['t.status'] = array('EQ',$where['status']);
        }
        return M('card')->table($buildSql)->alias('t')->field('t.*')->where($map2)->order('t.apply_time desc')->limit($page,$count)->select();

    }

    /**
     * 业主领用列表
     * @param $where
     * @param $page
     * @param $count
     * @return mixed
     */
    public function getUserCount($where){
        //礼券编号
        if(!empty($where['number'])){
            $map['cu.card_number'] = array('LIKE','%'.$where['number'].'%');
        }

        //礼券名称
        if(!empty($where['name'])){
            $map['c.name'] = array('LIKE','%'.$where['name'].'%');
        }

        //城市
        if(!empty($where['cs'])){
            $map['u.cs'] = array('EQ',$where['cs']);
        }

        //手机
        if(!empty($where['phone'])){
            $map['cu.tel']  = array('LIKE','%'.$where['phone'].'%');
        }

        //礼券类型
        if(!empty($where['type'])){
            $map['c.type']  = array('EQ',$where['type']);
        }

        //领取时间
        if(!empty($where['add_time'])){
            $time = explode('~',$where['add_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['cu.add_time'] = array('between',[$start,$end]);
        }

        //使用时间
        if(!empty($where['use_time'])){
            $time = explode('~',$where['use_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['cu.use_time'] = array('between',[$start,$end]);
        }



        //公司名称/id
        if(!empty($where['card_id'])){
            $map['_complex'] =array(
                "u.qc" =>array("LIKE","%".$where['card_id']."%"),
                "u.id"    =>array("LIKE","%".$where['card_id']."%"),
                "_logic"  =>"OR"
            );
        }

        $buildSql =  M('card_user')->alias('cu')
            ->join('qz_card_com_record r on r.id= cu.record_id')
            ->join('qz_card_com com on r.card_com_id = com.id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($map)
            ->field('
                cu.card_number,c.name,cu.tel,q.cname,cu.xiaoqu,c.type,u.id,u.qc,cu.add_time,r.start,r.end,cu.is_use,cu.use_time,
                case cu.is_use
                when 2 then 2 
                when 1 and unix_timestamp() > r.end then 3
                else 1 end status             
            ')
            ->buildSql();

        //使用状态
        if(!empty($where['is_use'])){
            $map2['t.status'] = array('EQ',$where['is_use']);
        }

        return M('card_user')->table($buildSql)->alias('t')->where($map2)->count();
    }

    /**
     * 业主领用列表
     * @param $where
     * @return mixed
     */
    public function getUserList($where,$page,$count){
        //礼券编号
        if(!empty($where['number'])){
            $map['cu.card_number'] = array('LIKE','%'.$where['number'].'%');
        }

        //礼券名称
        if(!empty($where['name'])){
            $map['c.name'] = array('like','%'.$where['name'].'%');
        }

        //城市
        if(!empty($where['cs'])){
            $map['u.cs'] = array('EQ',$where['cs']);
        }

        //手机
        if(!empty($where['phone'])){
            $map['cu.tel']  = array('LIKE','%'.$where['phone'].'%');
        }

        //礼券类型
        if(!empty($where['type'])){
            $map['c.type']  = array('EQ',$where['type']);
        }

        //领取时间
        if(!empty($where['add_time'])){
            $time = explode('~',$where['add_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['cu.add_time'] = array('between',[$start,$end]);
        }

        //使用时间
        if(!empty($where['use_time'])){
            $time = explode('~',$where['use_time']);
            $start = strtotime($time[0]);
            $end = strtotime($time[1])+86399;
            $map['cu.use_time'] = array('between',[$start,$end]);
        }


        //公司名称/id
        if(!empty($where['card_id'])){
            $map['_complex'] =array(
                "u.qc" =>array("LIKE","%".$where['card_id']."%"),
                "u.id"    =>array("LIKE",'%'.$where['card_id'].'%'),
                "_logic"  =>"OR"
            );
        }

        $buildSql =  M('card_user')->alias('cu')
            ->join('qz_card_com_record r on r.id= cu.record_id')
            ->join('qz_card_com com on r.card_com_id = com.id')
            ->join('qz_card c on com.card_id = c.id')
            ->join('qz_user u on u.id = com.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->where($map)
            ->field('
            cu.card_number,c.name,cu.tel,q.cname,cu.xiaoqu,c.type,u.id,u.qc,cu.add_time,r.start,r.end,cu.is_use,cu.use_time,
            case cu.is_use
                when 2 then 2 
                when 1 and unix_timestamp() > r.end then 3
                else 1
                end status             
            ')->buildSql();

        //使用状态
        if(!empty($where['is_use'])){
            $map2['t.status'] = array('EQ',$where['is_use']);
        }

        return M('card_user')->table($buildSql)->alias('t')
            ->where($map2)
            ->order('t.add_time desc')
            ->limit($page,$count)
            ->select();

    }

    /**
     * 通用券编辑页信息
     */
    public function getCardInfo($id){
        $map['id'] = array('EQ',$id);
        $map['type'] = array('EQ',1);
        return M('card')->where($map)->field('id,name,money1,money2,rule,img,img2,enable,disable_time')->find();
    }

    /**
     * 公司是否领取过券
     * @param $cardid
     * @param $comid
     */
    public function existComCard($cardid,$comid){
        $map['card_id'] = array("EQ",$cardid);
        $map['com_id'] = array("EQ",$comid);
        return M('card_com')->where($map)->field('id')->find();
    }
}