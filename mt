#!/usr/bin/env bash
set -o nounset
set -e

# @deprecated use `mat` instead

if hash realpath 2> /dev/null; then
    DIR="$( cd "$(dirname $(realpath "${BASH_SOURCE[0]}" ))" && pwd )";
elif hash readlink 2> /dev/null; then
    DIR="$( cd "$(dirname $(readlink -f "${BASH_SOURCE[0]}" ))" && pwd )";
else
    DIR="$( cd "$(dirname "${BASH_SOURCE[0]}" )" && pwd )";
fi

/usr/bin/env bash "$DIR/mat" "$@";
