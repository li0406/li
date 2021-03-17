<?php
class MobilePage {
    public $pageIndex = 0;//开始页数
    private $pageCount = 10;//每页显示数量
    private $pageRowCount = 0;//总记录数
    public $pageTotalCount = 0;//总页数
    private $link="";//分页链接
    private $nowPage = 0;//当前页数
    private $myConfig = array();//自定义配置
    private $extra = null;//自定义额外的参数
    private $prefix = "";//自定义后缀

    // 默认分页显示定制
    public $config  = array(
        'header' => '<span class="pageheader">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<div id="prev-page" ><a %NOFOLLOW% href="%LINK%" class="page-change">上一页</a></div>',
        'next'   => '<div id="next-page" class="red-active"><a rel="nofollow" href="%LINK%" class="page-change">下一页</a></div>',
        'theme'  => '%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%'
    );

    public $pindaoconfig  = array(
        'header' => '<span class="pageheader">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<div id="prev-page" ><a href="%LINK%" class="page-change" rel="nofollow">上一页</a></div>',
        'next'   => '<div id="next-page" class="red-active"><a href="%LINK%" class="page-change" rel="nofollow">下一页</a></div>',
        'theme'  => '%PREV% %FIRST% %LINK_PAGE% %LAST% %NEXT% %DOWN_PAGE% %HEADER%'
    );

    public function __construct($pageIndex,$pageCount,$pageRowCount,$config = "",$prefix = "",$extra = null){
        $this->pageIndex = intval($pageIndex)<=0?1:intval($pageIndex);
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
        $scheme_host = getSchemeAndHost();
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
                "link_page"=>$buttons,
                "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->config["header"])
                        );
            //处理上一页，下一页是否能点击
            if($this->pageIndex <= 1){
                $config['prev'] = str_replace("%LINK%",'javascript:void(0)',$this->config["prev"]);
            }else{
                $config['prev'] = str_replace("%LINK%",($scheme_host["scheme_full"].$pageLink."p=".($this->pageIndex-1)).(empty($query_string)?"":"?".$query_string),$this->config["prev"]);
            }
            if($this->pageIndex >= $this->pageTotalCount){
                $config['next'] = str_replace("%LINK%",'javascript:void(0)',$this->config["next"]);
            }else{
                $config['next'] = str_replace("%LINK%",($scheme_host["scheme_full"].$pageLink."p=".($this->pageIndex+1)),$this->config["next"]);
            }

            //当第二页的时候，第一页不使用页码参数
            if($this->pageIndex == 2){
                $config['prev'] = str_replace("%LINK%",($scheme_host["scheme_full"].rtrim($pageLink,'?')),$this->config["prev"]);

                // 第一页不添加rel="nofollow"标签
                $config['prev'] = str_replace("%NOFOLLOW%","",$config["prev"]);
            } else {
                // 除第一页外添加rel="nofollow"标签
                $config['prev'] = str_replace("%NOFOLLOW%",'rel="nofollow"',$config["prev"]);
            }

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

