<?php
/**
 *  装修公司申请表 qz_company_application_queue
 */
namespace Common\Model;
use Think\Model;
class Company_application_queueModel extends Model{
    //最后一个参数 验证范围 4 注册验证 5 登录验证
    protected $_validate = array(
        array('user','require','请填写正确的公司名称！',1,"",4),//新增的时候公司名称不能为空
        array('user','','帐号名称已经存在！',1,'unique',4), // 在新增的时候验证name字段是否唯一
        array('cs','require','请选择您所在的省/市',0,'',4),//新增的时候验证公司的城市是否选择
        array('quyu','require','请选择您所在的市/地区',0,'',4),//新增的时候验证公司的区域是否选择
        array('name','require','请填写公司联系人！',1,"",4),//新增的时候手机/邮箱不能为空
        array('tel','require','请填写手机号码！',1,"",4),//新增的时候手机不能为空
        array('mail','require','请填写正确的邮箱！',1,"",4),//新增的时候邮箱不能为空
    );
    /**
     * 添加申请
     */
    public function adds($data){
        return M("company_application_queue")->add($data);
    }
}