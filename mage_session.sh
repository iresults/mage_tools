#!/usr/bin/env bash
set -o nounset
set -e

if hash realpath 2> /dev/null; then
    DIR="$( cd "$(dirname $(realpath "${BASH_SOURCE[0]}" ))" && pwd )";
elif hash readlink 2> /dev/null && [[ "$(uname -s)" != "Darwin" ]]; then
    DIR="$( cd "$(dirname $(readlink -f "${BASH_SOURCE[0]}" ))" && pwd )";
else
    DIR="$( cd "$(dirname "${BASH_SOURCE[0]}" )" && pwd )";
fi

if [ -e "lib.sh" ]; then source "lib.sh"; fi
source "$DIR/lib.sh";

function mage::session::help() {
    echo "Usage: $0 command

gc  Remove old sessions

";
}

function mage::session::gc() {
    local days="15";
    if [[ "$#" -gt "1" ]]; then
        days="$1";
    fi

    lib::go_to_magento_root;
    find var/session/ -type f -mtime "+$days" -print
}

lib::sub_command::run "mage::session" "$@";
