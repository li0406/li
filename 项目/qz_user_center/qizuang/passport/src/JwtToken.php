<?php


namespace Qizuang\passport\src;

class JwtToken
{
    const PASSPORT_SERVER_URL = 'passport:19000';

    const U_CENTER_REDIS_KEY = 'U_CENTER:PASSPORT:';

    /**
     * 设备
     */
    const DEVICE = [
        'WEB',
        'ANDROID',
        'IOS',
    ];

    /**
     * 产品线缩写
     */
    const PRODUCT_LINE_ADDR = [
        'APP_ZXS',
        'APP_QZ',
        'PC_QZ',
    ];

    /**
     * 解码token
     * @param $token
     * @return array
     * @throws JwtException
     */
    public static function decode($token)
    {
        try {
            $tks = explode('.', $token);
            if (count($tks) != 3) {
                throw new JwtException('token格式错误', JwtAuthCode::INVALID_TOKEN);
            }
            list($headb64, $bodyb64, $cryptob64) = $tks;
            if (null === ($header = static::urlsafeB64Decode($headb64))) {
                throw new JwtException('头编码无效', JwtAuthCode::INVALID_HEADER_ENCODING);
            }
            if (null === $payload = static::urlsafeB64Decode($bodyb64)) {
                throw new JwtException('声明编码无效', JwtAuthCode::INVALID_CLAIMS_ENCODING);
            }
            if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
                throw new JwtException('签名编码无效', JwtAuthCode::INVALID_SIGNATURE_ENCODING);
            }
        } catch (JwtException $e) {
            throw $e;
        }
        return $payload;
    }

    /**
     * 获取ticket加密
     * @param $uuid
     * @param $addr
     * @param $device
     * @return string
     */
    public static function ticket($uuid, $addr, $device)
    {
        return md5($uuid . $addr . $device);
    }

    /**
     * 检测token并获取刷新token
     * @param $token
     * @return array
     * @throws JwtException
     * @throws \think\Exception
     */
    public static function check($token)
    {
        try {
            $ret = [
                'data' => [],
                'token' => $token,
            ];
            $curTimestamp = time();
            //1.解码
            $decodedToken = JwtToken::decode($token);

            //2.是否redis中已不存在
            $ticket = static::ticket($decodedToken['uuid'], $decodedToken['addr'], $decodedToken['device']);
            if (!cache(static::U_CENTER_REDIS_KEY . $ticket)) {        //已下线，需要重新登陆
                throw new JwtException('token已经被注销', JwtAuthCode::TOKEN_EXPIRE_LONG);
            }

            //3.已存在，判断
            $ret['data'] = $decodedToken;
            if ($decodedToken['re_exp'] < $curTimestamp) {
                throw new JwtException('token失效，重新登陆', JwtAuthCode::TOKEN_EXPIRE_LONG);
            }
            //可刷新时间内，刷新token
            if ($decodedToken['exp'] <= $curTimestamp && $curTimestamp <= $decodedToken['re_exp']) {
                //获取
                $ticket = static::ticket($decodedToken['uuid'], $decodedToken['addr'], $decodedToken['device']);
                //刷新
                $refreshedToken = static::refresh($ticket);
                $ret['token'] = $refreshedToken;
                $ret['data'] = JwtToken::decode($token);
            }
        } catch (JwtException $e) {
            throw $e;
        }
        return $ret;
    }


    /**
     * 获取token
     * @param $uuid
     * @param $phone
     * @param $addr
     * @param $app_env
     * @return string
     */
    public static function getToken($uuid, $phone, $addr, $app_env)
    {
        try {
            $data = [
                'uuid' => strval($uuid),
                'phone' => $phone,
                'addr' => $addr,
                'device' => self::getAppEnvPlatform($app_env),
            ];

            if (!in_array($addr, static::PRODUCT_LINE_ADDR)) {
                throw new JwtException('无效的Addr',JwtAuthCode::INVALID_PARAMS);
            }

            //application/json;charset=utf-8 请求转json_encode
            $url = static::PASSPORT_SERVER_URL . '/v1/auth/get';
            $headers = [
                'Content-Type: application/json;charset=utf-8;',
            ];
            $postData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Length: ' . strlen($postData);
            $result = self::curl($url, $postData, $headers);
            if ($result['error_code'] != 0) {
                throw new JwtException($result['error_msg'], $result['error_code']);
            }
            $token = $result['data']['token'] ?: '';
        } catch (JwtException $e) {
            return '';
//            throw $e;
        }
        return $token;
    }

    /**
     * 刷新token
     * @param $ticket
     * @return mixed|string
     * @throws JwtException
     */
    public static function refresh($ticket)
    {
        try {
            $postData = [];
            $postData['ticket'] = $ticket;

            //application/json;charset=utf-8 请求转json_encode
            $url = static::PASSPORT_SERVER_URL . '/v1/auth/refresh';
            $headers = [
                'Content-Type: application/json;charset=utf-8;',
            ];
            $postData = json_encode($postData, JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Length: ' . strlen($postData);
            $result = static::curl($url, $postData, $headers);
            if (empty($result)) {
                throw new JwtException('获取token失败', 500);
            }
            if ($result['error_code'] !== 0) {
                throw new JwtException($result['error_msg'], $result['error_code']);
            }
            $token = $result['data']['token'];
        } catch (JwtException $e) {
            throw $e;
        }
        return $token;
    }

    /**
     * 删除token
     * @param $ticket
     * @return bool
     * @throws JwtException
     */
    public static function delToken($ticket)
    {
        try {
            $data['ticket'] = $ticket;

            //application/json;charset=utf-8 请求转json_encode
            $url = static::PASSPORT_SERVER_URL . '/v1/auth/drop';
            $headers = [
                'Content-Type: application/json;charset=utf-8;',
            ];
            $postData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Length: ' . strlen($postData);
            $result = self::curl($url, $postData, $headers);
            if ($result['error_code'] == 0) {
                return true;
            } else {
                return false;
            }
        } catch (JwtException $e) {
            throw $e;
        }
    }

    /**
     * 所有产品下线
     * @param $token
     * @return bool
     * @throws JwtException
     * @throws \think\Exception
     */
    public static function loginOutAll($token)
    {
        try {
            //1.解码
            $decodedToken = static::decode($token);
            foreach (static::PRODUCT_LINE_ADDR as $addr) {
                foreach (static::DEVICE as $device) {
                    $ticket[] = static::ticket($decodedToken['uuid'], $addr, $device);
                }
            }

            $postData['ticket'] = $ticket;

            //application/json;charset=utf-8 请求转json_encode
            $url = static::PASSPORT_SERVER_URL . '/v1/auth/drop';
            $headers = [
                'Content-Type: application/json;charset=utf-8;',
            ];
            $postData = json_encode($postData, JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Length: ' . strlen($postData);
            $result = self::curl($url, $postData, $headers);
            if ($result['error_code'] != 0) {
                throw new JwtException($result['error_msg'], $result['error_code']);
            }
            if ($result['error_code'] == 0) {
                return true;
            } else {
                return false;
            }
        } catch (JwtException $e) {
            throw $e;
        }
    }

    /************************  封装函数  **************************************/
    /**
     * base64解密
     * @param $input
     * @return array
     */
    private static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $ret = base64_decode(strtr($input, '-_', '+/'));
        return json_decode($ret, true);
    }

    /**
     * 从APP_Env中获取设备是ios还是android
     * @param $appEnv
     * @return array|string
     */
    private static function getAppEnvPlatform($appEnv)
    {
        if (!$appEnv) {
            return 'WEB';
        }
        $list1 = explode('&', $appEnv);
        $result = 'WEB';
        if (is_array($list1) && count($list1) > 1) {
            foreach ($list1 as $key => $val) {
                if (strpos($val, 'platform') !== false) {
                    $result = explode('=', $val);
                    $result = strtoupper($result[1]);
                    break;
                }
            }
        } else {
            if (is_array($list1)) {
                $list1 = $list1[0];
            }
            if ($list1) {
                if (strpos($list1, 'platform') !== false) {
                    $result = explode('=', $list1);
                    $result = strtoupper($result[1]);
                }
            }
        }
        return $result;
    }


    /**
     * curl请求函数封装
     * @param $curllink
     * @param null $data
     * @param int $type
     * @return mixed
     */
    private static function curl($curllink, $data = [], $headers = null)
    {
        //初始化一个curl对象
        $ch = curl_init();
        //设置需要抓取的url
        curl_setopt($ch, CURLOPT_URL, $curllink);
        //跳过SSL证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //连接超时15秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        //超时设置60秒
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if (is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定是否显示头信息
        $output = curl_exec($ch);//执行
        curl_close($ch);//关闭
        //判断能否解析成json
        return self::isJson($output) !== false ? self::isJson($output, true) : $output;
    }

    /**
     * 判断字符串是否为Json格式
     * @param string $data Json 字符串
     * @param bool $assoc 是否返回关联数组。默认返回对象
     * @return bool|array 成功返回转换后的对象或数组，失败返回 false
     */
    private static function isJson($jsonString = '', $assoc = true)
    {
        $data = json_decode($jsonString, $assoc);
        if (is_array($data)) {
            return $data;
        }
        return false;
    }


}