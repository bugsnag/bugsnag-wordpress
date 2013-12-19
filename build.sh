#!/bin/bash

BUILD_DIR=build
PLUGIN_NAME=bugsnag
PLUGIN_DIR=$BUILD_DIR/$PLUGIN_NAME
ZIP_NAME=bugsnag-wordpress.zip

# Install dependencies
composer install

# Prepare the build directory
mkdir -p $PLUGIN_DIR

# Copy plugin files to the build directory
cp -r bugsnag.php readme.txt views $PLUGIN_DIR

# Copy vendored bugsnag to the build directory
cp -r vendor/bugsnag/bugsnag/src/Bugsnag $PLUGIN_DIR/bugsnag-php

# Zip it up
cd $BUILD_DIR
zip -r $ZIP_NAME $PLUGIN_NAME
cp $ZIP_NAME ../
cd ..

# Remove the temp directory
rm -rf $BUILD_DIR