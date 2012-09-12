#! /bin/sh

# Check output file
OUTPUTFILE="livefyre-`sed 's/\./_/' version`.zip"
if [ -f $OUTPUTFILE ]; then
    echo "Cannot overwrite, please remove existing plugin:"
    echo "(eg: rm $OUTPUTFILE)"
    exit 1
fi

# Grab the livefyre-api
git submodule init
git submodule update

# Build the zip file with a prescribed name
zip -r $OUTPUTFILE livefyre-comments/ -x "livefyre-comments/**/.*" -x "livefyre-comments/.*"

# Indicate success
echo ""
echo "* Made a new zip file for you: $OUTPUTFILE"
