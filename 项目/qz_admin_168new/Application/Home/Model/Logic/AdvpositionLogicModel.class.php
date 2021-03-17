<?php

namespace Home\Model\Logic;

use Home\Model\Db\AdvertModel;
use Home\Model\Db\AdvertPositionModel;
use Home\Model\Db\AdvertPositionOptionModel;

class AdvpositionLogicModel {

    public $position_type = array(
        "1" => "单图",
        "2" => "多图",
        "3" => "轮播",
        "4" => "单图文",
        "5" => "多图文",
        "6" => "JS广告",
        "7" => "双排JS广告"
    );

    /**
     * 广告位图片上传数
     * @var array
     */
    public $position_img_num = array(
        "1" => [
            'name' => '单图',
            'img_num' => 1,
        ],
        "2" => [
            'name' => '多图',
            'img_num' => 3,
        ],
        "3" => [
            'name' => '轮播',
            'img_num' => 1,
        ],
        "4" => [
            'name' => '单图文',
            'img_num' => 1,
        ],
        "5" => [
            'name' => '多图文',
            'img_num' => 3,
        ]
    );

    public $adv_status = [
        '1' => '有效(生效中)',
        '2' => '过期',
        '3' => '有效(未生效)',
        '0' => '未知'
    ];

    /**
     * 验证广告位置名称唯一性
     * @return [type] [description]
     */
    public function validatePositionOptionByName($name, $id = 0, $parentid = 0){
        $advModel = new AdvertPositionOptionModel();
        $result = $advModel->validateByName($name, $id, $parentid);
        return $result ? false : true;
    }

    /**
     * 验证广告位置标识唯一性
     * @param  [type]  $name [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function validatePositionOptionByCode($code, $id = 0){
        $advModel = new AdvertPositionOptionModel();
        $result = $advModel->validateByCode($code, $id);
        return $result ? false : true;
    }

    /**
     * 获取位置信息下级数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getPositionOptionChildrenNum($id){
        $advModel = new AdvertPositionOptionModel();
        return $advModel->getChildrenNum($id);
    }

    /**
     * 获取广告位置下广告位数量
     * @param  [type] $position_id [description]
     * @return [type]              [description]
     */
    public function getPositionNumByPositionId($position_id){
        $advModel = new AdvertPositionModel();
        return $advModel->getNumByPositionId($position_id);
    }

    /**
     * 获取广告位置详情
     * @return [type] [description]
     */
    public function getPositionOptionDetail($id){
        return M("advert_position_option")->where(["id" => $id])->find();
    }

    /**
     * 获取广告位置树形列表
     * @return [type] [description]
     */
    public function getPositionOptionTree(){
        $advModel = new AdvertPositionOptionModel();
        $fields = ["id", "level", "name", "code", "parentid", "sort"];
        $list = $advModel->order("sort asc,id desc")->field($fields)->select();
        $list = array_column($list, null, "id");

        $platform_list = $module_list = $page_list = $position_list = [];
        foreach ($list as $key => $item) {
            switch ($item["level"]) {
                case 1:
                    $platform_list[$key] = $item;
                    break;
                case 2:
                    $module_list[$key] = $item;
                    break;
                case 3:
                    $page_list[$key] = $item;
                    break;
                case 4:
                    $position_list[$key] = $item;
                    break;
            }
        }

        // 把位置加入页面列表
        foreach ($position_list as $key => $position) {
            $parentid = $position["parentid"];
            $page_list[$parentid]["children"][] = $position;
        }

        // 把页面加入模块列表
        foreach ($page_list as $key => $page) {
            $parentid = $page["parentid"];
            $module_list[$parentid]["children"][] = $page;
        }

        // 把模块加入平台列表
        foreach ($module_list as $key => $module) {
            $parentid = $module["parentid"];
            $platform_list[$parentid]["children"][] = $module;
        }

        return array_values($platform_list);
    }

    /**
     * 广告位置数据保存
     * @param  [type] $id   [description]
     * @param  [type] $name [description]
     * @param  [type] $code [description]
     * @param  [type] $sort [description]
     * @return [type]       [description]
     */
    public function positionOptionSave($id, $name, $code, $sort, $level = 1, $parentid = 0){
        $data = array(
            "name" => $name,
            "code" => $code,
            "sort" => $sort,
            "level" => $level,
            "parentid" => $parentid,
            "updated_at" => time()
        );

        if (empty($id)) {
            $data["created_at"] = time();
            $result = M("advert_position_option")->add($data);
        } else {
            $data["id"] = $id;
            $result = M("advert_position_option")->save($data);
        }

        return $result;
    }

