<?php
class LitePage {
    public $pageIndex = 0;//开始页数
    public $pageCount = 10;//每页显示数量
    private $pageRowCount = 0;//总记录数
    private $pageTotalCount = 0;//总页数
    private $myConfig = array();//自定义配置
    private $extra = null;//自定义额外的参数

    // 默认分页显示定制
    public $config  = array(
        'header' => '<span class="pageheader">共 %TOTAL_ROW% 条记录</span>',
        // 'prev'   => '<a href="%LINK%" class="first"><i class="fa fa-angle-left"></i> 上一页</a>',
        // 'next'   => '<a href="%LINK%">下一页 <i class="fa fa-angle-right"></i></a>',
        'prev'   => '<a href="%LINK%" class="first">上一页</a>',
        'next'   => '<a href="%LINK%">下一页</i></a>',
        'first'  => '<a href="%LINK%">首页</a>',
        'last'   => '<a href="%LINK%">…</a>',
        'down_page' =>'<em>到第</em><input type="text" size="3" maxlength="3" class="pageInput" /><em>页</em> <a href="javascript:void(0)" id="pageSearch">确定</a>',
        'theme'  => '%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%'
    );

    // 默认分页显示定制
    public $pindaoconfig  = array(
        'header' => '<span class="pageheader">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<a href="%LINK%" class="first" rel="nofollow"><i class="fa fa-angle-left"></i> 上一页</a>',
        'next'   => '<a href="%LINK%" rel="nofollow">下一页 <i class="fa fa-angle-right"></i></a>',
        'first'  => '<a href="%LINK%" rel="nofollow">首页</a>',
        'last'   => '<a href="%LINK%" rel="nofollow">尾页</a>',
        'down_page' =>'<em>到第</em><input type="text" size="3" maxlength="3" class="pageInput" /><em>页</em> <a href="javascript:void(0)" id="pageSearch" rel="nofollow">确定</a>',
        'theme'  => '%PREV% %FIRST% %BEGIN% %LINK_PAGE% %END% %LAST% %NEXT% %DOWN_PAGE% %HEADER%'
    );

    // 默认nofollow分页显示定制
    public $nofollowConfig  = array(
        'header' => '<span class="pageheader">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<a href="%LINK%" class="first" rel="nofollow"><i class="fa fa-angle-left"></i> 上一页</a>',
        'next'   => '<a href="%LINK%" rel="nofollow">下一页 <i class="fa fa-angle-right"></i></a>',
        'first'  => '<a href="%LINK%" rel="nofollow">首页</a>',
        'last'   => '<a href="%LINK%" rel="nofollow">…</a>',
        'down_page' =>'<em>到第</em><input type="text" size="3" maxlength="3" class="pageInput" /><em>页</em> <a href="javascript:void(0)" id="pageSearch">确定</a>',
        'theme'  => '%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%'
    );


    public function __construct($pageIndex, $pageCount, $pageRowCount, $config = '', $extra = null) {
        $pageIndex = intval($pageIndex);
        $this->pageIndex =  $pageIndex <= 0 ? 1 : $pageIndex;
        $this->pageCount = $pageCount;
        $this->pageRowCount = $pageRowCount;
        $this->myConfig = $config;
        $this->extra = $extra;
    }

    public function getPageIndex(){
        $pageIndex = $this->pageIndex;
        if ($this->pageIndex >= $this->pageTotalCount) {
            $pageIndex = $this->pageTotalCount;
        }
        return $pageIndex;
    }

