#qz_admin_sales

#接入域名  168sales.qizuang.com

## 容器化
```
docker build -t registry.cn-shanghai.aliyuncs.com/qizuang/168sales-admin-ui .
docker run --rm -p 20860:80 registry.cn-shanghai.aliyuncs.com/qizuang/168sales-admin-ui
```

## 构建和运行

## 安装依赖命令
npm install

## 打包命令
npm run build:prod

## 开发模式
npm run dev