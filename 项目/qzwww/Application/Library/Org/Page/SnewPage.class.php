<?php

// +----------------------------------------------------------------------
// | Author: 阿狸儿 <lliie.com>
// +----------------------------------------------------------------------

class SnewPage{
    // 列表每页显示行数
    public $listRows;
    // 总行数
    public $totalRows;
    // 分页总页面数
    public $totalPages;
    // 分页栏每页显示的页数
    public $rollPage   = 6;
    // 最后一页是否显示总页数
    public $lastSuffix = false;
    //分页参数名
    public $p          = 'p';
    //当前链接URL
    public $url        = '';
    //当前页码
    public $nowPage    = 1;
    //是否启用静态分页
    // eg:
    // array(
    //     'force'     => false, //如不填写，则可自动判断，如果为true，则强制使用静态分页
    //     'templet'   => '/gonglue/list-jubu-[PAGE].html',
    //     'firstUrl'  => '/gonglue/jubu/'
    // )
    public $static     = array();

    //第一页的URL
    public $firstUrl   = '';

    // 分页显示定制
    public $config  = array(
        'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<i class="fa fa-angle-left" id="prve"></i>',
        'next'   => '<i class="fa fa-angle-right" id="next"></i>',
        'first'  => '1',
        'last'   => '末页',
        'theme'  => '%UP_PAGE%%FIRST%%OMIT%%LINK_PAGE%%OMIT_PAGE%%END%%DOWN_PAGE%',
    );

