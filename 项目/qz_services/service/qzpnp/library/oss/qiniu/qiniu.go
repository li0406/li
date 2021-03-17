// qiniu
// 七牛对象存储
package qiniu

import (
	"encoding/json"

	"github.com/qiniu/api.v7/v7/auth/qbox"
	"github.com/qiniu/api.v7/v7/storage"
)

type Qiniu struct {
	Gcfg       *Gcfg // 全局配置
	Mac        *qbox.Mac
	PutPolicy  *storage.PutPolicy // 上传策略
	PutExtra   *storage.PutExtra  // 上传扩展
	ReturnBody *ReturnBody        // 返回body结构
	Config     *storage.Config    // 七牛SDK内部配置
}

// Config
// 全局配置文件
type Gcfg struct {
	AccessKey  string // ak
	SecertKey  string // sk
	Bucket     string // 空间
	Zone       string // 地域
	DomainHost string // 空间绑定的域名
}

// ReturnBody
// 对象存储上传返回结果
type ReturnBody struct {
	Key     string `json:"key"`
	Hash    string `json:"hash"`
	Fsize   string `json:"fsize"`
	Bucket  string `json:"bucket"`
	Name    string `json:"name"`
	UrlFull string `json:"url_full"`
}

func New(c *Gcfg) *Qiniu {
	q := Qiniu{
		Gcfg:       nil,
		Mac:        nil,
		PutPolicy:  &storage.PutPolicy{},
		PutExtra:   &storage.PutExtra{},
		ReturnBody: &ReturnBody{},
		Config:     &storage.Config{},
	}
	q.Gcfg = c                                    // 全局配置
	q.Mac = qbox.NewMac(c.AccessKey, c.SecertKey) // ak  sk  > mac
	q.SetBucket(q.Gcfg.Bucket)                    // Bucket

	// 配置sdk
	cfg := storage.Config{}
	// 是否使用https域名
	cfg.UseHTTPS = false
	// 上传是否使用CDN上传加速
	cfg.UseCdnDomains = false
	q.SetConfig(&cfg)

	// 设置默认的返回body结构
	q.SetReturnBodyDefault()

	return &q
}

// SetPutPolicy
// 设置上传策略
func (q *Qiniu) SetPutPolicy(p *storage.PutPolicy) {
	q.PutPolicy = p
}

// SetSaveKey
// 设置保存key
func (q *Qiniu) SetSaveKey(k string) {
	q.PutPolicy.SaveKey = k
	q.PutPolicy.ForceSaveKey = true
}

// SetBucket
// 设置空间
func (q *Qiniu) SetBucket(b string) {
	q.PutPolicy.Scope = b
}

// SetPutExtra
// 设置上传扩展
func (q *Qiniu) SetPutExtra(p *storage.PutExtra) {
	q.PutExtra = p
}

// SetPutRet
func (q *Qiniu) SetPutRet(r *ReturnBody) {
	q.ReturnBody = r
	b, _ := json.Marshal(r)
	q.PutPolicy.ReturnBody = string(b)
}

// SetReturnBodyDefault
// 设置默认的返回结构
func (q *Qiniu) SetReturnBodyDefault() {
	r := ReturnBody{
		Key:     "$(key)",
		Hash:    "$(etag)",
		Fsize:   "$(fsize)",
		Bucket:  "$(bucket)",
		Name:    "$(x:name)}",
		UrlFull: q.Gcfg.DomainHost + "/$(key)",
	}
	b, _ := json.Marshal(r)
	q.PutPolicy.ReturnBody = string(b)
}

// SetConfig
func (q *Qiniu) SetConfig(cfg *storage.Config) {
	q.Config = cfg
}

// SetZone
// 设置存储地域
//机房	Zone对象
//华东	storage.ZoneHuadong
//华北	storage.ZoneHuabei
//华南	storage.ZoneHuanan
//北美	storage.ZoneBeimei
// huadong huabei huanan beimei
func (q *Qiniu) SetZone(z string) {
	if z == "" {
		z = "huadong"
	}
	cfg := storage.Config{}
	switch z {
	case "huadong":
		cfg.Zone = &storage.ZoneHuadong
		break
	case "huabei":
		cfg.Zone = &storage.ZoneHuabei
		break
	case "huanan":
		cfg.Zone = &storage.ZoneHuanan
		break
	case "beimei":
		cfg.Zone = &storage.ZoneBeimei
		break
	default:
		cfg.Zone = &storage.ZoneHuadong
	}
	q.Config.Zone = cfg.Zone
}
