# mv config.toml.tpl to config.toml
# 应用系统设置
[setting]
	addr = "0.0.0.0"
	port = "12000"
    logpath = "./log"
    stdout  = "true"
    app_debug = false
    app_env = "REPLACE_ENV"
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
