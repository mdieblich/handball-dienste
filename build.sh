#!/bin/bash
# 0. Sicherstellen, dass config-Datei existiert
if [ ! -e "config.sh" ] ; then
    printf "#!/bin/bash\r\nWORDPRESS=\"pfad\zu\...\wordpress\wp-content\plugins\"" > config.sh
    exit;
fi
source config.sh

cd ./build

# 1. AUFRÄUMEN!!!
rm -rf dienstedienst
rm dienstedienst.zip

# 2. Alles rüberkopieren
mkdir -p ./dienstedienst
cp -r ../src/* dienstedienst
cp -r ../vendor dienstedienst

#3. Versionsnummer setzen
LAST_VERSION=$(cat ../version.txt)
NEXT_VERSION=$(echo $LAST_VERSION | awk -F. -v OFS=. '{$NF++;print}')
echo $NEXT_VERSION > ../version.txt
sed -i -e  's/VERSIONSTRING/'$NEXT_VERSION'/' dienstedienst/dienstedienst.php

# 4. Zippen
./7za.exe a -r dienstedienst.zip dienstedienst

# 5. Zusätzlich in's Wordpress-Verzeichnis kopieren, damit nicht manuell installiert werden muss.
cp -r dienstedienst $WORDPRESS