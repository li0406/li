# build stage
FROM golang:stretch AS build-env
ADD . /go/src
WORKDIR /go/src

ENV GO111MODULE=on
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -mod=vendor -o qzws
RUN pwd && ls -lsa

# final stage
FROM nginx:alpine
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories
RUN apk update \
    && apk upgrade \
    && apk add supervisor \
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

ENV APP_NAME="qzws"
ENV APP_ROOT="/data/app/"$APP_NAME
RUN mkdir -p $APP_ROOT
WORKDIR $APP_ROOT
COPY --from=build-env /go/src/qzws  $APP_ROOT/

# 运行环境和配置
COPY deploy/nginx/wsapi.qizuang.com.key /etc/nginx/ssl/
COPY deploy/nginx/wsapi.qizuang.com.pem /etc/nginx/ssl/
COPY deploy/nginx/wsapi.qizuang.com.conf /etc/nginx/conf.d/
COPY deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

## nginx设置
RUN set -xe \
    && mkdir -p /run/nginx \
    # forward nginx logs to docker log collector
    && sed -i -E "s/error_log .+/error_log \/dev\/stderr warn;/" /etc/nginx/nginx.conf \
    && sed -i -E "s/access_log .+/access_log \/dev\/stdout main;/" /etc/nginx/nginx.conf

## app设置
COPY config/config.toml.tpl $APP_ROOT/config/
RUN  ls -lsah \
	&& pwd \
	&& mv ./config/config.toml.tpl ./config/config.toml \
	&& ls -lsah  $APP_ROOT/config


EXPOSE 18000 80 443

# supervisord start nginx & app
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]