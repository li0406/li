<?php
//效果图
namespace Common\Model\Logic;

use Common\Model\CasesModel;

use Common\Enums\XgtCodeEnum;

class CasesLogicModel
{
    const CLASSID_HOME = 1;
    const CLASSID_PUB = 2;
    const CLASSID_ZJGD = 3;

    public function getNewCases($map = [], $pageCount = 20, $order = '', $group = '')
    {
        //公用条件
        $where = [];
        $where['a.on'] = ['eq', 1];
        $where['a.status'] = ['eq', 2];
        $where['a.isdelete'] = ['eq', 1]; //未删除的



        if (!empty($map['fengge'])) {
            $where['a.fengge'] = ['eq', $map['fengge']];
        }
        if (!empty($map['cs'])) {
            $where['a.cs'] = ['eq', $map['cs']];
        }

        /*if (!empty($map['id'])) {
            $where['a.id'] = ['not in', [$map['id']]];
        }*/
        $pageCount += 1; //多查1个，目的为了后面排除自己

        $caseDb = new CasesModel();
        $data = $caseDb->getCasesByMap($where,$pageCount,'a.id,a.title,a.cs');
        // 多查1个后需要排除1个
        $removeSelfEd = false;
        foreach ($data as $key => $value) {
            if ($value['id'] == $map['id']) {
                // 排除自己
                unset($data[$key]);
                $removeSelfEd = true;
            }
        }
        if (!$removeSelfEd && count($data) == $pageCount ) {
            // 没有自己排除最后1个
            array_pop($data);
        }
        return $data;
    }

    public function getCasesByTagname($page = 1, $size = 10 ,$tagName,$cityCode,$classid)
    {
        $field = ['c.title','c.id','ci.img_path','ci.img_host'];
        return D('Common/Cases')->getCasesByTagname($page, $size ,$tagName,$cityCode,$classid,$field);
    }

    public function getCaseShow($id){
        $model = new CasesModel();
        $result  = $model->getCaseShow($id);
        if($result['is_show'] == 1){
            return true;
        }else{
            return false;
        }
    }

    // 获取筛选项
    public function getFilterInfo(){
        $xiaoguotuInfo = S('Cache:Xiaoguotu:Filter:Logic:xgt');
        if(empty($xiaoguotuInfo)){
            // 获取户型
            $hx = D("Common/Huxing")->gethx();
            $top = [
                "id"=>"",
                "type"=>"hx",
                "name" =>"不限",
                'nofollow' => 'rel="external nofollow"',
            ];
            array_unshift($hx,$top);
            $xiaoguotuInfo["hx"] = $hx;

            // 获取装修风格列表
            $fg = D("Common/Fengge")->getfg();
            $top = [
                "id"=>"",
                "type"=>"fengge",
                "name" =>"不限",
                'nofollow' => 'rel="external nofollow"',
            ];
            array_unshift($fg, $top);
            $xiaoguotuInfo["fenge"] = $fg;

            // 获取造价
            $jiage = D("Common/Jiage")->getJiage();
            $top = [
                "id"=>"",
                "type"=>"zaojia",
                "name" =>"不限",
                'nofollow' => 'rel="external nofollow"',
            ];

            array_pop($jiage);
            array_unshift($jiage,$top);
            $xiaoguotuInfo["jiage"] = $jiage;

            // 获取类型
            $leixing = D("Common/Leixing")->getlx();
            $top = [
                "id"=>"",
                "type"=>"leixing",
                "name" =>"不限",
                'nofollow' => 'rel="external nofollow"',
            ];
            array_unshift($leixing,$top);
            //删除无用的最后三个选项
            array_pop($leixing);
            array_pop($leixing);
            array_pop($leixing);

            foreach ($leixing as $key => $value) {
                $value["name"] = str_replace("装修", "", $value["name"]);
                $leixing[$key] = $value;
            }
            $xiaoguotuInfo["leixing"] = $leixing;

            // 面积
            $mianji = XgtCodeEnum::getMianji();
            $top = [
                "id"=>"",
                "type"=>"mianji",
                "name" =>"不限",
                'nofollow' => 'rel="external nofollow"',
            ];
            array_unshift($mianji, $top);
            $xiaoguotuInfo["mianji"] = $mianji;

            //获取选项卡
            $tabs = [
                ["id"=>static::CLASSID_HOME,"name"=>'家装案例', 'link' => 'type=1'],
                ["id"=>static::CLASSID_PUB,"name"=>'公装案例', 'link' => 'type=2'],
                ["id"=>static::CLASSID_ZJGD,"name"=>'在建工地', 'link' => 'type=3'],
            ];
            $xiaoguotuInfo["tabs"] = $tabs;

            S('Cache:Xiaoguotu:Filter:Logic:xgt', $xiaoguotuInfo, 3600 * 24);
        }

        return $xiaoguotuInfo;
    }

