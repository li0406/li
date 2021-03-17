<?php
namespace User\Controller;
use Think\Controller;
class UeditorController extends Controller{
    public function upload(){
        //import('Library.Org.ueditor.controller',"",".php");
        $action = $_GET['action'];
        $path = dirname(dirname(dirname(__FILE__)))."/Library/Org/ueditor/";
        $configPath =  $path."config.json";
        $opts = array(
                'http'=>array(
                'method'=>"GET",
                'timeout'=>60,
                )
            );
        $context = stream_context_create($opts);
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($configPath,false,$context)), true);

        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $path .= "action_upload.php";
                $result = include_once($path);
                break;

            /* 列出图片 */
            case 'listimage':
                $path .= "action_list.php";
                $result = include_once("action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $path .= "action_list.php";
                $result = include_once("action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $path .= "action_crawler.php";
                $result = include_once("action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;

        }

        die();
    }
}