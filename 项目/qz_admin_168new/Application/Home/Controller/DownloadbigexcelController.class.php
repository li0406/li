<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class DownloadbigexcelController extends HomeBaseController{
    public function index(){
        $this->_error("访问错误,没有该文件！");
    }

    /**
     * 下载EXCEL文件,支持上万行数据
     * @param  array  $list  数据row
     * @param  string $sheetName  sheet名
     * @return mixed  下载excel
     */
    public function downloadBigExcel($list, $sheetName='Sheet1', $title='新建excel文件')
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {

          $writer = new \XLSXWriter();
          foreach ($list as $key => $value) {
              $wArr = array_values($value);
              $writer->writeSheetRow($sheetName, $value);
          }
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
          header("Content-Type:application/force-download");
          header("Content-Type:application/vnd.ms-execl");
          header("Content-Type:application/octet-stream");
          header("Content-Type:application/download");;
          header('Content-Disposition:attachment;filename="'.$title.'.xlsx"');
          header("Content-Transfer-Encoding:binary");
          $writer->writeToStdOut("php://output");
          //echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";
        }catch (Exception $e){
          if($_SESSION["uc_userinfo"]["uid"] == 1){
            var_dump($e);
          }
        }

    }

}