    public function setFilterParam($xiaoguotuInfo, $input){
        $params = array(
            "type" => $input["type"],
            "hx" => $input["hx"],
            "lx" => $input["lx"],
            "mj" => $input["mj"],
            "fg" => $input["fg"],
            "jg" => $input["jg"],
        );

        //家装选择项
        $xiaoguotuInfo["hx"] = $this->getParams2("hx",$params,$xiaoguotuInfo["hx"],1,$params["hx"]);
        $xiaoguotuInfo["fenge"] = $this->getParams2("fg",$params,$xiaoguotuInfo["fenge"],1,$params["fg"]);
        $xiaoguotuInfo["jiage"] = $this->getParams2("jg",$params,$xiaoguotuInfo["jiage"],1,$params["jg"]);
        $xiaoguotuInfo["mianji"] = $this->getParams2("mj",$params,$xiaoguotuInfo["mianji"],1,$params["mj"]);

        //公装选择项
        $xiaoguotuInfo["gzleixing"] = $this->getParams2("lx",$params,$xiaoguotuInfo["leixing"],2,$params["lx"]);
        $xiaoguotuInfo["gzfenge"] = $this->getParams2("fg",$params,$xiaoguotuInfo["fenge"],2,$params["fg"]);
        $xiaoguotuInfo["gzjiage"] = $this->getParams2("jg",$params,$xiaoguotuInfo["jiage"],2,$params["jg"]);
        $xiaoguotuInfo["gzmianji"] = $this->getParams2("mj",$params,$xiaoguotuInfo["mianji"],2,$params["mj"]);

        //在建工地选择项
        $xiaoguotuInfo["zjhx"] = $this->getParams2("hx",$params,$xiaoguotuInfo["hx"],3,$params["hx"]);
        $xiaoguotuInfo["zjfenge"] = $this->getParams2("fg",$params,$xiaoguotuInfo["fenge"],3,$params["fg"]);
        $xiaoguotuInfo["zjjiage"] = $this->getParams2("jg",$params,$xiaoguotuInfo["jiage"],3,$params["jg"]);
        $xiaoguotuInfo["zjmianji"] = $this->getParams2("mj",$params,$xiaoguotuInfo["mianji"],3,$params["mj"]);

        return $xiaoguotuInfo;
    }

    //
    private function getParams2($prefix, $params, $data, $type, $val){
        if ($type == static::CLASSID_PUB) {
            unset($params["hx"]);
        } else {
            unset($params["lx"]);
        }

        foreach ($data as $key => $item) {
            $params["type"] = $type;
            $params[$prefix] = $item["id"];

            $param = array_filter($params);
            $data[$key]["link"] = http_build_query($param);
            $data[$key]["checked"] = 0;
            if ($val == $item["id"]) {
                $data[$key]["checked"] = 1;
                if(!empty($item['id']) && !empty($item['name'])){
                    $this->selectType[$prefix] = $data[$key];
                }
            }
        }

        return $data;
    }

    // 获取主数据
    public function getListMaininfo($list){
        if (!empty($list)) {
            $caseIds = array_column($list, "id");
            if (count($caseIds)) {
                $casesModel = new CasesModel();
                $caseList = $casesModel->getMaininfoByIds($caseIds);
                $caseList = array_column($caseList, null, "id");

                foreach ($list as $key => $item) {
                    $maininfo = [];
                    if (array_key_exists($item["id"], $caseList)) {
                        $maininfo = $caseList[$item["id"]];
                    }

                    $list[$key]["hx_lx_name"] = $item["huxing_name"];
                    if ($maininfo["classid"] == static::CLASSID_PUB) {
                        $list[$key]["hx_lx_name"] = $item["leixing_name"];
                    }

                    $list[$key]["title_full"] = sprintf("%s%s装修设计案例", $maininfo["cname"], $item["title"]);
                    $list[$key]["maininfo"] = $maininfo;
                    unset($maininfo);
                }
            }
        }

        return $list;
    }
}
