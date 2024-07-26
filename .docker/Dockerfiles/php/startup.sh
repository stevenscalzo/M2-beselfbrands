#!/bin/bash
bin/magento cron:install
service cron start
docker-php-entrypoint php-fpm
