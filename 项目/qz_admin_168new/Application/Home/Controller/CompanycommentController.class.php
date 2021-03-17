<?php
namespace Home\Controller;

use Common\Enums\ApiConfig;
use ErrorCode;
use Home\Common\Controller\HomeBaseController;
use Home\Model\CommentModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Logic\CommentsLogicModel;
use Home\Model\Logic\UcenterProfileLogicModel;
use Home\Model\QuyuModel;

class CompanycommentController extends HomeBaseController
{

    //评论列表页面
    public function index()
    {
        // 获取所有城市
        $citymodel = new QuyuModel();
        $citylist = $citymodel->getAllQuyuOnly('cid');
        foreach ($citylist as $key => $val){
            $admincitys[$key] = $val['cid'];
        }

//        $admincitys = getAdminCityIds(true, true, true);  //获取账号的管辖城市？
        $city = getCityListByCityIds($admincitys);
        $search = $_GET;
        if (empty($_GET['ordertype'])) {
            $search['ordertype'] = 1;
        } else {
            $search['ordertype'] = I("get.ordertype");
        }

        $c_page = intval($_GET['p']);
        if ($_SESSION['uid'] != 1) {
            $map['c.cs'] = array('in', $admincitys);
        }
        if ($_GET['cityid'] != 0) {
            $map['c.cs'] = array('eq', $_GET['cityid']);
        }
        $start_time = date("Y-m-d", strtotime("-1 day"));
        $end_time = date("Y-m-d");
        //选择时间段
        if (!empty($_GET['start_time']) && !empty($_GET['end_time'])) {
            $start_time = strtotime($_GET['start_time'] . " 00:00:00");
            $end_time = strtotime($_GET['end_time'] . " 23:59:59");
            $map['c.time'] = array('between', "$start_time,$end_time");
        }
        //公司名称、ID
        if (!empty($_GET['company'])) {
            $company = I("get.company");
            $map['u.company'] = $company;
        }
        //公司状态
        if (!empty($_GET['companytype']) || intval($_GET['companytype']) === 0) {
            $companytype = I("get.companytype");
            if ($companytype != 9 && $companytype != '') {
                $map['u.on'] = array('EQ', $companytype); //-1过期 0注册  1认证 2会员
            } else {
                if ($companytype == '') {
                    $map['u.on'] = 2;
                }
            }
        } else {
            $map['u.on'] = 2; //取消morning查询
            $search['companytype'] = 2;
        }

        //推荐状态
        if (!empty($_GET['recommend_state']) || intval($_GET['recommend_state']) === 0) {
            $recommend = I("get.recommend_state");
            if ($recommend != 3 && $recommend != '') {
                $map['c.company_recommend'] = array('EQ', $recommend);
            }

        }
        //审核状态
        if (!empty($_GET['audit_state']) || intval($_GET['audit_state']) === 0) {
            $isveritfy = I("get.audit_state");
            if ($isveritfy != 2 && $isveritfy != '') {
                $map['c.isveritfy'] = array('EQ', $isveritfy);
            }

        }
        //排序
        if (!empty($_GET['ordertype'])) {
            $map['order'] = I("get.ordertype");
        } else {
            $map['order'] = 1;
        }

        if (empty($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval(I("get.p"));
        }
        if (empty($_GET['pagecount'])) {
            $pagecount = 20;
        } else {
            $pagecount = I("get.pagecount");
        }
        $search['pagecount'] = $pagecount;
        $list = $this->getCommentList($map, $page, $pagecount);

        for ($i = 0; $i < count($list['list']); $i++) {
            //如果 sg sj fw三项都为0 说明是老的分数 直接取count分数即可
            //如果 sg sj fw三项不为0 说明是新的分数 取sg sj fw三项平均分
            if ($list['list'][$i]["sj"] != 0 && $list['list'][$i]["fw"] != 0 && $list['list'][$i]["sg"] != 0) {
                $list['list'][$i]['count'] = sprintf("%01.1f", ($list['list'][$i]["sj"] + $list['list'][$i]["fw"] + $list['list'][$i]["sg"]) / 3);
            } else {
                $list['list'][$i]['sj'] = $list['list'][$i]["fw"] = $list['list'][$i]["sg"] = $list['list'][$i]['count'];
            }

            $list['list'][$i]['text'] = str_replace('`&[', ' &#x261b;[', $list['list'][$i]['text']);
            $list['list'][$i]['text'] = str_replace(']&`', ']&#x261a; ', $list['list'][$i]['text']);
        }

        $this->assign("citys", $city); //取城市
        $this->assign('search', $search); //搜索条件
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 获取评论列表
     * @param  array            $map             查询条件
     * @param  string           $page            页码
     * @param  string           $count           分页长度
     * @return array            $result          修改结果
     */
    private function getCommentList($map, $pageIndex, $pageCount)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 20 : intval($pageCount);

        $count = D('Comment')->getCommentAllNum($map); //原查询有默认条件on=2
        $result['list'] = D('Comment')->getCommentList($map, ($pageIndex - 1) * $pageCount, $pageCount);
        //var_dump(M()->getLastSql());

        if ($count > $pageCount) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $pageCount);
            $result['page'] = $page->show();
        }
        $result['totalnum'] = $count;
        //var_dump($result);
        return $result;
    }

    /**
     * 推荐
     * @param  array            $map             查询条件
     * @param  string           $page            页码
     * @param  string           $count           分页长度
     * @return array            $result          修改结果
     */
    public function checkRecommend()
    {
        $model = new CommentModel();
        $map['id'] = I("post.id"); //操作的评论ID
        $data['company_recommend'] = I("post.recommend"); //推荐状态
        if($data['company_recommend'] == 2){
            $comment_info = $model->getCommentInfoById($map['id']);
            if($comment_info['isveritfy'] > 0){
                $this->ajaxReturn(array('data' => '', 'info' => '审核通过才可以推荐哦', 'status' => 0));
            }
            $data['update_time'] = time();
        }
        $result = D('Comment')->setRecommend($map, $data);
        //var_dump($result);
        if ($result) {
            $this->ajaxReturn(array('data' => $result, 'info' => '操作成功！^v^', 'status' => 1));
        } else {
            $this->ajaxReturn(array('data' => '', 'info' => '操作失败，请重试！', 'status' => 0));
        }
    }

    /**
     * 审核、批量审核
     * @param  array            $map             查询条件
     * @param  string           $page            页码
     * @param  string           $count           分页长度
     * @return array            $result          修改结果
     */
    public function checkVerify()
    {
        $ucenterprofilelogic = new UcenterProfileLogicModel();
        $commentlogic = new CommentsLogicModel();
        $map['id'] = array('IN', I("post.id")); //操作的评论ID
        $data['isveritfy'] = I("post.verify"); //推荐状态
        $result = D('Comment')->setVerify($map, $data);
        //var_dump($result);
        if ($result) {
            //只有verify为0的时候才去验证积分任务
            if(I("post.verify") == 0 && I("post.verify") != ''){

                //修改积分？
                $ids = explode(',',I("post.id"));
                if(is_array($ids) && count($ids) > 1){
                    // 验证id是否在用户中心里面有账号
                    $commentlist = $commentlogic->checkCommentUserid($ids);
                    //评论列表，最多50个，就放在了循环里面一个一个处理了
                    foreach ($commentlist as $key => $val){
                        $ucenterprofilelogic->addTaskScore(13,$val['userid'],$val['id'],12);    //12.装修公司评论
                    }

                }else{
                    $commentlist = $commentlogic->checkCommentUserid(I("post.id"));
                    $commentinfo = $commentlist[0];
                    $ucenterprofilelogic->addTaskScore(13,$commentinfo['userid'],I("post.id"),12);    //12.装修公司评论
                }
            }



            //重新更新对应公司的评论数
            D('Comment')->checkCompanyCountByCompanyId($map['id']);
            $this->ajaxReturn(array('data' => $result, 'info' => '操作成功！^v^', 'status' => 1));
        } else {
            $this->ajaxReturn(array('data' => '', 'info' => '操作失败，请重试！', 'status' => 0));
        }
    }

    /**
     * 获取评论的内容和评论图片列表
     */
    public function getCommentContengAndImgsById()
    {
         $model = new CommentModel();
        $comment_id = I('get.id');
        if($comment_id){
            $comment_info = $model->getCommentInfo($comment_id);

            $imgs= $model->getCommentImgsById($comment_id);
            $imglist = [];
            $i=0;
            if($imgs){
                foreach($imgs as $key => $val){
                    if($val['imgurl']){
                        $imglist[$i++] = changeImgUrl($val['imgurl'],2);
                    }
                }
                $comment_info['imgcount'] = count($imglist);
                $comment_info['imglist'] = $imglist;
            }else{
                $comment_info['imgcount'] = 0;
                $comment_info['imglist'] = [];
            }
            $this->ajaxReturn(array("error_code"=>ApiConfig::REQUEST_SUCCESS,"error_msg"=>"获取成功","data"=>$comment_info));
        }else{
            $this->ajaxReturn(array("error_code"=>ApiConfig::CANNOT_GETDATA,"error_msg"=>"未获取到评价id","data"=>""));
        }

    }

    //删除评价
    public function deleteCommentById()
    {
        $model = new CommentModel();
        $commentid = I('post.id');
        if($commentid){
            $commentinfo = $model->getCommentInfoById($commentid);
            if($commentinfo){
                M()->startTrans(); // 开启事务
                try {
                    $model->deleteCommentById($commentid);
                    $model->deleteCommentImgs($commentid);

                    //
                    $comid = $commentinfo['comid'];
                    $map = [];

                    $map['isveritfy'] = 0;
                    $map['comid'] = $comid;
                    $tempdata['comment_count'] = $model->getCommentCountByMap($map);    //获取装修公司评论数量

                    $comment_score = $model->getCompanyPingfen($comid); //获取装修公司评分
                    $tempdata['comment_score'] = $comment_score['score'];       //评分
                    $usercompanymodel = new CompanyModel();
                    $usercompanymodel->saveUserCompany($tempdata, $comid);

                    M()->commit(); // 事务提交
                    $this->ajaxReturn(array('data' => "", 'info' => '操作成功！^v^', 'status' => 1));
                }catch (ReflectionException $e){
                    M()->rollback(); // 事务回滚
                    $this->ajaxReturn(array("info"=>"操作失败,请是刷新重试！","status"=>0));
                }
            }else{
                $this->ajaxReturn(array('data' => '', 'info' => '未获取到评价内容', 'status' => 0));
            }
        }else{
            $this->ajaxReturn(array('data' => '', 'info' => '未获取到评价id', 'status' => ApiConfig::CANNOT_GETDATA));
        }
    }


}
