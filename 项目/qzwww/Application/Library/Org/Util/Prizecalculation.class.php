<?php
class Prizecalculation {
    private $prize_arr = array();
    /**
     * 设置奖品 按照特定的方式设置
     * @param [type] $data [description]
     * @example $data = array(
     * '0' => array('id'=>1,'min'=>1,'max'=>29,'prize'=>'一等奖','v'=>1),
     * '1' => array('id'=>2,'min'=>302,'max'=>328,'prize'=>'二等奖','v'=>2),
     * '2' => array('id'=>3,'min'=>242,'max'=>268,'prize'=>'三等奖','v'=>5),
     * '3' => array('id'=>4,'min'=>182,'max'=>208,'prize'=>'四等奖','v'=>7),
     * '4' => array('id'=>5,'min'=>122,'max'=>148,'prize'=>'五等奖','v'=>10),
     * '5' => array('id'=>6,'min'=>62,'max'=>88,'prize'=>'六等奖','v'=>25)
     * )
     */
    public function __construct($data){
        $this->prize_arr = $data;
    }

    /**
     * 奖品计算函数
     * @return [type] [description]
     */
    public function culation($type = "dazhuanpan"){
        $prize_arr = $this->prize_arr;
        foreach ($prize_arr as $key => $value) {
            $arr[$value["prizeid"]] = $value["v"];
        }

        $rid = $this->getRand($arr); //根据概率获取奖项id
        $res = $prize_arr[$rid]; //中奖项

        switch ($type) {
            default:
                $res["prize"] =empty($res["prize"])?"未中奖":$res["prize"];
                $result = $res;
                break;
        }
        return $result;
    }

    //获取随机数
    function getRand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
}