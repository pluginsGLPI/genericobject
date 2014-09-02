#!/usr/bin/env sh

topdir=$(git rev-parse --show-toplevel)
prefix="genericobject"
tag=$(git describe --tags --exact-match HEAD 2>/dev/null)

git archive --prefix=${prefix}/ -9 -o ${topdir}/../${prefix}-${tag}.zip HEAD
