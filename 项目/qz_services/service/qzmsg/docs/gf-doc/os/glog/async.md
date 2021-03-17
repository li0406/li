# 异步输出日志

对于日志输出即时行要求不高的内容，可以通过异步的方式输出日志，异步输出使得日志打印调用可立即返回，因此效率较高。`glog`当然支持异步输出特性，并且内部使用了`goroutine`池来管理异步日志打印任务，可以充分的降低对资源的占用率。

异步输出可以通过日志对象的`SetAsync`/`SetFlags`方法，或者通过链式操作`Async`方法实现。但是需要注意的是，如果通过对象设置方法设置异步输出，那么后续所有的日志输出都将是异步的；如果是通过链式操作输出，那么仅对当前日志输出为异步。

> 如果对于同一个文件日志输出既采用了同步打印，也采用了异步打印，注意日志文件的内容可能会出现乱序问题，这种情况应当尽量避免。

## 使用示例1，`SetAsync`

我们来看一个使用`SetAsync`方法实现异步打印的示例。
```go
package main

import (
	"github.com/gogf/gf/g/os/glog"
)

func main() {
	glog.SetAsync(true)
	for i := 0; i < 10; i++ {
		glog.Async().Print("async log", i)
	}
}
```
执行后，可以发现终端什么内容也没有输出，因为日志输出的异步的，该示例在日志内容还没有输出之前就退出了。因此，我们可以稍做改进如下：
```go
package main

import (
	"github.com/gogf/gf/g/os/glog"
	"time"
)

func main() {
	glog.SetAsync(true)
	for i := 0; i < 10; i++ {
		glog.Async().Print("async log", i)
	}
	time.Sleep(time.Second)
}
```
执行后，终端输出结果为：
```html
2019-06-02 15:44:21.399 async log 0
2019-06-02 15:44:21.399 async log 1
2019-06-02 15:44:21.399 async log 2
2019-06-02 15:44:21.399 async log 3
2019-06-02 15:44:21.399 async log 4
2019-06-02 15:44:21.399 async log 5
2019-06-02 15:44:21.399 async log 6
2019-06-02 15:44:21.399 async log 7
2019-06-02 15:44:21.399 async log 8
2019-06-02 15:44:21.399 async log 9
```

## 使用示例2，`Async`链式操作

使用链式操作比较简单。

```go
package main

import (
	"github.com/gogf/gf/g/os/glog"
	"time"
)

func main() {
	for i := 0; i < 10; i++ {
		glog.Async().Print("async log", i)
	}
	time.Sleep(time.Second)
}
```
执行后，终端输出结果同示例1.



