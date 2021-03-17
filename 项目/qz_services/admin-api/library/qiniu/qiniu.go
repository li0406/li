package qiniu

import (
    "bytes"
    "context"
    "fmt"
    "github.com/gogf/gf/g/util/grand"
    "github.com/qiniu/api.v7/auth/qbox"
    "github.com/qiniu/api.v7/storage"
    "time"
)

type QiniuUploader struct {
    accessKey string
    secretKey string
    bucket string
}

func NewQiniuUploader(accessKey string, secretKey string, bucket string) *QiniuUploader {
    return &QiniuUploader{
        accessKey: accessKey,
        secretKey: secretKey,
        bucket: bucket,
    }
}

// 上传
func (that *QiniuUploader) UploadByte(data []byte, module string) (string, error) {
    putPolicy := storage.PutPolicy{
        Scope: that.bucket,
    }

    mac := qbox.NewMac(that.accessKey, that.secretKey)
    upToken := putPolicy.UploadToken(mac) // 获取七牛云的token

    cfg := storage.Config{}
    // 空间对应的机房
    cfg.Zone = &storage.ZoneHuadong
    // 是否使用https域名
    cfg.UseHTTPS = false
    // 上传是否使用CDN上传加速
    cfg.UseCdnDomains = false

    formUploader := storage.NewFormUploader(&cfg)
    ret := storage.PutRet{}
    putExtra := storage.PutExtra{
        Params: map[string]string{
            "x:name": "github logo",
        },
    }

    dataLen := int64(len(data))
    fileKey := that.makekey(module)
    err := formUploader.Put(context.Background(), &ret, upToken, fileKey, bytes.NewReader(data), dataLen, &putExtra)
    return fileKey, err
}

func (that *QiniuUploader) makekey(module string) string {
    datetime := time.Unix(time.Now().Unix(), 0).Format("200601")
    fileName := grand.Letters(32) // 使用goframe的随机数生成32位字符串

    return fmt.Sprintf("%s/%s/%s", module, datetime, fileName)
}