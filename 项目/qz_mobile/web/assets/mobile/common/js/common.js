setTimeout(showPage,450);
function showPage(){
    var body = document.getElementsByTagName('body')[0];
    body.style.display = "block";
    body.style.visibility = "visible";
}
function fulnav(){
    var fullNav = document.getElementById("full-nav"),
    navBtn = document.getElementById("m-nav"),
    fullNavshut = document.getElementById("full-nav-shut");
    navBtn.addEventListener("touchend", function() {
        addClass(fullNav,"shut-buff");
        fullNavshut.addEventListener("touchend", function() {
            removeClass(fullNav,"shut-buff")
        });
    });//导航呼叫
}
function fullNavFn(){
    var fullNavBar = document.getElementById("full-navbar"),
    navBar = document.getElementById("m-navbar"),
    navItem = fullNavBar.getElementsByTagName('li');
    navBar.addEventListener("click", function(event) {
        fullNavBar.style.display = 'block';
        event.stopPropagation();
    });
    fullNavBar.addEventListener("click", function(event){
        fullNavBar.style.display = 'none';
        event.stopPropagation();
    });
    for(var i =0;i<navItem.length;i++){
        navItem[i].addEventListener('click', function(event){
            event.stopPropagation();
        });
    }
    document.addEventListener("touchmove", function(){
        fullNavBar.style.display = 'none';
    });
}
function contactKefuFn(){
    var mSys = mobileSystem().toLowerCase();
    $("#new-app-erji,.toapp,.open-qzapp").click(function(event) {
      location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a1'
        // if(mSys == 'android') {
        //     location.href = 'https://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.qz'
        // }else{
        //     window.location.href = 'https://itunes.apple.com/cn/app/id1439165740'
        // }
    });
    $("a[data-flag='app']").click(function(event) {

      location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a8'
        // if(mSys == 'android') {
        //     location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.zxs'
        // }else{
        //     window.location.href = 'https://itunes.apple.com/cn/app/id1439165740'
        // }
    });
    $(".footer-app-download > p").click(function(event) {

      location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a2'
        // if(mSys == 'android') {
        //     location.href = 'https://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.qz'
        // }else{
        //     window.location.href = 'https://itunes.apple.com/cn/app/id1439165740'
        // }
    });
    //点击下载APP
    $(".entry-app,.swiper-slide-app").click(function () {

        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-qdb-dl'
    })
    //移动端头部的app下载
    $(".common-inner-appentry").click(function () {

        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a9'
    })
    //效果图轮播点击app下载
    $(".banner-meitu").click(function (event) {
        var $target = $(event.target).closest('.banner-meitu')
        var tag = $target.attr('data-tag')
        if(tag && tag.toLowerCase() === 'm-10'){

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a10'
        }else if(tag && tag.toLowerCase() === 'm-13')

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a13'
    })
    //信息流（第五个添加App入口）
    $("body").on("click",".five-appentry",function (event) {
        var $target = $(event.target).closest('.five-appentry')
        var tag = $target.attr('data-tag')
        if(tag && tag.toLowerCase() === 'm-11'){

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a11'
        }else if(tag && tag.toLowerCase() === 'm-14'){

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a14'
        }else if(tag && tag.toLowerCase() === 'm-15'){

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a15'
        }else if(tag && tag.toLowerCase() === 'm-16'){

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a16'
        }else if(tag && tag.toLowerCase() === 'm-17'){

            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a17'
        }else if(tag && tag.toLowerCase() === 'm-18'){
            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a18'
        }else if(tag && tag.toLowerCase() === 'm-19'){
            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a19'
        }else if(tag && tag.toLowerCase() === 'm-20'){
            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a20'
        }else if(tag && tag.toLowerCase() === 'm-24'){
            window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a24'
        }
    })
    //公装app入口
    $("body").on("click","#gongzuang",function (event) {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a12'
    })
    $("div[data-name='p-inner-appentry']").click(function(event) {
      var $target = $(event.target).closest('div.p-inner-appentry')
      var tag = $target.attr('data-tag')
      if(tag && tag.toLowerCase() === 'm-1') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a1'
      }else if(tag && tag.toLowerCase() === 'm-4') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a3'
      }else if(tag && tag.toLowerCase() === 'm-5') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a4'
      }else if(tag && tag.toLowerCase() === 'm-6') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a5'
      }else if(tag && tag.toLowerCase() === 'm-7') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a6'
      }else if(tag && tag.toLowerCase() === 'm-8') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a7'
      }else if(tag && tag.toLowerCase() === 'm-22') {
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a22'
      }else if(tag && tag.toLowerCase() === 'm-23'){
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qz-mz-a23'
      }else if(tag && tag.toLowerCase() === 'qzb'){
        window.location.href = 'https://h5.qizuang.com/qzdownload?channel=qzb&channel=qzb'
      }else{
        location.href = 'https://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.qz'
      }

        // if(mSys == 'android') {
        //     location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.zxs'
        // }else{
        //     window.location.href = 'https://itunes.apple.com/cn/app/id1439165740'
        // }
    });
    $("#new-kefu-erji").click(function(event) {
        $("#new-kefu").fadeIn(200);
        $('#nav-fixed-box').fadeIn(200);
    });
    $("#new-kefu-close").click(function(event) {
        $("#new-kefu").fadeOut(200);
        $('#nav-fixed-box').fadeOut(200);
    });
}
//返回顶部
function gotop(){
    var obj = document.getElementById('gotop') || false;
    if(obj!=false){
        obj.addEventListener("touchend", function(){
            var timer = setInterval(function(){
                var a = document.body.scrollTop;
                a = a-50;
                document.body.scrollTop = a;
                if(a<=0){
                     clearInterval(timer);
                }
            },1);
        });
    }
}
// 新头部导航
function headerNav(){
    $("#m-nav-switch").click(function(event){

        if($(this).find('i').hasClass('fa-bars')){
            $(this).find('i').removeClass('fa-bars').hide();
            $("#nav-close-cha").show();
            $('#new-nav-m').show().stop().animate({right: "2%"},100);
            $('#nav-fixed-box').fadeIn(120);
        }else{
            $(this).find('i').addClass('fa-bars').show();
            $("#nav-close-cha").hide();
            $('#new-nav-m').hide().stop().animate({right: "-50%"});
            $('#nav-fixed-box').hide();
        }

    });
    $('#nav-fixed-box').on('touchend',function(e){
        $("#new-kefu").hide();
        $('#nav-fixed-box').hide();
        $('#new-nav-m').hide().stop().animate({right: "-50%"});
        $('#m-nav-switch').find('i').removeClass('fa-bars').addClass('fa-bars').show();
        $("#nav-close-cha").hide();
        e.stopPropagation();
        return false
    });

}
//判断class方法
function hasClass( elements,cName ){
    return !!elements.className.match( new RegExp( "(\\s|^)" + cName + "(\\s|$)") );
};

