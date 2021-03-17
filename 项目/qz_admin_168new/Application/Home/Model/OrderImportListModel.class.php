<?php

/***
 * 订单导入 订单详情
 */

namespace Home\Model;

use Think\Model;

class OrderImportListModel extends Model
{
    protected $autoCheckFields = false;


    public $lf_time = [
        "随时"    => "随时",
        "今天"    => "今天",
        "1周内"   => "1周内",
        "2周内"   => "2周内",
        "1个月内"  => "1个月内",
        "2个月内"  => "2个月内",
        "3个月内"  => "3个月内",
        "3个月以上" => "3个月以上",
        "周末"    => "周末",
        "拿房后"   => "拿房后",
        "电话预约后" => "电话预约后",
        "看户型图后" => "看户型图后",
        "去实体店后" => "去实体店后"
    ];

    public $start_time = [
        "1个月内开工"  => "1个月内开工",
        "2个月内开工"  => "2个月内开工",
        "3个月内开工"  => "3个月内开工",
        "4个月内开工"  => "4个月内开工",
        "5个月内开工"  => "5个月内开工",
        "6个月内开工"  => "6个月内开工",
        "6个月以上开工" => "6个月以上开工",
        "方案满意开工"  => "方案满意开工",
        "满意拿房后开工" => "满意拿房后开工",
        "面议"      => "面议"
    ];

    public $keys = [
        "1" => "有",
        "0" => "无",
        "3" => "其他"
    ];

    public $lx = [
        "1" => "家装",
        "2" => "公装",
    ];

    public $lxs = [
        "1" => "新房装修",
        "2" => "旧房装修",
        "3" => "旧房改造"
    ];

    //状态
    public $task_status = [
        0 => '请选择',
        1 => '成功',
        2 => '失败',
        3 => '重复'
    ];


    /**
     * @param mixed|string $data
     * @param array $taskId
     * @return bool|mixed|string
     */
    public function addList($excelData, $taskId)
    {
        /* -- 预处理数据 --*/
        unset($excelData[0]); //去掉标题字段


        //增加扩展数据
        $extDataOne = [];
        $extDataOne['task_id'] = $taskId; // 增加 taskId标识
        $extDataOne['ip'] = '223.112.69.58'; // ip用公司的ip
        $data = self::excel2Data($excelData, $extDataOne);
        //如果处理过的数据和上传数量不同 , 则修改
        $task = M('order_import_task')->where(['id' => ['eq', $taskId]])->find();
        if ($task && $task['import_count'] != count($data)) {
            //如果没有可导入数据 , 就删除原有的任务
            if(count($data) == 0){
                M('order_import_task')->where(['id' => ['eq', $taskId]])->delete();
            }else{
                M('order_import_task')->where(['id' => ['eq', $taskId]])->save(['import_count' => count($data)]);
            }
        }
        return M('order_import_list')->addAll($data);
    }

