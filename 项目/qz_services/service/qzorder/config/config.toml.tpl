# mv config.toml.tpl to config.toml
# 应用系统设置
[setting]
	addr = "0.0.0.0"
	port = "18800"
    logpath = "runtime/log"
    app_debug = false
    APP_ENV = "REPLACE_ENV"

# 数据库连接
[database]
    [[database.default]]
        host = "REPLACE_HOST"
        port = "3306"
        user = "REPLACE_USER"
        pass = "REPLACE_PASSWORD"
        name = "REPLACE_NAME"
        type = "mysql"