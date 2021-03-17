<?php
// +----------------------------------------------------------------------
// | Quote 报价管理
// +----------------------------------------------------------------------
namespace app\index\controller\v1;

use app\common\model\logic\QuoteLogic;
use think\Controller;
use think\facade\Env;

class Quote extends Controller
{
    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function get_city_quote(QuoteLogic $quoteLogic)
    {
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 20, 'intval');
        $get = $this->request->get();
        return $quoteLogic->city_quote_list($get, $p, $psize);
    }

    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function get_other_quote(QuoteLogic $quoteLogic)
    {
        return $quoteLogic->swjerp_quote_list();
    }

    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function add_city_quote(QuoteLogic $quoteLogic)
    {
        $post = $this->request->post();
        $sence = 'app\index\validate\Quote.addcityquote';
        if (($error_msg = $this->validate($post, $sence)) !== true) {
            return sys_response(4000800, $error_msg, []);
        }
        return $quoteLogic->save_city_quote($post, $this->request->param('user'));
    }

    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function add_other_quote(QuoteLogic $quoteLogic)
    {
        $post = $this->request->post();
        $sence = 'app\index\validate\Quote.addotherquote';
        if (($error_msg = $this->validate($post, $sence)) !== true) {
            return sys_response(4000800, $error_msg, []);
        }
        return $quoteLogic->save_swjerp_quote($post, $this->request->param('user'));
    }

    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function find_city_quote(QuoteLogic $quoteLogic)
    {
        $id = $this->request->get('id', 0, 'intval');
        if (empty($id)) {
            return sys_response(4000002, '', []);
        }
        return $quoteLogic->get_city_quote($id,false);
    }

    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function find_other_quote(QuoteLogic $quoteLogic)
    {
        $id = $this->request->get('id', 0, 'intval');
        if (empty($id)) {
            return sys_response(4000002, '', []);
        }
        return $quoteLogic->get_swjerp_quote($id);
    }
    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function get_quote_log(QuoteLogic $quoteLogic)
    {
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 20, 'intval');
        $get = $this->request->get();
        return $quoteLogic->get_quote_log($get, $p, $psize);
    }

    /**
     * @param QuoteLogic $quoteLogic
     * @return array
     */
    public function read_city_quote(QuoteLogic $quoteLogic)
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit','500M');
        $file = $this->request->file('file');
        if (empty($file)){
            return sys_response(4000900, '请上传EXCEL文件', []);
        }

        try {
            $options = [];
            $data = read_execl($file->getInfo('tmp_name'), 0, 29,$options,3);
        } catch (\Exception $e) {
            return sys_response(4000900, $e->getMessage(), []);
        }

        $result = $quoteLogic->import_city_quote($data,$this->request->param('user'));

        if ($result['flag'] !== false) {
            // 先修改临时文件权限
            chmod($file->getInfo('tmp_name'),777);
            // 如果临时文件可写，则删除临时文件
            if (is_writable($file->getInfo('tmp_name')) && is_readable($file->getInfo('tmp_name'))) {
                unlink($file->getInfo('tmp_name'));
            }
            return sys_response(0, '导入成功'.$result['successful_num'].'，失败'.$result['fail_num'], []);
        } else {
            return sys_response(4000900, '', []);
        }
    }

    public function del_city_quote(QuoteLogic $quoteLogic)
    {
        $id = $this->request->post('id', 0, 'intval');
        if (empty($id)) {
            return sys_response(4000002, '', []);
        }
        return $quoteLogic->del_city_quote($id);
    }

    public function del_other_quote(QuoteLogic $quoteLogic)
    {
        $id = $this->request->post('id', 0, 'intval');
        if (empty($id)) {
            return sys_response(4000002, '', []);
        }
        return $quoteLogic->del_swjerp_quote($id);
    }

    public function history_city_quote(QuoteLogic $quoteLogic){
        $p = $this->request->get('p', 1, 'intval');
        $psize = $this->request->get('psize', 2, 'intval');
        $get = $this->request->get();
        return $quoteLogic->get_history_quote_log($get, $p, $psize);
    }
}