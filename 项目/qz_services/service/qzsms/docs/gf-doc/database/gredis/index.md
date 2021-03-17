[TOC]

# gredis

Redis客户端由`gredis`模块实现，底层采用了链接池设计。

> 目前`gredis`暂不支持`redis`集群功能。

**使用方式**：
```go
import "github.com/gogf/gf/g/database/gredis"
```

**接口文档**：
https://godoc.org/github.com/gogf/gf/g/database/gredis


`gredis`使用了连接池来进行`Redis`连接管理，通过`Config`配置对象或者`Set*`方法可以对连接池的属性进行管理，通过`Stats`方法可以获取连接池的统计信息。

我们最常用的方法是`Do`方法，执行同步指令，通过向`Redis Server`发送对应的`Redis API`命令，来使用`Redis Server`的服务。`Do`方法最大的特点是内部自行从连接池中获取连接对象，使用完毕后自动丢回连接池中，开发者无序手动调用`Close`方法，方便使用。

- Redis中文手册请参考：http://redisdoc.com/ 
- Redis官方命令请参考：https://redis.io/commands

> `gredis.Redis`客户端对象提供了一个`Close`方法，该方法是关闭Redis客户端（同时关闭客户端的连接池），而不是连接对象，开发者基本不会用到，非高级玩家请不要使用。

## Redis配置

### 配置文件
绝大部分情况下推荐使用`g.Redis`单例方式来操作redis。因此同样推荐使用配置文件来管理Redis配置，在```config.toml```中的配置示例如下：
```toml
# Redis数据库配置
[redis]
    default = "127.0.0.1:6379,0"
    cache   = "127.0.0.1:6379,1,123456?idleTimeout=600"
```
其中，Redis的配置格式为：`host:port[,db,pass?maxIdle=x&maxActive=x&idleTimeout=x&maxConnLifetime=x]`

各配置项说明如下：

|配置项名称|是否必须|默认值|说明
|---|---|---|---
| host            | 是 | -  | 地址
| port            | 是 | -  | 端口
| db              | 否 | 0  | 数据库
| pass            | 否 | -  | 授权密码
| maxIdle         | 否 | 0  | 允许限制的连接数(0表示不闲置)
| maxActive       | 否 | 0  | 最大连接数量限制(0表示不限制)
| idleTimeout     | 否 | 60 | 连接最大空闲时间(单位秒,不允许设置为0)
| maxConnLifetime | 否 | 60 | 连接最长存活时间(单位秒,不允许设置为0)

使用示例：
```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
)

func main() {
    g.Redis().Do("SET", "k", "v")
    v, _ := g.Redis().Do("GET", "k")
    fmt.Println(gconv.String(v))
}
```
其中的`default`和`cache`分别表示配置分组名称，我们在程序中可以通过该名称获取对应配置的redis对象。不传递分组名称时，默认使用`redis.default`配置分组项)来获取对应配置的redis客户端单例对象。
执行后，输出结果为：
```html
v
```

### 配置方法

`gredis`提供了全局的分组配置功能，相关配置管理方法如下：
```go
func SetConfig(config Config, name ...string)
func GetConfig(name ...string) (config Config, ok bool)
func RemoveConfig(name ...string)
func ClearConfig()
```
其中`name`参数为分组名称，即为通过分组来对配置对象进行管理，我们可以为不同的配置对象设置不同的分组名称，随后我们可以通过`Instance`单例方法获取`redis`客户端操作对象单例。
```go
func Instance(name ...string) *Redis
```

使用示例：
```go
package main

import (
	"fmt"
	"github.com/gogf/gf/g/database/gredis"
	"github.com/gogf/gf/g/util/gconv"
)

var (
	config = gredis.Config{
		Host : "127.0.0.1",
		Port : 6379,
		Db   : 1,
	}
)

func main() {
	group := "test"
	gredis.SetConfig(config, group)

	redis := gredis.Instance(group)
	defer redis.Close()

	_, err := redis.Do("SET", "k", "v")
	if err != nil {
		panic(err)
	}

	r, err := redis.Do("GET", "k")
	if err != nil {
		panic(err)
	}
	fmt.Println(gconv.String(r))
}
```


## 使用Conn

使用`Do`方法已经能够满足绝大部分的场景需要，如果需要更复杂的Redis操作，那么我们可以使用`Conn`方法从连接池中获取一个连接对象，随后使用该连接对象进行操作。但需要注意的是，该连接对象不再使用时，应当显示调用`Close`方法进行关闭（丢回连接池）。

### 基本使用
```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
)

func main() {
    conn := g.Redis().Conn()
    defer conn.Close()
    conn.Do("SET", "k", "v")
    v, _ := conn.Do("GET", "k")
    fmt.Println(gconv.String(v))
}
```
执行后，输出结果为：
```html
v
```
### Send方法

`Send`可以执行批量指令，并通过`Receive`方法一一获取返回结果。

```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
)

func main() {
    conn := g.Redis().Conn()
    defer conn.Close()
    conn.Send("SET", "foo", "bar")
    conn.Send("GET", "foo")
    conn.Flush()
    // reply from SET
    conn.Receive()
    // reply from GET
    v, _ := conn.Receive()
    fmt.Println(gconv.String(v))
}
```
执行后，输出结果为：
```html
bar
```
### 订阅/发布

```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
)

func main() {
    conn := g.Redis().Conn()
    defer conn.Close()
    _, err := conn.Do("SUBSCRIBE", "channel")
    if err != nil {
        panic(err)
    }
    for {
        reply, err := conn.Receive()
        if err != nil {
            panic(err)
        }
        fmt.Println(gconv.Strings(reply))
    }
}
```
执行后，程序将阻塞等待获取数据。

另外打开一个终端通过`redis-cli`命令进入Redis Server，发布一条消息：
```shell
$ redis-cli
127.0.0.1:6379> publish channel test
(integer) 1
127.0.0.1:6379>
```
随后程序终端立即打印出从Redis Server获取的数据：
```html
[message channel test]
```


## 结果处理

可以看到通过客户端方法`Do/Receive`获取的数据都是二进制形式`[]byte`的，需要开发者手动进行数据转换。

当然，`gredis`模块也提供了`DoVar/ReceiveVar`方法，用以获取可供方便转换的`gvar.Var`通用变量结果。

通过`gvar.Var`的强大转换功能可以转换为任意的数据类型，如基本数据类型`Int`,`String`,`Strings`，或者结构体`Struct`等等。

### 示例1，`DoVar`

```go
package main

import (
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/util/gconv"
)

func main() {
    conn := g.Redis().Conn()
    defer conn.Close()
    conn.Do("SET", "k", "v")
    v, _ := conn.DoVar("GET", "k")
    fmt.Println(v.String())
}
```
执行后，输出结果为：
```html
v
```

### 示例2，`ReceiveVar`

```go
package main

import (
	"fmt"
	"github.com/gogf/gf/g"
)

func main() {
	conn := g.Redis().Conn()
	defer conn.Close()
	_, err := conn.Do("SUBSCRIBE", "channel")
	if err != nil {
		panic(err)
	}
	for {
		reply, err := conn.ReceiveVar()
		if err != nil {
			panic(err)
		}
		fmt.Println(reply.Strings())
	}
}
```
执行后，程序将阻塞等待获取数据。

另外打开一个终端通过`redis-cli`命令进入Redis Server，发布一条消息：
```shell
$ redis-cli
127.0.0.1:6379> publish channel test
(integer) 1
127.0.0.1:6379>
```
随后程序终端立即打印出从Redis Server获取的数据：
```html
[message channel test]
```