<?php
/**
 * @des:基础信息配置-上传城市数据
 */

namespace app\index\controller\v1;


use app\common\enum\BaseStatusCodeEnum;
use app\index\model\logic\TemplateLogic;
use app\index\validate\TemplateValidate;
use think\Controller;
use think\Request;

class Template extends Controller
{
    /**
     * @des:上传城市数据
     * @param Request $request
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function uploadCityData(Request $request)
    {
        $checked = TemplateValidate::checkFile($_FILES['file'] ?? null);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $logic = new TemplateLogic();
        $result = $logic->uploadCityData();

        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

}