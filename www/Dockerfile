FROM php:7.3-apache

USER root

WORKDIR /usr/local/docker

RUN echo "deb http://mirrors.ustc.edu.cn/debian stable main contrib non-free" > /etc/apt/sources.list \
    && echo "deb-src http://mirrors.ustc.edu.cn/debian stable main contrib non-free" >> /etc/apt/sources.list \
    && echo "deb http://mirrors.ustc.edu.cn/debian stable-proposed-updates main contrib non-free" >> /etc/apt/sources.list \
    && echo "deb-src http://mirrors.ustc.edu.cn/debian stable-proposed-updates main contrib non-free" >> /etc/apt/sources.list \
    && rm -f /etc/apt/sources.list.d/* \
    && apt update\
    && apt install -y libwebp-dev libjpeg-dev libpng-dev libfreetype6-dev\
    && docker-php-source extract\
    && cd /usr/src/php/ext/gd\
    && docker-php-ext-configure gd --with-webp-dir=/usr/include/webp --with-jpeg-dir=/usr/include --with-png-dir=/usr/include --with-freetype-dir=/usr/include/freetype2\
    && docker-php-ext-install gd mysqli\
    && docker-php-ext-install exif\
    # && docker-php-ext-enable gd mysqli exif\
    && php -m | grep gd && php -m | grep mysqli && php -m | grep exif\
    && a2enmod rewrite ssl
    # 以上处理gd库（jpeg）以及mysqli, exif支持
    # 启用 rewrite,ssl

# 初始化
# CMD ["sh", "/var/www/html/init.sh"]
CMD ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf & chown -R www-data:www-data /var/www/html & apache2-foreground

# 暴露端口
EXPOSE 80 443