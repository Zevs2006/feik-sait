#!/usr/bin/env sh
cd "$(dirname "$0")/public" || exit 1
php -S localhost:8000
