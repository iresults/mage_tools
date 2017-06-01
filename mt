#!/usr/bin/env bash
set -o nounset
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";

: ${VERBOSE=""}

source "$DIR/lib.sh";

function _check_argument_patch_file() {
    if [[ "$#" -lt "1" ]]; then
        lib::print_error "Required argument patch file missing";
        exit 1;
    fi

    if [[ ! -e "$1" ]]; then
        lib::print_error "Provided patch file '$1' does not exist";
        exit 1;
    fi
}

function _check_magento_root() {
    if [[ ! -e "app" ]]; then
        lib::print_error "Run the script from within the Magento root directory";
        exit 1;
    fi
}

function _find_patches_with_prefix() {
    local patch_file="$1";
    local prefix="$2";
    local relative_to_theme="$3";
    for file in `mage::list-patches ${patch_file}`; do
        if [[ "$file" =~ ^$prefix.* ]]; then
            if [[ "$relative_to_theme" == "yes" ]]; then
                _make_theme_path_relative "$file" "$prefix";
            else
                echo "$file";
            fi
        fi
    done
}

function _make_theme_path_relative() {
    local file="$1";
    local prefix="$2";
    echo "$file" | sed -e "s!^$prefix!!" | sed -e "s|^/*[^/]*||" | sed -e "s|^/*[^/]*/||";
}

function _find_custom_templates() {
    local subdirectory_name="$1";
    find "$subdirectory_name" \
        -mindepth 3 \
        -type f \
        -not -path "*/default/blank/*" \
        -not -path "*/default/default/*" \
        -not -path "*/default/modern/*" \
        -not -path "*/default/iphone/*" \
        -not -path "*/base/default/*"
}

function _find_files_to_patch_with_prefix() {
    _check_magento_root;
    _check_argument_patch_file "$@";

    local patch_file="$1";
    local prefix="$2";

    if [[ ! -e "$prefix" ]]; then
        lib::print_warning "Directory '$prefix' defined in prefix does not exist";
        return;
    fi

    local patched_files=$(_find_patches_with_prefix "$patch_file" "$prefix" "yes");
    local custom_templates=$(_find_custom_templates "$prefix");

    while read -r custom_template; do
        # echo "Looking for custom template: $(_make_theme_path_relative "$custom_template" "$prefix";) in $patched_files";

        if [[ "$custom_template" != "" ]]; then
            local custom_template_relative=$(_make_theme_path_relative "$custom_template" "$prefix");
            set +e;
            echo "$patched_files" | grep -q "$custom_template_relative" && {
                if [[ "$VERBOSE" != "false" ]]; then
                    lib::print_info "Custom template: $custom_template";
                    lib::print_info "Patch:";
                    local escaped_custom_template=$(echo ${custom_template_relative} | sed -e 's!/!\\/!g');
                    sed -n -e "/$escaped_custom_template/,/diff/p" "$patch_file" | sed ';$d';
                    echo "";
                    echo "";
                else
                    echo "$custom_template";
                fi
            }
            set -e;
        fi
    done <<< "$custom_templates"
}

function mage::selfupdate() {
    lib::selfupdate;
}

function print_usage() {
    echo "Usage: $0 command-set command

selfupdate                  Update the mage_tools
patch                       Patch related tasks

";
}

function run () {
    if [[ "$#" -lt "1" ]]; then
        lib::print_error "Missing argument 'command-set'";
        print_usage;

        exit 1;
    fi

    lib::check_utilities;

    COMMAND_SET="$1";
    shift;

    if type "mage::$COMMAND_SET" &> /dev/null; then
        mage::${COMMAND_SET} "$@";
    elif [[ -e "$(lib::get_script_directory)/mage_$COMMAND_SET" ]]; then
        /usr/bin/env bash "$(lib::get_script_directory)/mage_$COMMAND_SET" "$@";
    else
        lib::print_error "Command-set '$COMMAND_SET' not found";
    fi
}

run "$@";
