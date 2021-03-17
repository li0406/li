<?php
/**
 * Created by PhpStorm.
 * User: qzjsb
 * Date: 2019/4/29
 * Time: 10:32
 */

namespace Mobile\Controller;


use Common\Model\Logic\ThematicWordsLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class PindaoController extends MobileBaseController
{
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                $SCHEME_HOST = $this->SCHEME_HOST;
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
            }
        }
    }


    public function index(){

        $pageIndex = 1;
        $pageCount = 58;     //加上2条hot  一页60条

        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }

        //TDK
        $basic['head']['title'] = '在线知识问答平台 - 齐装网装修问答';
        $basic['head']['keywords'] = '知识问答,问答平台,在线问答,装修问答';
        $basic['head']['description'] = '齐装网在线知识问答平台提供全面的生活装修知识问答、在线问答、问答知识等网友提问及回答信息，解决装修问题、生活问题就来齐装网在线知识问答平台。';
        $basic['body']['title'] = '装修攻略';

        $logic = new ThematicWordsLogicModel();
        $result = $logic->getList(3,$pageIndex,$pageCount);

        $uri = explode('?',__SELF__);
        $this->assign('uri',$uri[0]);
        $this->assign('basic',$basic);

        $this->assign("page",$result["page"]);
        $this->assign("list",$result["list"]);
        $this->display();
    }

    /**
     * 获取关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSpecialkeyword($pageIndex,$pageCount,$keyword_module,$is_hot)
    {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $count = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordCount($keyword_module,$is_hot);
        if ($count > 0) {
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config,"html");
            $show    = $page->show3();
            $list = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordList($keyword_module, $is_hot,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $value) {
                if($value["keyword_module"] == 1){
                    $list[$key]["url"] = $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/wenda/zs/".$value["href"]."/";
                }else{
                    $list[$key]["url"] = $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/wenda/zs/".$value["href"]."/";
                }
            }
        }
        return array("list"=>$list,"page"=>$show);
    }
}