#!/usr/bin/env bash

# update-potpo.sh

source app/bin/i18n/config.sh

echo "Regenerating cache"
php app/bin/twigcache.php

echo "Running xgettext on the cached files"
xgettext -o ${LOCALE_FOLDER}/messages.pot --from-code=UTF-8 -n --omit-header --no-location /tmp/cache/*/*.php

for folder in $(find ${LOCALE_FOLDER} -maxdepth 1 -type d | awk -F/ '{print $NF}')
do
    if [ "${folder}" != ${LOCALE_FOLDER} ]; then
        if [[ -f ${LOCALE_FOLDER}/${folder}/LC_MESSAGES/messages.po ]]; then
            echo "Merging for ${folder}"
            msgmerge -U Locale/${folder}/LC_MESSAGES/messages.po ${LOCALE_FOLDER}/messages.pot
        else
            echo "Initializing for ${folder}"
            msginit --locale=${folder} --output-file=${LOCALE_FOLDER}/${folder}/LC_MESSAGES/messages.po --input=${LOCALE_FOLDER}/messages.pot
        fi
    fi
done
