<?php

/**
 * 熊掌号
 */
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class XiongzhangController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
        import('Library.Org.OAuth.BaiduOAuth2');
        $appid = "i0rmlcMsuL4X5Lmf6gG2VAwbrqjKaZwv";
        $this->auth = new \Library\Org\OAuth\BaiduOAuth2($appid,"GSHWnKAQlvPnonV4CUa8mnCGo07FietL");
    }

    public function index()
    {
        //获取百度TOKEN
        $token = $this->getToken();
        //获取菜单信息
        $menu = $this->getMenu($token);
        if ($menu != false) {
            $this->assign("menu",$menu);
        }
        //获取素材信息
        $materialList = $this->getAllMaterial();
        $this->assign("materialList",$materialList["list"]);
        $this->display();
    }

    /**
     * 自动回复
     * @return [type] [description]
     */
    public function reply()
    {
        if ($_POST) {
            $type = I("post.type");
            $sutType = I("post.subType");
            $id = I("post.id");
            switch ($type) {
                case 'subscribe':
                    //关注消息
                    $data["event"] = "subscribe";
                    break;
                case 'message':
                    //收到消息回复
                    $data["event"] = "message";
                    break;
                case 'keyword':
                    //关键字回复
                    $data["event"] = "keyword";
                    $data["rule"] = I("post.rule");
                    break;
            }

            switch ($sutType) {
                case 'view_text':
                    //文本回复
                    $data["msgtype"] = "text";
                    $data["content"] = I("post.msg");
                    break;
                case 'view_limited':
                    $data["msgtype"] = "mpnews";
                    $data["content"] = I("post.media_id");
                    break;
                case 'view_image':
                    //图文回复
                    $data["msgtype"] = "image";
                    $data["content"] = I("post.media_id");
                    break;
            }

            if (empty($data["content"])) {
                $this->ajaxReturn(array("status" => 0,"info" => "请选择回复内容！"));
            }

            if ($data["event"] != "keyword") {
                //删除原来的事件数据
                D("BaiduReply")->delReplyByEvent($data["event"]);
            }

            if (!empty($id)) {
                //添加数据
                $i =  D("BaiduReply")->editReply($id,$data);
            } else {
                //添加数据
                $id = $i =  D("BaiduReply")->addReply($data);
            }

            if ($i !== false) {
                if ($data["event"] == "keyword") {
                    //删除关键词
                    D("BaiduReply")->removeReplyKeyWord($id);
                    //添加关键词
                    $keyword = I("post.keyword");
                    $keyword = array_filter(explode(",", $keyword));
                    if (count($keyword) > 0) {
                        foreach ($keyword as $key => $value) {
                            $sub = array(
                                "reply_id" =>  $id,
                                "keyword" => $value
                            );
                            $all[] = $sub;
                        }
                        D("BaiduReply")->addAllKeyWord($all);
                    }
                }
                $this->ajaxReturn(array("status" => 1,"data"=>$i));
            }
            $this->ajaxReturn(array("status" => 0,"info" => "操作失败"));
        } else {
            //获取素材信息
            $materialList = $this->getAllMaterial();
            //获取图片信息
            $imgList = $this->getAllMaterial("image");
            //获取关键字列表
            $wordList = $this->getWordList();
            //获取被关注回复
            $subscribe = $this->getReplyEventInfo("subscribe");
            //收到消息回复
            $message = $this->getReplyEventInfo("message");

            $this->assign("message",$message);
            $this->assign("subscribe",$subscribe);
            $this->assign("wordList",$wordList);
            $this->assign("imgList",$imgList["list"]);
            $this->assign("materialList",$materialList["list"]);
            $this->display();
        }
    }

    public function findkeyword()
    {
        if ($_POST) {
            $id = I("post.id");
            $info = D("BaiduReply")->findReplyById($id);
            if (count($info) > 0) {
                if ($info["msgtype"] != "text") {
                    $info["media"] = $this->getPerpetualMaterialInfo($info["content"]);
                }
                $this->ajaxReturn(array("status" => 1,"data" => $info));
            }
            $this->ajaxReturn(array("status" => 0,"info" => "获取失败"));
        }
    }

    public function removeKeyWord()
    {
        if ($_POST) {
           $id = I("post.id");
           $i = D("BaiduReply")->removeReplyById($id);
           if ($i !== fasle) {
                D("BaiduReply")->removeReplyKeyWord($id);
                $this->ajaxReturn(array("status" => 1));
           }
           $this->ajaxReturn(array("status" => 0,"info" => "删除失败"));
        }
    }

    public function switchReply()
    {
        if ($_POST) {
            $enabled = I("post.enabled");
            $data = array(
                "option_name" => "xz_switch",
                "option_value" => $enabled,
                "option_group" => "xiongzhanghao",
                "option_remark" => "熊掌号自动回复开关"
            );
            $result = D("Options")->getOptionNoCache("xz_switch");
            if (count($result) > 0) {
                D("Options")->setOption("xz_switch",$data["option_value"]);
            } else {
                //新增
                D("Options")->addOption($data);
            }
        }
    }

    /**
     * 设置菜单
     */
    public function setMenu()
    {
        if ($_POST) {
            $menu = json_decode(htmlspecialchars_decode(I("post.menu")),true);
            foreach ($menu as $key => $value) {
                $sub = array(
                    'name' => trim($value["name"]),
                    "type" => $value["type"],
                    'sub_button' => array()
                );

                if (count($value["children"]) > 0) {
                    unset($sub["type"]);
                    foreach ($value["children"] as $val) {
                        $child = array(
                            'type'=> 'click',
                            'name'=> trim($val["name"]),
                            'key' => 'qizuanag'
                        );

                       switch ($val["msg"]['type']) {
                           case 'view':
                               unset($child["type"]);
                               unset($child["key"]);
                               $child["type"] = "view";
                               $child["url"] = $val["msg"]['url'];
                               break;
                           case 'view_limited':
                              $child["msg"] = array(
                                    "text" => $val["msg"]['text'],
                                    "type" => "view_limited",
                                    "materialId" => $val["msg"]['materialId']
                              );
                               break;
                           case 'view_text':
                               $child["msg"] = array(
                                    "text" => $val["msg"]['text'],
                                    "type" => "view_text"
                               );
                               break;
                       }
                       $sub["sub_button"][] = $child;
                    }
                } else {
                    unset($sub["sub_button"]);
                    $sub["key"] = "qizuang";
                    switch ($value["msg"]['type']) {
                        case 'view':
                            $sub = array(
                                "type"=> "view",
                                "name"=> trim($value["name"]),
                                "url"=> $value["msg"]["url"]
                            );
                           break;
                       case 'view_limited':
                            $sub = array(
                                "type"=> "click",
                                "name"=> trim($value["name"]),
                                "msg" => array(
                                    "text"=> $value["msg"]['text'],
                                    "type"=> "view_limited",
                                    "materialId"=> $value["msg"]['materialId']
                                ),
                                "key" => "qizuang"
                            );
                           break;
                       case 'view_text':
                            $sub = array(
                                "type"=> "click",
                                "name"=> trim($value["name"]),
                                "msg" => array(
                                    "text"=> $value["msg"]['text'],
                                    "type"=> "view_text"
                                ),
                                "key" => "qizuang"
                            );
                           break;
                   }
                }
                $param["button"][] = $sub;
            }

            $data["menues"] = json_encode($param);

            $token = $this->getToken();
            $url = "https://openapi.baidu.com/rest/2.0/cambrian/menu/create?access_token=".$token;
            $response = $this->auth->getHttp($url,"POST",$data);

            if ($responsep["error_code"] == 0) {
                $this->ajaxReturn(array("status" => 1));
            } else {
                $this->ajaxReturn(array("status" => 0,"info" => $response["error_msg"]));
            }
        }
        $this->display();
    }

    public function getMaterialList()
    {
        $response = $this->getAllMaterial();
        $this->ajaxReturn(array("status"=>1,"data" => $response['list'],"page"=>$response['page']));
    }

    /**
     * 获取菜单
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    private function getMenu($token)
    {
        $url = "https://openapi.baidu.com/rest/2.0/cambrian/menu/get?access_token=".$token;
        $response = $this->auth->getHttp($url,"GET");
        //有菜单
        if (!empty($response["menu"])) {
            $menu = $response["menu"];
            foreach ($menu["button"] as $key => $value) {
                if (count($value["sub_button"]) > 0) {
                    foreach ($value["sub_button"] as $k => $val) {
                        if ($val["type"] == "view") {
                            $menu["button"][$key]["sub_button"][$k]["msg"] = array(
                                "url" => $val['url'],
                                "type" => $val["type"]
                            );
                        }
                    }
                } else {
                    switch ($menu["button"][$key]["type"]) {
                        case 'view':
                            $menu["button"][$key]["type"] = "click";
                            $menu["button"][$key]["msg"] = array(
                                "url" => $value['url'],
                                "type" => $value["type"]
                            );
                            break;

                        default:
                            # code...
                            break;
                    }
                }
            }
            return $menu["button"];
        }
        return false;
    }

    /**
     * 获取所有素材
     * @return [type] [description]
     */
    private function getAllMaterial($type = "news")
    {
        $p = 1;
        if (I("get.p") !== "") {
            $p = intval(I("get.p"));
        }

        if (I("get.type") !== "") {
            $type = I("get.type");
        }
        $pageCount = 50;
        $page = ($p-1)*$pageCount;

        $list = S("CACHE:XIONGZHANG:".$type.":".$page);
        if (!$list) {
            //获取百度TOKEN
            $token = $this->getToken();
            $url = "https://openapi.baidu.com/rest/2.0/cambrian/material/batchget_material?access_token=$token&type=$type&offset=$page&count=".$pageCount;
            $response = $this->auth->getHttp($url,"GET");

            if ($response["total_count"] > 0) {
                foreach ($response["item"] as $key => $value) {
                    if ($type == "news") {
                        $list[] = array(
                            "media_id" => $value["media_id"],
                            "news_item" => $value["content"]["news_item"][0],
                            "total_count" => $response["total_count"]
                        );
                    } else {
                        $list[] = array(
                            "media_id" => $value["media_id"],
                            "news_item" => array(
                                "src" => $value["url"],
                                "title" => $value["name"]
                            ),
                            "total_count" => $response["total_count"]
                        );
                    }
                }

            } else {
                $list = null;
            }
            S("CACHE:XIONGZHANG:".$type.":".$page,$list,60*60);
        }
        return array("list" => $list,"page"=>$p+1);
    }

    public function getPerpetualMaterial()
    {
        if ($_POST) {
            $media_id = I("post.media_id");
            $response = $this->getPerpetualMaterialInfo($media_id);
        }
        return $this->ajaxReturn(array("status"=>1,"data"=>$response));
    }

    /**
     * 获取永久素材
     * @return [type] [description]
     */
    private function getPerpetualMaterialInfo($media_id)
    {
        $response = C("CACHE:XIONGZHANG:Material:".$media_id );
        if (!$response) {
            $token = $this->getToken();
            $url = "https://openapi.baidu.com/rest/2.0/cambrian/material/get_material?access_token=$token&media_id=".$media_id;
            $response = $this->auth->getHttp($url,"GET");

            if (isset($response["news_item"])) {
                  $response = $response["news_item"][0];
            } else {
                $response["title"] = $response["name"];
            }
            C("CACHE:XIONGZHANG:Material:".$media_id,$response,60*60*24);
        }
        return $response;
    }

    /**
     * 获取token
     * @return [type] [description]
     */
    private function getToken()
    {
        $accessToken = D("BaiduToken")->getToken();
        $time = time();

        if (count($accessToken) == 0 || $time > $accessToken["expires_in"]) {
            $appid = "i0rmlcMsuL4X5Lmf6gG2VAwbrqjKaZwv";
            $secret = "GSHWnKAQlvPnonV4CUa8mnCGo07FietL";

            $url =  "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=$appid&client_secret=".$secret;
            $response = $this->auth->getHttp($url,"GET");
            if (empty($response["error"])) {
                $accessToken = $response["access_token"];
                $data = array(
                    "appid" => $appid,
                    "token" =>  $accessToken,
                    "expires_in" =>  $time + 3600,
                    "created_at" =>  $time
                );
            }
            D("BaiduToken")->addToken($data);
        } else {
            $accessToken = $accessToken["token"];
        }

        return  $accessToken;
    }

    /**
     * 获取关键字列表
     * @return [type] [description]
     */
    private function getWordList()
    {
        $list = D("BaiduReply")->getKeyWordList();

        foreach ($list as $key => $value) {
            switch ($value["msgtype"]) {
                case 'image':
                    $list[$key]["content"] = "图片";
                    break;
                case 'mpnews':
                    $list[$key]["content"] = "图文";
                    break;
            }
        }
        return $list;
    }

    private function getReplyEventInfo($event)
    {
        $info = D("BaiduReply")->getReplyByEvent($event);
        if (count($info) > 0) {
            if ($info["msgtype"] != "text") {
                $response = $this->getPerpetualMaterialInfo($info["content"]);
                $info["media"] = $response;
            }
        }
        return $info;
    }
}
