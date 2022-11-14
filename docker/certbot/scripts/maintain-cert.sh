#!/bin/sh

# set prod/staging
if [ "$APP_ENV" = "production" ]; then
    if ! $CERTBOT_STAGING || [ -z "$CERTBOT_STAGING" ]; then
        CERTBOT_PROD=""
    else
        CERTBOT_PROD="--staging"
    fi
else
    CERTBOT_PROD="--staging"
fi

certbot certonly ${CERTBOT_PROD} \
    --domain "${APP_HOSTNAME}" \
    --email "${CERTBOT_EMAIL}" \
    --webroot --webroot-path="${WEB_ROOT}/public" \
    --keep-until-expiring --agree-tos --no-eff-email \
    --post-hook "/opt/scripts/reload-cert.sh"

# try again tomorrow
sleep 1d
exit 0
