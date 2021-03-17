package qiniu

import (
	"context"

	"github.com/qiniu/api.v7/v7/storage"
)

func (q *Qiniu) PutFile(file string) (ReturnBody, error) {

	localFile := file
	key := "" // 上传策略 如果强制开启了savekey, 这个手动指定的key无效, 这里留空

	//putPolicy := storage.PutPolicy{
	//	Scope:        bucket,
	//	ForceSaveKey: true,                                        //强制开启savekey
	//	SaveKey:      "video/$(year)/$(mon)/$(day)/$(etag)$(ext)", // video/2020/01/17/Fn6qeQi4VDLQ347NiRm.mp4
	//}
	upToken := q.PutPolicy.UploadToken(q.Mac)

	// 构建表单上传的对象
	formUploader := storage.NewFormUploader(q.Config)
	ret := ReturnBody{}
	err := formUploader.PutFile(context.Background(), &ret, upToken, key, localFile, q.PutExtra)
	if err != nil {
		return ret, err
	}
	return ret, nil
}
