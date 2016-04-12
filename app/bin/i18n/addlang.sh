#!/usr/bin/env bash

# addlang.sh

source app/bin/i18n/config.sh

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

if [ -z "$1" ]; then
for folder in $(find ${LOCALE_FOLDER} -maxdepth 1 -type d | awk -F/ '{print $NF}')
do
    if [ "${folder}" != ${LOCALE_FOLDER} ]; then
        echo "Executing locale-gen ${folder} ${folder}.UTF-8"
        locale-gen ${folder} ${folder}.UTF-8
    fi
done
echo "Executing updates..."
update-locale
dpkg-reconfigure locales
fi

if [ -n "$1" ]; then
echo "Executing locale-gen $1 $1.UTF-8"
locale-gen $1 $1.UTF-8

echo "Executing updates..."
update-locale
dpkg-reconfigure locales

echo "Creating folder: ${LOCALE_FOLDER}/$1/LC_MESSAGES"
sudo -u ${REGULAR_USER} mkdir -p ${LOCALE_FOLDER}/$1/LC_MESSAGES
fi