# mv config.toml.tpl to config.toml
# 应用系统设置
[setting]
	addr = "0.0.0.0"
	port = "18600"
    logpath = "./log"
    stdout  = "true"
    app_debug = false
    app_env = "REPLACEME_ENV"
# 数据库连接
[database]
    [[database.default]]
        host = "REPLACEME_HOST"
        port = "3306"
        user = "REPLACEME_USER"
        pass = "REPLACEME_PASSWORD"
        name = "REPLACEME_NAME"
        type = "mysql"
[dingding]
    [dingding.sms]
        webhook = "https://oapi.dingtalk.com/robot/send?access_token=506dd8ce366d9d35cff0a6363cce858d06b6af7ad551f00d9835092cc7307a18"
[oss]
	qiniu_ak = "REPLACEME_OSS_QINIU_AK"
	qiniu_sk = "REPLACEME_OSS_QINIU_SK"
	qiniu_bucket_voice = "qizuang-voice"
	qiniu_bucket_voice_zone = "huadong"
	qiniu_bucket_voice_domian = "https://voice.qizuang.com"
[mail]
	mail_smtp_server = "smtpdm.aliyun.com:80"
	mail_smtp_user   = "report@dm.qizuang.com"
	mail_smtp_password = "iS6ddGU9dGU9"
	mail_to  = "李青<liqing0091@dingtalk.com>,李秀桃<lixiutao0637@dingtalk.com>,张艳2<zhangyan23613@dingtalk.com>,陈文娟<chenwenjuan6575@dingtalk.com>,陈凯<chenkai6238@dingtalk.com>,李爽<licoolgo@dingtalk.com>"
	mail_voice_report_open = "REPLACEME_MAIL_MAIL_VOICE_REPORT_OPEN"
[redis]
    default = "REPLACEME_REDIS_HOST:6379?idleTimeout=300"