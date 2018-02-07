#!/usr/bin/env bash
set -o nounset
set -e

if [ -e "../lib.sh" ]; then source "../lib.sh"; fi

function print_usage() {
    echo "Usage: $0 [report-id]

";
}

function _show_report() {
    if [[ ! -e "var/report/$1" ]]; then
        lib::print_error "Report $1 does not exist";
        exit 1;
    fi
    echo "Show report: #$1";
    cat "var/report/$1";
}

function _get_latest() {
    ls -ltr var/report | awk '{print $9}' | tail -n 1
}
function _show_latest() {
    _show_report $(_get_latest);
}

function run () {
    lib::go_to_magento_root;
    if [[ "$(lib::has_argument "-h" "$@")" == "true" ]] \
        || [[ "$(lib::has_argument "--help" "$@")" == "true" ]] \
        || [[ "$(lib::has_argument "help" "$@")" == "true" ]]; then
        print_usage;

        exit 0;
    fi

    if [[ "$#" -gt "0" ]]; then
        _show_report "$1";
    else
        _show_latest;
    fi
}

run "$@";
