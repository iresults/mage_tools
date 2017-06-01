#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";

function lib::has_argument() {
    if [[ "$#" -lt "1" ]]; then
        lib::print_error "Missing argument 1 (search)";
    fi
    if [[ "$#" -lt "2" ]]; then
        lib::print_error "At least 2 arguments must be given";
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
