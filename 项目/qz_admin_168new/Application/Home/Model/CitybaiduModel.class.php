<?php

/**
 * 百度账户城市管理
 */

namespace Home\Model;
use Think\Model;

class CitybaiduModel extends Model {

    protected $autoCheckFields = false;


    /**
     * 获取 所有后台区域
     *
     * @return     array  The quyu list.
     */
    public function getQuyu($map){

        import('Library.Org.Util.App');
        $app = new \App();

        $map['type'] = array('EQ',1);
        $result = M('quyu')->field('cid,cname,little,manager,baidu_account')
                ->where($map)
                ->order('px_abc')
                ->select();

        foreach ($result as $k => $v) {
            //增加首字母大写
            $result[$k]['abc'] = $app->getFirstCharter($v["cname"]);
        }

        return $result;
    }

    /**
     * 获取所有帐号
     * @return [type] [description]
     */
    public function getAccounts($map){
        return M('quyu')->field('baidu_account')
        ->where($map)
        ->group('baidu_account')
        ->select();
    }

    //查询
    public function getAccount($account){
        $map['baidu_account'] = array('EQ',$account);
        return M("quyu")->where($map)->find();
    }

    //编辑
    public function editAccount($data,$ids){
        $map['cid'] = array('IN',$ids);
        return M("quyu")->where($map)->save($data);
    }

    //清空百度帐号
    public function clearAccount($account){
        $map['baidu_account'] = array('EQ',$account);
        $data['baidu_account'] = '';
        return M("quyu")->where($map)->save($data);
    }

    

}