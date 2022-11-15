#!/bin/ash

# initialize ipc pipe to queue worker
QUEUE_WORKER_IPC='/var/www/html/storage/logs/queue_worker.fifo'
[ ! -p $QUEUE_WORKER_IPC ] &&
    mkfifo $QUEUE_WORKER_IPC &&
    chmod 777 $QUEUE_WORKER_IPC

# --- Install/Update Application --------------------------------------------- #

[ -z "$APP_KEY" ] &&
    echo "Generating application key..." &&
    php artisan key:generate --force >/dev/null

if [ ! -d "$WEB_ROOT/node_modules" ]; then

    echo "Installing node modules..." &&
        npm i >/dev/null &&
        echo "Building CSS/JS assets..." &&
        npm run build >/dev/null

else # rebuild assets if stale

    BUILD_DIR="$WEB_ROOT/public/build"
    BUILD_EXPIRY=604800 # one week

    [ $(($(date +%s) - $(date -r $BUILD_DIR +%s))) -gt $BUILD_EXPIRY ] &&
        echo "CSS/JS assets are stale. Rebuilding CSS/JS assets..." &&
        npm run build >/dev/null

fi

if [ ! -d "$WEB_ROOT/vendor"]; then
    echo "Installing Composer packages..." &&
        composer install >/dev/null

else # update packages if stale

    VENDOR_DIR="$WEB_ROOT/vendor"
    VENDOR_EXPIRY=604800 # one week

    [ $(($(date +%s) - $(date -r $VENDOR_DIR +%s))) -gt $BUILD_EXPIRY ] &&
        echo "Composer packages are stale. Updating..." &&
        composer install >/dev/null

fi

echo "Updating database schema..." &&
    php artisan migrate --force >/dev/null

echo "Clearing cached views..." &&
    php artisan view:clear

RECORD_COUNT=$(php artisan tinker --execute 'echo(FontAwesomeGlyph::all()->count());' | grep "\d")
[ $RECORD_COUNT -eq 0 ] &&
    echo "Seeding database..." &&
    php artisan db:seed Glyphs --force >/dev/null

# ---------------------------------------------------------------------------- #

# apply host-guest permissions
chown -R :82 . && echo "Applying ownership re-assignment..." &&
    chmod -R 775 . && echo "Applying permissions re-assignment..." &&
    chmod -R g+s . && echo "Applying ownership inheritance re-assignment..."

# release blocking process on queue worker
echo 'Sending release message to queue worker...'
printf '.' >$QUEUE_WORKER_IPC

exit 0
