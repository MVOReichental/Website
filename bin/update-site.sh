#! /bin/bash

set -e

cd $(dirname $0)/..

git pull
composer install --no-dev
rm -rf src/main/resources/twig-cache
pushd httpdocs
npm install
popd
git rev-parse HEAD > version