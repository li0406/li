[TOC]


# HTTP对象视图

`gf`框架的`WebServer`的返回对象中提供了基础的模板解析方法，如下：
```go
func (r *Response) WriteTpl(tpl string, params map[string]interface{}, funcMap ...map[string]interface{}) error
func (r *Response) WriteTplContent(content string, params map[string]interface{}, funcMap ...map[string]interface{}) error
```

其中`WriteTpl`用于输出模板文件，`WriteTplContent`用于直接解析输出模板内容。

使用示例：
```go
package main

import (
	"github.com/gogf/gf/g"
	"github.com/gogf/gf/g/net/ghttp"
)

func main() {
	s := g.Server()
	s.BindHandler("/", func(r *ghttp.Request) {
		r.Cookie.Set("theme", "default")
		r.Session.Set("name", "john")
		r.Response.WriteTplContent(`Cookie:{{.Cookie.theme}}, Session:{{.Session.name}}`, nil)
	})
	s.SetPort(8199)
	s.Run()
}
```

执行后，输出结果为：
```html
Cookie:default, Session:john
```


# 控制器视图管理

`gf`为路由控制器注册方式提供了良好的模板引擎支持，由`gmvc.View`视图对象进行管理，提供了良好的数据`隔离性`。控制器视图是并发安全设计的，允许在多线程中异步操作。
```go
func (view *View) Assign(key string, value interface{})
func (view *View) Assigns(data gview.Params)

func (view *View) Parse(file string) ([]byte, error)
func (view *View) ParseContent(content string) ([]byte, error)

func (view *View) Display(files ...string) error
func (view *View) DisplayContent(content string) error

func (view *View) LockFunc(f func(vars map[string]interface{}))
func (view *View) RLockFunc(f func(vars map[string]interface{}))
```

使用示例1：
```go
package main

import (
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/frame/gmvc"
)

type ControllerTemplate struct {
    gmvc.Controller
}

func (c *ControllerTemplate) Info() {
    c.View.Assign("name", "john")
    c.View.Assigns(map[string]interface{}{
        "age"   : 18,
        "score" : 100,
    })
    c.View.Display("index.tpl")
}

func main() {
    s := ghttp.GetServer()
    s.BindController("/template", new(ControllerTemplate))
    s.SetPort(8199)
    s.Run()
}
```
其中```index.tpl```的模板内容如下：
```html
<html>
    <head>
        <title>gf template engine</title>
    </head>
    <body>
        <p>Name: {{.name}}</p>
        <p>Age:  {{.age}}</p>
        <p>Score:{{.score}}</p>
    </body>
</html>
```
执行后，访问```http://127.0.0.1:8199/template/info```可以看到模板被解析并展示到页面上。如果页面报错找不到模板文件，没有关系，因为这里并没有对模板目录做设置，默认是当前可行文件的执行目录（Linux&Mac下是```/tmp```目录，Windows下是```C:\Documents and Settings\用户名\Local Settings\Temp ```）。如何手动设置模板文件目录请查看后续章节，随后可回过头来手动修改目录后看到结果。

其中，给定的模板文件file参数是需要带完整的文件名后缀，例如：```index.tpl```，```index.html```等等，模板引擎对模板文件后缀名没有要求，用户可完全自定义。此外，模板文件参数也支持文件的绝对路径(完整的文件路径)。

当然，我们也可以直接解析模板内容，请看示例2：
```go
package main

import (
    "github.com/gogf/gf/g/net/ghttp"
    "github.com/gogf/gf/g/frame/gmvc"
)

type ControllerTemplate struct {
    gmvc.Controller
}

func (c *ControllerTemplate) Info() {
    c.View.Assign("name", "john")
    c.View.Assigns(map[string]interface{}{
        "age"   : 18,
        "score" : 100,
    })
    c.View.DisplayContent(`
        <html>
            <head>
                <title>gf template engine</title>
            </head>
            <body>
                <p>Name: {{.name}}</p>
                <p>Age:  {{.age}}</p>
                <p>Score:{{.score}}</p>
            </body>
        </html>
    `)
}

func main() {
    s := ghttp.GetServer()
    s.BindController("/template", new(ControllerTemplate{}))
    s.SetPort(8199)
    s.Run()
}
```
执行后，访问```http://127.0.0.1:8199/template/info```可以看到解析后的内容如下：
```html
<html>
    <head>
        <title>gf template engine</title>
    </head>
    <body>
        <p>Name: john</p>
        <p>Age:  18</p>
        <p>Score:100</p>
    </body>
</html>
```


