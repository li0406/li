# 用户中心服务端

## 用户中心架构
> [文档](https://qizuang.coding.net/p/user_center_design/d/user_center_design/git/blob/master/user_center_v1.md)


## 用户中心服务端

### 技术栈
> go [goframe](https://goframe.org) 1.9.9版本

### 项目目录
* app: 应用程序
    * controller: 控制器
    * enum: 枚举
    * logic: 逻辑层
    * model: 数据模型
    * service: 服务
* boot: 启动项
* config: 配置项
    * config.toml:配置文件
        >[错误等级](https://goframe.org/os/glog/level)
        * ALL: LEVEL_ALL 
        * DEV: LEVEL_DEV 
        * PROD: LEVEL_PROD 
        * DEBUG: LEVEL_DEBU 
        * INFO: LEVEL_INFO 
        * NOTIFY: LEVEL_NOTI 
        * WARNING: LEVEL_WARN 
        * ERROR: LEVEL_ERRO 
        * CRIT: LEVEL_CRIT 
* library: 库
* router: 路由
* runtime: 运行时(日志)
* scripts: 脚本
* vendor: 三方包
* go.mod: 模块版本管理
* main.go: 入口