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
    if ! hash php 2> /dev/null; then
        >&2 echo "command 'php' not found";
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

function lib::go_to_magento_root() {
    if [[ -e "app" ]]; then
        return;
    fi
    if [[ -e "www" ]]; then
        cd "www";
    elif [[ -e "web" ]]; then
        cd "web";
    elif [[ -e "public_html" ]]; then
        cd "public_html";
    elif [[ -e "httpdocs" ]]; then
        cd "httpdocs";
    fi
    lib::check_magento_root;
}

function lib::get_magento_version() {
    lib::go_to_magento_root;
    php -r 'include_once __DIR__ . "/app/Mage.php";echo Mage::getVersion();'
}

function lib::sub_command::run () {
    if [[ "$#" -lt "1" ]]; then
        lib::print_error "Logic error: Missing argument 'namespace'";

        exit 1;
    fi

    local namespace="$1";
    shift;

    if [[ "$#" -lt "1" ]] \
        || [[ "$(lib::has_argument "-h" "$@")" == "true" ]] \
        || [[ "$(lib::has_argument "--help" "$@")" == "true" ]] \
        || [[ "$(lib::has_argument "help" "$@")" == "true" ]]; then
        lib::print_error "Missing argument 'command'";
        ${namespace}::help;

        exit 1;
    fi

    lib::check_utilities;

    COMMAND="$1";
    shift;

    if type "$namespace::$COMMAND" &> /dev/null; then
        ${namespace}::${COMMAND} "$@";
    else
        lib::print_error "Command '$COMMAND' not found";
    fi
}

function lib::command_exists() {
    if type "mage::$1" &> /dev/null; then
        echo "true";
    else
        echo "false";
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
