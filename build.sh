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

# 3. Zippen
./7za.exe a -r dienstedienst.zip dienstedienst

# 4. Zusätzlich in's Wordpress-Verzeichnis kopieren, damit nicht manuell installiert werden muss.
cp -r dienstedienst $WORDPRESS