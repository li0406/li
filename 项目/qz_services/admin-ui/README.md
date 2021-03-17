## admin-api.service:20001

## 容器化
```
docker build -t registry.cn-shanghai.aliyuncs.com/qizuang/admin-ui .
docker run --rm -p 20001:80 registry.cn-shanghai.aliyuncs.com/qizuang/admin-ui
```

## 构建和运行

## 安装依赖命令
npm install

## 打包命令
npm run build:prod

## 开发模式
npm run dev