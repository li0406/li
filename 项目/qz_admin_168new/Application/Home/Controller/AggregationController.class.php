<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*
*/
class AggregationController extends HomeBaseController
{
    public function index()
    {
        //获取TDK列表
        $list = $this->getTdkList();
        $this->assign("list",$list);
        $tmp = $this->fetch("TDKListTmp");
        $this->assign("tdklist",$tmp);

        if (I("get.type") !== "") {
            $type = I("get.type");
        }

        if (I("get.code") !== "") {
            $words = I("get.code");
        }

        if (I("get.start") !== "" && I("get.end") !== "") {
            $map['time'] = [['EGT', strtotime(trim(I("get.start")))], ['ELT', strtotime(trim(I("get.end")))], 'and'];
        }
        $location = I("get.location");
        switch ($type) {
            case '1':
                //已匹配TDK列表
                $list = $this->getWords($words,1,1,$map,$location);
                break;
            case '2':
                //已删除TDK列表
                $list = $this->getWords($words,1,0,$map,$location);
                break;
            default:
                //获取未匹配长尾词列表
                $list = $this->getWords($words,0,0,$map,$location);
                break;
        }

        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }

    /**
     * 添加关键字
     * @return [type] [description]
     */
    public function addword()
    {
        if ($_POST) {
            $data = array(
                "words" => I("post.words"),
                "time" => time()
            );
            //插入长尾词
            $i = D("LongTailKeywords")->addWords($data);
            if ($i !== false) {
                //添加长尾词关键字
                $subWords = I("post.subwords");
                $words = array_filter(explode(",", str_replace("，",",",$subWords)));
                foreach ($words as $k => $val) {
                    $sub[] = array(
                        "long_tail_id" => $i,
                        "words" => $val,
                        "time" => time()
                    );
                }
                $i = D("LongTailKeywords")->addAllChildWords($sub);
                if ($i !== false) {
                    return $this->ajaxReturn(array("status"=>1));
                }
            }
            return $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 删除关键字
     * @return [type] [description]
     */
    public function delword()
    {
        if ($_POST) {
            $ids = I("post.ids");
            $ids = array_filter(explode(",",$ids));
            $i = D("LongTailKeywords")->delAllWords($ids);
            if ($i !== false) {
                //删除原有长尾词和TDK的关联
                D("LongTailKeywordsTdk")->delReleteByLongTailId($ids);
                return $this->ajaxReturn(array("status"=>1,"info"=>1));
            }
            return $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 添加TDK
     * @return [type] [description]
     */
    public function addtdk()
    {
        if ($_POST) {
            $title = I("post.title");
            //检测标题、描述、关键字是否带有变量
            $reg = '/\{关键词\}/';
            $flag = preg_match($reg, $title);
            if (!$flag) {
               $this->ajaxReturn(array("status"=>0,"info"=>"标题未检测到替换标识！"));
            }
            $keywords = I("post.keywords");
            // $flag = preg_match($reg, $keywords);
            // if (!$flag) {
            //    $this->ajaxReturn(array("status"=>0,"info"=>"关键字未检测到替换标识！"));
            // }
            $description = I("post.description");
            // $flag = preg_match($reg, $description);
            // if (!$flag) {
            //    $this->ajaxReturn(array("status"=>0,"info"=>"描述未检测到替换标识！"));
            // }

            $id = I("post.id");
             $data = array(
                "title" => $title,
                "keywords" => $keywords,
                "description" => $description
            );

            if (!empty($id)) {
                $i = D("LongTailKeywordsTdk")->editTdk($id,$data);
            } else{
                $data["time"] = time();
                $i = D("LongTailKeywordsTdk")->addTdk($data);
            }


            if ($i !== false) {
                //查询TDK列表
                $list = $this->getTdkList();
                $this->assign("list",$list);
                $tmp = $this->fetch("TDKListTmp");
                $this->ajaxReturn(array("status"=>1,"data"=>$tmp));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"添加失败！"));
        }
    }

    /**
     * 获取TDK弹窗信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function gettdkinfo()
    {
        if (I("get.id") !== "") {
            //查询TDK信息
            $info = D("LongTailKeywordsTdk")->getTdkInfo(I("get.id"));
            $this->assign("info",$info);
        }

        $tmp = $this->fetch("TDKtmp");
        $this->ajaxReturn(array("data"=>$tmp));
    }

    /**
     * 删除TDK
     * @param string $value [description]
     */
    public function deltdk()
    {
        if ($_POST) {
            $id = I("post.id");
            //删除TDK
            $i =  D("LongTailKeywordsTdk")->deltdk($id);
            if ($i !== false) {
                //删除TDK和长尾词的关联信息
                D("LongTailKeywordsTdk")->delReleteByTdkId($id);
                //查询TDK列表
                $list = $this->getTdkList();
                $this->assign("list",$list);
                $tmp = $this->fetch("TDKListTmp");
                $this->ajaxReturn(array("status"=>1,"data"=>$tmp));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"删除失败！"));
        }
    }

    /**
     * 添加长尾词和TDK关联
     */
    public function tdkrelete()
    {
        if ($_POST) {
            $id = I("post.id");
            $ids = I("post.ids");
            $ids = array_filter(explode(",",$ids));
            //删除原有长尾词和TDK的关联
            D("LongTailKeywordsTdk")->delReleteByLongTailId($ids);
            //添加新的关联
            foreach ($ids as $key => $value) {
                $all[] = array(
                    "long_tail_id" => $value,
                    "tdk_id" => $id
                );
            }
            $i = D("LongTailKeywordsTdk")->addRelete($all);

            if ($i !== false) {
                $data = array(
                    "istdk" => 1
                );
                D("LongTailKeywords")->editAllWords($ids,$data);
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0,"info"=>"操作失败！"));
        }
    }

    /**
     * 获取TDK列表
     * @return [type] [description]
     */
    private function getTdkList()
    {
        $list = D("LongTailKeywordsTdk")->getTdkList();
        $reg = '/\{关键词\}/';
        foreach ($list as $key => $value) {
            $str = preg_replace_callback($reg, function($m){
                return "<span class='red'>".$m[0]."</span>";
            },$value["title"]);
            $list[$key]["title"] = $str;

            $str = preg_replace_callback($reg, function($m){
                return "<span class='red'>".$m[0]."</span>";
            },$value["keywords"]);
            $list[$key]["keywords"] = $str;

            $str = preg_replace_callback($reg, function($m){
                return "<span class='red'>".$m[0]."</span>";
            },$value["description"]);
            $list[$key]["description"] = $str;
        }
        return $list;
    }

    /**
     * 获取未匹配长尾词列表
     * @param  [type] $words [description]
     * @return [type]        [description]
     */
    private function getWords($words,$istdk,$tdk_flag,$map,$location)
    {
        import('Library.Org.Util.Page');
        $model = D("LongTailKeywords");
        $count = $model->getWordsCount($words,$istdk,$tdk_flag,$map,$location);
        if ($count > 0) {
            $p = new \Page($count,1000);
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $show    = $p->show();
            $list = $model->getWords($words,$istdk,$tdk_flag,$p->firstRow,$p->listRows,$map,$location);

            $list = array_chunk($list,5);
        }
        return array("page"=>$show,"list"=>$list);
    }
}