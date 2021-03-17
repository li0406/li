<?php
namespace Home\Model;

use Think\Model;

class SensitiveWordsModel extends Model{
    protected $autoCheckFields = false;

    /**
     * 添加新的敏感词分类
     * @param  [array]  $data               [类别参数数组]
     * @return [string] $result             [新增的ID]
     */
    public function addType($data){
        $id = M("sensitive_words_type")->add($data);
        return $id;
    }


    /**
     * 编辑敏感词分类
     * @param  [array]  $where              [要修改的ID]
     * @param  [array]  $data               [类别参数数组]
     * @return [string] $result             [受影响行数]
     */
    public function editType($where,$data){
        if($data['status'] == 1){
            $w_data['status'] = 1;
            $w_where["type"] = $where["id"];
            M("sensitive_words")->where($w_where)->save($w_data); 
        }
        $id = M("sensitive_words_type")->where($where)->save($data);
        return $id;
    }

    /**
     * 删除敏感词分类（物理删除，没有备份）
     * @param  [array]  $where              [要删除的ID]
     * @return [string] $result             [受影响行数]
     */
    public function delType($where){
        //删除时，先把当前分类下的敏感词分类改为 未分类  type = 1
        $t_map['name'] = array('EQ','未分类');
        $typeArr = M("sensitive_words_type")->where($t_map)->find();
        $data['type'] = $typeArr['id'];
        $map['type'] = array('EQ',$where['id']);
        $id = M("sensitive_words")->where($map)->save($data);

        $result = M("sensitive_words_type")->where($where)->delete();
        return $result;
    }
    /**
     * 获取所有的敏感词分类
     * @param  [void]
     * @return [array] $result             [分类数组]
     */
    public function getAllTypes($status = null)
    {
        //$where['status'] = 0;//0为可用   1为已经停用
        if($status != null){
            $where['status'] = 0;
        }

        $result = M("sensitive_words_type")->where($where)->select();
        return $result;
    }

    /**
     * 添加新的敏感词
     * @param  [array]  $data               [敏感词数组]
     * @return [string] $result             [新增的ID]
     */
    public function addWords($data){
        //当前$data['words'] 可能是','拼接的字符串，需要处理
        if(!empty($data['words'])){
            $arr = explode(',',$data['words']);
        }
        unset($data['words']);

        foreach ($arr as $v) {
            $data['word'] = $v;
            $id[] = M("sensitive_words")->add($data);
        }
        return $id;
    }

    /**
     * 获取敏感词列表
     * @param  array            $map             查询条件
     * @param  string           $start            页码
     * @param  string           $end           分页长度 
     * @return array            $result          修改结果
     */
    public function getAllWords($map,$start,$end)
    {
        //$where['status'] = 0;//0为可用   1为已经停用
        if(!empty($map['w.word'])){
            $word = $map['w.word'];
        }
        unset($map['w.word']);
        if($map['order'] !== ''){
            $order = substr($map['order'], 0,-1);
        }else{
            $order = 'w.time desc';
        }
        unset($map['order']);

        if(!empty($word)){
            $result = M("sensitive_words")->alias("w")
                                    ->join("qz_sensitive_words_type as t on t.id = w.type and (w.id = '$word' or w.word like '%".$word."%')")
                                    ->field("w.*,t.name as typename,t.id as typeid")
                                    ->where($map)
                                    ->limit($start.",".$end)
                                    ->order($order)
                                    ->select();
        }else{
            $result = M("sensitive_words")->alias("w")
                                    ->join("qz_sensitive_words_type as t on t.id = w.type")
                                    ->field("w.*,t.name as typename,t.id as typeid")
                                    ->where($map)
                                    ->limit($start.",".$end)
                                    ->order($order)
                                    ->select();
        }

        
        return $result;
    }

    /**
     * 获取所有的敏感词数量
     * @param  array   $map                 查询条件数组
     * @return string $result               查询到的条数
     */
    public function getAllWordsCount($map)
    {
        //$where['status'] = 0;//0为可用   1为已经停用
        if(!empty($map['w.word'])){
            $word = $map['w.word'];
        }
        unset($map['w.word']);
        if($map['order'] !== ''){
            $order = substr($map['order'], 0,-1);
        }else{
            $order = 'w.time desc';
        }
        unset($map['order']);
        

        if(!empty($word)){
            $result = M("sensitive_words")->alias("w")
                                    ->join("qz_sensitive_words_type as t on t.id = w.type and (w.id = '$word' or w.word like '%".$word."%')")
                                    ->field("w.*,t.name as typename,t.id as typeid")
                                    ->where($map)
                                    ->order($order)
                                    ->count();
        }else{
            $result = M("sensitive_words")->alias("w")
                                    ->join("qz_sensitive_words_type as t on t.id = w.type")
                                    ->field("w.*,t.name as typename,t.id as typeid")
                                    ->where($map)
                                    ->order($order)
                                    ->count();
        }

        
        return $result;
    }

    /**
     * 删除敏感词（物理删除，没有备份）
     * @param  [array]  $where              [要删除的ID]
     * @return [string] $result             [受影响行数]
     */
    public function delWord($where,$data){
        $result = M("sensitive_words")->where($where)->delete();
        return $result;
    }

    /**
     * 编辑敏感词
     * @param  [array]  $where              [要修改的ID]
     * @param  [array]  $data               [类别参数数组]
     * @return [string] $result             [受影响行数]
     */
    public function editWord($where,$data){
        $id = M("sensitive_words")->where($where)->save($data);
        return $id;
    }

    
}