//添加class
function addClass( ele,className ){
    if (!ele || !className || (ele.className && ele.className.search(new RegExp("\\b" + className + "\\b")) != -1)) return;
    ele.className += (ele.className ? " " : "") + className;
};

//删除class
function removeClass( elements,cName ){
    if( hasClass( elements,cName ) ){
        elements.className = elements.className.replace( new RegExp( "(\\s|^)" + cName + "(\\s|$)" ), " " );
    };
};

function tab(id){
    var mask = document.getElementById('mask')
    var eleId = document.getElementById(id) || false;
    if(eleId!=false){
        var span = eleId.getElementsByTagName("span");
        var ul = eleId.getElementsByTagName("ul");
        var li = eleId.getElementsByTagName('li')
        for(var i=0; i<span.length;i++) {
            span[i].index = i;
             span[i].onclick = function(){
                if(this.className == 'active'){
                    this.className = "";
                    ul[this.index].className = "";
                }else{
                    for(var j = 0;j < ul.length; j++)
                    {
                        span[j].className = "";
                        ul[j].className = "";
                    }
                    this.className = "active";
                    ul[this.index].className = "show";
                }
             }
             mask.onclick = function(){
                for(var i=0;i<span.length;i++){
                    span[i].className = ''

                }
             }
        }
    }
}

if(document.getElementById('full-nav')){
    fulnav();
}
if(document.getElementById('full-navbar')){
    fullNavFn()
}
if(document.getElementById('new-kefu-erji')){
    contactKefuFn();
}
if(document.getElementById('new-app-erji')){
    contactKefuFn();
}
if(document.getElementById('activity-box')){
    contactKefuFn();
}
if(document.getElementById('gotop')){
    gotop();
}
if(document.getElementById('m-nav-switch')){
    headerNav();
}

/*上页下页 变色*/
function udpage(obj){
    var oChange=document.querySelectorAll(obj);
    for (var i = 0; i < oChange.length; i++) {
        oChange[i].onclick=function(){
            for (var i = 0; i < oChange.length; i++) {
                oChange[i].className="page-change";
            }
            if(this.className == "page-change"){
                this.className = "page-change ph";
            }else{
                this.className = "page-change";
            }
        }
    }
}

/**
 * 判断是Android还是iOS
 * @returns {string}
 */
function mobileSystem() {
    var u = navigator.userAgent;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    if(isAndroid){
        return "Android"
    }else if(isiOS){
        return "iOS"
    }else{
        return ""
    }
}


/**
 * 发单按钮置灰处理
 * btn: 发单按钮对象
 * btnType:发单按钮类型，img为图片
 * btnStatus：发单状态。success 请求完毕
 *
 * */
function orderButtonChangeStatus(btn, btnType,btnStatus){
    if(!btn){
        return
    }
    var activeClass = btnType&&btnType=="img"?'disable-order-status-opactivity':'disable-order-status'
    if(btnStatus&&btnStatus=="success"){
        setTimeout(function(){
            btn.removeClass(activeClass)
        },2000)
        return
    }
    btn.addClass(activeClass)
}
