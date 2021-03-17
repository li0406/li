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