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
cp -r ../handball dienstedienst
cp -r ../log dienstedienst
cp -r ../zeit dienstedienst
cp -r ../dao dienstedienst
cp -r ../service dienstedienst
cp -r ../components dienstedienst
cp -r ../export dienstedienst
cp -r ../vendor dienstedienst
cp -r ../wordpress/* dienstedienst

# 3. Zippen
./7za.exe a -r dienstedienst.zip dienstedienst

# 4. Zusätzlich in's Wordpress-Verzeichnis kopieren, damit nicht manuell installiert werden muss.
cp -r dienstedienst $WORDPRESS