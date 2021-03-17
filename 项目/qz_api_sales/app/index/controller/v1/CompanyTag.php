<?php

namespace app\index\controller\v1;

use app\common\model\logic\CompanyLogic;
use app\common\model\logic\SubTagLogic;
use \app\index\validate\CompanyTag as CompanyTagValidate;
use think\Controller;
use think\Request;

Class CompanyTag extends Controller
{
    public function companyList(Request $request, CompanyLogic $companyLogic)
    {
        $options = $request->param();
        $page = $request->param("page", 1, "intval");
        $page_size = $request->param("page_size", 20, "intval");
        $data = $companyLogic->getCompanyTagList($options, $page, $page_size);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $page_size, $page),
        ]);

        return json($response);
    }

    public function companyFixationTag(CompanyLogic $companyLogic)
    {
        $tag = $companyLogic->getCompanyFixationTag();
        return json(sys_response(0, '', ['list' => $tag]));
    }

    public function addCompanyTag(Request $request, CompanyLogic $companyLogic,SubTagLogic $subTagLogic)
    {
        $input = $request->post();
        // 验证
        $companyValidate = new CompanyTagValidate();
        if ($companyValidate->scene('add')->check($input) == false) {
            return json(sys_response(4000002, $companyValidate->getError()));
        }
        $tags = explode(',',$input['tag']);
        if (count($tags) > 10) {
            return json(sys_response(4000800,'公司标识最多选择10个!'));
        }
        //获取当前标签所有编号
        $tags = $subTagLogic->getSendTagInfo($input, $tags);
        $info = $companyLogic->saveCompanyTag($input['company_id'],$tags);
        return json($info);
    }
}