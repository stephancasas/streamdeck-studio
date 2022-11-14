#!/bin/sh

echo "Sending reload notification to nginx service."

# notify nginx to reload
echo ${NGINX_RELOAD_MESSAGE:-RELOAD_NGINX} | nc ${NGINX_SVC_NAME:-nginx} ${NGINX_RELOAD_LISTENER_PORT:-4444}

exit 0
