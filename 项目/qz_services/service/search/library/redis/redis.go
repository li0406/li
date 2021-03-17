package redis

import (
    "fmt"
    "github.com/gogf/gf/g"
    "github.com/gomodule/redigo/redis"
    "time"
)

type RedisConn struct {
}

func NewRedis()*RedisConn{
    return &RedisConn{}
}


func (r RedisConn)Conn() redis.Conn {
    config := g.Config()
    var address = config.GetString("redis.host")+":"+config.GetString("redis.port")
    c,err := redis.Dial("tcp",address)
    //3秒超时
    redis.DialConnectTimeout(3 * time.Second)
    if err != nil {
       fmt.Println("Connect to redis error", err.Error())
       return nil
    }
    return c
}

func (r RedisConn)Close()  {
   defer r.Conn().Close();
}

func (r RedisConn)ConvertString(str interface{}) (reply string,err error)  {
    reply ,err = redis.String(str,err)
    return reply,err
}

//添加
func (r RedisConn)SetKey(key string,value string)  {
    r.Conn().Do("SET",key,value)
    r.Close()
}

func (r RedisConn)GetKey(key string) string {
    r.Conn().Do("GET",key)
    str,_ :=    r.Conn().Do("GET",key)
    ss,err := r.ConvertString(str)
    if err != nil {
        fmt.Println("redis convert error : "+err.Error())
        return ""
    }
    r.Close()
    return ss
}

func (r RedisConn)Incr(key string)  {
    _,err := r.Conn().Do("INCR",key)
    if err != nil {
        fmt.Println(err)
        return
    }
    r.Conn().Flush()
    r.Close()
}

func (r RedisConn)SetHashIncr(key string,field string , number int)  {
    _,err := r.Conn().Do("HINCRBY",key,field,number)
    if err != nil {
        fmt.Println(err)
        return
    }
    r.Conn().Flush()
    r.Close()
}



