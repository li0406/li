<?php
/**
 *计算日期差
 * author: mcj
 */
if (!function_exists('day_diff')) {
    function day_diff($date1, $date2)
    {
        $datetime1 = date_create($date1);
        $datetime2 = date_create($date2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format('%R%a');
    }
}

/**
 * 检查密码强度
 * @Author   lovenLiu
 * @DateTime 2019-05-09T17:55:58+0800
 */
if(!function_exists('check_pwd')) {
    function check_pwd($password = ""){
        //检查密码是纯数字或者纯字母
        $reg  = '/^(?![^A-Za-z]+$)(?![^0-9]+$)[\x21-\x7e]{6,18}$/';
        preg_match($reg,$password,$m);

        if (count($m) == 0) {
            return false;
        }
        return true;
    }
}

