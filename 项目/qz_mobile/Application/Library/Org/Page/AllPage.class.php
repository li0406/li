<?php

/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/5/5
 * Time: 16:07
 * 分页统一使用该类，不满足需求时，拓展该类
 */
Class AllPage
{

    public $listRows; // 列表每页显示行数
    public $totalRows; // 总行数
    public $totalPages; // 分页总页面数

    private $p = 'p'; //分页参数名
    private $path = ''; //当前链接PATH_INFO

    private $nowPage = 1;

    private $firstUrl;
    private $static = [];
    public $parameter = []; //分页跳转时要带的参数


    public function __construct($totalRows, $listRows = 20, $static = [], $parameter = [])
    {

        /* 基础设置 */
        $this->totalRows = $totalRows; //设置总记录数
        $this->listRows = $listRows;  //设置每页显示行数
        $this->static = $static;  //设置每页显示行数
        $this->parameter = $parameter;
        $this->path = '/' . $_SERVER['PATH_INFO'];

        //获取当前页码
        if ($static) {
            $pattern = '/' . str_replace('\[PAGE\]', '([1-9]\d*)', preg_quote($static['template'], '/')) . '/';
            preg_match($pattern, $this->path, $matche);
            //如果当前访问链接为静态URL，则强制使用静态
            if (!empty($matche)) {
                $this->static['force'] = true;
            }
            $nowPage = $matche[1];
        } else {
            $nowPage = I('get.' . $this->p);

        }
        $this->nowPage = empty($nowPage) ? 1 : intval($nowPage);

        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数

        $this->firstUrl = empty($static['first_url']) ? $this->path : $static['first_url'];//第一页

    }

    public function mGongLue()
    {
        //上一页
        $up_row = intval($this->nowPage) - 1;
        if ($up_row > 0) {
            //可点击


            $up_page = '<a class="first" href="' . $this->url($up_row) . '"><i></i> 上一页</a>';
        } else {
            $up_page = '<a class="first disabled" href="avascript:void(0)"><i></i> 上一页</a>';
        }
        $page_count = '|';
        //显示分页情况 需求80/100会变动 以后会用到
//        if($this->totalPages ==0){
//            $page_count = '<span></span>';
//        }else{
//            $page_count = '<span>' . $this->nowPage . '/' . $this->totalPages . '</span>';
//        }

        //下一页
        $down_row = $this->nowPage + 1;
        if ($down_row < $this->totalPages) {
            //可点击
            $down_page = ' <a href="' . $this->url($down_row) . '">下一页 <i></i></a>';
        } else {
            $down_page = ' <a href="avascript:void(0)">下一页 <i> </i></a>';
        }

        //所有页码 需求80/100会变动 以后会用到
//        $all_page = '<li><a href="' . $this->url(1) . '">第<span>1</span>页</a></li>';
//        for ($i = 2; $i <= $this->totalPages; $i++) {
//            $all_page .= '<li><a href="' . $this->url($i) . '">第<span>' . $i . '</span>页</a></li>';
//        }


        return $up_page . $page_count . $down_page;

    }


    public function getNowPage()
    {
        return $this->nowPage;
    }


    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page)
    {
        $query = '';
        if ($this->parameter) {
            $query = '?' . http_build_query($this->parameter);
        }
        if ($page == 1) {
            return $this->firstUrl . $query;
        }
        if ($this->static) {
            return str_replace('[PAGE]', $page, $this->static['template']) . $query;
        }
    }


}