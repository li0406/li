<?php
/**
 * 全屋定制移动端
 *
 */
namespace Mobile\Controller;
use Common\Enums\ApiConfig;
use Mobile\Common\Controller\MobileBaseController;
class QwdzController extends MobileBaseController{
    public function index()
    {
        //SEO标题关键字描述
        $basic["head"]["title"] = "家具全屋定制方案 - 全屋定制家具报价 – 齐装网家具城";
        $basic["head"]["keywords"] = "全屋定制方案，全屋定制，全屋定制报价，全屋定制案例";
        $basic["head"]["description"] = "齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制报价方案，4步搞定，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。";
        $this->assign('basic',$basic);

        //定位和已开站城市
        $cityid = $_SESSION['m_mapUseInfo']['id'];

        //根据城市id获取城市是否已开站
        if(isset($cityid) && !empty($cityid)){
            $getreturn = D('Quyu')->getJiaJuOpenCityByid($cityid);
            if($getreturn){     //表示已开站
                $cityopen = 1;
                //获取该城市第一个区，用于显示默认城市
                $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
                $this->assign('info',$info);
                $this->assign('cityid',$cityid);
            }else{
                $cityopen = 2;
            }
        }else{
            $cityopen = 2;
        }
        $this->assign('isopen',$cityopen);
        $this->display();
    }

    /**
     * [baojiaCalculate 家具发单入口 ]
     * @return [type] [description]
     */
    public function qwdzCreateOrder()
    {
        $data = I('post.');
        if($data){
            //发单入库
            if(!empty(trim($data['tel']))){
                $tel =trim($data['tel']);
            }

            if(!empty(trim($data['name']))){
                $name = trim($data['name']);
            }

            if(!empty(trim($data['cs']))){
                $cs= trim($data['cs']);
            }else {
                $this->ajaxReturn(['error_code' => 9001000, "error_msg" => ApiConfig::CODE_9001000]);
            }

            if(!empty(trim($data['qx']))){
                $qx = trim($data['qx']);
            }else{
                $this->ajaxReturn(['error_code' => 9001000,"error_msg"=> ApiConfig::CODE_9001000]);
            }

            if(!empty(trim($data['source']))){
                $source = trim($data['source']);
            }else{
                $this->ajaxReturn(['error_code' => 9001001,"error_msg"=> ApiConfig::CODE_9001001]);
            }

            $resultFd = R('Common/JiajuZbfb/jiaju_boajia',array($tel,'','',$cs,$qx,$source,$name,2));
            $this->ajaxReturn($resultFd);
        }else{
           $this->ajaxReturn(['error_code' => 9000003,"error_msg"=> ApiConfig::CODE_9000003]);
        }

    }
    public function jjqwdzqd()
    {
        $this->display();
    }
    // a.19.2.8 前台m端家具推广落地页复制 （由于家具渠道来源组未添加，临时未用模板方式）
    public function jjqwdzdyjj(){
        $this->assign('convert_id', '1626153518397448');
        $this->display('jjqwdzmj');
    }
    public function jjqwdzjtttjj(){
        $this->assign('convert_id', '1626153653668872');
        $this->display('jjqwdzmj');
    }
    //2019/2/26复制家具设计和报价页面，并分别添加JS代码
    public function jrttjj2()
    {
        //SEO标题关键字描述
        $basic["head"]["title"] = "家具全屋定制方案 - 全屋定制家具报价 – 齐装网家具城";
        $basic["head"]["keywords"] = "全屋定制方案，全屋定制，全屋定制报价，全屋定制案例";
        $basic["head"]["description"] = "齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制报价方案，4步搞定，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。";
        $this->assign('basic',$basic);

        //定位和已开站城市
        $cityid = $_SESSION['m_mapUseInfo']['id'];

        //根据城市id获取城市是否已开站
        if(isset($cityid) && !empty($cityid)){
            $getreturn = D('Quyu')->getJiaJuOpenCityByid($cityid);
            if($getreturn){     //表示已开站
                $cityopen = 1;
                //获取该城市第一个区，用于显示默认城市
                $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
                $this->assign('info',$info);
                $this->assign('cityid',$cityid);
            }else{
                $cityopen = 2;
            }
        }else{
            $cityopen = 2;
        }
        $this->assign('isopen',$cityopen);
        $this->display();
    }
    public function jjqwdz_dx()
    {
        //SEO标题关键字描述
        $basic["head"]["title"] = "家具全屋定制方案 - 全屋定制家具报价 – 齐装网家具城";
        $basic["head"]["keywords"] = "全屋定制方案，全屋定制，全屋定制报价，全屋定制案例";
        $basic["head"]["description"] = "齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制报价方案，4步搞定，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。";
        $this->assign('basic',$basic);

        //定位和已开站城市
        $cityid = $_SESSION['m_mapUseInfo']['id'];

        //根据城市id获取城市是否已开站
        if(isset($cityid) && !empty($cityid)){
            $getreturn = D('Quyu')->getJiaJuOpenCityByid($cityid);
            if($getreturn){     //表示已开站
                $cityopen = 1;
                //获取该城市第一个区，用于显示默认城市
                $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
                $this->assign('info',$info);
                $this->assign('cityid',$cityid);
            }else{
                $cityopen = 2;
            }
        }else{
            $cityopen = 2;
        }
        $this->assign('isopen',$cityopen);
        $this->display("jjqwdz-dx");
    }

    /**
     * 全屋定制设计主题落地页
     *
     * @return void
     */
    public function jjqwsj()
    {
        $basic['head']['title'] = '家具全屋定制方案 - 全屋定制家具设计 – 齐装网家具城';
        $basic['head']['keywords'] = '全屋定制方案，全屋定制，全屋定制设计，全屋定制案例';
        $basic['head']['description'] = '齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制设计方案，让您快速获得全屋家具灵感，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。';

        $this->assign('basic',$basic);

        //定位和已开站城市
        $cityid = $_SESSION['m_mapUseInfo']['id'];

        //根据城市id获取城市是否已开站
        if(isset($cityid) && !empty($cityid)){
            $getreturn = D('Quyu')->getJiaJuOpenCityByid($cityid);
            if($getreturn){     //表示已开站
                $cityopen = 1;
                //获取该城市第一个区，用于显示默认城市
                $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
                $this->assign('info',$info);
                $this->assign('cityid',$cityid);
            }else{
                $cityopen = 2;
            }
        }else{
            $cityopen = 2;
        }
        $this->assign('isopen',$cityopen);

        $this->display('jjqwsj');
    }
}
