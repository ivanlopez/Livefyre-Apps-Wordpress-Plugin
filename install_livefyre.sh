#!/bin/bash

while getopts n:mec option
do
    case "${option}"
    in
        n) BLOGNAME="${OPTARG}";;
        m) MULTISITE=1;;
        e) ENTERPRISE=1;;
	    c) COMMUNITY=1;;
	    s) SITESYNC=1;;
        \?) exit;;
    esac
done

if [[ -z $BLOGNAME ]]; then
	echo "You need tp specify a blogname"
	echo "Format: $0 -n blogname -e OR -c (enterprise or community) -m (for multisite) -s (for site sync)"
	exit 1
fi

if [[ $COMMUNITY && $ENTERPRISE ]]; then
	echo "You cannot have a Community and Enterprise Version at the same time"
	echo "Format: $0 -n blogname -e OR -c (enterprise or community) -m (for multisite) -s (for site sync)"
	exit 1
fi

if [[ -z $COMMUNITY && -z $ENTERPRISE ]]; then
    echo "You need to specify at least Community or Enterprise"
    echo "Format: $0 -n blogname -e OR -c (enterprise or community) -m (for multisite) -s (for site sync)"
	exit 1
fi

PLUGINNAME=livefyre-wordpress-c.zip

if [[ $ENTERPRISE ]]; then
    PLUGINNAME=livefyre-wordpress-e.zip
fi

if [[ $MULTISITE ]]; then
    PLUGINNAME="${PLUGINNAME%%.*}-m.zip"
fi

if [[ $SITESYNC ]]; then
    PLUGINNAME="${PLUGINNAME%%.*}-s.zip"
fi


./build.sh "$@"

if [[ $MULTISITE ]]; then

	scp $PLUGINNAME root@orangesaregreat.com:/var/www/orangesaregreat.com/$BLOGNAME/wp-content/plugins/
	sleep 1
	ssh root@orangesaregreat.com "cd /var/www/orangesaregreat.com/$BLOGNAME/wp-content/plugins/; unzip -o $PLUGINNAME; rm $PLUGINNAME"

else
	scp $PLUGINNAME root@orangesaregreat.com:/var/www/orangesaregreat.com/$BLOGNAME/wp-content/plugins/
	sleep 1
	ssh root@orangesaregreat.com "cd /var/www/orangesaregreat.com/$BLOGNAME/wp-content/plugins/; unzip -o $PLUGINNAME; rm $PLUGINNAME"

fi
