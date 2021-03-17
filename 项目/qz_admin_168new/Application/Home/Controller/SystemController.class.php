<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class SystemController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->assign('sidebarid','1');
        $this->assign('side_sid','7');
    }

    public function addManageCity()
    {
        //获取部门角色
        // $info['department'] = D("RbacRole")->getDepartmentAndRole();
        $result = D("RbacRole")->getRoleListByDept();
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["id"], $info['department'])) {
                $info['department'][$value["id"]]["name"] = $value["name"];
            }
            $info['department'][$value["id"]]["roles"][] = $value;
        }

        //因为要分配管辖城市，这里的城市是直接城市
        $citys = D("Quyu")->getAllQuyuOnly();
        foreach ($citys as $key => $value) {
            $citys[$key]['cname'] = strtoupper($value['bm']['0']).' '.$value['cname'];
        }
        $info['citys'] = $citys;

        if(!empty($_POST)){
            $uids = array_filter($_POST['uids']);
            $city = implode(',', $_POST['city']);
            $result = D('Adminuser')->addUserCitys($uids,$city);
            if($result){
                S("C:AdminAllCity",null);
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！请联系技术部门','status'=>0));
        }
        $this->assign("info",$info);
        $this->display();
    }


    /**
     * [updateRealVipCity 更新真会员省份城市区域]
     * @return [type] [description]
     */
    public function updateRealVipCity(){
        $string = json_encode(D('Area')->getRealVipProvinceCityAndArea());
        $option = ['type' => 'option','data' => ['name' => 'ALL_REAL_VIP_PCA_JSON','remark' => '真会员省份P城市C区县A']];
        $result = $this->uploadStringToQiniu('common/js/rlpca' . date('YmdHis') . '.js', 'var rlpca = ' . $string,$option);
        $this->ajaxReturn($result);
    }


    /**
     * [uploadStringToQiniu 上传字符串到七牛]
     * @param  string $filename [文件名]
     * @param  string $string   [字符串]
     * @param  array  $option   [其他配置,如更新OP的值 array('type' => 'option' 'data' => ['name' => 'ALL_CITY_JSON'])]
     * @return [type]           [description]
     */
    public function uploadStringToQiniu($filename = '', $string = '', $option = []){
        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);
        $putPolicy->SaveKey = $filename;
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_Put($upToken, null, $string, $putExtra);
        if($err == null){
            $result = ['status' => 1, 'info' => $ret];
            if(!empty($option)){
                switch ($option['type']) {
                    case 'option':
                        if(!empty($option['data']['name'])){
                            $infos = M('options')->where(['option_name' => $option['data']['name']])->find();
                            if(empty($infos)){
                                $data['option_value'] = $filename;
                                $result['code'] = M('options')->add([
                                                          'option_name' => $option['data']['name'],
                                                          'option_group' => 'common',
                                                          'autoload' => 'yes',
                                                          'option_value' => $ret['key'],
                                                          'option_remark' => $option['data']['remark']
                                                          ]);
                            }else{
                                $result['code'] = M('options')->where(['option_name' => $option['data']['name']])->save([
                                                                                              'option_value' => $ret['key']
                                                                                              ]);
                            }
                        }
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }else{
            $result = ['status' => 0, 'info' => $err];
        }
        return $result;
    }
}