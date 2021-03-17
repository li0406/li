<?php

namespace Util;

class Page
{

    public  $pageSize = 10; //每页条数
    public  $page_total_number = 0; //总页数
    public  $firstrow = 0;
    private $default_result = ["page_total_number" => 0,"total_number" => 0,"page_size" => 0,"page_current" => 0];
    private $pageIndex = 1; //当前页数
    private $total_number = 0;//总条数

    public function __construct($pageIndex = 1,$pageSize = 10,$total_number = 0)
    {
        $this->pageIndex = $pageIndex <= 0 ? 1 : $pageIndex;
        $this->pageSize = $pageSize <= 0 ? 10 : $pageSize;
        $this->total_number = $total_number <= 0 ? 0 : $total_number;
        $this->firstrow = ($this->pageIndex-1)*$this->pageSize;
        $this->page_total_number = ceil($this->total_number/$this->pageSize);
    }

    public function show()
    {
        return [
            "page_total_number" => (int)$this->page_total_number,
            "total_number" => (int)$this->total_number,
            "page_size" => (int)$this->pageSize,
            "page_current" =>(int) $this->pageIndex
        ];
    }

    public function default_show()
    {
       return $this->default_result;
    }
}