            $page ="<div class='page' id='page'>%tmp%</div>";
            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }

    /**
     * 移动版分页(带弹层)
     * @return [type] [description]
     */
    public function show2(){
        $scheme_host = getSchemeAndHost();

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
        $pageLink = $scheme_host["scheme_full"].$parse_url["path"];

        if($this->pageRowCount > 0){
            // //如果是斜杠结尾，去掉斜杠
            // if(substr($parse_url["path"],strlen($parse_url["path"])-1) == "/"){
            //     $parse_url["path"] = substr($parse_url["path"], 0,-1);
            // }
            $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
            if( $this->pageIndex >= $this->pageTotalCount){
                $this->pageIndex = $this->pageTotalCount;
            }
            // $buttons = "<span>".$this->pageIndex."/".$this->pageTotalCount."</span>";
            $buttons = '<div id="page-count"><span id="current-page">'.$this->pageIndex.'</span>/<span id="page-num">'.$this->pageTotalCount.' <i class="fa fa-caret-down" id="goto-page"></i></span></div>';

            if(!empty($query_string)){
                $pageLink .= "?".$query_string."&";
            }else{
                $pageLink .= "?";
            }

            if(!empty($extra)){
                 $pageLink .= "?".$query_string."&".$extra."&";
            }

            $config = array(
                "link_page"=>$buttons,
                "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->config["header"])
            );

            //处理上一页，下一页是否能点击
            if($this->pageIndex <= 1){
                $config['prev'] = str_replace("%LINK%",'javascript:void(0)',$this->config["prev"]);
            }else{
                $config['prev'] = str_replace("%LINK%",($pageLink."p=".($this->pageIndex-1)).(empty($query_string)?"":"?".$query_string),$this->config["prev"]);
            }

            if($this->pageIndex >= $this->pageTotalCount){
                $config['next'] = str_replace("%LINK%",'javascript:void(0)',$this->config["next"]);
            }else{
                $config['next'] = str_replace("%LINK%",($pageLink."p=".($this->pageIndex+1)),$this->config["next"]);
            }

            //当第二页的时候，第一页不使用页码参数
            if($this->pageIndex == 2){
                $config['prev'] = str_replace("%LINK%",(rtrim($pageLink,'?')),$this->config["prev"]);

                // 第一页不添加rel="nofollow"标签
                $config['prev'] = str_replace("%NOFOLLOW%","",$config["prev"]);
            } else {
                // 除第一页外添加rel="nofollow"标签
                $config['prev'] = str_replace("%NOFOLLOW%",'rel="nofollow"',$config["prev"]);
            }

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

            $tmp = '<div class="page-box-container">';
            foreach ($exp as $key => $value) {
                $str = strtolower(str_replace("%", "", $key));
                if(array_key_exists($str,$item)){
                    $tmp .=$item[$str];
                }
            }
            $tmp .= "</div>";

            //添加弹出层
            $tmp .= '<div id="mask1"><div id="jump-num-box"><div class="fenye-title">分页</div><ul>';
            for ($i=1; $i <= $this->pageTotalCount ; $i++) {
                $link = $pageLink."p=".$i;
                if ($i == 1) {
                    $tmp .= '<li><a href="'.$link.'">第<span>'.$i.'</span>页</a></li>';
                } else {
                    $tmp .= '<li><a rel="nofollow" href="'.$link.'">第<span>'.$i.'</span>页</a></li>';
                }
            }
            $tmp .= '</ul></div></div>';
            //添加弹层JS代码
            $tmp .= '<script type="text/javascript">
                        var script = document.createElement("script");
                        script.src = "/assets/common/js/m-page.js";
                        var s = document.getElementsByTagName("script")[0];
                        s.parentNode.insertBefore(script, s);
                     </script>';
            $page ="<div class='page-box'>%tmp%</div>";
            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }



    /**
     * 移动版分页(带弹层)+ nofollow
     * @return [type] [description]
     */
    public function show3(){
        $scheme_host = getSchemeAndHost();
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
        $pageLink = $scheme_host["scheme_full"].$parse_url["path"];

        if($this->pageRowCount > 0){
            // //如果是斜杠结尾，去掉斜杠
            // if(substr($parse_url["path"],strlen($parse_url["path"])-1) == "/"){
            //     $parse_url["path"] = substr($parse_url["path"], 0,-1);
            // }
            $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
            if( $this->pageIndex >= $this->pageTotalCount){
                $this->pageIndex = $this->pageTotalCount;
            }
            // $buttons = "<span>".$this->pageIndex."/".$this->pageTotalCount."</span>";
            $buttons = '<div id="page-count"><span id="current-page">'.$this->pageIndex.'</span>/<span id="page-num">'.$this->pageTotalCount.' <i class="fa fa-caret-down" id="goto-page"></i></span></div>';

            if(!empty($query_string)){
                $pageLink .= "?".$query_string."&";
            }else{
                $pageLink .= "?";
            }

            if(!empty($extra)){
                $pageLink .= "?".$query_string."&".$extra."&";
            }

            $config = array(
                "link_page"=>$buttons,
                "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->pindaoconfig["header"])
            );

            //处理上一页，下一页是否能点击
            if($this->pageIndex <= 1){
                $config['prev'] = str_replace("%LINK%",'javascript:void(0)',$this->pindaoconfig["prev"]);
            }else{
                $config['prev'] = str_replace("%LINK%",($pageLink."p=".($this->pageIndex-1)).(empty($query_string)?"":"?".$query_string),$this->pindaoconfig["prev"]);
            }

            if($this->pageIndex >= $this->pageTotalCount){
                $config['next'] = str_replace("%LINK%",'javascript:void(0)',$this->pindaoconfig["next"]);
            }else{
                $config['next'] = str_replace("%LINK%",($pageLink."p=".($this->pageIndex+1)),$this->pindaoconfig["next"]);
            }

            //当第二页的时候，第一页不使用页码参数
            if($this->pageIndex == 2){
                $config['prev'] = str_replace("%LINK%",(rtrim($pageLink,'?')),$this->pindaoconfig["prev"]);
            }

            $exp = explode(" ",$this->pindaoconfig["theme"]);
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

            $tmp = '<div class="page-box-container">';
            foreach ($exp as $key => $value) {
                $str = strtolower(str_replace("%", "", $key));
                if(array_key_exists($str,$item)){
                    $tmp .=$item[$str];
                }
            }
            $tmp .= "</div>";

            //添加弹出层
            $tmp .= '<div id="mask1"><div id="jump-num-box"><div class="fenye-title">分页</div><ul>';
            for ($i=1; $i <= $this->pageTotalCount ; $i++) {
                $link = $pageLink."p=".$i;
                $tmp .= '<li><a href="'.$link.'" rel="nofollow">第<span>'.$i.'</span>页</a></li>';
            }
            $tmp .= '</ul></div></div>';
            //添加弹层JS代码
            $tmp .= '<script type="text/javascript">
                        var script = document.createElement("script");
                        script.src = "/assets/common/js/m-page.js";
                        var s = document.getElementsByTagName("script")[0];
                        s.parentNode.insertBefore(script, s);
                     </script>';
            $page ="<div class='page-box'>%tmp%</div>";
            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }


    public function showpage()
    {
        //计算总页数    $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
        $page_total_number = ceil($this->pageRowCount/$this->pageCount);
        $this->firstrow = ($this->pageIndex-1)*$this->pageCount;
        return ["page_total_number" => (int)$page_total_number,"total_number" => (int)$this->pageRowCount,"page_size" => (int)$this->pageCount,"page_current" =>(int) $this->pageIndex];
    }

    /**
     * 移动版分页(带弹层)
     * @return [type] [description]
     */
    public function staticshow(){
        $reg = '/\/p(\d+)/i';
        $pageLink = preg_replace($reg,"",$_SERVER["REQUEST_URI"] );

        if($this->pageRowCount > 0){
            // //如果是斜杠结尾，去掉斜杠
            // if(substr($parse_url["path"],strlen($parse_url["path"])-1) == "/"){
            //     $parse_url["path"] = substr($parse_url["path"], 0,-1);
            // }
            $this->pageTotalCount = ceil($this->pageRowCount/$this->pageCount);
            if( $this->pageIndex >= $this->pageTotalCount){
                $this->pageIndex = $this->pageTotalCount;
            }
            // $buttons = "<span>".$this->pageIndex."/".$this->pageTotalCount."</span>";
            $buttons = '<div id="page-count"><span id="current-page">'.$this->pageIndex.'</span>/<span id="page-num">'.$this->pageTotalCount.' <i class="fa fa-caret-down" id="goto-page"></i></span></div>';

            $config = array(
                "link_page"=>$buttons,
                "header"=>str_replace("%TOTAL_ROW%", $this->pageRowCount, $this->config["header"])
            );

            //处理上一页，下一页是否能点击
            if($this->pageIndex <= 1){
                $config['prev'] = str_replace("%LINK%",'javascript:void(0)',$this->config["prev"]);
            }else{
                $config['prev'] = str_replace("%LINK%",($pageLink."p".($this->pageIndex-1)."/"),$this->config["prev"]);
            }

            if($this->pageIndex >= $this->pageTotalCount){
                $config['next'] = str_replace("%LINK%",'javascript:void(0)',$this->config["next"]);
            }else{
                $config['next'] = str_replace("%LINK%",($pageLink."p".($this->pageIndex+1))."/",$this->config["next"]);
            }

            //当第二页的时候，第一页不使用页码参数
            if($this->pageIndex == 2){
                $config['prev'] = str_replace("%LINK%",(rtrim($pageLink,'?')),$this->config["prev"]);

                // 第一页不添加rel="nofollow"标签
                $config['prev'] = str_replace("%NOFOLLOW%","",$config["prev"]);
            } else {
                // 除第一页外添加rel="nofollow"标签
                $config['prev'] = str_replace("%NOFOLLOW%",'',$config["prev"]);
            }

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

            $tmp = '<div class="page-box-container">';
            foreach ($exp as $key => $value) {
                $str = strtolower(str_replace("%", "", $key));
                if(array_key_exists($str,$item)){
                    $tmp .=$item[$str];
                }
            }
            $tmp .= "</div>";

            //添加弹出层
            $tmp .= '<div id="mask1"><div id="jump-num-box"><div class="fenye-title">分页</div><ul>';
            for ($i=1; $i <= $this->pageTotalCount ; $i++) {
                $link = $pageLink."p".$i."/";
                if ($i == 1) {
                    $link = $pageLink;
                }


                if ($i == 1) {
                    $tmp .= '<li><a href="'.$link.'">第<span>'.$i.'</span>页</a></li>';
                } else {
                    $tmp .= '<li><a href="'.$link.'">第<span>'.$i.'</span>页</a></li>';
                }
            }
            $tmp .= '</ul></div></div>';
            //添加弹层JS代码
            $tmp .= '<script type="text/javascript">
                        var script = document.createElement("script");
                        script.src = "/assets/common/js/m-page.js";
                        var s = document.getElementsByTagName("script")[0];
                        s.parentNode.insertBefore(script, s);
                     </script>';
            $page ="<div class='page-box'>%tmp%</div>";
            if($this->pageTotalCount > 1){
                return str_replace("%tmp%", $tmp, $page);
            }
        }
    }
}
