#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";

### MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM
### HELPER METHODS
### MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM
function lib::check_utilities() {
    if ! hash find 2> /dev/null; then
        >&2 echo "command 'find' not found";
        exit 1;
    fi
    if ! hash xargs 2> /dev/null; then
        >&2 echo "command 'xargs' not found";
        exit 1;
    fi
    if ! hash grep 2> /dev/null; then
        >&2 echo "command 'grep' not found";
        exit 1;
    fi
    if ! hash git 2> /dev/null; then
        >&2 echo "command 'git' not found";
        exit 1;
    fi
    if ! hash sed 2> /dev/null; then
        >&2 echo "command 'sed' not found";
        exit 1;
    fi
}

function lib::get_script_directory() {
    if hash realpath 2> /dev/null; then
        pushd "$(dirname `realpath ${BASH_SOURCE[0]}`)" > /dev/null;
    else
        pushd "$(dirname ${BASH_SOURCE[0]})" > /dev/null;
    fi

    pwd;
}

function lib::selfupdate() {
    cd $(lib::get_script_directory);

    if [[ -e "lib.sh" ]]; then
        git pull;
    else
        lib::print_error "Could not reliably determine the installation dir";
    fi
}


function lib::check_magento_root() {
    if [[ ! -e "app" ]]; then
        lib::print_error "Run the script from within the Magento root directory";
        exit 1;
    fi
}

function lib::has_argument() {
    if [[ "$#" -lt "1" ]]; then
        lib::print_error "Missing argument 1 (search)";
    fi

    local search="$1";
    shift;
    for var in "$@"; do
        if [[ "$var" == "$search" ]]; then
            echo "true";
            return;
        fi
    done
    echo "false";
}

function _print_colored() {
    test -t 1 && tput setaf $2;
    echo "$1";
    test -t 1 && tput setaf 0;
}

function lib::print_error() {
    test -t 1 && tput setaf 1;
    >&2 echo "[ERROR]: $1";
    test -t 1 && tput setaf 0;
}

function lib::print_warning() {
    test -t 1 && tput setaf 1;
    >&2 echo "[WARNING]: $1";
    test -t 1 && tput setaf 0;
}

function lib::print_head() {
    _print_colored "[TASK]: $1" 2;
}

function lib::print_success() {
    _print_colored "$1" 2;
}

function lib::print_demand() {
    _print_colored "[!]: $1" 3;
}

function lib::print_info() {
    _print_colored "[INFO]: $1" 4;
}
