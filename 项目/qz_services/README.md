# 服务化
服务化端口范围 10000 - 20000

## 服务化管理后台API
http://admin.service:20000

## 抽词服务
http://chouci.service:11000

## 分词服务
http://fenci.service:11001

## 短信服务
http://qzsms:12000


## 项目结构
* admin-api 服务化管理后台api

* admin-ui 服务化管理后台前端

* service 各个service服务模块

所有项目都容器化,单机使用docker-compose 部署

## 部署

### 168service.qizuang.com 为服务化管理后台网址

"/" 为  admin-ui vue构建后的静态文件目录

"/api" 为 admin-api go构建后的nginx转发目录  /api 转发 http://admin-api.service:20000



### service 目录下的为服务模块

直接按照"服务化的方式部署"

构建
```
docker-compose  build
docker-compose  push
```

运行
```
docker-compose  up -d
```

更新
```
docker-compose  pull
docker-compose  restart
docker-compose  up -d
```

## 验证
admin-ui & admin-api：168uc.qizuang.com 单击短信网关服务管理
admin-api: 单击发送短信进去
qz_sms: 发送至手机号码测试
chouci_service：不清楚
fenci_service：不清楚

