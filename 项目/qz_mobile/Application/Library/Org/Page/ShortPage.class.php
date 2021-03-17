<?php
/**
 * 短链接分页类，暂未完成
 */
class Page{

    public $totalRows; // 总行数
    public $listRows; // 列表每页显示行数
    public $options; // 短参数信息
    public $sline = true; // 后面是否加入/
    public $dline = false; // 后面是否加入/
    public $p       = 'p'; //分页参数名
    public $url     = ''; //当前链接URL

    public $parameters;  // 参数信息
    public $rollPage   = 10;// 分页栏每页显示的页数
    public $lastSuffix = true; // 最后一页是否显示总页数
    public $totalPages; // 分页总页面数
    public $firstRow; // 起始行数
    public $nowPage = 1;
    public $urlPage; //当前链接URL

    // 分页显示定制
    private $config  = array(
        'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '上一页',
        'next'   => '下一页',
        'first'  => '首页',
        'last'   => '末页',
        'theme'  => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
    );

    /**
     * 架构函数
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($listRows=20, $options='', $sline = false, $dline = true,$p='p', $url) {
        //设置每页显示行数
        $this->listRows   = $listRows;
        //指定参数信息，如/list-
        $this->options  = $options;
        //后面是否加入/，传入参数ture或false
        $this->sline  = $sline;
        $this->dline  = $dline;
        //指定分页参数
        $this->p  = $p;
        //当前访问的URL
        $this->url  = empty($url) ? $_SERVER['REQUEST_URI'] : $url;
    }

    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page){
        return str_replace('[PAGE]', $page, $this->urlPage);
    }

    /**
     * [toUintval 将非数字的字符串转为大于等于0的字符串]
     * @return [type] [description]
     */
    private function toUintval($int){
        $result = empty($int) ? 0 : intval($int);
        $result = $result >= 0 ? $result : 0;
        return $result;
    }

    /**
     * [analyse 分解参数信息]
     * @return [type] [description]
     */
    public function analyse(){
        //通过?将动态参数和短参数分离开
        $info = explode('?', $this->url);
        $parameters = array();
        //如果该值为空，采用短链接分解动态参数
        if(!empty($this->options)){
            //解析短链接的参数,替换掉短链接前缀
            $statics = str_ireplace($this->options['prefix'], '', $info[0]);
            foreach ($this->options['short'] as $key => $value) {
                $pattern = '/'. $key .'\d+/i';
                $count = preg_match($pattern, $statics, $match);
                if($count > 0){
                    $k = preg_replace('/\d/s', '', $match[0]);
                    $v = preg_replace('/\D/s', '', $match[0]);
                    $parameters['short'][$k] = $this->toUintval($v);
                }else{
                    //如果没有匹配到设置默认值为0
                    $parameters['short'][$key] = 0;
                }
            }

            //解析分页参数,获取当前所在分页
            $pattern = '/'. $this->p .'\d+/i';
            $count = preg_match($pattern, $statics, $match);
            if($count >0){
                $this->nowPage = preg_replace('/\D/s', '', $match[0]);
                $this->nowPage = $this->nowPage < 1 ? 1: $this->nowPage;
            }

            //解析动态参数
            if(!empty($info[1])){
                $dynamic = $info[1];
                $data = explode('&', $dynamic);
                foreach ($data as $key => $value) {
                    $array = explode('=', $value);
                    //删除分页参数p
                    if($array[0] != $this->p){
                        $parameters['long'][$array[0]] = $array[1];
                    }
                }
            }

            //遍历添加短参数
            $urlPage = $this->options['prefix'];


            if(!empty($this->options['sort'])){
                $sort = array_merge($parameters['short'],array($this->p => '[PAGE]'));
                foreach ($this->options['sort'] as $key => $value) {
                    if(array_key_exists($value, $sort)){
                        $urlPage = $urlPage . $value . $sort[$value];
                    }
                }
            }else{
                foreach ($parameters['short'] as $key => $value) {
                    $urlPage = $urlPage . $key . $value;
                }
                $urlPage = $urlPage . $this->p . '[PAGE]';
            }

            //判断在动态参数和静态参数之间是否需要加/
            if($this->sline){
                $urlPage = $urlPage . '/';
            }

            //添加动态参数
            if(!empty($parameters['long'])){
                $urlPage = $urlPage . '?';
            }
            foreach ($parameters['long'] as $key => $value) {
                $urlPage = $urlPage . $key . '=' . $value . '&';
            }
            $this->urlPage = rtrim($urlPage, '&');
            $result['param'] = $parameters['short'];
        }else{
            $urlPage = $info[0];
            //判断在动态参数和静态参数之间是否需要加/
            if($this->sline && substr($urlPage, -1) != '/'){
                $urlPage = $urlPage . '/';
            }

            //分解动态参数
            $array = array_filter(explode('&', $info[1]));
            $urlPage = $urlPage . '?';
            if(!empty($array)){
                $temp = array_flip($this->options['short']);
                foreach ($array as $key => $value) {
                    $info = explode('=', $value);
                    if($info[0] == $this->p){
                        $this->nowPage = $this->toUintval($info[1]);
                        $this->nowPage = $this->nowPage < 1 ? 1: $this->nowPage;
                    }else{
                        if(!empty($temp[$info[0]])){
                            $parameters['long'][$temp[$info[0]]] = $this->toUintval($info[1]);
                        }else{
                            $parameters['long'][$info[0]] = $this->toUintval($info[1]);
                        }
                        $urlPage = $urlPage . $info[0] . '=' . $this->toUintval($info[1]) . '&';
                    }
                }
            }
            $urlPage = $urlPage . $this->p . '=[PAGE]';
            $this->urlPage = rtrim($urlPage, '&');
            $result['param'] = $parameters['long'];
        }

        if(!empty($this->options)){
            $statics = $this->options['prefix'];
            foreach ($parameters['short'] as $key => $value) {
                $statics = $statics . $key . $value;
            }
            if($this->sline && substr($urlPage, -1) != '/'){
                $statics = $statics . '/';
            }
            $dynamic = $this->options['dynamic'];

            if($this->dline && (substr($dynamic, -1) != '/')){
                $dynamic = $dynamic . '/';
            }

            $dynamic = $dynamic . '?';

            foreach ($parameters['short'] as $key => $value) {
                $dynamic = $dynamic . $this->options['short'][$key] . '=' . $value . '&';
            }
            $dynamic = rtrim($dynamic, '&');

            $result['urls']['statics'] = $statics;
            $result['urls']['dynamic'] = $dynamic;
        }else{
            $result['urls']['dynamic'] = $_SERVER['REQUEST_URI'];
        }

        return $result;
    }

