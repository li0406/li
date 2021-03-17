<?php

class Weixin {

    //微信服务常用URL地址
    private $url = array(
        'message'     => 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=[access_token]',
        'qrcode'      => 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=[access_token]',
        'qrcode_url'  => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=[ticket]',
        'set_menu'    => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=[access_token]',
        'get_menu'    => 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=[access_token]',
        'delete_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=[access_token]',
    );

    //基本配置信息
    private $config = array(
        'validate_token' => '',
        'app_id' => '',
        'app_secret' => '',
        'encoding_aes_key' => ''
    );

    //access_token配置
    private $access_token = array(
        'url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&',
        'token' => ''
    );

    //带参数二维码配置
    private $ticket_config = array(
        'category' => array(
            'min'    => 1000,
            'max'    => 9999,
            'length' => 4,
        ),
        'key' => array(
            'length_max' => 32,
        )
    );

    /**
     * 初始化
     * @param string $prefix 微信配置前缀，一个前缀代表一个微信项目，配置项示例：WX_CONFIG_PREFIX_APP_ID
     */
    public function __construct($prefix = '') {
        //基本配置信息
        foreach ($this->config as $key => $value) {
            $this->config[$key] = OP('WX_CONFIG_' . strtoupper($prefix) . '_' . strtoupper($key));
        }

        //access_token配置
        $this->access_token['url'] = $this->access_token['url'] . 'appid=' . $this->config['app_id'] . '&secret=' . $this->config['app_secret'];
        $access_token_key = 'WX_CONFIG_' . strtoupper($prefix) . '_ACCESS_TOKEN';
        $cache = S('Class:Weixin:' . $prefix . ':ACCESS_TOKEN');
        //判断过期时间，如果过期重新获取
        if (empty($cache) || ((time() - intval($cache['time']) + 600) > $cache['expires_in'])) {
            $cache = json_decode($this->option($access_token_key), true);
            //判断从数据库中获取的token是否可用
            if (empty($cache) || (time() - intval($cache['time']) + 600) > $cache['expires_in']) {
                $result = $this->get($this->access_token['url']);
                if($result){
                    $info = json_decode($result,true);
                    $cache = array(
                        'time'         => time(),
                        'expires_in'   => $info['expires_in'],
                        'access_token' => $info['access_token']
                    );
                    $this->option($access_token_key, json_encode($cache));
                }
            }
            S('Class:Weixin:' . $prefix . ':ACCESS_TOKEN', $cache);
        }
        $this->access_token['token'] = $cache['access_token'];

        //替换参数
        $search = array(
            '[access_token]',
        );
        $replace = array(
            $this->access_token['token']
        );
        foreach ($this->url as $key => $value) {
            $this->url[$key] = str_replace($search, $replace, $this->url[$key]);
        }
    }

