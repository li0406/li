<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class MeitudesignerController extends HomeBaseController{
    /**
     * [index 主站文章列表]
     * @return [type] [description]
     */
    public function index()
    {
        $data = I('get.');
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }

        // 装修公司或设计师名字
        if (!empty($data['condition'])) {
            $condition  = $data['condition'];
        }

        $info['info'] = $this->getMeituDesignerList($condition,$pageIndex);
        $this->assign('info',$info);
        $this->display();
    }


    /**
     * [operate 文字编辑]
     * @return [type] [description]
     */
    public function operate()
    {
        //文章新增编辑
        $data = I('post.');
        if(!empty($data)){
            $admin = getAdminUser();
            $id = $data['id'];
            $save['name'] = $data['name'];
            if (empty($save['name'])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请添加设计师！','status'=>0));
            }
            $save['bm'] = $data['bm'];
            $save['uid'] = $data['uid'];
            $save['comid'] = $data['comid'];
            $save['comname'] = $data['comname'];
            $save['shortname'] = $data['shortname'];
            $save['px'] = $data['px'];
            $save['enabled'] = $data['enabled'];
            $save['userid'] = $admin['id'];
            $save['username'] = $admin['name'];
            if (empty($id)) {
                $save['time'] = time();
                $result = D('MeituDesigner')->addMeituDesigner($save);
                if (!$result) {
                    $this->ajaxReturn(array('data'=>'','info'=>'新增操作失败！','status'=>0));
                }
            } else {
                $result = D('MeituDesigner')->editMeituDesigner($id, $save);
                if (!$result) {
                    $this->ajaxReturn(array('data'=>'','info'=>'编辑操作失败！','status'=>0));
                }
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
        }
        //编辑时获取信息
        $id = I('get.id');
        if(!empty($id)){
            $info['info'] = D('MeituDesigner')->getMeituDesignerById($id);
        }
        $this->assign('info',$info);
        $this->display();
    }

    public function deleteMeituDesigner(){
        $id = I('post.id');
        if (!empty($id)) {
            $result = D('MeituDesigner')->deleteMeituDesigner($id);
            if ($result) {
                $this->ajaxReturn(array('data'=>'','info'=>'删除成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'删除失败,请选择要删除的美图大师！','status'=>0));
    }

    //获取列表并分页
    private function getMeituDesignerList($condition,$pageIndex=1,$pageCount = 16)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D('MeituDesigner')->getMeituDesignerCount($condition);
        $result['list'] = D('MeituDesigner')->getMeituDesignerList($condition,($pageIndex-1)*$pageCount,$pageCount);

        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        return $result;
    }
}