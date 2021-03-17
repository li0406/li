window.onload = function() {
    var fullNav = document.getElementById("full-nav"),
        navBtn = document.getElementById("m-nav"),
        fullNavshut = document.getElementById("full-nav-shut");
    navBtn.addEventListener("touchend", function() {
        addClass(fullNav, "shut-buff");
        fullNavshut.addEventListener("touchend", function() {
            removeClass(fullNav, "shut-buff")
        });
    }); //导航呼叫


    function hasClass(elements, cName) {
        return !!elements.className.match(new RegExp("(\\s|^)" + cName + "(\\s|$)"));
    }; //判断class方法
    function addClass(elements, cName) {
        if (!hasClass(elements, cName)) {
            elements.className += " " + cName;
        };
    }; //添加class
    function removeClass(elements, cName) {
        if (hasClass(elements, cName)) {
            elements.className = elements.className.replace(new RegExp("(\\s|^)" + cName + "(\\s|$)"), " ");
        };
    }; //删除class

    gotop();
    function gotop(){
        var obj = document.getElementById('gotop') || false;
        if(obj!=false){
            obj.addEventListener("touchend", function(){

                timer = setInterval(function(){
                    var a = document.body.scrollTop;
                    a = a-50;
                    document.body.scrollTop = a;
                    if(a<=0){
                         clearInterval(timer);
                    }
                },1);
            });
        }
    }//返回顶部
}
