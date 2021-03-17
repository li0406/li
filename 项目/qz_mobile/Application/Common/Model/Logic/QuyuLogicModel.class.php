<?php


namespace Common\Model\Logic;


use Common\Model\QuyuModel;

class QuyuLogicModel
{
    /**
     * 获取所有已开站城市
     * @return mixed
     */
    public function getAllOpenCitys()
    {
        $model = new QuyuModel();
        $list = $model->getAllOpenCitys();

        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();

        foreach ($list as $key => $val) {
            $tempKey = $app->getFirstCharter($val["cname"]);
            $list[$key]["cname_u"] = $tempKey . ' ' . $val['cname'];
        }
        return $list;

    }

}