    /**
     * 删除广告位置信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function positionOptionDelete($id){
        return M("advert_position_option")->where(["id" => $id])->delete();
    }

    /**
     * 根据广告位置父ID获取所属子级
     * @return [type] [description]
     */
    public function getPositonOptionByParentId($id){
        return M("advert_position_option")->where(["parentid" => $id])->field("id,name,level")->order("sort asc")->select();
    }

    /**
     * 验证广告位名称唯一性
     * @param  [type]  $name [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function validatePositionName($name, $id = 0){
        $advModel = new AdvertPositionModel();
        $result = $advModel->validateByName($name, $id);
        return $result ? false : true;
    }

    /**
     * 验证广告位位置唯一性
     * @param  [type]  $position_id [description]
     * @param  integer $id          [description]
     * @return [type]               [description]
     */
    public function validatePositionPositionId($position_id, $id = 0){
        $advModel = new AdvertPositionModel();
        $result = $advModel->validateByPositionId($position_id, $id);
        return $result ? false : true;
    }

    /**
     * 获取广告位列表
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function getPositionList($param){
        $advModel = new AdvertPositionModel();
        $list = $advModel->getList($param);

        foreach ($list as $key => $item) {
            $list[$key]["type_name"] = $this->position_type[$item["type"]];
            $list[$key]["enabled_name"] = $item["enabled"] == 1 ? "启用" : "停用";
        }

        return $list;
    }

    /**
     * 获取广告位详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getPositionDetail($id){
        $advModel = new AdvertPositionModel();
        $detail = $advModel->getDetail($id);

        if (!empty($detail)) {
            $detail["type_name"] = $this->position_type[$detail["type"]];
            $detail["enabled_name"] = $detail["enabled"] == 1 ? "启用" : "停用";
            $detail["img_num"] = $this->position_img_num[$detail["type"]]['img_num'];
            $detail["id"] = sprintf('%06s',$detail["id"]);
        }

        return $detail;
    }

    /**
     * 验证广告位是否可以删除
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function validatePositionDelete($id){
        $count = M("advert")->where(["location_id" => $id])->count("id");
        return $count > 0 ? false : true;
    }

    /**
     * 获取广告位下有效广告数
     * @param  [type] $location_id [description]
     * @return [type]              [description]
     */
    public function getAdvertActiveCountByLocationId($location_id){
        $nowtime = time();
        $map = array(
            "location_id" => array("EQ", $location_id),
            "end_time" => array("EGT", $nowtime)
        );
        return M("advert")->where($map)->count("id");
    }

