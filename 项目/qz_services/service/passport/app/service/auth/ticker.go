package auth

import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"github.com/gogf/gf/util/gconv"
	"passport/library/util"
	"strings"
	"time"
)

var Tick *Ticker

const PASSPORT_REDIS_KEY = "U_CENTER:PASSPORT:"

type Ticker struct {
	Refresh_Expire time.Duration // 刷新过期时间,基本单位 time.Duration
	Redis_Key      string
}

func NewTicker() *Ticker {
	return &Ticker{
		Redis_Key: PASSPORT_REDIS_KEY,
	}
}

func (t *Ticker) GeyRedisKey(key string) string {
	key = strings.Join([]string{t.Redis_Key, key}, "")
	return key
}

func (t *Ticker) Get(key string) (ticket string, err error) {
	key = t.GeyRedisKey(key)
	ticket, err = util.NewRedis().Get(key).Result()
	if err != nil {
		return "", fmt.Errorf("获取ticket失败")
	}
	return
}

func (t *Ticker) Set(seeder string) (ticket string, err error) {
	h := md5.New()
	_, err = h.Write([]byte(seeder))
	if err != nil {
		return "", fmt.Errorf("生成ticket失败")
	}
	ticket = hex.EncodeToString(h.Sum(nil))
	return
}

// 保存ticket,成功返回数据
func (t *Ticker) Save(key string, value interface{}, expire time.Duration) (bool, error) {
	key = t.GeyRedisKey(key)
	ret, err := util.NewRedis().Set(key, value, expire).Result()
	if err != nil {
		return false, fmt.Errorf("保存ticket失败")
	}
	return gconv.Bool(ret), nil
}

func (t *Ticker) Del(key string) (bool, error) {
	key = t.GeyRedisKey(key)
	ret, err := util.NewRedis().Del(key).Result()
	if err != nil {
		return false, fmt.Errorf("删除ticket失败")
	}
	return gconv.Bool(ret), nil
}

func (t *Ticker) Exist(key string) (bool, error) {
	key = t.GeyRedisKey(key)
	ret, err := util.NewRedis().Get(key).Result()
	if err != nil {
		return false, fmt.Errorf("该ticket不存在")
	}
	return gconv.Bool(ret), nil
}
