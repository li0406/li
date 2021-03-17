<?php

namespace Mobile\Controller;

use Mobile\Common\Controller\MobileBaseController;

class TuiController extends MobileBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 推广页面动态生成
     * 根据数据库配置OrderSourceManage表中动态读取参数，拼接成完整页面
     */
    public function index()
    {
        $templeteArray = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
        if (empty($templeteArray)) {
            $this->_empty();
            die();
        }
        if (empty($templeteArray[0]) || empty($templeteArray[1])) {
            $this->_empty();
            die();
        }
        $templete = $templeteArray[0];
        if (stripos($templeteArray[1], '?') === false) {
            $path = $templeteArray[1];
        } else {
            $path = explode('?', $templeteArray[1])[0];
        }
        $src = I('get.src', '');
        $map['type'] = 2; //类型 1为PC 2为M端
        $map['templete'] = $templete; //模板
        $map['path'] = $path; //路径
        //$map['src'] = $src;
        $map['status'] = 1; //选择已经启用的
        $sourceInfoCK = 'C:M:tui:dt:'.md5(json_encode($map));
        $sourceInfo = S($sourceInfoCK);
        if (empty($sourceInfo)) {
            $sourceInfo = D('OrderSourceManage')->getInfoByMap($map);
            S($sourceInfoCK, $sourceInfo, 60 * 5);
        }
        if (empty($sourceInfo)) {
            $this->_empty();
            die();
        }
        //获取代码赋值给前端
        $this->assign('base_code', $sourceInfo['base_code']);
        $this->assign('js_code', $sourceInfo['js_code']);
        $this->assign("src", $src);
        if(!empty($_GET['src'])){
            cookie('baojia_first_url', $_SERVER['REQUEST_URI']);
        }else{
            cookie('baojia_first_url', '');
        }
        switch ($templete) {
            case 'sheji':
                session("m_redirect", $this->SCHEME_HOST['scheme_full'].'m.' . C('QZ_YUMING') . '/shejidone/');
                session("m_wanshan_tmp", null);
                //传入source，没有传入则默认为302(即本页)
                $laiyuan = $_GET['fi'];
                if (empty($laiyuan)) {
                    $source['top'] = 302;
                    $source['bottom'] = 301;
                } else {
                    $source['top'] = $laiyuan;
                    $source['bottom'] = $laiyuan;
                }
                $this->assign('source', $source);

                //SEO标题关键字描述
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'baojia10':
                session("m_redirect", $this->SCHEME_HOST['scheme_full'].'m.' . C('QZ_YUMING') . '/details/');
                session("m_wanshan_tmp", 'wanshan');
                //传入source，没有传入则默认为311(即本页)
                $source = $_GET['fi'];
                if (empty($source)) {
                    $source = 311;
                }
                $this->assign('source', $source);

                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";

                break;
            case 'zhaobiao':
                session("m_wanshan_tmp", 'wanshan');
                session("m_redirect", $this->SCHEME_HOST['scheme_full'].'m.' . C('QZ_YUMING') . '/shejidone/');
                $safe = getSafeCode();
                $this->assign("safecode", $safe["safecode"]);
                $this->assign("safekey", $safe["safekey"]);
                $this->assign("ssid", $safe["ssid"]);
                //如果有城市
                $cityInfo = $this->cityInfo;
                if ($cityInfo) {
                    $this->assign('cid', $cityInfo['id']); //城市id
                }
                //SEO标题关键字描述
                $basic["head"]["title"] = "装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                //获取该城市第一个区，用于显示默认城市
                $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
                break;
            case 'liangfang':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修免费量房_免费量房设计_免费量房报价-齐装网";
                $basic["head"]["keywords"] = "免费量房,免费量房设计,免费量房报价";
                $basic["head"]["description"] = "齐装网开启“安心量房”工程，每天提供300个免费量房设计报价名额，拒绝量房猫腻和量房陷阱，抵制隐形消费，让业主不花冤枉钱。齐装网服务1500万业主，轻松装修乐无忧从免费量房开始。";
                break;
            case 'baojia2':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'baojia3':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修全包套餐报价 - 齐装网";
                $basic["head"]["keywords"] = "装修全包套餐报价";
                $basic["head"]["description"] = "齐装网为装修业主提供多种装修全包套餐价格优惠，全包装修价格最低仅需569元每平米，包设计、包量房、包主材、包装修，更有六大装修保障全方面保障您的权益。装修特惠季快来齐装网享受装修全包套餐报价优惠吧。";
                break;
            case 'newbaojia':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'baojia11':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'baojia1-jzrk':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'baojia-zst':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'baojia4':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'qdsj1':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'qdsj1-xcx':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'sheji-jzrk':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'sheji-xcx':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'sheji-dyqd':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'sheji-jzrk-shxs':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'sheji-dyqd-2':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'baojia-jzrk':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'jjqwdzqd':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "家具全屋定制方案 - 全屋定制家具报价 – 齐装网家具城";
                $basic["head"]["keywords"] = "全屋定制方案，全屋定制，全屋定制报价，全屋定制案例";
                $basic["head"]["description"] = "齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制报价方案，4步搞定，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。";
                break;
            case 'jjqwsj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'jjqwbj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "家具全屋定制方案 - 全屋定制家具报价 – 齐装网家具城";
                $basic["head"]["keywords"] = "全屋定制方案，全屋定制，全屋定制报价，全屋定制案例";
                $basic["head"]["description"] = "齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制报价方案，4步搞定，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。";
                break;
            case 'qwdzbj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "家具全屋定制方案 - 全屋定制家具报价 – 齐装网家具城";
                $basic["head"]["keywords"] = "全屋定制方案，全屋定制，全屋定制报价，全屋定制案例";
                $basic["head"]["description"] = "齐装网家具城全屋定制栏目免费为您提供4套家具全屋定制报价方案，4步搞定，减少您各种不要的担忧，早定早享受，覆盖全品类家具，流行时尚都有，齐装网家具城让您不多花钱，还能获得质量管控和完整的服务。";
                break;
            case 'zhaoshang':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修公司入驻招商 - 齐装网";
                $basic["head"]["keywords"] = "装修公司入驻招商，装修公司入驻平台";
                $basic["head"]["description"] = "齐装网互联网装修平台诚邀各地装修公司入驻，加入齐装网，装修公司即可获得全网营销方案、稳定的订单量、独立企业网店、免费装企培训课程、精准营销推广及完善的售后服务。现在申请，立即开始接单盈利。";
                break;
            case 'gzsjbj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "公装设计报价 - 靠谱的公装公司推荐 - 齐装网";
                $basic["head"]["keywords"] = "公装设计，公装报价，公装公司，公装设计报价";
                $basic["head"]["description"] = "齐装网公装设计报价栏目提供办公室、写字楼、厂房、商铺、门面房、餐饮、酒店等各类公装案例及报价清单，简单三步即可轻松找到靠谱的公装公司，更可免费领取4份公装设计报价！找公装，来齐装。";
                break;
            case 'mjsj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "0元抢装修设计方案 - 免费获取装修报价 - 齐装网";
                $basic["head"]["keywords"] = "装修设计方案，装修报价";
                $basic["head"]["description"] = "想准确知道装修预算，快来抢免费装修设计方案吧，装修提前准备，弄清预算再开搞。业主可获得装修公司免费上门量房，免费设计报价，更可尊享齐装网六大装修保障，让您轻松装修乐无忧";
                break;
            case 'mjsj1':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "0元抢装修设计方案 - 免费获取装修报价 - 齐装网";
                $basic["head"]["keywords"] = "装修设计方案，装修报价";
                $basic["head"]["description"] = "想准确知道装修预算，快来抢免费装修设计方案吧，装修提前准备，弄清预算再开搞。业主可获得装修公司免费上门量房，免费设计报价，更可尊享齐装网六大装修保障，让您轻松装修乐无忧";
                break;
            case 'baojiazk':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                $templete = "zk";
                break;
            case 'qzjzj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "齐装家装季7800元限量红包";
                $basic["head"]["keywords"] = "装修报价，量房设计，设计师1V1";
                $basic["head"]["description"] = "齐装装修季，7800元超值家装福利送不停，装修弄清预算再开搞，免费4套设计方案满足你对家的所有想象。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。";
                break;
            case 'mthd':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "0元抢装修设计方案 - 免费获取装修报价 - 齐装网";
                $basic["head"]["keywords"] = "装修设计方案，装修报价";
                $basic["head"]["description"] = "想准确知道装修预算，快来抢免费装修设计方案吧，装修提前准备，弄清预算再开搞。业主可获得装修公司免费上门量房，免费设计报价，更可尊享齐装网六大装修保障，让您轻松装修乐无忧";
                break;
            case 'lyzxhd':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修优惠券 - 装修礼券 - 装修代金券免费领取 - 齐装网";
                $basic["head"]["keywords"] = "装修优惠券,装修礼券,装修代金券";
                $basic["head"]["description"] = "齐装网礼券中心提供多种装修优惠券，可抵平面设计费用、上门量房费用、预算报价费用！还有更多冰点价格全包套餐限时限量预定！";
                break;
            case 'baojia-ywwx':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'baojia':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'baojia1':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'zxbj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'xhxsj':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                break;
            case 'baojia13':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'baojia14':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'baojia15':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'zxbaojia1':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,装小七";
                $basic["head"]["description"] = "装小七是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'zxbaojia2':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-装小七";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "装小七装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'zxbaojia3':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-装小七";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "装小七装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'baojia16':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价";
                $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
                $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";
                break;
            case 'xbaojia5':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'xbaojia6':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'xbaojia4':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器_家庭装修在线设计-齐装网";
                $basic["head"]["keywords"] = "装修报价计算器,装修在线设计";
                $basic["head"]["description"] = "齐装网装修报价计算器以及家庭装修在线设计频道，为想要装修房子的业主和即将装修的业主提供全方面的报价以及精美的家庭装修设计。";
                break;
            case 'sheji-xy1':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
                $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
                $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";
                $this->assign("FLAG_NAV_ICON", 0);
                break;
            case 'baojiawqz':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器—免费装修报价设计";
                $basic["head"]["keywords"] = "装修报价计算器,免费装修报价，免费装修设计";
                $basic["head"]["description"] = "装修报价计算器轻松提供免费报价，免费装修设计，让您不在为装修而发愁！";
                break;
            case 'shuang11':
                //seo 标题/描述/关键字
                $basic["head"]["title"] = "装修报价计算器—免费装修报价设计";
                $basic["head"]["keywords"] = "装修报价计算器,免费装修报价，免费装修设计";
                $basic["head"]["description"] = "装修报价计算器轻松提供免费报价，免费装修设计，让您不在为装修而发愁！";
                break;
            default:
                $this->_empty();
                die();
                break;
        }
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo', session('m_cityInfo'));

        $city = session('m_cityInfo');
        if (empty($city['name'])) {
            $city['name'] = '';
        }
        $this->assign('basic', $basic);
        $this->display($templete);
    }
}
