#!/bin/bash
WORDPRESS="E:\wamp64\www\wordpress\wp-content\plugins\dienstedienst"

cd ./build

# 1. AUFRÄUMEN!!!
mkdir -p ./dienstedienst
rm -rf ./dienstedienst/*.*
rm dienstedienst.zip

# 2. Alles rüberkopieren
cp ../wordpress/*.* dienstedienst

# 3. Zippen
./7za.exe a -r dienstedienst.zip dienstedienst

# 4. Zusätzlich in's Wordpress-Verzeichnis kopieren, damit nicht manuell installiert werden muss.
cp dienstedienst/*.* $WORDPRESS