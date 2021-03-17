<?php
/**
 * 配置表
 */
namespace Common\Model;
use Think\Model;
class OptionsModel extends Model{
    // protected $connection = 'QIZUANG_CONFIG';
    // protected $trueTableName  = 'sq_qizuang.qz_options'; //数据库名.表名(包含了前缀)
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    protected $tableName = "options";
    private $Optionslist = array();

    /**
     * [getOption 取网站配置项]
     * @param  str   $status 对应数据库autoload字段 启用还是禁用 yes是启用 no是禁用
     * @param  [str] $group [分组]
     * @return [array]        [结果集]
     */
    public function getOption($status="",$group="") {
        $optionslist = array();
        $optionslist = S('Cache:Optionslist');

        if (!empty($optionslist)) { //如果已经缓存
            $this->Optionslist = S('Cache:Optionslist');
        }else{ //如果没有缓存,查询所有后再缓存
            $optionslist = $this->db(1,$this->connection)->table("qz_options")->select();
            S('Cache:Optionslist',$optionslist,900);
            $this->Optionslist = S('Cache:Optionslist');
        }

        if (!empty($status) || !empty($group)) { //如果传入了status 或者  group.从已经缓存的全部数据中操作

            //如果只传入了status
            if (!empty($status) && empty($group)) {
                $responseyes = array();
                foreach ($this->Optionslist as $key => $value) { //遍历所有结果集
                    if ($status == $value['autoload']) {
                        $responseyes[] = $value;
                    }
                }
                return $responseyes;
            }

            //如果传入了status group
            if (!empty($status) && !empty($group)) {
                $responsegy = array();
                foreach ($this->Optionslist as $key => $value) { //遍历所有结果集
                    if ($status == $value['autoload'] && $group == $value['option_group']) {
                        $responsegy[] = $value;
                    }
                }

                return $responsegy;
            }

        }else{  //如果什么参数都没传入返回所有缓存结果集
            return $this->Optionslist;

        }

        return $this->Optionslist;
    }

    /**
     * [getOptionName 获取某一项的配置值]
     * @param  [str] $name [单独的配置项目名称]
     * @param  str   $status 对应数据库autoload字段 启用还是禁用 yes是启用 no是禁用
     * @return [array]       [单独一项的表一行记录]
     */
    public function getOptionName($name,$status) {
        if (!empty($name)) {
            $listall =  $this->getOption();
            foreach ($listall as $key => $value) {
                if (!empty($name) && empty($status)) {
                    if ($name == $value['option_name']) {
                        return $listall[$key];
                    }
                }else if (!empty($name) && !empty($status)) {
                    if ($name == $value['option_name'] && $status == $value['autoload']) {
                        return $listall[$key];
                    }
                }

            }
        }else{
            return array();
        }
    }

    /**
     * [getOptionNameCC 获取某一项的配置值 是否获取缓存可控制]
     * @param  [str] $name [单独的配置项目名称]
     * @param  [str] $CC  [缓存控制开关,1获取缓存,0不获取缓存]
     * @return [array]       [单独一项的表一行记录]
     */
    public function getOptionNameCC($name, $CC=0)
    {
        if (!empty($name)) {
            //缓存
            $optionone = S('C:OP:NV:'.$name);

            //如果缓存存在 且 要取缓存
            if (!empty($optionone) && 1 == $CC) {
                return $optionone;
            }

            //如果缓存不存在 或者 不需要取缓存
            if (empty($optionone) || 0 == $CC) {
                $map = array(
                        'option_name' => array('eq', $name)
                         );
                $optionone = M('options')->where($map)->find();
                //去掉备注信息
                unset($optionone['option_remark']);
                //缓存
                S('C:OP:NV:'.$name, $optionone, 60 * 5);
                return $optionone;
            }
        }else{
            return array();
        }
    }

    /**
     * [setOptionNameValue 设置option的值]
     * @param [type] $data name=>value值
     */
    public  function setOptionNameValue($data)
    {
        if (empty($data)) {
            return array();
        }
        $status = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $optiononesave = M('options')->where(array('option_name'=> array('eq', $key)))
                                 ->save(array('option_value'=> $value));
                $status[$key] = $optiononesave;
            }
            return $status;
        }
        return array();
    }

    /**
     * [getOptionAdminName 查询特例用户登录名]
     * @param [type] $data id值
     * $type = 1根据ID查user ,$type = 2根据user查ID
     */
    public function getOptionAdminName($type,$data)
    {
        if($type == 1){
            $map['id']    = array('in',$data);
            $names  = M("adminuser")->where($map)->field("user")->select();
            foreach ($names as $k => $v) {
                $arr[] = $v['user'];
            }
        }else{
            $map['user']    = array('in',$data);
            $names  = M("adminuser")->where($map)->field("id")->select();
            foreach ($names as $k => $v) {
                $arr[] = $v['id'];
            }
        }

        $names = implode(',', $arr);
        return $names;
    }

    /**
     * 通过后台用户ID查询 用户自定义电话呼叫系统提供商
     */
    public function getMyTelCenter_ChannelByid($id)
    {
        $TelCenter_Channel     = $this->getOptionNameCC('TelCenter_Channel')['option_value'];
        $TelCenter_Diy_id      = $this->getOptionNameCC('TelCenter_Diy_id')['option_value'];
        $TelCenter_Diy_idArr   = explode(',', trim($TelCenter_Diy_id,','));
        $TelCenter_Diy_Channel = $this->getOptionNameCC('TelCenter_Diy_Channel')['option_value'];

        if (in_array($id, $TelCenter_Diy_idArr)) {
            //如果用户自定义了电话呼叫系统提供商
            $TelCenter_Channel = $TelCenter_Diy_Channel;
        }

        return $TelCenter_Channel;
    }

    /**
     * 通过 电话呼叫提供商 获取完整的信息
     * @param array
     */
    public function getTelCenter_ChannelInfoByChannel($TelCenter_Channel)
    {
        $TelCenter_Channel_INFO['TelCenter_Channel'] = $TelCenter_Channel;
        switch ($TelCenter_Channel) {
            case 'ytx':
                $TelCenter_Channel_INFO['TelCenter_Channel_name']='ytx(容联云通讯) 已不可用';
                $TelCenter_Channel_INFO['solutions'] = 'yuntongxun';
                break;
            case 'cuct';
                $TelCenter_Channel_INFO['TelCenter_Channel_name']='cuct(联通云总机)';
                $TelCenter_Channel_INFO['solutions'] = 'cuct';
                break;
            case 'holly';
                $TelCenter_Channel_INFO['TelCenter_Channel_name']='holly';
                $TelCenter_Channel_INFO['solutions'] = 'holly';
                break;
            default:
                $TelCenter_Channel_INFO['TelCenter_Channel_name'] ='系统未定义的 电话呼叫系统提供商';
                $TelCenter_Channel_INFO['solutions']              = '';
                break;
        }
        return $TelCenter_Channel_INFO;
    }

}