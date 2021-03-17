#qzmsg

QZMSG消息中心

golang项目

## 消息服务

见service.json

 
* docs 文档,MarkDown



## 开发环境准备
### golang
本项目使用 go module管理包,要求go >= 1.12.0

### make/npm
项目需要make  支持

如果是windows环境先安装
Chocolatey(win下的二进制包管理器)
安装好Chocolatey后可使用choco命令安装各种需要的三方软件

```
# make   支持
choco install -y make

```

```
# 文档生成支持
npm i -g apidoc
```

### Goland设置 
Settings > Go > Go Modules(vgo) > 
勾选 Enable Go Modules(vgo) intergration


命令行运行
```
go mod download
go run main.go
```


  

## 项目结构
基础框架使用gogf,一个模块化做的很不错的框架
官方网站 https://goframe.org
go不太受框架约束,也可以引入其他三方包使用
使用gogf框架后推荐的Go业务型项目目录结构如下：
```
/
├── app
│   ├── controller
│   ├── library
│   └── model
├── boot
├── config
├── docs
├── public
├── router
├── template
├── vendor
├── go.mod
└── main.go
```
|目录/文件名称   | 说明 | 描述
|---|---|---
|`app`           | 业务逻辑层 | 所有的业务逻辑存放目录。
| - `controller` | 控制器    | 接收/解析用户输入参数的入口/接口层。
| - `library`    | 逻辑封装   | 公共的业务逻辑封装层，可供不同的包调用。
| - `model`      | 数据模型   | 数据管理层，仅用于操作管理数据，如数据库操作。
|`boot`          | 初始化包   | 用于项目初始化参数设置。
|`config`        | 配置管理   | 所有的配置文件存放目录。
|`docs`          | 项目文档   | DOC项目文档，如: 设计文档、脚本文件等等。
|`public`        | 静态目录   | 仅有该目录下的文件才能对外提供静态服务访问。
|`router`        | 路由注册   | 用于路由统一的注册管理。
|`template`      | 模板文件   | MVC模板文件存放的目录。
|`vendor`        | 第三方包   | 第三方依赖包存放目录(可选, 未来会被淘汰)。
|`go.mod`        | 依赖管理   | 使用`Go Module`包管理的依赖描述文件。
|`main.go`       | 入口文件   | 程序入口文件。

> 注意：如果需要提供静态服务，那么所有静态文件都需要存放到`public`目录下，仅有该目录下的静态文件才能被外部直接访问。不推荐将程序运行目录加入到静态服务中。

自定义扩展包

|目录/文件名称   | 说明 | 描述
|---|---|---
|`pkg`           | 公共包 | 这里的包为项目需要抽象,可以拷贝到其他项目使用。


## 包名约定

根据官方[《Effective Go》](https://golang.google.cn/doc/effective_go.html#package-names)建议，包名尽量采用言简意赅的名称(`short, concise, evocative`)。

我们建议，对于项目结构中的 控制器层`/app/controller`下的包名统一使用`ctl_`前缀；逻辑封装层`/app/library`下的包名统一使用`lib_`前缀；数据模型`/app/model`下的包名统一使用`mod_`前缀。

例如，控制器层以及逻辑封装层中都有`user`这个包，虽然通过`import`不同的路径可以做区分，但是在代码中很难以识别，阅读质量不高，并且对于开发中的IDE代码提示来说也十分不友好。

> 使用约定前缀的包命名方式, 好处之一：在IDE中输入前缀(如:`lib_`)后会自动代码提示过滤，迅速定位到所需的包。

## 短信内容
由于使用多网关发送，所以一条短信要支持多平台发送，每家的发送方式不一样，但是我们抽象定义了以下公用属性：
* content 文字内容，使用在像云片类似的以文字内容发送的平台
* template 模板 ID，使用在以模板ID来发送短信的平台
* data 模板变量，使用在以模板ID来发送短信的平台

## 参考项目
 https://github.com/overtrue/easy-sms 