package common

import (
    "admin-api/app/enum"
    "admin-api/app/logic"
    "admin-api/library/qiniu"
    "admin-api/library/util"
    "github.com/gogf/gf/g"
    "github.com/gogf/gf/g/frame/gmvc"
)

type CommonController struct {
    gmvc.Controller
}

// 文件上传
func (that *CommonController) Upload(){
    module := that.Request.GetString("module", "common")
    if file, h, err := that.Request.FormFile("file"); err == nil {
        defer file.Close()
        buffer := make([]byte, h.Size)
        _,_ = file.Read(buffer)

        config := g.Config()
        bucket := config.GetString("qiniu.qzupload.bucket")
        accessKey := config.GetString("qiniu.qzupload.access_key")
        secretKey := config.GetString("qiniu.qzupload.secret_key")
        qiniudomain := config.GetString("qiniu.qzupload.domain")

        // 上传
        qiniuUploader := qiniu.NewQiniuUploader(accessKey, secretKey, bucket)
        url, err := qiniuUploader.UploadByte(buffer, module)
        if err != nil {
           ret, _ := util.EncodeResponseJson(5000002, "文件上传失败", nil)
           _ = that.Response.WriteJson(ret)
           that.Exit()
        }

        reapData := g.Map{
           "img_path": url,
           "img_full": qiniudomain + "/" + url,
        }
        ret, _ := util.EncodeResponseJson(0, enum.CODE_0, reapData)
        _ = that.Response.WriteJson(ret)
        that.Exit()
    }

    ret, _ := util.EncodeResponseJson(4000002, "请先选择文件", nil)
    _ = that.Response.WriteJson(ret)
    that.Exit()
}

func (that *CommonController) GetAllCity()  {
    cityLogic := logic.NewCityLogic()
    info := cityLogic.GetCityList()
    //设置返回值
    respData := make(map[string]interface{})
    respData["info"] = info
    ret, _ := util.EncodeResponseJson(0, enum.CODE_0, respData)
    _ = that.Request.Response.WriteJson(ret)
}