    /**
     * 初始化函数
     * @param [type]  $totalRows 总记录条数
     * @param integer $listRows  每页显示数量
     * @param boolean $static    静态分页配置
     * @param string  $p         动态分页分页参数
     */
    public function __construct($totalRows, $listRows = 10, $static = false, $p = 'p') {
        //设置总记录数
        $this->totalRows     = $totalRows;
        //设置每页显示行数
        $this->listRows      = $listRows;
        //设置静态分页模板
        $this->static = $static;
        //设置动态分页GET参数
        $this->p             = $p;
        //优先通过静态URL获取当前页码
        if (false !== $static['templet']) {
            $pattern = '/' . str_replace('\[PAGE\]', '([1-9]\d*)', preg_quote($static['templet'], '/')) . '/';
            preg_match($pattern, $_SERVER['REQUEST_URI'], $matche);
            //如果当前访问链接为静态URL，则强制使用静态
            if (!empty($matche)) {
                $this->static['force'] = true;
            }
            $nowPage = $matche[1];
        }
        //获取当前页码
        if (empty($nowPage)) {
            $nowPage = I('get.' . $this->p);
        }
        $this->nowPage = empty($nowPage) ? 1 : intval($nowPage);
        $this->nowPage = $this->nowPage > 0 ? $this->nowPage : 1;
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string        分页模板
     */
    private function url($page = 1){
        if ($page == 1) {
            return $this->firstUrl;
        }
        return str_replace('[PAGE]', $page, $this->url);
    }

    /**
     * 解析请求参数，生成分页模板
     * @return string 分页模板
     */
    private function combine(){
        $infos  = parse_url($_SERVER['REQUEST_URI']);
        $query_array = explode('&',$infos['query']);
        $query_param  = array();
        foreach ($query_array as $k => $v) {
            $res = explode('=',$v);
            if (!empty($res['0'])) {
                $query_param[$res['0']] = $res['1'];
            }
        }
        //释放分页变量
        unset($query_param[$this->p]);

        //如果除了分页GET参数外其他参数为空，则强制使用静态分页
        if (!empty($this->static) && empty($query_param)) {
            $this->static['force'] = true;
        }

        //如果当前访问链接
        if ($this->static['force'] === true) {
            //设置第一页，默认采用static里的firstUrl，如果没有则取静态url模板中带有参数之前的url
            if (!empty($this->static['firstUrl'])) {
                $this->firstUrl = $this->static['firstUrl'];
            } else {
                $this->firstUrl = '/';
                $urls = array_filter(explode('/', $this->static['templet']));
                foreach ($urls as $key => $value) {
                    if (strpos($value, '[PAGE]') === false) {
                        $this->firstUrl = $this->firstUrl . $value . '/';
                    } else {
                        break;
                    }
                }
            }
            //设置分页模板，此处强制无需考虑其他GET参数
            $templet = $this->static['templet'];
        } else {
            $this->firstUrl = '/' . trim($infos['path'], '/') . '/';
            if (empty($query_param)) {
                $templet = $this->firstUrl . '?' . $this->p . '=[PAGE]';
            } else {
                if (!empty($query_param)) {
                    $this->firstUrl = $this->firstUrl . '?' ;
                    foreach ($query_param as $key => $value) {
                        $this->firstUrl = $this->firstUrl . $key . '=' . $value . '&';
                    }
                    $this->firstUrl = rtrim($this->firstUrl, '&');
                    //设置分页模板
                    $templet = $this->firstUrl . '&' . $this->p . '=[PAGE]';
                } else {
                    //设置分页模板
                    $templet = $this->firstUrl . '?' . $this->p . '=[PAGE]';
                }
            }
        }
        return $templet;
    }

    /**
     * 组装分页链接
     * @param  string $extra 额外参数,比如增加nofollow属性等
     * @return string        分页模板
     */
    public function show($extra = '') {
        if(0 == $this->totalRows) {
            return '';
        }

        /* 生成URL */
        $this->url = $this->combine();

        /* 计算分页信息 */
        //总页数
        $this->totalPages = ceil($this->totalRows / $this->listRows);
        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页临时变量 */
        $now_cool_page      = $this->rollPage/2;
        $now_cool_page_ceil = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;

        //上一页
        $up_row  = $this->nowPage - 1;
        if($up_row > 0){
            $up_page = '<a class="first" href="' . $this->url($up_row) . '" '.$extra.'>' . $this->config['prev'] . '</a>';
        }else{
            $up_page = '<a class="first disabled" href="avascript:void(0)" '.$extra.'>' . $this->config['prev'] . '</a>';
        }

        //下一页
        $down_row  = $this->nowPage + 1;
        if($down_row <= $this->totalPages){
            $down_page = '<a href="' . $this->url($down_row) . '" '.$extra.'>' . $this->config['next'] . '</a>';
        }else{
            $down_page = '<a class="disabled" href="javascript:void(0)" '.$extra.'>' . $this->config['next'] . '</a>';
        }

        //第一页
        $the_first = '';
        if(1 != $this->nowPage){
            $the_first = '<a class="first" href="' . $this->url(1) . '">' . $this->config['first'] . '</a>';
        }else{
            $the_first = '<a class="first disabled" href="javascript:void(0)" '.$extra.'>' . $this->config['first'] . '</a>';
        }

        //最后一页
        $the_end = '';
        if($this->totalPages > 6){
            if($this->nowPage <= $this->totalPages-3){
                $the_end = '<a href="' . $this->url($this->totalPages) . '" '.$extra.'>' . $this->totalPages . '</a>';
            }else{
                $the_end = '';
            }
        }else{
            //$the_end = '<a class="disabled" href="javascript:void(0)" '.$extra.'>' . $this->totalPages . '</a>';
            $the_end = '';
        }

        //数字连接
        $link_page = "";
        if($this->nowPage <= 4){
            for($i = 0; $i < $this->rollPage; $i++){    //显示6个数字标签
//            if(($this->nowPage - $now_cool_page) <= 0 ){
//                $page = $i;
//            }elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
//                $page = $this->totalPages - $this->rollPage + $i;
//            }else{
//                $page = $this->nowPage - $now_cool_page_ceil + $i;
//            }
                if(in_array($this->nowPage,[1,2,3])){
                    $page = $i;
                }else{
                    $page = $this->nowPage - $now_cool_page_ceil + $i;
                }
                if($page > 0 && $page != $this->nowPage){
                    if($page <= $this->totalPages){
                        $link_page .= '<a href="' . $this->url($page) . '" '.$extra.'>' . $page . '</a>';
                    }else{
                        break;
                    }
                }elseif($page > 0 && $page == $this->nowPage){
                    $link_page .= '<a class="current" href="javascript:void(0)" '.$extra.'>' . $page . '</a>';
                }
            }
        }else{
            for($i = 0; $i < $this->rollPage - 1; $i++){    //显示五个数字标签
//            if(($this->nowPage - $now_cool_page) <= 0 ){
//                $page = $i;
//            }elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
//                $page = $this->totalPages - $this->rollPage + $i;
//            }else{
//                $page = $this->nowPage - $now_cool_page_ceil + $i;
//            }
                if ($this->nowPage == $this->totalPages){   //最后一页
                    $page = $this->nowPage - 4 + $i;
                }elseif ($this->nowPage == $this->totalPages - 1){      //倒数第二页
                    $page = $this->nowPage - 3 + $i;
                }else{
                    $page = $this->nowPage - 2 + $i;
                }
                if($page > 0 && $page != $this->nowPage){
                    if($page <= $this->totalPages){
                        $link_page .= '<a href="' . $this->url($page) . '" '.$extra.'>' . $page . '</a>';
                    }else{
                        break;
                    }
                }elseif($page > 0 && $page == $this->nowPage){
                    $link_page .= '<a class="current" href="javascript:void(0)" '.$extra.'>' . $page . '</a>';
                }
            }
        }


        if ($this->nowPage == $this->totalPages) {
            $down_page = "";
        }

        if ($this->nowPage == 1) {
            $up_page = "";
        }

        //替换分页内容
        if($this->totalPages > 6){
            if($this->nowPage == 1){
                $page_str = str_replace(
                    array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%', '%OMIT_PAGE%','%OMIT%'),
                    array($this->config['header'], $this->nowPage, $up_page, $down_page, '', $link_page, $the_end, $this->totalRows, $this->totalPages, '<a href="javascript:void(0);">…</a>',''),
                    $this->config['theme']
                );
            }elseif ($this->nowPage == 2 || $this->nowPage == 3 || $this->nowPage == 4 ){
                $page_str = str_replace(
                    array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%', '%OMIT_PAGE%','%OMIT%'),
                    array($this->config['header'], $this->nowPage, $up_page, $down_page, '', $link_page, $the_end, $this->totalRows, $this->totalPages, '<a href="javascript:void(0);">…</a>', ''),
                    $this->config['theme']
                );
            }elseif($this->nowPage >=5 && $this->nowPage <= $this->totalPages-3){
                $page_str = str_replace(
                    array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%', '%OMIT_PAGE%','%OMIT%'),
                    array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages, '<a href="javascript:void(0);">…</a>', '<a href="javascript:void(0);">…</a>'),
                    $this->config['theme']
                );
            }elseif($this->nowPage >= $this->totalPages-2 && $this->nowPage <= $this->totalPages){
                $page_str = str_replace(
                    array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%', '%OMIT_PAGE%','%OMIT%'),
                    array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages, '', '<a href="javascript:void(0);">…</a>'),
                    $this->config['theme']
                );
            }else{
                $page_str = str_replace(
                    array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%', '%OMIT_PAGE%','%OMIT%'),
                    array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages, '', '<a href="javascript:void(0);">…</a>'),
                    $this->config['theme']
                );
            }
        }else{
            $page_str = str_replace(
                array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%', '%OMIT_PAGE%','%OMIT%'),
                array($this->config['header'], $this->nowPage, $up_page, $down_page, '', $link_page, $the_end, $this->totalRows, $this->totalPages, '',''),
                $this->config['theme']
            );
        }


        if ($this->totalRows <= $this->listRows ) {
            return "";
        } else {
             return '<div class="page">'.$page_str.'</div>';
        }
    }
}
