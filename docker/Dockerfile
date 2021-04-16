FROM wyveo/nginx-php-fpm:php80

WORKDIR /var/www/html
LABEL MAINTAINER hieuhanufit@gmail.com
COPY ./start.sh /root
RUN chmod +x /root/start.sh
ADD . ./docker
RUN mkdir -p /etc/nginx/ssl &&\
    cp ./docker/ssl/* /etc/nginx/ssl

CMD ["/root/start.sh"]