#!/bin/sh

# check for existing symlink
if [ -h "docker-compose.yml" ]; then
    while [ "$CHOICE" != "y" ] && [ "$CHOICE" != "n" ]; do
        clear
        printf "\e[1;31m[!] There is an existing symbolic link for docker-compose.yml. \n    Are you sure you want to overwrite it? [y/n]: \e[0m"
        read -r CHOICE
    done
    [ "$CHOICE" = "n" ] && printf '\nExiting with no changes made...\n' && exit 0
fi

# check for existing file
if [ -f "docker-compose.yml" ]; then
    while [ "$CHOICE" != "y" ] && [ "$CHOICE" != "n" ]; do
        clear
        printf "\e[1;31m[!] There is an existing file named docker-compose.yml. \n    Are you sure you want to overwrite it? [y/n]: \e[0m"
        read -r CHOICE
    done
    [ "$CHOICE" = "n" ] && printf '\nExiting with no changes made...\n' && exit 0
fi

clear

# prompt for environment
while [ "$ENV" != "1" ] && [ "$ENV" != "2" ]; do
    [ ! -z "$ENV" ] && printf " \e[1;37;1;41m[!] Invalid option -- try again.\e[0m\n\n"

    echo "Specify an environment:"
    echo "  [1]: local"
    echo "  [2]: production"
    printf '\n  Selection: '

    read -r ENV

done

case $ENV in
1)
    ENV='local'
    DOCKER_COMPOSE='dev'
    ;;
2)
    ENV='production'
    DOCKER_COMPOSE='prod'
    ;;
esac

clear

# remove existing docker-compose.yml (if any) and link new
printf "\e[1;32m[*] Linking docker/docker-compose.%s.yml to project root as docker-compse.yml...\e[0m\n\n" $DOCKER_COMPOSE
rm -f docker-compose.yml
ln -s "docker/docker-compose.$DOCKER_COMPOSE.yml" "docker-compose.yml"

exit 0
