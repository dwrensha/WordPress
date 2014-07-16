#! /bin/sh
STATICDIR=/var/www
TMPDIR=/var/www_`date +%s%N`
URL=http://127.0.0.1:10000/
REPLACE_URL=http://this_will_be_replaced_by_sandstorm

rm -rf $TMPDIR
wget -r -p -np -nH -U 'sandstormpublish' -P $TMPDIR $URL
find $TMPDIR -name '*\?*' -print0 | xargs -0 -n1 bash -c 'echo "moving $0 to ${0%\?*}";mv "$0" "${0%\?*}"'
find $TMPDIR -type f -exec sed -i "s|${REPLACE_URL}/|/|g" {} \;
find $TMPDIR -type f -exec sed -i "s|${REPLACE_URL}|/|g" {} \; # URLs without a trailing / need to be changed to point the index

mv $STATICDIR ${STATICDIR}_tmp
mv $TMPDIR $STATICDIR
rm -rf ${STATICDIR}_tmp
