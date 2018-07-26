#!/bin/bash
clear
echo Scss2css - Mac
SOURCE=$(dirname ${BASH_SOURCE[0]})
sass --watch "$SOURCE"/scss:"$SOURCE"/css --style compact