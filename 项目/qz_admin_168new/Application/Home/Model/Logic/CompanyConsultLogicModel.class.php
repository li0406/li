<?php
/**
 * 装修公司咨询
 * Created by PhpStorm.
 * User: mcj
 * Date: 2019/1/16
 * Time: 13:27
 */

namespace Home\Model\Logic;

use Home\Model\CompanyConsultModel;

class CompanyConsultLogicModel
{
    /**
     * 表单数据验证
     *
     * @param $data
     * @return array
     */
    public function _consultCheckVaildate($data)
    {
        //验证城市
        if (!empty($data['city'])) {
            if (!is_numeric($data['city'])) {
                return ['result' => false, 'mes' => '城市筛选条件异常'];
            }
        }
        //公司名称
        if (!empty($data['company'])) {
            if (mb_strlen($data['company'], 'utf-8') > 50) {
                return ['result' => false, 'mes' => '装修公司筛选条件超长'];
            }
        }
        //联系电话
        if (!empty($data['tel'])) {
            if (!preg_match('/^1\d{10}$/', $data['tel'])) {
                return ['result' => false, 'mes' => '手机号格式不正确'];
            }
        }
        //合作状态
        if (!empty($data['cpstatus'])) {
            if (!preg_match('/^[0-4]$/', $data['cpstatus'])) {
                return ['result' => false, 'mes' => '合作状态筛选条件异常'];
            }
        }
        //合作类型
        if (!empty($data['cp_type'])) {
            if (!preg_match('/^[1-2]$/', $data['cp_type'])) {
                return ['result' => false, 'mes' => '合作状态筛选条件异常'];
            }
        }
        //咨询日期
        if (!empty($data['start_time'])) {
            if (!preg_match('/^\d{4}\-\d{1,2}\-\d{1,2}$/', $data['start_time'])) {
                return ['result' => false, 'mes' => '咨询日期开始时间异常'];
            }
        }
        if (!empty($data['end_time'])) {
            if (!preg_match('/^\d{4}\-\d{1,2}\-\d{1,2}$/', $data['end_time'])) {
                return ['result' => false, 'mes' => '咨询日期截止时间异常'];
            }
        }
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            if ($data['start_time'] > $data['end_time']) {
                return ['result' => false, 'mes' => '咨询日期开始时间大于截止时间'];
            }
        }
        //分页条数
        if (!empty($data['psize'])) {
            if (!in_array($data['psize'], [10, 20, 50])) {
                return ['result' => false, 'mes' => '分页条数异常'];
            }
        }
        return ['result' => true, 'mes' => '验证通过'];
    }

    /**
     * 查询条件
     *
     * @param $data
     * @return array
     */
    public function getMap($data)
    {
        $where = [];
        //城市
        if (!empty($data['city'])) {
            $where['a.cs'] = $data['city'];
        }
        //公司名称
        if (!empty($data['company'])) {
            $where['a.name'] = ['like', "%{$data['company']}%"];
        }
        //合作状态
        if (!empty($data['cpstatus'])) {
            $where['a.record_status'] = $data['cpstatus'];
        }
        //合作类型
        if (!empty($data['cp_type'])) {
            $where['a.cooperation_type'] = $data['cp_type'];
        }
        //联系方式
        if (!empty($data['tel'])) {
            $where['a.tel'] = $data['tel'];
        }
        //咨询日期
        if (!empty($data['start_time'])) {
            $where['a.add_time'][] = ['egt', strtotime($data['start_time'] . ' 00:00:00')];
        }
        if (!empty($data['end_time'])) {
            $where['a.add_time'][] = ['elt', strtotime($data['end_time'] . ' 23:59:59')];
        }

        //处理状态
        if (!empty($data['operate_status'])) {
            $where['a.operate_status'] = $data['operate_status'];
        }
        return $where;
    }

    /**
     * 获取客户咨询列表
     *
     * @param array $where
     * @param array $order
     * @param int $p
     * @param int $page_size
     * @return mixed
     */
    public function consultList($where = [], $order = [], $p = 1, $page_size = 20)
    {
        $skip = ($p - 1) * $page_size;
        $order_by = array_merge($order, ['a.add_time' => 'desc']);

        return M('company_consult')
            ->alias('a')
            ->where($where)
            ->field('a.*, b.cname AS city, c.qz_area AS area,d.ip,d.time,au.name operator')
            ->join('LEFT JOIN qz_quyu AS b ON b.cid = a.cs')
            ->join('LEFT JOIN qz_area AS c ON c.qz_areaid = a.qx')
            ->join('LEFT JOIN qz_company_ip_info AS d ON d.id = a.ip_id')
            ->join('LEFT JOIN qz_adminuser AS au ON au.id = a.operator')
            ->order($order_by)
            ->limit($skip, $page_size)
            ->select();
    }

    /**
     * 获取手机号码统计
     *
     * @param array $list
     * @return array
     */
    public function telCount($list = [])
    {

        if (empty($list)) {
            return [];
        }
        $where['tel'] = ['in', array_column($list, 'tel')];
        $telCountList = M('company_consult')->where($where)->field('count(id) as tel_repeat,tel')->group('tel')->select();
        foreach ($list as $k => $v) {
            foreach ($telCountList as $k1 => $v1) {
                if ($v1['tel'] == $v['tel']) {
                    $list[$k]['tel_repeat'] = $v1['tel_repeat'];
                }
            }
        }
        return $list;
    }

    /**
     * 获取客户咨询总数
     *
     * @param array $where
     * @return mixed
     */
    public function consultCount($where = [])
    {
        return M('company_consult')
            ->alias('a')
            ->where($where)
            ->field('a.*, b.cname AS city, c.qz_area AS area,d.ip,d.time')
            ->join('LEFT JOIN qz_quyu AS b ON b.cid = a.cs')
            ->join('LEFT JOIN qz_area AS c ON c.qz_areaid = a.qx')
            ->join('LEFT JOIN qz_company_ip_info AS d ON d.id = a.ip_id')
            ->count('a.id');
    }

    /**
     * 更新创建状态
     * @param $id
     * @param $uid
     * @return mixed
     */
    public function operate($id,$uid)
    {
        $where['id'] = $id;
        $data['operate_time'] = time();
        $data['operate_status'] = 2;
        $data['operator'] = $uid;
        $ret = M('company_consult')->where($where)->data($data)->save();
        return $ret;
    }

    /**
     * 装修合作来源列表
     * @param $begin
     * @param $end
     * @param $city
     * @param $src
     * @param $group
     * @param bool $dowload 是否下载
     * @return array
     */
    public function getSourceList($begin, $end, $city, $src, $group, $download = false)
    {
        $monthBegin = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $monthEnd = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthBegin = strtotime($begin);
            $monthEnd = strtotime($end) + 86400 - 1;
        }

        $model = new CompanyConsultModel();

        $count = $model->getSourceListCount($monthBegin, $monthEnd, $city, $src, $group);

        $list = [];
        if ($count > 0) {

            if (!$download) {
                import('Library.Org.Util.Page');
                $p = new \Page($count, 20);
                $show = $p->show();
                $list = $model->getSourceList($p->firstRow, $p->listRows, $monthBegin, $monthEnd, $city, $src, $group);
            } else {
                $list = $model->getSourceList(null, null, $monthBegin, $monthEnd, $city, $src, $group);
            }
        }
        return array("list" => $list, "page" => $show);
    }

    public function exportConsult($where = [], $order = [])
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = ['公司名称', 'IP地址', '咨询日期', '所属区域', '客户名称', '合作类型', '合作状态', '联系方式', '留言', '处理人', '处理时间','处理状态'];

            $writer->writeSheetRow('Sheet1', $herder);
            $order_by = array_merge($order, ['a.add_time' => 'desc']);
            $list = M('company_consult')
                ->alias('a')
                ->where($where)
                ->field('a.*, b.cname AS city, c.qz_area AS area,d.ip,d.time,au.name operator')
                ->join('LEFT JOIN qz_quyu AS b ON b.cid = a.cs')
                ->join('LEFT JOIN qz_area AS c ON c.qz_areaid = a.qx')
                ->join('LEFT JOIN qz_company_ip_info AS d ON d.id = a.ip_id')
                ->join('LEFT JOIN qz_adminuser AS au ON au.id = a.operator')
                ->order($order_by)
                ->select();
            foreach ($list as $key => $value) {
                $dd['name'] = $value['name'] ?: '';
                $dd['ip'] = $value['ip'] ?: '';
                $dd['add_time'] = date('Y-m-d', $value['add_time']);
                $dd['city'] = $value['city'] . '-' . $value['area'];
                $dd['linkman'] = $value['linkman'] ?: '';
                $dd['cooperation_type'] = $value['cooperation_type'] == 1 ? '其他' : ($value['cooperation_type'] == 2 ? '装修公司入驻' : '已合作');
                $dd['record_status'] = $value['record_status'] == 1 ? '--' : ($value['record_status'] == 2 ? '谈判中' : ($value['record_status'] == 3 ? '不合作' : '已合作'));
                $dd['tel'] = $value['tel'];
                $dd['remark'] = $value['remark'];


                if ($value['operate_status'] == 1) {
                    $dd['operator'] = '--';
                    $dd['operate_time'] = '--';
                }else {
                    $dd['operator'] = $value['operator']?:'--';
                    $dd['operate_time'] = $value['operate_time']?date('Y-m-d',$value['operate_time']):'--';
                }
                $dd['operate_status'] = $value['operate_status'] == 2 ? '已处理' : '未处理';
                $wArr = array_values($dd);
                unset($list[$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control:must-revalidate, post-check=0, pre-check=0');
            header('Content-Type:application/force-download');
            header('Content-Type:application/vnd.ms-execl');
            header('Content-Type:application/octet-stream');
            header('Content-Type:application/download');;
            header('Content-Disposition:attachment;filename="装修公司咨询审核信息列表.xlsx"');
            header('Content-Transfer-Encoding:binary');
            $writer->writeToStdOut();
        } catch (\Exception $e) {
            if ($_SESSION['uc_userinfo']['uid'] == 1) {
                var_dump($e);
            }
        }
        exit();
    }
}