[TOC]

# 静态文件服务配置
默认情况下，`gf`框架已开启了静态文件服务功能，但是需要开发者配置**静态文件目录**才能提供服务。

静态文件服务涉及到的常用配置方法如下：
```go
// 设置http server参数 - ServerRoot
func (s *Server) SetServerRoot(root string)

// 添加静态文件搜索目录，必须给定目录的绝对路径
func (s *Server) AddSearchPath(path string)

// 设置http server参数 - IndexFiles，默认展示文件，如：index.html, index.htm
func (s *Server) SetIndexFiles(index []string)

// 是否允许展示访问目录的文件列表
func (s *Server) SetIndexFolder(enabled bool)

// 添加URI与静态目录的映射
func (s *Server) AddStaticPath(prefix string, path string)

// 静态文件服务总开关：是否开启/关闭静态文件服务
func (s *Server) SetFileServerEnabled(enabled bool)

// 设置URI重写规则
func (s *Server) SetRewrite(uri string, rewrite string) 

// 设置URI重写规则（批量）
func (s *Server) SetRewriteMap(rewrites map[string]string) 
```
其中，
1. `IndexFiles`为当访问目录时默认检索的文件名称列表（按照slice先后顺序进行检索），当检索的文件存在时则返回文件内容，否则展示目录列表(`SetIndexFolder`为`true`时)，默认的`IndexFiles`为：`index.html, index.htm`；
1. `SetIndexFolder`为设置是否在用户访问文件目录，且没有在目录下检索到`IndexFiles`时，则展示目录下的文件列表，默认为关闭；
1. `SetServerRoot`为设置默认提供服务的静态文件目录，该目录会被自动添加到`SearchPath`中的第一个搜索路径；
1. `AddSearchPath`为添加静态文件检索目录，可以有多个，按照文件目录添加的先后顺序执行优先级检索；
1. `AddStaticPath`为添加URI与目录路径的映射关系，可以自定义静态文件目录的访问URI规则；
1. `SetRewrite`/`SetRewriteMap`为重写规则设置（类似于`nginx`的`rewrite`），严格上来讲不仅仅是静态文件服务，当然也支持动态的路由注册的`rewrite`；

> Tips: 设置静态文件服务的目录路径时，可以使用绝对路径，也可以使用相对路径，例如设置当前运行目录提供静态文件服务可以使用`SetServerRoot(".")`。

> 开发者可以设置多个文件目录来提供静态文件服务，并且可以设置目录及URI的优先级，但是一旦通过`SetFileServerEnabled`关闭了静态服务，所有静态文件/目录的访问都将失效。

# 示例1， 基本使用
```go
package main

import "github.com/gogf/gf/g"

// 静态文件服务器基本使用
func main() {
    s := g.Server()
    s.SetIndexFolder(true)
    s.SetServerRoot("/Users/john/Temp")
    s.AddSearchPath("/Users/john/Documents")
    s.SetPort(8199)
    s.Run()
}
```

# 示例2，静态目录映射
```go
package main

import "github.com/gogf/gf/g"

// 静态文件服务器，支持自定义静态目录映射
func main() {
    s := g.Server()
    s.SetIndexFolder(true)
    s.SetServerRoot("/Users/john/Temp")
    s.AddSearchPath("/Users/john/Documents")
    s.AddStaticPath("/my-doc", "/Users/john/Documents")
    s.SetPort(8199)
    s.Run()
}
```

# 示例3，静态目录映射，优先级控制

静态目录映射的优先级按照绑定的`URI`精准度进行控制，绑定的URI越精准（深度优先匹配），那么优先级越高。

```go
package main

import "github.com/gogf/gf/g"

// 静态文件服务器，支持自定义静态目录映射
func main() {
    s := g.Server()
    s.SetIndexFolder(true)
    s.SetServerRoot("/Users/john/Temp")
    s.AddSearchPath("/Users/john/Documents")
    s.AddStaticPath("/my-doc", "/Users/john/Documents")
    s.AddStaticPath("/my-doc/test", "/Users/john/Temp")
    s.SetPort(8199)
    s.Run()
}
```
其中，访问`/my-doc/test`的优先级会比`/my-doc`高，因此假如`/Users/john/Documents`目录下存在`test`目录（与自定义的`/my-doc/test`冲突），将会无法被访问到。

# 示例4，URI重写

`gf`框架的静态文件服务支持将任意的`URI`重写，替换为制定的`URI`，使用`SetRewrite/SetRewriteMap`方法。

示例，在`/Users/john/Temp`目录下只有两个文件`test1.html`及`test2.html`。
```go
package main

import "github.com/gogf/gf/g"

func main() {
    s := g.Server()
    s.SetServerRoot("/Users/john/Temp")
    s.SetRewrite("/test.html", "/test1.html")
        s.SetRewriteMap(g.MapStrStr{
            "/my-test1" : "/test1.html",
            "/my-test2" : "/test2.html",
        })
    s.SetPort(8199)
    s.Run()
}
```
执行后，
1. 当我们访问 `/test.html` ，其实最终被重写到了 `test1.html`，返回的是该文件内容；
1. 当我们访问 `/my-test1` ，其实最终被重写到了 `test1.html`，返回的是该文件内容；
1. 当我们访问 `/my-test2` ，其实最终被重写到了 `test2.html`，返回的是该文件内容；