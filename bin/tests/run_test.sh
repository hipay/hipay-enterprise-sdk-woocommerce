#!/usr/bin/env bash

set -e

# --------
# Parse command line options
# ----------
function parse_args ()
{
	while [[ $# -gt 0 ]]; do
		opt="$1"
		shift

		case "$opt" in
			-e|--exclude)
				exclude+="$1 "
				;;
			-p|--part)
				part="$1"
				;;
			-c|--config)
				config="$1"
				;;
			--env)
				env="$1"
				;;
		esac
	done
}

function join_by () {
  local d=$1; shift;
  echo -n "$1";
  shift;
  printf "%s" "${@/#/$d}";
}

function exclude_file () {
    local e file="$1"
    shift
    for e; do [[ "$file" == *"${e%%*( )}"* ]] && return 0; done
    return 1
}

BACKEND_TESTS_DIR=(cypress/integration/001_BACKEND)
LOCAL_TESTS_DIR=(cypress/integration/006_LOCAL_PAYMENTS)
CREDIT_CARD_TESTS_DIR=(cypress/integration/002_CREDIT_CARD)
NOTIFICATIONS_TESTS_DIR=(cypress/integration/003_NOTIFICATIONS)
BASKET_TESTS_DIR=(cypress/integration/004_BASKET)
CAPTURE_REFUND_TESTS_DIR=(cypress/integration/005_CAPTURE_REFUND)

parse_args "$@"

if [ "$part" = "0" ];then

    ARRAY=()
    IFS=',' read -a EXCL_ARRAY <<< "${exclude}"

    for file in $BACKEND_TESTS_DIR/* $LOCAL_TESTS_DIR/*;
    do
        if ! exclude_file "${file}" ${EXCL_ARRAY[@]} ; then
            ARRAY+=($file)
        fi
    done

    TESTS=$(join_by , "${ARRAY[@]}")

    echo $TESTS
    $(npm bin)/cypress run --spec "${TESTS}" --config $config --env $env
fi

if [ "$part" = "1" ];then

    ARRAY=()
    IFS=',' read -a EXCL_ARRAY <<< "${exclude}"

    for file in $CREDIT_CARD_TESTS_DIR/* $NOTIFICATIONS_TESTS_DIR/* $BASKET_TESTS_DIR/* $CAPTURE_REFUND_TESTS_DIR/*;
    do
        if ! exclude_file "${file}" ${EXCL_ARRAY[@]} ; then
            ARRAY+=($file)
        fi
    done

    TESTS=$(join_by , "${ARRAY[@]}")

    echo $TESTS
    $(npm bin)/cypress run --spec "${TESTS}" --config $config --env $env
fi
