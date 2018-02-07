#!/usr/bin/env bash
set -o nounset
set -e

if [ -e "../lib.sh" ]; then source "../lib.sh"; fi

function mage::info::help() {
    echo "Usage: $0 command

version     Print the installed Magento version

";
}

function mage::info::version () {
    lib::get_magento_version;
}
