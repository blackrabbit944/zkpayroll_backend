#!/bin/sh
find ../../ -name "*.php" -type f -not -path "*/vendor/*" -not -path "../_framework/util/*" | while read n
do
    echo Parsing $n...
    xgettext -j -d locale $n --from-code=utf-8 --keyword=__
done

for i in *.po
do
    if [ "locale.po" = "$i" ]
    then
        continue
    fi

    msgmerge -U -N $i locale.po
done

exit 0


