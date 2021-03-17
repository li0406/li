[TOC]

# 模板布局

`gview`模板引擎支持两种`layout`模板布局方式：`define` + `template`方式 和 `include`模板嵌入方式。

## `define` + `template`

由于`gview`底层采用了`ParseFiles`方式批量解析模板文件，因此可以使用`define`标签定义模板内容块，通过`template`标签在其他任意的模板文件中引入指定的模板内容块。`template`标签支持跨模板引用，也就是说`define`标签定义的模板内容块可能是在其他模板文件中，`template`也可以随意引入。

使用示例：

![](/images/layout1-1.png)

1. `layout.html`
    ```html
    <!DOCTYPE html>
    <html>
    <head>
        <title>GoFrame Layout</title>
        {{template "header"}}
    </head>
    <body>
        <div class="container">
        {{template "container"}}
        </div>
        <div class="footer">
        {{template "footer"}}
        </div>
    </body>
    </html>
    ```
1. `header.html`
    ```html
    {{define "header"}}
        <h1>HEADER</h1>
    {{end}}
    ```
1. `container.html`
    ```html
    {{define "container"}}
    <h1>CONTAINER</h1>
    {{end}}
    ```
1. `footer.html`
    ```html
    {{define "footer"}}
    <h1>FOOTER</h1>
    {{end}}
    ```
1. `main.go`
    ```go
    package main

    import (
        "github.com/gogf/gf/g"
        "github.com/gogf/gf/g/net/ghttp"
    )

    func main() {
        s := g.Server()
        s.BindHandler("/", func(r *ghttp.Request) {
            r.Response.WriteTpl("layout.html", nil)
        })
        s.SetPort(8199)
        s.Run()
    }
    ```

执行后，访问 `http://127.0.0.1:8199` 结果如下：

![](/images/layout1-2.png)



## `include`模板嵌入

当然我们也可以使用`include`标签来实现页面布局。

使用示例：

![](/images/layout2-1.png)

1. `layout.html`
    ```html
    {{include "header.html" .}}
    {{include .mainTpl .}}
    {{include "footer.html" .}}
    ```
1. `header.html`
    ```html
    <h1>HEADER</h1>
    ```
1. `footer.html`
    ```html
    <h1>FOOTER</h1>
    ```
1. `main1.html`
    ```html
    <h1>MAIN1</h1>
    ```
1. `main2.html`
    ```html
    <h1>MAIN2</h1>
    ```
1. `main.go`
    ```go
    package main

    import (
        "github.com/gogf/gf/g"
        "github.com/gogf/gf/g/net/ghttp"
    )

    func main() {
        s := g.Server()
        s.BindHandler("/main1", func(r *ghttp.Request) {
            r.Response.WriteTpl("layout.html", g.Map{
                "mainTpl": "main/main1.html",
            })
        })
        s.BindHandler("/main2", func(r *ghttp.Request) {
            r.Response.WriteTpl("layout.html", g.Map{
                "mainTpl": "main/main2.html",
            })
        })
        s.SetPort(8199)
        s.Run()
    }
    ```

执行后，访问不同的路由地址，将会看到不同的结果：

1. `http://127.0.0.1:8199/main1`

    ![](/images/layout2-2.png)

1. `http://127.0.0.1:8199/main2`

    ![](/images/layout2-3.png)