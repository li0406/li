<?php
// +----------------------------------------------------------------------
// | CompanyContactViewsLogicModel   装修公司联系方式查看逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;


class CompanyContactViewsLogicModel
{
    /**
     * 添加装修公司联系方式访问黑名单
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function saveBlack($data, $map)
    {
        $model = new \Home\Model\Db\CompanyContactViewsBlackModel();
        $find = $model->getOne($map);
        $where = [];
        if ($find) {
            $where['id'] = ['EQ',$find['id']];
        }
        if (!empty($data['tel']) && !isset($data['tel_encrypt'])) {
            import('Library.Org.Util.App');
            $app = new \App();
            $data['tel_encrypt'] = $app->order_tel_encrypt($data['tel']);
        }

        if (empty($data['ip'])) {
            unset($data['ip']);
        }
        if (empty($data['tel'])) {
            unset($data['tel']);
        }
        $data['create_time'] = time();
        $data['admin_id'] = session('uc_userinfo.id');
        $data['admin_name'] = session('uc_userinfo.name');
        return $model->saveBlack($data, $where);
    }

    /**
     * 删除装修公司联系方式访问黑名单
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function removeBlack($id)
    {
        $model = new \Home\Model\Db\CompanyContactViewsBlackModel();
        $where['id'] = ['EQ', $id];
        return $model->removeBlack($where);
    }
    /**
     * 装修公司联系方式访问黑名单无分页
     *
     * @param string $tel 手机号码
     * @param string $start 开始时间
     * @param string $end 结束时间
     * @return mixed
     */
    public function getBlackListNoPage($tel, $start, $end)
    {
        $map = [];
        if (!empty($tel)) {
            $map['a.tel'] = ['EQ', $tel];
        }
        if (empty($start) && empty($end)) {
            $map['a.create_time'] = ['BETWEEN', [strtotime(date('Y-m-01')), time()]];
        } else {
            if (!empty($start) && !empty($end)) {
                $map['a.create_time'] = ['BETWEEN', [strtotime($start), strtotime($end.' 23:59:59')]];
            } elseif (!empty($end)) {
                $map['a.create_time'] = ['ELT', strtotime($end.' 23:59:59')];
            } else {
                $map['a.create_time'] = ['EGT', strtotime($start)];
            }
        }

        $model = new \Home\Model\Db\CompanyContactViewsBlackModel();
        return $model->getListNoPage($map);
    }

    /**
     * 装修公司联系方式访问黑名单
     *
     * @param string $tel 手机号码
     * @param string $start 开始时间
     * @param string $end 结束时间
     * @return mixed
     */
    public function getBlackList($tel, $start, $end)
    {
        $map = [];
        if (!empty($tel)) {
            $map['a.tel'] = ['EQ', $tel];
        }
        if (empty($start) && empty($end)) {
            $map['a.create_time'] = ['BETWEEN', [strtotime(date('Y-m-01')), time()]];
        } else {
            if (!empty($start) && !empty($end)) {
                $map['a.create_time'] = ['BETWEEN', [strtotime($start), strtotime($end.' 23:59:59')]];
            } elseif (!empty($end)) {
                $map['a.create_time'] = ['ELT', strtotime($end.' 23:59:59')];
            } else {
                $map['a.create_time'] = ['EGT', strtotime($start)];
            }
        }

        $model = new \Home\Model\Db\CompanyContactViewsBlackModel();
        $count = $model->getCount($map);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, 15);
            $list = $model->getList($map, $page->nowPage, $page->listRows);
        }
        $return['list'] = isset($list) ? $list : [];
        $return['page'] = isset($page) ? $page->show() : '';
        return $return;
    }

    /**
     * 装修公司手机号码访问列表无分页
     *
     * @param string $tel 手机号码
     * @param string $start 开始时间
     * @param string $end 结束时间
     * @return mixed
     */
    public function getLogListNoPage($tel, $start, $end)
    {
        $map = ['b.id' => [['EXP','IS NULL'],['EQ',''],'OR']];
        if (!empty($tel)) {
            $map['a.tel'] = ['EQ', $tel];
        }
        if (empty($start) && empty($end)) {
            $map['a.create_time'] = ['BETWEEN', [strtotime(date('Y-m-01')), time()]];
        } else {
            if (!empty($start) && !empty($end)) {
                $map['a.create_time'] = ['BETWEEN', [strtotime($start), strtotime($end.' 23:59:59')]];
            } elseif (!empty($end)) {
                $map['a.create_time'] = ['ELT', strtotime($end.' 23:59:59')];
            } else {
                $map['a.create_time'] = ['EGT', strtotime($start)];
            }
        }
        $model = new \Home\Model\Db\LogCompanyContactViewsModel();
        return $model->getListNoPage($map);
    }

    /**
     * 装修公司手机号码访问列表
     *
     * @param string $tel 手机号码
     * @param string $start 开始时间
     * @param string $end 结束时间
     * @return mixed
     */
    public function getLogList($tel, $start, $end)
    {
        $map = ['b.id' => [['EXP','IS NULL'],['EQ',''],'OR']];
        if (!empty($tel)) {
            $map['a.tel'] = ['EQ', $tel];
        }
        if (empty($start) && empty($end)) {
            $map['a.create_time'] = ['BETWEEN', [strtotime(date('Y-m-01')), time()]];
        } else {
            if (!empty($start) && !empty($end)) {
                $map['a.create_time'] = ['BETWEEN', [strtotime($start), strtotime($end.' 23:59:59')]];
            } elseif (!empty($end)) {
                $map['a.create_time'] = ['ELT', strtotime($end.' 23:59:59')];
            } else {
                $map['a.create_time'] = ['EGT', strtotime($start)];
            }
        }
        $model = new \Home\Model\Db\LogCompanyContactViewsModel();
        $count = $model->getCount($map);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, 15);
            $list = $model->getList($map, $page->nowPage, $page->listRows);
        }
        $return['list'] = isset($list) ? $list : [];
        $return['page'] = isset($page) ? $page->show() : '';
        return $return;
    }
}