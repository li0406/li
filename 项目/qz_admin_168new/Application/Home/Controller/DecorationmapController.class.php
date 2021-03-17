<?php

//装修地图-数据清洗

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\DecorationmapLogicModel;
use Home\Model\Logic\LogAdminLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\Logic\UserLogicModel;

class DecorationmapController extends HomeBaseController
{
    public function index()
    {
        $input = I('get.');
        $pageCount = !empty($input['limit']) ? $input['limit'] : 10;
        $mapLogic = new DecorationmapLogicModel();
        $count = $mapLogic->getDecorationMapCount($input);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $list = $mapLogic->getDecorationMapList($input, $p->firstRow, $p->listRows);
            $this->assign('page', $p->show());
            $this->assign('list', $list);
            $this->assign('total_count', $count);
        }
        $citys = getUserCitys();
        $this->assign("city", $citys);
        return $this->display();
    }

    public function edit_decoration()
    {
        $mapLogic = new DecorationmapLogicModel($this->User);
        //保存操作
        if (IS_AJAX) {
            $post = I('post.');
            //原数据信息
            $info = $mapLogic->getDecorationMapInfo($post['id']);
            //更新操作
            $status = $mapLogic->updateDecorationMapInfo($post);
            if ($status) {
                if (!empty($info)) {
                    $log = new LogAdminLogicModel();
                    $log->addLog('编辑装修地图数据', 'update-map', $info, $post['id']);
                }
                $this->ajaxReturn(['error_code' => 0, 'error_msg' => '编辑成功!']);
            } else {
                $this->ajaxReturn(['error_code' => 400, 'error_msg' => '编辑失败!']);
            }
        }
        $id = I('get.id');
        //展示
        $info = $mapLogic->getDecorationMapInfo($id);
        $info['haoping_lv'] = $info['haoping_lv'] * 100;

        $citys = getUserCitys();
        $this->assign("city", $citys);
        $this->assign('info', $info);
        return $this->display('edit');
    }

    public function del_decoration()
    {
        $id = I('post.id');
        $mapLogic = new DecorationmapLogicModel();
        $info = $mapLogic->getDecorationMapInfo($id);
        $status = $mapLogic->delDecorationMap($id);
        if ($status) {
            $log = new LogAdminLogicModel();
            $log->addLog('删除装修地图数据', 'del-map', $info, $id);
            $this->ajaxReturn(['error_code' => 0, 'error_msg' => '删除成功!']);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '删除失败!']);
        }
    }

    /**
     * 导入数据
     */
    public function importMap()
    {
        $mapLogic = new DecorationmapLogicModel($this->User);
        $quyuLogic = new QuyuLogicModel();
        //处理excel上传
        $ex = $_FILES['file'];
        $info = $mapLogic->importFile($ex);
        if ($info['error_code'] != 0) {
            $this->ajaxReturn($info);
        }
        //获取城市数据
        $cityInfo = $quyuLogic->getCityInfoByName($info['data']['city']);
        $cityInfo = array_column($cityInfo, null, 'cname');
        //获取现有公司信息
        $nowCompanyInfo = [];
        if (count($info['data']['fake']) > 0) {
            $userLogic = new UserLogicModel();
            $nowCompanyInfo = $userLogic->getUserCompany(1, array_column($info['data']['fake'], 'company_id'));
            $nowCompanyInfo = array_column($nowCompanyInfo, null, 'id');
        }
        //添加非真会员数据
        $fakeStatus = $mapLogic->disposeData(2, $info['data']['fake'], $cityInfo, $nowCompanyInfo);
        //添加三方数据
        $tripartiteStatus = $mapLogic->disposeData(3, $info['data']['tripartite'], $cityInfo);
        $info = [
            'success' => array_merge($fakeStatus['success'] ?: [], $tripartiteStatus['success'] ?: []),
            'false' => array_merge($fakeStatus['false'] ?: [], $tripartiteStatus['false'] ?: []),
            'repetition' => array_merge($fakeStatus['repetition'] ?: [], $tripartiteStatus['repetition'] ?: [])
        ];
        $this->ajaxReturn(['error_code' => 0, 'data' => $info]);
    }
}
