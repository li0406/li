<?php
/**
 * Created by PhpStorm.
 * User: wwek
 * Date: 2019/3/25
 * Time: 17:32
 */

namespace Home\Controller;

use Think\Controller;

class SearchEngineController extends Controller
{
    /**
     *
     * 根据域名头返回robots.txt
     *
     */
    public function robots()
    {
        $host =  I("server.HTTP_HOST");
        $hostArr = explode('.',$host);
        $hostDomain = implode('.',array_slice($hostArr,-2));

        if ($hostDomain == C('QZ_YUMING')) $hostPrefix = 'qizuang';
        if ($hostDomain == C('QZ_YUMING_ZWDZJJ')) $hostPrefix = 'zwdzjj';
        if ($hostDomain == C('QZ_YUMING_TAOZUANG')) $hostPrefix = 'taozuang';

        $robotsContent = self::getRobotsFilePath($hostPrefix);
        //处理未获取到的情况
        if (empty($robotsContent)) {
            $robotsContent =
                <<<EOF
User-agent: *
Disallow: /search?keyword=*
Disallow:
EOF;
        }

        header("Content-Type: text/plain");

        echo $robotsContent;

        die();
    }

    /**
     * 获取robots文件内容
     * @param string $hostPrefix
     * @return string | null
     */
    private function getRobotsFilePath($hostPrefix='qizuang') {
        $robotsFilePath = 'robots'. DIRECTORY_SEPARATOR .$hostPrefix. DIRECTORY_SEPARATOR .'robots.txt';
        $robotsContent = @file_get_contents($robotsFilePath);
        return $robotsContent;
    }


}