    /**
     *
     * 把excel数据映射成数据库入库数据
     *
     * @param $excelData excel数据
     * @param $extDataOne 字段扩展数据
     * @return array 数据库入库数据
     */
    public function excel2Data($excelData, $extDataOne)
    {
        $redata = [];

        foreach ($excelData as $k => $v) {
            //*联系电话	     备用电话	联系人	性别	城市	区域	    小区名称	家装公装	   新房旧房
            //13812345678	15945457845	张三	    女士	苏州	吴江区	实验小区	家装	       新房装修

            //用途	 户型	    室	面积	喜欢风格	预算装修类型	预算	    拿房时间	   钥匙	开工时间	     量房时间	装修需求
            //结婚用  6室2厅3卫	7	201	美式风格	全包	        4万以下	2018-06-20	有	6个月以上开工	 随时	    要用名牌的主材


            $redataOne = [];

            $redataOne['tel'] = trim($v[0]); // 联系电话
            $redataOne['other_contact'] = trim($v[1]); // 备用电话
            $redataOne['name'] = trim($v[2]); // 联系人
            $redataOne['sex'] = trim($v[3]); // 性别
            if(!$v[4]){
                continue;
            }
            $redataOne['cs'] = trim($v[4]); // 城市 human
            $redataOne['qx'] = trim($v[5]); // 区域 human
            $redataOne['xiaoqu'] = trim($v[6]); // 小区名称
            $redataOne['lx'] = trim($v[7]); // 家装公装 human
            $redataOne['lxs'] = trim($v[8]); // 新房旧房 human
            $redataOne['yt'] = trim($v[9]); // 用途
            $redataOne['huxing'] = trim($v[10]); // 户型 human
            $redataOne['shi'] = trim($v[11]); // 室
            $redataOne['mianji'] = trim($v[12]); // 面积
            $redataOne['fengge'] = trim($v[13]); // 喜欢风格 human
            $redataOne['fangshi'] = trim($v[14]); // 预算装修类型 human
            $redataOne['yusuan'] = trim($v[15]); // 预算 human
            $redataOne['nf_time'] = trim($v[16]); // 拿房时间
            //处理时间为Excel中填入 2018/05/23 导入后变成了 05-23-18这种时间问题
            $nf_timeArr = preg_split('/[\\\-]+/is', $redataOne['nf_time']);
            if (strlen($nf_timeArr[0]) <= 2) {
                //如果年为小于等于2个字符那么需要处理下顺序
                $redataOne['nf_time'] = $nf_timeArr[2] . '-' . $nf_timeArr[0] . '-' . $nf_timeArr[1];
            }

            $redataOne['keys'] = trim($v[17]); // 钥匙 human
            $redataOne['start'] = trim($v[18]); // 开工时间 human
            $redataOne['lftime'] = trim($v[19]); // 量房时间 human
            $redataOne['text'] = trim($v[20]); // 装修需求
            $redataOne['source_in'] = trim($v[21]); // 数据类型(来源入口)
            //扩展字段
            $redataOne = array_merge_recursive($redataOne, $extDataOne);

            $redataOne = self::humanData2Data($redataOne);

            $redata[] = $redataOne;
        }

        return $redata;
    }

    /**
     * 转换订单信息中表单数据中中文数据为数据库表示数据
     * @param $humanData
     * @return array
     */
    public function humanData2Data($humanData)
    {
        $data = $humanData;

        $huxing = D("Huxing")->gethx(); //户型
        $yusuan = D("Jiage")->getJiage(); //预算
        $fangshi = D("Fangshi")->getfs(); //装修方式
        $fengge = D("Fengge")->getfg(); //风格


        $cityInfo = D("Quyu")->getCityInfoByCityName($data['cs']);
        if (!empty($cityInfo)) {
            $data['cs'] = $cityInfo['cs']['cid'];
            $data['sf'] = $cityInfo['cs']['uid'];
            $haveQx = false;
            foreach ($cityInfo['qx'] as $k => $v) {
                if ($v['qz_area'] == $data['qx']) {
                    $data['qx'] = $v['qz_areaid'];
                    $haveQx = true;
                    break;
                }
            }
            unset($v);
            if (false == $haveQx) {
                $data['qx'] = end($cityInfo['qx'])['qz_areaid'];
            }
        } else {
            $data['cs'] = '000001';
            $data['qx'] = '000001';
            $data['sf'] = '';
        }


        $data['lx'] = array_flip($this->lx)[$data['lx']]; // 家装公装 human
        $data['lxs'] = array_flip($this->lxs)[$data['lxs']]; // 新房旧房 human

        // 户型 human
        $haveHuxing = false;
        foreach ($huxing as $k => $v) {
            if ($v['name'] == $data['huxing']) {
                $data['huxing'] = $v['id'];
                $haveHuxing = true;
                break;
            }
        }
        unset($v);
        if (false == $haveHuxing) $data['huxing'] = '';

        // 喜欢风格 human
        $haveFengge = false;
        foreach ($fengge as $k => $v) {
            if ($v['name'] == $data['fengge']) {
                $data['fengge'] = $v['id'];
                $haveFengge = true;
                break;
            }
        }
        unset($v);
        if (false == $haveFengge) $data['fengge'] = '';

        // 预算装修类型 human
        $haveFangshi = false;
        foreach ($fangshi as $k => $v) {
            if ($v['name'] == $data['fangshi']) {
                $data['fangshi'] = $v['id'];
                $haveFangshi = true;
                break;
            }
        }
        unset($v);
        if (false == $haveFangshi) $data['fangshi'] = '';

        // 预算 human
        $haveYusuan = false;
        foreach ($yusuan as $k => $v) {
            if ($v['name'] == $data['yusuan']) {
                $data['yusuan'] = $v['id'];
                $haveYusuan = true;
                break;
            }
        }
        unset($v);
        if (false == $haveYusuan) $data['yusuan'] = '';

        $data['keys'] = array_flip($this->keys)[$data['keys']]; // 钥匙 human
        $data['start'] = array_flip($this->start_time)[$data['start']]; // 开工时间 human
        $data['lftime'] = array_flip($this->lf_time)[$data['lftime']]; // 量房时间 human

        return $data;
    }

