#!/bin/sh
  URLLIST=`wget -q https://mcrscifi.wordpress.com/ -O - \
 | sed 's/&/&amp;/' \
 | xmllint --recover --xpath "//section[@id='archives-3']/nav/ul/li/a/@href"  -  2> /dev/null \
 | sed 's/^ href="//' | sed 's/"$//'`
  
for i in $URLLIST 
do
    echo "$i , " ` wget -q $i -O - \  | sed 's/&/&amp;/'   | xmllint --recover --xpath "//article/header/h2/a/text()"  -  2> /dev/null | awk ' { print $6} ' `
done
