FROM mariadb:10.4

RUN rm -rf /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Madrid /etc/localtime
COPY conf.cnf /etc/mysql/conf.d/conf.cnf