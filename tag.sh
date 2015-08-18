#!/bin/sh

# Récupération du tag
right_now=$(date +"%x %r %Z")

if [ "$1" = "" ]; then
    echo "Please set tag value as first parameter"
    exit
fi
echo "1/3: Enregistrement dans le CORE du tag $1"
echo "$right_now;$1" >> version.txt

echo "2/3: Commit sur GIT de la modification du CORE"
git add version.txt
git commit -m "update version.txt for tag version : $1"
git push origin master

echo "3/3: Enregistrement du tag sur GIT"

