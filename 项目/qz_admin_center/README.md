﻿#项目目录
admin-168uc

## 容器化

```
docker build -t registry.cn-shanghai.aliyuncs.com/qizuang/admin-168uc .
docker run --rm -it registry.cn-shanghai.aliyuncs.com/qizuang/admin-168uc
```


###框架漏洞修复
```
SSV-97234 	Thinkphp3.2.3最新版update注入漏洞
SSV-97511   ThinkPHP3.2 框架sql注入漏洞
SSV-97512   ThinkPHP 3.X/5.X order by注入漏洞(代码未更新，不用处理)
访问地址：https://www.seebug.org/appdir/ThinkPHP
```