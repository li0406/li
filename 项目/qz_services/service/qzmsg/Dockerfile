# build stage
FROM golang:stretch AS build-env
ADD . /go/src
WORKDIR /go/src

ENV GO111MODULE=on
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -mod=vendor -o qzmsg
RUN pwd && ls -lsa

# final stage
FROM alpine:latest
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories
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
# 系统设置(设置时区)
RUN rm -rf /etc/localtime \
 && ln -s /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

ENV APP_NAME="qzmsg"
ENV APP_ROOT="/data/app/"$APP_NAME
RUN mkdir -p $APP_ROOT
WORKDIR $APP_ROOT
COPY --from=build-env /go/src/qzmsg  $APP_ROOT/

## 增加 配置文件、其他依赖文件
COPY config/config.toml.tpl $APP_ROOT/config/
#RUN cp -f config/config.toml.tpl config/config.toml \
#    && ls -lsah
#COPY config/config.toml $APP_ROOT/config/
RUN  ls -lsah && pwd && mv ./config/config.toml.tpl ./config/config.toml && ls -lsah  $APP_ROOT/config && cat config/config.toml

EXPOSE 12000

CMD $APP_ROOT/$APP_NAME