    /**
     * 组装分页链接
     * @return string
     */
    public function show($count) {
        //解析参数信息
        $this->totalRows = $count;

        if(0 == $this->totalRows) return '';

        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数

        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页临时变量 */
        $now_cool_page      = $this->rollPage/2;
        $now_cool_page_ceil = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;

        $first = str_ireplace('&'.$this->p.'=[PAGE]', '', $this->urlPage);
        $first = str_ireplace($this->p.'[PAGE]', '', $first);

        if($first == '/list-l0f0h0c0'){
            if ($this->p == 'p') {
                $first = '/list/';
            } else {
                $first = '/list-l0f0h0c0q1';
            }
        }elseif ($first == '/gongzhuang-l0f0m0') {
            if ($this->p == 'p') {
                $first = '/gongzhuang/';
            } else {
                $first = '/gongzhuang-l0f0m0q1';
            }
        }

        //上一页
        $up_row  = $this->nowPage - 1;
        //如果上一页是第一页，则取第一页的链接
        if($up_row == 1){
            $up_page = $up_row > 0 ? '<a class="prev" href="' . $first . '">' . $this->config['prev'] . '</a>' : '';
        }else{
            $up_page = $up_row > 0 ? '<a class="prev" href="' . $this->url($up_row) . '">' . $this->config['prev'] . '</a>' : '';
        }

        //下一页
        $down_row  = $this->nowPage + 1;
        $down_page = ($down_row <= $this->totalPages) ? '<a class="next" href="' . $this->url($down_row) . '">' . $this->config['next'] . '</a>' : '';

        //第一页
        $the_first = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1){
            $the_first = '<a class="first" href="' . $first . '">' . $this->config['first'] . '</a>';
        }

        //最后一页
        $the_end = '';
        if($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages){
            $the_end = '<a class="end" href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a>';
        }

        //数字连接
        $link_page = "";
        for($i = 1; $i <= $this->rollPage; $i++){
            if(($this->nowPage - $now_cool_page) <= 0 ){
                $page = $i;
            }elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
                $page = $this->totalPages - $this->rollPage + $i;
            }else{
                $page = $this->nowPage - $now_cool_page_ceil + $i;
            }
            if($page > 0 && $page != $this->nowPage){
                if($page <= $this->totalPages){
                    if($page == 1){
                        $link_page .= '<a class="num" href="' . $first . '">' . $page . '</a>';
                    }else{
                        $link_page .= '<a class="num" href="' . $this->url($page) . '">' . $page . '</a>';
                    }
                }else{
                    break;
                }
            }else{
                if($page > 0 && $this->totalPages != 1){
                    $link_page .= '<a class="current" disabled href="javascript:void(0)">' . $page . '</a>';
                }
            }
        }

        //替换分页内容
        $page_str = str_replace(
            array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'),
            array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages),
            $this->config['theme']);
        return '<div class="page"><div class="pagination">'.$page_str.'</div></div>';
    }
}
