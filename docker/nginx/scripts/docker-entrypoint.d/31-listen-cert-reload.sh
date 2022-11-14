#!/bin/ash

# check that nc listener is not already running
if [ -z $(ps -o comm= | grep '^nc$') ]; then

    # listen for reload messages from certbot with netcat
    nc -lk -p "${NGINX_RELOAD_LISTENER_PORT:-4444}" \
        -e /opt/scripts/handle-cert-reload.sh &
    echo "Started listening for reload messages from certbot on port ${NGINX_RELOAD_LISTENER_PORT:-4444}." >&2

else

    echo "Nginx is already listening for reload messages from certbot on port ${NGINX_RELOAD_LISTENER_PORT:-4444}." >&2

fi
