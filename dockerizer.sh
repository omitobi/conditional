#!/bin/sh

no_rebuild=$1

if [ "$no_rebuild" != '--no_rebuild' ]
then
  printf "Rebuilding docker image because of missing --no_rebuild flag\n"
  docker-compose build
fi

docker-compose up -d --remove-orphans

docker-compose exec conditional composer install