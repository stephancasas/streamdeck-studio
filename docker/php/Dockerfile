# ------------------------------------------------------------------------------
# Base PHP Image
# ------------------------------------------------------------------------------
FROM php:8.1.12-fpm-alpine AS php_base

# set www dir
ARG WEB_ROOT='/var/www/html'

# use extension installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync

# install dependencies
RUN install-php-extensions \
    bcmath \
    pdo_mysql \
    intl \
    gd \
    exif \
    redis \
    pcntl \
    zip

# install supervisor
RUN apk add --update supervisor

# start supervisor with specified config
CMD [ "/usr/bin/supervisord" ]

# expose php on port 9000 (www.conf)
EXPOSE 9000


# ------------------------------------------------------------------------------
# App Image
# ------------------------------------------------------------------------------
FROM php_base AS php_app

ARG WEB_ROOT

# does this container use ssh?
ARG SSH_USER
ARG SSH_PASSWORD

# install openssh
RUN [ ! -z "$SSH_USER" ] && apk add --update openssh-server \
    && ssh-keygen -A \
    && echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config \
    && adduser -h "/home/$SSH_USER" -s /bin/sh -D -G www-data "$SSH_USER" \
    && echo -n "$SSH_USER:$SSH_PASSWORD" | chpasswd \
    || echo "Skipping OpenSSH install."

# install nodejs/npm
RUN apk add --update nodejs-current
RUN apk add --update npm

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ------------------------------------------------------------------------------
# Worker Image
# ------------------------------------------------------------------------------
FROM php_base AS php_worker

# install cron with laravel task scheduler
RUN apk add --update apk-cron \
    && printf "* * * * * cd ${WEB_ROOT} && php artisan schedule:run >> /dev/null 2>&1" > /var/spool/cron/crontabs/www-data