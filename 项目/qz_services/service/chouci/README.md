# chouci
抽词服务
服务名 chouci.service
分配端口 11000

python项目

## 容器打包
部署用
```
docker build  -t registry.cn-shanghai.aliyuncs.com/qizuang/chouci.service ./
docker run -d --name chouci.service --restart=always -p 11000:11000  registry.cn-shanghai.aliyuncs.com/qizuang/chouci.service
# docker push  registry.cn-shanghai.aliyuncs.com/qizuang/chouci.service
```



开发测试用
```
开发build
docker build  -t registry.cn-shanghai.aliyuncs.com/qizuang/chouci.service ./
开发测试
docker run --rm -p 11001:11001  registry.cn-shanghai.aliyuncs.com/qizuang/chouci.service
```


## requirements.txt
项目依赖包列表
```
pip install pipreqs
pipreqs ./
```