    /**
     * 显示分页模版
     * @return [type] [description]
     */
    public function show(){
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $parse_url = parse_url ( $url );
        if(isset($parse_url["query"])){
            $query_string = $parse_url["query"];
        }

        //正则判断是否包含P参数,包含则替换为空
        $reg = '/&?p=.*&?/i';
        $query_string = preg_replace($reg, "", urldecode(htmlspecialchars_decode($query_string)));

        //添加额外的自定义参数
        if(gettype($this->extra) == "array"){
            foreach ($this->extra as $key => $value) {
                $reg = '/[\?|&]?%s=(.*?).[\?|&]?/i';
                $reg = sprintf($reg,$key);
                $query_string = preg_replace($reg, "", $query_string);
                $extra = $key."=".$value;
            }
        }

        $query_string = rtrim($query_string,'&');
        if($this->pageRowCount > 0){
            $link = $parse_url["path"];
            $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
            if( $this->pageIndex >= $this->pageTotalCount){
                $this->pageIndex = $this->pageTotalCount;
            }
            $buttons = "" ;
            $i = $this->pageIndex;
            if($this->pageTotalCount > 6){
                if($this->pageIndex <=4){
                    if($this->pageIndex <= 3){  //显示5个标签
                        for($i = 1;$i <= 5;$i++) {
                            if($i == $this->pageIndex){
                                $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                            }else{
                                $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                            }

                            if($i == 1){
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string;
                                }else{
                                    $pageLink = "//".$link.'?';
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }else{
                                    $pageLink = rtrim($pageLink,'?');
                                }
                            }else{
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string."&p=$i";
                                }else{
                                    $pageLink = "?p=$i";
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }
                            }
                            $button = str_replace("%LINK%",$pageLink,$button);
                            $buttons .=$button;
                        }

                        $pl = !empty($query_string) ? $query_string . "&" : "";
                        $buttons .= '<a href="javascript:;">…</a><a href="//'.$link."?".$pl."p=".$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';
                    }else{      //显示6个标签
                        for($i = 1;$i <= 6;$i++) {
                            if($i == $this->pageIndex){
                                $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                            }else{
                                $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                            }

                            if($i == 1){
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string;
                                }else{
                                    $pageLink = "//".$link.'?';
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }else{
                                    $pageLink = rtrim($pageLink,'?');
                                }
                            }else{
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string."&p=$i";
                                }else{
                                    $pageLink = "?p=$i";
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }
                            }
                            $button = str_replace("%LINK%",$pageLink,$button);
                            $buttons .=$button;
                        }

                        $pl = !empty($query_string) ? $query_string . "&" : "";
                        $buttons .= '<a href="javascript:;">…</a><a href="//'.$link."?".$pl."p=".$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';
                    }
                }elseif ($this->pageIndex == $this->pageTotalCount){    //显示5个标签
                    $i = $this->pageIndex - 4;
                    for(;$i <= $this->pageIndex;$i++) {
                        if($i == $this->pageIndex){
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        }else{
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if($i == 1){
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string;
                            }else{
                                $pageLink = "//".$link.'?';
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }else{
                                $pageLink = rtrim($pageLink,'?');
                            }
                        }else{
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string."&p=$i";
                            }else{
                                $pageLink = "?p=$i";
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }
                        }
                        $button = str_replace("%LINK%",$pageLink,$button);
                        $buttons .=$button;
                    }
                    $pl = !empty($query_string) ? $query_string . "&" : "";
                    $buttons =  '<a href="//'.$link.'?'.$pl.'p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }elseif($this->pageIndex == $this->pageTotalCount -1){
                    $i = $this->pageIndex - 3;
                    for(;$i <= $this->pageIndex+1;$i++) {
                        if ($i == $this->pageIndex) {
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        } else {
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if ($i == 1) {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string;
                            } else {
                                $pageLink = "//" . $link . '?';
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            } else {
                                $pageLink = rtrim($pageLink, '?');
                            }
                        } else {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string . "&p=$i";
                            } else {
                                $pageLink = "?p=$i";
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            }
                        }
                        $button = str_replace("%LINK%", $pageLink, $button);
                        $buttons .= $button;
                    }
                    $pl = !empty($query_string) ? $query_string . "&" : "";
                    $buttons =  '<a href="//'.$link.'?'.$pl.'p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }elseif($this->pageIndex == $this->pageTotalCount - 2){
                    $i = $this->pageIndex - 2;
                    for(;$i <= $this->pageIndex+2;$i++) {
                        if ($i == $this->pageIndex) {
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        } else {
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if ($i == 1) {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string;
                            } else {
                                $pageLink = "//" . $link . '?';
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            } else {
                                $pageLink = rtrim($pageLink, '?');
                            }
                        } else {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string . "&p=$i";
                            } else {
                                $pageLink = "?p=$i";
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            }
                        }
                        $button = str_replace("%LINK%", $pageLink, $button);
                        $buttons .= $button;
                    }

                    $pl = !empty($query_string) ? $query_string . "&" : "";
                    $buttons =  '<a href="//'.$link.'?'.$pl.'p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }else{
                    $i = $this->pageIndex - 2;
                    for(;$i <= $this->pageIndex + 2;$i++) {
                        if($i == $this->pageIndex){
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        }else{
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if($i == 1){
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string;
                            }else{
                                $pageLink = "//".$link.'?';
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }else{
                                $pageLink = rtrim($pageLink,'?');
                            }
                        }else{
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string."&p=$i";
                            }else{
                                $pageLink = "?p=$i";
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }
                        }
                        $button = str_replace("%LINK%",$pageLink,$button);
                        $buttons .=$button;
                    }

                    $pl = !empty($query_string) ? $query_string . "&" : "";
                    $buttons .= '<a href="javascript:;">…</a><a href="//'.$link.'?'.$pl.'p='.$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';
                    $buttons =  '<a href="//'.$link.'?'.$pl.'p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }
            }else{  //总页数不满6页
                $end = $this->pageTotalCount;
                for($i = 1;$i <= $end;$i++) {
                    if($i == $this->pageIndex){
                        $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                    }else{
                        $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                    }

                    if($i == 1){
                        if(!empty($query_string)){
                            $pageLink = "?".$query_string;
                        }else{
                            $pageLink = "//".$link.'?';
                        }
                        if(!empty($extra)){
                            $pageLink .= "&".$extra;
                        }else{
                            $pageLink = rtrim($pageLink,'?');
                        }
                    }else{
                        if(!empty($query_string)){
                            $pageLink = "?".$query_string."&p=$i";
                        }else{
                            $pageLink = "?p=$i";
                        }
                        if(!empty($extra)){
                            $pageLink .= "&".$extra;
                        }
                    }
                    $button = str_replace("%LINK%",$pageLink,$button);
                    $buttons .=$button;
                }
            }
