<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 11:43
 */

namespace App\Copy;

use App\Config\Mores;
use Symfony\Component\Filesystem;
use think\template;

class CopyDo
{

    /**
     *
     * 复制小程序
     *
     * @return array
     */
    public function copyMores()
    {
        $Mores = new Mores();
        $files = $Mores->getMoresFiles();
        $files_txt = $Mores->getMoresFilesTxt($files);
        $MoresArr = $Mores->parseMoresFilesTxt($files_txt);

        $Filesystem = new  Filesystem\Filesystem();

        $targetDirArr = [];
        foreach ($MoresArr as $k => $v) {
            $originDir = ROOT_PATH . $v['origin']['origin_dir'];
            $targetDir = ROOT_PATH . $v['target']['target_dir'];
            $appConfigTempDir = ROOT_PATH . 'config' . DS . 'mores_temp' . DS;
            foreach ($v['target']['mores'] as $km => $vm) {
                $appTargetdir = $targetDir . $vm['appdir'] . DS;
                $targetDirArr[] = $appTargetdir . " " . $vm['config']['projectname'];
                //dump($originDir);
                //dump($appTargetdir);
                //复制程序目录
                $options = [];
                $options['override'] = true;
                $Filesystem->mirror($originDir, $appTargetdir, null, $options);

                //修改配置
                foreach ($v['target']['config_temp'] as $kct => $vct) {
                    //config.js
                    $nowck =  key($vct);
                    if ($nowck == 'config.js') {
                        $appConfigFile = $appTargetdir . $nowck;
                        $appConfigTempFile = $appConfigTempDir . $vct[$nowck];
                        $nowAppconfig = self::configMake($appConfigTempFile, $vm['config']);
                        //写入配置文件
                        file_put_contents($appConfigFile, $nowAppconfig);
                    }

                    //project.config.json
                    if ($nowck == 'project.config.json') {
                        $appProjectConfigFile = $appTargetdir . $nowck;
                        $appConfigTempFile = $appConfigTempDir . $vct[$nowck];
                        $nowAppconfig = self::configMake($appConfigTempFile, $vm['config']);
                        //写入配置文件
                        file_put_contents($appProjectConfigFile, $nowAppconfig);
                    }

                }

            }
        }

        return $targetDirArr;

    }

    public function configMake($temp, $data)
    {
        // 设置模板引擎参数
        $config = [
            //'view_path'	=>	'',
            'cache_path'  => './runtime/',
            'view_suffix' => 'tpl',
        ];

        $template = new Template($config);
        // 模板变量赋值
        $template->assign('config', $data);
        // 读取模板文件渲染输出
        ob_start();
        $template->fetch($temp);
        $nowAppconfig = ob_get_contents();
        ob_end_clean();
        return $nowAppconfig;
    }


}