<?php


class SnewPage
{
    private $pageIndex = 1; //当前页数
    public  $pageSize = 10; //每页条数
    private $total_number = 0;//总条数
    public  $firstrow = 0;
    public  $default_result = ["page_total_number" => 0,"total_number" => 0,"page_size" => 0,"page_current" => 0];

    public function __construct($pageIndex = 1,$pageSize = 10,$total_number = 0)
    {
        $this->total_number = $total_number;
        $this->pageIndex = $pageIndex <= 0 ? 1 : $pageIndex;
        $this->pageSize = $pageSize;
        $this->firstrow = ($this->pageIndex-1)*$this->pageSize;
    }

    public function show()
    {
        //计算总页数
        $page_total_number = ceil($this->total_number/$this->pageSize);
        return ["page_total_number" => (int)$page_total_number,"total_number" => (int)$this->total_number,"page_size" => (int)$this->pageSize,"page_current" =>(int) $this->pageIndex];
    }

}