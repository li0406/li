# admin-api.service

服务化管理后台api

http://admin-api.service:20000

新
http://admin-api:20000

Golang项目

```
#设置拉包代理
go env -w GOPROXY=https://goproxy.cn,direct
#设置环境变量允许绕过所选模块的代理
go env -w GOPRIVATE=*.coding.net
```
去除module缓存
go clean -modcache

## 编译
### 带参数编译
```bash
sh ./scripts/build.sh
```