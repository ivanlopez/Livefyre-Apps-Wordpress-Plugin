zip -r livefyre-`sed 's/\./_/' version`.zip livefyre-comments/ -x "livefyre-comments/**/.*" -x "livefyre-comments/.*"
echo "
*Made a new zip file for you: livefyre-`sed 's/\./_/' version`.zip"