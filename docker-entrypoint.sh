#! /bin/bash

data_dir="/app/data"

for folder in forms mail-queue pictures profile-pictures twig-cache uploads; do
    mkdir -p ${data_dir}/${folder}
done

chown -R www-data:www-data ${data_dir}

exec "$@"