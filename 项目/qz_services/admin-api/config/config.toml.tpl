[setting]
    logpath = "runtime/log"
    loglevel = "LEVEL_ALL"
    jwt_token = "REPLACE_TOKEN"
    app_debug = false
[database]
    [[database.default]]
        host = "REPLACE_HOST"
        port = "3306"
        user = "REPLACE_USER"
        pass = "REPLACE_PASSWORD"
        name = "REPLACE_NAME"
        type = "mysql"
[sms]
    url = "http://qzsms:12000"
[qiniu]
    [qiniu.qzupload]
        bucket = "qizuang-upload"
        access_key = "Wt0BTnEFDP2HEj8KaTAOWZ1_V9SbrEIx4EOkylr2"
        secret_key = "2fhPo2--sy9Sxu-nOTcddGU9qQQJuYiS6IYfCiP9"
        domain = "https://staticqn.qizuang.com"