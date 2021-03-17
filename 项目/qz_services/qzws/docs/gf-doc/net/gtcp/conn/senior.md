
# 通信开发进阶

针对于短连接而言，每一次发送接收数据即关闭连接，连接的处理逻辑比较简单，当然通信效率也会比较低。在大多数的TCP通信场景中，往往是使用长连接操作，并采用异步全双工的TCP通信模式，即所有的通信完全是异步，`gtcp.Conn`链接对象可能同时处于多个读写操作（`gtcp.Conn`对象的数据读写操作是并发安全的），因此`SendRecv`操作在逻辑上将会失效。因为当你在同一逻辑操作中发送完毕数据之后，随后立即获取数据有可能得到的是其他写操作的结果。无论服务端还是客户端，都需要在独立的异步循环中封装使用`Recv*`方法获取数据并通过`switch...case...`处理数据（仅在一个`goroutine`中全权负责读取数据），根据请求数据进行业务处理的转发。

> 也就是说，`Send*`/`Recv*`方法是并发安全的，但是发送数据时要一次性发送。由于支持异步并发写，`gtcp.Conn`对象不带任何缓冲实现。

# 使用示例

我们通过一个完成的示例来说明一下如何在程序中实现异步全双工通信，完成示例代码位于：https://github.com/gogf/gf/tree/master/geg/net/gtcp/pkg_operations/common

1. `types/types.go`

    定义通信的数据格式，随后我们可以使用`SendPkg`/`RecvPkg`方法来通信。

    考虑到简化测试代码复杂度，因此这里使用`JSON`数据格式来传递数据。在一些对于消息包大小比较严格的场景中，`数据`字段可以自行按照二进制进行封装解析设计。此外，需要注意的是，即使使用`JSON`数据格式，其中的`Act`字段往往定义常量来实现，大部分场景中使用`uint8`类型即可，以减小消息包大小，这里偷一下懒，直接使用字符串，以便演示。
    ```go
    package types

    type Msg struct {
        Act  string // 操作
        Data string // 数据
    }
    ```
1. `funcs/funcs.go`

    自定义数据格式的发送/获取定义，便于数据结构编码/解析。
    ```go
    package funcs

    import (
        "encoding/json"
        "fmt"
        "github.com/gogf/gf/g/net/gtcp"
        "github.com/gogf/gf/geg/net/gtcp/pkg_operations/common/types"
    )

    // 自定义格式发送消息包
    func SendPkg(conn *gtcp.Conn, act string, data...string) error {
        s := ""
        if len(data) > 0 {
            s = data[0]
        }
        msg, err := json.Marshal(types.Msg{
            Act  : act,
            Data : s,
        })
        if err != nil {
            panic(err)
        }
        return conn.SendPkg(msg)
    }

    // 自定义格式接收消息包
    func RecvPkg(conn *gtcp.Conn) (msg *types.Msg, err error) {
        if data, err := conn.RecvPkg(); err != nil {
            return nil, err
        } else {
            msg = &types.Msg{}
            err = json.Unmarshal(data, msg)
            if err != nil {
                return nil, fmt.Errorf("invalid package structure: %s", err.Error())
            }
            return msg, err
        }
    }
    ```
1. `gtcp_common_server.go`

    通信服务端。在该示例中，服务端并不主动断开连接，而是在`10`秒后向客户端发送`doexit`消息，通知客户端主动断开连接，以结束示例。
    ```go
    package main

    import (
        "github.com/gogf/gf/g/net/gtcp"
        "github.com/gogf/gf/g/os/glog"
        "github.com/gogf/gf/g/os/gtimer"
        "github.com/gogf/gf/geg/net/gtcp/pkg_operations/common/funcs"
        "github.com/gogf/gf/geg/net/gtcp/pkg_operations/common/types"
        "time"
    )

    func main() {
        gtcp.NewServer("127.0.0.1:8999", func(conn *gtcp.Conn) {
            defer conn.Close()
            // 测试消息, 10秒后让客户端主动退出
            gtimer.SetTimeout(10*time.Second, func() {
                funcs.SendPkg(conn, "doexit")
            })
            for {
                msg, err := funcs.RecvPkg(conn)
                if err != nil {
                    if err.Error() == "EOF" {
                        glog.Println("client closed")
                    }
                    break
                }
                switch msg.Act {
                    case "hello":     onClientHello(conn, msg)
                    case "heartbeat": onClientHeartBeat(conn, msg)
                    default:
                        glog.Errorfln("invalid message: %v", msg)
                        break
                }
            }
        }).Run()
    }

    func onClientHello(conn *gtcp.Conn, msg *types.Msg) {
        glog.Printfln("hello message from [%s]: %s", conn.RemoteAddr().String(), msg.Data)
        funcs.SendPkg(conn, msg.Act, "Nice to meet you!")
    }

    func onClientHeartBeat(conn *gtcp.Conn, msg *types.Msg) {
        glog.Printfln("heartbeat from [%s]", conn.RemoteAddr().String())
    }
    ```
