package oss

import (
	"qzpnp/library/oss/qiniu"

	"github.com/gogf/gf/frame/g"
)

// OssQiniuUpFile
// 对象存储 七牛上传文件
//
func OssQiniuUpFile(f string, perfix string) (qiniu.ReturnBody, error) {
	gcfg := &qiniu.Gcfg{}

	gcfg.AccessKey = g.Cfg().GetString("oss.qiniu_ak")
	gcfg.SecertKey = g.Cfg().GetString("oss.qiniu_sk")
	gcfg.Bucket = g.Cfg().GetString("oss.qiniu_bucket_voice")
	gcfg.Zone = g.Cfg().GetString("oss.qiniu_bucket_voice_zone")
	gcfg.DomainHost = g.Cfg().GetString("oss.qiniu_bucket_voice_domian")

	q := qiniu.New(gcfg)

	// key 保存的layout
	q.SetSaveKey(perfix + "/$(year)/$(mon)/$(day)/$(etag)$(ext)") // voice/2020/01/17/Fn6qeQi4VDLQ347NiRm.mp3
	//fmt.Println(q.PutPolicy.SaveKey)

	return q.PutFile(f)
}
