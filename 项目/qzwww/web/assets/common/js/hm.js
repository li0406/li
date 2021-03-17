(function () {
	var uid = sessionStorage.getItem("QZUUID")?sessionStorage.getItem("QZUUID"):"";
	var tag = sessionStorage.getItem("QZSRC")?sessionStorage.getItem("QZSRC"):"";
	var second = 0,ck = 0;
	var host = window.location.href;
	host = encodeURIComponent(host);
	setCookie("QZHOST",host);
	var weber = "[{}]";
	weber = eval("("+weber+")");

	if (uid == "") {
		uid = uuid();
		sessionStorage.setItem("QZUUID",uid);
		setCookie("QZUUID",uid);
	}

	if (tag == "") {
		var url = window.location.href;
		if (url.indexOf("?") != -1) {
			if (url.indexOf("src") != -1) {
				tag = GetQueryString(url,"src");
				tag = tag.split("#")[0];
				sessionStorage.setItem("QZSRC",encodeURIComponent(tag));
				setCookie("QZSRC",tag);
			}
		}
	}

	weber = {
		uuid:uid,
		tag:tag,
		host:host,
		ref: encodeURIComponent(window.top.document.referrer),
		timeIn: Date.parse(new Date())/1000
	}

	var li = document.getElementsByTagName('a');

	for (var i = 0; i < li.length; i++) {
		if (!isNotIE()) {
			li[i].attachEvent("onclick",function(){
				ck ++;
			});
		} else {
			li[i].addEventListener("click",function(){
				ck ++;
			});
		}
	}

	function http_send() {
		try {
			var str = [];
		  	for(var i in weber) {
		  		str.push(i +"="+ encodeURIComponent(weber[i]));
		  	}
			str = str.join("&");
			var url =  "http://oauthtmp.qizuang.com/v0/hm?" + str + "&rnd="+ encodeURIComponent(Math.random());
			var img = document.createElement("img");
			img.setAttribute("src",url);
			document.querySelector("body").appendChild(img);
		} catch (e) {
		}
	}

	function GetQueryString(url,name) {
		url = url.split("?")[1];
		try {
			var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
			var r = url.match(reg);

			if (r != null){
				return unescape(r[2]);
			}
		} catch (err){
			return null;
		}
		return null;
	}

	function uuid() {
		var s = [];
		var hexDigits = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		for (var i = 0; i < 36; i++) {
			s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
		}
		s[14] = "4";
		s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);
		s[8] = s[13] = s[18] = s[23] = "";

		var uuid = s.join("");
		return uuid;
	}

	function setCookie(name,value){
		var path ="";
		//"; expires=" + path +
		if (isIE()) {
			var expires = "; expires=At the end of the Session";
		} else {
			var expires = "; expires=Session";
		}
		document.cookie = name + "=" + encodeURIComponent(value) + expires + ";domain=.qizuang.com";
	}

	function getCookie(name) {
		var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
		if(arr = document.cookie.match(reg)){
			return unescape(arr[2]);
		}
		return null;
	}

	function isIE() {
        if (!!window.ActiveXObject || "ActiveXObject" in window){
            return true;
        }
        return false;
    }

    function isNotIE(){
		if (window.navigator.userAgent.indexOf("MSIE")>=1){
			return false;
		}
		return true;
	}

	function isFirefox() {
		if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
	        return true;
	   	}
	   	return false;
	}

	window.setInterval(function(){
		second ++;
	},1000);

	http_send();
})();
