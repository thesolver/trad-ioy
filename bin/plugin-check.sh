#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIST="/tmp/trad-itores-in-one-year-dist"
rm -rf "$DIST"
mkdir -p "$DIST"
rsync -a --delete \
	--exclude '.git' --exclude '.github' --exclude 'vendor' --exclude 'tests' \
	--exclude '.gitignore' --exclude '.distignore' --exclude 'phpunit.xml.dist' \
	--exclude 'phpcs.xml.dist' --exclude 'composer.json' --exclude 'composer.lock' \
	--exclude '.wordpress-org' --exclude 'composer.phar' \
	"$ROOT/" "$DIST/"
wp plugin check traditores-in-one-year --path="${WP_PATH:-/tmp/wordpress}" --plugin="$DIST"
