# 构建阶段 vue build stage
FROM node:10.16.0-alpine  AS build-stage-node

## 配置 apk包加速镜像为阿里
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

## 安装 一些基础包
RUN apk update \
  && apk upgrade \
  && apk add git \
  && apk add bash \
  #&& apk add nghttp2-dev \
  && apk add ca-certificates \
  && apk add wget \
  #&& apk add curl \
  #&& apk add tcpdump \
  && apk add iputils \
  && apk add iproute2 \
  && apk add libc6-compat \
  && apk add -U tzdata \
  && rm -rf /var/cache/apk/*

# ## 设置 操作系统时区
RUN rm -f /etc/localtime \
  && ln -s /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

# 设置 工作目录
ENV MY_HOME=/app
RUN mkdir -p $MY_HOME
WORKDIR $MY_HOME

## 安装 全局依赖
RUN npm install -g cross-env

# 安装 项目依赖
COPY package.json ./
RUN npm config set registry https://registry.npm.taobao.org \
  && npm set sass_binary_site=https://npm.taobao.org/mirrors/node-sass/ \
  && npm install

# 复制vue项目文件
COPY . .

# 构建
RUN npm run build:prod

RUN ls -lsah /app

# 打包应用
# 生产阶段 nginx
# 使用nginx镜像运行构建物
FROM nginx:stable-alpine
COPY --from=build-stage-node /app/web /var/www/html
RUN ls -lsha /var/www/html
COPY ./deploy/nginx/168service.qizuang.com.conf /etc/nginx/conf.d/
#RUN rm /etc/nginx/conf.d/default.conf
EXPOSE 80 443
CMD ["nginx","-g","daemon off;"]
