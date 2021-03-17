# mv config.toml.tpl to config.toml
# 应用系统设置
[setting]
	addr = "0.0.0.0"
	port = "16000"
    logpath = "./log"
    stdout  = "true"
    app_debug = false
 [elasticsearch]
    host = "REPLACT_ELASTIC_HOST"
    username = "REPLACT_ELASTIC_USERNAME"
    password = "REPLACT_ELASTIC_PASSWORD"
 [redis]
     host = "REPLACEME_REDIS_HOSTNAME"
     port = "6379"
# 数据库连接
[database]
    [[database.default]]
        host = "REPLACE_HOST"
        port = "3306"
        user = "REPLACE_USER"
        pass = "REPLACE_PASSWORD"
        name = "REPLACE_NAME"
        type = "mysql"