<?php

namespace Common\Model\Logic;

use Common\Model\Db\QccSearchModel;

class QccLogicModel
{
    private $key;
    private $secretKey;

    public function __construct()
    {
        $this->key = C('QCC_KEY');
        $this->secretKey = C('QCC_SECRET');
    }

    /**
     * 企查查模糊搜索
     * @param $keyword
     * @param int $page
     * @param int $pageSize
     * @return array|mixed
     */
    public function searchQccCompany($keyword, $page = 1, $pageSize = 10)
    {
        $search_url = 'http://api.qichacha.com/ECIV4/Search';
        $param = [
            'key' => $this->key,
            'keyword' => urldecode($keyword),
            'pageIndex' => $page,
            'pageSize' => $pageSize,
        ];
        $header = $this->getQccHeader();
        $response = $this->getRequest($search_url, $param, $header);
        $returnData = [];
        if ($response['Status'] == 200) {
            $totalCount = !empty($response['Paging']['TotalRecords']) && $response['Paging']['TotalRecords'] == 10000 ? '999+' : $response['Paging']['TotalRecords'];
            $list = $response['Result'];
            foreach ($list as $k => $v) {
                //搜索出的内容添加颜色
                $list[$k]['Name'] = strpos($v['Name'], $keyword) !== false ? str_replace($keyword, '<span class="red">' . $keyword . '</span>', $v['Name']) : $v['Name'];
            }
            $returnData = ['list' => $list, 'total' => $totalCount];
        }
        return $returnData;
    }

    public function getQccCompanyDetail($keyNo)
    {
        $search_url = 'http://api.qichacha.com/ECIV4/GetBasicDetailsByName';
        $param = [
            'key' => $this->key,
            'keyword' => $keyNo
        ];
        $header = $this->getQccHeader();
        $response = $this->getRequest($search_url, $param, $header);
        $data = [];
        if ($response['Status'] == 200) {
            $data = $response['Result'];
        }
        return $data;
    }

    public function addQccSearchContent($uuid, $content = '',$cs)
    {
        $save = [
            'uuid' => $uuid,
            'content' => $content,
            'location' => 2, //m端搜索
            'ip' => get_client_ip(),
            'city' => $cs ?: '',
            'created_at' => time(),
            'updated_at' => time(),
        ];
        $searchDb = new QccSearchModel();
        return $searchDb->setSearchContent($save);
    }
    /**
     * 企查查请求头
     * @return array
     */
    private function getQccHeader()
    {
        $time = time();
        $token = $this->getQccToken($time);
        $header = [
            "Token:{$token}",
            "Timespan:{$time}"
        ];
        return $header;
    }

    /**
     * 获取企查查token
     * @param $time 时间戳(精确到秒)
     * @return string
     */
    private function getQccToken($time)
    {
        //验证加密值 Md5(key+Timespan+SecretKey) 加密的32位大写字符串)
        return strtoupper(md5($this->key . $time . $this->secretKey));
    }

    // http请求
    private function getRequest($url, $param = [], $header = [], $type = "GET")
    {
        $postdata = [];
        if ($type == "GET" && !empty($param)) {
            $url .= "?" . http_build_query($param);
        } else if ($type == "POST") {
            $param["_timestamp"] = time();
            $postdata = json_encode($param);
        }

        $response = curl(urldecode($url), $postdata, $header);
        if ($response['Status'] != 200) {
            $response = ["error_code" => -1, "error_msg" => "请求失败"];
        }

        return $response;
    }

}