<?php
// +----------------------------------------------------------------------
// | LogCompanyContactViews   装修公司联系方式查看记录逻辑层
// +----------------------------------------------------------------------

namespace Common\Model\Logic;


class CompanyContactViewsLogicModel
{
    /**
     * 添加装修公司联系方式查看记录
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function saveLog($data)
    {
        $model = new \Common\Model\Db\LogCompanyContactViewsModel();
        $data['create_time'] = time();
        if (isset($data['tel']) && !isset($data['tel_encrypt'])) {
            $app = new \App();
            $data['tel_encrypt'] = $app->order_tel_encrypt($data['tel']);
        }
        return $model->saveLog($data);
    }

    /**
     * 查询当天装修公司手机号码查看次数
     *
     * @param string $tel 手机号码
     * @return mixed
     */
    public function getCount($tel)
    {
        $map['tel'] = ['EQ', $tel];
        $model = new \Common\Model\Db\LogCompanyContactViewsModel();
        return $model->getCount($map);
    }

    /**
     * 添加装修公司联系方式查看黑名单
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function saveBlack($data)
    {
        $model = new \Common\Model\Db\CompanyContactViewsBlackModel();
        $find = $model->getOne($data);
        if ($find) {
            return true;
        }
        $data['create_time'] = time();
        if (isset($data['tel']) && !isset($data['tel_encrypt'])) {
            $app = new \App();
            $data['tel_encrypt'] = $app->order_tel_encrypt($data['tel']);
        }
        $data['mark'] = '系统自动封禁';
        $data['admin_name'] = '系统';
        return $model->saveBlack($data);
    }

    /**
     * 查询当前IP或者手机是否在黑名单
     *
     * @param string $tel 手机号码
     * @param string $company_id 装修公司ID
     * @return mixed
     */
    public function hasBlack($tel, $ip)
    {
        $map['_complex'] = [
            'tel' => ['EQ', $tel],
            'ip' => ['EQ', $ip],
            '_logic' => 'OR'
        ];

        $model = new \Common\Model\Db\CompanyContactViewsBlackModel();
        return $model->getOne($map);
    }
}