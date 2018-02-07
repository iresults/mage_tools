#!/usr/bin/env bash
set -o nounset
set -e

if [ -e "../lib.sh" ]; then source "../lib.sh"; fi

function mage::watch::help() {
    echo "Usage: $0 command

system      Watch the system log
exception   Watch the exception log
debug       Watch the debug log

";
}

function _check_log_dir() {
    lib::go_to_magento_root;

	if [[ ! -d "var/log/" ]]; then
		lib::print_error "The log directory var/log/ doesn't seem to exist";
		exit 1;
	fi
}

function _check_log_file() {
	if [[ ! -e "var/log/$1.log" ]]; then
		lib::print_error "The log file var/log/$1.log doesn't seem to exist";
		exit 1;
	fi
}

function _watch() {
    _check_log_dir;
    _check_log_file "$1";
    tail -fn 45 --sleep-interval=5 "var/log/$1.log";
}

function mage::watch::system () {
    _watch "system";
}

function mage::watch::exception() {
    _watch "exception";
}

function mage::watch::debug() {
    _watch "debug";
}

function mage::watch::sys() {
    mage::watch::system;
}

function mage::watch::exc() {
    mage::watch::exception;
}

#lib::sub_command::run "mage::watch" "$@";
