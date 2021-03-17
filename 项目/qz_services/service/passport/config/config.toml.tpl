# 项目配置文件

# 项目应用
[app]
    debug = false    # 是否开启项目debug
    port = 19000     # 端口号
# 日志配置
[log]
    path = "./runtime/log"  # 日志目录
    level = "ERRO"  # 日志等级
# 认证jwt
[jwt]
    key = "REPLACEME_JWT_SECRET" # 密钥
    expire = "24" # 过期时间/小时,默认2天
    refresh_expire = "168" # 刷新过期时间/小时,默认7天
# 数据库配置
[database]
    [[database.default]]
        host = "REPLACE_HOST"   # host
        port = "3306"   # 端口
        name = "REPLACE_NAME"   # 数据库名称
        user = "REPLACE_USER"   # 数据库用户名
        pass = "REPLACE_PASSWORD"   # 数据库密码
        type = "mysql"  # 类型
        debug= "false"  # 是否开启debug
        charset = "utf8"    # 编码
# redis 配置
[redis]
    host = "REPLACEME_REDIS_HOSTNAME"   # 地址
    port = "6379" # 端口
    pass = "REPLACEME_REDIS_PASSWORD"  # 授权密码