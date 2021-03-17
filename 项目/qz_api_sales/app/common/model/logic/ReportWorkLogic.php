<?php

/**
 * 工作汇报逻辑层
 * @Author: lovenLiu
 * @Email: lypeng9205@163.com
 * @Date:   2019-05-15 09:08:16
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-13 08:32:01
 */

namespace app\common\model\logic;

use think\Db;
use think\db\Query;
use think\facade\Request;

use app\common\model\db\ReportWork;
use app\common\model\db\ReportWorkReply;

class ReportWorkLogic {

    /**
     * 获取工作汇报列表逻辑
     * @Author   liuyupeng
     * @DateTime 2019-05-15T09:58:22+0800
     */
    public function getRwPageList(array $options, $page = 1, $page_size = 20){
        $query = new Query();
        $query->where('w.enabled', 1);

        // 管理员查看所有，上级查看下级，下级查看自己
        if ($userids = AdminuserLogic::getAuthUserids()) {
            $query->where('w.uid', 'in', $userids);
        }

        // 汇报人关键字模糊查询
        if (isset($options['user_search']) && !empty($options['user_search'])) {
           $query->where('u.user|u.name', 'LIKE', '%'.$options['user_search'].'%');
        }

        // 开始日期
        if (isset($options['date_begin']) && !empty($options['date_begin'])) {
           $query->where('w.date', 'EGT', $options['date_begin']);
        }

        // 结束日期
        if (isset($options['date_end']) && !empty($options['date_end'])) {
           $query->where('w.date', 'ELT', $options['date_end']);
        }

        return $this->getPageList($query, $page, $page_size);
    }

    /**
     * 获取工作汇报详情数据
     * @Author   liuyupeng
     * @DateTime 2019-05-15T10:08:04+0800
     */
    public function getRwDetail($id){
        $user = Request::instance()->user;

        $reportWork = ReportWork::getDetail($id);
        if (empty($reportWork)) {
            return sys_response(4000001, '工作汇报不存在或已删除');
        }

        // 权限验证
        $userids = AdminuserLogic::getAuthUserids();
        if (!empty($userids) && !in_array($reportWork['uid'], $userids)) {
            return sys_response(3000001, '你没有权限查看该工作汇报内容');
        }

        // 获取汇报人
        $adminuser = Db::name('adminuser')->alias('u')
            ->join('rbac_role r', 'u.uid=r.id', 'left')
            ->field(['u.id', 'u.name', 'r.role_name', 'u.cs', 'u.css'])
            ->where('u.id', $reportWork['uid'])
            ->find();

        // 管辖城市和代管城市
        $cs = array_filter(explode(',', $adminuser['cs']));
        if (!empty($adminuser['css'])) {
            $css = explode(',', $adminuser['css']);
            $cs = array_unique(array_merge($cs, $css));
        }

        // 获取城市名称
        $citys = '';
        if (!empty($cs)) {
            $city_list = Db::name('quyu')->where('cid', 'in', $cs)->field('cid,cname')->select();
            $city_names = array_column($city_list, 'cname');
            $citys = implode(' ', $city_names);
        }

        $adminuser['citys'] = $citys;
        unset($adminuser['cs'], $adminuser['css']);

        // 如果查看人不是自己那就是超级管理员或者自己的上级，具有回复权限（自己无需回复自己）
        if ($reportWork['reply_status'] == 2 && $reportWork['uid'] != $user['user_id']) {
            $is_can_reply = 1;
        } else {
            $is_can_reply = 2;
        }

        return sys_response(0, '', [
                'detail' => $reportWork,
                'adminuser' => $adminuser,
                'is_can_reply' => $is_can_reply
            ]);
    }

    /**
     * 保存工作汇报数据
     * 同一用户同一日期只能发布一条（后者覆盖前者）
     * @Author   liuyupeng
     * @DateTime 2019-05-15T10:38:36+0800
     */
    public function saveRwData($data){
        // 查找同一用户同一日期的工作汇报
        $reportWork = ReportWork::where([
                'uid' => $data['uid'], 'date' => $data['date'], 'enabled' => 1
            ])->find();

        if (empty($reportWork)) {
            $reportWork = new ReportWork();
            $data['created_at'] = time();
        }

        $data['updated_at'] = time();
        return $reportWork->allowField(true)->save($data);
    }

    /**
     * 工作汇报回复
     * @Author   liuyupeng
     * @DateTime 2019-05-15T13:52:52+0800
     */
    public function saveRwReply($id, $reply_text){
        $user = Request::instance()->user;
        $reportWork = ReportWork::where('enabled', 1)->where('id', $id)->find();

        if (empty($reportWork)) {
            return sys_response(4000001, '工作汇报不存在或已删除');
        } else if ($reportWork['reply_status'] == 1) {
            return sys_response(1000001, '该工作汇报已回复无需再次回复');
        }

        // 权限验证
        $userids = AdminuserLogic::getAuthUserids();
        if (!empty($userids) && !in_array($reportWork['uid'], $userids)) {
            return sys_response(3000001, '你没有权限对该工作汇报进行回复');
        } else if ($reportWork['uid'] == $user['user_id']) {
            return sys_response(3000001, '不能回复自己哦');
        }

        try {
            Db::startTrans();

            // 更新回复状态
            $reportWork->reply_status = 1;
            $result_rw = $reportWork->save();

            // 插入回复内容数据
            $result_reply = ReportWorkReply::insert([
                    'rw_id' => $reportWork['id'],
                    'uid' => $user['user_id'],
                    'content' => $reply_text,
                    'created_at' => time(),
                    'updated_at' => time()
                ]);

            if (empty($result_rw) || empty($result_reply)) {
                throw new \Exception('回复失败');
            }

            Db::commit();
            $response = sys_response(0, '回复成功');
        } catch (\Exception $e) {
            Db::rollback();
            $response = sys_response(5000002, $e->getMessage());
        }

        return $response;
    }


    /**
     * 获取分页列表
     * @Author   liuyupeng
     * @DateTime 2019-05-15T10:02:34+0800
     */
    private function getPageList($map, $page, $page_size){
        $page = intval($page) <= 0 ? 1 : intval($page); 
        $page_size = intval($page_size) <= 0 ? 20 : intval($page_size); 

        $dbQuery = ReportWork::alias('w')
            ->join('adminuser u', 'u.id = w.uid')
            ->join('report_work_reply r', 'r.rw_id = w.id', 'left')
            ->where($map);

        $count = $dbQuery->field('w.id')->count(); // 获取总数
        if ($count > 0) {
            $list = $dbQuery->field([
                    'w.id', 'w.uid', 'w.date', 'w.content', 
                    'u.name as username', 'w.reply_status',
                    // 'IF (r.content IS NULL, "", r.content) reply_text'
                    'IF (w.reply_status = 1, r.content, "") reply_text'
                ])->order('w.updated_at desc')->page($page, $page_size)->select()->toArray(); // 获取分页数据
        } else {
            $list = [];
        }
        
        return ['count' => $count, 'list' => $list];
    }

    
}