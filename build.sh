#!/bin/bash

# Grab the arguments and decide which plguin to make
while getopts n:mecs option
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

if [[ $COMMUNITY && $ENTERPRISE ]]; then
        echo "You cannot have a Community and Enterprise Version at the same time"
        exit 1
fi

if [[ -z $COMMUNITY && -z $ENTERPRISE ]]; then
        echo "You need to specify at least Community or Enterprise"
        exit 1
fi

# Need to update all of the files to include the right version from the version file
VERSION=`cat version`
find livefyre-comments/src/ -type f -exec sed -i.bak "s/.*Version:.*/Version: $VERSION/" {} +
sed -i.bak "s/.*Version:.*/Version: $VERSION/" livefyre-comments/livefyre.php
# Remove backup files
rm livefyre-comments/src/*.bak
rm livefyre-comments/src/admin/*.bak
rm livefyre-comments/src/import/*.bak
rm livefyre-comments/src/display/*.bak
rm livefyre-comments/src/sync/*.bak
# Update the stable tag
sed -i.bak "s/.*Stable tag:.*/Stable tag: ${VERSION%%-*}/" livefyre-comments/readme.txt
rm livefyre-comments/*.bak

# Create a temp directory to store changed plugins
mkdir temp_build
cp -r livefyre-comments temp_build/
cd temp_build/livefyre-comments


# sed things to make the plugin build for ceratin options.
# Options are:
# Community: Multisite turned off and force use of Livefyre.com domain.
# Enterprise: Allow the use of any domain as well as a more robust settings page to add code snippits.
# 	Multisite: Allow for multisite support
# 	Site Sync: Allow for site_sync support
if [[ $COMMUNITY ]]; then
    PLUGINNAME=livefyre-wordpress-c.zip
    echo $PLUGINNAME
	echo "Adding in community related plugin items!"
	# Remove enterprise stuff from the plugin
	EXCLUDES="livefyre-comments/src/admin/enterprise-settings.php livefyre-comments/src/admin/enterprise-multisite.php"

	# Always add in SiteSync for Community

	# Always add in Multisite for Community

	# Update the versions in the README

elif [[ $ENTERPRISE ]]; then
    PLUGINNAME=livefyre-wordpress-e.zip
    echo $PLUGINNAME
	echo "Adding in enterprise related plugin items!"
	# Add in enterprise level things

    EXCLUDES="livefyre-comments/src/admin/settings-template.php livefyre-comments/src/admin/multisite-settings.php livefyre-comments/src/import/Livefyre_Import_Impl.php"

	# sed-ing the settings page to use the enterprise version
	cd src/admin
	sed -i.bak 's/\/settings-template.php/\/enterprise-settings.php/g' Livefyre_Admin.php
    rm Livefyre_Admin.php.bak
	cd ../..

	# Check if we need mulitsite
	if [[ $MULTISITE ]]; then
        PLUGINNAME="${PLUGINNAME%%.*}-m.zip"
        echo $PLUGINNAME
    	echo "Adding multisite related plugin items!"
    	# Add in multisite level stuff
		cd src/admin
		sed -i.bak 's/\/multisite-settings.php/\/enterprise-multisite.php/g' Livefyre_Admin.php
		rm Livefyre_Admin.php.bak
		cd ../..
    else
        echo "Removing multisite related plugin items!"
        # Add in multisite level stuff
        cd src/admin
        sed -i.bak "/define( 'LF_MULTI_SETTINGS_PAGE', '\/multisite-settings.php' );/d" Livefyre_Admin.php
        rm Livefyre_Admin.php.bak
        cd ../..

        EXCLUDES="$EXCLUDES livefyre-comments/src/admin/enterprise-multisite.php"
	fi

    # Check if we need site sync
	if [[ $SITESYNC ]]; then
        PLUGINNAME="${PLUGINNAME%%.*}-s.zip"
        echo $PLUGINNAME
		echo "Stubbing out Site Sync related items!"
		# Remove site_sync level stuff

        cd src/
        sed -i.bak 's/Livefyre_Sync_Impl/Livefyre_Sync_Stub/g' Livefyre_WP_Core.php
        rm Livefyre_WP_Core.php.bak
        cd ..

        sed -i.bak '/require_once( dirname( __FILE__ ) . "\/src\/sync\/sync_helpers.php" );/d' livefyre.php
        rm livefyre.php.bak

        EXCLUDES="$EXCLUDES livefyre-comments/src/sync/Livefyre_Sync_Impl.php"
	fi

    # Switch import to stub
    cd src
    sed -i.bak 's/Livefyre_Import_Impl/Livefyre_Import_Stub/g' Livefyre_WP_Core.php
    rm Livefyre_WP_Core.php.bak
    cd ..

    # Update the versions in the README

fi

# Actually build the new files now and come back down to temp_build
cd ..
zip -r $PLUGINNAME livefyre-comments/ -x "livefyre-comments/**/.*" -x "livefyre-comments/.*"

for EXCLUDE in $EXCLUDES; do
    zip -d $PLUGINNAME $EXCLUDE
done

mv $PLUGINNAME ..

# Delete temp files as each build is different
cd ..
rm -r temp_build

for EXCLUDE in $EXCLUDES; do
	echo $EXCLUDE
done