//            if($this->pageIndex <=10){
//                $i = 1;
//            }else{
//                $i = $this->pageIndex;
//                if($i >= ($this->pageTotalCount - 9)){
//                    $i = $this->pageTotalCount - 9;
//                }
//            }
//
//            if($this->pageTotalCount > 10){
//                $end = $i+10;
//            }else{
//                $end = $this->pageTotalCount+1;
//            }
//
//            for(;$i < $end;$i++) {
//                if($i == $this->pageIndex){
//                    $button = "<a href='%LINK%' class='current'>$i</a>";
//                }else{
//                    $button = "<a href='%LINK%'>$i</a>";
//                }
//
//                if($i == 1){
//                    if(!empty($query_string)){
//                        $pageLink = "?".$query_string;
//                    }else{
//                        $pageLink = "http://".$link.'?';
//                    }
//                    if(!empty($extra)){
//                        $pageLink .= "&".$extra;
//                    }else{
//                        $pageLink = rtrim($pageLink,'?');
//                    }
//                }else{
//                    if(!empty($query_string)){
//                        $pageLink = "?".$query_string."&p=$i";
//                    }else{
//                        $pageLink = "?p=$i";
//                    }
//                    if(!empty($extra)){
//                        $pageLink .= "&".$extra;
//                    }
//                }
//                $button = str_replace("%LINK%",$pageLink,$button);
//                $buttons .=$button;
//            }

            //%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%

            if(!empty($query_string)){
                $pageLink = "?".$query_string."&";
            }else{
                $pageLink = "?";
            }

            if(!empty($extra)){
                 $pageLink = "?".$query_string."&".$extra."&";
            }

            $exp = explode(" ",$this->config["theme"]);
            $exp = array_filter($exp);
            $exp = array_flip($exp);
            $config = array(
                    "prev"=>str_replace("%LINK%","//".$link.$pageLink."p=".($this->pageIndex-1),$this->config["prev"]),
                    "first"=>str_replace("%LINK%","//".rtrim(rtrim($link.$pageLink,'?'),'&'),$this->config["first"]),
                    "link_page"=>$buttons,
                    "last"=>str_replace("%LINK%","//".$link.$pageLink."p=".$this->pageTotalCount,$this->config["last"]),
                    "next"=>str_replace("%LINK%","//".$link.$pageLink."p=".($this->pageIndex+1),$this->config["next"]),
                    "down_page"=>$this->config["down_page"],
                    "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->config["header"])
                            );

            //说明上一页是第一页
            if($this->pageIndex-1 == 1){
                $config['prev'] = str_replace("%LINK%","//".rtrim(rtrim($link.$pageLink,'?'),'&'),$this->config["prev"]);
            }


            $config['last'] = '<a href="javascript:;">…</a><a href="//'.$link.$pageLink."p=".$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';

