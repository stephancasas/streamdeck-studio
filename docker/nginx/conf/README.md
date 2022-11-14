# NGINX Templates

Templating is not a native NGINX function. This is provided in the container entrypoint by `/docker-entrypoint.d/20-envsubst-on-templates.sh`

## Pre-SSL and Post-SSL

Because shipping placeholder certs in the repository leads to overwrite issues on push/pull, and NGINX will crash if config-specified certs are not present at runtime, it is more efficient to ship two configurations — one which serves HTTP-only for `certbot` and another which will serve HTTPS *after* `certbot` has provided valid certificates.

## Config Selection

As the container entrypoint will overwrite the template-defined configurations, simply updating the symbolic link on `default.conf.template` from `pre-ssl.conf` to `post-ssl.conf` will enable a quick method through which the appropriate configuration can be populated and loaded post-startup.

Updating of the symbolic link is handled via an additional startup script, `11-mount-config.sh`, mapped from the `../scripts` directory into `/docker-entrypoint.d`. The container's default entrypoint script executes this script *before* the substitution script runs — allowing the correct configuration to apply at runtime.