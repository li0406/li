

- JS封装了cookie操作,增删改查功能
- **注意：jscookies-1.0.2.js中window.cookies改为cookies对象操作**
- **例如：**cookies.set(key,value,day,path);cookies.get(key);等,操作方法名称没变,与jscookies-1.0.0一样
- **jscookies-1.0.0**使用方法如下::
- 1、写入cookie,window.cookies.set(key,value,day,path);
-       其中变量day为cookie储存天数,默认为0.5天,变量path为cookie储存路径,默认储存在站点根域名下
- 2、读取cookie,window.cookies.get(key)
- 3、根据键名判断cookie是否存在,window.cookies.has(key)
- 4、删除一个cookie,window.cookies.remove(key)
- 5、清除所有cookie,window.cookies.clear()
- 6、json序列化数据,window.cookies.stringify(data)
- 7、json反序列化数据,window.cookies.parse(data)
- 8、清除字符串前后空格,window.cookies.trim(string)
- 9、谷歌浏览器数据调试打印,window.cookies.dump(data)