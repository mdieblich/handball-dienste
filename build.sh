#!/bin/bash

cd ./build

# 1. AUFRÄUMEN!!!
mkdir -p ./tmp
rm -rf ./tmp/*.*
rm dienstedienst.zip

# 2. Alles rüberkopieren
cp ../wordpress/*.* tmp

# 3. Zippen
./7zr.exe a dienstedienst.zip .\tmp\*.*