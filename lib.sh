#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";

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

function lib::print_demand() {
    _print_colored "[!]: $1" 3;
}

function lib::print_info() {
    _print_colored "[INFO]: $1" 4;
}
