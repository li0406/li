# mv config.toml.tpl to config.toml
# 应用系统设置
[setting]
	addr = "0.0.0.0"
	port = "18100"
    logpath = "runtime/log"
    stdout = true
    app_debug = false
    ws_host = "qzws"
    ws_msg_token = "9993b6724d0fdc8c644f5bc7a004941c"
    APP_ENV = "pred"

# 数据库连接
[database]
    [[database.default]]
        host = "REPLACE_HOST"
        port = "3306"
        user = "REPLACE_USER"
        pass = "REPLACE_PASSWORD"
        name = "REPLACE_NAME"
        type = "mysql"
[dingding]
    [dingding.sms]
        webhook = "https://oapi.dingtalk.com/robot/send?access_token=506dd8ce366d9d35cff0a6363cce858d06b6af7ad551f00d9835092cc7307a18"
[weixin]
    [weixin.official]
        [weixin.official.default]
            appid = "wx9df29223e790e641"
            secret = "49fa97409471315992429f504a2bb42e"
        [weixin.official.develop]
            appid = "wx900342c6f3440e2b"
            secret = "c1c240db298219983281d4492fc44c3f"
    [weixin.applet]
        [weixin.applet.sales]
            appid = "wxe691252a731f0435"