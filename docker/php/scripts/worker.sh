#!/bin/sh

export $(grep -v '^#' .env | xargs)

TIMEOUT=240
QUEUE_WORKER_IPC="/var/www/html/storage/logs/queue_worker.fifo"
[ ! -p $QUEUE_WORKER_IPC ] &&
    mkfifo $QUEUE_WORKER_IPC &&
    chmod 777 $QUEUE_WORKER_IPC

# flush ipc pipe before waiting for new message
#   -- use `read` with minimal timeout (alpine doesn't have non-blocking `dd`)
while read -r -t 0.1 -n1 NUL <>$QUEUE_WORKER_IPC; do
    echo >/dev/null # no-op
done

# await message from main app process before starting queue worker
[ -p $QUEUE_WORKER_IPC ] && read -r -t $TIMEOUT -n1 MSG <>$QUEUE_WORKER_IPC

if [ -z "$MSG" ]; then
    echo "Timed-out after waiting $TIMEOUT seconds for \"ready\" message from app service."
    echo 'Attempting to start queue worker with unknown app service state.'
else
    echo 'Received "ready" message from app service.'
    echo 'Starting queue worker.'
fi

# start queue worker
php artisan queue:work
