# mv config.toml.tpl to config.toml
# 应用系统设置
[setting]
    addr = "0.0.0.0"
	port = "18000"
	stdout = true
    logpath = "runtime/log"
    jwt_token = "REPLACE_TOKEN"
    app_debug = false
    app_pporf = false # 性能分析 /debug/pporf
	ws_msg_token = "9993b6724d0fdc8c644f5bc7a004941c"

# 数据库连接
[database]
    [[database.default]]
        host = "REPLACE_HOST"
        port = "3306"
        user = "REPLACE_USER"
        pass = "REPLACE_PASSWORD"
        name = "REPLACE_NAME"
        type = "mysql"
