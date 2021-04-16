FROM wyveo/nginx-php-fpm:php80

LABEL MAINTAINER hieuhanufit@gmail.com
COPY ./start.sh /root
RUN chmod +x /root/start.sh

CMD ["/root/start.sh"]