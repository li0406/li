<?php
class MobilePage {
    public $pageIndex = 0;//开始页数
    private $pageCount = 10;//每页显示数量
    private $pageRowCount = 0;//总记录数
    private $pageTotalCount = 0;//总页数
    private $link="";//分页链接
    private $nowPage = 0;//当前页数
    private $myConfig = array();//自定义配置
    private $extra = null;//自定义额外的参数
    private $prefix = "";//自定义后缀

    // 默认分页显示定制
    public $config  = array(
        'header' => '<span class="pageheader">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<a href="%LINK%">上一页</a>',
        'next'   => '<a href="%LINK%">下一页</a>',
        'theme'  => '%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%'
    );

    public function __construct($pageIndex,$pageCount,$pageRowCount,$config = "",$prefix,$extra){
        $this->pageIndex = intval($pageIndex)<=0?1:$pageIndex;
        $this->pageCount = $pageCount;
        $this->pageRowCount = $pageRowCount;
        $this->myConfig = $config;
        $this->extra = $extra;
        $this->prefix = $prefix;
    }

     /**
     * 移动版分页
     * @return [type] [description]
     */
    public function show(){
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $parse_url = parse_url ( $url );
        if(isset($parse_url["query"])){
            $query_string = $parse_url["query"];
        }
        //正则判断是否包含P参数,包含则替换为空
        $reg = '/p=([0-9]*)?.\&?/i';
        $query_string = preg_replace($reg, "", $query_string);

         //添加额外的自定义参数
        if(gettype($this->extra) == "array"){
            foreach ($this->extra as $key => $value) {
                $reg = '/[\?|&]?%s=(.*?).[\?|&]?/i';
                $reg = sprintf($reg,$key);
                $query_string = preg_replace($reg, "", $query_string);
                $extra = $key."=".$value;
            }
        }
        $pageLink = $parse_url["path"];
        if($this->pageRowCount > 0){
            //如果是斜杠结尾，去掉斜杠
            if(substr($parse_url["path"],strlen($parse_url["path"])-1) == "/"){
                $parse_url["path"] = substr($parse_url["path"], 0,-1);
            }
            $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
            if( $this->pageIndex >= $this->pageTotalCount){
                $this->pageIndex = $this->pageTotalCount;
            }
            $buttons = "<span>".$this->pageIndex."/".$this->pageTotalCount."</span>";

            if(!empty($query_string)){
                $pageLink .= "?".$query_string."&";
            }else{
                $pageLink .= "?";
            }

            if(!empty($extra)){
                 $pageLink .= "?".$query_string."&".$extra."&";
            }

            $config = array(
                "prev"=>str_replace("%LINK%",("//".$pageLink."p=".(($this->pageIndex-1)<=1?1:($this->pageIndex-1))).(empty($query_string)?"":"?".$query_string),$this->config["prev"]),
                "link_page"=>$buttons,
                "next"=>str_replace("%LINK%",("//".$pageLink."p=".(($this->pageIndex+1)>=$this->pageTotalCount?$this->pageTotalCount:($this->pageIndex+1))),$this->config["next"]),
                "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->config["header"])
                        );
            $exp = explode(" ",$this->config["theme"]);
            $exp = array_filter($exp);
            $exp = array_flip($exp);

            $item = array(
                    "link_page"=>$config["link_page"]
                          );
            if(gettype($this->myConfig) == "array" && count($this->myConfig) > 0){
                foreach ($this->myConfig as $key => $value) {
                  $value = strtolower($value);
                  $item[$value] = $config[$value];
                }
            }else{
                $item = $config;
            }

            $tmp = '';
            foreach ($exp as $key => $value) {
                $str = strtolower(str_replace("%", "", $key));
                if(array_key_exists($str,$item)){
                    $tmp .=$item[$str];
                }
            }

            $page ="<div class='page'>%tmp%</div>";
            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }


}