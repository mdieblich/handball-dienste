#!/bin/bash

cd ./build

# 1. AUFRÄUMEN!!!
mkdir -p ./dienstedienst
rm -rf ./dienstedienst/*.*
rm dienstedienst.zip

# 2. Alles rüberkopieren
cp ../wordpress/*.* dienstedienst

# 3. Zippen
./7za.exe a -r dienstedienst.zip dienstedienst