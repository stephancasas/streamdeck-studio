#!/bin/ash

# digest message
read MESSAGE

echo "Received message '$MESSAGE' from certbot on port ${NGINX_RELOAD_LISTENER_PORT:-4444}." >&2

# verify content and reload
if [ "$MESSAGE" = "${NGINX_RELOAD_MESSAGE:-RELOAD_NGINX}" ]; then
    /docker-entrypoint.sh nginx -s reload -c /etc/nginx/nginx.conf >&2
fi

# reply to certbot and hangup
echo "ok"
