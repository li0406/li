<?php
namespace Org\Util;

class Krpano
{
    private $img_path = "";//图片路径
    private $file_output = "";//输出文件夹路径
    private $runtime = ""; //运行环境 win : windows环境  linux:linux环境

    function __construct($runtime,$img_path)
    {
        $this->img_path = $img_path;
        $this->runtime = $runtime;
    }

    /**
     * 执行切图操作
     * @return string 返回切图文件夹路径
     */
    public function exec()
    {
        switch ($this->runtime) {
            case 'win':
                return $this->exec_win();
                break;
            case 'linux':
                return $this->exec_liunx();
                break;
            default:
                return "Please select the running environment！ (select to win or liunx )";
                break;
        }
    }

    /**
     * 上传图片
     * @param  string $folderPath [文件夹地址]
     * @param  string $subfix     [前缀]
     * @return array
     */
    public function upload_qiniu($folderPath,$subfix)
    {
        if (!is_dir($folderPath)) {
            return "folder is not exist !!";
        }

        $handler = opendir($folderPath);
        while( ($fileName = readdir($handler)) !== false ) {
            if($fileName != "." && $fileName != ".."){
                $file[] = $fileName;
            }
        }

        if (empty($subfix)) {
            $subfix = "threedimensional/$(year)$(mon)$(day)$(hour)$(min)".rand(1,100);
        }

        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);

        foreach ($file as $key => $value) {
            $saveName =  $value;
            $name = substr($saveName,0, strrpos($saveName, '.'));
            if ($saveName == "bg.jpg") {
                $saveName =  $name;
            }
            $filePath = $folderPath.$value;
            $putPolicy = new \Qiniu_RS_PutPolicy($bucket);
            $putPolicy->SaveKey = $subfix."/".$saveName;
            $upToken = $putPolicy->Token(null);
            $putExtra = new \Qiniu_PutExtra();
            $putExtra->Crc32 = 1;
            list($ret, $err) = Qiniu_PutFile($upToken, null, $filePath, $putExtra);
            if($err == null){
                $result[] = array(
                    "hash"=>$ret["hash"],
                    "key"=> $ret["key"],
                    "name" => $name
                );
            }
        }
        //上传成功后删除文件夹
        $this->delDir($folderPath);
        return $result;
    }

    /**
     * window环境
     * @param  string $img_path [图片路径]
     * @return string $folderPath 图片文件夹路径
     */
    private function exec_win()
    {
        //判断文件是否存在
        if (!is_file($this->img_path)) {
            return "file is not exist!";
        }

        $path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

        //判断文件夹
        if (!empty($this->file_output)) {
            $folderPath = "";
        } else {
            $folderPath = $path."/web/assets/krpano/";
        }

        if (!is_dir($folderPath)) {
            mkdir($folderPath,0755);
        }

        //将文件转移到文件夹下面
        if (!empty($_FILES[array_keys($_FILES)[0]]["tmp_name"])) {
            $fileName = $_FILES[array_keys($_FILES)[0]]["name"];
            $newFile = $folderPath.$fileName;
            move_uploaded_file($this->img_path,$newFile);
        } else {
            //获取图片名称
            $fileName = substr($this->img_path, strrpos($this->img_path, '/')+1);
            $newFile = $folderPath.$fileName;
            copy($this->img_path,$newFile);
        }

        $register ='FXsqTqaGNSZER5dSETEm+VzQEh9sWSa5DZMFsSmMxYV9GcXs8W3R8A/mWXrGNUceXvrihmh28hfRF1ivrW0HMzEychPvNiD8B/4/ZzDaUE9Rh6Ig22aKJGDbja1/kYIqmc/VKfItRE2RTSOIbIroxOtsz626NIpxWksAAifwhpNwuPXqDQpz2sRUMBzoPqZktpkItoSenN2mKd8Klfx7pOuB6CIK3e1CDXgyndqOt2mWybLZcU/wfJVAecfxk15ghiqrzaDsbqrdABDowg==';
        $command = $path."/Application/Library/Org/krpano/win/krpanotools64.exe register ".$register;
        exec($command,$result,$output);

        $command = $path."/Application/Library/Org/krpano/win/krpanotools64.exe  makepano -config=templates/normal.config ".$newFile;
        exec($command,$result,$output);

        //获取切图文件
        $folderPath =  substr($newFile, 0,strrpos($newFile, '.'))."/";


        //将原图移动至文件夹下面
        copy($newFile,$folderPath."/bg.jpg");
        //删除原图
        unlink($newFile);
        return $folderPath;
    }

        /**
     * liunx环境
     * @param  string $img_path [图片路径]
     * @return string $folderPath 图片文件夹路径
     */
    private function exec_liunx($img_path)
    {
        //判断文件是否存在
        if (!is_file($this->img_path)) {
            return "file is not exist!";
        }

        $path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

        //判断文件夹
        if (!empty($this->file_output)) {
            $folderPath = "";
        } else {
            $folderPath = $path."/web/assets/krpano/";
        }

        if (!is_dir($folderPath)) {
            mkdir($folderPath,0755);
        }

        //将文件转移到文件夹下面
        if (!empty($_FILES[array_keys($_FILES)[0]]["tmp_name"])) {
            $fileName = $_FILES[array_keys($_FILES)[0]]["name"];
            $newFile = $folderPath.$fileName;
            move_uploaded_file($this->img_path,$newFile);
        } else {
            //获取图片名称
            $fileName = substr($this->img_path, strrpos($this->img_path, '/')+1);
            $newFile = $folderPath.$fileName;
            copy($this->img_path,$newFile);
        }

        $register ='FXsqTqaGNSZER5dSETEm+VzQEh9sWSa5DZMFsSmMxYV9GcXs8W3R8A/mWXrGNUceXvrihmh28hfRF1ivrW0HMzEychPvNiD8B/4/ZzDaUE9Rh6Ig22aKJGDbja1/kYIqmc/VKfItRE2RTSOIbIroxOtsz626NIpxWksAAifwhpNwuPXqDQpz2sRUMBzoPqZktpkItoSenN2mKd8Klfx7pOuB6CIK3e1CDXgyndqOt2mWybLZcU/wfJVAecfxk15ghiqrzaDsbqrdABDowg==';
        $command = "/data/software/Krpano/liunx/krpanotools register ".$register;
        exec($command,$result,$output);

        $command = "/data/software/Krpano/liunx/krpanotools makepano -config=templates/normal.config ".$newFile;
        exec($command,$result,$output);

        //获取切图文件
        $folderPath =  substr($newFile, 0,strrpos($newFile, '.'))."/";

        //将原图移动至文件夹下面
        copy($newFile,$folderPath."/bg.jpg");
        //删除原图
        unlink($newFile);
        return $folderPath;
    }

    /**
     * 删除文件夹
     * @param  [type] $dir [description]
     * @return [type]      [description]
     */
    private function delDir($dir){
        if(!is_dir($dir)){
            echo "文件夹{$dir}不存在！";
            return 0;
        }

        $handle = dir($dir);
        while(false!== ($entry=$handle->read())){
            if(($entry!=".")&&($entry!="..")){
                if(is_file($dir.$entry)){
                    unlink($dir.$entry);
                }else{
                   $this->delDir($dir.$entry);
                }
            }
        }
        $handle->close();
        rmdir($dir);
    }
}