    /*----分页和搜索----*/
    /**
     * 某个任务导入的订单详情 分页计算数量
     * @param $param
     * @return mixed
     */
    public function getOrderImportListCount($param)
    {
        $db = self::getOrderImportListObj($param);
        return $db->count();
    }

    /**
     * 某个任务导入的订单详情 分页数据
     * @param $param
     * @return mixed
     */
    public function getOrderImportList($param)
    {
        $db = self::getOrderImportListObj($param);
        return $db->alias('a')
            ->join('LEFT JOIN qz_quyu as qy ON qy.cid = a.cs')
            ->join('LEFT JOIN qz_area as ar ON ar.qz_areaid = a.qx')
            ->field('a.*,qy.cname as cs_h,ar.qz_area as qx_h')
            ->limit($param['limit']['start'], $param['limit']['end'])
            ->order('id DESC')->select();
    }

    /**
     * 某个任务导入的订单详情 带参数的翻页对象
     * @param $param
     * @return Model
     */
    public function getOrderImportListObj($param)
    {
        $admin = getAdminUser();

        $map = [];

        if (!empty($param['task_id'])) {
            $map['task_id'] = ['EQ', $param['task_id']];
        }

        if (!empty($param['name'])) {
            $map['name'] = ['LIKE', $param['name'] . '%'];
        }

        if (!empty($param['tel'])) {
            $map['_complex'] = [
                'tel'           => ['LIKE', $param['tel'] . '%'],
                'other_contact' => ['LIKE', $param['tel'] . '%'],
                '_logic'        => 'OR'
            ];
        }

        if (!empty($param['task_status'])) {
            $map['task_status'] = ['EQ', $param['task_status']];
        }


        /*---权限控制---*/
        //产品经理，运营总监可以看到所有导入的记录
        //推广一部主管仅能看到其下属操作的所有导入记录
        //推广二部主管仅能看到其下属操作的所有导入记录
        //其余人员仅能看到自己操作的所有导入记录


        //看所有
        $roleLookAll = false;
        if (in_array($admin['uid'], [1, 51, 68])) $roleLookAll = true; //51产品经理 68运营总监
        if ( false == $roleLookAll) {
           $result = D('OrderImportTask')->getTaskBytaskId($param['task_id']);
           if ($admin['id'] != $result['op_id']) {
               //如果本任务不是自己的就不能看
               return false;
           }
        }

        //看自己管辖 目前不支持
        /*$roleLookPrecinct  = false;
        if (in_array($admin['uid'], [70,75]) ) $roleLookPrecinct = true; //70流量部门主管（推广二部） 75推广主管（推广一部）
        if ($roleLookPrecinct) {
            $precinctListId = '';
            $map['op_id'] = ['IN', $precinctListId];
        }*/

        $db = M('order_import_list');
        return $db->where($map);
    }


}
