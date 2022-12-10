#!/bin/bash

trap 'clear && printf "\e[0m" && exit 0' SIGINT

# --- App Source ------------------------------------------------------------- #

CHOICE=""
while [ "$CHOICE" != "y" ] && [ "$CHOICE" != "n" ]; do
    clear
    printf "\e[1;33m[?] Are you sure you want to update the application? [y/n]: \e[0m"
    read -r CHOICE
done
[ "$CHOICE" = "n" ] && printf '\nExiting with no changes made...\n' && exit 0

git fetch origin master
git reset --hard FETCH_HEAD

# --- CSS/JS Assets ---------------------------------------------------------- #

CHOICE=""
while [ "$CHOICE" != "y" ] && [ "$CHOICE" != "n" ]; do
    clear
    printf "\e[1;33m[?] Would you like to rebuild CSS/JS assets? [y/n]: \e[0m"
    read -r CHOICE
done
if [ "$CHOICE" = "n" ]; then
    printf '\e[1;30m    Skipping asset CSS/JS rebuild...\n\e[0m'
else
    printf '\e[1;30m    Sending rebuild instruction to container...\e[0m'

    bash -c 'docker exec app npm i && npm run dev' &>/dev/null

    printf '\n\e[1;32m[*] Finished rebuilding CSS/JS assets.\n\e[0m'
fi

# --- Composer Packages ------------------------------------------------------ #

CHOICE=""
while [ "$CHOICE" != "y" ] && [ "$CHOICE" != "n" ]; do
    printf "\n\e[1;33m[?] Would you like to update Composer packages? [y/n]: \e[0m"
    read -r CHOICE
done
if [ "$CHOICE" = "n" ]; then
    printf '\e[1;30m    Skipping Composer package updates...\n\e[0m'
else
    printf '\e[1;30m    Sending update instruction to container...\e[0m'

    bash -c \
        'docker exec app composer install --ignore-platform-reqs' \
        &>/dev/null

    printf '\n\e[1;32m[*] Finished updating Composer packages.\n\e[0m'
fi

# --- Database Schema -------------------------------------------------------- #

CHOICE=""
while [ "$CHOICE" != "y" ] && [ "$CHOICE" != "n" ]; do
    printf "\n\e[1;33m[?] Would you like to update the database schema? [y/n]: \e[0m"
    read -r CHOICE
done
if [ "$CHOICE" = "n" ]; then
    printf '\e[1;30m    Skipping database schema updates...\n\e[0m'
else
    printf '\e[1;30m    Sending migrate instruction to container...\e[0m'

    bash -c 'docker exec app php artisan migrate --force' &>/dev/null

    printf '\n\e[1;32m[*] Finished updating database schema.\n\e[0m'
fi

# --- App Reboot ------------------------------------------------------------- #

printf '\n\e[1;31m[!] Restarting the application...\n\e[0m'

printf '\e[1;30m    Stopping application services...\n\e[0m'
docker-compose stop &>/dev/null

printf '\e[1;30m    Starting application services...\n\e[0m'
docker-compose up -d &>/dev/null

printf '\n\e[1;32m[*] The application finished applying updates.\n\e[0m'

exit 0
