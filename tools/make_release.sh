#!/usr/bin/env sh

topdir=$(git rev-parse --show-toplevel)
prefix="genericobject"
tag=$(git describe --tags --exact-match HEAD 2>/dev/null)

export GZIP=-9
export TAR_OPTIONS=--mode=u=rwX,g=rwX,o=rX
git archive --prefix=${prefix}/ -o ${topdir}/../${prefix}-${tag}.tar.gz HEAD
