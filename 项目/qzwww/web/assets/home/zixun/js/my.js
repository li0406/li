/**
 * Created by andy on 2015/12/8.
 */
var nav = document.getElementById("Q-nav");
var val = document.getElementById("top").offsetHeight;  // 得到自身的高度
var mains = document.getElementById("mains");
window.onscroll = function() {
    if(scroll().top >= 200) {
        nav.className = "gl_fixed";
    }
    else {
        nav.className = "";
        nav.style.paddingTop = "0";
    }
}
function scroll() {
    if(window.pageYOffset != null) {  // ie9+ 高版本浏览器
        return {
            left: window.pageXOffset,
            top: window.pageYOffset
        }
    }
    else if(document.compatMode === "CSS1Compat") {    // 标准浏览器   来判断有没有声明DTD
        return {
            left: document.documentElement.scrollLeft,
            top: document.documentElement.scrollTop
        }
    }
    return {   // 未声明 DTD
        left: document.body.scrollLeft,
        top: document.body.scrollTop
    }
}