    public function getAdvert($param)
    {
        $advDb = new AdvErtModel();
        $map = [
            'p.enabled' => ['eq', 1],
        ];
        if (!empty($param['condition'])) {
            $map[]["_complex"] = array(
                "p.id" => array("EQ", $param['condition']),
                "a.name" => array("LIKE", "%" . $param['condition'] . "%"),
                "_logic" => "OR"
            );
        }
        // 平台
        if (!empty($param["platform_id"])) {
            $map["plat.id"] = array("EQ", $param["platform_id"]);
        }

        // 模块
        if (!empty($param["module_id"])) {
            $map["module.id"] = array("EQ", $param["module_id"]);
        }

        // 页面
        if (!empty($param["page_id"])) {
            $map["page.id"] = array("EQ", $param["page_id"]);
        }

        //状态
        if (!empty($param["status"])) {
            $map["a.status"] = array("EQ", $param["status"]);
        }

        //时间查询
        if (!empty($param["start"])) {
            $map["a.start_time"] = ['egt', strtotime($param["start"] . ' 00:00:00')];
        }
        if (!empty($param["end"])) {
            $map["a.end_time"] = ['elt', strtotime($param["end"] . ' 23:59:59')];
        }

        if (empty($param["start"]) && empty($param["end"])) {
            $start = strtotime(date('Y-m-d', strtotime('-6 month')) . ' 00:00:00');
            $end = strtotime(date('Y-m-d', strtotime('+6 month')) . ' 23:59:59');
            $map["a.start_time"] = ['elt', $end];
            $map["a.end_time"] = ['egt', $start];
        }
        $list = [];
        $show = '';
        $count = $advDb->getAdvertCount($map);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = $advDb->getAdvertList($map, 'a.*', $p->firstRow, $p->listRows);
            //处理广告数据
            foreach ($list as $k => $v) {
                //投放天数
                $list[$k]['all_day'] = ceil(($v['end_time'] - $v['start_time']) / (60 * 60 * 24)) . '天';
                //剩余天数
                if($v['status'] == 1){
                    $list[$k]['surplus_day'] = ceil(($v['end_time'] - time()) / (60 * 60 * 24)) . '天';
                }elseif($v['status'] == 3){
                    $list[$k]['surplus_day'] = ceil(($v['end_time'] - $v['start_time']) / (60 * 60 * 24)) . '天';
                }
                else{
                    $list[$k]['surplus_day'] = 0 . '天';
                }
                //广告状态
                $list[$k]['status_name'] = $this->adv_status[$v['status']];
            }
        }
        return ['page' => $show, 'list' => $list];
    }

    /**
     * 广告删除
     * @param $id
     * @return mixed
     */
    public function delAdvert($id){
        M()->startTrans(); // 开启事务
        try {

            $advDb = new AdvertModel();
            $advDb->delAdvImg(['advert_id' => ['eq', $id]]);    //删除图片
            $advDb->delAdvCity(['advert_id' => ['eq', $id]]);    //删除城市
            $advDb->saveAdvJs(['advert_id' => ['eq', $id]]);    //删除JS
            M("advert")->where(["id" => $id])->delete();

            M()->commit(); // 事务提交

            return true;

        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            return false;
        }
    }
    /**
     * 广告位删除
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function positionDelete($id){
        return M("advert_position")->where(["id" => $id])->delete();
    }

    /**
     * 广告位编辑
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function positionSave($id, $data){
        $row = array(
            "name" => $data["name"],
            "type" => $data["type"],
            "demand" => $data["demand"],
            "preview" => $data["preview"],
            "enabled" => $data["enabled"],
            "position_id" => $data["position_id"],
            "updated_at" => time()
        );

        if (!empty($id)) {
            $row["id"] = $id;
            $row["created_at"] = time();
            $ret = M("advert_position")->save($row);
            $result = $ret ? $id : false;
        } else {
            $ret = M("advert_position")->add($row);
            $result = $ret ? M("advert_position")->getLastInsID() : false;
        }

        return $result;
    }

    public function getValidAdvertPosition()
    {
        $positionDb = new AdvertPositionModel();
        $where = [
            'p.enabled' => ['eq', 1]
        ];
        return $positionDb->getValidPositionList($where,'p.id,p.name');
    }

    /**
     * 添加广告
     * @param $data
     * @return array
     */
    public function saveAdv($data)
    {
        $advDb = new AdvertModel();
        $log = new LogAdminLogicModel();
        $adv_id = $data['edit_id'];//广告id
        $save = [
            'location_id' => $data['advPosition'],
            'name' => $data['advName'],
            'start_time' => strtotime($data['advTimeBegin'] . ' 00:00:00'),
            'end_time' => strtotime($data['advTimeEnd'] . ' 23:59:59'),
            'url' => $data['advLink'],
            'px' => (int)$data['advSort'],
            'op_uid' => session('uc_userinfo.id'),
            'updated_at' => time(),
        ];
        //通过设置的投放时间 , 算出当前广告的状态
        if (!empty($data['advTimeBegin']) && !empty($data['advTimeEnd'])) {
            //过期
            if (strtotime($data['advTimeBegin'] . ' 00:00:00') < time() && strtotime($data['advTimeEnd'] . ' 23:59:59') < time()) {
                $save['status'] = 2;
            }
            //进行中
            if (strtotime($data['advTimeBegin'] . ' 00:00:00') < time() && strtotime($data['advTimeEnd'] . ' 23:59:59') > time()) {
                $save['status'] = 1;
            }
            //未开始
            if (strtotime($data['advTimeBegin'] . ' 00:00:00') > time() && strtotime($data['advTimeEnd'] . ' 23:59:59') > time()) {
                $save['status'] = 3;
            }
        }


        //获取广告详情验证
        $advInfo = $this->getAdvertDetail(['a.id' => ['eq', $data['edit_id']]], $data['edit_id'], 'a.*');
        $add_city = $data['citys'];
        if(!empty($advInfo)){
            //获取新增的城市
            $add_city = array_diff($data['citys'],$advInfo['quyu_cid']);
        }
        //新增操作全是新增
        //如果位置和时间为做修改 /没有新增城市 修改的城市则不需要验证
        if (empty($data['edit_id']) || $advInfo['location_id'] != $data['advPosition'] || $advInfo['start_time'] != strtotime($data['advTimeBegin'] . ' 00:00:00') || $advInfo['end_time'] != strtotime($data['advTimeEnd'] . ' 23:59:59') || count($add_city) > 0) {
            //验证位置信息
            $info = self::checkAdvertPosition($data, $add_city);
            if ($info['status'] == 0) {
                return $info;
            }
        }

        if (!empty($data['edit_id'])) {
            //编辑
            $advDb->updateAdv(['id' => ['eq', $data['edit_id']]], $save);

        } else {
            $save['created_at'] = time();
            //添加
            $adv_id = $advDb->addAdv($save);
        }
        //添加日志
        $log->addLog('编辑广告信息', 'advert', $data, $adv_id);
        //添加广告图片
        $this->saveAdvImg($data, $adv_id);
        // 添加js广告的JS到数据库
        $this->saveAdvJs($data,$adv_id);
        //添加广告设计城市
        $this->saveAdvCity($data, $adv_id);
        return ['status' => 1];
    }

    /**
     * 验证当前广告位是否可以使用
     * @param $location
     * @param array $new_city 编辑时新增的城市
     * @return array
     */
    static public function checkAdvertPosition($location,$new_city = [])
    {
        $position = new AdvertPositionModel();
        if (empty($location['advPosition'])) {
            return ['status' => 0, 'error_msg' => '未查询到广告位信息!'];
        }
        //不允许添加过期的广告
        if ((strtotime($location['advTimeBegin'] . ' 00:00:00') < strtotime(date('Y-m-d') . ' 00:00:00')) || (strtotime($location['advTimeEnd'] . ' 23:59:59') < time())) {
            return ['status' => 0, 'error_msg' => '不可以添加过期广告!'];
        }

        $where = [
            'p.id' => ['eq', $location['advPosition']],
            'a.status' => ['in', [1, 3]], //查询所有有效的
        ];
        if (!empty($location['edit_id'])) {
            $where['a.id'] = ['neq', $location['edit_id']]; //不查询自己
        }
        //当前广告位所有有效广告(不包含本身)
        $location_data = $position->checkAdvPosition($where, 'p.type,p.id location_id,a.id,a.status,a.start_time,a.end_time,cs.cs');
        if (count($location_data) > 0) {
            foreach ($location_data as $k=>$v){
                $location_data[$v['id']]['id'] = $v['id'];
                $location_data[$v['id']]['type'] = $v['type'];
                $location_data[$v['id']]['location_id'] = $v['location_id'];
                $location_data[$v['id']]['status'] = $v['status'];
                $location_data[$v['id']]['start_time'] = $v['start_time'];
                $location_data[$v['id']]['end_time'] = $v['end_time'];
                $location_data[$v['id']]['quyu_cid'][] = $v['cs'];
                unset($location_data[$k]);
            }
            foreach ($location_data as $k => $v) {
                //广告位是轮播图 就可以多条广告
                if ($v['type'] != 3) {
                    $status = is_time_cross($v['start_time'], $v['end_time'], strtotime($location['advTimeBegin'] . ' 00:00:00'), strtotime($location['advTimeEnd'] . ' 23:59:59'));
                    //验证每一个有效广告中的城市 是否包含当前新增的城市
                    $is_exist = array_intersect($new_city,$v["quyu_cid"]);
                    if ($status && count($is_exist) > 0) {
                        return ['status' => 0, 'error_msg' => '投放时间/城市与其他广告重叠'];
                    }
                }
            }
        }
        return ['status' => 1];
    }

    /**
     * 添加广告图片
     * @param $data
     * @param $adv_id
     */
    public function saveAdvImg($data, $adv_id)
    {
        if (count($data['imgs']) > 0) {
            $img_save = [];
            //添加城市
            foreach ($data['imgs'] as $k => $v) {
                $img_save[] = [
                    'advert_id' => $adv_id,
                    'title' => $data['advTitle']?:'',
                    'url' => $v,
                ];
            }
            $advDb = new AdvertModel();
            $advDb->delAdvImg(['advert_id' => ['eq', $adv_id]]);
            $advDb->saveAdvImg($img_save);
        }
    }

    /**
     * 添加JS广告的JS引用代码
     * @param $data
     * @param $adv_id
     */
    public function saveAdvJs($data, $adv_id)
    {
        $js_save = [];
        if ($data['adv_js']) {   // 单JS广告
            $js_save[] = [
                'advert_id' => $adv_id,
                'js' => $data['adv_js'],
                'type' => 0,        // 单JS广告
            ];
        } elseif ($data['adv_jsleft'] && $data['adv_jsright']) {      // 双排JS广告
            $js_save[] = [
                'advert_id' => $adv_id,
                'js' => $data['adv_jsleft'],
                'type' => 1,        // 左边JS
            ];

            $js_save[] = [
                'advert_id' => $adv_id,
                'js' => $data['adv_jsright'],
                'type' => 2,        // 右边JS
            ];

        }

        if (count($js_save) > 0) {
            $advDb = new AdvertModel();
            $advDb->delAdvJs(['advert_id' => ['eq', $adv_id]]);
            $advDb->saveAdvJs($js_save);
        }

    }

    /**
     * 添加广告关联城市
     * @param array $data
     * @param $adv_id
     */
    public function saveAdvCity($data = [], $adv_id)
    {
        if (count($data['citys']) > 0) {
            $city_save = [];
            //添加城市
            foreach ($data['citys'] as $k => $v) {
                $city_save[] = [
                    'cs' => $v,
                    'advert_id' => $adv_id
                ];
            }
            $advDb = new AdvertModel();
            $advDb->delAdvCity(['advert_id' => ['eq', $adv_id]]);
            $advDb->saveAdvCity($city_save);
        }
    }

    public function getAdvInfo($id)
    {
        $advDb = new AdvertModel();
        $field = 'a.*,p.name location_name,p.type,p.preview,p.demand,
        pos.`name` as position_name,plat.`name` as platform_name,module.`name` as module_name,page.`name` as page_name';
        $where = [
            'a.id' => ['eq', $id]
        ];
        $info = $this->getAdvertDetail($where, $id ,$field);
        $img = $advDb->getImgByAdvId($id);//广告图片
        $advJs = $advDb->getJsByAdvId($id);//广告JS

        $info['type_name'] = $this->position_type[$info['type']];
        $info['start_time'] = date('Y-m-d', $info['start_time']);
        $info['end_time'] = date('Y-m-d', $info['end_time']);
        $info['img_num'] = $this->position_img_num[$info['type']]['img_num'];
        $info['location_id'] = sprintf('%06s', $info['location_id']);
        if (count($img) > 0) {
            $imgs = $qr_preconfig = [];
            $code = time();
            foreach ($img as $v) {
                $imgs[] = '<img data-id=' . $code . ' class="file-preview-image" src= "http://' . C('QINIU_DOMAIN') . '/' . $v['url'] . '" >';
                $info['title'] = $v['title'];
                $info['img_url'][] = 'http://' . C('QINIU_DOMAIN') . '/' . $v['url'];
                $info['img_hidden_url'] .= $v['url'] . ',';
                $qr_preconfig[] = ['url' => '/advposition/del_delimg', 'extra' => ['id' => $code]];
            }
            $info['imgs'] = json_encode($imgs);
            $info['previewconfig'] = json_encode($qr_preconfig);
        }

        if (count($advJs) > 0) {
            foreach ($advJs as $key => $val) {
                switch (intval($val['type'])){
                    case 1:
                        $info['adv_jsleft'] = htmlspecialchars_decode($val['js']);
                        break;
                    case 2:
                        $info['adv_jsright'] = htmlspecialchars_decode($val['js']);
                        break;
                    default :
                        $info['adv_js'] = htmlspecialchars_decode($val['js']);
                        break;
                }
            }

        }
        return $info;
    }

    public function getAdvertDetail($where, $id,$field = 'a.*'){
        $advDb = new AdvertModel();
        $info = $advDb->getAdvertDetail($where, $field);
        $city = $advDb->getCityByAdvId($id,'a.id,q.cid,q.cname');//广告城市
        foreach ($city as $k=>$v){
            $info['quyu_cid'][] = $v['cid'];
            $info['quyu_name'][] = $v['cname'];
            unset($city[$k]);
        }
        return $info;
    }
}
