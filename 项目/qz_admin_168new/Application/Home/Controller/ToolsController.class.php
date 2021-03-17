<?php
/**
 * 管理工具类
 */

namespace Home\Controller;

use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;

class ToolsController extends HomeBaseController
{
    public function specialWord()
    {
        //获取生僻字列表
        $list = D("Home/Logic/SpecialwordLogic")->getSpecialWordList();
        $this->assign("list", $list);
        $this->display();
    }

    public function specialWordUp()
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "word" => trim(I("post.word")),
                "character" => strtoupper(trim(I("post.character")))
            );

            if (empty($data["word"]) || empty($data["character"])) {
                $this->ajaxReturn(array("status" => 0, "info" => "请填写完整的生僻字资料！"));
            }

            if (!preg_match('/[A-Z]/', $data["character"])) {
                $this->ajaxReturn(array("status" => 0, "info" => "首字母只能填写英文"));
            }

            if (!empty($id)) {
                if (I("post.del") == "on") {
                    //删除生僻字
                    $i = D("Home/Logic/SpecialwordLogic")->delSpecialWord($id);
                } else {
                    $i = D("Home/Logic/SpecialwordLogic")->editSpecialWord($id, $data);
                }
            } else {
                $i = D("Home/Logic/SpecialwordLogic")->addSpecialWord($data);
            }

            if ($i !== false) {
                $this->ajaxReturn(array("status" => 1));
            }
            $this->ajaxReturn(array("status" => 0, "info" => "添加失败！"));
        }
    }

    public function upFiles()
    {
        $this->display();
    }

    /**
     * 缓存管理页面
     * author: mcj
     */
    public function cacheView()
    {
        $this->display('cache');
    }

    /**
     * author: mcj
     * 管理缓存的接口
     */
    public function command()
    {
        $data = I('post.', '', 'trim,htmlspecialchars');
        if (empty($data['method']) || empty($data['command'])) {
            $this->ajaxReturn(['status' => ApiConfig::PARAMETER_ILLEGAL, 'error_msg' => '必要参数不全!'], 'JSON');
        }
        $redis_logic = D('Home/Logic/RedisLogic');
        $result = '操作成功';
        switch ($data['method']) {
            case 'redis_key_get':
               $result = $redis_logic->key_get($data['command']);
               break;
            case 'redis_get':
                $result = $redis_logic->get($data['command']);
                break;
            case 'redis_del':
                $redis_logic->del($data['command']);
                break;
            default:
                $this->ajaxReturn(['status' => ApiConfig::PARAMETER_ILLEGAL, 'error_msg' => '操作参数不合法!']);
                break;
        }
        $this->ajaxReturn(['status'=>1,'data'=>$result],'JSON');
    }
}