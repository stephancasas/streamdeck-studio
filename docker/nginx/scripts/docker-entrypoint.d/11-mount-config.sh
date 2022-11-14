#!/bin/ash

rm -f /etc/nginx/templates/default.conf.template

if [ -f "/etc/cert/live/${APP_HOSTNAME}/chain.pem" ]; then
    echo "LetsEncrypt certificates for '${APP_HOSTNAME}' were found in certificate store." >&2
    echo "Nginx will load with HTTPS configuration." >&2

    ln -s \
        /etc/nginx/templates/post-ssl.conf \
        /etc/nginx/templates/default.conf.template
else
    echo "LetsEncrypt certificates for '${APP_HOSTNAME}' are not present in certificate store." >&2
    echo "Nginx will load with HTTP configuration." >&2

    ln -s \
        /etc/nginx/templates/pre-ssl.conf \
        /etc/nginx/templates/default.conf.template
fi
