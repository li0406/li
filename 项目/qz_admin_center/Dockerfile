FROM registry.cn-shanghai.aliyuncs.com/qizuang/php:7.0-fpm-alpine-nginx

ENV APPNAME=admin-168uc
WORKDIR /data/app/${APPNAME}
COPY . .
RUN chown -R www:www .

#部署
RUN mv ./deploy/nginx/168uc.qizuang.com.conf /etc/nginx/conf.d/ \
    && rm -rf ./deploy
#RUN rm /etc/nginx/conf.d/default.conf

#应用配置
RUN mv Application/Common/Conf/config.sample.php Application/Common/Conf/config.php

EXPOSE 80 443

#CMD ["/bin/s6-svscan", "/etc/s6/"]
