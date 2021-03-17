# 构建阶段 build stage
FROM golang:stretch AS build-env
ADD . /go/src
WORKDIR /go/src

ENV GO111MODULE=on
ENV APP_NAME="passport"
#ENV APP_VERSION=`$(git log -1 --oneline)`
#ENV BUILD_VERSION=$(git log -1 --oneline)
#ENV BUILD_TIME=$(date "+%FT%T%z")
#ENV GIT_REVISION=$(git rev-parse --short HEAD)
#ENV GIT_BRANCH=$(git name-rev --name-only HEAD)
#ENV GO_VERSION=$(go version)

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -a -installsuffix cgo -v -mod=vendor \
-ldflags "'main.AppName=passport' \
#'-X main.AppVersion=`git log -1 --oneline`' \
#-X 'main.BuildVersion=`git log -1 --oneline`' \
#-X 'main.BuildTime=`date "+%FT%T%z"`' \
#-X 'main.GitRevision=`git rev-parse --short HEAD`' \
#-X 'main.GitBranch=`git name-rev --name-only HEAD`' \
-X 'main.GoVersion=`go version`'" \
-o ${APP_NAME}

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
ENV APP_NAME="passport"
ENV APP_ROOT="/data/app/"${APP_NAME}
RUN mkdir -p $APP_ROOT
WORKDIR $APP_ROOT

## 从构建阶段复制构建物
COPY --from=build-env /go/src/passport  $APP_ROOT/

## 增加 配置文件、其他依赖文件
COPY config/config.toml.tpl $APP_ROOT/config/config.toml.tpl
RUN  ls -lsah && pwd && mv ./config/config.toml.tpl ./config/config.toml && ls -lsah  $APP_ROOT/config && cat config/config.toml

## 配置 对外端口
EXPOSE 19000

# 设置启动时预期的命令参数, 可以被 docker run 的参数覆盖掉.
CMD $APP_ROOT/$APP_NAME
