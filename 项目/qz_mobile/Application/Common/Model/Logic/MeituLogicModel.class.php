<?php
namespace Common\Model\Logic;


use Common\Model\Db\MeituModel;

class MeituLogicModel
{
    public function getFenggeMeitu(){

        $model =new MeituModel();
        $app_env = C('APP_ENV');
        $pictypelist = [];
        if($app_env == 'dev'){
            $pictypelist[0]['id'] = 50191;
            $pictypelist[0]['name'] = 'os'; //欧式
            $pictypelist[1]['id'] = 50443;
            $pictypelist[1]['name'] = 'zs'; //中式
            $pictypelist[2]['id'] = 49378;
            $pictypelist[2]['name'] = 'dzh'; //地中海
            $pictypelist[3]['id'] = 49393;
            $pictypelist[3]['name'] = 'rsjy'; //日式简约
            $pictypelist[4]['id'] = 49979;
            $pictypelist[4]['name'] = 'ty'; //田园
            $pictypelist[5]['id'] = 49682;
            $pictypelist[5]['name'] = 'qt'; //其他
        }else{
            $pictypelist[0]['id'] = 53157;
            $pictypelist[0]['name'] = 'os'; //欧式
            $pictypelist[1]['id'] = 53201;
            $pictypelist[1]['name'] = 'zs'; //中式
            $pictypelist[2]['id'] = 52968;
            $pictypelist[2]['name'] = 'dzh'; //地中海
            $pictypelist[3]['id'] = 3106;
            $pictypelist[3]['name'] = 'rsjy'; //日式简约
            $pictypelist[4]['id'] = 52226;
            $pictypelist[4]['name'] = 'ty'; //田园
            $pictypelist[5]['id'] = 28156;
            $pictypelist[5]['name'] = 'qt'; //其他
        }
        $result2 = [];

        $ids = array_column($pictypelist,'id');

        if(!empty($ids)){
            $scheme_host = getSchemeAndHost();
            $result = $model->getMeituListByIds($ids);;
            $imglist = [];
            foreach($result as $val){
                $val['img_url'] = $scheme_host["scheme_full"].C('QINIU_DOMAIN') . "/" . $val['img_path'];
                $vv = $val['id'];
                unset($val['id']);
                $imglist[$vv][] = $val;
            }
            $result2 = [];
            foreach ($pictypelist as $val){
                $result2[$val['name']] = $imglist[$val['id']];
            }
        }
        return $result2 ? $result2 : [];

    }
}