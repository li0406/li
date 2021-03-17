FROM alpine:latest

LABEL version="1.0" \
    description="alpine:latest" \
    maintainer="wwek<licoolgo@gmail.com>"

# 配置apk包加速镜像
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

RUN apk update \
    && apk upgrade \
    && apk add s6 \
    && apk add bash \
    # && apk add nghttp2-dev \
    && apk add ca-certificates \
    && apk add wget \
    # && apk add curl \
    # && apk add tcpdump \
    # && apk add bash-completion \
    && apk add iputils \
    && apk add iproute2 \
    && apk add libc6-compat \
    && apk add -U tzdata \
    && rm -rf /var/cache/apk/*

# 系统设置(设置时区)
RUN rm -rf /etc/localtime \
 && ln -s /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

# 设置时区变量
ENV TIME_ZONE Asia/Shanghai

# 安装软件python3,升级pip,setuptools
RUN apk add --no-cache python3 \
    #&& apk add --no-cache python3-dev \
    && python3 -m ensurepip \
    && rm -r /usr/lib/python*/ensurepip \
    && pip3 install --default-timeout=100 --no-cache-dir --upgrade pip \
    && pip3 install --default-timeout=100 --no-cache-dir --upgrade setuptools \
    && if [ ! -e /usr/bin/pip ]; then ln -s pip3 /usr/bin/pip ; fi \
    && if [[ ! -e /usr/bin/python ]]; then ln -sf /usr/bin/python3 /usr/bin/python; fi \
    && rm -rf /var/cache/apk/* \
    && rm -rf ~/.cache/pip

# 设置语言支持
ENV LANG=C.UTF-8

# 配置工作目录
WORKDIR /data/app/fenci.service

# 增加项目文件
ADD requirements.txt ./
## 安装项目依赖包
RUN pip install -r requirements.txt -i http://mirrors.aliyun.com/pypi/simple/ --trusted-host mirrors.aliyun.com
ADD fenci.py ./
ADD dict ./dict


# 配置对外端口
EXPOSE 11001

# 设置启动时预期的命令参数, 可以被 docker run 的参数覆盖掉.
CMD ["python", "fenci.py"]