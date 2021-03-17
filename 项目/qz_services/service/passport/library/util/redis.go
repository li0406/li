package util

import (
	"fmt"
	"github.com/go-redis/redis/v7"
	"github.com/gogf/gf/frame/g"
	"log"
)

var Conn *redis.Client

func init() {
	config := g.Config().GetMap("redis")
	if Conn == nil {
		if len(config) < 0 {
			log.Fatal("未发现redis配置项")
		}
		if _, ok := config["host"]; !ok {
			log.Fatal("未发现redis配置项host")
		}
		if _, ok := config["port"]; !ok {
			log.Fatal("未发现redis配置项port")
		}
		if _, ok := config["pass"]; !ok {
			log.Fatal("未发现redis配置项pass")
		}

		addr := fmt.Sprintf("%s:%s", config["host"], config["port"])
		passport := fmt.Sprintf("%s", config["pass"])

		Conn = redis.NewClient(&redis.Options{
			Addr:     addr,
			Password: passport,
		})
		pong, err := Conn.Ping().Result()
		if err != nil || pong != "PONG" {
			log.Fatal("redis连接失败", err)
		}
	}
}

func NewRedis() *redis.Client {
	return Conn
}
