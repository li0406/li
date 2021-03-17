# 构建阶段 build stage
FROM golang:stretch AS build-env
ADD . /go/src
WORKDIR /go/src

ENV GO111MODULE=on
ENV APP_NAME="search"
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -mod=vendor -o ${APP_NAME}
RUN pwd && ls -lsa

# 构建物打包阶段 final stage
FROM alpine:latest

## 配置 apk包加速镜像为阿里
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

## 安装 一些基础包
RUN apk update \
    && apk upgrade \
    #&& apk add s6 \
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

## 设置 操作系统时区
RUN rm -rf /etc/localtime \
 && ln -s /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

## 打包 应用
ENV APP_NAME="search"
ENV APP_ROOT="/data/app/"${APP_NAME}
RUN mkdir -p $APP_ROOT
WORKDIR $APP_ROOT

## 从构建阶段复制构建物
COPY --from=build-env /go/src/search  $APP_ROOT/

## 增加 配置文件、其他依赖文件
COPY config/config.toml.tpl $APP_ROOT/config/
RUN cp -f config/config.toml.tpl config/config.toml \
    && ls -lsah

## 配置 对外端口
EXPOSE 16000

# 设置启动时预期的命令参数, 可以被 docker run 的参数覆盖掉.
CMD $APP_ROOT/$APP_NAME
