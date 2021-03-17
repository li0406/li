
# fenci
服务化端口范围 10000 - 20000

分词服务
服务名 fenci.service
分配端口 11001

python项目

## 容器
部署用
```
docker build  -t registry.cn-shanghai.aliyuncs.com/qizuang/fenci.service ./
docker run -d --name fenci.service --restart=always -p 11001:11001 -v /data/app/fenci.service/dict/:/data/app/fenci.service/dict/ registry.cn-shanghai.aliyuncs.com/qizuang/fenci.service
# docker push registry.cn-shanghai.aliyuncs.com/qizuang/fenci.service
```

开发测试用
```
开发build
docker build  -t registry.cn-shanghai.aliyuncs.com/qizuang/fenci.service ./
开发测试
docker run --rm -p 11001:11001  registry.cn-shanghai.aliyuncs.com/qizuang/fenci.service
```

### 容器数据目录
词库
```
/data/app/fenci.service/dict/
```

## requirements.txt
项目依赖包列表
```
pip install pipreqs
pipreqs ./
```

## 词库文件
dict.txt

替换原词库
路径:/usr/lib/python2.7/site-packages/jieba/dict.txt