<?php
/**
 * 配置表
 */
namespace Common\Model;
use Think\Model;
class OptionsModel extends Model
{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    private $Optionslist       = array();

    /**
     * 添加配置
     */
    public function addOption($data)
    {
        return M("options")->add($data);
    }

    /**
     * [getOption 取全部的网站配置项 ]
     * // 数据量过大,慎重使用!
     * @param  str   $status 对应数据库autoload字段 启用还是禁用 yes是启用 no是禁用
     * @param  [str] $group [分组]
     * @return [array]        [结果集]
     */
    public function getOption($status="",$group="")
    {
        $optionslist = array();
        $optionslist = S('Cache:Op:All');

        if (!empty($optionslist)) { //如果已经缓存
            $this->Optionslist = S('Cache:Op:All');
        }else{ //如果没有缓存,查询所有后再缓存
            $optionslist = M('options')->select();
            S('Cache:Op:All',$optionslist,900);
            $this->Optionslist = S('Cache:Op:All');
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
     * [getOptionName 获取某一项的配置值(带缓存)]
     * @param  str   $name [单独的配置项目名称]
     * @param  str   $status 对应数据库autoload字段 启用还是禁用 yes是启用 no是禁用
     * @return [array]       [单独一项的表一行记录]
     */
    public function getOptionName($name, $status="yes")
    {
        if (!empty($name)) {
            $optionName = SL2('C:OP:N:'.$name); //先取二级缓存
            if ($optionName) {
                return $optionName;
            }else{
                $optionName = S('C:OP:N:'.$name); //再取一级缓存
                if ($optionName) {
                    return $optionName;
                } else {
                    $optionName = $this->getOptionNameBySql($name);
                    S('C:OP:N:'.$name, $optionName); //一级缓存缓存
                    return $optionName;
                }
            }
        }else{
            return array();
        }

    }


    /**
     * [getOptionNameBySql 数据库查询获取某一项的配置值]
     * @param  str   $name [单独的配置项目名称]
     * @return [array]       [单独一项的表一行记录]
     */
    public function getOptionNameBySql($name)
    {
        if (!empty($name)) {
            $map                = array();
            $map['option_name'] = array('EQ', $name);
            return M('options')->field('option_name,option_value,option_group,autoload')->where($map)->find();
        }
        return false;
    }

    /**
     * [getOptionNoCache 不使用缓存获取op值]
     * @return [type] [description]
     */
    public function getOptionNoCache($name){
        $map = array('option_name' => $name);
        $result = M('options')->where($map)->find();
        return $result;
    }

}