1. `gtcp_common_client.go`

    通信客户端，可以看到代码结构和服务端差不多，数据获取独立处于`for`循环中，每个业务逻辑发送消息包时直接使用`SendPkg`方法进行发送。
    
    心跳消息常用`gtimer`定时器实现，在该示例中，客户端每隔`1`秒主动向服务端发送心跳消息，在`3`秒后向服务端发送`hello`测试消息。这些都是由`gtimer`定时器实现的，定时器在TCP通信中比较常见。

    客户端连接`10`秒后，服务端会给客户端发送`doexit`消息，客户端收到该消息后便主动断开连接，长连接结束。
    ```go
    package main

    import (
        "github.com/gogf/gf/g/net/gtcp"
        "github.com/gogf/gf/g/os/glog"
        "github.com/gogf/gf/g/os/gtimer"
        "github.com/gogf/gf/geg/net/gtcp/pkg_operations/common/funcs"
        "github.com/gogf/gf/geg/net/gtcp/pkg_operations/common/types"
        "time"
    )

    func main() {
        conn, err := gtcp.NewConn("127.0.0.1:8999")
        if err != nil {
            panic(err)
        }
        defer conn.Close()
        // 心跳消息
        gtimer.SetInterval(time.Second, func() {
            if err := funcs.SendPkg(conn, "heartbeat"); err != nil {
                panic(err)
            }
        })
        // 测试消息, 3秒后向服务端发送hello消息
        gtimer.SetTimeout(3*time.Second, func() {
            if err := funcs.SendPkg(conn, "hello", "My name's John!"); err != nil {
                panic(err)
            }
        })
        for {
            msg, err := funcs.RecvPkg(conn)
            if err != nil {
                if err.Error() == "EOF" {
                    glog.Println("server closed")
                }
                break
            }
            switch msg.Act {
                case "hello":     onServerHello(conn, msg)
                case "doexit":    onServerDoExit(conn, msg)
                case "heartbeat": onServerHeartBeat(conn, msg)
                default:
                    glog.Errorfln("invalid message: %v", msg)
                    break
            }
        }
    }

    func onServerHello(conn *gtcp.Conn, msg *types.Msg) {
        glog.Printfln("hello response message from [%s]: %s", conn.RemoteAddr().String(), msg.Data)
    }

    func onServerHeartBeat(conn *gtcp.Conn, msg *types.Msg) {
        glog.Printfln("heartbeat from [%s]", conn.RemoteAddr().String())
    }

    func onServerDoExit(conn *gtcp.Conn, msg *types.Msg) {
        glog.Printfln("exit command from [%s]", conn.RemoteAddr().String())
        conn.Close()
    }
    ```
1. 执行后
    - 服务端输出结果
        ```html
        2019-05-03 14:59:13.732 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:14.732 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:15.733 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:15.733 hello message from [127.0.0.1:51220]: My name's John!
        2019-05-03 14:59:16.731 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:17.733 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:18.731 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:19.730 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:20.732 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:21.732 heartbeat from [127.0.0.1:51220]
        2019-05-03 14:59:22.698 client closed
        ```
    - 客户端输出结果
        ```html
        2019-05-03 14:59:15.733 hello response message from [127.0.0.1:8999]: Nice to meet you!
        2019-05-03 14:59:22.698 exit command from [127.0.0.1:8999]
        ```