    /**
     * 配置接口时候验证
     */
    public function validate()
    {
        $echoStr   = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];
        $tmpArr    = array($this->config['validate_token'], $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr    = implode($tmpArr);
        $tmpStr    = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
        }
        exit();
    }

   /**
    * 创建二维码ticket
    * @param  integer $category 分类
    * @param  integer $key      ID
    * @param  integer $type     1:临时二维码；2:永久二维码(此时expire参数无效)
    * @param  integer $expire   过期时间
    * @return array
    */
    public function getTicket($scene_str = '', $type = 1, $expire = 2592000) {
        //判断是临时二维码还是永久二维码
        if ($type == 1) {
            //现在临时二维码最长有效时间
            $expire = $expire > 2592000 ? 2592000 : $expire;
            //获取缓存key
            $cache_key = md5($type . $scene_str . $expire);
            $info = S('Class:Weixin:getTicket:' . $cache_key);
            if (empty($info) || ((time() - intval($info['time']) + 600) > $info['expire_seconds'])) {
                $message = array(
                    'expire_seconds' => $expire,
                    'action_name' => 'QR_STR_SCENE',
                    'action_info' => array(
                        'scene' => array(
                            'scene_str' => $scene_str
                        )
                    )
                );
                $result = $this->post($this->url['qrcode'], json_encode($message));
                if(!$result){
                    return false;
                }
                $result = json_decode($result, ture);
                $info = array(
                    'time' => time(),
                    'ticket' => $result['ticket'],
                    'expire_seconds' => $result['expire_seconds'],
                );
                S('Class:Weixin:getTicket:' . $cache_key, $info, $expire);
            }
        } else {
            $cache_key = md5($type . $scene_str);
            $info = S('Class:Weixin:getTicket:' . $cache_key);
            if (empty($info)) {
                $message = array(
                    'action_name' => 'QR_LIMIT_STR_SCENE',
                    'action_info' => array(
                        'scene' => array(
                            'scene_str' => $scene_str
                        )
                    )
                );
                $result = $this->post($this->url['qrcode'], json_encode($message));
                if(!$result){
                    return false;
                }
                $result = json_decode($result, ture);
                $info = array(
                    'ticket' => $result['ticket']
                );
                S('Class:Weixin:getTicket:' . $cache_key, $info, $expire);
            }
        }
        $info['qrcode_url'] = str_replace('[ticket]', $info['ticket'], $this->url['qrcode_url']);
        return $info;
    }

    /**
     * 拼接EventKey
     * @param  integer $category 分类
     * @param  integer $key      key值
     * @return string|bool
     */
    public function implodeEventKey($category = 0, $key = 0){
        //判断category，key是否符合要求，并拼接scene_id、
        if (($category < $this->ticket_config['category']['min']) || ($category > $this->ticket_config['category']['max']) || (strlen($category) != $this->ticket_config['category']['length']) || (strlen($key) > $this->ticket_config['key']['length_max'])) {
            return false;
        }
        return $category . str_repeat('0', ($this->ticket_config['key']['length_max'] - strlen($key))) . $key;
    }

    /**
     * 拆分EventKey
     * @param  string $string EventKey
     * @return string|bool
     */
    public function explodeEventKey($string = ''){
        //为空返回false
        if (empty($string)) {
            return false;
        }
        //拆分EventKey
        return array(
            'category' => substr($string, 0, $this->ticket_config['category']['length']),
            'key'      => ltrim(substr($string, $this->ticket_config['category']['length']), '0')
        );
    }

    /**
     * 获取扫描二维码微信服务器发送过来的客户端ID和扫描ID
     * @return array
     */
    public function getDataFromWeiXin()
    {
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $receive = $GLOBALS["HTTP_RAW_POST_DATA"];
            $obj = simplexml_load_string($receive, 'SimpleXMLElement', LIBXML_NOCDATA);
            $result = json_decode(json_encode((array) $obj), true);
            $EventKey = $result['EventKey'];
            $keyArray = explode("_", $EventKey);
            //已关注者扫描，first：是否第一次关注
            if (count($keyArray) > 1){
                $result['FirstFollow'] = true;
                $data = $keyArray[1];
            } else {
                $result['FirstFollow'] = false;
                $data = $EventKey;
            }
            $result['EventKey'] = $this->explodeEventKey($data);
            return $result;
        }
        return false;
    }

    /**
     * 发送消息给关注的用户
     * @param  array  $message 消息内容
     */
    public function sendMessage($message = array())
    {
        $url = $this->url['message'];
        $this->post($url, $message);
    }

    /**
     * 创建菜单
     * @param  array  $menu 菜单数组
     */
    public function setMenu($menu = array())
    {
        $url = $this->url['set_menu'];
        return $this->post($url, $menu);
    }

    /**
     * 获取菜单
     */
    public function getMenu()
    {
        $url = $this->url['get_menu'];
        return $this->get($url);
    }

    /**
     * 删除菜单
     */
    public function deleteMenu()
    {
        $url = $this->url['delete_menu'];
        return $this->get($url);
    }

    /**
     * get请求
     * @param  string $url 请求地址
     * @return fix
     */
    private function get($url = '')
    {
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close( $ch );
            return false;
        }else{
            curl_close( $ch );
            return $info;
        }
    }

    /**
     * 向执行URL发送数据
     * @param  string $url  url地址
     * @param  array  $data 发生数据
     * @return fixed
     */
    private function post($url = '', $data = array()){
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close( $ch );
            return false;
        }else{
            curl_close( $ch );
            return $info;
        }
    }

    /**
     * 存储在数据库中的微信配置
     * @param  string $key   配置名
     * @param  string $value 配置值
     * @return string|bool
     */
    private function option($key = '', $value = '')
    {
        if (empty($key)) {
            return false;
        }
        $map = array(
            'option_name' => $key
        );
        if (empty($value)) {
            return M('options')->where($map)->find()['option_value'];
        }
        $save = array(
            'option_value' => $value
        );
        return M('options')->where($map)->save($save);
    }
}