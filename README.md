# StreamDeck Studio

StreamDeck Studio is a web application offering you a tool for creating consistently-styled icons for your [Elgato StreamDeck](https://www.elgato.com/en/welcome-to-stream-deck) device.

## Donating

Hosting ain't cheap! If you like the project and would like to see it maintained, please [consider donating](https://www.buymeacoffee.com/stephancasas) to help take care of hosting costs and the cost of caffeine.

## Usage

Unless otherwise indicated in this repository, the official release of StreamDeck Studio is hosted online at [https://www.streamdeck.studio/](https://www.streamdeck.studio/). This instance of the application will continue to remain online and receive updates unless hosting costs become unfeasible.

### Create an Icon

Use the search field to locate a glyph of your choosing. Click the glyph to select it, and then use the icon editor options to modify the label and colour options. When done, you can drag the icon's image straight into StreamDeck preferences, click the _Download_ button to download the single icon, or click the _Collect_ button to add the icon to your collection.

### Create an Icon Collection

Using the steps described above to create an icon, add any number of created icons to your collection. If you wish to adjust an icon's style, click the icon in your collection to load it into the editor then, make changes, and click the _Collect_ button again. To remove an icon from a collection, hover over the icon's thumbnail and click the _Delete_ (❌) button. Once you're satisfied with your collection, you can click the _Download Collection_ button to archive your icon collection to disk as a ZIP-compressed archive.

## Installation

This is a web application, and cannot be installed as a desktop app. If you are not installing for contribution or self-hosting, turn-back here and use the hosted application at [https://www.streamdeck.studio/](https://www.streamdeck.studio/). Otherwise, please continue...

### Prerequisites

StreamDeck.studio is designed to run containerized, and thus requires an installation of Docker on your host system. For development environments, Docker Desktop will work.

### docker-compose

Run `./install.sh` from the project's root directory and specify `local` or `production` from the given options. This will create a symbolic link to the appropriate `docker-compose.${ENV}.yml` in the `docker/` directory. Once complete, review your `.env` file and then run `docker-compose up` or `docker-compose up -d` to get started.

### Local Environment

The local developer environment is designed on Laravel Sail — Laravel's prescribed developer container based on `php artisan serve`. This environment can serve over HTTP or HTTPS, but does not create a LetsEncrypt certbot container for certificate renewal, and does not create additional containers for nginx or worker processes.

As ancillary processes are not maintained automatically by Supervisor in the local environment, your workflow should include a process that creates a terminal session for the following commands:

- `docker-compose up` or `sail up`
- `sail artisan queue:work`

### Production Environment

The production environment is built from scratch using and extending official PHP, Nginx, mySQL, Redis, and Certbot containers from Docker Hub. A completed `.env` file should yield a successful startup on the first run of `docker-compose up` or `docker-compose up -d`.

It is important to note that the production environment _does_ include an automatically-maintained certbot container. Setting the `APP_ENV` value in your `.env` file to `production` will cause `certbot` to request a production-ready certificate in non-staging mode. If you need to test your certificate requests prior to requesting a production certificate, you can modify your `.env` file to set `APP_ENV=local` or, with `APP_ENV=production`, set `CERTBOT_STAGING=true`.

#### Certbot Automatic Renewal

Automatic renewal of your application's LetsEncrypt certificates is provided by `docker/certbot/scripts/maintain-cert.sh`, which is designated as an overriden entrypoint on the official Certbot container image. This script will check for renewal eligibility daily and process accordingly.

#### Reloading Nginx

When certificate renewal occurs, it is necessary for Nginx to reload so that the new certificates can be pushed into memory. This process is automatically handled by making use of the `--post-hook` option on `certbot` to invoke a script, `docker/certbot/scripts/reload-cert.sh`.

On successful renewal, the hook-called script will sent a message via TCP to the Nginx container, which is listening for messages with `netcat`. This netcat listener is initialized by the script `docker/nginx/scripts/docker-entrypoint.d/31-listen-cert-reload.sh` which is, in turn, initialized by the container's default entrypoint script (a routine which polls the `%nginx_container_root%/docker-entrypoint.d/` directory for executable scripts, sorts the found scripts alphanumerically-ascending by name, and then runs each script in order).On receipt of a valid reload message, the netcat listener will call `docker/nginx/scripts/opt/handle-cert-reload.sh` which invokes `nginx reload` using the container's default entrypoint script.

It is important to call `nginx reload` using the container entrypoint as a "wrapper" because (espeically on first run) doing so will ensure that `docker/nginx/scripts/docker-entrypoint.d/11-mount-config.sh` is run before nginx reloads. This is the script which is responsible for checking that certificates are available for serving your application over HTTPS with your specified `APP_HOSTNAME`. Absent a valid set of certificates, config which is mounted will serve HTTP-only — allowing only enough access for `certbot` to perform webroot-based domain verification. Operating in this strategic order enables automatic certificate renewal or wholesale modification of `APP_HOSTNAME` in the event of a domain name changeover.

#### Automatic Install

Both the `app` and `worker` containers are maintained by `supervisord`. On each startup, the `app` container will run `docker/php/scripts/install.sh` — taking care of updating database schema, building CSS/JS assets, seeding, and other tasks. This process must complete successfully before the worker process can invoke `php artisan queue:work`.

To handle this ordered execution, the `docker/php/scripts/worker.sh` uses the GNU `read` command to ingest input from a FIFO pipe at `storage/logs/queue_worker.fifo` (auto-generated by the script — also gitignored). This blocking process causes the supervisord-invoked `worker.sh` to hang (with a specified timeout of 4 minutes) until data is written to the FIFO by `install.sh` — running inside the `app` container.

Cross-service process-blocking in this way is a minimal but highly-reusable approach to the logic engaged by many containerized apps via the popular `waitforit.sh`[https://github.com/vishnubob/wait-for-it] shell script. However, instead of testing for availability of a TCP port, the blocked process is waiting for an IPC message from another service. Controlled service startup order helps to mitigate errors encountered by the queue worker which could contribute to a fatal error, thus requiring reboot/restart of the application entirely.

## Contributing

Please follow the instructions in the **Install** section of this README to setup a development environment. Pull requests with clean commits are welcome!

## Let's Be Friends

I need some more developer friends! Please follow me on [Twitter](https://www.twitter.com/stephancasas) or add me on [LinkedIn](https://www.linkedin.com/in/stephancasas).