//            if ($this->pageIndex <= 11) {
//                unset($config["last"]);
//            }

            $item = array(
                    "link_page"=>$config["link_page"]
                          );
            if(gettype($this->myConfig) == "array" && count($this->myConfig) > 0){
              // $myConfig = array_flip($this->myConfig);
              foreach ($this->myConfig as $key => $value) {
                  $value = strtolower($value);
                  $item[$value] = $config[$value];
              }
            }else{
                $item = $config;
            }

            if($this->pageIndex <= 1){
                if(array_key_exists("%PREV%", $exp)){
                    unset($exp["%PREV%"]);
                }
            }
            if($this->pageIndex >= $this->pageTotalCount ||  $this->pageTotalCount ==1){
                if(array_key_exists("%NEXT%", $exp)){
                    unset($exp["%NEXT%"]);
                }
            }
            $tmp = '';
            foreach ($exp as $key => $value) {
                $str = strtolower(str_replace("%", "", $key));
                if(array_key_exists($str,$item)){
                    $tmp .=$item[$str];
                }
            }
            $page ="<div class='page'>%tmp%</div>";
            if(array_key_exists("down_page", $item)){
                $script = '<script>$("#pageSearch").click(function(){
                                var p = $(".pageInput").val();
                                var reg = /^[0-9]+$/gi;
                                if(!p.match(reg)){
                                    return false;
                                }
                                window.location.href = "'.$pageLink.'p="+p;
                            });</script>';
                $page .=$script;
            }

            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }

    /**
     * 增加<a>标签增加nofollow
     * @return mixed
     */
    public function pindaoShow(){
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $parse_url = parse_url ( $url );
        if(isset($parse_url["query"])){
            $query_string = $parse_url["query"];
        }

        //正则判断是否包含P参数,包含则替换为空
        $reg = '/&?p=.*&?/i';
        $query_string = preg_replace($reg, "", urldecode(htmlspecialchars_decode($query_string)));

        //添加额外的自定义参数
        if(gettype($this->extra) == "array"){
            foreach ($this->extra as $key => $value) {
                $reg = '/[\?|&]?%s=(.*?).[\?|&]?/i';
                $reg = sprintf($reg,$key);
                $query_string = preg_replace($reg, "", $query_string);
                $extra = $key."=".$value;
            }
        }

        $query_string = rtrim($query_string,'&');
        if($this->pageRowCount > 0){
            $link = $parse_url["path"];
            $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
            if( $this->pageIndex >= $this->pageTotalCount){
                $this->pageIndex = $this->pageTotalCount;
            }
            $buttons = "" ;
            $i = $this->pageIndex;
            if($this->pageTotalCount > 6){
                if($this->pageIndex <=4){
                    if($this->pageIndex <= 3){  //显示5个标签
                        for($i = 1;$i <= 5;$i++) {
                            if($i == $this->pageIndex){
                                $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                            }else{
                                $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                            }

                            if($i == 1){
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string;
                                }else{
                                    $pageLink = "//".$link.'?';
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }else{
                                    $pageLink = rtrim($pageLink,'?');
                                }
                            }else{
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string."&p=$i";
                                }else{
                                    $pageLink = "?p=$i";
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }
                            }
                            $button = str_replace("%LINK%",$pageLink,$button);
                            $buttons .=$button;
                        }
                        $buttons .= '<a href="javascript:;">…</a><a href="//'.$link."?p=".$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';
                    }else{      //显示6个标签
                        for($i = 1;$i <= 6;$i++) {
                            if($i == $this->pageIndex){
                                $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                            }else{
                                $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                            }

                            if($i == 1){
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string;
                                }else{
                                    $pageLink = "//".$link.'?';
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }else{
                                    $pageLink = rtrim($pageLink,'?');
                                }
                            }else{
                                if(!empty($query_string)){
                                    $pageLink = "?".$query_string."&p=$i";
                                }else{
                                    $pageLink = "?p=$i";
                                }
                                if(!empty($extra)){
                                    $pageLink .= "&".$extra;
                                }
                            }
                            $button = str_replace("%LINK%",$pageLink,$button);
                            $buttons .=$button;
                        }
                        $buttons .= '<a href="javascript:;">…</a><a href="//'.$link."?p=".$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';
                    }
                }elseif ($this->pageIndex == $this->pageTotalCount){    //显示5个标签
                    $i = $this->pageIndex - 4;
                    for(;$i <= $this->pageIndex;$i++) {
                        if($i == $this->pageIndex){
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        }else{
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if($i == 1){
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string;
                            }else{
                                $pageLink = "//".$link.'?';
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }else{
                                $pageLink = rtrim($pageLink,'?');
                            }
                        }else{
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string."&p=$i";
                            }else{
                                $pageLink = "?p=$i";
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }
                        }
                        $button = str_replace("%LINK%",$pageLink,$button);
                        $buttons .=$button;
                    }
                    $buttons =  '<a href="//'.$link.'?p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }elseif($this->pageIndex == $this->pageTotalCount -1){
                    $i = $this->pageIndex - 3;
                    for(;$i <= $this->pageIndex+1;$i++) {
                        if ($i == $this->pageIndex) {
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        } else {
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if ($i == 1) {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string;
                            } else {
                                $pageLink = "//" . $link . '?';
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            } else {
                                $pageLink = rtrim($pageLink, '?');
                            }
                        } else {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string . "&p=$i";
                            } else {
                                $pageLink = "?p=$i";
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            }
                        }
                        $button = str_replace("%LINK%", $pageLink, $button);
                        $buttons .= $button;
                    }
                    $buttons =  '<a href="//'.$link.'?p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }elseif($this->pageIndex == $this->pageTotalCount - 2){
                    $i = $this->pageIndex - 2;
                    for(;$i <= $this->pageIndex+2;$i++) {
                        if ($i == $this->pageIndex) {
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        } else {
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if ($i == 1) {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string;
                            } else {
                                $pageLink = "//" . $link . '?';
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            } else {
                                $pageLink = rtrim($pageLink, '?');
                            }
                        } else {
                            if (!empty($query_string)) {
                                $pageLink = "?" . $query_string . "&p=$i";
                            } else {
                                $pageLink = "?p=$i";
                            }
                            if (!empty($extra)) {
                                $pageLink .= "&" . $extra;
                            }
                        }
                        $button = str_replace("%LINK%", $pageLink, $button);
                        $buttons .= $button;
                    }
                    $buttons =  '<a href="//'.$link.'?p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }else{
                    $i = $this->pageIndex - 2;
                    for(;$i <= $this->pageIndex + 2;$i++) {
                        if($i == $this->pageIndex){
                            $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                        }else{
                            $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                        }

                        if($i == 1){
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string;
                            }else{
                                $pageLink = "//".$link.'?';
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }else{
                                $pageLink = rtrim($pageLink,'?');
                            }
                        }else{
                            if(!empty($query_string)){
                                $pageLink = "?".$query_string."&p=$i";
                            }else{
                                $pageLink = "?p=$i";
                            }
                            if(!empty($extra)){
                                $pageLink .= "&".$extra;
                            }
                        }
                        $button = str_replace("%LINK%",$pageLink,$button);
                        $buttons .=$button;
                    }
                    $buttons .= '<a href="javascript:;">…</a><a href="//'.$link."?p=".$this->pageTotalCount.'">'.$this->pageTotalCount.'</a>';
                    $buttons =  '<a href="//'.$link.'?p=1">1</a><a href="javascript:;">…</a>'.$buttons;
                }
            }else{  //总页数不满6页
                $end = $this->pageTotalCount;
                for($i = 1;$i <= $end;$i++) {
                    if($i == $this->pageIndex){
                        $button = "<a href='%LINK%' class='current' rel='nofollow'>$i</a>";
                    }else{
                        $button = "<a href='%LINK%' rel='nofollow'>$i</a>";
                    }

                    if($i == 1){
                        if(!empty($query_string)){
                            $pageLink = "?".$query_string;
                        }else{
                            $pageLink = "//".$link.'?';
                        }
                        if(!empty($extra)){
                            $pageLink .= "&".$extra;
                        }else{
                            $pageLink = rtrim($pageLink,'?');
                        }
                    }else{
                        if(!empty($query_string)){
                            $pageLink = "?".$query_string."&p=$i";
                        }else{
                            $pageLink = "?p=$i";
                        }
                        if(!empty($extra)){
                            $pageLink .= "&".$extra;
                        }
                    }
                    $button = str_replace("%LINK%",$pageLink,$button);
                    $buttons .=$button;
                }
            }
