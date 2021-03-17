<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 9:04
 *
 * mores json文件
 *
 */

namespace App\Config;

use  Symfony\Component\Filesystem;


class Mores
{

    /**
     *
     * 获取mores配置文件文件列表
     * @param  string $dir 配置文件目录 默认为空,自动目录
     * @param  bool $fullPath 是否包含完整文件路径,默认true
     * @return array|bool 配置文件文件列表
     */
    public function getMoresFiles($dir = "", $fullPath = true)
    {
        if ($dir == "") {
            $moresDir = ROOT_PATH . 'config' . DS . 'mores' . DS;
        } else {
            $moresDir = $dir;
        }

        $file_arr = array();
        //判断目标目录是否是文件夹
        if (is_dir($moresDir)) {
            //打开
            if ($dh = @opendir($moresDir)) {
                //读取
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        if ($fullPath) {
                            $file = $moresDir . $file;
                        }
                        $file_arr[] = $file;
                    }
                }
                //关闭
                closedir($dh);
            }

            return $file_arr;

        } else {
            return false;
        }

    }

    /**
     *
     * 读取多个文件内容
     *
     * @param  array $files 文件路径数组
     * @return array|bool 文件内容数组
     */
    public function getMoresFilesTxt($files)
    {
        if (is_array($files)) {
            $files_txtArr = [];
            foreach ($files as $k => $v) {
                $file_path = $v;
                if (file_exists($file_path)) {
                    $fp = fopen($file_path, "r");
                    $file_txt = fread($fp, filesize($file_path)); //指定读取大小，这里把整个文件内容读取出来
                    $files_txtArr[] = $file_txt;
                }
            }
            //dump($files_txtArr);
            return $files_txtArr;
        } else {
            return false;
        }
    }

    /**
     *
     * 解析json配置文件为数组
     *
     * @param $files_txtArr josn文本文件数组
     * @return array|bool
     */
    public function parseMoresFilesTxt($files_txtArr)
    {
        $MoresArr = [];
        if (is_array($files_txtArr)) {
            foreach ($files_txtArr as $k => $v) {
                $MoresArr[] = json_decode($v, true);
            }
            //dump($MoresArr);
            return $MoresArr;
        } else {
            return false;
        }
    }

}