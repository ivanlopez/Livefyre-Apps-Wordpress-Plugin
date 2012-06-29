import os

thisfile = os.path.realpath(__file__)
thisdir = '/'.join(thisfile.split('/')[:-1])

def format_version(v):
    return ('%.2f' % float(v))

# read existing version and increment by one
f = open(thisdir + '/version')
v = float(f.read().strip())
new_v = v + .01
f.close()

# update the files that need the new version #
replacements = [
    ('/livefyre-comments/readme.txt', 'Stable tag: '), 
    ('/livefyre-comments/livefyre.php', 'Version: '), 
    ('/livefyre-comments/livefyre_core.php', "'LF_PLUGIN_VERSION', '"),
]
print "\n"
for fname, prefix in replacements:
    cmd = 'sed -i "" "s/%s%s/%s%s/g" "%s%s"' % (prefix, format_version(v), prefix, format_version(new_v), thisdir, fname)
    print "calling:\n%s\n..." % cmd
    os.system(cmd)
print "\n\n"

# now write out the updated version numer to the file
f = open(thisdir + '/version', 'w')
f.write(format_version(new_v))
f.close()

# now make a new zip file
os.system("./build.sh")