//            if($this->pageIndex <=10){
//                $i = 1;
//            }else{
//                $i = $this->pageIndex;
//                if($i >= ($this->pageTotalCount - 9)){
//                    $i = $this->pageTotalCount - 9;
//                }
//            }
//
//            if($this->pageTotalCount > 10){
//                $end = $i+10;
//            }else{
//                $end = $this->pageTotalCount+1;
//            }
//
//            for(;$i < $end;$i++) {
//                if($i == $this->pageIndex){
//                    $button = "<a href='%LINK%' class='current'>$i</a>";
//                }else{
//                    $button = "<a href='%LINK%'>$i</a>";
//                }
//                if($i == 1){
//                    if(!empty($query_string)){
//                        $pageLink = "?".$query_string;
//                    }else{
//                        $pageLink = "http://".$link.'?';
//                    }
//                    if(!empty($extra)){
//                        $pageLink .= "&".$extra;
//                    }else{
//                        $pageLink = rtrim($pageLink,'?');
//                    }
//                }else{
//                    if(!empty($query_string)){
//                        $pageLink = "?".$query_string."&p=$i";
//                    }else{
//                        $pageLink = "?p=$i";
//                    }
//                    if(!empty($extra)){
//                        $pageLink .= "&".$extra;
//                    }
//                }
//                $button = str_replace("%LINK%",$pageLink,$button);
//                $buttons .=$button;
//            }

            //%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%

            if(!empty($query_string)){
                $pageLink = "?".$query_string."&";
            }else{
                $pageLink = "?";
            }

            if(!empty($extra)){
                $pageLink = "?".$query_string."&".$extra."&";
            }

            $exp = explode(" ",$this->config["theme"]);
            $exp = array_filter($exp);
            $exp = array_flip($exp);
            $config = array(
                "prev"=>str_replace("%LINK%","//".$link.$pageLink."p=".($this->pageIndex-1),$this->config["prev"]),
                "first"=>str_replace("%LINK%","//".rtrim(rtrim($link.$pageLink,'?'),'&'),$this->config["first"]),
                "link_page"=>$buttons,
                "last"=>str_replace("%LINK%","//".$link.$pageLink."p=".$this->pageTotalCount,$this->config["last"]),
                "next"=>str_replace("%LINK%","//".$link.$pageLink."p=".($this->pageIndex+1),$this->config["next"]),
                "down_page"=>$this->config["down_page"],
                "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->config["header"])
            );

            $item = array(
                "link_page"=>$config["link_page"]
            );
            if(gettype($this->myConfig) == "array" && count($this->myConfig) > 0){
                // $myConfig = array_flip($this->myConfig);
                foreach ($this->myConfig as $key => $value) {
                    $value = strtolower($value);
                    $item[$value] = $config[$value];
                }
            }else{
                $item = $config;
            }

            if($this->pageIndex <= 1){
                if(array_key_exists("%PREV%", $exp)){
                    unset($exp["%PREV%"]);
                }
            }
            if($this->pageIndex >= $this->pageTotalCount ||  $this->pageTotalCount ==1){
                if(array_key_exists("%NEXT%", $exp)){
                    unset($exp["%NEXT%"]);
                }
            }
            $tmp = '';
            foreach ($exp as $key => $value) {
                $str = strtolower(str_replace("%", "", $key));
                if(array_key_exists($str,$item)){
                    $tmp .=$item[$str];
                }
            }
            $page ="<div class='page'>%tmp%</div>";
            if(array_key_exists("down_page", $item)){
                $script = '<script>$("#pageSearch").click(function(){
                                var p = $(".pageInput").val();
                                var reg = /^[0-9]+$/gi;
                                if(!p.match(reg)){
                                    return false;
                                }
                                window.location.href = "'.$pageLink.'p="+p;
                            });</script>';
                $page .=$script;
            }

            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }

    public function staticshow()
    {
        $reg = '/\/p(\d+)/i';
        $link = preg_replace($reg,"",$_SERVER["REQUEST_URI"] );

        $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);

        if ($this->pageTotalCount >= 834) {
            $this->pageTotalCount = 833;
        }

        if ($this->pageIndex >  $this->pageTotalCount) {
            $this->pageIndex = $this->pageTotalCount;
        }

        //分页
        $pageTmp = "";
        $placeHolder = "<a href='javascript:void(0)' rel='nofollow'>...</a>";

        //上一页
        if ($this->pageIndex > 1) {
            $pageLink = $link."p".($this->pageIndex-1)."/";
            $prev = "<a href='$pageLink' rel='nofollow'>上一页</a>";
        }

        //下一页
        if ($this->pageIndex < $this->pageTotalCount) {
            $pageLink = $link."p".($this->pageIndex+1)."/";
            $next = "<a href='$pageLink' rel='nofollow'>下一页</a>";
        }

        //第一页
        $pageLink = $link;
        $firstPage = "<a href='$pageLink'>1</a>";

        //最后一页
        $pageLink = $link."p".$this->pageTotalCount."/";
        $lastPage = "<a href='$pageLink'>".($this->pageTotalCount)."</a>";

        //总页数少于5页
        $lenth = 5;
        //分页显示类型
        $tmpModule = 1;

        if ($this->pageTotalCount <= $lenth) {
            $index = 1;
            $tmpModule = 4;
        } else if ($this->pageIndex < $lenth) {
            $index = 1;
        } else if($this->pageIndex >= 5 && $this->pageIndex < ($this->pageTotalCount - $lenth) ){
            $index = $this->pageIndex - 2;
            $lenth = $this->pageIndex + 2;
            $tmpModule = 2;
        } else if( $this->pageIndex >= ($this->pageTotalCount - $lenth)){
            $index = ($this->pageTotalCount - $lenth);
            $lenth = $this->pageTotalCount;
            $tmpModule = 3;
        }

        $buttons = "";
        for ($i = $index; $i <= $lenth; $i++){
            $pageLink = $link."p".$i."/";
            if ($i == 1) {
                $pageLink = $link;
            }

            if ($i == $this->pageIndex) {
                $button = "<a href='%LINK%' class='current'>$i</a>";
            } else if ($this->pageTotalCount >= $i) {
                $button = "<a href='%LINK%'>$i</a>";
            } else {
                continue;
            }

            $button = str_replace("%LINK%",$pageLink,$button);
            $buttons .= $button;
        }

        switch ($tmpModule){
            case 1:
                $pageTmp .= $buttons.$placeHolder.$lastPage.$next;
                break;
            case 2:
                $pageTmp .= $prev.$firstPage.$placeHolder.$buttons.$placeHolder.$lastPage.$next;
                break;
            case 3:
                $pageTmp .= $prev.$firstPage.$placeHolder.$buttons;
                break;
            case 4:
                $pageTmp .= $prev.$buttons.$next;
                break;
        }

        $page ="<div class='page'>%tmp%</div>";
        if($this->pageTotalCount > 1){
            return str_replace("%tmp%", $pageTmp, $page);
        }
    }

    /**
     * 美图专用分页
     * @return string
     */
    public function tuShow()
    {
        $baseURL  = $this->extra['baseURL'];
        $page = $this->pageIndex;
        $count = $this->pageRowCount;
        //页码数量
        $pages = ceil($count/$this->pageCount);

        $pageOffset = 2;
        $url = $baseURL;
        $prevURL = $url.($page -1).'/';
        $nextPage = $url. ($page + 1).'/';

        //是否存在keyword
        $keywordURL = $this->extra['params']['keyword']? '?keyword='.$this->extra['params']['keyword']:'';


        $prev = ($page == 1) ? '' : '<a href='.$prevURL.$keywordURL.' class="first" rel="nofollow"><i class="fa"></i> 上一页</a>';
        $next = ($page == $pages) ? '' : '<a href='.$nextPage.$keywordURL.' rel="nofollow">下一页 <i class="fa"></i></a>';

        $pagination = '';
        $lessPage = $page - $pageOffset;
        $morePage = $page + $pageOffset;
        for ($i = 1; $i <= $pages; $i++) {
            //分页数量大于3
            if ($pages <= 5) {
                if ($i == $page) {
                    if ($i==1){
                        $pagination .= "<a href='{$url}' class='current'>{$i}</a>";
                    }else {
                        $pagination .= "<a href='{$url}{$i}/{$keywordURL}' class='current' rel='nofollow'>{$i}</a>";
                    }
                } else {
                    if ($i==1){
                        $pagination .= "<a href='{$url}{$keywordURL}'>{$i}</a>";
                    }else {
                        $pagination .= "<a href='{$url}{$i}/{$keywordURL}' rel='nofollow'>{$i}</a>";
                    }
                }
            } else {
                if ($page == $i) {
                    if ($i==1){
                        $pagination .= "<a href='{$url}' class='current'>{$i}</a>";
                    }else {
                        $pagination .= "<a href='{$url}{$i}/{$keywordURL}' class='current'>{$i}</a>";
                    }
                } elseif (($i >= $lessPage) && ($i <= $morePage)) {
                    if (($i == $lessPage) && $i > 1) {
                        $pagination .= '<a>...</a>';
                    }
                    if($i==1){
                        $pagination .= "<a href='{$url}{$keywordURL}'>{$i}</a>";
                    }else {
                        $pagination .= "<a href='{$url}{$i}/{$keywordURL}'  rel='nofollow'>{$i}</a>";
                    }
                    if ($i == $morePage && $morePage != $pages) {
                        $pagination .= '<a>...</a>';
                    }
                }
            }
        }
        $container = "<div class='page'><div class='pagination'>";
        $containerEnd = "</div></div>";
        $pagination = $container. $prev . $pagination . $next . $containerEnd;
        return $pagination;
    }
}