#!/usr/bin/env bash

# update-mo.sh

source app/bin/i18n/config.sh

for folder in $(find ${LOCALE_FOLDER} -maxdepth 1 -type d | awk -F/ '{print $NF}')
do
    if [ "${folder}" != ${LOCALE_FOLDER} ]; then
        echo "Compiling .mo for ${folder}"
        msgfmt -c -o Locale/${folder}/LC_MESSAGES/messages.mo ${LOCALE_FOLDER}/${folder}/LC_MESSAGES/messages.po
    fi
done
